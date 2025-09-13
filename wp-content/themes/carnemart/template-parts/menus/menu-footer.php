<div class="site__footer-columna-menu">
    <?php
    if (has_nav_menu('menu-footer')) {


        $locations = get_nav_menu_locations();
        $menu = wp_get_nav_menu_object($locations['menu-footer']);
        echo '<div class="footer__menu-title">' . wp_kses_post($menu->name) . '</div>';
    ?>
    <?php wp_nav_menu(array(
            'theme_location' => 'menu-footer',
            'depth' => 3,
            'container_id' => 'site__menu-footer',
            'container_class' => '',
            'menu_class' => 'site__menu-footer',
            'add_a_class' => 'nav-link',
            'link_before' => '<span>',
            'link_after' => '</span>',
            // 'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
            // 'walker' => new wp_bootstrap_navwalker()
        ));
    }
    ?>
</div>