<?php
/**
 * Plugin Name: Bafar :: Ofertones v1
 * Description: Listado dinámico de ofertones con caché (16 por página). Incluye botón para limpiar caché (solo administradores).
 * Version:     3.1
 * Author:      Sparklabs
 */

if (!defined('ABSPATH'))
    exit;

/* ============================================================
 * Configuración
 * ============================================================ */
define('CM_OFERTONES_DEBUG', false);   // true para loguear
define('CM_OFERTONES_CACHE', true);    // activar/desactivar caché fácil
define('CM_TTL_IDS', 3600);          // 1 hora: IDs con oferta (tienda+grupo)
define('CM_TTL_SORT', 3600);          // 1 hora: lista ordenada
define('CM_TTL_HTML', 3600);          // 1 hora: HTML por página (opcional)

/* ============================================================
 * Logger auxiliar
 * ============================================================ */
function cm_log($msg, $level = 'info')
{
    if (!CM_OFERTONES_DEBUG || !function_exists('wc_get_logger'))
        return;
    wc_get_logger()->log($level, $msg, ['source' => 'cm-ofertones']);
}

/* ============================================================
 * Utilidades de caché (transients)
 * ============================================================ */
function cm_cache_get($key)
{
    return CM_OFERTONES_CACHE ? get_transient($key) : false;
}
function cm_cache_set($key, $value, $ttl)
{
    if (CM_OFERTONES_CACHE)
        set_transient($key, $value, $ttl);
}
/** Borra todos los transients que empiecen con el prefijo dado (sin _transient_) */
function cm_cache_del_like($prefix_without_transient)
{
    global $wpdb;
    $like = esc_sql($prefix_without_transient) . '%';
    $wpdb->query("
        DELETE FROM {$wpdb->options}
        WHERE option_name LIKE '_transient_{$like}'
           OR option_name LIKE '_transient_timeout_{$like}'
    ");
}

/* ============================================================
 * Helpers de precios y “oferta real”
 * ============================================================ */

/** Precio regular respetando override por tienda. */
function cm_regular_price_for_store(int $product_id, int $term_id): float
{
    $regular = (float) get_post_meta($product_id, '_regular_price', true);
    $store_override = (float) get_post_meta($product_id, "wcmlim_regular_price_at_{$term_id}", true);
    if ($store_override > 0)
        $regular = $store_override;
    return max(0.0, $regular);
}

/** Mínimo tier para el grupo (sin cache persistente; lectura directa de meta). */
function cm_get_min_tier_price(int $product_id, int $cg): ?float
{
    $tiers_json = get_post_meta($product_id, "eib2bpro_price_tiers_group_{$cg}", true);
    if (!$tiers_json)
        return null;

    $tiers = json_decode($tiers_json, true);
    if (!is_array($tiers) || empty($tiers))
        return null;

    foreach ($tiers as $v) {
        if (is_numeric($v))
            return (float) $v;
        if (is_array($v) && isset($v['price']) && is_numeric($v['price']))
            return (float) $v['price'];
    }
    return null;
}

/** ¿El producto tiene oferta real en esta tienda+grupo? */
function cm_has_real_offer(int $product_id, int $term_id, int $cg): bool
{
    $regular = cm_regular_price_for_store($product_id, $term_id);
    if ($regular <= 0)
        return false;

    $sale = (float) get_post_meta($product_id, '_sale_price', true);
    $tier = cm_get_min_tier_price($product_id, $cg);

    if (($sale <= 0) && ($tier === null))
        return false;

    $effective = $regular;
    if ($sale > 0)
        $effective = min($effective, $sale);
    if ($tier !== null && $tier > 0)
        $effective = min($effective, $tier);

    return ($effective < $regular);
}

/** Métricas de oferta para ordenar por "mejor ofertón". */
function cm_offer_metrics(int $product_id, int $term_id, int $cg): array
{
    $regular = cm_regular_price_for_store($product_id, $term_id);
    if ($regular <= 0)
        return ['regular' => 0.0, 'sale' => 0.0, 'has' => false, 'pct' => 0.0, 'abs' => 0.0];

    $sale = (float) get_post_meta($product_id, '_sale_price', true);
    $tier = cm_get_min_tier_price($product_id, $cg);

    $effective = $regular;
    if ($sale > 0)
        $effective = min($effective, $sale);
    if ($tier !== null && $tier > 0)
        $effective = min($effective, $tier);

    $has = ($effective < $regular);
    $abs = $has ? ($regular - $effective) : 0.0;
    $pct = $has ? ($abs / $regular) : 0.0;

    return ['regular' => $regular, 'sale' => $effective, 'has' => $has, 'pct' => $pct, 'abs' => $abs];
}

/* ============================================================
 * Construcción de IDs con oferta (+ caché)
 * ============================================================ */

/**
 * Devuelve IDs con oferta real para (tienda, grupo).
 * Fuente: productos publicados, con stock_status=instock, stock en tienda >0,
 * y (sale_price>0 OR tiers del grupo). Luego validamos oferta real.
 */
function cm_get_offer_ids(int $term_id, int $cg): array
{
    $ckey = "cm_ids_t{$term_id}_g{$cg}";
    if (($cached = cm_cache_get($ckey)) !== false)
        return (array) $cached;

    global $wpdb;
    $meta_stock_key = "wcmlim_stock_at_{$term_id}";
    $tiers_key = "eib2bpro_price_tiers_group_{$cg}";

    $sql = $wpdb->prepare("
        SELECT DISTINCT p.ID
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} m_status
            ON m_status.post_id = p.ID AND m_status.meta_key = '_stock_status' AND m_status.meta_value = 'instock'
        INNER JOIN {$wpdb->postmeta} m_stock
            ON m_stock.post_id = p.ID AND m_stock.meta_key = %s
        LEFT JOIN {$wpdb->postmeta} m_sale
            ON m_sale.post_id = p.ID AND m_sale.meta_key = '_sale_price'
        LEFT JOIN {$wpdb->postmeta} m_tiers
            ON m_tiers.post_id = p.ID AND m_tiers.meta_key = %s
        WHERE p.post_type = 'product'
          AND p.post_status = 'publish'
          AND CAST(m_stock.meta_value AS DECIMAL(12,4)) > 0
          AND (
                (m_sale.meta_value IS NOT NULL AND TRIM(m_sale.meta_value) <> '' AND CAST(m_sale.meta_value AS DECIMAL(12,4)) > 0)
             OR (m_tiers.meta_value IS NOT NULL AND TRIM(m_tiers.meta_value) <> '')
          )
    ", $meta_stock_key, $tiers_key);

    $candidates = array_map('intval', $wpdb->get_col($sql));
    if ($candidates)
        update_meta_cache('post', $candidates); // precarga en memoria (no persistente)

    $ids = [];
    foreach ($candidates as $pid) {
        if (cm_has_real_offer($pid, $term_id, $cg))
            $ids[] = $pid;
    }
    if ($ids)
        shuffle($ids);

    cm_cache_set($ckey, $ids, CM_TTL_IDS);
    cm_log("IDs construidos t={$term_id} cg={$cg}: " . count($ids) . " de " . count($candidates));
    return $ids;
}

/**
 * Ordena los IDs según sort.
 * - random: tal cual llegan (ya barajados)
 * - best: por mayor % descuento
 */
function cm_sorted_offer_ids(int $term_id, int $cg, string $sort = 'random'): array
{
    if ($sort === 'random')
        return cm_get_offer_ids($term_id, $cg);

    $ckey = "cm_ids_sorted_t{$term_id}_g{$cg}_{$sort}";
    if (($cached = cm_cache_get($ckey)) !== false)
        return (array) $cached;

    $base = cm_get_offer_ids($term_id, $cg);
    if (count($base) <= 1)
        return $base;

    $scores = [];
    foreach ($base as $pid) {
        $m = cm_offer_metrics($pid, $term_id, $cg);
        $scores[$pid] = $m['pct'];
    }
    uasort($scores, function ($a, $b) {
        if ($a === $b)
            return 0;
        return ($a > $b) ? -1 : 1;
    });
    $sorted = array_map('intval', array_keys($scores));

    cm_cache_set($ckey, $sorted, CM_TTL_SORT);
    return $sorted;
}

/* ============================================================
 * Shortcode [ofertones]
 * ============================================================ */
add_shortcode('ofertones', function ($atts) {
    $term_id = !empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        ? (int) $_COOKIE['wcmlim_selected_location_termid'] : 0;
    if (!$term_id)
        return '';

    $atts = shortcode_atts([
        'columns' => 4,
        'total_products' => 16,
    ], $atts, 'ofertones');

    $per = max(1, (int) $atts['total_products']);
    $cg = (int) get_term_meta($term_id, 'customer_group', true);

    $count = count(cm_get_offer_ids($term_id, $cg));

    ob_start(); ?>
    <div class="cm-ofertones-toolbar" role="region" aria-label="Barra de Ofertones">
        <div class="cm-oft-title">
            <span class="cm-oft-badge" aria-hidden="true">
                <!-- ícono bolsa + % -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 7V6a5 5 0 0 1 10 0v1" stroke="currentColor" stroke-width="1.6" />
                    <rect x="4" y="7" width="16" height="13" rx="2.5" stroke="currentColor" stroke-width="1.6" />
                    <path d="M8 14l8-5M9 11h0M15 15h0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                Ofertones
            </span>
            <span class="cm-oft-count" aria-live="polite">
                <strong><?php echo esc_html($count); ?></strong> disponibles
            </span>
        </div>
        <label class="cm-oft-sort">
            <span class="cm-oft-sort-label">Ordenar</span>
            <div class="cm-oft-select-wrap">
                <select id="ofertones-sort" class="cm-oft-select" aria-label="Ordenar ofertones">
                    <option value="random" selected>Aleatorio</option>
                    <option value="best">Mejor ofertón (mayor %)</option>
                </select>
                <svg class="cm-oft-caret" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" />
                </svg>
            </div>
        </label>
    </div>
    <div id="product-list-ofertones" data-per-page="<?php echo esc_attr($per); ?>" style="margin-top: 30px;"
        data-columns="<?php echo (int) $atts['columns']; ?>" class="elementor-element carnemart-loop-productos">
        <div class="elementor-widget-container">
            <div class="woocommerce">
                <ul class="products elementor-grid columns-<?php echo (int) $atts['columns']; ?>"></ul>
            </div>
        </div>
    </div>
    <!-- Sentinel para IntersectionObserver -->
    <div id="ofertones-sentinel" style="width:1px;height:1px;"></div>
    <div id="ofertones-loading" style="display:block;">
        <span class="msg-consulta">Cargando ofertas…</span>
    </div>
    <?php
    return ob_get_clean();
});

add_action('wp_enqueue_scripts', function () {
    // Solo aplicar en la página /ofertones/
    if (is_page('ofertones') || is_page('ofertas')) {
        // usa cualquier handle ya registrado para inyectar estilos, por ejemplo el del tema
        $css = file_get_contents(__DIR__ . '/css/cm-ofertones-toolbar.css'); // si prefieres archivo
        wp_register_style('cm-ofertones-inline', false);
        wp_enqueue_style('cm-ofertones-inline');
        wp_add_inline_style('cm-ofertones-inline', $css);
    }
});

add_action('wp_footer', function () {
    if (!is_page())
        return; ?>
    <script>
        jQuery(function ($) {
            if (window.CM_OFERTONES_INIT) return; window.CM_OFERTONES_INIT = true;

            var container = $('#product-list-ofertones'),
                list = container.find('ul.products'),
                loadingDiv = $('#ofertones-loading'),
                loadingTxt = loadingDiv.find('.msg-consulta'),
                sentinel = $('#ofertones-sentinel'),
                perPage = parseInt(container.data('perPage'), 10) || 16,
                page = 1,
                sort = $('#ofertones-sort').val() || 'random',
                loading = false,
                finished = false,
                io = null,
                pendingAutoPulls = 0; // limite de autocargas para llenar viewport

            if (!container.length || !loadingDiv.length) return;

            function doFetch(p) {
                if (loading || finished) return;
                loading = true;
                loadingTxt.text(p === 1 ? 'Cargando ofertas…' : 'Cargando más ofertas…').show();

                $.post('<?php echo esc_js(admin_url("admin-ajax.php")); ?>', {
                    action: 'load_more_products_ofertones_fast',
                    page: p,
                    posts_per_page: perPage,
                    sort: sort
                })
                    .done(function (resp) {
                        resp = $.trim(resp);
                        if (resp) {
                            list.append(resp);
                            page = p + 1;
                            loading = false;
                            loadingTxt.hide();

                            // Si todavía no llenamos la pantalla, intenta autocargar hasta 3 veces
                            // (evita loops infinitos en páginas con muy pocos productos)
                            if (document.documentElement.scrollHeight <= window.innerHeight + 120 && !finished) {
                                if (pendingAutoPulls < 3) {
                                    pendingAutoPulls++;
                                    doFetch(page);
                                }
                            }
                        } else {
                            finished = true;
                            loading = false;
                            loadingTxt.text(p === 1 ? 'No hay ofertas para esta tienda.' : 'No hay más ofertas.').show();
                            disconnectIO();
                        }
                    })
                    .fail(function () {
                        loading = false;
                        loadingTxt.text('Error al cargar.').show();
                    });
            }

            function connectIO() {
                if (!sentinel.length) return;
                disconnectIO();
                try {
                    io = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                doFetch(page);
                            }
                        });
                    }, {
                        root: null,
                        rootMargin: '600px 0px', // dispara “antes” de llegar al fondo
                        threshold: 0
                    });
                    io.observe(sentinel[0]);
                } catch (e) {
                    // Si falla IO por alguna razón, activa el fallback por scroll
                    enableScrollFallback();
                }
            }

            function disconnectIO() {
                if (io) { io.disconnect(); io = null; }
            }

            // Fallback por scroll/resize (debounced)
            var scrollTick = null;
            function onScrollFallback() {
                if (loading || finished) return;
                if (scrollTick) return;
                scrollTick = requestAnimationFrame(function () {
                    scrollTick = null;
                    // Cerca del fondo -> cargar
                    var pos = window.scrollY + window.innerHeight;
                    var doc = document.documentElement.scrollHeight;
                    if (doc - pos < 600) {
                        doFetch(page);
                    }
                });
            }
            function enableScrollFallback() {
                window.addEventListener('scroll', onScrollFallback, { passive: true });
                window.addEventListener('resize', onScrollFallback);
            }

            // Inicial
            doFetch(1);
            connectIO();
            enableScrollFallback(); // lo dejamos activo como “red de seguridad”

            // Cambio de orden
            $(document).on('change', '#ofertones-sort', function () {
                sort = $(this).val() || 'random';
                page = 1;
                finished = false;
                loading = false;
                pendingAutoPulls = 0;
                list.empty();
                loadingTxt.text('Cargando ofertas…').show();
                doFetch(1);
                connectIO();
            });

        });
    </script>
