<?php
/**
 * Plugin Name: Bafar :: Ofertas (Export JSON Admin)
 * Description: WooCommerce → Ofertas: exporta JSON por term_id con lógica de OFERTÓN basada en el primer tier. Toda la salida numérica es string.
 * Version:     1.2.0
 * Author:      Sparklabs
 */

if (!defined('ABSPATH'))
    exit;

/* ==========================
 * Helpers de conversión (salida SIEMPRE string)
 * ========================== */

/** Convierte cualquier número a string legible (sin notación científica, sin ceros basura). */
function bof_num_str($v): string
{
    if (is_string($v))
        return $v;
    if (is_int($v))
        return (string) $v;
    if (is_float($v)) {
        // hasta 12 decimales, sin ceros ni punto final si no hace falta
        $s = sprintf('%.12F', $v);
        $s = rtrim($s, '0');
        $s = rtrim($s, '.');
        if ($s === '')
            $s = '0';
        return $s;
    }
    // null / bool / otros
    return (string) $v;
}

/** Para cálculos internos: float "mejor esfuerzo" a partir de string/number. */
function bof_to_float($v): float
{
    if ($v === null || $v === '')
        return 0.0;
    if (is_numeric($v))
        return (float) $v;
    // normaliza coma -> punto, quita espacios
    $v = str_replace([' ', ','], ['', '.'], (string) $v);
    return (float) $v;
}

/* ==========================
 * Paso/step del producto (mejor esfuerzo)
 * ========================== */
function bof_product_step_raw(int $product_id): ?string
{
    // Devuelve el texto crudo del meta si existe; si no, null
    $keys = ['product_step', 'min_quantity', 'wc_b2b_qty_step', 'quantity_step', 'qty_step', 'eib2bpro_step', '_step', 'step'];
    foreach ($keys as $k) {
        $val = get_post_meta($product_id, $k, true);
        if ($val !== '' && $val !== null)
            return (string) $val;
    }
    return null;
}
function bof_product_step_float(int $product_id): float
{
    $raw = bof_product_step_raw($product_id);
    return ($raw !== null) ? bof_to_float($raw) : 1.0;
}

/* ==========================
 * Precios por tienda
 * ========================== */
function bof_regular_price_for_store_raw(int $product_id, int $term_id): ?string
{
    // devuelvo el texto del meta override si existe; si no, el _regular_price (texto)
    $override = get_post_meta($product_id, "wcmlim_regular_price_at_{$term_id}", true);
    if ($override !== '' && $override !== null)
        return (string) $override;
    $regular = get_post_meta($product_id, '_regular_price', true);
    if ($regular !== '' && $regular !== null)
        return (string) $regular;
    return null;
}
function bof_regular_price_for_store_float(int $product_id, int $term_id): float
{
    $raw = bof_regular_price_for_store_raw($product_id, $term_id);
    return ($raw !== null) ? bof_to_float($raw) : 0.0;
}

/* ==========================
 * Parseo de tiers del meta eib2bpro_price_tiers_group_{cg}
 * - Mantiene orden original (primer tier = base)
 * - Guarda price también como string "tal cual"
 * ========================== */
function bof_parse_tiers(int $product_id, int $cg): array
{
    $tiers_json = get_post_meta($product_id, "eib2bpro_price_tiers_group_{$cg}", true);
    if (!$tiers_json)
        return [];
    $raw = json_decode($tiers_json, true);
    if (!is_array($raw))
        return [];

    $parsed = [];
    foreach ($raw as $label => $price) {
        $label_str = (string) $label;
        $price_str = bof_num_str($price); // asegura string estable aunque venga como número

        $step = null;            // string del step encontrado en label (si hay)
        $step_f = null;          // float del step para comparaciones
        $regular_hint = null;    // string del (XX.YY) si hay
        $regular_hint_f = null;  // float para comparaciones

        // Ej: "1.00 (78.9) 🔥 ¡OFERTÓN! ..."  o  "10.00 PRECIO REGULAR"
        if (preg_match('/^\s*([0-9]+(?:[.,][0-9]+)?)\s*(?:\((\d+(?:[.,]\d+)?)\))?/u', $label_str, $m)) {
            $step = $m[1];
            $step_f = bof_to_float($step);
            if (!empty($m[2])) {
                $regular_hint = $m[2];
                $regular_hint_f = bof_to_float($regular_hint);
            }
        }

        $parsed[] = [
            'label' => $label_str,
            'step' => $step,              // string o null
            'step_f' => $step_f,            // float o null
            'price_raw' => $price_str,         // string para salida
            'price_f' => bof_to_float($price_str), // float para comparar
            'regular_hint' => $regular_hint,      // string o null
            'regular_hint_f' => $regular_hint_f     // float o null
        ];
    }
    return $parsed; // orden preservado
}

