<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Dashboard
{

    public static function run()
    {
        if (eib2bpro_get('range')) {
            eib2bpro_option('b2b_dashboard_range', intval(eib2bpro_get('range', 7)), 'set');
        }
        echo eib2bpro_view('b2b', 'admin', 'dashboard.main');
    }

    public static function stats()
    {
        global $wpdb;

        $all = [];
        $range = eib2bpro_option('b2b_dashboard_range', 7);
        $statuses =  "'" . implode("','", apply_filters('eib2bpro_dashboard_chart_statuses', ['wc-processing', 'wc-completed'])) . "'";

        for ($i = 0; $i < $range; ++$i) {
            $date = eib2bpro_strtotime("now - $i days", 'Ymd');

            $stats  = get_transient('eib2bpro_dashboard_stats_' . $date);

            if (!$stats || 0 === $i) {

                $stats = ['b2b' => ['revenue' => 0, 'count' => 0, 'customer' => 0], 'b2c' => ['revenue' => 0, 'count' => 0, 'customer' => 0]];

                $orders = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM `{$wpdb->prefix}wc_order_stats` WHERE status IN (" . $statuses . ") AND date_created>=%s AND date_created<=%s",
                    eib2bpro_strtotime("now - $i days", 'Y-m-d 00:00:00'),
                    eib2bpro_strtotime("now - $i days", 'Y-m-d 23:59:59')
                ));

                foreach ($orders as $order) {
                    $user_id = get_post_meta($order->order_id, '_customer_user', true);
                    if (get_user_meta($user_id, 'eib2bpro_user_type', true) === 'b2b') {
                        $stats['b2b']['revenue'] += get_post_meta($order->order_id, '_order_total', true);
                        $stats['b2b']['count'] += 1;
                    } else {
                        $stats['b2c']['revenue'] += get_post_meta($order->order_id, '_order_total', true);
                        $stats['b2c']['count'] += 1;
                    }
                }

                foreach (['b2b', 'b2c'] as $type) {
                    $users = $wpdb->get_var($wpdb->prepare(
                        "SELECT count(ID) AS cnt FROM `{$wpdb->prefix}users` AS u LEFT JOIN $wpdb->usermeta AS m ON u.ID=m.user_id WHERE m.meta_key='eib2bpro_user_type' AND m.meta_value=%s AND u.user_registered>=%s AND u.user_registered<=%s",
                        $type,
                        eib2bpro_strtotime("now - $i days", 'Y-m-d 00:00:00'),
                        eib2bpro_strtotime("now - $i days", 'Y-m-d 23:59:59')
                    ));

                    $stats[$type]['customer'] = intval($users);
                }
                set_transient('eib2bpro_dashboard_stats_' . $date, $stats, DAY_IN_SECONDS);
            }

            $all[$date] = $stats;
            $stats = false;
        }

        return $all;
    }

    public static function non_approved_users()
    {
        $users = get_transient('eib2bpro_non_approved_users');

        if (!$users) {
            $users_query = new \WP_User_Query(
                array(
                    'orderby' => 'user_registered',
                    'order' => 'DESC',
                    'role' => ['customer'],
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'eib2bpro_user_approved',
                            'value' => 'no',
                            'compare' => '='
                        )
                    )
                )
            );

            $users = $users_query->get_results();
            set_transient('eib2bpro_non_approved_users', $users);
        }

        return $users;
    }
}
