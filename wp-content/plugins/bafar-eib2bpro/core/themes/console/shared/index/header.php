<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>


<div id="eib2bpro-theme" class="eib2bpro-Segment_<?php echo strtolower(eib2bpro_get('app', 'dashboard')) ?>">
    <a id="trig2" href="javascript:;" class="eib2bpro-panel3 mobilemenu" hrefx="<?php echo eib2bpro_admin(''); ?>">
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
        <div class="eib2bpro-logo d-flex justify-content-center align-items-center">
            <?php
            $eib2bpro_img_src = wp_get_attachment_image_src(eib2bpro_option('logo'), 'full');
            if (!is_array($eib2bpro_img_src)) {
                $eib2bpro_img_src = array('');
            }
            ?>
            <a href="<?php echo admin_url("admin.php?page=eib2bpro") ?>"><img src="<?php echo esc_url($eib2bpro_img_src[0]) ?>"> </a>
        </div>
        <div class="eib2bpro-menu eib2bpro-eib2bpro_Menu_C d-flex flex-fill">
            <nav class="eib2bpro-MainMenu overflow-hidden">
                <ul class="eib2bpro-MenuH eib2bpro-main-menu eib2bpro-MainMenuH">
                    <?php echo \EIB2BPRO\Admin::generateMenu() ?>
                    <li class="more"><i class="ri-more-fill pl-2"></i>
                        <ul id="overflow">
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="float-right align-middle flex-fillx align-items-center eib2bpro-menu eib2bpro-My_Name">
            <nav class="eib2bpro-MainMenu">
                <ul class="eib2bpro-MenuH">
                    <li>
                        <a href="javascript:;">
                            <i class="ri-store-2-line eib2bpro-font-18"></i>
                        </a>
                        <ul class="eib2bpro-header-submenu">
                            <?php if ("1" === eib2bpro_option('feature-own_themes') || is_admin() || (!is_admin() && '1' === eib2bpro_option('reactors-tweaks-settings-woocommerce', 0))) { ?>
                                <li><a href="<?php echo eib2bpro_admin('settings'); ?>"><span class="eib2bpro-menu--textx"><?php esc_html_e('Settings', 'eib2bpro'); ?></span></a>
                                </li>
                            <?php } ?>
                            <li><a href="<?php echo esc_url_raw(get_bloginfo('url')) ?>" target="_blank"><?php esc_html_e('View Store', 'eib2bpro'); ?></a></li>
                            <?php if (!\EIB2BPRO\Admin::is_full()) { ?>
                                <li>
                                    <a href="<?php echo admin_url('index.php?no-ei'); ?>"><span class="eib2bpro-menu--textx"><?php esc_html_e('WP Admin', 'eib2bpro'); ?></span></a>
                                </li>
                            <?php } ?>
                            <li><a href="<?php echo get_edit_profile_url(); ?>"><span class="eib2bpro-menu--textx"><?php esc_html_e('Profile', 'eib2bpro'); ?></span></a>
                            </li>
                            <li><a href="<?php echo wp_logout_url(); ?>"><span class="eib2bpro-menu--textx"><?php esc_html_e('Log out', 'eib2bpro'); ?></span></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="float-right eib2bpro-My eib2bpro-My_Name d-flex align-items-center">
            <a href="javascript:;" class="eib2bpro-global-search-button"><span class="ri-search-line eib2bpro-font-18"></span></a>
        </div>
        <div class="eib2bpro-top-widgets-container  flex-fillx flex-nowrap flex-row-reverse">
            <?php echo \EIB2BPRO\Core\Top::render('', '', true) ?>
        </div>
    </div>
    <div class="eib2bpro-Site_Name">
        <div><?php echo get_bloginfo('name'); ?></div>
    </div>