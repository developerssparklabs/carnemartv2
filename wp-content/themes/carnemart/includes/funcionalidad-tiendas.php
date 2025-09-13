<?php
function crear_cpt_sucursales()
{
    $labels = array(
        'name'               => 'Sucursales',
        'singular_name'      => 'Sucursal',
        'menu_name'          => 'Sucursales',
        'name_admin_bar'     => 'Sucursal',
        'add_new'            => 'Agregar Nueva',
        'add_new_item'       => 'Agregar Nueva Sucursal',
        'new_item'           => 'Nueva Sucursal',
        'edit_item'          => 'Editar Sucursal',
        'view_item'          => 'Ver Sucursal',
        'all_items'          => 'Todas las Sucursales',
        'search_items'       => 'Buscar Sucursales',
        'not_found'          => 'No se encontraron sucursales',
        'not_found_in_trash' => 'No se encontraron sucursales en la papelera',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'sucursal'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-store',
        'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest'       => true, // Para compatibilidad con el editor de bloques y ACF
    );

    register_post_type('sucursal', $args);
}
add_action('init', 'crear_cpt_sucursales');



function my_acf_google_map_api($api)
{
    $api['key'] = 'AIzaSyDMDBDU9aMaoHI2ov7Ywa6_Jo9gDMhjGOc'; // Reemplaza con tu clave API
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');
