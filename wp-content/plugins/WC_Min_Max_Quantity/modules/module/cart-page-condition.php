<?php


add_action( 'wcmmq_form_panel_before_message','wcmmq_cart_page_conditions' );

function wcmmq_cart_page_conditions( $saved_data ){
    
    $min_price = isset( $saved_data['cart_price_min'] ) && $saved_data['cart_price_min'] != '' ? $saved_data['cart_price_min'] : '';
    $max_price = isset( $saved_data['cart_price_max'] ) && $saved_data['cart_price_max'] != '' ? $saved_data['cart_price_max'] : '';
    $min_quantity = isset( $saved_data['cart_quantity_min'] ) && $saved_data['cart_quantity_min'] != '' ? $saved_data['cart_quantity_min'] : '';
    $max_quantity = isset( $saved_data['cart_quantity_max'] ) && $saved_data['cart_quantity_max'] != '' ? $saved_data['cart_quantity_max'] : '';
    $step_quantity = isset( $saved_data['cart_quantity_step'] ) && $saved_data['cart_quantity_step'] != '' ? $saved_data['cart_quantity_step'] : '';

    $excluded_products = isset( $saved_data['cart_product_exclude'] ) && $saved_data['cart_product_exclude'] != '' ? $saved_data['cart_product_exclude'] : '';
    $included_products = isset( $saved_data['cart_product_include'] ) && $saved_data['cart_product_include'] != '' ? $saved_data['cart_product_include'] : '';
    
    
    
    ?>
    <div class="wcmmq-section-panel">
        <table class="wcmmq-table cart-page-condition">
            <thead>
                <tr>
                    <th class="wcmmq-inside">
                        <div class="wcmmq-table-header-inside">
                            <h3><?php echo esc_html__( 'Cart Page Conditions', 'wcmmq_pro'); ?></h3>
                        </div>
                        
                    </th>
                    <th>
                        <div class="wcmmq-table-header-right-side"></div>
                    </th>
                </tr>
            </thead>

            <tbody>
                
                <tr class="extra-divider">
                    <td>
                        <div class="wcmmq-form-control">
                            <div class="form-label col-lg-6">
                                <label for=""><?php echo esc_html__( 'Quantity Limit', 'wcmmq_pro'); ?></label>
                            </div>
                            <div class="form-field col-lg-6">

                                <div class="inside-field-collection">
                                    <div class="col-lg-12 inside-form-field form-field">
                                        <label for="cart_quantity_min"><?php echo esc_html__( 'Min', 'wcmmq_pro'); ?> <?php echo esc_html__( '(Optional)', 'wcmmq_pro'); ?></label>  
                                        <input type="number" name="data[cart_quantity_min]" id="cart_quantity_min" value="<?php echo esc_attr( $min_quantity ); ?>" placeholder=<?php echo esc_html__( "Minimum Quantity", 'wcmmq_pro'); ?> >
                                    </div>
                                    <div class="col-lg-12 inside-form-field form-field">
                                        <label for="cart_quantity_max"><?php echo esc_html__( 'Max', 'wcmmq_pro'); ?><?php echo esc_html__( '(Optional)', 'wcmmq_pro'); ?></label>
                                        <input type="number" name="data[cart_quantity_max]" id="cart_quantity_max" value="<?php echo esc_attr( $max_quantity ); ?>" placeholder=<?php echo esc_html__( "Maximum Quantity", 'wcmmq_pro'); ?> >
                                    </div>

                                    <div class="col-lg-12 inside-form-field form-field">
                                        <label for="cart_quantity_step"><?php echo esc_html__( 'Step', 'wcmmq_pro'); ?><?php echo esc_html__( '(Optional)', 'wcmmq_pro'); ?></label>
                                        <input type="number" name="data[cart_quantity_step]" id="cart_quantity_step" value="<?php echo esc_attr( $step_quantity ); ?>" placeholder=<?php echo esc_html__( "Step Quantity", 'wcmmq_pro'); ?> > 
                                    </div>

                                </div>

                                
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="wcmmq-form-info">
                            <?php wcmmq_doc_link('https://codeastrology.com/min-max-quantity/set-conditions-on-cart-page/'); ?>
                        </div> 
                    </td>
                </tr>

                <tr class="extra-divider">
                    <td>
                        <div class="wcmmq-form-control">
                            <div class="form-label col-lg-6">
                                <label><?php echo esc_html__( 'Price Limit', 'wcmmq_pro'); ?></label>
                            </div>
                            <div class="form-field col-lg-6">
                                <div class="inside-field-collection">
                                    <div class="col-lg-12 inside-form-field form-field">
                                        <label for="cart_price_min"><?php echo esc_html__( 'Min', 'wcmmq_pro'); ?><?php echo esc_html__( '(Optional)', 'wcmmq_pro'); ?></label>
                                        <input type="number" name="data[cart_price_min]" id="cart_price_min" value="<?php echo esc_attr( $min_price ); ?>" placeholder=<?php echo esc_html__( "Minimum Price", 'wcmmq_pro'); ?> >
                                    </div>
                                    <div class="col-lg-12 inside-form-field form-field">
                                        <label for="cart_price_max"><?php echo esc_html__( 'Max', 'wcmmq_pro'); ?><?php echo esc_html( '(Optional)', 'wcmmq_pro'); ?></label>
                                        <input type="number" name="data[cart_price_max]" id="cart_price_max" value="<?php echo esc_attr( $max_price ); ?>" placeholder=<?php echo esc_html__( "Maximum Price", 'wcmmq_pro'); ?> >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="wcmmq-form-info">
                            <?php wcmmq_doc_link('https://codeastrology.com/min-max-quantity/set-conditions-on-cart-page/'); ?>
                        </div> 
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="wcmmq-form-control">
                            <div class="form-label col-lg-6">
                                <label for="cart_product_exclude"><?php echo esc_html__( 'Product Exclude', 'wcmmq_pro'); ?></label>
                            </div>
                            <div class="form-field col-lg-6">
                                <input type="text" name="data[cart_product_exclude]" id="cart_product_exclude" value="<?php echo esc_attr( $excluded_products ); ?>" placeholder=<?php echo esc_html__( "Input product Ids.eg: 45,46,50", 'wcmmq_pro'); ?> >
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="wcmmq-form-info">
                            <?php wcmmq_doc_link('https://codeastrology.com/min-max-quantity/exclude-include-products-on-cart-page/'); ?>
                            <p><?php echo esc_html__( 'Insert Products IDs. Use a comma as a separator. (Example: 45,84,5).  Cart conditions will not apply to those products', 'wcmmq_pro' ); ?></p>
                        </div> 
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="wcmmq-form-control">
                            <div class="form-label col-lg-6">
                                <label for="cart_product_include"><?php echo esc_html__( 'Product Include', 'wcmmq_pro'); ?></label>
                            </div>
                            <div class="form-field col-lg-6">
                            <input type="text" name="data[cart_product_include]" id="cart_product_include" value="<?php echo esc_attr( $included_products ); ?>" placeholder=<?php echo esc_html__( "Input product Ids.eg: 45,46,50", 'wcmmq_pro'); ?> >
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="wcmmq-form-info">
                            <?php wcmmq_doc_link('https://codeastrology.com/min-max-quantity/exclude-include-products-on-cart-page/'); ?>
                            <p><?php echo esc_html__( 'Insert Products IDs. Use a comma as a separator. (Example: 45,84,5).  Cart conditions will apply only those products', 'wcmmq_pro'); ?></p> 
                        </div> 
                    </td>
                </tr>
                
            </tbody>
        </table>

    </div>
    <?php
}



