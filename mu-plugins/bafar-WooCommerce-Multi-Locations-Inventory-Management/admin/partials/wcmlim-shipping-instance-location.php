<?php

/**
 * WooCommerce Shipping Instance Setting Location Enable.
 *
 * @link       http://www.techspawn.com
 * @since      3.5.5
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 */

/**
 * WooCommerce Shipping Instance Setting Location Enable of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Woocommerce_Shipping_Instance_Setting
{

  public function __construct()
  {
    add_action('woocommerce_init', array($this, 'wcmlim_shipping_settings_location_enabled'));
    add_action("wp_ajax_wcmlim_save_localpickup_instance_enabled", array($this, "wcmlim_save_localpickup_instance_enabled"));
    add_action("wp_ajax_wcmlim_fetch_localpickup_instance_enabled", array($this, "wcmlim_fetch_localpickup_instance_enabled")); 
  }

  public function wcmlim_shipping_settings_location_enabled() {
		$shipping_methods = WC()->shipping->get_shipping_methods();
		foreach ( $shipping_methods as $shipping_method ) {
			add_filter( 'woocommerce_shipping_instance_form_fields_' . $shipping_method->id, array( $this, 'wcmlim_shipping_settings_fields_location_enabled' ) );
		}
	}
  /**
   * Add value field for 'Locations' condition
   * 
   * @param	array	$values		List of value field arguments
   * @param	string	$condition	Name of the condition.
   * @return	array	$values		List of modified value field arguments.
   */
  public function wcmlim_shipping_settings_fields_location_enabled($settings ) {

   
    $zone_id = isset($_GET['zone_id'])?$_GET['zone_id']:'';
    $shipping_zone = new WC_Shipping_Zone($zone_id);
    $settings['wcmlim_localpickup_instance_enabled'] = array(
      'title' => "<h2>WooCommerce Multi Locations </h2><p> Assign this Shipping Method to specific Locations</p>",
      'type'    => 'title',
      'label'   => __( 'Enable Location' )
    );
    $pickup_terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    foreach ($pickup_terms as $pkey => $pterm) {   
      $pterm_id = $pterm->term_id;
    
      $pterm_name = ucfirst($pterm->name);
      $title = '';
    
    $wcmlim_shipping_method = get_term_meta($pterm_id, 'wcmlim_shipping_method', true);
    // if(is_array($wcmlim_shipping_method))
    // {
    //   $wcmlim_shipping_method[] = isset($instance_id)?$instance_id:'';
    // }
    // else
    // {
    //   $wcmlim_shipping_method[] = isset($instance_id)?$instance_id:'';

    // }
    
    
      $settings['wcmlim_localpickup_instance_enabled_'.$pterm_id] = array(
        'title' => $title,
        'type'    => 'checkbox',
        'class'   => 'wcmlim_localpickup_instance_enabled',
        'label'   => __( 'Enable for <b>'.$pterm_name.'</b>' ),
        'default' => 'no',
      );
    
      }
      
 
     return $settings;
   }

public function wcmlim_save_localpickup_instance_enabled()
{
  $instance_id = $_POST['instance_id'];
  $zone_id = $_POST['zone_id'];
  $this_value = $_POST['this_value'];
  $this_id = $_POST['this_id'];
  $zone_key = 0;

    //only get last numeric value from this id
    preg_match('/\d+$/', $this_id, $matches);
    $location_id = $matches[0];

    //get zone key from zone id
    $shipping_zones = WC_Shipping_Zones::get_zones();
    foreach ((array) $shipping_zones as $key => $value) {
      if($zone_id == $value['zone_id'])
      {
        $zone_key = $key;
      }
    }
    $wcmlim_shipping_zone = array();
    //get wcmlim_shipping_zone from term meta
    $wcmlim_shipping_zone = get_term_meta($location_id, 'wcmlim_shipping_zone', true);
    if(!is_array($wcmlim_shipping_zone))
    {
      $wcmlim_shipping_zone[] = $zone_key;
    }


    //check if zone key already exists
    if($this_value == 1)
    { 
      if(!in_array($zone_key, $wcmlim_shipping_zone))
      {
        $wcmlim_shipping_zone[] = $zone_key;
      }
      //remove duplicate values from $wcmlim_shipping_zone
      $wcmlim_shipping_zone = array_unique($wcmlim_shipping_zone);
      update_term_meta($location_id, 'wcmlim_shipping_zone', $wcmlim_shipping_zone);
    }else{
      
      // if(($key = array_search($zone_key, $wcmlim_shipping_zone)) !== false) {
      //   unset($wcmlim_shipping_zone[$key]);
      
      // update_term_meta($location_id, 'wcmlim_shipping_zone', $wcmlim_shipping_zone);
      
    }

    
    $wcmlim_shipping_method = get_term_meta($location_id, 'wcmlim_shipping_method', true);  
    // print_r($wcmlim_shipping_method);
    if(!is_array($wcmlim_shipping_method))
    {
      $wcmlim_shipping_method[] = $instance_id;
    }
    
    if($this_value == 1)
    {
      if(!in_array($instance_id, $wcmlim_shipping_method))
      {
        $wcmlim_shipping_method[] = $instance_id;
      }
    }
    else
    {
      if(($key = array_search($instance_id, $wcmlim_shipping_method)) !== false) {
        unset($wcmlim_shipping_method[$key]);
      }
    }
    //remove duplicate values from $wcmlim_shipping_method
    $wcmlim_shipping_method = array_unique($wcmlim_shipping_method);
    update_term_meta($location_id, 'wcmlim_shipping_method', $wcmlim_shipping_method);
    $wcmlim_shipping_zone = get_term_meta($location_id, 'wcmlim_shipping_zone', true);
    $wcmlim_shipping_method = get_term_meta($location_id, 'wcmlim_shipping_method', true);

    $wcmlim_shipping_method = get_term_meta($location_id, 'wcmlim_shipping_method', true);
    $shipping_zone = new WC_Shipping_Zone($zone_key);
    $shipping_methods = $shipping_zone->get_shipping_methods();
    $shipping_methods_keys = array_keys($shipping_methods);
    $result = array_intersect($shipping_methods_keys, $wcmlim_shipping_method);
    if(empty($result)){
      if(($key = array_search($zone_key, $wcmlim_shipping_zone)) !== false) {
        unset($wcmlim_shipping_zone[$key]);
        update_term_meta($location_id, 'wcmlim_shipping_zone', $wcmlim_shipping_zone);
      }
    }

    echo 'method updated';
    die();
}

public function wcmlim_fetch_localpickup_instance_enabled()
{
$instance_id = $_POST['instance_id'];
$zone_id = $_POST['zone_id'];
$this_id = $_POST['this_id'];
$zone_key = 0;

//only get last numeric value from this id
preg_match('/\d+$/', $this_id, $matches);
$location_id = $matches[0];

//get wcmlim_shipping_zone from term meta
$wcmlim_shipping_zone = get_term_meta($location_id, 'wcmlim_shipping_zone', true);
$wcmlim_shipping_zone[] = $zone_key;

$wcmlim_shipping_method = get_term_meta($location_id, 'wcmlim_shipping_method', true);
if(in_array($instance_id, $wcmlim_shipping_method))
{
  $checked = 'checked';
}
else
{
  $checked = 'uncheck';
}
echo $checked;
die();
}

  
}

new Wcmlim_Woocommerce_Shipping_Instance_Setting();
