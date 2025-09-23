<?php

namespace EIB2BPRO\Rules;

defined('ABSPATH') || exit;

class Site extends \EIB2BPRO\Rules
{

    public static $already_logged = [];
    public static $already_passed = [];
    public static $applied = [];
    public static $price_rules = [0];
    public static $transport = [];

    public static function parse_users($users = [])
    {
        $passed = false;

        if ('all' === $users['users']) {
            return true;
        }

        if (isset(static::$already_passed['users'][$users['id']])) {
            return static::$already_passed['users'][$users['id']];
        }

        if (!is_user_logged_in() && 'guest' === $users['users']) {
            $passed = true;
        } else {
            if ('all_b2c' === $users['users'] && 'b2c' === \EIB2BPRO\B2b\Site\Main::user('user_type')) {
                $passed =  true;
            } else
            if ('all_b2b' === $users['users'] && 'b2b' === \EIB2BPRO\B2b\Site\Main::user('user_type')) {
                $passed =  true;
            } else
            if ('group' === $users['users']) {
                $users['users_values'] = wp_parse_id_list($users['users_values']);
                if ('in' === $users['users_operator'] && in_array(\EIB2BPRO\B2b\Site\Main::user('group'), $users['users_values'])) {
                    $passed =  true;
                }
                if ('not_in' === $users['users_operator'] && !in_array(\EIB2BPRO\B2b\ite_Main::user('group'), $users['users_values'])) {
                    $passed =  true;
                }
            } else
            if ('user' === $users['users']) {
                $users['users_values'] = wp_parse_id_list($users['users_values']);
                if ('in' === $users['users_operator'] && in_array(\EIB2BPRO\B2b\Site\Main::user('id'), $users['users_values'])) {
                    $passed =  true;
                }
                if ('not_in' === $users['users_operator'] && !in_array(\EIB2BPRO\B2b\Site\Main::user('id'), $users['users_values'])) {
                    $passed =  true;
                }
            }
        }

        if ($passed) {
            static::$already_passed['users'][$users['id']] = $passed;
        }
        return $passed;
    }
    public static function parse_products($rule = [], $product, $operator = 'AND')
    {
        $passed = 0;

        if (!isset($rule['products']) || empty($rule['products'])) {
            return true;
        }

        if (is_int($product)) {
            $product = wc_get_product($product);
        }

        $product_id = $product->get_id();

        foreach ($rule['products'] as $line) {
            // key: product
            if ('product' === $line['key']) {
                $values = wp_parse_id_list($line['values']);
                if (!is_a($product, 'WC_Product_Variation')) {
                    if ('in' === $line['operator'] && in_array($product_id, $values)) {
                        ++$passed;
                    }
                    if ('not_in' === $line['operator'] && !in_array($product_id, $values)) {
                        ++$passed;
                    }
                } else {
                    $parent_id = $product->get_parent_id();
                    if ('in' === $line['operator'] && (in_array($parent_id, $values) || in_array($product_id, $values))) {
                        ++$passed;
                    }
                    if ('not_in' === $line['operator'] && (!in_array($parent_id, $values) && !in_array($product_id, $values))) {
                        ++$passed;
                    }
                }
            }

            if ('category' === $line['key']) {
                $values = wp_parse_id_list($line['values']);;
                if ($product->is_type('variation')) {
                    $compare = array_intersect($values, self::get_product_categories($product->get_parent_id()));
                } else {
                    $compare = array_intersect($values, self::get_product_categories($product_id));
                }

                if ('in' === $line['operator']) {
                    if (count($compare) > 0) {
                        ++$passed;
                    }
                }

                if ('not_in' === $line['operator']) {
                    if (count($compare) === 0) {
                        ++$passed;
                    }
                }
            }
        }

        if ('AND' === $operator) {
            return $passed === count($rule['products']) ? true : false;
        } else {
            return 0 < $passed ? true : false;
        }
    }

