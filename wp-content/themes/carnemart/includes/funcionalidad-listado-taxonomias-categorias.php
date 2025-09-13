


<?php


add_action('wp_footer', function () {
    if (current_user_can('administrator')) {
        global $template;
        echo '<div style="background: rgba(0,0,0,0.8); color: #fff; padding: 10px; position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999; font-size: 14px; text-align: center;">';
        echo 'Template en uso: ' . basename($template);
        echo '</div>';
    }
});

// add_action('init', function () {
//     global $wp_taxonomies;

//     echo '<pre>';
//     print_r($wp_taxonomies);
//     echo '</pre>';
//     exit;
// });


function get_dynamic_archive_title_and_image()
{
    $queried_object = get_queried_object(); // Objeto consultado
    $title = 'Productos'; // Título por defecto
    $image_url = get_template_directory_uri() . '/img/default-image-banner.jpg'; // Imagen predeterminada

    if ($queried_object) {
        // Si estamos en una taxonomía
        if (isset($queried_object->taxonomy)) {
            $title = esc_html($queried_object->name);
            $image_id = get_term_meta($queried_object->term_id, 'imagen_destacada', true);
            if ($image_id) {
                $image_url = wp_get_attachment_url($image_id);
            }
        }
        // Si estamos en el archivo general de productos
        elseif (isset($queried_object->post_type) && $queried_object->post_type === 'product') {
            $title = 'Todos los productos';
        }
    }

    return [
        'title' => $title,
        'image_url' => $image_url,
    ];
}







add_filter('template_include', function ($template) {
    if (is_tax('product_cat') || is_tax('your_custom_taxonomy')) {
        // Cambiar al template de archive-product.php para estas taxonomías
        $wc_archive_template = locate_template('archive-product.php');
        if ($wc_archive_template) {
            return $wc_archive_template;
        }
    }

    return $template;
});



// Manejo de taxonomías personalizadas (mantén esto igual)
add_action('init', function () {
    $taxonomias = [
        'tipos_vino' => 'Tipos de Vino',
        'region'     => 'Región',
        'pais'       => 'País',
        'bodega'     => 'Bodega',
        'variedad'   => 'Variedad',
        'maridaje'   => 'Maridaje',
        'familia'    => 'Familia',
    ];

    // Registrar reescrituras dinámicamente
    foreach ($taxonomias as $slug => $nombre) {
        add_rewrite_rule(
            "^{$slug}/?$", // Ruta raíz de la taxonomía
            "index.php?taxonomy={$slug}", // Endpoint de WordPress
            'top'
        );
    }

    flush_rewrite_rules(false); // Regenerar reglas
});

add_action('parse_request', function ($query) {
    $taxonomias = [
        'tipos_vino',
        'region',
        'pais',
        'bodega',
        'variedad',
        'maridaje',
        'familia',
    ];

    // Verificar si la URL coincide con alguna raíz de taxonomía
    if (!empty($query->request) && preg_match('/^(' . implode('|', $taxonomias) . ')\/?$/', $query->request, $matches)) {
        $taxonomy = $matches[1]; // Detectar la taxonomía desde la URL

        // Ajustar las variables de consulta
        $query->query_vars['taxonomy'] = $taxonomy;
        $query->query_vars['post_type'] = 'product'; // Asociar al CPT 'product'
        $query->query_vars['is_archive'] = true;
        $query->query_vars['is_tax'] = true;

        return; // Procesar como una consulta válida
    }
});

add_filter('template_include', function ($template) {
    global $wp_query;

    if (isset($wp_query->query_vars['taxonomy'])) {
        $taxonomy = $wp_query->query_vars['taxonomy'];

        // Intentar cargar una plantilla específica
        $custom_template = locate_template("taxonomy-{$taxonomy}.php");
        if ($custom_template) {
            return $custom_template;
        }

        // Cargar la plantilla genérica de taxonomías
        return locate_template('taxonomy.php') ?: $template;
    }

    return $template;
});



add_action('pre_get_posts', function ($query) {
    // Solo aplicar en el front-end, en la consulta principal y en páginas de taxonomías
    if (!is_admin() && $query->is_main_query() && is_tax()) {
        $queried_taxonomy = get_queried_object();

        if ($queried_taxonomy && isset($queried_taxonomy->taxonomy)) {
            $valid_taxonomies = [
                'region',
                'tipos_vino',
                'pais',
                'bodega',
                'variedad',
                'maridaje',
                'familia',
                'product_cat', // Categorías de productos
            ];

            if (in_array($queried_taxonomy->taxonomy, $valid_taxonomies, true)) {
                // Filtrar productos asociados con términos de la taxonomía actual
                $query->set('post_type', 'product'); // Forzar `product`
                $query->set('tax_query', [
                    [
                        'taxonomy' => $queried_taxonomy->taxonomy,
                        'field'    => 'term_id',
                        'terms'    => get_terms([
                            'taxonomy'   => $queried_taxonomy->taxonomy,
                            'fields'     => 'ids',
                            'hide_empty' => false,
                        ]),
                        'operator' => 'IN',
                    ],
                ]);
            }
        }
    }
});













// Asegurar la regla para la raíz de `product_cat`
add_action('init', function () {
    add_rewrite_rule(
        '^categoria-producto/?$', // Ruta raíz para `product_cat`
        'index.php?taxonomy=product_cat', // Endpoint de WordPress para categorías de productos
        'top'
    );

    flush_rewrite_rules(false); // Regenerar reglas sin borrar las existentes
});

// Configurar `post_type` para la raíz de `product_cat`
add_action('parse_query', function ($query) {
    if (is_tax('product_cat') && empty($query->query_vars['post_type'])) {
        $query->query_vars['post_type'] = 'product'; // Asociar al CPT `product`
    }
});

// Forzar el uso de `archive-product.php`
add_filter('template_include', function ($template) {
    if (is_tax('product_cat')) {
        $wc_archive_template = locate_template('archive-product.php');
        if ($wc_archive_template) {
            return $wc_archive_template;
        }
    }
    return $template;
});
