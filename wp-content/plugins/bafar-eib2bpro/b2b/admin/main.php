<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Main
{
    public static function run()
    {
        if (eib2bpro_option('badge-b2b')) {
            delete_option('eib2bpro_badge-b2b');
        }
        self::main();
    }

    public static function main()
    {

        if (in_array(eib2bpro_get('section', 'main'), apply_filters('eib2bpro_disable_nav_items', []))) {
            die(esc_html__('Not allowed', 'eib2bpro'));
        }

        switch (eib2bpro_get('section', 'main')) {
            case 'offers':
                Offers::run();
                break;
            case 'groups':
                Groups::run();
                break;
            case 'quote':
                Quote::run();
                break;
            case 'rules':
                Rules::run();
                break;
            case 'fields':
                Registration::run();
                break;
            case 'settings':
                Settings::run();
                break;
            case 'bulk':
                Bulk::run();
                break;
            case 'toolbox':
                Toolbox::run();
                break;
            case 'main':
                Dashboard::run();
                break;
        }
    }

    public static function hooks()
    {
        add_action('init', '\EIB2BPRO\B2b\Admin\Main::register_post_types');

        // categories
        add_action('product_cat_add_form_fields', '\EIB2BPRO\B2b\Admin\Category::product_cat_add_form_fields', 100, 1);
        add_action('product_cat_edit_form_fields', '\EIB2BPRO\B2b\Admin\Category::product_cat_add_form_fields', 1000, 1);
        add_action('edited_product_cat', '\EIB2BPRO\B2b\Admin\Category::edited_product_cat', 10, 1);
        add_action('create_product_cat', '\EIB2BPRO\B2b\Admin\Category::edited_product_cat', 10, 1);

        // products
        add_filter('woocommerce_product_data_tabs', '\EIB2BPRO\B2b\Admin\Product::woocommerce_product_data_tabs');
        add_action('woocommerce_product_data_panels', '\EIB2BPRO\B2b\Admin\Product::woocommerce_product_data_panels');
        add_action('save_post', '\EIB2BPRO\B2b\Admin\Product::woocommerce_product_data_panels_save', 10, 1);

        add_action('woocommerce_product_options_pricing', '\EIB2BPRO\B2b\Admin\Product::woocommerce_product_options_pricing', 99);
        add_action('woocommerce_process_product_meta', '\EIB2BPRO\B2b\Admin\Product::woocommerce_process_product_meta');
        add_action('woocommerce_variation_options_pricing', '\EIB2BPRO\B2b\Admin\Product::woocommerce_variation_options_pricing', 99, 3);
        add_action('woocommerce_save_product_variation', '\EIB2BPRO\B2b\Admin\Product::woocommerce_save_product_variation', 10, 2);

        add_filter('parse_query', '\EIB2BPRO\B2b\Admin\Main::hide_offer');

        // orders
        add_filter('woocommerce_order_get_formatted_billing_address', '\EIB2BPRO\B2b\Admin\Orders::woocommerce_order_get_formatted_billing_address', 10, 3);
        add_filter('woocommerce_admin_billing_fields', '\EIB2BPRO\B2b\Site\Registration::woocommerce_billing_fields', 9999, 1);
        add_filter('woocommerce_billing_fields', '\EIB2BPRO\B2b\Site\Registration::woocommerce_billing_fields', 9999, 1);
        add_filter('woocommerce_ajax_get_customer_details', '\EIB2BPRO\B2b\Admin\Orders::woocommerce_ajax_get_customer_details', 10, 3);

        // users

        add_action('user_new_form', '\EIB2BPRO\B2b\Admin\User::show_user_profile', 100, 1);
        add_action('show_user_profile', '\EIB2BPRO\B2b\Admin\User::show_user_profile', 100, 1);
        add_action('edit_user_profile', '\EIB2BPRO\B2b\Admin\User::show_user_profile', 100, 1);
        add_action('personal_options_update', '\EIB2BPRO\B2b\Admin\User::edit_user_profile_update');
        add_action('edit_user_profile_update', '\EIB2BPRO\B2b\Admin\User::edit_user_profile_update');
        add_action('user_register', '\EIB2BPRO\B2b\Admin\User::edit_user_profile_update');
        add_action('delete_user', '\EIB2BPRO\B2b\Admin\Toolbox::clear_users_cache', 999, 1);
        add_filter('woocommerce_customer_meta_fields', '\EIB2BPRO\B2b\Admin\User::customer_meta_fields', 100, 1);

        // Emails

        add_filter('woocommerce_email_classes', '\EIB2BPRO\B2b\Admin\Main::email_classes');
        add_filter('woocommerce_email_actions', '\EIB2BPRO\B2b\Admin\Main::email_actions');

        // Settings

        add_filter('eib2bpro_notifications', function ($notifications) {
            $notifications['new_quote_request'] = esc_html__('New quote request', 'eib2bpro');
            $notifications['user_needs_approval'] = esc_html__('User needs approval', 'eib2bpro');
            $notifications['new_b2b_user'] = esc_html__('New B2B user', 'eib2bpro');

            return $notifications;
        });

        // REST API
        add_action('rest_api_init', '\EIB2BPRO\Rules\Main::rest_api_metadata');

        // IMPORT
        add_filter('woocommerce_csv_product_import_mapping_options', '\EIB2BPRO\B2b\Admin\Toolbox::import_columns', 999, 1);
        add_filter('woocommerce_csv_product_import_mapping_default_columns', '\EIB2BPRO\B2b\Admin\Toolbox::mapping_screen', 999, 1);
        add_filter('woocommerce_product_import_pre_insert_product_object', '\EIB2BPRO\B2b\Admin\Toolbox::process_import', 999, 2);

        // Others
        add_action('save_post', '\EIB2BPRO\B2b\Admin\Toolbox::products_flush_cache', 10, 1);
    }


    public static function register_post_types()
    {
        $post_types = [
            ['slug' => 'eib2bpro_groups', 'title' => esc_html__('B2B Pro Groups', 'eib2bpro')],
            ['slug' => 'eib2bpro_fields', 'title' => esc_html__('B2B Pro Custom Fields', 'eib2bpro')],
            ['slug' => 'eib2bpro_regtype', 'title' => esc_html__('B2B Pro Types', 'eib2bpro')],
            ['slug' => 'eib2bpro_offers', 'title' => esc_html__('B2B Pro Offers', 'eib2bpro')],
            ['slug' => 'eib2bpro_quote', 'title' => esc_html__('B2B Pro Quotes', 'eib2bpro')],
            ['slug' => 'eib2bpro_quote_field', 'title' => esc_html__('B2B Pro Quote Fields', 'eib2bpro')],
            ['slug' => 'eib2bpro_rules', 'title' => esc_html__('B2B Pro Rules', 'eib2bpro')],
            ['slug' => 'eib2bpro_quick', 'title' => esc_html__('B2B Pro Quick Orders', 'eib2bpro')],
        ];

        foreach ($post_types as $post) {
            register_post_type(
                $post['slug'],
                array(
                    'label' => $post['title'],
                    'description' => $post['title'],
                    'labels' => array(
                        'name' => $post['title'],
                        'singular_name' => $post['title'],
                        'all_items' => $post['title'],
                        'menu_name' => $post['title'],
                    ),
                    'supports' => array('title', 'custom-fields'),
                    'hierarchical' => false,
                    'public' => false,
                    'show_in_menu' => false,
                    'show_ui' => true,
                    'menu_position' => 100,
                    'show_in_bar' => false,
                    'show_in_nav_menus' => false,
                    'can_export' => true,
                    'has_archive' => false,
                    'exclude_from_search' => true,
                    'publicly_queryable' => false,
                    'capability_type' => 'product',
                    'map_meta_cap' => true,
                    'show_in_rest' => true,
                    'rest_base' => $post['slug'],
                    'rest_controller_class' => 'WP_REST_Posts_Controller',
                )
            );
        }
    }

    public static function hide_offer($query)
    {
        $offer = eib2bpro_option('b2b_offer_default_id', -999999);
        $not_in = $query->query_vars['post__not_in'];
        if (is_array($not_in)) {
            $query->query_vars['post__not_in'] = array_merge(array($offer), $not_in);
        } else {
            $query->query_vars['post__not_in'] = array($offer);
        }
    }

    public static function email_classes($email_classes)
    {
        $email_classes['EIB2BPRO_New_Customer'] = include(EIB2BPRO_DIR . '/b2b/emails/new-customer.php');
        $email_classes['EIB2BPRO_Account_Approved'] = include(EIB2BPRO_DIR . '/b2b/emails/account-approved.php');
        $email_classes['EIB2BPRO_Quick_Orders_Reminder'] = include(EIB2BPRO_DIR . '/b2b/emails/quick-orders-reminder.php');
        $email_classes['EIB2BPRO_New_Quote_Request'] = include(EIB2BPRO_DIR . '/b2b/emails/admin-new-quote-request.php');
        $email_classes['EIB2BPRO_New_Offer'] = include(EIB2BPRO_DIR . '/b2b/emails/new-offer.php');

        return $email_classes;
    }

    public static function email_actions($actions)
    {
        $actions[] = 'eib2bpro_account_approved';
        $actions[] = 'eib2bpro_quick_orders_reminder';
        $actions[] = 'eib2bpro_new_quote_request';
        $actions[] = 'eib2bpro_new_offer_mail';

        return $actions;
    }

    public static function clear_cache($keys = ['all'])
    {
        global $wpdb;

        foreach ($keys as $key) {
            switch ($key) {
                case 'all':
                    $transients = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%transient_eib2bpro_%' LIMIT %d",
                            1000000
                        )
                    );

                    foreach ($transients as $transient) {
                        delete_transient(str_replace('_transient_', '', $transient->option_name));
                    }

                    eib2bpro_option('b2b_clear_product_caches', 1, 'set');

                    self::clear_cache(['users']);

                    wp_cache_flush();
                    break;

                case 'users':

                    $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                    foreach ($groups as $group) {
                        delete_post_meta($group->ID, '_eib2bpro_stats_revenue');
                    }

                    delete_transient('eib2bpro_group_users_count');
                    delete_transient('eib2bpro_non_approved_users');
                    break;

                case 'sales':

                    $date = eib2bpro_strtotime("now", 'Ymd');

                    $sales = get_transient('eib2bpro_total_revenue');
                    if ($sales) {
                        set_transient('eib2bpro_total_revenue', $sales, 2 * MINUTE_IN_SECONDS);
                    }

                    // Dashboard stats
                    $sales2 = get_transient('eib2bpro_dashboard_stats_' . $date);
                    if ($sales2) {
                        set_transient('eib2bpro_dashboard_stats_' . $date, $sales2, 2 * MINUTE_IN_SECONDS);
                    }
                    break;
            }
        }
    }
}
