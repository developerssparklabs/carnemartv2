<?php
/**
 * Plugin Name:     Bafar :: Update Stock
 * Description:     Sincroniza horarios con WP-Cron a diario (00:10 AM local) y, por cada tienda activa,
 *                  consulta el stock vía POST a SASOC, lo guarda en postmeta [wcmlim_stock_at_{store}],
 *                  y hace invalidación de caché por producto.
 * Version:         1.2
 * Author:          Dens – Spark Labs
 * Text Domain:     bafar-update-stock
 */

if (!defined('ABSPATH'))
    exit;

/* =========================
 * Config
 * ========================= */
const BFO_DEBUG = true;                     // true => logs verbosos en Woo logs
const BFO_DAILY_LOCAL_TIME = '00:10:00';               // hora local para el disparo maestro
const BFO_DELAY_BETWEEN_STORES = 40;                       // seg entre tiendas (throttle de SASOC)
const BFO_REQUEST_TIMEOUT = 30;                       // seg timeout HTTP
const BFO_MAX_RETRIES = 2;                        // reintentos adicionales (total = 1 + BFO_MAX_RETRIES)
const BFO_LOCK_TTL = 6 * HOUR_IN_SECONDS;      // TTL lock del run (a prueba de flush)
const BFO_STORE_LOCK_TTL = 30 * MINUTE_IN_SECONDS;   // TTL lock por tienda
const BFO_RUN_GC_AFTER = 12 * HOUR_IN_SECONDS;     // GC del estado del run
const BFO_API_STOCK = 'https://sasoc.asofom.online/api/stock_productos_carnemart.php';

/* =========================
 * Logger helpers
 * ========================= */
function bfo_logger()
{
    return function_exists('wc_get_logger') ? wc_get_logger() : null;
}
function bfo_log(string $msg, string $level = 'info', array $ctx = [])
{
    if (!BFO_DEBUG)
        return;
    $l = bfo_logger();
    if ($l)
        $l->log($level, $msg, array_merge(['source' => 'bfo_stock_sasoc'], $ctx));
}
function bfo_log_ctx(array $extra = []): array
{
    $base = [];
    foreach (['run_id', 'store_id', 'centro', 'ms', 'updated', 'bytes', 'rows_total', 'rows_bad', 'skus_miss', 'skus_hit', 'skus_changed', 'skus_same'] as $k) {
        if (array_key_exists($k, $extra))
            $base[$k] = $extra[$k];
    }
    return $base + $extra;
}

/* =========================
 * Schedules extra (weekly)
 * ========================= */
add_filter('cron_schedules', function ($s) {
    if (!isset($s['weekly']))
        $s['weekly'] = ['interval' => 7 * DAY_IN_SECONDS, 'display' => __('Once Weekly')];
    return $s;
});

/* =========================
 * Tiendas activas
 * ========================= */
function bfo_get_active_stores(): array
{
    $out = [];
    $terms = get_terms(['taxonomy' => 'locations', 'hide_empty' => false, 'fields' => 'all']);
    if (is_wp_error($terms)) {
        bfo_log('get_terms error: ' . $terms->get_error_message(), 'error');
        return $out;
    }
    foreach ($terms as $t) {
        $activo = get_term_meta($t->term_id, 'centro_activo', true);
        $centro = get_term_meta($t->term_id, 'centro_location', true);
        if ((string) $activo === '1' && $centro) {
            $out[] = ['id' => (int) $t->term_id, 'centro' => (string) $centro];
        }
    }
    bfo_log('Active stores discovered: ' . count($out));
    return $out;
}

/* =========================
 * Locks en DB (wp_options)
 * ========================= */
function bfo_acquire_db_lock(string $key, int $ttl, ?string &$token_out = null): bool
{
    $now = time();
    $token = wp_generate_password(20, false);
    $value = wp_json_encode(['token' => $token, 'expires' => $now + $ttl]);
    $created = add_option($key, $value, '', 'no');
    if (!$created) {
        $raw = get_option($key, '');
        $cur = $raw ? json_decode($raw, true) : null;
        if (!$cur || !isset($cur['expires']) || (int) $cur['expires'] <= $now) {
            update_option($key, $value, false);
            $token_out = $token;
            return true;
        }
        return false;
    }
    $token_out = $token;
    return true;
}
function bfo_release_db_lock(string $key, string $token): void
{
    $raw = get_option($key, '');
    $cur = $raw ? json_decode($raw, true) : null;
    if ($cur && isset($cur['token']) && hash_equals($cur['token'], $token))
        delete_option($key);
}

