<?php

namespace EIB2BPRO\Reports;

defined('ABSPATH') || exit;

class Main extends \EIB2BPRO\Reports
{
    public static $store = array();
    public static $zero = '0';

    public static function run()
    {

        if (!eib2bpro_get('report')) {
            $homepage = eib2bpro_option('reports-home', 'overview');
            if ('overview' !== $homepage) {
                wp_redirect(eib2bpro_admin('reports', ['action' => 'woocommerce', 'report' => $homepage]));
            }
        }
        self::route();
    }

    /**
     * Router for sub pages
     *
     * @return null
     */

    private static function route()
    {
        switch (eib2bpro_get('action')) {

            case 'woocommerce':
                echo eib2bpro_view('reports', self::app('mode'), 'woocommerce', array('report' => eib2bpro_get('report', '')));
                break;

            default:
                self::index();
                break;
        }
    }

    public static function index()
    {
        global $wpdb;

        if (eib2bpro_get('graph')) {
            eib2bpro_option('reports-graph', (string)intval(eib2bpro_get('graph', "2")), 'set');
        }

        $data                 = array();
        $range                = eib2bpro_get('range', 'daily');
        $data['results']      = self::data(array('range' => $range));

        switch ($range) {

            case 'yearly':

                $data['quick'][0] = array('title' => esc_html__("This Year's Sales", 'eib2bpro'), 'text' => wc_price($data['results'][eib2bpro_strtotime('now', 'Y')]['total_sales']));
                if (isset($data['results'][eib2bpro_strtotime('last year', 'Y')]['total_sales'])) {
                    $data['quick'][1] = array('title' => esc_html__("Last Year's Sales", 'eib2bpro'), 'text' => wc_price($data['results'][eib2bpro_strtotime('last year', 'Y')]['total_sales']));
                }
                $data['quick'][2] = array('title' => esc_html__("Average Sales", 'eib2bpro'), 'text' => wc_price(end($data['results'])['average_sales']));

                $result_key = eib2bpro_strtotime('now', 'Y');

                break;


            case 'monthly':

                $data['quick'] = array(
                    0 => array('title' => esc_html__("This Month's Sales", 'eib2bpro'), 'text' => wc_price($data['results'][eib2bpro_strtotime('now', 'Ym')]['total_sales'])),
                    1 => array('title' => esc_html__("Last Month's Sales", 'eib2bpro'), 'text' => wc_price(eib2bpro_clean($data['results'][eib2bpro_strtotime('last month', 'Ym')]['total_sales'], 0))),
                    2 => array('title' => esc_html__("Average Sales", 'eib2bpro'), 'text' => wc_price(eib2bpro_clean(end($data['results'])['average_sales'], 0))),
                );

                if (12 === (int)eib2bpro_strtotime('last month', 'm')) {
                    if (isset($data['results'][eib2bpro_strtotime('last month', 'Ym')])) {
                        unset($data['results'][eib2bpro_strtotime('last month', 'Ym')]);
                    }
                }

                $result_key = eib2bpro_strtotime('now', 'Ym');

                break;


            case 'weekly':

                $data['quick'] = array(
                    0 => array('title' => esc_html__("This Week's Sales", 'eib2bpro'), 'text' => wc_price($data['results'][eib2bpro_strtotime('now', 'YW')]['total_sales'])),
                    1 => array('title' => esc_html__("Last Week's Sales", 'eib2bpro'), 'text' => wc_price(eib2bpro_clean($data['results'][eib2bpro_strtotime('last week', 'YW')]['total_sales']))),
                    2 => array('title' => esc_html__("Average Sales", 'eib2bpro'), 'text' => wc_price(eib2bpro_clean(end($data['results'])['average_sales'], 0))),
                );

                $result_key = eib2bpro_strtotime('now', 'YW');

                break;

            case 'daily':

                if (!eib2bpro_get('month')) {
                    $data['quick'][0] = array('title' => esc_html__("Today's Sales", 'eib2bpro'), 'text' => wc_price($data['results'][eib2bpro_strtotime('now', 'Ymd')]['total_sales']));
                    if (isset($data['results'][eib2bpro_strtotime('yesterday', 'Ymd')]['total_sales'])) {
                        $data['quick'][1] = array('title' => esc_html__("Yesterday's Sales", 'eib2bpro'), 'text' => wc_price($data['results'][eib2bpro_strtotime('yesterday', 'Ymd')]['total_sales']));
                    }
                    $data['quick'][2] = array('title' => esc_html__("Average Sales", 'eib2bpro'), 'text' => wc_price(end($data['results'])['average_sales']));
                }

                $result_key = eib2bpro_strtotime('now', 'Ymd');
        }

        $funnel_order         = (isset($data['results'][$result_key]['orders_count'])) ? intval($data['results'][$result_key]['orders_count']) : 0;
        $funnel_visitors      = (isset($data['results'][$result_key]['visitors'])) ? intval($data['results'][$result_key]['visitors']) : 0;
        $funnel_product_pages = (isset($data['results'][$result_key]['product_pages'])) ? intval($data['results'][$result_key]['product_pages']) : 0;
        $funnel_carts         = (isset($data['results'][$result_key]['carts'])) ? intval($data['results'][$result_key]['carts']) : 0;
        $funnel_checkout      = (isset($data['results'][$result_key]['checkout'])) ? intval($data['results'][$result_key]['checkout']) : 0;

        if (0 === $funnel_visitors) {
            $funnel_visitors = '0.0001'; // Prevent graph error
        }

        $data['funnel']       = array($funnel_visitors, $funnel_product_pages, $funnel_carts, $funnel_checkout, $funnel_order);
        echo eib2bpro_view('reports', self::app('mode'), 'overview', array('data' => $data));
    }

