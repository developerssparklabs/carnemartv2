<?php

add_action('woocommerce_order_item_add_action_buttons', 'deus_add_order_received_link');

function deus_add_order_received_link($order)
{
   if (!$order instanceof WC_Order) {
      return;
   }

   $order_id = $order->get_id();
   $order_key = $order->get_order_key();

   $order_received_url = wc_get_endpoint_url(
      'order-received',
      $order_id,
      wc_get_checkout_url()
   ) . '?key=' . $order_key;

   echo '<a href="' . esc_url($order_received_url) . '" class="button" target="_blank">Ver recibo orden</a>';
}
