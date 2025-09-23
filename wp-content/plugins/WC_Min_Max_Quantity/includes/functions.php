<?php
// add_action('wcmmq_arg_asign',function($wcmmq){
//     // $wcmmq->min_value = 10;
//     // $wcmmq->max_value = 100;
//     // $wcmmq->step_value = 5;
//     // $wcmmq->setIfVariationArgs();
    
//     // // $wcmmq->finalizeArgs();
//     // var_dump($wcmmq->input_args);

//     //if( $this->is_pro && $this->setIfVariationArgs() ) return true;
// });
/**
 * This feature should control from admin panel
 * I mean: when will activate, then bellow code will execute
 */
add_action( 'init', 'wcmmq_float_stock_amount', PHP_INT_MAX );
if ( ! function_exists( 'wcmmq_float_stock_amount' ) ) {
	/**
	 * wcmmq_float_stock_amount.
	 */
	function wcmmq_float_stock_amount() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter(    'woocommerce_stock_amount', 'floatval' );
	}
}

/**
 * Load Script Under Variation form
 * for set QTY based on Variation
 * 
 * @since 1.8
 */
function wcmmq_pro_js_for_variation_product(){
global $product;
$validation = apply_filters( 'wcmmq_js_variation_script', true, $product );

if( !$validation || 'variable' !== $product->get_type() ){
    return;
}

$product_id = $product->get_id();
$product_data[WC_MMQ_PREFIX_PRO . 'min_quantity'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'min_quantity', true );
$product_data[WC_MMQ_PREFIX_PRO . 'default_quantity'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'default_quantity', true );
$product_data[WC_MMQ_PREFIX_PRO . 'max_quantity'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'max_quantity', true );
$product_data[WC_MMQ_PREFIX_PRO . 'product_step'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'product_step', true );

$product_data = apply_filters( 'wcmmq_product_data_for_json', $product_data, $product );

$product_data = wp_json_encode( $product_data );

$default_data[WC_MMQ_PREFIX_PRO . 'min_quantity'] = WC_MMQ::getOption( WC_MMQ_PREFIX_PRO . 'min_quantity' );
$default_data[WC_MMQ_PREFIX_PRO . 'default_quantity'] = WC_MMQ::getOption( WC_MMQ_PREFIX_PRO . 'default_quantity' );
$default_data[WC_MMQ_PREFIX_PRO . 'max_quantity'] = WC_MMQ::getOption( WC_MMQ_PREFIX_PRO . 'max_quantity' );
$default_data[WC_MMQ_PREFIX_PRO . 'product_step'] = WC_MMQ::getOption( WC_MMQ_PREFIX_PRO . 'product_step' );

//For Taxonomy
$terms_data = WC_MMQ::getOption( 'terms' );
$terms_data = is_array( $terms_data ) ? $terms_data : array();
foreach( $terms_data as $term_key => $values ){
    $product_term_list = wp_get_post_terms( $product_id, $term_key, array( 'fields' => 'ids' ));
    foreach ( $product_term_list as $product_term_id ){

        $my_term_value = isset( $values[$product_term_id] ) ? $values[$product_term_id] : false;
        if( is_array( $my_term_value ) ){
            $default_data[WC_MMQ_PREFIX_PRO . 'min_quantity'] = !empty( $my_term_value['_min'] ) ? $my_term_value['_min'] : $default_data[WC_MMQ_PREFIX_PRO . 'min_quantity'];
            $default_data[WC_MMQ_PREFIX_PRO . 'default_quantity'] = !empty( $my_term_value['_default'] ) ? $my_term_value['_default'] : $default_data[WC_MMQ_PREFIX_PRO . 'default_quantity'];
            $default_data[WC_MMQ_PREFIX_PRO . 'max_quantity'] = !empty( $my_term_value['_max'] )  ? $my_term_value['_max'] : $default_data[WC_MMQ_PREFIX_PRO . 'max_quantity'];
            $default_data[WC_MMQ_PREFIX_PRO . 'product_step'] = !empty( $my_term_value['_step'] ) ? $my_term_value['_step'] : $default_data[WC_MMQ_PREFIX_PRO . 'product_step'];
            break;
        }
    }

}

$default_data = apply_filters( 'wcmmq_default_data_for_json', $default_data, $product );

$default_data = wp_json_encode( $default_data );
$variables = $product->get_children();
//var_dump(count( $variables ) > 0);
$data = array();
if(!is_array($variables) || ( is_array( $variables ) && count( $variables ) < 1 )) return;

foreach( $variables as $variable_id){
    //$min_qty = get_post_meta( $variable_id, WC_MMQ_PREFIX_PRO . 'min_quantity', true );
    $data[$variable_id] = array(
        WC_MMQ_PREFIX_PRO . 'min_quantity' => get_post_meta( $variable_id, WC_MMQ_PREFIX_PRO . 'min_quantity', true ),
        WC_MMQ_PREFIX_PRO . 'default_quantity' => get_post_meta( $variable_id, WC_MMQ_PREFIX_PRO . 'default_quantity', true ),
        WC_MMQ_PREFIX_PRO . 'max_quantity' => get_post_meta( $variable_id, WC_MMQ_PREFIX_PRO . 'max_quantity', true ),
        WC_MMQ_PREFIX_PRO . 'product_step' => get_post_meta( $variable_id, WC_MMQ_PREFIX_PRO . 'product_step', true ),
    );
}
$data = apply_filters( 'wcmmq_variation_data_for_json', $data, $product );
$data = wp_json_encode( $data );//htmlspecialchars( wp_json_encode( $data ) );





//var_dump($product_data,$default_data,$data);
?>
<script  type='text/javascript'>
(function($) {
    'use strict';
    $(document).ready(function($) {
        var product_id = "<?php echo $product->get_id(); ?>";
        var default_data = '<?php echo $default_data; ?>';
        var product_data = '<?php echo $product_data; ?>';
        var variation_data = '<?php echo $data; ?>';
        //var ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";

        default_data = JSON.parse(default_data);
        product_data = JSON.parse(product_data);
        variation_data = JSON.parse(variation_data);
        var form_selector = 'form.variations_form.cart[data-product_id="' + product_id + '"]';
        
        //console.log(variation_data[55]['<?php echo WC_MMQ_PREFIX_PRO; ?>min_quantity']);
        //$(form_selector + ' input.variation_id').css('display','none');
        //console.log(variation_data);

        $(document).on( 'found_variation', form_selector, function( event, variation ) {
            // console.log(variation);
        });
  
        $(document.body).on('change',form_selector + ' input.variation_id',function(){
           
            //$( form_selector + ' input.input-text.qty.text' ).triggerHandler( 'binodon');
            var variation_id = $(form_selector + ' input.variation_id').val();
            var qty_box = $(form_selector + ' input.input-text.qty.text');

            if(typeof variation_id !== 'undefined' && variation_id !== ''  && variation_id !== ' '){
                var min,max,step,basic;

                min = variation_data[variation_id]['<?php echo WC_MMQ_PREFIX_PRO; ?>min_quantity'];
                if(typeof min === 'undefined'){
                    return false;
                }
                if(min === '' || min === false){
                    min = product_data['<?php echo WC_MMQ_PREFIX_PRO; ?>min_quantity'];
                }
                if(min === '' || min === false){
                    min = default_data['<?php echo WC_MMQ_PREFIX_PRO; ?>min_quantity'];
                }
                max = variation_data[variation_id]['<?php echo WC_MMQ_PREFIX_PRO; ?>max_quantity'];
                if(max === '' || max === false){
                    max = product_data['<?php echo WC_MMQ_PREFIX_PRO; ?>max_quantity'];
                }
                if(max === '' || max === false){
                    max = default_data['<?php echo WC_MMQ_PREFIX_PRO; ?>max_quantity'];
                }
                
                step = variation_data[variation_id]['<?php echo WC_MMQ_PREFIX_PRO; ?>product_step'];
                if(step === '' || step === false){
                    step = product_data['<?php echo WC_MMQ_PREFIX_PRO; ?>product_step'];
                }
                if(step === '' || step === false){
                    step = default_data['<?php echo WC_MMQ_PREFIX_PRO; ?>product_step'];
                }
                basic = variation_data[variation_id]['<?php echo WC_MMQ_PREFIX_PRO; ?>default_quantity'];
                if(basic === '' || basic === false){
                    basic = product_data['<?php echo WC_MMQ_PREFIX_PRO; ?>default_quantity'];
                }
                if(basic === '' || basic === false){
                    basic = default_data['<?php echo WC_MMQ_PREFIX_PRO; ?>default_quantity'];
                }
                
                if(basic === '' || basic === false){
                    basic = min;
                }
                var lateSome = setInterval(function(){

                    qty_box.attr({
                        min:min,
                        max:max,
                        step:step,
                        value:basic
                    });
                    qty_box.val(basic).trigger('change');
                    clearInterval(lateSome);
                },500);

            }
            
            
        });

    });
})(jQuery);
</script>
<?php
}
add_action('woocommerce_single_variation','wcmmq_pro_js_for_variation_product');
add_action('wpt_action_variation','wcmmq_pro_js_for_variation_product');