    /**
     * Get reports data from eib2bpro_daily table
     *
     * @since  1.0.0
     * @param  array     $args 
     */

    public static function data($args = array())
    {
        global $wpdb;

        $request = new \WP_REST_Request('GET', '/wc-analytics/reports/revenue/stats');

        switch ($args['range']) {

            case 'yearly':

                $type       = 'Y';
                $label      = 'y';
                $day_end    = eib2bpro_strtotime('last day of december', 'Y');
                $goal       = eib2bpro_option('goals-yearly', 0);
                $request->set_param('interval', 'year');

                break;

            case 'monthly':

                $type       = 'M';
                $label      = 'm';
                $start_date = eib2bpro_strtotime('first day of january', 'Y-m-d\T00:00:00');
                $end_date = eib2bpro_strtotime('now', 'Y-m-d\T23:59:59');
                $day_start  = eib2bpro_strtotime('first day of january', 'Ym');
                $day_end    = eib2bpro_strtotime('now', 'Ym');
                $goal       = eib2bpro_option('goals-monthly', 0);
                $request->set_param('interval', 'month');

                $source = (array)eib2bpro_option('reports-monthly-' . eib2bpro_strtotime('yesterday', 'Y'), array());
                foreach ($source as $source_day => $source_count) {
                    $result[$source_day]['visitors'] = $source_count;
                }

                break;

            case 'weekly':

                $type       = 'W';
                $label      = 'W';
                $start_date = eib2bpro_strtotime('first day of january', 'Y-m-d\T00:00:00');
                $end_date = eib2bpro_strtotime('now', 'Y-m-d\T23:59:59');
                $day_start  = date('Y01');
                $day_end    = eib2bpro_strtotime('now', 'YW');
                $goal       = eib2bpro_option('goals-weekly', 0);
                $request->set_param('interval', 'week');

                $source = (array)eib2bpro_option('reports-weekly-' . eib2bpro_strtotime('yesterday', 'Y'), array());
                foreach ($source as $source_day => $source_count) {
                    $result[$source_day]['visitors'] = $source_count;
                }

                break;

            case 'daily':

                $type       = 'D';
                $label      = 'd l';
                $start_date = eib2bpro_strtotime('first day of this month', 'Y-m-d\T00:00:00');
                $end_date = eib2bpro_strtotime('now', 'Y-m-d\T23:59:59');
                $day_start  = eib2bpro_strtotime('first day of this month', 'Ymd');
                $day_end    = eib2bpro_strtotime('now', 'Ymd');
                $goal       = eib2bpro_option('goals-daily', 0);

                if (eib2bpro_get('month')) {
                    $start_date = date("Y-m-01\T00:00:00", strtotime("-" . (int)eib2bpro_get('month') . " month"));
                    $day_start  = date("Ym01", strtotime("-" . (int)eib2bpro_get('month') . " month"));
                    $day_end    = date("Ymt", strtotime("-" . (int)eib2bpro_get('month') . " month"));
                }

                $request->set_param('interval', 'day');

                $source = (array)eib2bpro_option('reports-daily-' . eib2bpro_strtotime('yesterday', 'Y-m'), array());
                foreach ($source as $source_day => $source_count) {
                    $result[eib2bpro_strtotime($source_day, 'Ymd')]['visitors'] = $source_count;
                }

                break;
        }

        $request->set_param('per_page', 100);
        $request->set_param('after', $start_date);
        $request->set_param('before', $end_date);
        $request->set_param('order', 'asc');
        $_response = rest_do_request($request);

        if (is_wp_error($_response) || (isset($_response->status) && 200 !== $_response->status)) {
            echo esc_html__('API ERROR:', 'eib2bpro') . '<br>';
            print_r($_response); // display formatted error
            wp_die();
        }

        $response = $_response->get_data();

        if (isset($response['intervals'])) {
            foreach ($response['intervals'] as $intervals) {
                $interval = str_replace('-', '', $intervals['interval']);

                foreach ($intervals['subtotals'] as $k => $v) {
                    $result[$interval][$k] = $v;
                }

                $result[$interval]['day']   = $interval;
                $result[$interval]['goal']   = $goal;
                $result[$interval]['checkout']       = 0;
                $result[$interval]['product_pages']  = 0;
                $result[$interval]['orders']         = 0;
                $result[$interval]['label'] = strtoupper(date_i18n($label, strtotime($interval)));
            }

            $params = $request->get_query_params();
            $extend = apply_filters('eib2bpro_extends_reports_data', $params['interval'], $start_date, $end_date);
            if (is_array($extend)) {
                $result = $result + $extend;
            }
        }

        $average_sales = 0;
        $prev          = 0;
        $graph         = 2;
        $i             = 0;

        if ("1" === eib2bpro_option('reports-graph', "2")) {
            $graph = "1";
        }

        for ($x = $day_start; $x <= $day_end; ++$x) {
            ++$i;

            if ('weekly' === $args['range']) {
                $dto = new \DateTime();
                $dto->setISODate(date('Y'), $i);
                $day2 = date_i18n("d M", strtotime($dto->format('Y-m-d')) + (0 * 24 * 60 * 60));
            } elseif ('daily' === $args['range']) {
                if ("1" === $graph) {
                    $day2 =    date_i18n('d', strtotime($x));
                } else {
                    $day2 =    date_i18n('d D', strtotime($x));
                    if (eib2bpro_get('month')) {
                        $day2 =    date_i18n('d M - D', strtotime($x));
                    }
                }
            } elseif ('monthly' === $args['range']) {
                $day2 =    date_i18n('F', strtotime($x . "01"));
            } elseif ('yearly' === $args['range']) {
                $day2 =    date_i18n('Y', strtotime($x . "-01-01"));
            }

            if (!isset($result[$x])) {
                $results[$x] = array(
                    'day' => $x,
                    'average_sales'            => ($average_sales / $i),
                    'goal'                     => $goal,
                    'customers'                => 0,
                    'carts'                    => 0,
                    'checkout'                 => 0,
                    'product_pages'            => 0,
                    'orders'                   => 0,
                    'net_sales'                => 0,
                    'total_refunds'            => 0,
                    'total_shipping'           => 0,
                    'total_tax'                => 0,
                    'total_discount'           => 0,
                    'sales'                    => static::$zero,
                    'visitors'                 => static::$zero,
                    'label'                    => strtoupper($day2),
                    'prev'                     => 0
                );
            } else {
                $average_sales               += $result[$x]['total_sales'];
                if (!isset($result[$x]['visitors'])) {
                    $result[$x]['visitors'] = static::$zero;
                }
                $result[$x]['label']          = strtoupper($day2);
                $result[$x]['average_sales']  = $average_sales / $i;
                $result[$x]['prev']           = $prev;
                $results[$x]                  = $result[$x];
            }
            if (isset($result[$x]['total_sales'])) {
                $prev = $result[$x]['total_sales'];
            }
        }

        if ((int)eib2bpro_get('month') === 0) {
            $results = self::data_today($args['range'], $results);
        }

        return $results;
    }


