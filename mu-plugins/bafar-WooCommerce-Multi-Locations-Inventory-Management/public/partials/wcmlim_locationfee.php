<?php

/**
 * Display Location Fee based on Selected Location.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.5
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 */

/**
 * Display Shipping base on zone.
 *
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Display_Location_Fee
{
    public function __construct()
    {
        add_filter( 'woocommerce_cart_calculate_fees', array($this, 'wcmlim_woo_add_cart_fee'));
    }
    public function wcmlim_woo_add_cart_fee()
    {
        global $woocommerce;
        $prepare_location_fee = 0.0;
        $cart_message = '';
        $cart = $woocommerce->cart->cart_contents;
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));         
        foreach ($cart as $array_item) {       
            if (isset($array_item['select_location']['location_name'])) {
                    foreach ($terms as $term) {
                      if ($term->name == $array_item['select_location']['location_name']) {
                        $term_id = $term->term_id;
                        $location_fee = get_term_meta($term_id, 'wcmlim_location_fee', true);
                        $prepare_location_fee += floatval($location_fee);
                      }
                    }
            }
        }
	    $woocommerce->cart->add_fee( __('Additional Fee', 'woocommerce'), $prepare_location_fee );
    }

}
new Wcmlim_Display_Location_Fee();