/* ==========================
 * Métricas de OFERTÓN basadas en el PRIMER tier
 * Salida: todo en strings, además de info para cálculos internos
 * Regresa:
 *  - has (bool), prices (regular, sale, discount_abs, discount_pct) como strings
 *  - steps: product_step, first_tier_step (strings)
 *  - tiers_filtered: map string step => string price (solo steps > product_step)
 * ========================== */
function bof_oferton_metrics(int $product_id, int $term_id, int $cg): array
{
    $tiers = bof_parse_tiers($product_id, $cg);
    if (!$tiers)
        return ['has' => false];

    $first = $tiers[0];

    // step del producto
    $product_step_raw = bof_product_step_raw($product_id);
    $product_step_f = bof_product_step_float($product_id);
    $product_step_str = ($product_step_raw !== null) ? (string) $product_step_raw : bof_num_str($product_step_f);

    // step del primer tier
    $first_step_str = $first['step'] ?? null;
    $first_step_f = $first['step_f'] ?? $product_step_f; // si no hay, suponemos aplicable

    // precios: sale = valor del primer tier (string); regular prioriza hint del label
    $sale_str = $first['price_raw'];                // string
    $sale_f = $first['price_f'];                  // float

    if ($first['regular_hint'] !== null) {
        $regular_str = (string) $first['regular_hint'];         // tal cual de la etiqueta
        $regular_f = (float) $first['regular_hint_f'];
    } else {
        $regular_raw = bof_regular_price_for_store_raw($product_id, $term_id);
        $regular_str = ($regular_raw !== null) ? (string) $regular_raw : '0';
        $regular_f = ($regular_raw !== null) ? bof_to_float($regular_raw) : 0.0;
    }

    // condición de OFERTÓN: primer step <= step del producto y sale < regular
    $step_ok = ($first_step_f <= $product_step_f);
    $has = ($step_ok && ($sale_f < $regular_f));

    // tiers adicionales: SOLO con step > step del producto
    $tiers_filtered = [];
    foreach ($tiers as $t) {
        if ($t['step'] === null)
            continue;
        if ($t['step_f'] > $product_step_f) {
            // key con 2 decimales para consistencia visual (pero string)
            $key = number_format($t['step_f'], 2, '.', '');
            $tiers_filtered[$key] = (string) $t['price_raw']; // string tal cual
        }
    }

    // descuentos (como string); si no hay oferta, pone "0" / "0.0…"
    $abs_f = $has ? max(0.0, $regular_f - $sale_f) : 0.0;
    $pct_f = ($has && $regular_f > 0) ? ($abs_f / $regular_f) : 0.0;

    $abs_str = bof_num_str($abs_f);               // "3" / "3.5" / "3.00" (sin ceros sobrantes)
    $pct_str = bof_num_str($pct_f);               // "0.0380228137" etc.

    return [
        'has' => $has,
        'prices' => [
            'regular' => (string) $regular_str,
            'sale' => (string) $sale_str,
            'discount_abs' => (string) $abs_str,
            'discount_pct' => (string) $pct_str,
        ],
        'steps' => [
            'product_step' => (string) $product_step_str,
            'first_tier_step' => ($first_step_str !== null) ? (string) $first_step_str : '',
        ],
        'tiers_filtered' => $tiers_filtered,
    ];
}

/* ==========================
 * IDs candidatos por tienda/grupo en chunks (stock>0 y tienen tiers)
 * ========================== */
