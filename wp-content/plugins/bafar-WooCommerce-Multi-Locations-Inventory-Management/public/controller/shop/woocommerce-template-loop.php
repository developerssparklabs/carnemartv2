<?php
global $product;
$setLocation = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";
if ($product->get_type() == 'simple') {
    $exclExists = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($exclExists)) {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $exclExists));
    } else {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }

    foreach ($terms as $k => $term) {
        $locationQty = get_post_meta($product->get_id(), "wcmlim_stock_at_{$term->term_id}", true);
        if ($setLocation == $k && empty($locationQty)) {
            
           echo '<span class="locsoldout">' . __('Out of stock', 'woocommerce') . '</span>';
        }
    }
}