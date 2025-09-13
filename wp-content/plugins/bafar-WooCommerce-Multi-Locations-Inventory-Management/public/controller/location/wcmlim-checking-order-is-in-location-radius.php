<?php
global $woocommerce;
$rates_arr = array();
$google_api_key = get_option('wcmlim_google_api_key');
$destination = '';
    $country =  $package['destination']['country'];
    $state =  $package['destination']['state'];
    $postcode =  $package['destination']['postcode'];
    $city = $package['destination']['city'];

    $destination = $country."+".$state."+".$postcode."+".$city;

    $cart = $woocommerce->cart->cart_contents;
    if(!empty($cart))
    {
    foreach ($cart as $array_item) {

    if (isset($array_item['select_location']['location_name'])) {
        $product_name = $array_item['data']->get_name();
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
        foreach ($terms as $term) {
            if ($term->name == $array_item['select_location']['location_name']) {
                $_locRadius = 	get_term_meta( $term->term_id, 'wcmlim_service_radius_for_location', true );
                $term_meta = get_option("taxonomy_$term->term_id");
                $term_meta = array_map(function ($term) {
                    if (!is_array($term)) {
                    return $term;
                    }
                }, $term_meta);
                $__spare = implode(" ", array_filter($term_meta));
                $__seleOrigin[] = str_replace(" ", "+", $__spare);
                if (isset($__seleOrigin[0])) {
                    $origins = $__seleOrigin[0];
                }
                

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://maps.googleapis.com/maps/api/distancematrix/json?units=metrics&origins=" . $origins . "&destinations=" . $destination . "&key={$google_api_key}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                ));
    
                $response = curl_exec($curl);
                $response_arr = json_decode($response);
                curl_close($curl);
                foreach ($response_arr->rows as $r => $t) {
                    foreach ($t as $key => $value) {
                        foreach ($value as $a => $b) {
                            if ($b->status == "OK") {
                                $dis = explode(" ", $b->distance->text);
                        
                            }
                        }
                    }
                }
         

            if ($_locRadius < $dis[0]) {
                wc_clear_notices();
                wc_add_notice("La distancia de tu ubicación parece estar muy lejos del punto de entrega...", "error");
            }else {
                wc_clear_notices();
            }
            }
        }
    }
}
}
return $rates;