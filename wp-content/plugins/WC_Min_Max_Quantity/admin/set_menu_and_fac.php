<?php

function wcmmq_before_form_content(){
    $direct = false;

    if( WC_MMQ_PRO::$direct ){
        $direct = true;
    }
?>
<div class="wcmmq-nav">
    <ul>
        <li><a href="https://codeastrology.com/min-max-quantity/documentation/" target="_blank">Documentation</a></li>
        <li><a href="https://wordpress.org/support/plugin/woo-min-max-quantity-step-control-single/reviews/#new-post" target="_blank">Rate on wordpress.org</a></li>
        <li><a href="https://wordpress.org/support/plugin/woo-min-max-quantity-step-control-single/" target="_blank">WordPress Forum</a></li>
        <li><a href="https://codeastrology.com/support/" target="_blank">Need Help?</a></li>
        <li><a href="https://github.com/codersaiful/woo-min-max-quantity-step-control/issues/new" target="_blank">Request Features</a></li>
        

        <?php
        if($direct){
        ?>
        <li class="wcmmq-pro-buy-now"><a href="<?php echo esc_attr( admin_url('admin.php?page=wcmmq-license') ); ?>" target="_blank">Activate License</a></li>
        <?php 
        }else{
        ?>
        <li class="wcmmq-pro-buy-now"><a href="https://wooproducttable.com/get-automatic-updates-from-codecanyon/" target="_blank">Get Automatic Updates</a></li>
        <?php 
        }
        
        ?>
    </ul>
</div>        
<?php    
}
add_action( 'wcmmq_before_form','wcmmq_before_form_content' );
/**
 * Obviously need tr and td here
 */
