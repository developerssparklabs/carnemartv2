<?php

namespace EIB2BPRO\B2b\Admin;

use EIB2BPRO\Admin\Data;

defined('ABSPATH') || exit;

class Groups
{
    public static $total_revenue = 0;
    public static function run()
    {
        switch (eib2bpro_get('action')) {
            case 'edit':
                self::edit();
                break;
            case 'delete':
                self::delete();
                break;
            default:
                self::all();
                break;
        }
    }

    public static function get($id = 0, $fields = 'all')
    {
        $params = [
            'post_type' => 'eib2bpro_groups',
            'post_status' => ['publish', 'draft'],
            'numberposts' => -1
        ];

        if (0 < $id) {
            $params['include'] = [intval($id)];
        }

        if ('all' !== $fields) {
            $params['fields'] = $fields;
        }

        return get_posts($params);
    }

    public static function all()
    {
        $groups = get_posts([
            'post_type' => 'eib2bpro_groups',
            'post_status' => ['publish', 'draft'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);

        echo eib2bpro_view('b2b', 'admin', 'groups.list', ['groups' => $groups]);
    }

    public static function edit()
    {
        $id = intval(eib2bpro_get('id', 0));

        if ($_POST) {
            $id = intval(eib2bpro_post('id'));

            // create new
            if (-3 < $id) {
                $data = Data::validate([
                    'title' => ['required' => true, 'default' => '-']
                ], $_POST);

                if (0 === $id) {
                    // insert group
                    $post = wp_insert_post([
                        'post_title' => wp_strip_all_tags($data['title']),
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_type' => 'eib2bpro_groups',
                        'post_author' => get_current_user_id()
                    ]);
                }

                if (0 < $id) {
                    // update group
                    $group = get_post($id);

                    if (!is_wp_error($group) && 'eib2bpro_groups' === $group->post_type) {
                        wp_update_post([
                            'ID' => $id,
                            'post_title' => wp_strip_all_tags($data['title']),
                        ]);

                        $post = $group->ID;
                    }
                }

                if (0 === intval($post)) {
                    eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#10');
                }

                if (0 === $id) {
                    update_post_meta($post, 'eib2bpro_position', time() * -1);
                }

                // display prices
                update_post_meta($post, 'eib2bpro_display_price_tax', eib2bpro_post('display_price', 'default'));
                eib2bpro_option('b2b_display_price_group_' . $post, eib2bpro_post('display_price', 'default'), 'set');

                // product message
                update_post_meta($post, 'eib2bpro_product_message', eib2bpro_r(wp_kses_post($_POST['product_message'])));
                if (empty(trim(eib2bpro_post('product_message')))) {
                    eib2bpro_option('b2b_product_message_group_' . $post, false, 'set');
                } else {
                    eib2bpro_option('b2b_product_message_group_' . $post, eib2bpro_r(wp_kses_post($_POST['product_message'])), 'set');
                }

                // payment methods
                $payment_methods = \WC()->payment_gateways->payment_gateways();
                foreach ($payment_methods as $payment_method) {
                    if (1 === intval(eib2bpro_post('payment_methods_' . $payment_method->id))) {
                        update_post_meta($post, 'eib2bpro_payment_method_' . $payment_method->id, '1');
                    } else {
                        update_post_meta($post, 'eib2bpro_payment_method_' . $payment_method->id, '0');
                    }
                }

                // shipping methods
                $shipping_methods = \EIB2BPRO\B2b\Site\Shipping::get_all();
                foreach ($shipping_methods as $shipping_method) {
                    if (1 === intval(eib2bpro_post('shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id))) {
                        update_post_meta($post, 'eib2bpro_shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id, '1');
                    } else {
                        update_post_meta($post, 'eib2bpro_shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id, '0');
                    }
                }
            }
            Main::clear_cache();

            eib2bpro_success('', ['after' => ['close' => true, 'refresh_window' => true]]);
        }


        if (-1 === $id || -2 === $id) {
            // b2c/guests groups

            if (-1 === $id) {
                $key = 'eib2bpro_b2c_group_settings';
                $title = esc_html__('B2C', 'eib2bpro');
            } else {
                $key = 'eib2bpro_guest_group_settings';
                $title = esc_html__('Guests', 'eib2bpro');
            }

            $other_groups = get_posts([
                'post_type' => 'eib2bpro_groups',
                'post_status' => 'private',
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => $key,
                        'value' => 'yes'
                    )
                )
            ]);

            if (0 === count($other_groups)) {
                // if not exist, create
                $post = wp_insert_post([
                    'post_title' => $title,
                    'post_content' => '',
                    'post_status' => 'private',
                    'post_type' => 'eib2bpro_groups',
                    'post_author' => get_current_user_id()
                ]);

                update_post_meta($post, $key, 'yes');

                $id = $post;
            } else {
                $id = $other_groups[0]->ID;
            }
        }

        echo eib2bpro_view('b2b', 'admin', 'groups.edit', ['id' => $id]);
    }

    public static function delete()
    {
        if ($_POST) {
            $id = intval(eib2bpro_post('id', 0));
            $move = intval(eib2bpro_post('eib2bpro_move_to', 0));

            $users = get_users([
                'meta_key'     => 'eib2bpro_group',
                'meta_value'   => $id,
                'fields' => array('ID')
            ]);

            foreach ($users as $user) {
                $type = (0 === $move) ? 'b2c' : 'b2b';
                $group = (0 === $move) ? 'b2c' : $move;

                update_user_meta($user->ID, 'eib2bpro_user_type', $type);
                update_user_meta($user->ID, 'eib2bpro_group', $group);
            }

            wp_delete_post($id, true);

            Main::clear_cache();

            eib2bpro_success('', ['after' => ['close' => true, 'refresh_window' => true]]);
        } else {
            $id = intval(eib2bpro_get('id', 0));

            if (0 === $id) {
                die(esc_html__('Error:', 'eib2bpro') . '#81');
            }

            $users = get_users([
                'meta_key'     => 'eib2bpro_group',
                'meta_value'   => $id,
                'fields' => array('ID', 'user_login')
            ]);

            echo eib2bpro_view('b2b', 'admin', 'groups.delete', ['id' => $id, 'users' => $users]);
        }
    }

    public static function mini_group_details()
    {
        $group_id = intval(eib2bpro_post('id'));
        if (self::get($group_id)) {
            echo eib2bpro_view('b2b', 'admin', 'groups.mini', ['group_id' => $group_id]);
            wp_die();
        }
    }
    public static function users($group_id = 0)
    {

        $query = new \WP_User_Query(
            array(
                'fields' => 'ID',
                'number' => 20,
                'orderby' => 'registered',
                'order' => 'DESC',
                'meta_key'     => 'eib2bpro_group',
                'meta_value'   => intval($group_id),
                'meta_compare' => '=',
            )
        );


        return $query->get_results();
    }

    public static function count_users($group_id = 0)
    {
        global $wpdb;

        $count = get_transient('eib2bpro_group_users_count');

        if (!$count) {
            $groups = self::get(0, 'ID');
            foreach ($groups as $group) {
                $users = intval($wpdb->get_var($wpdb->prepare(
                    "
                    SELECT count(*) AS cnt FROM {$wpdb->usermeta} WHERE `meta_key`='eib2bpro_group' AND meta_value=%d
                    ",
                    $group->ID
                )));

                $count[$group->ID] = $users;
            }

            set_transient('eib2bpro_group_users_count', $count, 30 * DAY_IN_SECONDS);
        }

        if (0 < $group_id) {
            return isset($count[$group_id]) ? $count[$group_id] : 0;
        }

        return (!is_array($count) || empty($count)) ? [0] : $count;
    }

    public static function revenue($group_id = 0, $last_year = false, $get_total = false)
    {
        global  $wpdb;

        $total  = 0;
        $sums = get_post_meta($group_id, '_eib2bpro_stats_revenue', true);

        $recalculate = intval(get_post_meta($group_id, '_eib2bpro_stats_total_revenue', true)) !== intval(self::$total_revenue) ? true : false;

        if (!$sums || $recalculate) {

            $sums = [0 => ['total' => 0, 'count' => 0]];

            $_sums = $wpdb->get_results($wpdb->prepare(
                "
            SELECT DATE_FORMAT(date_created, '%Y%m') AS dt, sum(total_sales) as sm, count(order_id) as cnt FROM `{$wpdb->prefix}wc_order_stats` WHERE status NOT IN ('wc-trash', 'wc-failed', 'wc-cancelled') AND `customer_id` IN (SELECT wc.customer_id FROM $wpdb->usermeta AS um LEFT JOIN {$wpdb->prefix}wc_customer_lookup AS wc ON wc.user_id=um.user_id WHERE um.meta_key='eib2bpro_group' AND um.meta_value=%d) GROUP BY dt
                ",
                $group_id
            ));

            foreach ($_sums as $sum) {
                $sums[$sum->dt] = ['total' => floatval($sum->sm), 'count' => intval($sum->cnt)];
            }
            update_post_meta($group_id, '_eib2bpro_stats_revenue', $sums);
            update_post_meta($group_id, '_eib2bpro_stats_total_revenue', intval(self::$total_revenue));
        }

        if ($get_total) {
            foreach ($sums as $sum) {
                $total += $sum['total'];
            }
            return $total;
        }

        if ($last_year) {
            for ($i = 0; $i < 12; ++$i) {
                $date = eib2bpro_strtotime("now - $i month", 'Ym');
                if (isset($sums[$date]['total'])) {
                    $__sums[$date] = $sums[$date]['total'];
                } else {
                    $__sums[$date] = 0;
                }
            }
            return $__sums;
        }

        return $sums;
    }

    public static function total_revenue()
    {
        global $wpdb;

        $sum = get_transient('eib2bpro_total_revenue');

        if (!$sum) {
            $sum = $wpdb->get_var($wpdb->prepare(
                "
         SELECT sum(total_sales) as sm FROM `{$wpdb->prefix}wc_order_stats` WHERE status NOT IN ('wc-trash', 'wc-failed', 'wc-cancelled') AND 1=%d
            ",
                1
            ));

            set_transient('eib2bpro_total_revenue', floatval($sum), 30 * DAY_IN_SECONDS);
        }

        return self::$total_revenue = floatval($sum);
    }

    public static function change_group_status()
    {
        $group_id = intval(eib2bpro_post('groupid'));

        $group = get_post($group_id);

        if (!is_wp_error($group) && 'eib2bpro_groups' === $group->post_type) {
            wp_update_post([
                'ID' => $group_id,
                'post_status' => eib2bpro_post('checked', 'false') === 'true' ? 'publish' : 'draft'
            ]);
            eib2bpro_success('OK');
        } else {
            eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#11');
        }
    }
}
