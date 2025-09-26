<?php
global $woocommerce;
$reserr = '0';
$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));

$product_location_termid = isset($_POST['product_location_termid']) ? $_POST['product_location_termid'] : "";
$manage_stock = get_post_meta($product_id, '_manage_stock', true);
$quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);

$cart_qty = 0;
foreach ($woocommerce->cart->get_cart() as $item => $items) {
    $product_ids = $items['variation_id'] ? $items['variation_id'] : $items['product_id'];
    if ($product_ids == $product_id) {
        $cart_qty += $items['quantity'];


    }
}



$cart_qty += $quantity;
if ($manage_stock == "true" || $manage_stock == "yes" || $manage_stock == "1") {
    update_post_meta($product_id, '_manage_stock', 'yes');
    $manage_stock = "yes";
}


//15 de enero se modifica para que no permita agregar al carrito si no hay stock en la ubicación
// comentamos esto para que no bloquee la seleccion
//restricting user from adding product to cart if the location is not selected (when manage stock is on)
if ($manage_stock == "yes" && empty($product_location_termid)) {
    //   echo '3';
    // wp_die();
}

if ($manage_stock != "yes" && !empty($product_location_termid)) {
    //   echo '2';
    //  wp_die();
}


//modifique esta funcion ya que busca el stock en el $product_location_termid y debe ser en la ubicación
// wcmlim_allow_specific_location_241_location_id_22
$_location_termid = $_COOKIE['wcmlim_selected_location_termid'];

$__stock_at_location = get_post_meta($product_id, "wcmlim_stock_at_{$_location_termid}", true);

if ($manage_stock == "yes") {
    if ($__stock_at_location <= 0) {
        echo '2';
        wp_die();
    }
}
// is_backorder_allowed at location fetch

$allow_backorder = get_post_meta($product_id, "wcmlim_allow_backorder_at_{$product_location_termid}", true);

if ($allow_backorder == 'No' || $allow_backorder == 'no') {
    // Backorders are not allowed, check if cart quantity exceeds stock at location
    if ($cart_qty > $__stock_at_location) {
        // Quantity in cart exceeds stock at $quantity` return error
        echo '4';
        wp_die();
    }
}


$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
$product_status = get_post_status($product_id);
$product_price = isset($_POST['product_price']) ? $_POST['product_price'] : "";
$_isrspon = get_option("wcmlim_enable_price");
// Location data
$product_location = isset($_POST['product_location']) ? $_POST['product_location'] : "";
$product_location_key = isset($_POST['product_location_key']) ? $_POST['product_location_key'] : "";
$product_location_qty = isset($_POST['product_location_qty']) ? $_POST['product_location_qty'] : "";

$product_location_regular_price = isset($_POST['product_location_regular_price']) ? $_POST['product_location_regular_price'] : "";
$product_location_sale_price = isset($_POST['product_location_sale_price']) ? $_POST['product_location_sale_price'] : "undefined";

// wcmlim_allow_specific_location_241_location_id_22
$_location_termid = $_COOKIE['wcmlim_selected_location_termid'];

$allow_specific_location = get_post_meta($product_id, 'wcmlim_allow_specific_location_at_' . $_location_termid, true);

if ($allow_specific_location != 'Yes' && get_option('wcmlim_enable_specific_location') == "on") {
    $reserr = "2";
    echo $reserr;
    wp_die();
}

$isClearCart = get_option('wcmlim_clear_cart');
//esta funcion controla que la carta debe vaciarse si se agrega un producto de otra ubicación
/*
if ($isClearCart == 'on') {


    $refine_manage_stock = "yes";
    foreach( $woocommerce->cart->get_cart() as $item => $items)
    {
        $product_ids = $items['variation_id'] ? $items['variation_id'] : $items['product_id'];
        //check product has manage stock
        $manage_stock = get_post_meta($product_ids, '_manage_stock', true);
        if($manage_stock != 'yes')
        {

            $refine_manage_stock = "no";
        }
        else
        {

            // check the location of the product in cart
            $cart_location_id = $items['select_location']['location_termId'];

            echo "cart_location_id: ".$cart_location_id;
            echo "product_location_termid: ".$product_location_termid;
            
            if($cart_location_id != $product_location_termid)
            {
                $refine_manage_stock = "no";
                $reserr = '1';
                echo $reserr;
                wp_die();
            }
            else
            {
                $refine_manage_stock = "yes";
            }

        }
       
    }
}*/


if ($passed_validation && 'publish' === $product_status) {

    $_location_data = array();
    $_location_data['select_location']['location_name'] = $product_location;
    $_location_data['select_location']['location_key'] = (int) $product_location_key;
    $_location_data['select_location']['location_qty'] = (int) $product_location_qty;
    $_location_data['select_location']['location_termId'] = (int) $product_location_termid;

    if ($_isrspon == "on") {
        if (!empty($product_location_regular_price) && empty($product_location_sale_price)) {
            $_location_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode(wc_price($product_location_regular_price)));
        }

        if (!empty($product_location_sale_price)) {
            $_location_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode(wc_price($product_location_sale_price)));
        }

        if (empty($product_location_regular_price) && empty($product_location_sale_price)) {
            $_location_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode(wc_price($product_price)));
        }
    }

    WC()->cart->add_to_cart($product_id, $quantity, '0', array(), $_location_data);

    WC_AJAX::get_refreshed_fragments();
}

wp_die();
