<?php
// use \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use \Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Copyright: (c)  [2020] - Techspawn Solutions Private Limited ( contact@techspawn.com  ) 
 *  All Rights Reserved.
 * 
 * NOTICE:  All information contained herein is, and remains
 * the property of Techspawn Solutions Private Limited,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Techspawn Solutions Private Limited,
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Techspawn Solutions Private Limited
 *
 * @link              http://www.techspawn.com
 * @since             1.0.0
 * @package           Wcmlim
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Multi Locations Inventory Management
 * Plugin URI:        http://www.techspawn.com
 * Description:       This plugin will help you manage WooCommerce Products stocks through locations.
 * Version:           4.1.0
 * Requires at least: 4.9
 * Author:            Techspawn Solutions fork Sparklabs
 * Author URI:        http://www.techspawn.com
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wcmlim
 * Domain Path:       /languages
 * WC requires at least:	3.4
 * WC tested up to: 	5.8.0
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}
update_option('wcmlim_license', "valid");
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WCMLIM_VERSION', '3.5.9');
/**
 * Define default path and url for plugin.
 * 
 * @since    1.1.5
 */
define('WCMLIM_DIR_PATH', plugin_dir_path(__FILE__));
define('WCMLIM_URL_PATH', plugins_url('/', __FILE__));
define('WCMLIM_BASE', plugin_basename(__FILE__));
define('WCMLIM_SVALIDATOR', 'wcmlim_support_validater');

add_action("enqueue_block_editor_assets", "wcmlim_blocks_enqueue");

function wcmlim_blocks_enqueue()
{
	wp_enqueue_script('wcmlim-switch-block', plugin_dir_url(__FILE__) . 'admin/blocks/switch-block.js', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, true);

	wp_enqueue_script('wcmlim-popup-block', plugin_dir_url(__FILE__) . 'admin/blocks/popup-block.js', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, true);

	wp_enqueue_script('wcmlim-location-finder-block', plugin_dir_url(__FILE__) . 'admin/blocks/loc-finder-block.js', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, true);

	wp_enqueue_script('wcmlim-lflv-block', plugin_dir_url(__FILE__) . 'admin/blocks/lflv-block.js', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, true);

	wp_enqueue_script('wcmlim-locinfo-block', plugin_dir_url(__FILE__) . 'admin/blocks/locinfo-block.js', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, true);

	wp_enqueue_script('wcmlim-prod-by-id-block', plugin_dir_url(__FILE__) . 'admin/blocks/prod-by-id-block.js', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, true);

	wp_enqueue_style('wcmlim-popup-block', plugin_dir_url(__FILE__) . 'admin/css/wcmlim-popup-block.css', array('wp-blocks', 'wp-i18n', 'wp-editor'), true, false);
}

function wcmlim_block_category($categories)
{
	$custom_block = array(
		'slug' => 'amultilocation',
		'title' => 'Multilocations For WooCommerce'
	);
	$categories_sorted = array();
	$categories_sorted[0] = $custom_block;
	foreach ($categories as $category) {
		$categories_sorted[] = $category;
	}
	return $categories_sorted;
}
add_filter('block_categories', 'wcmlim_block_category', 10, 2);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wcmlim-activator.php
 */
function wcmlim_activate()
{
	$active_plugins = get_option('active_plugins');
	$wooactive_plugins = is_woocommerce_activated();
	$locationCookieTime = get_option('wcmlim_set_location_cookie_time');
	if ($locationCookieTime == '') {
		update_option('wcmlim_set_location_cookie_time', '30');
	}

	if (is_multisite()) {
		require_once plugin_dir_path(__FILE__) . 'includes/class-wcmlim-activator.php';
		Wcmlim_Activator::activate();
	} else {
		switch ($wooactive_plugins) {
			case 0:
				deactivate_plugins(__FILE__);
				$error_message = esc_html__('<WooCommerce has not yet been installed or activated. WooCommerce Multi Locations Inventory Management is a WooCommerce Extension that will only function if WooCommerce is installed. Please first install and activate the WooCommerce Plugin.', 'wcmlim');
				wp_die($error_message, 'Plugin dependency check', array('back_link' => true));
				break;
			default:
				$soldoutbutton_text = get_option('wcmlim' . '_soldout_button_text');
				if ($soldoutbutton_text != 'Agotado') {
					update_option('wcmlim' . '_soldout_button_text', 'Agotado');
				}

				$stockbutton_text = get_option('wcmlim' . '_instock_button_text');
				if ($stockbutton_text != 'En stock') {
					update_option('wcmlim' . '_instock_button_text', 'En stock');
				}

				$backorder_text = get_option('wcmlim' . '_onbackorder_button_text');
				if ($backorder_text != 'Disponible en pedido') {
					update_option('wcmlim' . '_onbackorder_button_text', 'Disponible en pedido');
				}

				require_once plugin_dir_path(__FILE__) . 'includes/class-wcmlim-activator.php';
				Wcmlim_Activator::activate();
				break;
		}
	}
}

