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
 * Plugin Name:       Bafar :: WooCommerce Multi Locations Inventory Management 🏦
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

// dd_action("enqueue_block_editor_assets", "wcmlim_blocks_enqueue");

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
	/**
	 * Check if WooCommerce is active
	 **/

	$locationCookieTime = get_option('wcmlim_set_location_cookie_time');
	if ($locationCookieTime == '') {
		update_option('wcmlim_set_location_cookie_time', '30');
	}

	if (is_multisite()) {
		require_once plugin_dir_path(__FILE__) . 'includes/class-wcmlim-activator.php';
		Wcmlim_Activator::activate();
	} else {
		if ($wooactive_plugins == 0) {
			deactivate_plugins(__FILE__);
			$error_message = esc_html_e('WooCommerce has not yet been installed or activated. WooCommerce Multi Locations Inventory Management is a WooCommerce Extension that will only function if WooCommerce is installed. Please first install and activate the WooCommerce Plugin.', 'wcmlim');
			wp_die($error_message, 'Plugin dependency check', array('back_link' => true));
		} else {

			$soldoutbutton_text = get_option('wcmlim' . '_soldout_button_text');
			if ($soldoutbutton_text == false) {
				update_option('wcmlim' . '_soldout_button_text', 'Sold Out');
			}

			$stockbutton_text = get_option('wcmlim' . '_instock_button_text');
			if ($stockbutton_text == false) {
				update_option('wcmlim' . '_instock_button_text', 'In Stock');
			}

			$backorder_text = get_option('wcmlim' . '_onbackorder_button_text');
			if ($backorder_text == false) {
				update_option('wcmlim' . '_onbackorder_button_text', 'Available on backorder');
			}

			require_once plugin_dir_path(__FILE__) . 'includes/class-wcmlim-activator.php';
			Wcmlim_Activator::activate();
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
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
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

//write shortcode for wocommerce related products
function wcmlim_related_products_shortcode()
{
	ob_start();
	$product_id = get_the_ID();
	$terms = get_the_terms($product_id, 'product_cat');
	$term_names = array();
	if ($terms && !is_wp_error($terms)) {
		foreach ($terms as $term) {
			if ($term->name !== 'Uncategorized') {
				$term_names[] = $term->name;
			}
		}
	}

	$selected_loc_id = isset($_COOKIE['wcmlim_selected_location']) ? (int) $_COOKIE['wcmlim_selected_location'] : 0;
	$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
	//get hide out of stock settings
	foreach ($locations as $key => $term) {
		if ($key == $selected_loc_id) {
			$term_slug = $term->slug;
			$term_id = $term->term_id;
			break;
		}
	}
	$q = new WP_Query(array(
		'post_type' => 'product',
		'posts_per_page' => '4',
		'tax_query' => array(
			'relation' => "AND",
			array(
				'taxonomy' => 'product_cat',
				'field' => 'name',
				'terms' => $term_names,
				'operator' => 'IN'
			),

			array(
				'taxonomy' => 'product_visibility',
				'field' => 'name',
				'terms' => array('outofstock'),
				'operator' => 'NOT IN'
			),
			array(
				'taxonomy' => 'locations',
				'field' => 'slug',
				'terms' => array($term_slug),
				'operator' => 'IN'
			),

		),
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'wcmlim_stock_at_' . $term_id,
				'value' => 0,
				'compare' => '!=',
			),
			array(
				'key' => 'wcmlim_stock_at_' . $term_id,
				'value' => '',
				'compare' => '!=',
			),

		),
	));

	if ($q->have_posts()) {
		echo '<div class="related-products-grid">';

		$counter = 0;
		while ($q->have_posts()) {
			$q->the_post();

			echo '<div class="related-product-item">';
			wc_get_template_part('content', 'product');
			echo '</div>';

			$counter++;
			if ($counter % 3 === 0) {
				echo '<div class="related-products-grid"></div>';
			}
		}

		echo '</div>';

		wp_reset_postdata();
	} else {
		echo 'No related products found.';
	}

	return ob_get_clean();
}

add_shortcode('wcmlim_related_products_shortcode', 'wcmlim_related_products_shortcode');

