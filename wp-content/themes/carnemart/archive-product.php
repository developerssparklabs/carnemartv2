<?php

/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

get_header('shop');
$current_category = get_queried_object();

?>
<section id="post-<?php the_ID(); ?>" <?php post_class('site__content-page'); ?>>
    <?php
    /**
     * Hook: woocommerce_before_main_content.
     *
     * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
     * @hooked woocommerce_breadcrumb - 20
     * @hooked WC_Structured_Data::generate_website_data() - 30
     */
    do_action('woocommerce_before_main_content');

    /**
     * Hook: woocommerce_shop_loop_header.
     *
     * @since 8.6.0
     *
     * @hooked woocommerce_product_taxonomy_archive_header - 10
     */
    do_action('woocommerce_shop_loop_header');

    ?>

    <?php
    $term = get_queried_object();

    if ($term instanceof WP_Term && $term->taxonomy === 'product_cat') {
        // Descripción de la categoría
        $descripcion = term_description($term->term_id, 'product_cat'); // puede traer HTML
    
        // Imagen destacada de la categoría (WooCommerce la guarda como 'thumbnail_id')
        $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
        $img_html = '';
        if ($thumb_id) {
            $img_html = wp_get_attachment_image($thumb_id, 'large', false, [
                'class' => 'cat-thumb',
                'alt' => esc_attr($term->name),
                'loading' => 'lazy',
            ]);
        }
    }
    ?>


    <section class="site__section bloque-contenido-shortcode  section-shortcode-contenido">
        <div class="row-shortcode-section">
            <?php echo do_shortcode('[barra_promociones]'); ?>
        </div>
    </section>

    <?php
    $term = get_queried_object();
    if ($term instanceof WP_Term) {
        $imagen_id = (int) get_term_meta($term->term_id, 'imagen_destacada', true);
        if ($imagen_id) {
            $alt = get_post_meta($imagen_id, '_wp_attachment_image_alt', true);
            if ($alt === '')
                $alt = get_the_title($imagen_id);
            if ($alt === '')
                $alt = $term->name;
            ?>
            <section class="archive-product-top">
                <figure class="archive-product__figure">
                    <?php
                    echo wp_get_attachment_image(
                        $imagen_id,
                        'full',
                        false,
                        [
                            'class' => 'archive-product__image',
                            'alt' => $alt,
                            'loading' => 'lazy',
                            'decoding' => 'async',
                        ]
                    );
                    ?>
                </figure>
            </section>
            <?php
        }
    }
    ?>

    <div class="custom-row">
        <div class="area-breadcomps">
            <?php
            if (function_exists('woocommerce_breadcrumb')) {
                woocommerce_breadcrumb(array(
                    'delimiter' => ' &#47; ',
                    'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="breadcrumb">',
                    'wrap_after' => '</nav>',
                    'before' => '<span>',
                    'after' => '</span>',
                ));
            }
            ?>
        </div>
    </div>

    <section class="site__section-header-pagina-interna">
        <div class="row no-gutters row-header-page-interna">
            <h1>
                <?php
                if (is_product_category()) {
                    single_term_title();
                } elseif (is_shop()) {
                    woocommerce_page_title();
                } else {
                    the_archive_title();
                }
                ?>
            </h1>

            <?php if (!empty($descripcion)): ?>
                <div class="cat-description">
                    <?php echo wp_kses_post($descripcion); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contenedor principal -->

    <div class="row-listado-productos">

        <!-- Columna de filtros -->
        <div class="columna-filtros">
            <aside class="widget-area">
                <!-- Buscador -->
                <div class="widget widget-search">
                    <?php get_search_form(); ?>
                </div>
                <?php if (is_active_sidebar('woocommerce-sidebar')): ?>
                    <?php dynamic_sidebar('woocommerce-sidebar'); ?>
                <?php endif; ?>
                <!-- Resumen de filtros -->
                <?php include_once get_template_directory() . '/includes/filtros-categoria-resumen.php'; ?>
                <?php include_once get_template_directory() . '/includes/filtros-categoria-form.php'; ?>

            </aside>
        </div>

        <!-- Columna de productos -->
        <div class="columna-productos -grid-archive">
            <div class="columna-header-wc">
                <div class="woocommerce-header-info">
                    <div class="woocommerce-header-info">
                        <p class="woocommerce-result-count" role="alert" aria-relevant="all" aria-hidden="false">
                            <?php cm_echo_archive_total(); ?>
                        </p>
                    </div>
                </div>
                <select name="orderby" class="orderby" aria-label="Pedido de la tienda">
                    <option value="menu_order" selected="selected">Orden por defecto</option>
                    <option value="popularity">Ordenar por popularidad</option>
                    <option value="rating">Ordenar por calificación media</option>
                    <option value="date">Ordenar por las últimas</option>
                    <option value="price">Ordenar por precio: bajo a alto</option>
                    <option value="price-desc">Ordenar por precio: alto a bajo</option>
                </select>
                <input type="hidden" name="paged" value="1">
                <input type="hidden" name="categorias[0]" value="aceites"><input type="hidden" name="min_price"
                    value="7"><input type="hidden" name="max_price" value="770">
            </div>
        </div>
    </div>
    <div class="espaciox2"></div>
</section>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */

get_footer('shop');
?>