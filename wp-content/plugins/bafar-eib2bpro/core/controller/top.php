<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;
class Top
{
    public static function run()
    {
        $allWidgets = self::allWidgets();
        $widgets = self::widgets();

        switch (eib2bpro_get('do')) {
            case 'view':
                self::viewWidgetPage();
                break;
        }
    }

    public static function render($start = '', $end = '', $reverse = false)
    {
        $output = "";

        $allWidgets = self::allWidgets();
        $widgets = self::widgets();

        if ($reverse || 'console' === \EIB2BPRO\Admin::$theme) {
            $widgets = array_reverse($widgets);
        }

        foreach ($widgets as $id => $widget) {
            if (in_array($id, array_keys($allWidgets)) && 1 === $widget['active']) {
                $class = $allWidgets[$id]['class'];
                $output .= $start . "<div class='eib2bpro-top-widget eib2bpro-top-{$id}'>" . $class() . "</div>" . $end;
            }
        }
        return $output;
    }

    public static function widgets()
    {
        return eib2bpro_option('top-widgets', [], 'get');
    }

    public static function allWidgets()
    {
        return apply_filters('eib2bpro_top_widgets', [
            'me' => ['id' => 'me', 'title' => esc_html__('Me', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::me'],
            'notifications' => ['id' => 'notifications', 'title' => esc_html__('Notifications', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::notifications'],
            'today_revenue' => ['id' => 'today_revenue', 'title' => esc_html__('Today revenue', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::today_revenue'],
            'online_visitors' => ['id' => 'online_visitors', 'title' => esc_html__('Online visitors', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::online_visitors'],
            'orders' => ['id' => 'orders', 'title' => esc_html__('Orders', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::orders'],
            'divider_1' => ['id' => 'divider_1', 'title' => esc_html__('Divider', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::divider'],
            'divider_2' => ['id' => 'divider_2', 'title' => esc_html__('Divider', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::divider'],
            'divider_3' => ['id' => 'divider_3', 'title' => esc_html__('Divider', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::divider'],
            'divider_4' => ['id' => 'divider_4', 'title' => esc_html__('Divider', 'eib2bpro'), 'active' => 0, 'class' => '\EIB2BPRO\Core\Top::divider']
        ]);
    }

    public static function viewWidgetPage()
    {
        $id = sanitize_key(eib2bpro_get('id'), '');

        if (empty($id)) {
            eib2bpro_error(esc_html__('Not found.', 'eib2bpro'));
        }

        $widgets = self::allWidgets();

        if (isset($widgets[$id]['class'])) {
            $class = $widgets[$id]['class'] . '_Page';
            $class();
        }
    }

    public static function me($args = false)
    {


        return '<div class="eib2bpro-os-avatar">
        <a href="' . eib2bpro_admin('core', ['action' => 'me']) . '" class="eib2bpro-panel" data-background="#fff" data-width="450px">
        ' . eib2bpro_ui('avatar', get_current_user_id(), ['toggle' => 'none']) . '
        </a>
    </div>';
    }

    public static function today_revenue($args = false)
    {
        $request = new \WP_REST_Request('GET', '/wc-analytics/reports/revenue/stats');
        $request->set_param('per_page', 100);
        $request->set_param('after', eib2bpro_strtotime('now', 'Y-m-d\T00:00:00'));
        $request->set_param('before', eib2bpro_strtotime('now', 'Y-m-d\T23:59:59'));
        $request->set_param('order', 'asc');
        $_response = rest_do_request($request);
        $response = $_response->get_data();

        $total = intval($response['totals']['total_sales']);

        if (isset($args['raw'])) {
            return $total;
        }
        return "<span class='text-danger eib2bpro-top-sup'>" . get_woocommerce_currency_symbol() . "</span> &nbsp;&nbsp; <span class='odometer eib2bpro-top-data-today_revenue'>" . eib2bpro_r(number_format($total)) . '</span>';
    }

    public static function online_visitors($args = false)
    {
        $count = \EIB2BPRO\Dashboard\Widgets\Onlineusers::run(['ajax' => 1]);

        if (isset($args['raw'])) {
            return $count;
        }
        return "<span class='text-danger  eib2bpro-top-sup'>●</span> &nbsp;&nbsp; <span class='odometer eib2bpro-top-data-online_visitors'>" . eib2bpro_r($count) . '</span>';
    }

    public static function orders($args = false)
    {
        return '<a href="' . eib2bpro_admin('orders') . '" class="eib2bpro-panel" data-width="1260px" data-hide-close="true"><i class="eib2bpro-top-notification-icon ri-shopping-bag-3-line"></i></a>';
    }

    public static function notifications($args = false)
    {
        $count = \EIB2BPRO\Core\Notifications::count();

        return '<div class="eib2bpro-top-data-notifications"><a href="' . eib2bpro_admin('core', ['section' => 'notifications']) . '" class="eib2bpro-panel" data-background="#fff" data-width="450px"><i class="eib2bpro-top-notification-icon ri-notification-4' . (0 < $count ? '-fill' : '-line') . '' . (0 < $count ? ' eib2bpro-top-fill' : '') . '"></i></a></div>';
    }


    public static function divider($args = false)
    {
        return "<div class='eib2bpro-top-widget-divider'></div>";
    }

    public static function divider_vertical($args = false)
    {
        return "<div class='eib2bpro-top-widget-divider-vertical float-right'>&mdash;</div>";
    }
}
