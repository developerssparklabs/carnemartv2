<?php

namespace EIB2BPRO\Products;

defined('ABSPATH') || exit;

class Main extends \EIB2BPRO\Products
{
    /**
     * Starts everything
     *
     * @return void
     */

    public static function run()
    {
        \EIB2BPRO\Admin::wc_engine();

        self::route();
    }

    /**
     * Router for sub pages
     *
     * @return void
     */

    public static function route()
    {
        switch (eib2bpro_get('action')) {

            case 'categories':
                self::categories();
                break;

            case 'attributes':
                self::attributes();
                break;

            case 'bulk_price':
                self::bulk_price();
                break;

            default:
                self::index();
                break;
        }
    }

    /**
     * Prepare filter array for query
     *
     * @param mixed $filter array of filter or false
     * @return array           new filter array
     */

    public static function filter($filter = false)
    {
        if (!$filter) {
            $filter['post_status'] = array('publish', 'private', 'pending', 'future');
            $filter['offset'] = 0;
            $filter['page'] = 1;
            $filter['q'] = '';
            $filter['orderby'] = "date";
            $filter['order'] = "DESC";

            if (0 < eib2bpro_option('b2b_offer_default_id', 0)) {
                $filter['not_in'] = eib2bpro_option('b2b_offer_default_id', 0);
            }
        }

        if (!isset($filter['limit'])) {
            $filter['limit'] = absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10));
        }

        if (eib2bpro_get('go', null)) {
            $filter['mode'] = 95;
        }

        if ('' !== eib2bpro_get('s', '')) {
            $filter['q'] = eib2bpro_get('s', '');
        }

        if ('' !== eib2bpro_get('category', '')) {
            $filter['category'] = eib2bpro_get('category', 0);
        }


        if ('trash' === eib2bpro_get('status', '')) {
            $filter['post_status'] = array('trash');
        }

        if ('private' === eib2bpro_get('status', '')) {
            $filter['post_status'] = array('private');
        }

        if ('draft' === eib2bpro_get('status', '')) {
            $filter['post_status'] = array('draft');
        }

        if ('pending' === eib2bpro_get('status', '')) {
            $filter['post_status'] = array('pending');
        }

        if (eib2bpro_get('pg', null)) {
            $filter['offset'] = (intval(eib2bpro_get('pg', 1)) - 1) * $filter['limit'];
        }

        if (eib2bpro_get('orderby')) {
            if (false !== strpos(eib2bpro_get('orderby', ''), 'meta_')) {
                $filter['orderby'] = "meta_value_num";
                $filter['orderby_meta_key'] = sanitize_sql_orderby(str_replace('meta_', '', eib2bpro_get('orderby', '')));
            } else {
                $filter['orderby'] = sanitize_sql_orderby(eib2bpro_get('orderby', ''));
            }


            $filter['order'] = 'ASC' === eib2bpro_get('order', 'ASC') ? 'ASC' : 'DESC';
        }

        return $filter;
    }

    /**
     * Main function
     *
     * @param mixed $filter array of filter
     *
     * @return eib2bpro_View
     */

    public static function index($filter = false)
    {
        global $wpdb;

        $filter = self::filter($filter);

        $pagination = array();
        $pagination['query'] = false;

        $products = array();

        $critical_stock = intval(
            $wpdb->get_var(
                $wpdb->prepare(
                    "
				SELECT COUNT(p.ID)
				FROM {$wpdb->prefix}posts as p
				INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
				WHERE p.post_type = 'product'
				AND p.post_status IN ('publish', 'private')
				AND pm.meta_key = '_manage_stock'
				AND pm.meta_value = 'yes'
				AND pm.post_id IN (SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE meta_key ='_stock' AND meta_value < %d)
				AND pm.post_id IN (SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE meta_key ='_stock_status' AND meta_value = 'instock')
				",
                    get_option('woocommerce_notify_low_stock_amount', 2)
                )
            )
        );


        $outof_stock = intval(
            $wpdb->get_var(
                $wpdb->prepare(
                    "
					SELECT COUNT(p.ID)
					FROM {$wpdb->prefix}posts as p
					INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
					WHERE p.post_type = 'product'
					AND p.post_status IN ('publish', 'private')
					AND pm.meta_key = '_stock_status'
					AND pm.meta_value = %s
					",
                    'outofstock'
                )
            )
        );

        $on_sales = intval(
            $wpdb->get_var(
                $wpdb->prepare(
                    "
						SELECT COUNT(p.ID)
						FROM {$wpdb->prefix}posts as p
						INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
						WHERE p.post_type = 'product'
						AND p.post_status IN ('publish', 'private')
						AND pm.meta_key = '_sale_price'
						AND pm.meta_value > %d
						",
                    0
                )
            )
        );

        // Getting 'Critical Stock'
        if ('-1' === eib2bpro_get('category')) {
            $_products['products'] = array();

            $critical_stocks_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "
								SELECT p.ID
								FROM {$wpdb->prefix}posts as p
								INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
								WHERE p.post_type = 'product'
								AND p.post_status IN ('publish', 'private')
								AND pm.meta_key = '_manage_stock'
								AND pm.meta_value = 'yes'
								AND pm.post_id IN (SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE meta_key ='_stock' AND meta_value < %d)
								AND pm.post_id IN (SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE meta_key ='_stock_status' AND meta_value = 'instock')
								LIMIT %d
								OFFSET %d
								",
                    get_option('woocommerce_notify_low_stock_amount', 2),
                    absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)),
                    absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)) * (intval(eib2bpro_get('pg', 1)) - 1)
                )
            );


            if ($critical_stocks_ids) {
                $critical_stocks_ids_filter['post_status'] = array('publish', 'private');
                $critical_stocks_ids_filter['in'] = implode(",", wp_list_pluck($critical_stocks_ids, "ID"));

                $_products = \WC()->api->WC_API_Products->get_products(null, null, $critical_stocks_ids_filter);
                $pagination = \EIB2BPRO\Admin::$api;
                $pagination['query']->found_posts = $critical_stock;
            }

            // Getting 'Out of Stock'
        } elseif ('-2' === eib2bpro_get('category')) {
            $_products['products'] = array();

            $outofstocks_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "
									SELECT p.ID
									FROM {$wpdb->prefix}posts as p
									INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
									WHERE p.post_type = 'product'
									AND p.post_status IN ('publish', 'private')
									AND pm.meta_key = '_stock_status'
									AND pm.meta_value = %s
									LIMIT %d
									OFFSET %d
									",
                    'outofstock',
                    absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)),
                    absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)) * (intval(eib2bpro_get('pg', 1)) - 1)
                )
            );

            if ($outofstocks_ids) {
                $outofstocks_ids_filter['post_status'] = array('publish', 'private');
                $outofstocks_ids_filter['in'] = implode(",", wp_list_pluck($outofstocks_ids, "ID"));

                $_products = \WC()->api->WC_API_Products->get_products(null, null, $outofstocks_ids_filter);
                $pagination = \EIB2BPRO\Admin::$api;
                $pagination['query']->found_posts = $outof_stock;
            } else {
                $pagination['query'] = null;
            }
        } elseif ('-4' === eib2bpro_get('category')) {
            $_products['products'] = array();

            $onsales_ids = $wpdb->get_results(
                $wpdb->prepare(
                    "
										SELECT p.ID
										FROM {$wpdb->prefix}posts as p
										INNER JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id
										WHERE p.post_type = 'product'
										AND p.post_status IN ('publish', 'private')
										AND pm.meta_key = '_sale_price'
										AND pm.meta_value > %d
										LIMIT %d
										OFFSET %d
										",
                    0,
                    absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)),
                    absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)) * (intval(eib2bpro_get('pg', 1)) - 1)
                )
            );

            if ($onsales_ids) {
                $onsales_ids_filter['post_status'] = array('publish', 'private');
                $onsales_ids_filter['in'] = implode(",", wp_list_pluck($onsales_ids, "ID"));

                $_products = \WC()->api->WC_API_Products->get_products(null, null, $onsales_ids_filter);
                $pagination = \EIB2BPRO\Admin::$api;
                $pagination['query']->found_posts = $outof_stock;
            } else {
                $pagination['query'] = null;
            }
        } else {
            if (eib2bpro_post('q', '') or eib2bpro_get('s', '')) {
                add_filter('posts_where', '\EIB2BPRO\Products\Main::extend_wp_query_where', 10, 2);
            }

            $_products = \WC()->api->WC_API_Products->get_products(null, null, $filter);
            $pagination = \EIB2BPRO\Admin::$api;

            if (eib2bpro_post('q', '') or eib2bpro_get('s', '')) {
                remove_filter('posts_where', '\EIB2BPRO\Products\Main::extend_wp_query_where');
            }
        }

        $search_categories = array();
        foreach ($_products['products'] as $product) {
            $products[$product['id']] = $product;
            $products[$product['id']]['categories'] = wc_get_object_terms($product['id'], 'product_cat');

            if ('variable' === $product['type']) {
                foreach ($product['variations'] as $variant) {
                    $products[$variant['id']] = $variant;
                    $products[$variant['id']]['parent'] = $product['id'];
                    $products[$variant['id']]['type'] = 'variant';
                }
            }
        }


        switch ($mode = (!empty($filter['mode']) ? absint($filter['mode']) : eib2bpro_option('products-mode', 1))) {

                // Woocommerce Native
            case 99:
                if (!\EIB2BPRO\Admin::is_full()) {
                    eib2bpro_frame(admin_url('edit.php?post_type=product'));
                } else {
                    wp_redirect(admin_url('edit.php?post_type=product'));
                }
                break;

                // Other menus
            case 95:
                echo eib2bpro_view(self::app('name'), self::app('mode'), 'list-95', array('iframe_url' => eib2bpro_get_submenu_url(eib2bpro_get('go'))));
                break;

                // Standart
            default:
            case 98:
            case 2:
            case 1:
                $categories = eib2bpro_group_by('parent', \WC()->api->WC_API_Products->get_product_categories()['product_categories']);
                echo eib2bpro_view(self::app('name'), 1, (98 === $mode) ? ' search' : 'list',  array('filter' => $filter, 'products' => $products, 'categories' => $categories, 'critical_stock' => $critical_stock, 'outof_stock' => $outof_stock, 'on_sales' => $on_sales, 'pagination' => $pagination['query'], 'ajax' => eib2bpro_is_ajax()));
                break;
        }
    }


    /**
     * extend_wp_query_where
     *
     * @return string
     */


    public static function extend_wp_query_where($where, $wp_query)
    {
        global $wpdb;

        if (eib2bpro_post('q', '')) {
            $energy_q = "%" . esc_sql(sanitize_text_field(wc_clean(eib2bpro_post('q', '')))) . "%";
        }

        if (eib2bpro_get('s', '')) {
            $energy_q = "%" . esc_sql(sanitize_text_field(wc_clean(eib2bpro_get('s', '')))) . "%";
        }

        if (isset($energy_q)) {
            $where .= " OR " . "({$wpdb->prefix}posts.ID IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE  {$wpdb->prefix}postmeta.meta_key = '_sku' AND {$wpdb->prefix}postmeta.meta_value LIKE '$energy_q'))";
        }
        return $where;
    }

    /**
     * Get categories
     *
     * @return void
     */

    public static function categories()
    {
        $_categories = \WC()->api->WC_API_Products->get_product_categories();
        $categories = eib2bpro_group_by('parent', $_categories['product_categories']);
        echo eib2bpro_view('products', 0, 'categories', array('categories' => $categories));
    }

    /**
     * Get attributes
     *
     * @return void
     */

    public static function attributes()
    {
        $attributes = \WC()->api->WC_API_Products->get_product_attributes();
        echo eib2bpro_view('products', 0, 'attributes', array('attributes' => $attributes));
    }

    /**
     * Bulk operations for setting prices
     *
     * @return null
     */

    public static function bulk_price()
    {
        if (!eib2bpro_get('ids')) {
            wp_die(-2);
        }

        $ids = explode('-', eib2bpro_get('ids', ''));

        if (!array($ids) || 0 === count($ids)) {
            wp_die(-3);
        }

        $ids = array_map('absint', $ids);

        $products = array();

        if ($_POST) :

            $type = absint(eib2bpro_post('type', 0));

            $percent = 0;
            $fixed = 0;

            switch ($type) {

                case 1:

                    $percent = floatval(eib2bpro_post('percent_1', 0));
                    $fixed = floatval(eib2bpro_post('fixed_1', 0));

                    break;

                case 2:

                    $percent = floatval(eib2bpro_post('percent_2', 0)) * -1;
                    $fixed = floatval(eib2bpro_post('fixed_2', 0)) * -1;

                    break;
            }


            foreach ($ids as $id) {
                $_product = wc_get_product(absint($id));

                $new = array();

                if ($_product) {
                    if ($_product->get_regular_price() > 0) {
                        $new['regular_price'] = floatval($_product->get_regular_price()) * (1 + $percent / 100) + $fixed;
                    }

                    if ($_product->get_sale_price() > 0) {
                        $new['sale_price'] = floatval($_product->get_sale_price()) * (1 + $percent / 100) + $fixed;
                    }

                    $o = \WC()->api->WC_API_Products->edit_product(absint($id), array(
                        'product' => $new
                    ));
                }
            }

        endif;

        foreach ($ids as $id) {
            $_product = wc_get_product(absint($id));
            if ($_product) {
                $products[$id] = $_product;
            }
        }

        echo eib2bpro_View::run('products/bulk-price', array('products' => $products));
    }


    /**
     * Get stock status label
     *
     * @param array $return
     * @return null
     * @since  1.0.0
     */

    public static function stock_status($return)
    {
        if (true === $return['product']['in_stock']) {
            if (true === $return['product']['managing_stock']) {
                eib2bpro_success(intval($return['product']['stock_quantity']));
            } else {
                eib2bpro_success('<span class="text-mute">∞</span>');
            }
        } else {
            eib2bpro_success('<span class="badge badge-danger">Out Of Stock</span>');
        }
    }
}
