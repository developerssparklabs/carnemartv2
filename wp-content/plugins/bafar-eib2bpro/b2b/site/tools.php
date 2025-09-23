<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Tools
{

    public static function shortcode_content($atts = [], $content = null)
    {
        $params = shortcode_atts(array(
            'show' => 'b2b'
        ), $atts);

        if (empty($params['show'])) {
            return '';
        }

        $allowed = wp_parse_list($params['show']);

        if (in_array(Main::user('user_type'), $allowed)) {
            return self::shortcode_content_render($content);
        } elseif (in_array(Main::user('group'), $allowed)) {
            return self::shortcode_content_render($content);
        } else {
            if (is_user_logged_in()) {
                $username = wp_get_current_user()->user_login;
                if (in_array($username, $allowed)) {
                    return self::shortcode_content_render($content);
                }
            }
        }

        return '';
    }

    public static function shortcode_content_render($content = '')
    {

        $check = substr($content, 1, -1);

        if (shortcode_exists($check) || shortcode_exists(explode(' ', $check)[0])) {
            return do_shortcode($content);
        }
        return $content;
    }
}
