<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-menu-2">
    <div class="eib2bpro-Scroll">
        <ul>
            <?php foreach (\EIB2BPRO\Settings::menu() as $settings_menu_group_id => $settings_menu_group) { ?>
                <li class="eib2bpro-menu-2-group">
                    <div class="text-uppercase">
                        <?php echo esc_html($settings_menu_group['title']) ?>
                    </div>
                    <ul>
                        <?php foreach ((array)$settings_menu_group['menu'] as $settings_menu_id => $settings_menu) { ?>
                            <li class="eib2bpro-menu-2-item<?php eib2bpro_a($settings_menu_id === eib2bpro_get('section') || $settings_menu_id === eib2bpro_get('id')  ? ' eib2bpro-Selected' : '') ?>">
                                <a href="<?php
                                            if ('apps' === $settings_menu_group_id) {
                                                echo eib2bpro_admin('settings', ['section' => 'app', 'id' => $settings_menu_id]);
                                            } elseif (isset($settings_menu['href'])) {
                                                echo esc_url($settings_menu['href']);
                                            } else {
                                                echo eib2bpro_admin('settings', ['section' => $settings_menu_id]);
                                            } ?>">
                                    <i class="<?php echo esc_attr($settings_menu['icon']) ?>"></i>
                                    <span class="eib2bpro-menu-2-item-title"><?php echo esc_html($settings_menu['title']) ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>