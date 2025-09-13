<?php
function custom_woocommerce_products_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'category' => '', // Categoría de productos (usar slug)
            'taxonomy' => '', // Taxonomía personalizada (usar slug)
            'terms' => '', // Términos específicos de la taxonomía (slugs separados por comas)
            'posts_per_page' => 12, // Número de productos a mostrar
            'paged' => 1, // Paginación
        ),
        $atts,
        'custom_woocommerce_products'
    );

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => intval($atts['posts_per_page']),
        'post_status' => 'publish',
        'paged' => $paged,
        'orderby' => 'rand',
        'meta_query'     => array(
            array(
                'key'     => '_stock_status',
                'value'   => 'instock'
            )
        )
    );

    global $wpdb;
    echo $wpdb->last_query;
    if (!empty($atts['category'])) {
        $args['tax_query'][] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => explode(',', $atts['category']),
            )
        );
    }

    if (!empty($atts['taxonomy']) && !empty($atts['terms'])) {
        $args['tax_query'][] = array(
            array(
                'taxonomy' => sanitize_text_field($atts['taxonomy']),
                'field'    => 'slug',
                'terms'    => explode(',', $atts['terms']),
            )
        );
    }

    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {

        $output .= '<div class="woocommerce"> <div class="custom-woocommerce-slider products-slide">';

        while ($query->have_posts()) {
            $query->the_post();
            global $product;

            ob_start();
            wc_get_template_part('content', 'product'); // Usa las cards nativas de WooCommerce
            $output .= ob_get_clean();
        }

        $output .= '</div></div>';

        // Agregar paginación
        $big = 999999999;
        $output .= '<div class="pagination">' . paginate_links(array(
            'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'  => '?paged=%#%',
            'current' => max(1, $paged),
            'total'   => $query->max_num_pages,
        )) . '</div>';
    } else {
        $output .= '<p class="woocommerce-info">No hay productos disponibles en esta categoría o taxonomía.</p>';
    }

    wp_reset_postdata();

    // Iniciar Slick Slider solo si existe el contenedor y re-inicializar al cambiar de tab
    $output .= '<script>
        jQuery(document).ready(function($) {
            function initSlickSlider() {
                $(".custom-woocommerce-slider").each(function() {
                    var $slider = $(this);
                    if ($slider.length > 0 && !$slider.hasClass("slick-initialized")) {
                        $slider.slick({
                            slidesToShow: 4,
                            slidesToScroll: 4,
                            infinite: true,
                            autoplay: false,
                            dots: false,
                            autoplaySpeed: 8000,
                            responsive: [
                                {
                                    breakpoint: 1280,
                                    settings: {
                                        slidesToShow: 4,
                                        slidesToScroll: 4
                                    }
                                },
                                {
                                    breakpoint: 768,
                                    settings: {
                                        slidesToShow: 3,
                                        slidesToScroll: 3
                                    }
                                },
                                {
                                    breakpoint: 767,
                                    settings: {
                                        slidesToShow: 2,
                                        slidesToScroll: 2,
                                        dots: true,
                                        arrows: false,
                                    }
                                }
                            ]
                        });
                    }
                });
            }

            setTimeout(initSlickSlider, 10); // Retraso para asegurar que el DOM está listo

            document.querySelectorAll("[data-bs-toggle=\"tab\"]").forEach(function(tab) {
                tab.addEventListener("shown.bs.tab", function () {
                    setTimeout(function() {
                        $(".custom-woocommerce-slider").each(function() {
                            var $slider = $(this);
                            if ($slider.hasClass("slick-initialized")) {
                                $slider.slick("unslick"); // Destruir Slick
                            }
                            initSlickSlider(); // Volver a inicializar
                        });
                    }, 10);
                });
            });
        });
    </script>';

    return $output;
}
add_shortcode('custom_woocommerce_products', 'custom_woocommerce_products_shortcode');


