<?php
function wcmlim_local_shipping_init()
{
 
  if (!class_exists('Wcmlim_Pickup_Shipping_Method')) {

    class Wcmlim_Pickup_Shipping_Method extends WC_Shipping_Method
    {
      /**
       * Constructor.
       *
       * @param int $instance_id
       */
      public function __construct($instance_id = 0)
      {
        
        $this->id = 'wcmlim_pickup_location';
        $this->instance_id = absint($instance_id);
        $this->method_title = __("Pickup Location", 'wcmlim');
        $this->supports = array(
          'shipping-zones',
          'instance-settings',
          'instance-settings-modal',
        );
        $this->init();
      }

      /**
       * Initialize custom shiping method.
       */
      public function init()
      {

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->title = $this->get_option('title');

        // Actions
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
      }

      /**
       * Calculate custom shipping method.
       *
       * @param array $package
       *
       * @return void
       */
      public function calculate_shipping($package = array())
      {
        $this->add_rate(array(
          'label' => $this->title,
          'package' => $package,
        ));
      }

      /**
       * Init form fields.
       */
      public function init_form_fields()
      {
        $this->instance_form_fields = array(
          'title' => array(
            'title' => __('Pickup Location', 'wcmlim'),
            'type' => 'text',
            'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
            'default' => __('Pickup Location', 'wcmlim'),
            'desc_tip' => true,
          ),
        );
      }
    }
  }
}
// show address in below local-pickup selection on checkout page -codeinit

function details($pickup_location_term)
  {
    if(get_option('wcmlim_allow_local_pickup') == 'on' && get_option('wcmlim_allow_only_backend') == 'on'){
    ?>
    <p class="local_pickup_address"></p>
    <?php
    }
    if(get_option('wcmlim_allow_local_pickup') == 'on' && get_option('wcmlim_allow_only_backend') != 'on'){
      ?>
      <p class="local_pickup_address"></p>
      <?php
      }
  }
// show address in below local-pickup selection on checkout page -codeend

// ajax call back and hook to show local pickup address on checkout page -codeinit
add_action('wp_ajax_wcmlim_show_address',  'wcmlim_show_address_on_checkout');
add_action('wp_ajax_nopriv_wcmlim_show_address', 'wcmlim_show_address_on_checkout');
function wcmlim_show_address_on_checkout()
    {
      if(empty($_POST['location_id']))
      {
        wp_die();
      }

      $term_id = sanitize_text_field($_POST['location_id']);
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
      
      foreach ($terms as $key => $term) {
        if ($term_id == $term->term_id) {
          $street_address = get_term_meta(
            $term->term_id,
            'wcmlim_street_number',
            true
          );
          $postcode = get_term_meta($term_id, 'wcmlim_postal_code', true);
          $state = get_term_meta(
            $term_id,
            'wcmlim_administrative_area_level_1',
            true
          );
          $state = ucwords($state);
          $country_state = get_term_meta(
            $term_id,
            'wcmlim_country',
            true
          );
          $email = get_term_meta($term_id, 'wcmlim_email', true);
          $phone = get_term_meta($term_id, 'wcmlim_phone', true);
          $term_meta = array(
            'street_address' => $street_address,
            'wcmlim_city' => $term->name,
            'wcmlim_postcode' => $postcode,
            'wcmlim_state_code' => $wcmlim_state_code,
            'wcmlim_state' => $state,
            'wcmlim_country_state' => $country_state,
            'wcmlim_email' => $email,
            'wcmlim_phone' => $phone,
          );
          $error_prep = '';
          foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {  
            $cart_product_id = $cart_item['variation_id'] ?: $cart_item['product_id'];
            $wc_product = wc_get_product( $cart_product_id );
            $wc_item_name = $wc_product->get_name();
             if(!empty($cart_product_id))
            {
              $loc_stock = intval(get_post_meta($cart_product_id, "wcmlim_stock_at_$term_id", true));
              $postmeta_backorders_product = get_post_meta($cart_product_id, '_backorders', true);
              if(($loc_stock == '0') || ($loc_stock == '') || ($loc_stock < 0))
              {
                if((!empty($postmeta_backorders_product)) && ($postmeta_backorders_product != "yes") )
                {
                  if(!empty($error_prep))
                  {
                    $error_prep .= "<br /> The Item <i>$wc_item_name</i> could not be Pickup at <i>$term->name</i>";
                  }
                  else
                  {
                    $error_prep .= "<br /> The Item <i>$wc_item_name</i> could not be Pickup at <i>$term->name</i>";
                  }
                }
              }          
            }
          }

          if(!empty($error_prep))
          {
            wc_clear_notices();
            wc_add_notice($error_prep, 'error');
            do_action( 'woocommerce_set_cart_cookies',  true );
          }
          echo json_encode($term_meta);
          wp_die();
        }
      }
      wp_die();
    }
