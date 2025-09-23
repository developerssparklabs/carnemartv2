<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Payment
{

    public static function methods($gateways)
    {

        $enabled = [];
        $group = Main::user('group');
        $setting_post = Shipping::get_settings($group);
        foreach ($gateways as $gateway_id => $gateway) {
            if (!empty($setting_post)) {

                $status = get_post_meta($setting_post->ID, 'eib2bpro_payment_method_' . $gateway_id, true);

                if (is_user_logged_in()) {
                    if ('custom' === get_user_meta(Main::user('id'), 'eib2bpro_payment_shipping', true)) {
                        $status = get_user_meta(Main::user('id'), 'eib2bpro_payment_method_' . $gateway_id, true);
                    }
                }

                if (1 === intval($status) || '' === $status) {
                    $enabled[$gateway_id] = $gateway;
                }
            } else {
                $enabled[$gateway_id] = $gateway;
            }
        }
        return $enabled;
    }
}
