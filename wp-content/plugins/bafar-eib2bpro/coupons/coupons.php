<?php

namespace EIB2BPRO;

/**
 * Coupons
 */

defined('ABSPATH') || exit;

class Coupons
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'coupons',
            'mode' => 1
        );

        if ($key) {
            if (!isset($app[$key])) {
                return $default;
            }
            return $app[$key];
        }

        return $app;
    }

    public static function boot()
    {
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Coupons\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        wp_enqueue_script("eib2bpro-coupons", EIB2BPRO_PUBLIC . "coupons/public/coupons.js", array("jquery"), EIB2BPRO_VERSION, true);
    }
}