/**
 * Based on main config we are trying to validate cart page before going to checkout.
 * 
 * @since 2.0.0
 * 
 * @return bool Where cart page conditions are met ot not.
 */
function wcmmq_cart_page_validation() {
    
    $cart_conditions = get_option( WC_MMQ_KEY );
    $min_price = isset( $cart_conditions['cart_price_min'] ) && !empty( $cart_conditions['cart_price_min'] ) ? floatval( $cart_conditions['cart_price_min'] ) : false;
    $max_price = isset( $cart_conditions['cart_price_max'] ) && !empty( $cart_conditions['cart_price_max'] ) ? floatval( $cart_conditions['cart_price_max'] ) : false;
    $min_quantity = isset( $cart_conditions['cart_quantity_min'] ) && !empty( $cart_conditions['cart_quantity_min'] ) ? floatval( $cart_conditions['cart_quantity_min'] ) : false;
    $max_quantity = isset( $cart_conditions['cart_quantity_max'] ) && !empty( $cart_conditions['cart_quantity_max'] ) ? floatval( $cart_conditions['cart_quantity_max'] ) : false;
    $step_quantity = isset( $cart_conditions['cart_quantity_step'] ) && !empty( $cart_conditions['cart_quantity_step'] ) ? floatval( $cart_conditions['cart_quantity_step'] ) : false;

    $excluded_products = $cart_conditions['cart_product_exclude'] ?? '';
    $included_products = $cart_conditions['cart_product_include'] ?? '';

    $excluded_products = explode( ',', $excluded_products );
    $included_products = explode( ',', $included_products );

    $excluded_products = array_filter($excluded_products,function($item){
        return ! empty( $item );
    });
    $included_products = array_filter($included_products,function($item){
        return ! empty( $item );
    });

    $contents = WC()->cart->get_cart();
    // var_dump($contents);
    $quantiti = 0;
    $totoal_price = 0;

    $include_empty = empty( $included_products );

    $qtys_arr = [];
    foreach( $contents as $cart){

        $product_id = $cart['product_id'];
        if( ! in_array( $product_id, $excluded_products ) && $include_empty ){
            $quantiti += $cart['quantity'];
            $totoal_price += $cart['line_subtotal'];
        }
        
        if( in_array( $product_id, $included_products ) ){
            $quantiti += $cart['quantity'];
            $totoal_price += $cart['line_subtotal'];
        }
        if( ! empty( $cart['product_id'] ) ){
            $qtys_arr[$product_id][] = $cart['quantity'];
        }
        
        
     }
     $qtys_sum_arr = array_map(function($val){
        return  is_array( $val ) ? array_sum($val) : 0;
     },$qtys_arr);

     
     if( is_array( $qtys_sum_arr ) && ! empty( $qtys_sum_arr ) ){
        wc_clear_notices();
        // wc_print_notices();

        $min_total_errrs = 0;
        foreach( $qtys_sum_arr as $pr_id => $pr_qty ){
            if( empty( $pr_qty ) ) continue;

            $title = get_the_title( $pr_id );
            $min_total = get_post_meta($pr_id,'wcmmq_var_quantity_min_total',true);

            $args = [
                'product_name' => $title,
                'vari_total_min_qty' => $min_total,
            ];

            if( $pr_qty < $min_total ){
                $message = sprintf( wcmmq_get_message( 'msg_vari_total_min_qty', false ), $title, $min_total );
                $message = wcmmq_message_convert_replace( $message, $args );
                wc_add_notice( $message, 'error' );
                $min_total_errrs++;

            }
        }

        if($min_total_errrs){
            
            return false;
            
         }
     }

    if( empty( $excluded_products ) && empty( $included_products ) ){
        $cart_total = WC()->cart->subtotal;
        $cart_total_quantity = WC()->cart->get_cart_contents_count();
    }else{
        $cart_total = $totoal_price;
        $cart_total_quantity = $quantiti;
    }

    if( ! $cart_total_quantity ) return true;

    // Need to fix this. ekhon message $step_quantity diye deya ache 
    $should_min = $step_quantity;
    $should_next = $should_min + $step_quantity;

    $args = array(
        'cart_min_price' => wc_price( $min_price ),
        'cart_max_price' => wc_price( $max_price ),
        'cart_min_quantity' => $min_quantity,
        'cart_max_quantity' => $max_quantity,
        'step_quantity' => $step_quantity,
        'should_min' => $should_min,
        'should_next' => $should_next,
    );
    $error_count = 0;
    if( $min_price ){
        if( $cart_total < $min_price ){
            $error_count++;
            $message = sprintf( wcmmq_get_message( 'msg_min_price_cart', false ), wc_price( $min_price ) );
            $message = wcmmq_message_convert_replace( $message, $args );
            wc_add_notice( $message, 'error' );
        }
    }
    if( $max_price ){
        if( $cart_total > $max_price ){
            $error_count++;
            $message = sprintf( wcmmq_get_message( 'msg_max_price_cart', false ), wc_price( $max_price ) );
            $message = wcmmq_message_convert_replace( $message, $args );
            wc_add_notice( $message, 'error' );
        }
    }
    if( $min_quantity ){
        if( $cart_total_quantity < $min_quantity ){
            $error_count++;
            $message = sprintf( wcmmq_get_message( 'msg_min_quantity_cart', false ), $min_quantity );
            $message = wcmmq_message_convert_replace( $message, $args );
            wc_add_notice( $message, 'error' );
        }
    }
    if( $max_quantity ){
        if( $cart_total_quantity > $max_quantity ){
            $error_count++;
            $message = sprintf( wcmmq_get_message( 'msg_max_quantity_cart', false ), $max_quantity );
            $message = wcmmq_message_convert_replace( $message, $args );
            wc_add_notice( $message, 'error' );
        }
    }
    if( $step_quantity ){
        if( ($cart_total_quantity % $step_quantity ) == !0 ){
            $error_count++;
            $message = sprintf( wcmmq_get_message( 'msg_step_quantity_cart', false ), $should_min, $should_next, $step_quantity );
            $message = wcmmq_message_convert_replace( $message, $args );
            wc_add_notice( $message, 'error' );
        }
    }

  
    //start 1.10
    $terms_data = $cat_term_data = wcmmq_get_term_data_wpml();
    $cat_minmaxData = $cat_term_data['product_cat'] ?? [];
    $min_max_cats_cond = [];
    foreach( $cat_minmaxData as $key=>$saif ){
        if((!empty($saif['_cart_min']) && $saif['_cart_min'] > 0) || (!empty($saif['_cart_max']) && $saif['_cart_max'] > 0)){
            $min_max_cats_cond[$key]=[
                'type'  => 'product_cat',
                'min'   => $saif['_cart_min'] ?? '',
                'max'   => $saif['_cart_max'] ?? '',
                'min_prod_total'   => $saif['_cart_prod_min_total'] ?? '',
                'max_prod_total'   => $saif['_cart_prod_max_total'] ?? '',
            ];
        }
    }
 
    // var_dump($min_max_cats_cond);
	// Loop through each item in the cart
    $avalBOCat = [];
    $vailProductCatWise = [];
	foreach ( $contents as $cart_item_key => $cart_item ) {
        // var_dump($cart_item);
		// Get the product ID and category for the current item
        // dd($contents);
		$product_id = $cart_item['product_id'];
		$quantity = $cart_item['quantity'] ?? 0;
		$product_categories = wp_get_post_terms( $product_id, 'product_cat' );
        
        foreach($product_categories as $prCat){
            if( in_array($prCat->term_id,array_keys($min_max_cats_cond)) && ( ! empty( $min_max_cats_cond[$prCat->term_id]['min_prod_total'] ) || ! empty( $min_max_cats_cond[$prCat->term_id]['max_prod_total'] ) ) ){
                $vailProductCatWise[$prCat->term_id][$product_id] = 1;
            }
            // var_dump(array_keys($min_max_cats_cond));
            if(in_array($prCat->term_id,array_keys($min_max_cats_cond))){
                $avalBOCat[$prCat->term_id] = $avalBOCat[$prCat->term_id] ?? 0;
                $avalBOCat[$prCat->term_id] += $quantity;
            }
            
        }
        // var_dump($product_categories);
        $product_categories = array_map(function($trmObj){

            return $trmObj->term_id;
        },$product_categories);

	}
    $vailProductCatWise = array_map(function($arrs){
        return is_array( $arrs ) ? count( $arrs ) : 0;
    },$vailProductCatWise);

    foreach($vailProductCatWise as $targetCatID => $currProdTotalCount){
        $conds = $min_max_cats_cond[$targetCatID];
        $min_prod_total = $conds['min_prod_total'] ?? 0;
        $max_prod_total = $conds['max_prod_total'] ?? 0;
        if(empty($min_prod_total)) continue;
        $tax = get_term_by('term_id',$targetCatID, 'product_cat');

        $taxName = $tax->name;
        if($currProdTotalCount < $min_prod_total && ! empty( $min_prod_total )){
            // $avalBOCat=[];
            $error_count++;
            wc_add_notice( "In $taxName category, you have added $currProdTotalCount type product, need $min_prod_total.", 'error' );
        }
        if($currProdTotalCount > $max_prod_total && ! empty( $max_prod_total )){
            // $avalBOCat=[];
            $error_count++;
            wc_add_notice( "In $taxName category, you have added $currProdTotalCount type product, but max to be $max_prod_total.", 'error' );
        }
    }


    //Generate Message of Warning
    foreach($avalBOCat as $targetCatID => $currentQty){
        $conds = $min_max_cats_cond[$targetCatID];
        $min_req = $conds['min'] ?? 0;
        $max_req = $conds['max'] ?? 0;
        $tax = get_term_by('term_id',$targetCatID, 'product_cat');

        $taxName = $tax->name;
        if($currentQty < $min_req  && ! empty( $min_req ) ){
            $error_count++;
            wc_add_notice( "You have to buy minimum $min_req quantity of products from $taxName category", 'error' );
        }elseif($currentQty > $max_req && ! empty( $max_req ) ){
            $error_count++;
            wc_add_notice( "You can buy maximum $max_req quantity of products from $taxName category", 'error' );
        }
    }

    


    if( $error_count > 0 ){
        wc_print_notices();
        return false;
    }else{
        return true;
    }

}
add_action( 'woocommerce_before_cart_table' , 'wcmmq_cart_page_validation' );

