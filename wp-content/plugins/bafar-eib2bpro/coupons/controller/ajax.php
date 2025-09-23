<?php

namespace EIB2BPRO\Coupons;

defined('ABSPATH') || exit;

class Ajax
{
    public static function run()
    {
        $do = eib2bpro_post('do');
        $coupon_id = eib2bpro_post('id', 0);

        switch ($do) {

                // Searching
            case 'search':

                \EIB2BPRO\Admin::wc_engine();

                $filter['s'] = eib2bpro_post('q', '');
                $filter['post_status'] = array('publish', 'private');
                $filter['limit'] = 99;

                echo \EIB2BPRO\Coupons\Main::index($filter);
                wp_die();

                break;

                // Bulk operations
            case 'bulk':

                \EIB2BPRO\Admin::wc_engine();

                if ('' === $coupon_id) {
                    exit;
                }

                $ids = explode(',', $coupon_id);

                if (!is_array($ids) or (0 === count($ids))) {
                    exit;
                }

                $ids = array_map('absint', $ids);

                $success = array();

                foreach ($ids as $id) {

                    $coupon = get_page($id, OBJECT, 'shop_coupon');

                    if (0 === $coupon->ID) {
                        return eib2bpro_error(esc_html__('Error', 'eib2bpro'));
                    }

                    if ('trash' === eib2bpro_post('state')) {
                        \WC()->api->WC_API_Coupons->delete_coupon($id);
                        $success[] = $id;
                        $state = 'trashnew';
                    }

                    if ('restore' === eib2bpro_post('state')) {
                        $result = wp_untrash_post($id);
                        $success[] = $id;
                        $state = 'trashnew';
                    }

                    if ('deleteforever' === eib2bpro_post('state')) {
                        $result = \WC()->api->WC_API_Coupons->delete_coupon($id, 'true');
                        $success[] = $id;
                        $state = 'trashnew';
                    }

                    if ('private' === eib2bpro_post('state') or 'publish' === eib2bpro_post('state')) {
                        $state = ('private' === eib2bpro_post('state')) ? 'private' : 'publish';

                        $my_post = array(
                            'ID' => $id,
                            'post_status' => $state,
                        );

                        $success[] = $id;

                        wp_update_post($my_post);
                    }
                }

                return eib2bpro_success('Coupon has been changed', array('id' => $success, 'new' => $state));

                break;


                // Active or passive the coupon
            case 'active':

                if ('' === $coupon_id) {
                    exit;
                }

                $coupon = get_page_by_title($coupon_id, OBJECT, 'shop_coupon');

                if (0 === $coupon->ID) {
                    return eib2bpro_error(esc_html__('Error', 'eib2bpro'));
                }

                $state = ('false' === eib2bpro_post('state')) ? 'private' : 'publish';

                $my_post = array(
                    'ID' => $coupon->ID,
                    'post_status' => $state,
                );

                wp_update_post($my_post);

                return eib2bpro_success('Coupon has been changed', array('id' => $coupon->ID, 'new' => $state));

                break;
        }
    }
}
