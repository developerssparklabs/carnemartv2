<?php
// use Automattic\WooCommerce\Utilities\OrderUtil;
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       http://www.techspawn.com
 * @since      1.0.0
 * @package    Wcmlim
 * @subpackage Wcmlim/includes
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      Wcmlim_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    if (defined('WCMLIM_VERSION')) {
      $this->version = WCMLIM_VERSION;
    } else {
      $this->version = '3.1.3';
    }

    $this->plugin_name = 'wcmlim';

    $this->load_dependencies();
    $this->set_locale();
    if (is_admin()) {
      $this->define_admin_hooks();
    }

    $wcmlim_allow_only_backend = get_option('wcmlim_allow_only_backend');
    if ($wcmlim_allow_only_backend != "on") {
      $this->define_public_hooks();
    }


    $this->define_product_taxonomy();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Wcmlim_Loader. Orchestrates the hooks of the plugin.
   * - Wcmlim_i18n. Defines internationalization functionality.
   * - Wcmlim_Admin. Defines all hooks for the admin area.
   * - Wcmlim_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {
    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    $hpos_enabled = get_option('wcmlim_hpos_enabled');
    if ($hpos_enabled == "on") {
      define('WC_HPOS_IS_ACTIVE', true);
    } else {
      define('WC_HPOS_IS_ACTIVE', false);
    }

    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcmlim-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcmlim-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wcmlim-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    if (get_option('wcmlim_license') != '' || get_option('wcmlim_license') == 'valid') {
      require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wcmlim-public.php';
    }

    /**
     * The class responsible for defining custom product taxonomy.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcmlim-product-taxonomy.php';

    /**
     * The class responsible for defining custom widget.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcmlim-widget.php';

    if ((!in_array('local-pickup-for-woocommerce/local-pickup.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_array(get_site_option('active_sitewide_plugins')) && !array_key_exists('local-pickup-for-woocommerce/local-pickup.php', get_site_option('active_sitewide_plugins'))) && get_option('wcmlim_allow_local_pickup') == 'on') {

      require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-local-pickup-shipping-method.php';
    }
    /**
     * The class responsible for Product Central bluk edit.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcmlim-product-central.php';

    /**
     * The class responsible for custom rest api endpoints.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcmlim-rest-api.php';


    $this->loader = new Wcmlim_Loader();
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Wcmlim_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale()
  {

    $plugin_i18n = new Wcmlim_i18n();

    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {

    $plugin_admin = new Wcmlim_Admin($this->get_plugin_name(), $this->version);

    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    // Add plugin menu
    $this->loader->add_action('admin_menu', $plugin_admin, 'wcmlim_register_menu_page');
    // added custom taxonomy in plugin submenu
    $this->loader->add_action('parent_file', $plugin_admin, 'wcmlim_submenu_highlight');
    // hightlight the custom taxonomy submenu
    $this->loader->add_filter('submenu_file', $plugin_admin, 'wcmlim_hightlight_submenu');

    $this->loader->add_action('admin_init', $plugin_admin, 'wcmlim_register_setting');
    $this->loader->add_action('admin_init', $plugin_admin, "wcmlim_order_restrict");
    if (function_exists('icl_object_id')) {
      $this->loader->add_action('admin_init', $plugin_admin, 'wcmlim_wpml_init');
    }

    // hooks and filters for display location on product list admin page

    $this->loader->add_action('woocommerce_admin_stock_html', $plugin_admin, 'wcmlim_filter_woocommerce_admin_stock_html', 10, 2);

    $this->loader->add_filter('manage_edit-product_columns', $plugin_admin, 'wcmlim_remove_default_tcoloumn', 10, 1);
    // $this->loader->add_action('manage_posts_custom_column', $plugin_admin, 'wcmlim_populate_stock_column', 10, 2);
    $this->loader->add_filter('woocommerce_admin_stock_html', $plugin_admin, 'wcmlim_populate_stock_column', 10, 2);

    $this->loader->add_action('manage_posts_custom_column', $plugin_admin, 'wcmlim_populate_locations_column');
    $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'wcmlim_filter_by_taxonomy_locations', 10, 2);

    $this->loader->add_action('wp_ajax_check_location_group', $plugin_admin, 'check_location_group_callback');
    // hooks and filters for display location Price on product list admin page
    $enable_price = 'on';
    if ($enable_price == 'on') {
      $this->loader->add_filter('manage_edit-product_columns', $plugin_admin, 'wcmlim_add_price_at_location_tcoloumn', 10, 1);
      $this->loader->add_action('manage_posts_custom_column', $plugin_admin, 'wcmlim_populate_price_locations_column');
    }

    // Hooks responsible for display location filter on shop page and filter shop page
    $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'wcmlim_display_location_filter_on_shoppge', 'top');

    // HPOS - Hooks responsible for display location filter on shop page and filter shop page
    $this->loader->add_action('woocommerce_order_list_table_restrict_manage_orders', $plugin_admin, 'wcmlim_hpos_display_location_filter_on_shoppge', 10, 2);

    $this->loader->add_filter('request', $plugin_admin, 'wcmlim_process_location_filter_on_shoppage', 99);

    // Added For HPOS Feature
    $this->loader->add_action('woocommerce_order_query_args', $plugin_admin, 'wcmlim_process_location_filter_on_shoppage', 10);

    // * Custom Iventory Fields of the plugin like Stock Quantity, Purchase Price , Regular & Sale Price.
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-custom-inventory-fields.php';

    // Common Functions of the plugin for admin side.
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-class-common-functions.php';

    // *restrict user to default location

    $restricUsers = get_option('wcmlim_enable_userspecific_location');
    if ($restricUsers == "on") {
      //  *This action for 'Add New User' screen
      $this->loader->add_action('user_register', $plugin_admin, 'wcmlim_save_profile_fields');

      //  *This actions for 'User Profile' screen
      $this->loader->add_action('personal_options_update', $plugin_admin, 'wcmlim_save_profile_fields');
      $this->loader->add_action('edit_user_profile_update', $plugin_admin, 'wcmlim_save_profile_fields');

      //  *This action for 'Add New User' screen
      $this->loader->add_action('user_new_form', $plugin_admin, 'wcmlim_register_profile_fields');

      //  *This actions for 'User Profile' screen
      $this->loader->add_action('show_user_profile', $plugin_admin, 'wcmlim_register_profile_fields');
      $this->loader->add_action('edit_user_profile', $plugin_admin, 'wcmlim_register_profile_fields');

      // *Bulk assign default location to users
      $this->loader->add_action('restrict_manage_users', $plugin_admin, 'wcmlim_locations_bulk_dropdown');
    }

    $this->loader->add_action('wp_ajax_bulk_assign_default_location', $plugin_admin, 'wcmlim_bulk_assign_default_location');

    // *Filtering orders by assigned location to location shop manager
    $this->loader->add_action('pre_get_posts', $plugin_admin, 'wcmlim_filter_orders_by_location');

    $isShopManagerEnable = get_option('wcmlim_assign_location_shop_manager');
    if ($isShopManagerEnable == "on") {
      $this->loader->add_filter('woocommerce_admin_html_order_item_class', $plugin_admin, 'wcmlim_woocommerce_admin_html_order_item_class_filter', 10, 3);
    }

    // * update stock and price inline ajax callback
    $this->loader->add_action('wp_ajax_update_stock_inline', $plugin_admin, 'wcmlim_update_stock_inline');
    $this->loader->add_action('wp_ajax_update_price_inline', $plugin_admin, 'wcmlim_update_price_inline');
    $this->loader->add_action('default_hidden_meta_boxes', $plugin_admin, 'wcmlim_default_hidden_meta_boxes', 10, 2);
    // *Restricted location parent dropdown to level-1
    $this->loader->add_filter('taxonomy_parent_dropdown_args', $plugin_admin, 'wcmlim_restrict_parent_location', 10, 2);

    // * Add Location column in Order Edit & Order Fulfilment Automatically
    $wcmlim_allow_only_backend = get_option('wcmlim_allow_only_backend');
    $fulfilment_rule = get_option("wcmlim_order_fulfilment_rules");
    if ($fulfilment_rule == "nearby_instock") {
      $fulfilment_rule = "clcsadd";
    }
    if ($wcmlim_allow_only_backend == "on") {
      require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-backend-only-mode.php';
    }

    // * Added widget for locations
    $widgetOnShop = get_option("wcmlim_use_location_widget");
    if ($widgetOnShop == "on") {
      $this->loader->add_action('widgets_init', $plugin_admin, 'wcmlim_register_wcmlim_widget');
    }

    // * Table Rate Shipping Compatibility 

    if (in_array('woocommerce-table-rate-shipping/woocommerce-table-rate-shipping.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-table-rate-shipping-compatibility.php';
    }

    // * WooCommerce Advanced Shipping Compatibility

    if (in_array('woocommerce-advanced-shipping/woocommerce-advanced-shipping.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-woocommerce-advanced-shipping-compatibility.php';
    }
    // * validate sub location time
    $this->loader->add_action('wp_ajax_show_parent_location_time', $plugin_admin, 'wcmlim_show_parent_paremeter');

    // * OpenPOS Compatibility
    $wcmlim_pos_compatibility = get_option('wcmlim_pos_compatiblity');

    if ($wcmlim_pos_compatibility == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      $this->loader->add_action('op_add_order_after', $plugin_admin, 'wcmlim_pos_stock_levels_reduction', 1, 2);

      $this->loader->add_action('woocommerce_order_status_processing_to_refunded', $plugin_admin, 'wcmlim_pos_restore_order_stock', 10, 1);
      $this->loader->add_action('woocommerce_order_status_completed_to_refunded', $plugin_admin, 'wcmlim_pos_restore_order_stock', 10, 1);
      $this->loader->add_action('woocommerce_order_status_on-hold_to_refunded', $plugin_admin, 'wcmlim_pos_restore_order_stock', 10, 1);
      $this->loader->add_action('woocommerce_order_status_cancelled_to_refunded', $plugin_admin, 'wcmlim_pos_restore_order_stock', 10, 1);
    }

    // * Restore stock after order status change(Refunded or Failed)
    $restore_location_stock_for_failed = get_option('wcmlim_restore_location_stock_for_failed');
    if ($restore_location_stock_for_failed == 'on') {
      $this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'wcmlim_increase_stock_levels_order_status_failed_refunded', 10, 3);
    }
    $restore_location_stock_for_refund = get_option('wcmlim_restore_location_stock_for_refund');
    if ($restore_location_stock_for_refund == 'on') {
      $this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'wcmlim_increase_stock_levels_order_status_failed_refunded', 10, 3);
    }

    // * validate Google API
    $this->loader->add_action('wp_ajax_distance_matrix_validate_api', $plugin_admin, 'wcmlim_distance_matrix_validate_api');
    $this->loader->add_action('wp_ajax_geocode_validate_api', $plugin_admin, 'wcmlim_geocode_validate_api');
    $this->loader->add_action('wp_ajax_place_validate_api', $plugin_admin, 'wcmlim_place_validate_api');

    // * Added plugin documentation and support url
    $this->loader->add_filter('plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 2);

    // * location list dropdown on product list page
    $this->loader->add_action('restrict_manage_posts', $plugin_admin, 'wcmlim_locations_bulk_dropdown');

    // * Stock wise product sorting as per location on shop-page.
    $this->loader->add_action('woocommerce_get_catalog_ordering_args', $plugin_admin, 'filter_woocommerce_get_catalog_ordering_args');
    $this->loader->add_action('woocommerce_default_catalog_orderby_options', $plugin_admin, 'custom_woocommerce_catalog_orderby');
    $this->loader->add_action('woocommerce_catalog_orderby', $plugin_admin, 'custom_woocommerce_catalog_orderby');

    /**Product Central Ajax */
    $this->loader->add_action('wp_ajax_wcmlim_update_product_central', $plugin_admin, 'wcmlim_update_product_central');
    $this->loader->add_action('wp_ajax_wcmlim_product_updated', $plugin_admin, 'wcmlim_product_updated');

    // * Cron and update product button
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-cron-update.php';

    // *Ajax call for dynamic shipping methods as per shipping zone
    $this->loader->add_action("wp_ajax_populate_shipping_methods", $plugin_admin, "wcmlim_dynamic_shipping_methods");
    $this->loader->add_action("wp_ajax_wcmlim_deactivate_plugin", $plugin_admin, "wcmlim_deactivate_plugin");
    $this->loader->add_action('admin_notices', $plugin_admin, 'support_validator_admin_notice');
    // if HPOS is enabled this hook will call
    $this->loader->add_action('woocommerce_order_list_table_extra_tablenav', $plugin_admin, 'wcmlim_hpos_admin_order_list_top_bar_button', 10, 2);

    // if HPOS is disabled this hook will call
    $this->loader->add_action('manage_posts_extra_tablenav', $plugin_admin, 'wcmlim_admin_order_list_top_bar_button');

    $this->loader->add_action('added_post_meta', $plugin_admin, 'wcmlim_sync_on_product_save', 10, 4);
    $this->loader->add_action('updated_post_meta', $plugin_admin, 'wcmlim_sync_on_product_save', 10, 4);
    $this->loader->add_action("wp_ajax_wcmlim_get_lat_lng", $plugin_admin, "wcmlim_get_lat_lng");

    /////////////////////////////////////////////////////////////////
    $this->loader->add_action("wp_ajax_wcmlim_get_lcpriority", $plugin_admin, "wcmlim_get_lcpriority");
    $this->loader->add_action("wp_ajax_priv_wcmlim_get_lcpriority", $plugin_admin, "wcmlim_get_lcpriority");
    // for showing to location shop manager his location instock products
    $this->loader->add_action("pre_get_posts", $plugin_admin, "exclude_other_location_products");
    $isShippingMethods = get_option('wcmlim_enable_shipping_methods');
    if ($isShippingMethods == "on") {
      require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/wcmlim-shipping-instance-location.php';
    }
  }
  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */

  private function define_public_hooks()
  {
    if (get_option('wcmlim_license') != '' || get_option('wcmlim_license') == 'valid') {
      $plugin_public = new Wcmlim_Public($this->get_plugin_name(), $this->get_version());

      // die('entre aca');
      $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
      $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
      $this->loader->add_filter('script_loader_tag', $plugin_public, 'wcmlim_add_type_attribute_scripts', 10, 3);
      $hide_out_of_stock_location = get_option('wcmlim_hide_show_location_dropdown');
      $specific_location = 'on';
      $geo_location = get_option('wcmlim_geo_location');
      $enable_price = 'on';
      $ispreferred = get_option('wcmlim_preferred_location');
      $coordinates_calculator = get_option('wcmlim_distance_calculator_by_coordinates');
      $isshipping = get_option('wcmlim_enable_shipping_zones');
      $wcmlim_location_fee = get_option('wcmlim_location_fee');
      $locationOnShop = get_option('wcmlim_enable_location_onshop');
      $locationPriceOnShop = get_option('wcmlim_enable_location_price_onshop');
      $shortShopAsLocation = get_option("wcmlim_hide_outofstock_products");
      $widgetOnShop = get_option("wcmlim_use_location_widget");
      $restricUsers = get_option('wcmlim_enable_userspecific_location');
      $isLocationsGroup = get_option('wcmlim_enable_location_group');
      $suggestion_off = get_option("wcmlim_suggestion_off");
      $auto_billing_address = get_option("wcmlim_auto_billing_address");

      if ($auto_billing_address == "on") {
        $this->loader->add_action('wp_ajax_wcmlim_order_review', $plugin_public, 'wcmlim_order_review');
        $this->loader->add_action('wp_ajax_nopriv_wcmlim_order_review', $plugin_public, 'wcmlim_order_review'); // For non-logged-in users
      }
      if (($restricUsers == "on") && (get_current_user_id())) {

        add_action('wp_enqueue_scripts', function () {
          wp_enqueue_script('usspecloc_js', plugins_url('', dirname(__FILE__)) . '/public/js/specloc-min.js', array('jquery'), rand());
        });
      }
      if ($isLocationsGroup == "on") {
        $this->loader->add_short_code('wcmlim_loc_storedropdown', $plugin_public, 'woo_storelocator_dropdown');
      }
      if ($ispreferred == 'on') {

        $this->loader->add_action('init', $plugin_public, 'handle_switch_form_submit');

        $this->loader->add_short_code('wcmlim_locations_switch', $plugin_public, 'woo_switch_content');
        $this->loader->add_action('wp_ajax_wcmlim_closest_location', $plugin_public, 'wcmlim_closest_location');
        $this->loader->add_action('wp_ajax_nopriv_wcmlim_closest_location', $plugin_public, 'wcmlim_closest_location');
        $this->loader->add_action('woocommerce_product_query', $plugin_public, 'wcmlim_change_product_query');
        // display an 'Out of Stock' label on archive pages
        $this->loader->add_action('woocommerce_before_shop_loop_item_title', $plugin_public, 'woocommerce_template_loop_stock', 10);
      }

      $isPaymentMethods = get_option('wcmlim_assign_payment_methods_to_locations');
      if ($isPaymentMethods == "on") {
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/locationwisePaymentMethod.php';
      }
      $isClearCart = get_option('wcmlim_clear_cart');
      if ($isClearCart == "on") {
        $this->loader->add_action('woocommerce_cart_item_removed', $plugin_public, 'action_woocommerce_cart_item_removed', 10, 2);
        $this->loader->add_action('wp_ajax_wcmlim_empty_cart_content', $plugin_public, 'wcmlim_empty_cart_content');
        $this->loader->add_action('wp_ajax_nopriv_wcmlim_empty_cart_content', $plugin_public, 'wcmlim_empty_cart_content');

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts_clear_cart', 99);
      }

      if ($isshipping == 'on') {
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/displayShippingZone.php';
      }
      if ($wcmlim_location_fee == 'on') {
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wcmlim_locationfee.php';
      }

      $this->loader->add_action('wp_ajax_wcmlim_closest_location', $plugin_public, 'wcmlim_closest_location');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_closest_location', $plugin_public, 'wcmlim_closest_location');

      $this->loader->add_action('wp_ajax_wcmlim_closest_instock_location', $plugin_public, 'wcmlim_closest_instock_location');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_closest_instock_location', $plugin_public, 'wcmlim_closest_instock_location');


      $this->loader->add_action('woocommerce_before_add_to_cart_button', $plugin_public, 'wcmlim_display_location');

      $this->loader->add_action('wp_ajax_wcmlim_display_location', $plugin_public, 'wcmlim_display_location_dropdown');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_display_location', $plugin_public, 'wcmlim_display_location_dropdown');

      $this->loader->add_action('wp_ajax_wcmlim_drop2_location', $plugin_public, 'wcmlim_getdropdown_location');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_drop2_location', $plugin_public, 'wcmlim_getdropdown_location');

      $this->loader->add_filter('woocommerce_add_cart_item_data', $plugin_public, 'wcmlim_add_location_item_data', 10, 4);
      $this->loader->add_filter('woocommerce_cart_item_name', $plugin_public, 'wcmlim_cart_item_name', 10, 3);

      $this->loader->add_action('woocommerce_add_order_item_meta', $plugin_public, 'add_order_item_meta', 10, 3);

      //removing extra meta data from email
      $this->loader->add_filter('woocommerce_order_item_get_formatted_meta_data', $plugin_public, 'wcmlim_remove_item_meta_from_mail', 10, 3);
      $this->loader->add_filter('woocommerce_hidden_order_itemmeta', $plugin_public, 'hidden_order_itemmeta', 50);
      // modify input on cart page as per location quantity
      $this->loader->add_filter('woocommerce_quantity_input_args', $plugin_public, 'wcmlim_max_qty_input_args', 10, 2);
      /**Custom Notice message */
      $this->loader->add_filter('woocommerce_get_script_data', $plugin_public, 'wcmlim_get_wc_script_data', 10, 2);
      if ($enable_price == 'on') {
        // hook for modify product price as per location
        // $this->loader->add_action('woocommerce_before_calculate_totals', $plugin_public, 'wcmlim_add_custom_price');
        $this->loader->add_filter('woocommerce_cart_item_price', $plugin_public, 'wcmlim_cart_item_price', 10, 3);
        $this->loader->add_filter('woocommerce_product_price_class', $plugin_public, 'wcmlim_woocommerce_price_class', 10, 2);
      }

      if ($locationOnShop == 'on') {
        $this->loader->add_action('woocommerce_after_shop_loop_item', $plugin_public, 'wcmlim_show_stock_shop', 10);
      }
      if ($locationPriceOnShop == 'on') {
        $this->loader->add_filter('woocommerce_get_price_html', $plugin_public, 'wcmlim_change_product_price', 10, 2);
      }
      if ($hide_out_of_stock_location == 'on') {
        $this->loader->add_filter('woocommerce_get_availability_text', $plugin_public, 'wcmlim_stock_availability_for_each_location', 10, 2);
      }
      if ($specific_location == 'on') {
        $this->loader->add_filter('woocommerce_get_availability_text', $plugin_public, 'wcmlim_stock_availability_for_each_location', 10, 2);
      }
      $this->loader->add_filter('woocommerce_loop_add_to_cart_link', $plugin_public, 'wcmlim_replacing_add_to_cart_button', 10, 2);
      $this->loader->add_action('wp_ajax_wcmlim_ajax_add_to_cart', $plugin_public, 'wcmlim_ajax_add_to_cart');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_ajax_add_to_cart', $plugin_public, 'wcmlim_ajax_add_to_cart');

      $this->loader->add_action('wp_ajax_wcmlim_ajax_validation_manage_stock', $plugin_public, 'wcmlim_ajax_validation_manage_stock');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_ajax_validation_manage_stock', $plugin_public, 'wcmlim_ajax_validation_manage_stock');


      // Checking and validating when products are added to cart    
      $this->loader->add_filter('woocommerce_add_to_cart_validation', $plugin_public, 'wcmlim_select_location_validation', 10, 5);
      $this->loader->add_filter('woocommerce_add_to_cart_validation', $plugin_public, 'wcmlim_location_stock_allowed_add_to_cart', 10, 3);

      $this->loader->add_action('init', $plugin_public, 'wcmlim_url_init');
      $this->loader->add_action('template_redirect', $plugin_public, 'wcmlim_url_redirect');
      $this->loader->add_action('wp_ajax_wcmlim_calculate_distance_search', $plugin_public, 'wcmlim_calculate_distance_search');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_calculate_distance_search', $plugin_public, 'wcmlim_calculate_distance_search');
      $this->loader->add_action('wp_ajax_wcmlim_filter_map_product_wise', $plugin_public, 'wcmlim_filter_map_product_wise');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_filter_map_product_wise', $plugin_public, 'wcmlim_filter_map_product_wise');

      $this->loader->add_action("woocommerce_payment_complete", $plugin_public, "wcmlim_maybe_reduce_stock_levels");
      $this->loader->add_action('woocommerce_order_status_completed', $plugin_public, "wcmlim_maybe_reduce_stock_levels");
      $this->loader->add_action('woocommerce_order_status_processing', $plugin_public, "wcmlim_maybe_reduce_stock_levels");
      $this->loader->add_action('woocommerce_order_status_on-hold', $plugin_public, "wcmlim_maybe_reduce_stock_levels");

      $this->loader->add_action('woocommerce_order_status_cancelled', $plugin_public, 'wcmlim_maybe_increase_stock_levels');
      $this->loader->add_action('woocommerce_order_status_refunded', $plugin_public, 'wcmlim_maybe_increase_stock_levels');
      $this->loader->add_action('woocommerce_order_status_pending', $plugin_public, 'wcmlim_maybe_increase_stock_levels');

      // * OpenPOS Compatibility
      $wcmlim_pos_compatibility = get_option('wcmlim_pos_compatiblity');
      if ($wcmlim_pos_compatibility == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        $this->loader->add_action('woocommerce_order_status_processing_to_refunded', $plugin_public, 'restore_order_stock', 10, 1);
      }
      $this->loader->add_action('woocommerce_product_query', $plugin_public, 'wcmlim_change_cookie_change_location');
      $this->loader->add_action('wp_ajax_wcmlim_ajax_cart_count', $plugin_public, 'wcmlim_ajax_cart_count');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_ajax_cart_count', $plugin_public, 'wcmlim_ajax_cart_count');
      //hook before proceed to checkout button
      $this->loader->add_action('woocommerce_after_checkout_validation', $plugin_public, 'wcmlim_proceed_to_checkout');

      if ($shortShopAsLocation == 'on') {
        $this->loader->add_action('woocommerce_product_query', $plugin_public, 'wcmlim_change_product_query');
        // display an 'Out of Stock' label on archive pages
        $this->loader->add_action('woocommerce_before_shop_loop_item_title', $plugin_public, 'woocommerce_template_loop_stock', 10);
      }

      if ($widgetOnShop == 'on') {
        $this->loader->add_action('woocommerce_product_query', $plugin_public, 'wcmlim_widget_product_query');
      }

      $this->loader->add_short_code('wcmlim_location_info', $plugin_public, 'woo_locations_info');
      $this->loader->add_short_code('wcmlim_location_finder', $plugin_public, 'woo_store_finder');
      $this->loader->add_short_code('wcmlim_location_finder_list_view', $plugin_public, 'woo_store_finder_list_view');


      // Product Page Shortcode with location id attribute
      $this->loader->add_filter('shortcode_atts_products', $plugin_public, 'wcmlim_shortcode_atts_products', 10, 4);
      $this->loader->add_filter('woocommerce_shortcode_products_query', $plugin_public, 'wcmlim_woocommerce_shortcode_products_query', 10, 2);
      $this->loader->add_filter('woocommerce_update_cart_validation', $plugin_public, 'wc_qty_update_cart_validation', 10, 4);
      // * popup settings
      require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/shortcodes/wcmlim-location-popup.php';

      //display tax on checkout according to locations
      $isTaxLocation = get_option('wcmlim_allow_tax_to_locations');
      if ($isTaxLocation == "on") {
        $this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'action_woocommerce_add_tax_each_location', 10, 2);
      }

      //add tax class for each line item
      $isTaxClassLineItem = get_option('wcmlim_enable_tax_to_each_item');
      if ($isTaxClassLineItem == 'on') {
        add_action('woocommerce_cart_totals_get_item_tax_rates', $plugin_public, 'overwrite_tax_calculation_to_use_product_tax_class_and_location_zip_code', 10, 3);
      }

      // *WooCommerce shipstation compatibility

      if (in_array('woocommerce-shipstation-integration/woocommerce-shipstation.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_filter('woocommerce_shipstation_export_custom_field_2', array($plugin_public, 'wcmlim_shipstation_custom_field_2'), 10, 2);
      }

      $b4L = 'on';
      if (true) {
        //Backorder for each location
        $this->loader->add_action('wp_ajax_wcmlim_backorder4el', $plugin_public, 'wcmlim_backorder4el');
        $this->loader->add_action('wp_ajax_nopriv_wcmlim_backorder4el', $plugin_public, 'wcmlim_backorder4el');
      }

      $specific_location = 'on';
      if (true) {
        //allow each location
        $this->loader->add_action('wp_ajax_wcmlim_allow_each_location', $plugin_public, 'wcmlim_allow_each_location');
        $this->loader->add_action('wp_ajax_nopriv_wcmlim_allow_each_location', $plugin_public, 'wcmlim_allow_each_location');
      }

      //advanced list view ajax for location information
      $this->loader->add_action('wp_ajax_wcmlim_prepare_advanced_view_information', $plugin_public, 'wcmlim_prepare_advanced_view_information');
      $this->loader->add_action('wp_ajax_nopriv_wcmlim_prepare_advanced_view_information', $plugin_public, 'wcmlim_prepare_advanced_view_information');
      $service_radius_for_location = get_option('wcmlim_service_radius_for_location');
      if ($service_radius_for_location == 'on') {
        $this->loader->add_filter('woocommerce_package_rates', $plugin_public, 'checking_order_is_in_location_radius', 10, 2);

      }
      // variation product hide of setting

      $this->loader->add_action('wp_ajax_action_variation_dropdown', $plugin_public, 'wcmlim_display_selected_location_dropdown');
      $this->loader->add_action('wp_ajax_nopriv_action_variation_dropdown', $plugin_public, 'wcmlim_display_selected_location_dropdown');


      //restrict user instock quantity text change
      $this->loader->add_filter('woocommerce_get_availability_text', $plugin_public, 'wcmlim_restrict_user_instock_quantity_text', 99, 2);

      $isLocationsGroup = get_option('wcmlim_enable_location_group');
      $isLimitLocation = get_option('wcmlim_clear_cart');
      ;
      if ($isLocationsGroup == "on" && $isLimitLocation == "on") {
        //$this->loader->add_action('woocommerce_before_cart', $plugin_public, 'check_stock_for_location_group', 10, 1);
      }
    }


  }

  private function define_product_taxonomy()
  {
    new Wcmlim_Product_Taxonomy();
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();
  }


  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name()
  {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Wcmlim_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader()
  {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version()
  {
    return $this->version;
  }
}
