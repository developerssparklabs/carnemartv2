<?php
$location_id = get_queried_object_id();
$setLocation = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";
$exclExists = get_option("wcmlim_exclude_locations_from_frontend");
if (!empty($exclExists)) {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $exclExists));
} else {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
}
foreach ($terms as $k => $term) {
    $term_id = $term->term_id;
    if ((empty($setLocation))) {

        if($term_id == $location_id)
        {
            $_COOKIE['wcmlim_selected_location'] = $k;
            $_COOKIE['wcmlim_nearby_location'] = $k;
            $_COOKIE['wcmlim_selected_location_termid'] = $term_id;
            setcookie("wcmlim_selected_location", $k, time() + 36000, '/');
            $autodetect_by_maxmind = get_option('wcmlim_enable_autodetect_location_by_maxmind');
    if($autodetect_by_maxmind != 'on'){
            setcookie("wcmlim_nearby_location", $k, time() + 36000, '/');
    }
            setcookie("wcmlim_selected_location_termid", $term_id, time() + 36000, '/');
        }
    }
    else
    {
        if($term_id == $location_id)
        {
            if($setLocation != $k)
            {
                $_COOKIE['wcmlim_selected_location'] = $k;
                $_COOKIE['wcmlim_nearby_location'] = $k;
                $_COOKIE['wcmlim_selected_location_termid'] = $term_id;
                setcookie("wcmlim_selected_location", $k, time() + 36000, '/');
                $autodetect_by_maxmind = get_option('wcmlim_enable_autodetect_location_by_maxmind');
    if($autodetect_by_maxmind != 'on'){
                setcookie("wcmlim_nearby_location", $k, time() + 36000, '/');
    }
                setcookie("wcmlim_selected_location_termid", $term_id, time() + 36000, '/');
            }
        }
    }
}
