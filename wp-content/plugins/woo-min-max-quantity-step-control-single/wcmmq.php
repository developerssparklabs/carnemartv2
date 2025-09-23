<?php
/**
 * Plugin Name: Min Max Control - Min Max Quantity & Step Control for WooCommerce
 * Requires Plugins: woocommerce
 * Plugin URI: https://codeastrology.com/min-max-quantity/
 * Description: [Min Max Quantity & Step Control for WooCommerce] offers to display specific products with minimum, maximum quantity. As well as by this plugin you will be able to set the increment or decrement step as much as you want. In a word: Minimum Quantity, Maximum Quantity and Step can be controlled. for any issue: codersaiful@gmail.com
 * Author: CodeAstrology Team
 * Author URI: https://codeastrology.com
 * Tags: WooCommerce, minimum quantity, maximum quantity, woocommrce quantity, input step control for WC, customize wc quantity, wc qt, max qt, min qt, maximum qt, minimum qt
 * 
 * Version: 7.0.2
 * Requires at least:    4.0.0
 * Tested up to:         6.8
 * WC requires at least: 3.0.0
 * WC tested up to: 	 9.9.5
 * 
 * Text Domain: woo-min-max-quantity-step-control-single
 * Domain Path: /languages/
 * 
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Defining constant
 */

define('WC_MMQ__FILE__', __FILE__);
define('WC_MMQ_VERSION', '7.0.2.0');
define('WC_MMQ_PATH', plugin_dir_path(WC_MMQ__FILE__));
define('WC_MMQ_URL', plugins_url(DIRECTORY_SEPARATOR, WC_MMQ__FILE__));
//for Modules and 
define('WC_MMQ_MODULES_PATH', plugin_dir_path(WC_MMQ__FILE__) . 'modules' . DIRECTORY_SEPARATOR);


define('WC_MMQ_PLUGIN_BASE_FOLDER', plugin_basename(dirname(__FILE__)));
define('WC_MMQ_PLUGIN_BASE_FILE', plugin_basename(__FILE__));
define("WC_MMQ_BASE_URL", plugins_url() . '/'. plugin_basename( dirname( __FILE__ ) ) . '/');
define("wc_mmq_dir_base", dirname(__FILE__) . '/');
define("WC_MMQ_BASE_DIR", str_replace('\\', '/', wc_mmq_dir_base));

/**
 * Option key handle based on old user and new user
 */
$wcmmp_is_old = get_option('wcmmq_s_universal_minmaxstep') ? true : false;
$wcmmp_is_old_pro = get_option('wcmmq_universal_minmaxstep') ? true : false;

