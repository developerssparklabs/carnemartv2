<?php

/**
 * Plugin Name: Min Max Control (PRO)
 * Plugin URI: https://min-max-quantity.codeastrology.com/
 * Description: [Min Max Quantity & Step Control for WooCommerce] offers to display specific products with minimum, maximum quantity. As well as by this plugin you will be able to set the increment or decrement step as much as you want. In a word: Minimum Quantity, Maximum Quantity and Step can be controlled.
 * Author: CodeAstrology Team
 * Author URI: https://codeastrology.com
 * Tags: WooCommerce, minimum quantity, maximum quantity, woocommrce quantity, customize woocommerce quantity, customize wc quantity, wc qt, max qt, min qt, maximum qt, minimum qt
 * 
 * Version: 3.0.0
 * Requires at least:    4.0.0
 * Tested up to:         6.4.2
 * WC requires at least: 3.0.0
 * WC tested up to: 	 8.0.2
 * 
 * Text Domain: wcmmq_pro
 * Domain Path: /languages/
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Defining constant
 */

define('WC_MMQ_PRO__FILE__', __FILE__);
define('WC_MMQ_PRO_VERSION', '3.0.0.1');
define('WC_MMQ_PRO_PATH', plugin_dir_path(WC_MMQ_PRO__FILE__));
define('WC_MMQ_PRO_URL', plugins_url(DIRECTORY_SEPARATOR, WC_MMQ_PRO__FILE__));
//for Modules and 
define('WC_MMQ_PRO_MODULES_PATH', plugin_dir_path(WC_MMQ_PRO__FILE__) . 'modules' . DIRECTORY_SEPARATOR);


define('WC_MMQ_PRO_PLUGIN_BASE_FOLDER', plugin_basename(dirname(__FILE__)));
define('WC_MMQ_PRO_PLUGIN_BASE_FILE', plugin_basename(__FILE__));
define("WC_MMQ_PRO_BASE_URL", plugins_url() . '/'. plugin_basename( dirname( __FILE__ ) ) . '/');
define("WC_MMQ_PRO_dir_base", dirname(__FILE__) . '/');
define("WC_MMQ_PRO_BASE_DIR", str_replace('\\', '/', WC_MMQ_PRO_dir_base));

/**
 * eta invator jonno kete dite hobe
 */
define("WC_MMQ_PRO_DIRECT", 1);

$wcmmp_is_old = get_option('wcmmq_s_universal_minmaxstep') ? true : false;
$wcmmp_is_old_pro = get_option('wcmmq_universal_minmaxstep') ? true : false;
//var_dump(get_option('wcmmq_universal_minmaxstep'));
if($wcmmp_is_old_pro){
    define("WC_MMQ_PREFIX_PRO", '_wcmmq_');
    define("WC_MMQ_KEY_PRO", 'wcmmq_universal_minmaxstep');
}elseif( $wcmmp_is_old ){
    define("WC_MMQ_PREFIX_PRO", '_wcmmq_s_');
    define("WC_MMQ_KEY_PRO", 'wcmmq_s_universal_minmaxstep');
}elseif( ! defined( 'WC_MMQ_PREFIX_PRO' ) || ! defined( 'WC_MMQ_KEY_PRO' ) ){
    define("WC_MMQ_PREFIX_PRO", '');
    define("WC_MMQ_KEY_PRO", 'wcmmq_universal_minmaxstep');
}
//$WC_MMQ_PRO = WC_MMQ_PRO::getInstance();

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );




/**
 * Main Class for "WooCommerce Min Max Quantity & Step Control"
 * We have included file from __constructor of this class [WC_MMQ_PRO]
 */
class WC_MMQ_PRO {

    /**
     * Static Property
     * Used for Maintenance of Admin Notice for Require Plugin
     * With Our Plogin Woo Product Table Pro and Woo Product Table
     *
     * @var Array
     */
    public static $own = array(
        'plugin'  => 'woo-min-max-quantity-step-control-single/wcmmq.php',
        'plugin_slug'  => 'woo-min-max-quantity-step-control-single',
        'type'  => 'error',
        'message' => 'Install to working',
        'btn_text' => 'Install Now',
        'name' => 'Min Max Quantity & Step Control for WooCommerce',
        'perpose' => 'install', //install,upgrade,activation
    );


    public static $direct;
    /**
     * Plugin Version
     *
     * @since 1.0.0
     *
     * @var string The plugin version.
     */
    const VERSION = WC_MMQ_PRO_VERSION;


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
     * @return WC_MMQ_PRO An instance of the class.
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


        $dir = dirname(__FILE__);

        //Include AutoLoader
        include_once $dir . '/autoloader.php';
        

        
        if( WC_MMQ_PRO\Framework\Required::fail() ){
            return;
        }

