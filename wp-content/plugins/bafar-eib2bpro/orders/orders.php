<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Orders
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'orders',
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
        self::route();
    }

    public static function route()
    {
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Orders\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        // there is no script for this app
    }
}
