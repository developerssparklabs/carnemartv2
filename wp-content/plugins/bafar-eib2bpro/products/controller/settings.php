<?php

namespace EIB2BPRO\Products;

defined('ABSPATH') || exit;

class Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-stack-fill',
            'title' => esc_html__('Products', 'eib2bpro'),
            'description' => 'EnergyInc',
            'save_button' => 'hidden',
            'buttons' => array()
        );

        $settings['pages']['general'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];
        $settings['pages']['general']['options'][] = [
            'id' => 'products-mode',
            'type' => 'big_select',
            'title' => esc_html__('App Mode', 'eib2bpro'),
            'opt' => [
                '1' => [
                    'title' => esc_html__('Standard', 'eib2bpro'),
                    'description' => 'Energy',
                    'conditions' => ['show' => '.rel-eib2bpro-0']
                ],
                '99' => [
                    'title' => esc_html__('WooCommerce Native', 'eib2bpro'),
                    'description' => 'WooCommerce',
                    'conditions' => ['hide' => '.rel-eib2bpro-0']
                ],
            ],
            'default' => 1,
            'class' => '',
            'style' => '',
            'col' => 12
        ];


        $settings['pages']['about'] = ['title' => esc_html__('About', 'eib2bpro'), 'save' => 0, 'function' => '\EIB2BPRO\Settings\App::about'];
        $settings['pages']['about']['content'] = '';

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }
}
