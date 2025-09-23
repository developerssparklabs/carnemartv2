<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

class B2B
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'b2b',
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

    public static function boot()
    {
        if (is_admin()) {
            \EIB2BPRO\B2b\Admin\Main::run();
        }
    }

    public static function scripts()
    {
        wp_enqueue_media();
        wp_enqueue_style("eib2bpro-settings", EIB2BPRO_PUBLIC . "settings/public/settings.css", null, EIB2BPRO_VERSION);
        wp_enqueue_script("eib2bpro-settings", EIB2BPRO_PUBLIC . "settings/public/settings.js", array("jquery"), EIB2BPRO_VERSION, true);
        wp_enqueue_script("eib2bpro-charts", EIB2BPRO_PUBLIC . "core/public/js/charts.js", array(), EIB2BPRO_VERSION, true);

        if ('rules' === eib2bpro_get('section')) {
            wp_enqueue_script("eib2bpro-rules", EIB2BPRO_PUBLIC . "rules/public/rules.js", array(), EIB2BPRO_VERSION, true);
            wp_enqueue_style("eib2bpro-rules", EIB2BPRO_PUBLIC . 'rules/public/rules.css', null, EIB2BPRO_VERSION);
        }
        if ('settings' === eib2bpro_get('section')) {
            wp_enqueue_style("wp-color-picker");
            wp_enqueue_script("eib2bpro-footer", EIB2BPRO_PUBLIC . "core/public/js/footer.js", array('wp-color-picker'), EIB2BPRO_VERSION, true);
        }
    }
}
