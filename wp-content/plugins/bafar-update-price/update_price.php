<?php
/**
 * Plugin Name: Bafar :: Update Price (directo)
 * Description: Actualiza precios por tienda directamente desde el servicio remoto, sin archivos JSON intermedios. Orquestación maestro/workers con locking, reintentos y logs.
 * Version:     3.1
 * Author:      Dens – Spark Labs
 * Text Domain: bafar-update-price-direct
 */

if (!defined('ABSPATH'))
    exit;

/* =============== Config =============== */
const BFO_PRICE_DEBUG = true;                // logs verbosos
const BFO_PRICE_DAILY_LOCAL_TIME = '01:40:00';          // hora local (CDMX) para el maestro
const BFO_PRICE_DELAY_BETWEEN = 70;                  // segundos entre tiendas (colita suave)
const BFO_PRICE_HTTP_TIMEOUT = 40;                  // timeout HTTP (s)
const BFO_PRICE_MAX_RETRIES = 2;                   // reintentos extra
const BFO_PRICE_LOCK_TTL = 6 * HOUR_IN_SECONDS; // TTL lock del run
const BFO_PRICE_STORE_LOCK_TTL = 30 * MINUTE_IN_SECONDS; // TTL lock por tienda
const BFO_PRICE_RUN_GC_AFTER = 12 * HOUR_IN_SECONDS;   // GC estado de run
const BFO_PRICE_API = 'https://sasoc.asofom.online/api/precios_productos_carnemart.php';

/* =============== Logger helpers =============== */
function bfo_price_logger()
{
    return function_exists('wc_get_logger') ? wc_get_logger() : null;
}
function bfo_price_log($msg, $level = 'info', $ctx = [])
{
    if (!BFO_PRICE_DEBUG)
        return;
    $l = bfo_price_logger();
    if ($l)
        $l->log($level, $msg, array_merge(['source' => 'bfo_price_direct'], $ctx));
}

/* =============== Schedules extra (weekly) =============== */
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['weekly'])) {
        $schedules['weekly'] = [
            'interval' => 7 * DAY_IN_SECONDS,
            'display' => __('Once Weekly')
        ];
    }
    return $schedules;
});

/* =============== Tiendas activas (taxonomy: locations) =============== */
function bfo_price_get_active_stores(): array
{
    $out = [];
    $terms = get_terms(['taxonomy' => 'locations', 'hide_empty' => false, 'fields' => 'all']);
    if (is_wp_error($terms)) {
        bfo_price_log('get_terms error: ' . $terms->get_error_message(), 'error');
        return $out;
    }
    foreach ($terms as $t) {
        $activo = get_term_meta($t->term_id, 'centro_activo', true);
        $centro = get_term_meta($t->term_id, 'centro_location', true);
        if ((string) $activo === '1' && $centro) {
            $out[] = ['id' => (int) $t->term_id, 'centro' => (string) $centro];
        }
    }
    bfo_price_log('Active stores (limited to 2): ' . count($out));
    return $out;
}

/* =============== Locks en DB (wp_options) =============== */
/**
 * Lock genérico en DB con TTL (inmune a flush de cache).
 * Devuelve true si pudo tomar el lock y saca por referencia el $token.
 */
function bfo_price_acquire_db_lock(string $key, int $ttl, ?string &$token_out = null): bool
{
    $now = time();
    $token = wp_generate_password(20, false);
    $value = wp_json_encode(['token' => $token, 'expires' => $now + $ttl]);

    // Intento atómico: crea si no existe
    $created = add_option($key, $value, '', 'no');
    if (!$created) {
        $raw = get_option($key, '');
        $cur = $raw ? json_decode($raw, true) : null;
        if (!$cur || !isset($cur['expires']) || (int) $cur['expires'] <= $now) {
            update_option($key, $value, false);
            $token_out = $token;
            return true;
        }
        return false; // vigente por otro proceso
    }
    $token_out = $token;
    return true;
}
function bfo_price_release_db_lock(string $key, string $token): void
{
    $raw = get_option($key, '');
    $cur = $raw ? json_decode($raw, true) : null;
    if ($cur && isset($cur['token']) && hash_equals($cur['token'], $token)) {
        delete_option($key);
    }
}

