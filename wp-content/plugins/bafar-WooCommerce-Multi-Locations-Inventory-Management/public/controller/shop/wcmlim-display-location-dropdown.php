<?php
$response = array();
$hideDropdown = get_option('wcmlim_hide_show_location_dropdown');
$product_id = $_POST['product_id'];
$product = wc_get_product($product_id);
$manage_stock = get_post_meta($product_id, '_manage_stock', true);
//get default location for product
 $default_location = get_post_meta($product_id, 'wcmlim_default_location', true);
 //remove loc from loc1
 if($default_location)
 {
     $default_location = explode("_", $default_location);
     $default_location = $default_location[1];
 }
 $def_location_term_id = 0;
 $def_location_term = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
 foreach($def_location_term as $def_key=>$def_term){
         if($def_key == $default_location){
             $def_location_term_id = $def_term->term_id;
         }
     }
  
if($hideDropdown == 'on' && $manage_stock == 'yes' && $product->is_type('variable') && $default_location != ''){
    echo "dropdown_hide";
    $location_key = $_COOKIE['wcmlim_selected_location'];
    $location_term_id = $_COOKIE['wcmlim_selected_location_termid'];	
    


    $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    if(empty($location_term_id)){
        foreach($locations as $key=>$term){
            if($key == $location_key){
                $location_term_id = $term->term_id;
            }
        }
    }

 
   
    $is_rspon = 'on';
    if($is_rspon == 'on'){
        $regular_price = get_post_meta($product_id, 'wcmlim_regular_price_at_'.$location_term_id, true);
        if(empty($regular_price) || $regular_price == 0){
            $regular_price = get_post_meta($product_id, '_regular_price', true);
        }
        
        $sale_price = get_post_meta($product_id, 'wcmlim_sale_price_at_'.$location_term_id, true);
        if(!empty($sale_price) && $sale_price != 0){
            $response[$location_key]['sale_price'] = html_entity_decode(wc_price($sale_price));
        }
        $response[$location_key]['regular_price'] = html_entity_decode(wc_price($regular_price));
    }else{
        $regular_price = get_post_meta($product_id, '_regular_price', true);
        $sale_price = get_post_meta($product_id, '_sale_price', true);
        $response[$location_key]['regular_price'] = html_entity_decode(wc_price($regular_price));
        if(!empty($sale_price) && $sale_price != 0){
            $response[$location_key]['sale_price'] = html_entity_decode(wc_price($sale_price));
        }
    }
    if($def_location_term_id == $location_term_id){
        $response[$location_key]['default_location'] = $def_location_term_id;
    }
    echo json_encode($response);
    die();	
}

$isExcludeLocation = get_option("wcmlim_exclude_locations_from_frontend");
if (!empty($isExcludeLocation)) {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isExcludeLocation));
} else {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
}
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : "";

// $manage_stock = get_post_meta($product_id, '_manage_stock', true);
$stock_status = get_post_meta($product_id, '_stock_status', true);
if($manage_stock == 'no'){
    $response['manage_stock'] = 'no';
//     $response['stock_status'] = $stock_status;
    echo json_encode($response);
    die();
}
if($product_id == "")
{
    echo "Product ID is missing";
    die();

}
else
{
    $product = wc_get_product($product_id);
}

