<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<?php $x = -1;
foreach ($_eib2bpro_menu as $eib2bpro_menu_k => $eib2bpro_menu) {
?>
    <?php if (isset($eib2bpro_menu['active']) && 1 === (int)$eib2bpro_menu['active']) { ?>
        <li id="eib2bpro-menu-app-<?php echo esc_attr($eib2bpro_menu_k) ?>" title="<?php echo esc_html(strip_tags($eib2bpro_menu[0])) ?>" <?php if (isset($eib2bpro_menu['app']) && eib2bpro_get("app") === $eib2bpro_menu['app']) {
                                                                                                                                                echo "class='eib2bpro-menu--selected'";
                                                                                                                                            } ?>>
            <?php if (isset($eib2bpro_menu['admin_link'])) { ?>
                <a href="<?php echo esc_url($eib2bpro_menu['admin_link']) ?>" <?php if (isset($eib2bpro_menu['target'])) {
                                                                                    if (2 === intval($eib2bpro_menu['target'])) {
                                                                                        echo " class='eib2bpro-menu-divider'";
                                                                                    } else if (0 === intval($eib2bpro_menu['target'])) {
                                                                                        echo "";
                                                                                    } else {
                                                                                        echo " target='_blank'";
                                                                                    }
                                                                                } ?>>
                <?php } else { ?>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=eib2bpro&app=" . isset($eib2bpro_menu['app']) ?: 'dashboard')) ?>">
                    <?php } ?>
                    <?php if (isset($eib2bpro_menu['target']) && 2 === intval($eib2bpro_menu['target'])) {
                        echo '<div class="eib2bpro-menu-divider-in"></div>';
                    } ?>
                    <?php if (false !== stripos($eib2bpro_menu[6], 'fa') or false !== stripos($eib2bpro_menu[6], 'mtrl') or false !== stripos($eib2bpro_menu[6], 'ri')) { ?>
                        <div class="dashicons-before svg">
                            <span class='eib2bpro-Custom_Icon_Container eib2bpro-custom-icon <?php eib2bpro_a(eib2bpro_option('theme_menu_iconset', 'material-icons')) ?>  <?php echo esc_attr(str_replace('-fill', str_replace('remixicons', '', eib2bpro_option('theme_menu_iconset', 'remixicons-fill')), $eib2bpro_menu[6])) ?>'>
                            </span>
                        </div>
                    <?php } elseif ('dashicons-admin-generic' === $eib2bpro_menu[6] or false === stripos($eib2bpro_menu[6], 'dashicons') or (false !== stripos($eib2bpro_menu[6], '//'))) { ?>
                        <div class="dashicons-before svg"><span class='eib2bpro-Custom_Icon_Container eib2bpro-menu--empty-icon'><?php echo esc_html(substr($eib2bpro_menu[0], 0, 2)); ?></span>
                        </div>
                    <?php } else { ?>
                        <div class="eib2bpro-Custom_Icon_Container dashicons-before <?php echo esc_attr($eib2bpro_menu[6]); ?>"><?php if ("" === $eib2bpro_menu[6]) {
                                                                                                                                    echo "<span class='eib2bpro-menu--empty-icon'>" . esc_html(substr($eib2bpro_menu[0], 0, 2)) . "</span>";
                                                                                                                                } ?></div>
                    <?php } ?>
                    <?php if (isset($eib2bpro_menu['badge']) && $eib2bpro_menu['badge'] > 0) { ?>
                        <span class="badge badge-pill badge-danger eib2bpro-Menu_Badge"><?php echo absint($eib2bpro_menu['badge']); ?></span>
                    <?php } ?>
                    <div class="eib2bpro-menu--text"><?php echo wp_kses_post($eib2bpro_menu[0]) ?></div>
                    </a>
                    <?php if (isset($eib2bpro_menu['submenu'])) { ?>
                        <ul class="eib2bpro-header-submenu">
                            <?php foreach ($eib2bpro_menu['submenu'] as $sub) { ?>
                                <li><a href="<?php echo esc_url($sub[2]) ?>">
                                        <span class="eib2bpro-menu--textx"><?php echo wp_kses_post($sub[0]) ?></span>
                                    </a></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
        </li>
    <?php } ?>
<?php
} ?>