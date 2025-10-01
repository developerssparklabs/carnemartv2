<?php

/**
 * Backend Only Mode of the plugin (Order Fulfilment).
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.2
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Nearby_Instock_Location
{
  public function __construct()
  {
    $wcmlim_order_fulfil_edit = get_option('wcmlim_order_fulfil_edit');
    $fulfilment_rule = get_option("wcmlim_order_fulfilment_rules");

    if ($fulfilment_rule == "nearby_instock") {
      $fulfilment_rule = "clcsadd";
    }

    if ($fulfilment_rule == "nearby_instock") {
      add_action('woocommerce_before_cart_contents', array($this, 'wcmlim_nearby_instock_location'), 10, 4);
      add_filter('woocommerce_cart_item_name', array($this, 'nearby_wcmlim_cart_item_name'), 10, 3);
    }

    $checked = 'on';
    if ($checked == "on") {
      //add_action('woocommerce_before_calculate_totals', array($this, 'add_custom_price_nearby'), 100);
    }
  }

  /**
   * define the woocommerce_saved_order_items callback 
   * 
   * @since 1.2.13
   * Updated 3.0.7
   */
  private static function sortByDis($a, $b)
  {
    if ($a["value"] == $b["value"]) {
      return 0;
    }
    return ($a["value"] < $b["value"]) ? -1 : 1;
  }

  //* Second Nearest Location if Nearest Location is Out Of Stock  
  public function getSecondNearestLocation($addresses, $dis_unit, $product_id)
  {
    $ExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($ExcLoc)) {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExcLoc));
    } else {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }
    foreach ($addresses as $ad) {
      $dnumber[] = $ad["value"];
    }
    if (!empty($dnumber)) {
      sort($dnumber, SORT_NUMERIC);
      $smallest_2nd = array_shift($dnumber);
    }
    foreach ($addresses as $e => $v) {
      if ($smallest_2nd == $v["value"]) {
        $finalKeyOfLocation = $e;
      }
    }

    $secondNearLocKey = isset($finalKeyOfLocation) ? $finalKeyOfLocation : "";

    foreach ($terms as $index => $term) {
      if ($index == $secondNearLocKey) {
        $secNearStore[] = $term->name;
      }
    }

    $dis_in_un = "";
    foreach ($addresses as $k => $address) {
      if ($secondNearLocKey == $k) {
        if ($dis_unit == "kms") {
          $dis_in_un = $address["dis_in_un"];
        } elseif ($dis_unit == "miles") {
          $dis_in_un = round($address["value"] * 0.621, 1) . ' miles';
        } elseif ($dis_unit == "none") {
          $dis_in_un = $address["dis_in_un"];
        }
        $secNearStore[] = $dis_in_un;
      }
    }
    $secNearStore[] = $secondNearLocKey;
    return $secNearStore;
  }

  //Displays the Selected Location below the Product name in Cart
  public function nearby_wcmlim_cart_item_name($name1, $cart_item, $cart_item_key)
  {
    $from_address = get_option('from_address');

    foreach ($from_address as $key => $value) {
      if ((in_array($cart_item['variation_id'], $value))) {
        $name = $value['name'];
      }

      if (in_array($cart_item['product_id'], $value)) {
        $name = $value['name'];
      }
    }

    if (isset($cart_item['select_location'])) {
      $locescstring = __("Location :", "wcmlim");
      $name1 .= sprintf('<p>%s</p>', __($locescstring . $cart_item['select_location']['location_name']));
    } else {
      $termExclude = get_option("wcmlim_exclude_locations_from_frontend");
      if (!empty($termExclude)) {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $termExclude));
      } else {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
      }
      foreach ($terms as $term) {
        $this->max_value_inpl = get_post_meta($cart_item['product_id'], "wcmlim_stock_at_{$term->term_id}", true);
      }
    }
    $this->max_in_value = isset($cart_item['select_location']) ? $cart_item['select_location'] : "";


    return $name1;
  }

  public function getLocationServiceRadius($distanceKey)
  {
    $ExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($ExcLoc)) {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExcLoc));
    } else {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }
    foreach ($terms as $key => $value) {
      if ($distanceKey == $key) {
        $_locRadius = get_term_meta($value->term_id, 'wcmlim_service_radius_for_location', true);
      }
    }
    return $_locRadius;
  }

  public function wcmlim_nearby_instock_location()
  {
    global $woocommerce, $post, $nearby_term_id, $order_id;
    $country = WC()->customer->get_shipping_country();
    $state = WC()->customer->get_shipping_state();
    $city = WC()->customer->get_shipping_city();
    $addressone = WC()->customer->get_shipping_address_1();
    $addresstwo = WC()->customer->get_shipping_address_2();
    $postcode = WC()->customer->get_shipping_postcode();
    if (isset($city)) {
      $ladd = str_replace(",", "", $city);
      $city = str_replace(" ", "+", $ladd);
    }

    $origins = $city . "+" . $state . "+" . $country;
    $dis_unit = get_option("wcmlim_show_location_distance");
    $google_api_key = get_option('wcmlim_google_api_key');

    $isExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($isExcLoc)) {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isExcLoc));
    } else {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }

    $items = $woocommerce->cart->get_cart();
    foreach ($items as $item => $values) {
      //get product id
      $_product = wc_get_product($values['data']->get_id());
      if ($values['variation_id'] > 0) {
        $product_id = ($values['variation_id']);
      } else {
        $product_id = $values['data']->get_id();
      }

      //get terms
      $dest = array();
      foreach ($terms as $in => $term) {
        //get stock for product
        $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
        if (!empty($postmeta_stock_at_term) && ($postmeta_stock_at_term > 0)) {
          $location_terms[] = $term;
          $term_meta = get_option("taxonomy_$term->term_id");
          $term_meta = array_map(function ($term) {
            if (!is_array($term)) {
              return $term;
            }
          }, $term_meta);
          $spacead = implode(" ", array_filter($term_meta));
          $dest[] = str_replace(" ", "+", $spacead);
        }
      }

      $destination = implode("|", $dest);
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://maps.googleapis.com/maps/api/distancematrix/json?units=metrics&origins=" . $origins . "&destinations=" . $destination . "&key={$google_api_key}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
      ));
      $response = curl_exec($curl);
      $response_arr = json_decode($response);

      curl_close($curl);
      if (isset($response_arr->error_message)) {
        $response_array["status"] = "false";
        wc_add_notice($response_arr->error_message, $notice_type = 'error');
      }

      foreach ($response_arr->rows as $r => $t) {
        foreach ($t as $key => $value) {
          foreach ($value as $a => $b) {
            if ($b->status == "OK") {
              $dis = explode(" ", $b->distance->text);
              $plaindis = str_replace(',', '', $dis[0]);
              if ($dis_unit == "kms") {
                $dis_in_un = $b->distance->text;
              } elseif ($dis_unit == "miles") {
                $dis_in_un = round($plaindis * 0.621, 1) . ' miles';
              } elseif ($dis_unit == "none") {
                $dis_in_un = $b->distance->text;
              }
              $distance[] = array("value" => $plaindis, "key" => $a, "dis_in_un" => $dis_in_un);
            }
          }
        }
      }

      usort($distance, array('Wcmlim_Nearby_Instock_Location', 'sortByDis'));
      $mindiskey = $distance[0]['key'];
      $loc_key = 0;
      foreach ($terms as $in => $term) {
        //get stock for product
        $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
        if (!empty($postmeta_stock_at_term) && ($postmeta_stock_at_term > 0)) {
          if ($mindiskey == $loc_key) {
            $nearby_term_id = $term->term_id;
            $name = $term->name;
            $values['select_location']['location_name'] = $term->name;
            $values['select_location']['location_termId'] = (int) $nearby_term_id;
            $from_address[] = array(
              'nearby_id_location' => $nearby_term_id,
              'product_id' => $product_id,
              'name' => $name
            );
          }
          $loc_key++;
        }
      }
    }
    update_option('from_address', $from_address);
    return $from_address;
  }

  function add_custom_price_nearby($cart)
  {
    global $woocommerce;
    error_log('add_custom_price_nearby-admin');
    if (is_admin() && !defined('DOING_AJAX'))
      return;

    if (did_action('woocommerce_before_calculate_totals') >= 2)
      return;


    // Loop Through cart items
    foreach ($cart->get_cart() as $cart_item) {
      // Get the product id (or the variation id)
      $product_id = $cart_item['data']->get_id();
      $items = $woocommerce->cart->get_cart();
      foreach ($items as $item => $values) {
        $isExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
        if (!empty($isExcLoc)) {
          $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isExcLoc));
        } else {
          $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
        }

        foreach ($terms as $in => $term) {
          $from_address = get_option('from_address');
          $id_pml = array_search($product_id, array_column($from_address, 'product_id'));
          $location_regular_price = get_post_meta($product_id, 'wcmlim_regular_price_at_' . $from_address[$id_pml]['nearby_id_location'], true);
          $location_sale_price = get_post_meta($product_id, 'wcmlim_sale_price_at_' . $from_address[$id_pml]['nearby_id_location'], true);
          $location_sale_price = get_post_meta($product_id, 'wcmlim_sale_price_at_' . $from_address[$id_pml]['nearby_id_location'], true);

          $pml_product = wc_get_product($product_id);

          $reg_p = $pml_product->get_regular_price();
          $sale_p = $pml_product->get_sale_price();
          $price = $pml_product->get_price();

          if (empty($location_sale_price) && empty($location_regular_price)) {
            if (!empty($sale_p)) {
              $cart_item['data']->set_price($sale_p);
            } else {
              $cart_item['data']->set_price($price);
            }
          } else {
            if (empty($location_sale_price)) {
              $cart_item['data']->set_price($location_regular_price);
            } else {
              $cart_item['data']->set_price($location_sale_price);
            }
          }
        }
      }
    }
  }
}
new Wcmlim_Nearby_Instock_Location();