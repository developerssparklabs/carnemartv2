<?php

/**
 * WooCommerce Payment Method locationwise.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.2
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 */

/**
 * WooCommerce Payment Method locationwise.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Checkout_Payment_Method_Locationwise
{
	public function __construct()
	{
		add_filter('woocommerce_available_payment_gateways', array($this, 'filter_available_payment_gateways_per_category'), 10, 2);
	}

	public function filter_available_payment_gateways_per_category($available_gateways)
	{
		if (is_admin())
			return $available_gateways;

		global $woocommerce;
		$tmp_term_meta = array();
		if (isset($woocommerce->cart->cart_contents)) {
			$cart = $woocommerce->cart->cart_contents;
			foreach ($cart as $array_item) {
				$cart_locations = $array_item["select_location"]["location_name"];
				$isExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
				if (!empty($isExcLoc)) {
					$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isExcLoc));
				} else {
					$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
				}
				foreach ($terms as $term) {
					if ($term->name == $array_item['select_location']['location_name']) {
						$wcmlim_payment_methods = get_term_meta($term->term_id, 'wcmlim_payment_methods');
						if (!empty($wcmlim_payment_methods[0])) {
							foreach ($wcmlim_payment_methods[0] as $payment_for_location) {
								if (!empty($payment_for_location)) {
									array_push($tmp_term_meta, $payment_for_location);
								}
							}
						}
					}
				}
			}
			$common_payment_methods = array_intersect($tmp_term_meta, array_unique(array_diff_key($tmp_term_meta, array_unique($tmp_term_meta))));
			if (empty($common_payment_methods) && count($cart) == 1) {
				$common_payment_methods = $tmp_term_meta;
			}
			foreach ($available_gateways as $gateway) {
				if (!in_array($gateway->id, $common_payment_methods)) {
					unset($available_gateways[$gateway->id]);
				}
			}
		}

		if($available_gateways) {
			$available_gateway =  $available_gateways[$gateway->id];
		} else {
			$available_gateway = null;
		}	
			
		if (empty($available_gateway)) {		
			add_filter('woocommerce_no_available_payment_methods_message', function ($no_gateways_message) {
				return __('Common payment methods for selected items in the cart are not available please place an order for each location separately', 'wcmlim');
			});
		}
		return $available_gateways;
	}
}
new Wcmlim_Checkout_Payment_Method_Locationwise();
