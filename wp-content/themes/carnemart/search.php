<?php get_header(); ?>

<div class="espaciox2"></div>
<section class="site__content-page">
    <header class="site__section-header-pagina-interna mini-header">
        <div class="row no-gutters row-header-page-interna">
            <h1><?php printf(esc_html__('Resultados de búsqueda: %s', 'tu-tema'), '<span>' . get_search_query() . '</span>'); ?></h1>
        </div>
    </header>
    <section class="site__section site__section-bloque-general">
        <div class="search-results-content">
            <div class="row-listado-productos">
                <!-- Columna de filtros -->
                <div class="columna-filtros">
                    <aside class="widget-area">
                        <!-- Buscador -->
                        <div class="widget widget-search">
                            <?php get_search_form(); ?>
                        </div>

                        <?php if (is_active_sidebar('woocommerce-sidebar')) : ?>
                            <?php dynamic_sidebar('woocommerce-sidebar'); ?>
                        <?php endif; ?>

                    </aside>
                </div>

                <!-- Columna de productos -->
                <div class="columna-productos grid-archive woocommerce">
                    <?php if (have_posts()) : ?>
                        <?php
                        $productos = array();
                        $posts_regulares = array();

                        // Separar productos y posts regulares
                        while (have_posts()) {
                            the_post();
                            if (get_post_type() === 'product') {
                                $productos[] = get_the_ID();
                            } else {
                                $posts_regulares[] = get_the_ID();
                            }
                        }
                        // Mostrar productos si existen
                        if (!empty($productos)) : ?>

                            <?php
                            woocommerce_product_loop_start();
                            foreach ($productos as $producto_id) {
                                $post = get_post($producto_id);
                                setup_postdata($post);
                                wc_get_template_part('content', 'product');
                            }
                            woocommerce_product_loop_end();
                            ?>

                        <?php endif;

                        if (!empty($posts_regulares)) : ?>
                            <div class="row-resultados">
                                <?php
                                foreach ($posts_regulares as $post_id) {
                                    $post = get_post($post_id);
                                    setup_postdata($post);
                                    get_template_part('loop');
                                }
                                ?>
                            </div>
                        <?php endif;

                        wp_reset_postdata();
                        ?>

                        <div class="search-pagination">
                            <?php
                            the_posts_pagination(array(
                                'mid_size'  => 2,
                                'prev_text' => __('« Anterior', 'tu-tema'),
                                'next_text' => __('Siguiente »', 'tu-tema'),
                            ));
                            ?>
                        </div>
                    <?php else : ?>
                        <div class="no-results">
                            <h2><?php esc_html_e('No se encontraron resultados', 'tu-tema'); ?></h2>
                            <p><?php esc_html_e('Intenta con otra palabra clave.', 'tu-tema'); ?></p>
                            <?php get_search_form(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </section>
</section>


<style>
    .woocommerce ul.products li.product a img {
        width: 60%;
        height: 250px;
        margin: 15px auto 15px auto;
        object-fit: contain;
        object-position: bottom;
    }

    .products .product {
        border: solid 1px #eeeeee;
    }
</style>

<?php get_footer(); ?>