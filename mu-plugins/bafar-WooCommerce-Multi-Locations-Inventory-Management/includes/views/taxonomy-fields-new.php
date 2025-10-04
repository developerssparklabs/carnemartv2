<?php 
$isLocationsGroup = get_option('wcmlim_enable_location_group');	
if( $isLocationsGroup == "on"){
?>
<div class="form-field form-required term-locator-wrap">
  <label class="" for="locator"><?php esc_html_e('Location Group', 'wcmlim'); ?></label>  
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
    'selected'          => 0,
  );
  wp_dropdown_categories($tax_args);
  ?>
</div>
<?php 
}
?>
<div class="form-field term-address-wrap">
  <label for="wcmlim_autocomplete_address"><?php esc_html_e('Enter Address', 'wcmlim'); ?></label>
  <input id="wcmlim_autocomplete_address" placeholder="Enter your address" type="text" class="form-control">
</div>
<div class="form-field term-streetNumber-wrap">
  <label class="" for="street_number"><?php esc_html_e('Street address', 'wcmlim'); ?></label>
  <input class="form-control" id="street_number" name="wcmlim_street_number" type="text" aria-required="true">
</div>
<div class="form-field term-route-wrap">
  <label class="" for="route"><?php esc_html_e('Route', 'wcmlim'); ?></label>
  <input class="form-control" id="route" name="wcmlim_route" type="text">
</div>
<div class="form-field term-city-wrap">
  <label class="" for="locality"><?php esc_html_e('City', 'wcmlim'); ?></label>
  <input class="form-control" id="locality" name="wcmlim_locality" type="text" aria-required="true">
</div>
<div class="form-field term-state-wrap">
  <label class="" for="administrative_area_level_1"><?php esc_html_e('State', 'wcmlim'); ?></label>
  <input class="form-control" id="administrative_area_level_1" name="wcmlim_administrative_area_level_1" type="text" aria-required="true">
</div>
<div class="form-field form-required term-postcode-wrap">
  <label class="" for="postal_code"><?php esc_html_e('Zip code', 'wcmlim'); ?></label>
  <input class="form-control" id="postal_code" name="wcmlim_postal_code" type="text" aria-required="true">
</div>
<div class="form-field form-required term-country-wrap">
  <label class="" for="country"><?php esc_html_e('Country', 'wcmlim'); ?></label>
  <input class="form-control" id="country" name="wcmlim_country" type="text" aria-required="true">
</div>
<div class="form-field term-email-wrap">
  <label class="" for="email"><?php esc_html_e('Email', 'wcmlim'); ?></label>
  <input class="form-control" id="email" name="wcmlim_email" type="text">
</div>
<div class="form-field term-phone-wrap">
  <label class="" for="wcmlim_phone_validation"><?php esc_html_e('Phone', 'wcmlim'); ?></label>
  <input class="form-control" id="wcmlim_phone_validation" name="wcmlim_phone" type="number" pattern="[0-9]{10}">
  <div class="phonevalmsg" id="phonevalmsg"></div>
</div>
<div class="form-field term-phone-wrap">
  <label class="" for="wcmlim_lat"><?php esc_html_e('Location Lat', 'wcmlim'); ?></label>
  <input class="form-control" id="wcmlim_lat" name="wcmlim_lat" type="text">
</div>
<?php
if (in_array('pos-ninja/pos-ninja', apply_filters('active_plugins', get_option('active_plugins'))))
{
  ?>
<div class="form-field term-phone-wrap">
  <label class="" for="stockuppposoutlets"><?php esc_html_e('StockUpp POS Outlet', 'wcmlim'); ?></label>
  <select name="wcmlim_stockupp_pos" id="wcmlim_stockupp_pos">
              <option value="">Select StockUpp POS Outlet</option>
              <?php 
              $stockupp_outlets = get_terms(array('taxonomy' => 'pos_outlets', 'hide_empty' => false, 'parent' => 0));
            foreach ($stockupp_outlets as $k => $stockupp_pos_outlets) {
              $stockupp_pos_id = $stockupp_pos_outlets->term_id;
              $all_terms_locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
            $stockupp_pos_outlet_mapped = 0;
              foreach($all_terms_locations as $loc_edit_screen_key=>$loc_edit_screen_term){
              $location_terms_id = $loc_edit_screen_term->term_id;
            $row_wcmlim_stockupp_pos = get_term_meta($location_terms_id, 'wcmlim_stockupp_pos', true);

              if($row_wcmlim_stockupp_pos == $stockupp_pos_id){
                $stockupp_pos_outlet_mapped = 1;
              }
            }
if($stockupp_pos_outlet_mapped != 1)
{
  ?>
  <option value="<?php echo $stockupp_pos_outlets->term_id; ?>" ><?php echo $stockupp_pos_outlets->name; ?></option>
<?php
}
            
            }

            ?>
          </select>
</div>
<?php
}
?>
<div class="form-field term-phone-wrap">
  <label class="" for="wcmlim_lng"><?php esc_html_e('Location Lng', 'wcmlim'); ?></label>
  <input class="form-control" id="wcmlim_lng" name="wcmlim_lng" type="text">
</div>

