<?php

$className = 'section-botonera-destacada';

if (!empty($block['className'])) {

    $className .= ' ' . $block['className'];
}

if (!empty($block['align'])) {
    $className .= 'align' . $block['align'];
}

$id_seccion = get_field('id_seccion');
$seccion_fondo_color = get_field('seccion_fondo_color');
?>

<!-- Bloque para Gutenberg -->
<section class="site__section site__section-grid-enlaces <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">

    <?php
    // Verificar si el repeater tiene contenido
    if (have_rows('enlace_botonera')): ?>
        <!-- INICIO Contenedor principal -->
        <div class="site__section-grid-categorias-wrapper">

            <?php
            $counter = 0; // Iniciar contador para los elementos

            // Abrir el selector del repeater
            while (have_rows('enlace_botonera')): the_row();

                // Validar y obtener los datos de cada subcampo
                $titulo = get_sub_field('titulo') ?: '';
                $enlace = get_sub_field('enlace') ?: '#';
                $img_fondo = get_sub_field('img_fondo'); // Obtener el valor del campo imagen
                $img_fondo_url = is_array($img_fondo) ? $img_fondo['url'] : wp_get_attachment_url($img_fondo); // Obtener la URL de la imagen
                $color_titulo = get_sub_field('color_titulo') ?: '#000';
                $open_new_tab = get_sub_field('open_new_tab');

                // Elementos con lógica basada en el contador
                if ($counter === 0 || $counter === 3) {
                    // Elemento individual (0 y 3)
                    echo '<a href="' . esc_url($enlace) . '" ' . ($open_new_tab ? 'target="_blank"' : '') . ' class="site__section-grid-categorias-caja caja-con-contenido es-Animado elemento-contador-' . $counter . '" style="background-image: url(' . esc_url($img_fondo_url) . ');">';
                    echo '<div class="item-contenido">';
                    echo '<h2 style="color: ' . esc_attr($color_titulo) . ';">' . $titulo . '</h2>';
                    echo '</div>';
                    echo '</a>';
                    $counter++; // Incrementar contador después del elemento
                } elseif ($counter === 1 || $counter === 4) {
                    // Iniciar contenedor para pares de elementos
                    echo '<div class="site__section-grid-categorias-caja">';

                    // Elemento 1 o 4
                    echo '<a href="' . esc_url($enlace) . '" ' . ($open_new_tab ? 'target="_blank"' : '') . ' class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado elemento-contador-' . $counter . '" style="background-image: url(' . esc_url($img_fondo_url) . ');">';
                    echo '<div class="item-contenido">';
                    echo '<h2 style="color: ' . esc_attr($color_titulo) . ';">' . $titulo . '</h2>';
                    echo '</div>';
                    echo '</a>';
                    $counter++; // Incrementar contador después del primer elemento del par
                } elseif ($counter === 2 || $counter === 5) {
                    // Elemento 2 o 5 (cerrar contenedor después del segundo elemento del par)
                    echo '<a href="' . esc_url($enlace) . '" ' . ($open_new_tab ? 'target="_blank"' : '') . ' class="site__section-grid-categorias-caja-internax2 caja-con-contenido es-Animado elemento-contador-' . $counter . '" style="background-image: url(' . esc_url($img_fondo_url) . ');">';
                    echo '<div class="item-contenido">';
                    echo '<h2 style="color: ' . esc_attr($color_titulo) . ';">' . $titulo . '</h2>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>'; // Cerrar el contenedor de pares
                    $counter++; // Incrementar contador
                }

            endwhile; // Cerrar el repeater
            ?>

        </div> <!-- FIN Contenedor principal -->
    <?php else: ?>
        <p>No hay elementos configurados en el Repeater.</p>
    <?php endif; ?>
</section>