add_action('wcmmq_edit_terms_bottom','wcmmq_edit_terms_att_cart_page_cond', 10, 3);
function wcmmq_edit_terms_att_cart_page_cond($term_key, $minmaxsteps, $trm_id){

    $id = $trm_id;
    $cart_max = $minmaxsteps['_cart_max'] ?? '';
    $cart_min = $minmaxsteps['_cart_min'] ?? '';
    $prod_min_total = $minmaxsteps['_cart_prod_min_total'] ?? '';
    $prod_max_total = $minmaxsteps['_cart_prod_max_total'] ?? '';

    ?>
    <?php if($term_key == 'product_cat'){ ?>
    <tr>
        <td colspan="2"> 
            <h3>Cart Page Count Condition for this Category</h3>
            <table style="display: inline-block;">
                <tr>
                    <th>
                        <label><?php echo esc_html__( 'Minimum Cart Qty total', 'wcmmq' ); ?></label>
                    </th>
                    <td>
                        <input class="ua_input" name="data[terms][<?php echo esc_attr( $term_key ); ?>][<?php echo esc_attr( $id ); ?>][_cart_min]" 
                            value="<?php echo $cart_min ?>"  type="number" step=any>
                    </td>
                </tr> 

                <tr>
                    <th>
                        <label><?php echo esc_html__( 'Maximum Cart Qty total', 'wcmmq' ); ?></label>
                    </th>
                    <td>
                        <input class="ua_input" name="data[terms][<?php echo esc_attr( $term_key ); ?>][<?php echo esc_attr( $id ); ?>][_cart_max]" 
                            value="<?php echo $cart_max ?>"  type="number" step=any>
                    </td>
                </tr> 

                <tr>
                    <td colspan="2">
                        <br><br>
                        <h4>Product Type Condition</h4>
                        
                        Following 2 option can be confusing. If keep empty, that will not impact any way.
                        Support: you have added product like: Album 2 qty and T-shirt 6 qty, Here added 2 type product actually.
                    </td>
                </tr>
                <tr>
                    <th>
                        <label><?php echo esc_html__( 'Minimum Product Type total', 'wcmmq' ); ?></label>
                    </th>
                    <td>
                        <input class="ua_input" name="data[terms][<?php echo esc_attr( $term_key ); ?>][<?php echo esc_attr( $id ); ?>][_cart_prod_min_total]" 
                            value="<?php echo $prod_min_total ?>"  type="number" step=any>
                    </td>
                </tr>  
                <tr>
                    <th>
                        <label><?php echo esc_html__( 'Maximum Product Type total', 'wcmmq' ); ?></label>
                    </th>
                    <td>
                        <input class="ua_input" name="data[terms][<?php echo esc_attr( $term_key ); ?>][<?php echo esc_attr( $id ); ?>][_cart_prod_max_total]" 
                            value="<?php echo $prod_max_total ?>"  type="number" step=any>
                    </td>
                </tr>  
            </table>
        </td>
    </tr>  
    <?php } ?>
    
    <?php 
}

