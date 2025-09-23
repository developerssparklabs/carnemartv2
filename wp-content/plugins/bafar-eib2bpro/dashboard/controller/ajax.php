<?php

/**
 * Ajax functions for Dashboard
 */

namespace EIB2BPRO\Dashboard;

defined('ABSPATH') || exit;

class Ajax
{
    public static function run()
    {
        $do = eib2bpro_post('do');
        $id = sanitize_key(eib2bpro_post('id'));

        switch ($do) {
                /* Adding new widget */
            case 'add-widget':

                $available_widgets = \EIB2BPRO\Dashboard\Main::availableWidgets();

                if (isset($available_widgets[$id])) {
                    $uniq = uniqid();

                    $map = eib2bpro_option('dashboard-widgets', array());

                    if ($available_widgets[$id]['multiple'] === false && array_search($id, array_column($map, 'type')) !== false) {
                        eib2bpro_error(esc_html__('This widget is not allowed multiple instance', 'eib2bpro'));
                    }

                    $map[$uniq]['id'] = $uniq;
                    $map[$uniq]['type'] = sanitize_key($available_widgets[$id]['id']);
                    $map[$uniq]['w'] = $available_widgets[$id]['w'];
                    $map[$uniq]['h'] = $available_widgets[$id]['h'];

                    eib2bpro_option("dashboard-widgets", $map, 'set');
                    eib2bpro_success();
                }

                break;

                /* Deletes widget from dashboard */
            case 'delete-widget':

                $map = eib2bpro_option('dashboard-widgets', array());

                if (isset($map[$id])) {
                    unset($map[$id]);
                    eib2bpro_option("dashboard-widgets", $map, 'set');
                    eib2bpro_success();
                }
                break;

            case 'remap':
                $page = eib2bpro_post('p', '');
                $_widgets = $_POST['widgets'];

                $new = array();
                $map = eib2bpro_option('dashboard-widgets', array());

                $widgets = array();
                $__widgets = array();

                foreach ($_widgets as $_widget) {
                    if (isset($map[$_widget['id']])) {
                        $new[$_widget['id']] = array('type' => sanitize_key($map[$_widget['id']]['type']), 'col' => sanitize_key($_widget['col']), 'row' => sanitize_key($_widget['row']), 'id' => sanitize_key($_widget['id']), 'w' => sanitize_key($_widget['w']), 'h' => sanitize_key($_widget['h']));
                    }
                }
                eib2bpro_option("dashboard-widgets", $new, 'set');
                wp_die();
                break;


            case 'save-widget-settings':
                self::saveWidgetSettings();
                break;

            case 'get-widgets':
                self::getWidgets();
                break;

            case 'set-range':
                $id         = sanitize_key(eib2bpro_post('id', 0));
                $setting_id = sanitize_key(eib2bpro_post('set_id'));
                $value      = sanitize_key(eib2bpro_post('s'));

                $map        = eib2bpro_option('dashboard-widgets', array());

                if (isset($map[$id])) {
                    $all = eib2bpro_option('dashboard-widgets-settings', array());

                    $all[$id][$setting_id] = $value;

                    eib2bpro_option('dashboard-widgets-settings', $all, 'set');
                }
                break;
        }
    }

    public static function getWidgets()
    {
        $output = array();

        $map = eib2bpro_option('dashboard-widgets', array());
        $settings = eib2bpro_option('dashboard-widgets-settings', array());

        foreach ($map as $id => $widget) {
            $class = '\EIB2BPRO\Dashboard\Widgets\\' . sanitize_key($widget['type']);

            if (isset($settings[$id])) {
                $_settings = $settings[$id];
            } else {
                $_settings = array();
            }

            $output[$id]['type'] = sanitize_key($widget['type']);
            $output[$id]['result'] = $class::run(array('id' => $id, 'ajax' => 1, 'counter' => intval(eib2bpro_post('c')), 'lasttime' => intval(eib2bpro_post('t'))), $_settings);
        }

        $output[0] = array('type' => 'system', 'lasttime' => time());

        echo eib2bpro_r(json_encode($output));
        wp_die();
    }

    public static function saveWidgetSettings()
    {
        $id = sanitize_key(eib2bpro_post('widget_id', 0));
        $map = eib2bpro_option('dashboard-widgets', array());

        if (isset($map[$id])) {
            unset($_POST['app']);
            unset($_POST['action']);
            unset($_POST['asnonce']);
            unset($_POST['do']);

            $all = eib2bpro_option('dashboard-widgets-settings', array());

            if (eib2bpro_post('set_id')) {
                $all[$id][sanitize_key(eib2bpro_post('set_id'))] = sanitize_text_field(eib2bpro_post('s'));
            } else {
                $all[$id] = array();
                foreach ($_POST as $k => $v) {
                    $all[$id][sanitize_key($k)] = sanitize_text_field($v);
                }
            }

            if (is_array($all)) {
                eib2bpro_option('dashboard-widgets-settings', $all, 'set');
            }

            if (eib2bpro_post('set_id')) {
                eib2bpro_success();
            } else {
                eib2bpro_success(
                    '',
                    array(
                        'after' => array(
                            'close' => true,
                            'reload_widgets' => true
                        )
                    )
                );
            }
        }
    }
}