function bof_candidate_ids_chunk(int $term_id, int $cg, int $limit, int $offset): array
{
    global $wpdb;
    $meta_stock = "wcmlim_stock_at_{$term_id}";
    $tiers_key = "eib2bpro_price_tiers_group_{$cg}";

    $sql = $wpdb->prepare("
        SELECT DISTINCT p.ID
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} msp
          ON (msp.post_id=p.ID AND msp.meta_key='_stock_status' AND msp.meta_value='instock')
        INNER JOIN {$wpdb->postmeta} mst
          ON (mst.post_id=p.ID AND mst.meta_key=%s AND CAST(mst.meta_value AS SIGNED) > 0)
        INNER JOIN {$wpdb->postmeta} mtg
          ON (mtg.post_id=p.ID AND mtg.meta_key=%s AND TRIM(mtg.meta_value) <> '')
        WHERE p.post_type='product' AND p.post_status='publish'
        LIMIT %d OFFSET %d
    ", $meta_stock, $tiers_key, $limit, $offset);

    $prev = wp_suspend_cache_addition(true);
    $ids = array_map('intval', $wpdb->get_col($sql));
    wp_suspend_cache_addition($prev);
    return $ids;
}

/* ==========================
 * Exportador streaming → uploads/ofertones/ofertas_term_{term}.json
 * ========================== */
function bof_export_offers_json(int $term_id): array
{
    $cg = (int) get_term_meta($term_id, 'customer_group', true);

    $uploads = wp_upload_dir();
    $dir = trailingslashit($uploads['basedir']) . 'ofertones';
    if (!wp_mkdir_p($dir))
        return ['ok' => false, 'msg' => 'No se pudo crear el directorio destino'];

    $filename = "ofertas_term_{$term_id}.json";
    $path = trailingslashit($dir) . $filename;
    $url = trailingslashit($uploads['baseurl']) . 'ofertones/' . $filename;

    $tmp = $path . '.tmp';
    $fh = @fopen($tmp, 'wb');
    if (!$fh)
        return ['ok' => false, 'msg' => 'No se pudo abrir archivo temporal para escritura'];

    fwrite($fh, "{\n");
    fwrite($fh, '  "term_id": "' . bof_num_str($term_id) . "\",\n");
    fwrite($fh, '  "customer_group": "' . bof_num_str($cg) . "\",\n");
    fwrite($fh, '  "generated_at": "' . gmdate('c') . "\",\n");
    fwrite($fh, '  "items": [' . "\n");

    $chunk = 200;
    $offset = 0;
    $written = 0;
    $first = true;
    $prev = wp_suspend_cache_addition(true);
    @set_time_limit(300);

    do {
        $ids = bof_candidate_ids_chunk($term_id, $cg, $chunk, $offset);
        if (!$ids)
            break;

        foreach ($ids as $pid) {
            $m = bof_oferton_metrics($pid, $term_id, $cg);
            if (empty($m['has']))
                continue; // solo OFERTÓN válido

            $post = get_post($pid);
            if (!$post)
                continue;

            // metadatos en strings
            $sku = (string) get_post_meta($pid, '_sku', true);
            $slug = $post->post_name;
            $name = get_the_title($pid);
            $permalink = get_permalink($pid);
            $image_id = get_post_thumbnail_id($pid);
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';

            $stock_at_raw = get_post_meta($pid, "wcmlim_stock_at_{$term_id}", true);
            $status = (string) get_post_meta($pid, '_stock_status', true);

            $row = [
                'id' => bof_num_str($pid),
                'sku' => $sku,
                'slug' => $slug,
                'name' => $name,
                'permalink' => $permalink,
                'image' => $image_url,
                'store_term_id' => bof_num_str($term_id),
                'customer_group' => bof_num_str($cg),
                'prices' => [
                    'regular' => (string) $m['prices']['regular'],
                    'sale' => (string) $m['prices']['sale'],
                    'discount_abs' => (string) $m['prices']['discount_abs'],
                    'discount_pct' => (string) $m['prices']['discount_pct'],
                ],
                'stock' => [
                    'status' => $status,
                    'at_store' => bof_num_str($stock_at_raw), // como string
                ],
                'tiers' => $m['tiers_filtered'],       // map "10.00" => "70.00"
                'steps' => [
                    'product_step' => (string) $m['steps']['product_step'],
                    'first_tier_step' => (string) $m['steps']['first_tier_step'],
                ],
            ];

            $json = wp_json_encode($row, JSON_UNESCAPED_UNICODE);
            if (!$first)
                fwrite($fh, ",\n");
            fwrite($fh, '    ' . $json);
            $first = false;

            $written++;
        }

        $offset += $chunk;
    } while (count($ids) === $chunk);

    wp_suspend_cache_addition($prev);

    fwrite($fh, "\n  ]\n}\n");
    fclose($fh);
    @rename($tmp, $path);

    return ['ok' => true, 'msg' => "Exportados {$written} ítems con OFERTÓN", 'path' => $path, 'url' => $url, 'count' => $written];
}

