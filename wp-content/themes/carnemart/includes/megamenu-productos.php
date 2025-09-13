<?php
// [wc_menu_categorias columns="6" hide_empty="1" orderby="name" order="ASC"]
add_shortcode('wc_menu_categorias', function ($atts) {
    if (!taxonomy_exists('product_cat')) return '';

    $a = shortcode_atts([
        'columns'    => 5,       // columnas en desktop
        'hide_empty' => 1,       // 1=oculta categorías vacías
        'orderby'    => 'name',  // name | slug
        'order'      => 'ASC',   // ASC | DESC
    ], $atts, 'wc_menu_categorias');

    $columns   = max(1, (int) $a['columns']);
    $hide      = (bool) (int) $a['hide_empty'];
    $orderby   = in_array($a['orderby'], ['name', 'slug'], true) ? $a['orderby'] : 'name';
    $order     = strtoupper($a['order']) === 'DESC' ? 'DESC' : 'ASC';


    // Padres
    $parents = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => 0,
        'hide_empty' => $hide,
        'orderby'    => $orderby,
        'order'      => $order,
    ]);
    if (is_wp_error($parents) || empty($parents)) return '';

    ob_start();
?>
    <div class="wc-cats-menu" style="--cols:<?php echo esc_attr($columns); ?>">
        <div class="wc-cats-menu__grid">
            <?php foreach ($parents as $parent):
                $children = get_terms([
                    'taxonomy'   => 'product_cat',
                    'parent'     => $parent->term_id,
                    'hide_empty' => $hide,
                    'orderby'    => $orderby,
                    'order'      => $order,
                ]);
                if (is_wp_error($children) || empty($children)) continue;
                $sec_id = 'panel-' . $parent->term_id;
            ?>
                <section class="wc-cats-menu__group" data-term-id="<?php echo esc_attr($parent->term_id); ?>">
                    <div class="wc-cats-menu__header">
                        <a class="wc-cats-menu__heading" href="<?php echo esc_url(get_term_link($parent)); ?>">
                            <?php echo esc_html($parent->name); ?>
                        </a>
                        <button class="wc-cats-menu__acc-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($sec_id); ?>" title="Mostrar/ocultar <?php echo esc_attr($parent->name); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                    <ul id="<?php echo esc_attr($sec_id); ?>" class="wc-cats-menu__list">
                        <?php foreach ($children as $child): ?>
                            <li class="wc-cats-menu__item">
                                <a href="<?php echo esc_url(get_term_link($child)); ?>"><?php echo esc_html($child->name); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
});