// ajax call back and hook to show local pickup address on checkout page -codeend

add_action('woocommerce_after_checkout_validation', 'wcmlim_after_checkout_validation', 10, 2);

function wcmlim_after_checkout_validation( $fields, $errors ) {
  $error_prep = '';

   $wcmlim_get_shipping_method = $fields['shipping_method'];
   $wcmlim_get_shipping_method = explode(':', $wcmlim_get_shipping_method[0]);
   $wcmlim_pickup_location_result = str_contains("wcmlim_pickup_location", $wcmlim_get_shipping_method[0]) ? 'wcmlim_pickup_location' : 'other';
   if($wcmlim_pickup_location_result == "wcmlim_pickup_location")
   {
   $wcmlim_selected_pickup_location = $_POST['wcmlim_pickup'];
   
   $wcmlim_selected_pickup_location_terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
   $pickup_location_id = '';
   foreach ($wcmlim_selected_pickup_location_terms as $wcmlim_selected_pickup_location_term) {

    if( $wcmlim_selected_pickup_location_term->name == $wcmlim_selected_pickup_location)
     {
      $pickup_location_name = $wcmlim_selected_pickup_location_term->name;
      $pickup_location_id = $wcmlim_selected_pickup_location_term->term_id;
     }
   }
   if($wcmlim_selected_pickup_location == '-1')
    {
      $error_prep .= "It seems the Pickup Location has not been selected, Please Select Location and try again";
    }
   if(!empty($pickup_location_id))
   {
    foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {
      $cart_product_id = $cart_item['variation_id'] ?: $cart_item['product_id'];
      $wc_product = wc_get_product( $cart_product_id );
      $wc_item_quantity = intval($cart_item['quantity']);
      $wc_item_name = $wc_product->get_name();
     
       if(!empty($cart_product_id))
      {
        $loc_stock = intval(get_post_meta($cart_product_id, "wcmlim_stock_at_$pickup_location_id", true));
        $postmeta_backorders_product = get_post_meta($cart_product_id, '_backorders', true);
        if((($loc_stock == 0) || ($loc_stock == '') || ($loc_stock < 0)) && ($postmeta_backorders_product == 'no'))
        {
          if((!empty($postmeta_backorders_product)) && ($postmeta_backorders_product != "yes") )
          {
            if(!empty($error_prep))
            {
              $error_prep .= "<br /> The Item <i>$wc_item_name</i> could not be Pickup at <i>$pickup_location_name</i>";
            }
            else
            {
              $error_prep .= "<br /> The Item <i>$wc_item_name</i> could not be Pickup at <i>$pickup_location_name</i>";
            }
          }
        }
        if(($loc_stock != 0) && ($loc_stock < $wc_item_quantity) && ($postmeta_backorders_product == 'no'))
        {
          $error_prep .= "<br /> The Item <i>$wc_item_name</i>'s quantity <i>$wc_item_quantity</i> is not be available for Pickup location [<i>$pickup_location_name</i>]";
        }
      }
    }
   }
    }
    if(!empty($error_prep))
   {
    $errors->add( 'validation', $error_prep );
   }
}

add_action('woocommerce_shipping_init', 'wcmlim_local_shipping_init');

function wcmlim_local_shipping_method($methods)
{
  $methods['wcmlim_pickup_location'] = 'Wcmlim_Pickup_Shipping_Method';
  return $methods;
}
add_filter('woocommerce_shipping_methods', 'wcmlim_local_shipping_method');


/**
 * Set message on cart page
 * @version 1.6.1
 */

