<?php
// [slider_publicidad_cinturon]
function spc_slider_publicidad_cinturon_shortcode()
{
    if (! function_exists('have_rows')) return '';

    ob_start();
    $uid = uniqid('spc-');

    if (have_rows('slider_promocion_cinturon', 'option')) : ?>
        <div class="slider-promociones-cinturon-wrapper" id="<?php echo esc_attr($uid); ?>">

            <!-- Arrows -->
            <div class="custom-arrow-square-cinturon cas-prev" aria-label="Anterior" role="button" tabindex="0">
                <i class="bi bi-arrow-left-circle-fill" aria-hidden="true"></i>
            </div>
            <div class="custom-arrow-square-cinturon cas-next" aria-label="Siguiente" role="button" tabindex="0">
                <i class="bi bi-arrow-right-circle-fill" aria-hidden="true"></i>
            </div>

            <!-- Slider -->
            <div class="slider-promociones-cinturon">
                <?php while (have_rows('slider_promocion_cinturon', 'option')) : the_row();

                    // --- Imagen: acepta Array / ID / URL ---
                    $imgField = get_sub_field('imagen_slider');
                    $img_id = 0;
                    $src = '';
                    $width = '';
                    $height = '';
                    $alt = get_bloginfo('name');

                    if (is_array($imgField)) {
                        $img_id = isset($imgField['ID']) ? (int)$imgField['ID'] : 0;
                        $src    = isset($imgField['url']) ? $imgField['url'] : '';
                        $width  = isset($imgField['width']) ? (int)$imgField['width'] : '';
                        $height = isset($imgField['height']) ? (int)$imgField['height'] : '';
                        if (!empty($imgField['alt'])) $alt = $imgField['alt'];
                    } elseif (is_numeric($imgField)) {
                        $img_id = (int)$imgField;
                        $src    = wp_get_attachment_url($img_id);
                        $meta   = wp_get_attachment_metadata($img_id);
                        if ($meta && !empty($meta['width'])) {
                            $width = (int)$meta['width'];
                            $height = (int)$meta['height'];
                        }
                        $alt_meta = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                        if ($alt_meta) $alt = $alt_meta;
                    } elseif (is_string($imgField)) {
                        $src = $imgField; // URL directa
                    }

                    if (! $src) continue;

                    $srcset = $img_id ? wp_get_attachment_image_srcset($img_id) : '';
                    $sizes  = $img_id ? wp_get_attachment_image_sizes($img_id) : '100vw';

                    // --- Enlace: acepta Link (array) o URL (string) ---
                    $linkField = get_sub_field('enlace_slider');
                    $href = '';
                    $target = '_self';
                    $ariaLabel = $alt;

                    if (is_array($linkField)) {
                        $href   = isset($linkField['url']) ? $linkField['url'] : '';
                        $target = !empty($linkField['target']) ? $linkField['target'] : '_self';
                        if (!empty($linkField['title'])) $ariaLabel = $linkField['title'];
                    } elseif (is_string($linkField)) {
                        $href = trim($linkField);
                    }
                ?>
                    <div class="spc-slide">
                        <?php if ($href): ?>
                            <a href="<?php echo esc_url($href); ?>" target="<?php echo esc_attr($target); ?>"
                                <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                                aria-label="<?php echo esc_attr($ariaLabel); ?>">
                            <?php endif; ?>

                            <img
                                src="<?php echo esc_url($src); ?>"
                                <?php if ($srcset): ?>srcset="<?php echo esc_attr($srcset); ?>" sizes="<?php echo esc_attr($sizes); ?>" <?php endif; ?>
                                alt="<?php echo esc_attr($alt); ?>"
                                <?php if ($width && $height): ?>width="<?php echo esc_attr($width); ?>" height="<?php echo esc_attr($height); ?>" <?php endif; ?>
                                loading="lazy" decoding="async" fetchpriority="low" />

                            <?php if ($href): ?></a><?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div><!-- /.slider-promociones-cinturon -->
        </div><!-- /.wrapper -->

        <script>
            jQuery(function($) {
                var $wrap = $('#<?php echo esc_js($uid); ?>');
                var $slider = $wrap.find('.slider-promociones-cinturon');

                if ($slider.length && $.fn.slick) {
                    $slider.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: true,
                        arrows: true,
                        autoplay: true,
                        autoplaySpeed: 7000,
                        pauseOnHover: true,
                        pauseOnFocus: true,
                        adaptiveHeight: true,
                        prevArrow: $wrap.find('.cas-prev'),
                        nextArrow: $wrap.find('.cas-next'),
                        accessibility: true,
                        customPaging: function(slider, i) {
                            return '<button type="button" aria-label="Ir al slide ' + (i + 1) + '"></button>';
                        }
                    });
                }
            });
        </script>
<?php
    endif;

    return ob_get_clean();
}
add_shortcode('slider_publicidad_cinturon', 'spc_slider_publicidad_cinturon_shortcode');
