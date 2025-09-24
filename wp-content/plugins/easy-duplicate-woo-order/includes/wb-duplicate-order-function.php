<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wizbee_duplicate_order_logic($order) {
    if (!is_a($order, 'WC_Order')) {
        return;
    }

    $order_id = $order->get_id();
    $new_order = wc_create_order();

    // Copy customer
    $user_id = $order->get_customer_id();
    $new_order->set_customer_id($user_id);

    // Copy billing and shipping addresses
    $new_order->set_address($order->get_address('billing'), 'billing');
    $new_order->set_address($order->get_address('shipping'), 'shipping');

    // settings
    $copy_old_price = get_option('wizbee_duplicate_order_copy_old_price', 'yes');
    $copy_fees = get_option('wizbee_duplicate_order_copy_fees', 'yes');
    $copy_shipping = get_option('wizbee_duplicate_order_copy_shipping', 'yes');
    $apply_coupons = get_option('wizbee_duplicate_order_apply_coupons', 'yes');

    // Product items
    $items = $order->get_items();
    foreach ($items as $item_id => $originalOrderItem) {
        $quantity = $originalOrderItem['qty'];
		$variationID = $originalOrderItem['variation_id'];
        $lineSubtotal = $originalOrderItem['line_subtotal'];
        $productID = $originalOrderItem['product_id'];
        $product = wc_get_product($productID);

        if ('yes' === $copy_old_price) {
            if ('yes' === $apply_coupons) {
                $lineTotal = $originalOrderItem['line_total'];
            } else {
                $lineSubtotal = $originalOrderItem['line_subtotal'] / $quantity;
                $lineNewSubtotal = $lineSubtotal;
                $lineTotal = $lineNewSubtotal * $quantity;
            }
        } else {
            $lineTotal = $product ? $product->get_price() * $quantity : 0;
        }

        // Add the product as a line item in the new order
        $new_item_id = wc_add_order_item($new_order->get_id(), [
            'order_item_name' => $originalOrderItem['name'],
            'order_item_type' => 'line_item',
        ]);
$original_meta_data = $originalOrderItem->get_meta_data();
    foreach ($original_meta_data as $meta) {
        wc_add_order_item_meta($new_item_id, $meta->key, $meta->value, true); // Add original meta to the new item
    }
        // Copy item meta and set calculated price
        wc_add_order_item_meta($new_item_id, '_qty', $quantity);
        wc_add_order_item_meta($new_item_id, '_product_id', $productID);
		wc_add_order_item_meta($new_item_id, '_variation_id', $variationID);
        wc_add_order_item_meta($new_item_id, '_line_total', wc_format_decimal($lineTotal));
        wc_add_order_item_meta($new_item_id, '_line_subtotal', wc_format_decimal($lineSubtotal));
    }

    // Copy currency
    if ('yes' === $copy_old_price) {
        $currency = $order->get_currency();
        $new_order->set_currency($currency);
    }

    // Copy fees, shipping, and coupons
    if ('yes' === $copy_fees) {
        $fee_items = $order->get_items('fee');
        foreach ($fee_items as $fee_item) {
            $new_fee_item = new WC_Order_Item_Fee();
            $new_fee_item->set_name($fee_item->get_name());
            $new_fee_item->set_total($fee_item->get_total());
            //$new_fee_item->set_taxes($fee_item->get_taxes());
            $new_order->add_item($new_fee_item);
        }
    }

    if ('yes' === $copy_shipping) {
        $shipping_items = $order->get_items('shipping');
        foreach ($shipping_items as $shipping_item) {
            $new_shipping_item = new WC_Order_Item_Shipping();
            $new_shipping_item->set_method_title($shipping_item->get_method_title());
            $new_shipping_item->set_method_id($shipping_item->get_method_id());
            $new_shipping_item->set_total($shipping_item->get_total());
        	$meta_data = $shipping_item->get_meta_data();
        	foreach ($meta_data as $meta) {
            $new_shipping_item->add_meta_data($meta->key, $meta->value, true);
        }
			
            $new_order->add_item($new_shipping_item);
        }
    }

    if ('yes' === $apply_coupons) {
        $coupons = $order->get_used_coupons();
        foreach ($coupons as $coupon_code) {
            $new_order->apply_coupon($coupon_code);
        }
    }

    // Set the selected status
    $selected_status = get_option('wizbee_duplicate_order_status', 'wc-pending');
    $new_order->set_status($selected_status);

 
    $new_order->calculate_totals();
    $new_order->save();

    // Add notes for tracking
    $original_order_url = admin_url('post.php?post=' . $order_id . '&action=edit');
    $new_order->add_order_note(sprintf(__('Duplicated from order <a href="%1$s">#%2$d</a>', 'easy-duplicate-woo-order'), esc_url($original_order_url), $order_id));

    $new_order_url = admin_url('post.php?post=' . $new_order->get_id() . '&action=edit');
    $order->add_order_note(sprintf(__('This order duplicated to order <a href="%1$s">#%2$d</a>', 'easy-duplicate-woo-order'), esc_url($new_order_url), $new_order->get_id()));
		
	wp_safe_redirect(add_query_arg('duplicated', 'yes', $new_order_url));
	exit;
}