        add_action('init', [$this, 'i18n']);

        WC_MMQ_PRO\Modules\Controller::run();


        if ( is_admin() ) {
     

            include_once $dir . '/admin/functions.php';
            // include_once $dir . '/admin/variation-options.php';
            include_once $dir . '/admin/set_menu_and_fac.php';
            include_once $dir . '/admin/plugin_setting_link.php';

            
            include_once $dir . '/admin/plugin_setting_link.php';

            //Update/License Module AND etaO invator jonno kete dite hobe
            self::$direct = new \WC_MMQ_PRO\Admin\License\Init();
            
        }
        
        /**
         * If plus minus plugin installed or not
         */
        $is_wqpmb = is_plugin_active( 'wc-quantity-plus-minus-button/init.php' );
        $option = get_option(WC_MMQ_KEY_PRO);
        $is_qty_button = isset( $option[WC_MMQ_PREFIX_PRO . 'qty_plus_minus_btn'] ) && $option[WC_MMQ_PREFIX_PRO . 'qty_plus_minus_btn'] == '1' ? true : false;

        if( $is_qty_button && ! $is_wqpmb ){
            include_once $dir . '/includes/plus_minus_button.php';
        }
        
        include_once $dir . '/includes/enqueue.php';
        include_once $dir . '/includes/functions.php';
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
        load_plugin_textdomain('wcmmq_pro',false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    }


    /**
     * Installation function for Plugn WC_MMQ_PRO
     * 
     * @since 1.0
     */
    public static function install() {
        //Nothing to do here for this new version
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
       
        return isset($data['Version'] ) ? $data['Version'] : '';
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

    /**
     * For checking anything
     * Only for test, Nothing for anything else
     * 
     * @since 1.0
     * @param void $something
     */
    public static function vd($something) {
        echo '<div style="width:400px; margin: 30px 0 0 181px;">';
        var_dump($something);
        echo '</div>';
    }

    public function wcmmq_upgrade_main_plugin_notice(){
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

           $message = sprintf(
                   esc_html__( '"%1$s" requires "%2$s" to be upgraded to the latest version or greater than 2.0', 'wcmmq_pro' ),
                   '<strong>' . esc_html__( 'Min Max Quantity & Step Control for WooCommerce (PRO)', 'wcmmq_pro' ) . '</strong>',
                   '<strong><a href="' . esc_url( 'https://wordpress.org/plugins/woo-min-max-quantity-step-control-single/' ) . '" target="_blank">' . esc_html__( 'Min Max Quantity & Step Control for WooCommerce', 'wcmmq_pro' ) . '</a></strong>'
           );

           printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
    }
    
    /**
     * Admin notice
     *
     * Warning when the site doesn't have Elementor installed or activated.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice() {
        if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
        }

        $plugin         = isset( self::$own['plugin'] ) ? self::$own['plugin'] : '';
        $type           = isset( self::$own['type'] ) ? self::$own['type'] : false;
        $plugin_slug    = isset( self::$own['plugin_slug'] ) ? self::$own['plugin_slug'] : '';
        $message        = isset( self::$own['message'] ) ? self::$own['message'] : '';
        $btn_text       = isset( self::$own['btn_text'] ) ? self::$own['btn_text'] : '';
        $name           = isset( self::$own['name'] ) ? self::$own['name'] : false; //Mainly providing OUr pLugin Name
        $perpose        = isset( self::$own['perpose'] ) ? self::$own['perpose'] : 'install';
        if( $perpose == 'activation' ){
            $url = $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
        }elseif( $perpose == 'upgrade' ){
            $url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin, 'upgrade-plugin_' . $plugin );
        }elseif( $perpose == 'install' ){
            //IF PERPOSE install or Upgrade Actually || $perpose == install only supported Here
            $url = wp_nonce_url( self_admin_url( 'update.php?action=' . $perpose . '-plugin&plugin=' . $plugin_slug ), $perpose . '-plugin_' . $plugin_slug ); //$install_url = 
        }else{
            $url = false;
        }
        
        
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = '<p>' . $message . '</p>';
        if( $url ){
            $style = isset( $type ) && $type == 'error' ? 'style="background: #ff584c;border-color: #E91E63;"' : 'style="background: #ffb900;border-color: #c37400;"';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary" %s>%s</a>', $url,$style, $btn_text ) . '</p>';
        }
        printf( '<div class="notice notice-' . $type . ' is-dismissible"><p>%1$s</p></div>', $message );

    }
    

}


//Call to Instance
$WC_MMQ_PRO = WC_MMQ_PRO::instance();

register_activation_hook(__FILE__, array('WC_MMQ_PRO', 'install'));
register_deactivation_hook(__FILE__, array('WC_MMQ_PRO', 'uninstall'));
