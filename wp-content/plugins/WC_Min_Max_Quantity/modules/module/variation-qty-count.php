<?php 

add_filter('woocommerce_add_to_cart_validation', 'wcmmq_variation_qty_validation', PHP_INT_MAX, 4 );

function wcmmq_variation_qty_validation( $bool, $product_id, $quantity, $variation_id = 0 ){

    $product = wc_get_product($product_id);
    if( $product->get_type() !== 'variable' ) return $bool;
    $title = $product->get_title();
    
    $variation_count_limit = get_post_meta($product_id,'wcmmq_var_count_limit',true);
    $all_variations_qty_total_limi = get_post_meta($product_id,'wcmmq_var_quantity_limit',true);
    $all_variations_min_qty_total = get_post_meta($product_id,'wcmmq_var_quantity_min_total',true);

    if( empty( $variation_count_limit ) && empty( $all_variations_qty_total_limi ) && empty( $all_variations_min_qty_total ) ) return $bool;

    $variation_qty_count = 0;

    $new_variation_qty_total = 0;

    $variation_match = array();
    
    if( count( WC()->cart->cart_contents ) < 1 ) $bool;

    foreach( WC()->cart->cart_contents as $contents ){
        

        if( $contents['variation_id'] == $variation_id){
            $variation_match[$variation_id] = $variation_id;
        }

        if($contents['product_id'] == $product_id){
            $new_variation_qty_total += $contents['quantity'] ?? 0;
            $variation_qty_count +=1;
        }

    }

    $new_variation_qty_total += $quantity;
    
    $args = [
        'product_name' => $title,
        'vari_total_max_qty' => $all_variations_qty_total_limi,
        'vari_total_min_qty' => $all_variations_min_qty_total,
        'vari_count_total' => $variation_count_limit,
    ];

    if( ! empty( $all_variations_qty_total_limi) && $new_variation_qty_total > $all_variations_qty_total_limi ){
        $message = sprintf( wcmmq_get_message( 'msg_vari_total_max_qty', false ), $title, $all_variations_qty_total_limi );
        $message = wcmmq_message_convert_replace( $message, $args );
        wc_add_notice( $message, 'error' );
        return false;
    }

    if( ! empty( $all_variations_min_qty_total) && $new_variation_qty_total < $all_variations_min_qty_total ){
        $message = sprintf( wcmmq_get_message( 'msg_vari_total_min_qty', false ), $title, $all_variations_min_qty_total );
        $message = wcmmq_message_convert_replace( $message, $args );
        wc_add_notice( $message, 'notice' );
        // return false;
    }

    if( ! empty($variation_match) ) return $bool;

    if( ! empty( $variation_count_limit ) && $variation_qty_count >= $variation_count_limit ){
        $message = sprintf( wcmmq_get_message( 'msg_vari_count_total', false ), $title, $variation_count_limit );
        $message = wcmmq_message_convert_replace( $message, $args );
        wc_add_notice( $message, 'error' );
        return false;
    }
    return $bool;
}

