<?php

/**
 * WIDGET
 *
 * Daily views count of products/categories
 *
 *
 * @since      1.0.0
 * @author     EN.ER.GY <support@en.er.gy>
 * */

namespace EIB2BPRO\Dashboard\Widgets;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Productviews
{
    public static $name = 'Product Views';
    public static $multiple = false;

    public static function run($args = array(), $settings = array())
    {
        global $wpdb;

        $settings = wp_parse_args($settings, ['live' => '0']);

        // disable ajax if it is not live
        if ('0' ===  $settings['live'] && (eib2bpro_is_ajax() or isset($args['ajax']))) {
            return;
        }

        $time = eib2bpro_strtotime('today', 'Y-m-d 00:00:00');

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "
      SELECT id, type, count(*) AS cnt
      FROM {$wpdb->prefix}eib2bpro_requests
      WHERE type IN (1,2) AND week = %d AND date > %s
      GROUP BY id
      ORDER BY cnt DESC, id DESC",
                eib2bpro_strtotime('now', 'W'),
                $time
            ),
            ARRAY_A
        );

        if (eib2bpro_is_ajax() or isset($args['ajax'])) {
            return eib2bpro_view('dashboard', 1, 'widgets.productviews', array('args' => $args, 'result' => $result));
        } else {
            echo eib2bpro_view('dashboard', 1, 'widgets.productviews', array('args' => $args, 'result' => $result));
        }
    }

    /**
     * Widget's settings
     *
     * @param array $args
     * @return array
     * @since  1.0.0
     */

    public static function settings($settings)
    {
        if (empty($settings)) {
            $settings['live'] = '0';
        }

        return array(
            'type' => 'options',
            'info' => array(
                'type' => 'checkbox',
                'title' => esc_html__('Live', 'eib2bpro'),
                'values' => array(
                    'live' => array('title' => '', 'id' => 'live', 'selected' => (isset($settings['live']) && '1' === $settings['live']) ? '1' : '0'),
                )
            )
        );
    }
}
