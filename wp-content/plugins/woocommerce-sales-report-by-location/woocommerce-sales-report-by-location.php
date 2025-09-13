<?php
/**
 * Plugin Name: WooCommerce Sales Reports By Location
    * Plugin URI:  http://www.techspawn.com
    * Description: WooCommerce Sales Reports By Location Wordpress plugin addon enables report tab to display location wise sales report. Addon tab place in WooCommerce reports section.
    * Version: 1.0.3
    * Author: Techspawn Solutions 
    * Author URI: http://www.techspawn.com
    * 
    *
    
    * Techspawn Solutions Private Limited (www.techspawn.com)
    * 
    * 
    *  Copyright: (c)  [2019] - Techspawn Solutions Private Limited ( contact@techspawn.com  ) 
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
    * License: GNU General Public License v3.0
    * License URI: http://www.gnu.org/licenses/gpl-3.0.html
    *
    *
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


/**
 * # WooCommerce Location Report Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds a new section in the WooCommerce Reports -> Orders area called 'Sales By Location'.
 * The report visualizes the customer purchases by location into a Choropleth map to show where the orders
 * are being placed.
 *
 * This plugin utilizes jVectorMap (http://jvectormap.com) for its map functions.
 *
 */
class WC_Country_Report {

	/** plugin version number */
	public static $version = '1.0.0';

	/** @var string the plugin file */
	public static $plugin_file = __FILE__;

	/** @var string the plugin file */
	public static $plugin_dir;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 */
	public static function init() {

		global $wpdb;

		self::$plugin_dir = dirname( __FILE__ );			

		// Add the reports layout to the WooCommerce -> Reports admin section
		add_filter( 'woocommerce_admin_reports',  __CLASS__ . '::initialize_location_admin_report', 12, 1 );

		// Add the path to the report class so WooCommerce can parse it
		add_filter( 'wc_admin_reports_path',  __CLASS__ . '::initialize_location_admin_reports_path', 12, 3 );

		// Load translation files
		add_action( 'plugins_loaded', __CLASS__ . '::load_plugin_textdomain' );
		
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::admin_styles', 4 );
		
		register_activation_hook( __FILE__, __CLASS__ . '::woocommerce_sales_location_report_install');


		add_filter('woocommerce_analytics_orders_query_args',__CLASS__ . '::apply_mlocation_arg');
        add_filter('woocommerce_analytics_orders_stats_query_args', __CLASS__ . '::apply_mlocation_arg');

        add_filter('woocommerce_analytics_clauses_join_orders_subquery', __CLASS__ . '::add_join_subquery',10, 1);
        add_filter('woocommerce_analytics_clauses_join_orders_stats_total', __CLASS__ . '::add_join_subquery',10, 1);
        add_filter('woocommerce_analytics_clauses_join_orders_stats_interval', __CLASS__ . '::add_join_subquery',10, 1);
		
        add_filter('woocommerce_analytics_clauses_where_orders_subquery', __CLASS__ . '::add_where_subquery',10, 1);
        add_filter('woocommerce_analytics_clauses_where_orders_stats_total', __CLASS__ . '::add_where_subquery',10, 1);
        add_filter('woocommerce_analytics_clauses_where_orders_stats_interval', __CLASS__ . '::add_where_subquery',10, 1);

		add_filter('woocommerce_analytics_clauses_select_orders_subquery', __CLASS__ . '::add_select_subquery',10, 1);
		add_filter('woocommerce_analytics_clauses_select_orders_stats_total', __CLASS__ . '::add_select_subquery',10, 1);
		add_filter('woocommerce_analytics_clauses_select_orders_stats_interval', __CLASS__ . '::add_select_subquery',10, 1);
	}


	/**
	 * Add our location report to the WooCommerce order reports array.
	 *
	 * @param array Array of All Report types & their labels
	 * @return array Array of All Report types & their labels, including the 'Sales By Location' report.
	 * @since 1.0
	 */
	public static function initialize_location_admin_report( $report ) {

		$report['orders']['reports']['sales_by_location'] = array(
			'title'       => __( 'Sales By Location', 'woo-sales-location-reports' ),
			'description' => '',
			'hide_title'  => true,
			'callback'    => array( 'WC_Admin_Reports', 'get_report' ),
			);

		return $report;

	}


	/**
	 * If we hit one of our reports in the WC get_report function, change the path to our dir.
	 *
	 * @param array Array of Report types & their labels
	 * @return array Array of Report types & their labels, including the Subscription product type.
	 * @since 1.0
	 */
	public static function initialize_location_admin_reports_path( $report_path, $name, $class ) {		
		if ( 'WC_Report_sales_by_location' == $class ) {
			$report_path = self::$plugin_dir . '/classes/class-wc-report-' . $name . '.php';
		}

		return $report_path;

	}


