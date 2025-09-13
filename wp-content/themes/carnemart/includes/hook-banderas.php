<?php

add_action('woocommerce_after_shop_loop_item_title', 'añadir_bandera_a_card_producto', 10);

function añadir_bandera_a_card_producto()
{
    global $product;

    // Obtener los términos asociados a la taxonomía 'pais' para este producto
    $terms = get_the_terms($product->get_id(), 'pais');

    echo '<div class="flag-producto-card">';

    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            // Obtener la URL de la imagen del ACF
            $image_url = get_field('img_flag_pais', 'pais_' . $term->term_id);

            // Mostrar la imagen si existe
            if ($image_url) {
                echo '<div class="flag-circle">';
                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($term->name) . '" />';
                echo '</div>';
            }
        }
    }
    echo '</div>';
}
