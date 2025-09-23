<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;
class Tracker
{
    public static $visitor_id = false;
    public static $session_id = false;

    public static function record()
    {
        add_action('init', function () {
            self::$visitor_id = self::get_visitor();
            self::$session_id = self::get_session();
        });

        add_action("wp_footer", function () {
            if (eib2bpro_get('s')) { // Search
                $data['type']  = 10;
                $data['id']    = 1;
                $data['extra'] = sanitize_text_field(eib2bpro_post('i', ''));
                self::add_request($data);
            } elseif (is_front_page()) {  // Homepage
                $data['type'] = 7;
                $data['id']   = 1;
                self::add_request($data);
            } elseif (is_product()) { // Product
                global $product;
                if (is_callable($product, 'get_id')) {
                    $data['type'] = 1;
                    $data['id']   = $product->get_id();
                    self::add_request($data);
                }
            } elseif (is_product_category()) { // Category
                global $wp_query;
                $cat = $wp_query->get_queried_object();
                $data['type'] = 2;
                $data['id']   = $cat->term_id;
                self::add_request($data);
            } else {
                global $wp_query;
                if (isset($wp_query->queried_object) && is_object($wp_query->queried_object) && isset($wp_query->queried_object->has_archive)) {
                    $page = get_page_by_path($wp_query->queried_object->has_archive);
                    if ($page) {
                        $data['type']  = 17;
                        $data['id']    = $page->ID;
                        $data['extra'] = sanitize_text_field($page->post_title);
                        self::add_request($data);
                    }
                } elseif (isset($wp_query->post->ID) && $wp_query->post->ID > 0) {
                    $data['type']  = 17;
                    $data['id']    = $wp_query->post->ID;
                    $data['extra'] = sanitize_text_field($wp_query->post->post_title);
                    self::add_request($data);
                }
            }
        });
    }

    public static function record_by_ajax()
    {
        global $wpdb, $woocommerce;

        $visitor = self::get_visitor();

        $data['ref'] = "";

        switch (eib2bpro_post('t')) {

                // Product
            case "p":

                $ID      = intval(eib2bpro_post('i', 0));
                $product = wc_get_product($ID);

                if (!$product) {
                    wp_die();
                }

                $data['type'] = 1;
                $data['id']   = $ID;

                self::add_request($data);

                break;

                // Category
            case "c":

                $ID       = intval(eib2bpro_post('i', 0));
                $category = get_term_by('id', $ID, 'product_cat', 'ARRAY_A');

                if (!$category) {
                    wp_die();
                }

                $data['type'] = 2;
                $data['id']   = $ID;

                self::add_request($data);

                break;

                // Home
            case "h":

                $data['type'] = 7;
                $data['id']   = 1;

                self::add_request($data);

                break;

                // Search
            case "s":

                $data['type']  = 10;
                $data['id']    = 1;
                $data['extra'] = sanitize_text_field(eib2bpro_post('i', ''));

                self::add_request($data);

                break;

                // Pages
            case "o":

                $ID      =  intval(eib2bpro_post('i', 0));

                $title = get_the_title($ID);

                if (!$title) {
                    wp_die();
                }

                $data['type']  = 17;
                $data['id']    = $ID;
                $data['extra'] = sanitize_text_field($title);

                self::add_request($data);

                break;
        }
    }

