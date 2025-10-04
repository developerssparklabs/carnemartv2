<table class="form-table" role="presentation">
  <tbody>
  <?php 
  $isLocationsGroup = get_option('wcmlim_enable_location_group');	
  if( $isLocationsGroup == "on"){
  ?>
  <tr class="form-field term-locator-wrap">
      <th scope="row" valign="top">
        <label class="" for="locator"><?php esc_html_e('Location Group', 'wcmlim'); ?></label>
      </th>
      <td>
         <?php 
          //wp_dropdown_categories( 'show_count=1&hierarchical=1' ); 

          $tax_args = array(
            'taxonomy'     => 'location_group',
            'show_option_none'  => 'Select Group',
            'hide_empty'        => 0,
            'value_field'       => 'term_id',
            'name'              => 'wcmlim_locator',
            'id'                => 'locator',
            'class'             => 'form-control',
            'orderby'           => 'id',
            'selected'          => false,
          );
          wp_dropdown_categories($tax_args);  
          $term_locator = get_term_meta( $term->term_id , 'wcmlim_locator', true);
          ?>  
      </td>
    </tr>
    <script type="text/javascript"> 
    jQuery(document).ready(function(){
        var gLocation = <?php echo $term_locator;?>;  
        jQuery( "#locator" ).find( 'option[value="'+gLocation+'"]' ).attr('selected', 'selected');
        jQuery( "#select_location" ).on( "change", function (  )
          {  
          jQuery( "#locator" ).find( ":selected" ).removeAttr( "selected" );
          jQuery( "#locator" ).find( 'option[value="'+gLocation+'"]' ).attr('selected', 'selected');
          } 
        );
    });
    </script>
    <?php 
    }
    ?>
    <tr class="form-field term-address-wrap">
      <th scope="row" valign="top">
        <label for="wcmlim_autocomplete_address"><?php esc_html_e('Search Address', 'wcmlim'); ?></label>
      </th>
      <td>
        <input id="wcmlim_autocomplete_address" placeholder="Enter your address" type="text" class="form-control">
        <div class="wclimtip">?<span class="wclimtiptext">
        <pre>Google API must be valid.
        Field help to fetch register address details from google map.</pre>
        </span></div>
      </td>
    </tr>
    <tr class="form-field  term-streetNumber-wrap">
      <th scope="row" valign="top">
        <label class="" for="street_number"><?php esc_html_e('Street address', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="street_number" name="wcmlim_street_number" type="text" value="<?php esc_attr_e($streetNumber); ?>">
      </td>
    </tr>
    <tr class="form-field term-route-wrap">
      <th scope="row" valign="top">
        <label class="" for="route"><?php esc_html_e('Route', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="route" name="wcmlim_route" type="text" value="<?php esc_attr_e($route); ?>">
      </td>
    </tr>
    <tr class="form-field term-city-wrap">
      <th scope="row" valign="top">
        <label class="" for="locality"><?php esc_html_e('City', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="locality" name="wcmlim_locality" type="text" value="<?php esc_attr_e($locality); ?>">
      </td>
    </tr>
    <tr class="form-field term-state-wrap">
      <th scope="row" valign="top">
        <label class="" for="administrative_area_level_1"><?php esc_html_e('State', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="administrative_area_level_1" name="wcmlim_administrative_area_level_1" type="text" value="<?php esc_attr_e($state); ?>">
      </td>
    </tr>
    <tr class="form-field form-required term-postcode-wrap">
      <th scope="row" valign="top">
        <label class="" for="postal_code"><?php esc_html_e('Zip code', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="postal_code" name="wcmlim_postal_code" type="text" value="<?php esc_attr_e($postal_code); ?>">
      </td>
    </tr>
    <tr class="form-field form-required term-country-wrap">
      <th scope="row" valign="top">
        <label class="" for="country"><?php esc_html_e('Country', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="country" name="wcmlim_country" type="text" value="<?php esc_attr_e($country); ?>">
      </td>
    </tr>
    <?php
    $isShipping = get_option('wcmlim_enable_shipping_zones');
    if ($isShipping == "on") {
    ?>
      <tr class="form-field term-shippingZone-wrap">
        <th scope="row" valign="top"><label for="wcmlim_shipping_zone"><?php esc_html_e('Select Shipping Zones', 'wcmlim'); ?></label></th>
        <td>
          <select multiple="multiple" class="multiselect" name="wcmlim_shipping_zone[]" id="wcmlim_shipping_zone">
            <?php $shipping_zones = WC_Shipping_Zones::get_zones();

            foreach ((array) $shipping_zones as $key => $value) {
            ?>
              <option value="<?php echo $key; ?>" <?php if (!empty($wcmlim_shipping_zone)) {
                                                    if (in_array($key, $wcmlim_shipping_zone)) {
                                                      echo "selected='selected'";
                                                    }
                                                  }
                                                  ?>><?php echo $value['zone_name']; ?></option>
            <?php }

            ?>

          </select>
        </td>
      </tr>
    <?php }
    
    $isTaxLocation       = get_option('wcmlim_allow_tax_to_locations');
    if ($isTaxLocation == "on") {
      $all_tax_rates = [];
      $tax_classes = WC_Tax::get_tax_classes(); // Retrieve all tax classes.
      if ( !in_array( '', $tax_classes ) ) { // Make sure "Standard rate" (empty class name) is present.
          array_unshift( $tax_classes, '' );
      }
      foreach ( $tax_classes as $tax_class ) { // For each tax class, get all rates.
          $taxes = WC_Tax::get_rates_for_tax_class( $tax_class );
          $all_tax_rates = array_merge( $all_tax_rates, $taxes );
      }
      ?>
        <tr class="form-field term-taxLocations-wrap">
          <th scope="row" valign="top"><label for="wcmlim_taxLocations"><?php esc_html_e('Select WC Tax', 'wcmlim'); ?></label></th>
          <td>
            <select multiple="multiple" class="multiselect" name="wcmlim_tax_locations[]" id="wcmlim_tax_locations">
              <?php foreach($all_tax_rates as $tax_key => $tax_value)
              {
                ?>
                <option value="<?php echo $tax_value->tax_rate_id; ?>" <?php if (!empty($wcmlim_tax_location)) {
                  if (in_array($tax_value->tax_rate_id, $wcmlim_tax_location)) {
                    echo "selected='selected'";
                  }
                } ?>><?php echo $tax_value->tax_rate.' - '.$tax_value->tax_rate_name; ?></option>
                <?php
              }
              ?>
  
            </select>
          </td>
        </tr>
      <?php }

    ?>
    <tr class="form-field term-email-wrap">
      <th scope="row" valign="top">
        <label class="" for="email"><?php esc_html_e('Email', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="email" name="wcmlim_email" type="text" value="<?php esc_attr_e($email); ?>">
      </td>
    </tr>
    <tr class="form-field term-phone-wrap">
      <th scope="row" valign="top">
        <label class="" for="wcmlim_phone_validation"><?php esc_html_e('Phone', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="wcmlim_phone_validation" name="wcmlim_phone" type="number" pattern="[0-9]{10}" value="<?php esc_attr_e($phone); ?>">
        <div class="phonevalmsg" id="phonevalmsg"></div>
      </td>
    </tr>
    <?php

    $wcmlim_order_fulfil_edit = get_option('wcmlim_order_fulfil_edit');
    $wcmlim_allow_only_backend   = get_option('wcmlim_allow_only_backend');
    //checking backorder  is on or not
    if($wcmlim_allow_only_backend == 'on' ){
    $fulfilment_rule = get_option("wcmlim_order_fulfilment_rules");

    if($fulfilment_rule == "nearby_instock")
      {
        $fulfilment_rule = "clcsadd";
      }
    
    if (($fulfilment_rule == "locappriority") || ($fulfilment_rule == "clcsadd")) {

    ?>
      <tr class="form-field term-location-priority-wrap">
        <th scope="row" valign="top">
          <label class="" for="lcpriority"><?php esc_html_e('Location Priority', 'wcmlim'); ?></label>
        </th>
        <td>
          <input class="form-control noscroll" id="lcpriority" name="wcmlim_location_priority" type="number" min="1" pattern="[0-9]{10}" value="<?php esc_attr_e($locPriority); ?>">
        </td>
      </tr>
    <?php } }?>

    <?php $espe = get_option("wcmlim_assign_location_shop_manager");
    if ($espe == "on") { ?>
      <tr class="form-field term-shopManager-wrap">
        <th>
          <label class="" for="wcmlim_shop_manager"><?php esc_html_e('Location Shop Manager', 'wcmlim'); ?></label>
        </th>
        <td>
          <select multiple="multiple" class="multiselect" name="wcmlim_shop_manager[]" id="wcmlim_shop_manager">
            <?php
            $args = ['role' => 'location_shop_manager'];
            $all_users = get_users($args);
            foreach ((array) $all_users as $key => $user) {
            ?>
              <option value="<?php esc_html_e($user->ID); ?>" <?php if (!empty($lshopManager)) {
                                                                if (in_array($user->ID, $lshopManager)) {
                                                                  echo "selected='selected'";
                                                                }
                                                              }
                                                              ?>><?php esc_html_e($user->display_name); ?></option>
            <?php } ?>
          </select>
        </td>
      </tr>
    <?php } ?>
    <?php
    $isPaymentMethods = get_option('wcmlim_assign_payment_methods_to_locations');
    if ($isPaymentMethods == "on") { ?>
      <tr class="form-field term-paymentMethods-wrap">
        <th>
          <label class="" for="wcmlim_payment_methods"><?php esc_html_e('Select Payment Methods', 'wcmlim'); ?></label>
        </th>
        <td>
          <select multiple="multiple" class="multiselect" name="wcmlim_payment_methods[]" id="wcmlim_payment_methods">
            <?php
            global $woocommerce;
            $gateways = $woocommerce->payment_gateways();
            foreach ($gateways as $gateway_single) {
              foreach ($gateway_single as $gateway) {
                if ($gateway->enabled == 'yes') {
                  $wcmlim_payment_methods = get_term_meta($term->term_id, 'wcmlim_payment_methods', true);
            ?>
                  <option value="<?php esc_html_e($gateway->id); ?>" <?php if (!empty($wcmlim_payment_methods)) {
                                                                        if (in_array($gateway->id, $wcmlim_payment_methods)) {
                                                                          echo "selected='selected'";
                                                                        }
                                                                      }
                                                                    }

                                                                      ?>><?php esc_html_e($gateway->title); ?></option>
              <?php }
            } ?>
          </select>
        </td>
      </tr>
    <?php } ?>

    <?php
    $wcmlim_pos_compatiblity1 = get_option('wcmlim_pos_compatiblity');
    if ($wcmlim_pos_compatiblity1 == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
      <tr class="form-field term-pos-wrap">
        <th>
          <label class="" for="wcmlim_pos_compatiblity"><?php esc_html_e('Select OpenPOS Outlets', 'wcmlim'); ?></label>
        </th>
        <td>
          <select name="wcmlim_pos_compatiblity" id="wcmlim_pos_compatiblity">
             <option value="default"><?php esc_html_e('Select Outlet'); ?></option>
            <?php
            $wcmlim_locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
      
            $posts = get_posts([
              'post_type' => '_op_warehouse',
              'post_status' => array('publish'),
              'numberposts' => -1
            ]);

            foreach ((array) $posts as $p) 
            {
              $post = get_post($p->ID);
              $name = $post->post_title;
              $openposOutletMap = array();
                foreach ($wcmlim_locations as $key => $value) {
                  $term_id = $value->term_id;
                  $loc_outlet_id = get_term_meta($term_id, 'wcmlim_pos_compatiblity', true);
                  $openposOutletMap[] = $loc_outlet_id;
                }
                if($wcmlim_pos_compatiblity == $p->ID)
                {
                  ?>
                  <option value="<?php esc_html_e($p->ID); ?>" <?php
                                                        if ($p->ID == $wcmlim_pos_compatiblity) {
                                                          echo "selected='selected'";
                                                        }
                                                        ?>><?php esc_html_e($name); ?></option>
                <?php
                }
                if(!in_array($p->ID,$openposOutletMap))
                {
                  ?>
                  <option value="<?php esc_html_e($p->ID); ?>" <?php
                                                        if ($p->ID == $wcmlim_pos_compatiblity) {
                                                          echo "selected='selected'";
                                                        }
                                                        ?>><?php esc_html_e($name); ?></option>
                <?php
                }
            } ?>
          </select>
        </td>
      </tr>
    <?php }
    $wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');
    if ($wc_pos_compatiblity1 == "on" && in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins')))) { 
      $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
      
    ?>
    <tr class="form-field term-wcpos-wrap">
      <th>
        <label class="" for="wcmlim_wcpos_compatiblity"><?php esc_html_e('Select WC point of sale Outlets', 'wcmlim'); ?></label>
      </th>
      <td>
        <select name="wcmlim_wcpos_compatiblity" id="wcmlim_wcpos_compatiblity">
        <option value="default"><?php esc_html_e('Select Outlet'); ?></option>

          <?php
          $args = array('numberposts' => -1,'post_type'   => 'pos_outlet','post_status' => 'publish');
          $all_outlets = get_posts( $args );
          

          foreach ((array) $all_outlets as $key => $outlet) {
          
            $outlet_id = $outlet->ID;
            $outlet_name = $outlet->post_title;
            $tmpOutletMap = array();
            foreach ($locations as $key => $value) {
              $term_id = $value->term_id;
              $loc_outlet_id = get_term_meta($term_id, 'wcmlim_wcpos_compatiblity', true);
              $tmpOutletMap[] = $loc_outlet_id;
            }
            if($wcpos_selected_outlet == $outlet_id)
            {
              ?>
          <option value="<?php esc_html_e($outlet_id); ?>" <?php
                                                        if ($outlet_id == $wcpos_selected_outlet) {
                                                          echo "selected='selected'";
                                                        }
                                                        ?>><?php esc_html_e($outlet_name); ?></option>
        <?php
            }
            if(!in_array($outlet_id,$tmpOutletMap))
            {
              ?>
          <option value="<?php esc_html_e($outlet_id); ?>" <?php
                                                        if ($outlet_id == $wcpos_selected_outlet) {
                                                          echo "selected='selected'";
                                                        }
                                                        ?>><?php esc_html_e($outlet_name); ?></option>
        <?php

             
            }

            }


          
          ?>
        </select>
      </td>
    </tr>
  <?php 
  }

  $wcmlim_enable_split_packages = get_option("wcmlim_enable_split_packages");
  if ($wcmlim_enable_split_packages == 'on') {
    $wcmlim_dimension_unit = get_term_meta($term->term_id, 'wcmlim_dimension_unit', true);
?>
      <tr class="form-field term-measurement-unit-wrap">
        <th scope="row" valign="top"><label for="wcmlim_dimension_unit"><?php esc_html_e('Select Measurement Unit', 'wcmlim'); ?></label></th>
        <td>
          <select name="wcmlim_dimension_unit" id="wcmlim_dimension_unit">
            <option <?php echo ($wcmlim_dimension_unit == 'LBS_IN') ? 'selected': '';?> value="LBS_IN">LBS_IN</option>
            <option value="KG_CM" <?php echo ($wcmlim_dimension_unit == 'KG_CM') ? 'selected': '';?>>KG_CM</option>
        </select>
        </td>
      </tr>
<?php  
}
    $isShippingMethods = get_option('wcmlim_enable_shipping_methods');
    if ($isShippingMethods == "on") {
    ?>
      <tr class="form-field term-shippingMethod-wrap">
        <th scope="row" valign="top"><label for="wcmlim_shipping_method"><?php esc_html_e('Select Shipping Methods', 'wcmlim'); ?></label></th>
        <td>
          <select multiple="multiple" class="multiselect" name="wcmlim_shipping_method[]" id="wcmlim_shipping_method">
            <?php
            $wcmlim_payment_methods = get_term_meta($term->term_id, 'wcmlim_payment_methods', true);

            // Get all your existing shipping zones IDS
            $zone_ids = array_keys(array($wcmlim_payment_methods) + WC_Shipping_Zones::get_zones());

            // Loop through shipping Zones IDs
            foreach ($zone_ids as $zone_id) {
              // Get the shipping Zone object
              $shipping_zone = new WC_Shipping_Zone($zone_id);

              // Get all shipping method values for the shipping zone
              $shipping_methods = $shipping_zone->get_shipping_methods(true, 'values');

              // Loop through each shipping methods set for the current shipping zone
              foreach ($shipping_methods as $instance_id => $shipping_method) {
                // The dump of protected data from the current shipping method
            ?>
                <option value="<?php echo $instance_id; ?>" <?php if (!empty($wcmlim_shipping_method)) {
                                                              if (in_array($instance_id, $wcmlim_shipping_method)) {
                                                                echo "selected='selected'";
                                                              }
                                                            }
                                                            ?>><?php echo $shipping_method->title; ?></option>
            <?php }
            }

            ?>

          </select>
        </td>
      </tr>
    <?php }

    ?>
    <tr class="form-field term-time-wrap">
      <th scope="row" valign="top">
        <label class="" for="Time"><?php esc_html_e('Set your location time', 'wcmlim'); ?></label>
      </th>
      <td>
        Opens at - <input class="form-control w-15" id="start_time" name="wcmlim_start_time" type="time" value="<?php esc_attr_e($start_time); ?>">
        Close at - <input class="form-control w-15" id="end_time" name="wcmlim_end_time" type="time" value="<?php esc_attr_e($end_time); ?>">
      </td>
    </tr>

    <tr class="form-field term-latlng-wrap">
      <th scope="row" valign="top">
        <label class="" for="Latlng"><?php esc_html_e('Location Lat/Lng', 'wcmlim'); ?></label>
      </th>
      <td>
        Lat - <input class="form-control w-15" id="wcmlim_lat" name="wcmlim_lat" type="text" value="<?php esc_attr_e($wcmlim_lat); ?>">
        Lng - <input class="form-control w-15" id="wcmlim_lng" name="wcmlim_lng" type="text" value="<?php esc_attr_e($wcmlim_lng); ?>">
      </td>
    </tr>
    <?php
        if (in_array('pos-ninja/pos-ninja.php', apply_filters('active_plugins', get_option('active_plugins'))))
       {
         ?>
           <tr class="form-field term-outlet-mapping-wrap">
            <th scope="row" valign="top">
                <label class="" for="stockuppposoutlets"><?php esc_html_e('StockUpp POS Outlet', 'wcmlim'); ?></label>
              </th>
              <td>
              <select name="wcmlim_stockupp_pos" id="wcmlim_stockupp_pos">
              <option value="">Select StockUpp POS Outlet</option>
            <?php
            $wcmlim_stockupp_pos = get_term_meta($term->term_id, 'wcmlim_stockupp_pos', true);
            $stockupp_outlets = get_terms(array('taxonomy' => 'pos_outlets', 'hide_empty' => false, 'parent' => 0));
            foreach ($stockupp_outlets as $k => $stockupp_pos_outlets) {
              $stockupp_pos_id = $stockupp_pos_outlets->term_id;
              $all_terms_locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
            $stockupp_pos_outlet_mapped = 0;
              foreach($all_terms_locations as $loc_edit_screen_key=>$loc_edit_screen_term){
              $location_terms_id = $loc_edit_screen_term->term_id;
            $row_wcmlim_stockupp_pos = get_term_meta($location_terms_id, 'wcmlim_stockupp_pos', true);

              if(($row_wcmlim_stockupp_pos == $stockupp_pos_id)){
                $stockupp_pos_outlet_mapped = 1;
              }
              if(!empty($wcmlim_stockupp_pos))
              {
                $stockupp_pos_outlet_mapped = 0;
              }
            }
if($stockupp_pos_outlet_mapped != 1)
{
  ?>
  <option value="<?php echo $stockupp_pos_outlets->term_id; ?>" <?php if (!empty($wcmlim_stockupp_pos)) {
                                                if ($stockupp_pos_outlets->term_id==$wcmlim_stockupp_pos) {
                                                  echo "selected='selected'";
                                                }
                                              }
                                              ?>><?php echo $stockupp_pos_outlets->name; ?></option>
<?php
}
            
            }

            ?>

          </select>
              </td>
            </tr>
         <?php
         }
         ?>

    <?php
    
     if (in_array('local-pickup-for-woocommerce/local-pickup.php', apply_filters('active_plugins', get_option('active_plugins'))) ||  is_array(get_site_option('active_sitewide_plugins')) && !array_key_exists('local-pickup-for-woocommerce/local-pickup.php', get_site_option('active_sitewide_plugins'))) 
    {
      update_option('wcmlim_allow_local_pickup','');
    }
    $allow_local_pickup = get_option('wcmlim_allow_local_pickup');
    // if ($allow_local_pickup == "on") { below code removed/deactivated for local pickup setting
      if (false) {
    ?>
      <tr class="form-field term-pickup">
        <th scope="row" valign="top">
          <label class="" for="allow_pickup"><?php esc_html_e('Enable Pickup For Location', 'wcmlim'); ?></label>
        </th>
        <td>
          <label class="switch">
            <input type="checkbox" id="allow_pickup" name="wcmlim_allow_pickup" <?php echo ($allow_pickup == 'on') ? 'checked="checked"' : ''; ?>>
            <span class="slider round"></span>
          </label>
        </td>
      </tr>
    <?php }

    $locationRadius = get_option( 'wcmlim_service_radius_for_location' );
    if($locationRadius == "on"){
    ?>
    <tr class="form-field term-service-radius-wrap">
      <th scope="row" valign="top">
        <label class="" for="wcmlim_service_radius"><?php esc_html_e('Service Radius', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="wcmlim_service_radius" name="wcmlim_service_radius" type="number" value="<?php echo $location_radius;?>" pattern="[0-9]{10}">
      </td>
    </tr>
    <?php } 
    $locationFee = get_option( 'wcmlim_location_fee' );
    if($locationFee == "on"){
    ?>
    
    <tr class="form-field term-location-fee-wrap">
      <th scope="row" valign="top">
        <label class="" for="wcmlim_location_fee"><?php esc_html_e('Location Fee', 'wcmlim'); ?></label>
      </th>
      <td>
        <input class="form-control" id="wcmlim_location_fee" name="wcmlim_location_fee" type="number" value="<?php echo $location_fee;?>" min="0" step="any">
      </td>
    </tr>
    <?php } ?>
  </tbody>
</table>