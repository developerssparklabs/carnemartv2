<?php
global $woocommerce;
$pass_location = isset($_REQUEST['select_location']) ? $_REQUEST['select_location'] : "";
$product_id = $_REQUEST['product_id'];
$manage_stock = get_post_meta($product_id, '_manage_stock', true);

if ($manage_stock != 'no') {

    if ($pass_location == -1) {
        wc_add_notice(__($select_loc_val, 'wcmlim'), 'error');
        $passed = false;
        return $passed;
    }
} else {
    $slCookie = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";

    if ($slCookie == "-1") {
        $passed = false;
        return $passed;
    } else {
        $passed = true;
        return $passed;
    }
}


$product = wc_get_product($product_id);
if (is_a($product, 'WC_Product')) {
    $isBackorder = $product->backorders_allowed();
    if ($isBackorder) {
        return true;
    }
}

if (WC()->cart->cart_contents_count == 0) {
    return true;
}
if (WC()->cart->cart_contents_count > 0) {
    foreach (WC()->cart->get_cart() as $key => $val) {
        if (isset($val['select_location']['location_qty']) && isset($val['select_location']['location_key'])) {
            $_product = $val['data'];
            $pro = wc_get_product($val['product_id']);
            $stock_invalid = get_option('wcmlim_prod_instock_valid');
            if ($pro->is_type('simple')) {

                // a simple product

                $_locqty = $val['select_location']['location_qty'];
                $cart_items_count = $val['quantity'];
                $total_count = ((int) $cart_items_count + (int) $quantity);


                if ($_POST['select_location'] == $val['select_location']['location_key'] && $product_id == $_product->get_id()) {
                    if ($cart_items_count >= $_locqty || $total_count > $_locqty) {
                        // Set to false
                        $passed = false;
                        // Display a message
                        wc_add_notice(__($stock_invalid, "wcmlim"), "error");
                    }
                }
            } elseif ($pro->is_type('variable')) {

                // a variable product

                $_locqty = $val['select_location']['location_qty'];
                $cart_items_count = $val['quantity'];
                $total_count = ((int) $cart_items_count + (int) $quantity);

                if ($_POST['select_location'] == $val['select_location']['location_key'] && $_POST['variation_id'] == $_product->get_id()) {
                    if ($cart_items_count >= $_locqty || $total_count > $_locqty) {
                        // Set to false
                        $passed = false;
                        // Display a message
                        wc_add_notice(__($stock_invalid, "wcmlim"), "error");
                    }
                }
            }
        }
    }
}
return $passed;
