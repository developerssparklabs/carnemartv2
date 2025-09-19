<?php

// Registrar el shortcode
add_shortcode('slb_best_sellers', 'mostrar_best_sellers');

function mostrar_best_sellers($atts)
{
    // Extraer atributos con valores por defecto
    $atts = shortcode_atts(
        array(
            'titulo' => 'Mejores vendedores',
        ),
        $atts,
        'slb_best_sellers'
    );

    // HTML con section y id para el shortcode
    $output = '<ul class="products columns-4" id="slb_best_sellers_shortcode">';
    $output .= '</ul>';

    return $output;
}