    /**
     * Get live reports which are not saved to database yet
     *
     * @since  1.0.0
     */

    public static function data_today($range, $results)
    {
        global $wpdb;

        if ('daily' === $range) {
            $key       = date('Ymd', current_time('timestamp'));
            $day_start = eib2bpro_strtotime('now', 'Y-m-d 00:00:00');
            $day_end   = eib2bpro_strtotime('now', 'Y-m-d H:i:s');
        } elseif ('weekly' === $range) {
            $key                    = date('YW', current_time('timestamp'));
            $results[$key]['label'] = strtoupper(eib2bpro_strtotime('now', 'd M'));
            $day_start              = eib2bpro_strtotime('monday this week', 'Y-m-d 00:00:00');
            $day_end                = eib2bpro_strtotime('now', 'Y-m-d H:i:s');
        } elseif ('monthly' === $range) {
            $key       = date('Ym', current_time('timestamp'));
            $day_start = eib2bpro_strtotime('first day of this month', 'Y-m-d 00:00:00');
            $day_end   = eib2bpro_strtotime('now', 'Y-m-d H:i:s');
        } elseif ('yearly' === $range) {
            $key       = date('Y', current_time('timestamp'));
            $day_start = eib2bpro_strtotime('first day of january', 'Y-m-d 00:00:00');
            $day_end   = eib2bpro_strtotime('last day of december', 'Y-m-d 00:00:00');
        }


        $_visitors = $wpdb->get_results(
            $wpdb->prepare(
                "
		SELECT type, count(distinct session_id) as count
		FROM {$wpdb->prefix}eib2bpro_requests
		WHERE date >= %s AND date <= %s
		GROUP By type",
                $day_start,
                $day_end
            ),
            ARRAY_A
        );

        $_visitors_all = $wpdb->get_var(
            $wpdb->prepare(
                "
	SELECT  count(distinct session_id) as count
	FROM {$wpdb->prefix}eib2bpro_requests
	WHERE date >= %s AND date <= %s",
                $day_start,
                $day_end
            )
        );

        $results[$key]['visitors'] = $_visitors_all;

        foreach ($_visitors as $value) {
            if ("1" === $value['type']) {
                if (!isset($results[$key]['product_pages'])) {
                    $results[$key]['product_pages'] = 0;
                }

                $results[$key]['product_pages'] += $value['count'];
            }

            if ("4" === $value['type']) {
                if (!isset($results[$key]['carts'])) {
                    $results[$key]['carts'] = 0;
                }

                $results[$key]['carts'] += $value['count'];
            }

            if ("6" === $value['type']) {
                if (!isset($results[$key]['checkout'])) {
                    $results[$key]['checkout'] = 0;
                }
                $results[$key]['checkout'] += $value['count'];
            }
        }

        if (!isset($results[$key]['product_pages'])) {
            $results[$key]['product_pages'] = 0;
        }

        if (!isset($results[$key]['carts'])) {
            $results[$key]['carts'] = 0;
        }

        if (!isset($results[$key]['checkout'])) {
            $results[$key]['checkout'] = 0;
        }

        return $results;
    }
}
