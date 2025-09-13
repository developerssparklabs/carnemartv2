<?php
global $prepare_op;
$wcmlim_enable_price = 'on';
$product_id = $_POST['product_id'];
$isLcex = get_option("wcmlim_exclude_locations_from_frontend");
if (!empty($isLcex)) {
$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isLcex));
} else {
$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
}
$manage_stock = get_post_meta($product_id, '_manage_stock', true);
$indexing = 1;
foreach ($terms as $k => $term) {
$location_id = $term->term_id;										
$loc_stock_pid = get_post_meta($product_id, "wcmlim_stock_at_{$location_id}", true);
$loc_each_backorder = get_post_meta($product_id, 'wcmlim_allow_backorder_at_'.$location_id, true);

$stock_regular_price = get_post_meta($product_id, "wcmlim_regular_price_at_{$location_id}", true);
$stock_sale_price = get_post_meta($product_id, "wcmlim_sale_price_at_{$location_id}", true);

$pml_product = wc_get_product( $product_id );
$price = $pml_product->get_price();
$sales_price = $pml_product->get_sale_price();
$loc_start_time = get_term_meta($location_id, "wcmlim_start_time", true); 
$loc_end_time = get_term_meta($location_id, "wcmlim_end_time", true); 

if ($wcmlim_enable_price == 'on') {
$loc_stock_pid = get_post_meta($product_id, "wcmlim_stock_at_{$location_id}", true);
$stock_wcmlim_regular_price = get_post_meta($product_id, "wcmlim_regular_price_at_{$location_id}", true);
$stock_wcmlim_stock_sale_price = get_post_meta($product_id, "wcmlim_sale_price_at_{$location_id}", true);
if((!empty($stock_wcmlim_stock_sale_price)))
{
$stock_price = $stock_wcmlim_stock_sale_price;
}
elseif(($stock_wcmlim_regular_price != 0 && $stock_wcmlim_stock_sale_price == 0) || ($stock_wcmlim_regular_price != '' && $stock_wcmlim_stock_sale_price == ''))
{
$stock_price = $stock_wcmlim_regular_price;
}
elseif(($stock_wcmlim_regular_price == 0 && $stock_wcmlim_stock_sale_price == 0) || ($stock_wcmlim_regular_price == '' && $stock_wcmlim_stock_sale_price == ''))
{
$stock_price = $price;
}
else
{
$stock_price = $price;
}
}elseif((!empty($sales_price)))
{
$stock_price = $sales_price;
}
elseif(($stock_regular_price != 0 && $stock_sale_price == 0) || ($stock_regular_price != '' && $stock_sale_price == ''))
{
$stock_price = $price;
}
elseif(($stock_regular_price == 0 && $stock_sale_price == 0) || ($stock_regular_price == '' && $stock_sale_price == ''))
{
$stock_price = $price;
}
else
{
$stock_price = $price;
}



$shipping_cost = $this->wcmlim_calculate_shipping($location_id);
if($loc_start_time != '' && $loc_end_time != ''){
$prepare_op[] = array(
"indexing" => intval($indexing),
"location_id" => intval($location_id),
"loc_stock_pid" => intval($loc_stock_pid),
"loc_each_backorder" => $loc_each_backorder,
"stock_price" => floatval($stock_price),
"shipping_cost" => $shipping_cost,
"location_start_time" => $loc_start_time,
"location_end_time" => $loc_end_time
);
}
else{
$prepare_op[] = array(
"indexing" => intval($indexing),
"location_id" => intval($location_id),
"loc_stock_pid" => intval($loc_stock_pid),
"loc_each_backorder" => $loc_each_backorder,
"stock_price" => floatval($stock_price),
"shipping_cost" => $shipping_cost
);
}
$indexing++;
}

if($manage_stock == 'yes')
{
echo json_encode($prepare_op);
}
die();