<?php
$GoogleAPIKEY = get_option('wcmlim_google_api_key');
$geocode = file_get_contents("https://maps.google.com/maps/api/geocode/json?address=$address&key=$GoogleAPIKEY");
$json = json_decode($geocode);
$latitude = $json->results[0]->geometry->location->lat;
$longitude = $json->results[0]->geometry->location->lng;

// get zipcode
$geocode = file_get_contents("https://maps.google.com/maps/api/geocode/json?latlng=$latitude,$longitude&key=$GoogleAPIKEY");
$json = json_decode($geocode);

foreach($json->results[0]->address_components as $adr_node) {
    if($adr_node->types[0] == 'postal_code') {
        $postal_code =  $adr_node->long_name;
    }
    if($adr_node->types[0] == 'country') {
    $country = $adr_node->short_name;
    }
    if($adr_node->types[0] == 'administrative_area_level_1') {
    $state = $adr_node->short_name;
    }
    if($adr_node->types[0] == 'locality') {
    $city = $adr_node->long_name;
    } 
}
$addressString = array(
'postal_code' => $postal_code,
'country' => $country,
'state' => $state,
'city' => $city
);

return $addressString;
wp_die();