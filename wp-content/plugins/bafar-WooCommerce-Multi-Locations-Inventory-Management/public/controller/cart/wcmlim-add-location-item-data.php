<?php
$manage_stock = get_post_meta($variation_id, '_manage_stock', true);
if($variation_id == "0"){
    $manage_stock = get_post_meta($product_id, '_manage_stock', true);
}else{
    $manage_stock = get_post_meta($variation_id, '_manage_stock', true);
}
if($manage_stock == "no"){
    return $cart_item_data;
}

$_isrspon = get_option("wcmlim_enable_price");

$product = wc_get_product($product_id);

if ($product->is_type('composite')) {

    if (!isset($cart_item_data['select_location'])) {

        $selected_store = $_COOKIE['wcmlim_selected_location'];

        $stores = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));

        foreach ($stores as $key => $store) {
            if ($selected_store == $key) {
                $cart_item_data['select_location'] = array(
                    'location_name'     => $store->name,
                    'location_key'      => $key,
                    'location_termId'   => $store->term_id
                );
            }
        }
    }
} else {
    if ($product->is_type('simple')) {
        $productPrice = $product->get_price();
        $sProductPrice = wc_price($productPrice);
    } elseif ($product->is_type('variable')) {
        $varProduct = wc_get_product($variation_id);
        $productPrice = $varProduct->get_price();
        $sProductPrice = wc_price($productPrice);
    }

    if (isset($_POST['select_location'])) {
        $lcKey = isset($_POST['select_location']) ? $_POST['select_location'] : "";
        $lcQty = isset($_POST['location_qty']) ? $_POST['location_qty'] : "";
        $ExL = get_option("wcmlim_exclude_locations_from_frontend");
        if (!empty($ExL)) {
            $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExL));
        } else {
            $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
        }
        foreach ($terms as $k => $term) {
            if ($k == $lcKey) {
                $locationName = $term->name;
                $locationTermId = $term->term_id;
                $locaKey = $k;
                $this->set_location_cookie($locaKey);
            }
        }

        $cart_item_data['select_location']['location_name'] = $locationName;
        $cart_item_data['select_location']['location_key'] = isset($locaKey) ? (int)$locaKey : "";
        $cart_item_data['select_location']['location_qty'] = isset($lcQty) ? (int)$lcQty : "";
        $cart_item_data['select_location']['location_termId'] = (int)$locationTermId;
        
        if($_isrspon == "on"){
            if(!empty($_POST['location_regular_price']) && $_POST['location_sale_price'] == 'undefined'){
                $cart_item_data['select_location']['location_cart_price'] = strip_tags($_POST['location_regular_price']);
            }
            if(isset($_POST['location_sale_price']) && $_POST['location_sale_price'] !== 'undefined' ){
                $cart_item_data['select_location']['location_cart_price'] = strip_tags($_POST['location_sale_price']);
            }
            
            if(empty($_POST['location_regular_price']) && empty($_POST['location_sale_price'])){
                $cart_item_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode($sProductPrice));
            }
        }
        
    }
}

return $cart_item_data;