<?php

/**
 * WIDGET
 *
 * Information about store's sales, customers etc.
 *
 *
 * @since      1.0.0
 * @author     EN.ER.GY <support@en.er.gy>
 * */

namespace EIB2BPRO\Dashboard\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Overview
{
    public static $name = 'Overview';
    public static $multiple = 1;

    public static function run($args = array(), $settings = array())
    {

        global $wpdb;

        $time = isset($args['lasttime']) ? $args['lasttime'] : time() - (24 * 60 * 60);
        $time = time() - (24 * 60 * 60);

        if (empty($settings)) {
            $settings['today_total_visitors'] = '1';
            $settings['today_orders'] = '1';
            $settings['today_revenue'] = '1';
            $settings['week_revenue'] = '1';
            $settings['month_revenue'] = '1';
        }

        $result = array(
            'online' => array('title' => esc_html__('Online', 'eib2bpro'), 'count' => 0, 'active' => (isset($settings['online']) && '1' === $settings['online']) ? '1' : '0'),
            'today_total_visitors' => array('title' => esc_html__('Today - Visitors', 'eib2bpro'), 'count' => 0, 'active' => (isset($settings['today_total_visitors']) && '1' === $settings['today_total_visitors']) ? '1' : '0'),
            'today_orders' => array('title' => esc_html__('Today - Orders', 'eib2bpro'), 'count' => 0, 'active' => (isset($settings['today_orders']) && '1' === $settings['today_orders']) ? '1' : '0'),
            'pending_orders' => array('title' => esc_html__('Orders in progress', 'eib2bpro'), 'count' => 0, 'active' => (isset($settings['pending_orders']) && '1' === $settings['pending_orders']) ? '1' : '0'),
            'today_revenue' => array('title' => esc_html__('Today - Sales', 'eib2bpro'), 'count' => 0, 'is_price' => 1, 'active' => (isset($settings['today_revenue']) && '1' === $settings['today_revenue']) ? '1' : '0'),
            'week_revenue' => array('title' => esc_html__('Week - Sales', 'eib2bpro'), 'count' => 0, 'is_price' => 1, 'active' => (isset($settings['week_revenue']) && '1' === $settings['week_revenue']) ? '1' : '0'),
            'month_revenue' => array('title' => esc_html__('Month - Sales', 'eib2bpro'), 'count' => 0, 'is_price' => 1, 'active' => (isset($settings['month_revenue']) && '1' === $settings['month_revenue']) ? '1' : '0'),

        );

        // Online visitors
        if ('1' === $result['online']['active']) {
            $result['online']['count'] = \EIB2BPRO\Dashboard\Widgets\Onlineusers::run(array('ajax' => 1));
        }

        // Todays total visitors
        if ('1' === $result['today_total_visitors']['active']) {
            $_today_total_visitors = $wpdb->get_var(
                $wpdb->prepare(
                    "
              SELECT COUNT(DISTINCT session_id)
              FROM {$wpdb->prefix}eib2bpro_requests
              WHERE week = %d AND date >= %s",
                    eib2bpro_strtotime('now', 'W'),
                    eib2bpro_strtotime('today')
                )
            );

            if (isset($_today_total_visitors)) {
                $result['today_total_visitors']['count'] = absint($_today_total_visitors);
            }
        }


        $date = eib2bpro_strtotime('now', 'Y-m-d');

        $request = new \WP_REST_Request('GET', '/wc-analytics/reports/orders/stats');
        $request->set_param('interval', 'day');
        $request->set_param('per_page', 100);
        $request->set_param('after', eib2bpro_strtotime('now', 'Y-m-d\T00:00:00'));
        $request->set_param('before', eib2bpro_strtotime('now', 'Y-m-d\T23:59:59'));
        $request->set_param('order', 'desc');
        $_response = rest_do_request($request);
        if (is_wp_error($_response)) {
            return false;
        }
        $response = $_response->get_data();

        if ('1' === $result['today_revenue']['active']) {
            $result['today_revenue']['count'] = intval($response['totals']['total_sales']);
        }

        if ('1' === $result['today_orders']['active']) {
            $result['today_orders']['count'] = intval($response['totals']['orders_count']);
        }

        $request = new \WP_REST_Request('GET', '/wc-analytics/reports/orders/stats');
        $request->set_param('interval', 'day');
        $request->set_param('per_page', 100);
        $request->set_param('after', eib2bpro_strtotime('monday this week', 'Y-m-d\T00:00:00'));
        $request->set_param('before', eib2bpro_strtotime('now', 'Y-m-d\T23:59:59'));
        $request->set_param('order', 'desc');
        $_response = rest_do_request($request);
        if (is_wp_error($_response)) {
            return false;
        }
        $response = $_response->get_data();

        if ('1' === $result['week_revenue']['active']) {
            $result['week_revenue']['count'] = intval($response['totals']['total_sales']);
        }

        $request = new \WP_REST_Request('GET', '/wc-analytics/reports/orders/stats');
        $request->set_param('interval', 'day');
        $request->set_param('per_page', 100);
        $request->set_param('after', date('Y-m-01\T00:00:00'));
        $request->set_param('before', eib2bpro_strtotime('now', 'Y-m-d\T23:59:59'));
        $request->set_param('order', 'desc');
        $_response = rest_do_request($request);
        if (is_wp_error($_response)) {
            return false;
        }
        $response = $_response->get_data();

        if ('1' === $result['month_revenue']['active']) {
            $result['month_revenue']['count'] = intval($response['totals']['total_sales']);
        }

        if ('1' === $result['pending_orders']['active']) {
            $result['pending_orders']['count'] = intval($response['totals']['orders_count']);
        }

        if (eib2bpro_is_ajax() or isset($args['ajax'])) {
            return eib2bpro_view('dashboard', 1, 'widgets.overview', array('args' => $args, 'results' => $result));
        } else {
            echo eib2bpro_view('dashboard', 1, 'widgets.overview', array('args' => $args, 'results' => $result));
        }
    }

