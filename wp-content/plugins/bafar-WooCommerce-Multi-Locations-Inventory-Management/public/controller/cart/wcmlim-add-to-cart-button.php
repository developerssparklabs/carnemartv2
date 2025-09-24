<?php
// $slCookie = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";
// $cookie_termId = isset($_COOKIE['wcmlim_selected_location_termid']) ? $_COOKIE['wcmlim_selected_location_termid'] : "";
// $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
// foreach ($terms as $t => $v) {
//     if ($cookie_termId == $v->term_id) {
//         $ln = $v->name;
//         $_location_key = $t;
//         $_location_qty = get_post_meta($product->get_id(), "wcmlim_stock_at_{$v->term_id}", true);
//         $_location_regular_price = get_post_meta($product->get_id(), "wcmlim_regular_price_at_{$v->term_id}", true);
//         $_location_sale_price = get_post_meta($product->get_id(), "wcmlim_sale_price_at_{$v->term_id}", true);
//         $_location_termId = $v->term_id;
//     }
// }

// $ln = !empty($ln) ? $ln : "";
// $_location_key = isset($_location_key) ? (int) $_location_key : "";
// $_location_qty = isset($_location_qty) ? (int) $_location_qty : "";
// $_location_termId = isset($_location_termId) ? (int) $_location_termId : "";
// $_location_regular_price = !empty($_location_regular_price) ? $_location_regular_price : "";
// $_location_sale_price = !empty($_location_sale_price) ? $_location_sale_price : "";
// $_isRedirect = get_option("woocommerce_cart_redirect_after_add");
// $_cart_url = wc_get_cart_url();

// if ($product->is_type('simple') && !$product->is_downloadable() && !$product->is_virtual()) {
//     $_product_id = $product->get_id();
//     // FIX - Spark-jesus: Siempre se le pasaba en cantidad 1, pero algunos productos en min tienen un valor diferente a 1 esto causaba que no se pudiera añadir al carrito
//     $qty_min = get_post_meta($_product_id, 'min_quantity', true) ?: 1;
//     $_product_sku = $product->get_sku();
//     $_product_name = $product->get_name();
//     $_product_price = $product->get_price();
//     $_product_backorder = $product->backorders_allowed();
//     $_manage_stock_enabled = get_post_meta($_product_id, '_manage_stock', true);
//     if ($_manage_stock_enabled == 'no') {
//         return $button;
//     }

//     $button_text = __("Add to cart", "woocommerce");
//     $button = '<a data-cart-url="' . $_cart_url . '" data-isredirect="' . $_isRedirect . '" data-quantity="' . $qty_min . '" class="button product_type_simple add_to_cart_button wcmlim_ajax_add_to_cart" data-product_id="' . $_product_id . '" data-product_sku="' . $_product_sku . '" aria-label="Add “' . $_product_name . '” to your cart" data-selected_location="' . $ln . '" data-location_key="' . $_location_key . '" data-location_qty="' . $_location_qty . '" data-location_termid="' . $_location_termId . '" data-product_price="' . $_product_price . '" data-location_sale_price="' . $_location_sale_price . '" data-location_regular_price="' . $_location_regular_price . '" data-product_backorder="' . $_product_backorder . '" rel="nofollow">' . $button_text . '</a>';
// }
// return $button;