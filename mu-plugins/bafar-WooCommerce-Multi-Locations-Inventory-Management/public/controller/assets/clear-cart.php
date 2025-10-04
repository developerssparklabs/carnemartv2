<?php
$isClearCart = get_option('wcmlim_clear_cart');
$autodetect = get_option('wcmlim_enable_autodetect_location');
$autodetect_by_maxmind = get_option('wcmlim_enable_autodetect_location_by_maxmind');
$enable_price     = 'on';
$uspecLoc = get_option('wcmlim_enable_userspecific_location');
$showLS = get_option('wcmlim_show_location_selection');
$instock     = get_option('wcmlim_instock_button_text');
$soldout = get_option('wcmlim_soldout_button_text');
$showNxtLoc = get_option("wcmlim_next_closest_location");
$optiontype_loc = get_option('wcmlim_select_or_dropdown');		
$scoptiontype_loc = get_option('wcmlim_listing_inline_location');		
$fulladd = get_option('wcmlim_radio_loc_fulladdress');
$viewformat = get_option('wcmlim_radio_loc_format');
$default_origin_center = get_option('wcmlim_default_origin_center');
$setting_loc_dis_unit = get_option("wcmlim_show_location_distance", true);
$store_on_map_arr = get_option("store_on_map_arr");		
$store_on_map_prod_arr = get_option("store_on_map_prod_arr");
$default_list_align = get_option('wcmlim_default_list_align');
$default_zoom = get_option('wcmlim_default_zoom');	
$default_map_color = get_option('wcmlim_default_map_color');
$wchideoosproduct = get_option("woocommerce_hide_out_of_stock_items");
$isdefault   = get_option('wcmlim_enable_default_location');
$stock_display_format = get_option('woocommerce_stock_format');		
$cart_valid_message = get_option('wcmlim_valid_cart_message');
$cart_valid_buttontxt = get_option('wcmlim_btn_cartclear');
$popup_headtxt = get_option('wcmlim_cart_popup_heading');
$popup_mssgtxt = get_option('wcmlim_cart_popup_message');
$current_user_id = get_current_user_id();
$isAdmin = current_user_can('administrator', $current_user_id);
$getdirection = get_option('wcmlim_get_direction_for_location');
$specificLocation = get_user_meta($current_user_id, 'wcmlim_user_specific_location', true);	
$isLocationsGroup = get_option('wcmlim_enable_location_group');

if ($isClearCart == 'on') {
    wp_enqueue_script('woocommerce-ajax-add-to-cart', plugin_dir_path(plugin_dir_url(__DIR__)) . 'js/clear-cart-min.js', array('jquery'), $this->version . rand(), true);
    wp_localize_script($this->plugin_name, 'multi_inventory', array(
        "ajaxurl" => admin_url("admin-ajax.php"),	
        "wc_currency" => get_woocommerce_currency_symbol(),	
        "autodetect_by_maxmind" => $autodetect_by_maxmind,		
        "autodetect" => $autodetect,
        "enable_price" => $enable_price,
        "user_specific_location" => $uspecLoc,
        "show_location_selection" => $showLS,
        "instock" => $instock,
        "soldout" => $soldout,
        "optiontype_loc" => $optiontype_loc,
        "scoptiontype_loc" => $scoptiontype_loc,
        "fulladd" => $fulladd,
        "viewformat" => $viewformat,
        'swal_cart_update_btn' => $cart_valid_buttontxt, 			
        'swal_cart_validation_message' => $cart_valid_message,
        'swal_cart_update_heading' => $popup_headtxt, 
        'swal_cart_update_message' => $popup_mssgtxt,
        "nxtloc" => $showNxtLoc,
        "default_origin_center" => $default_origin_center,
        "setting_loc_dis_unit" => $setting_loc_dis_unit,
        "store_on_map_arr" => $store_on_map_arr,
        "store_on_map_prod_arr" => $store_on_map_prod_arr,
        'away' => esc_html__('away', 'wcmlim'),
        "default_list_align" => $default_list_align,
        "default_zoom" => $default_zoom,
        "default_map_color" => $default_map_color,
        "wchideoosproduct" => $wchideoosproduct,
        "isClearCart" => $isClearCart,
        "NextClosestinStock" => esc_html__('Next Closest in Stock', 'wcmlim'),
        "isdefault" => $isdefault,
        "stock_format" => $stock_display_format,
        "isUserLoggedIn" => is_user_logged_in(),
        "loginURL" => get_permalink(get_option('woocommerce_myaccount_page_id')),
        "isUserAdmin" => $isAdmin,
        "resUserSLK" => $specificLocation,
        "isLocationsGroup" => $isLocationsGroup,
    ));
}