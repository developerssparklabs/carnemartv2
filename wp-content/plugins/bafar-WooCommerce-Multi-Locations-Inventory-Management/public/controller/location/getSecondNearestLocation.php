<?php


		
$ExcLoc = get_option("wcmlim_exclude_locations_from_frontend");
if (!empty($ExcLoc)) {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $ExcLoc));
} else {
    $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
}
$addresses = array();
$dnumber = array();
foreach ($addresses as $ad) {
    $dnumber[] = $ad["value"];
}
sort($dnumber, SORT_NUMERIC);
$smallest = array_shift($dnumber);
$smallest_2nd = array_shift($dnumber);
foreach ($addresses as $e => $v) {
    if ($smallest_2nd == $v["value"]) {
        $finalKeyOfLocation = $e;
    }
}
$secondNearLocKey = isset($finalKeyOfLocation) ? $finalKeyOfLocation : "";



foreach ($terms as $index => $term) {
        if ($index == $secondNearLocKey) {
            $secNearStore[] = $term->name;
        }
}

foreach ($addresses as $k => $address) {
    if ($secondNearLocKey == $k) {
        if ($dis_unit == "kms") {
            $dis_in_un = $address["dis_in_un"];
        } elseif ($dis_unit == "miles") {
            $dis_in_un = round($address["value"] * 0.621, 1) . ' miles';
        } elseif ($dis_unit == "none") {
            $dis_in_un = $address["dis_in_un"];
        }
        $secNearStore[] = $dis_in_un;
    }
}
$secNearStore[] = $secondNearLocKey;
return $secNearStore;