<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

/**
 * Customers
 */

class Customers
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'customers',
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
        $class = '\EIB2BPRO\Customers\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        wp_enqueue_script("eib2bpro-customers", EIB2BPRO_PUBLIC . "customers/public/customers.js", array("jquery"), EIB2BPRO_VERSION, true);
    }
}
