<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class Reactors extends \EIB2BPRO\Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-space-ship-fill',
            'title' => esc_html__('Reactors', 'eib2bpro'),
            'description' => esc_html__('Add additional features to your panel', 'eib2bpro'),
            'save_button' => 'hidden',
            'buttons' => array()
        );

        $settings['pages']['all'] = ['title' => esc_html__('All', 'eib2bpro'), 'save' => '0', 'function' => '\EIB2BPRO\Settings\Reactors::all'];

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }

    public static function index()
    {
        echo eib2bpro_view(self::app('name'), self::app('mode'), 'main', array(
            'settings' => self::settings()
        ));
    }

    public static function all()
    {

        self::list('all');
    }

    public static function active()
    {
        self::list(0);
    }

    public static function list($status = 'all')
    {
        $available = \EIB2BPRO\Reactors::list();
        $map       = eib2bpro_option('reactors-list', array());

        foreach ($map as $_map) {
            if (isset($available[$_map['id']])) {
                $available[$_map['id']]['active'] = 1;
                $available[$_map['id']]['order'] = $available[$_map['id']]['order'] + 1000;
            }
        }


        array_multisort(array_map(function ($element) {
            return $element['order'];
        }, $available), SORT_DESC, $available);

        echo eib2bpro_view('settings', 0, 'reactors.list', array('items' => $available, 'status' => $status));
    }
}
