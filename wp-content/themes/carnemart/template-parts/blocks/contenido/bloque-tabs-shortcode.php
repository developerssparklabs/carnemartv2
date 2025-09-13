<?php

$className = 'section-shortcode-tabs';

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


<section class="site__section bloque-contenido-shortcode <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id_seccion); ?>" style="background-color:<?php echo esc_attr($seccion_fondo_color); ?>!important;">



    <div class="row-shortcode-section <?php the_field('distribucion_contenido'); ?> AOFFF">



        <?php
        if (have_rows('tabs_site_custom')):
            echo '<div class="tabs_site_aog">';
            echo '<ul class="nav nav-tabs" role="tablist">';

            $index = 0;
            while (have_rows('tabs_site_custom')) : the_row();
                $titleDirecto = get_sub_field('tab_title');
                $title = get_sub_field('tab_title');
                $id = sanitize_title($title); // Sanitiza el ID
                $active = $index === 0 ? 'active' : '';

                echo '<li class="nav-item" role="presentation">
                <button class="nav-link ' . $active . '" id="tab-' . $id . '" data-bs-toggle="tab" data-bs-target="#content-' . $id . '" type="button" role="tab">' . $titleDirecto . '</button>
              </li>';

                $index++;
            endwhile;
            echo '</ul>';

            echo '<div class="tab-content">';

            $index = 0;
            while (have_rows('tabs_site_custom')) : the_row();
                $title = get_sub_field('tab_title');
                $id = sanitize_title($title);
                $active = $index === 0 ? 'active' : '';

                echo '<div class="tab-pane fade show ' . $active . '" id="content-' . $id . '" role="tabpanel">'; ?>


                <?php if (get_sub_field('tab_shortcode_content') == "show_content") { ?>

                    <!-- Contenido de tab -->
                    <div class="tab_custom_content">

                        <?php
                        $tab_content_title = get_sub_field('tab_content_title');
                        $tab_content_content = get_sub_field('tab_content_content');
                        ?>

                        <?php if ($tab_content_title) { ?>
                            <h3><?php echo $tab_content_title; ?></h3>
                        <?php } ?>

                        <?php if ($tab_content_content) { ?>
                            <div><?php echo $tab_content_content; ?></div>
                        <?php } ?>

                    </div>
                    <!-- Contenido de tab -->

                <?php } elseif (get_sub_field('tab_shortcode_content') == "show_shortcode") { ?>


                    <div class="shortcode-wrapper">
                        <?php
                        $shortcode = get_sub_field('tab_content_shortcode');

                        if (!empty($shortcode)) {
                            echo do_shortcode($shortcode);
                        }
                        ?>
                    </div>


                <?php } else { ?>


                <?php } ?>

                <div class="box-botonera">
                    <div class="botonera mt-4">

                        <?php
                        $link = get_sub_field('tab_content_btn');
                        if ($link):
                            $link_url = $link['url'];
                            $link_title = $link['title'];
                            $link_target = $link['target'] ? $link['target'] : '_self';
                        ?>
                            <a class="btn btn-solid" href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>"><span><?php echo esc_html($link_title); ?></span></a>
                        <?php endif; ?>

                    </div>
                </div>




        <?php echo '</div>';

                $index++;
            endwhile;

            echo '</div>'; // Cierra .tab-content
            echo '</div>'; // Cierra .tabs_site_aog
        else:
            echo '<p>No hay pestañas disponibles.</p>';
        endif;
        ?>

</section>