add_action('admin_init', 'wcmlim_deactivate_self');
function wcmlim_deactivate_self()
{
	$plugins_dir = basename(dirname(__FILE__));
	$woocommerce_active_plugins = is_woocommerce_activated();
	if ($woocommerce_active_plugins == 0) {
		if (is_plugin_active($plugins_dir . '/wcmlim.php')) {

			deactivate_plugins($plugins_dir . '/wcmlim.php');
		}
	}
}

if (!function_exists('is_woocommerce_activated')) {
	function is_woocommerce_activated()
	{
		$active_plugins = get_option('active_plugins');
		$wooactive_plugins = 0;
		foreach ($active_plugins as $key => $value) {
			if (strpos($value, 'woocommerce.php') !== false) {
				$wooactive_plugins = 1;
			}
		}
		update_option('woocommerce_active_plugins', $wooactive_plugins);
		return $wooactive_plugins;
	}
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wcmlim-deactivator.php
 */
function wcmlim_deactivate()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-wcmlim-deactivator.php';
	Wcmlim_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'wcmlim_activate');
register_deactivation_hook(__FILE__, 'wcmlim_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wcmlim.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function wcmlim_run()
{
	$plugin = new Wcmlim();
	$plugin->run();
	$hpos_enabled = get_option('woocommerce_custom_orders_table_enabled');
	$wcmlim_hpos_enabled = get_option('wcmlim_hpos_enabled');
	if ($wcmlim_hpos_enabled == 'on' || $hpos_enabled == 'yes') {
		// Declare compatibility with WooCommerce features. KKW
		add_action(
			'before_woocommerce_init',
			function () {
				if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, positive_compatibility: true);
				}

				if (OrderUtil::custom_orders_table_usage_is_enabled()) {
					update_option('wcmlim_hpos_enabled', 'on');
				} else {
					update_option('wcmlim_hpos_enabled', 'off');
				}
			}

		);
	}
}

wcmlim_run();

/*
 * Include front end file when user access /stores URL
 */
if (preg_match('#^/tiendas/?(\?.*)?$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
	require_once plugin_dir_path(__FILE__) . 'includes/searchStore/index.php';
}

/*
 * Schedule Cron Job for Sync Locations JSON
 */
if (!class_exists('SyncLocationsJson')) {
	require_once plugin_dir_path(__FILE__) . 'cron/SyncLocationsJson.php';

	// Si no está programado aún, crear el evento cron
	if (!wp_next_scheduled('sync_locations_json_event')) {
		wp_schedule_event(time() + 30, 'daily', 'sync_locations_json_event');
		error_log("🛠️ Evento del cron registrado manualmente.");
	}

	// Registrar el hook del cron
	add_action('sync_locations_json_event', ['SyncLocationsJson', 'run']);

	// Registrar evento al activar plugin
	register_activation_hook(__FILE__, function () {
		if (!wp_next_scheduled('sync_locations_json_event')) {
			wp_schedule_event(time() + 300, 'daily', 'sync_locations_json_event'); // 5 minutos
		}
	});

	// Limpiar cron al desactivar
	register_deactivation_hook(__FILE__, function () {
		wp_clear_scheduled_hook('sync_locations_json_event');
	});
}

// Al activar el plugin, solo dejamos un "pendiente".
register_activation_hook(__FILE__, 'cm_queue_states_stores_build');

function cm_queue_states_stores_build(): void
{
	update_option('cm_build_states_stores_pending', 1, false);
}

// En la siguiente carga del admin, ejecutamos al final del ciclo.
add_action('wp_loaded', 'cm_run_states_stores_build_if_needed', PHP_INT_MAX);

function cm_run_states_stores_build_if_needed(): void
{
	// Solo en admin y evitando AJAX/CRON
	if (!is_admin() || wp_doing_ajax() || wp_doing_cron()) {
		return;
	}

	// ¿Hay tareas pendientes?
	if (!get_option('cm_build_states_stores_pending')) {
		return;
	}

	// (Opcional) pequeño guard-rail: asegúrate de que la clase existe.
	if (!class_exists('Wcmlim_Product_Taxonomy')) {
		return; // o require_once tu clase si procede
	}

	// Ejecuta las tareas ya con todo cargado.
	$wc = new Wcmlim_Product_Taxonomy();
	$wc->wcmlim_save_location_groups_json();
	$wc->wcmlim_resync_locator_meta_from_groups();
	$wc->wcmlim_generate_store_shards();

	// Borra el flag para que corra solo una vez.
	delete_option('cm_build_states_stores_pending');
}