// add_filter( 'post_class',function($classes, $class, $post_ID){
    
//     $product_id = $post_ID;
//     $data = wcmmq_custom_box_data_fromwise( $product_id );

//     if( ! $data ) return $classes;

//     $min = $data['min'];
//     $max = $data['max'];
//     $step = $data['step'];
    
//     if( empty( $max ) || empty( $min ) || empty( $step )  ) return $classes;

//     if( is_array($classes)){
//         $classes[] = 'wcmmq-custom-input-box';
//         $classes[] = 'wcmmq-custom-input-box-single';
//     }
    
//     return $classes;
// },10,3 );

// add_action( 'woocommerce_before_add_to_cart_quantity',function(){
//     echo '<h2>HHHHHHHHHHHHH</h2>';
// } );


// add_filter('woocommerce_quantity_input_args',function($args,$product){
//     $args['product_id']=$product->get_id();
//     //var_dump($args);
//     return $args;
// },10,2);

// add_filter( 'woocommerce_cart_item_class',function($classs,$cart_item, $cart_item_key){
//     //var_dump($cart_item); $cart_item['product_id']
//     $product_id = $cart_item['product_id'];
//     $data = wcmmq_custom_box_data_fromwise( $product_id );

//     if( ! $data ) return $classs;

//     $min = $data['min'];
//     $max = $data['max'];
//     $step = $data['step'];
    