	/**
	 * Load our language settings for internationalization
	 *
	 * @since 1.0
	 */
	public static function load_plugin_textdomain() {

		load_plugin_textdomain( 'woocommerce-sales-location-reports', false, basename( self::$plugin_dir ) . '/language' );

	}
	
	/**
	 * Load admin styles.
	 */
	public static function admin_styles() {		
		wp_enqueue_style( 'country_report_style', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );

		//amcharts js	
		wp_enqueue_script( 'amcharts', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/amcharts.js' );
		wp_enqueue_script( 'amcharts-light-theme', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/light.js' );
		wp_enqueue_script( 'amcharts-pie', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/pie.js' );
		wp_enqueue_script( 'amcharts-serial', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/serial.js' );
		wp_enqueue_script( 'amcharts-export', plugin_dir_url( __FILE__ ) . 'assets/js/amcharts/export.js' );
		
		//Main js
		wp_enqueue_script( 'sales-by-country-main-js', plugin_dir_url( __FILE__ ) . 'assets/js/script.js' );		

		if (class_exists('\Automattic\WooCommerce\Admin\PageController') && !\Automattic\WooCommerce\Admin\PageController::is_admin_page()) {
			return;
		}

		$script_handle = 'wc-multi-location-analytics';
        wp_register_script(
            $script_handle,
            plugins_url('/assets/js/bundle.js', __FILE__),
            ['wp-hooks', 'wp-element', 'wp-i18n', 'wp-plugins', 'wc-components'],
            filemtime(dirname(__FILE__) . '/assets/js/bundle.js'),
            true
        );
        wp_enqueue_script($script_handle);

		$locations_json = wp_json_encode(self::get_locations());
        wp_add_inline_script(
            $script_handle,
            "if (typeof wcSettings !== 'undefined') {
                wcSettings.multiLocations = JSON.parse(decodeURIComponent('" . esc_js(rawurlencode($locations_json)) . "'));
            } else {
                console.error('wcSettings is not defined.');
            }",
            'before'
        );
	}

	/**
	 * Get locations for the report.
	 * @since 1.0.5
	 * @return array
	 */
	public static function get_locations() {
		$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
		$locations = [];
		$locations[] = array('label' => __('Select Location', 'woocommerce-admin'), 'value' => '-1');
		if (!empty($terms) && !is_wp_error($terms)) {
			foreach ($terms as $term) {
				$locations[] = array(
					'label' => __($term->name, 'woocommerce-admin'),
					'value' => (string)$term->term_id
				);
			}
		} else {
			$locations[] = array('label' => __('Error fetching locations', 'woocommerce-admin'), 'value' => '');
		}
       return (array)$locations;
    }


	/**
	 * Define plugin activation function
	 *
	 * Create Table
	 *
	 * Insert data 
	 *
	 * 
	*/	
	 public static function woocommerce_sales_location_report_install(){
		
		global $wpdb;	
		
		$woo_sales_country_table_name = $wpdb->prefix . 'woo_sales_country_region';

		// create the ECPT metabox database table
		if($wpdb->get_var("show tables like '$woo_sales_country_table_name'") != $woo_sales_country_table_name) 
		{
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $woo_sales_country_table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				country varchar(500) DEFAULT '' NOT NULL,
				region varchar(500) DEFAULT '' NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			include('woocommerce-location-country-array.php');
			foreach($country_list as $country){								
				$success = $wpdb->insert($woo_sales_country_table_name, array(
					"country" => $country['Country'],
					"region" => $country['Region'],
				));
			}
		}		
		
	}	

	public static function apply_mlocation_arg($args) {
        if (isset($_GET['mlocation']) && $_GET['mlocation'] !== '-1') {
            $args['mlocation'] = sanitize_text_field(wp_unslash($_GET['mlocation']));
			$args['force_cache_refresh'] = true;
        }
        return $args;
    }

    public static function add_join_subquery($clauses) {
        global $wpdb;
        if (isset($_GET['mlocation']) && $_GET['mlocation'] !== '-1') {
			$clauses[] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS woi ON woi.order_id = {$wpdb->prefix}wc_order_stats.order_id";
			$clauses[] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim ON woim.order_item_id = woi.order_item_id AND woim.meta_key = '_selectedLocTermId'";
        }
        return $clauses;
    }

    public static function add_where_subquery($clauses) {
        global $wpdb;
        if (isset($_GET['mlocation']) && $_GET['mlocation'] !== '-1') {
            $clauses[] = $wpdb->prepare("AND woim.meta_value = %s", $_GET['mlocation']);
        }
        return $clauses;
    }

	public static function add_select_subquery($clauses) {
		global $wpdb;
		if (isset($_GET['mlocation']) && $_GET['mlocation'] !== '-1') {
			$clauses[] = ", woim.meta_value as mlocation";
		}
		return $clauses;
	}

} // end \WC_Location_Report class


WC_Country_Report::init();