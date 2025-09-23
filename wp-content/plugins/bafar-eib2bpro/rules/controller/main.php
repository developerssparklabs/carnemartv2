<?php

namespace EIB2BPRO\Rules;

defined('ABSPATH') || exit;

class Main extends \EIB2BPRO\Rules
{
    public static function run()
    {
        // nothing
    }

    public static function list()
    {

        $items = new \WP_Query([
            'post_type' => 'eib2bpro_rules',
            'post_status' => ['publish', 'private'],
            'posts_per_page' => eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10),
            'paged' => (eib2bpro_get('pg') ? eib2bpro_get('pg') : 1),
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);

        // empty item for template
        $items->posts[-1] = new \stdClass();
        $items->posts[-1]->ID = -1;
        $items->posts[-1]->post_status = 'draft';


        echo eib2bpro_view('rules', 0, 'list', ['items' => $items]);
    }

    public static function save()
    {
        $id = intval(eib2bpro_post('id', -1));

        $data = [];
        $data['title'] = eib2bpro_post('title', 'Untitled rule');

        if (-1 === $id) {
            // insert
            $post_id = wp_insert_post([
                'post_title' => wp_strip_all_tags($data['title']),
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'eib2bpro_rules',
                'post_author' => get_current_user_id()
            ]);

            update_post_meta($post_id, 'eib2bpro_position', time() * -1);
        } else {
            // update
            $post = get_post($id);

            if (!is_wp_error($post) && 'eib2bpro_rules' === $post->post_type) {
                $status = get_post_status($id);
                wp_update_post([
                    'ID' => $id,
                    'post_status' => $status,
                    'post_title' => wp_strip_all_tags($data['title']),
                ]);
            }

            $post_id = $post->ID;
        }

        if (0 === intval($post_id)) {
            eib2bpro_error(esc_html__('Error', 'eib2bpro'));
        }

        update_post_meta($post_id, 'eib2bpro_rule_type', eib2bpro_post('type', ''));
        update_post_meta($post_id, 'eib2bpro_rule_title', sanitize_text_field(get_the_title($post_id)));
        update_post_meta($post_id, 'eib2bpro_rule_track', 0);

        // users
        update_post_meta($post_id, 'eib2bpro_rule_users', eib2bpro_post('eib2bpro-rule-users', ''));
        update_post_meta($post_id, 'eib2bpro_rule_users_operator', eib2bpro_post('eib2bpro-rule-users-operator', ''));
        if ('user' === eib2bpro_post('eib2bpro-rule-users', '')) {
            update_post_meta($post_id, 'eib2bpro_rule_users_values', eib2bpro_post('eib2bpro-rule-users-values2', ''));
        } else {
            update_post_meta($post_id, 'eib2bpro_rule_users_values', implode(',', (array)wc_clean($_POST['eib2bpro-rule-users-values'])));
        }

        // chanege price
        update_post_meta($post_id, 'eib2bpro_rule_change_price_type', eib2bpro_post('eib2bpro-rule-change-price-type', ''));
        update_post_meta($post_id, 'eib2bpro_rule_change_price_values', wc_format_decimal(eib2bpro_post('eib2bpro-rule-change-price-values', '')));


        // step
        update_post_meta($post_id, 'eib2bpro_rule_step_values', intval(eib2bpro_post('eib2bpro-rule-step-values', '')));
        update_post_meta($post_id, 'eib2bpro_rule_step_min', eib2bpro_post('eib2bpro-rule-step-min', ''));
        update_post_meta($post_id, 'eib2bpro_rule_step_max', eib2bpro_post('eib2bpro-rule-step-max', ''));


        // minmax
        update_post_meta($post_id, 'eib2bpro_rule_minmax_type', eib2bpro_post('eib2bpro-rule-minmax-type', ''));
        update_post_meta($post_id, 'eib2bpro_rule_minmax_values', eib2bpro_post('eib2bpro-rule-minmax-values', ''));

        // payment method - discounts
        update_post_meta($post_id, 'eib2bpro_rule_payment_discount_operator', eib2bpro_post('eib2bpro-rule-payment-discount-operator', ''));
        update_post_meta($post_id, 'eib2bpro_rule_payment_discount_type', eib2bpro_post('eib2bpro-rule-payment-discount-type', ''));
        update_post_meta($post_id, 'eib2bpro_rule_payment_discount_amount', wc_format_decimal(eib2bpro_post('eib2bpro-rule-payment-discount-amount', '')));
        update_post_meta($post_id, 'eib2bpro_rule_payment_discount_values', implode(',', (array)wc_clean($_POST['eib2bpro-rule-payment-discount-values'])));

        // payment method - minmax
        update_post_meta($post_id, 'eib2bpro_rule_payment_minmax_operator', eib2bpro_post('eib2bpro-rule-payment-minmax-operator', ''));
        update_post_meta($post_id, 'eib2bpro_rule_payment_minmax_values', implode(',', (array)wc_clean($_POST['eib2bpro-rule-payment-minmax-values'])));
        update_post_meta($post_id, 'eib2bpro_rule_payment_minmax_min', wc_format_decimal(eib2bpro_post('eib2bpro-rule-payment-minmax-min', '')));
        update_post_meta($post_id, 'eib2bpro_rule_payment_minmax_max', wc_format_decimal(eib2bpro_post('eib2bpro-rule-payment-minmax-max', '')));

        // add fee
        update_post_meta($post_id, 'eib2bpro_rule_add_fee_type', eib2bpro_post('eib2bpro-rule-add-fee-type', ''));
        update_post_meta($post_id, 'eib2bpro_rule_add_fee_values', wc_format_decimal(eib2bpro_post('eib2bpro-rule-add-fee-values', '')));
        update_post_meta($post_id, 'eib2bpro_rule_add_fee_name', eib2bpro_post('eib2bpro-rule-add-fee-name', ''));

        // add quote button
        update_post_meta($post_id, 'eib2bpro_rule_add_quote_button_remove_atc', intval(eib2bpro_post('eib2bpro_rule_add_quote_button_remove_atc', 0)));

        // change price format
        update_post_meta($post_id, 'eib2bpro_rule_change_price_html', wp_kses_post($_POST['eib2bpro-rule-change-price-html']));

        // tax exemption (user)
        update_post_meta($post_id, 'eib2bpro_rule_tax_exemption_country', implode(',', (array)wc_clean($_POST['eib2bpro-rule-tax-exemption-country'])));
        if (!isset($_POST['eib2bpro-rule-tax-exemption-country'])) {
            update_post_meta($post_id, 'eib2bpro_rule_tax_exemption_country', '0');
        }
        update_post_meta($post_id, 'eib2bpro_rule_tax_exemption_vies_validation', intval(eib2bpro_post('eib2bpro_rule_tax_exemption_vies_validation')));

        $i = 0;
        $products = [];
        foreach ($_POST as $index => $value) {
            if (stripos($index, 'eib2bpro-rule-products_') !== false) {
                $key = str_replace('eib2bpro-rule-products_', '', $index);
                if ('product' === wc_clean($_POST['eib2bpro-rule-products_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-products-values_' . $key]))) {
                        $products[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-products_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-products-operator_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-products-values_' . $key])];
                    }
                } elseif ('category' === wc_clean($_POST['eib2bpro-rule-products_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-products-values2_' . $key]))) {
                        $products[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-products_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-products-operator_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-products-values2_' . $key])];
                    }
                }
                ++$i;
            }
        }

        update_post_meta($post_id, 'eib2bpro_rule_products', $products);

        $i = 0;
        $conditions = [];
        foreach ($_POST as $index => $value) {
            if (stripos($index, 'eib2bpro-rule-conditions_') !== false) {
                $key = str_replace('eib2bpro-rule-conditions_', '', $index);

                if ('cart_total_value' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator2_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values_' . $key])];
                    }
                }

                if ('cart_total_qty' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator2_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values_' . $key])];
                    }
                }

                if ('cart_product' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values2_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values2_' . $key])];
                    }
                }

                if ('cart_product_qty' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values4_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator2_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values2_' . $key]), 'values2' => wc_clean($_POST['eib2bpro-rule-conditions-values4_' . $key])];
                    }
                }

                if ('cart_category' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values3_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values3_' . $key])];
                    }
                }

                if ('date' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values5_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]),  'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator3_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values5_' . $key])];
                    }
                }

                if ('time' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values6_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator3_' . $key]),  'values' => wc_clean($_POST['eib2bpro-rule-conditions-values6_' . $key])];
                    }
                }

                if ('date_time' === wc_clean($_POST['eib2bpro-rule-conditions_' . $key])) {
                    if ('' !== trim(wc_clean($_POST['eib2bpro-rule-conditions-values7_' . $key]))) {
                        $conditions[$i] = ['key' => wc_clean($_POST['eib2bpro-rule-conditions_' . $key]), 'operator' => wc_clean($_POST['eib2bpro-rule-conditions-operator3_' . $key]), 'values' => wc_clean($_POST['eib2bpro-rule-conditions-values7_' . $key])];
                    }
                }
                ++$i;
            }
        }
        update_post_meta($post_id, 'eib2bpro_rule_conditions', $conditions);

        self::build_map();

        if (-1 === $id) {
            eib2bpro_success('', ['after' => ['val' => ['input' => '.btnA input[value=-1]', 'val' => $post_id], 'refresh_window' => true]]);
        }
        eib2bpro_success('', []);
    }

    public static function delete()
    {
        $id = eib2bpro_post('id', -9999);
        if (current_user_can('delete_post', $id)) {
            wp_delete_post($id, true);
        }
        self::build_map();
        eib2bpro_success('', ['after' => ['refresh_window' => true]]);
    }

    public static function build_map()
    {

        $map = [];
        $users = [];

        $rules = new \WP_Query([
            'post_type' => 'eib2bpro_rules',
            'post_status' => ['publish'],
            'posts_per_page' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);

        if ($rules->have_posts()) {
            foreach ($rules->posts as $rule) {
                $meta = get_post_meta($rule->ID);
                foreach ($meta as $meta_key => $meta_value) {
                    if (stripos($meta_key, 'eib2bpro_rule') !== false) {
                        $map['rules'][get_post_meta($rule->ID, 'eib2bpro_rule_type', true)][$rule->ID][str_replace('eib2bpro_rule_', '', $meta_key)] = maybe_unserialize($meta_value[0]);
                    }
                }

                $map['rules'][get_post_meta($rule->ID, 'eib2bpro_rule_type', true)][$rule->ID]['id'] = $rule->ID;

                $map['users'][get_post_meta($rule->ID, 'eib2bpro_rule_type', true)][$rule->ID]['users'] =  get_post_meta($rule->ID, 'eib2bpro_rule_users', true);
                $map['users'][get_post_meta($rule->ID, 'eib2bpro_rule_type', true)][$rule->ID]['operator'] =  get_post_meta($rule->ID, 'eib2bpro_rule_users_operator', true);
                $map['users'][get_post_meta($rule->ID, 'eib2bpro_rule_type', true)][$rule->ID]['value'] =  wp_parse_list(maybe_unserialize(get_post_meta($rule->ID, 'eib2bpro_rule_users_values', true)));
            }
        }
        eib2bpro_option('rules_map', $map, 'set');

        \EIB2BPRO\B2b\Admin\Main::clear_cache();
    }
    public static function search()
    {
        $for = sanitize_text_field(eib2bpro_post('for'));
        $q = sanitize_text_field(eib2bpro_post('query'));

        switch ($for) {
            case "category":
                $args = array(
                    'taxonomy'   => "product_cat",
                    'search' => $q
                );
                $product_categories = get_terms($args);
                foreach ($product_categories as $item) {
                    $json[] = ['id' => $item->term_id, 'name' => $item->name];
                }

                echo eib2bpro_r(json_encode($json));
                die;
                break;
            case "product":
                \EIB2BPRO\B2b\Admin\Product::search();
                break;
            case "user":
                \EIB2BPRO\B2b\Admin\User::search();
                break;
            case "group":
                $json = [];
                $groups = new \WP_Query([
                    'post_type' => 'eib2bpro_groups',
                    'post_status' => ['publish', 'private'],
                    'numberposts' => -1,
                    's' => $q
                ]);

                foreach ($groups->posts as $group) {
                    $json[] = ['id' => $group->ID, 'name' => esc_html(get_the_title($group->ID))];
                }

                echo eib2bpro_r(json_encode($json));
                die;
                break;
        }
    }

    public static function rest_api_metadata()
    {
        $meta = array(
            'eib2bpro_rule_type',
            'eib2bpro_rule_users',
            'eib2bpro_rule_users_operator',
            'eib2bpro_rule_users_values',
            'eib2bpro_rule_change_price_type',
            'eib2bpro_rule_change_price_values',
            'eib2bpro_rule_step_values',
            'eib2bpro_rule_minmax_type',
            'eib2bpro_rule_minmax_values',
            'eib2bpro_rule_payment_discount_operator',
            'eib2bpro_rule_payment_discount_type',
            'eib2bpro_rule_payment_discount_amount',
            'eib2bpro_rule_payment_discount_values',
            'eib2bpro_rule_payment_minmax_operator',
            'eib2bpro_rule_payment_minmax_values',
            'eib2bpro_rule_payment_minmax_min',
            'eib2bpro_rule_payment_minmax_max',
            'eib2bpro_rule_add_fee_type',
            'eib2bpro_rule_add_fee_values',
            'eib2bpro_rule_add_fee_name',
            'eib2bpro_rule_tax_exemption_country',
            'eib2bpro_rule_tax_exemption_vies_validation',
            'eib2bpro_rule_products',
            'eib2bpro_rule_conditions',
            'eib2bpro_position'
        );

        foreach ($meta as $item) {
            register_meta('post', $item, [
                'object_subtype' => 'eib2bpro_rules',
                'show_in_rest' => true
            ]);
        }
    }
}
