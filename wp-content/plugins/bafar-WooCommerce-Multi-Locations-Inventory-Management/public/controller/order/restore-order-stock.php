<?php
$order = wc_get_order($order_id);

if ( ! get_option('woocommerce_manage_stock') == 'yes' && ! sizeof( $order->get_items() ) > 0 ) {
    return;
}

foreach ( $order->get_items() as $item ) {

    if ( $item['product_id'] > 0 ) {
        $_product = $order->get_product_from_item( $item );

        if ( $_product && $_product->exists() && $_product->managing_stock() ) {
                //OpenPos - Outlet stock updated
            $wcmlim_pos_compatiblity = get_option('wcmlim_pos_compatiblity');

            if ($wcmlim_pos_compatiblity == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                $location_name = $item->get_meta('Location');
                $termid = $item->get_meta('_selectedLocTermId');
                $product_id = $_product->get_id();
                $wcmlim_pos_id =  get_term_meta($termid, 'wcmlim_pos_compatiblity', true);
                $poswarehouseStock = get_post_meta($item['product_id'], "_op_qty_warehouse_{$wcmlim_pos_id}", true);
                $locstockQty = get_post_meta($item['product_id'], "wcmlim_stock_at_{$termid}", true);
                $newqty = $poswarehouseStock + $item['qty'];
                $locstock = $locstockQty + $item['qty'];
                
                update_post_meta($item['product_id'], "_op_qty_warehouse_{$wcmlim_pos_id}", $newqty);
                update_post_meta($item['product_id'], "wcmlim_stock_at_{$termid}", $locstock);
            }
        }
    }
}