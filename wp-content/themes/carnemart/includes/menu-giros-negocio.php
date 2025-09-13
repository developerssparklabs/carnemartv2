<?php
add_shortcode('menus_giro_negocio', 'woocommerce_product_tag_filter2');

function woocommerce_product_tag_filter($atts)
{
    $atts = shortcode_atts(array(
        'ismenu' => 'false',
    ), $atts);

    $isMenu = filter_var($atts['ismenu'], FILTER_VALIDATE_BOOLEAN);

    $tags = get_terms('product_tag', array(
        'hide_empty' => true,
    ));

    if (!empty($tags) && !is_wp_error($tags)) {
        $output = '<ul class="' . ($isMenu ? 'product-tags-filter-menu' : 'product-tags-filter') . '">';

        $shown = [];

        foreach ($tags as $tag) {
            // Separar etiquetas que vienen unidas por comas
            $names = array_map('trim', explode(',', $tag->name));

            foreach ($names as $name) {
                if (!in_array($name, $shown)) {
                    // Buscar el objeto del tag por nombre
                    $tag_obj = get_term_by('name', $name, 'product_tag');

                    if ($tag_obj && !is_wp_error($tag_obj)) {
                        $url = get_term_link($tag_obj);
                    } else {
                        $url = home_url('/');
                    }

                    $output .= '<li><a href="' . esc_url($url) . '">' . esc_html($name) . '</a></li>';
                    $shown[] = $name;
                }
            }
        }

        $output .= '</ul>';
        return $output;
    }

    return '<p>Sin etiquetas</p>';
}

add_shortcode('menus_giro_negocio_header', 'woocommerce_product_tag_filter');
