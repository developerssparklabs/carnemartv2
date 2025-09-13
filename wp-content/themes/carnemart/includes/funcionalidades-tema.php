<?php


// Agregar clase al body si la marquesina esta activa
add_filter('body_class', function ($classes) {
    // Verifica el valor del ACF
    if (get_field('mostrar_marquesina', 'option')) {
        // Agrega la clase personalizada
        $classes[] = 'marquesina-activa';
    }
    return $classes;
});



// Menú add class to links
function add_additional_class_on_a($classes, $item, $args)
{
    if (isset($args->add_a_class)) {
        $classes['class'] = $args->add_a_class;
    }
    return $classes;
}

add_filter('nav_menu_link_attributes', 'add_additional_class_on_a', 1, 3);



// Agregar bage a la card cuando no esta disponible el prouducto
// 1) Badge "Agotado" en cards (déjalo igual)
function agregar_etiqueta_agotado_en_cards()
{
    global $product;
    if ($product && ! $product->is_in_stock()) {
        echo '<span class="badge agotado">Agotado</span>';
    }
}
add_action('woocommerce_before_shop_loop_item_title', 'agregar_etiqueta_agotado_en_cards', 10);

// 2) Placeholder personalizado (déjalo igual)
function custom_woocommerce_placeholder_img_src($src)
{
    return get_stylesheet_directory_uri() . '/img/img-product.webp';
}
add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src');

// 3) Miniatura CORRECTA en el loop (esto reemplaza tu bloque que pedía 'full')
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if (! $product) return;

    $size  = 'woocommerce_thumbnail';
    $attrs = [
        'class'    => 'wp-post-image',
        'loading'  => 'lazy',
        'decoding' => 'async',
        // Ajusta a tu grid real (183px fue el target de Lighthouse)
        'sizes'    => '(max-width:480px) 45vw, (max-width:768px) 30vw, 183px',
    ];

    if (has_post_thumbnail($product->get_id())) {
        echo get_the_post_thumbnail($product->get_id(), $size, $attrs);
    } else {
        // Usa tu placeholder, con clases/tamaño del catálogo
        $src = wc_placeholder_img_src($size);
        printf(
            '<img src="%s" alt="%s" class="attachment-%1$s size-%1$s wp-post-image" loading="lazy" decoding="async" sizes="%s" />',
            esc_url($src),
            esc_attr(get_the_title($product->get_id())),
            esc_attr($attrs['sizes']),
            esc_attr($size)
        );
    }
}, 10);

// 4) (Recomendado) Asegura el tamaño del catálogo y que WC lo pida
add_filter('woocommerce_get_image_size_thumbnail', function () {
    return [
        'width'  => 366,  // 183*2 para pantallas 2x
        'height' => 366,
        'crop'   => 1,
    ];
}, 999);

add_filter('single_product_archive_thumbnail_size', function () {
    return 'woocommerce_thumbnail';
}, 999);



// 1) Forzar un 'sizes' más agresivo en mobile para el catálogo
add_filter('wp_calculate_image_sizes', function ($sizes, $size, $image_src, $image_meta, $attachment_id) {
    if (is_shop() || is_product_taxonomy() || is_post_type_archive('product')) {
        // mobile pide ~40vw, tablet ~25vw, desktop fijo 183px
        return '(max-width:480px) 40vw, (max-width:768px) 25vw, 183px';
    }
    return $sizes;
}, 10, 5);

// 2) (Opcional) En la primer card del loop, sube o baja prioridad de descarga
add_filter('get_post_thumbnail_id', function ($thumb_id) {
    // Nada que cambiar aquí; sólo recordatorio: si alguna vez quieres dar
    // fetchpriority="high" al primer producto del loop, se puede hacer con un contador global.
    return $thumb_id;
});



// Shortcode: listado de categorías del producto
function shortcode_listado_categorias()
{
    global $post;

    if (! $post || 'product' !== get_post_type($post)) {
        return ''; // solo aplica en productos
    }

    $terms = wp_get_post_terms($post->ID, 'product_cat');

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    ob_start(); ?>
    <p class="has-principal-color pb-0 mb-0"><strong>Producto de:</strong></p>
    <ul class="listado-categorias">
        <?php foreach ($terms as $term) : ?>
            <li class="listado__item">
                <a href="<?php echo esc_url(get_term_link($term)); ?>" class="listado__link">
                    <?php echo esc_html($term->name); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php
    return ob_get_clean();
}
add_shortcode('listado_categorias', 'shortcode_listado_categorias');





// Agrupa la info del header y el select.orderby en un solo contenedor
add_action('after_setup_theme', function () {
    // Quita los que imprime Woo por defecto
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

    // Añade nuestro wrapper con ambos dentro
    add_action('woocommerce_before_shop_loop', function () {
        // Opcional: limita solo a tienda/categoría/tag de producto
        if (! (is_shop() || is_product_category() || is_product_tag())) {
            // Si no estás en esos contextos, mejor no tocar nada
            return;
        }

        echo '<div class="columna-header-wc">';

        // Bloque izquierdo/derecho: tú decides con CSS
        echo '<div class="woocommerce-header-info">';
        // Esto imprime el <p class="woocommerce-result-count">…</p> nativo
        woocommerce_result_count();
        echo '</div>';

        // Esto imprime el <form class="woocommerce-ordering"> con el <select class="orderby">
        woocommerce_catalog_ordering();

        echo '</div>';
    }, 25); // un punto medio entre 20 y 30 para que quede en el lugar esperado
});


// Shortcode: listado de etiquetas del producto
function shortcode_listado_etiquetas()
{
    global $post;

    if (! $post || 'product' !== get_post_type($post)) {
        return ''; // solo aplica en productos
    }

    $tags = wp_get_post_terms($post->ID, 'product_tag');

    if (empty($tags) || is_wp_error($tags)) {
        return '';
    }

    ob_start(); ?>
    <p class="has-principal-color pb-0 mb-0"><strong>Giro de negocio:</strong></p>
    <ul class="listado-giros">
        <?php foreach ($tags as $tag) : ?>
            <li class="listado__item">
                <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="listado__link">
                    <?php echo esc_html($tag->name); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php
    return ob_get_clean();
}
add_shortcode('listado_etiquetas', 'shortcode_listado_etiquetas');



// Agregar medios de pago en checkout


// functions.php
add_action('woocommerce_cart_collaterals', function () {
    if (! function_exists('get_field')) return;

    $img = get_field('medios_pago_img', 'option');
    if (empty($img) || empty($img['url'])) return;

    $alt = !empty($img['alt']) ? $img['alt'] : __('Medios de pago', 'your-textdomain');

    // Imprimir al final de .cart-collaterals
    echo '<div class="wc-medios-pago below-cart-totals">';
    echo '<p class="has-principal-color pb-1 mb-0">Medios de pago</p>';
    echo '<img src="' . esc_url($img['url']) . '" alt="' . esc_attr($alt) . '" class="single-product-medios-pago" loading="lazy" decoding="async" fetchpriority="low">';
    echo '</div>';
}, 99); // prioridad alta para que salga después de los totales