/* ==========================
 * Admin: WooCommerce → Ofertas
 * ========================== */
add_action('admin_menu', function () {
    add_submenu_page(
        'woocommerce',
        'Ofertas (Export JSON)',
        'Ofertas',
        'manage_woocommerce',
        'bafar-ofertas-export',
        'bafar_ofertas_export_page'
    );
});

function bafar_ofertas_export_page()
{
    if (!current_user_can('manage_woocommerce'))
        return;

    if (!empty($_GET['bof_done'])) {
        $msg = sanitize_text_field($_GET['bof_msg'] ?? '');
        $url = esc_url_raw($_GET['bof_url'] ?? '');
        $cnt = intval($_GET['bof_cnt'] ?? 0);
        echo '<div class="notice notice-success"><p><strong>¡Listo!</strong> ' . esc_html($msg) . '. ';
        if ($url)
            echo 'Descargar: <a class="button button-primary" target="_blank" href="' . esc_url($url) . '">Abrir JSON</a>';
        echo ' <span style="opacity:.7">(' . $cnt . ' productos)</span></p></div>';
    }
    if (!empty($_GET['bof_err'])) {
        $msg = sanitize_text_field($_GET['bof_err']);
        echo '<div class="notice notice-error"><p><strong>Error:</strong> ' . esc_html($msg) . '</p></div>';
    } ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Exportar Ofertas a JSON</h1>
        <p>El archivo se guarda en <code>wp-content/uploads/ofertones/</code>.</p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('bof_export'); ?>
            <input type="hidden" name="action" value="bof_export_json">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="bof_term_id">term_id de la tienda</label></th>
                    <td>
                        <input name="term_id" id="bof_term_id" type="number" min="1" step="1" class="regular-text" required>
                        <p class="description">Se obtiene el <code>customer_group</code> desde la metainfo del término.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Cargar ofertas (exportar JSON)'); ?>
        </form>

        <hr />
        <p><strong>Salida por producto (ejemplo):</strong></p>
        <pre style="background:#f6f7f7;padding:12px;border:1px solid #ccd0d4;overflow:auto">
                                                                                        {
                                                                                          "id": "78",
                                                                                          "sku": "504",
                                                                                          "slug": "pierna-sin-hueso-frontera-de-cerdo-reb",
                                                                                          "name": "Pierna Sin Hueso Frontera De Cerdo Reb",
                                                                                          "permalink": "https://…/product/…/",
                                                                                          "image": "https://…/imagen.jpg",
                                                                                          "store_term_id": "264",
                                                                                          "customer_group": "1837",
                                                                                          "prices": {
                                                                                            "regular": "78.90",
                                                                                            "sale": "75.90",
                                                                                            "discount_abs": "3",
                                                                                            "discount_pct": "0.0380228137"
                                                                                          },
                                                                                          "stock": { "status": "instock", "at_store": "2610" },
                                                                                          "tiers": { "10.00": "70.00" },      // solo steps > step del producto
                                                                                          "steps": { "product_step": "1.00", "first_tier_step": "1.00" }
                                                                                        }
                                                                                        </pre>
    </div>
<?php }

