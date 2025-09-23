<?php

/**
 * style
 *
 * @since      1.0.0
 * @author     EN.ER.GY <support@en.er.gy>
 * */

namespace EIB2BPRO\Reactors\style;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class style
{
    public static function settings()
    {
        $reactor = \EIB2BPRO\Reactors::list('style');

        $saved = 0;

        $screens = self::all_screens();

        if ($_POST) {
            if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'eib2bpro_reactors')) {
                exit;
            }

            eib2bpro_option('reactors-style-screens', eib2bpro_sanitize_array($_POST['reactors-style-screens']), 'set');

            eib2bpro_option('reactors-style-shadow', eib2bpro_post('reactors-style-shadow', '0'), 'set');
            eib2bpro_option('reactors-style-bg', eib2bpro_post('reactors-style-bg', '0'), 'set');
            eib2bpro_option('reactors-style-click', eib2bpro_post('reactors-style-click', '0'), 'set');

            $saved = 1;
        }

        $settings = eib2bpro_option('reactors-style-settings', array());

        echo eib2bpro_view('reactors', '*', 'style.view.settings', array('reactor' => $reactor, 'screens' => $screens, 'settings' => $settings, 'saved' => $saved));
    }

    public static function init()
    {
    }

    public static function all_screens()
    {
        return array(
            'edit-post'                    => esc_html__('Posts', 'eib2bpro'),
            'edit-page'                    => esc_html__('Pages', 'eib2bpro'),
            'upload'                       => esc_html__('Media', 'eib2bpro'),
            'attachment'                   => esc_html__('Edit Attachment', 'eib2bpro'),
            'edit-comments'                => esc_html__('Comments', 'eib2bpro'),
            'comment'                      => esc_html__('Comment > Edit', 'eib2bpro'),
            'edit-shop_order'              => esc_html__('Orders', 'eib2bpro'),
            'shop_order'                   => esc_html__('Orders > Edit', 'eib2bpro'),
            'edit-shop_coupon'             => esc_html__('Coupons', 'eib2bpro'),
            'shop_coupon'                  => esc_html__('Coupons > Edit', 'eib2bpro'),
            'edit-product'                 => esc_html__('Products', 'eib2bpro'),
            'product'                      => esc_html__('Products > Edit', 'eib2bpro'),
            'themes'                       => esc_html__('Themes', 'eib2bpro'),
            'theme-editor'                 => esc_html__('Theme Editor', 'eib2bpro'),
            'theme-install'                => esc_html__('Theme Install', 'eib2bpro'),
            'widgets'                      => esc_html__('Appearance > Widgets', 'eib2bpro'),
            'users'                        => esc_html__('Users', 'eib2bpro'),
            'user'                         => esc_html__('Users > Add New', 'eib2bpro'),
            'profile'                      => esc_html__('Users > Profile', 'eib2bpro'),
            'nav-menus'                    => esc_html__('Nav Menus', 'eib2bpro'),
            'plugins'                      => esc_html__('Plugins', 'eib2bpro'),
            'plugin-install'               => esc_html__('Plugins > Add New', 'eib2bpro'),
            'plugin-editor'                => esc_html__('Plugins > Editor', 'eib2bpro'),
            'woocommerce_page_wc-settings' => esc_html__('WooCommerce > Settings', 'eib2bpro'),
            'woocommerce_page_wc-reports'  => esc_html__('WooCommerce > Reports', 'eib2bpro'),
            'woocommerce_page_wc-status'   => esc_html__('WooCommerce > Status', 'eib2bpro'),
            'options-general'              => esc_html__('Options > General', 'eib2bpro'),
            'options-writing'              => esc_html__('Options > Writing ', 'eib2bpro'),
            'options-reading'              => esc_html__('Options > Reading ', 'eib2bpro'),
            'options-discussion'           => esc_html__('Options > Discussion ', 'eib2bpro'),
            'options-media'                => esc_html__('Options > Media', 'eib2bpro'),
            'options-permalink'            => esc_html__('Options > Permalink', 'eib2bpro'),
            'options-privacy'              => esc_html__('Options > Privacy', 'eib2bpro'),
            'tools'                        => esc_html__('Tools', 'eib2bpro'),
            'import'                       => esc_html__('Tools > Import', 'eib2bpro'),
            'export'                       => esc_html__('Tools > Export', 'eib2bpro'),
            'export-personal-data'         => esc_html__('Tools > Export Personal Data', 'eib2bpro'),
            'erase-personal-data'          => esc_html__('Tools > Erase Personal Data', 'eib2bpro'),
            'tools_page_action-scheduler'  => esc_html__('Tools > Action Scheduler', 'eib2bpro')
        );
    }

    public static function styles()
    {
        $screens = eib2bpro_option('reactors-style-screens', array_keys(self::all_screens()));

        if (isset(get_current_screen()->id) && in_array(get_current_screen()->id, $screens)) {
            wp_enqueue_style("eib2bpro-reactors-style", EIB2BPRO_PUBLIC . "reactors/style/public/style.css", null, EIB2BPRO_VERSION);
            wp_enqueue_script('eib2bpro-reactors-style', EIB2BPRO_PUBLIC . "reactors/style/public/style.js", array(), EIB2BPRO_VERSION, true);

            $css = ".wrap .search-box {display:none}";

            if (isset($_GET) && count($_GET) > 3) {
                $css .= ".__A__WP_searchbox {display:none} .tablenav.top {display:block} .wrap .search-box {display:block}";
            }

            if ("1" === eib2bpro_option('reactors-style-shadow', "1")) {
                $css .= ".wp-list-table > tbody {box-shadow: 0 0 15px 0 rgba(0,0,0,.05) !important;}";
            }

            if ("1" === eib2bpro_option('reactors-style-bg', "1")) {
                $css .= ".wp-list-table > tbody > tr td, .wp-list-table > tbody > tr th {background: transparent !important; }";
            }

            if ("1" === eib2bpro_option('reactors-style-click', "0")) {
                wp_localize_script('eib2bpro-reactors-style', 'eib2bpro_style', array('openclick' => 1));
                $css .= ".wp-list-table .row-actions {display: none;}";
            } else {
                wp_localize_script('eib2bpro-reactors-style', 'eib2bpro_style', array('openclick' => 0));
            }

            wp_add_inline_style('eib2bpro-reactors-style', $css);
        }
    }

    public static function deactivate()
    {
        // Remove options
        delete_option('eib2bpro_reactors-style-screens');
        delete_option('eib2bpro_reactors-style-bg');
        delete_option('eib2bpro_reactors-style-shadow');
        delete_option('eib2bpro_reactors-style-click');
    }
}
