<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Rules
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'rules',
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
        $class = '\EIB2BPRO\Rules\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        // nothing
    }
}
