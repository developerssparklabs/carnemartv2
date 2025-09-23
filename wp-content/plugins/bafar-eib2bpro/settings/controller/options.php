<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class Options extends \EIB2BPRO\Settings
{
    public static $settings = array();

    public static function options($page)
    {
        return eib2bpro_view('settings', 0, 'options.form', array('options' => self::$settings['pages'][$page]['options']));
    }
}
