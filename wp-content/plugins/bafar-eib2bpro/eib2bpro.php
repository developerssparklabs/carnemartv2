<?php

/**
 *  Bafar :: B2B Pro 🏆
 *
 *  Powerful and beautiful B2B & Wholesale solution for WooCommerce 
 *
 * @link              https://woocommerce-b2b-wholesale.com/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Bafar :: B2B Pro 🏆
 * Plugin URI:        https://woocommerce-b2b-wholesale.com/?s=wa&v=1.2.2
 * Description:       Se utiliza para catalogo multinivel y b2c b2b
 * Version:           2
 * Author:            ENERGY fork Sparklabs
 * Author URI:        https://woocommerce-b2b-wholesale.com
 * Text Domain:       eib2bpro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version and paths.
 */

define('EIB2BPRO_VERSION', '1.2.2');
define('EIB2BPRO_DIR', plugin_dir_path(__FILE__));
define('EIB2BPRO_PUBLIC', plugin_dir_url(__FILE__));

/**
 * Activate B2B Pro
 *
 * @return void
 * @since  1.0
 */

function eib2bpro_activate()
{
    require_once plugin_dir_path(__FILE__) . 'core/controller/activate.php';
    \EIB2BPRO\Core\Activate::activate();
}

/**
 * Deactivate B2B Pro
 *
 * @return void
 * @since  1.0
 */

function eib2bpro_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'core/controller/activate.php';
    \EIB2BPRO\Core\Activate::deactivate();
}

/**
 * Activation/Deactivation hooks
 */

register_activation_hook(__FILE__, 'eib2bpro_activate');
register_deactivation_hook(__FILE__, 'eib2bpro_deactivate');

/**
 * SPL Autoload
 *
 * @since    1.0.0
 */
spl_autoload_register(
    function ($class) {
        if (stripos($class, 'EIB2BPRO\\') === false) {
            return;
        }

        if (class_exists($class)) {
            return;
        }

        $class = strtolower($class);
        $segments = explode('\\', $class);

        $count = count($segments);

        $file = str_replace('eib2bpro', '', $class);
        $file = str_replace(array("_", '\\'), "/", $file);

        if (2 === $count) {
            $file = $segments[1] . '/' . $segments[1];
        }

        if (3 === $count) {
            $file = $segments[1] . '/controller/' . $segments[2];
        }
        if (3 < $count) {
            $file = $file;
        }

        $file = EIB2BPRO_DIR . $file . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
);

/**
 * Let's start.
 *
 * @since    1.0.0
 */

add_action('plugins_loaded', 'eib2bpro_plugins_loaded');

function eib2bpro_plugins_loaded()
{
    if (!class_exists('WooCommerce')) {
        return;
    }

    if (function_exists('icl_object_id')) {
        define('EIB2BPRO_SUPPRESS_FILTERS', 0);
    } else {
        define('EIB2BPRO_SUPPRESS_FILTERS', 1);
    }

    // i18n
    $mo_file = EIB2BPRO_DIR . 'languages/' . get_locale() . '.mo';

    if (!file_exists($mo_file)) {
        $mo_file = EIB2BPRO_DIR . 'languages/eib2bpro-' . get_locale() . '.mo';
    } elseif (!file_exists($mo_file)) {
        $mo_file = EIB2BPRO_DIR . '/plugins/eib2bpro-' . get_locale() . '.mo';
    }

    load_textdomain('eib2bpro', $mo_file);


    require_once EIB2BPRO_DIR . 'helpers/functions.php';

    add_filter('eib2bpro_apps', function ($apps) {
        return array(
            'dashboard' => array('title' => esc_html__('Dashboard', 'eib2bpro'), 'icon' => 'ri-dashboard-fill', 'eib2bpro' => true, 'order' => 1),
            'orders' => array('title' => esc_html__('Orders', 'eib2bpro'), 'icon' => 'ri-shopping-bag-3-fill', 'eib2bpro' => true, 'order' => 2),
            'products' => array('title' => esc_html__('Products', 'eib2bpro'), 'icon' => 'ri-stack-fill', 'eib2bpro' => true, 'order' => 3),
            'b2b' => array('title' => esc_html__('B2B Pro', 'eib2bpro'), 'icon' => 'ri-eiicons eib2bpro-icon-b2b-fill', 'eib2bpro' => true, 'order' => 4),
            'customers' => array('title' => esc_html__('Customers', 'eib2bpro'), 'icon' => 'ri-account-box-fill', 'eib2bpro' => true, 'order' => 5),
            'reports' => array('title' => esc_html__('Reports', 'eib2bpro'), 'icon' => 'ri-pie-chart-fill', 'eib2bpro' => true, 'order' => 6),
            'coupons' => array('title' => esc_html__('Coupons', 'eib2bpro'), 'icon' => 'ri-price-tag-2-fill', 'eib2bpro' => true, 'order' => 7),
            'comments' => array('title' => esc_html__('Comments', 'eib2bpro'), 'icon' => 'ri-chat-2-fill', 'eib2bpro' => true, 'order' => 8),
        );
    }, 100);

    add_action('eib2bpro_hourly_cron', '\EIB2BPRO\Core\Cron::run');

    if (is_admin()) {
        \EIB2BPRO\Admin::boot();
    } else {
        \EIB2BPRO\Admin\Site::boot();
    }
}
