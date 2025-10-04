<?php
global $woocommerce;
$productID = sanitize_text_field($_POST['product_simple']);
if($productID == ""){
    $productID = trim($_POST['product_vid']);
}
$term_key = $_POST['term_key'];
$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
$term_id = '';
foreach($locations as $key=>$term){
        if($key == $term_key){

                $term_name = $term->name;
                $term_id = $term->term_id;
                
        }}

    
$product = wc_get_product( $productID );
if($term_id != "")
{
    $postmeta_stock_at_term = get_post_meta($productID, 'wcmlim_stock_at_' . $term_id, true);
$stz = 'wcmlim_allow_backorder_at_'.$term_id.'';
$is_backorder_enabled = get_post_meta($productID ,$stz,true);
if($is_backorder_enabled != "Yes" && $postmeta_stock_at_term >0){
    $response = "instk";
        echo $response;
}else {
    if($is_backorder_enabled == "Yes"){
        //then show button
        $response = "show_btn";
        echo $response;
    }else{
        if($postmeta_stock_at_term < 1){
            echo "ofs";

        }else{
            if($postmeta_stock_at_term > 1){
                echo "instk";
            }
            echo "hide";
        }									
    }
}
}
wp_die();