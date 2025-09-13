<?php
$api_key = get_option('wcmlim_google_api_key');
$default_list_align = get_option('wcmlim_default_list_align');
$default_origin_center = get_option('wcmlim_default_origin_center');
$default_origin_center_modify = str_replace(' ', '+', $default_origin_center);

$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
$result = [];
$store_on_map_arr = [];
$mapid = 1;
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($default_origin_center_modify) . '&sensor=false&key=' . $api_key,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
));
$geocode = curl_exec($curl);
$output = json_decode($geocode);
curl_close($curl);
if (isset($output->results[0]->geometry->location->lat)) {
    $originlatitude = $output->results[0]->geometry->location->lat;
    $originlongitude = $output->results[0]->geometry->location->lng;
} else {
    $originlatitude = 0;
    $originlongitude = 0;
}
$origin_store_on_map_str = array(
    "<div class='locator-store-block'><h4>" . $default_origin_center . "</h4></div>",
    floatval($originlatitude),
    floatval($originlongitude),
    intval($mapid),
    'origin'
);
array_push($store_on_map_arr, $origin_store_on_map_str);
$mapid = 2;
foreach ($terms as $k => $term) {
    $slug = $term->slug;
    $term_meta = get_option("taxonomy_$term->term_id");
    $term_meta = array_map(function ($term) {
        if (!is_array($term)) {
            return $term;
        }
    }, $term_meta);
    $prod_stoc_detail = "<div class='map-prod-details'> <strong> Filter - </strong><br>";
    if ($_REQUEST['searchtype'] == 'product') {
        foreach ($_REQUEST['parameter_id'] as $key => $parameter_id) {
        /* for variable products code start */
            $var_stock_count = 0;
            $product = wc_get_product($parameter_id);
            if ($product->is_type('variable')){
                
                $variations = $product->get_available_variations();
            if (!empty($variations)) {
                $var_stock_status = '';
                foreach ($variations as $value) {
                    $variation_id = $value['variation_id'];
                    $variation_obj = new WC_Product_variation($variation_id);
                    $stockqty = $variation_obj->get_stock_quantity();

                    $var_location_stock = get_post_meta($variation_id, 'wcmlim_stock_at_' . $term->term_id, true);

                        $var_stock_count += intval($var_location_stock);

                    if( intval($var_stock_count) != 0 || $var_stock_count != ''  ){
                        $var_stock_status = 'true';
                    }
                }
            }
            }
        else {
            $prod_location_stock = get_post_meta($parameter_id, 'wcmlim_stock_at_' . $term->term_id, true);
            }

            if ($product->is_type('variable')){

                if (intval($var_stock_count) == 0) {
                    
                    $prod_stoc_detail = $prod_stoc_detail . "<a class='map_cont_prod_link map_prod_outstock' href='" . $product->get_permalink() . "' target='_blank'>" . $product->get_name() . "-<span> 0 <span></a>";
                } else {
                    $prod_stoc_detail = $prod_stoc_detail . "<a class='map_cont_prod_link map_prod_instock' href='" . $product->get_permalink() . "' target='_blank'>" . $product->get_name() . "-<span> " . $var_stock_count . "<span></a>";
                }

            }/* for variable products code end */
            else {

                    if (empty($prod_location_stock) || $prod_location_stock == '0' || $prod_location_stock == 0 ) {
                        $prod_stoc_detail = $prod_stoc_detail . "<a class='map_cont_prod_link map_prod_outstock' href='" . $product->get_permalink() . "' target='_blank'>" . $product->get_name() . "-<span> 0 <span></a>";
                    } else {
                        $prod_stoc_detail = $prod_stoc_detail . "<a class='map_cont_prod_link map_prod_instock' href='" . $product->get_permalink() . "' target='_blank'>" . $product->get_name() . "-<span> " . $prod_location_stock . "<span></a>";
                    }
            }
        
        }
    } else {
        foreach ($_REQUEST['parameter_id'] as $key => $cat_parameter_id) {
            $category = get_term_by('slug', $cat_parameter_id, 'product_cat');
            $cat_id = $category->term_id; // Get the ID of a given category
            // Get the URL of this category
            $cat_link = get_category_link($cat_id);
            $all_ids = get_posts(array(
                'post_type' => 'product',
                'numberposts' => -1,
                'post_status' => 'publish',
                'fields' => 'ids',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $cat_parameter_id, /*category name*/
                        'operator' => 'IN',
                    )
                ),
            ));
            $prod_location_stock_tmp = 0;
            foreach ($all_ids as $parameter_id) {
                $product = wc_get_product($parameter_id);
                $prod_location_stock = get_post_meta($parameter_id, 'wcmlim_stock_at_' . $term->term_id, true);
                $prod_location_stock_tmp = intval($prod_location_stock_tmp) + intval($prod_location_stock);
            }
            
            if (empty($prod_location_stock_tmp) || $prod_location_stock_tmp == '0' || $prod_location_stock_tmp == 0) {
                $prod_stoc_detail = $prod_stoc_detail . "<a class='map_cont_prod_link map_prod_outstock' href='" . $cat_link . "' target='_blank'>" . $cat_parameter_id . "</a>";
            } else {
                $prod_stoc_detail = $prod_stoc_detail . "<a class='map_cont_prod_link map_prod_instock' href='" . $cat_link . "' target='_blank'>" . $cat_parameter_id . "</a>";
            }
        }
    }
    $prod_stoc_detail = $prod_stoc_detail . "</div>";
    $get_address = $term_meta['wcmlim_street_number'] . ' ' . $term_meta['wcmlim_route'] . ' ' . $term_meta['wcmlim_locality'] . ' ' . $term_meta['wcmlim_administrative_area_level_1'] . ',' . $term_meta['wcmlim_country'] . ' - ' . $term_meta['wcmlim_postal_code'];
    $address = $term_meta['wcmlim_street_number'] . ' ' . $term_meta['wcmlim_route'] . ' ' . $term_meta['wcmlim_locality'] . ' ' . $term_meta['wcmlim_administrative_area_level_1'] . ' ' . $term_meta['wcmlim_postal_code'] . ' ' . $term_meta['wcmlim_country'];
    $address = str_replace(' ', '+', $address);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $api_key,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));
    $geocode = curl_exec($curl);
    $output = json_decode($geocode);
    curl_close($curl);
    $latitude = $output->results[0]->geometry->location->lat;
    $longitude = $output->results[0]->geometry->location->lng;
    $wcmlim_email = get_term_meta($term->term_id, 'wcmlim_email', true);
    $titletext = "<div class='locator-store-block' id='" . $term->term_id . "'><h4>" . $term->name . "</h4>";
    
    $site_url = get_site_url();
    $get_address_dir = str_replace(' ', '+', $get_address);
    if (isset($wcmlim_email) && !empty($wcmlim_email)) {
        $titletext = $titletext . "<p><span class='far fa-envelope'></span>" . $wcmlim_email . '</p>';
    }
    $titletext =  $titletext . "<p><span class='far fa-map'></span>" . $get_address . "</p>";
    $titletext =  $titletext . $prod_stoc_detail;
    $titletext =  $titletext . "<a class='marker-btn-1 btn btn-primary' target='_blank' href='https://www.google.com/maps/dir//$get_address_dir'> " . __('Direction', 'wcmlim') . " </a>";
    $titletext =  $titletext . "<a class='marker-btn-2 btn btn-primary' target='_blank' href='$site_url?locations=$slug'> " . __('Shop Now', 'wcmlim') . " </a></div>";
    $store_on_map_str = array(
        $titletext,
        floatval($latitude),
        floatval($longitude),
        intval($mapid),
        intval($term->term_id)
    );
    array_push($store_on_map_arr, $store_on_map_str);
    $mapid++;
}
update_option("store_on_map_prod_arr", json_encode($store_on_map_arr));

$store_on_map_arr = json_encode($store_on_map_arr);
echo $store_on_map_arr;
die();