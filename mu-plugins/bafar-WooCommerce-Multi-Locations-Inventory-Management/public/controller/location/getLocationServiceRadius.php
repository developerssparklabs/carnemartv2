<?php
$ExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
if (!empty($ExcLoc)) {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExcLoc));
} else {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
}

foreach ($terms as $key => $value) {
    if($distanceKey == $key){
        $_locRadius = 	get_term_meta( $value->term_id, 'wcmlim_service_radius_for_location', true );
    }
}
return $_locRadius;