if($wcmmp_is_old_pro){
    define("WC_MMQ_PREFIX", '_wcmmq_');
    define("WC_MMQ_KEY", 'wcmmq_universal_minmaxstep');
}elseif( $wcmmp_is_old ){
    define("WC_MMQ_PREFIX", '_wcmmq_s_');
    define("WC_MMQ_KEY", 'wcmmq_s_universal_minmaxstep');
}else{
    define("WC_MMQ_PREFIX", '');
    define("WC_MMQ_KEY", 'wcmmq_minmaxstep');
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


/**
 * Setting Default Quantity for Configuration page
 * It will work for all product
 * 
 * @todo amra key gulor prefix remove korar jonno kaj korbo (using user consent/permission)
 * 
 * @since 1.0
 */
WC_MMQ::$default_values = array(
    WC_MMQ_PREFIX . 'min_quantity' => 1,
    WC_MMQ_PREFIX . 'default_quantity' => false,
    WC_MMQ_PREFIX . 'max_quantity' => false,
    WC_MMQ_PREFIX . 'product_step' => 1,
    WC_MMQ_PREFIX . 'prefix_quantity' => '',
    'quantiy_box_archive' => '0',//If we want enable by default, set 1 for this item
    WC_MMQ_PREFIX . 'sufix_quantity' => '',
    WC_MMQ_PREFIX . 'qty_plus_minus_btn' => '1', //Added at 1.8.4 Version
    WC_MMQ_PREFIX . 'step_error_valiation'   => __( "Please enter a valid value. The two nearest valid values are [should_min] and [should_next]", 'woo-min-max-quantity-step-control-single' ),
    WC_MMQ_PREFIX . 'msg_min_limit' => __('Minimum quantity should [min_quantity] of "[product_name]"', 'woo-min-max-quantity-step-control-single'), //First %s = Quantity and Second %s is Product Title
    WC_MMQ_PREFIX . 'msg_max_limit' => __('Maximum quantity should [max_quantity] of "[product_name]"', 'woo-min-max-quantity-step-control-single'), //First %s = Quantity and Second %s is Product Title
    WC_MMQ_PREFIX . 'msg_max_limit_with_already' => __('You have already [current_quantity] item of "[product_name]"', 'woo-min-max-quantity-step-control-single'), //First %s = $current_qty_inCart Current Quantity and Second %s is Product Title
    WC_MMQ_PREFIX . 'min_qty_msg_in_loop' => __('Minimum qty is', 'woo-min-max-quantity-step-control-single'),
    'msg_min_price_cart' => __('Your cart total amount must be equal to or more of [cart_min_price]', 'woo-min-max-quantity-step-control-single'),
    'msg_max_price_cart' => __('Your cart total amount must be equal to or less than [cart_max_price]', 'woo-min-max-quantity-step-control-single'),
    'msg_min_quantity_cart' => __("Your cart item's total quantity must be equal to or more than [cart_min_quantity]", 'woo-min-max-quantity-step-control-single'),
    'msg_max_quantity_cart' => __("Your cart item's total quantity must be equal to or less than [cart_max_quantity]", 'woo-min-max-quantity-step-control-single'),
    'msg_step_quantity_cart' => __("Please enter a valid value. Value should be multiplier of [step_quantity]", 'woo-min-max-quantity-step-control-single'),
    'msg_vari_total_max_qty' => __('Maximum variation quantity total of "[product_name]" should be or less then [vari_total_max_qty]', 'woo-min-max-quantity-step-control-single'),
    'msg_vari_total_min_qty' => __('Minimum variation quantity total of "[product_name]" should be or greater then [vari_total_min_qty]', 'woo-min-max-quantity-step-control-single'),
    'msg_vari_count_total' => __('Maximum variation count total of "[product_name]" should be or less then [vari_count_total]', 'woo-min-max-quantity-step-control-single'),
    '_cat_ids' => false,
);

/**
 * Main Class for "WooCommerce Min Max Quantity & Step Control"
 * We have included file from __constructor of this class [WC_MMQ]
 */
class WC_MMQ {

    

    /**
     * Plugin Version
     *
     * @since 1.0.0
     *
     * @var string The plugin version.
     */
    const VERSION = WC_MMQ_VERSION;

    /**
     * Minimum WooCommerce Version
     *
     * @since 1.0.0
     *
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_WC_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     *
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '5.6';


    /*
     * Set default value based on default keyword.
     * All value will store in wp_options table based on Keyword wcmmq_universal_minmaxstep
     * 
     * @Sinc Version 1.0.0
     */

    public static $default_values = array();

    /**
     * For Instance
     *
     * @var Object 
     * @since 1.0
     */
    private static $_instance;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.7.0
     *
     * @access public
     * @static
     *
     * @return WC_MMQ An instance of the class.
     */
    public static function instance() {
        if (!( self::$_instance instanceof self )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**

      public static function getInstance() {
      if ( ! ( self::$_instance instanceof self ) ) {
      self::$_instance = new self();
      }

      return self::$_instance;
      }
     */
    public function __construct() {
        

        // Declare compatibility with custom order tables for WooCommerce.
        add_action( 'before_woocommerce_init', function(){
                if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
                    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
                }
            }
        );

        require_once __DIR__ . '/autoloader.php';
        
        
        if( \WC_MMQ\Framework\Plugin_Required::fail() ){
            return;
        }

        add_action('init', [$this, 'i18n']);

        $dir = dirname(__FILE__);

        /**
         * Common Functions file,
         * where will stay function for both side
         * admin and front-end
         * 
         * @since 2.9.0
         */
        include_once $dir . '/includes/functions.php';

        if ( is_admin() ) {
     
            \WC_MMQ\Framework\Recommeded::check();
            include_once $dir . '/admin/functions.php';
            include_once $dir . '/admin/product_panel.php';
            include_once $dir . '/admin/add_options_admin.php';
            include_once $dir . '/admin/plugin_setting_link.php';

            new \WC_MMQ\Admin\Admin_Loader();
        }
        \WC_MMQ\Includes\Feature_Loader::run();
        \WC_MMQ\Modules\Module_Controller::instance();
        
        
        include_once $dir . '/includes/enqueue.php';
        
        include_once $dir . '/includes/set_max_min_quantity.php';
        \WC_MMQ\Includes\Min_Max_Controller::init();
    }

    /**
     * Load Textdomain
     *
     * Load plugin localization files.
     *
     * Fired by `init` action hook.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function i18n() {
        // load_plugin_textdomain('woo-min-max-quantity-step-control-single');
        load_plugin_textdomain('woo-min-max-quantity-step-control-single', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    }


    /**
     * Installation function for Plugn WC_MMQ
     * 
     * @since 1.0
     */
    public static function install() {
        //check current value
        $current_value = get_option(WC_MMQ_KEY);
        $default_value = self::$default_values;
        $changed_value = [];
        //Set default value in Options
        if ($current_value) {
            foreach ($default_value as $key => $value) {
                if (isset($current_value[$key]) && $key != 'plugin_version') { //We will add Plugin version in future
                    $changed_value[$key] = $current_value[$key];
                } else {
                    $changed_value[$key] = $value;
                }
            }
            update_option(WC_MMQ_KEY, $changed_value);
        } else {
            update_option(WC_MMQ_KEY, $default_value);
        }
    }

    /**
     * Getting default key and value 's array
     * 
     * @return Array getting default value for basic plugin
     * @since 1.0
     */
    public static function getDefaults() {
        return self::$default_values;
    }

    /**
     * Getting Array of Options of wcmmq_universal_minmaxstep
     * 
     * @return Array Full Array of Options of wcmmq_universal_minmaxstep
     * 
     * @since 1.0.0
     */
    public static function getOptions() {
        return get_option(WC_MMQ_KEY);
    }

    /**
     * Getting Array of Options of wcmmq_universal_minmaxstep
     * 
     * @return String Full Array of Options of wcmmq_universal_minmaxstep
     * 
     * @since 1.0.0
     */
    public static function getOption($kewword = false) {
        $data = get_option( WC_MMQ_KEY );
        return $kewword && isset($data[$kewword]) ? $data[$kewword] : false;
    }

    public static function minMaxStep($kewword = false, $product_id = false) {
        $data = get_option(WC_MMQ_KEY);
        $cat_ids = isset( $data['_cat_ids'] ) ? $data['_cat_ids'] : false;

        $check_arr = false;
        if (isset($cat_ids) && is_array($cat_ids) && $product_id && !empty($product_id)) {
            $product_cat_ids = wc_get_product_cat_ids($product_id);
            $check_arr = is_array($product_cat_ids) ? array_intersect($cat_ids, $product_cat_ids) : false;
        }

        if (is_array($check_arr) && count($check_arr) > 0) {
            return $kewword && isset($data[$kewword]) ? $data[$kewword] : false;
        }

        if (!$check_arr && isset($cat_ids) && is_array($cat_ids) && $product_id && !empty($product_id)) {
            $default = WC_MMQ::getDefaults();
            return $kewword && isset($default[$kewword]) ? $default[$kewword] : false;
        }
        /*
          $cat_ids_diff = is_array( $cat_ids ) ? array_diff( $product_cat_ids, $cat_ids ) : false;
          if(!$cat_ids){
          return $kewword && isset( $data[$kewword] ) ? $data[$kewword] : false;
          }
          if($cat_ids && $cat_ids_diff && is_array( $cat_ids ) && count( $cat_ids_diff ) > count( $cat_ids ) ){
          return $kewword && isset( $data[$kewword] ) ? $data[$kewword] : false;
          }
         */
        //$default = WC_MMQ::getDefaults();
        return self::getOption($kewword);
        //return $kewword && isset( $default[$kewword] ) ? $default[$kewword] : false;
    }

    /**
     * Un instalation Function
     * 
     * @since 1.0
     */
    public static function uninstall() {
        //Nothing to do for now
    }

    /**
     * Getting full Plugin data. We have used __FILE__ for the main plugin file.
     * 
     * @since V 1.0
     * @return Array Returnning Array of full Plugin's data for This Woo Product Table plugin
     */
    public static function getPluginData() {
        if (is_admin())
            return get_plugin_data(__FILE__);
    }

    /**
     * Getting Version by this Function/Method
     * 
     * @return type static String
     */
    public static function getVersion() {
        $data = self::getPluginData();
        return $data['Version'];
    }

    /**
     * Getting Version by this Function/Method
     * 
     * @return type static String
     */
    public static function getName() {
        $data = self::getPluginData();
        return $data['Name'];
    }


    public function admin_notice_missing_main_plugin(){

           $message = sprintf(
                /* translators: 1: Plugin name 2: WooCommerce with link */
                esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'woo-min-max-quantity-step-control-single' ),
                '<strong>' . esc_html__( 'Min Max Control', 'woo-min-max-quantity-step-control-single' ) . '</strong>',
                '<strong><a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">' . esc_html__( 'WooCommerce', 'woo-min-max-quantity-step-control-single' ) . '</a></strong>'
           );

           printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', wp_kses_post( $message ) );
    }
    

}


//Call to Instance
$WC_MMQ = \WC_MMQ::instance();

register_activation_hook(__FILE__, array('WC_MMQ', 'install'));
register_deactivation_hook(__FILE__, array('WC_MMQ', 'uninstall'));
