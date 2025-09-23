<?php

namespace EIB2BPRO\Core;

use \EIB2BPRO\Core\Top;

defined('ABSPATH') || exit;
class Notifications
{
    public static $alerts = [];
    public static $sounds = [];
    public static $count = null;

    public static function run()
    {
        switch (eib2bpro_get('do')) {
            case 'settings':
                self::settings();
                break;
            default:
                self::index();
                break;
        }
    }

    public static function index()
    {
        global $wpdb;

        $notifications = [];
        $update_id = 0;
        $last_event_id = intval(get_user_meta(get_current_user_id(), 'eib2bpro_last_event_id', true));

        $types = "'eib2bpro'";

        foreach (array_values(self::user_notification_types()) as $type) {
            $types .= ", '" . $type . "'";
        }

        $_notifications = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT * FROM {$wpdb->prefix}eib2bpro_events WHERE event_type IN (" . $types . ") AND event_id > %d ORDER BY event_id DESC
                ",
                $last_event_id
            )
        );

        if (0 === count($_notifications)) {
            $_notifications = $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT * FROM {$wpdb->prefix}eib2bpro_events WHERE event_type IN (" . $types . ") ORDER BY event_id DESC LIMIT %d
                    ",
                    10
                )
            );
        }

        foreach ($_notifications as $notify) {
            if (0 === $update_id) {
                $update_id = $notify->event_id;
            }
            switch ($notify->event_type) {

                case 'new_order':
                    try {
                        $order =  new \WC_Order($notify->resource_id);
                    } catch (Exception $e) {
                        continue 2;
                    }

                    $order_data = $order->get_data();

                    if ('failed' !== $order->get_status()) {
                        $notifications[$notify->event_id] = array(
                            'time'    => $notify->event_date,
                            'type'    => $notify->event_type,
                            'title'   => sprintf(esc_html__('New order', 'eib2bpro')),
                            'details' => array(
                                'order_id'             => $notify->resource_id,
                                'status'               => $order->get_status(),
                                'total'                => $order->get_formatted_order_total(),
                                'payment_method_title' => $order_data['payment_method_title'],
                                'customer'             => esc_html(sprintf("%s %s", $order_data['billing']['first_name'], $order_data['billing']['last_name'])),
                                'city'                 => esc_html(sprintf("%s, %s", $order_data['billing']['city'], isset(WC()->countries->states[$order_data['billing']['country']][$order_data['billing']['state']]) ? WC()->countries->states[$order_data['billing']['country']][$order_data['billing']['state']] : '')),
                            )
                        );
                    }
                    break;
                case 'new_comment':
                    $comment = get_comment($notify->resource_id);

                    if (!$comment) {
                        continue 2;
                    }

                    $post = get_post($comment->comment_post_ID);

                    $notifications[$notify->event_id] = array(
                        'time'    => $notify->event_date,
                        'type'    => $notify->event_type,
                        'title'   => sprintf(esc_html__("%s has commented to %s", 'eib2bpro'), $comment->comment_author, $post->post_title),
                        'details' => array(
                            'comment_content' => $comment->comment_content,
                            'post_id'         => $post->ID,
                            'comment_id'      => $comment->comment_ID,
                            'star'            => intval(get_comment_meta($comment->comment_ID, 'rating', true))
                        )
                    );
                    break;

                case 'user_needs_approval':
                    $user = get_userdata($notify->resource_id);

                    if (!$user) {
                        continue 2;
                    }

                    $title = esc_html__("The customer is waiting for your approval", 'eib2bpro');

                    if ('no' !== get_user_meta($notify->resource_id, 'eib2bpro_user_approved', true)) {
                        $title = esc_html__("The customer has been approved", 'eib2bpro');
                    }

                    $notifications[$notify->event_id] = array(
                        'time'    => $notify->event_date,
                        'type'    => $notify->event_type,
                        'title'   => $title,
                        'details' => array(
                            'user_id' => $notify->resource_id
                        )
                    );
                    break;

                case 'new_b2b_user':
                    $user = get_userdata($notify->resource_id);

                    if (!$user) {
                        continue 2;
                    }

                    $notifications[$notify->event_id] = array(
                        'time'    => $notify->event_date,
                        'type'    => $notify->event_type,
                        'title'   => esc_html__("New B2B customer", 'eib2bpro'),
                        'details' => array(
                            'user_id' => $notify->resource_id
                        )
                    );
                    break;

                case 'new_quote_request':
                    $post = get_post($notify->resource_id);

                    if (!$post) {
                        continue 2;
                    }

                    $notifications[$notify->event_id] = array(
                        'time'    => $notify->event_date,
                        'type'    => $notify->event_type,
                        'title'   => esc_html__("A new quote request", 'eib2bpro'),
                        'details' => array(
                            'post_id' => $notify->resource_id
                        )
                    );
                    break;

                case 'stock';
                    $product = wc_get_product($notify->resource_id);

                    if (!$product) {
                        continue 2;
                    }

                    if (intval($notify->extra) < 1) {
                        $title = sprintf(esc_html__("%s - Out of stock", 'eib2bpro'), $product->get_name());
                        $title2 =  esc_html__('Out of stock', 'eib2bpro');
                    } else {
                        $title = sprintf(esc_html__("%s - Low stock", 'eib2bpro'), $product->get_name());
                        $title2 =  esc_html__('Low stock', 'eib2bpro');
                    }

                    $notifications[$notify->event_id] = array(
                        'time'    => $notify->event_date,
                        'type'    => $notify->event_type,
                        'title'   => $title,
                        'details' => array(
                            'product_name' => $product->get_name(),
                            'product_id'   => $product->get_id(),
                            'qty'   => $notify->extra
                        )
                    );

                    break;
            }
        }

        if (0 < $update_id) {
            update_user_meta(get_current_user_id(), 'eib2bpro_last_event_id', $update_id);
        }

        echo eib2bpro_view('core', 0, 'shared.index.notifications', ['notifications' => $notifications]);
    }

    public static function settings()
    {
        wp_enqueue_style("eib2bpro-settings", EIB2BPRO_PUBLIC . "app/settings/public/settings.css");

        $options = self::notification_types();
        $user_types = self::user_notification_types(); ?>

        <div class="container-fluid">
            <div class="row">
                <div class="eib2bpro-app-new-item-head pl-4 ml-1 pb-4 font-14">
                    <a href="<?php echo eib2bpro_admin('core', ['section' => 'notifications']) ?>" class="eib2bpro-font-14 text-dark strong">← <?php esc_html_e('Back', 'eib2bpro'); ?></a>
                </div>
            </div>
        </div>

        <div class="eib2bpro-app-new-item-content eib2bpro-app-settings-menu mt-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <?php foreach ($options as $opt_key => $opt_name) { ?>
                            <div class="table-item pl-4 ml-1">
                                <div class="pb-2 pt-2 row align-items-center">
                                    <div class="float-left">
                                        <?php eib2bpro_ui('onoff_ajax', $opt_key, in_array($opt_key, $user_types) ? 1 : 0, ['app' => 'core', 'do' => 'enable-notifications', 'class' => 'switch-sm']); ?>
                                    </div>
                                    <div class="float-left">
                                        <h6 class="m-0 ml-3"><?php eib2bpro_e($opt_name) ?></h6>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    }

    public static function notification_types()
    {
        $types = apply_filters('eib2bpro_notifications', [
            'new_order' => esc_html__('New order', 'eib2bpro'),
            'new_comment' => esc_html__('New comment', 'eib2bpro'),
            'stock' => esc_html__('Stock alerts', 'eib2bpro')
        ]);
        return $types;
    }
    public static function user_notification_types()
    {
        $types = self::notification_types();
        $disabled_types = (array)wp_parse_list(get_user_meta(get_current_user_id(), 'eib2bpro-disabled-notifications', true));

        if (empty($disabled_types)) {
            return array_keys($types);
        }

        return array_diff(array_keys($types), $disabled_types);
    }

    public static function enable()
    {
        $name = sanitize_key(eib2bpro_post('name'));

        $types = (array)wp_parse_list(get_user_meta(get_current_user_id(), 'eib2bpro-disabled-notifications', true));

        if ('false' === eib2bpro_post('checked', 0)) {
            $types[$name] = $name;
        } else {
            if (isset($types[$name])) {
                unset($types[$name]);
            }
        }

        update_user_meta(get_current_user_id(), 'eib2bpro-disabled-notifications', $types);

        eib2bpro_success();
    }

    public static function count()
    {
        global $wpdb;

        if (null !== self::$count) {
            return self::$count;
        }

        $update_id = 0;
        $last_event_id = intval(get_user_meta(get_current_user_id(), 'eib2bpro_last_event_id', true));
        $last_alert_id = intval(get_user_meta(get_current_user_id(), 'eib2bpro_last_alert_id', true));

        $types = "'eib2bpro'";

        foreach (array_values(self::user_notification_types()) as $type) {
            $types .= ", '" . $type . "'";
        }

        $events = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT * FROM {$wpdb->prefix}eib2bpro_events WHERE event_type IN (" . $types . ") AND event_id > %d ORDER BY event_id DESC
                ",
                $last_event_id
            )
        );

        foreach ($events as $event) {
            if ((strtotime(current_time('mysql')) - eib2bpro_option('refresh', 60)) <= strtotime($event->event_date)) {
                if (0 === $update_id) {
                    $update_id = $event->event_id;
                }
                switch ($event->event_type) {
                    case 'new_order':
                        try {
                            $order =  new \WC_Order($event->resource_id);
                        } catch (Exception $e) {
                            continue 2;
                        }

                        $order_data = $order->get_data();

                        self::$alerts[] = [
                            'title' => esc_html__('New order', 'eib2bpro'),
                            'body'  => esc_html(sprintf("%s - %s %s", $order_data['currency'] . ' ' . $order_data['total'], $order_data['billing']['first_name'], $order_data['billing']['last_name'])),
                            'link'  => eib2bpro_admin('orders', array())
                        ];
                        self::$sounds[] = EIB2BPRO_PUBLIC . 'app/core/public/sounds/notification.mp3';

                        break;
                    case 'new_comment':
                        $comment = get_comment($event->resource_id);

                        if (!$comment) {
                            continue 2;
                        }

                        $post = get_post($comment->comment_post_ID);

                        self::$alerts[] = [
                            'title' => esc_html__('New comment', 'eib2bpro'),
                            'body'  => $post->post_title,
                            'link'  => eib2bpro_admin('comments', array()) . "#" . $comment->comment_ID
                        ];
                        self::$sounds[] = EIB2BPRO_PUBLIC . 'app/core/public/sounds/notification.mp3';

                        break;

                    case 'user_needs_approval':
                        $user = get_userdata($event->resource_id);

                        if (!$user) {
                            continue 2;
                        }

                        self::$alerts[] = [
                            'title' => esc_html__('The customer is waiting for your approval', 'eib2bpro'),
                            'body'  => $user->first_name . ' ' . $user->last_name,
                            'link'  => eib2bpro_admin('b2b', array())
                        ];
                        self::$sounds[] = EIB2BPRO_PUBLIC . 'app/core/public/sounds/notification.mp3';

                        break;

                    case 'new_b2b_user':
                        $user = get_userdata($event->resource_id);

                        if (!$user) {
                            continue 2;
                        }

                        self::$alerts[] = [
                            'title' => esc_html__('New B2B customer', 'eib2bpro'),
                            'body'  => $user->first_name . ' ' . $user->last_name,
                            'link'  => eib2bpro_admin('b2b', array())
                        ];
                        self::$sounds[] = EIB2BPRO_PUBLIC . 'app/core/public/sounds/notification.mp3';

                        break;


                    case 'new_quote_request':
                        $user = get_userdata($event->resource_id);

                        if (!$user) {
                            continue 2;
                        }

                        self::$alerts[] = [
                            'title' => esc_html__('New quote request', 'eib2bpro'),
                            'body'  => esc_html__('You have a new quote request', 'eib2bpro'),
                            'link'  => eib2bpro_admin('b2b', array())
                        ];
                        self::$sounds[] = EIB2BPRO_PUBLIC . 'app/core/public/sounds/notification.mp3';

                        break;

                        // Stock
                    case 'stock':

                        $product = wc_get_product($event->resource_id);

                        if (!$product) {
                            continue 2;
                        }

                        if (intval($event->extra) < 1) {
                            $title = sprintf(esc_html__("%s - Out of stock", 'eib2bpro'), $product->get_name());
                            $title2 =  esc_html__('Out of stock', 'eib2bpro');
                        } else {
                            $title = sprintf(esc_html__("%s - Low stock", 'eib2bpro'), $product->get_name());
                            $title2 =  esc_html__('Low stock', 'eib2bpro');
                        }

                        self::$alerts[] = [
                            'title' => $title2,
                            'body'  => sprintf(esc_html__("Low stock for %s - %d", 'eib2bpro'), $product->get_name(), $event->extra),
                            'link'  => eib2bpro_admin('products', array())
                        ];
                        self::$sounds[] = EIB2BPRO_PUBLIC . 'app/core/public/sounds/notification.mp3';


                        break;
                }
            }
        }

        if (0 < $update_id) {
            update_user_meta(get_current_user_id(), 'eib2bpro_last_alert_id', $update_id);
        }

        self::$count = count($events);

        return self::$count;
    }

    public static function title($title = '')
    {

        if (0 < self::$count) {
            $title = "(" . self::$count . ")";
        }

        return $title . ' ' . get_bloginfo('name');
    }



    public static function ajax()
    {
        self::count();

        $output = [
            ['type' => 'top', 'data' => self::top_widgets()],
            ['type' => 'alerts', 'data' => self::$alerts],
            ['type' => 'sounds', 'data' => self::$sounds],
            ['type' => 'title', 'data' => self::title()]
        ];


        eib2bpro_success('OK', $output);
    }

    public static function top_widgets()
    {
        $output = [];

        $allWidgets = Top::allWidgets();
        $widgets = Top::widgets();

        foreach ($widgets as $id => $widget) {
            if (in_array($id, array_keys($allWidgets)) && 1 === $widget['active']) {
                $class = $allWidgets[$id]['class'];
                $output[$id] = $class(['raw' => true]);
            }
        }

        return $output;
    }

    public static function add($data = array())
    {
        global $wpdb;

        if (!isset($data['user_id'])) {
            $user         = get_current_user_id();
            $data['user_id'] = absint($user);
        }

        $insert = $wpdb->insert(
            $wpdb->prefix . "eib2bpro_events",
            array(
                'user_id'   => $data['user_id'],
                'event_date'   => current_time('mysql'),
                'resource_id'     => $data['resource_id'],
                'event_type'   => $data['event_type'],
                'extra'  => $data['extra'],
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * New comment
     *
     * @since  1.0.0
     */

    public static function new_comment($comment_ID, $comment_approved)
    {
        if ('spam' !== $comment_approved && 'trash' !== $comment_approved) {
            $data = array(
                'event_type'  => 'new_comment',
                'resource_id'    => absint($comment_ID),
                'extra' => ''
            );

            self::add($data);
        }
    }

    public static function new_order($order_id)
    {
        global $wpdb;

        $_query = $wpdb->get_var(
            $wpdb->prepare(
                "
          SELECT event_id
          FROM {$wpdb->prefix}eib2bpro_events
          WHERE event_type = %s AND resource_id = %d",
                'new_order',
                $order_id
            )
        );

        if ($_query) {
            return false;
        }

        $notify = array('wc-cancelled', 'wc-refunded', 'wc-failed');

        $order = wc_get_order($order_id);

        $order_status  = $order->get_status();

        if (!in_array($order_status, $notify) && !in_array('wc-' . $order_status, $notify)) {
            $data = array(
                'event_type'  => 'new_order',
                'resource_id'    => $order_id,
                'extra' => ''
            );

            self::add($data);
        }

        // B2B: is that an offer?
        $offer_product_id = eib2bpro_option('b2b_offer_default_id', 0);
        if (0 < intval($offer_product_id)) {
            $offer = 0;
            $items = $order->get_items();
            foreach ($items as $item_id => $item) {
                if ($offer_product_id === $item->get_product_id()) {
                    $offer_id = wc_get_order_item_meta($item_id, '_eib2bpro_offer_id', true);
                    if ($offer_id) {
                        $offer_id = maybe_unserialize($offer_id);
                        if (isset($offer_id[0]) && 0 < intval($offer_id[0])) {

                            $offer = $offer_id[0];
                            $user_stats = get_user_meta(get_current_user_id(), '_eib2bpro_offer_stats_buy_' . $offer, true);

                            if (!$user_stats) {
                                update_post_meta($offer, '_eib2bpro_offer_stats_buy_count_' . \EIB2BPRO\B2b\Site\Main::user('group'), intval(get_post_meta($offer, '_eib2bpro_offer_stats_buy_count_' . \EIB2BPRO\B2b\Site\Main::user('group'), true)) + 1);
                                update_post_meta($offer, '_eib2bpro_offer_stats_buy_count_all', intval(get_post_meta($offer, '_eib2bpro_offer_stats_buy_count_all', true)) + 1);
                                update_post_meta($offer, '_eib2bpro_offer_stats_buy_total', $order->get_total());
                            }

                            update_user_meta(get_current_user_id(), '_eib2bpro_offer_stats_buy_' . $offer, eib2bpro_strtotime('now', 'Y-m-d H:i:s'));

                            $stats_by_month = (array)get_post_meta($offer, '_eib2bpro_offer_stats_buy_days', true);
                            if (is_array($stats_by_month) && isset($stats_by_month[eib2bpro_strtotime('now', 'Ymd')])) {
                                $stats_by_month[eib2bpro_strtotime('now', 'Ymd')] += 1;
                            } else {
                                $stats_by_month[eib2bpro_strtotime('now', 'Ymd')] = 1;
                            }
                            update_post_meta($offer, '_eib2bpro_offer_stats_buy_days', $stats_by_month);
                        }
                    }
                }
            }
        }
    }

    public static function new_quote($quote_id = 0)
    {
        $data = array(
            'event_type'  => 'new_quote_request',
            'resource_id'    => absint($quote_id),
            'extra' => ''
        );

        self::add($data);

        eib2bpro_option('badge-b2b',  intval(eib2bpro_option('badge-b2b', 0)) + 1, 'set');
        eib2bpro_option('badge-quote',  intval(eib2bpro_option('badge-quote', 0)) + 1, 'set');

        $mailer = WC()->mailer();
        do_action('eib2bpro_new_quote_request', $quote_id);
    }


    public static function new_user($user_id = 0)
    {
        $data = [];
        $user_type = get_user_meta($user_id, 'eib2bpro_user_type', true);
        $approved = get_user_meta($user_id, 'eib2bpro_user_approved', true);

        if ('yes' !== $approved) {
            $data = array(
                'event_type'  => 'user_needs_approval',
                'resource_id'    => absint($user_id),
                'extra' => '',
            );

            eib2bpro_option('approval_waiting', 1);
            eib2bpro_option('badge-b2b',  intval(eib2bpro_option('badge-b2b', 0)) + 1, 'set');
        } elseif ('b2b' === $user_type) {
            $data = array(
                'event_type'  => 'new_b2b_user',
                'resource_id'    => absint($user_id),
                'extra' => '',
            );
        }
        if (isset($data['event_type'])) {
            self::add($data);
        }
    }

    public static function low_stock($product)
    {
        if (is_object($product)) {
            $data = array(
                'event_type'  => 'stock',
                'resource_id'    => absint($product->get_id()),
                'extra' => $product->get_stock_quantity()
            );

            self::add($data);
        }
    }
}
