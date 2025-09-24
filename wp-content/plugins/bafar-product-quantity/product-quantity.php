<?php
/*
 * Plugin Name: Bafar :: Product Quantity  🛍️ 
 * Plugin URI: sparklabs.com.mx
 * Description: Limita cantidades de productos por stock de cada tienda
 * Version: 1.0.0
 * Author: Naveed @ Sparklabs
 * Author URI: sparklabs.com.mx
 * Support: sparklabs.com.mx
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: product-quantity
 * Domain Path: /languages/
 */
if (!defined('ABSPATH'))
	exit();
if (!defined('PQ_URL')) {
	define('PQ_URL', plugin_dir_url(__FILE__));
}
if (!defined('PQ_DIR')) {
	define('PQ_DIR', plugin_dir_path(__FILE__));
}
if (!class_exists('Product_Quantity')) {
	class Product_Quantity
	{
		public function __construct()
		{
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				self::pq_init_plugin_files();
			} else {
				add_action('admin_notices', array(__CLASS__, 'pq_admin_notice'));
			}
		}
		public static function pq_init_plugin_files()
		{
			if (function_exists('load_plugin_textdomain')) {
				load_plugin_textdomain('product-quantity', false, dirname(plugin_basename(__FILE__)) . '/languages/');
			}
			if (is_admin()) {
				require_once PQ_DIR . 'include/class-backend.php';
			}
			require_once PQ_DIR . 'include/class-frontend.php';
		}
		public static function pq_admin_notice()
		{
			global $pagenow;
			if ('plugins.php' === $pagenow) {
				$class = esc_attr('notice notice-error is-dismissible');
				$message = esc_html__('Product Quantity plugin needs WooCommerce to be installed and active.', 'product-quantity');
				printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
			}
		}
	}
	new Product_Quantity();
}