<?php
/**
 * Use in your function.php file.
 * To show all Terms to set condition
 * Rememeber: if match mutliple terms for a product, 
 * Only one will work
 * @param bool true|false True and False only
 * @param Array $saved_data Get Array of Saved Data of Forms
 */
add_filter( 'wcmmq_all_terms', '__return_true' );

if( ! function_exists( 'wcmmp_term_list' ) ){
    /**
     * To add new Terms in Term Condition for Min max
     * User able to add any type of Taxomony, 
     * Such: product_cat, product_tag, pa_color, pa_size etc
     * 
     * @param array $ourTermList
     * @param Array $saved_data Get Array of Saved Data of Forms
     * @return Array
     */
    function wcmmp_term_list( $ourTermList ){

        $ourTermList['product_tag'] = 'Tag';
        return $ourTermList;
    }
    
   
 }
//add_filter( 'wcmmq_terms_list', 'wcmmp_term_list' );
 /**
 * @ignore Currently not using this function
 */
 function wcmmq_pro_additional_field_single(){
     
     $args = array(
        'id'        =>  'qty_box_type',
        'name'      =>  'qty_box_type',
        'label'     =>  __( 'Type (Optional)', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'text',
        'desc_tip'  =>  true,
        'description'=> __('Mainly for dropdown quantity box. It will show as first item of dropdown.', 'wcmmq_pro' ),
        'options' => array(
            '' => 'Default',
            'dropdown'=>'Dropdown',
            'radio' => 'Radio',
        )
    );
    woocommerce_wp_select($args);
    
    $args = array(
        'id'        =>  'qty_box_label',
        'name'      =>  'qty_box_label',
        'label'     =>  __('Quantity Label (Optional)', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'text',
        'desc_tip'  =>  true,
        'description'=> __('Only for Dropdown first option', 'wcmmq_pro' ),
    );
    woocommerce_wp_text_input($args);
    
    $args = array(
        'id'        =>  'qty_option_sufix',
        'name'      =>  'qty_option_sufix',
        'label'     =>  __('Quantity Option Suffix (Optional)', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'type'      =>  'text',
        'desc_tip'  =>  true,
        'description'=> __('Only for Dropdown first option', 'wcmmq_pro' ),
    );
    woocommerce_wp_text_input($args);
    
    $args = array(
        'id'        =>  'qty_custom_dropdown',
        'name'      =>  'qty_custom_dropdown',
        'label'     =>  __( 'Customized Dropdown', 'wcmmq_pro' ),
        'class'     =>  'wcmmq_input',
        'placeholder'=>  'eg: 12:12|19:19|34:34 and Last',
        'type'      =>  'text',
        'description'=> __( 'Min,max compolsury. separat with(|). You have to set a limit for min and Max.', 'wcmmq_pro' ),
    );
    woocommerce_wp_textarea_input($args);
    

}

// add_action('woocommerce_product_options_wcmmq_minmaxstep','wcmmq_pro_additional_field_single', 99);
/**
 * @ignore Currently not using this function
 */
function wcmmq_pro_additional_field_save( $post_id ){
    
    $qty_box_type = isset( $_POST['qty_box_type'] ) ? $_POST['qty_box_type'] : false;
    $qty_box_label = isset( $_POST['qty_box_label'] ) ? $_POST['qty_box_label'] : false;
    $qty_option_sufix = isset( $_POST['qty_option_sufix'] ) ? $_POST['qty_option_sufix'] : false;
    $qty_custom_dropdown = isset( $_POST['qty_custom_dropdown'] ) ? $_POST['qty_custom_dropdown'] : false;
    
    //Updating Here
    update_post_meta( $post_id, 'qty_box_type', esc_attr( $qty_box_type ) ); 
    update_post_meta( $post_id, 'qty_box_label', esc_attr( $qty_box_label ) ); 
    update_post_meta( $post_id, 'qty_option_sufix', esc_attr( $qty_option_sufix ) ); 
    update_post_meta( $post_id, 'qty_custom_dropdown', esc_attr( $qty_custom_dropdown ) ); 
}
// add_action( 'woocommerce_process_product_meta', 'wcmmq_pro_additional_field_save' );


function wcmmq_cart_page_notices_settings( $saved_data ){

    $fields_arr = [
        'msg_min_price_cart' => [
            'title' => __( 'Cart Minimum Price Validation Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [cart_min_price]', 'wcmmq_pro' ),
        ],
        
        'msg_max_price_cart' => [
            'title' => __( 'Cart Maximum Price Validation Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [cart_min_quantity]', 'wcmmq_pro' ),
        ],
        'msg_min_quantity_cart' => [
            'title' => __( 'Cart Minimum Quantity Validation Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [cart_min_quantity]', 'wcmmq_pro' ),
        ],
        
        'msg_max_quantity_cart' => [
            'title' => __( 'Cart Maximum Quantity Validation Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [cart_max_quantity]', 'wcmmq_pro' ),
        ],
        'msg_step_quantity_cart' => [
            'title' => __( 'Cart Step Quantity Validation Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [step_quantity]', 'wcmmq_pro' ),
        ],
        'msg_vari_total_max_qty' => [
            'title' => __( 'Variation Total Maximum Quantity Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [vari_total_max_qty], [product_name]', 'wcmmq_pro' ),
        ],
        'msg_vari_total_min_qty' => [
            'title' => __( 'Variation Total Minimum Quantity Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [vari_total_min_qty], [product_name]', 'wcmmq_pro' ),
        ],
        'msg_vari_count_total' => [
            'title' => __( 'Variation Total Count Message', 'wcmmq_pro' ),
            'desc'  => __( 'Available shortcode: [vari_count_total], [product_name]', 'wcmmq_pro' ),
        ],
    ];
    
    wcmmq_message_field_generator( $fields_arr, $saved_data, 'Cart Page Notices', false );

}
add_action( 'wcmmq_form_panel_bottom', 'wcmmq_cart_page_notices_settings' );

if( ! function_exists('wcmmq_doc_link') ){
    /**
     * This function will add helper doc
     * @since 3.3.6.1
     * @author Fazle Bari <fazlebarisn@gmail.com>
     */
    function wcmmq_doc_link( $url, $title='Helper doc' ){
        ?>
            <a href="<?php echo esc_url($url)?>" target="_blank" class="wpt-doc-lick"><i class="wcmmq_icon-help-circled-alt"></i><?php esc_html_e( $title ); ?></a>
        <?php
    }
}