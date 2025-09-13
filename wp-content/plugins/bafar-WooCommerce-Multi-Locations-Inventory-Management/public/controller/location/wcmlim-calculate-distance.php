<?php
$api_key = get_option('wcmlim_google_api_key');
$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
$search_lat = isset($_POST['search_lat']) ? $_POST['search_lat'] : false;
$search_lng = isset($_POST['search_lng']) ? $_POST['search_lng'] : false;
$calclate_dist = array();
foreach ($terms as $k => $term) {
    $term_meta = get_option("taxonomy_$term->term_id");
    $term_meta = array_map(function ($term) {
        if (!is_array($term)) {
            return $term;
        }
    }, $term_meta);
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
    $theta = $search_lng - $longitude;
    $dist = sin(deg2rad($search_lat)) * sin(deg2rad($latitude)) +  cos(deg2rad($search_lat)) * cos(deg2rad($latitude)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $slug = $term->slug;
    $get_address = $term_meta['wcmlim_street_number'] . ' ' . $term_meta['wcmlim_route'] . ' ' . $term_meta['wcmlim_locality'] . ' ' . $term_meta['wcmlim_administrative_area_level_1'] . ',' . $term_meta['wcmlim_country'] . ' - ' . $term_meta['wcmlim_postal_code'];
    $get_address_dir = str_replace(' ', '+', $get_address);
    $wcmlim_email = get_term_meta($term->term_id, 'wcmlim_email_regmanager', true);
    $html = "<div class='wcmlim-map-sidebar-list' id='$term->term_id'>
    <h4> $term->name </h4>
    <p class='location-address'>
        <span class='far fa-building'></span>
        <span> $get_address </span>
        <br />";

        if (isset($wcmlim_email) && !empty($wcmlim_email)) {
            $html = $html ."<span class='far fa-envelope-open'></span>
            <span> $wcmlim_email</span>";
        }
    
        $site_url = get_site_url();
        $html = $html ."</p>
    <button class='btn btn-primary' onclick=window.open('https://www.google.com/maps/dir/$get_address_dir', '_blank');>
    Direction </button>
    <button class='btn btn-primary' onclick=window.open('$site_url?locations=$slug', '_blank');>
        Shop Now </button>
</div>";							
    $tmp_arr = array(
        "id" =>  $term->term_id,
        "distance" => $miles
    );
    array_push($calclate_dist, $tmp_arr);
}
$keys = array_column($calclate_dist, 'distance');
array_multisort($keys, SORT_ASC, $calclate_dist);
$calclate_dist = json_encode($calclate_dist);
echo $calclate_dist;
die();