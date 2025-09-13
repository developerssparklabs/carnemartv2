<?php
use Automattic\WooCommerce\Utilities\OrderUtil; // at the beginning of the file

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



class Wcmlim_Backend_Only_Mode
{
  
 	public $version;
 	public $max_value_inpl;
	public $max_in_value ;


  public function __construct()
  {
    $plugin_public = new Wcmlim_Public('wcmlim', WCMLIM_VERSION);

    $wcmlim_allow_only_backend   = get_option('wcmlim_allow_only_backend');
    if($wcmlim_allow_only_backend == 'on'){

      add_action('wp_enqueue_scripts', array($this, 'wcmlim_show_address_checkout'));

      add_action( 'woocommerce_saved_order_items', array($this,'action_woocommerce_saved_order'), 10, 2 );

      add_action( 'woocommerce_process_shop_order_meta', [$plugin_public,'wcmlim_maybe_reduce_stock_levels'], 10, 2 );
 
    }

    $wcmlim_order_fulfil_edit = get_option('wcmlim_order_fulfil_edit');
    $fulfilment_rule = get_option("wcmlim_order_fulfilment_rules");
    if($fulfilment_rule == "nearby_instock")
      {
        $fulfilment_rule = "clcsadd";
      }
    $plugin_public = new Wcmlim_Public('wcmlim', WCMLIM_VERSION);

      if ($fulfilment_rule == "clcsadd") {        
        add_action('woocommerce_checkout_update_order_review', [$this, 'wcmlim_backend_mode_nearby_location_selection_shipping_address']);
       
        add_action('woocommerce_add_order_item_meta',  array($this, 'add_order_item_meta'),  10, 3);
      } elseif ($fulfilment_rule ==  "shipping_loc") {
        add_action('woocommerce_thankyou',  array($this, 'wcmlim_fulfil_order_shipping_zones'),  10, 1);
        add_action('woocommerce_after_checkout_validation', array($this, 'wcmlim_shipping_error'),  10, 2);
      } elseif ( $fulfilment_rule == "maxinvloc" || $fulfilment_rule == "locappriority" || $fulfilment_rule == "nearby_instock" ) {
        add_action('woocommerce_thankyou',  array($this, 'wcmlim_fulfil_saved_order_items'),  10, 4);
      } 
      
      add_action( 'woocommerce_order_item_line_item_html',  array( $this, 'add_orders_multi_inventory_ui' ), 10, 4 );          

      add_filter( 'woocommerce_cart_item_name', [$this, 'wcmlim_cart_item_name'], 10, 3 );

      add_filter('woocommerce_hidden_order_itemmeta', [$plugin_public,'hidden_order_itemmeta'], 50);
    
    add_action("woocommerce_payment_complete", [$plugin_public, "wcmlim_maybe_reduce_stock_levels"]);
    add_action('woocommerce_order_status_completed', [$plugin_public, "wcmlim_maybe_reduce_stock_levels"]);
    add_action('woocommerce_order_status_processing', [$plugin_public, "wcmlim_maybe_reduce_stock_levels"]);
    add_action('woocommerce_order_status_cancelled', [$plugin_public, 'wcmlim_maybe_increase_stock_levels']);
    add_action('woocommerce_order_status_on-hold', [$plugin_public, "wcmlim_maybe_reduce_stock_levels"]); 
  }
  
  /**
   * define the woocommerce_saved_order_items callback 
   * 
   * @since 1.2.13
   * Updated 3.0.7
   */
  //enqueue js scripts while backend only mode is enabled 
  public function wcmlim_show_address_checkout()
    {
    $this->version = '3.2.1';
      wp_enqueue_script('jquery');
      wp_register_script('wcmlim_show_address', WCMLIM_URL_PATH . 'public/js/wcmlim-show-address-min.js', array('jquery'), rand(), false);
      wp_enqueue_script('wcmlim_show_address');
      wp_localize_script( 'wcmlim_show_address', 'admin_url', array('ajax_url' => admin_url( 'admin-ajax.php' ) ) );
      wp_enqueue_style('wcmlim_frontview_css', WCMLIM_URL_PATH . '/public/css/wcmlim-public-min.css', array(), $this->version, 'all');
    
    }

  public function wcmlim_maybe_reduce_stock_levels($order_id)
	{
    if (!$order_id) {
      return;
  }
  
  $order = wc_get_order($order_id);
  
  if (!$order) {
      return;
  }
  
  $stock_reduced = $order->get_data_store()->get_stock_reduced($order_id);
  $note = '';
  
  foreach ($order->get_items() as $item_id => $item) {
      // Retrieve item data
      $item_stock_reduced = $item->get_meta('_reduced_stock', true);
      $itemSelLocTermId = wc_get_order_item_meta($item_id, '_selectedLocTermId');
      $product_id = $item->get_variation_id() ?: $item->get_product_id();
      $item_quantity = $item->get_quantity();
      $location_name = wc_get_order_item_meta($item_id, 'Location');
  
      // Retrieve previous location and stock for the item
      $old_itemSelLocTermId = $item->get_meta('_old_selectedLocTermId', true);
      $old_maxStockAtSub = get_post_meta($product_id, "wcmlim_stock_at_{$old_itemSelLocTermId}", true);
  
      // Check if location has changed for this item
      if ($itemSelLocTermId != $old_itemSelLocTermId) {
          // Restore the stock at the previous location
          update_post_meta($product_id, "wcmlim_stock_at_{$old_itemSelLocTermId}", intval($old_maxStockAtSub) + intval($item_quantity));
          
          // Reduce stock at the new location for this item
          $maxStockAtSub = get_post_meta($product_id, "wcmlim_stock_at_{$itemSelLocTermId}", true);
          update_post_meta($product_id, "wcmlim_stock_at_{$itemSelLocTermId}", intval($maxStockAtSub) - intval($item_quantity));
  
          // Mark the new location stock as reduced
          update_post_meta($product_id, "_wcmlim_stock_at_{$itemSelLocTermId}_reduced", true);
  
          // Add note about stock reduction for the new location
          $stock_reduce_location = intval($maxStockAtSub) - intval($item_quantity);
          $note .= "{ Stock levels reduced: {$item->get_name()} from Location: {$location_name} {$maxStockAtSub} &rarr; {$stock_reduce_location} }";
  
          // Update item meta to indicate stock reduction for the new location
          $item->add_meta_data('_wcmlim_reduced_stock', $item_quantity, true);
          $item->add_meta_data('_reduced_stock', $item_quantity, true);
          $item->add_meta_data('_location_name', $location_name, true);
          $item->add_meta_data('_old_selectedLocTermId', $itemSelLocTermId, true);
          $item->save();
      }
  }
  
  $order->add_order_note($note);
  
  $trigger_reduce = apply_filters('woocommerce_payment_complete_reduce_order_stock', !$stock_reduced, $order_id);
  
  // Only continue reducing stock if necessary
  if (!$trigger_reduce) {
      return;
  }
  
  $this->wc_reduce_stock_levels($order);
  
  // Ensure stock is marked as "reduced" in case payment complete or other stock actions are called.
  $order->get_data_store()->set_stock_reduced($order_id, true);
  update_post_meta($order_id, '_order_stock_reduced', 'yes');
  
  
	}
  
  public function action_woocommerce_saved_order( $order_id, $items = null ) {

    $postaction = isset($_POST['action']) ? $_POST['action'] : "";
    if(WC_HPOS_IS_ACTIVE){
      $post_type = OrderUtil::get_order_type( $order_id );
    }else{
      $post_type = get_post_type( $order_id ); 
    }

    if( 'shop_order' != $post_type ) {
      return;
    }
    
    $order        = wc_get_order( $order_id );
    $item_storeid = array(); 
    
    foreach ( $order->get_items() as $item_id => $item ) {
       

      $order_s1 = '_locationtermid_'.$item_id;
      $order_s2 = '_locationKey_'.$item_id;
      $sltermid = filter_input( INPUT_POST,  $order_s1 );
      $sltermkey = filter_input( INPUT_POST,  $order_s2 );
      
      if('shop_order' == $post_type && $postaction == "edit_order"){
        $tid = wc_get_order_item_meta( $item_id, '_selectedLocTermId', true);       
        $tlc = get_term( $tid )->name;
      }
     
      
      $_lcti =  ($sltermid == null || $sltermid == false) ? intval(wc_get_order_item_meta( $item_id, '_selectedLocTermId', true)) : intval($sltermid);
      $_lctk = ($sltermkey == null || $sltermkey == false || $sltermkey == 0) ? $_lctk =  intval(wc_get_order_item_meta( $item_id, '_selectedLocationKey', true)) : intval($sltermkey);
      $term_name = get_term( $_lcti )->name;
      $_lc = (!empty($term_name)) ? $term_name : wc_get_order_item_meta( $item_id, 'Location', true);
     
      $product    = $item->get_product();
      $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();

      if($_lc){
         wc_update_order_item_meta( $item_id, 'Location', $_lc );
      } else {
         wc_add_order_item_meta($item_id, 'Location', $_lc);
      }

      if(!empty($_lctk) || $_lctk == 0){
         wc_update_order_item_meta( $item_id, '_selectedLocationKey', $_lctk );
      }else{
        wc_add_order_item_meta($item_id, '_selectedLocationKey', $_lctk);
      }

      if(!empty($_lcti)){      
          wc_update_order_item_meta($item_id, '_selectedLocTermId', $_lcti );
      
        if((int)$sltermid != $tid) {
          $this->wcmlim_maybe_reduce_stock_levels($order_id);
        }

      } else {
        wc_add_order_item_meta($item_id, '_selectedLocTermId', $_lcti);
      } 

      $itemSelLocTermId = wc_get_order_item_meta( $item_id, '_selectedLocTermId');
      if(!in_array( $itemSelLocTermId, $item_storeid ) ) {
        $item_storeid[] = $itemSelLocTermId;
      }    
            
      $item->save();
    } 
    $dataLocate = array(); 
    if (!empty($item_storeid)) {
      foreach($item_storeid as $wcmlim_locid) {
        $term_object = get_term( $wcmlim_locid );
        $dataLo = $term_object->term_id;                    
        $dataLocate[] = $dataLo;
      }   
    }
    
    if(!empty($dataLocate)) {
      if (WC_HPOS_IS_ACTIVE) {
        $order->update_meta_data( '_multilocation', $dataLocate );
      }else{
        update_post_meta($order_id, "_multilocation", $dataLocate);
      }
    }

    if(!empty($term_name)) {
      if (WC_HPOS_IS_ACTIVE) {
        $order->update_meta_data( '_location', $term_name );
      }else{
        update_post_meta($order_id, "_location", $term_name);
      }
    }
    if ($locChanges ||  $locChanges2 ) {
      $order->add_order_note(implode(', ', $locChanges));
      $order->add_order_note(implode(', ', $locChanges2));
      
      do_action('woocommerce_restore_order_stock', $order);
    }
     $order->save();
  }

