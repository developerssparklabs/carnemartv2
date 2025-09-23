<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="eib2bpro-Container_Fluid">
    <div id="eib2bpro-theme" class="eib2bpro-app-<?php echo strtolower(eib2bpro_get('app', 'dashboard')) ?>">
        <a id="trig2" class="eib2bpro-panel3 mobilemenu" href="javascript:;">
            <div class="d-flex align-items-center">
                <svg version="1.1" id="eib2bpro-Mobile_Menu_Icon" x="0px" y="0px" viewBox="0 0 384.97 384.97" xml:space="preserve">
                    <g>
                        <g id="Menu_1_">
                            <path d="M12.03,120.303h360.909c6.641,0,12.03-5.39,12.03-12.03c0-6.641-5.39-12.03-12.03-12.03H12.03 c-6.641,0-12.03,5.39-12.03,12.03C0,114.913,5.39,120.303,12.03,120.303z" />
                            <path d="M372.939,180.455H12.03c-6.641,0-12.03,5.39-12.03,12.03s5.39,12.03,12.03,12.03h360.909c6.641,0,12.03-5.39,12.03-12.03 S379.58,180.455,372.939,180.455z" />
                            <path d="M372.939,264.667H132.333c-6.641,0-12.03,5.39-12.03,12.03c0,6.641,5.39,12.03,12.03,12.03h240.606 c6.641,0,12.03-5.39,12.03-12.03C384.97,270.056,379.58,264.667,372.939,264.667z" />
                        </g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                        <g></g>
                    </g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                    <g></g>
                </svg>
            </div>
        </a>

        <div id="eib2bpro-header">

            <nav class="eib2bpro-MainMenu vertical">
                <ul class="eib2bpro-menu eib2bpro-main-menu eib2bpro-MainMenuV <?php if ("1" === eib2bpro_option('reactors-tweaks-icon-text', "0")) {
                                                                    echo " eib2bpro-With_Text";
                                                                } ?>">
                    <?php echo \EIB2BPRO\Admin::generateMenu() ?>
                    <li class="more d-none">
                        <span class="eib2bpro-custom-icon ri-menu-4-fill"></span>
                        <ul id="overflow">
                        </ul>
                    </li>
                </ul>
            </nav>
            </ul>

            <?php
            $eib2bpro_img_src = wp_get_attachment_image_src(eib2bpro_option('logo'), 'full');
            if (!is_array($eib2bpro_img_src)) {
                $eib2bpro_img_src = array('');
            }
            ?>
            <nav class="eib2bpro-MainMenu vertical eib2bpro-My fixed-bottom">
                <ul class="eib2bpro-menu">
                    <li class="text-center">
                        <img src="<?php echo esc_url($eib2bpro_img_src[0]) ?>" class="eib2bpro-Main_Logo">
                        <ul>
                            <?php if (\EIB2BPRO\Admin::is_admin()) { ?>
                                <li>
                                    <a href="<?php echo eib2bpro_admin('settings') ?>"><?php esc_html_e('Settings', 'eib2bpro'); ?></a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="<?php echo esc_url_raw(get_bloginfo('url')) ?>" target="_blank"><?php esc_html_e('View Store', 'eib2bpro'); ?></a>
                            </li>
                            <?php if (!\EIB2BPRO\Admin::is_full()) { ?>
                                <li>
                                    <a href="<?php echo admin_url('index.php?no-ei'); ?>"><?php esc_html_e('WP Admin', 'eib2bpro'); ?></a>
                                </li>
                            <?php } ?>
                            <li>
                                <a href="<?php echo wp_logout_url(); ?>"><?php esc_html_e('Logout', 'eib2bpro'); ?></a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

        </div>

        <div class="eib2bpro-header-top-container">
            <div class="eib2bpro-gp">
                <div class="row">
                    <div class="col-8">
                        <div class="eib2bpro-search">
                            <input type="text" class="eib2bpro-search-box eib2bpro-search-input" data-close-on-empty="1" placeholder="<?php esc_html_e('Search in orders, customers, products...', 'eib2bpro') ?>">
                        </div>
                    </div>
                    <div class="col-4 text-right eib2bpro-Top_Right">
                        <div class="eib2bpro-top-widgets-container">
                            <?php echo \EIB2BPRO\Core\Top::render() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="eib2bpro-Site_Name">
            <div><?php echo get_bloginfo('name'); ?></div>
        </div>