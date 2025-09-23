<?php

namespace EIB2BPRO\Dashboard;

defined('ABSPATH') || exit;

class Main
{

    public static function boot()
    {
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Dashboard\\' . sanitize_key($section);
        $class::run();
    }

    /**
     * Starts everything
     *
     * @return void
     */

    public static function run()
    {

        self::route();
    }

    /**
     * Router for sub pages
     *
     * @return void
     */

    private static function route()
    {
        $mode = eib2bpro_option('dashboard-mode', 'default', 'get', true);

        switch (eib2bpro_get('action', $mode)) {

            case 'widget-settings':
                self::widgetSettings();
                break;

            case 'widget_list':
                self::widgetList();
                break;

            case 'wc-admin':
                eib2bpro_option('dashboard-mode', 'wc-admin', 'set', true);
                echo eib2bpro_view('dashboard', 1, 'dashboard-charts', array());
                break;

            case 'default':
            default:
                if (eib2bpro_get('action')) {
                    eib2bpro_option('dashboard-mode', 'default', 'set', true);
                }
                self::index();
                break;
        }
    }


    public static function index()
    {
        $mode = eib2bpro_option('dashboard-mode', '1');

        switch ($mode) {
            case 1:
                self::dashboardWidgets();
                break;
        }
    }

    public static function dashboardWidgets()
    {

        $map = eib2bpro_option('dashboard-widgets', array());
        $settings = eib2bpro_option('dashboard-widgets-settings', array());

        echo eib2bpro_view('dashboard', 1, 'dashboard', ['map' => $map, 'settings' => $settings]);
    }

    /**
     * Widget listsadded to dashboard
     *
     * @return void
     */

    public static function widgetList()
    {
        $available_widgets = self::availableWidgets();

        $installed = array();
        $others = array();

        $map = eib2bpro_option('dashboard-widgets', array());

        foreach ($map as $_map) {
            if (isset($available_widgets[$_map['type']])) {
                $installed[] = array(
                    'id' => $_map['id'],
                    'type' => $_map['type'],
                    'title' => $available_widgets[$_map['type']]['title'],
                    'description' => $available_widgets[$_map['type']]['description'],
                    'multiple' => $available_widgets[$_map['type']]['multiple'],
                );
            }
        }

        echo eib2bpro_view('dashboard', 1, 'widget-list', array('installed' => $installed, 'all' => $available_widgets));
    }

    public static function widgetSettings()
    {
        $id = sanitize_key(eib2bpro_get('id', 0));
        if (0 === $id) {
            wp_die(esc_html__('Error:', 'eib2bpro') . '129');
        }

        $settings = eib2bpro_option('dashboard-widgets-settings', array());
        $map = eib2bpro_option('dashboard-widgets', array());
        if (!isset($map[$id])) {
            wp_die(esc_html__('Error:', 'eib2bpro') . '130');
        }

        if (isset($settings[$id])) {
            $__settings = $_settings = $settings[$id];
        } else {
            $__settings = $_settings = array();
        }

        $widgetclass = '\EIB2BPRO\Dashboard\Widgets\\' . sanitize_key($map[$id]['type']);
        $settings = $widgetclass::settings($_settings);

        echo eib2bpro_view('dashboard', 1, 'widget-settings', array('id' => $id, 'widget' => $map[$id], 'settings' => $settings));
    }

    /**
     * List of available widgets
     *
     * @since  1.0.0
     */

    public static function availableWidgets()
    {
        return array(
            'overview' => array('id' => 'overview', 'title' => esc_html__('Overview', 'eib2bpro'), 'image' => '', 'description' => esc_html__('Display info about sales, customers etc.', 'eib2bpro'), 'multiple' => true, 'settings' => true, 'w' => 20, 'h' => 3, 'minw' => 6, 'maxw' => 20, 'minh' => 3, 'maxh' => 3),
            'onlineusers' => array('id' => 'onlineusers', 'title' => esc_html__('Online Users', 'eib2bpro'), 'image' => '', 'description' => esc_html__('Count of online users on your store', 'eib2bpro'), 'multiple' => false, 'settings' => false, 'w' => 4, 'h' => 8, 'minw' => 4, 'maxw' => 8, 'minh' => 7, 'maxh' => 10),
            'hourly' => array('id' => 'hourly', 'title' => esc_html__('Visitors', 'eib2bpro'), 'image' => '', 'description' => esc_html__('Hourly/Daily/Monthly visitors count', 'eib2bpro'), 'multiple' => false, 'settings' => true, 'w' => 16, 'h' => 8, 'minw' => 10, 'maxw' => 20, 'minh' => 8, 'maxh' => 8),
            'productviews' => array('id' => 'productviews', 'title' => esc_html__('Product Views', 'eib2bpro'), 'image' => '', 'description' => esc_html__('Which products viewed today?', 'eib2bpro'), 'multiple' => false, 'settings' => true, 'w' => 6, 'h' => 12, 'minw' => 4, 'maxw' => 20, 'minh' => 6, 'maxh' => 40),
            'lastactivity' => array('id' => 'lastactivity', 'title' => esc_html__('Last Activities', 'eib2bpro'), 'image' => '', 'description' => esc_html__('Live info about your visitors activities', 'eib2bpro'), 'settings' => false, 'multiple' => false, 'w' => 14, 'h' => 12, 'minw' => 10, 'maxw' => 20, 'minh' => 6, 'maxh' => 30),
            'funnel' => array('id' => 'funnel', 'title' => esc_html__('Funnel Graph', 'eib2bpro'), 'image' => '', 'description' => esc_html__('Graph of conversions on your store', 'eib2bpro'), 'multiple' => false, 'settings' => true, 'w' => 20, 'h' => 8, 'minw' => 20, 'maxw' => 20, 'minh' => 8, 'maxh' => 8)
        );
    }
}
