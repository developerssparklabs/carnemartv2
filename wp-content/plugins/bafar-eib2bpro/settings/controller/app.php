<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class App extends \EIB2BPRO\Settings
{

    public static function index()
    {
        $app = sanitize_key(eib2bpro_get('id'));
        $app_list = apply_filters('eib2bpro_apps', array());
        if (isset($app_list[$app]['settings'])) {
            $settings = $app_list[$app]['settings'];
            try {
                $settings();
            } catch (\Exception $e) {
            }
        } else {
            $class = '\EIB2BPRO\\' . $app . "\Settings";
            $settings = '\EIB2BPRO\\' . $app . "\Settings::settings";
            if (is_callable($settings)) {
                $settings();
            }
        }

        echo eib2bpro_view('settings', 0, 'main', array(
            'settings' => \EIB2BPRO\Settings\Options::$settings
        ));
    }

    public static function about()
    {
        echo eib2bpro_view('settings', 0, 'about');
    }
}