<?php });

/* ============================================================
 * AJAX: usa la misma fuente (con caché)
 * ============================================================ */
add_action('wp_ajax_load_more_products_ofertones_fast', 'cm_ajax_more_ofertones');
add_action('wp_ajax_nopriv_load_more_products_ofertones_fast', 'cm_ajax_more_ofertones');

function cm_ajax_more_ofertones()
{
    $page = max(1, (int) ($_POST['page'] ?? 1));
    $per = max(1, (int) ($_POST['posts_per_page'] ?? 16));
    $sort = sanitize_key($_POST['sort'] ?? 'random'); // 'random' | 'best'

    $term_id = !empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        ? (int) $_COOKIE['wcmlim_selected_location_termid'] : 0;
    if (!$term_id)
        wp_die();

    $cg = (int) get_term_meta($term_id, 'customer_group', true);

    // (Opcional) caché de HTML por página
    $hkey = "cm_html_t{$term_id}_g{$cg}_p{$page}_per{$per}_{$sort}";
    if (CM_OFERTONES_CACHE) {
        $h = cm_cache_get($hkey);
        if ($h !== false) {
            echo $h;
            wp_die();
        }
    }

    $ids = cm_sorted_offer_ids($term_id, $cg, $sort);
    $offset = ($page - 1) * $per;
    $slice = array_slice($ids, $offset, $per);

    ob_start();
    if ($slice) {
        update_meta_cache('post', $slice); // precarga en memoria
        foreach ($slice as $pid) {
            $GLOBALS['post'] = get_post($pid);
            setup_postdata($GLOBALS['post']);
            wc_get_template_part('content', 'product');
        }
        wp_reset_postdata();
    }
    $html = ob_get_clean();

    if (CM_OFERTONES_CACHE && $html !== '')
        cm_cache_set($hkey, $html, CM_TTL_HTML);

    echo $html;
    wp_die();
}

