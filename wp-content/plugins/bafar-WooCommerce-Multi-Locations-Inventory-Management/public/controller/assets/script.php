<?php
// $pl_enable = get_option('wcmlim_preferred_location');
// $api_key = get_option('wcmlim_google_api_key');
// $autodetect = get_option('wcmlim_enable_autodetect_location');
// $autodetect_by_maxmind = get_option('wcmlim_enable_autodetect_location_by_maxmind');
// $enable_price = 'on';
// $uspecLoc = get_option('wcmlim_enable_userspecific_location');
// $showLS = get_option("wcmlim_show_location_selection");
// $instock = get_option('wcmlim_instock_button_text');
// $instock = __($instock, 'wcmlim');
// $soldout = get_option('wcmlim_soldout_button_text');
// $soldout = __($soldout, 'wcmlim');
// $showNxtLoc = get_option("wcmlim_next_closest_location");
// $store_on_map_arr = get_option("store_on_map_arr");
// $store_on_map_prod_arr = get_option("store_on_map_prod_arr");
// $default_list_align = get_option('wcmlim_default_list_align');
// $default_origin_center = get_option('wcmlim_default_origin_center');
// $default_zoom = get_option('wcmlim_default_zoom');
// $setting_loc_dis_unit = get_option("wcmlim_show_location_distance", true);
// $default_map_color = get_option('wcmlim_default_map_color');
// $widget_select_type = get_option('wcmlim_widget_select_mode');
// $optiontype_loc = get_option('wcmlim_select_or_dropdown');
// $scoptiontype_loc = get_option('wcmlim_listing_inline_location');
// $fulladd = get_option('wcmlim_radio_loc_fulladdress');
// $viewformat = get_option('wcmlim_radio_loc_format');
// $wchideoosproduct = get_option("woocommerce_hide_out_of_stock_items");
// $isClearCart = get_option('wcmlim_clear_cart');
// $isdefault = get_option('wcmlim_enable_default_location');
// $stock_display_format = get_option('woocommerce_stock_format');
// $isLocationsGroup = get_option('wcmlim_enable_location_group');
// $current_user_id = get_current_user_id();
// $isAdmin = current_user_can('administrator', $current_user_id);
// $specificLocation = get_user_meta($current_user_id, 'wcmlim_user_specific_location', true);
// $getdirection = get_option('wcmlim_get_direction_for_location');
// $coordinates_calculator = get_option('wcmlim_distance_calculator_by_coordinates');
// $suggestion_off = get_option('wcmlim_suggestion_off');
// $geolocation = get_option('wcmlim_geo_location');
// $auto_billing_address = get_option("wcmlim_auto_billing_address");

// wp_enqueue_script('wcmlim-sweet-js', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/sweetalert2@10-min.js', array('jquery'), $this->version, true);

// wp_enqueue_script($this->plugin_name . '_google_places', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places", array('jquery'), $this->version, true);
// wp_enqueue_script($this->plugin_name . '_chosen_js_public', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/chosen.jquery.min.js', array('jquery'), $this->version, false);
// // wcmlim_ajax_add_to_cart
// wp_enqueue_script($this->plugin_name . '_add_to_cart_js', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/ajax-add-to-cart-min.js', array('jquery'), $this->version . rand(), true);
// // Enqueue the script
// wp_enqueue_script($this->plugin_name, plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-public.js', array('jquery'), $this->version . rand(), true);
// $script_data_array = array(
//     'hideDetailsOnHover' => true, // this is just an example flag
// );
// wp_localize_script($this->plugin_name, 'wcmlim_public', $script_data_array);




// wp_enqueue_script($this->plugin_name . 'localization', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim_utility/generic/wcmlim_localization.js', array('jquery'), $this->version . rand(), true);
// wp_enqueue_script($this->plugin_name . 'localization', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim_utility/generic/wcmlim_listOrdering.js', array('jquery'), $this->version . rand(), true);


// wp_enqueue_script($this->plugin_name . '-popup-js', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-popup-min.js', array('jquery'), $this->version . rand(), true);
// if ($autodetect_by_maxmind == 'on') {
//     wp_enqueue_script($this->plugin_name . '_maxmind', 'https://geoip-js.com/js/apis/geoip2/v2.1/geoip2.js', array('jquery'), $this->version . rand(), true);
//     wp_enqueue_script($this->plugin_name . '_maxmind_geocode', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-autodetect-maxmind.js', array('jquery'), $this->version . rand(), true);

// }

// if ($isLocationsGroup == "on") {
//     wp_enqueue_script($this->plugin_name . '_locator', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-locator-min.js', array('jquery'), $this->version . rand(), true);
//     if ($getdirection == 'on') {
//         wp_enqueue_script($this->plugin_name . '_getdirlocationgroup', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-getdirlocationgroup-min.js', array('jquery'), $this->version . rand(), true);
//     }
// }
// if ($getdirection == 'on') {
//     wp_enqueue_script($this->plugin_name . '_direction', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-getdirection-min.js', array('jquery'), $this->version . rand(), true);
// }
// if ($showNxtLoc == 'on' && $coordinates_calculator != 'on') {
//     wp_enqueue_script($this->plugin_name . '_direction', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-get-next-closest-location-min.js', array('jquery'), $this->version . rand(), true);
// }

