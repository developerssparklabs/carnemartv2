<?php
$pass_location = isset($_REQUEST['select_location']) ? $_REQUEST['select_location'] : "";
$select_loc_va = get_option('wcmlim_select_loc_val');


$product_id = $_REQUEST['add-to-cart'];
$manage_stock =  get_post_meta($product_id,'_manage_stock',true);

if ($manage_stock != 'no') {
    if ($pass_location == -1) {
        wc_add_notice(__( $select_loc_va , 'wcmlim'), 'error');
        $passed = false;
        return $passed;
    }
    else
    {
    $passed = true;
    return $passed;
    }
}
else if ($manage_stock == 'no') {
    $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    $location_id = '';
    foreach($locations as $key=>$term){
        if($key == $pass_location){
            $location_id = $term->term_id;
        }
    }
    $allow_backorder_each_location = 'on';
    
    if($allow_backorder_each_location == "on")
    {
        $location_pid_backorder = get_post_meta($product_id, 'wcmlim_allow_backorder_at_' . $location_id, true);
        
        if($location_pid_backorder == 'Yes')
        {
            $passed = true;
            return $passed;
        }
        else
    {
    $passed = false;
    return $passed;
    }
        
    }
    else
    {  
        $slCookie = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";

        if($slCookie =="-1") 
        {
            wc_add_notice(__( $select_loc_va , 'wcmlim'), 'error');
            $passed = false;
            return $passed;
        }
        
    }
}
else
{
    $passed = true;
    return $passed;
}
