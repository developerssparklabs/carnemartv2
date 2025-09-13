<?php

$className = 'section-galeria';

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
<section class="site__section site__section-galeria <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">

    <div class="section-galeria-wrapper">

        <?php
        $images = get_field('galeria');
        if ($images): ?>
            <div class="popup-gallery grid-galeria">
                <?php foreach ($images as $image): ?>
                    <a href="<?php echo esc_url($image['url']); ?>" title="<?php echo esc_attr($image['alt']); ?>" class="grid-galeria-item es-Animado"><img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" class="img-grid-galeria" /></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</section>