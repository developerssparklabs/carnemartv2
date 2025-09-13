<?php

$stock = $product->get_stock_quantity();
$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
$manage_stock = get_post_meta($product->get_id(), '_manage_stock', true);
$setLocation = isset($_COOKIE['wcmlim_selected_location_termid']) ? $_COOKIE['wcmlim_selected_location_termid'] : null;

// product id fetch
$product_id = $product->get_id();
$loc_stock_val = "";

if ($setLocation != null) {
    $soldoutlabel = get_option("wcmlim_soldout_button_text", true);
    $instocklabel = get_option("wcmlim_instock_button_text", true);
    $loc_stock = get_post_meta($product_id, "wcmlim_stock_at_{$setLocation}", true);
    
    if (!$product->is_in_stock()) {
        if ($soldoutlabel) {
            $stock_text = $soldoutlabel;
        } else {
            $stock_text = 'Sold out';
        }
    } elseif ($product->is_in_stock()) {
        $arr_stock = array();
        foreach ($terms as $term) {
            $allow_specific_location = get_post_meta($product->get_id(), 'wcmlim_allow_specific_location_at_' . $term->term_id, true);
            if ($allow_specific_location == 'Yes') {
                // get the stock of selected location
                if ($term->term_id == $setLocation) {
                    $loc_stock_val = intval(get_post_meta($product->get_id(), "wcmlim_stock_at_{$term->term_id}", true));
                }
            }
        }
        $total_stock_qty = array_sum($arr_stock);
        if ($loc_stock_val <= 0) {
            $stock_text = 'Out of stock';
        } else {
            $stock_text = $loc_stock_val . ' ' . $instocklabel;
        }
    }
} else {
    $arr_stock = array();
    foreach ($terms as $term) {
        $allow_specific_location = get_post_meta($product->get_id(), 'wcmlim_allow_specific_location_at_' . $term->term_id, true);
        if ($allow_specific_location == 'Yes') {
            $loc_stock_val = intval(get_post_meta($product->get_id(), "wcmlim_stock_at_{$term->term_id}", true));
            array_push($arr_stock, $loc_stock_val);
        }
    }
    $total_stock_qty = array_sum($arr_stock);
    if ($total_stock_qty > 0 && $manage_stock == 'no') {
        $stock_text = $total_stock_qty . ' out of stock';
    }
}

return $stock_text;
