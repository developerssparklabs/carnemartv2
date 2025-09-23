<?php

namespace EIB2BPRO;

defined('ABSPATH') || exit;

/**
 * Comments
 */

class Comments
{
    /**
     * App
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    public static function app($key = false, $default = null)
    {
        $app = array(
            'name' => 'comments',
            'mode' => eib2bpro_option('comments-mode', 1)
        );

        if ($key) {
            if (!isset($app[$key])) {
                return $default;
            }
            return $app[$key];
        }

        return $app;
    }

    /**
     * Boot
     *
     * @return void
     */
    public static function boot()
    {
        $section = eib2bpro_get('section', 'main');
        $class = '\EIB2BPRO\Comments\\' . sanitize_key($section);
        $class::run();
    }

    /**
     * Scripts
     *
     * @return void
     */
    public static function scripts()
    {
        wp_enqueue_script("eib2bpro-comments", EIB2BPRO_PUBLIC . "comments/public/comments.js", array("jquery"), EIB2BPRO_VERSION, true);

        $i18n = array(
            'approve' => esc_attr__('Approve', 'eib2bpro'),
            'approved' => esc_attr__('APPROVED', 'eib2bpro'),
            'unapprove' => esc_attr__('Unapprove', 'eib2bpro'),
            'unapproved' => esc_attr__('UNAPPROVED', 'eib2bpro')
        );

        wp_localize_script('eib2bpro-comments', 'eiComments', $i18n);
    }
}
