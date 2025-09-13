<?php
// $name .= "";

if (isset($cart_item['select_location']['location_name'])) {
    $locescstring = __("Location : ", "wcmlim");
    $locfeeescstring = __("Fee : ", "wcmlim");

    if (isset($cart_item['select_location']['location_name']) && !empty($cart_item['select_location']['location_name'])) {
        $location_name = esc_html($cart_item['select_location']['location_name'] ?? '');

        $name .= sprintf(
            '<p class="datos-tienda">Tienda : %s</p>',
            $location_name
        );
    }

    // add custom fee assigned
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $wc_currency_symbol = get_woocommerce_currency_symbol();
   
    $locationFee = get_option( 'wcmlim_location_fee' );
    if($locationFee == "on"){
       
        foreach ($terms as $term) {
            if ($term->name == $cart_item['select_location']['location_name']) {
                $term_id = $term->term_id;
                $location_fee = get_term_meta($term_id, 'wcmlim_location_fee', true);
                if((!empty($location_fee)) && ($location_fee != 0))
                {
                    $name .= sprintf('<br>%s', __($locfeeescstring . $wc_currency_symbol.$location_fee));
                }
            }
        }         
        //$name .= sprintf('</p>');
    }

} else {
    $termExclude = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($termExclude)) {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $termExclude));
    } else {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }
    foreach ($terms as $term) {
        $this->max_value_inpl = get_post_meta($cart_item['product_id'], "wcmlim_stock_at_{$term->term_id}", true);
    }
}

$this->max_in_value = isset($cart_item['select_location']) ? $cart_item['select_location'] : "";
$this->max_value_inpl = isset($this->max_value_inpl) ? $this->max_value_inpl : ""; // Create dynamic property with default value
return $name;