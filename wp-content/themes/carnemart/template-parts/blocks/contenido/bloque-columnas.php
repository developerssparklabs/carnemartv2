<?php

$className = 'section-bloque-columnas';

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



<section class="site__section site__section-columnas <?php echo esc_attr($className); ?>" id="<?php echo $id_seccion; ?>" style="background-color:<?php echo $seccion_fondo_color; ?>!important;">

    <div class="site__section-row-custom-columnas">

        <?php if (have_rows('columna_item')): ?>

            <?php while (have_rows('columna_item')): the_row();
                $image = get_sub_field('imagen');
                $titulo = get_sub_field('titulo');
                $contenido = get_sub_field('contenido');
                $alineacion_del_titulo = get_sub_field('alineacion_del_titulo');
            ?>
                <div class="section-row-custom-columna es-Animado">

                    <?php if ($image) { ?>
                        <figure class="figure-responsiva">
                            <img src=<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" class="img-responsiva">
                        </figure>
                    <?php } ?>

                    <div class="contenido <?php echo $alineacion_del_titulo; ?>">
                        <?php if ($titulo) { ?>
                            <h4 class="<?php echo $alineacion_del_titulo; ?>"><?php echo $titulo; ?></h4>
                        <?php } ?>
                        <?php echo $contenido; ?>
                    </div>
                </div>
            <?php endwhile; ?>

        <?php endif; ?>




    </div>

</section>