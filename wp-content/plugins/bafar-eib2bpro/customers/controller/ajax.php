<?php

namespace EIB2BPRO\Customers;

defined('ABSPATH') || exit;

class Ajax extends \EIB2BPRO\Customers
{
    /**
     * Ajax router
     *
     * @since  1.0.0
     */

    public static function run()
    {

        $do = eib2bpro_post('do');
        $customer_id = absint(eib2bpro_post('id', 0));

        switch ($do) {

                // Search
            case 'search':

                \EIB2BPRO\Admin::wc_engine();

                $filter['mode'] = (eib2bpro_post('mode') ? absint(eib2bpro_post('mode')) : null);
                $filter['search1'] = esc_sql(sanitize_text_field(eib2bpro_post('q', '')));
                $filter['number'] = 99;
                $filter['meta_query']  = array();

                $url = parse_url(eib2bpro_post('_wp_http_referer', ''));
                if (isset($url['query'])) {
                    parse_str($url['query'], $queries);
                    if (isset($queries['group'])) {

                        if ('b2c' === $queries['group']) {
                            $filter['meta_query'] = array(
                                'relation' => 'AND',
                                array(
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
                                )
                            );
                        } else {
                            $filter['meta_query'] = array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'eib2bpro_group',
                                    'value' => intval($queries['group']),
                                    'compare' => '='
                                )
                            );
                        }
                    }
                }

                $filter['meta_query'] = array_merge(
                    $filter['meta_query'],
                    array(
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => 'first_name',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'last_name',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'billing_email',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'billing_phone',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'billing_city',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'shipping_email',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'shipping_phone',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            ),
                            array(
                                'key' => 'shipping_city',
                                'value' => $filter['search1'],
                                'compare' => 'LIKE'
                            )
                        )
                    )
                );

                echo \EIB2BPRO\Customers\Main::index($filter);
                wp_die();
                break;


                // Update user details
            case 'update':

                \EIB2BPRO\Admin::wc_engine();

                $customer = \WC()->api->WC_API_Customers->get_customer($customer_id);

                if (is_wp_error($customer)) {
                    eib2bpro_error($customer->get_error_message());
                    wp_die();
                }

                $customer_id = absint($customer['customer']['id']);

                $data['first_name'] = esc_sql(eib2bpro_post('billing_first_name'));
                $data['last_name'] = esc_sql(eib2bpro_post('billing_last_name'));
                $data['billing_address']['first_name'] = esc_sql(eib2bpro_post('billing_first_name'));
                $data['billing_address']['last_name'] = esc_sql(eib2bpro_post('billing_last_name'));
                $data['billing_address']['company'] = esc_sql(eib2bpro_post('billing_company'));
                $data['billing_address']['address_1'] = esc_sql(eib2bpro_post('billing_address_1'));
                $data['billing_address']['address_2'] = esc_sql(eib2bpro_post('billing_address_2'));
                $data['billing_address']['city'] = esc_sql(eib2bpro_post('billing_city'));
                $data['billing_address']['state'] = esc_sql(eib2bpro_post('billing_state'));
                $data['billing_address']['postcode'] = esc_sql(eib2bpro_post('billing_postcode'));
                $data['billing_address']['country'] = esc_sql(eib2bpro_post('billing_country'));
                if ('' !== trim(eib2bpro_post('billing_email'))) {
                    $data['billing_address']['email'] = esc_sql(sanitize_email(eib2bpro_post('billing_email')));
                    $data['email'] = esc_sql(sanitize_email(eib2bpro_post('billing_email')));
                }
                $data['billing_address']['phone'] = esc_sql(eib2bpro_post('billing_phone'));

                $customer = \WC()->api->WC_API_Customers->edit_customer($customer_id, array('customer' => $data));

                if (is_wp_error($customer)) {
                    eib2bpro_error($customer->get_error_message());
                    wp_die();
                }

                eib2bpro_success('OK');

                break;

                // Retrive states by country
            case 'states':

                \EIB2BPRO\Admin::wc_engine();

                $country = eib2bpro_post('country');
                $states = \WC()->countries->get_states($country);
                $return = "";


                eib2bpro_success(woocommerce_form_field(
                    'billing_state',
                    array(
                        'type' => 'state',
                        'country' => $country,
                        'class' => array(''),
                        'label' => '',
                        'placeholder' => esc_html__('Select a state', 'eib2bpro'),
                        'return' => TRUE
                    )
                ));

                break;

                // Retrive user details
            case 'details':

                \EIB2BPRO\Admin::wc_engine();

                $customer = \WC()->api->WC_API_Customers->get_customer($customer_id);

                if (is_wp_error($customer)) {
                    eib2bpro_error($customer->get_error_message());
                    wp_die();
                }

                $customer_id = absint($customer['customer']['id']);

                echo '<h6>' . esc_html__('Last Orders', 'eib2bpro') . '</h6>';

                \EIB2BPRO\Orders\Main::index(
                    array(
                        'post_status' => array_keys(wc_get_order_statuses()),
                        'mode' => 97,
                        'page' => 1,
                        'posts_per_page' => 99999,
                        'meta_query' => array(
                            array(
                                'key' => '_customer_user',
                                'value' => absint($customer_id),
                                'compare' => '=',
                            )
                        )
                    )
                );

                wp_die();

                break;

            case 'order-tabs':
                break;
        }
    }
}
