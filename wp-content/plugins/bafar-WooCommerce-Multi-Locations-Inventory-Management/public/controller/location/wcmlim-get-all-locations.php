<?php
$terms = get_terms([
    'taxonomy'   => 'locations',
    'hide_empty' => false,
    'parent'     => 0
]);

$result = [];

foreach ($terms as $term) {
    // Obtener metadata
    $term_meta     = get_option("taxonomy_$term->term_id");
    $term_locator  = get_term_meta($term->term_id, 'wcmlim_locator', true);

    // Asegurar que sea array
    if (!is_array($term_meta)) {
        $term_meta = [];
    }

    // Filtrar solo elementos válidos (evitar arrays anidados o vacíos)
    $flat_meta = array_filter($term_meta, function ($item) {
        return !is_array($item) && !empty($item);
    });

    $result[] = [
        'location_address'   => implode(' ', $flat_meta),
        'location_name'      => $term->name,
        'location_slug'      => $term->slug,
        'location_storeid'   => $term_locator,
        'location_termid'    => $term->term_id,
    ];
}

return $result; // Si esto se usa como callback en AJAX, deberías usar wp_send_json()

wp_die();