<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add a new tab to WooCommerce settings
add_filter('woocommerce_settings_tabs_array', 'wizbee_add_settings_tab', 50);
function wizbee_add_settings_tab($settings_tabs) {
    $settings_tabs['wizbee_duplicate_order'] = __('Duplicate Order', 'easy-duplicate-woo-order');
    return $settings_tabs;
}

// Add settings to the new tab
add_action('woocommerce_settings_wizbee_duplicate_order', 'wizbee_add_settings_tab_settings');
function wizbee_add_settings_tab_settings() {
	?>
    <div>
        <h3><strong><?php _e('Enjoying Easy Duplicate Woo Order?', 'easy-duplicate-woo-order'); ?></strong></h3>
        <p><?php _e('If you are enjoying Easy Duplicate Woo Order and find it useful, please consider supporting us.
Maintaining and regularly updating a plugin requires significant time and effort. Your contributions help us keep the plugin current and free for everyone.<br><strong>You can support us by leaving a positive review or buying us a coffee.</strong>', 'easy-duplicate-woo-order'); ?></p>
        
        <p>
            <a class="button-secondary" href="https://wordpress.org/support/plugin/easy-duplicate-woo-order/reviews/#new-post" target="_blank"><?php _e('Leave a Review', 'easy-duplicate-woo-order'); ?></a>
            <a class="button-primary" href="https://www.paypal.com/donate/?hosted_button_id=Z8NFDWW8RSDVL" target="_blank"><?php _e('Buy us a coffee', 'easy-duplicate-woo-order'); ?></a>
        </p>
    </div>
    <?php
	    woocommerce_admin_fields(wizbee_get_settings());
}

// Save the settings
add_action('woocommerce_update_options_wizbee_duplicate_order', 'wizbee_update_settings');
function wizbee_update_settings() {
    woocommerce_update_options(wizbee_get_settings());
}


function wizbee_get_settings() {
    $order_statuses = wc_get_order_statuses();
    $settings = array(
        'section_title' => array(
            'name'     => __('Duplicate Order Settings', 'easy-duplicate-woo-order'),
            'type'     => 'title',
            'desc'     => '',
            'id'       => 'wizbee_duplicate_order_section_title'
        ),
        'order_status' => array(
            'name'     => __('New Order Status', 'easy-duplicate-woo-order'),
            'type'     => 'select',
            'desc'     => __('Select the order status for the new duplicated order.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_status',
            'options'  => $order_statuses,
            'default'  => 'wc-pending',
        ),
        'copy_old_price' => array(
            'name'     => __('Copy Old Price', 'easy-duplicate-woo-order'),
            'type'     => 'checkbox',
            'desc'     => __('Enable copying of price from the original order.<br>Enable if you use multi-currency. This option will copy the currency data and the price together.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_copy_old_price',
            'default'  => 'yes',
        ),
		'apply_coupons' => array(
            'name'     => __('Apply Coupons', 'easy-duplicate-woo-order'),
            'type'     => 'checkbox',
            'desc'     => __('Enable copying and applying of coupons from the original order.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_apply_coupons',
            'default'  => 'yes',
            'class'    => 'coupons-options',
        ),
        'copy_fees' => array(
            'name'     => __('Copy Fee', 'easy-duplicate-woo-order'),
            'type'     => 'checkbox',
            'desc'     => __('Enable copying of fees from the original order.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_copy_fees',
            'default'  => 'yes',
            'class'    => 'fee-options',
        ),
        'copy_shipping' => array(
            'name'     => __('Copy Shipping', 'easy-duplicate-woo-order'),
            'type'     => 'checkbox',
            'desc'     => __('Enable copying of shipping information.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_copy_shipping',
            'default'  => 'yes',
            'class'    => 'ship-options',
        ),
		'add_order_menu' => array(
            'name'     => __('Add order page link in the menu', 'easy-duplicate-woo-order'),
            'type'     => 'checkbox',
            'desc'     => __('Add "Order" in the main admin menu to access order page without going to WooCommerce >> Orders.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_add_order_menu',
            'default'  => 'yes',
        ),
		'add_order_topbar' => array(
            'name'     => __('Add Orders link in the top admin bar', 'easy-duplicate-woo-order'),
            'type'     => 'checkbox',
            'desc'     => __('Enable/Disable "Orders" link in the WordPress top admin bar.', 'easy-duplicate-woo-order'),
            'id'       => 'wizbee_duplicate_order_add_order_topbar',
            'default'  => 'yes',
        ),
        'section_end' => array(
            'type'     => 'sectionend',
            'id'       => 'wizbee_duplicate_order_section_end'
        ),
    );
    return $settings;
}




function wizbee_move_orders_to_main_menu() {
    $enabled = get_option('wizbee_duplicate_order_add_order_menu', 'yes');
    if ($enabled === 'yes') {
        add_menu_page(
            __('Orders', 'easy-duplicate-woo-order'),
            __('Orders', 'easy-duplicate-woo-order'),
            'manage_woocommerce',
            'admin.php?page=wc-orders',
            '',
            'dashicons-cart',
            55.5
        );
    }
}
add_action('admin_menu', 'wizbee_move_orders_to_main_menu', 99);


function wizbee_add_orders_to_admin_bar($wp_admin_bar) {
    $enabled = get_option('wizbee_duplicate_order_add_order_topbar', 'yes');
    if ($enabled === 'yes' && current_user_can('manage_woocommerce')) {
        $wp_admin_bar->add_node(array(
            'id'    => 'wizbee-orders-link',
            'title' => '<span class="ab-icon dashicons dashicons-cart"></span><span class="ab-label">' . __('Orders', 'easy-duplicate-woo-order') . '</span>',
            'href'  => admin_url('admin.php?page=wc-orders'),
			
            'meta'  => array(
                'class' => 'wizbee-orders-link',
                'title' => __('Go to Orders page', 'easy-duplicate-woo-order'),
            ),
        ));
    }
}
add_action('admin_bar_menu', 'wizbee_add_orders_to_admin_bar', 99);