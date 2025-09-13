<?php

$className = 'section-listado-categorias-principales';

if (!empty($block['className'])) {

    $className .= ' ' . $block['className'];
}

if (!empty($block['align'])) {
    $className .= 'align' . $block['align'];
}

$id_seccion = get_field('id_seccion');
$seccion_fondo_color = get_field('seccion_fondo_color');
?>





<section class="site__section site__section-grid-categorias <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">

    <?php
    // Obtener las categorías seleccionadas desde el ACF
    $selected_categories = get_field('listado_cat_check');

    // Limitar a 6 elementos como máximo
    if ($selected_categories && is_array($selected_categories)) {
        $selected_categories = array_slice($selected_categories, 0, 6); // Máximo 6 categorías
        $counter = 0; // Contador de categorías

        echo '<div class="site__section-grid-categorias-wrapper">'; // Contenedor principal

        foreach ($selected_categories as $category_id) {
            // Obtener los datos de la categoría
            $category = get_term((int) $category_id, 'product_cat'); // Convertir el ID a entero
            if ($category && !is_wp_error($category)) {
                $thumbnail_id = get_term_meta($category_id, 'thumbnail_id', true);
                $thumbnail_url = wp_get_attachment_url($thumbnail_id);
                $category_link = get_term_link($category, 'product_cat');

                // Renderizar según la posición
                if ($counter === 0 || $counter === 3) {
                    // Elementos 0 y 3 (elementos "Full")
                    echo '<a href="' . esc_url($category_link) . '" aria-label="' . esc_attr($category->name) . '" title="' . esc_attr($category->name) . '" class="site__section-grid-categorias-caja caja-con-contenido es-Animado" style="background-image: url(' . esc_url($thumbnail_url) . ');">';
                    echo '<div class="item-contenido">';
                    echo '<h2>' . esc_html($category->name) . '</h2>';
                    echo '</div>';
                    echo '</a>';
                } elseif ($counter === 1 || $counter === 4) {
                    // Iniciar un div para encapsular elementos 1 y 2, o 4 y 5
                    echo '<div class="site__section-grid-categorias-caja">';
                    echo '<a href="' . esc_url($category_link) . '" aria-label="' . esc_attr($category->name) . '" title="' . esc_attr($category->name) . '" class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado" style="background-image: url(' . esc_url($thumbnail_url) . ');">';
                    echo '<div class="item-contenido">';
                    echo '<h2>' . esc_html($category->name) . '</h2>';
                    echo '</div>';
                    echo '</a>';
                } elseif ($counter === 2 || $counter === 5) {
                    // Elementos 2 y 5 (segundo elemento dentro del div)
                    echo '<a href="' . esc_url($category_link) . '" aria-label="' . esc_attr($category->name) . '" title="' . esc_attr($category->name) . '" class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado" style="background-image: url(' . esc_url($thumbnail_url) . ');">';
                    echo '<div class="item-contenido">';
                    echo '<h2>' . esc_html($category->name) . '</h2>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>'; // Cerrar el div que encapsula
                }


                $counter++;
            }
        }

        echo '</div>'; // Cerrar el contenedor principal
    } else {
        echo '<p>No hay categorías seleccionadas.</p>';
    }
    ?>

</section>