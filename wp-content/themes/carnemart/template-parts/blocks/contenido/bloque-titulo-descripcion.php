<?php

$className = 'section-titulo-descripcion';

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
<section class="site__section site__section-titulo-simple <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">


    <h2 class="site__section-titulo-simple-titulo remove-br es-Animado">
        <?php the_field('titulo_section'); ?>
    </h2>

    <?php
    $descripcion_section = get_field('descripcion_section');
    if ($descripcion_section) { ?>
        <div class="espacio"></div>
        <div class="site__section-titulo-simple-descripcion remove-br es-Animado">
            <?php echo $descripcion_section; ?>
        </div>
    <?php } ?>


</section>