/* ==========================
 * Acción que procesa el export
 * ========================== */
add_action('admin_post_bof_export_json', function () {
    if (!current_user_can('manage_woocommerce'))
        wp_die('No autorizado', 403);
    check_admin_referer('bof_export');

    $term_id = isset($_POST['term_id']) ? (int) $_POST['term_id'] : 0;
    if ($term_id <= 0) {
        wp_safe_redirect(add_query_arg('bof_err', rawurlencode('term_id inválido'), wp_get_referer()));
        exit;
    }

    $res = bof_export_offers_json($term_id);
    if ($res['ok']) {
        $args = [
            'bof_done' => 1,
            'bof_msg' => rawurlencode($res['msg']),
            'bof_url' => rawurlencode($res['url']),
            'bof_cnt' => (int) $res['count'],
        ];
        wp_safe_redirect(add_query_arg($args, wp_get_referer()));
    } else {
        wp_safe_redirect(add_query_arg('bof_err', rawurlencode($res['msg']), wp_get_referer()));
    }
    exit;
});

/**
 * Front-end CSS para Ofertones
 */
add_action('wp_enqueue_scripts', function () {
    // 1) ¿Debemos cargar? (página por slug o porque contiene el shortcode)
    $should = false;

    if (is_page(array('ofertas', 'ofertones'))) {
        $should = true;
    } else if (is_singular()) {
        global $post;
        if ($post && has_shortcode($post->post_content, 'ofertones')) {
            $should = true;
        }
    }

    if (!$should)
        return;

    // 2) Encolar el CSS del plugin (con versión por mtime)
    $css_rel_path = 'css/cm-ofertones-toolbar.css';
    $css_file = __DIR__ . '/' . $css_rel_path;
    $css_url = plugins_url($css_rel_path, __FILE__);
    $ver = file_exists($css_file) ? filemtime($css_file) : null;

    wp_enqueue_style('cm-ofertones', $css_url, array(), $ver);
}, 20);

/* ============================================================
 * Helpers seguros
 * ============================================================ */
function cm_oft_num_to_float($s)
{
    return is_numeric($s) ? (float) $s : (float) str_replace([',', ' '], ['.', ''], (string) $s);
}
function cm_oft_price_html($amount)
{
    // Formatea con Woo si está disponible; si no, simple.
    if (function_exists('wc_price'))
        return wc_price(cm_oft_num_to_float($amount));
    return '<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . esc_html(number_format(cm_oft_num_to_float($amount), 2)) . '</bdi></span>';
}

/** Render del bloque de tiers (solo si hay) */
function cm_oft_render_tiers_table($product_id, $tiers_map)
{
    if (empty($tiers_map) || !is_array($tiers_map))
        return '';
    ob_start(); ?>
    <style>
        .bafar-tiers {
            margin: .25rem 0 .5rem
        }

        .bafar-tiers>summary {
            cursor: pointer;
            color: #0866FD;
            font-weight: 600;
            list-style: none;
            outline: none
        }

        .bafar-tiers>summary::-webkit-details-marker {
            display: none
        }

        .bafar-tiers-table {
            width: 100%;
            margin-top: .35rem;
            border-collapse: collapse;
            font-size: .9rem
        }

        .bafar-tiers-table td {
            padding: .15rem 0
        }

        .bafar-tiers-table td.qty {
            color: #555
        }

        .bafar-tiers-table td.price {
            text-align: right;
            font-weight: 600
        }

        .bafar-tiers-bullet {
            display: inline-block;
            width: .45rem;
            height: .45rem;
            border-radius: 50%;
            background: #0866FD;
            margin-right: .35rem;
            vertical-align: middle
        }
    </style>
    <details class="bafar-tiers" id="tiers-<?php echo esc_attr($product_id); ?>">
        <summary>Ver precios por volumen</summary>
        <table class="bafar-tiers-table" aria-describedby="tiers-<?php echo esc_attr($product_id); ?>">
            <tbody>
                <?php foreach ($tiers_map as $stepStr => $priceStr): ?>
                    <tr>
                        <td class="qty">
                            <span class="bafar-tiers-bullet"></span>
                            desde <?php echo esc_html($stepStr); ?> Pza.
                            <div style="color:#999;font-size:.8rem;margin-top:2px;">Precio regular</div>
                        </td>
                        <td class="price"><?php echo cm_oft_price_html($priceStr); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </details>
    <?php
    return ob_get_clean();
}


