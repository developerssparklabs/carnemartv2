<?php

namespace EIB2BPRO\B2b;

defined('ABSPATH') || exit;

class Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'eib2bpro-icon-b2b-fill',
            'title' => esc_html__('B2B Pro', 'eib2bpro'),
            'description' => 'EnergyInc',
            'save_button' => 'hidden',
            'buttons' => array()
        );

        $settings['pages']['general'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options', 'save' => 0];
        $settings['pages']['general']['options'][] = [
            'id' => 'b2b',
            'type' => 'func',
            'title' => '',
            'func' => '\EIB2BPRO\B2b\Settings::goto',
            'default' => false,
            'description' => '',
            'class' => 'm-0 p-0',
            'style' => '',
            'col' => 12
        ];


        $settings['pages']['about'] = ['title' => esc_html__('About', 'eib2bpro'), 'save' => 0, 'function' => '\EIB2BPRO\Settings\App::about'];
        $settings['pages']['about']['content'] = '';

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }

    public static function goto()
    {
?>
        <div class="eib2bpro-settings-about">
            <div class="eib2bpro-about-icon">
                <i class="ri-external-link-fill text-dark"></i>
            </div>
            <br>
            <div class="text-dark eib2bpro-font-14 font-weight-bold"><?php esc_html_e('Please go to B2B Pro > Settings page', 'eib2bpro'); ?></div>
            <br>
            <br>
            <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'settings']) ?>" class="btn btn-dark font-weight-normal"><?php esc_html_e('Go to settings >', 'eib2bpro'); ?></a>
        </div>
<?php
    }
}
