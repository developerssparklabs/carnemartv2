<?php

namespace EIB2BPRO\Orders;

defined('ABSPATH') || exit;

class Main extends \EIB2BPRO\Orders
{
    /**
     * Starts everything
     *
     * @return void
     */

    public static function run()
    {
        self::route();
    }

    /**
     * Router for sub pages
     *
     * @return void
     */

    public static function route()
    {
        switch (eib2bpro_get('action')) {
            default:
                self::index();
                break;
        }
    }


    /**
     * Prepare filter array for query
     *
     * @param mixed $filter array of filter or false
     * @return array           new filter array
     */

    public static function filter($filter)
    {
        if (!$filter) {
            $filter['page'] = 1;
            $filter['limit'] = absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10));
            $filter['post_status'] = array_keys(wc_get_order_statuses());
        }

        if ($status = eib2bpro_get('status', null)) {
            if ('trash' === $status) {
                $filter['post_status'] = 'trash';
            } else {
                if (in_array('wc-' . $status, array_keys(wc_get_order_statuses()))) {
                    $filter['post_status'] = "wc-" . $status;
                }
                if (in_array($status, array_keys(wc_get_order_statuses()))) {
                    $filter['post_status'] = $status;
                }
            }
        }


        if (eib2bpro_get('s', null)) {
            $filter['q'] = eib2bpro_get('s', '');
        }

        if (eib2bpro_get('go', null)) {
            $filter['mode'] = 95;
        }

        if (eib2bpro_get('pg', null)) {
            $filter['page'] = intval(eib2bpro_get('pg', 0));
        }


        if ($customer = eib2bpro_get('customer')) {
            $filter['meta_query'] = array(
                array(
                    'key' => '_customer_user',
                    'value' => absint($customer),
                    'compare' => '=',
                )
            );
        }

        if ($group = eib2bpro_get('group') || isset($filter['group'])) {
            global $wpdb;
            $users_in_group = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT user_id FROM $wpdb->usermeta WHERE meta_key='eib2bpro_group' AND meta_value=%d",
                    $filter['group'] ?: eib2bpro_get('group',  0, 'int')
                )
            );

            $users_in_group[] = -1;

            $filter['meta_query'] = array(
                array(
                    'key' => '_customer_user',
                    'value' => $users_in_group,
                    'compare' => 'IN',
                )
            );
        }

        if (eib2bpro_get('orderby')) {
            if (false !== strpos(eib2bpro_get('orderby', ''), 'meta_')) {
                $filter['orderby'] = "meta_value_num";
                $filter['meta_key'] = sanitize_sql_orderby(str_replace('meta_', '', eib2bpro_get('orderby', '')));
            } else {
                $filter['orderby'] = sanitize_sql_orderby(eib2bpro_get('orderby', ''));
            }

            $filter['order'] = 'ASC' === eib2bpro_get('order', 'ASC') ? 'ASC' : 'DESC';
        }

        return $filter;
    }

    /**
     * Main function
     *
     * @param mixed $filter array of filter
     *
     * @return null
     */

    public static function index($filter = array())
    {
        \EIB2BPRO\Admin::wc_engine();

        $filter = self::filter($filter);

        $list = array();

        $list['statuses'] = \WC()->api->WC_API_Orders->get_order_statuses()['order_statuses'];

        foreach ($list['statuses'] as $list_status_k => $list_status_k) {
            $list['statuses_count'][$list_status_k] = \WC()->api->WC_API_Orders->get_orders_count($list_status_k)['count'];
        }

        $_orders = self::get_orders($filter['post_status'], $filter, $filter['page']);

        $orders['orders'] = $_orders['result'];

        $list['statuses_count']["count"] = $_orders['count'];
        $list['statuses_count']['trash'] = wp_count_posts('shop_order')->trash;

        switch ($mode = (!empty($filter['mode']) ? $filter['mode'] : eib2bpro_option('orders-mode', "simple"))) {

                // Standart
            case 'simple':
            case 98:
                $orders['orders'] = self::_group_by($orders['orders'], 'created_at');
                echo eib2bpro_view(self::app('name'), 1, 'list', array('orders' => $orders['orders'], 'list' => $list, 'ajax' => eib2bpro_is_ajax(), 'filter' => $filter));
                break;

            case 2:
                echo EnergyPlus_View::run('orders/list-2', array('orders' => array('all' => array('orders' => $orders['orders'])), 'list' => $list, 'ajax' => eib2bpro_is_ajax()));
                break;

            case 97:
                echo eib2bpro_view(self::app('name'), 0, 'list-mini', array('orders' => array('all' => array('orders' => $orders['orders'])), 'list' => $list, 'ajax' => 1));
                break;

                // Woocommerce Native
            case 'native':
                if (!\EIB2BPRO\Admin::is_full()) {
                    eib2bpro_frame(admin_url('edit.php?post_type=shop_order'));
                } else {
                    wp_redirect(admin_url('edit.php?post_type=shop_order'));
                }
                break;

                // Other menus
            case 95:
                echo EnergyPlus_View::run('orders/list-95', array('list' => $list, 'iframe_url' => eib2bpro_get_submenu_url(eib2bpro_get('go'))));
                break;

            case 'return':
                return $_orders;
                break;
        }
    }

    /**
     * Group titles by date
     *
     * @param array $array
     * @param string $key
     * @return array
     * @since  1.0.0
     */

    private static function _group_by($array, $key)
    {
        $return = array();

        foreach ($array as $val) {
            $time = eib2bpro_grouped_time($val['date_created']);

            $return[$time['key']]['title'] = $time['title'];
            $return[$time['key']]['orders'][] = $val;
        }
        return $return;
    }


    /**
     * Get list of products
     *
     * @param string $type
     * @param array $filter
     * @return array
     * @since  1.0.0
     */

    private static function get_orders($type, $filter = array(), $page = 0)
    {
        $count = 0;
        $orders = array();

        if (!empty(eib2bpro_get('s'))) {
            $filter['search'] = eib2bpro_get('s');
        }

        if (!empty($filter['search']) && (2 < strlen($filter['search']) or 0 === strlen($filter['search']))) {
            $results = wc_order_search($filter['search']);

            if (0 < count($results)) {
                $results = array_reverse($results);

                $results = array_slice($results, 0, 100); // Limit to 100 items

                foreach ($results as $order_id) {
                    $order = wc_get_order($order_id);
                    if ($order) {
                        if (!method_exists($order, 'get_formatted_billing_address')) {
                            continue;
                        }

                        $billing_formatted = $order->get_formatted_billing_address();
                        $shipping_formatted = $order->get_formatted_shipping_address();
                        $std = $order;

                        $order = $order->get_data();

                        $order['billing_formatted'] = $billing_formatted;
                        $order['shipping_formatted'] = $shipping_formatted;
                        $order['std'] = $std;

                        $next_statuses = array();
                        $cond = eib2bpro_option('orders-statuses', array_keys(wc_get_order_statuses()));

                        if (isset($cond['wc-' . $order['status']])) {
                            foreach ($cond['wc-' . $order['status']] as $key) {
                                if ('-' === $key) {
                                    continue;
                                } elseif ('trash' === $key) {
                                    $next_statuses[] = $key;
                                } else {
                                    $next_statuses[] = $key;
                                }
                            }
                        } else {
                            $next_statuses = array_keys(wc_get_order_statuses());
                            $next_statuses[] = 'trash';
                        }

                        $order['next_statuses'] = $next_statuses;


                        $orders[strtotime($order['date_created'])] = $order;
                    }
                }
                krsort($orders);
            }

            $count = count($results);
        } else {
            $query_args = array(
                'post_type' => 'shop_order',
                'post_status' => array_keys(wc_get_order_statuses()),
                'posts_per_page' => absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)),
                'paged' => $page,
                'orderby' => 'date',
                'order' => 'DESC'
            );

            if (isset($filter['post_status'])) {
                $query_args['post_status'] = $type;
            } else {
                $query_args['orderby'] = 'date';
                $query_args['order'] = 'DESC';
            }


            if (isset($filter['post__in'])) {
                $query_args['post__in'] = $filter['post__in'];
            }

            $query_args = array_merge($query_args, $filter);

            $query = new \WP_Query($query_args);

            if ($query->have_posts()) {
                foreach ($query->posts as $order_id) {
                    $order = wc_get_order($order_id);

                    if (is_wp_error($order)) {
                        continue;
                    }

                    $billing_formatted = $order->get_formatted_billing_address();
                    $shipping_formatted = $order->get_formatted_shipping_address();
                    $std = $order;

                    $order = $order->get_data();

                    $order['billing_formatted'] = $billing_formatted;
                    $order['shipping_formatted'] = $shipping_formatted;
                    $order['std'] = $std;

                    $next_statuses = array();
                    $cond = eib2bpro_option('orders-statuses', array_keys(wc_get_order_statuses()));

                    if (isset($cond['wc-' . $order['status']])) {
                        foreach ($cond['wc-' . $order['status']] as $key) {
                            if ('-' === $key) {
                                continue;
                            } elseif ('trash' === $key) {
                                $next_statuses[] = $key;
                            } else {
                                $next_statuses[] = $key;
                            }
                        }
                    } else {
                        $next_statuses = array_keys(wc_get_order_statuses());
                        $next_statuses[] = 'trash';
                    }

                    $order['next_statuses'] = $next_statuses;

                    $orders[] = $order;
                }
            }

            $count = $query->found_posts;
        }

        return array(
            'count' => $count,
            'result' => $orders
        );
    }
}