/* Helpers lock de run (incluye run_id dentro del option) */
function bfo_get_run_lock(): ?array
{
    $raw = get_option('bfo_run_lock', '');
    if (!$raw)
        return null;
    $cur = json_decode($raw, true);
    return is_array($cur) ? $cur : null;
}
function bfo_acquire_lock(): ?string
{
    $existing = bfo_get_run_lock();
    if ($existing && isset($existing['expires']) && (int) $existing['expires'] > time()) {
        bfo_log('Lock already held, skip new run', 'warning', bfo_log_ctx(['run_id' => $existing['run_id'] ?? 'unknown']));
        return null;
    }
    $token = null;
    if (!bfo_acquire_db_lock('bfo_run_lock', BFO_LOCK_TTL, $token)) {
        bfo_log('Failed to acquire DB lock', 'warning');
        return null;
    }
    $run_id = (string) (current_time('timestamp')) . '-' . wp_generate_password(6, false);
    update_option('bfo_run_lock', wp_json_encode([
        'token' => $token,
        'expires' => time() + BFO_LOCK_TTL,
        'run_id' => $run_id
    ]), false);
    bfo_log('Lock acquired', 'info', bfo_log_ctx(['run_id' => $run_id]));
    return $run_id;
}
function bfo_release_lock(?string $run_id = null)
{
    $cur = bfo_get_run_lock();
    if (!$cur)
        return;
    if (!$run_id || (!empty($cur['run_id']) && $cur['run_id'] === $run_id)) {
        bfo_release_db_lock('bfo_run_lock', (string) $cur['token']);
        bfo_log('Lock released', 'info', bfo_log_ctx(['run_id' => $cur['run_id'] ?? $run_id]));
    } else {
        bfo_log('Lock NOT released (run_id mismatch)', 'warning', bfo_log_ctx(['run_id' => $run_id]));
    }
}

/* =========================
 * Run state helpers (en options)
 * ========================= */
function bfo_new_run_state(array $stores): array
{
    return ['started' => time(), 'finished' => null, 'stores' => wp_list_pluck($stores, 'id'), 'scheduled' => [], 'done' => []];
}
function bfo_run_option_key($run_id)
{
    return "bfo_run_state_$run_id";
}
function bfo_save_run_state($run_id, array $state)
{
    update_option(bfo_run_option_key($run_id), $state, false);
}
function bfo_get_run_state($run_id)
{
    return get_option(bfo_run_option_key($run_id), []);
}
function bfo_finish_run($run_id)
{
    $st = bfo_get_run_state($run_id);
    if (!$st)
        return;
    $st['finished'] = time();
    bfo_save_run_state($run_id, $st);
    wp_schedule_single_event(time() + BFO_RUN_GC_AFTER, 'bfo_gc_run_state', [$run_id]);
    bfo_log('Run state marked finished', 'info', bfo_log_ctx(['run_id' => $run_id]));
}
add_action('bfo_gc_run_state', function ($run_id) {
    delete_option(bfo_run_option_key($run_id));
    bfo_log('Run state GC removed', 'info', bfo_log_ctx(['run_id' => $run_id]));
}, 10, 1);

register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('bfo_weekly_sweep')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'weekly', 'bfo_weekly_sweep');
    }
});
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bfo_weekly_sweep');
});

/* =========================
 * Activación/desactivación
 * ========================= */
register_activation_hook(__FILE__, function () {
    if (wp_next_scheduled('bfo_daily_stock_master'))
        return;

    $today = date('Y-m-d', current_time('timestamp'));
    $localTs = strtotime("$today " . BFO_DAILY_LOCAL_TIME);
    if ($localTs <= current_time('timestamp'))
        $localTs = strtotime('+1 day', $localTs);
    $gmtTs = strtotime(get_gmt_from_date(date('Y-m-d H:i:s', $localTs)));

    wp_schedule_event($gmtTs, 'daily', 'bfo_daily_stock_master');
    bfo_log('Activation scheduled master at local ' . BFO_DAILY_LOCAL_TIME . ' (UTC ' . $gmtTs . ')');
});
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bfo_daily_stock_master');
    bfo_log('Deactivation cleared master schedule');
});

/* =========================
 * Manual trigger ?bfo-run-now=1 (solo admins)
 * ========================= */
add_action('init', function () {
    if (!is_user_logged_in() || !current_user_can('manage_options'))
        return;
    if (isset($_GET['bfo-run-now'])) {
        do_action('bfo_daily_stock_master');
        wp_die('bfo_daily_stock_master triggered');
    }
});

/* =========================
 * Maestro: programa workers por tienda
 * ========================= */
