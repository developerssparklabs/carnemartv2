<?php

namespace EIB2BPRO\Reactors;

defined('ABSPATH') || exit;

class Main
{
    public static function run()
    {
        switch (eib2bpro_get('action')) {

            case 'settings':
                self::settings();
                break;

            case 'detail':
                self::detail();
                break;

            case 'activate':
                self::activate();
                break;
        }
    }

    public static function is_installed($id)
    {
        if (isset(eib2bpro_option('reactors-list', array())[$id])) {
            return true;
        } else {
            return false;
        }
    }


    public static function detail()
    {
        $id = sanitize_key(eib2bpro_get('id', 0));

        if (!isset(\EIB2BPRO\Reactors::list()[$id])) {
            wp_die(esc_html__('Not allowed', 'eib2bpro'));
        }

        if (!self::is_installed($id)) {
            echo eib2bpro_view('settings', 0, 'reactors.detail', array('reactor' => \EIB2BPRO\Reactors::list($id)));
        } else {
            $class = '\EIB2BPRO\Reactors\\' . $id .  '\\' . $id;
            $class::settings();
        }
    }


    public static function activate()
    {
        $id = sanitize_key(eib2bpro_get('id', 0));

        eib2bpro_ajax_nonce(true, $id);

        if (!isset(\EIB2BPRO\Reactors::list()[$id])) {
            wp_die(esc_html__('Not allowed', 'eib2bpro'));
        }

        $map = eib2bpro_option('reactors-list', array());

        if ('deactivate' === eib2bpro_get('do')) {
            unset($map[$id]);

            $class = "\EIB2BPRO\Reactors\\" . $id .  "\\" . $id;
            $class::deactivate();
        } else {
            $map[$id] = array('id' => $id, 'date' => date('Y-m-d H:i:s'));
        }

        eib2bpro_option('reactors-list', $map, 'set');

        wp_redirect(eib2bpro_admin('reactors', array('action' => 'detail', 'id' => $id, 'later' => 1)));
    }
}
