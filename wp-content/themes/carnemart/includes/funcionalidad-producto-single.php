<?php

// Funcionalidad de scripts de Woocommerce
add_action('after_setup_theme', function () {
    add_theme_support('wc-product-gallery-zoom');       // Habilitar el zoom
    add_theme_support('wc-product-gallery-lightbox');   // Habilitar el lightbox (abrir imágenes en modal)
    add_theme_support('wc-product-gallery-slider');     // Habilitar el slider/carousel
});

add_action('wp_enqueue_scripts', function () {
    if (is_product()) { // Solo cargar en páginas de producto
        wp_enqueue_script('wc-single-product'); // Scripts necesarios para la galería
    }
});


// Cambiar oferta por porcentaje
add_filter('woocommerce_sale_flash', function ($html, $post, $product) {
    if ($product->is_on_sale()) {
        $regular_price = floatval($product->get_regular_price());
        $sale_price = floatval($product->get_sale_price());

        if ($regular_price && $sale_price) {
            $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            $html = '<span class="onsale">-' . esc_html($percentage) . '%</span>';
        }
    }

    return $html;
}, 10, 3);

// Mostrar productos relacionados Shortcode
add_shortcode('productos_relacionados', function ($atts) {
    global $product, $wpdb;

    if (!is_product() || !$product) {
        return '';
    }

    $atts = shortcode_atts(array(
        'posts_per_page' => 4,
        'columns' => 4,
    ), $atts, 'productos_relacionados');

    ob_start();

    remove_action('woocommerce_product_related_products_heading', 'woocommerce_related_products_heading', 10);

    echo '<h2 class="productos-relacionados-titulo has-verde-color"><b>Te recomendamos también</b></h2>';

    // Hook temporal SOLO dentro de este shortcode
    add_filter('woocommerce_related_products', function ($related_ids) use ($wpdb) {

        // Leer tienda desde cookie
        $store_termid = isset($_COOKIE['wcmlim_selected_location_termid'])
            ? preg_replace('/\D/', '', $_COOKIE['wcmlim_selected_location_termid'])
            : '';

        $filtered_ids = [];

        foreach ($related_ids as $pid) {
            if ($store_termid) {
                // Revisar stock por tienda
                $meta_key = "wcmlim_stock_at_{$store_termid}";
                $stock = $wpdb->get_var($wpdb->prepare(
                    "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
                    $pid,
                    $meta_key
                ));

                if ($stock !== null && floatval($stock) > 0) {
                    $filtered_ids[] = $pid;
                }
            } else {
                // Fallback: stock general WooCommerce
                $status = get_post_meta($pid, '_stock_status', true);
                if ($status === 'instock') {
                    $filtered_ids[] = $pid;
                }
            }
        }

        return $filtered_ids;
    }, 20);

    woocommerce_related_products(array(
        'posts_per_page' => $atts['posts_per_page'],
        'columns' => $atts['columns'],
    ));

    // Importante: remover hook después para no afectar a otros
    remove_all_filters('woocommerce_related_products');

    return ob_get_clean();
});

function enqueue_taxonomy_media_scripts()
{
    // Cargar los scripts necesarios para el cargador de medios
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'enqueue_taxonomy_media_scripts');