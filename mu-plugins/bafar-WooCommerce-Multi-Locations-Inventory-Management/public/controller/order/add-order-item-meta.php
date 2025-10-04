<?php
if (isset($cart_item['select_location'])) {
    $values =  array();
    foreach ($cart_item['select_location'] as $key => $value) {
        $values[$key] = $value;
    }

    if(get_option('wcmlim_allow_local_pickup') == 'on' && $chosen_shipping[0] == "wcmlim_pickup_location"){
        wc_add_order_item_meta($item_id, "Pickup Location", $values["location_name"]);

    }
    else
    {
        wc_add_order_item_meta($item_id, "Location", $values["location_name"]);

    }
    wc_add_order_item_meta($item_id, "_selectedLocationKey", $values["location_key"]);
    wc_add_order_item_meta($item_id, "_selectedLocTermId", $values["location_termId"]);
    setcookie("wcmlim_selected_location", $values["location_key"], time() + 36000, '/');
}