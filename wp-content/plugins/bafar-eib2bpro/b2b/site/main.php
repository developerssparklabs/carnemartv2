<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Main
{
    public static $group = '';
    public static function run()
    {
        if (!is_admin()) {
            add_action('init', '\EIB2BPRO\B2b\Site\Product::category_visibility_ids');
            add_action('init', '\EIB2BPRO\B2b\Site\Main::shortcodes');

            if (!is_user_logged_in()) {
                if ('hide_prices' === eib2bpro_option('b2b_settings_visibility_guest') && 'request_a_qoute' === eib2bpro_option('b2b_settings_visibility_guest_hide_prices', 'login_to_view')) {
                    add_filter('woocommerce_get_price_html', '__return_null', 9999, 2);
                    add_filter('woocommerce_variation_get_price_html', '__return_null', 9999, 2);
                    add_filter('woocommerce_coupons_enabled', '__return_false', 9999);
                    add_filter('woocommerce_sale_flash', '__return_false', 9999);

                    add_filter('woocommerce_product_single_add_to_cart_text', '\EIB2BPRO\B2b\Site\Guest::request_a_quote_button');
                    add_filter('woocommerce_product_add_to_cart_text', '\EIB2BPRO\B2b\Site\Guest::request_a_quote_button');

                    add_action('template_redirect', '\EIB2BPRO\B2b\Site\Guest::redirect_checkout_to_cart', 50);

                    remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', 30);
                    remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 30);

                    add_filter('woocommerce_cart_item_price', '\EIB2BPRO\B2b\Site\Quote::hide_price', 10, 3);
                    add_filter('woocommerce_cart_item_subtotal', '\EIB2BPRO\B2b\Site\Quote::hide_price', 10, 3);
                    add_filter('woocommerce_cart_subtotal', '\EIB2BPRO\B2b\Site\Quote::hide_price', 10, 3);
                    add_filter('woocommerce_cart_total', '\EIB2BPRO\B2b\Site\Quote::hide_price', 10, 3);
                    add_action('woocommerce_cart_actions', '\EIB2BPRO\B2b\Site\Quote::button');
                } elseif ('hide_prices' === eib2bpro_option('b2b_settings_visibility_guest')) {
                    add_filter('woocommerce_get_price_html', '\EIB2BPRO\B2b\Site\Guest::hide_prices', 9999, 2);
                    add_filter('woocommerce_variation_get_price_html', '\EIB2BPRO\B2b\Site\Guest::hide_prices', 9999, 2);
                    add_filter('woocommerce_is_purchasable', '__return_false');
                    add_filter('woocommerce_variation_is_purchasable', '__return_false');
                    add_filter('woocommerce_structured_data_product_offer', '__return_empty_array');
                    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
                    remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);

                    if ('redirect_product_to_my_account' === eib2bpro_option('b2b_settings_visibility_guest_hide_prices', 'login_to_view')) {
                        add_action('template_redirect', '\EIB2BPRO\B2b\Site\Guest::redirect_product_to_my_account');
                    }
                } elseif ('hide_shop' === eib2bpro_option('b2b_settings_visibility_guest')) {

                    add_filter('woocommerce_get_price_html', '__return_null', 9999, 2);
                    add_filter('woocommerce_variation_get_price_html', '__return_null', 9999, 2);
                    add_filter('woocommerce_coupons_enabled', '__return_false', 9999);
                    add_filter('woocommerce_sale_flash', '__return_false', 9999);

                    if ('shop' === eib2bpro_option('b2b_settings_visibility_guest_hide_shop')) {
                        add_action('wp', '\EIB2BPRO\B2b\Site\Guest::hide_shop');
                    } elseif ('full_website' === eib2bpro_option('b2b_settings_visibility_guest_hide_shop')) {
                        add_action('wp', '\EIB2BPRO\B2b\Site\Guest::hide_website');
                    } elseif ('redirect_to_login' === eib2bpro_option('b2b_settings_visibility_guest_hide_shop')) {
                        add_action('wp', '\EIB2BPRO\B2b\Site\Guest::redirect_to_login');
                    }
                }
            } else {

                // My Account
                add_action('init', '\EIB2BPRO\B2b\Site\Main::endpoints');
                add_filter('woocommerce_account_menu_items', '\EIB2BPRO\B2b\Site\Main::account_menu_items', 10, 1);

                // User Tax Exemption
                $user_tax_exemption = get_user_meta(Main::user('id'), '_eib2bpro_tax_exemption', true);
                if ($user_tax_exemption && 'default' !== $user_tax_exemption) {
                    add_action('init', '\EIB2BPRO\Rules\Site::tax_exemption_custom', 1000);
                }
                // Offers
                if (1 === eib2bpro_option('b2b_enable_offers', 1)) {
                    add_action('woocommerce_account_' . eib2bpro_option('b2b_endpoints_offers', 'offers') . '_endpoint', '\EIB2BPRO\B2b\Site\Offers::content');
                    add_filter('woocommerce_cart_item_name', '\EIB2BPRO\B2b\Site\Offers::cart_display', 1, 3);
                    add_filter('woocommerce_cart_item_price', '\EIB2BPRO\B2b\Site\Offers::minicart_display', 10, 3);
                    add_filter('woocommerce_cart_item_thumbnail', '\EIB2BPRO\B2b\Site\Offers::cart_item_thumbnail', 10, 3);
                    add_filter('woocommerce_checkout_create_order_line_item', '\EIB2BPRO\B2b\Site\Offers::add_meta_to_order', 30, 4);
                    // add_action('woocommerce_before_calculate_totals', '\EIB2BPRO\B2b\Site\Offers::cart_calculate_totals');
                    add_action('woocommerce_after_cart_table', '\EIB2BPRO\B2b\Site\Offers::after_cart_table', 100);
                    add_filter('woocommerce_cart_item_visible', '\EIB2BPRO\B2b\Site\Offers::hide_offer_from_cart', 100, 3);
                    if (true === apply_filters('eib2bpro_offers_add_products_to_order', true)) {
                        add_filter('woocommerce_checkout_order_processed', '\EIB2BPRO\B2b\Site\Offers::add_products_to_order', 30, 3);
                    }
                }

                // bulk order
                if (1 === eib2bpro_option('b2b_enable_bulkorder', 0)) {
                    add_action('woocommerce_account_' . eib2bpro_option('b2b_endpoints_bulkorder', 'bulk-order') . '_endpoint', '\EIB2BPRO\B2b\Site\Bulkorder::content');
                }

                // quick orders
                if (1 === eib2bpro_option('b2b_enable_quickorders', 0)) {
                    add_action('woocommerce_account_' . eib2bpro_option('b2b_endpoints_quickorders', 'quick-orders') . '_endpoint', '\EIB2BPRO\B2b\Site\Quickorders::content');
                }
            }

            // Quote
            if (in_array(Main::user('group'), eib2bpro_option('b2b_settings_request_a_quote_on_cart', []))) {
                add_action('woocommerce_cart_actions', '\EIB2BPRO\B2b\Site\Quote::button');
            }

            add_filter('get_terms_args', '\EIB2BPRO\B2b\Site\Product::category_visibility', 10, 2);

            add_filter('woocommerce_product_get_price', '\EIB2BPRO\B2b\Site\Product::product_active_price', 999, 2);
            add_filter('woocommerce_product_get_regular_price', '\EIB2BPRO\B2b\Site\Product::product_regular_price', 999, 2);
            add_filter('woocommerce_product_variation_get_price', '\EIB2BPRO\B2b\Site\Product::product_active_price', 999, 2);
            add_filter('woocommerce_product_variation_get_regular_price', '\EIB2BPRO\B2b\Site\Product::product_regular_price', 999, 2);
            add_filter('woocommerce_variation_prices_price', '\EIB2BPRO\B2b\Site\Product::product_regular_price', 999, 2);
            add_filter('woocommerce_variation_prices_regular_price', '\EIB2BPRO\B2b\Site\Product::product_regular_price', 999, 2);

            add_filter('woocommerce_product_get_sale_price', '\EIB2BPRO\B2b\Site\Product::product_sale_price', 999, 2);
            add_filter('woocommerce_product_variation_get_sale_price', '\EIB2BPRO\B2b\Site\Product::product_sale_price', 999, 2);
            add_filter('woocommerce_variation_prices_sale_price', '\EIB2BPRO\B2b\Site\Product::product_sale_price', 999, 2);

            // if price tiers enabled
            if (1 === eib2bpro_option('b2b_enable_price_tiers', 1)) {
                add_filter('woocommerce_product_get_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_product_get_regular_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_product_get_sale_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_product_variation_get_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_product_variation_get_regular_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_product_variation_get_sale_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_variation_prices_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_variation_prices_regular_price', '\EIB2BPRO\B2b\Site\Product::tiered_regular_price', 1000, 2);
                add_filter('woocommerce_get_price_html', '\EIB2BPRO\B2b\Site\Product::tiered_formatted_price_with_range', 1001, 2);
                add_filter('woocommerce_variation_get_price_html', '\EIB2BPRO\B2b\Site\Product::tiered_formatted_price_with_range', 1001, 2);

                // price tiers table
                if (1 === eib2bpro_option('b2b_settings_appearance_show_tiers_table', 1)) {

                    if (is_user_logged_in() || 'hide_prices' !== eib2bpro_option('b2b_settings_visibility_guest')) {
                        add_action('woocommerce_after_add_to_cart_form', '\EIB2BPRO\B2b\Site\Product::price_tiers_table');
                        add_filter('woocommerce_available_variation', '\EIB2BPRO\B2b\Site\Product::price_tiers_table_variation', 10, 3);
                    }
                }
            }

            // product visibility
            add_filter('woocommerce_product_is_visible', '\EIB2BPRO\B2b\Site\Product::product_is_visible', 100, 2);
            add_filter('woocommerce_product_query', '\EIB2BPRO\B2b\Site\Product::product_visibility', 999, 1);
            add_filter('woocommerce_recently_viewed_products_widget_query_args', '\EIB2BPRO\B2b\Site\Product::shortcode_product_visibility', 9999, 1);
            add_filter('woocommerce_products_widget_query_args', '\EIB2BPRO\B2b\Site\Product::shortcode_product_visibility', 9999, 1);
            add_action('woocommerce_shortcode_products_query', '\EIB2BPRO\B2b\Site\Product::shortcode_product_visibility', 9999, 1);
            add_filter('woocommerce_top_rated_products_widget_args', '\EIB2BPRO\B2b\Site\Product::shortcode_product_visibility', 9999, 1);

            // product message
            add_action('woocommerce_product_meta_start', '\EIB2BPRO\B2b\Site\Product::show_message');

            // product qty
            add_filter('woocommerce_quantity_input_args', '\EIB2BPRO\B2b\Site\Product::quantity_input_args', 200, 2);
            add_filter('woocommerce_available_variation', '\EIB2BPRO\B2b\Site\Product::qty_variation', 200, 3);
            add_action('woocommerce_check_cart_items', '\EIB2BPRO\B2b\Site\Product::qty_check');

            // display price (tax)
            add_filter('option_woocommerce_tax_display_shop', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);
            add_filter('option_woocommerce_tax_display_cart', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);

            // registration form
            add_action('woocommerce_created_customer', '\EIB2BPRO\B2b\Site\Registration::woocommerce_created_customer');
            add_action('user_register', '\EIB2BPRO\B2b\Site\Registration::woocommerce_created_customer');
            add_action('woocommerce_register_form', '\EIB2BPRO\B2b\Site\Registration::woocommerce_register_form');
            add_action('woocommerce_process_registration_errors', '\EIB2BPRO\B2b\Site\Registration::woocommerce_process_registration_errors', 10, 3);
            add_action('woocommerce_registration_redirect', '\EIB2BPRO\B2b\Site\Registration::woocommerce_registration_redirect', 2);
            add_filter('woocommerce_process_login_errors', '\EIB2BPRO\B2b\Site\Registration::woocommerce_process_login_errors', 10, 3);
            add_action('woocommerce_register_form_tag', '\EIB2BPRO\B2b\Site\Registration::woocommerce_register_form_tag');

            // Custom fields
            add_filter('woocommerce_billing_fields', '\EIB2BPRO\B2b\Site\Registration::woocommerce_billing_fields', 9999, 1);
            add_action('woocommerce_checkout_update_order_meta', '\EIB2BPRO\B2b\Site\Registration::save_billings_to_order_meta');
            add_action('woocommerce_checkout_update_user_meta', '\EIB2BPRO\B2b\Site\Registration::save_billings_to_user_meta', 10, 2);
            add_filter('woocommerce_order_get_formatted_billing_address', '\EIB2BPRO\B2b\Admin\Orders::woocommerce_order_get_formatted_billing_address', 10, 3);

            add_action('woocommerce_save_account_details_errors', '\EIB2BPRO\B2b\Site\Registration::vies_validation', 10, 1);
            add_action('woocommerce_after_save_address_validation', '\EIB2BPRO\B2b\Site\Registration::vies_validation', 10, 1);
            add_action('woocommerce_after_save_address_validation', '\EIB2BPRO\B2b\Site\Registration::save_account_details', 11, 1);
            add_action('woocommerce_after_checkout_validation', '\EIB2BPRO\B2b\Site\Registration::vies_validation', 10, 1);

            add_action('template_redirect', '\EIB2BPRO\B2b\Site\Product::restrict_if_not_visible', 100);
            add_action('woocommerce_before_mini_cart', '\EIB2BPRO\B2b\Site\Product::woocommerce_before_mini_cart', 1000);

            if (1 === eib2bpro_option('b2b_enable_shipping_methods', 1)) {
                \WC_Cache_Helper::get_transient_version('shipping', true);
                add_action('woocommerce_package_rates', '\EIB2BPRO\B2b\Site\Shipping::methods', 100);
            }
            if (1 === eib2bpro_option('b2b_enable_payment_methods', 1)) {
                add_filter('woocommerce_available_payment_gateways', '\EIB2BPRO\B2b\Site\Payment::methods', 1);
            }

            if (1 === eib2bpro_option('b2b_enable_rules', 1)) {

                if (self::available_rules('hide_price')) {
                    add_filter('woocommerce_get_price_html', '\EIB2BPRO\Rules\Site::hide_price', 99999, 2);
                    add_filter('woocommerce_variation_price_html', '\EIB2BPRO\Rules\Site::hide_price', 99999, 2);
                    add_filter('woocommerce_is_purchasable', '\EIB2BPRO\Rules\Site::hide_price_disable_purchasable', 10, 2);
                    add_filter('woocommerce_variation_is_purchasable', '\EIB2BPRO\Rules\Site::hide_price_disable_purchasable', 10, 2);
                }

                if (self::available_rules('add_quote_button')) {
                    add_action('woocommerce_after_add_to_cart_button', '\EIB2BPRO\Rules\Site::add_quote_button', 100);
                    add_action('woocommerce_before_single_product', '\EIB2BPRO\Rules\Site::remove_add_to_cart_button', 100);
                    add_filter('woocommerce_product_single_add_to_cart_text', '\EIB2BPRO\Rules\Site::request_a_quote_button', 100);
                    add_filter('woocommerce_product_add_to_cart_text', '\EIB2BPRO\Rules\Site::request_a_quote_button', 100);
                }

                if (self::available_rules('change_price')) {
                    if (is_user_logged_in() || (!is_user_logged_in() && 'hide_prices' !== eib2bpro_option('b2b_settings_visibility_guest'))) {
                        add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
                        add_filter('woocommerce_product_variation_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
                        add_filter('woocommerce_product_get_regular_price', '\EIB2BPRO\Rules\Site::change_price_regular', 9999, 2);
                        add_filter('woocommerce_product_variation_get_regular_price', '\EIB2BPRO\Rules\Site::change_price_regular', 9999, 2);
                        add_filter('woocommerce_product_get_sale_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
                        add_filter('woocommerce_product_variation_get_sale_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
                        add_filter('woocommerce_variation_prices_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
                        add_filter('woocommerce_variation_prices_sale_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
                        add_filter('woocommerce_get_variation_prices_hash', '\EIB2BPRO\Rules\Site::change_price_variation_hash', 99, 1);
                        add_filter('woocommerce_get_price_html', '\EIB2BPRO\Rules\Site::change_price_display', 999, 2);
                    }
                }

                if (self::available_rules('min_order') || self::available_rules('max_order')) {
                    add_action('woocommerce_checkout_process', '\EIB2BPRO\Rules\Site::minmax_order');
                    add_action('woocommerce_before_cart', '\EIB2BPRO\Rules\Site::minmax_order');
                }

                if (self::available_rules('free_shipping')) {
                    \WC_Cache_Helper::get_transient_version('shipping', true);
                    add_filter('woocommerce_shipping_free_shipping_is_available', '\EIB2BPRO\Rules\Site::free_shipping', 100, 3);
                }

                if (self::available_rules('step')) {
                    add_action('woocommerce_check_cart_items', '\EIB2BPRO\Rules\Site::step');
                    add_filter('woocommerce_quantity_input_args', '\EIB2BPRO\Rules\Site::step_qty', 100, 2);
                    add_filter('woocommerce_available_variation', '\EIB2BPRO\Rules\Site::step_qty_variation', 100, 3);
                    //  add_filter('woocommerce_add_to_cart_quantity', '\EIB2BPRO\Rules\Site::step_qty_number', 100, 2);
                }

                if (self::available_rules('payment_discount')) {
                    add_filter('woocommerce_cart_calculate_fees', '\EIB2BPRO\Rules\Site::payment_method_discount', 9999, 1);
                    add_action('woocommerce_review_order_before_payment', '\EIB2BPRO\Rules\Site::refresh_checkout_on_payment_methods_change');
                    add_filter('woocommerce_gateway_title', '\EIB2BPRO\Rules\Site::payment_method_discount_change_titles', 500, 2);
                    add_action('woocommerce_checkout_process', '\EIB2BPRO\Rules\Site::remove_payment_method_discount_change_titles', 999, 1);
                }

                if (self::available_rules('payment_minmax')) {
                    add_filter('woocommerce_available_payment_gateways', '\EIB2BPRO\Rules\Site::payment_method_minmax', 9999, 1);
                }

                if (self::available_rules('cart_discount')) {
                    add_filter('woocommerce_cart_calculate_fees', '\EIB2BPRO\Rules\Site::cart_discount');
                    add_action('woocommerce_review_order_before_payment', '\EIB2BPRO\Rules\Site::refresh_checkout_on_payment_methods_change');
                }

                if (self::available_rules('add_fee')) {
                    add_filter('woocommerce_cart_calculate_fees', '\EIB2BPRO\Rules\Site::add_fee');
                    add_action('woocommerce_review_order_before_payment', '\EIB2BPRO\Rules\Site::refresh_checkout_on_payment_methods_change');
                }

                if (self::available_rules('tax_exemption', true)) {
                    add_action('init', '\EIB2BPRO\Rules\Site::tax_exemption', 999);
                    add_filter('option_woocommerce_tax_display_cart', '\EIB2BPRO\Rules\Site::tax_exemption_prices');
                    add_filter('option_woocommerce_tax_display_shop', '\EIB2BPRO\Rules\Site::tax_exemption_prices');
                }

                if (self::available_rules('tax_exemption_product')) {
                    add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);
                    add_filter('woocommerce_product_variation_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);
                }

                if (self::available_rules('change_price_html')) {
                    add_filter('woocommerce_get_price_html', '\EIB2BPRO\Rules\Site::change_price_html', 9999999, 2);
                }


                add_action('woocommerce_new_order', '\EIB2BPRO\Rules\Site::add_rule_note_to_order',  9999, 1);
            }
        }

        add_action('wp_enqueue_scripts', '\EIB2BPRO\B2b\Site\Main::wp_enqueue_scripts');

        add_action('init', '\EIB2BPRO\B2b\Admin\Main::register_post_types');

        // REST API
        add_action('rest_api_init', '\EIB2BPRO\Rules\Main::rest_api_metadata');

        // Emails
        add_action('woocommerce_email_footer', '\EIB2BPRO\B2b\Site\Registration::new_account_mail', 10, 1);
        add_filter('woocommerce_email_classes', '\EIB2BPRO\B2b\Admin\Main::email_classes');
        add_filter('woocommerce_email_actions', '\EIB2BPRO\B2b\Admin\Main::email_actions');

        // Others
        add_action('woocommerce_checkout_order_processed', '\EIB2BPRO\B2b\Admin\Toolbox::clear_sales_cache', 999, 1);
        add_action('save_post_shop_order',                 '\EIB2BPRO\B2b\Admin\Toolbox::clear_sales_cache', 999, 1);
        add_action('woocommerce_update_order',             '\EIB2BPRO\B2b\Admin\Toolbox::clear_sales_cache', 999, 1);
    }

    public static function user($meta_key, $default = false, $user_id = 0)
    {
        if (0 === $user_id) {
            $user_id = get_current_user_id();
        }

        switch ($meta_key) {
            case 'group':
                if (!is_user_logged_in()) {
                    $value = 'guest';
                } elseif ('b2b' !== get_user_meta($user_id, 'eib2bpro_user_type', true)) {
                    $value = 'b2c';
                } else {
                    $value = get_user_meta($user_id, 'eib2bpro_group', true);
                }
                if (!empty(static::$group)) {
                    $value = static::$group;
                }
                break;
            case 'group_or_b2c':
                if (!is_user_logged_in()) {
                    $value = 'b2c';
                } elseif ('b2b' !== get_user_meta($user_id, 'eib2bpro_user_type', true)) {
                    $value = 'b2c';
                } else {
                    $value = get_user_meta($user_id, 'eib2bpro_group', true);
                }
                if (!empty(static::$group)) {
                    $value = static::$group;
                }
                break;
            case 'group_name':
                $group = self::user('group', false, $user_id);
                if ('guest' === $group) {
                    $value = 'Guest';
                } elseif ('b2c' === $group) {
                    $value = 'B2C';
                } else {
                    $value = get_the_title($group);
                }
                break;
            case 'user_type':
                if (!is_user_logged_in()) {
                    $value = 'guest';
                } elseif ('b2b' !== get_user_meta($user_id, 'eib2bpro_user_type', true)) {
                    $value = 'b2c';
                } else {
                    $value = get_user_meta($user_id, 'eib2bpro_user_type', true);
                }
                if (!empty(static::$group) && 'b2c' === static::$group) {
                    $value = static::$group;
                }
                break;
            case 'mail':
                if (0 === get_current_user_id()) {
                    $value = '';
                } else {
                    $user_info = get_userdata($user_id);
                    $value = $user_info->user_email;
                }
                break;
            case 'id':
                $value = get_current_user_id();
                break;
            default:
                $value = false;
                break;
        }

        return $value;
    }



    public static function wp_enqueue_scripts()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_style('select2');
        wp_enqueue_script('selectWoo');
        wp_enqueue_script('wc-country-select');

        wp_enqueue_style('eib2bpro_public', EIB2BPRO_PUBLIC . 'b2b/public/public.css', null, EIB2BPRO_VERSION);
        wp_enqueue_script('eib2bpro_public', EIB2BPRO_PUBLIC . 'b2b/public/public.js', array(), EIB2BPRO_VERSION, true);

        $JSvars['ajax_url'] = admin_url('admin-ajax.php');
        $JSvars['admin_url'] = admin_url();
        $JSvars['cart_url'] = wc_get_cart_url();
        $JSvars['checkout_url'] = wc_get_checkout_url();
        $JSvars['nonce'] = wp_create_nonce('eib2bpro-security');
        $JSvars['vat_countries'] = implode(',', (array)eib2bpro_option('b2b_field_vat_countries', []));

        if (!is_user_logged_in() && 'hide_prices' === eib2bpro_option('b2b_settings_visibility_guest') && 'request_a_qoute' === eib2bpro_option('b2b_settings_visibility_guest_hide_prices', 'login_to_view')) {
            $JSvars['replace_add_to_cart_with_quote'] = 'popup' === eib2bpro_option('b2b_settings_quote_system', 'popup') ? 1 : 0;
        } else {
            $JSvars['replace_add_to_cart_with_quote'] = 0;
        }

        $JSvars['i18n'] = array(
            'wait' => eib2bpro_option_translate('b2b_lang_please_wait', esc_html__('Please wait', 'eib2bpro')),
            'are_you_sure' => eib2bpro_option_translate('b2b_lang_are_you_sure', esc_html__('Are you sure?', 'eib2bpro')),
            'fill' => eib2bpro_option_translate('b2b_lang_fill_all_the_blanks', esc_html__('Please fill all the fields', 'eib2bpro')),
            'add' => eib2bpro_option_translate('b2b_lang_add', esc_html__('Add', 'eib2bpro')),
            'quickorder_title' => eib2bpro_option_translate('quickorder_title', esc_html__('Please enter a title for this list', 'eib2bpro'))
        );

        wp_localize_script('eib2bpro_public', 'eiB2BPublic', $JSvars);

        if (1 === eib2bpro_option('b2b_enable_frontend_inline_css', 1)) {

            $inline_css = '';

            $inline_css .= '
            .eib2bpro-quickorders-item,
            .eib2bpro-bulkorder-l1-table,
            .eib2bpro-bulkorder-l2-table 
            {
                border-radius:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_radius', 20)) . 'px;
                background:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3')) . ';
                color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#fff')) . ';
                border: 1px solid ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10)) . ';
            }

            .eib2bpro-quickorders-reminder-container,
            .eib2bpro-quickorders-summary {
                background: ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10)) . ';
            }
            
            input:checked + .eib2bpro-slider:before,
            .eib2bpro-slider {
                box-shadow: 0 0 0 1px ' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#fff')) . ', 0 0 2px ' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#fff')) . ';
            }

            .eib2bpro-slider:before {
                background-color: ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_text', '#ffffff'), 10)) . ';
            }

            .eib2bpro-quickorders-actions-button,
            .eib2bpro-bulkorder-l2-add-to-cart,
            table.eib2bpro-bulkorder-l2-table input {
                border-radius:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_radius', 20)) . 'px;
            }
            
            .eib2bpro-bulkorder-l2-body tr:first-child td,
            .eib2bpro-bulkorder-l1-body tr:first-child td
            {
                background-color: ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10)) . ' !important;
                color: ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_text', '#ffffff'), 10)) . ' !important;
            }

            .eib2bpro-quickorders-item.eib2bpro-opened .eib2bpro-quickorders-reminder,
            .eib2bpro-quickorders-item.eib2bpro-opened .eib2bpro-quickorders-summary,
            .eib2bpro-quickorders-item .eib2bpro-bulkorder-l2-table,
            .eib2bpro-bulkorder-l2-table td,
            .eib2bpro-bulkorder-l1-table td,
            .eib2bpro-bulkorder-l1-body tr:first-child td,
            .eib2bpro-bulkorder-l2-body tr:first-child td
            {
                border:0;
                border-bottom: 1px solid ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10)) . ';
            }

            .eib2bpro-bulkorder-l2-table-tbody tr td {
                border-right: 1px solid ' . esc_attr(apply_filters('b2bpro_bulkorder_l2_line_color', wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10))) . ' !important;
                border-top: 1px solid ' . esc_attr(apply_filters('b2bpro_bulkorder_l2_line_color', wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10))) . ' !important;
            }

            .eib2bpro-bulkorder-l2-table-tbody tr td:last-child {
                border-right: 0px solid transparent !important;
            }

            .eib2bpro-bulkorder-l2-table-tbody tr:last-child td {
                border-bottom: 1px solid ' . esc_attr(apply_filters('b2bpro_bulkorder_l2_line_color', wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10))) . ' !important;
            }

            .eib2bpro-bulkorder-l2-product-list,
            .eib2bpro-bulkorder-l1-body td a,
            .eib2bpro-bulkorder-l1-body td,
            .eib2bpro-bulkorder-l2-body td, 
            .eib2bpro-bulkorder-l2-table-head th,
            table.eib2bpro-bulkorder-l2-table tbody td 
            {
                background-color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3')) . ' !important;
                color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#ffffff')) . ' !important;
            }

            .eib2bpro-bulkorder-l1-categories,
            .eib2bpro-bulkorder-l1-search,
            .eib2bpro-bulkorder-l1-search::placeholder
            {
                 color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#ffffff')) . '!important;
                 background-color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3')) . ' !important;
            }

            .eib2bpro-bulkorder-l2-item,
            .eib2bpro-bulkorder-l2-product-list .woocommerce-Price-amount
            {
                 color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#ffffff')) . '!important;
                 background-color:' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_background', '#8224e3'), 10)) . ' !important;
            }

            .eib2bpro-bulkorder-l2-product::placeholder,
            .eib2bpro-bulkorder-l2-product-x {
                color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_text', '#000000')) . '!important;
            }

            .eib2bpro-bulkorder-l2-table tfoot td{
                color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_text', '#ffffff')) . '!important;
            }

            .eib2bpro-quickorders-reminder-start,
            .eib2bpro-quickorders-reminder-every,
            .eib2bpro-quickorders-actions-button,
            button.eib2bpro-bulkorder-l1-page,
            .eib2bpro-bulkorder-l1-variations,
            .eib2bpro-bulkorder-l1-product-qty,
            .eib2bpro-bulkorder-l1-product-add,
            .eib2bpro-bulkorder-l2-product,
            .eib2bpro-input-ei-bulkorder-qty,
            .eib2bpro-bulkorder-l2-add-to-cart
            {
            border:0;
            color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_text', '#000000')) . '!important;
            background-color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_background', '#ffffff')) . '!important;
            }

            .eib2bpro-bulkorder-l2-product,
            .eib2bpro-input-ei-bulkorder-qty {
                color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_text', '#000000')) . '!important;
            }

            .eib2bpro-bulkorder-l2-product-x svg,
            .eib2bpro-bulkorder-l1-product-add svg {
                fill:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_text', '#000000')) . '!important;
            }

            .eib2bpro-bulkorder-l1-product-qty-num-in {
                color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_text', '#000000')) . '!important;
                background-color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_background', '#ffffff')) . '!important;    
            }

            .eib2bpro-bulkorder-l1-td-qty .num-in span:before, .eib2bpro-bulkorder-l1-td-qty .num-in span:after {
                background-color:' . esc_attr(eib2bpro_option('b2b_color_bulkorder_input_text', '#000000')) . '!important;
            }

            @media (max-width: 820px) {
                .eib2bpro-bulkorder-l2-table tbody tr {
                    border-bottom: 1px solid ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_bulkorder_input_background', '#ffffff'), 2)) . ';
                }
                .eib2bpro-bulkorder-l2-table-tbody tr:last-child td,
                .eib2bpro-bulkorder-l2-table-tbody tr td {
                    border: 0px !important;
                }
            }
            ';

            if (1 === eib2bpro_option('b2b_enable_offers', 1)) {

                $inline_css .= '
        .eib2bpro_offers {
            border-radius:' . esc_attr(eib2bpro_option('b2b_color_offers_radius', 20)) . 'px;
            background:' . esc_attr(eib2bpro_option('b2b_color_offers_background', '#8224e3')) . ';
            color:' . esc_attr(eib2bpro_option('b2b_color_offers_text', '#ffffff')) . ';
            border: 1px solid ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_offers_background', '#8224e3'), 10)) . ';
        }

        .eib2bpro_offers hr {
           border-top: 1px solid ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_offers_background', '#8224e3'), 10)) . ';
        }
        
        .eib2bpro_offers,
        .eib2bpro_offers th,
        .eib2bpro_offers td,
        .eib2bpro_offers a,
        .eib2bpro_offers a:hover,
        .eib2bpro_offers a:focus,
        .eib2bpro_offers .amount,
        .eib2bpro_offers h2,
        .eib2bpro_offers h4 {
            color:' . esc_attr(eib2bpro_option('b2b_color_offers_text', '#ffffff')) . ' !important;
        }

        a.eib2bpro-b2b-offer-in-cart-remove,
        a.eib2bpro-b2b-offer-in-cart-remove:hover,
        a.eib2bpro-b2b-offer-in-cart-remove:focus,
        a.eib2bpro_offer_add_to_cart_options,
        a.eib2bpro_offer_add_to_cart_options:hover,
        a.eib2bpro_offer_add_to_cart_options:focus,
        button.eib2bpro_offer_add_to_cart:hover,
        button.eib2bpro_offer_add_to_cart {
            color:' . esc_attr(eib2bpro_option('b2b_color_offers_button_text', '#ffffff')) . '!important;
            background-color:' . esc_attr(eib2bpro_option('b2b_color_offers_button_background', '#000000')) . '!important;
        }
    
        .eib2bpro_offers_product_table {
            border: 0px solid ' . esc_attr(wc_hex_darker(eib2bpro_option('b2b_color_offers_background', '#000000'), 10)) . ';
        }

        .eib2bpro_offers_product_table th,
        .eib2bpro_offers_product_table td {
            padding-top:12px;
            padding-bottom:12px;
            color:' . esc_attr(eib2bpro_option('b2b_color_offers_text', '#ffffff')) . ';
        }

        .eib2bpro_offers_product_table th {
            border-top: 1px solid ' . esc_attr(eib2bpro_option('b2b_color_offers_text', '#ffffff')) . ' !important;
            border-bottom: 1px solid ' . esc_attr(eib2bpro_option('b2b_color_offers_text', '#ffffff')) . ' !important;
        }
        ';
            }


            if (is_cart()) {
                $offer = 0;
                $other = 0;
                $cart = \WC()->cart->get_cart();
                foreach ($cart as $item => $values) {
                    if (eib2bpro_option('b2b_offer_default_id', 0) === $values['data']->get_id()) {
                        $offer++;
                    } else {
                        $other++;
                    }
                }

                if ($offer > 0 && $other === 0) {
                    $inline_css .= '.woocommerce-cart-form .shop_table { display:none; }';
                }
            }


            wp_add_inline_style('eib2bpro_public', \EIB2BPRO\B2b\Admin\Toolbox::minify_css($inline_css));
        }
    }

    public static function endpoints()
    {
        if (1 === eib2bpro_option('b2b_enable_offers', 1)) {
            add_rewrite_endpoint(eib2bpro_option('b2b_endpoints_offers', 'offers'), EP_ROOT | EP_PAGES | EP_PERMALINK);
        }

        if (1 === eib2bpro_option('b2b_enable_bulkorder', 0)) {
            add_rewrite_endpoint(eib2bpro_option('b2b_endpoints_bulkorder', 'bulk-order'), EP_ROOT | EP_PAGES | EP_PERMALINK);
        }

        if (1 === eib2bpro_option('b2b_enable_quickorders', 0)) {
            add_rewrite_endpoint(eib2bpro_option('b2b_endpoints_quickorders', 'quick-orders'), EP_ROOT | EP_PAGES | EP_PERMALINK);
        }

        // rewrite rules if need
        if (apply_filters('eib2bpro_flush_rewrite_rules', false)) {
            flush_rewrite_rules();
        }
    }

    public static function offer_in_cart()
    {
        if (is_object(\WC()->cart)) {
            $offer = eib2bpro_option('b2b_offer_default_id', 0);

            foreach (\WC()->cart->get_cart() as $cart) {
                if ($cart['product_id'] === $offer) {
                    return true;
                }
            }
        }

        return false;
    }
    public static function purchasable_for_offers($status, $product)
    {
        $offer = eib2bpro_option('b2b_offer_default_id', 0);
        if ($offer !== intval($product->get_id())) {
            return false;
        }
        return true;
    }

    public static function account_menu_items($items)
    {
        $new = [];
        $i = 0;

        foreach ($items as $key => $item) {
            ++$i;

            if (3 === $i) {
                if (1 === eib2bpro_option('b2b_enable_offers', 1)) {
                    $new[eib2bpro_option('b2b_endpoints_offers', 'offers')] = eib2bpro_option_translate('b2b_lang_offers', esc_html__('Offers', 'eib2bpro'));
                }

                if (1 === eib2bpro_option('b2b_enable_bulkorder', 0) && ('b2b' === self::user('user_type') || 1 === eib2bpro_option('b2b_settings_bulkorder_b2c', 0))) {
                    $new[eib2bpro_option('b2b_endpoints_bulkorder', 'bulk-order')] = eib2bpro_option_translate('b2b_lang_bulkorder', esc_html__('Bulk Order', 'eib2bpro'));
                }

                if (1 === eib2bpro_option('b2b_enable_quickorders', 0) && ('b2b' === self::user('user_type') || 1 === eib2bpro_option('b2b_settings_bulkorder_b2c', 0))) {
                    $new[eib2bpro_option('b2b_endpoints_quickorders', 'quick-orders')] = eib2bpro_option_translate('b2b_lang_quickorders', esc_html__('Quick Orders', 'eib2bpro'));
                }
            }

            $new[$key] = $item;
        }

        return $new;
    }

    public static function shortcodes()
    {
        if (1 === eib2bpro_option('b2b_enable_offers', 1)) {
            add_shortcode('b2bpro_offers', '\EIB2BPRO\B2b\Site\Offers::shortcode');
        }

        add_shortcode('b2bpro_bulk_order', '\EIB2BPRO\B2b\Site\Bulkorder::shortcode');
        add_shortcode('b2bpro_quick_orders', '\EIB2BPRO\B2b\Site\Quickorders::shortcode');

        add_shortcode('b2bpro_registration', '\EIB2BPRO\B2b\Site\Registration::shortcode_registration');

        add_shortcode('b2bpro_content', '\EIB2BPRO\B2b\Site\Tools::shortcode_content');
        add_shortcode('b2bpro_price', '\EIB2BPRO\B2b\Site\Product::shortcode_price');
    }

    public static function available_rules($type, $is_exists = false)
    {
        $map = eib2bpro_option('rules_map', []);

        if ($is_exists) {
            if (isset($map['rules'][$type])) {
                return true;
            } else {
                return false;
            }
        }
        if (!isset($map['users'][$type])) {
            return false;
        }

        foreach ($map['users'][$type] as $rule_id => $users) {

            if ('all' === $users['users']) {
                return true;
            }

            if (!is_user_logged_in() && 'guest' === $users['users']) {
                return true;
            } else {
                if ('all_b2c' === $users['users'] && 'b2c' === self::user('user_type')) {
                    return true;
                }
                if ('all_b2b' === $users['users'] && 'b2b' === self::user('user_type')) {
                    return true;
                }
                if ('group' === $users['users']) {
                    if ('in' === $users['operator'] && in_array(self::user('group'), $users['value'])) {
                        return true;
                    }
                    if ('not_in' === $users['operator'] && !in_array(self::user('group'), $users['value'])) {
                        return true;
                    }
                }
                if ('user' === $users['users']) {
                    if ('in' === $users['operator'] && in_array(self::user('id'), $users['value'])) {
                        return true;
                    }
                    if ('not_in' === $users['operator'] && !in_array(self::user('id'), $users['value'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
