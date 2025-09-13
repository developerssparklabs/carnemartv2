<?php

/**
 * Tax para productos
 * // NOTE MODIFICADO
 */

function registrar_taxonomias_woocommerce()
{
    $taxonomias = [
        'tipos_vino' => 'Tipos de Vino / Subfamilia',
        'region' => 'Región',
        'pais' => 'País',
        'bodega' => 'Bodega',
        'variedad' => 'Variedad',
        'maridaje' => 'Maridaje',
        'familia' => 'Familia',
    ];

    foreach ($taxonomias as $slug => $nombre) {
        register_taxonomy(
            $slug,
            'product',
            array(
                'label' => $nombre,
                'hierarchical' => true,
                'public' => true,
                'show_ui' => true,
                'rewrite' => array('slug' => $slug),
            )
        );
    }
}
add_action('init', 'registrar_taxonomias_woocommerce');


function agregar_taxonomias_personalizadas_importacion($options)
{
    $taxonomias_personalizadas = [
        'tipos_vino' => 'Tipos de Vino / Subfamilia',
        'region' => 'Región',
        'pais' => 'País',
        'bodega' => 'Bodega',
        'variedad' => 'Variedad',
        'maridaje' => 'Maridaje',
        'familia' => 'Familia',
    ];

    foreach ($taxonomias_personalizadas as $slug => $nombre) {
        $options['taxonomy-' . $slug] = $nombre;
    }

    return $options;
}
add_filter('woocommerce_csv_product_import_mapping_options', 'agregar_taxonomias_personalizadas_importacion');

function mapear_taxonomias_personalizadas($columns)
{
    $taxonomias_personalizadas = [
        'tipos_vino' => 'Tipos de Vino / Subfamilia',
        'region' => 'Región',
        'pais' => 'País',
        'bodega' => 'Bodega',
        'variedad' => 'Variedad',
        'maridaje' => 'Maridaje',
        'familia' => 'Familia',
    ];

    foreach ($taxonomias_personalizadas as $slug => $nombre) {
        $columns[$nombre] = 'taxonomy-' . $slug;
    }

    return $columns;
}
add_filter('woocommerce_csv_product_import_mapping_default_columns', 'mapear_taxonomias_personalizadas');

function procesar_taxonomias_personalizadas($object, $data)
{
    $taxonomias_personalizadas = [
        'tipos_vino',
        'region',
        'pais',
        'bodega',
        'variedad',
        'maridaje',
        'familia',
    ];

    foreach ($taxonomias_personalizadas as $slug) {
        if (!empty($data['taxonomy-' . $slug])) {
            $terms = array_map('trim', explode(',', $data['taxonomy-' . $slug]));
            wp_set_object_terms($object->get_id(), $terms, $slug, true);
        }
    }
}
add_action('woocommerce_product_import_inserted_product_object', 'procesar_taxonomias_personalizadas', 10, 2);
