<?php

/**
 * WooCommerce Table Rate Shipping Compatibility of the plugin.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.2
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 */

/**
 * WooCommerce Table Rate Shipping Compatibility of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Table_Rate_Shipping_Compatibility
{
  public function __construct()
  {
    add_filter('betrs_shipping_cost_conditionals', array($this, 'wcmlim_add_location_cond'), 10, 1);
    add_filter('betrs_shipping_cost_conditionals_secondary', array($this, 'wcmlim_add_location_cond_secondary'), 10, 1);
    add_filter('betrs_determine_condition_result', array($this, 'wcmlim_determine_condition_logic'), 10, 3);
    add_action('betrs_shipping_cost_conditionals_tertiary', array($this, 'wcmlim_condition_tertiary'), 10, 5);
    add_filter('betrs_calculated_totals-per_order', array($this, 'wcmlim_add_location_cartdata'), 10, 2);
  }

  public function wcmlim_add_location_cond($conditions)
  {
    // add new option to list
    $conditions['location'] = 'Location';
    return $conditions;
  }

  public function wcmlim_add_location_cond_secondary($conditions)
  {
    // add new option to list
    $conditions['includes']['conditions']['4'] = 'location';

    $conditions['excludes']['conditions']['4'] = 'location';

    return $conditions;
  }

  public function wcmlim_determine_condition_logic($return, $cond, $cart_data)
  {
    $cond_type = sanitize_title($cond['cond_type']);
    if ($cond_type == 'location') {

      $cond_tertiary = apply_filters('betrs_condition_tertiary_' . $cond_type, $cond['cond_tertiary'], $cond);
      $comparison = apply_filters('betrs_comparison_tertiary_' . $cond_type, $cart_data['locations'], $cond);

      if (is_array($comparison)) {
        if (is_array($cond_tertiary)) {
          if ($cond['cond_secondary'] === 'includes') {

            foreach ($comparison as $comp) {

              if (in_array($comp, $cond_tertiary))
                return true;
            }
          }

          if ($cond['cond_secondary'] === 'excludes') {
            $temp = true;
            foreach ($comparison as $comp) {
              if (in_array($comp, $cond_tertiary))
                $temp = false;
            }

            return $temp;
          }
        } else {

          if ($cond['cond_secondary'] === 'includes' && in_array($cond_tertiary, $comparison))
            return true;

          if ($cond['cond_secondary'] === 'excludes' && !in_array($cond_tertiary, $comparison))
            return true;
        }
      }
    }
    // return false if the condition is not met
    return $return;
  }

  public function wcmlim_condition_tertiary($type, $item, $row_ID, $option_ID = null, $cond_key = 0)
  {
    if (isset($item['cond_tertiary'])) {
      if (is_array($item['cond_tertiary'])) {
        $cond_tertiary = array_map('sanitize_text_field', $item['cond_tertiary']);
      } else {
        $cond_tertiary = sanitize_text_field($item['cond_tertiary']);
      }
    } else $cond_tertiary = '';

    $op_name_tertiary = "cond_tertiary[" . $option_ID . "][" . $row_ID . "]";
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
?>
    <select name="<?php echo $op_name_tertiary; ?>[]" class="cond_tertiary">
      <?php
      foreach ($terms as $term) :
        if (isset($term->term_id)) :
      ?>
          <option value="<?php echo $term->term_id; ?>" <?php selected($term->term_id, $cond_tertiary, true); ?>><?php echo $term->name; ?></option>
      <?php
        endif;
      endforeach;
      ?>
    </select>
<?php
  }

  public function wcmlim_add_location_cartdata($data, $items)
  {
    foreach ($items as $key => $values) {
      $locations[] =  $values['select_location']['location_termId'];
    }
    // add the new value to the cart data array
    $data['locations'] = $locations;

    return $data;
  }
}
new Wcmlim_Table_Rate_Shipping_Compatibility();