<?php
$wcmlim_enable_split_packages = get_option("wcmlim_enable_split_packages");
if ($wcmlim_enable_split_packages == 'on') {
?>
<div class="form-field term-location-measurement-unit-wrap">
  <label class="" for="wcmlim_dimension_unit"><?php esc_html_e('Location Measurement Unit', 'wcmlim'); ?></label>
  <select name="wcmlim_dimension_unit" id="wcmlim_dimension_unit">
        <option value="LBS_IN">LBS_IN</option>
        <option value="KG_CM">KG_CM</option>
  </select>
</div>
<?php  
}
?>
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
  <div class="form-field term-location-priority-wrap">
    <label class="" for="lcpriority"><?php esc_html_e('Location Priority', 'wcmlim'); ?></label>
    <input class="form-control noscroll" id="lcpriority" name="wcmlim_location_priority" type="number" min="1" pattern="[0-9]{10}">
  </div>
<?php }} ?>
<div class="form-field term-time-wrap">
  <label class="" for="time"><?php esc_html_e('Set your location time', 'wcmlim'); ?></label>
  <div class="per-18">Opens at -</div>
  <div class="per-40"> <input class="form-control" value="" id="start_time" name="wcmlim_start_time" type="time"> </div>
  <div class="per-18">Close at - </div>
  <div class="per-40"> <input class="form-control" value="" id="end_time" name="wcmlim_end_time" type="time"> </div>
</div>
<span class="alert-text" style="color: #e32!important;"></span>
<?php $esp = get_option("wcmlim_assign_location_shop_manager");
if ($esp == "on") { ?>
  <div class="form-field term-shopManager-wrap">
    <label class="" for="wcmlim_shop_manager"><?php esc_html_e('Location Shop Manager', 'wcmlim'); ?></label>
    <select multiple="multiple" class="multiselect" name="wcmlim_shop_manager[]" id="wcmlim_shop_manager">
      <?php
      $args = [
        'role' => 'location_shop_manager'
      ];
      $all_users = get_users($args);
      foreach ((array) $all_users as $key => $user) {
      ?>
        <option value="<?php esc_html_e($user->ID); ?>"><?php esc_html_e($user->display_name); ?></option>
      <?php } ?>
    </select>
  </div>
<?php } ?>
<?php
$isPaymentMethods = get_option('wcmlim_assign_payment_methods_to_locations');
if ($isPaymentMethods == "on") {
?>
  <div class="form-field term-paymentMethods-wrap">
    <label class="" for="wcmlim_payment_methods"><?php esc_html_e('Select Payment Methods', 'wcmlim'); ?></label>
    <select multiple="multiple" class="multiselect" name="wcmlim_payment_methods[]" id="wcmlim_payment_methods">
      <?php
      global $woocommerce;
      $gateways = $woocommerce->payment_gateways();
      foreach ($gateways as $gateway_single) {
        foreach ($gateway_single as $gateway) {
          if ($gateway->enabled == 'yes') {
      ?>
            <option value="<?php esc_html_e($gateway->id); ?>"><?php esc_html_e($gateway->title); ?></option>
      <?php
          }
        }
      } ?>
    </select>
  </div>
<?php }
$wcmlim_pos_compatiblity = get_option('wcmlim_pos_compatiblity');

if ($wcmlim_pos_compatiblity == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
?>
  <div class="form-field term-pos-wrap">
    <label class="" for="wcmlim_pos_compatiblity"><?php esc_html_e('Select OpenPOS Outlet', 'wcmlim'); ?></label>
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
  </div>
<?php }

$wc_pos_compatiblity = get_option('wcmlim_wc_pos_compatiblity');

if ($wc_pos_compatiblity == "on" && in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins')))) {
?>
  <div class="form-field term-wcpos-wrap">
    <label class="" for="wcmlim_wcpos_compatiblity"><?php esc_html_e('Select WC Point Of Sale Outlet', 'wcmlim'); ?></label>
    <select name="wcmlim_wcpos_compatiblity" id="wcmlim_wcpos_compatiblity">
    <option value=""><?php esc_html_e("Select outlet","wcmlim"); ?></option>
      <?php
      $args = array('post_type' => 'pos_outlet','numberposts' => -1,'post_status' => 'publish');
      $outlets = get_posts($args);
      foreach ((array) $outlets as $outlet) {
        $name = $outlet->post_title;
        $outid = $outlet->ID;
      ?>
        <option value="<?php esc_html_e($outid); ?>"><?php esc_html_e($name); ?></option>
      <?php } ?>
    </select>
  </div>
<?php }

$locationRadius = get_option( 'wcmlim_service_radius_for_location' );
if($locationRadius == "on"){
?>
<div class="form-field term-service-radius-wrap">
  <label class="" for="wcmlim_service_radius"><?php esc_html_e('Service Radius', 'wcmlim'); ?></label>
  <input class="form-control" id="wcmlim_service_radius" name="wcmlim_service_radius" type="number" pattern="[0-9]{10}">
</div>
<?php
}
$locationFee = get_option( 'wcmlim_location_fee' );
if($locationFee == "on"){
?>
<div class="form-field term-location-fee-wrap">
  <label class="" for="wcmlim_location_fee"><?php esc_html_e('Location Fee', 'wcmlim'); ?></label>
  <input class="form-control" id="wcmlim_location_fee" name="wcmlim_location_fee" type="number" pattern="[0-9]{10}">
</div>
<?php } ?>