/* Run lock helpers (encapsulan run_id) */
function bfo_price_acquire_lock(): ?string
{
    $lock_key = 'bfo_price_run_lock';
    $token = null;

    // Si lock vigente → no arrancar
    $raw = get_option($lock_key, '');
    if ($raw) {
        $cur = json_decode($raw, true);
        if ($cur && isset($cur['expires']) && (int) $cur['expires'] > time()) {
            bfo_price_log('Lock held, skip', 'warning', ['run_id' => $cur['run_id'] ?? 'unknown']);
            return null;
        }
    }

    if (!bfo_price_acquire_db_lock($lock_key, BFO_PRICE_LOCK_TTL, $token)) {
        bfo_price_log('Failed to acquire DB lock', 'warning');
        return null;
    }

    $run = (string) (current_time('timestamp')) . '-' . wp_generate_password(6, false);
    // Persistimos también el run_id dentro del lock para verificar en release
    update_option($lock_key, wp_json_encode([
        'token' => $token,
        'expires' => time() + BFO_PRICE_LOCK_TTL,
        'run_id' => $run,
    ]), false);

    bfo_price_log('Lock acquired', 'info', ['run_id' => $run]);
    return $run;
}
function bfo_price_get_run_lock(): ?array
{
    $raw = get_option('bfo_price_run_lock', '');
    if (!$raw)
        return null;
    $cur = json_decode($raw, true);
    return is_array($cur) ? $cur : null;
}
function bfo_price_release_lock(string $run_id): void
{
    $cur = bfo_price_get_run_lock();
    if (!$cur)
        return;
    if (!empty($cur['run_id']) && $cur['run_id'] === $run_id && !empty($cur['token'])) {
        bfo_price_release_db_lock('bfo_price_run_lock', (string) $cur['token']);
        bfo_price_log('Lock released', 'info', ['run_id' => $run_id]);
    } else {
        // Si no coincide, no soltamos para no interferir otro run
        bfo_price_log('Lock NOT released (run_id mismatch)', 'warning', ['run_id' => $run_id]);
    }
}

/* =============== Run state (options) + GC =============== */
function bfo_price_run_key($run_id)
{
    return "bfo_price_run_$run_id";
}
function bfo_price_new_state($stores)
{
    return [
        'started' => time(),
        'finished' => null,
        'stores' => wp_list_pluck($stores, 'id'),
        'scheduled' => [],   // [store_id => ts]
        'done' => [],   // [store_id => {...}]
    ];
}
function bfo_price_save_state($run, $st)
{
    update_option(bfo_price_run_key($run), $st, false);
}
function bfo_price_get_state($run)
{
    return get_option(bfo_price_run_key($run), []);
}
function bfo_price_finish_run($run)
{
    $st = bfo_price_get_state($run);
    if (!$st)
        return;
    $st['finished'] = time();
    bfo_price_save_state($run, $st);
    wp_schedule_single_event(time() + BFO_PRICE_RUN_GC_AFTER, 'bfo_price_gc_state', [$run]);
    bfo_price_log('Run finished', 'info', ['run_id' => $run]);
}
add_action('bfo_price_gc_state', function ($run) {
    delete_option(bfo_price_run_key($run));
    bfo_price_log('Run GC', 'info', ['run_id' => $run]);
}, 10, 1);