function wcmlim_wc_cart_totals_before_order_total()
{
  if (is_wcmlim_chosen_shipping_method()) {

    // load the contents of the cart into an array.
    global $woocommerce;
    $cart_message = '';
    $cart = $woocommerce->cart->cart_contents;
    $pickup_valid = get_option('wcmlim_pickup_valid');
    
    if (in_array('local-pickup-for-woocommerce/local-pickup.php', apply_filters('active_plugins', get_option('active_plugins'))) ||  is_array(get_site_option('active_sitewide_plugins')) && !array_key_exists('local-pickup-for-woocommerce/local-pickup.php', get_site_option('active_sitewide_plugins'))) {
      update_option($this->option_name . '_allow_local_pickup','');
    }
        
    // loop through the array looking for the tag you set. Switch to true if the tag is     found.
    if (get_option('wcmlim_allow_local_pickup') == 'on' && get_option('wcmlim_allow_only_backend') != 'on') {
      $locAdd = array();
      $pickupAdd = array();
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));    
      foreach ($cart as $array_item) {       
          if (isset($array_item['select_location']['location_name'])) {
            foreach ($terms as $term) {
              if ($term->name == $array_item['select_location']['location_name']) {
                $term_id = $term->term_id;
                $streetNumber = get_term_meta($term_id, 'wcmlim_street_number', true);
                $route = get_term_meta($term_id, 'wcmlim_route', true);
                $locality = get_term_meta($term_id, 'wcmlim_locality', true);
                $state = get_term_meta($term_id, 'wcmlim_administrative_area_level_1', true);
                $postal_code = get_term_meta($term_id, 'wcmlim_postal_code', true);
                $country = get_term_meta($term_id, 'wcmlim_country', true);
                $pickup = get_term_meta($term_id, 'wcmlim_allow_pickup', true);
                $isClearCart = get_option('wcmlim_clear_cart');
                $arrayloc = $streetNumber . " " . $locality . " " . $state . " " . $route;
                
                if(!in_array( $arrayloc, $locAdd ) && $pickup == "on")
                {
                  $streetNumber = ($streetNumber != null ) ? $streetNumber.' ,' : '';
                  $route = ($route != null ) ? $route.' ,' : '';
                  $locality = ($locality != null ) ? $locality.' ,' : '';
                  $state = ($state != null ) ? $state.' ,' : '';
                  $postal_code = ($postal_code != null ) ? $postal_code.' ,' : '';
                  $country = ($country != null ) ? $country : '';

                  $cart_message .= "Pickup Address : " . $streetNumber . " " . $route . " " . $locality . " "  . $state . " " . $postal_code . " " . $country . "<br/>";
                  array_push( $locAdd, $arrayloc );
                  $pickup_add = false; 
                  array_push( $pickupAdd, $pickup_add );

                }else if(!in_array( $arrayloc, $locAdd ) && $pickup == null && $isClearCart == false ) {     
                  $pickup_add = true;                         
                  $cart_message .= $term->name . " " . $pickup_valid . "<br>"; 
                  array_push( $pickupAdd, $pickup_add );
                  array_push( $locAdd, $arrayloc );

                }
              } 
            } 
          }
        }
        if (!empty($cart_message)) {echo $cart_message;}

    } elseif (get_option('wcmlim_allow_only_backend') == 'on') {
      $cart_message = 'Locations for pickup your order are available on the Checkout page.';
      if (!empty($cart_message)) {
      ?>
        <tr class="shipping-pickup-store">
          <td colspan="2">
            <p class="message"><?= $cart_message ?></p>
          </td>
        </tr>
      <?php
      }
    }
  }
}
add_action('woocommerce_cart_totals_before_order_total', 'wcmlim_wc_cart_totals_before_order_total');

/**
 * Get chosen shipping method
 */
function wcmlim_get_chosen_shipping_method()
{
  $chosen_methods = WC()->session->get('chosen_shipping_methods');
  return $chosen_methods[0];
}

/**
 * Check is chosen shipping is wcmlim_local_shipping
 * @version 1.6.1
 * @return bool True is chosen shipping is wcmlim_local_shipping
 */
function is_wcmlim_chosen_shipping_method()
{
  $chosen_shipping = wcmlim_get_chosen_shipping_method();
  $chosen_shipping = explode(":", $chosen_shipping);
  if ($chosen_shipping[0] == "wcmlim_pickup_location") {
    return true;
  }
}

/**
 ** Returns the main instance for wcmlim_local_shipping class
 **/
function wcmlim()
{
  return new Wcmlim_Pickup_Shipping_Method();
}

/**
 * Store table row layout
 */
