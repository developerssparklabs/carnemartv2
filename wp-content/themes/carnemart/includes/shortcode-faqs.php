<?php
// [faqs tipo="general|ganamas" abrir="0|1"]  (lee de ACF Options)
add_shortcode('faqs', function ($atts) {
    $atts = shortcode_atts([
        'tipo'  => 'general',
        'abrir' => '0',          // 1 = abrir primera
    ], $atts, 'faqs');

    // Normaliza tipo
    $tipo = strtolower(trim($atts['tipo']));
    $tipo = sanitize_title($tipo);
    if (in_array($tipo, ['ganamaslana', 'gana-mas', 'gana_mas'])) $tipo = 'ganamas';
    if (!in_array($tipo, ['general', 'ganamas'])) $tipo = 'general';

    // Flag abrir
    $abrir = in_array(strtolower((string)$atts['abrir']), ['1', 'true', 'yes', 'si', 'sí'], true) ? '1' : '0';

    $ctx = 'option';
    if (!have_rows('preguntas_faq', $ctx)) return '';

    ob_start(); ?>
    <div class="wrapper-faqs" data-abrir="<?php echo esc_attr($abrir); ?>">
        <?php while (have_rows('preguntas_faq', $ctx)) : the_row();
            $tipo_val = get_sub_field('tipo_pregunta_faq');
            if (is_array($tipo_val)) $tipo_val = $tipo_val['value'] ?? ($tipo_val['label'] ?? '');
            $tipo_val = sanitize_title((string)$tipo_val);
            if (in_array($tipo_val, ['ganamaslana', 'gana-mas', 'gana_mas'])) $tipo_val = 'ganamas';
            if ($tipo_val !== $tipo) continue;

            $preg = (string) get_sub_field('pregunta_faq');
            $resp = (string) get_sub_field('respuesta_faq');
            if ($preg === '' && $resp === '') continue; ?>
            <div class="faq-item">
                <button class="faq-btn" type="button">
                    <h3 class="faq-titulo"><?php echo esc_html($preg); ?></h3>
                    <span class="faq-chevron" aria-hidden="true"></span>
                </button>
                <div class="faq-contenido">
                    <div class="faq-espacio">
                        <?php echo apply_filters('the_content', $resp); ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php return ob_get_clean();
});
