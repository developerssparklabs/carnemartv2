<?php
$customcss_enable = get_option('wcmlim_custom_css_enable');

wp_enqueue_style($this->plugin_name . '_chosen_css_public', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/chosen.min.css', array(), $this->version . rand(), 'all');
wp_enqueue_style($this->plugin_name, plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-public.css', array(), $this->version . rand(), 'all');

// theme is equal to reychild
$current_theme = wp_get_theme();

// Get theme name
$theme_name = $current_theme->get('Name');

if ($theme_name == 'Rey Child') {
    wp_enqueue_style($this->plugin_name . '-reychild.min.css', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-reychild.min.css', array(), $this->version . rand(), 'all');
}
if ($theme_name == 'Woodmart Child') {
    wp_enqueue_style($this->plugin_name . '-woodmart.min.css', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-reychild.min.css', array(), $this->version . rand(), 'all');
}

wp_enqueue_style($this->plugin_name . "-popup-css", plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-popup-min.css', array(), $this->version . rand(), 'all');


if ($customcss_enable == "") {
    // wp_enqueue_style($this->plugin_name . '_frontview_css', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-frontview-min.css', array(), $this->version . rand(), 'all');
}

$advanceListView = get_option('wcmlim_radio_loc_format');
$optiontype_loc = get_option('wcmlim_select_or_dropdown');
if ($advanceListView == "advanced_list_view" && $optiontype_loc == 'on') {
    wp_enqueue_style($this->plugin_name . '_list_view_pro', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-list-view-pro-min.css', array(), $this->version . rand(), 'all');
    $theme = wp_get_theme();
    if ($theme['Name'] == 'Astra') {
        wp_enqueue_style($this->plugin_name . '_list_view_pro_astra', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/wcmlim-astra-list-view-min.css', array(), $this->version . rand(), 'all');
    }

}
$theme = wp_get_theme();
if ($theme['Name'] == 'Flatsome') {
    wp_enqueue_style($this->plugin_name . '_flatsome_css_public', plugin_dir_path(plugin_dir_url(__DIR__)) . 'css/flatsome_css_public-min.css', array(), $this->version . rand(), 'all');
}