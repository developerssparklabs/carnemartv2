<?php
//20 de enero sparklabs
$termselect = isset($_POST['selectedstoreValue']) ? intval($_POST['selectedstoreValue']) : "";
$termselectocid = isset($_POST['termidExists']) ? intval($_POST['termidExists']) : "";
$selected_location = $this->get_selected_location();

if (empty($selected_location)) {
    $selected_location = $termselectocid;
}


$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));

$result = array();
$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();
$current_ui = isset($current_user_id) ? $current_user_id : "";
$user_selected_location = get_user_meta($current_ui, 'wcmlim_user_specific_location', true);
$restricUsers = get_option('wcmlim_enable_userspecific_location');
$roles = $current_user->roles;

if ($restricUsers == 'on' && $roles[0] == 'customer') {
    foreach ($terms as $i => $term) {
        if ($user_selected_location == $i) {
            $regeionid = get_term_meta($term->term_id, 'wcmlim_locator', true);
            $term_locator = get_term_meta($term->term_id, 'wcmlim_locator', true);
            $area_name = get_term_meta($term->term_id, 'wcmlim_areaname', true);
            $id = strval($term->term_id);
            $name = strval($term->name);
            $slug = strval($term->slug);
            $classname = 'wclimloc_' . $slug . ' ' . 'wclimstoreloc_' . $term_locator;
            $value = $i;
            $selected = "selected";
            $result[$i]['regeionid'] = $regeionid;
            $result[$i]['selected'] = $selected_location;
            $result[$i]['term_id'] = $id;
            $result[$i]['classname'] = $classname;
            $result[$i]['vkey'] = $value;
            $result[$i]['location_name'] = $name;
            $result[$i]['location_slug'] = $slug;
            $result[$i]['location_storeid'] = $term_locator;
            $result[$i]['wcmlim_areaname'] = $area_name;
        }
    }
} else {
    foreach ($terms as $i => $term) {

        $regeionid = get_term_meta($term->term_id, 'wcmlim_locator', true);
        //le faltaba esta validacion s8k keikos sparklabs
        $centro_activo = get_term_meta($term->term_id, 'centro_activo', true);
        if ($centro_activo != "1") {
            continue;
        }
        if ($regeionid == $termselect) {

            $term_locator = get_term_meta($term->term_id, 'wcmlim_locator', true);
            $area_name = get_term_meta($term->term_id, 'wcmlim_areaname', true);
            $id = strval($term->term_id);
            $name = strval($term->name);
            $slug = strval($term->slug);
            $classname = 'wclimloc_' . $slug . ' ' . 'wclimstoreloc_' . $term_locator;
            $value = $i;
            $selected = "";
            if (preg_match('/^\d+$/', $selected_location)) {
                if ($selected_location == $i) {
                    $selected = 'selected';
                } else {
                    $selected = "";
                }
            }
            $result[$i]['regeionid'] = $regeionid;
            $result[$i]['selected'] = $selected_location;
            $result[$i]['term_id'] = $id;
            $result[$i]['classname'] = $classname;
            $result[$i]['vkey'] = $value;
            $result[$i]['location_name'] = $name;
            $result[$i]['location_slug'] = $slug;
            $result[$i]['location_storeid'] = $term_locator;
            $result[$i]['wcmlim_areaname'] = $area_name;

        }
    }
}

echo json_encode($result);
die();