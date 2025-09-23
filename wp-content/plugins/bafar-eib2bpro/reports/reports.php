<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class Reports
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'reports',
            'mode' => 1
        );

        if ($key) {
            if (!isset($app[$key])) {
                return $default;
            }
            return $app[$key];
        }

        return $app;
    }

    public static function scripts()
    {
        wp_enqueue_script("eib2bpro-reports", EIB2BPRO_PUBLIC . "reports/public/reports.js", array("jquery"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("funnel-graph", EIB2BPRO_PUBLIC . "core/public/3rd/funnel-graph/js/funnel-graph.js");
        wp_enqueue_script("chartjs", EIB2BPRO_PUBLIC . "core/public/3rd/chart.js", array(), EIB2BPRO_VERSION);
    }

    public static function boot()
    {
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Reports\\' . sanitize_key($section);
        $class::run();
    }
}