/* =============== Activación / Desactivación =============== */
register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('bfo_price_daily_master')) {
        // próxima 01:40:00 CDMX en UTC
        $mx = new DateTimeZone('America/Mexico_City');
        $utc = new DateTimeZone('UTC');
        $now = new DateTime('now', $mx);
        $first = (clone $now)->setTime(1, 40, 0);
        if ($first <= $now)
            $first->modify('+1 day');
        $ts = (clone $first)->setTimezone($utc)->getTimestamp();
        wp_schedule_event($ts, 'daily', 'bfo_price_daily_master');
        bfo_price_log('Activation scheduled master at local ' . BFO_PRICE_DAILY_LOCAL_TIME . ' (UTC ' . $ts . ')', 'info');
    }
    if (!wp_next_scheduled('bfo_price_weekly_sweep')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'weekly', 'bfo_price_weekly_sweep');
    }
});
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bfo_price_daily_master');
    wp_clear_scheduled_hook('bfo_price_worker_for_store');
    wp_clear_scheduled_hook('bfo_price_weekly_sweep');
});

/* =============== Sweeper semanal: limpia locks y runs obsoletos =============== */
add_action('bfo_price_weekly_sweep', function () {
    global $wpdb;
    $now = time();

    // 1) Run lock expirado
    $cur = bfo_price_get_run_lock();
    if ($cur && isset($cur['expires']) && (int) $cur['expires'] <= $now && !empty($cur['token'])) {
        bfo_price_release_db_lock('bfo_price_run_lock', (string) $cur['token']);
        bfo_price_log('Sweeper: released expired run lock', 'info');
    }

    // 2) Store locks expirados
    $like = $wpdb->esc_like('bfo_price_store_lock_') . '%';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
        $like
    ));
    foreach ((array) $rows as $r) {
        $cur = $r->option_value ? json_decode($r->option_value, true) : null;
        if ($cur && isset($cur['expires']) && (int) $cur['expires'] <= $now) {
            delete_option($r->option_name);
        }
    }

    // 3) Run states muy viejos (> 7 días desde 'finished')
    $like_run = $wpdb->esc_like('bfo_price_run_') . '%';
    $run_opts = $wpdb->get_col($wpdb->prepare(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
        $like_run
    ));
    foreach ((array) $run_opts as $opt) {
        $st = get_option($opt);
        if (is_array($st) && !empty($st['finished']) && ($now - (int) $st['finished']) > 7 * DAY_IN_SECONDS) {
            delete_option($opt);
        }
    }
});

/* =============== Trigger manual: ?bfo-price-now=1 (solo admins) =============== */
add_action('init', function () {
    if (!is_user_logged_in() || !current_user_can('manage_options'))
        return;
    if (isset($_GET['bfo-price-now'])) {
        do_action('bfo_price_daily_master');
        wp_die('bfo_price_daily_master triggered');
    }
});

/* =============== Maestro: agenda workers por tienda =============== */
add_action('bfo_price_daily_master', function () {
    $run = bfo_price_acquire_lock();
    if (!$run)
        return;

    $stores = bfo_price_get_active_stores();
    $state = bfo_price_new_state($stores);
    bfo_price_save_state($run, $state);
    bfo_price_log('Stores to process: ' . count($stores), 'info', ['run_id' => $run]);

    $delay = 0;
    $i = 0;
    $N = count($stores);
    foreach ($stores as $s) {
        $ts = time() + $delay;
        wp_schedule_single_event($ts, 'bfo_price_worker_for_store', [(int) $s['id'], (string) $s['centro'], $run]);
        $state['scheduled'][$s['id']] = $ts;
        bfo_price_save_state($run, $state);
        bfo_price_log(
            "Enqueued store {$s['id']} ({$s['centro']}) i=" . (++$i) . "/$N ETA=" . gmdate('H:i:s', $ts),
            'info',
            ['run_id' => $run, 'store_id' => $s['id'], 'centro' => $s['centro']]
        );
        $delay += BFO_PRICE_DELAY_BETWEEN;
    }

    // Watchdog (cierre de seguridad)
    $expected = $delay + 120;
    wp_schedule_single_event(time() + $expected, 'bfo_price_watchdog', [$run, $N]);
    bfo_price_log('Watchdog scheduled in ' . $expected . 's', 'info', ['run_id' => $run]);
});

