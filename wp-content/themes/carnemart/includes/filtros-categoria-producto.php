<?php
/**
 * Filtros: Categoría (product_cat) + Giro (product_tag) + Precio
 * Motor del filtrado sobre el loop principal de WooCommerce.
 */

/* Helper seguro para conocer la categoría actual (si la hay) */
if ( ! function_exists('cm_get_current_product_cat_term') ) {
  function cm_get_current_product_cat_term() {
    $qo = get_queried_object();
    if ( $qo instanceof WP_Term && isset($qo->taxonomy) && $qo->taxonomy === 'product_cat' ) {
      return $qo;
    }
    return null;
  }
}

/* ---------- 1) Aplica filtros al loop principal ---------- */
add_action('pre_get_posts', function ($q) {
  if (is_admin() || !$q->is_main_query()) return;
  if (!(is_post_type_archive('product') || is_tax(['product_cat','product_tag']))) return;

  $tax_query  = (array) $q->get('tax_query');
  $meta_query = (array) $q->get('meta_query');

  // Solo productos en stock (si no lo quieres, comenta este bloque)
  $meta_query[] = [
    'key'     => '_stock_status',
    'value'   => 'instock',
    'compare' => '='
  ];

  // Categorías seleccionadas (product_cat)
  if (!empty($_GET['categorias'])) {
    $cats = is_array($_GET['categorias']) ? $_GET['categorias'] : explode(',', $_GET['categorias']);
    $cats = array_map('sanitize_title', $cats);
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field'    => 'slug',
      'terms'    => $cats,
      'operator' => 'IN',
    ];
  }

  // Giros de negocio (product_tag)
  if (!empty($_GET['etiquetas'])) {
    $tags = is_array($_GET['etiquetas']) ? $_GET['etiquetas'] : explode(',', $_GET['etiquetas']);
    $tags = array_map('sanitize_title', $tags);
    $tax_query[] = [
      'taxonomy' => 'product_tag',
      'field'    => 'slug',
      'terms'    => $tags,
      'operator' => 'IN',
    ];
  }

  // Rango de precios (min_price / max_price) — compatible con 0 y cambios parciales
    $has_min = isset($_GET['min_price']) && $_GET['min_price'] !== '';
    $has_max = isset($_GET['max_price']) && $_GET['max_price'] !== '';

    if ($has_min || $has_max) {
    $min = $has_min ? (float) $_GET['min_price'] : null;
    $max = $has_max ? (float) $_GET['max_price'] : null;

    // Si vienen ambos y están invertidos, corrige
    if ($min !== null && $max !== null && $min > $max) {
    $tmp = $min; $min = $max; $max = $tmp;
    }

    // Asegura meta_query PLANO con relación AND (sin subarrays)
    if (!isset($meta_query['relation'])) $meta_query['relation'] = 'AND';

    if ($min !== null) {
    $meta_query[] = [
      'key'     => '_price',
      'value'   => $min,
      'compare' => '>=',
      'type'    => 'NUMERIC',
    ];
    }
    if ($max !== null) {
    $meta_query[] = [
      'key'     => '_price',
      'value'   => $max,
      'compare' => '<=',
      'type'    => 'NUMERIC',
    ];
    }
    }

  if (!empty($tax_query))  { $tax_query['relation'] = 'AND'; $q->set('tax_query',  $tax_query); }
  if (!empty($meta_query)) {                           $q->set('meta_query', $meta_query); }
});

/* ---------- 2) Preservar parámetros en paginación ---------- */
add_filter('paginate_links', function ($link) {
  $params = ['categorias','etiquetas','min_price','max_price'];
  foreach ($params as $p) {
    if (!empty($_GET[$p])) {
      $link  = remove_query_arg($p, $link);
      $value = is_array($_GET[$p]) ? implode(',', $_GET[$p]) : $_GET[$p];
      $link  = add_query_arg($p, $value, $link);
    }
  }
  return $link;
});

/* ---------- 3) Rango de precios eficiente (sin cargar todo) ---------- */
function get_filtered_price_range() {
  // Armamos tax_query según selección actual (cat/tag + categoría de contexto)
  $tax_query = [];
  $current_category = cm_get_current_product_cat_term();
  if ($current_category) {
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field'    => 'term_id',
      'terms'    => (int) $current_category->term_id,
    ];
  }
  if (!empty($_GET['categorias'])) {
    $cats = is_array($_GET['categorias']) ? $_GET['categorias'] : explode(',', $_GET['categorias']);
    $tax_query[] = [
      'taxonomy' => 'product_cat',
      'field'    => 'slug',
      'terms'    => array_map('sanitize_title', $cats),
      'operator' => 'IN',
    ];
  }
  if (!empty($_GET['etiquetas'])) {
    $tags = is_array($_GET['etiquetas']) ? $_GET['etiquetas'] : explode(',', $_GET['etiquetas']);
    $tax_query[] = [
      'taxonomy' => 'product_tag',
      'field'    => 'slug',
      'terms'    => array_map('sanitize_title', $tags),
      'operator' => 'IN',
    ];
  }
  if (!empty($tax_query)) $tax_query['relation'] = 'AND';

  // Consulta 1: MIN price
  $args_base = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 1,
    'fields'         => 'ids',
    'meta_key'       => '_price',
    'orderby'        => 'meta_value_num',
  ];
  if (!empty($tax_query)) $args_base['tax_query'] = $tax_query;

  $qmin = new WP_Query($args_base + ['order' => 'ASC']);
  $qmax = new WP_Query($args_base + ['order' => 'DESC']);

  $min = 0; $max = 0;
  if (!empty($qmin->posts)) { $min = (float) get_post_meta($qmin->posts[0], '_price', true); }
  if (!empty($qmax->posts)) { $max = (float) get_post_meta($qmax->posts[0], '_price', true); }

  return ['min' => $min, 'max' => $max > 0 ? $max : $min];
}
