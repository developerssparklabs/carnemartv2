<?php
defined('ABSPATH') || exit;

get_header();

echo '<div class="contenedor-archivo">';
echo '<h1>' . esc_html(post_type_archive_title('', false)) . '</h1>';

if (have_posts()) {
	echo '<div class="elementos-listado">';
	while (have_posts()) {
		the_post();
		get_template_part('content'); // Usa un template genérico para contenido
	}
	echo '</div>';
} else {
	echo '<p>No se encontraron elementos.</p>';
}

echo '</div>';


// Obtener el objeto consultado
$queried_object = get_queried_object();

// Verificar si es una taxonomía o categoría
if ($queried_object && !is_wp_error($queried_object)) {
	if (is_category()) {
		// Si es una categoría
		$nombre = single_cat_title('', false);
		echo '<h1>Categoría: ' . esc_html($nombre) . '</h1>';
	} elseif (is_tax()) {
		// Si es una taxonomía personalizada
		$nombre = single_term_title('', false);
		echo '<h1>Taxonomía: ' . esc_html($nombre) . '</h1>';
	} elseif (is_post_type_archive()) {
		// Si es un archivo de CPT
		$nombre = post_type_archive_title('', false);
		echo '<h1>Archivo de CPT: ' . esc_html($nombre) . '</h1>';
	}
} else {
	// Si no es un término o categoría válido
	echo '<h1>Nombre no disponible AOG</h1>';
}

get_footer();
