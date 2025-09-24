<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/*--------order edit page----------*/
add_filter('woocommerce_order_actions', 'wizbee_easy_duplicate_order_action', 10, 1);
function wizbee_easy_duplicate_order_action($actions) {
    $actions['duplicate_order'] = __('Duplicate Order', 'easy-duplicate-woo-order');
    return $actions;
}
add_action('woocommerce_order_action_duplicate_order', 'wizbee_duplicate_order_logic');

/*-----------------order table button part---------------------*/

add_filter('woocommerce_admin_order_actions', 'wizbee_add_duplicate_order_button', 10, 2);
function wizbee_add_duplicate_order_button($actions, $order) {
    $actions['duplicate_order'] = [
        'url'    => wp_nonce_url(admin_url('admin-post.php?action=wizbee_duplicate_order&order_id=' . $order->get_id()), 'wizbee_duplicate_order_' . $order->get_id()),
        'name'   => __('Duplicate Order', 'easy-duplicate-woo-order'),
        'action' => 'wb-duplicate'
    ];
    return $actions;
}
function wizbee_button_custom_styles() {
    wp_register_style('wb-duplicate-order-inline', false, [], '2.3.1');
    wp_enqueue_style('wb-duplicate-order-inline');
    $inline_css = '.wc-action-button-wb-duplicate::after {content: "\f105" !important;}';
    wp_add_inline_style('wb-duplicate-order-inline', $inline_css);
}
add_action('admin_enqueue_scripts', 'wizbee_button_custom_styles');


// Handle the button request
add_action('admin_post_wizbee_duplicate_order', 'wizbee_handle_action_from_button');
function wizbee_handle_action_from_button() {
    $order_id = isset($_GET['order_id']) ? intval(wp_unslash($_GET['order_id'])) : 0;
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (!$order_id || !$nonce || !wp_verify_nonce($nonce, 'wizbee_duplicate_order_' . $order_id)) {
        wp_die(esc_html__('Invalid nonce specified', 'easy-duplicate-woo-order'));
    }
    $order = wc_get_order($order_id);

    if (!$order) {
        wp_die(esc_html__('Order not found', 'easy-duplicate-woo-order'));
    }

    wizbee_duplicate_order_logic($order);
    
}
