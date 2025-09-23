<?php

namespace EIB2BPRO\Orders;

defined('ABSPATH') || exit;

class Ajax extends \EIB2BPRO\Orders
{

    /**
     * Ajax router
     *
     * @return EnergyPlus_Ajax
     * @since  1.0.0
     */

    public static function run()
    {
        global $woocommerce;

        \EIB2BPRO\Admin::wc_engine();

        $do = eib2bpro_post('do', 'default');

        switch ($do) {

            case "filter":
                $fields = $_POST['fields'];

                $filter = array();

                foreach ($fields as $key => $field) {

                    $field['value'] = sanitize_key($field['value']);

                    if ('order_id' === $field['name'] && trim($field['value']) !== '') {
                        $filter['post__in'] = array(eib2bpro_clean($field['value']));
                    }

                    if ('status' === $field['name'] && trim($field['value']) !== '') {
                        if (in_array($field['value'], array('pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'))) {
                            $filter['post_status'] = "wc-" . eib2bpro_clean($field['value']);
                        }
                    }

                    if ('customer' === $field['name'] && trim($field['value']) !== '') {
                        $filter['meta_query'] = array(
                            array(
                                'key' => '_customer_user',
                                'value' => absint(eib2bpro_clean($field['value'])),
                                'compare' => '=',
                            ),
                        );
                    }
                }

                echo \EIB2BPRO\Orders\Main::index($filter);
                wp_die();
                break;

                // Search
            case 'search':

                $status = eib2bpro_post('extra', '');

                if (in_array('wc-' . $status, array_keys(wc_get_order_statuses()))) {
                    $filter['post_status'] = "wc-" . $status;
                } else {
                    $filter['post_status'] = array_keys(wc_get_order_statuses());
                }

                $filter['search'] = eib2bpro_post('q', '');

                $filter['mode'] = (eib2bpro_post('mode') ? absint(eib2bpro_post('mode')) : null);
                $filter['page'] = 1;

                echo \EIB2BPRO\Orders\Main::index($filter);
                wp_die();
                break;

                // Delete or restrore order
            case 'deleteforever':
            case 'restore':

                $id = absint(eib2bpro_post('id', 0));

                $order = new \WC_Order($id);

                if (!$order) {
                    eib2bpro_error(esc_html__('Order is not exists', 'eib2bpro'));
                    wp_die();
                }

                if ('deleteforever' === $do) {
                    $change = wp_delete_post($id, true);
                } else {
                    $change = wp_untrash_post($id);
                }

                if (!$change) {
                    eib2bpro_error(esc_html__('Order can not be restore', 'eib2bpro'));
                } else {
                    eib2bpro_success('OK', array('id' => $id, 'message' => esc_html__('Order has been restored', 'eib2bpro')));
                }

                break;

                // Change status of order
            case 'changestatus':

                $result = array();
                $status = eib2bpro_post('status');
                $ids = wp_parse_id_list(eib2bpro_post('id', array()));

                if (!is_array($ids)) {
                    wp_die(-1);
                }

                if (!in_array("wc-" . $status, array_keys(wc_get_order_statuses())) && !in_array($status, array_keys(wc_get_order_statuses())) && !in_array($status, array('all', 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'trash', 'restore', 'deleteforever'))) {
                    wp_die(-2);
                }

                $ids = array_map('absint', $ids);

                foreach ($ids as $id) {

                    $change = false;
                    $order = new \WC_Order(absint($id));

                    if ($order) {

                        if ('trash' === $status) {
                            $change = wp_trash_post($id);
                        } else if ('deleteforever' === $status) {
                            $change = wp_delete_post($id, true);
                        } else if ('restore' === $status) {
                            $change = wp_untrash_post($id);
                        } else {
                            $change = $order->update_status($status);
                            do_action('woocommerce_update_order', $order->get_id());
                        }

                        wc_delete_shop_order_transients(absint($id));
                    }

                    if ($change) {
                        $result['success'][] = $id;
                    } else {
                        $result['errors'][] = $id;
                    }
                }

                return eib2bpro_success('Order status has been changed', $result);

                break;
        }
    }
}
