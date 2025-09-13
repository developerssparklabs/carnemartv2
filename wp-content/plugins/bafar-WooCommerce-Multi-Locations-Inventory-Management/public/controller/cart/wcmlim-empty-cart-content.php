<?php
global $woocommerce;
$updated_term_id = $_POST['loc_id'];


$cookieindays = get_option('wcmlim_set_location_cookie_time');
$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
$isClearCart = get_option('wcmlim_clear_cart');

if ($isClearCart == 'on') {

    foreach( $woocommerce->cart->get_cart() as $item => $items)
    {
        $product_ids = $items['variation_id'] ? $items['variation_id'] : $items['product_id'];
        //check product has manage stock
        $manage_stock = get_post_meta($product_ids, '_manage_stock', true);
       
        if($manage_stock == 'yes')
        { 
             // remove product from cart
            $woocommerce->cart->remove_cart_item($item); 
        }
    }
}
foreach($locations as $key=>$term){
    if($key == $updated_term_id){
        $match_term_id = $term->term_id;
        setcookie("wcmlim_selected_location_termid", $match_term_id, time() + (86400 * $cookieindays), "/");
        setcookie("wcmlim_selected_location", $updated_term_id, time() + (86400 * $cookieindays), "/");
        if(!empty($product_id))
        {
            $loc_stock_pid = get_post_meta($product_id, "wcmlim_stock_at_{$match_term_id}", true);
            $postmeta_backorders_product = get_post_meta($product_id, '_backorders', true);
            if(($loc_stock_pid > 0) || ($postmeta_backorders_product=='yes'))
            {
                $_location_data = array();
                $_location_data['select_location']['location_name'] = $term->name;
                $_location_data['select_location']['location_key'] = (int)$key;
                $_location_data['select_location']['location_qty'] = 1;
                $_location_data['select_location']['location_termId'] = $match_term_id;
                $location_id = $match_term_id;										
                $stock_regular_price = get_post_meta($product_id, "regular_price", true);
                $pml_product = wc_get_product( $product_id );
                $price = $pml_product->get_price();
            
                $_isrspon = get_option("wcmlim_enable_price");
                if ($wcmlim_enable_price == 'on') {
                    $loc_stock_pid = get_post_meta($product_id, "wcmlim_stock_at_{$location_id}", true);
                    $stock_wcmlim_regular_price = get_post_meta($product_id, "wcmlim_regular_price_at_{$location_id}", true);
                    $stock_wcmlim_stock_sale_price = get_post_meta($product_id, "wcmlim_sale_price_at_{$location_id}", true);
                    if((!empty($stock_wcmlim_stock_sale_price)))
                    {
                        $_location_data['select_location']['location_cart_price'] =  strip_tags(html_entity_decode(wc_price($stock_wcmlim_stock_sale_price)));
        
                    }
                    elseif(($stock_wcmlim_regular_price != 0 && $stock_wcmlim_stock_sale_price == 0) || ($stock_wcmlim_regular_price != '' && $stock_wcmlim_stock_sale_price == ''))
                    {
                        $_location_data['select_location']['location_cart_price'] =  strip_tags(html_entity_decode(wc_price($stock_wcmlim_regular_price)));
        
                    }
                    elseif(($stock_wcmlim_regular_price == 0 && $stock_wcmlim_stock_sale_price == 0) || ($stock_wcmlim_regular_price == '' && $stock_wcmlim_stock_sale_price == ''))
                    {
                        $_location_data['select_location']['location_cart_price'] =  strip_tags(html_entity_decode(wc_price($price)));
                    }
                    else
                    {
                        $_location_data['select_location']['location_cart_price'] =  strip_tags(html_entity_decode(wc_price($price)));
                    }
                }
                else
                {
                    $_location_data['select_location']['location_cart_price'] =  strip_tags(html_entity_decode(wc_price($price)));
                }
        
                WC()->cart->add_to_cart($product_id, 1, '0', array(), $_location_data);
            }
        
        } 
    } 
}
