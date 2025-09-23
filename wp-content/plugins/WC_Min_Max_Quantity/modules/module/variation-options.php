<?php


// 1. Add custom field input @ Product Data > Variations > Single Variation
 
add_action( 'woocommerce_variation_options', 'wcmmq_add_custom_field_to_variations', 10, 3 );
 
function wcmmq_add_custom_field_to_variations( $loop, $variation_data, $variation ) {
    //var_dump($loop);
    //var_dump( $loop, $variation_data, $variation );
    $args = array();
    $args[] = array(
        'id'        =>  WC_MMQ_PREFIX_PRO . 'min_quantity[' . $loop . ']',
        //'name'        =>  WC_MMQ_PREFIX_PRO . 'min_quantity',
        'label'     =>  __( 'Min Quantity', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'text',
        'desc_tip'  =>  true,
        'description'=> __( 'Enter Minimum Quantity for this Variation', 'wcmmq_pro' ),
        'data_type' => 'decimal',
        'value' => get_post_meta( $variation->ID, WC_MMQ_PREFIX_PRO . 'min_quantity', true ),
    );
    
    // $args[] = array(
    //     'id'        =>  WC_MMQ_PREFIX_PRO . 'default_quantity[' . $loop . ']',
    //     //'name'        =>  WC_MMQ_PREFIX_PRO . 'default_quantity',
    //     'label'     =>  __( 'Default Quantity', 'wcmmq_pro' ),
    //     'class'     =>  'wcmmq_input',
    //     'type'      =>  'text',
    //     'desc_tip'  =>  true,
    //     'description'=> __( 'It is an optional Number, If do not set, Product default quantity will come from Minimum Quantity', 'wcmmq_pro' ),
    //     'data_type' => 'decimal',
    //     'value' => get_post_meta( $variation->ID, WC_MMQ_PREFIX_PRO . 'default_quantity', true ),
    // );
    
    
    $args[] = array(
        'id'        =>  WC_MMQ_PREFIX_PRO . 'max_quantity[' . $loop . ']',
        //'name'        =>  WC_MMQ_PREFIX_PRO . 'max_quantity',
        'label'     =>  __('Max Quantity', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'text',
        'desc_tip'  =>  true,
        'description'=> __( 'Enter Maximum Quantity for this Variation', 'wcmmq_pro' ),
        'data_type' => 'decimal',
        'value' => get_post_meta( $variation->ID, WC_MMQ_PREFIX_PRO . 'max_quantity', true ),
    );
    
    $args[] = array(
        'id'        =>  WC_MMQ_PREFIX_PRO . 'product_step[' . $loop . ']',
        //'name'        =>  WC_MMQ_PREFIX_PRO . 'product_step',
        'label'     =>  __('Quantity Step', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'text',
        'desc_tip'  =>  true,
        'description'=> __( 'Enter quantity for this Variation', 'wcmmq_pro' ),
        'data_type' => 'decimal',
        'value' => get_post_meta( $variation->ID, WC_MMQ_PREFIX_PRO . 'product_step', true ),
    );
    
    foreach($args as $arg){
        woocommerce_wp_text_input($arg);
    }
    
}
 
// -----------------------------------------
// 2. Save custom field on product variation save
 
add_action( 'woocommerce_save_product_variation', 'wcmmq_save_custom_field_variations', 10, 2 );
 
function wcmmq_save_custom_field_variations( $variation_id, $i ) {
    // var_dump($variation_id, $i);
    $args = array(
        WC_MMQ_PREFIX_PRO . 'min_quantity',
        WC_MMQ_PREFIX_PRO . 'default_quantity',
        WC_MMQ_PREFIX_PRO . 'max_quantity',
        WC_MMQ_PREFIX_PRO . 'product_step',
    );
    foreach($args as $arg){
        $custom_field = $_POST[$arg][$i];
        $custom_field = wc_format_decimal( $custom_field );
        if ( ! isset( $custom_field ) ) continue;
        update_post_meta( $variation_id, $arg, esc_attr( $custom_field ) );
    }
}

