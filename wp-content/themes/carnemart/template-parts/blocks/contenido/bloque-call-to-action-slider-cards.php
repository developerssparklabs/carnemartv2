<?php

$className = 'section-slider-ancho-completo';

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
<section class="site__section site__section-slider-lateral-y-cta <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">
    <div class="site__slider-lateral-y-cta-wrapper">

        <div class="site__slider-lateral-y-cta-caja-informacion es-Animado">

            <div class="caja-header">
                <?php
                $titulo_section = get_field('titulo_section');
                $descripcion_section = get_field('descripcion_section');
                $enlace_section = get_field('enlace_section');



                if ($titulo_section) { ?>
                    <h2 class="remove-br">
                        <?php echo $titulo_section; ?>
                    </h2>
                <?php } ?>

                <?php
                if ($descripcion_section) { ?>
                    <div class="descricion-area">
                        <?php echo $descripcion_section; ?>
                    </div>
                <?php } ?>
            </div>

            <?php
            if ($enlace_section):
                $link_url = $enlace_section['url'];
                $link_title = $enlace_section['title'];
                $link_target = $enlace_section['target'] ? $enlace_section['target'] : '_self';
            ?>
                <a class="btn btn-primary w-arrow" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><span><?php echo esc_html($link_title); ?></span></a>
            <?php endif; ?>


        </div>

        <div class="slider-informacion es-Animado">


            <?php if (have_rows('card_slide')): ?>

                <?php while (have_rows('card_slide')): the_row();
                    $intro = get_sub_field('intro');
                    $titulo = get_sub_field('titulo');
                    $descripcion = get_sub_field('descripcion');
                    $img_icono = get_sub_field('img_icono');
                    $img_fondo = get_sub_field('img_fondo');
                    $color_fondo = get_sub_field('color_fondo');
                    $link_card_item = get_sub_field('enlace');
                ?>

                    <?php if ($link_card_item) { ?>

                        <?php
                        if ($link_card_item):
                            $link_url = $link_card_item['url'];
                            $link_title = $link_card_item['title'];
                            $link_target = $link_card_item['target'] ? $link_card_item['target'] : '_self';
                        ?>
                            <a class="site__slider-tipo-informacion-item" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" style="background-image: url('<?php echo $img_fondo['url']; ?>'); background-color: <?php echo $color_fondo; ?>;">
                            <?php endif; ?>
                        <?php } else { ?>
                            <div class="site__slider-tipo-informacion-item" style="background-image: url('<?php echo $img_fondo['url']; ?>'); background-color: <?php echo $color_fondo; ?>;">
                            <?php } ?>

                            <div class="site__slider-tipo-informacion-contenido">

                                <?php if ($intro) { ?>
                                    <div class="site__slider-tipo-informacion-cat">
                                        <?php echo $intro; ?>
                                    </div>
                                <?php } ?>

                                <?php if ($img_icono) { ?>
                                    <figure class="site__slider-tipo-informacion-figure">
                                        <img src="<?php echo $img_icono['url']; ?>" alt="<?php echo $img_icono['url']; ?>" class="site__slider-tipo-informacion-image">
                                    </figure>
                                <?php } else { ?>
                                    <div class="site__slider-tipo-informacion-titulo">
                                        <h3><?php echo $titulo; ?></h3>
                                    </div>

                                <?php } ?>



                                <?php if ($descripcion) { ?>
                                    <div class="site__slider-tipo-informacion-descripcion">
                                        <?php echo $descripcion; ?>
                                    </div>
                                <?php } ?>


                            </div>
                            <div class="mask-bg"></div>


                            <?php if ($link_card_item) { ?>
                            </a>
                        <?php } else { ?>
        </div>
    <?php } ?>

<?php endwhile; ?>

<?php endif; ?>




    </div>

    </div>

</section>