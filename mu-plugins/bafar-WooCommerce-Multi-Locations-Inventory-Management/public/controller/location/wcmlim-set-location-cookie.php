<?php
$cookieTimeOption = get_option("wcmlim_set_location_cookie_time");
$shold = get_option("wcmlim_show_location_selection");
$cookieTime = intval($cookieTimeOption) ? $cookieTimeOption : 1;
if ($shold == "on") {
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        if (current_user_can('administrator', $current_user_id)){
            if (isset($selected_location)) {
                setcookie("wcmlim_selected_location", $selected_location, time() + ($cookieTime * 24 * 60 * 60), '/');
                $_COOKIE['wcmlim_selected_location'] = $selected_location;
            }
        }else {
            $specificLocation = get_user_meta($current_user_id, 'wcmlim_user_specific_location', true);	
            setcookie("wcmlim_selected_location", $specificLocation, time() + 36000, '/');
        }
    }if (isset($selected_location)) {
        setcookie("wcmlim_selected_location", $selected_location, time() + ($cookieTime * 24 * 60 * 60), '/');
        $_COOKIE['wcmlim_selected_location'] = $selected_location;
    } else {
        // unset cookies
        setcookie('wcmlim_selected_location', -1, -1, '/');
        unset($_COOKIE['wcmlim_selected_location']);
    }
} else {

    if (isset($selected_location)) {
        setcookie("wcmlim_selected_location", $selected_location, time() + ($cookieTime * 24 * 60 * 60), '/');
        $_COOKIE['wcmlim_selected_location'] = $selected_location;
    } else {
        // unset cookies
        setcookie('wcmlim_selected_location', -1, -1, '/');
        unset($_COOKIE['wcmlim_selected_location']);
    }
}