<?php
if(get_option('wcmlim_enable_userspecific_location') == "on"){

    // Get the current user ID
    $user_id = get_current_user_id();

    // Get the user specific location
    $user_location = get_user_meta( $user_id, 'wcmlim_user_specific_location', true );
    if($user_location == "-1" || $user_location == ""){
        return $availability;
    }
    
    $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    foreach($locations as $key=>$term){
        if($key == $user_location){
            $user_location_termid = $term->term_id;
        }
    }

    //get stock at location
    $stock = get_post_meta( $product->get_id(), 'wcmlim_stock_at_'.$user_location_termid, true );

    if ( $product->is_in_stock() && $product->managing_stock() ) $availability = $stock . __( ' in stock', 'woocommerce' );
    return $availability;
}else{
    return $availability;
}