add_action('bfo_daily_stock_master', function () {
    $run_id = bfo_acquire_lock();
    if (!$run_id)
        return;

    $stores = bfo_get_active_stores();
    $state = bfo_new_run_state($stores);
    bfo_save_run_state($run_id, $state);

    bfo_log('Stores to process: ' . count($stores), 'info', bfo_log_ctx(['run_id' => $run_id]));

    $delay = 0;
    $i = 0;
    $N = count($stores);
    foreach ($stores as $s) {
        $ts = time() + $delay;
        wp_schedule_single_event($ts, 'bfo_update_stock_for_store', [(int) $s['id'], (string) $s['centro'], $run_id]);
        // Solo persistimos scheduled una vez al final del loop para reducir writes
        $state['scheduled'][$s['id']] = $ts;

        bfo_log(
            "Enqueued store {$s['id']} ({$s['centro']}) i=" . (++$i) . "/$N ETA=" . gmdate('H:i:s', $ts),
            'info',
            bfo_log_ctx(['run_id' => $run_id, 'store_id' => $s['id'], 'centro' => $s['centro']])
        );
        $delay += BFO_DELAY_BETWEEN_STORES;
    }
    bfo_save_run_state($run_id, $state);

    // Watchdog de cierre
    $expected = $delay + 90; // colchón
    wp_schedule_single_event(time() + $expected, 'bfo_watchdog_summary', [$run_id, $N]);
    bfo_log('Watchdog scheduled in ' . $expected . 's', 'info', bfo_log_ctx(['run_id' => $run_id]));
});

add_action('bfo_watchdog_summary', function ($run_id, $expected_count) {
    $st = bfo_get_run_state($run_id);
    if (!$st)
        return;
    $done = count($st['done']);
    $ok = count(array_filter($st['done'], fn($r) => !empty($r['ok'])));
    $fail = $done - $ok;
    bfo_log("Watchdog summary done=$done/$expected_count ok=$ok fail=$fail", 'info', bfo_log_ctx(['run_id' => $run_id]));
    if ($done >= $expected_count && empty($st['finished'])) {
        bfo_finish_run($run_id);
        bfo_release_lock($run_id);
        bfo_log('Run finished (watchdog close)', 'info', bfo_log_ctx(['run_id' => $run_id]));
    } else {
        // libera lock igual para no bloquear futuros runs
        bfo_release_lock($run_id);
        bfo_log('Watchdog released lock with incomplete run', 'warning', bfo_log_ctx(['run_id' => $run_id]));
    }
}, 10, 2);

/* =========================
 * Worker por tienda (con reintentos)
 * ========================= */