add_action('bfo_price_watchdog', function ($run, $expected_count) {
    $st = bfo_price_get_state($run);
    if (!$st)
        return;

    $done = count($st['done']);
    $ok = count(array_filter($st['done'], fn($r) => !empty($r['ok'])));
    $fail = $done - $ok;

    bfo_price_log("Watchdog done=$done/$expected_count ok=$ok fail=$fail", 'info', ['run_id' => $run]);

    if ($done >= $expected_count && empty($st['finished'])) {
        bfo_price_finish_run($run);
        bfo_price_release_lock($run);
        bfo_price_log('Run finished (watchdog close)', 'info', ['run_id' => $run]);
    } else {
        // libera lock igual para no bloquear futuros runs
        bfo_price_release_lock($run);
        bfo_price_log('Watchdog released lock with incomplete run', 'warning', ['run_id' => $run]);
    }
}, 10, 2);

/* =============== Worker por tienda (fetch + actualizar) =============== */
add_action('bfo_price_worker_for_store', function (int $term_id, string $centro_code, string $run_id = '', string $sku = '') {
    global $wpdb;

    if ($sku == '') {
        $src_store = 'bfo_price_' . sanitize_key($centro_code); // log por tienda
    } else {
        $src_store = 'bfo_price_manual';
    }

    $ctx = ['run_id' => $run_id, 'store_id' => $term_id, 'centro' => $centro_code, 'source' => $src_store];

    // Lock por tienda (evita doble ejecución concurrente)
    $store_lock_key = "bfo_price_store_lock_{$term_id}";
    $store_lock_token = null;
    if (!bfo_price_acquire_db_lock($store_lock_key, BFO_PRICE_STORE_LOCK_TTL, $store_lock_token)) {
        bfo_price_log("⛔ Saltado duplicado para tienda {$term_id} ({$centro_code})", 'warning', $ctx);
        return;
    }

    $t0 = microtime(true);
    bfo_price_log("Start store $term_id ($centro_code)", 'info', $ctx);

    // --- customer_group de la tienda ---
    $customer_group = get_term_meta($term_id, 'customer_group', true);
    if ($customer_group === '') {
        $customer_group = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->termmeta} WHERE term_id=%d AND meta_key='customer_group'",
            $term_id
        ));
    }
    $meta_group_key = "eib2bpro_price_tiers_group_{$customer_group}";
    $meta_reg_key = "wcmlim_regular_price_at_{$term_id}";

    // Payload requerido por el API
    $payload = [
        'KEY' => 'STORES',
        'VALUE' => wp_json_encode([$centro_code]), // ej. ["M430"]
        'SKU' => $sku ?? null,
    ];

    // --- HTTP fetch con reintentos ---
    $body = null;
    $code = 0;
    $err = '';
    $bytes = 0;
    for ($try = 0; $try <= BFO_PRICE_MAX_RETRIES; $try++) {
        $t = microtime(true);
        $res = wp_remote_post(BFO_PRICE_API, [
            'timeout' => BFO_PRICE_HTTP_TIMEOUT,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($payload),
            'data_format' => 'body',
        ]);
        $ms = (int) round((microtime(true) - $t) * 1000);
        if (is_wp_error($res)) {
            $err = $res->get_error_message();
            bfo_price_log("HTTP error try#$try {$ms}ms: $err", 'error', $ctx);
        } else {
            $code = (int) wp_remote_retrieve_response_code($res);
            $body = wp_remote_retrieve_body($res);
            $bytes = strlen((string) $body);
            bfo_price_log("HTTP resp try#$try code=$code bytes=$bytes time={$ms}ms", ($code === 200 ? 'info' : 'warning'), $ctx + ['bytes' => $bytes]);
            if ($code === 200 && $bytes > 0)
                break;
            $err = "HTTP $code bytes=$bytes";
        }
        if ($try < BFO_PRICE_MAX_RETRIES)
            wp_sleep(2 * (1 + $try));
    }
    if (!$body || $code !== 200) {
        bfo_price_worker_done($run_id, $term_id, false, 0, $t0, "fetch_failed:$err", []);
        bfo_price_release_db_lock($store_lock_key, (string) $store_lock_token);
        return;
    }

    // --- JSON parse ---
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        bfo_price_log('JSON error: ' . json_last_error_msg(), 'error', $ctx);
        bfo_price_worker_done($run_id, $term_id, false, 0, $t0, 'bad_json', []);
        bfo_price_release_db_lock($store_lock_key, (string) $store_lock_token);
        return;
    }

    // Estructuras posibles
    $root = $data['result'] ?? [];
    $items = [];
    if (isset($root['RESLISTA_PRECIOS']['RESULTS']))
        $items = (array) $root['RESLISTA_PRECIOS']['RESULTS'];
    elseif (isset($root['PRECIOS']['RESULTS']))
        $items = (array) $root['PRECIOS']['RESULTS'];

    if (!$items) {
        bfo_price_worker_done($run_id, $term_id, false, 0, $t0, 'no_results', []);
        bfo_price_release_db_lock($store_lock_key, (string) $store_lock_token);
        return;
    }

    // --- Procesamiento ---
    $updated = 0;
    $nf = 0;
    $chunks = array_chunk($items, 100);
    foreach ($chunks as $cidx => $chunk) {
        bfo_price_log("→ Bloque #" . ($cidx + 1) . " (" . count($chunk) . " items)", 'info', $ctx);
        foreach ($chunk as $row) {
            $sku = isset($row['SKU']) ? trim((string) $row['SKU']) : '';
            if ($sku === '')
                continue;

            $pid = wc_get_product_id_by_sku($sku);
            if (!$pid) {
                $nf++;
                continue;
            }

            // Construir mapa de niveles + determinar first_price y sale_price
            $niveles = isset($row['NIVELES']) ? (array) $row['NIVELES'] : [];
            $mapa = [];
            $first = null;
            $sale = null;

            foreach ($niveles as $niv) {
                $reg = isset($niv['PRECIO_REGULAR']) ? (float) $niv['PRECIO_REGULAR'] : null;
                $qty = isset($niv['CANTIDAD']) ? (float) $niv['CANTIDAD'] : null;
                if ($reg === null || $qty === null)
                    continue;

                if ($first === null)
                    $first = number_format($reg, 2, '.', '');

                // ¿Hay promo exactamente en esta cantidad?
                $promoArr = isset($niv['PROMOCION']) ? (array) $niv['PROMOCION'] : [];
                $hasPromoAtThisLevel = false;
                foreach ($promoArr as $p) {
                    if (!empty($p['ACTIVA']) && $p['ACTIVA'] === 'true' && (float) $p['CANTIDAD_MIN'] === (float) $qty) {
                        $hasPromoAtThisLevel = true;
                        break;
                    }
                }
                if (!$hasPromoAtThisLevel) {
                    $key = number_format($qty, 2, '.', '') . " (Precio regular)";
                    $mapa[$key] = round((float) $reg, 2);
                }

                // Promos activas
                foreach ($promoArr as $p) {
                    if (!empty($p['ACTIVA']) && $p['ACTIVA'] === 'true' && is_numeric($p['PRECIO_OFERTA'])) {
                        $pmin = (float) $p['CANTIDAD_MIN'];
                        $qtyk = number_format($pmin, 2, '.', '');
                        $tipo = isset($niv['TIPO_PRECIO']) ? (string) $niv['TIPO_PRECIO'] : '';
                        $txt = ($tipo === 'Oferton') ? '🔥 ¡OFERTÓN!' : (($tipo === 'Oferta' || $tipo === 'Promocion') ? '🔥 ¡Promoción!' : '');

                        $ff = isset($niv['FECHA_FIN']) ? (string) $niv['FECHA_FIN'] : '';
                        $fecha = '';
                        if ($ff && $ff !== '9999-12-31') {
                            $t = strtotime($ff);
                            $meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
                            $fecha = "hasta el " . date('j', $t) . " de " . $meses[(int) date('n', $t)];
                        }
                        $key = trim($qtyk . ' (' . $reg . ') ' . $txt . ' ' . $fecha);
                        $mapa[$key] = round((float) $p['PRECIO_OFERTA'], 2);

                        if ($sale === null)
                            $sale = number_format((float) $p['PRECIO_OFERTA'], 2, '.', '');
                    }
                }
            }
            if ($sale === null)
                $sale = $first;

            // Guardar tiers JSON en meta del grupo
            $json_map = wp_json_encode($mapa, JSON_UNESCAPED_UNICODE);
            $exists = get_metadata_raw('post', $pid, $meta_group_key, true);
            if ($exists === '')
                add_post_meta($pid, $meta_group_key, $json_map, true);
            else
                update_post_meta($pid, $meta_group_key, $json_map);

            // Guardar precios de tienda + globales WC
            if ($first !== null) {
                update_post_meta($pid, $meta_reg_key, $first);
                update_post_meta($pid, '_regular_price', $first);
            }
            if ($sale !== null) {
                update_post_meta($pid, '_price', $sale);
            }

            // Logs por producto (SKU, post_id, tienda, tiers, prices)
            bfo_price_log(
                "SKU $sku (post $pid) store $centro_code → tiers=" . json_encode($mapa, JSON_PRETTY_PRINT) . " first=$first sale=$sale",
                'info',
                $ctx
            );

            // Invalidación fina de caches
            if (function_exists('wc_delete_product_transients'))
                wc_delete_product_transients($pid);
            clean_post_cache($pid);
            wp_cache_delete($pid, 'posts');

            $updated++;
        }
    }

    bfo_price_log("Parsed items=" . count($items) . " upd=$updated nf=$nf", 'info', $ctx);
    bfo_price_worker_done($run_id, $term_id, true, $updated, $t0, '', ['nf' => $nf, 'items' => count($items), 'source' => $src_store]);

    // liberar lock por tienda
    bfo_price_release_db_lock($store_lock_key, (string) $store_lock_token);
}, 10, 4);