/* ============================================================
 * Limpieza de caché (solo administradores)
 * - Botón en Herramientas > Ofertones – Caché
 * - URL de emergencia: ?cm_ofertones_clear=1
 * ============================================================ */
add_action('admin_menu', function () {
    add_management_page(
        'Ofertones – Caché',
        'Ofertones – Caché',
        'manage_options',
        'cm-ofertones-cache',
        'cm_ofertones_cache_page'
    );
});

function cm_ofertones_cache_page()
{
    if (!current_user_can('manage_options'))
        return;

    if (isset($_POST['cm_ofertones_clear_cache']) && check_admin_referer('cm_ofertones_clear_cache')) {
        cm_cache_del_like('cm_ids_');
        cm_cache_del_like('cm_html_');
        echo '<div class="updated"><p>Caché de Ofertones limpiada.</p></div>';
    } ?>
    <div class="wrap">
        <h1>Ofertones – Caché</h1>
        <p>La caché acelera el cálculo de productos en oferta y el render de páginas. TTL actual: 1 hora.</p>
        <form method="post">
            <?php wp_nonce_field('cm_ofertones_clear_cache'); ?>
            <p><input type="submit" name="cm_ofertones_clear_cache" class="button button-primary"
                    value="Limpiar caché ahora"></p>
        </form>
        <p><em>Emergencia:</em> como administrador puedes abrir cualquier página con <code>?cm_ofertones_clear=1</code> para
            limpiar.</p>
    </div>
<?php }

add_action('init', function () {
    if (!is_user_logged_in() || !current_user_can('manage_options'))
        return;
    if (!isset($_GET['cm_ofertones_clear']))
        return;

    cm_cache_del_like('cm_ids_');
    cm_cache_del_like('cm_html_');
    cm_log('Caché limpiada desde query string');
});

/* ============================================================
 * (Opcional) Invalidación simple cuando se guarda un producto
 * ============================================================ */
add_action('save_post_product', function () {
    cm_cache_del_like('cm_ids_');
    cm_cache_del_like('cm_html_');
});