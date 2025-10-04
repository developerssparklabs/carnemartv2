<?php

/**
 * Custom Iventory Fields of the plugin like Stock Quantity, Purchase Price , Regular & Sale Price.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.2
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 */

/**
 * Custom Iventory Fields of the plugin like Stock Quantity, Purchase Price , Regular & Sale Price.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Custom_Inventory_Fields
{
  public function __construct()
  {
    add_action('woocommerce_product_options_inventory_product_data',  array($this, 'wcmlim_custom_inventory_fields'));
    add_action('woocommerce_process_product_meta',  array($this, 'wcmlim_save_custom_inventory_fields'));
    add_action('woocommerce_variation_options_inventory',  array($this, 'wcmlim_variation_settings_fields'), 10, 3);
    add_action('woocommerce_save_product_variation',  array($this, 'wcmlim_save_variation_settings_fields'), 10, 2);
    add_action('woocommerce_product_options_general_product_data',  array($this, 'wcmlim_add_custom_fields_product'));
    add_action('woocommerce_process_product_meta',  array($this, 'wcmlim_add_custom_general_fields_save'));
    add_action('woocommerce_product_after_variable_attributes',  array($this, 'wcmlim_price_variation_settings_fields'), 10, 3);
    add_action('woocommerce_save_product_variation',  array($this, 'wcmlim_price_save_variation_settings_fields'), 10, 2);
 
  }
  /**
   * Register the stock location field inside inventory simple product.
   * Stock Location Field inside the Products(Simple)
   * @since    1.0.0
   */




  public function wcmlim_custom_inventory_fields()
  { 
    $b4L = 'on';
    $specific_location = 'on';

    // Get the product ID
    $product_id = get_the_ID();
    $product = wc_get_product($product_id );
    $product_type = $product->get_type();
    $backendonlymode = get_option('wcmlim_allow_only_backend');
    $specific_location = 'on';
    $stockmanagment = get_post_meta($product_id, '_manage_stock', true);

    if($product_type == "simple"){ 
    
  

    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $defaultLocation = get_option("wcmlim_enable_default_location");
     echo '<div id="locationList" class="options_group">';
   if ($defaultLocation == "on" && $backendonlymode != "on" && $stockmanagment == "yes") {
      $value = get_post_meta($product_id, 'wcmlim_default_location', true);
      if (empty($value)) $value = '';
      $options[''] = __('Select location', 'wcmlim');
      foreach ($terms as $key => $term) {
        $options["loc_{$term->term_id}"] = $term->name;
      }   
      echo '<div class="options_group">';
      woocommerce_wp_select(array(
        'id'      => '_select_default_location',
        'label'   => __('Default location', 'woocommerce'),
        'options' =>  $options, //this is where I am having trouble
        'value'   => $value,
      ));

      echo '</div>';
    }
    if ($product_type == 'simple') {
    echo '<p><b>' . __('All Locations', 'wcmlim') . '</b><span class="description"> (Please enable location from location widget to enter stock)</span></p>';
    }
    foreach ($terms as $term) {
      if(!function_exists('wp_get_current_user')) {
        include(ABSPATH . "wp-includes/pluggable.php"); 
      }
      $cuser = wp_get_current_user();
      $cuserId = $cuser->ID;
      $cuserRoles = $cuser->roles;
      $shopM =  get_term_meta($term->term_id, 'wcmlim_shop_manager', true);
      $regM = get_term_meta($term->term_id, "wcmlim_locator", true);
      $regM2 = get_term_meta($regM, "wcmlim_shop_regmanager", true);
        
      if (in_array("location_shop_manager", $cuserRoles) && !empty($shopM) && in_array($cuserId, $shopM)) {

        echo '<div class="locationInner">';

      } elseif (in_array("location_regional_manager", $cuserRoles) && !empty($regM2) && in_array($cuserId, $regM2)) { 

        echo '<div class="locationInner">';

      } elseif (current_user_can('administrator')) {

        echo "<div class='locationInner' id='locationID_$term->term_id' value='$term->term_id'>";

      }else {

        echo '<div class="locationInner notManager">';
      }
      $enable_price = 'on';
      $backorder_option = 'on';
        echo '<h2>Location : ' . $term->name . '</h2><div class="salep_regp_stockc"><div class="row show_all_options"><div class="col-md-3">';
       $stock = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
       $stock = trim($stock); // Elimina espacios en blanco al inicio y final

      woocommerce_wp_text_input(array(
          'id'            => 'wcmlim_product_' . $product_id . '_location_id_' . $term->term_id,
          'label'         => __('Stock quantity', 'wcmlim'),
          'placeholder'   => __('', 'wcmlim'),
          'description'   => __('Enter the stock amount for this location.', 'wcmlim'),
          'desc_tip'      => true,
          'class'         => 'woocommerce scroll stock_qty_single',
          'type'          => 'number',
          'value'         => $stock,
          'custom_attributes' => array(
            'step' => 'any',
            'min' => "0",
            'loc_stock_qty_attributes' => 'loc_stock',
          )
        ));
        echo '</div>';
      if ($backendonlymode == "on") {
        
      }else{
      
      global $post;
      $post_id = $post->ID;
      $_product = wc_get_product($post_id);
      $productType = $_product->is_type('simple');
      $enable_price = 'on';
        if(!function_exists('wp_get_current_user')) {
          include(ABSPATH . "wp-includes/pluggable.php"); 
        }
        echo '<div class="col-md-3">';
        woocommerce_wp_text_input(
          array(
            'id' => "wcmlim_product_{$post_id}_regular_price_at_{$term->term_id}",
            'label' => __(
              'Regular price (' . get_woocommerce_currency_symbol() . ')',
              'wcmlim'
            ),
            'placeholder' => __('', 'wcmlim'),
            'desc_tip' => 'false',
            'description' => __('The amount of credits for this product in currency format.', 'wcmlim'),
            'type' => 'text',
            'class' => 'woocommerce wcmlim_product_regular_price',
            'attr' => "data-id=$term->term_id",
            'value' => get_post_meta($post_id, "wcmlim_regular_price_at_{$term->term_id}", true),
            'custom_attributes' => array(
              'loc-id' 	=> $term->term_id,
              'pro-id' 	=> $post_id

            ) 
          )
        );
        echo '</div>';
        echo '<div class="col-md-3">';
        woocommerce_wp_text_input(
          array(
            'id' => "wcmlim_product_{$post_id}_sale_price_at_{$term->term_id}",
            'label' => __(
              'Sale price (' . get_woocommerce_currency_symbol() . ')',
              'wcmlim'
            ),
            'placeholder' => __('', 'wcmlim'),
            'desc_tip' => 'false',
            'description' => __('The amount of credits for this product in currency format.', 'wcmlim'),
            'type' => 'text',
            'class' => 'woocommerce wcmlim_product_sale_price',
            'attr' => "data-id=$term->term_id",
            'value' => get_post_meta($post_id, "wcmlim_sale_price_at_{$term->term_id}", true),
            'custom_attributes' => array(
              'loc-id' 	=> $term->term_id,
              'pro-id' 	=> $post_id
            )

          )
        );
        echo '</div>';
        echo '<div class="col-md-3">';
          woocommerce_wp_text_input(
            array(
              'id' => "wcmlim_product_{$post_id}_cogs_at_{$term->term_id}",
              'label' => __(
                'Purchase price (' . get_woocommerce_currency_symbol() . ')',
                'wcmlim'
              ),
              'placeholder' => __('', 'wcmlim'),
              'desc_tip' => 'false',
              'description' => __('The amount of credits for this product in currency format.', 'wcmlim'),
              'type' => 'text',
              'class' => 'woocommerce wcmlim_cogs_at',
              'value' => get_post_meta($post_id, "wcmlim_cogs_at_{$term->term_id}", true)
            )
          );
      echo '</div>';
}     echo '</div><div class="row show_allow_options">';
      echo '<div class="col-md-3">';
        woocommerce_wp_select(array(
          'id'      => 'wcmlim_allow_backorder_'. $product_id . '_location_id_' . $term->term_id,
          'label'   => __('Allow Backorder', 'wcmlim'),
          'class' => 'woocommerce wcmlim_allow_backorder',
          'options'     => array(
            'No'    => __('Don`t Allow', 'woocommerce' ),
            'Yes' => __('Allow', 'woocommerce' ),
          ),
          'value'   => get_post_meta($product_id, 'wcmlim_allow_backorder_at_' . $term->term_id, true),
        ));
      echo '</div>';
      echo '<div class="col-md-3">';
        woocommerce_wp_select(array(
          'id'      => 'wcmlim_allow_specific_location_'. $product_id . '_location_id_' . $term->term_id,
          'label'   => __('Allow Specific Location?', 'wcmlim'),
          'class' => 'woocommerce wcmlim_allow_specific_location',
          'options'     => array(
            'Yes' => __('Yes', 'woocommerce' ),
            'No'  => __('No', 'woocommerce' ),
          ),
          'value'   => get_post_meta($product_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, true),
        ));   
      echo '</div>
      <div class="col-md-3"><p style="visibility:hidden;">empty column</p> </div>
      <div class="col-md-3"><p style="visibility:hidden;">empty column</p> </div>
      </div>';
      $isParent = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
      if (!empty($isParent)) {
        echo '<ol>';
        foreach ($isParent as $parentTerm) {
          echo '<li>';
          echo '<h2>Sub Location : ' . $parentTerm->name . '</h2>';
          woocommerce_wp_text_input(array(
            'id'            => "wcmlim_product_{$product_id}_location_id_{$parentTerm->term_id}",
            'label'         => __('Stock quantity', 'wcmlim'),
            'placeholder'   => __('Input stock', 'wcmlim'),
            'description'   => __('Enter the stock amount for this location.', 'wcmlim'),
            'desc_tip'      => true,
            'class'         => 'woocommerce',
            'type'          => 'number',
        
            'value'         => get_post_meta($product_id, "wcmlim_stock_at_{$parentTerm->term_id}", true),
            'custom_attributes' => array(
              'step' => 'any',
              'min' => "0",
            )
          ));
          echo '</li>';
        }
        echo '</ol>';
      }
      echo '</div></div>';
    }
    echo '</div>';

    foreach($terms as $key=>$term){
      $draftStock = get_post_meta($product_id, 'wcmlim_draft_stock_at_' . $term->term_id, true);
      ?>
        <input type="hidden" id="wcmlim_draft_stock_at_<?php echo $term->term_id; ?>" value="<?php echo $draftStock; ?>"/>
      <?php
    }
  }
}
  /**
   * Save the stock location field inside inventory simple product.
   * Save the Stock Location Inventory(Simple)
   * @since    1.0.0
   */

  public function wcmlim_save_custom_inventory_fields($post_id)
  {
    $product = wc_get_product($post_id);
    $locations_check = $_POST['tax_input']['locations'];
    //set tax_input[locations] to product
    
    if (defined('DOING_AJAX') && DOING_AJAX)
      return $post_id;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return $post_id;

    if (!current_user_can('edit_product', $post_id))
      return $post_id;

    // Get product object
    $product = wc_get_product($post_id);
    $product_type = $product->get_type();
    
    if (empty($product)) return;

    if( $product->is_type( 'simple' ) )
  {
    $defaultLocation = get_option("wcmlim_enable_default_location");
    if ($defaultLocation == "on") {
      //  save the default location
      $wcmlim_select_location = $_POST['_select_default_location'];
      if (!empty($wcmlim_select_location)){
        update_post_meta($post_id, 'wcmlim_default_location', esc_attr($wcmlim_select_location));
      }else {
        update_post_meta($post_id, 'wcmlim_default_location',  '');
      }
    }

    // Grab stock amount from all terms
    $product_terms_stock = array();

    // Grab input amounts
    $input_amounts = array();

    // Define counter
    $counter = 0;
    $categories = [];

    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $terms_total = count($terms);
    $b4L = 'on';
    $specific_location = 'on';

    foreach ($terms as $term) {
      
      if (isset($_POST['wcmlim_product_' . $post_id . '_location_id_' . $term->term_id])) {

        if (isset($_POST['wcmlim_allow_backorder_'. $post_id . '_location_id_' . $term->term_id])) {
          $wcmlim_allow_backorder = sanitize_text_field($_POST['wcmlim_allow_backorder_'. $post_id . '_location_id_' . $term->term_id]);
          $allow_backorder_post_mets = get_post_meta($post_id, 'wcmlim_allow_backorder_at_' . $term->term_id, true);
          if($allow_backorder_post_mets !=  $wcmlim_allow_backorder ){
           update_post_meta($post_id, 'wcmlim_allow_backorder_at_' . $term->term_id, $wcmlim_allow_backorder);
          }
        }
        if (isset($_POST['wcmlim_allow_specific_location_'. $post_id . '_location_id_' . $term->term_id])) {
          $wcmlim_allow_backorder = sanitize_text_field($_POST['wcmlim_allow_specific_location_'. $post_id . '_location_id_' . $term->term_id]);
          $allow_backorder_post_mets = get_post_meta($post_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, true);
          if($allow_backorder_post_mets !=  $wcmlim_allow_backorder ){
           update_post_meta($post_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, $wcmlim_allow_backorder);
          }
        }
  
        $counter++;
        if(($_POST['wcmlim_product_' . $post_id . '_location_id_' . $term->term_id] != 0) && ($_POST['wcmlim_product_' . $post_id . '_location_id_' . $term->term_id] != ''))
        {
          $categories[] = $term->term_id;
        }
        // Save input amounts to array
        $input_amounts[] = sanitize_text_field($_POST['wcmlim_product_' . $post_id . '_location_id_' . $term->term_id]);

        // Get post meta
        $postmeta_stock_at_term = get_post_meta($post_id, 'wcmlim_stock_at_' . $term->term_id, true);

        // Pass terms stock to variable
        if ($postmeta_stock_at_term) {
          $product_terms_stock[] = $postmeta_stock_at_term;
        }

        $stock_location_term_input = sanitize_text_field($_POST['wcmlim_product_' . $post_id . '_location_id_' . $term->term_id]);
        
        if($stock_location_term_input != ''){
          update_post_meta($post_id, 'wcmlim_draft_stock_at_' . $term->term_id, $stock_location_term_input);
        }
      
        // Check if the $_POST value is the same as the postmeta, if not update the postmeta
        if ($stock_location_term_input !== $postmeta_stock_at_term) {
          // Update the post meta
          update_post_meta($post_id, 'wcmlim_stock_at_' . $term->term_id, $stock_location_term_input);
        }

        // Update stock when reach the last term
        if ($counter === $terms_total) {
            update_post_meta($post_id, '_stock', array_sum($input_amounts));
        }

        $Pterms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
        $Pterms_total = count($Pterms);
        if (!empty($Pterms)) {
          // Grab stock amount from all terms
          $pp_terms_stock = array();

          // Grab input amounts
          $pinput_amounts = array();

          // Define counter
          $pcounter = 0;

          foreach ($Pterms as $pt) {
            if (isset($_POST['wcmlim_product_' . $post_id . '_location_id_' . $pt->term_id])) {

              // Initiate counter
              $pcounter++;

              $categories[] = $pt->term_id;

              // Save input amounts to array
              $pinput_amounts[] = sanitize_text_field($_POST['wcmlim_product_' . $post_id . '_location_id_' . $pt->term_id]);

              // Get post meta
              $stock_at_pterm = get_post_meta($post_id, 'wcmlim_stock_at_' . $pt->term_id, true);
          
              // Pass terms stock to variable
              if ($stock_at_pterm) {
                $pp_terms_stock[] = $stock_at_pterm;
              }

              $parent_location_term_input = sanitize_text_field($_POST['wcmlim_product_' . $post_id . '_location_id_' . $pt->term_id]);

              // Check if the $_POST value is the same as the postmeta, if not update the postmeta
              if ($parent_location_term_input !== $stock_at_pterm) {

                // Update the post meta
                update_post_meta($post_id, 'wcmlim_stock_at_' . $pt->term_id, $parent_location_term_input);
              }

              // Update stock when reach the last term
              if ($pcounter === $Pterms_total) {
                if (update_post_meta($post_id, 'wcmlim_stock_at_' . $term->term_id, array_sum($pinput_amounts))) {
                  $ParentStock[] = get_post_meta($post_id, "wcmlim_stock_at_{$term->term_id}", true);
                  if(intval(array_sum($ParentStock)) > 0)
                  {
                    update_post_meta($post_id, '_stock', array_sum($ParentStock));
                  }
                }
              }
            }
          }
        }
        
        $_parentStock[] = get_post_meta($post_id, "wcmlim_stock_at_{$term->term_id}", true);
        
        if(intval(array_sum($_parentStock)) > 0)
        {
          update_post_meta($post_id, '_stock', array_sum($_parentStock));
        }
        if ($_parentStock) {
          $product_terms_stock[] = $_parentStock;
        }
      }
    }
   // wp_set_post_terms($post_id, $categories, 'locations');

    $product_terms_stock = array_sum($product_terms_stock);
    $stock_qty = array_sum($input_amounts);
    // Check if stock in terms exist
    if (!empty($product_terms_stock)) {
      // update stock status

      $product = wc_get_product($post_id);
      if (empty($product)) return;

      // backorder disabled
      if (!$product->is_on_backorder()) {
        if ($stock_qty > 0) {
          update_post_meta($post_id, '_stock_status', 'instock');

          // remove the link in outofstock taxonomy for the current product
          wp_remove_object_terms($post_id, 'outofstock', 'product_visibility');
        } else {
          update_post_meta($post_id, '_stock_status', 'outofstock');

          // add the link in outofstock taxonomy for the current product
          wp_set_post_terms($post_id, 'outofstock', 'product_visibility', true);
        }

        // backorder enabled
      } else {
        $current_stock_status = get_post_meta($post_id, '_stock_status', true);
        if ($stock_qty > 0 && $current_stock_status != 'instock') {
          update_post_meta($post_id, '_stock_status', 'instock');
          // remove the link in outofstock taxonomy for the current product
          wp_remove_object_terms($post_id, 'outofstock', 'product_visibility');
        } else {
          update_post_meta($post_id, '_stock_status', 'onbackorder');
        }
      }
    }
  }
  if( $product->is_type( 'variable' ) )
  {
    // wp_set_post_terms($post_id, $categories, 'locations');

    $variations = $product->get_available_variations();
    $variations_ids = wp_list_pluck( $variations, 'variation_id' );
    $globalCategories = [];
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $terms_total = count($terms);

    foreach($variations_ids as $variations_id)
    {
      $var_post_id = $variations_id;
      $manage_stock = get_post_meta($var_post_id, '_manage_stock', true);
      if (!$manage_stock) {
        return;
      }
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    
    
    // Grab stock amount from all terms
    $product_terms_stock = array();

    // Grab input amounts
    $input_amounts = array();

    // Define counter
    $counter = 0;
    $categories = [];
    foreach ($terms as $term) {
      if (isset($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $term->term_id])) {
        $cogs_variation_price = $_POST['wcmlim_variation_'.$var_post_id.'_cogs_at_'.$term->term_id.'_field'];
         
        update_post_meta($var_post_id, 'wcmlim_cogs_at_' . $term->term_id, $cogs_variation_price);
        // Initiate counter
        $counter++;
        if(($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $term->term_id] != 0) && ($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $term->term_id] != ''))
        {
          $categories[] = $term->term_id;
        }
        // Save input amounts to array
        $input_amounts[] = sanitize_text_field($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $term->term_id]);

        // Get post meta
        $postmeta_stock_at_term = get_post_meta($var_post_id, 'wcmlim_stock_at_' . $term->term_id, true);

        // Pass terms stock to variable
        if ($postmeta_stock_at_term) {
          $product_terms_stock[] = $postmeta_stock_at_term;
        }

        $stock_location_term_input = sanitize_text_field($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $term->term_id]);

        if($stock_location_term_input != ''){
          update_post_meta($var_post_id, 'wcmlim_draft_stock_at_' . $term->term_id, $stock_location_term_input);
        }

        // Check if the $_POST value is the same as the postmeta, if not update the postmeta
        if ($stock_location_term_input !== $postmeta_stock_at_term) {
          // Update the post meta
          update_post_meta($var_post_id, 'wcmlim_stock_at_' . $term->term_id, $stock_location_term_input);
        }

        // Update stock when reach the last term
        if ($counter === $terms_total) {
          if(intval($input_amounts) > 0)
          {
            update_post_meta($var_post_id, '_stock', array_sum($input_amounts));
          }
        }
        

        $Pterms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
        $Pterms_total = count($Pterms);
        if (!empty($Pterms)) {
          // Grab stock amount from all terms
          $pp_terms_stock = array();

          // Grab input amounts
          $pinput_amounts = array();

          // Define counter
          $pcounter = 0;

          foreach ($Pterms as $pt) {
        
            if (isset($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $pt->term_id])) {

              // Initiate counter
              $pcounter++;

              $categories[] = $pt->term_id;

              // Save input amounts to array
              $pinput_amounts[] = sanitize_text_field($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $pt->term_id]);

              // Get post meta
              $stock_at_pterm = get_post_meta($var_post_id, 'wcmlim_stock_at_' . $pt->term_id, true);

              // Pass terms stock to variable
              if ($stock_at_pterm) {
                $pp_terms_stock[] = $stock_at_pterm;
              }

              $parent_location_term_input = sanitize_text_field($_POST['wcmlim_variation_' . $var_post_id . '_location_id_' . $pt->term_id]);

              // Check if the $_POST value is the same as the postmeta, if not update the postmeta
              if ($parent_location_term_input !== $stock_at_pterm) {

                // Update the post meta
                update_post_meta($var_post_id, 'wcmlim_stock_at_' . $pt->term_id, $parent_location_term_input);
              }
              // Update stock when reach the last term
              if ($pcounter === $Pterms_total) {
                if (update_post_meta($var_post_id, 'wcmlim_stock_at_' . $term->term_id, array_sum($pinput_amounts))) {
                  $ParentStock[] = get_post_meta($var_post_id, "wcmlim_stock_at_{$term->term_id}", true);
                  if(intval(array_sum($ParentStock)) > 0)
                  {
                    update_post_meta($var_post_id, '_stock', array_sum($ParentStock));
                  }
                }
              }
            }
          }
        }
        if ($_parentStock) {
          $product_terms_stock[] = $_parentStock;
        }
      }
    }
    $globalCategories[] = $categories;

    $product_terms_stock = array_sum($product_terms_stock);
    $stock_qty = array_sum($input_amounts);
    // Check if stock in terms exist
    if (!empty($product_terms_stock)) {
      // update stock status

      $product = wc_get_product($var_post_id);
    
      if (empty($product)) return;

      // backorder disabled
      if (!$product->is_on_backorder()) {
        if ($stock_qty > 0) {
          update_post_meta($var_post_id, '_stock_status', 'instock');

          // remove the link in outofstock taxonomy for the current product
          wp_remove_object_terms($var_post_id, 'outofstock', 'product_visibility');
        } else {
          update_post_meta($var_post_id, '_stock_status', 'outofstock');

          // add the link in outofstock taxonomy for the current product
          wp_set_post_terms($var_post_id, 'outofstock', 'product_visibility', true);
        }

        // backorder enabled
      } else {
        $current_stock_status = get_post_meta($var_post_id, '_stock_status', true);
        if ($stock_qty > 0 && $current_stock_status != 'instock') {
          update_post_meta($var_post_id, '_stock_status', 'instock');
          // remove the link in outofstock taxonomy for the current product
          wp_remove_object_terms($var_post_id, 'outofstock', 'product_visibility');
        } else {
          update_post_meta($var_post_id, '_stock_status', 'onbackorder');
        }
      }
    }
    }
    
    //taxonomy count not working for variations so we have to fix it like this... First we took all arrays of termids of variations then merge the array, and find unique values from it. Then we set the terms to the product.
    $flattenedArray = array_merge(...$globalCategories);
    $uniqueArray = array_unique($flattenedArray);
    $uniqueArray = array_values($uniqueArray);
    // wp_set_post_terms($post_id, $uniqueArray, 'locations');

  }    
  }

  /**
   * Register the stock location field inside inventory variable product.
   * Stock Location Field inside the Products(Variations)
   * @since    1.0.0
   */

  public function wcmlim_variation_settings_fields($loop, $variation_data, $variation)
  {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $b4L = 'on';
    $specific_location = 'on';
    $defaultLocation = get_option("wcmlim_enable_default_location");
    if ($defaultLocation == "on") {
      // Get the selected value  <== <== (updated)
      $value = get_post_meta($variation->ID, "wcmlim_default_location", true);

      if (empty($value)) $value = '';

      $options[''] = __('Select location', 'wcmlim'); // default value

      foreach ($terms as $key => $term) {
        $options["loc_{$term->term_id}"] = $term->name;
      }

      woocommerce_wp_select(array(
        'id'      => "_select_default_location_{$variation->ID}",
        'label'   => __('Default location', 'woocommerce'),
        'options' =>  $options, 
        'value'   => $value,
      ));
    }

    echo '<hr class="variable_product_line"><div id="locationVariationsList">';
    echo '<p><b>' . __('All Locations', 'wcmlim') . '</b></p>';
    foreach ($terms as $term) {
      if(!function_exists('wp_get_current_user')) {
        include(ABSPATH . "wp-includes/pluggable.php"); 
      }
      $cuser = wp_get_current_user();
      $cuserId = $cuser->ID;
      $cuserRoles = $cuser->roles;
      $shopM =  get_term_meta($term->term_id, 'wcmlim_shop_manager', true);
      $regM = get_term_meta($term->term_id, "wcmlim_locator", true);
      $regM2 = get_term_meta($regM, "wcmlim_shop_regmanager", true);  
      if (in_array("location_shop_manager", $cuserRoles) && !empty($shopM) && in_array($cuserId, $shopM)) {
        echo '<div class="locationInner">';
      } elseif (in_array("location_regional_manager", $cuserRoles) && !empty($regM2) && in_array($cuserId, $regM2)) { 
        echo '<div class="locationInner">';
      } elseif (current_user_can('administrator')) {
        echo "<div class='locationInner locationID_$term->term_id' id='locationID_$term->term_id' value='$term->term_id'>";
      } else {
        echo '<div class="locationInner notManager">';
      }
      //check if sale price and regular price is enabled
      $enable_price = 'on';
      $stockQuantity = get_post_meta($variation->ID, "wcmlim_stock_at_{$term->term_id}", true);
      $allow_backorder = get_post_meta($variation->ID, 'wcmlim_allow_backorder_at_' . $term->term_id, true);

      // If backorders are not allowed and stock quantity is negative, set it to 0
      if ($allow_backorder !== 'Yes' && $stockQuantity < 0) {
          $stockQuantity = 0;
      }
        echo '<h2>Location : ' . $term->name . '</h2><div class="salep_regp_stockc"><div class="row variable_show_all_options"><div class="col-md-3">';
      woocommerce_wp_text_input(
        array(
          'id' => "wcmlim_variation_{$variation->ID}_location_id_{$term->term_id}",
          'label'         => __('Stock quantity', 'wcmlim'),
          'placeholder'   => __('', 'wcmlim'),
          'value'     => $stockQuantity,
          'type'       => 'number',
          'desc_tip'     => true,
          'class'         => 'woocommerce scroll single_field_variable',
          'description'   => __('Enter the Stock amount for this location', 'wcmlim'),
          'custom_attributes' => array(
            'step' => 'any',
            'min' => "0",
          )
        )
      );
      echo '</div>';
      $enable_price = 'on';

        $backendonlymode = get_option('wcmlim_allow_only_backend');
        if ($backendonlymode == 'on') {
          
        } else {
        echo '<div class="col-md-3">';
        woocommerce_wp_text_input(
          array(
            'id' => "wcmlim_variation_{$variation->ID}_regular_price_at_{$term->term_id}",
            'label' => __(
              'Regular price (' . get_woocommerce_currency_symbol() . ')',
              'wcmlim'
            ),
            'placeholder' => __('', 'wcmlim'),
            'desc_tip' => 'true',
            'description' => __('The amount of credits for this product in currency format.', 'wcmlim'),
            'type' => 'text',
            'class'         => 'woocommerce wcmlim_variable_product_regular_price',
            'attr' => "data-id=$term->term_id",
            'value'     => get_post_meta($variation->ID, "wcmlim_regular_price_at_{$term->term_id}", true),
            'custom_attributes' => array(
              'loc-id' 	=> $term->term_id,
              'pro-id' 	=> $variation->ID
            )
          )
        );
        echo '</div>';
        echo '<div class="col-md-3">';
        woocommerce_wp_text_input(
          array(
            'id' => "wcmlim_variation_{$variation->ID}_sale_price_at_{$term->term_id}",
            'label' => __(
              'Sale price (' . get_woocommerce_currency_symbol() . ')',
              'wcmlim'
            ),
            'placeholder' => __('', 'wcmlim'),
            'desc_tip' => 'true',
            'description' => __('The amount of credits for this product in currency format.', 'wcmlim'),
            'type' => 'text',
            'class'         => 'woocommerce wcmlim_variable_product_sale_price',
            'attr' => "data-id=$term->term_id",
            'value'     => get_post_meta($variation->ID, "wcmlim_sale_price_at_{$term->term_id}", true),
            'custom_attributes' => array(
              'loc-id' 	=> $term->term_id,
              'pro-id' 	=> $variation->ID
            )
          )
        );
        echo '</div>';
        echo '<div class="col-md-3">';
        $enable_COGSprice = 'on';
          ?>
            <p class="form-field <?php echo "wcmlim_variation_{$variation->ID}_cogs_at_{$term->term_id}_field"?>">
              <label for="<?php echo "wcmlim_variation_{$variation->ID}_cogs_at_{$term->term_id}_field"?>">Purchase price ($)</label>
              <span class="woocommerce-help-tip"></span>
              <input type="text" class="woocommerce wcmlim_variable_cogs_at" style="" name="<?php echo "wcmlim_variation_{$variation->ID}_cogs_at_{$term->term_id}_field"?>" id="<?php echo "wcmlim_variation_{$variation->ID}_cogs_at_{$term->term_id}"?>" value="<?php echo get_post_meta($variation->ID, "wcmlim_cogs_at_{$term->term_id}", true);?>" placeholder=""> 
            </p>
          <?php
        echo '</div>';
      }
      echo '</div>';
      echo '<div class="row variable_show_allow_options">';
      echo '<div class="col-md-3">';
      woocommerce_wp_select(array(
        'id'      => 'wcmlim_allow_backorder_'. $variation->ID . '_location_id_' . $term->term_id,
        'label'   => __('Allow Backorders?', 'wcmlim'),
        'class' => 'woocommerce wcmlim_allow_backorder',
        'options'     => array(
          'No'    => __('Don`t Allow', 'woocommerce' ),
          'Yes' => __('Allow', 'woocommerce' ),
        ),
        'value'   => get_post_meta($variation->ID, 'wcmlim_allow_backorder_at_' . $term->term_id, true),
      ));
      echo '</div>';
      echo '<div class="col-md-3">';
      woocommerce_wp_select(array(
        'id'      => 'wcmlim_allow_specific_location_'. $variation->ID . '_location_id_' . $term->term_id,
        'label'   => __('Allow Specific Location?', 'wcmlim'),
        'class' => 'woocommerce wcmlim_allow_specific_location',
        'options'     => array(
          'Yes' => __('Yes', 'woocommerce' ),
          'No'    => __('No', 'woocommerce' ),
        ),
        'value'   => get_post_meta($variation->ID, 'wcmlim_allow_specific_location_at_' . $term->term_id, true),
      ));
      echo '</div>';
      echo '<div class="col-md-3"><p style="visibility:hidden;">empty column</p> </div>
      <div class="col-md-3"><p style="visibility:hidden;">empty column</p> </div>
      </div>';
      $_vsl = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));

      if (!empty($_vsl)) {
        echo '<ol>';
        foreach ($_vsl as $_vs) {
          echo '<li>';
          echo '<h2>Sub Location : ' . $_vs->name . '</h2>';
          woocommerce_wp_text_input(
            array(
              'id'            => "wcmlim_variation_{$variation->ID}_location_id_{$_vs->term_id}",
              'label'         => __('Stock quantity', 'wcmlim'),
              'placeholder'   => __('Input stock', 'wcmlim'),
              'value'         => get_post_meta($variation->ID, "wcmlim_stock_at_{$_vs->term_id}", true),
              'type'          => 'number',
              'desc_tip'     => true,
              'class'         => 'woocommerce',
              'description'   => __('Enter the Stock amount for this location', 'wcmlim'),
              'custom_attributes' => array(
                'step' => 'any'
              )
            )
          );
          echo '</li>';
        }
        echo '</ol>';
      }

      echo '</div></div>';
    }

    echo '</div>';

   
    foreach($terms as $key=>$term){
      $draftStock = get_post_meta($variation->ID, 'wcmlim_draft_stock_at_' . $term->term_id, true);
    //  if draftstock is in minus then set to zero
        if ($draftStock < 0) {
          $draftStock = 0;
      }
      
      ?>
        <input type="hidden" id="wcmlim_draft_stock_at_<?php echo $term->term_id; ?>" value="<?php echo $draftStock; ?>"/>
      <?php
    }
  }

  /**
   * Save the stock location field inside inventory variable product.
   * Save the Stock Location Field inside the Products(Variations)
   * @since    1.0.0
   */

  public function wcmlim_save_variation_settings_fields($variation_id, $loop)
  {
    
    
    if (empty($variation_id)) return;

    // Get product object
    $product = wc_get_product($variation_id);
    if (empty($product)) return;

    $manage_stock = get_post_meta($variation_id, '_manage_stock', true); 
    
    $defaultLocation = get_option("wcmlim_enable_default_location");
    if ($defaultLocation == "on" ) {
      //  save the default location
      $wcmlim_select_location = $_POST["_select_default_location_{$variation_id}"];
      if (!empty($wcmlim_select_location)){
        update_post_meta($variation_id, 'wcmlim_default_location', $wcmlim_select_location);
      }else {
        update_post_meta($variation_id, 'wcmlim_default_location',  '');
      }
    }
    
    // Grab stock amount from all terms
    $product_terms_stock = array();

    // Grab input amounts
    $input_amounts = array();

    // Define counter
    $counter = 0;
    $categories = [];

    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $terms_total = count($terms);
    $b4L = 'on';
    $specific_location = 'on';

    foreach ($terms as $term) {
      // if($b4L == "on"){
      if (isset($_POST['wcmlim_allow_backorder_'. $variation_id . '_location_id_' . $term->term_id])) {
        $wcmlim_allow_backorder = sanitize_text_field($_POST['wcmlim_allow_backorder_'. $variation_id . '_location_id_' . $term->term_id]);
        $allow_backorder_post_mets = get_post_meta($variation_id, 'wcmlim_allow_backorder_at_' . $term->term_id, true);
        if($allow_backorder_post_mets !=  $wcmlim_allow_backorder ){
         update_post_meta($variation_id, 'wcmlim_allow_backorder_at_' . $term->term_id, $wcmlim_allow_backorder);
        }
      }
    

    // if($specific_location == "on"){
      if (isset($_POST['wcmlim_allow_specific_location_'. $variation_id . '_location_id_' . $term->term_id])) {
        $wcmlim_allow_backorder = sanitize_text_field($_POST['wcmlim_allow_specific_location_'. $variation_id . '_location_id_' . $term->term_id]);
        $allow_backorder_post_mets = get_post_meta($variation_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, true);
        if($allow_backorder_post_mets !=  $wcmlim_allow_backorder ){
         update_post_meta($variation_id, 'wcmlim_allow_specific_location_at_' . $term->term_id, $wcmlim_allow_backorder);
        }
      }
    
 

      if (isset($_POST["wcmlim_variation_{$variation_id}_location_id_{$term->term_id}"])) {

        // Initiate counter
        $counter++;
        if(($_POST["wcmlim_variation_{$variation_id}_location_id_{$term->term_id}"] != 0) && ($_POST["wcmlim_variation_{$variation_id}_location_id_{$term->term_id}"] != ''))
        {
          $categories[] = $term->term_id;
        }  
        // Save input amounts to array
        $input_amounts[] = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_location_id_{$term->term_id}"]);

        // Get post meta
        $postmeta_stock_at_term = get_post_meta($variation_id, "wcmlim_stock_at_{$term->term_id}", true);

        // Pass terms stock to variable
        if ($postmeta_stock_at_term) {
          $product_terms_stock[] = $postmeta_stock_at_term;
        }

        $stock_location_term_input = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_location_id_{$term->term_id}"]);

        // Check if the $_POST value is the same as the postmeta, if not update the postmeta
        if ($stock_location_term_input !== $postmeta_stock_at_term) {
          // Update the post meta
          update_post_meta($variation_id, "wcmlim_stock_at_{$term->term_id}", $stock_location_term_input);
        }

        // Update stock when reach the last term
        if ($counter === $terms_total) {
          if(intval(array_sum($input_amounts)) > 0)
          {
            update_post_meta($variation_id, '_stock', array_sum($input_amounts));
          }
        }
        

        $Pterms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
        $Pterms_total = count($Pterms);
        if (!empty($Pterms)) {
          // Grab stock amount from all terms
          $pp_terms_stock = array();

          // Grab input amounts
          $pinput_amounts = array();

          // Define counter
          $pcounter = 0;

          foreach ($Pterms as $pt) {
            if (isset($_POST["wcmlim_variation_{$variation_id}_location_id_{$pt->term_id}"])) {

              // Initiate counter
              $pcounter++;

              $categories[] = $pt->term_id;

              // Save input amounts to array
              $pinput_amounts[] = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_location_id_{$pt->term_id}"]);

              // Get post meta
              $stock_at_pterm = get_post_meta($variation_id, "wcmlim_stock_at_{$pt->term_id}", true);

              // Pass terms stock to variable
              if ($stock_at_pterm) {
                $pp_terms_stock[] = $stock_at_pterm;
              }

              $parent_location_term_input = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_location_id_{$pt->term_id}"]);

              // Check if the $_POST value is the same as the postmeta, if not update the postmeta
              if ($parent_location_term_input !== $stock_at_pterm) {

                // Update the post meta
                update_post_meta($variation_id, "wcmlim_stock_at_{$pt->term_id}", $parent_location_term_input);
              }

              // Update stock when reach the last term
              if ($pcounter === $Pterms_total) {
                if (update_post_meta($variation_id, "wcmlim_stock_at_{$term->term_id}", array_sum($pinput_amounts))) {
                  $ParentStock[] = get_post_meta($variation_id, "wcmlim_stock_at_{$term->term_id}", true);
                  if(intval(array_sum($ParentStock)) > 0)
                  {
                    update_post_meta($variation_id, '_stock', array_sum($ParentStock));
                  }
                }
              }
            }
          }
        }

        $stock_regular_price = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_regular_price_at_{$term->term_id}"]);
        if (isset($stock_regular_price)) {
          update_post_meta($variation_id, "wcmlim_regular_price_at_{$term->term_id}", $stock_regular_price);
        }
        $stock_sale_price = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_sale_price_at_{$term->term_id}"]);
        if (isset($stock_sale_price)) {
          update_post_meta($variation_id, "wcmlim_sale_price_at_{$term->term_id}", $stock_sale_price);
        }

        $stock_cogs_price = sanitize_text_field($_POST["wcmlim_variation_{$variation_id}_cogs_at_{$term->term_id}"]);
        
        
        if (isset($stock_cogs_price)) {
          update_post_meta($variation_id, "wcmlim_cogs_at_{$term->term_id}", $stock_cogs_price);
        }

        $_parentStock[] = get_post_meta($variation_id, "wcmlim_stock_at_{$term->term_id}", true);
        if(intval(array_sum($_parentStock)) > 0)
        {
          update_post_meta($variation_id, '_stock', array_sum($_parentStock));
        }
        if ($_parentStock) {
          $product_terms_stock[] = $_parentStock;
        }
      }
    }
    wp_set_post_terms($variation_id, $categories, 'locations');

    $product_terms_stock = array_sum($product_terms_stock);
    $stock_qty = array_sum($input_amounts);
    // Check if stock in terms exist
    if (!empty($product_terms_stock)) {
      // update stock status

      $product = wc_get_product($variation_id);
      if (empty($product)) return;

          // backorder disabled
          if (!$product->is_on_backorder()) {
           
            if ($manage_stock == 'no') { 
              // Retrieve the variation IDs from the $_POST array
              $variation_ids = $_POST['variable_post_id'];

              // Retrieve the stock statuses from the $_POST array
              $stock_statuses = $_POST['variable_stock_status'];

              // Loop through each variation
              foreach ($variation_ids as $index => $variation_id) {
                  // Retrieve the stock status for the current variation
                  $stock_status = isset($stock_statuses[$index]) ? sanitize_text_field($stock_statuses[$index]) : 'instock';

                  // Update the variation's _stock_status meta with the selected stock status
                  update_post_meta($variation_id, '_stock_status', '$stock_status');

                  // Optionally, you can update product visibility terms based on the selected stock status
                  if ($stock_status == 'instock') {
                       
                      wp_remove_object_terms($variation_id, 'outofstock', 'product_visibility');
                      wp_set_post_terms($variation_id, 'instock', 'product_visibility', true);
                    
                  } else {
                      wp_set_post_terms($variation_id, 'outofstock', 'product_visibility', true);
                  }
              }

            } 
            else {
               
            if ($stock_qty > 0) {
              update_post_meta($variation_id, '_stock_status', 'instock');

          // remove the link in outofstock taxonomy for the current product
          wp_remove_object_terms($variation_id, 'outofstock', 'product_visibility');
        } else {
          update_post_meta($variation_id, '_stock_status', 'outofstock');

          // add the link in outofstock taxonomy for the current product
          wp_set_post_terms($variation_id, 'outofstock', 'product_visibility', true);
        }
      }
        // backorder enabled
      } else {
        $current_stock_status = get_post_meta($variation_id, '_stock_status', true);
        if ($stock_qty > 0 && $current_stock_status != 'instock') {
          update_post_meta($variation_id, '_stock_status', 'instock');
          // remove the link in outofstock taxonomy for the current product
          wp_remove_object_terms($variation_id, 'outofstock', 'product_visibility');
        } else {
          update_post_meta($variation_id, '_stock_status', 'onbackorder');
        }
      }
    }
  }

  /**
   * Add purchase price, Regular & Sale price for simple Product
   *
   * @since    1.0.1
   */
  public function wcmlim_add_custom_fields_product()
  {
    global $post;
    $post_id = $post->ID;
    $_product = wc_get_product($post_id);
    $productType = $_product->is_type('simple');
    $enable_price = 'on';
    if ($productType && $enable_price == 'on') {
      woocommerce_wp_text_input(array(
        'id' => 'purchase_price',
        'wrapper_class' => 'show_if_simple show_if_grouped show_if_subscription',
        'label' => __('Purchase Price', 'woocommerce'),
        'description' => __('Add Purchase/Cost of Goods Price', 'woocommerce'),
        'desc_tip' => true
      ));
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
      echo '<div id="locationList" class="options_group">';
      echo '<p style="display:none;"><b>' . __('All Locations', 'wcmlim') . '</b></p>';
      foreach ($terms as $term) {
        if(!function_exists('wp_get_current_user')) {
          include(ABSPATH . "wp-includes/pluggable.php"); 
        }
        $cuser = wp_get_current_user();
        $cuserId = $cuser->ID;
        $cuserRoles = $cuser->roles;
        $shopM =  get_term_meta($term->term_id, 'wcmlim_shop_manager', true);
        $regM = get_term_meta($term->term_id, "wcmlim_locator", true);
        $regM2 = get_term_meta($regM, "wcmlim_shop_regmanager", true);     
        if (in_array("location_shop_manager", $cuserRoles) && !empty($shopM) && in_array($cuserId, $shopM)) {
          echo '<div class="locationInner">';
        } elseif (in_array("location_regional_manager", $cuserRoles) && !empty($regM2) && in_array($cuserId, $regM2)) {      
          echo '<div class="locationInner">';
        } elseif (current_user_can('administrator')) {
          echo '<div class="locationInner">';
        } else {
          echo '<div class="locationInner notManager">';
        }
        echo '<div style="display:none;"><h2>Location : ' . $term->name . '</h2>';

        woocommerce_wp_text_input(
          array(
            'id' => "wcmlim_product_{$post_id}_regular_price_at_{$term->term_id}",
            'label' => __(
              'Regular price (' . get_woocommerce_currency_symbol() . ')',
              'wcmlim'
            ),
            'placeholder' => __('Input regular price for location', 'wcmlim'),
            'desc_tip' => 'false',
            'description' => __('The amount of credits for this product in currency format.', 'wcmlim'),
            'type' => 'text',
            'class' => 'woocommerce wcmlim_product_regular_price',
            'attr' => "data-id=$term->term_id",
            'value' => get_post_meta($post_id, "wcmlim_regular_price_at_{$term->term_id}", true),
            'custom_attributes' => array(
              'loc-id' 	=> $term->term_id,
              'pro-id' 	=> $post_id

            ) 
          )
        );

        woocommerce_wp_text_input(
          array(
            'id' => "wcmlim_product_{$post_id}_sale_price_at_{$term->term_id}",
            'label' => __(
              'Sale price (' . get_woocommerce_currency_symbol() . ')',
              'wcmlim'
            ),
            'placeholder' => __('Input Sale price for location', 'wcmlim'),
            'desc_tip' => 'false',
            'description' => __('The amount of credits for this proroduct in currency format.', 'wcmlim'),
            'type' => 'text',
            'class' => 'woocommerce wcmlim_product_sale_price',
            'attr' => "data-id=$term->term_id",
            'value' => get_post_meta($post_id, "wcmlim_sale_price_at_{$term->term_id}", true),
            'custom_attributes' => array(
              'loc-id' 	=> $term->term_id,
              'pro-id' 	=> $post_id
            )

          )
        );
        echo '</div> </div>';
      }
      echo '</div>';
    }
  }

  /**
   * Save purchase price, Regular & Sale price for simple Product
   *
   * @since    1.0.1
   */
  public function wcmlim_add_custom_general_fields_save($post_id)
  {
    if(empty($post_id)) return;
    
    // Save Purchase price custom field
    $woocommerce_text = isset($_POST['purchase_price']) ? $_POST['purchase_price'] : "";
    update_post_meta($post_id, 'purchase_price', esc_html($woocommerce_text));
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    foreach ($terms as $term) {
      $stock_regular_price = isset($_POST["wcmlim_product_{$post_id}_regular_price_at_{$term->term_id}"]) ? sanitize_text_field($_POST["wcmlim_product_{$post_id}_regular_price_at_{$term->term_id}"]) : "";
      if (isset($stock_regular_price)) {
        update_post_meta($post_id, "wcmlim_regular_price_at_{$term->term_id}", $stock_regular_price);
      }
      $stock_sale_price = isset($_POST["wcmlim_product_{$post_id}_sale_price_at_{$term->term_id}"]) ? sanitize_text_field($_POST["wcmlim_product_{$post_id}_sale_price_at_{$term->term_id}"]) : "";
      if (isset($stock_sale_price)) {
        update_post_meta($post_id, "wcmlim_sale_price_at_{$term->term_id}", $stock_sale_price);
      }
      $stock_cogs_price = isset($_POST["wcmlim_product_{$post_id}_cogs_at_{$term->term_id}"]) ? sanitize_text_field($_POST["wcmlim_product_{$post_id}_cogs_at_{$term->term_id}"]) : "";
      if (isset($stock_cogs_price)) {
        update_post_meta($post_id, "wcmlim_cogs_at_{$term->term_id}", $stock_cogs_price);
      }
    }
  }
  /**
   * Add purchase price for Variable Product
   *
   * @since    1.0.1
   */
  public function wcmlim_price_variation_settings_fields($loop, $variation_data, $variation)
  {
    woocommerce_wp_text_input(array(
      'id' => "purchase_price_variation{$loop}",
      'name' => "purchase_price_variation[{$loop}]",
      'value' => get_post_meta($variation->ID, 'purchase_price', true),
      'label' => __('Purchase Price', 'woocommerce'),
      'desc_tip' => true,
      'description' => __('Add Purchase/Cost of Goods Price', 'woocommerce'),
      'wrapper_class' => 'form-row form-row-full'
    ));
  }
  /**
   * Save purchase price for Variable Product
   *
   * @since    1.0.1
   */
  public function wcmlim_price_save_variation_settings_fields($variation_id, $loop)
  {
    // Save Purchase price custom field
    update_post_meta($variation_id, 'purchase_price', esc_attr($_POST['purchase_price_variation'][$loop]));
  }
}
new Wcmlim_Custom_Inventory_Fields();
