<?php
if (!class_exists('Product_Controller')) {

    class Product_Controller
    {
        private string $namespace = 'wcmlim/v1';

        public function register_routes()
        {
            register_rest_route($this->namespace, '/products', [
                'methods' => 'GET',
                'callback' => [$this, 'get_products'],
                'permission_callback' => '__return_true',
                'args' => [
                    'idStore' => [
                        'description' => 'Term ID de la tienda (location_group). 0 = sin tienda.',
                        'type' => 'integer',
                        'required' => false,
                        'sanitize_callback' => 'absint',
                    ],
                    'per_page' => [
                        'description' => 'Productos por página.',
                        'type' => 'integer',
                        'default' => 12,
                        'sanitize_callback' => 'absint',
                    ],
                    'page' => [
                        'description' => 'Página (1..n).',
                        'type' => 'integer',
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ],
                    'orderby' => [
                        'description' => 'date|modified|title|menu_order|rand',
                        'type' => 'string',
                        'default' => 'date',
                        'sanitize_callback' => function ($v) {
                            $v = sanitize_key($v ?: 'date');
                            return in_array($v, ['date', 'modified', 'title', 'menu_order', 'rand'], true) ? $v : 'date';
                        },
                    ],
                    'order' => [
                        'description' => 'ASC|DESC (ignorado si orderby=rand)',
                        'type' => 'string',
                        'default' => 'DESC',
                        'sanitize_callback' => function ($v) {
                            $v = strtoupper((string) $v);
                            return $v === 'ASC' ? 'ASC' : 'DESC';
                        },
                    ],
                    'top_sellers' => [
                        'required' => false,
                        'type' => 'boolean',
                        'default' => false
                    ]
                ],
            ]);
        }

        /**
         * Retrieves a paginated list of products for a specific store via the REST API endpoint `/wp-json/wcmlim/v1/products`.
         *
         * Parameters (from WP_REST_Request):
         * - idStore (int): Store ID to filter products by location. If 0, uses global stock and price.
         * - per_page (int): Number of products per page (default: 12, minimum: 1).
         * - page (int): Page number for pagination (default: 1, minimum: 1).
         * - orderby (string): Field to order by (options: date, modified, title, menu_order, rand, sales; default: date).
         * - order (string): Sort direction (ASC or DESC; default: DESC).
         * - top_sellers (bool): If true, filters and sorts products by sales (default: false).
         *
         * Functionality:
         * - Applies meta queries to filter products by stock status, minimum step, and price.
         * - Supports store-specific or global stock and price filtering.
         * - Optionally filters and sorts by sales for top sellers.
         * - Implements caching via WordPress transients for performance.
         * - Returns a lightweight payload with product ID, name, permalink, image, price, and product step.
         * - Includes sales data if requested.
         * - Indicates if more products are available for pagination.
         *
         * Response:
         * - ok (bool): Operation status.
         * - has_more (bool): Whether there are more products beyond the current page.
         * - count (int): Number of products returned.
         * - products (array): List of products with basic details.
         *
         * @param WP_REST_Request $request The REST API request object.
         * @return \WP_REST_Response JSON response containing product data.
         */
        /**
         * GET /wp-json/wcmlim/v1/products
         * Params: idStore, per_page, page, orderby, order
         */
        public function get_products(WP_REST_Request $request): \WP_REST_Response
        {
            $storeId = (int) $request->get_param('idStore');
            $perPage = min(20, max(1, (int) ($request->get_param('per_page') ?: 8)));
            $page = max(1, (int) ($request->get_param('page') ?: 1));
            $orderby = sanitize_key($request->get_param('orderby') ?: 'date'); // date|modified|title|menu_order|rand|sales
            $order = strtoupper($request->get_param('order') ?: 'DESC');
            $order = ($order === 'ASC') ? 'ASC' : 'DESC';
            $topSellers = (bool) ($request->get_param('top_sellers') ?? false);
            $minStep = 0.5;
            $ttl = 60;

            // meta de ventas: por tienda o global
            $salesKey = $storeId > 0 ? "wcmlim_sales_at_{$storeId}" : 'total_sales';

            // cache
            $cache_key = 'wcmlim:products:' . md5(json_encode([$storeId, $perPage, $page, $orderby, $order, $topSellers]));
            if ($ttl > 0) {
                $cached = get_transient($cache_key);
                if (is_array($cached)) {
                    return new \WP_REST_Response($cached, 200);
                }
            }

            // base: mínimo de joins
            $meta_query = [
                'relation' => 'AND',
                ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='],
                ['key' => 'product_step', 'value' => $minStep, 'type' => 'NUMERIC', 'compare' => '>='],
            ];

            if ($storeId > 0) {
                $meta_query[] = ['key' => "wcmlim_stock_at_{$storeId}", 'value' => '0', 'compare' => '>', 'type' => 'NUMERIC'];
                $meta_query[] = ['key' => "wcmlim_regular_price_at_{$storeId}", 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC'];
            } else {
                $meta_query[] = ['key' => '_stock', 'value' => '1', 'compare' => '>', 'type' => 'NUMERIC'];
                $meta_query[] = ['key' => '_regular_price', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC'];
            }

            $args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'fields' => 'ids',
                'posts_per_page' => $perPage + 1,
                'paged' => $page,
                'orderby' => $orderby === 'rand' ? 'date' : $orderby,
                'order' => $order,
                'ignore_sticky_posts' => true,
                'no_found_rows' => true,
                'cache_results' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'meta_query' => $meta_query,
            ];

            // ordenar / filtrar por ventas
            if ($topSellers) {
                // ahora sí: >= min_sales (default 1)
                $args['meta_query']['sales_clause'] = [
                    'key' => $salesKey,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                    'value' => 1,
                ];
                $args['orderby'] = [
                    'sales_clause' => 'DESC',
                    'date' => $order, // desempate
                ];
            }

            $q = new WP_Query($args);
            $ids = $q->posts;

            if (empty($ids)) {
                $out = ['ok' => true, 'has_more' => false, 'count' => 0, 'products' => []];
                if ($ttl > 0)
                    set_transient($cache_key, $out, $ttl);
                return new \WP_REST_Response($out, 200);
            }

            $hasMore = count($ids) > $perPage;
            $ids = array_slice($ids, 0, $perPage);

            // payload ligero
            $products = [];
            foreach ($ids as $id) {
                $name = get_the_title($id);
                $link = get_permalink($id);
                $thumb_id = (int) get_post_meta($id, '_thumbnail_id', true);
                $thumb = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';

                $price = $storeId > 0
                    ? (get_post_meta($id, "wcmlim_regular_price_at_{$storeId}", true) ?: 0)
                    : (get_post_meta($id, '_regular_price', true) ?: 0);
                $step = (get_post_meta($id, 'product_step', true) ?: 0);
                $row = [
                    'id' => (int) $id,
                    'name' => (string) $name,
                    'permalink' => (string) ($link ?: ''),
                    'image' => (string) ($thumb ?: ''),
                    'price' => $price,
                    'product_step' => $step,
                ];

                if ($topSellers || $orderby === 'sales') {
                    $salesRaw = get_post_meta($id, $salesKey, true);
                    $row['sales'] = is_numeric($salesRaw) ? (int) $salesRaw : 0;
                }

                $products[] = $row;
            }

            $out = [
                'ok' => true,
                'has_more' => $hasMore,
                'count' => count($products),
                'products' => $products,
            ];

            if ($ttl > 0) {
                set_transient($cache_key, $out, $ttl);
            }
            return new \WP_REST_Response($out, 200);
        }
    }
}