<?php
/**
 * Product quantity inputs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/global/quantity-input.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
$product_id = $product->get_id(); //It will be need for 

$data = wcmmq_custom_box_data_fromwise();

$_wcmmq_min = $data['min'];
$_wcmmq_max = $data['max'];
$_wcmmq_step = $data['step'];
$_wcmmq_default = $data['default'];
$_wcmmq_sufix = $data['sufix'];
$_wcmmq_custom_dropdown = isset( $data['custom_dropdown'] ) ? $data['custom_dropdown'] : '';

$b_label = $data['label'];
$b_type = $data['type'];

$_html = ! empty( $b_label ) && $b_type == 'dropdown' ? "<option>$b_label</option>" : '';
$wrp_tag = $b_type == 'dropdown' ? "select" : 'ul';
$wcmmq_number = 1;
for($i = $_wcmmq_min;$i <= $_wcmmq_max; $i+=$_wcmmq_step){
    
    $wcmmq_checked = ( ! empty( $_wcmmq_default ) && $_wcmmq_default == $i ) || ( empty($_wcmmq_default) && $wcmmq_number == 1) ? 'checked' : '';
    if( $b_type == 'dropdown' ){
        $selected = $wcmmq_checked === 'checked' ? 'selected' : '';
        $_html .= "<option value='$i' $selected>$i $_wcmmq_sufix</option>";
    }else{
        $name = "wcmmq_$product_id";
        $id = $name . "_" . $i;

        $class = '';
        $_html .= "<li><input class='wcmmq-radio-button' data-product_id='$product_id' name='$name' id='$id' type='radio' value='$i' $wcmmq_checked><label for='$id'>$i $_wcmmq_sufix</label></li>";
    }
    $wcmmq_number++;
}
$_html = apply_filters( 'wcmmq_custom_input_box_html', $_html, $data, $product_id );
$wcmmq_wrapper_class = 'wcmmq-hidden-input-wrapper wcmmq-hid-product_id-'. $product_id . ' wcmmq-dropdown-radio-input';
if( ! empty( $_wcmmq_custom_dropdown ) ){
    $wcmmq_wrapper_class .= ' wcmmq-custom-dropdonw';
}
$wcmmq_wrapper_class = apply_filters( 'wcmmq_input_wrapper_class', $wcmmq_wrapper_class, $data, $product_id );
if ( $max_value && $min_value === $max_value ) {
	?>
	<div class="quantity hidden">
		<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
	</div>
	<?php
} else {
	
	if ( $min_value && ( $input_value < $min_value ) ) {
		$input_value = $min_value;
	}

	if ( $max_value && ( $input_value > $max_value ) ) {
		$input_value = $max_value;
	}

	if ( '' === $input_value ) {
		$input_value = 0;
	}

	?>
        <div class="<?php echo esc_attr( $wcmmq_wrapper_class ); ?>">
            <div data-product_id="<?php echo esc_attr( $product_id ); ?>" class="wcmmq-custom-qty-box-wrapper qty-box-wrapper-<?php echo esc_attr( $product_id ); ?> qty-box-wrapper-<?php echo esc_attr( $b_type ); ?>">
                <<?php echo $wrp_tag; ?> class="wcmmq-custom-qty">
                    <?php echo $_html; ?>
                </<?php echo $wrp_tag; ?>>
            </div>

            <div class="quantity">
                <input type="number" id="wcmmq_<?php esc_attr_e( uniqid( 'quantity_' ) ); ?>" step="<?php echo esc_attr( $step ); ?>" min="<?php echo esc_attr( $min_value ); ?>" 
                <?php if ( isset( $max_value ) && 0 < $max_value ) : ?>
                        max="<?php echo esc_attr( $max_value ); ?>"
                <?php endif; ?>
                name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $input_value ); ?>"
                class="wcmmq-hidden-input input-text qty text" inputmode="<?php echo esc_attr( $inputmode ); ?>" />
            </div>
	</div>
	<?php
}
