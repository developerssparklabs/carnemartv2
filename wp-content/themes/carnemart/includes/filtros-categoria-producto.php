<?php
/**
 * Filtros: Categoría (product_cat) + Giro (product_tag) + Precio
 * Motor del filtrado sobre el loop principal de WooCommerce.
 * Con soporte de tienda (wcmlim_* metas) y preservación de parámetros.
 */

/* =========================
 * Helpers
 * ========================= */
if (!function_exists('cm_get_current_product_cat_term')) {
  function cm_get_current_product_cat_term()
  {
    $qo = get_queried_object();
    return ($qo instanceof WP_Term && isset($qo->taxonomy) && $qo->taxonomy === 'product_cat') ? $qo : null;
  }
}

if (!function_exists('cm_get_store_termid')) {
  function cm_get_store_termid(): string
  {
    return isset($_COOKIE['wcmlim_selected_location_termid'])
      ? preg_replace('/\D/', '', (string) $_COOKIE['wcmlim_selected_location_termid'])
      : '';
  }
}

if (!function_exists('cm_norm_array_from_query')) {
  function cm_norm_array_from_query($v): array
  {
    if ($v === null)
      return [];
    $arr = is_array($v) ? $v : explode(',', (string) $v);
    return array_filter(array_map('sanitize_title', $arr));
  }
}

/* =========================
 * 1) Aplica filtros al loop principal
 * ========================= */
add_action('pre_get_posts', function (WP_Query $q) {
  if (is_admin() || !$q->is_main_query())
    return;
  if (!(is_post_type_archive('product') || is_tax(['product_cat', 'product_tag'])))
    return;

  // ---- Lectura de filtros de la URL ----
  $cats_sel_slugs = cm_norm_array_from_query($_GET['categorias'] ?? null);
  $tags_sel_slugs = cm_norm_array_from_query($_GET['etiquetas'] ?? null);

  $has_min = (isset($_GET['min_price']) && $_GET['min_price'] !== '');
  $has_max = (isset($_GET['max_price']) && $_GET['max_price'] !== '');
  $min = $has_min ? (float) $_GET['min_price'] : null;
  $max = $has_max ? (float) $_GET['max_price'] : null;
  if ($min !== null && $max !== null && $min > $max) {
    $t = $min;
    $min = $max;
    $max = $t;
  }

  $current_cat = cm_get_current_product_cat_term();
  $store = cm_get_store_termid();

  // ---- TAX QUERY ----
  $tax_query = (array) $q->get('tax_query');
  if (!isset($tax_query['relation']))
    $tax_query['relation'] = 'AND';

  // Categorías:
  // Si el usuario seleccionó, usamos esas SIN hijos; si no seleccionó y estamos en
  // archivo de categoría, usamos la categoría del contexto CON hijos.
  if (!empty($cats_sel_slugs)) {
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field' => 'slug',
      'terms' => $cats_sel_slugs,
      'operator' => 'IN',
      'include_children' => false,
    ];
  } elseif ($current_cat) {
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field' => 'term_id',
      'terms' => [(int) $current_cat->term_id],
      'include_children' => true,
    ];
  }

  // Giros (OR / IN)
  if (!empty($tags_sel_slugs)) {
    $tax_query[] = [
      'taxonomy' => 'product_tag',
      'field' => 'slug',
      'terms' => $tags_sel_slugs,
      'operator' => 'IN',
    ];
  }

  // Excluir ocultos del catálogo
  if (function_exists('wc_get_product_visibility_term_ids')) {
    $vis = wc_get_product_visibility_term_ids();
    if (!empty($vis['exclude-from-catalog'])) {
      $tax_query[] = [
        'taxonomy' => 'product_visibility',
        'field' => 'term_taxonomy_id',
        'terms' => [(int) $vis['exclude-from-catalog']],
        'operator' => 'NOT IN',
      ];
    }
  }

  $q->set('tax_query', $tax_query);

  // ---- META QUERY ----
  $meta_query = (array) $q->get('meta_query');
  if (!isset($meta_query['relation']))
    $meta_query['relation'] = 'AND';

  // Siempre en stock
  $meta_query[] = ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='];
  $meta_query[] = ['key' => 'product_step', 'value' => 0.1, 'type' => 'DECIMAL(10,2)', 'compare' => '>'];
  // $meta_query[] = [
  //   'relation' => 'OR',
  //   [
  //     'key'     => 'product_step',
  //     'value'   => 0.1,
  //     'type'    => 'DECIMAL(10,2)',
  //     'compare' => '>'
  //   ],
  //   [
  //     'key'     => 'ri_quantity_step',
  //     'value'   => 0.1,
  //     'type'    => 'DECIMAL(10,2)',
  //     'compare' => '>'
  //   ]
  // ];

  if ($store !== '') {
    // Filtrado por tienda
    $meta_query[] = ['key' => "wcmlim_stock_at_{$store}", 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>'];
    $meta_query[] = ['key' => "wcmlim_regular_price_at_{$store}", 'value' => 0, 'type' => 'DECIMAL', 'compare' => '>'];

    if ($min !== null) {
      $meta_query[] = ['key' => "wcmlim_regular_price_at_{$store}", 'value' => $min, 'type' => 'DECIMAL', 'compare' => '>='];
    }
    if ($max !== null) {
      $meta_query[] = ['key' => "wcmlim_regular_price_at_{$store}", 'value' => $max, 'type' => 'DECIMAL', 'compare' => '<='];
    }
  } else {
    // Fallback: usar _price si no hay cookie de tienda
    if ($min !== null)
      $meta_query[] = ['key' => '_price', 'value' => $min, 'type' => 'NUMERIC', 'compare' => '>='];
    if ($max !== null)
      $meta_query[] = ['key' => '_price', 'value' => $max, 'type' => 'NUMERIC', 'compare' => '<='];
  }

  $q->set('meta_query', $meta_query);
});

