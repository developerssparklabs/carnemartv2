<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Dashboard
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'dashboard',
            'mode' => eib2bpro_option('dashboard-mode', 1)
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
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Dashboard\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        wp_enqueue_script("gridster", EIB2BPRO_PUBLIC . "core/public/3rd/gridster.min.js", array("jquery"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("eib2bpro-dashboard-widgets", EIB2BPRO_PUBLIC . "dashboard/public/dashboard-widgets.js", array("jquery"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("gauge", EIB2BPRO_PUBLIC . "core/public/3rd/gauge/gauge.js", array(), EIB2BPRO_VERSION);
        wp_enqueue_script("chartjs", EIB2BPRO_PUBLIC . "core/public/3rd/chart.js", array(), EIB2BPRO_VERSION);
        wp_enqueue_script("funnel-graph", EIB2BPRO_PUBLIC . "core/public/3rd/funnel-graph/js/funnel-graph.js");
    }
}