  public function wcmlim_update_totaldata($product_id)
  {
    $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
    if(empty($product_id)){
      return;
    }

    if (!empty($locations)) {
      foreach ($locations as $location) {
        $total +=  intval(get_post_meta($product_id, "wcmlim_stock_at_{$location->term_id}", true));
      }
    }       
      if(intval($total) > 0)
      {
        update_post_meta($product_id, '_stock', $total);
      }
      if ($total > 0) {
        update_post_meta($product_id, '_stock_status', 'instock');
      } else {
        update_post_meta($product_id, '_stock_status', 'outofstock');
      }
      wc_delete_product_transients( $product_id ); // Update product cache  
  }
  /**
   * Function responsible for 
   * line item and assign location to order item automatically as per condition used on rules
   * 
   * @since 3.0.7
   */
  public function wcmlim_fulfil_saved_order_items($order_id)
  {
    if ( ! $order_id ) {
      return;
    }
    $order =  wc_get_order($order_id);
    if(!$order) {
      return;
    }
    
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0)); 
    foreach ($order->get_items() as $item_id => $item) { 
      $product_id = $item->get_variation_id() ?: $item->get_product_id();
      $routeRules = get_option("wcmlim_order_fulfilment_rules");
      if($routeRules == "nearby_instock")
      {
        $routeRules = "clcsadd";
      }
      $item_quantity = $item->get_quantity();  
      if ($routeRules == "maxinvloc") {
        $item_quantity = $item->get_quantity();
        $this->stock_fullfllment_for_next_loc($product_id, $item_quantity, $item_id, $order, "most_inventory");
      }else if ($routeRules == "locappriority") {

        foreach ($terms as $k => $t) {
          $locPriority[$t->term_id] = get_post_meta($product_id, 'wcmlim_stock_at_' . $t->term_id, true);
          $cvalterm = get_term_meta($t->term_id, 'wcmlim_location_priority', true); // get priotity number
          if( $cvalterm != null ||  $cvalterm != false ) {
            $priority[$cvalterm] = $t->term_id;
            $lker[$cvalterm] = $k;
          }
        }
        if(isset($priority))
        { 
          if(is_array($priority))
          {
            ksort($priority); //sorting array
          }
        foreach($priority as $key => $val ) {
          $cvalterm = get_post_meta($product_id, "wcmlim_stock_at_{$val}", true);         
          if( $cvalterm != null || $cvalterm != false ) {
            $cvalterm = (int) $cvalterm;
            if ( $cvalterm > 0 ) {
              $termname = get_term($val)->name;
              $getKey = $lker[$key];   
              $set_location = wc_get_order_item_meta($item_id, 'Location', $termname);
              if (  $set_location == '') {
                wc_add_order_item_meta($item_id, '_selectedLocTermId', $val);              
                wc_add_order_item_meta($item_id, 'Location', $termname);
                wc_add_order_item_meta($item_id, '_selectedLocationKey', $getKey);
              }          
              $termname = get_term($val)->name;
              $postmeta_stock_at_term = $cvalterm;


              if(intval($item_quantity) > intval($postmeta_stock_at_term))
              {
                update_post_meta($product_id, 'wcmlim_stock_at_' . $val, 0);
                $item_quantity = intval($item_quantity) - intval($postmeta_stock_at_term);
                $item_quantity = intval($item_quantity);
                $stock_reduce_location = $postmeta_stock_at_term;
                $note = "{ Priority wise location Stock levels reduced: {$item->get_name()}  from Location: {$termname} {$postmeta_stock_at_term} &rarr; {$stock_reduce_location} }";
                $order->add_order_note($note);
                continue;
              }else{
              update_post_meta($product_id, 'wcmlim_stock_at_' . $val, $postmeta_stock_at_term - $item_quantity);
              $stock_reduce_location = intval($postmeta_stock_at_term) - intval($item_quantity);
              $note = "{ Priority wise location Stock levels reduced: {$item->get_name()}  from Location: {$termname} {$postmeta_stock_at_term} &rarr; {$stock_reduce_location} }";
              $order->add_order_note($note);

              }

              $item_storeid = array();
              if(!in_array( $val, $item_storeid ) ) {
                $item_storeid[] = $val;
              }
              $dataLocate = array();
              if(!empty($item_storeid) ){
                foreach($item_storeid as $wcmlim_item_id) {
                  $term_object = get_term( $wcmlim_item_id );
                  $dataLo = $term_object->term_id;										
                  $dataLocate[] = $dataLo;
                }
              }
              
              if(!empty($dataLocate)) {
                if(WC_HPOS_IS_ACTIVE){
                  $order->update_meta_data( '_multilocation', $dataLocate );
                }else{
                  update_post_meta($order_id, "_multilocation", $dataLocate);
                }
              }
              if(!empty($termname)) {
                if(WC_HPOS_IS_ACTIVE){
                  $order->update_meta_data( '_location', $termname );
                }else{
                  update_post_meta($order_id, "_location", $termname);
                }
              }
              $this->sendnotification_formanager($val, $order_id);
              $this->wcmlim_update_totaldata($product_id);
              break;
            }
          }
        }
      }
      } 
      else if ($routeRules == "nearby_instock") {
        global $woocommerce, $cart_item, $item;
        $items = $woocommerce->cart->get_cart();
        foreach($items as $item => $values) { 
          foreach ($terms as $in => $term) {
            if($values['variation_id'] > 0){
              $product_id = $values['variation_id'];
            }else{
              $product_id = $values['data']->get_id();
            }
          }
        }
        $from_address = get_option('from_address');
        $id_pml = array_search($product_id, array_column($from_address, 'product_id'));
        $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' .$from_address[$id_pml]['nearby_id_location'], true);
        update_post_meta($product_id, 'wcmlim_stock_at_' .$from_address[$id_pml]['nearby_id_location'] , $postmeta_stock_at_term - $item_quantity);
      } 
    }
    $order->save();
  }
  public function stock_fullfllment_for_next_loc($product_id, $item_quantity, $item_id, $order, $type)
{

  if ($type !== "most_inventory" || !empty(wc_get_order_item_meta($item_id, '_selectedLocTermId'))) {
    return;
  }
    
    $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    
    $product_title = get_the_title($product_id);
    $prepare_priority_loc = array();
    foreach($locations as $key=>$term){
      $product_id_loc_stock = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
      $prepare_priority_loc[] = array(
        "up_termid" => $term->term_id,
        "up_key" => $key,
        "up_name" => $term->name,
        "up_stock" => $product_id_loc_stock
      );  
    }
    //sort location by most inventory
    usort($prepare_priority_loc, function($a, $b) {
      return $b['up_stock'] <=> $a['up_stock'];
    });
    
    $carry_stock = 0;
    $updated_cart_item_quantity = $item_quantity;
    foreach ($prepare_priority_loc as $key => $value) {
      if($key == 0)
      {
        $order->remove_item($item_id);
      }
      $up_stock = $value['up_stock'];
      $up_key = $value['up_key'];
      $up_name = $value['up_name'];
      $up_termid = $value['up_termid'];

      $termname = get_term($up_termid)->name;
      $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $up_termid, true);
      if ($updated_cart_item_quantity > 0) {
        $product_name = get_the_title($product_id);
        if ($updated_cart_item_quantity <= $up_stock) {
                
          $order_item = $order->add_product(
          wc_get_product($product_id),
          $updated_cart_item_quantity,
          array(
          '_selectedLocTermId' => $up_termid,
          'Location' => $up_name,
          '_selectedLocationKey' => $up_key
          )
          );
      
          // Add order item meta
          wc_add_order_item_meta($order_item, '_selectedLocTermId', $up_termid);
          wc_add_order_item_meta($order_item, 'Location', $up_name);
          wc_add_order_item_meta($order_item, '_selectedLocationKey', $up_key);
      
          $updated_loc_stock =  $up_stock - $updated_cart_item_quantity;
      
          update_post_meta($product_id, 'wcmlim_stock_at_' . $up_termid, $updated_loc_stock);
          $note = "Priority wise location Stock levels reduced: {$product_name} from Location: {$termname} {$up_stock} &rarr; {$updated_loc_stock}";
          $order->add_order_note($note);
          $order->save();
          $updated_cart_item_quantity = 0;
        } else {
          if ($updated_cart_item_quantity > $up_stock) {
            $order_item = $order->add_product(
              wc_get_product($product_id),
              $up_stock,
              array(
                '_selectedLocTermId' => $up_termid,
                'Location' => $up_name,
                '_selectedLocationKey' => $up_key
              )
            );

            // Add order item meta
            wc_add_order_item_meta($order_item, '_selectedLocTermId', $up_termid);
            wc_add_order_item_meta($order_item, 'Location', $up_name);
            wc_add_order_item_meta($order_item, '_selectedLocationKey', $up_key);

            $updated_loc_stock = 0;
            
              update_post_meta($product_id, 'wcmlim_stock_at_' . $up_termid, $updated_loc_stock);
              $note = "Priority wise location Stock levels reduced: {$product_name} from Location: {$termname} {$up_stock} &rarr; {$updated_loc_stock}";
              $order->add_order_note($note);
              $order->save();
            $updated_cart_item_quantity = $updated_cart_item_quantity - $up_stock;
          }
        }
      }
    }
}
  /**
   * Function responsible for adding location dropdown
   * to each line item on add order section woocommerce
   * 
   * @since 1.2.13
   * update 3.0.7
   */

  public function add_orders_multi_inventory_ui( $item_id, $item, $order = NULL ) {
    if(! $item){
      return;
    }
    global $name, $cart_item, $cart_item_key;

    $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
    $postmeta_backorders_product = get_post_meta($product_id, '_backorders', true);
    $fulfilment_rule = get_option("wcmlim_order_fulfilment_rules");
    if($fulfilment_rule == "nearby_instock")
      {
        $fulfilment_rule = "clcsadd";
      }
    $routeRules = get_option("wcmlim_order_fulfilment_rules");
    if($routeRules == "nearby_instock")
      {
        $routeRules = "clcsadd";
      }
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0)); 
    $result = intval(wc_get_order_item_meta($item_id, '_selectedLocTermId', true));
    
    $from_address = get_option('from_address');
	  if(isset($from_address) && is_array($from_address))
		  {
    foreach($from_address as $key=>$value)
    {
      if($value['product_id'] == $item->get_product_id() || ($value['product_id'] == $item->get_variation_id() ))
      { 
        $nearby_location = $value['nearby_id_location'];
        $name = $value['name'];
      }
    } 
	  }
  
    if(!empty($result) || $result == 0) {
      $location_term_id = $result;
    }else{
      $location_term_id = "";
    } ?>
    <tr class="order-item-wcml-panel" data-sort-ignore="true" data-order_item_id="<?php echo $item_id;?>">
      <td colspan="100">
      <div class="order-item-wcml-wrapper">
          <div class="form-group">
          <label for="_bom_location"><?php _e('Location:', 'wcmlim');?></label> 
               <select id="_bom_location_<?=$item_id?>" name="_bom_location_<?=$item_id?>" style="width:auto">  
              <option value=""><?php _e('Select location', 'wcmlim'); ?></option>
            <?php             
              if ($routeRules == "maxinvloc" ) 
              {               
                foreach ($terms as $k => $t) {
                  $maxStockLoc[$t->term_id] = get_post_meta($product_id, 'wcmlim_stock_at_' . $t->term_id, true);
                  $lker[$t->term_id] = $k;
                }
                $maxValue = max($maxStockLoc); // get max number
                $maxKey = array_search($maxValue, $maxStockLoc); // get term id
                $getKey = array_search($maxKey, $lker); // get key
                
                if ($maxKey) {
                  $maxStockAtLoc = get_post_meta($product_id, "wcmlim_stock_at_{$maxKey}", true);
                }
                $termname = get_term($maxKey)->name;
                $lk = $getKey; 
                $lk2 = $maxKey;
                if($location_term_id !=  $maxKey){
                  $location_term_name = get_term($location_term_id)->name;
                  $location_stock = get_post_meta($product_id, "wcmlim_stock_at_{$location_term_id}", true);
                  ?>
                  <option class="maxinvloc" data-lc-key="<?php echo $location_term_id; ?>" value="<?php esc_html_e($location_term_id); ?>" <?php echo  'selected="selected"' ;?>><?php echo $location_term_name .' - '.$location_stock ;?></option>
                <?php }else{
                  ?>
                  <option class="maxinvloc" data-lc-key="<?php echo $getKey; ?>" value="<?php esc_html_e($maxKey); ?>" <?php echo ($location_term_id ==  $maxKey) ? ' selected="selected"' : '';?>><?php echo $termname .' - '.$maxStockAtLoc ;?></option>
                  <?php  
                }
               
              }
              else if ($routeRules == "locappriority") {
                foreach ($terms as $k => $t) {
                  $locPriority[$t->term_id] = get_post_meta($product_id, 'wcmlim_stock_at_' . $t->term_id, true);
                  $cvalterm = get_term_meta($t->term_id, 'wcmlim_location_priority', true); // get priotity number
                  if( $cvalterm != null ||  $cvalterm != false ) {
                    $priority[$cvalterm] = $t->term_id;
                    $lker[$cvalterm] = $k;
                  }
                }
                if(isset($priority))
                {
                  if(is_array($priority))
                  {
                    ksort($priority); //sorting array
                  }
                foreach($priority as $key => $val ) {
                  $cvalterm = (int) get_post_meta($product_id, "wcmlim_stock_at_{$val}", true);
                  if( $cvalterm > 0 && $cvalterm != null   ) {
                    $termname = get_term($val)->name;
                    $getKey = $lker[$key];
                    ?>
                    <option class="priority" data-lc-key="<?php echo $key; ?>" value="<?php esc_html_e($val); ?>" <?php echo ($location_term_id ==  $val) ? ' selected="selected"' : '';?>><?php echo $termname .' - ('. $cvalterm . ')'; ?></option> 
                    <?php
                    $lk = $getKey;
                    $lk2 = $val;
                  }
                }    
              }          
            }else if ($routeRules == "nearby_instock" ){ ?>
                <option class="nearby_instock" data-lc-key="<?php echo $name; ?>" value="<?php echo $name;?>" <?php echo (strtoupper(trim($name)) == strtoupper(trim($name))) ? ' selected="selected"' : '';?>><?php echo $name ;?></option>
               <?php
              }else {
                $lk = array();
                $lk2 = array();
                $errors = array();

                foreach ($terms as $id => $value) {
                    $_stockat = get_post_meta($product_id, "wcmlim_stock_at_{$value->term_id}", true);
                    if (!empty($location_term_id) && $value->term_id == $location_term_id) {
                        $lk[] = $id;
                        $lk2[] = $value->term_id;
                    }
          
                    if ($_stockat != null || $_stockat != false) {
                        $_stockat = (int) $_stockat;
                        if ($_stockat > 0) {
                            // Assuming the quantity is obtained from user input, replace this with your actual quantity value
                            $selected_quantity = $item->get_quantity();
                            $is_disabled = ($selected_quantity > $_stockat) ? 'disabled' : '';
                            $selected = "";
                            if($location_term_id == $value->term_id )
                            {
                              $selected = "selected";
                            }
                            ?>
                            <option class="default" <?php echo $selected; ?> data-lc-key="<?php echo $id; ?>" value="<?php echo $value->term_id; ?>" <?php echo $is_disabled; ?>><?php echo $value->name . ' - (' . $_stockat . ')'; ?></option>
                            <?php
                        }
                    }
                }
              }
            
            ?>              
            </select>         
            <input id="_locationKey_<?=$item_id?>" type="hidden" name="_locationKey_<?=$item_id?>" value="<?php 
              if( $lk != null ||  $lk != false )
              {
                echo $lk[0];
              }?>" />
              <input id="_locationtermid_<?=$item_id?>" type="hidden" name="_locationtermid_<?=$item_id?>" value="<?php 
              if( $lk2 != null ||  $lk2 != false )
              {
                echo $lk2[0]; 
              }?>" />
             
              <script>
              jQuery( document ).ready(function() {
                jQuery( "#_bom_location_<?=$item_id?>" ).on( "change", function (  )
                {  
                  const selectedtermid = jQuery( this ).find( "option:selected" ).val();
                  const locationKey = jQuery( this ).find( "option:selected" ).attr( 'data-lc-key' );
                  jQuery( "#_locationKey_<?=$item_id?>" ).val( locationKey );
                  jQuery( "#_locationtermid_<?=$item_id?>" ).val( selectedtermid );
                } );
              } );
              </script>
          </div>
        </div>   
      </td>
    </tr>
  <?php	
  }
 
  //* Allow to Fulfil order automatically from the Nearest Location from the Customer Shipping address
  
  public function find_product_in_array($product_id,$tmp_cart_item_exist)
  {
    if(empty($tmp_cart_item_exist))
    {
      return 0;
    }

    if(empty($product_id))
    {
      return 0;
    }

    $prepare_return = array();
    foreach($tmp_cart_item_exist as $key => $value)
    {
      if($product_id == $value['product_id'])
      {
        $prepare_return = array(
          "item_quantity" => $value['item_quantity'],
          "item_id" => $value['item_id']
        );
        unset($tmp_cart_item_exist[$key]);
        return $prepare_return;
      }
    }
    return 0;
  }
  
  public function wcmlim_backend_mode_nearby_location_selection_shipping_address($posted_data)
  {
    global $destination_prepare, $locations_data_lat_lng, $latlngarr, $shipping_latitude, $shipping_longitude, $distance, $woocommerce, $combine_item_arr;

    $items = $woocommerce->cart->get_cart();
    foreach($items as $item => $values) { 
      
      $_product_cart_id =  $values['data']->get_id(); 
      $_product_cart_quantity = $values['quantity'];
      if(isset($combine_item_arr[$_product_cart_id]))
      {
        $combine_cart_old_qty = $combine_item_arr[$_product_cart_id];
        $combine_cart_old_qty = intval($combine_cart_old_qty) + intval($_product_cart_quantity);
        $combine_item_arr[$_product_cart_id] = $combine_cart_old_qty;
      }
      else
      {
        $combine_item_arr[$_product_cart_id] = $_product_cart_quantity;
      }
    } 
    $cart = WC()->cart->get_cart();

    // Loop through each cart item and remove it
    foreach ( $cart as $cart_item_key => $cart_item ) {
        WC()->cart->remove_cart_item( $cart_item_key );
    }
    foreach($combine_item_arr as $fetch_product_id => $fetch_product_value )
    {
      WC()->cart->add_to_cart($fetch_product_id, $fetch_product_value, '0', array());
    }
    $items = $woocommerce->cart->get_cart();
       
    $coordinates_calculator = get_option('wcmlim_distance_calculator_by_coordinates');
    $addressone = $_POST['s_address'];
    $addresstwo = $_POST['s_address_2'];
    $city = $_POST['s_city'];
    $state = $_POST['s_state'];
    $country = $_POST['s_country'];
    $postcode = $_POST['s_postcode'];

    $prepare_destination_address_string = $addressone.'+'.$addresstwo.'+'.$city.'+'.$state.'+'.$country.'+'.$postcode;

    $tmp_cart_item_exist = array();
    foreach (WC()->cart->get_cart() as $item_id => $item) {
      $product_id = $item['variation_id'] ?: $item['product_id'];
      $item_quantity = $item['quantity'];
      if(!empty($tmp_cart_item_exist))
      {
        $find_in_array = $this->find_product_in_array($product_id,$tmp_cart_item_exist);
        if($find_in_array != 0)
        {
          $found_item_quantity = $find_in_array['item_quantity'];
          $found_item_id = $find_in_array['item_id'];
          $found_new_qty = intval($found_item_quantity) + intval($item_quantity);
          WC()->cart->remove_cart_item( $found_item_id );
          WC()->cart->cart_contents[$item_id]['quantity'] = $found_new_qty;
          WC()->cart->set_session();
        }
      }
      
      $tmp_cart_item_exist[] = array(
        "product_id"=>$product_id,
        "item_quantity" => $item_quantity,
        "item_id" => $item_id
      );
      WC()->cart->set_session();
    }
    foreach (WC()->cart->get_cart() as $item_id => $item) {
      
      $cart_item_product_name = $item['data']->get_name();
      $match_dest = array();
      $dest = array();
      
      $product_id = $item['variation_id'] ?: $item['product_id'];
      $item_quantity = $item['quantity'];
    $dis_unit = get_option("wcmlim_show_location_distance");
    $google_api_key = get_option("wcmlim_google_api_key");

    $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($prepare_destination_address_string) . '&sensor=false&key=' . $google_api_key,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
      ));
      $geocode = curl_exec($curl);
      $output = json_decode($geocode);
      curl_close($curl);
      if (isset($output->results[0]->geometry->location->lat)) {
        $shipping_latitude = $output->results[0]->geometry->location->lat;
        $shipping_longitude = $output->results[0]->geometry->location->lng;
      } else {
        $shipping_latitude = 0;
        $shipping_longitude = 0;
      }
      
    $latlngarr = array('latitude'=>$shipping_latitude,'longitude'=>$shipping_longitude);

    $isExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($isExcLoc)) {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isExcLoc));
    } else {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }
    $prepare_validation_msg = '';
    foreach ($terms as $in => $term) {
     
      $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
      $postmeta_backorders_product = get_post_meta($product_id, '_backorders', true);
      $term_meta = get_option("taxonomy_$term->term_id");
      $term_address = $this->wcmlim_get_locations_address($term->term_id);
      $loc_lat = get_term_meta( $term->term_id, 'wcmlim_lat', true );
			$loc_lng = get_term_meta( $term->term_id, 'wcmlim_lng', true );
      $termid = $term->term_id;
      $streetNumber = (get_term_meta($termid, 'wcmlim_street_number', true)) ? get_term_meta($termid, 'wcmlim_street_number', true).' ,' : '';
      $route = (get_term_meta($termid, 'wcmlim_route', true)) ? get_term_meta($termid, 'wcmlim_route', true).' ,' : '';
      $locality = (get_term_meta($termid, 'wcmlim_locality', true)) ? get_term_meta($termid, 'wcmlim_locality', true).' ,' : '';
      $state = (get_term_meta($termid, 'wcmlim_administrative_area_level_1', true)) ? get_term_meta($termid, 'wcmlim_administrative_area_level_1', true).' ,' : '';
      $postal_code = (get_term_meta($termid, 'wcmlim_postal_code', true)) ? get_term_meta($termid, 'wcmlim_postal_code', true).' ,' : '';
      $country = (get_term_meta($termid, 'wcmlim_country', true)) ? get_term_meta($termid, 'wcmlim_country', true) : '';
        $address = $streetNumber . $route . $locality . $state . $postal_code . $country;
        $find_address = $streetNumber .'+'. $route .'+'. $locality .'+'. $state .'+'. $postal_code .'+'. $country;
        $address = str_replace(' ', '+', $find_address);
        $address = str_replace(',', '+', $find_address);
        $getlatlng = wcmlim_get_lat_lng($address, $termid);
        $wcmlim_available_loc_stock = 0;
        $wcmlim_available_loc_stock = 1;

if(($postmeta_stock_at_term < 1) && ($postmeta_backorders_product == "yes"))
{
    $match_dest[] = array(
      "locationname" => $term->name,
      "address" => $term_address,
      "termid" => $termid,
      "termkey" => $in,
      "loc_lat" => $loc_lat,
      "loc_lng" => $loc_lng
    );
    $dest[] = $term_address;
  }
  if($postmeta_stock_at_term > 0)
{
    $match_dest[] = array(
      "locationname" => $term->name,
      "address" => $term_address,
      "termid" => $termid,
      "termkey" => $in,
      "loc_lat" => $loc_lat,
      "loc_lng" => $loc_lng
    );
    $dest[] = $term_address;
  }
}
   
     //max qty notice execution
  if(!empty($prepare_validation_msg))
  {
    wc_clear_notices();
    wc_add_notice(__($prepare_validation_msg,'wcmlim'), 'error');
  }

    //call distance matrix api
    $stockmanagment = get_post_meta($product_id, '_manage_stock', true);

    if($coordinates_calculator == '')
    {
    $destination = implode("|", $dest);
    if(empty($destination) && $stockmanagement == "yes")
      {
        if ($stockmanagement == "yes") {
          wc_clear_notices();
          wc_add_notice(__('The Item added to cart does not available at your shipping location, you need to remove a product - <b>'.$cart_item_product_name.'</b> from your cart!','wcmlim'), 'error');
      }
      }else{
            $distance = $this->get_nearby_distance_by_api($dis_unit,$prepare_destination_address_string,$destination,$google_api_key);
           
            if(is_array($distance))
            {
              usort($distance, function (array $a, array $b) {
                $adistance = floatval($a['value']);
                $bdistance = floatval($b['value']); 
                return floatval($adistance) <=> floatval($bdistance);
            });

              $this->wcmlim_nearby_stock_adjustment($product_id,$match_dest,$distance,$item_id);
            }
          }
    }else{
      if(empty($match_dest))
      {
        wc_clear_notices();
        wc_add_notice(__('The Item added to cart does not available at your shipping location, you need to remove a product - <b>'.$cart_item_product_name.'</b> from your cart!','wcmlim'), 'error');
      }
      else
      {
        $coordinates_prepare_result = array();
        foreach($match_dest as $key => $value)
        {
        
          $each_loc_lat = $value['loc_lat'];
          $each_loc_lng = $value['loc_lng'];
          $distance = distance_between_coordinates($shipping_latitude, $shipping_longitude, $each_loc_lat, $each_loc_lng);
            $coordinates_prepare_result[] = array(
            "locationname" => $value['locationname'],
            "address" => $value['address'],
            "termid" => $value['termid'],
            "termkey" => $value['termkey'],
            "loc_lat" => $value['loc_lat'],
            "loc_lng" => $value['loc_lng'],
            "distance" => $distance
          ); 
        }
        if(is_array($coordinates_prepare_result))
        {
          usort($coordinates_prepare_result, function (array $a, array $b) {
            $adistance = floatval($a['distance']);
            $bdistance = floatval($b['distance']); 
            return $adistance <=> $bdistance;
        });
            $this->wcmlim_nearby_stock_adjustment($product_id,$match_dest,$coordinates_prepare_result,$item_id);
        
        }       
        }    
    }             
  }
  return 0;
  wp_die();
}