if ($product->is_type('variation')) {
    $variation = wc_get_product($product_id);
   
    $parent_product =  $variation->get_parent_id();
    $product = wc_get_product($parent_product);
    $stock_display_format = get_option('woocommerce_stock_format');
    $instock_btntxt = get_option("wcmlim_instock_button_text");									
    $variation_stock = $variation->get_stock_quantity();
    $allow_backorder_each_location = 'on';
   
    if( $variation_stock < 0 && $allow_backorder_each_location != 'on'){
        
        $response['show_wcmlim_product'] = 'hide';
        echo json_encode($response);
        die();
    }
    $variations = $product->get_available_variations();
    if (!empty($variations)) {
        $variation_stock_status =  get_post_meta($product_id , "_stock_status", true);
        $manage_stock = get_post_meta($product_id, '_manage_stock', true);
        
            $stock_status = get_post_meta($product_id, '_stock_status', true);
        if(empty($variations))
        {
            if( $manage_stock == 'no' ){
                $response['manage_stock'] = 'no';
                echo json_encode($response);
                die();
            }
        }
        foreach ($variations as $key => $value) {
            
            foreach ($terms as $k => $term) {
                if ($product_id == $value['variation_id']) {
                        $stock_location_quantity =  get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true);
                    
                    $stock_regular_price = get_post_meta($product_id, "wcmlim_regular_price_at_{$term->term_id}", true);
                    $stock_sale_price = get_post_meta($product_id, "wcmlim_sale_price_at_{$term->term_id}", true);
                    $backorder = !empty($value['backorders_allowed']) ? $value['backorders_allowed'] : 0;
                    $manage_stock = get_post_meta($product_id, '_manage_stock', true);
                    $stock_status = get_post_meta($product_id, '_stock_status', true);
                    
                        $variable_is_in_stock =  $value['is_in_stock'];
                    $isBackorderEnabled = get_post_meta($product_id, "wcmlim_allow_backorder_at_$term->term_id", true );
                    $specific_location = 'on';
                    if ($specific_location == 'on') {
                    $allow_specific_location = get_post_meta($product_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, true);
                        $allow_specific_location = (empty($allow_specific_location)) ? 'Yes' : $allow_specific_location;
                    }else{
                        $allow_specific_location = 'Yes';
                    }

                    if($allow_specific_location == 'Yes')
                    {	
                        //get current user
                        $current_user = wp_get_current_user();

                        //get user specific location
                        $user_specific_location = get_user_meta($current_user->ID, 'wcmlim_user_specific_location', true);
                        if(get_option('wcmlim_enable_userspecific_location') != 'on'){
                            $user_specific_location = "-1";
                        }
                        if($user_specific_location == $k || $user_specific_location == '-1'){
                        
                        $d_location   = get_option('wcmlim_enable_default_location');
                        $allow_backorder_each_location = 'on';
                        $selDefLoc = get_post_meta($product_id, "wcmlim_default_location", true);
                        $term_meta = get_option("taxonomy_$term->term_id");
                        $rl = $this->wcmlim_get_loactionaddress($term->term_id);
                        $term_location = base64_encode($rl);
                        $hide_out_of_stock_location   = get_option('wcmlim_hide_out_of_stock_location');
                            
                            if ($manage_stock == 1 || $manage_stock == 'yes' ) {
                                if($isBackorderEnabled == "Yes" && $allow_backorder_each_location == "on"){
                                   
                                    $response[$k]['text'] = $term->name . ' - ' . __('Backorder', 'woocommerce');
                                            $response[$k]['location_qty'] = '0';
                                            $response[$k]['term_id'] = $term->term_id;
                                            $response[$k]['location_address'] = $term_location;
                                            $response[$k]['variation_backorder'] = "yes";
                                            $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                } else{
                                    if($backorder == 1){
                                        $response[$k]['text'] = $term->name. ' - ' . __('Available on backorder', 'woocommerce');
                                        if ($hide_out_of_stock_location != "on") {
                                            $response[$k]['term_id'] = $term->term_id;
                                            $response[$k]['text'] = $term->name . ' - ' . __('Available on backorder', 'woocommerce');
                                            $response[$k]['location_qty'] = round(intval($stock_location_quantity));
                                            $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                            $response[$k]['variation_backorder'] = "yes";
                                        }
                                    }else{
                                        if(!empty($stock_location_quantity) && $stock_location_quantity > 0){
                                             
                                            $response[$k]['text'] = $term->name. ' - ' . __('In stock', 'woocommerce');
                                            $response[$k]['location_qty'] = round(intval($stock_location_quantity));
                                            $response[$k]['location_address'] = $term_location;
                                            $response[$k]['term_id'] = $term->term_id;
                                            $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                        }else{
                                           
                                            if ($hide_out_of_stock_location != "on") {
                                                $response[$k]['text'] = $term->name . ' - ' . __('Out of Stock', 'woocommerce');
                                                $response[$k]['location_qty'] = '0';
                                                $response[$k]['term_id'] = $term->term_id;
                                                $response[$k]['location_address'] = $term_location;
                                                $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                                
                                            }
                                            else
                                            {  
                                                $response[$k]['text'] = $term->name . ' - ' . __('Out of Stock', 'woocommerce');
                                                $response[$k]['location_qty'] = '0';
                                                $response[$k]['term_id'] = $term->term_id;
                                                $response[$k]['location_address'] = $term_location;
                                                $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                                $response[$k]['variation_backorder'] = "no";
                                                $response[$k]['stock_status'] = "outofstock";

                                            }
                                            
                                        }
                                    }
                                }
                                
                               

                                if (!empty($stock_regular_price)) {
                                    $response[$k]['regular_price'] = html_entity_decode(wc_price($stock_regular_price));
                                }
                                if (!empty($stock_sale_price)) {
                                    $response[$k]['sale_price'] = html_entity_decode(wc_price($stock_sale_price));
                                }
                            } else {
                                if ($hide_out_of_stock_location != "on") {
                                    if ($stock_status =='instock') {
                                        $response[$k]['text'] = $term->name. ' - ' . __($stock_status, 'woocommerce'); 
                                        
                                    $response[$k]['location_address'] = $term_location;
                                    $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                    $response[$k]['location_stock_status'] = 'instock';
                                    }
                                    else{
                                        $response[$k]['text'] = $term->name . ' - ' . __('Out of Stock', 'woocommerce');
                                        $response[$k]['location_class'] = "wclimloc_". $term->slug;
                                    }
                                    if (!empty($stock_regular_price)) {
                                        $response[$k]['regular_price'] = html_entity_decode(wc_price($stock_regular_price));
                                    }
                                    if (!empty($stock_sale_price)) {
                                        $response[$k]['sale_price'] = html_entity_decode(wc_price($stock_sale_price));
                                    }
                                    
                                }

                            }
                            if (!empty($d_location) && !empty($selDefLoc)) {
                                $_actualLcK = explode("_", $selDefLoc);
                                        if ($_actualLcK[1] == $k) {
                                        $response[$k]['default_location'] = $k;
                                }else{
                                    $response[$k]['default_location'] = '';
                                }
                            }
                            if($def_location_term_id == $term->term_id)
                            {
                                   $response[$k]['default_location'] = $def_location_term_id;
                            }
                            $response[$k]['start_time'] = get_term_meta($term->term_id, 'wcmlim_start_time', true);
                            $response[$k]['end_time'] = get_term_meta($term->term_id, 'wcmlim_end_time', true);
                            $response[$k]['allow_specific_location'] = $allow_specific_location;
                        }
                    }
                }
            }
        }
        $response['backorder'] = $backorder;
    }
}
echo json_encode($response);
die();