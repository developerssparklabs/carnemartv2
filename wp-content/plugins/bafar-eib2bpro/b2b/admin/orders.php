<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Orders
{
    public static function woocommerce_order_get_formatted_billing_address($address, $raw_address, $order)
    {
        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        foreach ($custom_fields as $custom) {
            $label = get_post_meta($custom->ID, 'eib2bpro_field_label', true);
            $value = get_post_meta($order->get_id(), '_billing_eib2bpro_customfield_' . $custom->ID, true);
            $billing_show_invoice = get_post_meta($custom->ID, 'eib2bpro_field_billing_show_invoice', true);

            if (!empty($value) && 1 === intval($billing_show_invoice)) {
                $address .= '<br>' . esc_html($label) . ': ' . esc_html($value);
            }
        }

        return $address;
    }

    public static function woocommerce_ajax_get_customer_details($data, $customer, $user_id)
    {
        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        foreach ($custom_fields as $custom) {
            $type = get_post_meta($custom->ID, 'eib2bpro_field_type', true);
            $value = get_user_meta($user_id, 'eib2bpro_customfield_' . $custom->ID, true);
            $billing_field_type = get_post_meta($custom->ID, 'eib2bpro_field_billing_type', true);
            if ('file' === $type || ('new' !== $billing_field_type && 'billing_vat' !== $billing_field_type)) {
                continue;
            }
            if (NULL === $value) {
                $value = '';
            }

            $data['billing']['eib2bpro_customfield_' . $custom->ID] = $value;
        }

        return $data;
    }
}
