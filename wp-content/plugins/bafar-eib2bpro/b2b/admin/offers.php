<?php

namespace EIB2BPRO\B2b\Admin;

use EIB2BPRO\Admin\Data;

defined('ABSPATH') || exit;

class Offers
{
    public static function run()
    {
        do_action('wpml_set_translation_mode_for_post_type', 'eib2bpro_offers', 'translate');

        switch (eib2bpro_get('action')) {
            case 'edit':
                self::edit();
                break;
            case 'mail':
                self::mail();
                break;
            default:
                self::index();
                break;
        }
    }

    public static function index()
    {
        $list = new \WP_Query([
            'post_type' => 'eib2bpro_offers',
            'post_status' => ['publish', 'private'],
            'posts_per_page' => eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10),
            'paged' => (eib2bpro_get('pg') ? eib2bpro_get('pg') : 1),
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        echo eib2bpro_view('b2b', 'admin', 'offers.list', ['list' => $list]);
    }

    public static function edit()
    {
        $id = intval(eib2bpro_get('id', 0));

        if ($_POST) {
            $id = intval(eib2bpro_post('id'));

            // create new
            if (-1 < $id) {
                $data = Data::validate([
                    'title' => ['required' => true, 'default' => '-']
                ], $_POST);

                if (0 === $id) {
                    // insert
                    $post_id = wp_insert_post([
                        'post_title' => wp_strip_all_tags($data['title']),
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_type' => 'eib2bpro_offers',
                        'post_author' => get_current_user_id()
                    ]);
                }

                if (0 < $id) {
                    // update
                    $post = get_post($id);

                    if (!is_wp_error($post) && 'eib2bpro_offers' === $post->post_type) {
                        $status = get_post_status($id);

                        wp_update_post([
                            'ID' => $id,
                            'post_status' => $status,
                            'post_title' => wp_strip_all_tags($data['title']),
                        ]);

                        if ('draft' === $status) {
                            $quote_id = intval(eib2bpro_post('quote_id', 0));
                            if (0 < $quote_id) {
                                wp_publish_post($id);
                                update_post_meta($id, 'eib2bpro_position', time() * -1);

                                update_post_meta($quote_id, 'eib2bpro_offered', current_time('timestamp'));
                                update_post_meta($quote_id, 'eib2bpro_offer_id', $id);
                                update_post_meta($quote_id, 'eib2bpro_offered_by', get_current_user_id());
                            }
                        }

                        $post_id = $post->ID;
                    }
                }

                if (0 === intval($post_id)) {
                    eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#21');
                }

                update_post_meta($post_id, 'eib2bpro_groups', sanitize_text_field(implode(',', array_map('trim', $_POST['eib2bpro_groups']))));
                $selected_groups = array_values($_POST['eib2bpro_groups']);
                foreach (Groups::get() as $group) {
                    if (in_array($group->ID, $selected_groups)) {
                        update_post_meta($post_id, 'eib2bpro_group_' . $group->ID, 1);
                    } else {
                        delete_post_meta($post_id, 'eib2bpro_group_' . $group->ID);
                    }
                }

                $old_users = wp_parse_list(get_post_meta($post_id, 'eib2bpro_users', true));
                if (is_array($old_users)) {
                    foreach ($old_users as $old_user) {
                        if (is_numeric($old_user)) {
                            delete_post_meta($post_id, 'eib2bpro_user_' . intval($old_user));
                        } else {
                            delete_post_meta($post_id, 'eib2bpro_user_' . md5($old_user));
                        }
                    }
                }


                $selected_users = wp_parse_list($_POST['eib2bpro_users']);
                if (is_array($selected_users)) {
                    foreach ($selected_users as $selected_user) {
                        if (is_numeric($selected_user)) {
                            update_post_meta($post_id, 'eib2bpro_user_' . intval($selected_user), 1);
                        } else {
                            update_post_meta($post_id, 'eib2bpro_user_' . md5($selected_user), 1);
                        }
                    }
                }

                update_post_meta($post_id, 'eib2bpro_users', eib2bpro_post('eib2bpro_users', ''));

                // products
                $products = [];
                $total = 0;

                foreach ($_POST['offer-product'] as $index => $product) {
                    if (0 < intval(sanitize_text_field($product))) {
                        $unit = intval(sanitize_text_field($_POST['offer-unit'][$index]));
                        $price = wc_format_decimal(sanitize_text_field($_POST['offer-price'][$index]));

                        if (0 < $unit) {
                            $products[] = [
                                'id' => intval(sanitize_text_field($product)),
                                'unit' => $unit,
                                'price' => $price
                            ];
                            $total += $price * intval($unit);
                        }
                    }
                }

                update_post_meta($post_id, 'eib2bpro_products', $products);
                update_post_meta($post_id, 'eib2bpro_total', $total);

                // promo
                update_post_meta($post_id, 'eib2bpro_promo_text', wp_kses_post($_POST['eib2bpro_promo_text']));
                update_post_meta($post_id, 'eib2bpro_promo_img', intval(eib2bpro_post('eib2bpro_promo_img', 0)));
                update_post_meta($post_id, 'eib2bpro_cart_img', intval(eib2bpro_post('eib2bpro_cart_img', 0)));

                update_post_meta($post_id, 'eib2bpro_offer_type', eib2bpro_post('eib2bpro_offer_type', 'bundle'));

                if (0 === $id) {
                    update_post_meta($post_id, 'eib2bpro_position', time() * -1);
                }
            }

            if (0 < intval(eib2bpro_post('quote_id', 0))) {
                eib2bpro_success('', ['after' => ['close' => true, 'redirect' => eib2bpro_admin('b2b', ['section' => 'quote'])]]);
            } else {
                eib2bpro_success('', ['after' => ['close' => true, 'redirect' => eib2bpro_admin('b2b', ['section' => 'offers'])]]);
            }
        }

        if (0 < intval(eib2bpro_get('quote', 0))) {
            self::quote_request(eib2bpro_get('quote', 0));
        }

        if (0 < eib2bpro_get('trid', 0, 'int')) {
            $redirect_id = \EIB2BPRO\B2B\Admin\Toolbox::duplicate_post_for_wpml(eib2bpro_get('original', 0, 'int'));
            wp_safe_redirect(
                eib2bpro_admin('b2b', ['section' => 'offers', 'action' => 'edit', 'id' => $redirect_id])
            );
        }


        echo eib2bpro_view('b2b', 'admin', 'offers.edit', ['id' => $id]);
    }

    public static function quote_request($id)
    {
        $quote = get_post($id);
        if ($quote) {
            $meta = get_post_meta($id);

            $username = 'Visitor';

            if (0 < ($customer_id = $meta['eib2bpro_customer_id'][0])) {
                $customer = get_userdata($customer_id);
                if ($customer) {
                    $username = $customer->user_login;
                }
            } else {
                if (0 === intval($meta['eib2bpro_customer_id'][0]) && !empty($meta['eib2bpro_customer_email'][0])) {
                    $username =  $meta['eib2bpro_customer_email'][0];
                } else {
                    $username = esc_html__('Visitor', 'eib2bpro');
                }
            }

            $offer_id = wp_insert_post([
                'post_title' => wp_strip_all_tags(sprintf(esc_html__('Offer for %s', 'eib2bpro'), $username)),
                'post_content' => '',
                'post_status' => 'draft',
                'post_type' => 'eib2bpro_offers',
                'post_author' => get_current_user_id()
            ]);

            if (0 === $offer_id) {
                return;
            }

            update_post_meta($offer_id, 'eib2bpro_user_' . intval($meta['eib2bpro_customer_id'][0]), 1);
            if (0 === intval($meta['eib2bpro_customer_id'][0]) && !empty($meta['eib2bpro_customer_email'][0])) {
                update_post_meta($offer_id, 'eib2bpro_users', $meta['eib2bpro_customer_email'][0]);
            } elseif (0 === intval($meta['eib2bpro_customer_id'][0]) && empty($meta['eib2bpro_customer_email'][0])) {
                update_post_meta($offer_id, 'eib2bpro_users', '');
            } else {
                update_post_meta($offer_id, 'eib2bpro_users', $meta['eib2bpro_customer_id'][0]);
            }

            $products = [];
            $qutote_products = get_post_meta($id, 'eib2bpro_products', true);
            foreach ($qutote_products as $product_id => $product_details) {
                $product_obj = wc_get_product($product_id);
                if ($product_obj) {
                    $products[] = [
                        'id' => intval(sanitize_text_field($product_id)),
                        'unit' => $product_details['qty'],
                        'price' => number_format($product_obj->get_price(), 2)
                    ];
                }
            }

            update_post_meta($offer_id, 'eib2bpro_products', $products);

            wp_redirect(eib2bpro_admin('b2b', ['section' => 'offers', 'action' => 'edit', 'id' => $offer_id, 'quote_id' => $id]));
        }
    }

    public static function mail()
    {
        $id = eib2bpro_get('id', 0, 'int');
        if ($_POST) {
            self::mail_offer();
        }
        echo eib2bpro_view('b2b', 'admin', 'offers.mail', ['id' => $id]);
    }

    public static function mail_offer()
    {
        $id = eib2bpro_post('id', 0, 'int');
        $mailer = \WC()->mailer();

        $offer = get_post($id);
        if ($offer) {

            update_post_meta($offer->ID, 'eib2bpro_mail_sent', time());

            $groups = wp_parse_id_list(get_post_meta($offer->ID, 'eib2bpro_groups', true));
            foreach ($groups as $group) {
                if (0 < intval($group)) {
                    $users = get_users(
                        array(
                            'meta_query' => array(
                                array(
                                    'key'     => 'eib2bpro_group',
                                    'value'   => $group,
                                    'compare' => '='
                                )
                            ),
                            'fields' => array('user_email', 'ID'),
                        )
                    );

                    foreach ($users as $email) {
                        do_action('eib2bpro_new_offer_mail', $offer->ID, $email->user_email, $email->ID);
                    }
                }
            }

            $guests = wp_parse_list(get_post_meta($offer->ID, 'eib2bpro_users', true));
            if (!empty($guests)) {
                foreach ($guests as $guest) {
                    do_action('eib2bpro_new_offer_mail', $offer->ID, $guest, 0);
                }
            }
        }

        eib2bpro_success('', ['after' => ['close' => true, 'redirect' => eib2bpro_admin('b2b', ['section' => 'offers'])]]);
    }


    public static function edit_positions()
    {
        $index = 0;

        foreach ($_POST['position'] as $post => $value) {
            ++$index;
            $post_id = intval($post);
            update_post_meta($post_id, 'eib2bpro_position', $index);
        }

        eib2bpro_success();
    }
}
