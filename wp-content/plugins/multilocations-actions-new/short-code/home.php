<?php
if (!function_exists('anaquel_home')) {
    function anaquel_home($atts)
    {

        // Atributos para el shortcode
        $atts = shortcode_atts(
            [
                'columns' => 4,
                'rows' => 3,
                'total_products' => 20, // Total de productos a mostrar
            ],
            $atts,
            'home_productos_sb'
        );

        // Detectar tienda seleccionada
        $term_id = (!empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined')
            ? intval($_COOKIE['wcmlim_selected_location_termid'])
            : 0;

        // Aviso si no hay tienda seleccionada
        if (!$term_id) {
            echo '<div class="cm-store-required" style="
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 12px;
                    padding: 20px;
                    margin-bottom: 24px;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
                    display: flex;
                    align-items: center;
                    gap: 16px;
                    color: white;
                    position: relative;
                    overflow: hidden;
                ">
                    <div class="cm-sr-icon" style="
                        font-size: 24px;
                        background: rgba(255, 255, 255, 0.2);
                        border-radius: 50%;
                        width: 48px;
                        height: 48px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        backdrop-filter: blur(10px);
                    " aria-hidden="true">📍</div>
                    <div class="cm-sr-text" style="flex: 1;">
                        <strong style="
                            display: block;
                            font-size: 18px;
                            font-weight: 600;
                            margin-bottom: 4px;
                        ">Selecciona tu tienda</strong>
                        <span style="
                            font-size: 14px;
                            opacity: 0.9;
                            line-height: 1.4;
                        ">Así verás precios, promociones y disponibilidad</span>
                    </div>
                    <div style="
                        position: absolute;
                        top: -50%;
                        right: -20px;
                        width: 100px;
                        height: 100px;
                        background: rgba(255, 255, 255, 0.1);
                        border-radius: 50%;
                        z-index: 0;
                    "></div>
                </div>';
            return '';
        }

        // Cache key única por tienda y configuración
        $cache_key = 'anaquel_home_' . md5(serialize($atts) . '_' . $term_id);
        $cache_expiration = 15 * MINUTE_IN_SECONDS;

        // Intentar obtener del cache
        $cached_result = get_transient($cache_key);
        if ($cached_result !== false) {
            return $cached_result;
        }

        $pool_multiplier = 2; // sube/baja si hace falta
        $pool_size = max(intval($atts['total_products']) * $pool_multiplier, 60);

        // Preparar los argumentos de consulta
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $pool_size,
            // ¡NO RAND! => orden barato y estable
            'orderby' => ['menu_order' => 'ASC', 'ID' => 'DESC'],
            'order' => 'DESC',

            'fields' => 'ids',         // solo IDs
            'no_found_rows' => true,          // sin paginación
            'cache_results' => true,          // usa object cache local
            'update_post_meta_cache' => false,         // false aquí (priming manual después)
            'update_post_term_cache' => false,

            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                ],
                [
                    'key' => 'product_step',
                    'value' => 0.1,
                    'type' => 'DECIMAL(10,2)',
                    'compare' => '>'
                ],
            ],
        ];

        if ($term_id) {
            // Stock por tienda
            $args['meta_query'][] = [
                'key' => "wcmlim_stock_at_{$term_id}",
                'value' => '0',
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
            // Precio regular por tienda > 0
            $args['meta_query'][] = [
                'key' => "wcmlim_regular_price_at_{$term_id}",
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
        } else {
            // Sin tienda: precio base > 0 y stock > 1
            $args['meta_query'][] = [
                'key' => '_regular_price',
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
            $args['meta_query'][] = [
                'key' => '_stock',
                'value' => '1',
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
        }

        // Ejecutar consulta
        $product_ids = get_posts($args);

        // Filtrar por product_step vía query directa (optimizada)
        $filtered = [];
        if (!empty($product_ids)) {
            global $wpdb;

            // Asegurar IDs enteros
            $safe_ids = array_map('intval', $product_ids);
            $ids_string = implode(',', $safe_ids);

            // Nota: %s solo para la meta_key, el IN() ya va saneado arriba.
            $meta_results = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT post_id, meta_value 
                    FROM {$wpdb->postmeta} 
                    WHERE post_id IN ($ids_string) 
                    AND meta_key = %s
                ", 'product_step')
            );

            // Lookup de product_step
            $step_meta = [];
            foreach ($meta_results as $result) {
                $step_meta[intval($result->post_id)] = $result->meta_value;
            }

            // Filtrado final
            foreach ($safe_ids as $product_id) {
                if (count($filtered) >= intval($atts['total_products'])) {
                    break;
                }
                $step = isset($step_meta[$product_id]) ? $step_meta[$product_id] : '';
                if (is_numeric($step) && floor($step) == $step && $step <= 1) {
                    $filtered[] = $product_id;
                }
            }
        }

        // Renderizar salida (usar buffer y luego RETURN)
        ob_start();

        if (!empty($filtered)) {
            // Pasar columnas al loop nativo
            wc_set_loop_prop('columns', intval($atts['columns']));

            echo '<div class="elementor-element elementor-element-5828161beto carnemart-loop-productos elementor-grid-mobile-2 elementor-grid-4 elementor-grid-tablet-3 elementor-products-grid elementor-wc-products elementor-widget elementor-widget-woocommerce-products"><div class="elementor-widget-container"><div class="woocommerce columns-' . esc_attr($atts['columns']) . '"><ul class="products elementor-grid columns-' . esc_attr($atts['columns']) . '">';

            foreach ($filtered as $product_id) {
                $post_object = get_post($product_id);
                if ($post_object) {
                    setup_postdata($GLOBALS['post'] = $post_object);
                    wc_get_template_part('content', 'product'); // item nativo
                }
            }

            echo '</ul></div></div></div>';
            wp_reset_postdata();
        } else {
            echo '<div class="msg-general"><span class="cu-info-circle"></span><span class="msg-text">No hay productos disponibles en este momento.</span></div>';
        }

        $output = ob_get_clean();

        // Guardar cache y RETURN (no echo)
        set_transient($cache_key, $output, $cache_expiration);
        return $output;
    }
}

// Evitar registrar el shortcode dos veces
if (!shortcode_exists('home_productos_sb')) {
    add_shortcode('home_productos_sb', 'anaquel_home');
}

// Limpiar cache cuando se actualicen productos o stock
add_action('woocommerce_product_set_stock', 'clear_anaquel_home_cache');
add_action('woocommerce_variation_set_stock', 'clear_anaquel_home_cache');
add_action('save_post', 'clear_anaquel_home_cache_on_product_save');

if (!function_exists('clear_anaquel_home_cache')) {
    function clear_anaquel_home_cache($product = null)
    {
        global $wpdb;
        // Borra transients de DB (funciona aunque no haya object cache)
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_anaquel_home_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_anaquel_home_%'");
    }
}

if (!function_exists('clear_anaquel_home_cache_on_product_save')) {
    function clear_anaquel_home_cache_on_product_save($post_id)
    {
        if (get_post_type($post_id) === 'product') {
            clear_anaquel_home_cache();
        }
    }
}