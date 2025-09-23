<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Products
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'products',
            'mode' => eib2bpro_option('products-mode', 1)
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
        $class = '\EIB2BPRO\Products\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        wp_enqueue_script("eib2bpro-products", EIB2BPRO_PUBLIC . "products/public/products.js", array("jquery"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("nested-sortable", EIB2BPRO_PUBLIC . "core/public/3rd/nested-sortable.js", array("jquery"), EIB2BPRO_VERSION, true);
    }
}
