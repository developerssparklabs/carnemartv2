<?php wp_nav_menu(array(
  'theme_location' => 'menu-principal',
  'depth' => 3,
  'container_id' => 'site__menu-principal',
  'container_class' => '',
  'menu_class' => 'site__menu-principal',
  'add_a_class' => 'nav-link',
  'link_before' => '<span>',
  'link_after' => '</span>',
  // 'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
  // 'walker' => new wp_bootstrap_navwalker()
));
