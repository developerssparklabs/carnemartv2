<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Reactors
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'reactors',
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
        $class = '\EIB2BPRO\Reactors\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        wp_enqueue_style("eib2bpro-settings", EIB2BPRO_PUBLIC . 'settings/public/settings.css');
    }

    public static function list($id = false)
    {
        $list = array(

            'login' => array(
                'id'          => 'login',
                'title'       => esc_html__('Login Page', 'eib2bpro'),
                'description' => esc_html__('Customize WordPress Login page of your store', 'eib2bpro'),
                'details'     => esc_html__('This reactor adapts your WP Login page to the E+ style. (Important Note: If you are already using an other WP Login Page Customizer plugin, do not activate this reactor!)', 'eib2bpro'),
                'active'      => 0,
                'order'       => 300,
                'badge'       => esc_html__('NEW', 'eib2bpro'),
                'url'         => false
            ),


            'style' => array(
                'id'          => 'style',
                'title'       => esc_html__('Style', 'eib2bpro'),
                'description' => esc_html__('Apply E+ styles to important WP / WC pages', 'eib2bpro'),
                'details'     => esc_html__('This reactor adapts some important WordPress and WooCommerce pages to the E+ style. (Important Note: You need to enable Settings > Full Mode to use this reactor)', 'eib2bpro'),
                'active'      => 0,
                'order'       => 400,
                'badge'       => esc_html__('NEW', 'eib2bpro'),
                'url'         => false
            )

        );

        if ($id) {
            return $list[$id];
        }

        return $list;
    }
}
