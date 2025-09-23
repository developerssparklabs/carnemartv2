<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Shipping
{

    public static function methods($rates)
    {

        $enabled = [];
        $group = Main::user('group');
        $setting_post = self::get_settings($group);

        foreach ($rates as $rate_id => $rate) {
            if (!empty($setting_post)) {
                $status = get_post_meta($setting_post->ID, 'eib2bpro_shipping_methods_' . $rate->method_id . '_' . $rate->instance_id, true);

                if (is_user_logged_in()) {
                    if ('custom' === get_user_meta(Main::user('id'), 'eib2bpro_payment_shipping', true)) {
                        $status = get_user_meta(Main::user('id'), 'eib2bpro_shipping_methods_' . $rate->method_id . '_' . $rate->instance_id, true);
                    }
                }

                if (1 === intval($status) || '' === $status) {
                    $enabled[$rate_id] = $rate;
                }
            } else {
                $enabled[$rate_id] = $rate;
            }
        }
        return $enabled;
    }
    public static function get_all()
    {
        $shipping_methods = array();

        $_zones = \WC_Shipping_Zones::get_zones();
        foreach ($_zones as $item) {
            $zone_name = $item['zone_name'];
            foreach ($item['shipping_methods'] as $value) {
                $value->eib2bpro_zone_name = $zone_name;
                $shipping_methods[] =  $value;
            }
        }

        $default_zone = new \WC_Shipping_Zone(0);
        foreach ($default_zone->get_shipping_methods() as $value) {
            $value->eib2bpro_zone_name = esc_html__('Others', 'eib2bpro');
            $shipping_methods[] = $value;
        }

        return $shipping_methods;
    }
    public static function get_settings($group)
    {
        if ('b2c' === $group || 'guest' === $group) {
            $setting_post = get_posts([
                'post_type' => 'eib2bpro_groups',
                'post_status' => 'private',
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => "eib2bpro_{$group}_group_settings",
                        'value' => 'yes'
                    )
                )
            ]);
        } else {
            $setting_post[0] = get_post(intval($group));
        }

        if (!isset($setting_post[0])) {
            $setting_post[0] = false;
        }

        return $setting_post[0];
    }
}
