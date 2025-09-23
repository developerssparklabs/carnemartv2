<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

/**
 * Settings
 */

class Settings
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'settings',
            'mode' => 0
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
        if (!\EIB2BPRO\Admin::is_admin()) {
            wp_die(esc_html__('Not allowed', 'eib2bpro'));
        }

        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Settings\\' . sanitize_key($section);
        $class::index();
    }

    public static function scripts()
    {
        wp_enqueue_media();
        wp_enqueue_style("bootstrap-iconpicker", EIB2BPRO_PUBLIC . 'core/public/3rd/iconpicker/css/bootstrap-iconpicker.min.css');
        wp_enqueue_script("bootstrap-iconpicker", EIB2BPRO_PUBLIC . "core/public/3rd/iconpicker/js/bootstrap-iconpicker.bundle.min.js", array(), EIB2BPRO_VERSION, true);
        wp_enqueue_script("eib2bpro-settings", EIB2BPRO_PUBLIC . "settings/public/settings.js", array("jquery", 'wp-color-picker'), EIB2BPRO_VERSION, true);
        wp_enqueue_style("wp-color-picker");
    }

    public static function menu()
    {
        $menu = array();

        $menu['general'] = array(
            'title' => esc_html__('Settings', 'eib2bpro')
        );

        $menu['apps'] = array(
            'title' => esc_html__('Apps', 'eib2bpro')
        );

        $menu['sys'] = array(
            'title' => esc_html__('System', 'eib2bpro')
        );

        $menu['general']['menu']['general'] = array(
            'title' => esc_html__('General', 'eib2bpro'),
            'icon' => 'fas fa-building'
        );

        $menu['general']['menu']['theme'] = array(
            'title' => esc_html__('Theme', 'eib2bpro'),
            'icon' => 'fas fa-eye'
        );

        $menu['general']['menu']['menu'] = array(
            'title' => esc_html__('Menu', 'eib2bpro'),
            'icon' => 'fas fa-stream'
        );

        $menu['general']['menu']['reactors'] = array(
            'title' => esc_html__('Reactors', 'eib2bpro'),
            'icon' => 'ri-reactjs-fill'
        );

        // APPS
        $menu['apps']['menu'] = apply_filters('eib2bpro_apps', array());

        // SYSTEM
        $menu['sys']['menu']['docs'] = array(
            'title' => esc_html__('Documentation', 'eib2bpro'),
            'icon' => '',
            'href' => 'https://en.er.gy/docs/b2b'
        );

        $menu['sys']['menu']['support'] = array(
            'title' => esc_html__('Support', 'eib2bpro'),
            'icon' => '',
            'href' => 'mailto:support@en.er.gy'
        );

        return $menu;
    }
}