public function wcmlim_nearby_stock_adjustment($product_id,$match_dest,$distance,$item_id)
{
  if(isset($distance[0]["termkey"]))
  {
    $dis_first_key = $distance[0]['termkey'];
    $dis_first_location = $distance[0]['locationname'];
  }
  else
  {
    $dis_first_key = $distance[0]['key'];
    $dis_first_location = $distance[0]['location'];
  }

  $fulfillment_stock_array = $distance;
  $nearby_first_matching_array = $match_dest[$dis_first_key];
  $nearby_first__matching_termid = $nearby_first_matching_array['termid'];
  $nearby_first__matching_termkey = $nearby_first_matching_array['termkey'];
  $nearby_first__matching_locationname = $nearby_first_matching_array['locationname'];
  $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $nearby_first__matching_termid, true);
  $cart_item_quantity = WC()->cart->cart_contents[$item_id]["quantity"];
  $postmeta_backorders_product = get_post_meta($product_id, '_backorders', true);
  if($postmeta_backorders_product == "yes")
    {

      WC()->cart->cart_contents[$item_id]['select_location']['location_name'] = $nearby_first__matching_locationname;
      WC()->cart->cart_contents[$item_id]['select_location']['location_key'] = $nearby_first__matching_termkey;
      WC()->cart->cart_contents[$item_id]['select_location']['location_termId'] = $nearby_first__matching_termid;
      WC()->cart->cart_contents[$item_id]['quantity'] = $cart_item_quantity;
      WC()->cart->set_session();
      wc_clear_notices();
  }
  else if(intval($cart_item_quantity) <= intval($postmeta_stock_at_term))
  {
    WC()->cart->cart_contents[$item_id]['select_location']['location_name'] = $nearby_first__matching_locationname;
    WC()->cart->cart_contents[$item_id]['select_location']['location_key'] = $nearby_first__matching_termkey;
    WC()->cart->cart_contents[$item_id]['select_location']['location_termId'] = $nearby_first__matching_termid;
    WC()->cart->set_session();
    wc_clear_notices();
  }
  else
  {
      $updated_cart_item_quantity =  intval($cart_item_quantity) - intval($postmeta_stock_at_term);
      unset($fulfillment_stock_array[0]);
      WC()->cart->cart_contents[$item_id]['select_location']['location_name'] = $nearby_first__matching_locationname;
      WC()->cart->cart_contents[$item_id]['select_location']['location_key'] = $nearby_first__matching_termkey;
      WC()->cart->cart_contents[$item_id]['select_location']['location_termId'] = $nearby_first__matching_termid;
      WC()->cart->cart_contents[$item_id]['quantity'] = $postmeta_stock_at_term;
      WC()->cart->set_session();
      wc_clear_notices();
        $skip_term_id[] = $nearby_first__matching_termid;
        $this->wcmlim_nearby_stock_adjustment_with_priority($product_id,$match_dest,$fulfillment_stock_array,$item_id,$updated_cart_item_quantity,$skip_term_id);      
  }
  return 0;
  wp_die(); 
}

