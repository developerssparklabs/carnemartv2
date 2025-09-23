<?php

/**
 * Functions for hide prices/site
 *
 * @author     ENERGY <support@en.er.gy>
 */

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Guest
{
    /**
     * Hide prices
     *
     * @return void
     */

    public static function hide_prices()
    {
        if ('login_to_view' === eib2bpro_option('b2b_settings_visibility_guest_hide_prices', 'login_to_view')) {

            return wp_kses_post(__('Login to view prices', 'eib2bpro'));
        }

        if ('only_hide' === eib2bpro_option('b2b_settings_visibility_guest_hide_prices', 'login_to_view')) {
            return '';
        }
    }

    /**
     * Replace "Add to Cart" to "Request a Quote"
     *
     * @return void
     */

    public static function request_a_quote_button()
    {
        return esc_html__('Request a quote', 'eib2bpro');
    }

    /**
     * Redirect shop pages to My Account
     *
     * @return void
     */

    public static function hide_shop()
    {
        global $wp;
        if (!is_product() && !is_shop()) {
            return;
        }
        wp_safe_redirect(esc_url(add_query_arg('redirect_to', home_url($wp->request),  get_permalink(wc_get_page_id('myaccount')))));
        exit();
    }

    /**
     * Redirect all pages to My Account
     */

    public static function hide_website()
    {
        global $wp;
        if (is_account_page()) {
            return;
        }

        wp_safe_redirect(esc_url(add_query_arg('redirect_to', home_url($wp->request),  get_permalink(wc_get_page_id('myaccount')))));
        exit();
    }

    /**
     * Redirect to login page
     *
     * @return void
     */

    public static function redirect_to_login()
    {
        auth_redirect();
    }

    public static function redirect_product_to_my_account()
    {
        global $wp;
        if (is_product() && !is_user_logged_in()) {
            wp_safe_redirect(esc_url(add_query_arg('redirect_to', home_url($wp->request),  get_permalink(wc_get_page_id('myaccount')))));
        }
    }

    /**
     * Redirect checkout page to cart
     *
     * @return void
     */

    public static function redirect_checkout_to_cart()
    {
        if (!is_checkout()) {
            return;
        }

        wp_redirect(get_permalink(wc_get_page_id('cart')));
        exit();
    }
}
