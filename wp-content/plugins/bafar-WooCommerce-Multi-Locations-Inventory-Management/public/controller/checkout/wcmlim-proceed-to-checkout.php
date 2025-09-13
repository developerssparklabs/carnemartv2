<?php 

//get the cart items quantity of products
$cart_items = WC()->cart->get_cart_contents_count();

// get stock quantity of products from post meta of items added in cart
$stock_quantity = 0;
foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
    $product_id = isset($cart_item['variation_id']) && ($cart_item['variation_id'] != "0" && $cart_item['variation_id'] != "") ? $cart_item['variation_id'] : $cart_item['product_id'];

    $product = wc_get_product($product_id);
    $stz = 'wcmlim_allow_backorder_at_' . $cart_item['select_location']['location_termId'];
    $is_backorder_enabled = get_post_meta($product_id, $stz, true);
  
   
    $product_name = $product->get_name();
    $stock_quantity = $product->get_stock_quantity();
    $location_id = '';
    $location_name = '';
    if($is_backorder_enabled != 'yes' || $is_backorder_enabled != 'Yes')
    {
        if($cart_item['select_location']['location_termId'])
        { 
            
            $location_id = $cart_item['select_location']['location_termId']; 
            $location_name = $cart_item['select_location']['location_name'];
            $get_loc_stock = get_post_meta($product_id, 'wcmlim_stock_at_'.$location_id, true);
        
            
            if($get_loc_stock < $cart_item['quantity'])
            {
                wc_add_notice( "$product_name does not have stock quantity at  the selected locations <b> $location_name </b>.", 'error' );
            }
        }
    }
}
return true;
//get the locations of the products from the cookie
?>
