<?php

/**
 * WooCommerce Advanced Shipping Compatibility of the plugin.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.2
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 */

/**
 * WooCommerce Advanced Shipping Compatibility of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Woocommerce_Advanced_Shipping_Compatibility
{

  public function __construct()
  {
    add_filter('was_conditions', array($this, 'was_conditions_add_locations'), 10, 1);
    add_filter('was_values', array($this, 'was_values_add_locations'), 10, 2);
    add_action('was_match_condition_locations', array($this, 'was_match_condition_locations'), 10, 4);
  }

  /**
   * Add condition to conditions list.
   *
   * @param	array	$conditions	List of existing conditions.
   * @return	array			List of modified conditions.
   */
  public function was_conditions_add_locations($conditions)
  {
    $conditions['MultiInventory']['locations'] = __('Locations', 'woocommerce-advanced-shipping');
    return $conditions;
  }

  /**
   * Add value field for 'Locations' condition
   * 
   * @param	array	$values		List of value field arguments
   * @param	string	$condition	Name of the condition.
   * @return	array	$values		List of modified value field arguments.
   */
  public function was_values_add_locations($values, $condition)
  {
    switch ($condition) {
      case 'locations':
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
        $values['field'] = 'select';
        foreach ($terms as $key => $value) :
          $values['options'][$key] = $value->name;
        endforeach;
        break;
    }
    return $values;
  }

  /**
   * Must match given Location.
   *
   * @param  bool   $match    Current matching status. Default false.
   * @param  string $operator Store owner selected operator.
   * @param  mixed  $value    Store owner given value.
   * @param  array  $package  Shipping package.
   * @return bool             If the current user/environment matches this condition.
   */
  public function was_match_condition_locations($match, $operator, $value, $package)
  {
    if (!isset(WC()->cart)) :
      return $match;
    endif;

    $match = true;
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    if ('==' == $operator) :
      foreach (WC()->cart->get_cart() as $product) :
        foreach ($terms as $key => $term) :
          if ($value == $key) {
            error_log($term->name);
            error_log(json_encode($product['select_location']['location_name']));
            if ($term->name == $product['select_location']['location_name']) :
              $match = true;
            endif;
          }
        endforeach;
      endforeach;
    elseif ('!=' == $operator) :
      foreach (WC()->cart->get_cart() as $product) :
        foreach ($terms as $key => $term) :
          if ($value == $key) :
            if ($term->name == $product['select_location']['location_name']) :
              $match = false;
            endif;
          endif;
        endforeach;
      endforeach;
    endif;
    return $match;
  }
}

new Wcmlim_Woocommerce_Advanced_Shipping_Compatibility();