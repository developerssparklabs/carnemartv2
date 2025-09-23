<?php

/**
 * Admin Site: Frontend hooks
 */

namespace EIB2BPRO\Admin;

defined('ABSPATH') || exit;

class Site
{
    /**
     * Boot
     *
     * @return void
     */

    public static function boot()
    {
        // REST API
        add_action('rest_api_init', '\EIB2BPRO\Rules\Main::rest_api_metadata');

        // start B2B frontend (aka Site) class
        \EIB2BPRO\B2b\Site\Main::run();


        self::hooks();

        // start embedded tracker
        if (1 === eib2bpro_option('tracker', 1)) {
            \EIB2BPRO\Core\Tracker::add_tracker_js();
        }

        if (0 < eib2bpro_option('tracker', 1)) {
            add_action('woocommerce_add_to_cart', '\EIB2BPRO\Core\Tracker::woocommerce_add_to_cart', 10, 6);
            add_action('woocommerce_remove_cart_item', '\EIB2BPRO\Core\Tracker::woocommerce_remove_cart_item', 10, 2);
            add_action('woocommerce_checkout_order_review', '\EIB2BPRO\Core\Tracker::woocommerce_checkout_order_review', 10, 2);
        }

        // Reactors: Login
        if (\EIB2BPRO\Reactors\Main::is_installed('login')) {
            $class = "\EIB2BPRO\Reactors\login\login";
            if (class_exists($class)) {
                $class::init();
            }
        }
    }

    /**
     * Hooks
     *
     * @return void
     */
    public static function hooks()
    {
        add_action('comment_post', '\EIB2BPRO\Core\Notifications::new_comment', 10, 2);
        add_action('woocommerce_checkout_order_processed', '\EIB2BPRO\Core\Notifications::new_order', 20, 1);
        add_action('woocommerce_low_stock', '\EIB2BPRO\Core\Notifications::low_stock',  10, 1);
        add_action('woocommerce_no_stock', '\EIB2BPRO\Core\Notifications::low_stock',  10, 1);
    }
}
