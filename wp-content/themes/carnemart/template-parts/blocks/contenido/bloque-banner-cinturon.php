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
<section class="site__section site__section-banner-cinta <?php echo esc_attr($className); ?>"
    id="<?php echo esc_attr($id_seccion); ?>"
    style="background-color:<?php echo esc_attr($seccion_fondo_color); ?>!important;">

    <?php
    // ACF
    $imgD = get_field('banner_img_ancho_completo');         // desktop
    $imgM = get_field('banner_img_ancho_completo_mobile');  // mobile
    $link = get_field('enlace_ancho_completo');

    // IDs (si el campo devuelve URL, resolver a ID)
    $idD = !empty($imgD['ID']) ? (int)$imgD['ID'] : 0;
    $idM = !empty($imgM['ID']) ? (int)$imgM['ID'] : 0;
    if (!$idD && !empty($imgD['url'])) $idD = attachment_url_to_postid($imgD['url']);
    if (!$idM && !empty($imgM['url'])) $idM = attachment_url_to_postid($imgM['url']);

    // srcset / src
    $srcsetD = $idD ? wp_get_attachment_image_srcset($idD, 'full') : '';
    $srcsetM = $idM ? wp_get_attachment_image_srcset($idM, 'full') : '';
    $srcD    = !empty($imgD['url']) ? $imgD['url'] : ($idD ? wp_get_attachment_url($idD) : '');
    $srcM    = !empty($imgM['url']) ? $imgM['url'] : ($idM ? wp_get_attachment_url($idM) : '');

    // alt (fallback seguro)
    $altD = !empty($imgD['alt']) ? $imgD['alt'] : wp_strip_all_tags(get_the_title() . ' - banner');
    $altM = !empty($imgM['alt']) ? $imgM['alt'] : $altD;

    // <img> usará el mobile si existe (fallback desktop)
    $use_src = $srcM ?: $srcD;

    // ⬅️ Para width/height SIEMPRE usamos las dimensiones del asset desktop (si existe)
    $dims_id = $idD ?: $idM; // prioridad desktop
    $img_w = $img_h = null;
    if ($dims_id) {
        // 1) metadatos
        $meta = wp_get_attachment_metadata($dims_id);
        if (!empty($meta['width']) && !empty($meta['height'])) {
            $img_w = (int)$meta['width'];
            $img_h = (int)$meta['height'];
        } else {
            // 2) fallback
            $full = wp_get_attachment_image_src($dims_id, 'full');
            if ($full) {
                $img_w = (int)$full[1];
                $img_h = (int)$full[2];
            }
        }
    }

    // Envoltura (link seguro si target=_blank)
    $open  = '<div class="site__section-banner-cinta-enlace">';
    $close = '</div>';
    if (!empty($link['url'])) {
        $target = !empty($link['target']) ? $link['target'] : '_self';
        $rel    = ($target === '_blank') ? ' rel="noopener noreferrer"' : '';
        $open   = '<a class="site__section-banner-cinta-enlace" href="' . esc_url($link['url']) . '" target="' . esc_attr($target) . '"' . $rel . '>';
        $close  = '</a>';
    }
    echo $open;
    ?>

    <style>
        .site__section-banner-cinta-figure {
            width: 100%;
        }

        .site__section-banner-cinta-figure img {
            display: block;
            width: 100%;
            height: auto;
            /* responsive sin distorsión */
        }
    </style>

    <figure class="site__section-banner-cinta-figure">
        <picture>
            <?php if ($srcsetD || $srcD): ?>
                <source
                    media="(min-width: 768px)"
                    <?php if ($srcsetD): ?>
                    srcset="<?php echo esc_attr($srcsetD); ?>"
                    <?php else: ?>
                    srcset="<?php echo esc_url($srcD); ?>"
                    <?php endif; ?>
                    sizes="100vw">
            <?php endif; ?>

            <img
                class="site__section-banner-cinta-imagen cinta-imagen-desktop"
                src="<?php echo esc_url($use_src); ?>"
                <?php if (!empty($srcsetM)): ?>
                srcset="<?php echo esc_attr($srcsetM); ?>"
                sizes="100vw"
                <?php endif; ?>
                alt="<?php echo esc_attr($srcM ? $altM : $altD); ?>"
                <?php if ($img_w && $img_h): ?>
                width="<?php echo esc_attr($img_w); ?>"
                height="<?php echo esc_attr($img_h); ?>"
                <?php endif; ?>
                loading="lazy"
                decoding="async"
                fetchpriority="low">
        </picture>
    </figure>

    <?php echo $close; ?>
</section>