<?php
global $woocommerce;
foreach ( $cart->get_cart() as $cart_item ) {  
     $product_id = $cart_item['data']->get_id();
   $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
   $wcmlim_tax_location = array();
   foreach ($terms as $term) {
    $term_id = $term->term_id;
    if(isset($cart_item['select_location']['location_termId']))
    {
        $l_id = $cart_item['select_location']['location_termId'];
    }
    else
    {
        $req_lc_id = $_REQUEST['select_location'];
        $isExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
        if (!empty($isExcLoc)) {
            $In_terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isExcLoc));
        } else {
            $In_terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
        }
        foreach ($In_terms as $In_terms_key => $In_terms_term) {
            if($In_terms_key == $req_lc_id)
            {
                $l_id = $In_terms_term->term_id;
            }
        }				
    }
$wcmlim_tax_location = get_term_meta($l_id, 'wcmlim_tax_locations',true);
$cart_subtotal = WC()->cart->get_subtotal();
$all_tax_rates = [];
$tax_classes = WC_Tax::get_tax_classes(); // Retrieve all tax classes.
if ( !in_array( '', $tax_classes ) ) { // Make sure "Standard rate" (empty class name) is present.
    array_unshift( $tax_classes, '' );
}
foreach ( $tax_classes as $tax_class ) { // For each tax class, get all rates.
    $taxes = WC_Tax::get_rates_for_tax_class( $tax_class );
    $all_tax_rates = array_merge( $all_tax_rates, $taxes );
}
foreach ($all_tax_rates as $tax_key => $tax_value) {
    if (is_array($wcmlim_tax_location) && in_array($tax_value->tax_rate_id, $wcmlim_tax_location)) {
        $tax_name = $tax_value->tax_rate_name;
        $tax_rate = $tax_value->tax_rate;
        $shipping_tax_amount_to_apply = 0; // Initialize shipping tax amount to 0
        if ($tax_value->tax_rate_shipping == 1) {
            $shipping_tax_amount_to_apply = ($tax_rate * $woocommerce->cart->shipping_total) / 100; 
        }
        $tax_amount_to_apply = ($tax_rate * $cart_subtotal) / 100;
        $total_tax_amount = $tax_amount_to_apply + $shipping_tax_amount_to_apply; 
        WC()->cart->add_fee($tax_name, $total_tax_amount);
    }
}

} 
}