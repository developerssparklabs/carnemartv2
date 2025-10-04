<?php
global $post;
$hideDropdown = get_option('wcmlim_hide_show_location_dropdown');

    //for out of stock button text
  $soldoutbuttontext = get_option("wcmlim_soldout_button_text");
  $soldbtntext = array(
    'keys' => $soldoutbuttontext,
  );
  wp_localize_script( $this->plugin_name, 'passedSoldbtn', $soldbtntext );

   //for In stock button text
  
   $instockbuttontext = get_option("wcmlim_instock_button_text");
   $instobtntext = array(
     'keys' => $instockbuttontext,
   );
   wp_localize_script( $this->plugin_name, 'passedinstockbtn', $instobtntext);
   
   //for Backorder button text

   $backorderbuttontext = get_option("wcmlim_onbackorder_button_text");
   $backbtntext = array(
     'keys' => $backorderbuttontext,
   );
   wp_localize_script( $this->plugin_name, 'passedbackorderbtn', $backbtntext);
$restrictGuest = get_option('wcmlim_enable_restrict_guestuser_location');
  $selectedGuestLoc = get_option('wcmlim_restrict_guest_user_location');

$excludeLocations = get_option("wcmlim_exclude_locations_from_frontend");
if($restrictGuest == 'on' && !empty($selectedGuestLoc) && !is_user_logged_in()){
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'include' => $selectedGuestLoc));
}elseif (!empty($excludeLocations)) {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $excludeLocations));
} else {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
}
$preffLocation = (isset($_COOKIE['wcmlim_selected_location']) && $_COOKIE['wcmlim_selected_location'] != 'default') ? $_COOKIE['wcmlim_selected_location'] : false;
$enable_price = 'on';
$hideDropdown = get_option('wcmlim_hide_show_location_dropdown');
$stock_display_format = get_option('woocommerce_stock_format');
/**Display setting option */
$stockbox_color = get_option("wcmlim_preview_stock_bgcolor");
$txt_stock_inf = get_option("wcmlim_txt_stock_info");
$txtcolor_stock_inf = get_option("wcmlim_txtcolor_stock_info");
$display_stock_inf = get_option("wcmlim_display_stock_info");
$txt_preferred = get_option("wcmlim_txt_preferred_location");
$txtcolor_preferred = get_option("wcmlim_txtcolor_preferred_loc");
$display_preferred = get_option("wcmlim_display_preferred_loc");
$txt_nearest = get_option("wcmlim_txt_nearest_stock_loc");
$txtcolor_nearest = get_option("wcmlim_txtcolor_nearest_stock");
$display_nearest = get_option("wcmlim_display_nearest_stock");
$color_separator = get_option("wcmlim_separator_linecolor");
$display_separator = get_option("wcmlim_display_separator_line");
$oncheck_btntxt = get_option("wcmlim_oncheck_button_text");
$oncheck_btnbgcolor = get_option("wcmlim_oncheck_button_color");
$oncheck_btntxtcolor = get_option("wcmlim_oncheck_button_text_color");
$soldout_btntxt = get_option("wcmlim_soldout_button_text");
$soldout_btnbgcolor = get_option("wcmlim_soldout_button_color");
$soldout_btntxtcolor = get_option("wcmlim_soldout_button_text_color");
$instock_btntxt = get_option("wcmlim_instock_button_text");
$instock_btnbgcolor = get_option("wcmlim_instock_button_color");
$instock_btntxtcolor = get_option("wcmlim_instock_button_text_color");
$border_option = get_option("wcmlim_preview_stock_borderoption");
$border_color = get_option("wcmlim_preview_stock_bordercolor");
$border_width = get_option("wcmlim_preview_stock_border");
$border_radius = get_option("wcmlim_preview_stock_borderradius");
$refborder_radius = get_option("wcmlim_refbox_borderradius");
$input_radius = get_option("wcmlim_input_borderradius");
$oncheck_radius = get_option("wcmlim_oncheck_borderradius");
$instock_radius = get_option("wcmlim_instock_borderradius");
$soldout_radius = get_option("wcmlim_soldout_borderradius");
$showNxtLoc = get_option("wcmlim_next_closest_location");
$boxwidth = get_option("wcmlim_preview_stock_width");
$sel_padtop = get_option("wcmlim_sel_padding_top");
$sel_padright = get_option("wcmlim_sel_padding_right");
$sel_padbottom = get_option("wcmlim_sel_padding_bottom");
$sel_padleft = get_option("wcmlim_sel_padding_left");
$inp_padtop = get_option("wcmlim_inp_padding_top");
$inp_padright = get_option("wcmlim_inp_padding_right");
$inp_padbottom = get_option("wcmlim_inp_padding_bottom");
$inp_padleft = get_option("wcmlim_inp_padding_left");
$btn_padtop = get_option("wcmlim_btn_padding_top");
$btn_padright = get_option("wcmlim_btn_padding_right");
$btn_padbottom = get_option("wcmlim_btn_padding_bottom");
$btn_padleft = get_option("wcmlim_btn_padding_left");
$iconshow = get_option("wcmlim_display_icon");
$icon_color = get_option("wcmlim_iconcolor_loc");
$icon_size = get_option("wcmlim_iconsize_loc");
$is_padtop = get_option("wcmlim_is_padding_top");
$is_padbottom = get_option("wcmlim_is_padding_bottom");
$is_padright = get_option("wcmlim_is_padding_right");
$is_padleft = get_option("wcmlim_is_padding_left");
$sbox_padtop = get_option("wcmlim_sbox_padding_top");
$sbox_padbottom = get_option("wcmlim_sbox_padding_bottom");
$sbox_padright = get_option("wcmlim_sbox_padding_right");
$sbox_padleft = get_option("wcmlim_sbox_padding_left");
$sbox_bgcolor = get_option("wcmlim_selbox_bgcolor");
$optiontype_loc = get_option('wcmlim_select_or_dropdown');
$product = wc_get_product($post->ID);
$regprice = wc_price($product->get_price_html());
$isLocationsGroup = get_option('wcmlim_enable_location_group'); 
$backorderbuttontext = get_option("wcmlim_onbackorder_button_text");