public function wcmlim_nearby_stock_adjustment_with_priority($product_id,$match_dest,$fulfillment_stock_array,$item_id,$updated_cart_item_quantity,$skip_term_id)
{
  $prepare_priority_loc = array();
  $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
  if(!empty($locations)){
    foreach($locations as $key=>$term){
      if(!in_array($term->term_id, $skip_term_id))
      {
        $get_priority = get_term_meta($term->term_id, 'wcmlim_location_priority', true); // get priotity number
        $prepare_priority_loc[$get_priority] = 
        array(
          "up_termid" => $term->term_id,
          "up_key" => $key,
          "up_name" => $term->name,

        );
      }
    }
  }

  array_filter($prepare_priority_loc);
  ksort($prepare_priority_loc);
  $carry_stock = 0;
  if(!empty($prepare_priority_loc))
  {
    foreach($prepare_priority_loc as $priority_key=>$priority_value){
      $get_priority_term_id = $priority_value['up_termid'];
      $get_priority_term_key = $priority_value['up_key'];
      $get_priority_term_name = $priority_value['up_name'];
      $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $get_priority_term_id, true);
      $get_location_data = $this->get_location_array_for_cart_add($get_priority_term_name, $get_priority_term_id, $updated_cart_item_quantity, $get_priority_term_key);
      $postmeta_backorders_product = get_post_meta($product_id, '_backorders', true);
      if($postmeta_backorders_product == "yes")
      {
        WC()->cart->add_to_cart($product_id, $updated_cart_item_quantity, '0', array(), $get_location_data);
        wc_clear_notices();
        return 0;
        wp_die(); 
      }
      else if(intval($updated_cart_item_quantity) <= intval($postmeta_stock_at_term))
      {
        WC()->cart->add_to_cart($product_id, $updated_cart_item_quantity, '0', array(), $get_location_data);
        wc_clear_notices();
        return 0;
        wp_die(); 
      }
      else
      {
        $carry_stock = intval($updated_cart_item_quantity) - intval($postmeta_stock_at_term);
        WC()->cart->add_to_cart($product_id, $postmeta_stock_at_term, '0', array(), $get_location_data);
        wc_clear_notices();
        $skip_term_id[] = $get_priority_term_id; 
        $this->wcmlim_nearby_stock_adjustment_with_priority($product_id,$match_dest,$fulfillment_stock_array,$item_id,$carry_stock,$skip_term_id);
      } 
    }    
  }   
}

