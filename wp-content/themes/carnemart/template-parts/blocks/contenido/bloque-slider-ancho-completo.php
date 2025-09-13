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
<section class="site__section site__section-slider-tipo-banner-completo <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id_seccion); ?>" style="background-color:<?php echo esc_attr($seccion_fondo_color); ?>!important;">
    <div class="site__slider-tipo-banner-completo">
        <div class="slider-banner-completo">

            <?php if (have_rows('slider_destacado')): $i = 0; ?>
                <?php while (have_rows('slider_destacado')): the_row();
                    $img_slider_desktop = get_sub_field('img_slider_desktop'); // ACF image (array)
                    $img_slider_mobile  = get_sub_field('img_slider_mobile');  // ACF image (array)
                    $link_slider_item   = get_sub_field('link_slider_item');   // ACF link (array)

                    // IDs (preferidos)
                    $idD = !empty($img_slider_desktop['ID']) ? (int)$img_slider_desktop['ID'] : 0;
                    $idM = !empty($img_slider_mobile['ID'])  ? (int)$img_slider_mobile['ID']  : 0;
                    // Fallback si ACF devuelve URL
                    if (!$idD && !empty($img_slider_desktop['url'])) {
                        $idD = attachment_url_to_postid($img_slider_desktop['url']);
                    }
                    if (!$idM && !empty($img_slider_mobile['url'])) {
                        $idM = attachment_url_to_postid($img_slider_mobile['url']);
                    }

                    // Alt con fallback
                    $altD = !empty($img_slider_desktop['alt']) ? $img_slider_desktop['alt'] : (get_the_title() . ' - banner');
                    $altM = !empty($img_slider_mobile['alt'])  ? $img_slider_mobile['alt']  : $altD;

                    // Prioridad del primer slide
                    $is_first  = ($i === 0);
                    $loading   = $is_first ? 'eager' : 'lazy';
                    $priority  = $is_first ? 'high'  : 'low';

                    // Envoltura (link o div)
                    $open  = '<div class="site__slider-tipo-banner-completo-link">';
                    $close = '</div>';
                    if (!empty($link_slider_item['url'])) {
                        $link_url    = $link_slider_item['url'];
                        $link_target = !empty($link_slider_item['target']) ? $link_slider_item['target'] : '_self';
                        $open  = '<a class="site__slider-tipo-banner-completo-link" href="' . esc_url($link_url) . '" target="' . esc_attr($link_target) . '">';
                        $close = '</a>';
                    }

                    echo $open;
                ?>

                    <!-- Desktop -->
                    <figure class="site__slider-tipo-banner-completo-figure isElementDesktop">
                        <?php
                        if ($idD) {
                            echo wp_get_attachment_image(
                                $idD,
                                'full',
                                false,
                                [
                                    'class'         => 'site__slider-tipo-banner-completo-image',
                                    'alt'           => $altD,
                                    'loading'       => $loading,
                                    'decoding'      => 'async',
                                    'fetchpriority' => $priority,
                                ]
                            );
                        } elseif (!empty($img_slider_desktop['url'])) {
                            // Fallback si no hay ID
                        ?>
                            <img
                                class="site__slider-tipo-banner-completo-image"
                                src="<?php echo esc_url($img_slider_desktop['url']); ?>"
                                alt="<?php echo esc_attr($altD); ?>"
                                loading="<?php echo esc_attr($loading); ?>"
                                decoding="async"
                                fetchpriority="<?php echo esc_attr($priority); ?>">
                        <?php
                        }
                        ?>
                    </figure>

                    <!-- Mobile -->
                    <figure class="site__slider-tipo-banner-completo-figure isElementMobile">
                        <?php
                        if ($idM) {
                            echo wp_get_attachment_image(
                                $idM,
                                'full',
                                false,
                                [
                                    'class'         => 'site__slider-tipo-banner-completo-image',
                                    'alt'           => $altM,
                                    'loading'       => $loading,
                                    'decoding'      => 'async',
                                    'fetchpriority' => $priority,
                                ]
                            );
                        } elseif (!empty($img_slider_mobile['url'])) {
                            // Fallback si no hay ID
                        ?>
                            <img
                                class="site__slider-tipo-banner-completo-image"
                                src="<?php echo esc_url($img_slider_mobile['url']); ?>"
                                alt="<?php echo esc_attr($altM); ?>"
                                loading="<?php echo esc_attr($loading); ?>"
                                decoding="async"
                                fetchpriority="<?php echo esc_attr($priority); ?>">
                        <?php
                        }
                        ?>
                    </figure>

                <?php
                    echo $close;
                    $i++;
                endwhile; ?>
            <?php endif; ?>

        </div>
    </div>
</section>