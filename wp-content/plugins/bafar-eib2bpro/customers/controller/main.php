<?php

namespace EIB2BPRO\Customers;

defined('ABSPATH') || exit;

class Main extends \EIB2BPRO\Customers
{
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
            case 'view':
                self::detail();
                break;

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

    public static function filter($filter = false)
    {
        if (!$filter) {
            $filter['offset'] = 0;
            $filter['paged'] = 1;
            $filter['q'] = '';
        }

        if (!isset($filter['number'])) {
            $filter['number'] = absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10));
        }
        $filter['orderby'] = "registered";
        $filter['order'] = "DESC";

        if (eib2bpro_get('go', null)) {
            $filter['mode'] = 95;
        }

        if ('' !== eib2bpro_get('s', '')) {
            $filter['q'] = eib2bpro_get('s', '');
        }

        if ('' !== eib2bpro_get('role', '')) {
            $filter['role'] = sanitize_key(eib2bpro_get('role', ''));
        }

        if (eib2bpro_get('pg', null)) {
            $filter['offset'] = (intval(eib2bpro_get('pg', 1)) - 1) * $filter['number'];
        }

        if (eib2bpro_get('orderby')) {
            if (false !== strpos(eib2bpro_get('orderby', ''), 'meta_')) {
                $filter['orderby'] = $filter['meta_key'] = sanitize_sql_orderby(str_replace('meta_', '', eib2bpro_get('orderby', '')));

                if ('meta__money_spent' === eib2bpro_get('orderby', '')) {
                    $filter['orderby'] = 'meta_value_num';
                }
            } else {
                $filter['orderby'] = sanitize_sql_orderby(eib2bpro_get('orderby', ''));
            }

            $filter['order'] = 'ASC' === eib2bpro_get('order', 'ASC') ? 'DESC' : 'ASC';
        }

        if (eib2bpro_get('group')) {
            $group = eib2bpro_get('group');
            if ('b2c' === $group) {
                $filter['meta_query'] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'eib2bpro_group',
                        'value' => '',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => 'eib2bpro_group',
                        'value' => 'b2c'
                    )
                );
            } else {
                $filter['meta_key']     = 'eib2bpro_group';
                $filter['meta_value']   = eib2bpro_get('group');
                $filter['meta_compare'] = '=';
            }
        }

        return $filter;
    }

    /**
     * Main function
     *
     * @param mixed $filter array of filter
     * @return eib2bpro_View
     */

    public static function index($filter = false)
    {
        $filter = self::filter($filter);

        switch ($mode = (!empty($filter['mode']) ? absint($filter['mode']) : eib2bpro_option('customers_mode', 1))) {

                // Woocommerce Native
            case 99:
                if (!\EIB2BPRO\Admin::is_full()) {
                    eib2bpro_frame(admin_url('users.php'));
                } else {
                    wp_redirect(admin_url('users.php'));
                }
                break;

                // Other menus
            case 95:
                $customers_count = \WC()->api->WC_API_Customers->get_customers_count();
                echo eib2bpro_view('customers', 1, 'list-95', array('count' => intval($customers_count['count']), 'iframe_url' => eib2bpro_get_submenu_url(eib2bpro_get('go'))));
                break;

                // Standart & Search
            case 1:
            case 2:
            case 98:

                global $wpdb, $wp_roles;
                $customers = self::get_customers($filter);

                if ('roles' === eib2bpro_option('customers_nav', 'groups')) {
                    $roles = $wp_roles->roles;
                    $counts = count_users();

                    if (isset($counts['avail_roles'][sanitize_key(eib2bpro_get('role', ''))])) {
                        $count = $counts['avail_roles'][sanitize_key(eib2bpro_get('role', ''))];
                    } else {
                        $count = $customers[1];
                    }
                } else {
                    $roles = \EIB2BPRO\B2b\Admin\Groups::get();
                    $counts = \EIB2BPRO\B2b\Admin\Groups::count_users();

                    $total_users = count_users()['total_users'];
                    $counts['b2c'] = $total_users - array_sum($counts);
                    $counts['total_users'] = $total_users;

                    if (isset($counts[sanitize_key(eib2bpro_get('group', ''))])) {
                        $count = $counts[sanitize_key(eib2bpro_get('group', ''))];
                    } else {
                        $count = $total_users;
                    }
                }

                echo eib2bpro_view(self::app('name'), 1, 'list', array('count' => $count, 'roles' => $roles, 'counts' => $counts, 'filter' => $filter, 'per_page' => $filter['number'], 'customers' => $customers[0], 'mode' => $mode, 'ajax' => eib2bpro_is_ajax()));

                break;
        }
    }

    /**
     * Get user details
     *
     * @return void
     * @since  1.0.0
     */

    public static function detail()
    {
        $id = intval(eib2bpro_get('id', 0));

        if (0 === $id) {
            wp_die(-1);
        }

        \EIB2BPRO\Admin::wc_engine();
        $customer = \WC()->api->WC_API_Customers->get_customer($id);

        if (is_wp_error($customer)) {
            wp_die(-2);
        }

        $customer_id = absint($customer['customer']['id']);

        $orders = \WC()->api->WC_API_Orders->get_orders(null, array('customer' => $customer_id));
        $refunded = \WC()->api->WC_API_Orders->get_orders(null, array('status' => 'refunded', 'customer' => $customer_id));
        $meta = get_user_meta($customer_id);

        $orders = \EIB2BPRO\Orders\Main::index(array(
            'post_status' => array_keys(wc_get_order_statuses()),
            'mode' => 97,
            'page' => 1,
            'posts_per_page' => 99999,
            'meta_query' => array(
                array(
                    'key' => '_customer_user',
                    'value' => $customer_id,
                    'compare' => '=',
                )
            )
        ));
        echo eib2bpro_view('customers', 0, 'detail', array('customer' => $customer['customer'], 'orders' => $orders, 'meta' => $meta));
    }

    /**
     * Get list of customers
     *
     * @param array $filter
     * @return array
     * @since  1.0.0
     */

    public static function get_customers($filter)
    {
        $result = array();

        $_query = new \WP_User_Query($filter);
        $query = $_query->get_results();

        if (isset($filter['search1']) && !empty($filter['search1'])) {
            $filter2 = [
                'search' => "*" . $filter['search1'] . "*",
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email',
                    'user_url',
                )
            ];
            $_query2 = new \WP_User_Query($filter2);
            $query2 = $_query2->get_results();
            if (!empty($query2)) {
                $query = array_unique(array_merge($query, $query2), SORT_REGULAR);
            }
        }

        if (!empty($query)) {
            foreach ($query as $user) {
                $customer = new \WC_Customer($user->ID);

                if (is_wp_error($customer)) {
                    continue;
                }


                $last_order = $customer->get_last_order();

                $customer_data = array(
                    'id' => $customer->get_id(),
                    'email' => $customer->get_email(),
                    'first_name' => $customer->get_first_name(),
                    'last_name' => $customer->get_last_name(),
                    'username' => $customer->get_username(),
                    'role' => $customer->get_role(),
                    'last_order_id' => is_object($last_order) ? $last_order->get_id() : null,
                    'last_order_date' => is_object($last_order) ? ($last_order->get_date_created() ? $last_order->get_date_created() : 0) : 0,
                    'orders_count' => $customer->get_order_count(),
                    'total_spent' => wc_format_decimal($customer->get_total_spent(), 2),
                    'avatar_url' => $customer->get_avatar_url(),
                    'billing_address' => array(
                        'first_name' => $customer->get_billing_first_name(),
                        'last_name' => $customer->get_billing_last_name(),
                        'company' => $customer->get_billing_company(),
                        'address_1' => $customer->get_billing_address_1(),
                        'address_2' => $customer->get_billing_address_2(),
                        'city' => $customer->get_billing_city(),
                        'state' => $customer->get_billing_state(),
                        'postcode' => $customer->get_billing_postcode(),
                        'country' => $customer->get_billing_country(),
                        'email' => $customer->get_billing_email(),
                        'phone' => $customer->get_billing_phone(),
                    ),
                    'shipping_address' => array(
                        'first_name' => $customer->get_shipping_first_name(),
                        'last_name' => $customer->get_shipping_last_name(),
                        'company' => $customer->get_shipping_company(),
                        'address_1' => $customer->get_shipping_address_1(),
                        'address_2' => $customer->get_shipping_address_2(),
                        'city' => $customer->get_shipping_city(),
                        'state' => $customer->get_shipping_state(),
                        'postcode' => $customer->get_shipping_postcode(),
                        'country' => $customer->get_shipping_country(),
                    ),
                );

                $result[] = $customer_data;
            }
        }
        return array($result, $_query->get_total());
    }
}
