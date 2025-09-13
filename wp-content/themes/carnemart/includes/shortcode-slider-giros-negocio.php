<?php
// Shortcode PRO: [slider_giros_negocio taxonomy="product_tag" limit="12" orderby="count" order="DESC"]
add_shortcode('slider_giros_negocio', function ($atts) {
    $atts = shortcode_atts([
        // Query controls
        'taxonomy'              => 'product_tag',
        'limit'                 => 12,
        'include_ids'           => '',            // "1,5,9"
        'exclude_ids'           => '',            // "3,7"
        'include_slugs'         => '',            // "pizzerias,cafeterias"
        'exclude_slugs'         => '',
        'hide_empty'            => 'true',
        'orderby'               => 'count',       // name|count|slug|include|rand
        'order'                 => 'DESC',        // ASC|DESC
        'respect_include_order' => 'true',        // respeta el orden de include_* si es posible

        // Presentation
        'img_size'      => 'large',
        'wrapper_class' => 'slider-giros-negocio-wrapper',
        'slider_class'  => 'slider-giros-negocio',
        'item_class'    => 'slider-giros-item',
        // Slick columns
        'slides_desktop' => 4,
        'slides_tablet'  => 3,
        'slides_mobile'  => 1,

        // Perf
        'lcp' => 'true', // eager+fetchpriority en el 1er slide
    ], $atts, 'slider_giros_negocio');

    // --- Build terms query ---
    $args = [
        'taxonomy'   => $atts['taxonomy'],
        'hide_empty' => filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN),
        'number'     => (int)$atts['limit'],
        'orderby'    => in_array($atts['orderby'], ['name', 'count', 'slug', 'include', 'rand'], true) ? $atts['orderby'] : 'count',
        'order'      => (strtoupper($atts['order']) === 'ASC') ? 'ASC' : 'DESC',
    ];

    // Parse include/exclude IDs
    if (!empty($atts['include_ids'])) {
        $args['include'] = array_map('intval', array_filter(array_map('trim', explode(',', $atts['include_ids']))));
        if (filter_var($atts['respect_include_order'], FILTER_VALIDATE_BOOLEAN)) {
            $args['orderby'] = 'include'; // respeta el orden dado
        }
    }
    if (!empty($atts['exclude_ids'])) {
        $args['exclude'] = array_map('intval', array_filter(array_map('trim', explode(',', $atts['exclude_ids']))));
    }

    // Include by slugs
    if (!empty($atts['include_slugs'])) {
        $slugs = array_filter(array_map('sanitize_title', array_map('trim', explode(',', $atts['include_slugs']))));
        // Convertimos a IDs para poder respetar el orden real si se pide
        $include_ids = [];
        foreach ($slugs as $slug) {
            $t = get_term_by('slug', $slug, $atts['taxonomy']);
            if ($t && !is_wp_error($t)) $include_ids[] = (int)$t->term_id;
        }
        if ($include_ids) {
            $args['include'] = $include_ids;
            if (filter_var($atts['respect_include_order'], FILTER_VALIDATE_BOOLEAN)) {
                $args['orderby'] = 'include';
            }
        } else {
            $args['slug'] = $slugs; // fallback simple
        }
    }

    // Exclude by slugs
    if (!empty($atts['exclude_slugs'])) {
        $slugs = array_filter(array_map('sanitize_title', array_map('trim', explode(',', $atts['exclude_slugs']))));
        $exclude_ids = [];
        foreach ($slugs as $slug) {
            $t = get_term_by('slug', $slug, $atts['taxonomy']);
            if ($t && !is_wp_error($t)) $exclude_ids[] = (int)$t->term_id;
        }
        if (!empty($exclude_ids)) {
            $args['exclude'] = array_unique(array_merge(isset($args['exclude']) ? $args['exclude'] : [], $exclude_ids));
        }
    }

    $terms = get_terms($args);
    if (is_wp_error($terms) || empty($terms)) return '';

    // --- Image from ACF 'imagen_taxonomia' (ID|array|URL) ---
    $render_term_image = function ($term, $size = 'large', $is_first = false, $boost_lcp = true) {
        $term_key = $term->taxonomy . '_' . $term->term_id;
        $val      = function_exists('get_field') ? get_field('imagen_taxonomia', $term_key) : null;

        $img_id = null;
        $img_url = null;
        $alt = $term->name;
        if (is_numeric($val)) {
            $img_id = (int)$val;
        } elseif (is_array($val)) {
            if (!empty($val['ID']))  $img_id = (int)$val['ID'];
            if (!empty($val['url'])) $img_url = $val['url'];
            if (!empty($val['alt'])) $alt = $val['alt'];
        } elseif (is_string($val) && filter_var($val, FILTER_VALIDATE_URL)) {
            $img_url = $val;
        }

        // attrs comunes
        $common_attrs = [
            'class'    => 'slider-giros-img',
            'alt'      => $alt,
            'loading'  => ($boost_lcp && $is_first) ? 'lazy' : 'lazy',
            'decoding' => 'async',
            'sizes'    => '(max-width:680px) 90vw, (max-width:1024px) 30vw, 22vw',
        ];
        // fetchpriority en el primer slide si lcp=true
        $fetch_attr = ($boost_lcp && $is_first) ? ' fetchpriority="high"' : '';

        if ($img_id) {
            $meta_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            if ($meta_alt) $common_attrs['alt'] = $meta_alt;
            // wp_get_attachment_image no soporta fetchpriority, así que lo añadimos a mano
            $html = wp_get_attachment_image($img_id, $size, false, $common_attrs);
            if ($fetch_attr) {
                // inserta fetchpriority en la etiqueta resultante
                $html = preg_replace('/\/>$/', $fetch_attr . ' />', $html);
            }
            return $html;
        }

        if ($img_url) {
            return sprintf(
                '<img class="%s" src="%s" alt="%s" loading="%s" decoding="async" sizes="%s"%s>',
                esc_attr($common_attrs['class']),
                esc_url($img_url),
                esc_attr($common_attrs['alt']),
                esc_attr($common_attrs['loading']),
                esc_attr($common_attrs['sizes']),
                $fetch_attr
            );
        }

        $ph = function_exists('wc_placeholder_img_src') ? wc_placeholder_img_src('woocommerce_single') : includes_url('images/media/default.png');
        return sprintf(
            '<img class="%s" src="%s" alt="%s" loading="%s" decoding="async" sizes="%s"%s>',
            esc_attr($common_attrs['class']),
            esc_url($ph),
            esc_attr($common_attrs['alt']),
            esc_attr($common_attrs['loading']),
            esc_attr($common_attrs['sizes']),
            $fetch_attr
        );
    };

    // --- Render ---
    ob_start(); ?>
    <section class="<?php echo esc_attr($atts['wrapper_class']); ?>" role="region" aria-label="Giros de negocio">
        <div class="<?php echo esc_attr($atts['slider_class']); ?>">
            <?php
            $i = 0;
            $boost_lcp = filter_var($atts['lcp'], FILTER_VALIDATE_BOOLEAN);
            foreach ($terms as $term):
                $link  = get_term_link($term);
                if (is_wp_error($link)) continue;
                $label = $term->name;
                $img   = $render_term_image($term, $atts['img_size'], $i === 0, $boost_lcp); ?>
                <div class="<?php echo esc_attr($atts['item_class']); ?>">
                    <a href="<?php echo esc_url($link); ?>" class="slider-giros-link"
                        title="<?php echo esc_attr($label); ?>" aria-label="<?php echo esc_attr($label); ?>">
                        <figure class="slider-giros-figure">
                            <?php echo $img; ?>
                            <figcaption class="slider-giros-caption"><span class="slider-giros-title"><?php echo esc_html($label); ?></span></figcaption>
                        </figure>
                    </a>
                </div>
            <?php $i++;
            endforeach; ?>
        </div>

        <div class="custom-arrow-square-giros-negocio cas-prev" aria-label="Anterior" role="button" tabindex="0">
            <i class="bi bi-arrow-left-circle-fill"></i>
        </div>
        <div class="custom-arrow-square-giros-negocio cas-next" aria-label="Siguiente" role="button" tabindex="0">
            <i class="bi bi-arrow-right-circle-fill"></i>
        </div>
    </section>

    <script>
        (function($) {
            $(function() {
                var $wrap = $('.<?php echo esc_js($atts['wrapper_class']); ?>');
                var $slider = $wrap.find('.<?php echo esc_js($atts['slider_class']); ?>');

                if ($slider.length && typeof $.fn.slick === 'function') {
                    $slider.slick({
                        slidesToShow: <?php echo (int)$atts['slides_desktop']; ?>,
                        slidesToScroll: 1,
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 12000,
                        lazyLoad: 'progressive',
                        dots: false,
                        prevArrow: $wrap.find('.cas-prev'),
                        nextArrow: $wrap.find('.cas-next'),
                        accessibility: true,
                        adaptiveHeight: false,
                        responsive: [{
                                breakpoint: 1025,
                                settings: {
                                    slidesToShow: <?php echo (int)$atts['slides_tablet']; ?>
                                }
                            },
                            {
                                breakpoint: 681,
                                settings: {
                                    slidesToShow: <?php echo (int)$atts['slides_mobile']; ?>
                                }
                            }
                        ]
                    });
                }
            });
        })(jQuery);
    </script>
<?php
    return ob_get_clean();
});