// wp_enqueue_script($this->plugin_name . 'product_single_validation', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-product-single-validation-min.js', array('jquery'), $this->version . rand(), true);
// $backorder_for_locations = 'on';
// $specific_location = 'on';
// $hideDropdown = get_option('wcmlim_hide_show_location_dropdown');

// wp_localize_script($this->plugin_name, 'multi_inventory', array(
//     "ajaxurl" => admin_url("admin-ajax.php"),
//     "wc_currency" => get_woocommerce_currency_symbol(),
//     "autodetect" => $autodetect,
//     "autodetect_by_maxmind" => $autodetect_by_maxmind,
//     "stock_format" => $stock_display_format,
//     "enable_price" => $enable_price,
//     "specific_location" => $specific_location,
//     "user_specific_location" => $uspecLoc,
//     "show_location_selection" => $showLS,
//     "instock" => $instock,
//     "soldout" => $soldout,
//     "nxtloc" => $showNxtLoc,
//     "store_on_map_arr" => $store_on_map_arr,
//     "store_on_map_prod_arr" => $store_on_map_prod_arr,
//     "default_list_align" => $default_list_align,
//     "default_origin_center" => $default_origin_center,
//     "default_zoom" => $default_zoom,
//     "setting_loc_dis_unit" => $setting_loc_dis_unit,
//     "default_map_color" => $default_map_color,
//     "widget_select_type" => $widget_select_type,
//     "optiontype_loc" => $optiontype_loc,
//     "scoptiontype_loc" => $scoptiontype_loc,
//     "fulladd" => $fulladd,
//     "viewformat" => $viewformat,
//     'NextClosestinStock' => esc_html__('Next Closest in Stock', 'wcmlim'),
//     'away' => esc_html__('away', 'wcmlim'),
//     'wchideoosproduct' => $wchideoosproduct,
//     "isClearCart" => $isClearCart,
//     "isdefault" => $isdefault,
//     "isUserLoggedIn" => is_user_logged_in(),
//     "loginURL" => get_permalink(get_option('woocommerce_myaccount_page_id')),
//     "isUserAdmin" => $isAdmin,
//     "resUserSLK" => $specificLocation,
//     "isLocationsGroup" => $isLocationsGroup,
//     "isBackorderOn" => $backorder_for_locations,
//     "hideDropdown" => $hideDropdown,
//     'hideSuggestion' => $suggestion_off,
//     'auto_billing_address' => $auto_billing_address,

// ));
// /* fontawesome */
// wp_enqueue_script('wcmlim-fontawesome', "https://kit.fontawesome.com/82940a45e9.js", array('jquery'), $this->version, true);
// $advanceListView = get_option('wcmlim_radio_loc_format');
// $optiontype_loc = get_option('wcmlim_select_or_dropdown');
// if ($advanceListView == "advanced_list_view" && $optiontype_loc == 'on') {
//     wp_enqueue_script($this->plugin_name . '_list_view_pro', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/wcmlim-advanced-list-view.js', array(), $this->version . rand(), 'all');
// }

$up = wp_upload_dir();
$statesFile = trailingslashit($up['basedir']) . 'wcmlim/states.json';
$indexFile = trailingslashit($up['basedir']) . 'wcmlim/stores/index.json';
$statesURL = trailingslashit($up['baseurl']) . 'wcmlim/states.json';
$indexURL = trailingslashit($up['baseurl']) . 'wcmlim/stores/index.json';
$statesV = file_exists($statesFile) ? filemtime($statesFile) : time();
$indexV = file_exists($indexFile) ? filemtime($indexFile) : time();

$data = ['stores' => ['states' => ['url' => $statesURL, 'v' => $statesV], 'index' => ['url' => $indexURL, 'v' => $indexV],],];

$hHelper = $this->plugin_name . '-store-helper';
$hApp = $this->plugin_name . '-store-app';

// helper
wp_register_script($hHelper, WCMLIM_URL_PATH . 'public/js/store/helper.js', [], filemtime(WCMLIM_DIR_PATH . 'public/js/store/helper.js'), true);
wp_localize_script($hHelper, 'multi_inventory', $data); // tus URLs + v
wp_enqueue_script($hHelper);

// app depende de helper
wp_register_script($hApp, WCMLIM_URL_PATH . 'public/js/store/app.js', [$hHelper], filemtime(WCMLIM_DIR_PATH . 'public/js/store/app.js'), true);
wp_enqueue_script($hApp);

// (Opcional en WP>=6.3) defer
wp_script_add_data($hHelper, 'strategy', 'defer');
wp_script_add_data($hApp, 'strategy', 'defer');