<?php
$currency = get_woocommerce_currency_symbol(); 
$order_data = $order->get_data();
$billing_first_name = $order->get_billing_first_name();
$billing_last_name = $order->get_billing_last_name();
$billing_company = $order->get_billing_company();
$billing_address_1 = $order->get_billing_address_1();
$billing_address_2 = $order->get_billing_address_2();
$billing_postcode = $order->get_billing_postcode();
$billing_city = $order->get_billing_city();
$billing_state = $order->get_billing_state();
$billing_country = $order->get_billing_country();
$billing_email = $order->get_billing_email();
$billing_phone = $order->get_billing_phone();

//get shipping address
$shipping_first_name = $order->get_shipping_first_name();
$shipping_last_name = $order->get_shipping_last_name();
$shipping_company = $order->get_shipping_company();
$shipping_address_1 = $order->get_shipping_address_1();
$shipping_address_2 = $order->get_shipping_address_2();
$shipping_postcode = $order->get_shipping_postcode();
$shipping_city = $order->get_shipping_city();
$shipping_state = $order->get_shipping_state();
$shipping_country = $order->get_shipping_country();
$shipping_email = isset($shipping_address['email']) ? $shipping_address['email'] : '';
$shipping_phone = $order->get_shipping_phone();

$payment_method = (WC_HPOS_IS_ACTIVE) ? $order->get_meta("_payment_method", true) : get_post_meta($order_id, '_payment_method', true);
$sURL    = site_url();
$orderdate    = $order->get_date_created();


$islimitenable = get_option('wcmlim_clear_cart');
if ($islimitenable == "on") {
    // enable limit 1
    $TotalPrice    = $currency . "" . number_format($order->get_total(), 2, '.', '');
} else {
    // No limit 1
    $total=0;
    foreach ($order->get_items() as $item) 
    {
        $item_TermId = $item->get_meta('_selectedLocTermId', true);
        if ( $item_TermId == $wcmlim_email_val ) {
            $price = $item->get_subtotal();    
            $total+= $price;
        }
    }
    $TotalPrice    = $currency . "" . number_format($total, 2, '.', '');
}

$to = $wcmlim_email;
$termID_val = get_term( $wcmlim_email_val );
$item_LocName = $termID_val->name;
$subject = "You have received location order";
$message = '<table border="0" cellpadding="0" cellspacing="0" width="600" id="m_7726384717555498504template_container" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px"><tbody><tr>
										<td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="100%" id="m_7726384717555498504template_header" style="background-color:#96588a;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;border-radius:3px 3px 0 0"><tbody><tr>
										<td id="m_7726384717555498504header_wrapper" style="padding:36px 48px;display:block">
										<h1 style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left;color:#ffffff;background-color:inherit">Warehouse Received Order</h1></td>
										</tr></tbody></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="m_7726384717555498504template_body"><tbody><tr><td valign="top" id="m_7726384717555498504body_content" style="background-color:#ffffff">
										<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
										<td valign="top" style="padding:48px 48px 32px">
										<div id="m_7726384717555498504body_content_inner" style="color:#636363;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left">
										<p style="margin:0 0 16px">Hi ' . $item_LocName . ',</p><p style="margin:0 0 16px">Just to let you know — we have received your order ' . $order_id . ':</p><p style="margin:0 0 16px">Payment Via ' . $payment_method . '.</p>
										<h2 style="color:#96588a;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">[Order #' . $order_id . '] (' . $orderdate  . ')</h2>
										<div style="margin-bottom:40px">
										<table cellspacing="0" cellpadding="6" width="100%" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif"><thead><tr>
										<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Product</th>
										<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Quantity</th>
										<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Price</th></tr></thead>										
										<tbody>';								

										//message for product
										foreach ($order->get_items() as $item) {
											$item_TermId = $item->get_meta('_selectedLocTermId', true);

											if (!$item->is_type('line_item')) {
												continue;
											}
											if ( $item_TermId == $wcmlim_email_val ) {
												$quty       = apply_filters('woocommerce_order_item_quantity', $item->get_quantity(), $order, $item); //hve
												
												$itemname = $item->get_name();
												$product_data = $item->get_product();
											
												$price = $item->get_subtotal();
												$price = $currency . "" . number_format($price, 2, '.', '');//hve
												$itemSelLocName = $item->get_meta('Location', true);
												$message .=  '<tr>
												<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word">
													' . $itemname . '
												
												</td>
												<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif">
												' . $quty . '
												</td>
												<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif">
													<span>' . $price . '</span>
												</td>
												</tr>';
											}
										}	

										$message .= '</tbody>										
										<tfoot><tr></tr><tr><th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Payment Method:</th>
										<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $payment_method . '</td></tr>										
										
										<tr>
										<th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Total Payment:</th>
										<td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><span>' . $TotalPrice . '</span></td>
										</tr>

										</tfoot>
										</table></div>
																			<table id="m_7726384717555498504addresses" cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;margin-bottom:40px;padding:0"><tbody><tr></tr></tbody></table><p style="margin:0 0 16px">Thanks for using <a href="' . $sURL . '" target="_blank">' . $sURL . '</a>!</p></div>';



				$message .= '<table id="m_4333498424916750933addresses" cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;margin-bottom:40px;padding:0"><tbody><tr>
				<td valign="top" width="50%" style="text-align:left;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;border:0;padding:0"><h2 style="color:#96588a;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">Billing Address</h2>
				<address style="padding:12px;color:#636363;border:1px solid #e5e5e5">
				' . $billing_first_name . ' &nbsp; ' . $billing_last_name . '<br>' . $billing_address_1 . ' &nbsp; ' . $billing_address_2 . '<br>' . $billing_city . ',' . $billing_state . '&nbsp;' . $billing_postcode . '<br>' . $billing_country . '<br>
				' . $billing_phone . ',' . $billing_email . '<br></address></td></tr></tbody></table>';




				$message .= '<table id="m_4333498424916750933addresses" cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;margin-bottom:40px;padding:0"><tbody><tr>
				<td valign="top" width="50%" style="text-align:left;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;border:0;padding:0"><h2 style="color:#96588a;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">Shipping address</h2>
				<address style="padding:12px;color:#636363;border:1px solid #e5e5e5">
				' . $shipping_first_name . ' &nbsp; ' . $shipping_last_name . '<br>' . $shipping_address_1 . ' &nbsp; ' . $shipping_address_2 . '<br>' . $shipping_city . ',' . $shipping_state . '&nbsp;' . $shipping_postcode . '<br>' . $shipping_country . '<br>
				' . $shipping_phone . ',' . $shipping_email . '<br></address></td></tr></tbody></table>';

				
$fromemail = get_bloginfo('admin_email');
// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
// More headers
$headers .= 'From: Order Received <' . $fromemail . '>' . "\r\n";

wp_mail($to, $subject, $message, $headers);
