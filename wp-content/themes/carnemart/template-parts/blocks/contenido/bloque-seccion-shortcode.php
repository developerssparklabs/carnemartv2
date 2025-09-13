<?php

$className = 'section-shortcode-contenido';

if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

if (!empty($block['align'])) {
    $className .= 'align' . $block['align'];
}

$id_seccion = get_field('id_seccion');
$seccion_fondo_color = get_field('seccion_fondo_color');
?>

<?php
$color_titulo = get_field('color_titulo');
$color_descripcion = get_field('color_descripcion');
$titulo_seccion = get_field('titulo_seccion');
$contenido_seccion = get_field('contenido_seccion');
?>




<section class="site__section bloque-contenido-shortcode <?php if (get_field('bloque_full_width')) { ?> bloque-full<?php } ?> <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id_seccion); ?>" style="background-color:<?php echo esc_attr($seccion_fondo_color); ?>!important;">

    <?php if (get_field('bloque_full_width')) { ?>
        <div class="custom-shape-divider-top">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" class="shape-fill"></path>
            </svg>
        </div>
    <?php } ?>

    <?php if (get_field('bloque_full_width')) { ?>
        <div class="site__content-page">
        <?php } ?>

        <div class="row-shortcode-section <?php if (get_field('bloque_full_width')) { ?> espacio-separadores<?php } ?>  <?php the_field('distribucion_contenido'); ?> AOFFF">

            <?php if ($titulo_seccion) { ?>
                <div class="shortcode-section__contenido">
                    <?php if ($titulo_seccion) { ?>

                        <h2 class="comunicado-section__titulo <?php the_field('alineacion_titulo'); ?>">
                            <?php echo $titulo_seccion; ?>
                        </h2>

                    <?php } ?>

                    <?php if ($contenido_seccion) { ?>
                        <div class="comunicado-section__contenido" style="color: <?php echo $color_descripcion; ?>">
                            <?php echo $contenido_seccion; ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="shortcode-section__code">

                <?php
                $shortcode = get_field('bloque_sc');

                if (!empty($shortcode)) {
                    echo do_shortcode($shortcode);
                }
                ?>

            </div>

        </div>

        <?php if (get_field('bloque_full_width')) { ?>
        </div>
    <?php } ?>

    <?php if (get_field('bloque_full_width')) { ?>
        <div class="custom-shape-divider-bottom">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" class="shape-fill"></path>
            </svg>
        </div>
    <?php } ?>

</section>