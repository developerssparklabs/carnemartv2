<?php

$className = 'section-bloque-iconos-texto';

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
<section class="site__section site__section-area-bullets <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">


    <?php
    $intro_bullet = get_field('intro_bullet');
    if ($intro_bullet) {
    ?>
        <div class="espaciox2-"></div>
        <div class="row">
            <div class="col-md-12 text-center txt-amarillo">
                <?php echo $intro_bullet; ?>
            </div>
        </div>
    <?php
    }
    ?>



    <?php if (have_rows('bullet_item')): ?>
        <div class="espacio"></div>
        <div class="row justify-content-center">
            <?php while (have_rows('bullet_item')): the_row();
                $image = get_sub_field('imagen');
                $titulo = get_sub_field('titulo');
                $contenido = get_sub_field('contenido');
            ?>
                <div class="col-md-3">
                    <div class="site__imagen-texto-lateral">
                        <figure class="site__imagen-texto-figure">
                            <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" class="site__imagen-texto-img">
                        </figure>
                        <div class="site__imagen-texto-contenido">
                            <?php if ($titulo) { ?>
                                <h3 class="titulo"><?php echo $titulo; ?></h3>
                            <?php } ?>
                            <?php if ($contenido) { ?>
                                <div class="descripcion"><?php echo $contenido; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>


</section>