//     if( empty( $max ) || empty( $min ) || empty( $step )  ) return $classs;
    
//     return $classs . ' wcmmq-custom-input-box wcmmq-custom-input-box-cart';
// },99,3 );

// add_action( 'woocommerce_locate_template', 'wcmmq_cutomized_input_template',PHP_INT_MAX, 3 );

/**
 * @ignore Currently not using this function
 */
function wcmmq_cutomized_input_template( $template, $template_name, $template_path ){
    global $product;
    
    if( 'global/quantity-input.php' !== $template_name ) return $template; //checking template
    if( ! is_object($product) ) return $template; //checking object or not
    if( 'simple' !== $product->get_type() ) return $template; //checking type

    $data = wcmmq_custom_box_data_fromwise();

    if( ! $data ) return $template;

    $min = $data['min'];
    $max = $data['max'];
    $step = $data['step'];
    
    if( empty( $max ) || empty( $min ) || empty( $step )  ) return $template;
    
    //Assign custom template when found drop down
    $template = WC_MMQ_PRO_BASE_DIR . 'template/hidden-input.php';
    return $template;
    
}


/**
 * @ignore Currently not using this function
 */
function wcmmq_custom_box_data_fromwise($my_product_id = false){
    $data = array(
        'min' => null,
        'max' => null,
        'step'=> 1,
        'type'=> null,
        'label'=> null,
        'default'=> null,
        'sufix'=> null,
    );
    
    global $product;
    if( ! $my_product_id ){
        $product_id = $product->get_id(); //It will be need for getting/find out min max and step[custom]  
    }else{
        $product_id = $my_product_id;
    }
    
    
    
    $b_type = get_post_meta( $product_id, 'qty_box_type', true );
    $b_label = get_post_meta( $product_id, 'qty_box_label', true );

    $data_from = 'config';//can be single or config(wp options actually)
    if( ! empty( $b_type ) ){
        $data_from = 'single';
        
    }else{
        $options = WC_MMQ::getOptions();
        $b_type = isset( $options['qty_box_type'] ) ? $options['qty_box_type'] : false;
        $b_label = isset( $options['qty_box_label'] ) ? $options['qty_box_label'] : false;
    }
    
    if( empty( $b_type ) ) return null;
    
    
    
    if( $data_from == 'config' ){
        $options = WC_MMQ::getOptions();
        $data['min'] = $options[WC_MMQ_PREFIX_PRO . 'min_quantity'];
        $data['max'] = $options[WC_MMQ_PREFIX_PRO . 'max_quantity'];
        $data['step'] = $options[WC_MMQ_PREFIX_PRO . 'product_step'];
        $data['default'] = $options[WC_MMQ_PREFIX_PRO . 'default_quantity'];
        $data['sufix'] = isset( $options['qty_option_sufix'] ) ? $options['qty_option_sufix'] : '';
    }elseif( $data_from == 'single' ){
        
        $data['min'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'min_quantity', true );
        $data['max'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'max_quantity', true );
        $data['step'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'product_step', true );
        $data['default'] = get_post_meta( $product_id, WC_MMQ_PREFIX_PRO . 'default_quantity', true );
        $data['sufix'] = get_post_meta( $product_id, 'qty_option_sufix', true );
        $data['custom_dropdown'] = get_post_meta( $product_id, 'qty_custom_dropdown', true );
    }
    
    $data['type'] = $b_type;
    $data['label'] = $b_label;
    
    return $data;
}

