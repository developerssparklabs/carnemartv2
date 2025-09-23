<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Quickorders
{

    public static function content()
    {
        echo do_shortcode(apply_filters('eib2bpro_my_account_quick_orders', '[b2bpro_quick_orders]'));
    }

    public static function save()
    {
        $id = eib2bpro_post('id', 0, 'int');
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

        if (0 < Main::user('id') &&  'true' === eib2bpro_post('save')) {
            if (0 === $id) {
                // insert quick order
                $post = wp_insert_post([
                    'post_title' => wp_strip_all_tags(eib2bpro_post('title', '')),
                    'post_content' => '',
                    'post_status' => 'draft',
                    'post_type' => 'eib2bpro_quick',
                    'post_author' => get_current_user_id()
                ]);
            } else {
                $post = get_post($id);
                if (!is_wp_error($post) && 'eib2bpro_quick' === $post->post_type &&  get_current_user_id() === intval(get_post_field('post_author', $id))) {
                    wp_update_post([
                        'ID' => $id,
                        'post_title' => wp_strip_all_tags(eib2bpro_post('title', '')),
                    ]);

                    $post = $post->ID;
                }
            }

            if (0 < intval($post)) {
                update_post_meta($post, 'eib2bpro_quickorder_products', $final_array);
                update_post_meta($post, 'eib2bpro_quickorder_reminder', 'true' === eib2bpro_post('switch') ? 1 : 0);
                update_post_meta($post, 'eib2bpro_quickorder_every', eib2bpro_post('every', 7, 'int'));
                update_post_meta($post, 'eib2bpro_quickorder_start', '' === eib2bpro_post('start', '') ? date('Y-m-d') : eib2bpro_post('start', ''));
            }
        }

        eib2bpro_success();
    }

    public static function delete()
    {

        $id = eib2bpro_post('id', 0, 'int');

        $post = get_post($id);

        if (!is_wp_error($post) && 'eib2bpro_quick' === $post->post_type &&  get_current_user_id() === intval(get_post_field('post_author', $id))) {
            wp_delete_post($post->ID);
            eib2bpro_success();
        } else {
            eib2bpro_error(esc_html__('Error', 'eib2bpro'));
        }
    }

    public static function shortcode($atts, $content = null)
    {
        $params = shortcode_atts(array(
            'subtotals' => 'show'
        ), $atts);

        wp_enqueue_script("bindwithdelay", EIB2BPRO_PUBLIC . "core/public/3rd/bindwithdelay.js", array('jquery'), EIB2BPRO_VERSION, true);

        $posts =   new \WP_Query(array(
            'post_type' => 'eib2bpro_quick',
            'post_status' => 'draft',
            'orderby' => 'modified',
            'order' => 'DESC',
            'author' => get_current_user_id()
        ));

        ob_start(); ?>

        <section class="eib2bpro-quickorders-container">
            <?php
            if (0 < count($posts->posts)) {
                foreach ($posts->posts as $post) { ?>
                    <a id="<?php eib2bpro_a($post->ID) ?>"></a>
                    <div class="eib2bpro-quickorders-item">
                        <div class="eib2bpro-quickorders-summary">
                            <?php echo esc_html($post->post_title) ?>
                            <div class="eib2bpro-quickorders-summary-products">
                                <?php
                                $products = get_post_meta($post->ID, 'eib2bpro_quickorder_products', true);
                                if (is_array($products) || !empty($products)) {
                                    foreach ($products as $product) {
                                        echo eib2bpro_product_image($product['id']);
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="eib2bpro-quickorders-details<?php eib2bpro_a(intval(eib2bpro_get('id', '')) === intval($post->ID) ? ' eib2bpro-quickorders-reminder-show' : '') ?>">
                            <?php self::order_form($post->ID); ?>
                        </div>
                    </div>

            <?php }
            } ?>

            <div class="eib2bpro-quickorders-item">
                <div class="eib2bpro-quickorders-summary">
                    <?php esc_html_e('Create a new quick order list', 'eib2bpro'); ?>
                </div>
                <div class="eib2bpro-quickorders-details">
                    <?php self::order_form(0) ?>
                </div>
            </div>

        </section>

    <?php
        $content = ob_get_contents();
        ob_end_clean();
        return apply_filters('b2bpro_bulkorder_content', $content);
    }

    public static function order_form($id = 0)
    {
        $params = array(
            'subtotals' => 'show',
            'prices' => 'show'
        );

        $every = intval(get_post_meta($id, 'eib2bpro_quickorder_every', true));
        if (0 === $every) {
            $every = 7;
        }
    ?>
        <div class="eib2bpro-bulkorder-l2-container">
            <table class="eib2bpro-bulkorder-l2-table eib2bpro-quickorders-table" data-qoid="<?php eib2bpro_a($id) ?>" data-decimal="<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>">
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

                    $products = get_post_meta($id, 'eib2bpro_quickorder_products', true);

                    if (!is_array($products) || empty($products)) {
                        $products = [['id' => '', 'qty' => '0'], ['id' => '', 'qty' => '0'], ['id' => '', 'qty' => '0']];
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
                                    <input name="ei-bulkorder-product[]" class="eib2bpro-bulkorder-l2-product" <?php echo ((0 < intval($product['id'])) ? "data-selected='" . eib2bpro_r(json_encode(Bulkorder::search($product['id'], false, true))) . "'" : '') ?> value="<?php eib2bpro_a((0 < intval($product['id'])) ? get_the_title($product['id']) : '') ?>" placeholder="<?php esc_html_e('Search a product', 'eib2bpro'); ?>">
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
                        <td></td>
                        <td class="eib2bpro-bulkorder-l2-table-total-text">
                            <?php
                            esc_html_e('Total:', 'eib2bpro');
                            ?>
                        </td>
                        <td>
                            <span><?php echo get_woocommerce_currency_symbol() ?></span>
                            <span class="eib2bpro-bulkorder-total">0<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>00</span>
                        </td>

                    </tr>
                </tfoot>
            </table>

            <div class="eib2bpro-quickorders-reminder">
                <input type="hidden" class="eib2bpro-quickorders-title" value="<?php eib2bpro_a(get_the_title($id)) ?>">
                <label class="eib2bpro-switch">
                    <input type="checkbox" class="eib2bpro-quickorders-reminder-switch" <?php checked(get_post_meta($id, 'eib2bpro_quickorder_reminder', true)) ?>>
                    <span class="eib2bpro-slider"></span>
                </label>
                <span class="eib2bpro-quickorders-reminder-switch-label"><?php esc_html_e('Set a reminder', 'eib2bpro'); ?></span>

                <div class="eib2bpro-quickorders-reminder-container<?php eib2bpro_a('1' === get_post_meta($id, 'eib2bpro_quickorder_reminder', true) ? ' eib2bpro-quickorders-reminder-show' : '') ?>">
                    <?php echo eib2bpro_r(sprintf(esc_html__('Send me a reminder mail every %s days', 'eib2bpro'), '<input type="number" min="1" step="1" class="eib2bpro-quickorders-reminder-every" value="' . esc_attr($every) . '">')); ?>
                    <br>
                    <?php esc_html_e('Start reminder on', 'eib2bpro'); ?> <input type="date" class="eib2bpro-quickorders-reminder-start" value="<?php eib2bpro_a(get_post_meta($id, 'eib2bpro_quickorder_start', true)) ?>">
                </div>
            </div>
            <div class=" eib2bpro-quickorders-actions">
                <button class="eib2bpro-quickorders-actions-button eib2bpro-quickorders-add-to-cart"><?php esc_html_e('Add to cart', 'eib2bpro') ?></button>
                <button class="eib2bpro-quickorders-actions-button eib2bpro-quickorders-save"><?php esc_html_e('Save', 'eib2bpro') ?></button>
                <?php if (0 < intval($id)) { ?>
                    <button class="eib2bpro-quickorders-actions-button eib2bpro-quickorders-delete" data-id="<?php eib2bpro_a($id) ?>"><?php esc_html_e('Delete', 'eib2bpro') ?></button>
                <?php } ?>
            </div>
        </div>
<?php

    }
}