function wcmmq_setting_bottom_category_choose($saved_data){
    $_cat_ids = $saved_data['_cat_ids'] ?? false;
    if(!$_cat_ids) return;
?>
        <tr>
            <td>
                <div class="wcmmq-form-control">
                    <div class="form-label col-lg-6">
                        <label for="wcmmq_cat_ids"><?php echo esc_html__( 'Choose Category', 'wcmmq_pro' ); ?></label>
                    </div>
                    <div class="form-field col-lg-6">
                        <?php
                        $args = array(
                            'hide_empty'    => false, 
                            'orderby'       => 'count',
                            'order'         => 'DESC',
                        );

                        //WooCommerce Product Category Object as Array
                        $cat_object = get_terms( 'product_cat', $args );
                        $selected_cat_ids = isset( $saved_data['_cat_ids'] ) && !empty( $saved_data['_cat_ids'] ) ? $saved_data['_cat_ids'] : false;
                        ?>
                        <select name="data[_cat_ids][]" data-name="cat_ids" class="ua_input_select" id="wcmmq_cat_ids" multiple>
                            <?php
                            foreach ( $cat_object as $category ) {
                                echo "<option value='{$category->term_id}' " . ( is_array( $selected_cat_ids ) && in_array( $category->term_id, $selected_cat_ids ) ? 'selected' : false ) . ">{$category->name} - {$category->slug} ({$category->count})</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </td>
            <td>
                <div class="wcmmq-form-info">
                    <p><?php echo esc_html__( 'If you choose any category here then, this global setting will only be applied to the chosen categories. ( For Multiple selections, Press Ctrl + your Category. )', 'wcmmq_pro' ); ?></p>
                </div> 
            </td>
        </tr>
  
<!--
<tr class="wcmmq-qty-type-tr">
    <th><label for="wcmmq_qty_box_type">Quantity Box Type<span class="hightlighted_text">(Optional)</span></label></th>
    <td>
        <?php
        $qty_box_type = isset( $saved_data['qty_box_type'] ) && !empty( $saved_data['qty_box_type'] ) ? $saved_data['qty_box_type'] : false;
        ?>
        <select name="data[qty_box_type]" data-name="qty_box_type" class="ua_input_select" id="wcmmq_qty_box_type">
            <option value="" <?php echo $qty_box_type == '' ? 'selected' : ''; ?>>Default (Input Box)</option>
            <option value="dropdown" <?php echo $qty_box_type == 'dropdown' ? 'selected' : ''; ?>>Dropdown</option>
            <option value="radio" <?php echo $qty_box_type == 'radio' ? 'selected' : ''; ?>>Radio</option>
        </select>
        <p style="color: #228b22;">For drowpdown/radio type quantity box, Obviously should have max quantity.</p>
    </td>
</tr>  
<tr class="wcmmq-qty-drop-radio">
    <th><label for="wcmmq_qty_box_label">Quantity Box Type<span class="hightlighted_text">(Optional)</span></label></th>
    <td>
        <?php
        $qty_box_label = isset( $saved_data['qty_box_label'] ) && !empty( $saved_data['qty_box_label'] ) ? $saved_data['qty_box_label'] : false;
        ?>
        <input  name="data[qty_box_label]" type="text" value="<?php echo esc_attr( $qty_box_label ); ?>" id="wcmmq_qty_box_label" class="ua_input_number">
        <p style="color: #228b22;">Message here</p>
    </td>
</tr> 
<tr class="wcmmq-qty-drop-radio">
    <th><label for="wcmmq_qty_box_label">Quantity Option Sufix<span class="hightlighted_text">(Optional)</span></label></th>
    <td>
        <?php
        $qty_option_sufix = isset( $saved_data['qty_option_sufix'] ) && !empty( $saved_data['qty_option_sufix'] ) ? $saved_data['qty_option_sufix'] : false;
        ?>
        <input  name="data[qty_option_sufix]" type="text" value="<?php echo esc_attr( $qty_option_sufix ); ?>" id="wcmmq_qty_box_label" class="ua_input_number">
        <p style="color: #228b22;">Message here</p>
    </td>
</tr> 
-->

<?php    

}

add_action( 'wcmmq_setting_bottom_row','wcmmq_setting_bottom_category_choose' );


function wcmmq_prefix_sufix_area($saved_data){
    
$plusMinus_checkbox = isset( $saved_data[ WC_MMQ_PREFIX_PRO . 'qty_plus_minus_btn' ] ) && $saved_data[ WC_MMQ_PREFIX_PRO . 'qty_plus_minus_btn' ] == '1' ? 'checked' : false;
?>
<div class="wcmmq-section-panel">
<table class="wcmmq-table universal-setting">
    <thead>
        <tr>
            <th class="wcmmq-inside">
                <div class="wcmmq-table-header-inside">
                    <h3><?php echo esc_html__( 'Quantity Prefix/Suffix', 'wcmmq_pro' ); ?></h3>
                </div>
                
            </th>
            <th>
            <div class="wcmmq-table-header-right-side"></div>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="wcmmq-form-control">
                    <div class="form-label col-lg-6">
                        <label for="_wcmmq_qty_plus_minus_btn"><?php echo esc_html__( 'Quantity Button', 'wcmmq_pro' ); ?></label>
                    </div>
                    <div class="form-field col-lg-6">
                        <?php if( ! defined( 'WQPMB_VERSION' ) ){ ?>
                        <label class="switch">
                            <input 
                                value="1"  
                                name="data[<?php echo esc_attr( WC_MMQ_PREFIX ); ?>qty_plus_minus_btn]" 
                                <?php echo $plusMinus_checkbox; /* finding checked or null */ ?> 
                                type="checkbox" id="_wcmmq_qty_plus_minus_btn">
                            <div class="slider round"><!--ADDED HTML -->
                                <span class="on"><?php echo esc_html__('ON', 'wcmmq_pro'); ?></span><span class="off"><?php echo esc_html__('OFF', 'wcmmq_pro'); ?></span><!--END-->
                            </div>
                        </label>
                        <?php }else{?>
                        <a href="<?php echo admin_url( 'admin.php?page=ua-quanity-plus-minus-button' ); ?>" target="_blank"><?php echo esc_html__('Click Here to change.', 'wcmmq_pro'); ?></a>
                        <?php } ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="wcmmq-form-info">
                    
                </div> 
            </td>
        </tr>

        <tr>
            <td>
                <div class="wcmmq-form-control">
                    <div class="form-label col-lg-6">
                        <label for="wcmmq-prefix-quantity"><?php echo esc_html__( 'Prefix of Quantity', 'wcmmq_pro' ); ?></label>
                    </div>
                    <div class="form-field col-lg-6">
                        <?php 
                        $prefix = isset( $saved_data[WC_MMQ_PREFIX_PRO . 'prefix_quantity'] ) ? $saved_data[WC_MMQ_PREFIX_PRO . 'prefix_quantity'] : '';
                        $settings = array(
                            'textarea_name'     =>'data['.WC_MMQ_PREFIX_PRO.'prefix_quantity]',
                            'textarea_rows'     => 3,
                            'teeny'             => true,
                            );
                        wp_editor( wp_kses_post( $prefix ), 'wcmmq-prefix-quantity', $settings ); ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="wcmmq-form-info">
                    
                </div> 
            </td>
        </tr>

        <tr>
            <td>
                <div class="wcmmq-form-control">
                    <div class="form-label col-lg-6">
                        <label for="wcmmq-sufix-quantity"><?php echo esc_html__( 'Suffix of Quantity', 'wcmmq_pro' ); ?></label>
                    </div>
                    <div class="form-field col-lg-6">
                        <?php 
                        $sufix = isset( $saved_data[WC_MMQ_PREFIX_PRO . 'sufix_quantity'] ) ? $saved_data[WC_MMQ_PREFIX_PRO . 'sufix_quantity'] : '';
                        $settings = array(
                            'textarea_name'     =>'data['.WC_MMQ_PREFIX_PRO.'sufix_quantity]',
                            'textarea_rows'     => 3,
                            'teeny'             => true,
                            );
                        wp_editor( wp_kses_post( $sufix ), 'wcmmq-sufix-quantity', $settings ); ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="wcmmq-form-info">
                    
                </div> 
            </td>
        </tr>
    </tbody>
</table>

</div>
<?php    

}

add_action( 'wcmmq_form_panel_before_message','wcmmq_prefix_sufix_area' );












function wcmmq_pro_enqueue() {
    
    wp_register_script( 'wcmmq-pro-admin-script', WC_MMQ_PRO_BASE_URL . 'assets/js/admin.js', array( 'jquery','select2' ), WC_MMQ_PRO::getVersion(), true );
    wp_enqueue_script( 'wcmmq-pro-admin-script' );
    
    wp_register_style( 'wcmmq-pro_css', WC_MMQ_PRO_BASE_URL . 'assets/css/admin.css', false, WC_MMQ_PRO::getVersion() );
    wp_enqueue_style( 'wcmmq-pro_css' );
  
}
add_action( 'admin_enqueue_scripts', 'wcmmq_pro_enqueue' );