    public static function conditions($rule, $extra = false)
    {
        $passed = 0;

        if (!isset($extra['pass_check']) && isset(static::$already_passed['conditions'][$rule['id']])) {
            return static::$already_passed['conditions'][$rule['id']];
        }

        if (!isset($rule['conditions']) || empty($rule['conditions'])) {
            return true;
        }

        foreach ($rule['conditions'] as $line) {
            if ('cart_total_value' === $line['key']) {
                $cart_total = \WC()->cart->cart_contents_total;
                switch ($line['operator']) {
                    case 'more_than':
                        if (floatval($line['values']) <= floatval($cart_total)) {
                            ++$passed;
                        }
                        break;
                    case 'less_than':
                        if (floatval($line['values']) >= floatval($cart_total)) {
                            ++$passed;
                        }
                        break;
                    case 'equals';
                        if (floatval($line['values']) === floatval($cart_total)) {
                            ++$passed;
                        }
                        break;
                }
            } elseif ('cart_total_qty' === $line['key']) {
                $cart_total_qty = \WC()->cart->get_cart_contents_count();
                switch ($line['operator']) {
                    case 'more_than':
                        if (floatval($line['values']) <= floatval($cart_total_qty)) {
                            ++$passed;
                        }
                        break;
                    case 'less_than':
                        if (floatval($line['values']) >= floatval($cart_total_qty)) {
                            ++$passed;
                        }
                        break;
                    case 'equals';
                        if (floatval($line['values']) === floatval($cart_total_qty)) {
                            ++$passed;
                        }
                        break;
                }
            } elseif ('time' === $line['key']) {
                $line['values'] = str_replace(':', '', $line['values']);
                if ('from' === $line['operator'] &&  $line['values'] <= eib2bpro_strtotime('now', 'Hi')) {
                    ++$passed;
                } elseif ('to' === $line['operator'] &&  $line['values'] >= eib2bpro_strtotime('now', 'Hi')) {
                    ++$passed;
                } elseif ('equals' === $line['operator'] &&  $line['values'] === eib2bpro_strtotime('now', 'Hi')) {
                    ++$passed;
                }
            } elseif ('date' === $line['key']) {
                $line['values'] = str_replace('-', '', $line['values']);
                if ('from' === $line['operator'] &&  $line['values'] <= eib2bpro_strtotime('now', 'Ymd')) {
                    ++$passed;
                } elseif ('to' === $line['operator'] &&  $line['values'] >= eib2bpro_strtotime('now', 'Ymd')) {
                    ++$passed;
                } elseif ('equals' === $line['operator'] &&  $line['values'] === eib2bpro_strtotime('now', 'Ymd')) {
                    ++$passed;
                }
            } elseif ('date_time' === $line['key']) {
                if ('from' === $line['operator'] &&  eib2bpro_strtotime($line['values'], 'U') <= eib2bpro_strtotime('now', 'U')) {
                    ++$passed;
                } elseif ('to' === $line['operator'] &&  eib2bpro_strtotime($line['values'], 'U') >= eib2bpro_strtotime('now', 'U')) {
                    ++$passed;
                } elseif ('equals' === $line['operator'] &&  eib2bpro_strtotime($line['values'], 'Y-m-d H:i') === eib2bpro_strtotime('now', 'Y-m-d H:i')) {
                    ++$passed;
                }
            }
        }

        $passed = $passed === count($rule['conditions']) ? true : false;

        if ($passed) {
            static::$already_passed['conditions'][$rule['id']] = $passed;
        }

        return $passed;
    }