// Check for the custom field value
foreach ($terms as $term) {
    if ($product instanceof WC_Product && $product->is_type('variable') && !$product->is_downloadable() && !$product->is_virtual()) {
        $variations = $product->get_available_variations();
        if (!empty($variations)) {
            foreach ($variations as $key => $value) {
                $check_taxanomy_variable =  get_post_meta($value['variation_id'], "wcmlim_stock_at_{$term->term_id}", true);
            }
        }
    } elseif ($product instanceof WC_Product && $product->is_type('simple') && !$product->is_downloadable() && !$product->is_virtual()) {
        $check_taxanomy = get_post_meta($post->ID, "wcmlim_stock_at_{$term->term_id}", true);
    }
}

if ($product instanceof WC_Product && $product->is_type('variable') && !$product->is_downloadable() && !$product->is_virtual()) {
    $variations = $product->get_available_variations();
    $manage_stock_yes = false; 
    if (!empty($variations)) { 
        // Flag to track if any variation has stock management set to 'yes'
  
            foreach($variations as $variation){
                $variation_id = $variation['variation_id'];
        
                $manage_stock = get_post_meta($variation_id, '_manage_stock', true);
                if($manage_stock == 'yes'){
                    // If manage stock is 'yes', set the flag to true and break the loop
                    $manage_stock_yes = true;
                    break;
                }
            }
      
        
        if (!$manage_stock_yes) {
            // If none of the variations have manage stock set to 'yes', check for 'no'
            foreach($variations as $variation){
                $variation_id = $variation['variation_id'];
        
                $manage_stock = get_post_meta($variation_id, '_manage_stock', true);
                if($manage_stock == 'no'){
                    // If manage stock is 'no', return
                    return;
                }
            }
        }
        
        if ($hideDropdown == "on") {
            ?>
            <style>
                .wcmlim_product
                {
                    display:none !important;
                }
            </style>
            <?php
            }
            ?>
        <div class="Wcmlim_container wcmlim_product">
            <div class="Wcmlim_box_wrapper">
                <div class="Wcmlim_box_content select_location-wrapper">
                    <div class="Wcmlim_box_header">
                        <h4 class="Wcmlim_box_title">
                        <?php if ($hideDropdown != "on") { 
                            if ($txt_stock_inf) {
                                echo $txt_stock_inf;
                            } else {
                                _e('Stock Information', 'wcmlim');
                            }
                         } ?>
                        </h4>
                    </div>
                    <div class="Wcmlim_prefloc_box">

                        <?php if ($hideDropdown == "on") { ?>
                         
                            <div class="loc_dd Wcmlim_prefloc_sel" style="display: none;">
                            <?php } else { ?>
                                <div class="loc_dd Wcmlim_prefloc_sel">
                                <?php } ?>
                                <label class="Wcmlim_sloc_label" for="select_location" style = "<?php if($display_preferred == 'on'){echo 'display:none';}?>"><?php if ($txt_preferred) {
                                                                                            echo $txt_preferred;
                                                                                        } else {
                                                                                            _e('Location: ', 'wcmlim');
                                                                                        } ?></label>
                                <i class="wc_locmap fa fa-map-marker-alt" style="font-size: 18px;"></i>
                                <?php if ($isLocationsGroup == 'on') { ?>
                                    <div class="wclim_select_location" style="display: inline-block;">
                                        <?php 
                                        echo do_shortcode( '[wcmlim_loc_storedropdown]' );												
                                        ?>
                                    </div>
                                    <style>
                                    .loc_dd.Wcmlim_prefloc_sel .select_location {
                                        display: none !important;
                                    }
                                    </style>
                                    <select class="select_location" name="select_location" id="select_location">
                                <?php } else { ?>
                                    <select class="select_location Wcmlim_sel_loc" name="select_location" id="select_location" required>
                                <?php } ?>
                                    <option data-lc-qty="" value=""><?php echo esc_html('- Select Location -', 'wcmlim'); ?></option>
                                </select>
                                <?php if ($isLocationsGroup == null || $isLocationsGroup == false ) { ?>
                                    <!-- Radio Listing Mode -->
                                    <div class="wcmlradio_box rselect_location"></div>
                                    <div class="wc_scrolldown">
                                        <p>Scroll Location</p>
                                        <i class="fas fa-chevron-circle-down"></i>												
                                    </div>
                                <?php } ?>
                                </div><!-- Div loc_dd -->
                            </div> <!-- Div Wcmlim_prefloc_box -->
                            <?php
                            $geolocation = get_option('wcmlim_geo_location');
                            if ($geolocation == "on") : ?>
                                <div class="postcode-checker">
                                    <p class="postcode-checker-title">
                                        <strong>
                                            <?php if ($txt_nearest) {
                                                echo $txt_nearest;
                                            } else {
                                                _e('Check your nearest stock location :', 'wcmlim');
                                            } ?>
                                        </strong>
                                    </p>
                                    <div class="postcode-checker-div postcode-checker-div-show">
                                        
                                        <?php
                                        $globpin = isset($_COOKIE['wcmlim_nearby_location']) ? $_COOKIE['wcmlim_nearby_location'] : "";
                                        $loc_dis_un = get_option('wcmlim_location_distance');
                                        ?>
                                        <input type="text" placeholder="<?php _e("Enter Pincode/Zipcode", "wcmlim",); ?>" class="class_post_code" name="post_code" value="<?php esc_html_e($globpin); ?>" id="elementId">
                                        
                                        <button class="button" type="button" id="submit_postcode_product" style="line-height: 1.4;border: 0;">
                                            <i class="fa fa-map-marker-alt"></i>
                                            <?php if ($oncheck_btntxt) {
                                                echo $oncheck_btntxt;
                                            } else {
                                                _e('Check', 'wcmlim');
                                            } ?>
                                        </button>
                                        <input type='hidden' name="global_postal_check" id='global-postal-check' value='true'>
                                        <input type='hidden' name="product_postal_location" id='product-postal-location' value='<?php esc_html_e($globpin); ?>'>
                                        <input type='hidden' name="product_location_distance" id='product-location-distance' value='<?php esc_html_e($loc_dis_un); ?>'>
                                    </div>
                                    <div class="search_rep" style="display: inline-flex;">
                                        <div class="postcode-checker-response"></div>
                                        <a class="postcode-checker-change postcode-checker-change-show" href="#" data-wpzc-form-open="" style="display: none;">
                                            <i class="fa fa-edit" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                    <div class="Wcmlim_loc_label">
                                        <div class="Wcmlim_locadd">
                                            <div class="selected_location_detail"></div>
                                            <div class="postcode-location-distance"></div>
                                        </div>
                                        <div class="Wcmlim_locstock"></div>
                                    </div>
                                    <?php if ($showNxtLoc  == "on") { ?>
                                        <div class="Wcmlim_nextloc_label">
                                            <div class="Wcmlim_nextlocadd">
                                                <div class="next_closest_location_detail"></div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="Wcmlim_messageerror"></div>
                                </div>
                        <?php
                            endif;
                        }
                    } elseif ($product instanceof WC_Product && $product->is_type('simple') && !$product->is_downloadable() && !$product->is_virtual()) {
                        $productOnBackorder = $product->backorders_allowed();
                        $product_id = $product->get_id();
                        $manage_stock = get_post_meta($product_id, '_manage_stock', true);
                        $stock_status = get_post_meta($product_id, '_stock_status', true);
                        // 1) Leer de la cookie la sucursal seleccionada
                        $preffLocationId = isset($_COOKIE['wcmlim_selected_location_termid'])
                            ? intval($_COOKIE['wcmlim_selected_location_termid'])
                            : 0;

                        // 2) Si hay una sucursal seleccionada
                        if ( $preffLocationId > 0 ) {
                            // 3) Iteramos sólo para encontrar el término que coincida
                            foreach ( $terms as $term ) {
                                if ( intval( $term->term_id ) === $preffLocationId ) {

                                    // 4) Leemos los metadatos justo para esa sucursal
                                    $stock_qty   = get_post_meta( $post->ID, "wcmlim_stock_at_{$term->term_id}", true );
                                    $instock_txt = __( 'Disponible', 'wcmlim' );

                                    // 5) Dependiendo del formato de stock imprimimos un <p> u otro
                                    if ( $stock_display_format === 'no_amount' ) {
                                        printf(
                                            '<p id="globMsg">%s en <strong>%s</strong></p>',
                                            esc_html( $instock_txt ),
                                            esc_html( ucfirst( $term->name ) )
                                        );
                                    } else {
                                        printf(
                                            '<p id="globMsg"><strong>%s</strong> %s en <strong>%s</strong></p>',
                                            esc_html( $stock_qty ),
                                            esc_html( $instock_txt ),
                                            esc_html( ucfirst( $term->name ) )
                                        );
                                    }

                                    // dejamos de recorrer una vez que lo encontramos
                                    break;
                                }
                            }

                        } else {
                            // 6) Si no hay cookie o no está definida, puedes mostrar stock global
                            // echo '<p id="globMsg">Stock global: <strong>' . esc_html( $product->get_stock_quantity() ) . '</strong></p>';
                        }
                        // echo $preffLocationId;
                        // echo '<pre>';
                        // print_r($terms);
                        // echo '</pre>';
                        if ($hideDropdown == "on" || $manage_stock == 'no' ){
                        ?>
                        <style>
                            .wcmlim_product
                            {
                                display:none !important;
                            }
                        </style>
                        <?php
                        }
                        ?>
                        <div class="Wcmlim_container wcmlim_product">
                            <div class="Wcmlim_box_wrapper">
                                <div class="Wcmlim_box_content select_location-wrapper">
                                    <div class="Wcmlim_box_header">
                                        <h3 class="Wcmlim_box_title">
                                            <?php
                                            if ($hideDropdown != "on") {
                                                 if ($txt_stock_inf) {
                                                echo $txt_stock_inf;
                                            } else {
                                                _e('Stock Information', 'wcmlim');
                                            }
                                         } ?>
                                        </h3>
                                    </div>
                                    <hr style="margin: 5px 0px;" class="Wcmlim_line_seperator">
                                    <div class="Wcmlim_prefloc_box">
                                        <?php if ($hideDropdown == "on") { ?>
                                            <div class="loc_dd Wcmlim_prefloc_sel" style="display: none;">
                                            <?php } else { ?>
                                                <div class="loc_dd Wcmlim_prefloc_sel">
                                                <?php } ?>
                                                <label class="Wcmlim_sloc_label" for="select_location" style = "<?php if($display_preferred == 'on'){echo 'display:none';}?>">
                                                    <?php if ($txt_preferred) {
                                                        echo $txt_preferred;
                                                    } else {
                                                        _e('Location: ', 'wcmlim');
                                                    } ?>
                                                </label>
                                                <i class="wc_locmap fa fa-map-marker-alt" style="font-size: 18px;"></i>
                                                <?php if ($isLocationsGroup == 'on') { ?>
                                                <div class="wclim_select_location" style="display: inline-block;">
                                                    <?php 
                                                    echo do_shortcode( '[wcmlim_loc_storedropdown]' );												
                                                    ?>
                                                </div>
                                                <style>
                                                .loc_dd.Wcmlim_prefloc_sel .select_location {
                                                    display: none !important;
                                                }
                                                </style>
                                                    <select class="select_location" name="select_location" id="select_location">
                                                <?php } else { ?>
                                                    <select class="select_location Wcmlim_sel_loc" name="select_location" id="select_location" required>
                                                <?php } ?>												
                                                <option data-lc-qty="" data-lc-sale-price="" data-lc-regular-price="<?php esc_attr_e($regprice); ?>" value="-1"><?php echo esc_html('- Select Location -', 'wcmlim'); ?></option>
                                                    
                                                    <?php						
                                                    $preffLocationId = isset($_COOKIE['wcmlim_selected_location_termid']) ? intval($_COOKIE['wcmlim_selected_location_termid']) : 0;
                                                    foreach ($terms as $k => $term) {
                                                        $isBackorderEnabled = get_post_meta($product_id, "wcmlim_allow_backorder_at_$term->term_id", true );
                                                        $allow_backorder_each_location = 'on';
                                                        
                                                        $stock_location_quantity = get_post_meta($post->ID, "wcmlim_stock_at_{$preffLocationId}", true);
                                                        $stock_regular_price = get_post_meta($post->ID, "wcmlim_regular_price_at_{$preffLocationId}", true);
                                                        $stock_sale_price = get_post_meta($post->ID, "wcmlim_sale_price_at_{$preffLocationId}", true);
                                                        if($stock_regular_price == '' || $stock_regular_price == '0.00')
                                                        {
                                                            $stock_regular_price = wc_price($product->get_regular_price());
                                                            $stock_sale_price = wc_price($product->get_sale_price());
                                                        }
                                                        $term_meta = get_option("taxonomy_$term->term_id");
                                                        $rl = $this->wcmlim_get_loactionaddress($term->term_id);																
                                                        if ($enable_price == "on") {
                                                            $price = "on";
                                                        } else {
                                                            $price = "off";
                                                        }
                                                        $hide_out_of_stock_location   = get_option('wcmlim_hide_out_of_stock_location');
                                                        $d_location   = get_option('wcmlim_enable_default_location');
                                                        $selDefLoc = get_post_meta($post->ID, "wcmlim_default_location", true);
                                                        $specific_location = 'on';
                                                        if ($specific_location == 'on') {
                                                        $allow_specific_location = get_post_meta($product_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, true);
                                                        $allow_specific_location = (empty($allow_specific_location)) ? 'Yes' : $allow_specific_location;		
                                                        }else{
                                                            $allow_specific_location = 'Yes';
                                                        }
                                                        if (!empty($stock_location_quantity)) {
                                                            if (isset($stock_location_quantity)) {
                                                                $response = "wcmlim_stock_at_{$term->term_id}";
                                                                $term_vals = get_term_meta($term->term_id);
                                                                foreach ($term_vals as $key => $val) {
                                                                    if ($key == 'wcmlim_postcode') {
                                                                        $lc_code = $val[0];
                                                                    }
                                                                } ?>
                                                                <?php if($allow_specific_location == 'Yes'){ 
                                                                    
                                                                    //get current user
                                                                    $current_user = wp_get_current_user();

                                                                    //get user specific location
                                                                    $user_specific_location = get_user_meta($current_user->ID, 'wcmlim_user_specific_location', true);
                                                                    if(get_option('wcmlim_enable_userspecific_location') != 'on'){
                                                                        $user_specific_location = "-1";
                                                                    }
                                                                    if($user_specific_location == $k || $user_specific_location == '-1'){
                                                                    
                                                                    ?>
                                                                <option 
                                                                <?php		
                                                                $variation_backorder = '';	
                                                                                                                            
                                                                if(!empty($d_location) && !empty($selDefLoc)){
                                                                    $_actualLcK = explode("_", $selDefLoc);
                                                                    if ($_actualLcK[1] == $k) echo "selected='selected'";
                                                                }else{
                                                                    if (preg_match('/^\d+$/', $preffLocation)) {
                                                                        if ($preffLocation == $k) echo "selected='selected'";
                                                                    } 
                                                                } 
                                                                if(($productOnBackorder) ||  ($isBackorderEnabled == "Yes" && $allow_backorder_each_location == "on")){
                                                                        $variation_backorder = 'yes';
                                                                }
                                                                
                                                                ?> 
                                                                value="<?php echo $k; ?>" data-lc-backorder="<?php echo $variation_backorder; ?>" data-lc-qty="<?php esc_attr_e($stock_location_quantity); ?>" data-lc-address="<?php esc_attr_e(base64_encode($rl)); ?>" data-lc-regular-price="<?php esc_attr_e(wc_price($stock_regular_price)); ?>" data-lc-sale-price="<?php ((!empty($stock_sale_price)) ?  esc_attr_e(wc_price($stock_sale_price)) : _e("undefined")); ?>" class="<?php echo 'wclimloc_'.$term->slug; ?>"  style="<?php if($allow_specific_location == 'No' ){echo 'display'.':'.'none';}else {echo 'display'.':'.'';} ?>"><?php 
                                                                    if($productOnBackorder){
                                                                        echo ucfirst($term->name) . ' - ' . __($backorderbuttontext, 'woocommerce');
                                                                    }else{
                                                                        if( $isBackorderEnabled == "Yes" && $allow_backorder_each_location == "on"){
                                                                            echo ucfirst($term->name) . ' - ' . __('Backorder', 'woocommerce');
                                                                        }
                                                                        else if (empty($stock_location_quantity) || $stock_location_quantity < 0)  { 
                                                                            echo ucfirst($term->name) . ' - ' . __($soldout_btntxt, 'wcmlim'); 
                                                                        } else {
                                                                                    echo ucfirst($term->name) . ' - ' . __($instock_btntxt, 'wcmlim');
                                                                        } 		
                                                                    }
                                                                ?>
                                                                        </option>
                                                                <?php
                                                                    }
                                                                } 
                                                            } 
                                                            
                                                        } else {
                                                            if (!$product->managing_stock() && $product->is_in_stock()) {
                                                                $stock_status = $product->get_stock_status();
                                                                                                                    if($stock_status == "instock"){
                                                                    $optionname = ucfirst($term->name) . ' - ' . __($instock_btntxt, 'wcmlim');
                                                                    $location_stock_status = 'instock';
                                                                }elseif($stock_status == "outofstock"){
                                                                    $optionname = ucfirst($term->name) . ' - ' . __($soldout_btntxt, 'wcmlim');
                                                                }elseif($stock_status == "onbackorder"){
                                                                    $optionname = ucfirst($term->name) . ' - ' . __($backorderbuttontext, 'woocommerce');
                                                                }
                                                                ?>
                                                                <option <?php if (preg_match('/^\d+$/', $preffLocation)) {
                                                                            if ($preffLocation == $k) echo "selected='selected'";
                                                                        } ?> class="<?php echo 'wclimloc_'.$term->slug; ?>"  value="<?php echo $k; ?>" data-lc-address="<?php esc_attr_e(base64_encode($rl));  ?>" data-lc-stockstatus="<?php echo $location_stock_status; ?>" style="<?php if($allow_specific_location == 'No' ){echo 'display'.':'.'none';}else {echo 'display'.':'.'';} ?>" ><?php echo $optionname; ?></option>
                                                                <?php
                                                            }else{	
                                                                if (isset($stock_location_quantity) && $hide_out_of_stock_location != "on") {
                                                                    $response = "wcmlim_stock_at_{$term->term_id}";
                                                                    $term_vals = get_term_meta($term->term_id);
                                                                    foreach ($term_vals as $key => $val) {
                                                                        if ($key == 'wcmlim_postcode') {
                                                                            $lc_code = $val[0];
                                                                        }
                                                                    } ?>
                                                                    <option
                                                                                                                                            <?php
                                                                                                                                            $variation_backorder = "";
                                                                    if(!empty($d_location) && !empty($selDefLoc)){
                                                                        $_actualLcK = explode("_", $selDefLoc);
                                                                        if ($_actualLcK[1] == $k) echo "selected='selected'";
                                                                    }else{
                                                                        if (preg_match('/^\d+$/', $preffLocation)) {
                                                                            if ($preffLocation == $k) echo "selected='selected'";
                                                                        } 
                                                                    }
                                                                    if(($productOnBackorder) ||  ($isBackorderEnabled == "Yes" && $allow_backorder_each_location == "on")){
                                                                        $variation_backorder = 'yes';
                                                                }
                                                                    $int_stock_location_quantity = intval($stock_location_quantity);
                                                                    ?>
                                                                    value="<?php echo $k; ?>" data-lc-backorder="<?php echo $variation_backorder; ?>" data-lc-qty="<?php esc_attr_e(round($int_stock_location_quantity)); ?>" data-lc-address="<?php esc_attr_e(base64_encode($rl)); ?>" data-lc-regular-price="<?php esc_attr_e(wc_price($stock_regular_price)); ?>" data-lc-sale-price="<?php ((!empty($stock_sale_price)) ?  esc_attr_e(wc_price($stock_sale_price)) : _e("undefined")); ?>" class="<?php echo 'wclimloc_'.$term->slug; ?>"  style="<?php if($allow_specific_location == 'No' ){echo 'display'.':'.'none';}else {echo 'display'.':'.'';} ?>"><?php 
                                                                    if($productOnBackorder){
                                                                        echo ucfirst($term->name) . ' - ' . __($backorderbuttontext, 'woocommerce');
                                                                    }else{
                                                                
                                                                        if( $isBackorderEnabled == "Yes" && $allow_backorder_each_location == "on"){
                                                                            echo ucfirst($term->name) . ' - ' . __('Backorder', 'woocommerce');
                                                                        }
                                                                        else if (empty($stock_location_quantity) || $stock_location_quantity < 0)  { 
                                                                            echo ucfirst($term->name) . ' - ' . __($soldout_btntxt, 'wcmlim'); 
                                                                        } else {
                                                                                    echo ucfirst($term->name) . ' - ' . __($instock_btntxt, 'wcmlim');
                                                                        } 														
                                                                    }
                                                                
                                                                    ?></option>
                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    }														
                                                    ?>
                                                </select>
                                                <?php if ($isLocationsGroup == null || $isLocationsGroup == false ) { ?>
                                                    <!-- Radio Listing Mode -->
                                                    <div class="wcmlradio_box rselect_location"></div>
                                                    <div class="wc_scrolldown">
                                                        <p>Scroll Location</p>
                                                        <i class="fas fa-chevron-circle-down"></i>												
                                                    </div>
                                                <?php } ?>                                                      

                                                </div><!-- Div loc_dd -->
                                                <?php
                                                if ($preffLocation) {
                                               
                                                    foreach ($terms as $k => $term) {
                                                        $stock_location_quantity = get_post_meta($post->ID, "wcmlim_stock_at_{$term->term_id}", true);
                                                        $stock_regular_price = get_post_meta($post->ID, "wcmlim_regular_price_at_{$term->term_id}", true);
                                                        $stock_sale_price = get_post_meta($post->ID, "wcmlim_sale_price_at_{$term->term_id}", true);
                                                        if($stock_regular_price == '' || $stock_regular_price == '0.00')
                                                        {
                                                            $stock_regular_price = get_post_meta($post->ID, "_regular_price", true);
                                                            $stock_sale_price = get_post_meta($post->ID, "_sale_price", true);
                                                        }
                                                        if (isset($stock_location_quantity)) {
                                                            if ($preffLocation == $k) {
                                                                if ($stock_display_format == "no_amount") {
                                                                    echo '<p id="globMsg">' . __($instock_btntxt, 'wcmlim') . '</p>';
                                                                } elseif (empty($stock_display_format)) {
                                                                    echo '<p id="globMsg"><b>' . $stock_location_quantity . ' </b> ' . __($instock_btntxt, 'wcmlim') . '</p>';
                                                                }
                                                            }
                                                        }
                                                    }
                                                } ?>
                                            </div> <!-- Div Wcmlim_prefloc_box -->
                                            <?php $geolocation = get_option('wcmlim_geo_location');
                                            if ($geolocation == "on") :
                                            ?>
                                                <div class="postcode-checker">
                                                    <p class="postcode-checker-title">
                                                        <strong>
                                                            <?php if ($txt_nearest) {
                                                                echo $txt_nearest;
                                                            } else {
                                                                _e('Check your nearest stock location :', 'wcmlim');
                                                            } ?>
                                                        </strong>

                                                    </p>
                                                    <div class="postcode-checker-div postcode-checker-div-show">
                                                    <div class="zipcode_div">
                                                        <?php
                                                        $globpin = isset($_COOKIE['wcmlim_nearby_location']) ? $_COOKIE['wcmlim_nearby_location'] : "";
                                                        $loc_dis_un = get_option('wcmlim_location_distance');
                                                        ?>
                                                        <input type="text" placeholder="<?php _e("Enter Pincode/Zipcode", "wcmlim",); ?>" class="class_post_code" name="post_code" value="<?php esc_html_e($globpin); ?>" id="elementId">
                                                        <!-- <input type="button" class="button" id="submit_postcode_product" value=" <?php //_e('Check', 'wcmlim'); 
                                                                                                                                        ?>"> -->
                                                        </div>
                                                        <div class="loc_btn_div">
                                                        <button class="button" type="button" id="submit_postcode_product">
                                                            <i class="fa fa-map-marker-alt"></i>
                                                            <?php if ($oncheck_btntxt) {
                                                                echo $oncheck_btntxt;
                                                            } else {
                                                                _e('Check', 'wcmlim');
                                                            } ?>
                                                        </button>
                                                        </div>
                                                        <input type='hidden' name="global_postal_check" id='global-postal-check' value='true'>
                                                        <input type='hidden' name="product_postal_location" id='product-postal-location' value='<?php esc_html_e($globpin); ?>'>
                                                        <input type='hidden' name="product_location_distance" id='product-location-distance' value='<?php esc_html_e($loc_dis_un); ?>'>
                                                    </div><!-- Div postcode-checker-div -->
                                                    <div class="search_rep" style="display: inline-flex;">
                                                        <div class="postcode-checker-response"></div>
                                                        <a class="postcode-checker-change postcode-checker-change-show" href="#" data-wpzc-form-open="" style="display: none;">
                                                            <i class="fa fa-edit" aria-hidden="true"></i>
                                                        </a>
                                                    </div>
                                                    <div class="Wcmlim_loc_label">
                                                        <div class="Wcmlim_locadd">
                                                            <div class="selected_location_detail"></div>
                                                            <div class="postcode-location-distance"></div>
                                                        </div>
                                                        <div class="Wcmlim_locstock"></div>
                                                    </div>
                                                    <?php if ($showNxtLoc  == "on") { ?>
                                                        <div class="Wcmlim_nextloc_label">
                                                            <div class="Wcmlim_nextlocadd">
                                                                <div class="next_closest_location_detail"></div>
                                                            </div>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="Wcmlim_messageerror"></div>
                                                </div><!-- Div postcode-checker -->
                                        <?php
                                            endif;
                                        }
                                        ?>
                                        <!-- End If else  WC_Product variation and simple -->
                                        <input type="hidden" id="lc_regular_price" name="location_regular_price" value="">
                                        <input type="hidden" id="lc_sale_price" name="location_sale_price" value="">
                                        <input type="hidden" id="lc_qty" name="location_qty" value="">
                                        <input type="hidden" id="wcstdis_format" name="stock_display_format" value="<?php esc_attr_e($stock_display_format); ?>">
                                        <input type="hidden" id="productOrgPrice" name="product_original_price" value="<?php esc_attr_e($product->get_price_html()); ?>">
                                        <input type="hidden" id="backorderAllowed" name="backorder_allowed" value="<?php if (isset($productOnBackorder)) {
                                                                                                                        esc_attr_e($productOnBackorder);
                                                                                                                    } ?>">
                                                                                                                    <?php
                                        if (
                                            $product instanceof WC_Product && $product->is_type('variable') && !$product->is_downloadable() && !$product->is_virtual()
                                            || $product instanceof WC_Product && $product->is_type('simple') && !$product->is_downloadable() && !$product->is_virtual()
                                        ) {  ?>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                            <?php
                            $customcss_enable = get_option('wcmlim_custom_css_enable');
                            if ($customcss_enable == "") {
                            ?>
                                <style>
                                    .wcmlim_product .loc_dd.Wcmlim_prefloc_sel {
                                        border-radius: <?php echo $refborder_radius;
                                                        ?> !important;
                                        padding-top: <?php echo $sbox_padtop;
                                                        ?>px !important;
                                        padding-right: <?php echo $sbox_padright;
                                                        ?>px !important;
                                        padding-bottom: <?php echo $sbox_padbottom;
                                                        ?>px !important;
                                        padding-left: <?php echo $sbox_padleft;
                                                        ?>px !important;
                                        background: <?php echo $sbox_bgcolor;
                                                    ?> !important;
                                    }

                                    .Wcmlim_have_stock,
                                    .Wcmlim_over_stock {
                                        padding-top: <?php echo $is_padtop;
                                                        ?>px !important;
                                        padding-right: <?php echo $is_padright;
                                                        ?>px !important;
                                        padding-bottom: <?php echo $is_padbottom;
                                                        ?>px !important;
                                        padding-left: <?php echo $is_padleft;
                                                        ?>px !important;
                                    }

                                    .wcmlim_product .loc_dd.Wcmlim_prefloc_sel .Wcmlim_sel_loc {
                                        padding-top: <?php echo $sel_padtop;
                                                        ?>px !important;
                                        padding-right: <?php echo $sel_padright;
                                                        ?>px !important;
                                        padding-bottom: <?php echo $sel_padbottom;
                                                        ?>px !important;
                                        padding-left: <?php echo $sel_padleft;
                                                        ?>px !important;
                                    }

                                    .wcmlim_product .postcode-checker-div input[type="text"] {
                                        padding-top: <?php echo $inp_padtop;
                                                        ?>px !important;
                                        padding-right: <?php echo $inp_padright;
                                                        ?>px !important;
                                        padding-bottom: <?php echo $inp_padbottom;
                                                        ?>px !important;
                                        padding-left: <?php echo $inp_padleft;
                                                        ?>px !important;
                                        border-radius: <?php echo $input_radius;
                                                        ?> !important;
                                    }

                                    .wcmlim_product #submit_postcode_product {
                                        padding-top: <?php echo $btn_padtop;
                                                        ?>px !important;
                                        padding-right: <?php echo $btn_padright;
                                                        ?>px !important;
                                        padding-bottom: <?php echo $btn_padbottom;
                                                        ?>px !important;
                                        padding-left: <?php echo $btn_padleft;
                                                        ?>px !important;
                                    }

                                    .wcmlim_product .loc_dd.Wcmlim_prefloc_sel .fa-map-marker-alt {
                                        color: <?php echo $icon_color;
                                                ?> !important;
                                        font-size: <?php echo $icon_size;
                                                    ?>px !important;
                                    }

                                    <?php if ($iconshow == "on") {
                                    ?>.wcmlim_product .loc_dd.Wcmlim_prefloc_sel .fa-map-marker-alt {
                                        display: none !important;
                                    }

                                    <?php }	?>.Wcmlim_container.wcmlim_product {
                                        background-color: <?php echo $stockbox_color;
                                                            ?>;
                                        border-radius: <?php echo $border_radius;
                                                        ?>;
                                        border-color: <?php echo $border_color;
                                                        ?>;
                                        border-width: <?php echo $border_width;
                                                        ?>;
                                        width: <?php echo $boxwidth;
                                                ?>%;
                                    }

                                    <?php if ($border_option == "none") {
                                    ?>.Wcmlim_container.wcmlim_product {
                                         max-width: 438px;
                                           width: 100%;
                                        border-style: none;
                                        padding: 0;
                                    }

                                    <?php
                                    }

                                    ?><?php if ($border_option == "solid") {
                                        ?>.Wcmlim_container.wcmlim_product {
                                        border-style: solid;
                                        max-width: 438px;
                                          width: 100%;
                                        
                                    }

                                    <?php
                                        }

                                    ?><?php if ($border_option == "dotted") {
                                        ?>.Wcmlim_container.wcmlim_product {
                                             max-width: 438px;
                                               width: 100%;
                                        border-style: dotted;
                                    }

                                    <?php
                                        }

                                    ?><?php if ($border_option == "double") {
                                        ?>.Wcmlim_container.wcmlim_product {
                                        border-style: double;
                                        max-width: 438px;
                                          width: 100%;
                                    }

                                    <?php
                                        }

                                    ?><?php if ($border_option == "dashed") {
                                        ?>.Wcmlim_container.wcmlim_product {
                                        border-style: dashed;
                                        max-width: 438px;
                                          width: 100%;
                                    }

                                    <?php
                                        }

                                    ?>.wcmlim_product #submit_postcode_product,
                                    .wcmlim_product #submit_postcode_global {
                                        border-radius: <?php echo $oncheck_radius;
                                                        ?> !important;
                                        color: <?php echo $oncheck_btntxtcolor;
                                                ?> !important;
                                        background-color: <?php echo $oncheck_btnbgcolor;
                                                            ?> !important;
                                    }

                                    .wcmlim_product .Wcmlim_box_title {
                                        color: <?php echo $txtcolor_stock_inf;
                                                ?> !important;
                                    }

                                    .wcmlim_product .loc_dd {
                                        color: <?php echo $txtcolor_preferred;
                                                ?> !important;
                                    }

                                    .wcmlim_product .postcode-checker-title {
                                        color: <?php echo $txtcolor_nearest;
                                                ?> !important;
                                    }

                                    .wcmlim_product .Wcmlim_line_seperator {
                                        background-color: <?php echo $color_separator;
                                                        ?>;
                                    }

                                    .wcmlim_product #submit_postcode_product,
                                    .wcmlim_product #submit_postcode_global {
                                        border-radius: <?php echo $oncheck_radius;
                                                        ?> !important;
                                        color: <?php echo $oncheck_btntxtcolor;
                                                ?> !important;
                                        background-color: <?php echo $oncheck_btnbgcolor;
                                                            ?> !important;
                                    }

                                    .Wcmlim_have_stock {
                                        border-radius: <?php echo $instock_radius;
                                                        ?>;
                                        color: <?php echo $instock_btntxtcolor;
                                                ?> !important;
                                        background-color: <?php echo $instock_btnbgcolor;
                                                            ?> !important;
                                    }

                                    .Wcmlim_over_stock {
                                        border-radius: <?php echo $soldout_radius;
                                                        ?>;
                                        color: <?php echo $soldout_btntxtcolor;
                                                ?> !important;
                                        background-color: <?php echo $soldout_btnbgcolor;
                                                            ?> !important;
                                    }

                                    <?php if ($display_stock_inf == "on") {
                                    ?>.Wcmlim_box_header {
                                        display: none;
                                    }

                                    <?php
                                    }

                                    ?><?php if ($display_preferred == "on") {
                                        ?>.Wcmlim_sloc_label {
                                        display: none;
                                    }

                                    <?php
                                        }

                                    ?><?php if ($display_nearest == "on") {
                                        ?>.postcode-checker-title {
                                        display: none;
                                    }

                                    <?php
                                        }

                                    ?><?php if ($display_separator == "on") {
                                        ?>.Wcmlim_line_seperator {
                                        display: none;
                                    }

                                    <?php
                                        }else{
                                            ?>.Wcmlim_line_seperator {
                                               display: block;
                                            }
                                            <?php
                                        }

                                    ?>
                                </style>
                                <?php
                            }