/* =============== Cierre por tienda =============== */
function bfo_price_worker_done(string $run_id, int $store_id, bool $ok, int $updated, float $t0, string $error = '', array $extra = [])
{
    $ms = (int) round((microtime(true) - $t0) * 1000);
    $ctx = array_merge(['run_id' => $run_id, 'store_id' => $store_id, 'ms' => $ms, 'updated' => $updated], $extra);

    if ($ok)
        bfo_price_log("✔ Store $store_id done: updated=$updated in {$ms}ms", 'info', $ctx);
    else
        bfo_price_log("✖ Store $store_id failed: $error in {$ms}ms", 'error', $ctx + ['error' => $error]);

    if ($run_id) {
        $st = bfo_price_get_state($run_id);
        if ($st) {
            $st['done'][$store_id] = array_merge(['ok' => $ok, 'updated' => $updated, 'ms' => $ms, 'error' => $error], $extra);
            bfo_price_save_state($run_id, $st);

            if (count($st['done']) >= count($st['stores']) && empty($st['finished'])) {
                $okc = count(array_filter($st['done'], fn($r) => !empty($r['ok'])));
                $fail = count($st['done']) - $okc;
                bfo_price_finish_run($run_id);
                bfo_price_release_lock($run_id);
                bfo_price_log("Run completed total=" . count($st['stores']) . " ok=$okc fail=$fail", 'info', ['run_id' => $run_id]);
            }
        }
    }
}