    public static function hide_price($price, $product)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['hide_price'])) {
            return $price;
        }

        $rules = $map['rules']['hide_price'];
        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::parse_products($rule, $product)) {
                    if (self::conditions($rule)) {
                        self::log($rule, false);
                        return '';
                    }
                }
            }
        }
        return $price;
    }

    public static function hide_price_disable_purchasable($purchasable, $product)
    {
        $status = self::hide_price($product->get_price(), $product);

        if ('' === $status) {
            return false;
        }

        return $purchasable;
    }

    public static function change_price($sale_price, $product, $tier = false)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['change_price'])) {
            return $sale_price;
        }

        if (self::is_an_offer($product)) {
            return $sale_price;
        }

        if ((float)$sale_price <= 0 || (apply_filters('b2bpro_rules_change_price_ignore_sale_price', false) && !$tier)) {
            $sale_price = $product->get_regular_price();
        }

        if (apply_filters('b2bpro_enable_before_calculate_totals_count', false)) {
            if (did_action('woocommerce_before_calculate_totals') >= intval(apply_filters('b2bpro_enable_before_calculate_totals_count', 2))) {
                return;
            }
        }

        $prices = array($sale_price);

        $rules = $map['rules']['change_price'];
        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::parse_products($rule, $product)) {
                    if (self::conditions($rule)) {

                        self::log($rule, false);
                        self::$price_rules[$rule_id] = $rule_id;

                        if (self::is_an_offer($product)) {
                            continue;
                        }

                        if (empty($sale_price)) {
                            $sale_price_new = $product->get_regular_price();
                        } else {
                            $sale_price_new = $sale_price;
                        }

                        switch ($rule['change_price_type']) {
                            case 'fixed_discount':
                                $sale_price_new = (float)$sale_price - (float)$rule['change_price_values'];
                                break;
                            case 'percentage_discount':
                                $sale_price_new = (float)$sale_price - ((float)$sale_price * (float)$rule['change_price_values'] / 100);
                                break;
                            case 'fixed_price':
                                $sale_price_new = (float)$rule['change_price_values'];
                                break;
                        }
                        if (0 < (float)$sale_price_new) {
                            $prices[] = $sale_price_new;
                        } else {
                            $prices[] = 0;
                        }
                    }
                }
            }
        }

        $sale_price = min($prices);

        if (0 > $sale_price) {
            $sale_price = 0;
        }

        return $sale_price;
    }


    public static function change_price_display($price_html, $product)
    {

        if ($product->is_type('variable')) {
            $prices = $product->get_variation_prices(true);

            if (empty($prices['price'])) {
                return apply_filters('woocommerce_variable_empty_price_html', '', $product);
            }

            $min_price = $max_price = floatval(max(array_values($prices['price'])));

            foreach ($prices['price'] as $price) {
                if ((float)$price >= 0 && (float)$price <= $min_price) {
                    $min_price = (float)$price;
                }
            }

            if ($min_price === $max_price) {
                return wc_price($max_price);
            }
            return wc_format_price_range($min_price, $max_price);
        }

        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['change_price'])) {
            return $price_html;
        }

        $rules = $map['rules']['change_price'];
        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::parse_products($rule, $product)) {
                    if (self::conditions($rule)) {

                        if ($product->is_type('variable')) {

                            $prices = $product->get_variation_prices(true);

                            if (empty($prices['price'])) {
                                return apply_filters('woocommerce_variable_empty_price_html', '', $product);
                            }

                            $min_price = $max_price = max(array_values($prices['price']));

                            foreach ($prices['price'] as $price) {
                                if ((float)$price >= 0 && (float)$price <= $min_price) {
                                    $min_price = (float)$price;
                                }
                            }

                            if ($min_price === $max_price) {
                                return wc_price($max_price);
                            }

                            return wc_format_price_range($min_price, $max_price);
                        } else {
                            $price_html = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price())), wc_get_price_to_display($product, array('price' => $product->get_sale_price()))) . $product->get_price_suffix();
                        }
                    }
                }
            }
        }

        return $price_html;
    }

    public static function change_price_display_in_cart_item($price, $cart_item, $cart_item_key)
    {

        if (isset($cart_item['variation_id']) && intval($cart_item['variation_id']) !== 0) {
            $product_id = $cart_item['variation_id'];
            $product = new \WC_Product_Variation($product_id);
        } else {
            $product_id = $cart_item['product_id'];
            $product = new \WC_Product($product_id);
        }

        if (self::is_an_offer($product)) {
            return $price;
        }

        return self::change_price_display(wc_price($product->get_price()), $product);
    }

    public static function change_price_variation_hash($hash)
    {

        if (1 === eib2bpro_option('b2b_clear_product_caches', 0)) {
            \WC_Cache_Helper::get_transient_version('product', true);
            eib2bpro_option('b2b_clear_product_caches', 0, 'set');
        }

        $hash[] = get_current_user_id();
        return $hash;
    }

    public static function change_price_regular($regular_price, $product)
    {
        return $regular_price;
    }

    /* Add quote button */

    public static function add_quote_button()
    {
        global $product;

        if (!is_object($product)) {
            return;
        }
        $map = eib2bpro_option('rules_map', []);

        $rules = $map['rules']['add_quote_button'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    if (self::parse_products($rule, $product)) {
                        if (2 > intval($rule['add_quote_button_remove_atc'])) {
                            echo '<button type="button" class="eib2bpro-b2b-cart-request-a-quote-button button alt" value="' . esc_attr($product->get_id()) . '">' . esc_html__('Request a quote', 'eib2bpro') . '</button>';
                        }
                    }
                }
            }
        }
    }

    public static function request_a_quote_button($button)
    {
        global $product;
        if (!is_object($product)) {
            return;
        }
        $map = eib2bpro_option('rules_map', []);

        $rules = $map['rules']['add_quote_button'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    if (self::parse_products($rule, $product)) {
                        if (1 < intval($rule['add_quote_button_remove_atc'])) {
                            return esc_attr__('Request a quote', 'eib2bpro');
                        }
                    }
                }
            }

            return $button;
        }
    }
    public static function remove_add_to_cart_button()
    {
        global $product;
        if (!is_object($product)) {
            return;
        }
        $map = eib2bpro_option('rules_map', []);

        $rules = $map['rules']['add_quote_button'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    if (self::parse_products($rule, $product)) {
                        if (1 === intval($rule['add_quote_button_remove_atc'])) {
                            if (1 === apply_filters('b2bpro_remove_add_to_cart_button_version', 2)) {
                                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
                                add_filter('woocommerce_is_purchasable', '__return_false');
                                add_action('woocommerce_product_meta_start', function () {
                                    global $product;
                                    echo '<button type="button" class="eib2bpro-b2b-cart-request-a-quote-button button alt" value="' . esc_attr($product->get_id()) . '">' . esc_html__('Request a quote', 'eib2bpro') . '</button><br><br>';
                                }, 100);
                            } else {
                                echo apply_filters('b2bpro_rule_aqb_atc_styles', '<style>input[name="quantity"], .quantity, .single_add_to_cart_button, .button.product_type_simple.add_to_cart_button, .ajax_add_to_cart{display:none!important;}</style>');
                            }
                        } elseif (2 === intval($rule['add_quote_button_remove_atc'])) {
                            echo "<input type='hidden' name='eib2bpro_enable_quote_popup' class='eib2bpro_enable_quote_popup' value='1'>";
                            echo  apply_filters('b2bpro_rule_aqb_qty_styles', '<style>input[name="quantity"], .quantity{display:none!important;}</style>');
                        } elseif (0 === intval($rule['add_quote_button_remove_atc'])) {
                            if (!$product->is_purchasable()) {
                                add_action('woocommerce_product_meta_start', function () {
                                    global $product;
                                    echo '<button type="button" class="eib2bpro-b2b-cart-request-a-quote-button button alt" value="' . esc_attr($product->get_id()) . '">' . esc_html__('Request a quote', 'eib2bpro') . '</button><br><br>';
                                }, 1);
                            }
                        }
                    }
                }
            }
        }
    }

    /* Minmax*/

    public static function minmax_order()
    {

        $passed = 1;
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['min_order']) && !isset($map['rules']['max_order'])) {
            return;
        }

        $min_rules = isset($map['rules']['min_order']) ? $map['rules']['min_order'] : array();
        $max_rules = isset($map['rules']['max_order']) ? $map['rules']['max_order'] : array();

        $rules = array_merge($min_rules, $max_rules);

        foreach ($rules as $rule_id => $rule) {

            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {

                    $required = $rule['minmax_values'];

                    // apply to all cart
                    if (empty($rule['products'])) {

                        $count = 0;
                        $total = 0;

                        $cart = \WC()->cart;
                        if (is_object($cart)) {

                            foreach ($cart->get_cart() as $cart_item) {
                                $price = $cart_item['data']->get_price();
                                $qty = $cart_item["quantity"];
                                $line = $cart_item["line_total"] + $cart_item['line_tax'];
                                $count += $qty;
                                $total += $line;
                            }
                        }

                        if (0 < $count) {
                            if ('qty' === $rule['minmax_type']) {
                                if ('min_order' === $rule['type']) {
                                    if ($count < intval($required)) {
                                        $passed = 0;
                                        self::minmax_error(sprintf(
                                            esc_html__('The quantity of products in your cart must be at least %d. There are currently %d products.', 'eib2bpro'),
                                            $required,
                                            $count
                                        ));
                                    }
                                } elseif ('max_order' === $rule['type']) {
                                    if ($count > intval($required)) {
                                        $passed = 0;
                                        self::minmax_error(sprintf(
                                            esc_html__('The quantity of products in your cart must be at most %d. There are currently %d products.', 'eib2bpro'),
                                            $required,
                                            $count
                                        ));
                                    }
                                }
                            } else if ('value' === $rule['minmax_type']) {
                                if ('min_order' === $rule['type']) {
                                    if ($total < floatval($required)) {
                                        $passed = 0;
                                        self::minmax_error(sprintf(
                                            esc_html__('The total value of your cart must be at least %s', 'eib2bpro'),
                                            wc_price($required)
                                        ));
                                    }
                                } elseif ('max_order' === $rule['type']) {
                                    if ($total > floatval($required)) {
                                        $passed = 0;
                                        self::minmax_error(sprintf(
                                            esc_html__('The total value of your cart should not exceed %s', 'eib2bpro'),
                                            wc_price($required)
                                        ));
                                    }
                                }
                            }
                        }
                    } else {
                        foreach ($rule['products'] as $products_options) {
                            if ('product' === $products_options['key']) {
                                $products = wp_parse_id_list($products_options['values']);
                                $products[] = eib2bpro_option('b2b_offer_default_id', -99999999);
                                $cart = \WC()->cart;

                                $count = 0;
                                $total = 0;
                                $titles = [];
                                if (is_object($cart)) {
                                    if ('not_in' === $products_options['operator']) {
                                        foreach ($cart->get_cart() as $cart_item) {
                                            if (!in_array($cart_item['product_id'], $products) && !in_array($cart_item['variation_id'], $products)) {
                                                $qty = $cart_item["quantity"];
                                                $line = $cart_item["line_total"] + $cart_item['line_tax'];
                                                $count += $qty;
                                                $total += $line;
                                                $titles[] =  $cart_item['data']->get_title();
                                            }
                                        }
                                    } else {
                                        foreach ($products as $product) {
                                            foreach ($cart->get_cart() as $cart_item) {
                                                if ($cart_item['product_id'] === $product || $cart_item['variation_id'] === $product) {
                                                    $qty = $cart_item["quantity"];
                                                    $line = $cart_item["line_total"] + $cart_item['line_tax'];
                                                    $count += $qty;
                                                    $total += $line;
                                                    $titles[] =  $cart_item['data']->get_title();
                                                }
                                            }
                                        }
                                    }

                                    if (0 < $count) {
                                        if ('qty' === $rule['minmax_type']) {
                                            if ('min_order' === $rule['type']) {
                                                if ($count < intval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The quantity of products (%s) in your cart is %d and must be at least %d', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        $count,
                                                        $required
                                                    ));
                                                }
                                            } elseif ('max_order' === $rule['type']) {
                                                if ($count > intval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The quantity of products (%s) in your cart is %d and must not be more than %d', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        $count,
                                                        $required
                                                    ));
                                                }
                                            }
                                        } else if ('value' === $rule['minmax_type']) {
                                            if ('min_order' === $rule['type']) {
                                                if ($total < floatval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The value of the items (%s) in your cart is %s and must be at least %s', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        wc_price($total),
                                                        wc_price($required)
                                                    ));
                                                }
                                            } elseif ('max_order' === $rule['type']) {
                                                if ($total > floatval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The value of the items (%s) in your cart is %s and must not be more than %s', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        wc_price($total),
                                                        wc_price($required)
                                                    ));
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ('category' === $products_options['key']) {
                                $products = wp_parse_id_list($products_options['values']);
                                $cart = \WC()->cart;

                                $count = 0;
                                $total = 0;
                                $titles = [];

                                if (is_object($cart)) {
                                    if ('not_in' === $products_options['operator']) {
                                        foreach ($cart->get_cart() as $cart_item) {
                                            $category_pass = true;
                                            $categories = self::get_product_categories($cart_item['product_id']);
                                            foreach ($products as $product) {
                                                if (in_array($product, $categories)) {
                                                    $category_pass = false;
                                                }
                                            }
                                            if ($category_pass) {
                                                $qty = $cart_item["quantity"];
                                                $line = $cart_item["line_total"] + $cart_item['line_tax'];
                                                $count += $qty;
                                                $total += $line;
                                                if (is_array($categories) and !empty(current($categories))) {
                                                    $titles[$product] =  get_term(current($categories))->name;
                                                } else {
                                                    $titles[$product] = '';
                                                }
                                            }
                                        }
                                    } else {
                                        foreach ($products as $product) {
                                            foreach ($cart->get_cart() as $cart_item) {
                                                $categories = self::get_product_categories($cart_item['product_id']);
                                                if (in_array($product, $categories)) {
                                                    $qty = $cart_item["quantity"];
                                                    $line = $cart_item["line_total"] + $cart_item['line_tax'];
                                                    $count += $qty;
                                                    $total += $line;
                                                    $titles[$product] =  get_term($product)->name;
                                                }
                                            }
                                        }
                                    }

                                    if (0 < $count) {
                                        if ('qty' === $rule['minmax_type']) {
                                            if ('min_order' === $rule['type']) {
                                                if ($count < intval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The total quantity of products in the %s categories in your cart is %s and must be at least %s', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        $count,
                                                        $required
                                                    ));
                                                }
                                            } elseif ('max_order' === $rule['type']) {
                                                if ($count > intval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The total quantity of products in the %s categories in your cart is %s and should not be more than %s', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        $count,
                                                        $required
                                                    ));
                                                }
                                            }
                                        } else if ('value' === $rule['minmax_type']) {
                                            if ('min_order' === $rule['type']) {
                                                if ($total < floatval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The products in the %s categories in your cart have a value of %s and must be at least %s ', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        wc_price($total),
                                                        wc_price($required)
                                                    ));
                                                }
                                            } elseif ('max_order' === $rule['type']) {
                                                if ($total > floatval($required)) {
                                                    $passed = 0;
                                                    self::minmax_error(sprintf(
                                                        esc_html__('The total value of the products in the %s categories in your cart is %s and must not be more than %s', 'eib2bpro'),
                                                        implode(', ', $titles),
                                                        wc_price($total),
                                                        wc_price($required)
                                                    ));
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (0 === $passed) {
            remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
            self::log($rule, true);
        }
    }

    public static function minmax_error($error = '')
    {
        if (is_cart()) {
            wc_print_notice(
                $error,
                'error'
            );
        } else {
            wc_add_notice(
                $error,
                'error'
            );
        }
    }

    public static function minmax_order_qty_variation($variation_get_max_purchase_quantity,  $instance,  $variation)
    {
        return self::minmax_order_qty($variation_get_max_purchase_quantity, $variation);
    }

    /* Free Shipping */

    public static function free_shipping($is_available, $package, $shipping_method)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['free_shipping'])) {
            return $is_available;
        }

        $rules = $map['rules']['free_shipping'];
        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    $cart = \WC()->cart;
                    if (is_object($cart)) {
                        foreach ($cart->get_cart() as $cart_item) {
                            if (0 < $cart_item['variation_id']) {
                                if (true === self::parse_products($rule, $cart_item['variation_id'], 'OR')) {
                                    self::log($rule, false);
                                    return true;
                                }
                            } else {
                                if (true === self::parse_products($rule, $cart_item['product_id'], 'OR')) {
                                    self::log($rule, true);
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /* Step */

    public static function step()
    {
        $passed = 1;
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['step'])) {
            return;
        }

        $rules = $map['rules']['step'];

        foreach ($rules as $rule_id => $rule) {

            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {

                    $required = intval($rule['step_values']);

                    // apply to all cart
                    if (empty($rule['products'])) {

                        $count = 0;
                        $cart = \WC()->cart;
                        if (is_object($cart)) {
                            foreach ($cart->get_cart() as $cart_item) {
                                $count += $cart_item["quantity"];
                            }
                        }

                        if (0 < $count) {
                            if ($count % $required !== 0) {
                                $passed = 0;
                                self::minmax_error(sprintf(
                                    esc_html__('The quantity of products must be %d and its multiples.', 'eib2bpro'),
                                    $required
                                ));
                            }
                        }
                    } else {
                        foreach ($rule['products'] as $products_options) {
                            if ('product' === $products_options['key']) {
                                $products = wp_parse_id_list($products_options['values']);
                                $cart = \WC()->cart;
                                if (is_object($cart)) {
                                    foreach ($products as $product) {
                                        $count = 0;
                                        foreach ($cart->get_cart() as $cart_item) {
                                            if ($cart_item['product_id'] === $product || $cart_item['variation_id'] === $product) {
                                                $qty = $cart_item["quantity"];
                                                $count += $qty;
                                            }
                                        }

                                        if (0 < $count) {
                                            if ($count % $required !== 0) {
                                                $passed = 0;
                                                self::minmax_error(sprintf(
                                                    esc_html__('The quantity of products (%s) must be %s and its multiples.', 'eib2bpro'),
                                                    get_the_title($product),
                                                    $required
                                                ));
                                            }
                                        }
                                    }
                                }
                            } elseif ('category' === $products_options['key']) {
                                $products = wp_parse_id_list($products_options['values']);
                                $cart = \WC()->cart;
                                if (is_object($cart)) {
                                    foreach ($products as $product) {
                                        $count = 0;
                                        foreach ($cart->get_cart() as $cart_item) {
                                            $categories = self::get_product_categories($cart_item['product_id']);
                                            if (in_array($product, $categories)) {
                                                $qty = $cart_item["quantity"];
                                                $count += $qty;
                                            }
                                        }

                                        if (0 < $count) {

                                            if ($count % $required !== 0) {
                                                $passed = 0;
                                                self::minmax_error(sprintf(
                                                    esc_html__('The quantity of products (%s) must be %s and its multiples.', 'eib2bpro'),
                                                    get_term($product)->name,
                                                    $required
                                                ));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (0 === $passed) {
                remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
                self::log($rule, true);
            }
        }
    }

    public static function step_qty($args, $product)
    {

        if (!method_exists($product, 'get_id')) {
            return $args;
        }

        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['step'])) {
            return $args;
        }

        $rules = $map['rules']['step'];

        foreach ($rules as $rule_id => $rule) {

            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    if (self::parse_products($rule, $product)) {
                        $step = $rule['step_values'];
                        if (!is_cart()) {
                            $args['input_value'] = $step;
                            $args['min_qty'] = $step;
                            $args['min_value'] = $step;
                            $args['step'] = $step;
                        } else {
                            $args['min_qty'] = $step;
                            $args['min_value'] = $step;
                            $args['step'] = $step;
                        }

                        if (isset($rule['step_min'])) {
                            if (0 < intval($rule['step_min'])) {
                                $args['min_qty'] = $rule['step_min'];
                                $args['min_value'] = $rule['step_min'];

                                if ($step < intval($rule['step_min']) && !is_cart()) {
                                    $args['input_value'] = $rule['step_min'];
                                }
                            }
                        }

                        if (isset($rule['step_max'])) {
                            if (0 < intval($rule['step_max'])) {
                                $args['max_qty'] = $rule['step_max'];
                                $args['max_value'] = $rule['step_max'];
                            }
                        }
                    }
                }
            }
        }

        return $args;
    }

    public static function step_qty_variation($args, $instance, $product)
    {
        return self::step_qty($args, $product);
    }

    public static function step_qty_number($quantity, $product_id)
    {
        $product = wc_get_product($product_id);
        if ($product->is_type('variable')) {
            return $quantity;
        }

        $args = self::step_qty(['step' => $quantity], $product);
        return $args['step'];
    }

    /* Payment method discount */

    public static function payment_method_discount($cart)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['payment_discount']) || !is_checkout()) {
            return;
        }

        $discount = 0;
        $rules = $map['rules']['payment_discount'];

        foreach ($rules as $rule_id => $rule) {

            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {

                    $gateway = \WC()->session->get('chosen_payment_method');
                    $values = wp_parse_list($rule['payment_discount_values']);
                    if (
                        ('in' === $rule['payment_minmax_operator'] && in_array($gateway, $values)) ||
                        ('not_in' === $rule['payment_minmax_operator'] && !in_array($gateway, $values))
                    ) {
                        switch ($rule['payment_discount_type']) {
                            case 'decrease_percentage':
                                $total = \WC()->cart->get_subtotal();
                                $discount = $total * floatval($rule['payment_discount_amount']) / 100;
                                break;
                            case 'decrease_fixed':
                                $discount = floatval($rule['payment_discount_amount']);
                                break;
                            case 'increase_percentage':
                                $total = \WC()->cart->get_subtotal();
                                $discount = ($total * floatval($rule['payment_discount_amount']) / 100) * -1;
                                break;
                            case 'increase_fixed':
                                $discount = floatval($rule['payment_discount_amount']) * -1;
                                break;
                        }
                        if (0 !== $discount) {
                            if (is_object(WC()->cart)) {
                                $title = esc_html__('Payment method', 'eib2bpro');
                                $payment_methods = \WC()->payment_gateways->payment_gateways();
                                foreach ($payment_methods as $payment_method) {
                                    if ($payment_method->id === $gateway) {
                                        $title = $payment_method->title;
                                    }
                                }
                                self::log($rule, false);

                                if (0 < $discount) {
                                    $cart->add_fee(sprintf(esc_html__('%s discount', 'eib2bpro'), $title), -$discount, apply_filters('eib2bpro_rule_add_fee_taxable', false, $title));
                                } else {
                                    $cart->add_fee(sprintf(esc_html__('%s fee', 'eib2bpro'), $title), -$discount, apply_filters('eib2bpro_rule_add_fee_taxable', false, $title));
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    public static function remove_payment_method_discount_change_titles($order)
    {
        remove_filter('woocommerce_gateway_title', '\EIB2BPRO\Rules\Site::payment_method_discount_change_titles', 500);
    }

    public static function payment_method_discount_change_titles($title, $payment_id)
    {

        if (!is_checkout()) return $title;

        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['payment_discount'])) {
            return $title;
        }

        $rules = $map['rules']['payment_discount'];

        foreach ($rules as $rule_id => $rule) {

            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    $values = wp_parse_list($rule['payment_discount_values']);
                    foreach ($values as $value) {
                        $discount = 0;
                        switch ($rule['payment_discount_type']) {
                            case 'decrease_percentage':
                                $total = \WC()->cart->get_subtotal();
                                $discount = $total * floatval($rule['payment_discount_amount']) / 100;
                                break;
                            case 'decrease_fixed':
                                $discount = floatval($rule['payment_discount_amount']);
                                break;
                            case 'increase_percentage':
                                $total = \WC()->cart->get_subtotal();
                                $discount = ($total * floatval($rule['payment_discount_amount']) / 100) * -1;
                                break;
                            case 'increase_fixed':
                                $discount = floatval($rule['payment_discount_amount']) * -1;
                                break;
                        }

                        if (0 !== $discount) {
                            if (is_object(WC()->cart)) {
                                if ($payment_id === $value) {
                                    $title = $title  . " <span class='eib2bpro-b2b-payment-method-fee'>" . ((0 < $discount) ? '-' : '+') . wc_price((0 < floatval($discount) ? floatval($discount) : -1 * floatval($discount))) . '</span>';
                                }
                            }
                        }
                    }
                }
            }
        }

        return $title;
    }

    /* Payment method minmax */

    public static function payment_method_minmax($gateways)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['payment_minmax'])) {
            return $gateways;
        }

        $rules = $map['rules']['payment_minmax'];

        foreach ($rules as $rule_id => $rule) {

            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    $values = wp_parse_list($rule['payment_minmax_values']);
                    foreach ($gateways as $gateway => $gateway_value) {
                        if (
                            ('in' === $rule['payment_minmax_operator'] && in_array($gateway, $values)) ||
                            ('not_in' === $rule['payment_minmax_operator'] && !in_array($gateway, $values))
                        ) {
                            if (is_object(\WC()->cart)) {
                                $total = \WC()->cart->total;
                                if (
                                    (0 < floatval($rule['payment_minmax_min']) && floatval($total) < floatval($rule['payment_minmax_min'])) ||
                                    (0 < floatval($rule['payment_minmax_max']) && floatval($total) > floatval($rule['payment_minmax_max']))
                                ) {
                                    unset($gateways[$gateway]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $gateways;
    }

    /* Add fee */

    public static function add_fee($cart)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['add_fee'])) {
            return;
        }

        $fee = 0;
        $rules = $map['rules']['add_fee'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    switch ($rule['add_fee_type']) {
                        case 'percentage':
                            $total = \WC()->cart->get_subtotal();
                            $shipping = \WC()->cart->get_shipping_total();
                            $include_shipping = apply_filters('eib2bpro_rule_add_fee_include_shipping', false);
                            if ($include_shipping) {
                                $fee = round(($total + $shipping) * $rule['add_fee_values'] / 100, 5);
                            } else {
                                $fee = round($total * $rule['add_fee_values'] / 100, 5);
                            }
                            break;
                        case 'fixed':
                            $fee = $rule['add_fee_values'];
                            break;
                    }
                    if (0 !== $fee) {
                        if (is_object(WC()->cart)) {
                            $cart->add_fee(esc_html($rule['add_fee_name']), $fee, apply_filters('eib2bpro_rule_add_fee_taxable', false, $rule['add_fee_name']));
                        }
                    }
                }
            }
        }
    }

    /* Cart discount */

    public static function cart_discount($cart)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['cart_discount'])) {
            return;
        }

        $fee = 0;
        $rules = $map['rules']['cart_discount'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule)) {
                    switch ($rule['add_fee_type']) {
                        case 'percentage':
                            $total = \WC()->cart->get_subtotal();
                            $shipping = \WC()->cart->get_shipping_total();
                            $include_shipping = apply_filters('eib2bpro_rule_add_fee_include_shipping', false);
                            if ($include_shipping) {
                                $fee = round(($total + $shipping) * $rule['add_fee_values'] / 100, 5);
                            } else {
                                $fee = round($total * $rule['add_fee_values'] / 100, 5);
                            }
                            break;
                        case 'fixed':
                            $fee = $rule['add_fee_values'];
                            break;
                    }
                    if (0 !== $fee) {
                        if (is_object(WC()->cart)) {
                            $cart->add_fee(esc_html($rule['add_fee_name']), $fee * -1, apply_filters('eib2bpro_rule_add_fee_taxable', false, $rule['add_fee_name']));
                        }
                    }
                }
            }
        }
    }

    /* Tax Exemption (User) */
    public static function tax_exemption_custom()
    {

        $user_tax_exemption = get_user_meta(\EIB2BPRO\B2B\Site\Main::user('id'), '_eib2bpro_tax_exemption', true);

        if (is_a(\WC()->customer, 'WC_Customer')) {
            if ('yes' === $user_tax_exemption) {
                \WC()->customer->set_is_vat_exempt(true);
            } elseif ('no' === $user_tax_exemption) {
                \WC()->customer->set_is_vat_exempt(false);
            }
        }
    }
    public static function tax_exemption()
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['tax_exemption'])) {
            if (apply_filters('eib2bpro_default_tax_exemption_false', false)) {
                if (is_a(\WC()->customer, 'WC_Customer') &&  \WC()->customer->is_vat_exempt()) {
                    \WC()->customer->set_is_vat_exempt(false);
                }
            }

            return;
        }

        if (is_a(\WC()->customer, 'WC_Customer') && \WC()->customer->is_vat_exempt()) {
            \WC()->customer->set_is_vat_exempt(false);
        }

        $rules = $map['rules']['tax_exemption'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {

                global $woocommerce;
                $customer = $woocommerce->customer;

                if (is_a($customer, 'WC_Customer')) {
                    $tax_setting = get_option('woocommerce_tax_based_on');
                    if ($tax_setting === 'shipping') {
                        $country = \WC()->customer->get_shipping_country();
                    } else {
                        $country = \WC()->customer->get_billing_country();
                    }
                    if (1 === intval($rule['tax_exemption_vies_validation'])) {
                        if (0 === intval(get_user_meta(\EIB2BPRO\B2B\Site\Main::user('id'), '_eib2bpro_vies_validated', true))) {
                            return;
                        }
                    }

                    $countries = wp_parse_list($rule['tax_exemption_country']);
                    if (in_array($country, $countries) || in_array('0', $countries)) {
                        \WC()->customer->set_is_vat_exempt(true);
                        if (apply_filters('eib2bpro_change_price_suffix', false, get_current_user_id())) {
                            add_filter('woocommerce_get_price_suffix', '\EIB2BPRO\Rules\Site::eib2bpro_change_price_suffix_ex_vat', 99999, 4);
                        }
                    }
                }
            }
        }
    }

    public static function eib2bpro_change_price_suffix_ex_vat($html, $product, $price, $qty)
    {
        return '<small class="woocommerce-price-suffix"> ' . apply_filters('eib2bpro_change_price_suffix_ex_vat', esc_html__('ex. VAT', 'eib2bpro')) . '</small>';
    }

    public static function tax_exemption_prices($option)
    {

        if (\WC()->customer->is_vat_exempt()) {
            return 'excl';
        }

        return $option;
    }

    /* Tax Exemption (Product) */

    public static function tax_exemption_product($tax_class, $product)
    {
        $map = eib2bpro_option('rules_map', []);

        if (!isset($map['rules']['tax_exemption_product'])) {
            return $tax_class;
        }

        $rules = $map['rules']['tax_exemption_product'];

        foreach ($rules as $rule_id => $rule) {
            if (self::parse_users($rule)) {
                if (self::conditions($rule, ['pass_check' => true])) {
                    if (self::parse_products($rule, $product)) {

                        global $woocommerce;
                        $customer = $woocommerce->customer;

                        if (is_a($customer, 'WC_Customer')) {
                            $tax_setting = get_option('woocommerce_tax_based_on');
                            if ($tax_setting === 'shipping') {
                                $country = \WC()->customer->get_shipping_country();
                            } else {
                                $country = \WC()->customer->get_billing_country();
                            }

                            if (1 === intval($rule['tax_exemption_vies_validation'])) {
                                if (0 === intval(get_user_meta(\EIB2BPRO\B2B\Site\Main::user('id'), '_eib2bpro_vies_validated', true))) {
                                    return $tax_class;
                                }
                            }
                            $countries = wp_parse_list($rule['tax_exemption_country']);
                            if (in_array($country, $countries) || in_array('0', $countries)) {
                                $tax_class = 'Zero Rate';
                            }
                        }
                    }
                }
            }
        }
        return $tax_class;
    }

    public static function change_price_html($html, $product)
    {
        if (is_product()) {
            if ($product->is_type('variation')) {
                return $html;
            }

            $map = eib2bpro_option('rules_map', []);

            if (!isset($map['rules']['change_price_html'])) {
                return $html;
            }

            $rules = $map['rules']['change_price_html'];

            foreach ($rules as $rule_id => $rule) {
                if (self::parse_users($rule)) {
                    if (self::conditions($rule)) {
                        if (self::parse_products($rule, $product)) {
                            $html = "";
                            $text = $rule['change_price_html'];
                            $lines = explode(PHP_EOL, $text);
                            foreach ($lines as $line) {
                                $matches = [];
                                preg_match_all("/\[[^\]]*\]/", $line, $matches);
                                if (isset($matches[0][0])) {
                                    $type = str_replace(['[', ']'], '', $matches[0][0]);
                                    $price_return = do_shortcode('[b2bpro_price type=' . $type . ']');
                                    if (!empty($price_return)) {
                                        $html .= str_replace($matches[0][0], $price_return, $line);
                                    }
                                } else {
                                    $html .= $line;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $html;
    }


    /* Helpers */

    public static function add_rule_note_to_order($order_id)
    {

        if (!empty(static::$applied)) {
            $order = new \WC_Order($order_id);

            $note = esc_html__("Rules applied", 'eib2bpro');

            foreach (static::$applied as $apply) {
                $note .= $apply . '<br>';
            }
            $order->add_order_note($note);
        }
    }
    public static function get_product_categories($product_id)
    {

        $all = $categories = wc_get_product_term_ids($product_id, 'product_cat');

        foreach ($categories as $c) {
            $all = array_merge($all, get_ancestors($c, 'product_cat'));
        }
        return array_filter(array_unique($all));
    }

    public static function log($rule, $save = false)
    {
        $rule_id = $rule['id'];
        $save = false;
        $title = (isset($rule['title']) ? $rule['title'] : '');

        if (!isset(static::$already_logged[$rule_id])) {
            static::$already_logged[$rule_id] = 1;

            static::$applied[$rule_id]  = $title . ' (#' . $rule_id . ')';

            if ($save) {
                $today = eib2bpro_strtotime('now', 'Ymd');
                $logs = (array)get_post_meta($rule_id, 'eib2bpro_rule_stats', true);

                if (isset($logs[$today])) {
                    $logs[$today] = intval($logs[$today]) + 1;
                } else {
                    $logs[$today] = 1;
                }
            }
        }
    }

    public static function refresh_checkout_on_payment_methods_change()
    {
?>
        <script type="text/javascript">
            (function($) {
                "use strict";
                $('form.checkout').on('change', 'input[name^="payment_method"]', function() {
                    $('body').trigger('update_checkout');
                });
            })(jQuery);
        </script>
<?php
    }

    public static function is_an_offer($product)
    {
        $offer_id = intval(eib2bpro_option('b2b_offer_default_id', 0));

        if (!is_int($product)) {
            $product_id = intval($product->get_id());
        } else {
            $product_id = intval($product);
        }

        if ($offer_id === $product_id) {
            return true;
        }

        return false;
    }
}