function wcmmq_variation_control_field(){
    global $post;
    $product = wc_get_product($post->ID);
    if($product->get_type() !== 'variable' ) return;
    $args = array();
    $args[] = array(
        'id'        =>  'wcmmq_var_quantity_limit',
        'name'      =>  'wcmmq_var_quantity_limit',
        'label'     =>  __( 'Variation maximum quantity total (Optional)', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'number',
        'desc_tip'  =>  true,
        'description'=> __( 'Enter the maximum quantity number. Customer can buy maximum this amount of products.', 'wcmmq_pro' ),
    );

    $args[] = array(
        'id'        =>  'wcmmq_var_quantity_min_total',
        'name'      =>  'wcmmq_var_quantity_min_total',
        'label'     =>  __( 'Variation minimum quantity total (Optional)', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'number',
        'desc_tip'  =>  true,
        'description'=> __( 'Enter the minimum quantity number. Customer has to buy this amount of products.', 'wcmmq_pro' ),
    );
    
    $args[] = array(
        'id'        =>  'wcmmq_var_count_limit',
        'name'        =>  'wcmmq_var_count_limit',
        'label'     =>  __( 'Variation combination limit(Optional)', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'number',
        'desc_tip'  =>  true,
        'description'=> __( 'Enter the maximum limitation number, if you a limitation of variation quantity total.', 'wcmmq_pro' ),
    );
    
    ?>
    <h3><?php echo esc_html__( 'Variations quanity total and Variation count total', 'wcmmq_pro' ); ?></h3>
    <?php
    
    foreach($args as $arg){
        woocommerce_wp_text_input($arg);
    }
}

add_action('woocommerce_product_options_wcmmq_minmaxstep','wcmmq_variation_control_field', 999); 

function wcmmq_variation_control_save_field_data( $post_id ){
    
    $var_quantity_limit = $_POST['wcmmq_var_quantity_limit'] ?? '';
    $var_quantity_min_limit = $_POST['wcmmq_var_quantity_min_total'] ?? '';
    $var_count_limit = $_POST['wcmmq_var_count_limit'] ?? '';
    
       
    //Updating Here
    update_post_meta( $post_id, 'wcmmq_var_quantity_limit', sanitize_text_field($var_quantity_limit) ); 
    update_post_meta( $post_id, 'wcmmq_var_quantity_min_total', sanitize_text_field($var_quantity_min_limit) ); 
    update_post_meta( $post_id, 'wcmmq_var_count_limit', sanitize_text_field($var_count_limit) ); 
    
}
add_action( 'woocommerce_process_product_meta', 'wcmmq_variation_control_save_field_data' );


// add_filter('woocommerce_update_cart_action_cart_updated', 'wcmmq_variation_validation');
function wcmmq_variation_validation( $update ){

    if( count( WC()->cart->cart_contents ) < 1 ) $update;
    ob_start();
    // var_dump(WC()->cart->cart_contents,$update);
    $message = ob_get_clean();
    wc_add_notice( $message, 'success' );
    
    return $update;
}


add_filter('woocommerce_update_cart_validation', 'wcmmq_variation_qty_update_cart_validation', 20, 4);

function wcmmq_variation_qty_update_cart_validation( $bool, $cart_item_key, $values, $quantity ){

    
    $product_id = $values['product_id'];
    $product = wc_get_product($product_id);
    if( $product->get_type() !== 'variable' ) return $bool;
    $title = $product->get_title();

    $variation_id = $values['variation_id'];

    $variation_count_limit = get_post_meta($product_id,'wcmmq_var_count_limit',true);
    $all_variations_qty_total_limi = get_post_meta($product_id,'wcmmq_var_quantity_limit',true);
    $all_variations_min_qty_total = get_post_meta($product_id,'wcmmq_var_quantity_min_total',true);

    if( empty( $variation_count_limit ) && empty( $all_variations_qty_total_limi ) ) return $bool;

    $variation_qty_count = 0;

    $new_variation_qty_total = 0;

    $variation_match = array();
    
    if( count( WC()->cart->cart_contents ) < 1 ) $bool;

    foreach( WC()->cart->cart_contents as $contents ){
        

        if( $contents['variation_id'] == $variation_id){
            $variation_match[$variation_id] = $variation_id;
        }

        if($contents['product_id'] == $product_id && $contents['variation_id'] !== $variation_id){
            $new_variation_qty_total += $contents['quantity'] ?? 0;
            $variation_qty_count +=1;
        }

    }

    $new_variation_qty_total += $quantity;

    $args = [
        'product_name' => $title,
        'vari_total_max_qty' => $all_variations_qty_total_limi,
        'vari_total_min_qty' => $all_variations_min_qty_total,
        'vari_count_total' => $variation_count_limit,
    ];
    
    if( ! empty( $all_variations_qty_total_limi) && $new_variation_qty_total > $all_variations_qty_total_limi ){
        $message = sprintf( wcmmq_get_message( 'msg_vari_total_max_qty', false ), $title, $all_variations_qty_total_limi );
        $message = wcmmq_message_convert_replace( $message, $args );
        wc_add_notice( $message, 'error' );
        return false;
    }

    if( ! empty( $all_variations_min_qty_total) && $new_variation_qty_total < $all_variations_min_qty_total ){
        $message = sprintf( wcmmq_get_message( 'msg_vari_total_min_qty', false ), $title, $all_variations_min_qty_total );
        $message = wcmmq_message_convert_replace( $message, $args );
        wc_add_notice( $message, 'error' );
        return false;
    }

    if( ! empty($variation_match) ) return $bool;

    if( ! empty( $variation_count_limit ) && $variation_qty_count >= $variation_count_limit ){
        $message = sprintf( wcmmq_get_message( 'msg_vari_count_total', false ), $title, $variation_count_limit );
        $message = wcmmq_message_convert_replace( $message, $args );
        wc_add_notice( $message, 'error' );
        return false;
    }
    return $bool;

}