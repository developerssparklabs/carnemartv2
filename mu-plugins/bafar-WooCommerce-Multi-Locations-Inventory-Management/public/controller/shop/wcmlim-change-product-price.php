<?php

$setLocation = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";
if ($product->get_type() == 'simple') {
    $terms = get_terms(['taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0]);
    foreach ($terms as $k => $term) {
        $locRP = get_post_meta($product->get_id(), "wcmlim_regular_price_at_{$term->term_id}", true);
        $locSP = get_post_meta($product->get_id(), "wcmlim_sale_price_at_{$term->term_id}", true);
        $wRP = wc_price($locRP);
        $wSP = wc_price($locSP);
        if ($setLocation == $k) {
            if (!empty($locSP)) {
                $price_html = "<del>{$wRP}</del><ins>{$wSP}</ins>";
            } elseif (!empty($locRP)) {
                $price_html = $wRP;
            }
        }
    }
}
// 6068 show default variation price at locations on shop page code init
$default_var_price = get_option('wcmlim_enable_location_onshop_variable');
if($default_var_price == 'on'){
if ($product->get_type() == 'variable') {

    $default_attributes = $product->get_default_attributes();
    foreach($product->get_available_variations() as $variation_values ){
        foreach($variation_values['attributes'] as $key => $attribute_value ){
            $attribute_name = str_replace( 'attribute_', '', $key );
            $default_value = $product->get_variation_default_attribute($attribute_name);
            if( $default_value == $attribute_value ){
                $is_default_variation = true;
            } else {
                $is_default_variation = false;
                break; 
            }
        }
        if( $is_default_variation ){
            $_var_id = $variation_values['variation_id'];
            break; 
        }
    }
    
    $terms = get_terms(['taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0]);
    $selected_loc_id = $_COOKIE['wcmlim_selected_location'];
    $_var_id = isset($variation_values['variation_id']) ? $variation_values['variation_id'] : '';
    foreach($terms as $key=>$term){
        if($key == $selected_loc_id){
            $sel_term_id = $term->term_id;
        }}
        $locRP = get_post_meta($_var_id, "wcmlim_regular_price_at_{$sel_term_id}", true);
    
    foreach ($terms as $k => $term) {
        $locRP = get_post_meta($_var_id, "wcmlim_regular_price_at_{$sel_term_id}", true);
        
        $locSP = get_post_meta($_var_id, "wcmlim_sale_price_at_{$sel_term_id}", true);
        $wRP = wc_price($locRP);
        $wSP = wc_price($locSP);
        if ($setLocation == $k) {
            if (!empty($locSP)) {
                $price_html = "<del>{$wRP}</del><ins>{$wSP}</ins>";
            } elseif (!empty($locRP)) {
                $price_html = $wRP;
            }
        }
    }
}

if($product->get_type() == 'variable' && (empty($product->get_default_attributes()))){
    
    $terms = get_terms(['taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0]);
    $selected_loc_id = $_COOKIE['wcmlim_selected_location'];
    foreach($terms as $key=>$term){
        if($key == $selected_loc_id){
            $sel_term_id = $term->term_id;
        }
    }
    
    $variations = $product->get_available_variations(); $variations_id = wp_list_pluck( $variations, 'variation_id' );
    
    $locRP = array();
    $locSP = array();
    foreach ($variations_id as $k => $var_id) {
        
        $locRP[] = get_post_meta($var_id, "wcmlim_regular_price_at_{$sel_term_id}", true);
        
        $locSP[] = get_post_meta($var_id, "wcmlim_sale_price_at_{$sel_term_id}", true);
    }


    if(is_array($locRP) && is_array($locSP)){
        $locRP = array_unique($locRP);
        $locSP = array_unique($locSP);
        sort($locRP);
        sort($locSP);
        $cp = (int)count($locRP) - 1;
        $cps = (int)count($locSP) - 1;

        $rminPrice = wc_price($locRP[0]);
        $rmaxPrice = wc_price($locRP[$cp]);

        

        $sminPrice = wc_price($locSP[0]);
        $smaxPrice = wc_price($locSP[$cps]);
        if($locSP[0] != '' && $locSP[$cps] != ''){
            $price_html = "<del>{$rminPrice} - {$rmaxPrice}</del>  <ins>{$sminPrice} - {$smaxPrice}</ins>";
        }else{
            $price_html = "<ins>{$rminPrice} - {$rmaxPrice}</ins>";

        }
    }
    
}

}
// show default variation price at locations on shop page code end
return $price_html;