add_action( 'template_redirect' , 'wcmmq_cart_page_validation_redirect' );
function wcmmq_cart_page_validation_redirect(){

    /**
     * Actually if Disable Cart page Condition from 
     * Module Switcher page,
     * then we will return null automatically.
     * we will not go to bottom
     * 
     * @since 3.0.0.0
     * @author Saiful Islam <codersaiful@gmail.com>
     * @date 11.11.2023
     */
    if( ! function_exists('wcmmq_cart_page_validation')) return;

	if( is_checkout() ){
		$is_valid = wcmmq_cart_page_validation();
		if( ! $is_valid ){
			wp_safe_redirect( get_permalink( get_option('woocommerce_cart_page_id') ) );
			exit;
		}
	}
}
// add_filter( 'wcmmq_custom_input_box_html','wcmmq_custom_dropdown',10,2 );
/**
 * @ignore Currently not using this function
 */
function wcmmq_custom_dropdown($html,$args){
    
    if( empty( $args['custom_dropdown'] ) ) return $html;
    $str = $args['custom_dropdown'];
    $options = explode('|',rtrim($str,'|'));
    $options = array_filter( $options );
    $options = array_map(function($e_data){
        return is_string( $e_data ) ? explode(":", $e_data): $e_data;
    },$options);
    
    if( empty($options) || ! is_array( $options ) ) return $html;
    $sufix = isset( $args['$args'] ) && ! empty( $args['$args'] ) ? $args['$args'] : '';
    $new_html = "";
    foreach($options as $option){
        $val = isset( $option[0] ) ? $option[0] : '';
        $label = isset( $option[1] ) ? $option[1] : '';
        $new_html .= ! empty($label) & ! empty( $val ) ? "<option value='$val'>$label $sufix</option>" : '';
    }

    return ! empty( $new_html ) ? $new_html : $html;
}

// add_filter( 'wcmmq_last_step_checker_filter', 'wcmmq_remove_validation_for_cart',999,2 );
// add_filter( 'woocommerce_add_to_cart_validation', 'wcmmq_remove_validation_for_cart', 999,2 );

/**
 * @ignore Currently not using this function
 */
function wcmmq_remove_validation_for_cart($bool,$product_id){
    $data = wcmmq_custom_box_data_fromwise( $product_id );
    
    $custom_dropdown = isset( $data['custom_dropdown'] ) ? $data['custom_dropdown'] : '';
    if( ! empty( $custom_dropdown ) ){
        return true;
    }
    return $bool;
}

/**
 * manupulated price by quantity
 * @since 2.0.5.1 
 * @author Fazle Bari
 */
// function woocommerce_product_get_price_with_quantity( $price, $product ){
    
//     if( ! is_product() ) return $price;

//     $options = WC_MMQ::getOptions();
//     $display_price_with_min = $options['display_price_with_min'] ?? false;
//     if( ! $display_price_with_min ) return $price;