/* =============== Utilidad: leer meta sin pasar por filtros (tiny perf) =============== */
if (!function_exists('get_metadata_raw')) {
    function get_metadata_raw($meta_type, $object_id, $meta_key, $single = true)
    {
        global $wpdb;
        $table = $meta_type === 'post' ? $wpdb->postmeta : ($meta_type === 'term' ? $wpdb->termmeta : '');
        if (!$table)
            return '';
        $col = $meta_type === 'post' ? 'post_id' : 'term_id';
        $val = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$table} WHERE {$col}=%d AND meta_key=%s LIMIT 1", $object_id, $meta_key));
        return $single ? ($val !== null ? $val : '') : (array) $val;
    }
}


/* =============== Admin: Submenú "Actualizar precio por SKU" =============== */
add_action('admin_menu', function () {
    // parent_slug: 'woocommerce' para colgar en WooCommerce
    // capability: solo admins/gestores de tienda
    add_submenu_page(
        'woocommerce',
        'Actualizar precio por SKU',
        'Actualizar precio por SKU',
        'manage_woocommerce',
        'bfo-price-update-sku',
        'bfo_price_render_update_by_sku_page',
        60 // Aunque no garantiza orden exacto, ayuda a empujar abajo
    );
}, 60);

function bfo_price_render_update_by_sku_page()
{
    if (!current_user_can('manage_woocommerce')) {
        wp_die('No tienes permisos suficientes.');
    }

    // Mensaje por querystring (sin transients)
    if (!empty($_GET['bfo_msg'])) {
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html(wp_unslash($_GET['bfo_msg'])) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Actualizar precio por SKU</h1>
        <p>Ingresa uno o varios <strong>SKU</strong>. Puedes escribirlos:</p>
        <ul>
            <li>Separados por coma: <code>SKU123, SKU456, SKU789</code></li>
            <li>O en líneas diferentes:<br>
                <code>SKU123<br>SKU456<br>SKU789</code>
            </li>
        </ul>
        <p><em>El sistema los validará automáticamente antes de enviar.</em></p>

        <form id="bfo-update-sku-form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('bfo_price_update_by_sku_nonce', 'bfo_price_update_by_sku_nonce_field'); ?>
            <input type="hidden" name="action" value="bfo_price_update_by_sku">

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="bfo_skus">SKU(s)</label></th>
                        <td>
                            <textarea id="bfo_skus" name="bfo_skus" rows="5" cols="60" class="regular-text"
                                placeholder="Ejemplo:&#10;SKU123, SKU456&#10;o en líneas diferentes:&#10;SKU123&#10;SKU456"></textarea>
                            <p class="description">Separados por coma o por salto de línea. Solo letras y números
                                permitidos.</p>
                            <p id="bfo_sku_feedback" style="margin-top:5px;color:#0073aa;font-weight:bold;"></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button('Actualizar'); ?>
        </form>
    </div>

    <script>
        (function () {
            const textarea = document.getElementById('bfo_skus');
            const feedback = document.getElementById('bfo_sku_feedback');
            const form = document.getElementById('bfo-update-sku-form');

            function validarSKUs() {
                let raw = textarea.value.trim();
                if (!raw) {
                    feedback.textContent = "⚠️ No se ingresaron SKUs.";
                    return [];
                }
                // Normalizar: separar por comas o saltos de línea
                let skus = raw.split(/[\s,]+/).map(s => s.trim()).filter(s => s.length > 0);

                // Validar formato (solo letras, números y guiones opcionales)
                let validos = skus.filter(s => /^[A-Za-z0-9\-]+$/.test(s));
                let invalidos = skus.filter(s => !/^[A-Za-z0-9\-]+$/.test(s));

                if (validos.length) {
                    feedback.textContent = "✔ " + validos.length + " SKU(s) válido(s) detectado(s).";
                    feedback.style.color = "green";
                }
                if (invalidos.length) {
                    feedback.textContent += " ⚠️ Se ignorarán inválidos: " + invalidos.join(", ");
                    feedback.style.color = "red";
                }
                return validos;
            }

            textarea.addEventListener('input', validarSKUs);

            form.addEventListener('submit', function (e) {
                let validos = validarSKUs();
                if (validos.length === 0) {
                    e.preventDefault();
                    alert("Debes ingresar al menos un SKU válido antes de enviar.");
                }
            });
        })();
    </script>
    <?php
}

/* =============== POST handler =============== */
add_action('admin_post_bfo_price_update_by_sku', 'bfo_price_handle_update_by_sku');

function bfo_price_handle_update_by_sku()
{
    if (!current_user_can('manage_woocommerce')) {
        wp_die('No tienes permisos suficientes.');
    }
    if (empty($_POST['bfo_price_update_by_sku_nonce_field']) || !wp_verify_nonce($_POST['bfo_price_update_by_sku_nonce_field'], 'bfo_price_update_by_sku_nonce')) {
        wp_die('Nonce inválido.');
    }

    // Normalizar SKUs (coma o saltos de línea) -> array limpio
    $raw = isset($_POST['bfo_skus']) ? (string) wp_unslash($_POST['bfo_skus']) : '';
    $skus = array_filter(array_unique(array_map('trim', preg_split('/[\s,]+/', $raw))));
    if (empty($skus)) {
        wp_redirect(add_query_arg('bfo_msg', rawurlencode('No se proporcionaron SKUs válidos.'), admin_url('admin.php?page=bfo-price-update-sku')));
        exit;
    }

    // Tiendas activas
    $stores = bfo_price_get_active_stores();
    if (empty($stores)) {
        wp_redirect(add_query_arg('bfo_msg', rawurlencode('No hay tiendas activas configuradas.'), admin_url('admin.php?page=bfo-price-update-sku')));
        exit;
    }

    // Crear run_id (lock global del run)
    $run_id = bfo_price_acquire_lock();
    if (!$run_id) {
        wp_redirect(add_query_arg('bfo_msg', rawurlencode('No se pudo iniciar el proceso (lock). Intenta de nuevo.'), admin_url('admin.php?page=bfo-price-update-sku')));
        exit;
    }

    // (Opcional) Inicializar estado del run — en flujo manual no es esencial,
    // porque no usas watchdog, pero lo guardamos por consistencia.
    $state = bfo_price_new_state($stores);
    bfo_price_save_state($run_id, $state);

    // Programar 1 evento por tienda × por SKU
    $delay_step = 5; // segundos entre eventos para no saturar
    $i = 0;
    $scheduled = 0;
    $skipped_not_found = [];

    foreach ($skus as $sku) {
        // SKU limpio
        $sku = preg_replace('/\s+/', '', (string) $sku);
        if ($sku === '') {
            continue;
        }

        // ✅ Buscar producto por SKU ANTES de programar
        $pid = wc_get_product_id_by_sku($sku);
        if (!$pid) {
            $skipped_not_found[] = $sku;
            continue; // no agendas nada para este SKU
        }

        foreach ($stores as $s) {
            $term_id = (int) $s['id'];
            $centro = (string) $s['centro'];
            $ts = time() + ($i++ * $delay_step);

            // Evita duplicado exacto (por si el admin recarga)
            if (!wp_next_scheduled('bfo_price_worker_for_store', [$term_id, $centro, $run_id, $sku])) {
                wp_schedule_single_event($ts, 'bfo_price_worker_for_store', [$term_id, $centro, $run_id, $sku]);
                $scheduled++;
            }
        }
    }

    // Mensaje final (sin transients)
    $parts = [];
    $parts[] = "Run: {$run_id}";
    $parts[] = "Tiendas: " . count($stores);
    $parts[] = "Eventos programados: {$scheduled}";
    if (!empty($skipped_not_found)) {
        $parts[] = "SKUs inexistentes: " . implode(', ', $skipped_not_found);
    }
    $msg = implode(' | ', $parts);

    if ($scheduled === 0) {
        // Nadie quedó agendado → libera lock de inmediato
        bfo_price_release_lock($run_id);
    }

    wp_redirect(add_query_arg('bfo_msg', rawurlencode($msg), admin_url('admin.php?page=bfo-price-update-sku')));
    exit;
}

// =============== Utilidad: liberar run lock manualmente ===============
add_action('init', function () {
    if (!is_user_logged_in() || !current_user_can('manage_options'))
        return;

    if (isset($_GET['bfo-price-cancel']) && $_GET['bfo-price-cancel'] === '1') {
        $cur = bfo_price_get_run_lock();
        if ($cur && !empty($cur['run_id'])) {
            bfo_price_release_lock((string) $cur['run_id']);
            wp_die('Run lock liberado manualmente.');
        } else {
            wp_die('No hay run lock vigente.');
        }
    }
});