/**
 * ===============================================================
 * Integración: Carga dinámica de tiendas y sincronización JSON
 * ===============================================================
 *
 * Este bloque de código realiza dos tareas principales:
 *
 * 1. Renderizar el formulario de búsqueda de tiendas en la página
 *    `/tiendas` usando un shortcode personalizado.
 *
 * 2. Registrar y ejecutar un cron programado que genera archivos
 *    JSON cacheados para las tiendas activas por ubicación.
 *
 * Estructura:
 * - Verifica si la URL es `/tiendas` y carga el formulario.
 * - Incluye la clase del cron `SyncLocationsJson` si no existe.
 * - Programa el cron para que inicie en 30 segundos y se repita
 *   cada 24 horas (intervalo diario).
 * - Registra el hook del cron.
 * - Registra el hook de activación del plugin para agendar el cron.
 * - Registra el hook de desactivación del plugin para limpiar el cron.
 *
 * Autor: Dens - Spark
 * Fecha: 08/05/2025
 */

if (preg_match('#^/tiendas/?(\?.*)?$#', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
	require_once plugin_dir_path(__FILE__) . 'includes/searchStore/index.php';
}

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

function geolocation_form()
{
	?>
	<div id="geolocation-modal"
		style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; justify-content: center; align-items: center;">
		<div class="modal-msg-location">
			<div class="modal-msg-location__icon"></div>
			<div class="modal-msg-location__msg">
				<div class="modal-msg-location__msg-title">
					<h2 class="msg-title">¿Nos ayudas un momento?</h2>
				</div>
				<div class="modal-msg-location__msg-info">
					<p class="msg-info">
						Para ofrecerte una mejor experiencia y mostrarte productos disponibles cerca de ti,
						<strong>necesitamos saber tu ubicación.</strong>
					</p>
					<p class="msg-info c-blue">
						<strong>¿Te gustaría activarla ahora?</strong>
					</p>
				</div>
				<div class="modal-msg-location__buttonspanel">
					<button href="" class="modal-msg-location__btn btn-good" id="allow-geolocation">
						Sí, compartir ubicación.
					</button>
					<button href="" class="modal-msg-location__btn btn-bad" id="deny-geolocation">
						No, prefiero no hacerlo.
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="geolocation-reminder-modal"
		style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center;">
		<div
			style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
			<!-- Header con estilo warning -->
			<div style="background: #FFF3CD; padding: 15px; border-bottom: 1px solid #FFEEBA;">
				<h3
					style="margin: 0; color: #856404; display: flex; align-items: center; justify-content: center; gap: 10px;">
					<svg style="width: 24px; height: 24px; fill: #FFC107;" viewBox="0 0 24 24">
						<path
							d="M12 2L1 21h22L12 2zm0 3.5L18.5 19h-13L12 5.5zM12 16c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm-1-4V9h2v3h-2z" />
					</svg>
					<span>Experiencia limitada</span>
				</h3>
			</div>
			<p style="color: #555; line-height: 1.5;">De acuerdo, le preguntaremos de nuevo más tarde. Recuerda que
				necesitamos tu ubicación para mejorar su experiencia.</p>
			<div style="margin-top: 25px;">
				<button id="understand-reminder"
					style="background: #2196F3; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.3s;">
					Entendido
				</button>
			</div>
		</div>
	</div>

	<style>
		/*Modal de ubicación*/
		.modal-msg-location {
			display: flex;
			flex-direction: row;
			gap: 10px;
			padding: 35px 15px;
			background-color: #ffffff;
			border-radius: 15px;
			width: 100%;
			max-width: 520px;
		}

		.modal-msg-location__icon {
			display: block;
			width: 60px;
			min-width: 40px;
			background-repeat: no-repeat;
			background-size: contain;
			background-image: url("data:image/svg+xml,%3C%3Fxml version='1.0' encoding='UTF-8'%3F%3E%3Csvg id='Capa_1' data-name='Capa 1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 600'%3E%3Cdefs%3E%3Cstyle%3E .cls-1 %7B fill: %23009a38; %7D %3C/style%3E%3C/defs%3E%3Cpath class='cls-1' d='M300,589s216.75-205.41,216.75-361.25c0-119.71-97.04-216.75-216.75-216.75S83.25,108.04,83.25,227.75c0,155.84,216.75,361.25,216.75,361.25M300,336.12c-59.85,0-108.38-48.52-108.38-108.38s48.52-108.38,108.38-108.38,108.38,48.52,108.38,108.38-48.52,108.38-108.38,108.38'/%3E%3C/svg%3E");
		}

		.modal-msg-location__msg {
			display: flex;
			flex-direction: column;
			padding-right: 10px;
			font-family: "Poppins", Sans-serif;
		}

		.modal-msg-location__msg-title {
			color: #021B6D !important;
		}

		h2.msg-title {
			margin: 0 0 5px 0 !important;
			padding: 0 !important;
			font-size: 25px;
			font-weight: 700;
		}

		p.msg-info {
			margin: 0 !important;
			padding: 0 0 10px 0 !important;
			font-size: 14px;

			&.c-blue {
				color: #021B6D !important;
			}
		}

		.modal-msg-location__buttonspanel {
			display: flex;
			flex-direction: row;
			gap: 5px;

		}

		.modal-msg-location__btn {
			font-size: 14px;
			text-decoration: none !important;
			padding: 5px 15px;
			border-radius: 2px;
			color: #ffffff;
			border: none !important;

			&.btn-good {
				background-color: #009A38;

				&:hover {
					background-color: #13b950;
				}
			}

			&.btn-bad {
				background-color: #bc0909;

				&:hover {
					background-color: #eb0707;
				}
			}
		}

		@media screen and (max-width:680px) {
			h2.msg-title {
				font-size: 16px;
			}

			p.msg-info,
			.modal-msg-location__btn {
				font-size: 13px;
			}

			.modal-msg-location__msg {
				width: 80%;
			}

			.modal-msg-location {
				max-width: 90% !important;
			}
		}

		@media screen and (max-width:480px) {
			h2.msg-title {
				font-size: 16px;
			}

			p.msg-info,
			.modal-msg-location__btn {
				font-size: 13px;
			}

			.modal-msg-location__buttonspanel {
				flex-direction: column;
				text-align: center;
			}
		}

		/*Modal de ubicación*/
	</style>
	<script>

		// import * as alies_sucCalbk from "https://devs.mystagingwebsite.com/wp-content/plugins/bafar-WooCommerce-Multi-Locations-Inventory-Management/public/js/wcmlim_utility/generic/wcmlim_success_callback.js";
		// import * as alies_errCalbk from "https://devs.mystagingwebsite.com/wp-content/plugins/bafar-WooCommerce-Multi-Locations-Inventory-Management/public/js/wcmlim_utility/generic/wcmlim_error_callback.js";
		// import * as alies_setcookies from "https://devs.mystagingwebsite.com/wp-content/plugins/bafar-WooCommerce-Multi-Locations-Inventory-Management/public/js/wcmlim_utility/generic/wcmlim_setcookies.js";


	</script>
	<?php
}
add_action('wp_footer', 'geolocation_form');

function geolocation_modal_persistent(): void
{
	?>
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			// ✅ Detectar si ya hay tienda
			const hasStore = !!document.cookie.match(/wcmlim_selected_location_termid=\d+/);
			const popupBtn = document.querySelector('#set-def-store-popup-btn');
			if (!popupBtn) return;

			// ✅ Botón manual de cambio de ubicación (opcional)
			const manualBtn = document.querySelector('.btnManualmente');
			if (manualBtn) {
				manualBtn.addEventListener('click', function (e) {
					e.preventDefault();
					const trigger = document.querySelector('.postcode-checker-change.postcode-checker-change-show');
					if (trigger) trigger.click();
				});
			}

			// ✅ Si ya hay tienda, salir del script
			if (hasStore) return;

			// ✅ Interceptar cualquier botón de tipo add_to_cart
			document.body.addEventListener('click', (e) => {
				const target = e.target.closest('a.product_type_simple.add_to_cart_button.wcmlim_ajax_add_to_cart');
				if (!target) return;

				e.preventDefault();
				e.stopImmediatePropagation();

				console.warn('⚠️ Acción bloqueada. Debe elegir tienda primero.');
				popupBtn.click(); // Simular click al popup de selección de tienda
			}, true); // true: captura antes que WooCommerce
		});
	</script>
	<?php
}
add_action('wp_footer', 'geolocation_modal_persistent');