//     $product_id = $product->get_id();
    
//     $min = get_post_meta( $product_id, WC_MMQ_PREFIX . 'min_quantity', true );
//     if( empty( $min ) ){
        
//         $min = $options[WC_MMQ_PREFIX . 'min_quantity'];
//     }
//     $min = (int) $min;
    
//     if( $min > 0 ){
//         return $price * $min;
//     }else{
//         return $price;
//     }
// }
// add_filter('woocommerce_product_get_price' , 'woocommerce_product_get_price_with_quantity',10,2);


/**
 * Function name has been changed to wcmmq_wc_price_modify_4multiply
 * from woocommerce_product_getsss
 * 
 * actually name was un planned
 *
 * @param boolean|string $return
 * @param string $price
 * @param array $args
 * @param string $unformatted_price
 * @return string|bool|boolean
 */
function wcmmq_wc_price_modify_4multiply($return, $price, $args, $unformatted_price){

    if( !is_product() ) return $return;

    $options = WC_MMQ::getOptions();
    $display_price_with_min = $options['display_price_with_min'] ?? false;
    if( ! $display_price_with_min ) return $return;


    $return = "<span class='wcmmq-price-wrapper'><span class='wcmmq-original-price'>{$return}</span><span 
    data-decimal='{$args['decimals']}' 
    data-decimal_separator='{$args['decimal_separator']}' 
    data-thousand_separator='{$args['thousand_separator']}' 
    class='wcmmq-unformatted-price' 
    data-price='{$unformatted_price}' 
    data-price_format='{$args['price_format']}' >{$return}</span></span>";
    return $return;// $price_html;
}
add_filter('wc_price' , 'wcmmq_wc_price_modify_4multiply', 99, 4);

/**
 * This function will add an option on setting page.  
 * @since 2.0.5.1 
 * @author Fazle Bari
 */
function wcmmq_setting_bottom_row_price_callback($saved_data){
    $display_price_with_min = isset( $saved_data[ 'display_price_with_min' ] ) && $saved_data[ 'display_price_with_min' ] == '1' ? 'checked' : false;
    ?>

        <tr>
            <td>
                <div class="wcmmq-form-control">
                    <div class="form-label col-lg-6">
                        <label for="data[<?php echo esc_attr( WC_MMQ_PREFIX ); ?>display_price_with_min]">
                            <?php echo esc_html__( 'Multiply Price By Quantity', 'wcmmq_pro' ); ?>
                        </label>
                    </div>
                    <div class="form-field col-lg-6">
                        <label class="switch">
                            <input value="1" name="data[display_price_with_min]"
                                <?php echo $display_price_with_min; /* finding checked or null */ ?> type="checkbox" id="_wcmmq_display_price_with_min">
                            <div class="slider round"><!--ADDED HTML -->
                                <span class="on"><?php echo esc_html__( 'ON', 'wcmmq_pro' ); ?></span><span class="off"><?php echo esc_html__( 'OFF', 'wcmmq_pro' ); ?></span><!--END-->
                            </div>
                        </label>
                        
                    </div>
                </div>
            </td>
            <td>
                <div class="wcmmq-form-info">
                    <?php wcmmq_doc_link('https://codeastrology.com/min-max-quantity/multiply-price-by-quantity/'); ?>
                    <p><?php echo esc_html__( 'Product price will multiply by the quantity before being displayed on a single product page and will Increase or decrease in accordance with the quantity.', 'wcmmq_pro' ); ?> </p>
                </div> 
            </td>
        </tr>
    <?php
}
add_action('wcmmq_setting_bottom_row' , 'wcmmq_setting_bottom_row_price_callback');

/**
 * Specially for Quantity Box of Shop Page or any Taxonomy Page
 * There is a Filter hook , where we checked by ! in_array
 * 
 * File Location: includes/features/quantity-archive.php 
 * 
 * @since 2.0.7.3 and Free version: 2.6.0
 * @author Saiful Islam <codersaiful@gmail.com>
 */
add_filter( 'wcmmq_archive_qty_dissupport_arr', function( $dissupport_arr ){
    // var_dump(array_search('grouped',$dissupport_arr));
    $new_arr = ['grouped', 'external'];
    return $new_arr;
} );
