<?php
global $result;
$location_id = $location_id;
$prepare_shipping_cost_text = '';
WC()->shipping->load_shipping_methods();
WC()->shipping->reset_shipping();
$currency_symbol = get_woocommerce_currency_symbol();

$nearByAddress = $_COOKIE['wcmlim_nearby_location'];
$nearByAddress = str_replace(' ', '+', $nearByAddress);

if(empty($nearByAddress))
{
  $shipping_address = WC()->customer->get_shipping_address();
  $country = WC()->customer->get_shipping_country();
  $state   = WC()->customer->get_shipping_state();
  $postcode = WC()->customer->get_shipping_postcode();
  $city     = WC()->customer->get_shipping_city();
  //remove comma from city
  $city = str_replace(',', '', $city);
  $state = str_replace(',', '', $state);
}
else
{
  $address = $this->getAddress($nearByAddress);
  $country  = $address['country'];
  $state    = $address['state'];
  $postcode = $address['postal_code'];
  $city     = $address['city'];
}
if ($postcode && !WC_Validation::is_postcode($postcode, $country)) {
  throw new Exception(__('Please enter a valid postcode / ZIP.', 'woocommerce'));
} elseif ($postcode) {
  $postcode = wc_format_postcode($postcode, $country);
}

if ($country) {

  WC()->customer->set_location($country, $state, $postcode, $city);
  WC()->customer->set_shipping_location($country, $state, $postcode, $city);
  WC()->customer->set_shipping_address_1($city); 
} else {
  WC()->customer->set_to_base();
  WC()->customer->set_shipping_to_base();
}

WC()->customer->save();
WC()->customer->set_calculated_shipping(true);


do_action('woocommerce_calculated_shipping');

WC()->shipping->calculate_shipping(WC()->cart->get_shipping_packages());
$packages = WC()->shipping->get_packages();

if (count($packages) > 0) {
  foreach ($packages as $i => $package) {
    if (count($package['rates']) > 0) {
      foreach ($package['rates'] as $key => $value) {
        $instance_id = $value->instance_id;
        //get meta wcmlim_shipping_method of location_id
        $wcmlim_shipping_method = get_term_meta($location_id, 'wcmlim_shipping_method', true);
        //check if wcmlim_shipping_method exist instance_id
        if((in_array($instance_id, $wcmlim_shipping_method)) ||empty($wcmlim_shipping_method))
        {
          $result[$value->cost] = "<li>" . $value->label . " <span> - " . $currency_symbol . " " . $value->cost . "</span></li>";
        }
      }
    }
  }
}
if(is_array($result))
{
$shipping_array_count = count($result);

ksort($result);
if(count($result) > 1)
{
    $arr_inc_count = 1;
    foreach($result as $shipping_cost_key => $shipping_cost_value)
    {
        if($arr_inc_count == 1)
        {
        $prepare_shipping_cost_text .= $currency_symbol.$shipping_cost_key;
        }
        if($arr_inc_count == $shipping_array_count)
        {
        $prepare_shipping_cost_text .= '-'.$currency_symbol.$shipping_cost_key;
        }
        $arr_inc_count++;
    }
}
else
{
    foreach($result as $shipping_cost_key => $shipping_cost_value)
    {
        $prepare_shipping_cost_text .= $currency_symbol.$shipping_cost_key;	
    }
}
}


return $prepare_shipping_cost_text;
die();