public function get_location_array_for_cart_add($product_location, $product_location_termid, $product_location_qty, $product_location_key)
{
  $_location_data = array();
  $_location_data['select_location']['location_name'] = $product_location;
  $_location_data['select_location']['location_key'] = (int)$product_location_key;
  $_location_data['select_location']['location_qty'] = (int)$product_location_qty;
  $_location_data['select_location']['location_termId'] = (int)$product_location_termid;
  return $_location_data;
}

public function get_nearby_distance_by_api($dis_unit,$prepare_destination_address_string,$destination,$google_api_key)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://maps.googleapis.com/maps/api/distancematrix/json?units=metrics&origins=" . urlencode($prepare_destination_address_string) . "&destinations=" . urlencode($destination) . "&key={$google_api_key}",
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
        $response_array["message"] = $response_arr->error_message;
        $response_array["status"] = "false";
    }
    foreach ($response_arr->rows as $r => $t) {
      foreach ($t as $key => $value) {
        $res_inc=0;
            foreach ($value as $a => $b) {
              $res_location = $response_arr->destination_addresses[$res_inc];
                if ($b->status == "OK") {
                    $dis = explode(" ", $b->distance->text);
                    $plaindis = str_replace(',', '', $dis[0]);
                    if ($dis_unit == "kms") {
                        $dis_in_un = $b->distance->text;
                        $dis = $dis_in_un;
                    } elseif ($dis_unit == "miles") {
                        $dis_in_un = round($plaindis * 0.621, 1) . ' miles';
                        $dis = round($plaindis * 0.621, 1);
                    } elseif ($dis_unit == "none") {
                      $dis_in_un = $b->distance->text;
                      $dis = $dis_in_un;
                    }
                    $distance[] = array("value" => $plaindis, "location" => $res_location, "key" => $a, "distance" => $dis, "dis_in_un" => $dis_in_un);
                }
                $res_inc++;
            }
        }
    }
    if(isset($distance)){
    return $distance;
  }
}

  public function getLocationServiceRadius($distanceKey){
    if(empty($distanceKey)){
      return;
    }

		$ExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
		if (!empty($ExcLoc)) {
			$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExcLoc));
		} else {
			$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
		}
		
    if (!empty($terms)) {
      foreach ($terms as $key => $value) {
        if($distanceKey == $key){
          $_locRadius = 	get_term_meta( $value->term_id, 'wcmlim_service_radius_for_location', true );
        }
      }
    }
		return $_locRadius;
	}

  public function sendnotification_formanager($term_id,$order_id) {

    //get setting store manager email
    $store_manager = get_term_meta($term_id, 'wcmlim_shop_manager', true);

    if(empty($term_id) || empty($order_id)){
      return;
    }
    $id = $term_id;
    $order = wc_get_order($order_id);
    $wcmlim_emailadd = get_term_meta($id, 'wcmlim_email', true);
    $shop_manager = get_term_meta($id, 'wcmlim_shop_manager', true);
    if ($shop_manager) {
      $author_id = 	$shop_manager[0];
      $author_obj = get_user_by('id', $author_id);
      $author_email = $author_obj->user_email;	
      $wcmlim_email = $author_email . ", " . $wcmlim_emailadd;		
    } else {
      $wcmlim_email = $wcmlim_emailadd;
    }

    if (isset($wcmlim_email) && !empty($wcmlim_email)) {
      $currency = get_woocommerce_currency_symbol(); 
      $order_data = $order->get_data();
      $shipping_first_name = $order->get_billing_first_name();
      $shipping_last_name = $order->get_billing_last_name();
      $shipping_company = $order->get_billing_company();
      $shipping_address_1 = $order->get_billing_address_1();
      $shipping_address_2 = $order->get_billing_address_2();
      $shipping_postcode = $order->get_billing_postcode();
      $shipping_city = $order->get_billing_city();
      $shipping_state = $order->get_billing_state();
      $shipping_country = $order->get_billing_country();
      $shipping_email = $order->get_billing_email();
      $shipping_phone = $order->get_billing_phone();
      $payment_method = (WC_HPOS_IS_ACTIVE) ? $order->get_meta("_payment_method", true) : get_post_meta($order_id, '_payment_method', true);
      $sURL    = site_url();
      $orderdate    = $order->get_date_created();


      $islimitenable = get_option('wcmlim_clear_cart');
      if ($islimitenable == "on") {
          $TotalPrice    = $currency . "" . number_format($order->get_total(), 2, '.', '');
      } else {
          $total=0;
          foreach ($order->get_items() as $item) 
          {
            $item_TermId = $item->get_meta('_selectedLocTermId', true);
            if ( $item_TermId == $id ) {
                $price = $item->get_subtotal();    
                $total+= $price;
            }
          }
          $TotalPrice    = $currency . "" . number_format($total, 2, '.', '');
      }

      $to = $wcmlim_email;
      $termID_val = get_term( $id );
      $item_LocName = $termID_val->name;
      $subject = "You have received location order";
      $message = '<table border="0" cellpadding="0" cellspacing="0" width="600" id="m_7726384717555498504template_container" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px"><tbody><tr>
                      <td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="100%" id="m_7726384717555498504template_header" style="background-color:#96588a;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;border-radius:3px 3px 0 0"><tbody><tr>
                      <td id="m_7726384717555498504header_wrapper" style="padding:36px 48px;display:block">
                      <h1 style="font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:left;color:#ffffff;background-color:inherit">Warehouse Received Order</h1></td>
                      </tr></tbody></table></td></tr><tr><td align="center" valign="top"><table border="0" cellpadding="0" cellspacing="0" width="600" id="m_7726384717555498504template_body"><tbody><tr><td valign="top" id="m_7726384717555498504body_content" style="background-color:#ffffff">
                      <table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
                      <td valign="top" style="padding:48px 48px 32px">
                      <div id="m_7726384717555498504body_content_inner" style="color:#636363;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left">
                      <p style="margin:0 0 16px">Hi ' . $item_LocName . ',</p><p style="margin:0 0 16px">Just to let you know — we have received your order ' . $order_id . ':</p><p style="margin:0 0 16px">Payment Via ' . $payment_method . '.</p>
                      <h2 style="color:#96588a;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">[Order #' . $order_id . '] (' . $orderdate . ')</h2>
                      <div style="margin-bottom:40px">
                      <table cellspacing="0" cellpadding="6" width="100%" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif"><thead><tr>
                      <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Product</th>
                      <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Quantity</th>
                      <th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Price</th></tr></thead>										
                      <tbody>';
                      foreach ($order->get_items() as $item) {
                        $item_TermId = $item->get_meta('_selectedLocTermId', true);

                        if (!$item->is_type('line_item')) {
                          continue;
                        }
                        if ( $item_TermId == $id ) {
                          $quty       = apply_filters('woocommerce_order_item_quantity', $item->get_quantity(), $order, $item); //hve
                          
                          $itemname = $item->get_name();
                          $product_data = $item->get_product();
                        
                          $price = $item->get_subtotal();
                          $price = $currency . "" . number_format($price, 2, '.', '');//hve
                          $itemSelLocName = $item->get_meta('Location', true);
                          $message .=  '<tr>
                          <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word">
                            ' . $itemname . '
                          </td>
                          <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif">
                          ' . $quty . '
                          </td>
                          <td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif">
                            <span>' . $price . '</span>
                          </td>
                          </tr>';
                        }
                      }	

                      $message .= '</tbody>										
                      <tfoot><tr></tr><tr><th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Payment Method:</th>
                      <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $payment_method . '</td></tr>										
                      
                      <tr>
                      <th scope="row" colspan="2" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Total Payment:</th>
                      <td style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><span>' . $TotalPrice . '</span></td>
                      </tr>

                      </tfoot>
                      </table></div>
                                        <table id="m_7726384717555498504addresses" cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;margin-bottom:40px;padding:0"><tbody><tr></tr></tbody></table><p style="margin:0 0 16px">Thanks for using <a href="' . $sURL . '" target="_blank">' . $sURL . '</a>!</p></div><table id="m_4333498424916750933addresses" cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;margin-bottom:40px;padding:0"><tbody><tr>
                      <td valign="top" width="50%" style="text-align:left;font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;border:0;padding:0"><h2 style="color:#96588a;display:block;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">Shipping address</h2>
                      <address style="padding:12px;color:#636363;border:1px solid #e5e5e5">
                      ' . $shipping_first_name . ' &nbsp; ' . $shipping_last_name . '<br>' . $shipping_address_1 . ' &nbsp; ' . $shipping_address_2 . '<br>' . $shipping_city . ',' . $shipping_state . '&nbsp;' . $shipping_postcode . '<br>' . $shipping_country . '<br>
                      ' . $shipping_phone . ',' . $shipping_email . '<br></address></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
      $fromemail = get_bloginfo('admin_email');
      $headers = "MIME-Version: 1.0" . "\r\n";
      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
      $headers .= 'From: Order Received <' . $fromemail . '>' . "\r\n";

      wp_mail($to, $subject, $message, $headers);
    }
  }

  //* Hide Order Item Meta
  public function wcmlim_hidden_order_itemmeta($args)
  {
    $args[] = '_selectedLocationKey';
    $args[] = '_selectedLocTermId';
    return $args;
  }

  //* Second Nearest Location if Nearest Location is Out Of Stock  
  public function getSecondNearestLocation($addresses, $dis_unit, $product_id)
  {
    if (empty($addresses) || empty($dis_unit) ) {
      return;
    }

    $ExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
    if (!empty($ExcLoc)) {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExcLoc));
    } else {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    }

    foreach ($addresses as $ad) {
      $dnumber[] = $ad["value"];
    }
    sort($dnumber, SORT_NUMERIC);
    $smallest = array_shift($dnumber);
    $smallest_2nd = array_shift($dnumber);
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
 
  public function wcmlim_shipping_error( $fields, $errors )
  {
    global $woocommerce;
    $cart = $woocommerce->cart->cart_contents;
    $shipping_packages =  WC()->cart->get_shipping_packages();
    $shipping_zone = wc_get_shipping_zone( reset( $shipping_packages ) );
    $zone_id   = $shipping_zone->get_id(); 
    $zone_name = $shipping_zone->get_zone_name(); 
    $isShippingMethods = get_option('wcmlim_enable_shipping_methods');
    if ($isShippingMethods == "on") 
    {
      $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
      foreach ($cart as $array_item) {
        $product = wc_get_product( $array_item['product_id'] );
        $product_name = $product->get_title();
        $zone_found =  0;
        if ( $product->is_type( 'variable' ) ) {
          $product_id = $array_item['variation_id'];
        }
        if ($product->is_type( 'simple' )) {
          $product_id = $array_item['product_id'];
        }
       foreach ($terms as $term) {
          $wcmlim_shipping_zone = get_term_meta($term->term_id, 'wcmlim_shipping_zone', true);
          $stock_at_loc = get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true);
          if(in_array($zone_id,$wcmlim_shipping_zone) && ($stock_at_loc > 0 && $stock_at_loc != '')){
            $zone_found = $zone_found +1;
          }    
        }

        $product = wc_get_product( $product_id );
          $manage_stock = $product->get_manage_stock();
          if($manage_stock != false) {
            if ($zone_found == 0) {
              wc_clear_notices();
              $errors->add( 'validation', 'Cart item <b> '.$product_name.' </b> could not be delivered in shipping zone' );
            }
          }
      }
    }
  }

  
  public function wcmlim_fulfil_order_shipping_zones($order_id)
  {
    if(empty($order_id)){
      return;
    }

    $order =  wc_get_order($order_id);
    $wcmlim_order_placed_once = (WC_HPOS_IS_ACTIVE) ? $order->get_meta("wcmlim_order_placed_once", true) : get_post_meta($order_id, "wcmlim_order_placed_once", true);
    if($wcmlim_order_placed_once == 'yes'){
      return;
    }
    
    foreach ($order->get_shipping_methods() as $shipping_item_id => $shipping_item) {
      $order_data = array(
        'id' => $shipping_item_id,
        'instance_id' =>  $shipping_item['instance_id'],
        'method_id' => $shipping_item['method_id'],
        'method_title' => $shipping_item['name'],
      );
    }

    $rate_id = $order_data['method_id'] . ':' . $order_data['instance_id'];
    // Get the zone name
    $zone_id = $this->get_shipping_zone_from_method_rate_id($rate_id);
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
    if (!empty($terms)) {
      foreach ($terms as $term) {
        $wcmlim_shipping_zone = get_term_meta($term->term_id, 'wcmlim_shipping_zone', true);
        // start cond
        
        if (in_array($zone_id, $wcmlim_shipping_zone)) {
          foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_variation_id() ?: $item->get_product_id();
            $item_quantity = $item->get_quantity();
            $postmeta_stock_at_term = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
            $loc_item_meta = wc_get_order_item_meta($item_id, 'Location');
            
            if ((!empty($postmeta_stock_at_term) || ($postmeta_stock_at_term > 0)) && $loc_item_meta == '') {
              
              //get order item meta location
              $term_data_name = $item->get_meta('Location', true);
              if($term_data_name != $term->name)
                wc_add_order_item_meta($item_id, "Location", $term->name);
              
              wc_add_order_item_meta($item_id, "_selectedLocationKey", $in);
              wc_add_order_item_meta($item_id, "_selectedLocTermId", $term->term_id);
              $parentTerms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
              if (!empty($parentTerms)) {
                foreach ($parentTerms as $parentTerm) {
                  $stockInParentLocation[$parentTerm->term_id] = get_post_meta($product_id, "wcmlim_stock_at_{$parentTerm->term_id}", true);
                }
                $parentValue = max($stockInParentLocation);
                $parentKey = array_search($parentValue, $stockInParentLocation);
                if ($parentKey) {
                  $maxStockAtSub = get_post_meta($product_id, "wcmlim_stock_at_{$parentKey}", true);
                  update_post_meta($product_id,  "wcmlim_stock_at_{$parentKey}", $maxStockAtSub - $item_quantity);
                }
                if(WC_HPOS_IS_ACTIVE){
                  $order->update_meta_data("wcmlim_order_placed_once", 'yes');
                }else{
                  update_post_meta($order_id, 'wcmlim_order_placed_once', 'yes');
                }
              }
              update_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, $postmeta_stock_at_term - $item_quantity);
              $stock_reduce_location = $postmeta_stock_at_term - $item_quantity;
              $note = "{ Stock levels reduced: {$item->get_name()}  from Location: {$term->name} {$postmeta_stock_at_term} &rarr; {$stock_reduce_location} }";

               
              $order->add_order_note($note);
              $this->wc_reduce_stock_levels($order);
              //For-Manager Records 
              $id = $term->term_id;
              $item_storeid = array();
              if(!in_array( $id, $item_storeid ) ) {
                $item_storeid[] = $id;
              }
              $dataLocate = array();
              if(!empty($item_storeid)){
                foreach($item_storeid as $wcmlim_item_id) {
                  $term_object = get_term( $wcmlim_item_id );
                  $dataLo = $term_object->term_id;										
                  $dataLocate[] = $dataLo;
                }
              }

              if (!empty($dataLocate)) {
                if(WC_HPOS_IS_ACTIVE){
                  $order->update_meta_data( '_multilocation', $dataLocate );
                }else{
                  update_post_meta($order_id, "_multilocation", $dataLocate);
                }
              }

              if (!empty($term->name)) {
                if(WC_HPOS_IS_ACTIVE){
                  $order->update_meta_data( '_location', $term->name );
                }else{
                  update_post_meta($order_id, "_location", $term->name);
                }
              }

              // Email notified;
              $this->sendnotification_formanager($id, $order_id); 
              $this->wcmlim_update_totaldata($product_id);
            } 
          }
        }
      }
      $order->save();
    }
  } 


  public function wc_reduce_stock_levels($order_id)
	{
    
		if (is_a($order_id, 'WC_Order')) {
			$order    = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order($order_id);
		}

		$changes = array();
		$item_mail = array();
		$url = $_SERVER['REQUEST_URI']; 
		$key = 'wp-json/wc-pos';
    $wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');
		if (($wc_pos_compatiblity1 == "on") && (in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins')))) && (strpos($url, $key) != false)) { 
										
			$blogURL = get_bloginfo('url');
			$referer = $_SERVER['HTTP_REFERER'];
			$referer = str_replace($blogURL, "", $referer);
			$refe = explode("/", $referer);
			foreach($refe as $r => $s) {
				if($s == "point-of-sale")
				{
					$outletSlug = $refe[$r+1];
					$registerSlug = $refe[$r+2];
				}
			}
			
			$rargs = array(
				'post_type' => 'pos_register',
				'name' => $registerSlug,
				'post_status' => 'publish',
				'fields' => 'ids',
			);

			$registerPOST = get_posts($rargs);
			$regPostID = $registerPOST[0];
			$assignedOutletID = get_post_meta($regPostID, 'outlet', true);
			
			global $wpdb;
			$termMetaTable = $wpdb->prefix . 'termmeta';
			$getTermID = $wpdb->get_results("SELECT term_id FROM $termMetaTable WHERE meta_key = 'wcmlim_wcpos_compatiblity' AND meta_value = $assignedOutletID;");
			
			$exclExists = get_option("wcmlim_exclude_locations_from_frontend");
			if (!empty($exclExists)) {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $exclExists));
			} else {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
			}

			foreach ($order->get_items() as $item) {
				if (!$item->is_type('line_item')) {
					continue;
				}

				// Only reduce stock once for each item.
				$product            = $item->get_product();
				$product_id = $product->get_id();
				$item_stock_reduced = $item->get_quantity();

				// *WooCommerce Point Of Sale by Actuality Extensions compatibility

				foreach ($getTermID as $key => $value) {
					$foundTermID= $value->term_id;
			
				if (!$product || !$product->managing_stock()) {
					continue;
				}

				$qty       = apply_filters('woocommerce_order_item_quantity', $item->get_quantity(), $order, $item);
				$item_name = $product->get_formatted_name();
			
				$order = wc_get_order( $order_id ); 
				$item->add_meta_data('Location',  $value->name);
				$item->add_meta_data('_selectedLocationKey', $key);
				$item->add_meta_data('_selectedLocTermId', $value->term_id);
				$item->add_meta_data('_wcmlim_reduced_stock', $qty, true);
				$item->save();
				$this->wcpos_reduce_product_stock($product_id, $qty, $foundTermID, $order);
			}
			}
		}
		else{
		foreach ($order->get_items() as $item) {
			
			if (!$item->is_type('line_item')) {
				continue;
			}

			// Only reduce stock once for each item.
			$product            = $item->get_product();
			$item_stock_reduced = $item->get_meta('_reduced_stock', true);
			

				$item_selectedLocation_key = $item->get_meta('_selectedLocationKey', true);
					$itemSelLocTermId = $item->get_meta('_selectedLocTermId', true);
          
				$itemSelLocName = $item->get_meta('Location', true);
				$dataLocation = $item->get_meta('Location', true);
				
      if($itemSelLocTermId == '') {
        return;
      }
			$selLocQty = get_post_meta($product->get_id(), "wcmlim_stock_at_{$itemSelLocTermId}", true);

			if ($item_stock_reduced || !$product || !$product->managing_stock()) {
				continue;
			}

			$qty       = apply_filters('woocommerce_order_item_quantity', $item->get_quantity(), $order, $item);


			// *WooCommerce Point Of Sale by Actuality Extensions compatibility
			$wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');
			
			if (($wc_pos_compatiblity1 == "on") && (in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins'))))) { 
			  //now we have to get the outlet id and update the stock if wc pos is active
        $outlet_id = get_term_meta($itemSelLocTermId , 'wcmlim_wcpos_compatiblity', true);
        $new_outlet_stock = wc_pos_update_product_outlet_stock( $product , array( $outlet_id => $qty ), 'decrease' );
			}

			$item_name = $product->get_formatted_name();
			$new_stock = $this->wc_update_product_stock($product, $qty, 'decrease', false, $item_selectedLocation_key);

			if (is_wp_error($new_stock)) {
				/* translators: %s item name. */
				$order->add_order_note(sprintf(_e('Unable to reduce stock for item %s.', 'woocommerce'), $item_name));
				continue;
			}
			
			$item->add_meta_data('_reduced_stock', $qty, true);
			$item->save();

			$changes[] = array(
				'product' => $product,
				'from'    => intval($new_stock) + intval($qty),
				'to'      => $new_stock,
			);

			$locChanges[] = array(
				'product' 	=> $product,
				'location'	=> $itemSelLocName,
				'from'    	=> $selLocQty,
				'to'      	=> intval($selLocQty) - intval($qty),
			);
			//send Mail
			if(!in_array( $itemSelLocTermId, $item_mail ) ) {
				$item_mail[] = $itemSelLocTermId;
			}
		}
		$dataLocate = array();
		$wcmlim_assign_location_shop_manager = get_option('wcmlim_assign_location_shop_manager');
    if($wcmlim_assign_location_shop_manager != 'on'){
      $item_mail = array();
    }
    if(!empty($item_mail)){
      foreach($item_mail as $wcmlim_email_val) {
        $wcmlim_emailadd = get_term_meta($wcmlim_email_val, 'wcmlim_email', true);
        $shop_manager = get_term_meta($wcmlim_email_val, 'wcmlim_shop_manager', true);
        $term_object = get_term( $wcmlim_email_val );
        $dataLo = $term_object->term_id;										
        $dataLocate[] = $dataLo;
        if ($shop_manager) {
          $author_id = 	$shop_manager[0];
          $author_obj = get_user_by('id', $author_id);
          $author_email = $author_obj->user_email;	
          if($wcmlim_emailadd) {
            $wcmlimemail = $author_email . ", " . $wcmlim_emailadd;	
          }  else {
            $wcmlimemail = $author_email;	
          }												
        } else {
          $wcmlimemail = $wcmlim_emailadd;
        }
        /*Regional code */
        $regM = get_term_meta($wcmlim_email_val, "wcmlim_locator", true);
        $regM2 = get_term_meta($regM, "wcmlim_shop_regmanager", true);
        $wcmlim_regemail = get_term_meta($regM, 'wcmlim_email_regmanager', true);
        if ($regM2) {
          $authorid = 	$regM2[0];
          $authorobj = get_user_by('id', $authorid);
          $authoremail = $authorobj->user_email;	
            if($wcmlim_regemail) {
              $regwcmlimemail = $authoremail . ", " . $wcmlim_regemail;		
            } else {
              $regwcmlimemail = $authoremail;		
            }	
        } else {
          $regwcmlimemail = $wcmlim_regemail;
        }
        
        if($regwcmlimemail) {
          $wcmlim_email = $regwcmlimemail . ", " . $wcmlimemail;		
        } else {
          $wcmlim_email = $wcmlimemail;		
        }	
        
        if (isset($wcmlim_email) && !empty($wcmlim_email)) {
          include(plugin_dir_path(dirname(__FILE__)) . 'public/partials/email-template.php');
        }
      }
    }

		//update multiloc
    if (!empty($dataLocate)) {
      if(WC_HPOS_IS_ACTIVE){
        $order->update_meta_data( '_multilocation', $dataLocate );
      }else{
        update_post_meta($order_id, "_multilocation", $dataLocate);
      }
    }

		$wordCount = explode(" ", $dataLocation);
		if (count($wordCount) > 1) {
			$_location = str_replace(' ', '-', strtolower($dataLocation));
		} else {
			$_location = $dataLocation;
		}

		if (preg_match('/"/', $_location)) {
			$_location = str_replace('"', '', $_location);
		}
    if(!empty($_location)){
      if(WC_HPOS_IS_ACTIVE){
        $order->update_meta_data( '_location', $_location );
      }else{
        update_post_meta($order_id, "_location", $_location);
      }
    }    

		$this->wc_trigger_stock_change_notifications($order, $changes, $locChanges);

		do_action('woocommerce_reduce_order_stock', $order);
		}
    $order->save();
	}

  public function wc_trigger_stock_change_notifications($order, $changes, $locChanges)
	{
		if (empty($changes)) {
			return;
		}
		$order_notes     = array();
		$no_stock_amount = absint(get_option('woocommerce_notify_no_stock_amount', 0));

		foreach ($changes as $change) {
			$order_notes[]    = $change['product']->get_formatted_name() . ' ' . $change['from'] . '&rarr;' . $change['to'];
			$low_stock_amount = absint($this->wc_get_low_stock_amount(wc_get_product($change['product']->get_id())));
			if ($change['to'] <= $no_stock_amount) {
				do_action('woocommerce_no_stock', wc_get_product($change['product']->get_id()));
			} elseif ($change['to'] <= $low_stock_amount) {
				do_action('woocommerce_low_stock', wc_get_product($change['product']->get_id()));
			}

			if ($change['to'] < 0) {
				do_action(
					'woocommerce_product_on_backorder',
					array(
						'product'  => wc_get_product($change['product']->get_id()),
						'order_id' => $order->get_id(),
						'quantity' => abs($change['from'] - $change['to']),
					)
				);
			}
		}

		$order->add_order_note( implode(', ', $order_notes));				

		if (empty($locChanges)) {
			return;
		}

		foreach ($locChanges as $locChange) {										
			$loc_order_notes[]    = "{ Stock levels reduced: {$locChange['product']->get_formatted_name()}  from Location: {$locChange['location']} {$locChange['from']} &rarr; {$locChange['to']} }";
			$low_stock_amount = absint($this->wc_get_low_stock_amount(wc_get_product($locChange['product']->get_id())));
			if ($locChange['to'] <= $no_stock_amount) {
				do_action('woocommerce_no_stock', wc_get_product($locChange['product']->get_id()));
			} elseif ($locChange['to'] <= $low_stock_amount) {
				do_action('woocommerce_low_stock', wc_get_product($locChange['product']->get_id()));
			}

			if ($locChange['to'] < 0) {
				do_action(
					'woocommerce_product_on_backorder',
					array(
						'product'  => wc_get_product($locChange['product']->get_id()),
						'order_id' => $order->get_id(),
						'quantity' => intval($locChange['from']) - intval($locChange['to']),
					)
				);
			}
		}
    
    

		$order->add_order_note(implode(', ', $loc_order_notes));
	}

  public function add_order_item_meta($item_id, $cart_item, $cart_item_key)
  {
    if (isset($cart_item['select_location'])) {
      $values =  array();
      foreach ($cart_item['select_location'] as $key => $value) {
        $values[$key] = $value;
      }
      wc_add_order_item_meta($item_id, "Location", $values["location_name"]);
      wc_add_order_item_meta($item_id, "_selectedLocationKey", $values["location_key"]);
      wc_add_order_item_meta($item_id, "_selectedLocTermId", $values["location_termId"]);
      setcookie("wcmlim_selected_location", $values["location_key"], time() + 36000, '/');
    }
  }

  public function wcmlim_fulfil_order_nearby_shipping_address($item_id, $cart_item, $cart_item_key)
  {
    $cart_item_key = json_decode($cart_item);
    $cart_product_id = '';
    $cart_item_id = '';
    $product_id = ($cart_item['variation_id'] != 0) ? $cart_item['variation_id'] : $cart_item['product_id'];
    foreach (WC()->cart->get_cart() as $cart_item_id => $cart_item) {  
      $cart_product_id = $cart_item['variation_id'] ?: $cart_item['product_id'];
      $cart_item_id = $cart_item_id;       
      if($cart_product_id == $product_id)
      {
        $location_name =  WC()->cart->cart_contents[$cart_item_id]['select_location']['location_name'];
        $location_key =  WC()->cart->cart_contents[$cart_item_id]['select_location']['location_key'];
        $location_id =  WC()->cart->cart_contents[$cart_item_id]['select_location']['location_termId'];
        wc_add_order_item_meta($item_id, "Location", $location_name);
        wc_add_order_item_meta($item_id, "_selectedLocationKey", $location_key);
        wc_add_order_item_meta($item_id, "_selectedLocTermId", $location_id);
      }
    }   
  }

  public function get_shipping_zone_from_method_rate_id($method_rate_id)
  {
    if (empty($method_rate_id)) {
      return __("Error! doesn't exist…");
    }

    global $wpdb;
    $data = explode(':', $method_rate_id);
    $method_id = $data[0];
    $instance_id = $data[1];

    // The first SQL query
    $zone_id = $wpdb->get_col("
        SELECT wszm.zone_id
        FROM {$wpdb->prefix}woocommerce_shipping_zone_methods as wszm
        WHERE wszm.instance_id = '$instance_id'
        AND wszm.method_id LIKE '$method_id'
    ");
    $zone_id = reset($zone_id); // converting to string
    return $zone_id;
    // 1. Wrong Shipping method rate id
    if (empty($zone_id)) {
      return __("Error! doesn't exist…");
    }
    // 2. Default WC Zone name 
    elseif ($zone_id == 0) {
      return __("All Other countries");
    }
    // 3. Created Zone name  
    else {
      // The 2nd SQL query
      $zone_name = $wpdb->get_col("
            SELECT wsz.zone_name
            FROM {$wpdb->prefix}woocommerce_shipping_zones as wsz
            WHERE wsz.zone_id = '$zone_id'
        ");
      return reset($zone_name); // converting to string and returning the value
    }
  }

  //get location full address by term id
  public function wcmlim_get_locations_address($termid)
  {
    $streetNumber = (get_term_meta($termid, 'wcmlim_street_number', true)) ? get_term_meta($termid, 'wcmlim_street_number', true).' ,' : '';
    $route = (get_term_meta($termid, 'wcmlim_route', true)) ? get_term_meta($termid, 'wcmlim_route', true).' ,' : '';
    $locality = (get_term_meta($termid, 'wcmlim_locality', true)) ? get_term_meta($termid, 'wcmlim_locality', true).' ,' : '';
    $state = (get_term_meta($termid, 'wcmlim_administrative_area_level_1', true)) ? get_term_meta($termid, 'wcmlim_administrative_area_level_1', true).' ,' : '';
    $postal_code = (get_term_meta($termid, 'wcmlim_postal_code', true)) ? get_term_meta($termid, 'wcmlim_postal_code', true).' ,' : '';
    $country = (get_term_meta($termid, 'wcmlim_country', true)) ? get_term_meta($termid, 'wcmlim_country', true) : '';
    return $streetNumber .'+'. $route .'+'. $locality .'+'. $state .'+'. $postal_code .'+'. $country;
  }

	function distance_between_coordinates($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles') {
    $theta = $longitude1 - $longitude2; 
    $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
    $distance = acos($distance); 
    $distance = rad2deg($distance); 
    $dis_unit = get_option("wcmlim_show_location_distance", true);
    $distance = $distance * 60 * 1.1515; 
    $distance = round($distance,2);
    switch($unit) {
      case 'miles':
      $distance = $distance; 
      break; 
      case 'kilometers' : 
      $distance = $distance * 1.609344;
      $distance = $distance;
    } 
    return $distance; 
  }

  //co ordinates dependencies functions
  function wcmlim_get_lat_lng($address, $termid){

    if (empty($address)) {
      return;
    }
    global $latlngarr;
    $api_key = get_option('wcmlim_google_api_key');
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $api_key,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    ));
    $geocode = curl_exec($curl);
    $output = json_decode($geocode);
    curl_close($curl);

    if (isset($output->results[0]->geometry->location->lat)) {
      $latitude = $output->results[0]->geometry->location->lat;
      $longitude = $output->results[0]->geometry->location->lng;
    } else {
      $latitude = 0;
      $longitude = 0;
    }
    update_term_meta($termid, 'wcmlim_lat', $latitude);
    update_term_meta($termid, 'wcmlim_lng', $longitude);

    $latlngarr = array(
      'latitude'=>$latitude,
      'longitude'=>$longitude
    );
    //update term meta lat lng
    return json_encode($latlngarr);
    wp_die();
  }

  //Displays the Selected Location below the Product name in Cart
  function wcmlim_cart_item_name($name, $cart_item, $cart_item_key)
  {
      $max_in_value;
     $max_value_inpl;
        if (isset($cart_item['select_location']['location_name'])) {
      $locescstring = __("Location :", "wcmlim");
      $name .= sprintf('<p>%s</p>', __($locescstring . $cart_item['select_location']['location_name']));
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
    return $name;
  }
}
new Wcmlim_Backend_Only_Mode();