/** Builder del <li> de producto desde el item del JSON */
function cm_oft_render_product_li(array $it, int $term_id, string $selected_location_name)
{
    $pid = isset($it['id']) ? (string) $it['id'] : '';
    $sku = isset($it['sku']) ? (string) $it['sku'] : '';
    $slug = isset($it['slug']) ? (string) $it['slug'] : '';
    $name = isset($it['name']) ? (string) $it['name'] : '';
    $link = isset($it['permalink']) ? (string) $it['permalink'] : '#';
    $img = !empty($it['image']) ? (string) $it['image'] : (function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src() : '');
    $pr = $it['prices'] ?? [];
    $regular = isset($pr['regular']) ? (string) $pr['regular'] : '';
    $sale = isset($pr['sale']) ? (string) $pr['sale'] : '';
    $discPct = isset($pr['discount_pct']) ? (string) $pr['discount_pct'] : '0';
    $stock = $it['stock'] ?? [];
    $status = isset($stock['status']) ? (string) $stock['status'] : 'instock';
    $qty_at = isset($stock['at_store']) ? (string) $stock['at_store'] : '0';
    $tiers = $it['tiers'] ?? [];
    $steps = $it['steps'] ?? [];
    $product_step_str = !empty($steps['product_step']) ? (string) $steps['product_step'] : 1;
    $product_step_f = cm_oft_num_to_float($product_step_str);
    if ($product_step_f <= 0) {
        $product_step_f = 1.0;
        $product_step_str = 1;
    }

    $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : site_url('/cart/');
    $is_offer = (cm_oft_num_to_float($sale) > 0 && cm_oft_num_to_float($regular) > cm_oft_num_to_float($sale));

    // clases mínimas compatibles con Woo
    $classes = [
        'product',
        'type-product',
        "post-$pid",
        'status-publish',
        $status,
        'has-post-thumbnail',
        'shipping-taxable',
        'purchasable',
        'product-type-simple'
    ];
    ob_start(); ?>
    <li class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-discount="<?php echo esc_attr($discPct); ?>">
        <a href="<?php echo esc_url($link); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
            <?php if ($img): ?>
                <img src="<?php echo esc_url($img); ?>" class="wp-post-image wp-post-image" alt="" loading="lazy"
                    decoding="async" />
            <?php endif; ?>
            <h2 class="woocommerce-loop-product__title"><?php echo esc_html($name); ?></h2>
            <div class="flag-producto-card"></div>

            <span class="price">
                <span class="woocommerce-Price-amount amount">
                    <bdi>
                        <?php if ($is_offer): ?>
                            <del style="color:#888;margin-right:.5em;"><?php echo cm_oft_price_html($regular); ?></del>
                            <ins
                                style="color:#0866FD;text-decoration:none;font-weight:600;"><?php echo cm_oft_price_html($sale); ?></ins>
                        <?php else: ?>
                            <?php echo cm_oft_price_html($regular ?: $sale); ?>
                        <?php endif; ?>
                        <span class="por-label" style="color:#6c757d;font-size:15px;">por <b>Pza.</b></span>
                    </bdi>
                </span>
            </span>

            <?php echo cm_oft_render_tiers_table((int) $pid, $tiers); ?>
        </a>

        <div class="quantity-wrapper">
            <button class="quantity-decrease">-</button>
            <input type="number" class="quantity-input" value="<?php echo esc_attr($product_step_str); ?>"
                min="<?php echo esc_attr($product_step_str); ?>" step="<?php echo esc_attr($product_step_str); ?>"
                aria-label="Cantidad">
            <button class="quantity-increase">+</button>

            <button class="button product_type_simple add_to_cart_button wcmlim_ajax_add_to_cart add-to-cart"
                style="color:#fff" data-cart-url="<?php echo esc_url($cart_url); ?>" data-isredirect="no"
                data-quantity="<?php echo esc_attr($product_step_str); ?>" data-product_id="<?php echo esc_attr($pid); ?>"
                data-product_sku="<?php echo esc_attr($sku); ?>"
                aria-label="<?php echo esc_attr('Add “' . $name . '” to your cart'); ?>"
                data-selected_location="<?php echo esc_attr($selected_location_name); ?>"
                data-location_key="<?php echo esc_attr(get_term_meta($term_id, 'location_key', true) ?: (string) $term_id); ?>"
                data-location_qty="<?php echo esc_attr($qty_at); ?>"
                data-location_termid="<?php echo esc_attr($term_id); ?>"
                data-product_price="<?php echo esc_attr($sale ?: $regular); ?>"
                data-location_sale_price="<?php echo esc_attr($sale); ?>"
                data-location_regular_price="<?php echo esc_attr($regular); ?>"
                data-product_backorder="<?php echo $status === 'instock' ? '' : 'yes'; ?>" rel="nofollow">Añadir al
                carrito</button>
        </div>
    </li>
    <?php
    return ob_get_clean();
}


