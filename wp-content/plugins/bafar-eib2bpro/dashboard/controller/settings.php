<?php

namespace EIB2BPRO\Dashboard;

defined('ABSPATH') || exit;

class Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-dashboard-fill',
            'title' => esc_html__('Dashboard', 'eib2bpro'),
            'description' => 'EnergyInc',
            'save_button' => 'hidden',
            'buttons' => array()
        );

        $settings['pages']['general'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];
        $settings['pages']['general']['options'][] = [
            'id' => 'refresh',
            'type' => 'input',
            'title' => esc_html__('Refresh time (seconds)', 'eib2bpro'),
            'default' => '60',
            'description' => esc_html__('Enter the number of seconds to refresh the widgets and notifications. If you enter a value less than 10 seconds, auto-refresh will be disabled.', 'eib2bpro'),
            'class' => 'w-25',
            'style' => '',
            'col' => 12
        ];


        $settings['pages']['about'] = ['title' => esc_html__('About', 'eib2bpro'), 'save' => 0, 'function' => '\EIB2BPRO\Settings\App::about'];
        $settings['pages']['about']['content'] = '';

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }
}