/* =========================
 * 2) Preservar parámetros en paginación
 * ========================= */
add_filter('paginate_links', function ($link) {
  $params = ['categorias', 'etiquetas', 'min_price', 'max_price'];
  foreach ($params as $p) {
    if (!empty($_GET[$p])) {
      $link = remove_query_arg($p, $link);
      $value = is_array($_GET[$p]) ? implode(',', array_map('sanitize_title', $_GET[$p])) : sanitize_text_field($_GET[$p]);
      $link = add_query_arg($p, $value, $link);
    }
  }
  return $link;
});

/* =========================
 * 3) Rango de precios eficiente (2 queries)
 *    -> Usa wcmlim_regular_price_at_{store} si hay tienda
 *    -> Fallback a _price si no hay tienda
 * ========================= */
function get_filtered_price_range()
{
  $store = cm_get_store_termid();
  $current_cat = cm_get_current_product_cat_term();

  // --- TAX QUERY de contexto ---
  $tax_query = ['relation' => 'AND'];

  // Categoría de contexto o seleccionadas
  $cats_sel_slugs = cm_norm_array_from_query($_GET['categorias'] ?? null);
  if (!empty($cats_sel_slugs)) {
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field' => 'slug',
      'terms' => $cats_sel_slugs,
      'operator' => 'IN',
      'include_children' => false,
    ];
  } elseif ($current_cat) {
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field' => 'term_id',
      'terms' => [(int) $current_cat->term_id],
      'include_children' => true,
    ];
  }

  // Giros (OR / IN)
  $tags_sel_slugs = cm_norm_array_from_query($_GET['etiquetas'] ?? null);
  if (!empty($tags_sel_slugs)) {
    $tax_query[] = [
      'taxonomy' => 'product_tag',
      'field' => 'slug',
      'terms' => $tags_sel_slugs,
      'operator' => 'IN',
    ];
  }

  // Excluir ocultos del catálogo
  if (function_exists('wc_get_product_visibility_term_ids')) {
    $vis = wc_get_product_visibility_term_ids();
    if (!empty($vis['exclude-from-catalog'])) {
      $tax_query[] = [
        'taxonomy' => 'product_visibility',
        'field' => 'term_taxonomy_id',
        'terms' => [(int) $vis['exclude-from-catalog']],
        'operator' => 'NOT IN',
      ];
    }
  }

  // --- Args base ---
  $args_base = [
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'no_found_rows' => true,
    'fields' => 'ids',
    'cache_results' => false,
    'update_post_meta_cache' => false,
    'update_post_term_cache' => false,
    'tax_query' => $tax_query,
    'meta_query' => [
      'relation' => 'AND',
      ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='],
    ],
  ];

  if ($store !== '') {
    $meta_key = "wcmlim_regular_price_at_{$store}";
    $args_min = $args_base;
    $args_max = $args_base;

    $args_min['meta_key'] = $meta_key;
    $args_min['orderby'] = 'meta_value_num';
    $args_min['order'] = 'ASC';
    $args_min['meta_query'][] = ['key' => $meta_key, 'value' => 0, 'type' => 'DECIMAL', 'compare' => '>'];
    $args_min['meta_query'][] = ['key' => "wcmlim_stock_at_{$store}", 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>'];

    $args_max['meta_key'] = $meta_key;
    $args_max['orderby'] = 'meta_value_num';
    $args_max['order'] = 'DESC';
    $args_max['meta_query'][] = ['key' => $meta_key, 'value' => 0, 'type' => 'DECIMAL', 'compare' => '>'];
    $args_max['meta_query'][] = ['key' => "wcmlim_stock_at_{$store}", 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>'];

    $qmin = new WP_Query($args_min);
    $qmax = new WP_Query($args_max);

    $min = 0.0;
    $max = 0.0;
    if (!empty($qmin->posts))
      $min = (float) get_post_meta($qmin->posts[0], $meta_key, true);
    if (!empty($qmax->posts))
      $max = (float) get_post_meta($qmax->posts[0], $meta_key, true);

  } else {
    // Fallback: _price
    $args_min = $args_base + ['meta_key' => '_price', 'orderby' => 'meta_value_num', 'order' => 'ASC'];
    $args_max = $args_base + ['meta_key' => '_price', 'orderby' => 'meta_value_num', 'order' => 'DESC'];

    $qmin = new WP_Query($args_min);
    $qmax = new WP_Query($args_max);

    $min = 0.0;
    $max = 0.0;
    if (!empty($qmin->posts))
      $min = (float) get_post_meta($qmin->posts[0], '_price', true);
    if (!empty($qmax->posts))
      $max = (float) get_post_meta($qmax->posts[0], '_price', true);
  }

  // Normaliza (evita min/max = 0 cuando no hay resultados)
  if ($max <= 0 && $min > 0)
    $max = $min;
  return ['min' => $min, 'max' => $max];
}