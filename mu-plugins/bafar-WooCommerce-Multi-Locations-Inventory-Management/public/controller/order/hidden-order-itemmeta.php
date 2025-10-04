<?php
$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
if(count($locations) == '0'){
    return 0;
    wp_die();
}
$args[] = '_selectedLocationKey';
$args[] = '_selectedLocTermId';
return $args;