    public static function add_tracker_js()
    {
        add_action("wp_footer", function () {
            wp_enqueue_script('eib2bpro', EIB2BPRO_PUBLIC . 'core/public/js/public.js', null, '1.0');

            $JSvars = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eib2bpro-security'),
            );


            $JSvars["eib2bpro_p"] = "pulse";

            if (eib2bpro_get('s')) {
                $JSvars["eib2bpro_t"] = "s";
                $JSvars["eib2bpro_i"] = eib2bpro_get('s', '');
            } elseif (is_front_page()) {
                $JSvars["eib2bpro_t"] = "h";
                $JSvars["eib2bpro_i"] = 0;
            } elseif (is_product()) {
                global $product;

                $JSvars["eib2bpro_t"] = "p";
                $JSvars["eib2bpro_i"] = $product->get_id();
            } elseif (is_product_category()) {
                global $wp_query;

                $cat = $wp_query->get_queried_object();

                $JSvars["eib2bpro_t"] = "c";
                $JSvars["eib2bpro_i"] = $cat->term_id;
            } else {
                global $wp_query;

                if (isset($wp_query->queried_object) && is_object($wp_query->queried_object) && isset($wp_query->queried_object->has_archive)) {
                    $page = get_page_by_path($wp_query->queried_object->has_archive);
                    if ($page) {
                        $JSvars["eib2bpro_t"] = "o";
                        $JSvars["eib2bpro_i"] = $page->ID;
                    }
                } elseif (isset($wp_query->post->ID) && $wp_query->post->ID > 0) {
                    $JSvars["eib2bpro_t"] = "o";
                    $JSvars["eib2bpro_i"] = intval($wp_query->post->ID);
                }
            }

            wp_localize_script('eib2bpro', 'eiB2BProPublic', $JSvars);
        });
    }

    /**
     * Insert front-end events to database
     *
     * @since 1.0.0
     * @param array     $data
     */

    public static function add_request($data = array())
    {
        global $wpdb, $woocommerce;

        // Detects if it is a bot.

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            $bot_identifiers = array(
                'bot',
                'slurp',
                'crawler',
                'spider',
                'curl',
                'facebook',
                'fetch'
            );

            foreach ($bot_identifiers as $identifier) {
                if (stripos($user_agent, $identifier) !== false) {
                    return true;
                }
            }
        }

        if (!isset($data['visitor'])) {
            $data['visitor'] = self::get_visitor();
        }

        if (!isset($data['date'])) {
            $data['date'] =  current_time('mysql');
        }

        if (!isset($data['ip'])) {
            $data['ip'] =   preg_replace('/[^0-9a-fA-F:., ]/', '', self::get_ip_address());

            if (!filter_var($data['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $data['ip'] = '0.0.0.0';
            }

            if (1 === eib2bpro_option('tracker-geo', 1)) {
                $geo = \WC_Geolocation::geolocate_ip($data['ip'], true);
                if (!empty($geo['country'])) {
                    $data['ip'] =  $geo['country'] . " — " . $data['ip'];
                }
            }
        }

        if (!isset($data['ref'])) {
            $data['ref'] = '';
        }

        if (!isset($data['extra'])) {
            $data['extra'] = '';
        }

        $insert = $wpdb->insert(
            $wpdb->prefix . "eib2bpro_requests",
            array(
                'session_id' => self::get_session(),
                'visitor'    => $data['visitor'],
                'year'       => eib2bpro_strtotime($data['date'], 'y'),
                'month'      => eib2bpro_strtotime($data['date'], 'm'),
                'week'       => eib2bpro_strtotime($data['date'], 'W'),
                'day'        => eib2bpro_strtotime($data['date'], 'd'),
                'date'       => $data['date'],
                'ip'         => $data['ip'],
                'type'       => $data['type'],
                'id'         => $data['id'],
                'extra'      => $data['extra'],
                'ref'        => $data['ref']
            ),
            array('%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }


    /**
     * Get user id
     *
     * @return none
     */

    public static function get_visitor()
    {
        if (self::$visitor_id) {
            return self::$visitor_id;
        }

        global $_COOKIE;
        $visitor = get_current_user_id();

        if ($visitor === 0) {
            if (!isset($_COOKIE["eib2bpro_u"])) {
                $visitor = md5(uniqid() . rand(1, 100000) . self::get_ip_address() . time());
                setcookie("eib2bpro_u", $visitor, time() + (365 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
            } else {
                $visitor = sanitize_key($_COOKIE["eib2bpro_u"]);
            }

            if (32 !== strlen($visitor)) {
                $visitor = md5(uniqid() . time());
            }
            $visitor = "v" . $visitor;
        } else {
            if (isset($_COOKIE["eib2bpro_u"])) {

                $visitor_id = sanitize_key($_COOKIE["eib2bpro_u"]);
                $_session_id = eib2bpro_clean($_COOKIE["eib2bpro_session"]);

                if ($_session_id and 32 === strlen($_session_id)) {
                    $session_id = sanitize_key($_session_id);

                    if (32 === strlen($visitor_id) && 0 < intval(get_current_user_id())) {

                        global $wpdb;

                        $wpdb->update(
                            $wpdb->prefix . 'eib2bpro_requests',
                            [
                                'visitor' => intval(get_current_user_id())
                            ],
                            [
                                'session_id' => $session_id,
                                'visitor' => "v" . $visitor_id,
                            ]
                        );
                    }
                }
                setcookie("eib2bpro_u", $visitor, time() - (365 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
            }
        }

        return $visitor;
    }


    /**
     * Set and get session id
     *
     * @return none
     */

    public static function get_session()
    {
        if (self::$session_id) {
            return self::$session_id;
        }

        if (!isset($_COOKIE["eib2bpro_session"])) {
            $session_id = md5(uniqid() . rand(1, 100000) . self::get_ip_address() . time());
            setcookie("eib2bpro_session", $session_id, time() + (20 * 60), COOKIEPATH, COOKIE_DOMAIN);
        } else {
            $_session_id = eib2bpro_clean($_COOKIE["eib2bpro_session"]);

            if ($_session_id and 32 === strlen($_session_id)) {
                $session_id = sanitize_key($_session_id);
            } else {
                $session_id = md5(uniqid() . rand(1, 100000) . self::get_ip_address() . time());
                setcookie("eib2bpro_session", $session_id, time() + (20 * 60), COOKIEPATH, COOKIE_DOMAIN);
            }
        }
        return $session_id;
    }

    public static function get_ip_address()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return (string) rest_is_ip_address(trim(current(preg_split('/,/', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']))))));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        }
        return '';
    }

    /**
     * Hook for add to cart
     *
     * @since 1.0.0
     */

    public static function woocommerce_add_to_cart($key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        if (!isset($_COOKIE["eib2bpro_session"])) {
            return false;
        }

        $data['type']  = 4;
        $data['id']    = $product_id;
        $data['extra'] = serialize(array('quantity' => $quantity, 'variation_id' => $variation_id));

        self::add_request($data);
    }

    /**
     * Hook for remove cart
     *
     * @since 1.0.0
     */


    public static function woocommerce_remove_cart_item($cart_item_key, $_this)
    {
        global $woocommerce;

        $items = $woocommerce->cart->get_cart();

        if (isset($items[$cart_item_key])) {
            $data['type']  = 5;
            $data['id']    = absint($items[$cart_item_key]['product_id']);
            $data['extra'] = serialize(array('line_total' =>  floatval($items[$cart_item_key]['line_total'])));

            self::add_request($data);
        }
    }

    /**
     * Hook for checkout
     *
     * @since 1.0.0
     */


    public static function woocommerce_checkout_order_review()
    {
        global $woocommerce;

        $data['type']  = 6;
        $data['id']    = 0;
        $data['extra'] = '';

        self::add_request($data);
    }
}
