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
    global $product;

    // Asegurarnos de que estamos en una página de producto
    if (! is_product() || ! $product) {
        return '';
    }

    // Extraer atributos del shortcode
    $atts = shortcode_atts(array(
        'posts_per_page' => 4, // Número de productos relacionados a mostrar
        'columns'        => 4, // Número de columnas
    ), $atts, 'productos_relacionados');

    ob_start();

    // Eliminar el título predeterminado de WooCommerce
    remove_action('woocommerce_product_related_products_heading', 'woocommerce_related_products_heading', 10);

    // Personalizar el título
    echo '<h2 class="productos-relacionados-titulo has-verde-color "><b>Te recomendamos también</b></h2>';

    // Mostrar productos relacionados
    woocommerce_related_products(array(
        'posts_per_page' => $atts['posts_per_page'],
        'columns'        => $atts['columns'],
    ));

    return ob_get_clean();
});



function enqueue_taxonomy_media_scripts()
{
    // Cargar los scripts necesarios para el cargador de medios
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'enqueue_taxonomy_media_scripts');
