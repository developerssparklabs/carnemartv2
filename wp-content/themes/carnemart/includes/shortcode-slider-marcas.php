<?php
// Shortcode: [slider_marcas]
add_shortcode('slider_marcas', function () {
    if (!function_exists('have_rows')) return ''; // requiere ACF

    $uid = uniqid('slider_marcas_'); // id único por instancia
    ob_start();

    if (have_rows('elemento_marca', 'option')): ?>
        <div id="<?php echo esc_attr($uid); ?>" class="slider-slick-marcas">
            <?php while (have_rows('elemento_marca', 'option')): the_row();

                $nombre = trim((string) get_sub_field('nombre_marca'));
                $logo   = get_sub_field('logo_marca'); // imagen (array/ID/URL)
                $enlace = get_sub_field('enlace');     // link (array) o string

                // --- IMAGEN (ID/URL/srcset/alt) ---
                $img_id = 0;
                if (is_array($logo) && !empty($logo['ID'])) {
                    $img_id = (int) $logo['ID'];
                } elseif (is_numeric($logo)) {
                    $img_id = (int) $logo;
                } elseif (is_array($logo) && !empty($logo['url'])) {
                    $img_id = attachment_url_to_postid($logo['url']);
                }

                $src    = '';
                $srcset = '';
                $alt    = '';
                if ($img_id) {
                    $src    = wp_get_attachment_image_url($img_id, 'large');
                    $srcset = wp_get_attachment_image_srcset($img_id, 'full') ?: '';
                    $alt    = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                } elseif (is_array($logo) && !empty($logo['url'])) {
                    $src = $logo['url'];
                    $alt = !empty($logo['alt']) ? $logo['alt'] : '';
                }

                // Alt fallback
                if ($alt === '' && $nombre !== '') $alt = $nombre . ' - logo';
                if ($alt === '') $alt = 'Logo';

                // --- ENLACE ---
                $href   = '';
                $target = '_blank';
                if (is_array($enlace) && !empty($enlace['url'])) {
                    $href   = $enlace['url'];
                    $target = !empty($enlace['target']) ? $enlace['target'] : '_self';
                } elseif (is_string($enlace) && $enlace !== '') {
                    $href = $enlace;
                }
                $rel = ($target === '_blank') ? 'noopener noreferrer' : '';

                if (!$src) continue; // sin imagen no pintamos item
            ?>
                <div class="slide-slick-marca-item slick-slide">
                    <?php if ($href): ?>
                        <a href="<?php echo esc_url($href); ?>" target="<?php echo esc_attr($target); ?>" <?php echo $rel ? 'rel="' . esc_attr($rel) . '"' : ''; ?>>
                        <?php endif; ?>

                        <?php if ($nombre): ?>
                            <span class="slide-slick-marca_nombre"><?php echo esc_html($nombre); ?></span>
                        <?php endif; ?>

                        <figure class="slide-slick-marca_figure">
                            <picture>
                                <img
                                    class="slide-slick-marca_img"
                                    src="<?php echo esc_url($src); ?>"
                                    <?php if ($srcset): ?>
                                    srcset="<?php echo esc_attr($srcset); ?>"
                                    sizes="100vw"
                                    <?php endif; ?>
                                    alt="<?php echo esc_attr($alt); ?>"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low">
                            </picture>
                        </figure>

                        <?php if ($href): ?>
                        </a><?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Init Slick solo para esta instancia -->
        <script>
            jQuery(function($) {
                var $el = $('#<?php echo esc_js($uid); ?>');
                if (!$el.length) return;
                if ($el.hasClass('slick-initialized')) return; // evita doble init

                var opts = <?php echo wp_json_encode([
                                'slidesToShow'   => 4,
                                'slidesToScroll' => 1,
                                'infinite'       => true,
                                'autoplay'       => true,
                                'autoplaySpeed'  => 99000,
                                'arrows'         => true,
                                'dots'           => false,
                                'lazyLoad'       => 'ondemand',
                                'responsive'     => [
                                    ['breakpoint' => 1200, 'settings' => ['slidesToShow' => 4]],
                                    ['breakpoint' => 992,  'settings' => ['slidesToShow' => 4]],
                                    ['breakpoint' => 768,  'settings' => ['slidesToShow' => 3]],
                                    ['breakpoint' => 480,  'settings' => ['slidesToShow' => 2, 'slidesToScroll' => 2 ]],
                                ],
                            ]); ?>;

                $el.slick(opts);
            });
        </script>
<?php
    endif;

    return ob_get_clean();
});
