<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Product
{
    public static function product_is_visible($visible, $product_id)
    {
        $parent_id = wp_get_post_parent_id($product_id);
        $visible_products = self::product_visibility_ids();

        if (in_array($product_id, $visible_products) || in_array($parent_id, $visible_products)) {
            return true;
        }

        return false;
    }


    /**
     * Funcion para obtener customer group
     */
    public static function get_location_id_or_fallback()
    {
        if (isset($_GET["id"])) {
            return $_GET["id"];
        }

        //s8k spark keikos sergio
        $selected_store = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : null;
        $stores = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
        foreach ($stores as $key => $store) {
            if ($selected_store == $key) {
                $cart_item_data['select_location'] = array(
                    'location_name' => $store->name,
                    'location_key' => $key,
                    'location_termId' => $store->term_id
                );
                $customer_group = get_term_meta($store->term_id, 'customer_group', true);

            }

        }
        $location_id = $customer_group;
        return $location_id;
    }



    //funcion para obtener un id de tienda a partir de un customer group
    public static function get_term_id_by_meta($meta_key, $meta_value)
    {
        global $wpdb;

        // Busca el term_id usando el meta_key y meta_value
        $term_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value = %s",
                $meta_key,
                $meta_value
            )
        );

        return $term_id ? intval($term_id) : null; // Devuelve el term_id si existe, de lo contrario null
    }




    public static function product_regular_price($price, $product)
    {
        $location_id = self::get_location_id_or_fallback();

        $new_price = get_post_meta($product->get_id(), 'eib2bpro_regular_price_group_' . $location_id, true);

        if (!empty($new_price)) {
            return wc_format_decimal($new_price);
        }

        return $price;
    }

    public static function product_sale_price($price, $product)
    {
        $location_id = self::get_location_id_or_fallback();
        $new_price = get_post_meta($product->get_id(), 'eib2bpro_sale_price_group_' . $location_id, true);

        if (!empty($new_price)) {
            return wc_format_decimal($new_price);
        }

        return $price;
    }

    public static function product_active_price($price, $product)
    {
        $regular_price = self::product_regular_price($price, $product);
        $sale_price = self::product_sale_price($price, $product);
        ;

        return ($sale_price < $regular_price) ? $sale_price : $regular_price;
    }

    public static function formatted_price($price_html, $product)
    {
        return $price_html;
        if ($product->is_type('variable')) {
            return $price_html;
        }
        $location_id = self::get_location_id_or_fallback();
        $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_' . $location_id, true));
        if (empty($price_tiers)) {
            $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_b2c', true));
        }

        if (empty($price_tiers)) {
            return $price_html;
        }

        $min = $product->get_sale_price();
        $max = $product->get_regular_price();

        if ($product->is_on_sale()) {
            $max = $product->get_sale_price();
        }

        foreach ($price_tiers as $tier_qty => $tier_price) {
            if ($max < $tier_price) {
                $max = wc_format_decimal($tier_price);
            }
            if ($min > $tier_price) {
                $min = wc_format_decimal($tier_price);
            }
        }

        return wc_format_sale_price($min, $max);
    }

    public static function tiered_formatted_price($price_html, $product)
    {
        if ($product->is_type('variable')) {
            return $price_html;
        }
        $location_id = self::get_location_id_or_fallback();
        $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_' . $location_id, true));


        if (empty($price_tiers)) {
            $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_b2c', true));
        }

        if (empty($price_tiers)) {
            return $price_html;
        }

        $min = self::product_sale_price($product->get_sale_price(), $product);
        $max = self::product_regular_price($product->get_regular_price(), $product);


        foreach ($price_tiers as $tier_qty => $tier_price) {
            if ($max < $tier_price) {
                $max = wc_format_decimal($tier_price);
            }
            if ($min > $tier_price) {
                $min = wc_format_decimal($tier_price);
            }
        }

        return wc_format_sale_price($max, $min);
    }

    public static function tiered_formatted_price_with_range($price_html, $product)
    {

        if (
            ($product->is_type('simple') && ((1 === eib2bpro_option('b2b_settings_tiers_show_range', 0) || 1 === eib2bpro_option('b2b_settings_tiers_show_range_from', 0))
            ))
            ||
            $product->is_type('variable')
        ) {
            if ($product->is_type('simple')) {
                $location_id = self::get_location_id_or_fallback();
                $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_' . $location_id, true));

                if (empty($price_tiers)) {
                    $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_b2c', true));
                }

                if (empty($price_tiers) && $product->is_type('simple')) {
                    return $price_html;
                }
            }

            $cache_key = 'eib2bpro_price_range_' . substr($product->get_id(), -2) . '_' . self::currency() . '_' . implode('__', \EIB2BPRO\Rules\Site::$price_rules) . '_' . self::cache_vat_exempt();
            $range = (array) get_transient($cache_key, false);

            if (!empty($range) && isset($range[$_GET["id"]][$product->get_id()])) {
                $new_price_html = $range[$_GET["id"]][$product->get_id()];
            } else {
                $new_price_html = self::calculate_price_range($product, $_GET["id"], true);
            }

            if (!empty($new_price_html)) {
                return $new_price_html;
            }
        }
        return $price_html;
    }


    /**
     * obtiene los precios para trabajar
     */
    public static function tiered_regular_price($price, $product)
    {

        if (!is_object(WC()->cart)) {
            return $price;
        }
        $location_id = self::get_location_id_or_fallback();
        $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_' . $location_id, true));

        //** en este proceso quitamos el formato que tiene los json para mostrar promos y dejamos q trabaje el cart */

        $price_tiers_clean = [];

        foreach ($price_tiers as $key => $value) {
            // Extrae el número inicial (cantidad) usando regex
            if (preg_match('/^([\d\.]+)/', $key, $matches)) {
                $qty = $matches[1];
                $price_tiers_clean[$qty] = $value;
            }
        }

        $price_tiers = $price_tiers_clean;



        if (empty($price_tiers)) {
            $price_tiers = (array) json_decode(get_post_meta($product->get_id(), 'eib2bpro_price_tiers_group_b2c', true));
        }

        if (empty($price_tiers)) {
            return $price;
        }

        $qty = self::get_qty_from_cart($product);
        $new_price = $price;

        if (0 < $qty) {
            foreach ($price_tiers as $tier_qty => $tier_price) {
                if ($qty >= $tier_qty) {
                    $new_price = wc_format_decimal($tier_price);
                }
            }
        }
        return min($new_price, $price);
    }

    public static function get_qty_from_cart($product)
    {
        $qty = 0;
        if (is_object(WC()->cart)) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                if ($product->get_id() === $cart_item['product_id'] || $product->get_id() === $cart_item['variation_id']) {
                    $qty = $cart_item['quantity'];
                    break;
                }
            }
        }
        return $qty;
    }

    public static function price_tiers_table($post_id = 0, $is_variable = false)
    {
        global $post;

        if (0 === intval($post_id)) {
            $post_id = $post->ID;
        }

        $product = wc_get_product($post_id);

        if (!is_object($product)) {
            return;
        }

        if ($product->is_type('simple')) {
            if (!$product->is_purchasable() || 'no' === get_post_meta($post_id, 'eib2bpro_show_price_tiers_table', true)) {
                return;
            }
        } else {
            $parent_id = wp_get_post_parent_id($post_id);
            if (!$is_variable || 'no' === get_post_meta($parent_id, 'eib2bpro_show_price_tiers_table', true) || !$product->is_purchasable()) {
                return;
            }
        }

        /**
         * s8k: Obtener curren location
         */

        $selected_store = $_COOKIE['wcmlim_selected_location'] ?? [];


        $stores = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));

        foreach ($stores as $key => $store) {
            if ($selected_store == $key) {
                $cart_item_data['select_location'] = array(
                    'location_name' => $store->name,
                    'location_key' => $key,
                    'location_termId' => $store->term_id
                );
            }
        }


        $price = self::product_regular_price($product->get_regular_price(), $product);
        $sale_price = self::product_sale_price($product->get_sale_price(), $product);

        if (!empty($sale_price) && $sale_price < $price) {
            $price = $sale_price;
        }

        //s8k: debemos obtener el id
        $location_id = self::get_location_id_or_fallback();
        $price_tiers = (array) json_decode(get_post_meta($post_id, 'eib2bpro_price_tiers_group_' . $location_id, true));


        if (empty($price_tiers)) {
            $price_tiers = (array) json_decode(get_post_meta($post_id, 'eib2bpro_price_tiers_group_b2c', true));
        }

        if (empty($price_tiers)) {
            return;
        }

        ksort($price_tiers); ?>


        <table class="eib2bpro_price_tiers_table <?php eib2bpro_a('eib2bpro_product_' . esc_attr($post_id)); ?>"
            data-product_id="<?php eib2bpro_a($post_id) ?>"
            data-current_qty="<?php eib2bpro_a($qty = self::get_qty_from_cart($product)) ?>" style="display:none;">
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Quantity', 'eib2bpro'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Price', 'eib2bpro'); ?>
                    </th>
                    <?php
                    if (1 === eib2bpro_option('b2b_settings_appearance_show_discount', 0)) { ?>
                        <th>
                            <?php esc_html_e('Discount', 'eib2bpro'); ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = count($price_tiers);
                $index = 0;
                foreach ($price_tiers as $tier_qty => $tier_price) {
                    ++$index;
                    $tier_price = self::woocs(
                        self::fix_price(
                            $product,
                            [
                                'price' =>
                                    self::price_with_rules($tier_price, $product, true)
                            ]
                        )
                    );
                    ?>
                    <tr data-qty="<?php eib2bpro_a($tier_qty) ?>">
                        <td>
                            <?php
                            eib2bpro_e($tier_qty);
                            if ((1 === $index && 1 === $count) || ($index === $count)) {
                                //echo "+";
                            } else {
                                next($price_tiers);
                                if ($tier_qty !== intval(key($price_tiers)) - 1) {
                                    //  echo " - " . (intval(key($price_tiers)) - 1);
                                }
                                current($price_tiers);
                            } ?>
                        </td>
                        <td class="eib2bpro_tier_price">
                            <?php echo eib2bpro_r(wc_price($tier_price)) ?>
                        </td>
                        <?php if (1 === eib2bpro_option('b2b_settings_appearance_show_discount', 0)) {
                            $regular_price = floatval(get_post_meta($post_id, '_regular_price', true)); ?>
                            <td class="eib2bpro_tier_discount">
                                <?php
                                if (0 === floatval($regular_price) || empty($regular_price)) {
                                    echo "0%";
                                } else {
                                    eib2bpro_e(apply_filters('b2b_price_tiers_discount', round(($regular_price - $tier_price) / $regular_price * 100) . '%'));
                                }
                                ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                } ?>
            </tbody>
        </table>


        <!--- tabla -->

        <table style="display: none;"
            class="eib2bpro_price_tiers_table <?php eib2bpro_a('eib2bpro_product_' . esc_attr($post_id)); ?>"
            data-product_id="<?php eib2bpro_a($post_id) ?>"
            data-current_qty="<?php eib2bpro_a($qty = self::get_qty_from_cart($product)) ?>">
            <thead>
                <tr>
                    <th>
                        <?php esc_html_e('Quantity', 'eib2bpro'); ?>
                    </th>
                    <th>
                        <?php esc_html_e('Price', 'eib2bpro'); ?>
                    </th>
                    <?php
                    if (1 === eib2bpro_option('b2b_settings_appearance_show_discount', 0)) { ?>
                        <th>
                            <?php esc_html_e('Discount', 'eib2bpro'); ?>
                        </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = count($price_tiers);
                $index = 0;
                foreach ($price_tiers as $tier_qty => $tier_price) {
                    ++$index;
                    $tier_price = self::woocs(
                        self::fix_price(
                            $product,
                            [
                                'price' =>
                                    self::price_with_rules($tier_price, $product, true)
                            ]
                        )
                    );
                    ?>
                    <tr data-qty="<?php eib2bpro_a($tier_qty) ?>">
                        <td>
                            <?php
                            eib2bpro_e($tier_qty);
                            if ((1 === $index && 1 === $count) || ($index === $count)) {
                                //echo "+";
                            } else {
                                next($price_tiers);
                                if ($tier_qty !== intval(key($price_tiers)) - 1) {
                                    //  echo " - " . (intval(key($price_tiers)) - 1);
                                }
                                current($price_tiers);
                            } ?>
                        </td>
                        <td class="eib2bpro_tier_price">
                            <?php echo eib2bpro_r(wc_price($tier_price)) ?>
                        </td>
                        <?php if (1 === eib2bpro_option('b2b_settings_appearance_show_discount', 0)) {
                            $regular_price = floatval(get_post_meta($post_id, '_regular_price', true)); ?>
                            <td class="eib2bpro_tier_discount">
                                <?php
                                if (0 === floatval($regular_price) || empty($regular_price)) {
                                    echo "0%";
                                } else {
                                    eib2bpro_e(apply_filters('b2b_price_tiers_discount', round(($regular_price - $tier_price) / $regular_price * 100) . '%'));
                                }
                                ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                } ?>
            </tbody>
        </table>

        <!---tabla--->
<table class="custom_price_table_<?php echo esc_attr($post_id); ?>" data-product-id="<?php echo esc_attr($post_id); ?>"
    data-current-qty="<?php echo esc_attr(self::get_qty_from_cart($product)); ?>" style="display:none;">
    <thead>
        <tr>
            <th>
                <?php esc_html_e('Cantidad', 'custom'); ?>
            </th>
            <th>
                <?php esc_html_e('Precio', 'custom'); ?>
            </th>
            <?php if (1 === eib2bpro_option('b2b_settings_appearance_show_discount', 0)) { ?>
            <th>
                <?php esc_html_e('Descuento', 'custom'); ?>
            </th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = count($price_tiers);
        $index = 0;
        foreach ($price_tiers as $tier_qty => $tier_price) {
            ++$index;
            $tier_price = self::woocs(
                self::fix_price(
                    $product,
                    ['price' => self::price_with_rules($tier_price, $product, true)]
                )
            );
            ?>
        <tr data-custom-qty="<?php echo esc_attr($tier_qty); ?>"
            class="<?php echo (strpos($tier_qty, '🔥') !== false) ? 'promocion' : ''; ?>"
            data-regular-price="<?php echo esc_attr($regular_price); ?>">
            <td>
                <?php echo esc_html($tier_qty); ?>
            </td>
            <td class="custom_tier_price">
                <?php echo wc_price($tier_price); ?>
            </td>
            <?php if (1 === eib2bpro_option('b2b_settings_appearance_show_discount', 0)) {
                $regular_price = floatval(get_post_meta($post_id, '_regular_price', true)); ?>
            <td class="custom_tier_discount">
                <?php
                if (0 === floatval($regular_price) || empty($regular_price)) {
                    echo "0%";
                } else {
                    echo round(($regular_price - $tier_price) / $regular_price * 100) . '%';
                }
                ?>
            </td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>
</table>

<style>
    /* 🎨 Estilos personalizados CarneMart para la tabla */
    table[class^="custom_price_table_"] {
        width: 100%;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
        font-size: 15px;
        border: 2px solid #1c3d8d;
        /* azul */
        border-radius: 8px;
        overflow: hidden;
        margin-top: 15px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    table[class^="custom_price_table_"] thead {
        background: #1c3d8d;
        /* azul CarneMart */
        color: #fff;
        font-weight: bold;
    }

    table[class^="custom_price_table_"] th,
    table[class^="custom_price_table_"] td {
        padding: 10px 12px;
        border-bottom: 1px solid #ddd;
    }

    table[class^="custom_price_table_"] tbody tr:nth-child(odd) {
        background: #f5fdf5;
        /* verde muy claro */
    }

    table[class^="custom_price_table_"] tbody tr:nth-child(even) {
        background: #ffffff;
    }

    table[class^="custom_price_table_"] tbody tr.promocion {
        background: #e8ffe8;
        /* verde suave */
        font-weight: bold;
        border-left: 4px solid #3ca535;
        /* verde CarneMart */
    }

    table[class^="custom_price_table_"] tbody tr.promocion td {
        color: #3ca535;
        /* texto verde en promociones */
    }

    table[class^="custom_price_table_"] tbody tr:hover {
        background: #d7e6ff;
        /* azul muy claro */
    }
</style>



<?php
    }

    public static function price_tiers_table_variation($data, $product, $variation)
    {
        if (!$variation->is_purchasable()) {
            return;
        }

        ob_start();

        $variation_price = $variation->get_price();

        self::price_tiers_table($variation->get_id(), true);

        $old_data = $data['availability_html'];
        $data['availability_html'] = ob_get_clean() . $old_data;
        return $data;
    }

    public static function price_tiers_current()
    {
        global $post;

        $product = wc_get_product($post->ID);

        if ($product->is_type('simple')) {
            echo eib2bpro_r("<p class='eib2bpro_active_price_container'><span class='eib2bpro_active_price eib2bpro-hidden-row'>" . wc_price(self::tiered_regular_price(self::product_active_price(0, $product), $product)) . "</span><span class='eib2bpro_active_discount'></span></p>");
        }
    }

    public static function price_with_rules($price, $product, $tier = false)
    {
        return \EIB2BPRO\Rules\Site::change_price($price, $product, $tier);
    }

    public static function category_visibility($args, $taxonomies)
    {
        if (is_admin()) {
            return $args;
        }

        $lang = '_default';
        if (defined('ICL_LANGUAGE_CODE')) {
            $lang = '_' . ICL_LANGUAGE_CODE;
        }

        $user_group = Main::user('group');

        $categories = get_transient('eib2bpro_visibility_categories' . $lang);

        if (false !== $categories) {
            $hide = [];
            if (isset($categories[$user_group]['hide'])) {
                $hide = $categories[$user_group]['hide'];
                if (isset($categories['users'][get_current_user_id()])) {
                    $hide = array_diff($categories[$user_group]['hide'], (array) $categories['users'][get_current_user_id()]);
                }
            }

            $args['exclude'] = array_keys($hide);
        }

        return $args;
    }

    public static function category_visibility_ids()
    {
        $lang = '_default';
        if (defined('ICL_LANGUAGE_CODE')) {
            $lang = '_' . ICL_LANGUAGE_CODE;
        }

        if (!$cache = get_transient('eib2bpro_visibility_categories' . $lang)) {
            $cache = [];

            $groups = get_posts([
                'post_type' => 'eib2bpro_groups',
                'post_status' => ['publish'],
                'numberposts' => -1,
                'fields' => 'ids'
            ]);

            $categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'fields' => 'ids',
                'hide_empty' => false
            ));

            foreach ($categories as $category) {

                // guests
                $show1 = ('0' !== get_term_meta($category, 'eib2bpro_group_guests', true)) ? 'show' : 'hide';
                $cache['guest'][$show1][$category] = $category;

                // b2c
                $show2 = ('0' !== get_term_meta($category, 'eib2bpro_group_b2c', true)) ? 'show' : 'hide';
                $cache['b2c'][$show2][$category] = $category;

                // users
                if (!empty($users = get_term_meta($category, 'eib2bpro_users', true))) {
                    $all_users = array_map('intval', (array) explode(',', $users));
                    foreach ($all_users as $user_id) {
                        if (0 < $user_id) {
                            $cache['users'][$user_id][$category] = $category;
                        }
                    }
                }

                // groups
                if ($groups) {
                    foreach ($groups as $group) {
                        $show = ("0" !== (string) get_term_meta($category, 'eib2bpro_group_' . $group, true)) ? 'show' : 'hide';
                        $cache[$group][$show][$category] = $category;
                    }
                }
            }

            set_transient('eib2bpro_visibility_categories' . $lang, $cache);
        }
        return $cache;
    }

    public static function product_visibility_ids()
    {
        global $wpdb;

        $lang = '_default';
        if (defined('ICL_LANGUAGE_CODE')) {
            $lang = '_' . ICL_LANGUAGE_CODE;
        }

        $user_group = Main::user('group');

        $show_or_hide = 'show';

        // get categories
        $categories = self::category_visibility_ids();
        if (isset($categories[$user_group][$show_or_hide])) {
            $category_ids = $categories[$user_group][$show_or_hide];
        } else {
            $category_ids = [-13829877675676];
        }

        if (isset($categories['users'][Main::user('id')])) {
            $category_ids = array_merge($category_ids, $categories['users'][Main::user('id')]);
        }

        $visible_categories = array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'operator' => 'IN',
            'include_children' => 0,
            'terms' => $category_ids,
        );

        if (!$products = get_transient('eib2bpro_visible_products_group_' . $user_group . $lang)) {
            $query_products = new \WP_Query(array(
                'posts_per_page' => -1,
                'post_type' => 'product',
                'fields' => 'ids',
                'tax_query' => array($visible_categories)
            ));

            $products = $query_products->posts;
            $manual_by_groups = new \WP_Query(array(
                'posts_per_page' => -1,
                'fields' => 'ids',
                'post_type' => 'product',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'eib2bpro_visibility_manual',
                        'value' => '1',
                    ),
                    array(
                        'key' => 'eib2bpro_group_' . $user_group,
                        'value' => '0',
                        'operator' => 'NOT'
                    ),
                )
            ));

            $products_by_group = array_diff($products, $manual_by_groups->posts);

            $products = $products_by_group;
            set_transient('eib2bpro_visible_products_group_' . $user_group . $lang, $products);
        }

        if (!$manual_by_users = get_transient('eib2bpro_visible_products_by_users' . $lang)) {
            $manual_by_users = [];
            $manual_by_users['-999999']['-999999'] = '-999999';

            $query_manual_by_users = $wpdb->get_results(
                $wpdb->prepare(
                    "select post_id, meta_value from {$wpdb->postmeta} where meta_key = %s",
                    'eib2bpro_users'
                ),
                ARRAY_A
            );

            if ($query_manual_by_users) {
                foreach ($query_manual_by_users as $item) {
                    $users = array_map(function ($v) {
                        $v = trim($v);
                        $v = intval($v);
                        return $v;
                    }, (array) explode(',', $item['meta_value']));

                    foreach ($users as $user) {
                        $manual_by_users[$user][$item['post_id']] = $item['post_id'];
                    }
                }
            }
            set_transient('eib2bpro_visible_products_by_users' . $lang, $manual_by_users);
        }


        if (isset($manual_by_users[Main::user('id')])) {
            $products = array_merge($products, array_keys($manual_by_users[Main::user('id')]));
        }

        return $products;
    }

    public static function shortcode_product_visibility($args)
    {
        if (!is_user_logged_in() && 'hide_shop' === eib2bpro_option('b2b_settings_visibility_guest')) {
            $args['post__in'] = [99999999999];
            return $args;
        }

        $products = self::product_visibility_ids();

        if (empty($products)) {
            $products = [99999999999];
        }

        if (empty($args['post__in'])) {
            $args['post__in'] = $products;
        } else {
            $args['post__in'] = array_intersect($args['post__in'], $products);
        }

        return $args;
    }

    public static function hide_all_products($visible, $product_id)
    {
        return false;
    }

    public static function hide_all_categories($args, $taxonomies)
    {

        if ('product_cat' !== $taxonomies[0]) {
            return $args;
        }

        $args['include'] = [999999999];
        return $args;
    }

    public static function product_visibility($query)
    {

        // get visible products

        $visible_products = self::product_visibility_ids();

        if (!empty($visible_products)) {
            $query->set('post__in', $visible_products);
            $query->set('post__not_in', array(eib2bpro_option('b2b_offer_default_id', -999999)));
        } else {
            $query->set('post__in', array('-99999999'));
        }

        return $query;
    }


    public static function calculate_price_range($post, $active_group = false, $save = false)
    {
        if (is_object($post)) {
            $product = $post;
            $post_id = $product->get_id();
        } else {
            $product = wc_get_product($post);
            $post_id = $post;
        }

        if (!is_object($product)) {
            return;
        }

        $cache_key = 'eib2bpro_price_range_' . substr($product->get_id(), -2) . '_' . self::currency() . '_' . implode('__', \EIB2BPRO\Rules\Site::$price_rules);

        $range = (array) get_transient($cache_key, []);
        $product_ids = [];

        if ($active_group) {
            $groups[$active_group] = (object) array('ID' => $active_group);
        } else {
            $groups = \EIB2BPRO\B2b\Admin\Groups::get();
            $groups['b2c'] = (object) array('ID' => 'b2c');
        }

        if ($product->is_type('simple')) {
            $product_ids[] = $post_id;
        } elseif ($product->is_type('variable')) {
            $variations = $product->get_children();
            foreach ($variations as $variation_id) {
                $product_ids[] = $variation_id;
            }
        } else {
            return null;
        }

        foreach ($groups as $group) {
            $prices = [];
            foreach ($product_ids as $pid) {
                $product_obj = wc_get_product($pid);
                if ('b2c' === $_GET["id"]) {
                    $group_max = wc_format_decimal(self::price_with_rules(get_post_meta($pid, '_regular_price', true), $product_obj));
                    $group_min = wc_format_decimal(self::price_with_rules(get_post_meta($pid, '_sale_price', true), $product_obj));
                } else {
                    $group_max = wc_format_decimal(self::price_with_rules(get_post_meta($pid, 'eib2bpro_regular_price_group_' . $_GET["id"], true), $product_obj));
                    $group_min = wc_format_decimal(self::price_with_rules(get_post_meta($pid, 'eib2bpro_sale_price_group_' . $_GET["id"], true), $product_obj));
                    if (empty($group_max)) {
                        $group_max = wc_format_decimal(self::price_with_rules(get_post_meta($pid, '_regular_price', true), $product_obj));
                    }
                    if (empty($group_min)) {
                        $group_min = wc_format_decimal(self::price_with_rules(get_post_meta($pid, '_sale_price', true), $product_obj));
                    }
                }

                if ($group_min > 0) {
                    $prices[] = $group_min;
                } elseif ($group_max > 0) {
                    $prices[] = $group_max;
                }
                $price_tiers = (array) json_decode(get_post_meta($pid, 'eib2bpro_price_tiers_group_' . $_GET["id"], true));

                if (empty($price_tiers)) {
                    $price_tiers = (array) json_decode(get_post_meta($pid, 'eib2bpro_price_tiers_group_b2c', true));
                }

                if (!empty($price_tiers)) {
                    $tiers_max = max(array_values($price_tiers));
                    $tiers_min = min(array_values($price_tiers));

                    if ($tiers_max > 0) {
                        $prices[] = self::price_with_rules($tiers_max, $product_obj);
                    }

                    if ($tiers_min > 0) {
                        $prices[] = self::price_with_rules($tiers_min, $product_obj);
                    }
                }
                if (0 < count($prices)) {
                    $prices = array_unique($prices);
                    $min = self::woocs(self::fix_price($product_obj, ['price' => min($prices)]));
                    $max = self::woocs(self::fix_price($product_obj, ['price' => max($prices)]));
                    if ($min !== $max) {
                        $range[$_GET["id"]][$product->get_id()] = wc_format_price_range($min, $max);
                    }
                    if (1 === eib2bpro_option('b2b_settings_tiers_show_range_from', 0)) {
                        $range[$_GET["id"]][$product->get_id()] = esc_html__('From ', 'eib2bpro') . wc_price($min);
                    }
                }
            }

            if ($save) {
                $cache_key = 'eib2bpro_price_range_' . substr($product->get_id(), -2) . '_' . self::currency() . '_' . implode('__', \EIB2BPRO\Rules\Site::$price_rules) . '_' . self::cache_vat_exempt();
                set_transient($cache_key, $range);
            }
        }
        if ($active_group) {
            return isset($range[$active_group][$product->get_id()]) ? $range[$active_group][$product->get_id()] : null;
        }
    }

    public static function quantity_input_args($args, $product)
    {

        if (!method_exists($product, 'get_id')) {
            return $args;
        }
        $product_id = $product->get_id();
        $parent = $product->get_parent_id();
        if ($parent && 0 < intval($parent)) {
            $product_id = $parent;
        }

        if (isset($_GET["id"])) {
            $group = $_GET["id"];
            $step = intval(get_post_meta($product_id, 'eib2bpro_product_qty_step_group_' . $group, true));
            $min = intval(get_post_meta($product_id, 'eib2bpro_product_qty_min_group_' . $group, true));
            $max = intval(get_post_meta($product_id, 'eib2bpro_product_qty_max_group_' . $group, true));

        }

        /*
          if (0 < $min) {
              $args['min_qty'] = $min;
              $args['min_value'] = $min;
              if (!is_cart()) {
                  $args['input_value'] = $min;
              }
          }

          if (0 < $max) {
              $args['max_qty'] = $max;
              $args['max_value'] = $max;
          }

          if (0 < $step) {
              $args['step'] = $step;
              if (0 === $min) {
                  $args['min_qty'] = $step;
                  $args['min_value'] = $step;
              }
              if (!is_cart() && $step > $min) {
                  $args['input_value'] = $step;
              }
          }*/
        return $args;
    }

    public static function qty_variation($args, $instance, $product)
    {
        $parent = $product->get_parent_id();
        if ($parent && 0 < intval($parent)) {
            $parent_product = wc_get_product($parent);
            return self::quantity_input_args($args, $parent_product);
        }

        return $args;
    }

    public static function qty_check()
    {

        $group = self::get_location_id_or_fallback();

        $passed = 1;

        $cart = \WC()->cart;
        if (is_object($cart)) {
            foreach ($cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                $product = wc_get_product($product_id);
                $qty = $cart_item["quantity"];
                if ($product) {
                    $step = intval(get_post_meta($product->get_id(), 'eib2bpro_product_qty_step_group_' . $group, true));
                    $min = intval(get_post_meta($product->get_id(), 'eib2bpro_product_qty_min_group_' . $group, true));
                    $max = intval(get_post_meta($product->get_id(), 'eib2bpro_product_qty_max_group_' . $group, true));

                    if (0 < $min) {
                        if ($qty < $min) {
                            $passed = 0;
                            \EIB2BPRO\Rules\Site::minmax_error(sprintf(
                                esc_html__('The quantity of product (%s) must be at least %s.', 'eib2bpro'),
                                get_the_title($product_id),
                                $min
                            ));
                        }
                    }

                    if (0 < $max) {
                        if ($qty > $max) {
                            $passed = 0;
                            \EIB2BPRO\Rules\Site::minmax_error(sprintf(
                                esc_html__('The quantity of product (%s) can not be more than %s.', 'eib2bpro'),
                                get_the_title($product_id),
                                $max
                            ));
                        }
                    }

                    if (0 < $step) {
                        if ($qty % $step !== 0) {
                            $passed = 0;
                            \EIB2BPRO\Rules\Site::minmax_error(sprintf(
                                esc_html__('The quantity of product (%s) must be %s and its multiples.', 'eib2bpro'),
                                get_the_title($product_id),
                                $step
                            ));
                        }
                    }
                }
            }
        }

        if (0 === $passed) {
            remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
        }
    }

    public static function restrict_if_not_visible()
    {
        if (is_preview() || !is_product()) {
            return;
        }

        if (!self::product_is_visible(true, get_the_ID())) {
            wp_redirect(get_permalink(wc_get_page_id('myaccount')));
            exit();
        }
    }
    public static function display_price_tax($option)
    {
        $group_id = 0;
        $group = Main::user('group');

        if ('b2c' === $group || 'guest' === $group) {
            $group_id = get_transient('eib2bpro_group_settings_id_' . $group);
            if (!$group_id) {
                $setting_post = Shipping::get_settings($group);
                if ($setting_post) {
                    $group_id = $setting_post->ID;
                    set_transient('eib2bpro_group_settings_id_' . $group, $group_id);
                }
            }
        } else {
            $group_id = Main::user('group');
        }

        if (0 < intval($group_id)) {
            $custom = eib2bpro_option('b2b_display_price_group_' . $group_id, 'default');
            if ('default' !== $custom) {
                $option = $custom;
            }
        }
        return $option;
    }


    public static function show_message()
    {
        $group_id = 0;
        $group = Main::user('group');

        if ('b2c' === $group || 'guest' === $group) {
            $group_id = get_transient('eib2bpro_group_settings_id_' . $group);
            if (!$group_id) {
                $setting_post = Shipping::get_settings($group);
                if ($setting_post) {
                    $group_id = $setting_post->ID;
                    set_transient('eib2bpro_group_settings_id_' . $group, $group_id);
                }
            }
        } else {
            $group_id = Main::user('group');
        }

        if (0 < intval($group_id)) {
            $message = eib2bpro_option('b2b_product_message_group_' . $group_id, false);
            if ($message) {
                echo '<span class="eib2bpro_global_product_message">' . eib2bpro_r(wp_kses_post($message)) . '</span>';
            }
        }
    }

    public static function shortcode_price($atts, $content = null)
    {
        global $product;

        $params = shortcode_atts(array(
            'type' => 'regular_price',
            'product' => 0,
            'group' => '',
            'group2' => '',
            'display' => '',
            'show' => '',
            'product_type' => 'all',
        ), $atts);

        $output = false;

        if ('regular_price' === $params['type']) {
            Main::$group = $params['group'];
            add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

            if ($product->is_type('simple') && ('all' === $params['product_type'] || 'simple' === $params['product_type'])) {
                if (intval($params['group']) > 0 && empty(get_post_meta($product->get_id(), 'eib2bpro_regular_price_group_' . $params['group'], true))) {
                    $output = false;
                } else {
                    if ($product->get_regular_price() > 0 || 'always' === $params['show']) {
                        $output = wc_price(self::fix_price($product, ['price' => $product->get_regular_price()]));
                    }
                }
            }

            if ($product->is_type('variable') && ('all' === $params['product_type'] || 'variable' === $params['product_type'])) {
                $prices = [];
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    $prices[$variation_id] = $variation_product->get_regular_price();
                }
                $min_price = self::fix_price($product, ['price' => min($prices)]);
                $max_price = self::fix_price($product, ['price' => max($prices)]);

                if ($min_price !== $max_price) {
                    $price = wc_format_price_range($min_price, $max_price);
                } else {
                    $price = wc_price($min_price);
                }

                $price = apply_filters('woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product);
                $output = $price;
            }
        }

        if ('sale_price' === $params['type']) {
            Main::$group = $params['group'];
            add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

            if ($product->is_type('simple') && ('all' === $params['product_type'] || 'simple' === $params['product_type'])) {
                if (intval($params['group']) > 0 && empty(get_post_meta($product->get_id(), 'eib2bpro_sale_price_group_' . $params['group'], true))) {
                    $output = false;
                } else {
                    if ($product->get_sale_price() > 0 || 'always' === $params['show']) {
                        $output = wc_price(self::fix_price($product, ['price' => $product->get_sale_price()]));
                    }
                }
            }

            if ($product->is_type('variable') && ('all' === $params['product_type'] || 'variable' === $params['product_type'])) {
                $prices = [];
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    if ($variation_product->is_on_sale()) {
                        $prices[$variation_id] = $variation_product->get_sale_price();
                    } else {
                        $prices[$variation_id] = $variation_product->get_regular_price();
                    }
                }
                $min_price = self::fix_price($product, ['price' => min($prices)]);
                $max_price = self::fix_price($product, ['price' => max($prices)]);

                if ($min_price !== $max_price) {
                    $price = wc_format_price_range($min_price, $max_price);
                } else {
                    $price = wc_price($min_price);
                }

                $price = apply_filters('woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product);
                $output = $price;
            }
        }

        if ('active_price' === $params['type']) {
            Main::$group = $params['group'];
            add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

            if ($product->is_type('simple') && ('all' === $params['product_type'] || 'simple' === $params['product_type'])) {
                if ($product->get_price() > 0 || 'always' === $params['show']) {
                    $output = wc_price(wc_get_price_to_display($product));
                }
            }
            if ($product->is_type('variable') && ('all' === $params['product_type'] || 'variable' === $params['product_type'])) {
                $prices = [];
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    $prices[$variation_id] = $variation_product->get_price();
                }
                $min_price = self::fix_price($product, ['price' => min($prices)]);
                $max_price = self::fix_price($product, ['price' => max($prices)]);

                if ($min_price !== $max_price) {
                    $price = wc_format_price_range($min_price, $max_price);
                } else {
                    $price = wc_price($min_price);
                }

                $price = apply_filters('woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product);
                $output = $price;
            }
        }


        if ('price' === $params['type']) {
            Main::$group = $params['group'];
            add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

            if ($product->is_type('simple') && ('all' === $params['product_type'] || 'simple' === $params['product_type'])) {
                if ('' === $product->get_price()) {
                    $price = apply_filters('woocommerce_empty_price_html', '', $product);
                } elseif ($product->is_on_sale()) {
                    $price = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $product->get_regular_price())), wc_get_price_to_display($product)) . $product->get_price_suffix();
                } else {
                    $price = wc_price(wc_get_price_to_display($product)) . $product->get_price_suffix();
                }
                $output = $price;
            }
            if ($product->is_type('variable') && ('all' === $params['product_type'] || 'variable' === $params['product_type'])) {
                $prices = [];
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    $prices[$variation_id] = $variation_product->get_price();
                }
                $min_price = min($prices);
                $max_price = max($prices);

                if ($min_price !== $max_price) {
                    $price = wc_format_price_range($min_price, $max_price);
                } else {
                    $price = wc_price($min_price);
                }

                $price = apply_filters('woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product);
                $output = self::calculate_price_range($product, (!empty($params['group']) ? $params['group'] : $_GET["id"]));
                if (empty($output)) {
                    $output = $price;
                }
            }
        }


        if ('discount' === $params['type']) {
            Main::$group = 'b2c';
            add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

            $b2c_price = $product->get_regular_price();

            if ($product->is_type('variable') && ('all' === $params['product_type'] || 'variable' === $params['product_type'])) {

                $prices = [];
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    $prices[$variation_id] = $variation_product->get_regular_price();
                }
                $min_price = min($prices);
                $max_price = max($prices);

                $b2c_price = $max_price;
            }

            Main::$group = $params['group2'];
            add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
            add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

            if ($product->is_type('simple') && ('all' === $params['product_type'] || 'simple' === $params['product_type'])) {
                $group_price = $product->get_price();

                $diff = $b2c_price - $group_price;
                if ($diff > 0) {

                    if ('' === $params['display']) {
                        $output = wc_price($diff);
                    }

                    if ('percentage' === $params['display']) {
                        $output = round($diff * 100 / $b2c_price);
                    }
                }
            }

            if ($product->is_type('variable') && ('all' === $params['product_type'] || 'variable' === $params['product_type'])) {

                $prices = [];
                $variations = $product->get_children();
                foreach ($variations as $variation_id) {
                    $variation_product = wc_get_product($variation_id);
                    $prices[$variation_id] = $variation_product->get_price();
                }
                $min_price = min($prices);
                $max_price = max($prices);

                $diff = $b2c_price - $min_price;
                if ($diff > 0) {

                    if ('' === $params['display']) {
                        $output = wc_price($diff);
                    }

                    if ('percentage' === $params['display']) {
                        $output = round($diff * 100 / $b2c_price);
                    }
                }
            }
        }

        Main::$group = '';
        add_filter('woocommerce_product_get_price', '\EIB2BPRO\Rules\Site::change_price', 9999, 2);
        add_filter('woocommerce_product_get_tax_class', '\EIB2BPRO\Rules\Site::tax_exemption_product', 10, 2);

        return $output;
    }

    public static function woocs($price)
    {
        if (class_exists('WOOCS')) { // WOOCS 

            global $WOOCS;
            $currrent = $WOOCS->current_currency;
            if ($currrent !== $WOOCS->default_currency) {
                $currencies = $WOOCS->get_currencies();
                $rate = $currencies[$currrent]['rate'];
                $price = $price * $rate;
            }
        }
        return $price;
    }

    public static function currency()
    {
        $current = get_woocommerce_currency();

        if (class_exists('WOOCS')) { // WOOCS 

            global $WOOCS;
            $currrent = $WOOCS->current_currency;
        }
        return strtolower($current);
    }

    public static function woocommerce_before_mini_cart()
    {
        \WC()->cart->calculate_totals();
    }

    public static function fix_price($product, $args = array())
    {
        global $woocommerce;

        if (is_a($woocommerce->customer, 'WC_Customer')) {
            $customer = \WC()->customer;
            $vat_exempt = $customer->is_vat_exempt();
        } else {
            $vat_exempt = false;
        }

        $args = wp_parse_args(
            $args,
            array(
                'qty' => 1,
                'price' => $product->get_price(),
            )
        );

        $price = $args['price'];
        $qty = $args['qty'];

        if ('incl' === get_option('woocommerce_tax_display_cart') && !$vat_exempt) {
            return
                wc_get_price_including_tax(
                    $product,
                    array(
                        'qty' => $qty,
                        'price' => $price,
                    )
                );
        } else {
            return
                wc_get_price_excluding_tax(
                    $product,
                    array(
                        'qty' => $qty,
                        'price' => $price,
                    )
                );
        }
    }

    public static function cache_vat_exempt()
    {
        global $woocommerce;

        if (is_a($woocommerce->customer, 'WC_Customer')) {
            $customer = \WC()->customer;
            $vat_exempt = $customer->is_vat_exempt();
        } else {
            $vat_exempt = false;
        }

        return $vat_exempt ? 'exempt_yes' : 'exempt_no';
    }
}