    /**
     * Widget's settings
     *
     * @param array $args
     * @return array
     * @since  1.0.0
     */

    public static function settings($settings)
    {
        if (empty($settings)) {
            $settings['today_total_visitors'] = '1';
            $settings['today_orders'] = '1';
            $settings['today_revenue'] = '1';
            $settings['week_revenue'] = '1';
            $settings['month_revenue'] = '1';
        }

        return array(
            'type' => 'options',
            'info' => array(
                'type' => 'checkbox',
                'title' => esc_html__('Show these metrics', 'eib2bpro'),
                'values' => array(
                    'online' => array('title' => esc_html__('Online', 'eib2bpro'), 'id' => 'online', 'selected' => (isset($settings['online']) && '1' === $settings['online']) ? '1' : '0'),
                    'today_total_visitors' => array('title' => esc_html__('Today - Visitors', 'eib2bpro'), 'id' => 'today_total_visitors', 'selected' => (isset($settings['today_total_visitors']) && '1' === $settings['today_total_visitors']) ? '1' : '0'),
                    'today_orders' => array('title' => esc_html__('Today - Orders', 'eib2bpro'), 'id' => 'today_orders', 'selected' => (isset($settings['today_orders']) && '1' === $settings['today_orders']) ? '1' : '0'),
                    'pending_orders' => array('title' => esc_html__('Orders in progress', 'eib2bpro'), 'id' => 'pending_orders', 'selected' => (isset($settings['pending_orders']) && '1' === $settings['pending_orders']) ? '1' : '0'),
                    'today_revenue' => array('title' => esc_html__('Today - Sales', 'eib2bpro'), 'id' => 'today_revenue', 'selected' => (isset($settings['today_revenue']) && '1' === $settings['today_revenue']) ? '1' : '0'),
                    'week_revenue' => array('title' => esc_html__('Week - Sales', 'eib2bpro'), 'id' => 'week_revenue', 'selected' => (isset($settings['week_revenue']) && '1' === $settings['week_revenue']) ? '1' : '0'),
                    'month_revenue' => array('title' => esc_html__('Month - Sales', 'eib2bpro'), 'id' => 'month_revenue', 'selected' => (isset($settings['month_revenue']) && '1' === $settings['month_revenue']) ? '1' : '0'),
                )
            )
        );
    }
}
