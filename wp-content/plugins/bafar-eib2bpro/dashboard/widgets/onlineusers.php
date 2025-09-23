<?php

/**
 * WIDGET
 *
 * Online users count
 *
 *
 * @since      1.0.0
 * @author     EN.ER.GY <support@en.er.gy>
 * */

namespace EIB2BPRO\Dashboard\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Onlineusers
{
    public static $name = 'Online Users';
    public static $multiple = false;


    public static function run($args = array(), $settings = array())
    {
        global $wpdb;
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COUNT(DISTINCT session_id)
                FROM {$wpdb->prefix}eib2bpro_requests
                WHERE week = %d AND date >= %s AND date <= %s",
                eib2bpro_strtotime('now - 5 minute', 'W'),
                eib2bpro_strtotime('now - 5 minute', 'Y-m-d H:i:s'),
                current_time('mysql')
            )
        );

        $max = intval(eib2bpro_option("visitors_max", 0));
        $min = intval(eib2bpro_option("visitors_min", 0));

        if ($result > $max) {
            eib2bpro_option("visitors_max", $result, 'set');
        }

        if ($result < $min) {
            eib2bpro_option("visitors_min", $result, 'set');
        }

        if (eib2bpro_is_ajax() or isset($args['ajax'])) {
            return intval($result);
        } else {
            echo eib2bpro_view('dashboard', 1, 'widgets.online-users', array('args' => $args, 'result' => $result, 'min' => $min, 'max' => $max));
        }
    }

    /**
     * Widget's settings
     *
     * @param array $args
     * @return array
     * @since  1.0.0
     */

    public static function settings($args)
    {
        return array();
    }
}
