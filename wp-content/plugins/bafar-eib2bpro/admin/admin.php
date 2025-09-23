<?php

/**
 * Admin Class
 */

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Admin
{
    public static $theme = 'one';
    public static $menu_hash;
    public static $api;

    /**
     * Boot
     *
     * @return void
     */
    public static function boot()
    {

        self::$theme = eib2bpro_option('theme', 'one');

        add_action('admin_init', '\EIB2BPRO\Admin::init');
        add_action('admin_menu', '\EIB2BPRO\Admin::admin_menu');
        add_action('admin_head', '\EIB2BPRO\Admin::admin_head', 10);
        add_action('in_admin_header', '\EIB2BPRO\Admin::in_admin_header', 10);
        add_action('woocommerce_api_pagination_headers', 'eib2bpro_api_pagination', 10, 2);
        add_action('admin_enqueue_scripts', '\EIB2BPRO\Admin::styles');

        add_action('wp_ajax_eib2bpro', '\EIB2BPRO\Admin::ajax');
        add_action('wp_ajax_eib2bpro_public', '\EIB2BPRO\Admin::ajax_public');
        add_action('wp_ajax_nopriv_eib2bpro_public', '\EIB2BPRO\Admin::ajax_public');

        add_filter('admin_body_class', '\EIB2BPRO\Admin::admin_body_class', 10, 1);
        add_filter('admin_title', '\EIB2BPRO\Core\Notifications::title', 10, 1);
        add_filter('comment_edit_redirect', '\EIB2BPRO\Admin::filter_comment_edit_redirect', 10, 2);

        \EIB2BPRO\B2b\Admin\Main::hooks();

        if ('eib2bpro' === eib2bpro_get('page') && eib2bpro_get('perpage')) {
            eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), intval(eib2bpro_get('perpage', 10)), 'set');
        }
    }

    /**
     * Initial
     *
     * @since 1.0.0
     */

    public static function init()
    {
        global $pagenow;

        /* Check if WooCommerce is activated. */
        if (!class_exists('WooCommerce')) {
            return;
        }

        if (
            $pagenow === 'index.php'
            && !is_network_admin()
            && 0 === count($_GET)
            && (self::is_full() || (!self::is_full() && 1 === eib2bpro_option('autostart', 0)))
        ) {
            wp_redirect(admin_url('admin.php?page=eib2bpro&app=' . eib2bpro_option('landing', 'dashboard')));
        }
    }

    /**
     * Admin page 
     *
     * @return void
     */

    public static function admin_menu()
    {
        add_menu_page('B2B Pro', 'B2B Pro', 'manage_woocommerce', 'eib2bpro', 'EIB2BPRO\Admin::admin_page', 'dashicons-plus-alt');
    }

    /**
     * Show pages of admin panel
     *
     * @return void
     */

    public static function admin_page()
    {
        $app = eib2bpro_get('app');

        if (!$app) {
            if (0 === eib2bpro_option('b2b_enable_admin_panel', 1)) {
                wp_redirect(eib2bpro_admin('b2b', []));
            } else {
                wp_redirect(eib2bpro_admin('dashboard', []));
            }
        }

        switch ($app) {
            case 'go':

                if ($url = eib2bpro_get('in')) {
                    if (!wp_verify_nonce(eib2bpro_get('asnonce'), 'eib2bpro-security')) {
                        die(esc_html__('Failed on security check', 'eib2bpro'));
                    }

                    $url = esc_url_raw(urldecode($url));

                    if (strpos($url, admin_url()) !== false && strpos($url, admin_url()) === 0) {
                        eib2bpro_frame(eib2bpro_get('in'));
                    } else {
                        esc_html_e("Restricted Area.", 'eib2bpro');
                        wp_die();
                    }
                } else {
                    $go = sanitize_key(eib2bpro_get('to'));

                    self::generateMenu(array('hash' => true));

                    if (!isset(self::$menu_hash[$go])) {
                        esc_html_e("Restricted Area.", 'eib2bpro');
                        wp_die();
                    }
                    echo eib2bpro_view('core', 0, 'shared.index.frame', array('page' => esc_url(self::$menu_hash[$go][0])));
                }

                break;
            default:
                if (!empty($app)) {
                    $class = '\EIB2BPRO\\' . $app;
                    $class::boot();
                }
                break;
        }
    }

    /**
     * Generate and show the main menu
     *
     * @param array $args
     * @return void
     */

    public static function generateMenu($args = array())
    {
        global $submenu;

        $user = wp_get_current_user();
        $apps = apply_filters('eib2bpro_apps', array());
        $roles = (array)$user->roles;

        if (is_network_admin()) {
            $roles = ['administrator'];
        }

        foreach ($roles as $role) {
            $menu = eib2bpro_option('menu_' . $role, false);
            if ($menu) {
                break;
            }
        }

        foreach ($apps as $app_id => $app) {
            if (true === $app['eib2bpro']) {
                if (!isset($menu[$app_id])) {
                    $menu[$app_id]['order'] = $app['order'];
                    $menu[$app_id]['active'] = 1;
                    $menu[$app_id][6] = $app['icon'];
                }
                $menu[$app_id][0] = $app['title'];
                $menu[$app_id][2] = eib2bpro_admin($app_id);
                $menu[$app_id]['app'] = $app_id;
                $menu[$app_id]['admin_link'] = 'eib2bpro&app=' . $app_id;
                $menu[$app_id]['eib2bpro'] = 1;
            }
        }

        $next_index = 100;

        // Add all menus to EIB2BPRO Menu
        if (isset($GLOBALS['menu'])) {
            foreach ($GLOBALS['menu'] as $key => $value) {
                ++$next_index;

                if (isset($value[5]) && $value[5] !== "toplevel_page_eib2bpro") {
                    $menu_id = md5($value[2]);
                    if (!isset($menu[$menu_id])) {
                        $menu[$menu_id]['order'] = $next_index;
                        $menu[$menu_id]['active'] = 1;
                    }

                    $menu[$menu_id][0] = $value[0];
                    $menu[$menu_id][2] = $value[2];
                    if (!isset($menu[$menu_id][6])) {
                        $menu[$menu_id][6] = $value[6];
                    }
                    $menu[$menu_id]['other'] = 1;
                    $menu[$menu_id]['admin_link'] = $value[2];
                }
            }
        }
        if (isset($submenu)) {
            foreach ($submenu as $parent => $___menu) {
                $menu_id = md5($parent);
                foreach ($___menu as $key => $__menu) {
                    if (isset($menu[$menu_id])) {
                        $menu[$menu_id]['submenu'][$key] = $__menu;
                    }
                }
            }
        }

        $output = array();

        foreach ($menu as $_m_k => $_m) {
            if (isset($_m['admin_link']) && isset($submenu[$_m['admin_link']])) {
                foreach ($submenu[$_m['admin_link']] as $sublink_key => $sublink) {
                    if (false === stripos($sublink[2], '.')) {
                        $sublink[2] = 'admin.php?page=' . $sublink[2];
                    }

                    if (!empty(get_plugin_page_hook($sublink[2], $_m['admin_link'])) or ('index.php' !== $sublink[2] && file_exists(WP_PLUGIN_DIR . "/" . $sublink[2]))) {
                        $sublink[2] = admin_url('admin.php?page=' . $sublink[2]);
                    }

                    if (false !== stripos($sublink[2], 'customize.php')) {
                        $sublink[2] = 'customize.php';
                    }

                    if (false !== stripos($sublink[2], 'index.php')) {
                        $sublink[2] = 'index.php?dashboard=yes';
                    }

                    self::$menu_hash[md5($sublink[2])] = array($sublink[2], strip_tags($_m[0]) . ' - ' . $sublink[0]);

                    if (!self::is_full()) {
                        $sublink[2] = eib2bpro_admin('go', array('to' => md5($sublink[2])));
                    }
                    $_m["submenu"][$sublink_key] = $sublink;
                }
            }
            if ('x' === substr($_m_k, 0, 1)) {
                $_m['admin_link'] = $_m[2];
            }

            if (isset($_m['admin_link'])) {
                self::$menu_hash[md5($_m['admin_link'])] = array($_m['admin_link'], $_m[0]);

                if ('index.php' === $_m['admin_link']) {
                    $_m['admin_link'] = 'index.php?dashboard=yes';
                }
                if (isset($_m['other']) && false !== stripos($_m['admin_link'], '.')) {
                    if (!empty(get_plugin_page_hook($_m['admin_link'], "admin.php")) or ('index.php' !== $_m['admin_link'] && file_exists(WP_PLUGIN_DIR . "/" . $_m['admin_link']))) {
                        $_m['admin_link'] = admin_url('admin.php?page=' . $_m['admin_link']);
                    } else {
                        $_m['admin_link'] = $_m['admin_link'];
                    }
                } else {
                    if (isset($_m['target']) && 1 === intval($_m['target'])) {
                        $_m['admin_link'] = $_m['admin_link'];
                    } elseif (isset($_m['target']) && 2 === intval($_m['target'])) {
                        $_m['admin_link'] = '[EIB2BPRO-DIVIDER]';
                    } elseif (false === stripos($_m['admin_link'], '.')) {
                        self::$menu_hash[md5($_m['admin_link'])] = array($_m['admin_link'], $_m[0]);
                        $_m['admin_link'] = admin_url('admin.php?page=' . $_m['admin_link']);
                    } else {
                        self::$menu_hash[md5($_m['admin_link'])] = array($_m['admin_link'], $_m[0]);
                        $_m['admin_link'] = eib2bpro_admin('go', array('to' => md5($_m['admin_link'])));
                    }
                }

                if (!self::is_full() && isset($_m['other'])) {
                    self::$menu_hash[md5($_m['admin_link'])] = array($_m['admin_link'], $_m[0]);
                    $_m['admin_link'] = eib2bpro_admin('go', array('to' => md5($_m['admin_link'])));
                }
            }

            if (eib2bpro_is_ajax()) {
                $output[$_m_k] = $_m;
            } else {
                if (isset($_m['admin_link'])) {
                    $output[$_m_k] = $_m;
                }
            }
        }

        array_multisort(array_map(function ($element) {
            return $element['order'];
        }, $output), SORT_ASC, $output);

        /* Check if WooCommerce is activated. */
        if (class_exists('WooCommerce')) {
            /* Badges */
            $output['orders']['badge'] = wc_orders_count('on-hold') + wc_orders_count('processing') + wc_orders_count('pending');
        }

        $output['comments']['badge'] = intval(wp_count_comments()->moderated);
        $output['b2b']['badge'] = eib2bpro_option('badge-b2b', 0);

        if (isset($args['hash'])) {
            return;
        }

        return eib2bpro_view('core', 0, 'shared.index.menu', array('_eib2bpro_menu' => $output));
    }

    /**
     * admin_body_class filter
     *
     * @since  1.0.0
     */

    public static function admin_body_class($classes)
    {
        if (self::is_ei()) {
            $classes .= ' eib2bpro-engine';
        } else {
            $classes .= ' eib2bpro-no-engine';
        }

        if (self::is_full()) {
            $classes .= ' eib2bpro-full';
        } else {
            $classes .= ' eib2bpro-half';
        }

        $classes .= ' eib2bpro-theme-' . self::$theme;

        return esc_attr($classes) . " eib2bpro-admin-" . esc_attr(eib2bpro_get('app', 'dashboard')) . " eib2bpro-action-" . esc_attr(eib2bpro_get('action', 'default')) . " eib2bpro-section-" . esc_attr(eib2bpro_get('section', 'default'));
    }

    /**
     * Panel styles
     *
     * @return void
     */

    public static function styles()
    {
        // Ajax & i18n for ei
        $JSvars['ajax_url'] = admin_url('admin-ajax.php');
        $JSvars['admin_url'] = admin_url();
        $JSvars['asnonce'] = wp_create_nonce('eib2bpro-security');
        $JSvars['refresh'] = absint(eib2bpro_option('refresh', 60)) * 1000;
        $JSvars['current_app'] = eib2bpro_get('app', 'none');

        $JSvars['enable'] = array(
            'ui_instant_search' => intval(eib2bpro_option('ui_instant_search', 1)),
            'ui_quick_link' => intval(eib2bpro_option('ui_quick_link', 0))
        );

        $JSvars['i18n'] = array(
            'wait' => esc_html__('Please wait', 'eib2bpro'),
            'done' => esc_html__('Done', 'eib2bpro'),
            'save' => esc_html__('Save', 'eib2bpro'),
            'saved' => esc_html__('Saved', 'eib2bpro'),
            'saving' => esc_html__('Saving', 'eib2bpro'),
            'are_you_sure' => esc_html__('Are you sure?', 'eib2bpro')
        );
        $JSvars['theme_panel_width'] = eib2bpro_option('theme_panel_width', '1090px');
        $JSvars['remap_widgets'] = eib2bpro_get('remap_dashboard_widgets', 0);


        if ((self::is_full()) or (!self::is_full() && self::is_ei())) {
            // Styles
            if (self::is_ei()) {
                wp_enqueue_style("bootstrap", EIB2BPRO_PUBLIC . "core/public/3rd/bootstrap/4.5.3/css/bootstrap.min.css", null, EIB2BPRO_VERSION);
            } else {
                wp_enqueue_style("eib2bpro-font", "//fonts.googleapis.com/css?family=Noto+Sans:400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,devanagari,greek,greek-ext,latin-ext,vietnamese");
                wp_enqueue_style("bootstrap-lite", EIB2BPRO_PUBLIC . "core/public/css/bootstrap-lite.css", null, EIB2BPRO_VERSION);
            }

            // App specific styles
            if ('go' !== eib2bpro_get('app', 'core')) {
                wp_enqueue_style("eib2bpro-" . eib2bpro_get('app', 'core'), EIB2BPRO_PUBLIC . "" . eib2bpro_get('app', 'core') . "/public/" . eib2bpro_get('app', 'core') . ".css", null, EIB2BPRO_VERSION);
            }
            // Bootstrap scripts
            wp_enqueue_script("bootstrap", EIB2BPRO_PUBLIC . "core/public/3rd/bootstrap/4.5.3/js/bootstrap.bundle.min.js", array("jquery", 'jquery-ui-sortable'), EIB2BPRO_VERSION, true);

            // Icons
            wp_enqueue_style("remixicon", EIB2BPRO_PUBLIC . "core/public/3rd/icons/remix/remixicon.css");

            // Shared styles
            wp_enqueue_style("eib2bpro-app", EIB2BPRO_PUBLIC . "core/public/css/app.css", null, EIB2BPRO_VERSION);
            wp_enqueue_style("eib2bpro-theme", EIB2BPRO_PUBLIC . "core/themes/" . esc_attr(self::$theme) . "/public/theme.css", null, EIB2BPRO_VERSION);

            // Standalone styles if the EIB2BPRO Panel disabled
            if (0 === eib2bpro_option('b2b_enable_admin_panel', 1)) {
                wp_enqueue_style("eib2bpro-standalone", EIB2BPRO_PUBLIC . "core/public/css/standalone.css", null, EIB2BPRO_VERSION);
            }

            // 3rd party libraries
            wp_enqueue_script("jscroll", EIB2BPRO_PUBLIC . "core/public/3rd/jscroll.js", array('jquery'), EIB2BPRO_VERSION, true);
            wp_enqueue_script("bindwithdelay", EIB2BPRO_PUBLIC . "core/public/3rd/bindwithdelay.js", array('jquery'), EIB2BPRO_VERSION, true);
            wp_enqueue_script("slidereveal", EIB2BPRO_PUBLIC . "core/public/3rd/slidereveal.js", array('jquery'), EIB2BPRO_VERSION, true);
            wp_enqueue_script("sortable-animation", EIB2BPRO_PUBLIC . "core/public/3rd/sortable-animation.js", array('jquery', 'jquery-ui-sortable'), EIB2BPRO_VERSION, true);
            wp_enqueue_script("odometer", EIB2BPRO_PUBLIC . "core/public/3rd/odometer.js", array('jquery'), EIB2BPRO_VERSION, true);
            wp_enqueue_script("apexcharts", EIB2BPRO_PUBLIC . "core/public/3rd/apexcharts.js", array("jquery"), EIB2BPRO_VERSION, true);
            wp_enqueue_script("jquery-key", EIB2BPRO_PUBLIC . "core/public/3rd/jquery.key.js", array("jquery"), EIB2BPRO_VERSION, true);

            // admin scripts
            wp_enqueue_script("eib2bpro-admin", EIB2BPRO_PUBLIC . "core/public/js/admin.js", array(), EIB2BPRO_VERSION, true);
            wp_enqueue_script("eib2bpro-orders", EIB2BPRO_PUBLIC . "orders/public/orders.js", array("jquery"), EIB2BPRO_VERSION, true);

            if (self::is_ei()) {
                $app_class = sanitize_key(eib2bpro_get('app', false));
                if ($app_class && 'go' !== $app_class) {
                    $app_class = '\EIB2BPRO\\' . $app_class;
                    $app_class::scripts();
                }
            }

            // FONT
            $fonts = array(
                'one' => "//fonts.googleapis.com/css?family=Noto+Sans:400,700&display=swap&subset=cyrillic,cyrillic-ext,devanagari,greek,greek-ext,latin-ext,vietnamese",
                'console' => "//fonts.googleapis.com/css?family=Source+Sans+Pro:400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese"
            );

            if (eib2bpro_option('font', false)) {
                $font = eib2bpro_option('font', 'Noto+Sans');
                wp_enqueue_style("eib2bpro-font2", "//fonts.googleapis.com/css?family=" . esc_attr($font) . ":400,700,900&display=swap&subset=cyrillic,cyrillic-ext,devanagari,greek,greek-ext,latin-ext,vietnamese");
                wp_add_inline_style('eib2bpro-font2', '#eib2bpro-theme,#notifications,#eib2bpro-Ajax_Notification,#eib2bpro-search-1--overlay {font-family: "' . str_replace(array(':400', '+'), array('', ' '), $font) . '"}');
            } else {
                wp_enqueue_style("eib2bpro-font", esc_url($fonts[self::$theme]));
            }

            // colors 
            $colors = eib2bpro_option('colors', \EIB2BPRO\Settings\Theme::defaultColors()['ffffff'], 'get');

            $colors_css = ":root{";
            foreach ($colors as $k => $v) {
                $colors_css .= esc_html("--$k: $v;");
            }
            $colors_css .= "}";

            if ("1" === eib2bpro_option('tweaks-screenoptions', "0")) {
                $colors_css .= '#screen-meta-links {display: block !important;position: absolute !important;bottom: 15px;right: 5vw;top: unset !important;} #screen-meta {margin-top: -9px;}#screen-meta-links .show-settings {border-top: 1px solid #ccd0d4; border-radius:inherit;}';
            }

            $inline_css = '
             .btnA { margin-bottom: ' . esc_attr(eib2bpro_option('theme_card_margin', -1)) . 'px !important; 
             border-radius: ' . esc_attr(eib2bpro_option('theme_card_radius', 0)) . 'px !important;  
             padding: ' . esc_attr(eib2bpro_option('theme_card_padding', 30)) . 'px !important;
             padding-left: ' . esc_attr((intval(eib2bpro_option('theme_card_padding', 30)) - 10)) . 'px !important;
             padding-right: ' . esc_attr((intval(eib2bpro_option('theme_card_padding', 30)) - 10)) . 'px !important;
             ';

            if (30 > eib2bpro_option('theme_card_padding', 30)) {
                $inline_css .= 'padding-left: 25px !important;
             padding-right: 30px !important;';
            }

            $inline_css .= '}';

            $inline_css .= '
            .p-30 {
                padding: ' . esc_attr(eib2bpro_option('theme_card_padding', 30)) . 'px;
                padding-left: ' . esc_attr((intval(eib2bpro_option('theme_card_padding', 30))) - 15) . 'px;
             }
            .pl-5.p-30 {padding-left:1.85rem !important} .p-30.pl-3 {padding-left:15px !important}';


            if (-1 < eib2bpro_option('theme_card_margin', -1)) {
                $inline_css .= '
                .eib2bpro-list-container, #eib2bpro-products-1 .eib2bpro-List_M1, .eib2bpro-Products_Sortable, .eib2bpro-Coupons_Container, .eib2bpro-Orders_Container, .eib2bpro-Comments_Container, .eib2bpro-Customers_Container { box-shadow: none !important; }
            .eib2bpro-List_M1 .eib2bpro-Item.collapsed, .btnA {box-shadow: 0 0 15px 0 rgb(0 0 0 / 5%) !important;}
            ';
            }

            if ('one' === eib2bpro_option('theme', 'one')) {
                $inline_css .= '#eib2bpro-header .eib2bpro-menu a {padding-top:' . esc_attr(eib2bpro_option('theme_menu_padding', 10)) . 'px; padding-bottom:' . esc_attr(eib2bpro_option('theme_menu_padding', 10)) . 'px; padding-left: 8px; }';
                $inline_css .= '#eib2bpro-header .eib2bpro-MainMenuV li.more {padding-top:' . esc_attr(eib2bpro_option('theme_menu_padding', 10)) . 'px; padding-bottom:' . esc_attr(eib2bpro_option('theme_menu_padding', 10)) . 'px; padding-left:1.2em; }';
                $inline_css .= '#eib2bpro-header .eib2bpro-menu>li>a>.eib2bpro-menu--text  {margin-left:-' . esc_attr(eib2bpro_option('theme_menu_padding', 10)) . 'px }';
                $inline_css .= '#eib2bpro-header .eib2bpro-menu >li>a> .eib2bpro-menu--text {display:' . (0 === eib2bpro_option('theme_menu_text', 0) ? 'none' : 'block') . '}';
                $inline_css .= '.eib2bpro-custom-icon,#eib2bpro-header .eib2bpro-menu .dashicons-before::before {font-size:' . esc_attr(eib2bpro_option('theme_menu_icon_size', 20)) . 'px}';
            }

            $inline_css .= wp_strip_all_tags(eib2bpro_option('custom_css', ''));

            wp_add_inline_style('eib2bpro-theme', $inline_css . $colors_css);

            if (\EIB2BPRO\Reactors\Main::is_installed('style')) {
                \EIB2BPRO\Reactors\style\style::styles();
            }

            wp_localize_script('eib2bpro-admin', 'eiB2BProGlobal', $JSvars);
        }

        wp_enqueue_style("eib2bpro-b2b-wp", EIB2BPRO_PUBLIC . "b2b/public/b2b-wp.css", null, EIB2BPRO_VERSION);
        wp_enqueue_style("selectize", EIB2BPRO_PUBLIC . "core/public/3rd/selectize/selectize.bootstrap4.css", null, EIB2BPRO_VERSION);
        wp_enqueue_script("eib2bpro-b2b-wp", EIB2BPRO_PUBLIC . "b2b/public/b2b-wp.js", array("jquery"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("eib2bpro-b2b", EIB2BPRO_PUBLIC . "b2b/public/b2b.js", array("jquery", "selectize"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("selectize", EIB2BPRO_PUBLIC . "core/public/3rd/selectize/selectize.min.js", array("jquery"), EIB2BPRO_VERSION, true);

        wp_localize_script('eib2bpro-b2b-wp', 'eiB2BProWPGlobal', $JSvars);
    }

    /**
     * Is user in EIB2BPRO page?
     *
     * @since  1.0.0
     */

    public static function is_ei()
    {
        return (isset($_GET["page"]) and ($_GET["page"] === 'eib2bpro' || stripos($_GET["page"], 'eib2bpro_') !== false)) ? true : false;
    }

    /**
     * Is EIB2BPRO in Full Mode?
     */

    public static function is_full()
    {
        $user = get_userdata(get_current_user_id());

        if (is_network_admin()) {
            return false;
        }

        if (!empty($user->roles) && is_array($user->roles)) {
            foreach ($user->roles as $role) {
                if (1 === eib2bpro_option('full-' . $role, 0)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Is current user admin?
     *
     * @return boolean
     */

    public static function is_admin()
    {
        return current_user_can('manage_options');
    }


    /**
     * Get all available roles
     *
     * @return array
     */

    public static function roles()
    {
        $roles = array();
        $capabilities = apply_filters('eib2bpro_required_capabilities', array('manage_woocommerce'), 1);
        $_roles = wp_roles();

        foreach ($_roles->roles as $id => $role) {
            foreach ($capabilities as $cap) {
                if (isset($role['capabilities'][$cap]) && 1 === (int)$role['capabilities'][$cap]) {
                    $roles[$id] = $role;
                }
            }
        }
        return $roles;
    }

    /**
     * Get available users can use EIB2BPRO
     *
     * @param array $roles
     * @param boolean $name
     * @return array
     */

    public static function users($roles = [], $name = false)
    {
        $users = array();
        $result = array();

        $args = array(
            'role' => 'administrator',
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );
        $users = get_users($args);

        $args = array(
            'role' => 'shop_manager',
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        );
        $users = array_merge($users, (array)get_users($args));

        foreach ($users as $user) {
            $avatar = get_user_meta($user->ID, 'campfire-avatar', true);

            $result[$user->ID] = ['id' => $user->ID, 'name' => $user->display_name];

            // avatar
            if ($avatar) {
                $image = wp_get_attachment_image_src(intval($avatar), 'full');
                if (is_array($image) && isset($image[1])) {
                    $display = esc_url($image[0]);
                    $display = '<div class="eib2bpro-Avatar-c"><img class="eib2bpro-Avatar img-fluid" src="' . esc_url($display) . '"></div>';
                }
            } else {
                $display = '<div class="eib2bpro-Avatar-c eib2bpro-Avatar-e"><span>' . substr($user->display_name, 0, 1) . '</span></div>';
            }

            $result[$user->ID]['avatar'] = $display;
        }

        return $result;
    }

    /**
     * Ajax router
     *
     * @return void
     */

    public static function ajax()
    {
        $app = eib2bpro_post('app', false);

        if (!$app) {
            $app = eib2bpro_get('app', false);
        }

        check_admin_referer('eib2bpro-security', 'asnonce');

        if (!wp_verify_nonce($_REQUEST['asnonce'], 'eib2bpro-security')) {
            die(esc_html__('Failed on security check', 'eib2bpro'));
        }

        eib2bpro_class($app, 'ajax', 'run');
    }

    /**
     * Ajax router for public
     */

    public static function ajax_public()
    {

        $app = eib2bpro_post('app', false);

        if (!$app) {
            $app = eib2bpro_get('app', false);
        }

        if (!check_admin_referer('eib2bpro-security', 'nonce')) {
            die(esc_html__('Failed on security check', 'eib2bpro'));
        }

        eib2bpro_class($app, 'ajax', 'public');
    }

    /**
     * Starts Woocommece API
     *
     * @since  1.0.0
     */

    public static function wc_engine()
    {
        \WC()->api->includes();
        \WC()->api->register_resources(new \WC_API_Server('/'));
    }

    /**
     * Filter for redirection after comment update
     *
     * @since  1.0.0
     */

    public static function filter_comment_edit_redirect($location, $comment_id)
    {

        if (0 < stripos($_POST['referredby'], 'eib2bpro')) {
            return admin_url("edit-comments.php");
        } else {
            return $location;
        }
    }

    /**
     * Header for admin
     *
     * @return void
     */
    public static function in_admin_header()
    {
        if ((self::is_full()) or (!self::is_full() && self::is_ei())) {
            echo eib2bpro_view('core', 0, 'shared.index.header');
        }
    }


    /**
     * Hides WP elements in ei panel
     *
     * @since  1.0.0
     */

    public static function admin_head()
    {

        // When does a POST action, we refresh ei page
        if ($_POST) {
            echo '<script>"use strict"; window.parent.refreshOnClose=1;</script>';
        }

        if ((self::is_full()) or (!self::is_full() && self::is_ei())) {
            echo eib2bpro_view('core', 0, 'shared.index.footer');
        }

        // Hides some WP styles when it is loaded from ei iframe
        echo '<script>
        "use strict";
        var EIB2BPRO_Window = 1; // Necessary global scope with unique prefix
        if (self!==top && window.parent.EIB2BPRO_Window !== null && window.parent.EIB2BPRO_Window !== undefined) {
          document.write("<style> \
          body{background: transparent} \
          html.wp-toolbar {padding-top: 0 !important;} \
          #wpbody { width: 100% !important; padding-left:0px !important; padding-top: 0px !important; } \
          body.woocommerce-embed-page #wpbody { margin-top:0px; width: 100% !important; padding-left:0px !important; padding-top: 0px !important; } \
          .post-type-shop_order.post-php .page-title-action, .update-nag,#eib2bpro-header, .eib2bpro-header-top, .eib2bpro-header-top-container, #trig2, .eib2bpro-Site_Name {display:none !important;} \
          #adminmenuback,#adminmenuwrap,#screen-meta-links,#wpadminbar,#woocommerce-embedded-root,.woocommerce-layout__header{display: none !important;} \
          body:not(.eib2bpro-engine) #wpbody-content  { margin-right: 0px; margin-left: 0px; padding-top: 0px; width:100%; } \
          body.woocommerce-page:not(.eib2bpro-engine) #wpbody-content  { margin-right: 0px; margin-left: 0px; padding-top: 20px; width:100%; } \
          body:not(.eib2bpro-engine).eib2bpro-theme-console #wpbody-content, body.woocommerce-page:not(.eib2bpro-engine) #wpbody-content  { margin-right: 0px; margin-left: 0px; padding-top: 0px; margin-top:0px;width:100%; } \
          body:not(.eib2bpro-engine).eib2bpro-theme-console #wpbody-content, body.eib2bpro-theme-console.woocommerce-page:not(.eib2bpro-engine) #wpbody-content  { margin-right: 0px; margin-left: 0px; padding-top: 0px; margin-top:0px;width:100%; } \
          body:not(.eib2bpro-engine).rtl #wpbody {margin-right: 0px !important} \
          body.eib2bpro-engine #wpbody {margin: 0 auto 0 0px;} \
          .woocommerce-embed-page .wrap { padding-top:0px !important; } \
          .woocommerce-embed-page #wpbody .woocommerce-layout { padding-top:0px !important; } \
          .eib2bpro-theme-console.woocommerce-embed-page .wrap { padding-top:0px !important; } \
          .branch-5-4.auto-fold .block-editor-editor-skeleton {top:0px !important; left:0px !important;} \
          .woocommerce-embed-page:not(.edit-tags-php) #wpbody .woocommerce-layout, .woocommerce-embed-page:not(.edit-tags-php) .woocommerce-layout__notice-list-hide+.wrap { padding-top:0px !important; height:0px !important; } \
          @media (max-width: 782px) { \
            #wpbody { padding-top: 0px; } \
            body:not(.eib2bpro-engine) #wpbody { padding-top: 0px !important; } \
            .woocommerce-table__table {width:88vw !important} \
            .woocommerce table.form-table .select2-container, .woocommerce table.form-table input[type=text], .select2-container{width:80vw !important; max-width:80vw !important;min-width:100px !important} \
            .woocommerce_order_items_wrapper {width:85vw !important; max-width:90vw !important} \
            .woocommerce-layout__primary { \
              margin-top: 0px; \
            } \
          }';

        if ("1" === eib2bpro_option('reactors-tweaks-screenoptions', "0")) {
            echo '#screen-meta-links {display: block !important;position: absolute !important;bottom: 15px;right: 5vw;top: unset !important;z-index:99999} #screen-meta {margin-top: -9px;}#screen-meta-links .show-settings {border-top: 1px solid #ccd0d4; border-radius:inherit;}';
        }

        echo '@media (min-width: 782px) { \
            #footer, #wpcontent {margin-left : 0 !important;padding-left: 0 !important;} \
            .rtl #footer, .rtl #wpcontent {margin-right : 0 !important;padding-left: 0 !important;} \
            .rtl #wpcontent { margin-right: 0px; } \
            .woocommerce-embed-page .wrap {padding:00px 0px 0px 0px; width:90%} \
            .woocommerce-layout__primary { margin-left: 0; margin-top: 50px; } \
            .wrap { margin:0 auto !important; width: 90%; padding-top:25px } \
            body.auto-fold .edit-post-layout__content, .edit-post-header {margin-left:0px !important; left: 0 !important;} \
            .woocommerce-layout__primary{margin-top: 10px !important; padding-top:0px !important;} \
            .update-nag a {color: #353535 !important;} \
          } \
          \
          ::-webkit-scrollbar {width: 8px;height: 8px; background-color: rgb(245, 245, 245); }\
          ::-webkit-scrollbar:hover { background-color: rgba(0, 0, 0, 0.09); }\
          ::-webkit-scrollbar-thumb { background : rgb(230, 230, 230);-webkit-border-radius: 100px; } \
          ::-webkit-scrollbar-thumb:active { background : rgba(0,0,0,0.61); -webkit-border-radius: 100px; } \
          </style>");
          jQuery(document).ready(function(jQuery){if (jQuery(".inbrowser--loading", window.parent.document).length>0) { jQuery(".inbrowser--loading", window.parent.document).removeClass("d-flex").addClass("hidden").css("display", "none !important"); jQuery("#eib2bpro-frame", window.parent.document).show(); } jQuery(".button,.submitdelete").on("click",function() {window.parent.refreshOnClose=1;console.log("refreshOnClose")})});
        }</script>';


    }
}