/* ============================================================
 * Shortcode [ofertones]
 * Render directo desde JSON + ordenamiento client-side
 * ============================================================ */
add_shortcode('ofertones', function ($atts) {
    $term_id = !empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        ? (int) $_COOKIE['wcmlim_selected_location_termid'] : 0;
    if (!$term_id)
        return '<p class="cm-ofertones-error">Por favor, selecciona una ubicación para ver los ofertones.</p>';

    $atts = shortcode_atts([
        'columns' => 4,
        'total_products' => -1, // -1 = todos
    ], $atts, 'ofertones');

    $per = (int) $atts['total_products'];
    $cols = (int) $atts['columns'];

    // JSON de ofertas
    $offers_file = WP_CONTENT_DIR . '/uploads/ofertones/ofertas_term_' . $term_id . '.json';
    if (!file_exists($offers_file))
        return '<p class="cm-ofertones-error">Lo sentimos, no hay ofertas para esta ubicación.</p>';

    $data = json_decode(file_get_contents($offers_file), true);
    $items = is_array($data['items'] ?? null) ? $data['items'] : [];
    $count = count($items);

    // Limitar si se pidió per-page
    if ($per > 0)
        $items = array_slice($items, 0, $per);

    $term = get_term($term_id);
    $loc_name = $term && !is_wp_error($term) ? $term->name : ('Tienda ' . $term_id);

    ob_start(); ?>
    <div class="cm-ofertones-toolbar" role="region" aria-label="Barra de Ofertones">
        <div class="cm-oft-title">
            <span class="cm-oft-badge" aria-hidden="true">
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

    <div id="product-list-ofertones" class="elementor-element carnemart-loop-productos"
        data-columns="<?php echo (int) $cols; ?>">
        <div class="elementor-widget-container">
            <div class="woocommerce">
                <ul id="cm-oft-ul" class="products elementor-grid columns-<?php echo (int) $cols; ?>">
                    <?php
                    foreach ($items as $it) {
                        echo cm_oft_render_product_li($it, $term_id, $loc_name);
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        (function () {
            // Ordenamiento client-side
            var select = document.getElementById('ofertones-sort');
            var ul = document.getElementById('cm-oft-ul');
            if (!select || !ul) return;

            function sortRandom(a, b) { return Math.random() - 0.5; }
            function sortBest(a, b) {
                var da = parseFloat(a.getAttribute('data-discount') || '0');
                var db = parseFloat(b.getAttribute('data-discount') || '0');
                return db - da; // mayor primero
            }

            function resort() {
                var lis = Array.prototype.slice.call(ul.children);
                if (select.value === 'best') lis.sort(sortBest);
                else lis.sort(sortRandom);
                lis.forEach(function (li) { ul.appendChild(li); });
            }

            select.addEventListener('change', resort, false);
        })();
    </script>
    <?php
    return ob_get_clean();
});