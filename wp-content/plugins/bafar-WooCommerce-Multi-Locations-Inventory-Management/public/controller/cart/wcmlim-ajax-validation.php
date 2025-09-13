<?php
global $woocommerce;
$product_id = $_POST['product_id'];
$manage_stock = get_post_meta($product_id, '_manage_stock', true);

if($manage_stock == "yes")
{
    $current_stock = get_post_meta($product_id, '_stock', true);
    if($current_stock <= 0){
        echo '0';
        wp_die();
    }else{
        echo '1';
        wp_die();
    }
}
else 
{
    $current_stock_status = get_post_meta($product_id, '_stock_status', true);
    if ($current_stock_status == 'instock') {
        echo '1';
    }
    else
    {
        echo '0';
    }
}
wp_die();