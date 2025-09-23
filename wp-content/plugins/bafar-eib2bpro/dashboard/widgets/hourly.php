<?php

/**
 * WIDGET
 *
 * Hourly/Daily/Monthly visitors count
 *
 *
 * @since      1.0.0
 * @package    EnergyPlus
 * @subpackage EnergyPlus/framework/libs/widgets
 * @author     EN.ER.GY <support@en.er.gy>
 * */

namespace EIB2BPRO\Dashboard\Widgets;


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class Hourly
{
    public static $name = 'Hourly';
    public static $multiple = true;


    public static function run($args = array(), $settings = array())
    {
        global $wpdb;

        $settings = wp_parse_args($settings, ['live' => '0']);

        // disable ajax if it is not live
        if ('0' ===  $settings['live'] && eib2bpro_is_ajax() && -2 !== intval(eib2bpro_post('c', 0))) {
            return;
        }

        if (isset($args['c']) && 0 < intval($args['c'])) {
            return;
        }

        $time = isset($args['lasttime']) ? $args['lasttime'] : time() - (24 * 60 * 60);
        $time = time() - (24 * 60 * 60);
        $max = 0;
        $range = (isset($settings['range'])) ? $settings['range'] : 'hourly';

        $args['range'] = $range;
        switch ($range) {

            case 'monthly':

                // Online visitors
                $result = [];

                $source = (array)eib2bpro_option('reports-monthly-' . eib2bpro_strtotime('today', 'Y'), array());

                foreach ($source as $source_day => $source_count) {
                    $result[] = array(
                        'step' => intval(eib2bpro_strtotime($source_day . '01', 'm')),
                        'counts' => $source_count
                    );
                }

                // this month
                $this_month_total = 0;
                $this_month = (array)eib2bpro_option('reports-daily-' . eib2bpro_strtotime('today', 'Y-m'), array());

                foreach ($this_month as $source_day => $source_count) {
                    $this_month_total += $source_count;
                }

                // today
                $this_month_total += intval($wpdb->get_var(
                    $wpdb->prepare(
                        "
                            SELECT count(distinct session_id) as counts
                            FROM {$wpdb->prefix}eib2bpro_requests
                            WHERE date >= %s AND month=%d",
                        eib2bpro_strtotime('today'),
                        eib2bpro_strtotime('today', 'm')
                    )
                ));

                $result[] = array(
                    'step' => intval(eib2bpro_strtotime('today', 'm')),
                    'counts' => $this_month_total
                );

                $_result = array_fill(1, 12, '0');
                $labels = array();
                for ($i = 1; $i < 13; ++$i) {
                    $labels[] = "'" . eib2bpro_strtotime(date('Y') . "-$i-01", 'M') . "'";
                }

                break;


            case 'daily':

                // Online visitors
                $result = [];

                $source = (array)eib2bpro_option('reports-daily-' . eib2bpro_strtotime('today', 'Y-m'), array());

                foreach ($source as $source_day => $source_count) {
                    $result[] = array(
                        'step' => intval(eib2bpro_strtotime($source_day, 'd')),
                        'counts' => $source_count
                    );
                }

                $result[] = array(
                    'step' => intval(date('d')),
                    'counts' => $wpdb->get_var(
                        $wpdb->prepare(
                            "
                                SELECT count(distinct session_id) as counts
                                FROM {$wpdb->prefix}eib2bpro_requests
                                WHERE date >= %s AND month=%d",
                            eib2bpro_strtotime('today'),
                            eib2bpro_strtotime('today', 'm')
                        )
                    )
                );


                $_result = array_fill(1, date('t'), '0');
                $labels = range(1, date('t'));

                break;

            default:
                // Online visitors
                $result = $wpdb->get_results(
                    $wpdb->prepare(
                        "
                        SELECT hour(date) AS step, count(distinct session_id) as counts
                        FROM {$wpdb->prefix}eib2bpro_requests
                        WHERE week = %d AND date >= %s GROUP BY hour(date) ORDER BY step ASC",
                        eib2bpro_strtotime('now', 'W'),
                        eib2bpro_strtotime('today')
                    ),
                    ARRAY_A
                );

                $_result = array_fill(0, 23, '0');
                $labels = range(0, 23);

                break;
        }

        foreach ($result as $step) {
            $max = ($step['counts'] < $max) ? $max : $step['counts'];
            $_result[$step['step']] = $step['counts'];
        }
        if (eib2bpro_is_ajax() or isset($args['ajax'])) {
            return eib2bpro_view('dashboard', 1, 'widgets.hourly', array('args' => $args, 'max' => $max, 'labels' => $labels, 'ajax' => true, 'results' => $_result));
        } else {
            echo eib2bpro_view('dashboard', 1, 'widgets.hourly', array('args' => $args, 'max' => $max, 'labels' => $labels, 'results' => $_result));
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
