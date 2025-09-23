<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;
class Core
{
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'core',
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
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Core\\' . sanitize_key($section);
        $class::run();
    }

    public static function scripts()
    {
        wp_enqueue_media();
        wp_enqueue_style("eib2bpro-settings", EIB2BPRO_PUBLIC . "settings/public/settings.css", null, EIB2BPRO_VERSION);
        wp_enqueue_script("eib2bpro-note", EIB2BPRO_PUBLIC . "core/public/js/note.js", null, EIB2BPRO_VERSION);
        wp_enqueue_script("eib2bpro-todo", EIB2BPRO_PUBLIC . "core/public/js/todo.js", null, EIB2BPRO_VERSION);
    }
}