add_action('bfo_update_stock_for_store', function (int $location_id, string $centro, string $run_id = '') {
    $src_store = 'bfo_stock_' . sanitize_key($centro); // logs por tienda
    $t0 = microtime(true);
    $ctx = bfo_log_ctx(['run_id' => $run_id, 'store_id' => $location_id, 'centro' => $centro, 'source' => $src_store]);
    bfo_log("Start store $location_id ($centro)", 'info', $ctx);

    // Lock por tienda (evita doble worker simultáneo)
    $store_lock_key = "bfo_store_lock_{$location_id}";
    $store_lock_token = null;
    if (!bfo_acquire_db_lock($store_lock_key, BFO_STORE_LOCK_TTL, $store_lock_token)) {
        bfo_log("⛔ Saltado duplicado para tienda {$location_id} ({$centro})", 'warning', $ctx);
        return;
    }

    // HTTP + retries
    $resp_body = null;
    $resp_code = 0;
    $err = '';
    $bytes = 0;
    for ($try = 0; $try <= BFO_MAX_RETRIES; $try++) {
        $http_t0 = microtime(true);
        $response = wp_remote_post(BFO_API_STOCK, [
            'timeout' => BFO_REQUEST_TIMEOUT,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode(['centro' => $centro]),
            'data_format' => 'body',
        ]);
        $http_ms = (int) round((microtime(true) - $http_t0) * 1000);

        if (is_wp_error($response)) {
            $err = $response->get_error_message();
            bfo_log("HTTP error try#$try {$http_ms}ms: $err", 'error', $ctx);
        } else {
            $resp_code = (int) wp_remote_retrieve_response_code($response);
            $resp_body = wp_remote_retrieve_body($response);
            $bytes = strlen((string) $resp_body);
            bfo_log("HTTP resp try#$try code=$resp_code bytes=$bytes time={$http_ms}ms", ($resp_code === 200 ? 'info' : 'warning'), $ctx + ['bytes' => $bytes]);
            if ($resp_code === 200 && $bytes > 0)
                break;
            $err = "HTTP $resp_code bytes=$bytes";
        }
        if ($try < BFO_MAX_RETRIES) {
            $sleep = 2 * (1 + $try);
            bfo_log("Retrying in {$sleep}s", 'warning', $ctx);
            wp_sleep($sleep);
        }
    }

    if (!$resp_body || $resp_code !== 200) {
        bfo_store_done($run_id, $location_id, false, 0, $t0, "fetch_failed:$err");
        bfo_release_db_lock($store_lock_key, (string) $store_lock_token);
        return;
    }

    // JSON parse
    $data = json_decode($resp_body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        bfo_log('JSON error: ' . json_last_error_msg(), 'error', $ctx);
        bfo_store_done($run_id, $location_id, false, 0, $t0, 'bad_json');
        bfo_release_db_lock($store_lock_key, (string) $store_lock_token);
        return;
    }

    $rows = $data['result']['IT_STOCK'] ?? null;
    if (!is_array($rows)) {
        bfo_log('Invalid structure (no IT_STOCK)', 'warning', $ctx);
        bfo_store_done($run_id, $location_id, false, 0, $t0, 'no_it_stock');
        bfo_release_db_lock($store_lock_key, (string) $store_lock_token);
        return;
    }

    // Conteos
    $rows_total = count($rows);
    $rows_bad = 0;
    $skus_miss = 0;
    $skus_hit = 0;
    $skus_changed = 0;
    $skus_same = 0;

    $changed_pids = [];
    $meta_key = "wcmlim_stock_at_{$location_id}";

    foreach ($rows as $row) {
        $sku_raw = $row['MATERIALNUMBER'] ?? '';
        $stock_v = isset($row['LABST']) ? abs((float)$row['LABST']) : null;

        if ($sku_raw === '' || !is_numeric($stock_v)) {
            $rows_bad++;
            continue;
        }

        $sku = ltrim($sku_raw, '0') ?: $sku_raw;
        $stock = max(0, (int) round((float) $stock_v));

        $pid = wc_get_product_id_by_sku($sku);
        if (!$pid) {
            $skus_miss++;
            continue;
        }

        $skus_hit++;
        $prev = get_post_meta($pid, $meta_key, true);
        $prev_i = is_numeric($prev) ? (int) $prev : null;

        if ($prev_i === $stock) {
            $skus_same++;
            continue;
        }

        update_post_meta($pid, $meta_key, $stock);
        // Invalidación fina de caché por producto
        if (function_exists('wc_delete_product_transients')) {
            wc_delete_product_transients($pid);
        }
        clean_post_cache($pid);
        wp_cache_delete($pid, 'posts');

        // Log de actualización por producto
        bfo_log("Updated product $pid (SKU $sku): $meta_key = $stock | Store: $centro", 'info', $ctx);

        $changed_pids[] = $pid;
        $skus_changed++;
    }

    // Resumen + cierre de tienda
    $extra = [
        'bytes' => $bytes,
        'rows_total' => $rows_total,
        'rows_bad' => $rows_bad,
        'skus_miss' => $skus_miss,
        'skus_hit' => $skus_hit,
        'skus_changed' => $skus_changed,
        'skus_same' => $skus_same,
    ];
    bfo_log("Parsed rows=$rows_total bad=$rows_bad hit=$skus_hit miss=$skus_miss changed=$skus_changed same=$skus_same", 'info', $ctx + $extra);

    bfo_store_done($run_id, $location_id, true, $skus_changed, $t0, '', $extra);

    // libera lock por tienda
    bfo_release_db_lock($store_lock_key, (string) $store_lock_token);
}, 10, 3);

/* =========================
 * Marca tienda done + actualiza estado de run
 * ========================= */
function bfo_store_done(string $run_id, int $store_id, bool $ok, int $updated, float $t0, string $error = '', array $extra = [])
{
    $ms = (int) round((microtime(true) - $t0) * 1000);
    $ctx = bfo_log_ctx(array_merge(['run_id' => $run_id, 'store_id' => $store_id, 'ms' => $ms, 'updated' => $updated], $extra));

    if ($ok)
        bfo_log("✔ Store $store_id done: updated=$updated in {$ms}ms", 'info', $ctx);
    else
        bfo_log("✖ Store $store_id failed: $error in {$ms}ms", 'error', $ctx + ['error' => $error]);

    if ($run_id) {
        $st = bfo_get_run_state($run_id);
        if ($st) {
            $st['done'][$store_id] = array_merge(['ok' => $ok, 'updated' => $updated, 'ms' => $ms, 'error' => $error], $extra);
            bfo_save_run_state($run_id, $st);

            // ¿terminó todo?
            if (count($st['done']) >= count($st['stores']) && empty($st['finished'])) {
                $okc = count(array_filter($st['done'], fn($r) => !empty($r['ok'])));
                $fail = count($st['done']) - $okc;
                bfo_finish_run($run_id);
                bfo_release_lock($run_id);
                bfo_log("Run completed total=" . count($st['stores']) . " ok=$okc fail=$fail", 'info', bfo_log_ctx(['run_id' => $run_id]));
            }
        }
    }
}