function wcmlim_location_row_layout()
{
  if (is_wcmlim_chosen_shipping_method()) {
    // load the contents of the cart into an array.
    global $woocommerce;
    $cart_message = '';
    $cart = $woocommerce->cart->cart_contents;
    $pickup_valid = get_option('wcmlim_pickup_valid');

    if (get_option('wcmlim_allow_local_pickup') == 'on' && get_option('wcmlim_allow_only_backend') != 'on') {
      // loop through the array looking for the tag you set. Switch to true if the tag is     found.
      $locAdd = array();  
      $pickupAdd = array(); 
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));

      foreach ($cart as $array_item) {
        $pid = ($array_item['variation_id'] != '0') ? $array_item['variation_id'] : $array_item['product_id'];
        $product_obj = wc_get_product( $pid );
        $product_obj_name = $product_obj->get_name();
        if (isset($array_item['select_location']['location_name'])) {
            foreach ($terms as $term) {
              if ($term->name == $array_item['select_location']['location_name']) {
                $term_id = $term->term_id;
                $term_name = $term->name;
                $streetNumber = get_term_meta($term_id, 'wcmlim_street_number', true);
                $route = get_term_meta($term_id, 'wcmlim_route', true);
                $locality = get_term_meta($term_id, 'wcmlim_locality', true);
                $state = get_term_meta($term_id, 'wcmlim_administrative_area_level_1', true);
                $postal_code = get_term_meta($term_id, 'wcmlim_postal_code', true);
                $country = get_term_meta($term_id, 'wcmlim_country', true);
                $email = get_term_meta($term_id, 'wcmlim_email', true);
                $phone= get_term_meta($term_id, 'wcmlim_phone', true);
                $pickup = get_term_meta($term_id, 'wcmlim_allow_pickup', true);
                $isClearCart = get_option('wcmlim_clear_cart');
                $new_address = '';

                if(!empty($streetNumber)){
                  $new_address .=  $streetNumber.',';
                }if(!empty($route)){
                  $new_address .=  $route.',';
                }
                if(!empty($locality)){
                  $new_address .=  $locality.',';
                }
                if(!empty($state)){
                  $new_address .=  $state.',';
                }
                if(!empty($postal_code)){
                  $new_address .=  $postal_code.',';
                }if(!empty($country)){
                  $new_address .=  $country;
                }
                if(!empty($email)){
                  $new_address .= ' '. "<br>"." <b>Email Address:</b> ". $email;
                }if(!empty($phone)){
                  $new_address .= ' '. "<br>"." <b>Phone No:</b> ". $phone;
                }

                $cart_message = "<b>Product ".$product_obj_name." </b><br /> <small>Pickup Address for $term_name: </small>" . $new_address. "<br/>";
                if (!empty($cart_message)) { ?>
                  <tr class="shipping-pickup-store ">
                    <td colspan="2">
                      <p class="message"><?= $cart_message ?></p>
                    </td>
                  </tr>
              <?php }
            }
          }
        }
      }
    } elseif (get_option('wcmlim_allow_only_backend') == 'on') { ?>
      <tr class="shipping-pickup-store">
        <th><strong><?php echo "Pickup Location"; ?></strong></th>
        <td>
          <select name="wcmlim_pickup" id="wcmlim_pickup" class="wcmlim_pickup" style="width: fit-content;">
          <option value="-1">-Select-</option>
            <?php
            // Loop over $cart items
            if (!empty(WC()->cart->get_cart())) {
              $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
              foreach ($terms as $key => $term) { ?>
                <option data-termid="<?php esc_html_e($term->term_id); ?>" value="<?php esc_html_e($term->name); ?>"><?php echo $term->name; ?></option>
              <?php }
            } ?>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <p class="local_pickup_address"></p>
        </td>
      </tr>
  <?php
    }
  }
}
add_action('woocommerce_review_order_after_shipping', 'wcmlim_location_row_layout');

/**
 ** Save the Location meta.
 **/
function wcmlim_location_save_order_meta($order_id)
{
  if (empty($order_id)) return;
  
  if(empty($_POST['wcmlim_pickup'])) return;
  $order = wc_get_order($order_id);

  $location = isset($_POST['wcmlim_pickup']) ? $_POST['wcmlim_pickup'] : '';

  $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
  foreach ($terms as $key => $term) {
    if ($term->name == $location) {
      foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();   
        $wcmlim_order_fulfil_edit = get_option('wcmlim_order_fulfil_edit');
        if ($wcmlim_order_fulfil_edit != "on") {  
          wc_add_order_item_meta($item_id, "Location", $term->name);
          wc_add_order_item_meta($item_id, "_selectedLocTermId", $term->term_id);
          wc_add_order_item_meta($item_id, "_selectedLocationKey", $key);
        }
      }
    }
  }
}
$fulfilment_rule = get_option("wcmlim_order_fulfilment_rules");
if($fulfilment_rule == "nearby_instock")
{
  $fulfilment_rule = "clcsadd";
}

add_action('woocommerce_checkout_update_order_meta', 'wcmlim_location_save_order_meta');
