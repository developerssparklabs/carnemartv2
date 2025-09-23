<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Bulkorder
{
    public static function content()
    {
        echo do_shortcode(apply_filters('eib2bpro_my_account_bulk_order', '[b2bpro_bulk_order]'));
    }

    public static function shortcode($atts, $content = null)
    {
        $params = shortcode_atts(array(
            'category_list' => 'show',
            'child_categories' => 'show',
            'prices' => 'show',
            'subtotals' => 1 === eib2bpro_option('b2b_settings_bulkorder_subtotal', 1) ? 'show' : 'hide',
            'layout' => eib2bpro_option('b2b_settings_bulkorder_layout', '1'),
            'category_ids' => '',
            'limit' => apply_filters('b2bpro_bulkorder_limit', eib2bpro_option('b2b_settings_bulkorder_limit', 10)),
            'b2c_message' => '',
        ), $atts);

        wp_enqueue_script("bindwithdelay", EIB2BPRO_PUBLIC . "core/public/3rd/bindwithdelay.js", array('jquery'), EIB2BPRO_VERSION, true);

        if (!empty($params['b2c_message']) && 'b2c' === Main::user('group_or_b2c')) {
            return '<div class="eib2bpro-bulkorder-l1-container"><div class="eib2bpro-bulkorder-l1-message">' . eib2bpro_r($params['b2c_message']) . '</div>';
        }

        ob_start();

        if ('1' === $params['layout']) {
            self::layout_1($params);
        } else {
            self::layout_2($params);
        }

        $content = ob_get_contents();
        ob_end_clean();
        return apply_filters('b2bpro_bulkorder_content', $content);
    }

    public static function layout_1($params = [])
    {
?>
        <div class="eib2bpro-bulkorder-l1-container">
            <table class="eib2bpro-bulkorder-l1-table" data-limit="<?php eib2bpro_a($params['limit']) ?>" data-subtotal="<?php eib2bpro_a(1 === eib2bpro_option('b2b_settings_bulkorder_subtotal', 1) ? 'show' : 'hide') ?>" data-categories="<?php eib2bpro_a($params['category_ids']) ?>" data-page="1" data-prices="<?php eib2bpro_a($params['prices']) ?>" data-decimal="<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>">
                <thead>
                    <tr>
                        <td colspan="4">
                            <?php eib2bpro_ui('input', 'eib2bpro_bulkorder_search', '', ['class' => 'form-control eib2bpro-bulkorder-l1-search', 'attr' => 'type="search" autocomplete="off" placeholder="' . esc_attr__('Search', 'eib2bpro') . '"']) ?>
                            <?php if ('show' === $params['category_list']) { ?>
                                <select class="form-control eib2bpro-bulkorder-l1-categories">
                                    <option value="0"><?php esc_html_e('All products', 'eib2bpro') ?>
                                        <?php self::categories(0, 0, $params); ?>
                                </select>
                            <?php } ?>
                        </td>
                    </tr>
                </thead>
                <tbody class="eib2bpro-bulkorder-l1-body">
                    <?php self::products_by_category($params); ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    public static function layout_2($params = [])
    {
    ?>
        <div class="eib2bpro-bulkorder-l2-container">
            <form action="" name="eib2bpro-bulkorder-l2-form" class="eib2bpro-bulkorder-l2-form<?php eib2bpro_a('hide' === $params['subtotals'] ? ' width-90' : '') ?>">
                <table class="eib2bpro-bulkorder-l2-table" data-decimal="<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>">
                    <thead class="eib2bpro-bulkorder-l2-table-head">
                        <tr>
                            <th class="eib2bpro-bulkorder-l2-table-th-product"><?php esc_html_e('Product', 'eib2bpro'); ?></th>
                            <th class="eib2bpro-bulkorder-l2-table-th-qty"><?php esc_html_e('Qty', 'eib2bpro'); ?></th>
                            <?php if ('show' === $params['subtotals']) { ?>
                                <th class="eib2bpro-bulkorder-l2-table-th-subtotal"><?php esc_html_e('Subtotal', 'eib2bpro'); ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody class="eib2bpro-bulkorder-l2-table-tbody">
                        <tr class="eib2bpro-hidden-row w-100">
                            <td class="eib2bpro-bulkorder-l2-table-product" data-colname="<?php esc_html_e('Product', 'eib2bpro'); ?>">
                                <div class="eib2bpro-bulkorder-l2-product-container">

                                    <input name="ei-bulkorder-product_id[]" class="eib2bpro-bulkorder-l2-product-id" type="hidden">
                                    <input name="ei-bulkorder-product[]" class="eib2bpro-bulkorder-l2-product" placeholder="<?php esc_html_e('Type to search', 'eib2bpro'); ?>">
                                    <div class="eib2bpro-bulkorder-l2-product-list"></div>
                                    <button class="eib2bpro-bulkorder-l2-product-x"><svg xmlns="http://www.w3.org/2000/svg" id="Layer_2" data-name="Layer 2" width="14" height="14" viewBox="0 0 24 24">
                                            <path d="M19,7a1,1,0,0,0-1,1V19.191A1.92,1.92,0,0,1,15.99,21H8.01A1.92,1.92,0,0,1,6,19.191V8A1,1,0,0,0,4,8V19.191A3.918,3.918,0,0,0,8.01,23h7.98A3.918,3.918,0,0,0,20,19.191V8A1,1,0,0,0,19,7Z" />
                                            <path d="M20,4H16V2a1,1,0,0,0-1-1H9A1,1,0,0,0,8,2V4H4A1,1,0,0,0,4,6H20a1,1,0,0,0,0-2ZM10,4V3h4V4Z" />
                                            <path d="M11,17V10a1,1,0,0,0-2,0v7a1,1,0,0,0,2,0Z" />
                                            <path d="M15,17V10a1,1,0,0,0-2,0v7a1,1,0,0,0,2,0Z" />
                                        </svg></button>
                                </div>
                            </td>
                            <td class="text-center eib2bpro-bulkorder-l2-table-qty" data-colname="<?php esc_html_e('Qty', 'eib2bpro'); ?>">
                                <?php eib2bpro_ui('input', 'ei-bulkorder-qty[]', '', ['attr' => 'type="number" step="1" min="1"']) ?>
                                <span class="eib2bpro-bulkorder-l2-qty-max-alert"><?php esc_html_e('This is the maximum you can add', 'eib2bpro'); ?></span>
                            </td>
                            <?php if ('show' === $params['subtotals']) { ?>
                                <td class="eib2bpro-bulkorder-l2-table-subtotal" data-colname="<?php esc_html_e('Subtotal', 'eib2bpro'); ?>">
                                    <div class="w-100 d-flex flex-nowrap align-items-center">
                                        <span><?php echo get_woocommerce_currency_symbol() ?></span>
                                        <span class="eib2bpro-bulkorder-subtotal">0<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>00</span>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php

                        $products = get_user_meta(Main::user('id'), 'eib2bpro_bulkorder', true);

                        if (!is_array($products) || empty($products)) {
                            $products = [['id' => '', 'qty' => '0'], ['id' => '', 'qty' => '0'], ['id' => '', 'qty' => '0'], ['id' => '', 'qty' => '0'], ['id' => '', 'qty' => '0']];
                        }

                        $products = array_merge($products, [['id' => '', 'qty' => '0']]);

                        foreach ($products as $product) {
                            if (0 < intval($product['id'])) {
                                if (!wc_get_product(intval($product['id']))) {
                                    continue;
                                }
                            }
                        ?>
                            <tr class="w-100">
                                <td class="eib2bpro-bulkorder-l2-table-product<?php eib2bpro_a('hide' === $params['prices'] ? ' width-90' : '') ?>" data-colname="<?php esc_html_e('Product', 'eib2bpro'); ?>">
                                    <div class="eib2bpro-bulkorder-l2-product-container">
                                        <input name="ei-bulkorder-product_id[]" class="eib2bpro-bulkorder-l2-product-id" value="<?php eib2bpro_a($product['id']) ?>" type="hidden">
                                        <input name="ei-bulkorder-product[]" class="eib2bpro-bulkorder-l2-product" <?php echo ((0 < intval($product['id'])) ? "data-selected='" . eib2bpro_r(json_encode(self::search($product['id'], false, true))) . "'" : '') ?> value="<?php eib2bpro_a((0 < intval($product['id'])) ? get_the_title($product['id']) : '') ?>" placeholder="<?php esc_html_e('Search a product', 'eib2bpro'); ?>">
                                        <div class="eib2bpro-bulkorder-l2-product-list"></div>
                                        <button class="eib2bpro-bulkorder-l2-product-x <?php eib2bpro_a(0 < intval($product['id']) ? '' : 'ei-no-show') ?>"><svg xmlns="http://www.w3.org/2000/svg" id="Layer_2" data-name="Layer 2" width="14" height="14" viewBox="0 0 24 24">
                                                <path d="M19,7a1,1,0,0,0-1,1V19.191A1.92,1.92,0,0,1,15.99,21H8.01A1.92,1.92,0,0,1,6,19.191V8A1,1,0,0,0,4,8V19.191A3.918,3.918,0,0,0,8.01,23h7.98A3.918,3.918,0,0,0,20,19.191V8A1,1,0,0,0,19,7Z" />
                                                <path d="M20,4H16V2a1,1,0,0,0-1-1H9A1,1,0,0,0,8,2V4H4A1,1,0,0,0,4,6H20a1,1,0,0,0,0-2ZM10,4V3h4V4Z" />
                                                <path d="M11,17V10a1,1,0,0,0-2,0v7a1,1,0,0,0,2,0Z" />
                                                <path d="M15,17V10a1,1,0,0,0-2,0v7a1,1,0,0,0,2,0Z" />
                                            </svg></button>
                                    </div>
                                </td>
                                <td class="text-center eib2bpro-bulkorder-l2-table-qty" data-colname="<?php esc_html_e('Qty', 'eib2bpro'); ?>">
                                    <?php eib2bpro_ui('input', 'ei-bulkorder-qty[]', eib2bpro_r($product['qty']), ['attr' => 'type="number" step="1" min="1"']) ?>
                                    <span class="eib2bpro-bulkorder-l2-qty-max-alert"><?php esc_html_e('This is the maximum you can add', 'eib2bpro'); ?></span>
                                </td>

                                <?php if ('show' === $params['subtotals']) { ?>
                                    <td class="eib2bpro-bulkorder-l2-table-subtotal" data-colname="<?php esc_html_e('Subtotal', 'eib2bpro'); ?>">
                                        <div class="w-100 d-flex flex-nowrap align-items-center">
                                            <span><?php echo get_woocommerce_currency_symbol() ?></span>
                                            <span class="eib2bpro-bulkorder-subtotal">0<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>00</span>

                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php
                        } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><button class="eib2bpro-bulkorder-l2-add-to-cart"><?php esc_html_e('Add to cart', 'eib2bpro') ?></button></td>
                            <td class="eib2bpro-bulkorder-l2-table-total-text">
                                <?php
                                if ('show' === $params['subtotals']) {
                                    esc_html_e('Total:', 'eib2bpro');
                                } ?>
                            </td>
                            <?php if ('show' === $params['subtotals']) { ?>
                                <td>
                                    <span><?php echo get_woocommerce_currency_symbol() ?></span>
                                    <span class="eib2bpro-bulkorder-total">0<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>00</span>
                                </td>
                            <?php } ?>
                        </tr>
                    </tfoot>
                </table>
            </form>
        </div>
        <?php
    }

    public static function products_by_category($params = [])
    {
        $from_search = false;
        $category = eib2bpro_post('category', []);
        $categories = eib2bpro_post('categories', '');
        $page = eib2bpro_post('page', 1, 'int');
        $search = eib2bpro_post('q');

        if (empty($category) && !empty($categories)) {
            $category = wp_parse_list($categories);
        }

        if (eib2bpro_post('prices')) {
            $params['prices'] = eib2bpro_post('prices');
        }

        if (eib2bpro_post('limit')) {
            $params['limit'] = absint(eib2bpro_post('limit', 10, 'int'));
        }

        $params['subtotals'] = 1 === eib2bpro_option('b2b_settings_bulkorder_subtotal', 1) ? 'show' : 'hide';
        $params['images'] = 1 === eib2bpro_option('b2b_settings_bulkorder_images', 1) ? 'show' : 'hide';
        $params['outofstock'] = 1 === eib2bpro_option('b2b_settings_bulkorder_outofstock', 0) ? 'show' : 'hide';

        $available_products = Product::product_visibility_ids();
        $cart_items = [];

        if (isset(\WC()->cart)) {
            $cart = \WC()->cart->get_cart();
            foreach ($cart as $item => $values) {
                $cart_items[] = $values['data']->get_id();
            }
        }

        if (!empty($params['category_ids']) && empty($category)) {
            $category = (array)wp_parse_list($params['category_ids']);
        }
        $args = array(
            'category' => $category,
            'limit' => $params['limit'],
            'page' => $page,
            'paginate' => true,
            'visibility' => 'visible',
            'type' => ['simple', 'variable'],
            'orderby' => eib2bpro_option('b2b_settings_bulkorder_orderby', 'date'),
            'order' => 'date' === eib2bpro_option('b2b_settings_bulkorder_orderby', 'date') ? 'DESC' : 'ASC',
            's' => wc_clean($search)
        );

        if ('hide' === $params['outofstock']) {
            $args['stock_status'] = 'instock';
        }

        $args = apply_filters('b2bpro_bulkorder_args', $args);

        $products = wc_get_products($args);

        if (!is_wp_error($products) && count($products->products) > 0) {
        ?>
            <tr>
                <td class="eib2bpro-bulkorder-l1-td-product"><?php esc_html_e('Product', 'eib2bpro'); ?></td>
                <?php if ('show' === $params['prices'] && 'show' === $params['subtotals']) { ?>
                    <td class="eib2bpro-bulkorder-l1-td-subtotal"><?php esc_html_e('Subtotal', 'eib2bpro'); ?></td>
                <?php } ?>
                <td class="eib2bpro-bulkorder-l1-td-qty"><?php esc_html_e('Qty', 'eib2bpro'); ?></td>
                <td class="eib2bpro-bulkorder-l1-td-add"><?php esc_html_e('Cart', 'eib2bpro'); ?></td>
            </tr>
            <?php
            foreach ($products->products as $product) {

                $image = '';
                $outofstock = '';
                $button_status = '';

                if (empty($product->get_parent_id()) && !in_array($product->get_id(), $available_products)) {
                    continue;
                }

                if (!empty($product->get_parent_id()) && !in_array($product->get_parent_id(), $available_products)) {
                    continue;
                }

                if (!$product->is_in_stock() && 'hide' === $params['outofstock']) {
                    continue;
                } else {
                    if (!$product->is_in_stock()) {
                        $outofstock = '<br><span class="eib2bpro-b2b-bulkorder-l1-outofstock">' . esc_html__('Out of stock', 'eib2bpro') . '</span>';
                        $button_status = ' disabled';
                    }
                }

                if (!$product->is_type('simple') && !$product->is_type('variable')) {
                    continue;
                }

                if ('show' ===  $params['images'] && get_the_post_thumbnail_url($product->get_id())) {
                    $image = '<span class="eib2bpro-b2b-bulkorder-l1-image"><img src="' . esc_url(get_the_post_thumbnail_url($product->get_id(), 'thumbnail')) . '" /></span>';
                }

                $arr = self::product_attr($product);

            ?>
                <tr class="eib2bpro-bulkorder-l1-product" data-details='<?php echo eib2bpro_r(json_encode($arr)) ?>'>
                    <td class="eib2bpro-bulkorder-l1-td-product"><?php
                                                                    if ($product->is_type('variable')) {


                                                                        $variations = $product->get_children();
                                                                        echo  eib2bpro_r($image) . "<a href='" . esc_attr($product->get_permalink()) . "' target='_blank'>" . esc_html(html_entity_decode(get_the_title($product->get_id()))) . "</a>";
                                                                        if ('show' === $params['prices'] && 'show' === $params['subtotals']) {
                                                                            echo '<div class="eib2bpro-bulkorder-l1-td-subtotal2"><span>' . get_woocommerce_currency_symbol() . '</span><span class="eib2bpro-bulkorder-l1-product-subtotal">' . eib2bpro_r(wc_format_decimal($product->get_price(), 2)) . '</span></div>';
                                                                        }
                                                                        echo "<select class='eib2bpro-bulkorder-l1-variations'>";
                                                                        echo "<option value='0'>" . esc_html__('Select', 'eib2bpro') . "</option>";

                                                                        foreach ($variations as $variation_id) {
                                                                            $variation = wc_get_product($variation_id);
                                                                            $arr2 = self::product_attr($variation);
                                                                            $price = $arr2['price'];
                                                                            $attributes = array(); // Initializing
                                                                            foreach ($variation->get_attributes() as $attribute => $value) {
                                                                                $attribute_label = wc_attribute_label($attribute, $product);
                                                                                $attribute_value = $variation->get_attribute($attribute);
                                                                                $attributes[]    = $attribute_label . ':&nbsp;' . $attribute_value;
                                                                            }

                                                                            echo '<option data-details=\'' . eib2bpro_r(json_encode($arr2)) . '\'  value="' . esc_attr($variation->get_id()) . '">' . implode(', ', $attributes) . '</option>';
                                                                        }
                                                                        echo "</select>";
                                                                        if (in_array($variation_id, $cart_items)) {
                                                                            $button = 'remove';
                                                                        } else {
                                                                            $button = 'add';
                                                                        }
                                                                    ?>
                            <?php if ('show' === $params['prices'] && 'show' === $params['subtotals']) { ?>
                    <td class="eib2bpro-bulkorder-l1-td-subtotal"><span><?php echo get_woocommerce_currency_symbol() ?></span><span class="eib2bpro-bulkorder-l1-product-subtotal"><?php echo eib2bpro_r(wc_format_decimal($price * intval($arr['min_qty']), 2)) ?></span></td>
                <?php } ?>
                <td class="eib2bpro-bulkorder-l1-td-qty ">
                    <div class="num-block">
                        <div class="num-in eib2bpro-bulkorder-l1-product-qty-num-in">
                            <span class="minus dis"></span>
                            <input type="number" min="<?php eib2bpro_a($arr['step']) ?>" max="<?php eib2bpro_a($arr['max_qty']) ?>" name="eib2bpro-bulkorder-l1-product-qty" class="form-control eib2bpro-bulkorder-l1-product-qty" step="<?php eib2bpro_a($arr['step']) ?>" value="<?php eib2bpro_a($arr['step']) ?>">
                            <span class="plus"></span>
                        </div>
                </td>
                <td class="eib2bpro-bulkorder-l1-td-add"><button class="eib2bpro-bulkorder-l1-product-add" data-status="<?php eib2bpro_a($button) ?>" data-id=" <?php eib2bpro_a($product->get_id()) ?>" disabled><?php echo eib2bpro_r('remove' === $button ? apply_filters('b2bpro_bulkorder_replace_remove_text', '<svg id="bold" height="19" viewBox="0 0 24 24" width="19" xmlns="http://www.w3.org/2000/svg"><circle cx="10.5" cy="22.5" r="1.5"/><circle cx="18.5" cy="22.5" r="1.5"/><path d="m24 6.5c0 3.584-2.916 6.5-6.5 6.5s-6.5-2.916-6.5-6.5 2.916-6.5 6.5-6.5 6.5 2.916 6.5 6.5zm-3 0c0-.552-.448-1-1-1h-5c-.552 0-1 .448-1 1s.448 1 1 1h5c.552 0 1-.448 1-1z"/><path d="m9 6.5c0-.169.015-.334.025-.5h-2.666l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.547-.011c-.001 0-.002 0-.003 0-.413 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75h-15c-.414 0-.75-.336-.75-.75s.336-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l.665-3.157c-1.431 1.095-3.213 1.753-5.149 1.753-4.687 0-8.5-3.813-8.5-8.5z"/></svg>') : apply_filters('b2bpro_bulkorder_replace_add_text', '<svg id="bold" height="19" viewBox="0 0 24 24" width="19" xmlns="http://www.w3.org/2000/svg"><path d="m21.25 17.5h-15c-.414 0-.75-.336-.75-.75s.337-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l2-9.5c.046-.221-.009-.451-.151-.627-.143-.175-.357-.277-.583-.277h-16.891l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.546-.011c-.002 0-.003 0-.004 0-.412 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75z"/><circle cx="10.5" cy="21.5" r="1.5"/><circle cx="18.5" cy="21.5" r="1.5"/></svg>')) ?></button></td>

            <?php
                                                                    } else {
                                                                        if (in_array($product->get_id(), $cart_items)) {
                                                                            $button = 'remove';
                                                                        } else {
                                                                            $button = 'add';
                                                                        }
                                                                        echo eib2bpro_r($image) . "<a href='" . esc_attr($product->get_permalink()) . "' target='_blank'>" . esc_html(html_entity_decode(get_the_title($product->get_id()))) . eib2bpro_r($outofstock) . "</a>";
                                                                        $price = $arr['price'];
                                                                        if ('show' === $params['prices'] && 'show' === $params['subtotals']) {
                                                                            echo '<div class="eib2bpro-bulkorder-l1-td-subtotal2"><span>' . get_woocommerce_currency_symbol() . '</span><span class="eib2bpro-bulkorder-l1-product-subtotal">' . eib2bpro_r(wc_format_decimal($price * intval($arr['min_qty']), 2)) . '</span></div>';
                                                                        }
            ?>
                <?php if ('show' === $params['prices'] && 'show' === $params['subtotals']) { ?>
                    <td class="eib2bpro-bulkorder-l1-td-subtotal"><span><?php echo get_woocommerce_currency_symbol() ?></span><span class="eib2bpro-bulkorder-l1-product-subtotal"><?php echo eib2bpro_r(wc_format_decimal($price * intval($arr['min_qty']), 2)) ?></span></td>
                <?php } ?>
                <td class="eib2bpro-bulkorder-l1-td-qty">
                    <div class="num-block">
                        <div class="num-in eib2bpro-bulkorder-l1-product-qty-num-in">
                            <span class="minus dis"></span>
                            <input type="number" min="<?php eib2bpro_a($arr['step']) ?>" max="<?php eib2bpro_a($arr['max_qty']) ?>" name="eib2bpro-bulkorder-l1-product-qty" class="form-control eib2bpro-bulkorder-l1-product-qty" step="<?php eib2bpro_a($arr['step']) ?>" value="<?php eib2bpro_a($arr['step']) ?>">
                            <span class="plus"></span>
                        </div>
                </td>
                <td class="eib2bpro-bulkorder-l1-td-add"><button class="eib2bpro-bulkorder-l1-product-add" data-status="<?php eib2bpro_a($button) ?>" data-id="<?php eib2bpro_a($product->get_id()) ?>" <?php eib2bpro_a($button_status) ?>><?php echo eib2bpro_r('remove' === $button ? apply_filters('b2bpro_bulkorder_replace_remove_text', '<svg id="bold" height="19" viewBox="0 0 24 24" width="19" xmlns="http://www.w3.org/2000/svg"><circle cx="10.5" cy="22.5" r="1.5"/><circle cx="18.5" cy="22.5" r="1.5"/><path d="m24 6.5c0 3.584-2.916 6.5-6.5 6.5s-6.5-2.916-6.5-6.5 2.916-6.5 6.5-6.5 6.5 2.916 6.5 6.5zm-3 0c0-.552-.448-1-1-1h-5c-.552 0-1 .448-1 1s.448 1 1 1h5c.552 0 1-.448 1-1z"/><path d="m9 6.5c0-.169.015-.334.025-.5h-2.666l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.547-.011c-.001 0-.002 0-.003 0-.413 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75h-15c-.414 0-.75-.336-.75-.75s.336-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l.665-3.157c-1.431 1.095-3.213 1.753-5.149 1.753-4.687 0-8.5-3.813-8.5-8.5z"/></svg>') : apply_filters('b2bpro_bulkorder_replace_add_text', '<svg id="bold" height="19" viewBox="0 0 24 24" width="19" xmlns="http://www.w3.org/2000/svg"><path d="m21.25 17.5h-15c-.414 0-.75-.336-.75-.75s.337-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l2-9.5c.046-.221-.009-.451-.151-.627-.143-.175-.357-.277-.583-.277h-16.891l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.546-.011c-.002 0-.003 0-.004 0-.412 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75z"/><circle cx="10.5" cy="21.5" r="1.5"/><circle cx="18.5" cy="21.5" r="1.5"/></svg>')) ?></button></td>
            <?php
                                                                    }
            ?></td>
                </tr>
            <?php
            }
        } else { ?>
            <tr>
                <td colspan="4"><?php esc_html_e('Can not find any products', 'eib2bpro'); ?></td>

            </tr>
        <?php } ?>

        <tr>
            <td colspan=" 4">
                <?php
                // pagination
                if (1 < $products->max_num_pages) { ?>
                    <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                        <?php if (1 !== $page) : ?>
                            <button class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button eib2bpro-bulkorder-l1-page"><?php esc_html_e('Previous', 'woocommerce'); ?></button>
                        <?php endif; ?>

                        <?php if (intval($products->max_num_pages) !== $page) : ?>
                            <button class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button eib2bpro-bulkorder-l1-page"><?php esc_html_e('Next', 'woocommerce'); ?></button>
                        <?php endif; ?>
                    </div>
                <?php } ?>
            </td>
        </tr>
        <?php
    }
    public static function product_attr($product, $params = [])
    {
        $from_search = eib2bpro_is_ajax() ? true : false;

        $min_qty = 1;
        $max_qty = 999999;

        if ($product->managing_stock()) {
            if ($product->backorders_allowed()) {
                $max_qty = 999999;
            } else {
                if (0 >= intval($product->get_stock_quantity())) {
                    $max_qty = 1;
                } else {
                    $max_qty = intval($product->get_stock_quantity());
                }
            }
        }

        $price_tiers = (array)json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_' . Main::user('group_or_b2c'), true));

        if (empty($price_tiers)) {
            $price_tiers = (array)json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_b2c', true));
        }

        if (!empty($price_tiers)) {
            ksort($price_tiers);
            foreach ($price_tiers as $item => $value) {
                if (empty($item) || empty($value)) {
                    continue;
                }
                $price_tiers[$item] = Product::fix_price($product, ['price' => Product::price_with_rules($value, $product), 'qty' => 1]);
            }
        }

        if ($from_search) {
            // check if there is a tax exemption for products
            if (Main::available_rules('tax_exemption_product')) {
                add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);
                add_filter('woocommerce_product_variation_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);
            }

            add_filter('option_woocommerce_tax_display_shop', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);
            add_filter('option_woocommerce_tax_display_cart', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);

            $price = floatval(Product::price_with_rules(Product::product_active_price($product->get_price(), $product), $product));
        } else {
            add_filter('option_woocommerce_tax_display_shop', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);
            add_filter('option_woocommerce_tax_display_cart', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);

            $price = floatval(Product::product_active_price($product->get_price(), $product));
        }

        if (!is_user_logged_in() && 'hide_prices' === eib2bpro_option('b2b_settings_visibility_guest', 'hide_prices')) {
            $price = "Q";
            $price_tiers = [];
        }

        $step = \EIB2BPRO\Rules\Site::step_qty(['min_qty' => $min_qty, 'min_value' => $min_qty, 'step' => 1], $product);

        $arr = [
            'id' => $product->get_id(),
            'name' => esc_attr(get_the_title($product->get_id())),
            'max_qty' => $max_qty,
            'min_qty' => $min_qty,
            'price' => Product::fix_price($product, ['price' => $price, 'qty' => 1]),
            'price_tiers' => $price_tiers,
            'step' => isset($step['step']) ? $step['step'] : 1
        ];

        return $arr;
    }
    public static function categories($parent = 0, $deep = 0, $params = [])
    {
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent'   => $parent
        );

        $categories = get_terms($args);
        if ($categories) {
            foreach ($categories as $category) {
                if (!empty($params['category_ids'])) {
                    $available = (array)wp_parse_list($params['category_ids']);
                    if (!in_array($category->slug, $available)) {
                        continue;
                    }
                }
        ?>
                <option value="<?php eib2bpro_a($category->slug) ?>"><?php echo str_repeat('&nbsp;&nbsp; - ', $deep);
                                                                        eib2bpro_e($category->name); ?> </option>
<?php
                $sub_args = array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                    'parent'   => $category->term_id
                );

                if ('show' === $params['child_categories']) {
                    if (count(get_terms($sub_args)) > 0) {
                        self::categories($category->term_id, ++$deep, $params);
                        --$deep;
                    }
                }
            }
        }
    }

    public static function search($id = false, $from_search = true, $internal = false)
    {
        global $wpdb;

        $str = trim(eib2bpro_post('query'));
        $products = [];

        $args = array(
            'posts_per_page' => -1,
            'post_type' => array('product', 'product_variation'),
            'post_status' => 'publish',
            'fields' => 'ids'
        );

        if ($id) {
            $args['post__in'] = [$id];
        } else {
            $args['s'] = wc_clean($str);
        }

        $products_by_name = new \WP_Query($args);
        if ($products_by_name->posts) {
            $products = $products_by_name->posts;
        }

        if (!empty($str)) {
            $products_by_sku_query = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT product_id FROM {$wpdb->prefix}wc_product_meta_lookup WHERE sku=%s",
                    $wpdb->esc_like($str)
                )
            );

            if (0 < count($products_by_sku_query)) {
                $products = array_merge($products, wp_list_pluck($products_by_sku_query, 'product_id'));
            }
        }

        $available_products = Product::product_visibility_ids();

        // check if there is a tax exemption for products
        if (Main::available_rules('tax_exemption_product')) {
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);
            add_filter('woocommerce_product_variation_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);
        }

        add_filter('option_woocommerce_tax_display_shop', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);
        add_filter('option_woocommerce_tax_display_cart', '\EIB2BPRO\B2b\Site\Product::display_price_tax', 10, 1);

        $result = [];

        if (0 < count($products)) {
            foreach ($products as $product_id) {
                $product = wc_get_product($product_id);

                if ($product->is_type('variable')) {
                    continue;
                }

                if (empty($product->get_parent_id()) && !in_array($product->get_id(), $available_products)) {
                    continue;
                }

                if (!empty($product->get_parent_id()) && !in_array($product->get_parent_id(), $available_products)) {
                    continue;
                }

                if (!$product->is_in_stock() && 0 === eib2bpro_option('b2b_settings_bulkorder_outofstock', 0)) {
                    continue;
                }

                $min_qty = 1;
                $max_qty = 9999999;

                if ($product->managing_stock()) {
                    if ($product->backorders_allowed()) {
                        $max_qty = 999999;
                    } else {
                        if (0 >= intval($product->get_stock_quantity())) {
                            $max_qty = 1;
                        } else {
                            $max_qty = intval($product->get_stock_quantity());
                        }
                    }
                }

                $price_tiers = (array)json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_' . Main::user('group_or_b2c'), true));

                if (empty($price_tiers)) {
                    $price_tiers = (array)json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_b2c', true));
                }

                if (!empty($price_tiers)) {
                    ksort($price_tiers);
                    foreach ($price_tiers as $item => $value) {
                        if (empty($item) || empty($value)) {
                            continue;
                        }
                        $price_tiers[$item] = Product::fix_price($product, ['price' => Product::price_with_rules($value, $product), 'qty' => 1]);
                    }
                }

                if ($from_search) {
                    $price = floatval(Product::price_with_rules(Product::product_active_price($product->get_price(), $product), $product));
                } else {
                    $price = floatval(Product::product_active_price($product->get_price(), $product));
                }

                if (!is_user_logged_in() && 'hide_prices' === eib2bpro_option('b2b_settings_visibility_guest', 'hide_prices')) {
                    $price = "Q";
                    $price_tiers = [];
                }

                $arr = [
                    'id' => $product->get_id(),
                    'name' => html_entity_decode(get_the_title($product->get_id())),
                    'max_qty' => $max_qty,
                    'min_qty' => $min_qty,
                    'price' => Product::fix_price($product, ['price' => $price, 'qty' => 1]),
                    'price_tiers' => $price_tiers,
                ];

                if ($id) {
                    return $arr;
                }

                $html = '<div class="eib2bpro-bulkorder-l2-item" data-details=\'' . eib2bpro_r(json_encode($arr)) . '\' data-productid="' . esc_attr($product->get_id()) . '">';
                $html .= '<div><span  class="eib2bpro-bulkorder-l2-name">' . html_entity_decode(get_the_title($product->get_id())) . '</span> &nbsp; &nbsp; ';

                if ($product->get_sku()) {
                    $html .= '<span class="eib2bpro-bulkorder-l2-sku">' . esc_html__('SKU: ', 'eib2bpro') . ' ' . $product->get_sku() . '</span>';
                }

                if (!$product->is_in_stock()) {
                    $html .= '<span class="eib2bpro-bulkorder-l2-outofstock">' . esc_html__('Out of stock ', 'eib2bpro') . '</span>';
                }

                if (0 < $price) {
                    // hide prices if user is not logged in and prices are hidden
                    if (!is_user_logged_in() && 'hide_prices' === eib2bpro_option('b2b_settings_visibility_guest', 'hide_prices')) {
                        $html .= '<div class="eib2bpro-bulkorder-l2-price"></div>';
                    } else {
                        $html .= '<div class="eib2bpro-bulkorder-l2-price">' . wc_price($arr['price']) . '</div>';
                    }
                }


                $html .= '</div>';

                if (get_the_post_thumbnail_url($product->get_id()) && 1 === eib2bpro_option('b2b_settings_bulkorder_images', 1)) {
                    $html .= '<div class="eib2bpro-bulkorder-l2-image"><img src="' . esc_url(get_the_post_thumbnail_url($product->get_id())) . '"></img></div>';
                }

                $html .= '</div>';

                $result[] = ['id' => $product->get_id(), 'html' => $html];
            }
        }

        if ($internal) {
            return json_encode($result);
        }

        \eib2bpro_success('', ['result' => $result]);
        exit;
    }

    public static function add_to_cart()
    {
        $qty = eib2bpro_post('qty', 1, 'int');
        $status = eib2bpro_post('status', 'add');

        if ('add' === $status) {
            \WC()->cart->add_to_cart(eib2bpro_post('id', 0, 'int'), $qty, 0, []);
            echo eib2bpro_r(json_encode(['status' => 2, 'message' => apply_filters('b2bpro_bulkorder_replace_remove_text', '<svg id="bold" height="19" viewBox="0 0 24 24" width="19" xmlns="http://www.w3.org/2000/svg"><circle cx="10.5" cy="22.5" r="1.5"/><circle cx="18.5" cy="22.5" r="1.5"/><path d="m24 6.5c0 3.584-2.916 6.5-6.5 6.5s-6.5-2.916-6.5-6.5 2.916-6.5 6.5-6.5 6.5 2.916 6.5 6.5zm-3 0c0-.552-.448-1-1-1h-5c-.552 0-1 .448-1 1s.448 1 1 1h5c.552 0 1-.448 1-1z"/><path d="m9 6.5c0-.169.015-.334.025-.5h-2.666l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.547-.011c-.001 0-.002 0-.003 0-.413 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75h-15c-.414 0-.75-.336-.75-.75s.336-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l.665-3.157c-1.431 1.095-3.213 1.753-5.149 1.753-4.687 0-8.5-3.813-8.5-8.5z"/></svg>')]));
        } else {
            $cartId = \WC()->cart->generate_cart_id(eib2bpro_post('id', 0, 'int'));
            $cartItemKey = \WC()->cart->find_product_in_cart($cartId);
            \WC()->cart->remove_cart_item($cartItemKey);
            echo eib2bpro_r(json_encode(['status' => 2, 'message' => apply_filters('b2bpro_bulkorder_replace_add_text', '<svg id="bold" height="19" viewBox="0 0 24 24" width="19" xmlns="http://www.w3.org/2000/svg"><path d="m21.25 17.5h-15c-.414 0-.75-.336-.75-.75s.337-.75.75-.75h1.499.001 13.5c.354 0 .661-.249.734-.596l2-9.5c.046-.221-.009-.451-.151-.627-.143-.175-.357-.277-.583-.277h-16.891l-.38-1.806c-.266-1.26-1.392-2.178-2.679-2.183l-2.546-.011c-.002 0-.003 0-.004 0-.412 0-.748.333-.75.747s.333.751.747.753l2.546.011c.585.002 1.097.42 1.218.992l.505 2.401 1.81 8.596h-.576c-1.241 0-2.25 1.009-2.25 2.25s1.009 2.25 2.25 2.25h15c.414 0 .75-.336.75-.75s-.336-.75-.75-.75z"/><circle cx="10.5" cy="21.5" r="1.5"/><circle cx="18.5" cy="21.5" r="1.5"/></svg>')]));
        }
        wp_die();
    }

    public static function auto_save()
    {
        if (!isset($_POST['form'])) {
            eib2bpro_success();
            wp_die();
        }

        $form_array = $_POST['form'];
        $final_array = [];

        if (is_array($form_array)) {
            foreach ($form_array as $form_array_2) {
                if (0 === intval($form_array_2['qty']) || !isset($form_array_2['id'])) {
                    continue;
                }
                $final_array[] = ['id' => intval($form_array_2['id']), 'qty' => intval($form_array_2['qty'])];
                if ('true' === eib2bpro_post('add_to_cart')) {
                    \WC()->cart->add_to_cart(intval($form_array_2['id']), intval($form_array_2['qty']));
                }
            }
        }

        if (0 < Main::user('id') && 'true' === eib2bpro_post('save')) {
            update_user_meta(Main::user('id'), 'eib2bpro_bulkorder', $final_array);
        }

        eib2bpro_success();
    }
}
