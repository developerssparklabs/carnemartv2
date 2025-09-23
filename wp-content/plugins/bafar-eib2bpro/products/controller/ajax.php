<?php

namespace EIB2BPRO\Products;

defined('ABSPATH') || exit;

class Ajax
{


    /**
     * Ajax router
     *
     * @return eib2bpro_Ajax
     * @since  1.0.0
     */

    public static function run()
    {

        $do = eib2bpro_post('do');
        \EIB2BPRO\Admin::wc_engine();

        switch ($do) {

                // Searching
            case 'search':

                $filter['q'] = eib2bpro_post('q', '');
                $filter['category'] = eib2bpro_post('status', '');
                $filter['mode'] = (eib2bpro_post('mode') ? absint(eib2bpro_post('mode')) : null);
                $filter['post_status'] = array('publish', 'private');
                echo Main::index($filter);
                wp_die();

                break;

                // Delete an attributes
            case 'delete-attribute':

                $id = absint(eib2bpro_post('id', 0));

                if (0 === $id) {
                    wp_die(-1);
                }

                if (!wp_verify_nonce(eib2bpro_post('_wpnonce'), 'eib2bpro-products--attr-delete-' . $id)) {
                    wp_die(esc_html__('Security check', 'eib2bpro'));
                }

                \EIB2BPRO\Admin::wc_engine();

                $r = \WC()->api->WC_API_Products->delete_product_attribute($id);

                if (is_wp_error($r)) {
                    eib2bpro_error($r->get_error_message());
                } else {
                    eib2bpro_success('OK');
                }

                break;

                // Bulk operations
            case 'bulk':

                \EIB2BPRO\Admin::wc_engine();

                $product_ids = eib2bpro_post('id');

                if ('' === $product_ids) {
                    exit;
                }

                $ids = explode(',', $product_ids);

                if (!is_array($ids) or (0 === count($ids))) {
                    exit;
                }

                $ids = array_map('absint', $ids);

                $success = array();

                foreach ($ids as $id) {

                    $product = \WC()->api->WC_API_Products->get_product(intval($id));

                    if (!$product) {
                        continue;
                    }

                    if ('outofstock' === eib2bpro_post('state') or 'instock' === eib2bpro_post('state')) {

                        if ('instock' === eib2bpro_post('state')) {
                            if (0 < $product['product']['stock_quantity']) {
                                $new_stock_quantity = intval($product['product']['stock_quantity']);
                            } else {
                                $new_stock_quantity = 9998;
                            }
                            $new_instock = true;
                        } else {
                            $new_stock_quantity = 0;
                            $new_instock = false;
                        }

                        if (true === $product['product']['managing_stock']) {
                            $do_it = array('stock_quantity' => $new_stock_quantity);
                        } else {
                            $do_it = array('in_stock' => $new_instock);
                        }

                        $return = \WC()->api->WC_API_Products->edit_product($product['product']['id'], array(
                            'product' => $do_it
                        ));

                        if (true === $return['product']['in_stock']) {
                            if (true === $return['product']['managing_stock']) {
                                $r[] = array('id' => $id, 'status' => intval($return['product']['stock_quantity']));
                            } else {
                                $r[] = array('id' => $id, 'status' => '<span class="text-mute">∞</span>');
                            }
                        } else {
                            $r[] = array('id' => $id, 'status' => '<span class="badge badge-danger">' . esc_html__('Out of stock', 'eib2bpro') . '</span>');
                        }
                    } else if ('trash' === eib2bpro_post('state')) {

                        $change = wp_trash_post($id);

                        if ($change) {
                            $r[] = array('id' => $id, 'status' => esc_html__('Deleted', 'eib2bpro'));
                        } else {
                            return eib2bpro_error(sprintf(esc_html__('Product #%d can not be deleted', 'eib2bpro'), $id));
                        }
                    } else if ('deleteforever' === eib2bpro_post('state')) {

                        $change = wp_delete_post($id, true);

                        if ($change) {
                            $r[] = array('id' => $id, 'status' => esc_html__('Deleted', 'eib2bpro'));
                        } else {
                            return eib2bpro_error(sprintf(esc_html__('Product #%d can not be deleted', 'eib2bpro'), $id));
                        }
                    } else if ('restore' === eib2bpro_post('state')) {

                        $change = wp_untrash_post($id);

                        if ($change) {
                            $r[] = array('id' => $id, 'status' => '<span class="badge badge-success">' . esc_html__('Restored', 'eib2bpro') . '</span>');
                        } else {
                            return eib2bpro_error(sprintf(esc_html__('Product #%d can not be restored', 'eib2bpro'), $id));
                        }
                    }
                }

                return eib2bpro_success('OK', array('id' => $r, ''));

                break;

                // Set quantity and prices for product
            case 'quantity':

                $id = intval(eib2bpro_post('id', 0));
                $name = eib2bpro_post('name', '');
                $val = eib2bpro_post('val', '');
                $state = ('true' === eib2bpro_post('state', 'false')) ? false : true;

                $product = \WC()->api->WC_API_Products->get_product($id);

                if (is_wp_error($product)) {
                    wp_die(-1);
                }

                switch ($name) {

                        // Set price of product
                    case 'sale_price':
                    case 'regular_price':
                    case 'set_price':

                        $id = intval(eib2bpro_post('id', 0));
                        $name = ('sale_price' === eib2bpro_post('name', '')) ? 'sale_price' : 'regular_price';

                        $product = \WC()->api->WC_API_Products->get_product(intval(eib2bpro_clean($id)));

                        if (is_wp_error($product)) {
                            wp_die(-1);
                        }

                        $product = current($product);

                        $k = \WC()->api->WC_API_Products->edit_product(
                            $product['id'],
                            array(
                                'product' => array(
                                    'regular_price' => eib2bpro_post('val'),
                                    'sale_price' => eib2bpro_post('val1')
                                )
                            )
                        );

                        $product = \WC()->api->WC_API_Products->get_product(intval(eib2bpro_clean($id)));
                        $product = current($product);

                        eib2bpro_success($product['price_html'], array(), TRUE);

                        break;

                        // Set quantity of product
                    case "qnty":

                        $return = \WC()->api->WC_API_Products->edit_product($product['product']['id'], array(
                            'product' => array(
                                'stock_quantity' => intval($val),
                                'managing_stock' => true,
                                'in_stock' => true
                            )
                        ));

                        Main::stock_status($return);

                        break;

                        // Set stock to unlimited
                    case "unlimited":

                        $return = \WC()->api->WC_API_Products->edit_product($product['product']['id'], array(
                            'product' => array(
                                'managing_stock' => $state
                            )
                        ));

                        Main::stock_status($return);

                        break;

                        // Set stock to Out Of Stock
                    case "outofstock":
                        if (true === $product['product']['managing_stock']) {
                            if (false === $state) {
                                $do_it = array('stock_quantity' => 0);
                            } else {
                                $do_it = array('stock_quantity' => 9999);
                            }
                        } else {
                            $do_it = array('in_stock' => $state);
                        }

                        $return = \WC()->api->WC_API_Products->edit_product($product['product']['id'], array(
                            'product' => $do_it
                        ));


                        Main::stock_status($return);

                        break;
                }

                break;


                // Set product's visibilty on catalog
            case 'visible':

                $id = intval(eib2bpro_post('id', 0));
                $state = ('true' === eib2bpro_post('state', 'false')) ? 'visible' : 'hidden';
                $state_status = ('true' === eib2bpro_post('state', 'false')) ? 'publish' : 'private';

                $product = \WC()->api->WC_API_Products->get_product(intval(eib2bpro_clean($id)));

                if (is_wp_error($product)) {
                    wp_die(-1);
                }

                $product = current($product);

                $k = \WC()->api->WC_API_Products->edit_product($product['id'], array(
                    'product' => array(
                        'catalog_visibility' => $state,
                        'status' => $state_status
                    )
                ));

                eib2bpro_success();

                break;

                // Set stock of procut
            case 'in_stock':
                $id = intval(eib2bpro_post('id', 0));
                $state = ('true' === eib2bpro_post('state', 'false')) ? true : false;

                $product = \WC()->api->WC_API_Products->get_product(intval(eib2bpro_clean($id)));

                if (is_wp_error($product)) {
                    wp_die(-1);
                }

                $product = current($product);
                if ('variable' === $product['type']) {
                    foreach ($product['variations'] as $variants) {
                        $k = \WC()->api->WC_API_Products->edit_product($variants['id'], array(
                            'product' => array(
                                'in_stock' => $state
                            )
                        ));
                    }
                } else {
                    $k = \WC()->api->WC_API_Products->edit_product($product['id'], array(
                        'product' => array(
                            'in_stock' => $state
                        )
                    ));
                }
                eib2bpro_success();

                break;

                // Reorder categories
            case 'categories_reorder':

                $ids = $_POST['ids'];

                if (!is_array($ids)) {
                    wp_die(-1);
                }

                foreach ($ids as $i => $id) {

                    if (!isset($id['id'])) {
                        continue;
                    }

                    $i = absint($i);

                    $category = \WC()->api->WC_API_Products->get_product_category(absint($id['id']));

                    if (!is_wp_error($category)) {

                        wc_set_term_order($category['product_category']['id'], $i, 'product_cat');
                        $k = \WC()->api->WC_API_Products->edit_product_category(
                            $category['product_category']['id'],
                            array(
                                'product_category' => array(
                                    'product_category' => absint($category['product_category']['id']),
                                    'parent' => absint($id['parent_id'])
                                )
                            )
                        );
                    }
                }

                eib2bpro_success('Done');
        }
    }
}
