<?php

/**
 * Display Shipping base on zone.
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
class Wcmlim_Display_Shipping_zone
{
    public function __construct()
    {
        $isSplitPackages = get_option('wcmlim_enable_split_packages');
        if($isSplitPackages == 'on')
        {
            add_filter( 'woocommerce_shipping_package_name', array($this, 'rename_wcmlim_package'), 10, 3);
            add_filter( 'woocommerce_package_rates', array( $this,'conditional_hide_shipping_methods'), 10, 2 );            
            add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'wcmlim_woocommerce_cart_shipping_packages') ); 
        }
        else
        {
           add_action('woocommerce_before_cart_contents', array($this, 'wcmlim_display_shipping_zones_locations_cartpage_load'));
            add_filter('woocommerce_package_rates', array($this, 'wcmlim_display_shipping_zones_locations'), 10, 2);
        }
    }
    public function wcmlim_woocommerce_cart_shipping_packages( $packages ) {
				// Reset the packages
				$packages = array();
				$location_items_map = array();

				foreach ( WC()->cart->get_cart() as $item ) {
                    $location_id = $item['select_location']['location_termId'];
                    $product_id = $item['product_id'];
                    if($location_id) {
                        $location_items_map[$location_id][] = $item;
                    }
				}
                
				foreach($location_items_map as $key => $location_items) {
                    $term_name = get_term( $key )->name;
					$packages[] = array(
                        'shipping_name' => $term_name,
                        'shipping_term_id' => $key,
						'contents' => $location_items,
						'contents_cost' => array_sum( wp_list_pluck( $location_items, 'line_total' ) ),
						'applied_coupons' => WC()->cart->applied_coupons,
						'destination' => array(
							'country' => WC()->customer->get_shipping_country(),
							'state' => WC()->customer->get_shipping_state(),
							'postcode' => WC()->customer->get_shipping_postcode(),
							'city' => WC()->customer->get_shipping_city(),
							'address' => WC()->customer->get_shipping_address(),
							'address_2' => WC()->customer->get_shipping_address_2()
						)
					);	
				}
				return $packages;
			}
    public function rename_wcmlim_package( $package_name, $i, $package ) {
     
        if ( ! empty( $package['shipping_name'] ) ) {
            $package_name = $package['shipping_name'];
        }
     
        return $package_name;
    }
    public function conditional_hide_shipping_methods( $rates, $package ){
        global $woocommerce;
        $rates_arr = array();
        foreach( $rates AS $id => $data )  {
            $loc_term_id = $package["shipping_term_id"];
            // if the rate id starts with "ups_"
                if ( ! empty( $package['shipping_name'] ) ) {
                    $tmp_package_items = array();
				if(isset($package['contents']))
				{
					foreach($package['contents'] as $key => $item)
					{
						$product_id = $item['product_id'];
						$variation_id = $item['variation_id'];
						$product_id = (!empty($variation_id) || $variation_id != 0) ? $variation_id : $product_id;
						$quantity = $item['quantity'];
						$tmp_package_items[] = array(
							'product_id' => $product_id,
							'qty' => $quantity
						);
					}
				}
                if(isset($data->meta_data))
                {
                    $data->meta = array('location' => $loc_term_id);	
                }
                    $wcmlim_shipping_method = get_term_meta($loc_term_id, 'wcmlim_shipping_method', true);
                   if(is_array($wcmlim_shipping_method)){
                  
                    $rate_split_id = $data->instance_id;
                    if (!in_array($rate_split_id, $wcmlim_shipping_method)) {
                        unset( $rates[ $id ] );
                    }
                }
        }
        }
        return $rates;
    }

    public function wcmlim_display_shipping_zones_locations($available_methods)
    {
        global $woocommerce;
        $packages = $woocommerce->cart->get_shipping_packages();

      if(isset($packages[0])){

        $packages[0]['destination']['country']  = WC()->customer->get_shipping_country();
        $packages[0]['destination']['state']    = WC()->customer->get_shipping_state();
        $packages[0]['destination']['postcode'] =  WC()->customer->get_shipping_postcode();
        $packages[0]['destination']['city']     = WC()->customer->get_shipping_city();
        $packages[0]['destination']['address']  = WC()->customer->get_shipping_address();
        $packages[0]['destination']['address_2']= WC()->customer->get_shipping_address_2();
        
        $shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $packages[0] );
      } else{
        $shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $packages );
      }
        
       
       
        $zone=$shipping_zone->get_zone_name();
            $isShippingMethods = get_option('wcmlim_enable_shipping_methods');
            
            if ($isShippingMethods == "on") {
                // load the contents of the cart into an array.
                global $woocommerce;

                $cart = $woocommerce->cart->cart_contents;

                $found = 'no';
                // loop through the array looking for the tag you set. Switch to true if the tag is     found.
                foreach ($cart as $array_item) {
                    
                    if (isset($array_item['select_location']['location_name'])) {
                        $product_name = $array_item['data']->get_name();
                        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
                        foreach ($terms as $term) {
                            if ($term->name == $array_item['select_location']['location_name']) {
                                $wcmlim_shipping_method = get_term_meta($term->term_id, 'wcmlim_shipping_method', true);
                            $method_found =  0;
                                foreach ($available_methods as $shipping_method => $value) {
                                     $instance_id = $value->instance_id;
                                     if(is_array($wcmlim_shipping_method)){ 
                                        if (!in_array($instance_id, $wcmlim_shipping_method)) {
                                            unset($available_methods[$shipping_method]);  
                                      }else {
                                          $method_found = $method_found +1;
                                      }
                                     }
                                     

                                }
                                if ($method_found == 0) {
                                     wc_clear_notices();
                                     wc_add_notice("Cart item <b> $product_name </b> could not be delivered in shipping zone", "error");
                                }
                            }
                        }
                    }
                }
            }
        return $available_methods;
    }
    public function wcmlim_check_cart_zone()
    {
        // load the contents of the cart into an array.
        global $woocommerce;
        $packages = $woocommerce->cart->get_shipping_packages();

        $cart = $woocommerce->cart->cart_contents;

        $found = 'no';
        // loop through the array looking for the tag you set. Switch to true if the tag is     found.
        foreach ($cart as $array_item) {
            if (isset($array_item['select_location']['location_name'])) {
                $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
                foreach ($terms as $term) {
                    if ($term->name == $array_item['select_location']['location_name']) {
                        $wcmlim_shipping_zone = get_term_meta($term->term_id, 'wcmlim_shipping_zone', true);
                        $shipping_packages =  WC()->cart->get_shipping_packages();
                        $shipping_zone = wc_get_shipping_zone(reset($shipping_packages));
                        $zone_id   = $shipping_zone->get_id();

                    }
                }
            }
        }
        return $found;
    }

    function wcmlim_display_shipping_zones_locations_cartpage_load(){
        $packages = WC()->shipping->get_packages();
    
        if (isset($packages[0])) {
            $methods = $packages[0]['rates'];
            $this->wcmlim_display_shipping_zones_locations($methods);
        }
    }
    

}
new Wcmlim_Display_Shipping_zone();
