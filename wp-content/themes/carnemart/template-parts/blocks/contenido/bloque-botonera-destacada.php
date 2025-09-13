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

<!-- Bloque para gutemberg -->
<section class="site__section site__section-grid-enlaces <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">

    <?php
    // Verificar si el repeater tiene contenido
    if (have_rows('enlace_botonera')): ?>
        <!-- INICIO Contenedor principal -->
        <div class="site__section-grid-enlaces-wrapper">

            <?php
            $counter = 0; // Iniciar contador para los elementos

            // Abrir el selector del repeater
            while (have_rows('enlace_botonera')): the_row();

                // Validar y obtener los datos de cada subcampo
                $titulo = get_sub_field('titulo') ?: '';
                $descripcion = get_sub_field('descripcion') ?: '';
                $enlace = get_sub_field('enlace') ?: '#';
                $img_fondo = get_sub_field('img_fondo'); // Obtener el valor del campo imagen
                $img_fondo_url = is_array($img_fondo) ? $img_fondo['url'] : wp_get_attachment_url($img_fondo); // Obtener la URL de la imagen
                $color_titulo = get_sub_field('color_titulo') ?: '#000';
                $color_texto = get_sub_field('color_texto') ?: '#000';
                $open_new_tab = get_sub_field('open_new_tab');

                // Ajustar clases específicas según la posición
                $class_caja = '';
                if ($counter === 0) {
                    $class_caja = 'caja-60 alto-grande'; // Elemento 0
                } elseif ($counter === 1) {
                    $class_caja = 'caja-40 alto-grande'; // Elemento 1
                } elseif (in_array($counter, [2, 3, 4, 5])) {
                    $class_caja = 'caja-25 alto-chico'; // Elementos 2, 3, 4, 5
                }

                // Dividir en filas
                if ($counter === 0): ?>
                    <!-- INICIO FILA 1 -->
                    <div class="wrapper__fila_01">
                    <?php endif; ?>

                    <?php if ($counter === 2): ?>
                        <!-- Cerrar fila 1 -->
                    </div>
                    <!-- INICIO FILA 2 -->
                    <div class="wrapper__fila_02">
                    <?php endif; ?>

                    <!-- Contenido dinámico -->
                    <?php if ($counter === 0): ?>
                        <!-- Estructura personalizada para el primer elemento -->
                        <a href="<?php echo esc_url($enlace); ?>"
                            <?php if ($open_new_tab) echo 'target="_blank"'; ?>
                            class="site__section-grid-enlaces-caja <?php echo esc_attr($class_caja); ?> es-Animado elemento-contador-<?php echo $counter; ?>">
                            <div class="item-contenido">
                                <div class="imagen-lateral" style="background-image: url('<?php echo esc_url($img_fondo_url); ?>');"></div>
                                <div class="contenido">
                                    <h3 style="color: <?php echo esc_attr($color_titulo); ?>">
    <?php echo $titulo; ?>
</h3>

                                    <?php if ($descripcion): ?>
                                        <div class="descripcion" style="color: <?php echo esc_attr($color_texto); ?>">
                                            <?php echo esc_html($descripcion); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </a>
                    <?php else: ?>
                        <!-- Estructura estándar para los demás elementos -->
                        <a href="<?php echo esc_url($enlace); ?>"
                            <?php if ($open_new_tab) echo 'target="_blank"'; ?>
                            class="site__section-grid-enlaces-caja <?php echo esc_attr($class_caja); ?> es-Animado elemento-contador-<?php echo $counter; ?>"
                            style="background-image: url('<?php echo esc_url($img_fondo_url); ?>');">
                            <div class="item-contenido">
                                <div class="contenido">
                                    <h3 style="color: <?php echo esc_attr($color_titulo); ?>">
    <?php echo $titulo; ?>
</h3>

                                    <?php if ($descripcion): ?>
                                        <div class="descripcion" style="color: <?php echo esc_attr($color_texto); ?>">
                                            <?php echo esc_html($descripcion); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mascara"></div>
                        </a>
                    <?php endif; ?>

                    <?php
                    // Incrementar contador
                    $counter++;

                    // Finalizar filas
                    if ($counter === 6): ?>
                    </div> <!-- FIN FILA 2 -->
                <?php endif; ?>

            <?php endwhile; // Cerrar el repeater 
            ?>

        </div> <!-- FIN Contenedor principal -->
    <?php else: ?>
        <p>No hay elementos configurados en el Repeater.</p>
    <?php endif; ?>




</section>