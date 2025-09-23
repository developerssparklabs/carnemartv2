<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;
class Cron
{
    public static function run()
    {
        self::daily();
    }

    public static function daily()
    {
        global $wpdb;

        $last = get_option('eib2bpro_cron-daily-lastrun', '1970-01-01 00:00:00');

        if (eib2bpro_strtotime($last, 'Y-m-d') === eib2bpro_strtotime('now', 'Y-m-d')) {
            return;
        }

        self::reports();

        self::quick_orders();

        // delete unnecessary records
        $wpdb->query(
            $wpdb->prepare(
                "
                DELETE
                FROM {$wpdb->prefix}eib2bpro_requests
                WHERE date <= %s
                ",
                date('Y-m-d 00:00:00', strtotime("-" . intval(eib2bpro_option('tracker-keep-data', 7)) . " day"))
            )
        );

        update_option('eib2bpro_cron-daily-lastrun', eib2bpro_strtotime('now', 'Y-m-d H:i:s'), false);
    }

    public static function reports()
    {
        global $wpdb;
        foreach (array('D', 'W', 'M') as $type) {
            $result = [];

            if ('D' === $type) {
                $start_date = eib2bpro_strtotime('yesterday', 'Y-m-d 00:00:00');
                $end_date   = eib2bpro_strtotime('yesterday', 'Y-m-d 23:59:59');

                $visitors = $wpdb->get_var(
                    $wpdb->prepare(
                        "
                    SELECT count(distinct session_id) as counts
                    FROM {$wpdb->prefix}eib2bpro_requests
                    WHERE date >= %s AND date <= %s",
                        $start_date,
                        $end_date
                    )
                );

                $data = (array)eib2bpro_option('reports-daily-' . eib2bpro_strtotime('yesterday', 'Y-m'), array());
                $data[eib2bpro_strtotime('yesterday', 'Ymd')] = intval($visitors);

                update_option('eib2bpro_reports-daily-' . eib2bpro_strtotime('yesterday', 'Y-m'), $data, false);
            }

            if ('W' === $type && 1 === intval(eib2bpro_strtotime('now', 'N'))) {
                $data = (array)eib2bpro_option('reports-weekly-' . eib2bpro_strtotime('yesterday', 'Y'), array());

                $total = 0;

                for ($i = 1; $i < 8; ++$i) {
                    $source = (array)eib2bpro_option('reports-daily-' . eib2bpro_strtotime("now - $i days", 'Y-m'), array());
                    $source_date = eib2bpro_strtotime("now - $i days", 'Ymd');

                    if (isset($source[$source_date])) {
                        $total += intval($source[$source_date]);
                    }
                }

                $data[eib2bpro_strtotime('yesterday', 'YW')] = intval($total);

                update_option('eib2bpro_reports-weekly-' . eib2bpro_strtotime('yesterday', 'Y'), $data, false);
            }

            if ('M' === $type && 1 === intval(eib2bpro_strtotime('now', 'j'))) {

                $data = (array)eib2bpro_option('reports-monthly-' . eib2bpro_strtotime('yesterday', 'Y'), array());

                $total = 0;
                $day_count =  cal_days_in_month(CAL_GREGORIAN, eib2bpro_strtotime('yesterday', 'm'), eib2bpro_strtotime('yesterday', 'Y'));

                for ($i = 1; $i <= $day_count; ++$i) {
                    $source = (array)eib2bpro_option('reports-daily-' . eib2bpro_strtotime("now - $i days", 'Y-m'), array());
                    $source_date = eib2bpro_strtotime("now - $i days", 'Ymd');

                    if (isset($source[$source_date])) {
                        $total += intval($source[$source_date]);
                    }
                }

                $data[eib2bpro_strtotime('yesterday', 'Ym')] = intval($total);

                update_option('eib2bpro_reports-monthly-' . eib2bpro_strtotime('yesterday', 'Y'), $data, false);
            }
        }
    }

    public static function quick_orders()
    {
        $posts = new \WP_Query(array(
            'post_type' => 'eib2bpro_quick',
            'post_status' => 'draft',
            'meta_query' => array(
                array(
                    'key' => 'eib2bpro_quickorder_reminder',
                    'value' => '1',
                )
            )
        ));

        if ($posts->have_posts()) {
            $mailer = \WC()->mailer();
            foreach ($posts->posts as $post) {
                $every = intval(get_post_meta($post->ID, 'eib2bpro_quickorder_every', true));
                $start = get_post_meta($post->ID, 'eib2bpro_quickorder_start', true);

                $endTimeStamp = strtotime(date('Y-m-d'));
                $startTimeStamp = strtotime($start);

                $timeDiff = $endTimeStamp - $startTimeStamp;

                $numberDays = $timeDiff / 86400;

                $numberDays = intval($numberDays);

                if (0 < $numberDays && 0 < $every) {
                    if (0 === $numberDays % $every) {
                        do_action('eib2bpro_quick_orders_reminder', intval($post->ID));
                    }
                }
            }
        }
    }
}