function destacados_por_pais_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'pais' => '',
        ),
        $atts,
        'destacados_por_pais'
    );

    $skus_por_pais = array();

    switch (strtolower($atts['pais'])) {
        case 'españa':
            $skus_por_pais = array('7480', '7504', '7531', '8439', '7508', '7477', '8475', '8327', '7513', '7580', '7440');
            break;
        case 'portugal':
            $skus_por_pais = array('9190', '9192', '9714', '9193', '9196', '9198', '9818', '9226');
            break;
        case 'francia':
            $skus_por_pais = array('7574', '7498', '7559', '7788', '7780', '7781', '8760', '9766', '9792', '7750', '7737', '7722');
            break;
        default:
            return '<p class="woocommerce-info">No hay productos destacados para este país.</p>';
    }

    if (empty($skus_por_pais)) {
        return '<p class="woocommerce-info">No hay productos destacados para este país.</p>';
    }

    // Armar meta_query para buscar productos con esos SKUs
    $meta_query = array(
        'relation' => 'AND',
        array(
            'key'     => '_stock_status',
            'value'   => 'instock'
        ),
        array(
            'key'     => '_sku',
            'value'   => $skus_por_pais,
            'compare' => 'IN'
        )
    );

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query
    );


    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {

        $output .= '<div class="woocommerce"> <div class="custom-woocommerce-slider products-slide">';

        while ($query->have_posts()) {
            $query->the_post();
            global $product;

            ob_start();
            wc_get_template_part('content', 'product'); // Usa las cards nativas de WooCommerce
            $output .= ob_get_clean();
        }

        $output .= '</div></div>';

        // Agregar paginación
        $big = 999999999;
        $output .= '<div class="pagination">' . paginate_links(array(
            'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format'  => '?paged=%#%',
            'current' => max(1, $paged),
            'total'   => $query->max_num_pages,
        )) . '</div>';
    } else {
        $output .= '<p class="woocommerce-info">No hay productos disponibles en esta categoría o taxonomía.</p>';
    }
    wp_reset_postdata();
    $output .= '<script>
                jQuery(document).ready(function($) {
                    function initSlickSlider() {
                        $(".custom-woocommerce-slider").each(function() {
                            var $slider = $(this);
                            if ($slider.length > 0 && !$slider.hasClass("slick-initialized")) {
                                $slider.slick({
                                    slidesToShow: 4,
                                    slidesToScroll: 4,
                                    infinite: true,
                                    autoplay: false,
                                    dots: false,
                                    autoplaySpeed: 8000,
                                    responsive: [
                                        {
                                            breakpoint: 1280,
                                            settings: {
                                                slidesToShow: 4,
                                                slidesToScroll: 4
                                            }
                                        },
                                        {
                                            breakpoint: 768,
                                            settings: {
                                                slidesToShow: 3,
                                                slidesToScroll: 3
                                            }
                                        },
                                        {
                                            breakpoint: 767,
                                            settings: {
                                                slidesToShow: 2,
                                                slidesToScroll: 2,
                                                dots: true,
                                                arrows: false,
                                            }
                                        }
                                    ]
                                });
                            }
                        });
                    }

                    setTimeout(initSlickSlider, 10); // Retraso para asegurar que el DOM está listo

                    document.querySelectorAll("[data-bs-toggle=\"tab\"]").forEach(function(tab) {
                        tab.addEventListener("shown.bs.tab", function () {
                            setTimeout(function() {
                                $(".custom-woocommerce-slider").each(function() {
                                    var $slider = $(this);
                                    if ($slider.hasClass("slick-initialized")) {
                                        $slider.slick("unslick"); // Destruir Slick
                                    }
                                    initSlickSlider(); // Volver a inicializar
                                });
                            }, 10);
                        });
                    });
                });
                </script>';

    return $output;
}
add_shortcode('destacados_por_pais', 'destacados_por_pais_shortcode');
