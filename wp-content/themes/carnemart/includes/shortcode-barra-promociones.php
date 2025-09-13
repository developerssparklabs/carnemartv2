<?php
// Shortcode: [barra_promociones class="barra-promociones"]
add_shortcode('barra_promociones', function ($atts) {
    $atts = shortcode_atts([
        'class' => 'barra-promociones',
    ], $atts, 'barra_promociones');

    // Si no hay filas, no renderizamos nada
    if (! have_rows('banners_barra', 'option')) {
        return '';
    }

    ob_start(); ?>
    <section class="<?php echo esc_attr($atts['class']); ?>">
        <?php while (have_rows('banners_barra', 'option')) : the_row();

            // Subfields
            $img  = get_sub_field('imagen_banner');   // ACF image (array o ID)
            $link = get_sub_field('enlace_banner');   // ACF link: ['url','title','target']

            // Normalizar imagen
            $img_id  = is_array($img) ? ($img['ID'] ?? null) : (is_numeric($img) ? (int)$img : null);
            $img_alt = is_array($img) ? ($img['alt'] ?? '') : '';
            $img_alt = $img_alt ?: ($img_id ? get_post_meta($img_id, '_wp_attachment_image_alt', true) : '');

            // Generar la <img> (size full + decoding async + lazy)
            if ($img_id) {
                $img_html = wp_get_attachment_image(
                    $img_id,
                    'full',
                    false,
                    [
                        'class'     => 'barra-promociones__image',
                        'alt'       => $img_alt,
                        'loading'   => 'lazy',
                        'decoding'  => 'async',
                    ]
                );
            } elseif (is_array($img) && ! empty($img['url'])) {
                $img_html = sprintf(
                    '<img src="%s" alt="%s" class="barra-promociones__image" loading="lazy" decoding="async">',
                    esc_url($img['url']),
                    esc_attr($img_alt)
                );
            } else {
                // Sin imagen, saltamos el item
                continue;
            }

            // Normalizar link
            $has_link   = (is_array($link) && ! empty($link['url']));
            $link_url   = $has_link ? $link['url'] : '';
            $link_title = $has_link ? ($link['title'] ?: '') : '';
            $link_tgt   = $has_link ? ($link['target'] ?: '_self') : '_self';

            // Accesibilidad
            $aria_label = $link_title ?: $img_alt ?: 'Banner';
        ?>
            <div class="barra-promociones__item">
                <?php if ($has_link) : ?>
                    <a
                        href="<?php echo esc_url($link_url); ?>"
                        class="barra-promociones__link"
                        title="<?php echo esc_attr($link_title); ?>"
                        aria-label="<?php echo esc_attr($aria_label); ?>"
                        target="<?php echo esc_attr($link_tgt); ?>"
                        <?php echo $link_tgt === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                        <?php echo $img_html; ?>
                    </a>
                <?php else : ?>
                    <?php echo $img_html; ?>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </section>
<?php
    return ob_get_clean();
});
