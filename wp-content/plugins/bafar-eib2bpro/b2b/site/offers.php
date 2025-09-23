<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Offers
{
    public static function content()
    {
        echo do_shortcode('[b2bpro_offers]');
    }

    public static function shortcode($atts)
    {
        global $wp_query;

        $params = shortcode_atts(array(
            'always_include_tax' => 'false',
            'add_to_cart' => esc_html__('Add to cart', 'eib2bpro')
        ), $atts);


        ob_start();

        $last_seen = intval(get_user_meta(get_current_user_id(), '_eib2bpro_last_offer', true));

        $current_page = get_query_var(eib2bpro_option('b2b_endpoints_offers', 'offers')) ? intval(get_query_var(eib2bpro_option('b2b_endpoints_offers', 'offers'))) : 1;

        $offers = new \WP_Query(array(
            'post_type' => 'eib2bpro_offers',
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'eib2bpro_group_' . Main::user('group'),
                    'value' => '1',
                ),
                array(
                    'key' => 'eib2bpro_user_' . Main::user('id'),
                    'value' => '1',
                ),
                array(
                    'key' => 'eib2bpro_user_' . md5(Main::user('mail')),
                    'value' => '1',
                )
            ),
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'posts_per_page' => 10,
            'paged' => $current_page
        ));


        echo '<div class="eib2bpro_offers_container">';

        if (0 === count($offers->posts)) {
            echo '<div class="eib2bpro_offers_empty">' . esc_html__('There are currently no offers', 'eib2bpro') . '</div>';
        }

        foreach ($offers->posts as $offer) {
            echo '<div class="eib2bpro_offers">';
            echo "<h2>" . esc_html(get_the_title($offer->ID)) . "</h2>";

            if (0 < ($promo_img = intval(get_post_meta($offer->ID, 'eib2bpro_promo_img', true)))) {
                echo "<img src='" . esc_url(wp_get_attachment_image_src($promo_img, 'full')[0]) . "'>";
            } ?>

            <?php if ('suggestion' === get_post_meta($offer->ID, 'eib2bpro_offer_type', true)) {
                $products = get_post_meta($offer->ID, 'eib2bpro_products', true);
                if (0 < count($products)) {
                    foreach ($products as $_product) {
                        $product = wc_get_product($_product['id']);
                        if ($product) { ?>
                            <div class="eib2bpro-b2b-offer-line">
                                <div class="eib2bpro-b2b-offer-line-image"><img src="<?php
                                                                                        if ($product->is_type('variation')) {
                                                                                            eib2bpro_a(get_the_post_thumbnail_url($product->get_parent_id()));
                                                                                        } else {
                                                                                            eib2bpro_a(get_the_post_thumbnail_url($_product['id']));
                                                                                        }
                                                                                        ?>" class="eib2bpro-Product_Image"></div>
                                <div class="eib2bpro-b2b-offer-line-name">
                                    <div class="eib2bpro-b2b-offer-line-product-name">
                                        <a href="<?php eib2bpro_a($product->get_permalink()) ?>"><?php eib2bpro_e($product->get_name()) ?></a>
                                    </div>
                                    <div class="eib2bpro-b2b-offer-line-price">
                                        <?php echo eib2bpro_r($product->get_price_html()); ?></a>
                                    </div>
                                    <?php if ($product->is_type('variable')) { ?>
                                        <a href="<?php eib2bpro_a($product->get_permalink()) ?>" class="eib2bpro_offer_add_to_cart_options button"><?php esc_html_e('SELECT OPTIONS', 'eib2bpro') ?></a>
                                    <?php } else { ?>
                                        <button class="eib2bpro_offer_add_to_cart" data-product="<?php eib2bpro_a($product->get_id()) ?>"><?php eib2bpro_e($params['add_to_cart']) ?></button>
                                    <?php } ?>
                                </div>
                            </div>
            <?php }
                    }
                }
            } ?>

            <?php if ('bundle' === get_post_meta($offer->ID, 'eib2bpro_offer_type', true)) {
                $products = get_post_meta($offer->ID, 'eib2bpro_products', true);
                $total = 0;
                if (0 < count($products)) {
                    foreach ($products as $_product) {
                        $product = wc_get_product($_product['id']);
                        if ($product) {
                            $_product['price'] = wc_format_decimal($_product['price']);
                            $_product['price'] = self::fix_price_by_tax($_product['price'], $params['always_include_tax']); ?>
                            <div class="eib2bpro-b2b-offer-line">
                                <div class="eib2bpro-b2b-offer-line-image"><img src="<?php
                                                                                        if ($product->is_type('variation')) {
                                                                                            eib2bpro_a(get_the_post_thumbnail_url($product->get_parent_id()));
                                                                                        } else {
                                                                                            eib2bpro_a(get_the_post_thumbnail_url($_product['id']));
                                                                                        }
                                                                                        ?>" class="eib2bpro-Product_Image"></div>
                                <div class="eib2bpro-b2b-offer-line-name">
                                    <div class="eib2bpro-b2b-offer-line-product-name">
                                        <a href="<?php eib2bpro_a($product->get_permalink()) ?>"><?php eib2bpro_e($product->get_name()) ?></a>
                                    </div>
                                    <div class="eib2bpro-b2b-offer-line-price-div">
                                        <div class="eib2bpro-b2b-offer-line-price">
                                            <?php echo wc_price(wc_format_decimal($_product['price'])); ?>
                                            x
                                            <span class="eib2bpro-b2b-offer-line-badge"><?php echo esc_html($_product['unit']); ?></span>
                                            = <?php echo wc_price(wc_format_decimal($_product['price']) * $_product['unit']);
                                                $total += $_product['price'] * $_product['unit'];
                                                ?>
                                        </div>
                                    </div>

                                </div>
                            </div>
                    <?php }
                    }
                    ?>
                    <div class="eib2bpro-b2b-offer-line-end"></div>
                    <hr>
                    <table class="eib2bpro_offers_product_table">
                        <tfoot>
                            <td class="eib2bpro_offers_add_to_cart" colspan="2"><button class="eib2bpro_offer_add_to_cart" data-offer="<?php eib2bpro_a($offer->ID) ?>"><?php eib2bpro_e($params['add_to_cart']) ?></button></td>
                            <td class="eib2bpro_offer_total" colspan="2">
                                <?php esc_html_e('Total', 'eib2bpro') ?>: <?php echo eib2bpro_r(wc_price($total)) ?>
                                <?php if ('incl' !== get_option('woocommerce_tax_display_shop')) {
                                    echo '<span class="eib2bpro-text-muted">' . esc_html__('+ TAXES') . '</span>';
                                } ?>
                            </td>
                        </tfoot>
                    </table>
            <?php }
            } ?>
            <div class="eib2bpro-b2b-offer-line-end"></div>
            <?php if (!empty($promo_text = get_post_meta($offer->ID, 'eib2bpro_promo_text', true))) {
                echo '<div class="eib2bpro_offers_promo_text">' . wp_kses_post(do_shortcode(nl2br($promo_text))) . "</div>";
            }

            if ($offer->ID > $last_seen) {
                update_post_meta($offer->ID, '_eib2bpro_offer_stats_seen_count_' . Main::user('group'), intval(get_post_meta($offer->ID, '_eib2bpro_offer_stats_seen_count_' . Main::user('group'), true)) + 1);
                update_post_meta($offer->ID, '_eib2bpro_offer_stats_seen_count_all', intval(get_post_meta($offer->ID, '_eib2bpro_offer_stats_seen_count_all', true)) + 1);
                update_user_meta(get_current_user_id(), '_eib2bpro_last_offer', $offer->ID);
            }
            echo '</div>';
        }
        echo '</div>';

        // pagination
        if (1 < $offers->max_num_pages) { ?>
            <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                <?php if (1 !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url(wc_get_endpoint_url(eib2bpro_option('b2b_endpoints_offers', 'offers'), $current_page - 1)); ?>"><?php esc_html_e('Previous', 'woocommerce'); ?></a>
                <?php endif; ?>

                <?php if (intval($offers->max_num_pages) !== $current_page) : ?>
                    <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url(wc_get_endpoint_url(eib2bpro_option('b2b_endpoints_offers', 'offers'), $current_page + 1)); ?>"><?php esc_html_e('Next', 'woocommerce'); ?></a>
                <?php endif; ?>
            </div>
            <?php }

        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function add_to_cart()
    {
        $type = eib2bpro_post('po', 'offer');
        $qty = eib2bpro_post('qty', 1, 'int');
        $status = eib2bpro_post('status', 'add');

        if ('product' === $type) {
            if ('add' === $status) {
                \WC()->cart->add_to_cart(eib2bpro_post('id', 0, 'int'), $qty, 0, []);
            } else {
                $cartId = \WC()->cart->generate_cart_id(eib2bpro_post('id', 0, 'int'));
                $cartItemKey = \WC()->cart->find_product_in_cart($cartId);
                \WC()->cart->remove_cart_item($cartItemKey);
            }
            echo eib2bpro_r(json_encode(['status' => 2, 'message' => esc_html__('Added', 'eib2bpro')]));
            wp_die();
        }

        $offer = intval(eib2bpro_post('id', 0));

        if (0 === intval(get_post_meta($offer, 'eib2bpro_group_' . Main::user('group'), true)) && 0 === intval(get_post_meta($offer, 'eib2bpro_user_' . Main::user('id'), true)) && 0 === intval(get_post_meta($offer, 'eib2bpro_user_' . md5(Main::user('mail')), true))) {
            die(esc_html__('Not available for you', 'eib2bpro'));
            return;
        }

        $cart = [
            'eib2bpro_offer' => $offer,
            'eib2bpro_offer_name' => get_the_title($offer)
        ];

        $id = intval(eib2bpro_option('b2b_offer_default_id', 0));
        if (!get_post_status($id)) {
            $id = self::create_default_offer_id();
        }

        if (get_post_status($id) === 'draft') {
            wp_publish_post($id);
        }

        // add to offer stats
        $user_stats = get_user_meta(get_current_user_id(), '_eib2bpro_offer_stats_atc_' . $offer, true);

        if (!$user_stats) {
            update_post_meta($offer, '_eib2bpro_offer_stats_atc_count_' . Main::user('group'), intval(get_post_meta($id, '_eib2bpro_offer_stats_atc_count_' . Main::user('group'), true)) + 1);
            update_post_meta($offer, '_eib2bpro_offer_stats_atc_count_all', intval(get_post_meta($id, '_eib2bpro_offer_stats_count_all', true)) + 1);
        }

        update_user_meta(get_current_user_id(), '_eib2bpro_offer_stats_atc_' . $offer, eib2bpro_strtotime('now', 'Y-m-d H:i:s'));

        \WC()->cart->add_to_cart($id, 1, 0, [], $cart);

        // finish
        eib2bpro_success();
    }

    public static function create_default_offer_id()
    {
        if (0 === intval(eib2bpro_option('b2b_offer_default_id', 0))) {
            $default = array(
                'post_title' => esc_html__('Offer', 'eib2bpro'),
                'post_status' => 'publish',
                'post_type' => 'product',
                'post_author' => 1,
            );

            $product_id = wp_insert_post($default);

            wp_set_object_terms($product_id, ['exclude-from-catalog', 'exclude-from-search'], 'product_visibility');
            wp_set_object_terms($product_id, 'simple', 'product_type');
            update_post_meta($product_id, '_visibility', 'hidden');
            update_post_meta($product_id, '_stock_status', 'instock');
            update_post_meta($product_id, '_regular_price', '');
            update_post_meta($product_id, '_sale_price', '');
            update_post_meta($product_id, '_price', '99999999999');
            update_post_meta($product_id, '_sold_individually', '');

            $product = wc_get_product($product_id);
            $product->set_price(99999999999);
            $product->save();

            eib2bpro_option('b2b_offer_default_id', $product_id, 'set');

            return intval(eib2bpro_option('b2b_offer_default_id', 0));
        }
    }

    public static function cart_display($product_name, $values, $cart_item_key)
    {
        if (!isset($values['eib2bpro_offer'])) {
            return $product_name;
        }

        return "<strong>" . esc_html($values['eib2bpro_offer_name']) . "</strong>";
    }

    public static function cart_item_thumbnail($product_image, $cart_item, $cart_item_key)
    {
        if (!isset($cart_item['eib2bpro_offer']) || intval($cart_item['product_id']) !== eib2bpro_option('b2b_offer_default_id', 0)) {
            return $product_image;
        }

        $offer = intval($cart_item['eib2bpro_offer']);
        $image_id = intval(get_post_meta($offer, 'eib2bpro_cart_img', true));
        if ($image_id > 0) {
            return wp_get_attachment_image($image_id);
        } else {
            return $product_image;
        }
    }

    public static function minicart_display($price, $cart_item, $cart_item_key)
    {
        if (intval($cart_item['product_id']) !== eib2bpro_option('b2b_offer_default_id', 0)) {
            return $price;
        }

        $offer_id = intval($cart_item['eib2bpro_offer']);
        $products = get_post_meta($offer_id, 'eib2bpro_products', true);

        $total = 0;

        if ($products) {
            foreach ($products as $prod) {
                $total += self::fix_price_by_tax($prod['price']) * intval($prod['unit']);
            }

            return wc_price($total);
        }
    }

    public static function add_products_to_order($order_id, $posted_data, $order)
    {
        $offer_id = 0;

        $order_items = $order->get_items();
        if (!is_wp_error($order_items)) {
            foreach ($order_items as $item_id => $order_item) {
                if (intval(eib2bpro_option('b2b_offer_default_id', 0)) === intval($order_item->get_product_id())) {
                    if (is_array($order_item->get_meta('_eib2bpro_offer_id'))) {
                        $offer_id = intval($order_item->get_meta('_eib2bpro_offer_id')[0]);
                        if (0 < $offer_id) {
                            $offer_products = get_post_meta($offer_id, 'eib2bpro_products', true);
                            if ($offer_products) {
                                foreach ($offer_products as $prod) {
                                    $oproduct = wc_get_product($prod['id']);
                                    if ($oproduct) {
                                        $order->add_product($oproduct, intval($prod['unit']), [
                                            '_offer_id' => $offer_id,
                                            'subtotal' => \EIB2BPRO\B2B\Site\Offers::fix_price_by_tax($prod['price']),
                                            'total' => \EIB2BPRO\B2B\Site\Offers::fix_price_by_tax($prod['price']) * $prod['unit']
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                    $order->remove_item($order_item->get_id());
                }
            }
        }

        if (0 < $offer_id) {
            $order->update_meta_data('eib2bpro_offer_id', $offer_id);
            $order->add_order_note(esc_html__('Offer', 'eib2bpro') . ': ' . get_the_title($offer_id));
            $order->calculate_totals();
            $order->save();
        }
    }

    public static function add_meta_to_order($item, $cart_item_key, $values, $order)
    {
        if (isset($values['eib2bpro_offer'])) {
            $item->update_meta_data('_eib2bpro_offer_id', [intval($values['eib2bpro_offer'])]);
            $item->update_meta_data(esc_html__('Offer', 'eib2bpro'), esc_html($values['eib2bpro_offer_name']));

            $content = '';
            $offer_id = intval($values['eib2bpro_offer']);
            $products = get_post_meta($offer_id, 'eib2bpro_products', true);
            if ($products) {
                foreach ($products as $prod) {
                    $price = self::fix_price_by_tax($prod['price']);
                    $content .= sprintf("%s <br>%s: %s - %s: %s<br><br>", get_the_title($prod['id']), esc_html__('Qty', 'eib2bpro'), $prod['unit'], esc_html__('Price', 'eib2bpro'), wc_price($prod['price']));
                }

                $item->update_meta_data(esc_html__('Details', 'eib2bpro'), $content);
            }
        }
    }

    public static function cart_calculate_totals($cart)
    {
        foreach ($cart->cart_contents as $cart_item_key => $value) {
            if (isset($value['eib2bpro_offer'])) {
                $offer_id = intval($value['eib2bpro_offer']);
                $products = get_post_meta($offer_id, 'eib2bpro_products', true);
                if ($products) {
                    $total = 0;
                    foreach ($products as $prod) {
                        $total += intval($prod['unit']) * floatval($prod['price']);
                    }
                    $value['data']->set_price($total);
                }
            }
        }
    }

    public static function after_cart_table()
    {
        $cart = \WC()->cart->get_cart();
        foreach ($cart as $item => $values) {
            if (eib2bpro_option('b2b_offer_default_id', 0) === $values['data']->get_id()) {
                $offer = get_post(intval($values['eib2bpro_offer']));
                if ($offer) {
                    echo '<div class="eib2bpro-b2b-offer-in-cart eib2bpro_offers">';
                    echo  '<div class="eib2bpro-b2b-offer-in-cart-head">' . esc_html__('You have added an offer to your cart', 'eib2bpro') . '</div>';
                    echo '<hr>';

                    $products = get_post_meta($offer->ID, 'eib2bpro_products', true);
                    $total = 0;
                    if (0 < count($products)) {
                        foreach ($products as $_product) {
                            $product = wc_get_product($_product['id']);
                            if ($product) {
                                $_product['price'] = wc_format_decimal($_product['price']);
                                $_product['price'] = self::fix_price_by_tax($_product['price']); ?>
                                <div class="eib2bpro-b2b-offer-line">
                                    <div class="eib2bpro-b2b-offer-line-image"><img src="<?php
                                                                                            if ($product->is_type('variation')) {
                                                                                                eib2bpro_a(get_the_post_thumbnail_url($product->get_parent_id()));
                                                                                            } else {
                                                                                                eib2bpro_a(get_the_post_thumbnail_url($_product['id']));
                                                                                            }
                                                                                            ?>" class="eib2bpro-Product_Image"></div>
                                    <div class="eib2bpro-b2b-offer-line-name">
                                        <div class="eib2bpro-b2b-offer-line-product-name">
                                            <a href="<?php eib2bpro_a($product->get_permalink()) ?>"><?php eib2bpro_e($product->get_name()) ?></a>
                                        </div>
                                        <div class="eib2bpro-b2b-offer-line-price-div">
                                            <div class="eib2bpro-b2b-offer-line-price">
                                                <?php echo wc_price(wc_format_decimal($_product['price'])); ?>
                                                x
                                                <span class="eib2bpro-b2b-offer-line-badge"><?php echo esc_html($_product['unit']); ?></span>
                                                = <?php echo wc_price(wc_format_decimal($_product['price']) * $_product['unit']);
                                                    $total += $_product['price'] * $_product['unit'];
                                                    ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                        <?php }
                        }
                        ?>
                        <div class="eib2bpro-b2b-offer-line-end"></div>
                        <hr>
                        <table class="eib2bpro_offers_cart_table">
                            <tbody>
                                <td class="eib2bpro_offers_add_to_cart">
                                    <a href="<?php eib2bpro_a(wc_get_cart_remove_url($item)) ?>" class="eib2bpro-b2b-offer-in-cart-remove button "><?php esc_html_e('Remove from cart', 'eib2bpro') ?></a>
                                </td>
                                <td class=" eib2bpro_offer_total">
                                    <?php esc_html_e('Total', 'eib2bpro') ?>: <?php echo eib2bpro_r(wc_price($total)) ?>
                                </td>
                            </tbody>
                        </table>
<?php }
                    echo '</div>';
                }
            }
        }
    }

    public static function hide_offer_from_cart($visible, $cart_item, $cart_item_key)
    {
        $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

        if ($product->get_id() === eib2bpro_option('b2b_offer_default_id', 0)) {
            $visible = false;
        }
        return $visible;
    }

    public static function fix_price_by_tax($price, $always_include_tax = 'false')
    {
        $offer = wc_get_product(intval(eib2bpro_option('b2b_offer_default_id', 0)));

        // always display prices with taxes if enabled
        if ('true' === $always_include_tax || apply_filters('eib2bpro_offers_always_include_tax', false)) {
            if (!\WC()->customer->is_vat_exempt()) {
                $rates = \WC_Tax::get_rates($offer->get_tax_class());
                $taxes = \WC_Tax::calc_tax($price, $rates, false);
            } else {
                $taxes = 0;
            }
            $price = \WC_Tax::round($price + array_sum($taxes));
            return $price;
        }

        if (wc_prices_include_tax() && ('incl' !== get_option('woocommerce_tax_display_shop') || \WC()->customer->is_vat_exempt())) {
            $rates = \WC_Tax::get_base_tax_rates($offer->get_tax_class('unfiltered'));
            $taxes = \WC_Tax::calc_tax($price, $rates, true);
            $price = \WC_Tax::round($price - array_sum($taxes));
        } elseif (!wc_prices_include_tax() && ('incl' === get_option('woocommerce_tax_display_shop') && !\WC()->customer->is_vat_exempt())) {
            $rates = \WC_Tax::get_rates($offer->get_tax_class());
            $taxes = \WC_Tax::calc_tax($price, $rates, false);
            $price = \WC_Tax::round($price + array_sum($taxes));
        }

        return $price;
    }
}
