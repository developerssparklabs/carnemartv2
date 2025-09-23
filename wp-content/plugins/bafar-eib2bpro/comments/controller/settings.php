<?php

namespace EIB2BPRO\Comments;

defined('ABSPATH') || exit;

/** 
 * Settings
 */

class Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-chat-2-fill',
            'title' => esc_html__('Comments', 'eib2bpro'),
            'description' => 'EnergyInc',
            'save_button' => '',
            'buttons' => array()
        );

        $settings['pages']['general'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];
        $settings['pages']['general']['options'][] = [
            'id' => 'comments-mode',
            'type' => 'big_select',
            'title' => esc_html__('App Mode', 'eib2bpro'),
            'opt' => [
                '1' => [
                    'title' => esc_html__('Standard', 'eib2bpro'),
                    'description' => 'Energy',
                ],

                '99' => [
                    'title' => esc_html__('Wordpress Native', 'eib2bpro'),
                    'description' => 'Wordpress',
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
