<?php

$className = 'section-bloque-contenido-lateral';

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


<section class="site__section site__section-contenido-lateral <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">


    <?php if (have_rows('bloque_item')): ?>

        <?php while (have_rows('bloque_item')): the_row();
            $alineacion_contenido = get_sub_field('alineacion_contenido');
            $imagen_titulo = get_sub_field('imagen_titulo');
            $detalle = get_sub_field('detalle');
            $titulo = get_sub_field('titulo');
            $contenido = get_sub_field('contenido');
            $video_imagen = get_sub_field('video_imagen');
            $imagen_destacada = get_sub_field('imagen_destacada');
            $idYouTube = get_sub_field('youtube_id');
        ?>

            <?php if ($alineacion_contenido == 'contenido-banner') { ?>


                <div class="site__bloque banner-contenido es-Animado">


                    <?php if ($video_imagen == 'is_img') { ?>
                        <div class="banner-contenido__image" style="background-image:url('<?php echo $imagen_destacada['url']; ?>');">

                        </div>
                    <?php } elseif ($video_imagen == 'is_video') { ?>
                        <div class="banner-contenido__image forzado-video">
                            <div class="ratio ratio-16x9 video-marco">
                                <iframe src="https://www.youtube.com/embed/<?php echo $idYouTube; ?>?rel=0" title="YouTube video" allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php } else {  ?>

                    <?php } ?>


                    <div class="banner-contenido__contenido" style="background-color: <?php the_sub_field('color_fondo'); ?>!important; color:<?php the_sub_field('color_textos'); ?>!important;">
                        <?php if ($detalle) { ?>
                            <div class="detalle"><?php echo $detalle; ?></div>
                        <?php } ?>

                        <?php if ($imagen_titulo) { ?>
                            <figure class="site__slider-tipo-informacion-figure">
                                <img src="<?php echo $imagen_titulo['url']; ?>" alt="<?php echo $imagen_titulo['url']; ?>" class="site__slider-tipo-informacion-image">
                            </figure>
                        <?php } else { ?>
                            <div class="titulo">
                                <h2 style="color:<?php the_sub_field('color_textos'); ?>!important;"><?php echo $titulo; ?></h2>
                            </div>
                        <?php } ?>

                        <?php if ($contenido) { ?>
                            <div class="contenido">
                                <?php echo $contenido; ?>
                            </div>
                        <?php } ?>

                        <?php if (have_rows('enlaces_botonera')): ?>
                            <div class="botonera">
                                <?php while (have_rows('enlaces_botonera')): the_row(); ?>
                                    <?php
                                    $link = get_sub_field('enlace');
                                    if ($link):
                                        $link_url = $link['url'];
                                        $link_title = $link['title'];
                                        $link_target = $link['target'] ? $link['target'] : '_self';
                                    ?>
                                        <a class="btn btn-primary w-arrow" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><span><?php echo esc_html($link_title); ?></span></a>
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>


            <?php } else { ?>
                <!--  -->
                <div class="row-contenido-bloques-generales remove-br es-Animado <?php echo $alineacion_contenido; ?>">

                    <div class="contenido-bloques-generales__wrapper <?php if ($video_imagen == 'sin_multimedia') { ?>full-width<?php } ?>">
                        <div class="contenido-bloques-generales__contenido">
                            <?php if ($detalle) { ?>
                                <div class="detalle"><?php echo $detalle; ?></div>
                            <?php } ?>

                            <?php if ($imagen_titulo) { ?>
                                <figure class="site__slider-tipo-informacion-figure">
                                    <img src="<?php echo $imagen_titulo['url']; ?>" alt="<?php echo $imagen_titulo['url']; ?>" class="site__slider-tipo-informacion-image">
                                </figure>
                            <?php } else { ?>
                                <div class="titulo">
                                    <h2><?php echo $titulo; ?></h2>
                                </div>
                            <?php } ?>

                            <?php if ($contenido) { ?>
                                <div class="contenido">
                                    <?php echo $contenido; ?>
                                </div>
                            <?php } ?>



                            <?php if (have_rows('enlaces_botonera')): ?>
                                <div class="botonera">
                                    <?php while (have_rows('enlaces_botonera')): the_row(); ?>
                                        <?php
                                        $link = get_sub_field('enlace');
                                        if ($link):
                                            $link_url = $link['url'];
                                            $link_title = $link['title'];
                                            $link_target = $link['target'] ? $link['target'] : '_self';
                                        ?>
                                            <a class="btn btn-primary w-arrow" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><span><?php echo esc_html($link_title); ?></span></a>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <?php if ($video_imagen == 'is_img') { ?>
                        <div class="contenido-bloques-generales__multimedia">
                            <figure class="bloques-generales__multimedia_figure">
                                <img src="<?php echo $imagen_destacada['url']; ?>" alt="<?php echo $imagen_destacada['alt']; ?>" class="bloques-generales__multimedia_img">
                            </figure>
                        </div>
                    <?php } elseif ($video_imagen == 'is_video') { ?>
                        <div class=" contenido-bloques-generales__multimedia">
                            <div class="ratio ratio-16x9 video-marco">
                                <iframe src="https://www.youtube.com/embed/<?php echo $idYouTube; ?>?rel=0" title="YouTube video" allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php } else {  ?>

                    <?php } ?>

                </div>
                <!--  -->
            <?php } ?>



        <?php endwhile; ?>

    <?php endif; ?>




</section>