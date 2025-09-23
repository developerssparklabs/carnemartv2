<?php

/**
 * WIDGET
 *
 * Funnel Chart
 *
 *
 * @since      1.0.0
 * @author     EN.ER.GY <support@en.er.gy>
 * */

namespace EIB2BPRO\Dashboard\Widgets;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


class funnel
{
    public static $name = 'Funnel Chart';
    public static $multiple = false;

    public static function run($args = array(), $settings = array())
    {
        $settings = wp_parse_args($settings, ['live' => '0']);

        // disable ajax if it is not live
        if ('0' ===  $settings['live'] && (eib2bpro_is_ajax() or isset($args['ajax']))) {
            return;
        }

        $data['results'] = \EIB2BPRO\Reports\Main::data(array('range' => 'daily'));

        $result_key = eib2bpro_strtotime('now', 'Ymd');

        $funnel_order = intval($data['results'][$result_key]['orders_count']);
        $funnel_visitors = intval($data['results'][$result_key]['visitors']);
        $funnel_product_pages = intval($data['results'][$result_key]['product_pages']);
        $funnel_carts = intval($data['results'][$result_key]['carts']);
        $funnel_checkout = intval($data['results'][$result_key]['checkout']);

        if (0 === $funnel_visitors) {
            $funnel_visitors = '0.0001'; // Prevent graph error
        }

        $data['funnel'] = array($funnel_visitors, $funnel_product_pages, $funnel_carts, $funnel_checkout, $funnel_order);

        if (eib2bpro_is_ajax() or isset($args['ajax'])) {
            return eib2bpro_view('dashboard', 1, 'widgets.funnel', array('args' => $args, 'data' => $data));
        } else {
            echo eib2bpro_view('dashboard', 1, 'widgets.funnel', array('args' => $args, 'data' => $data));
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
