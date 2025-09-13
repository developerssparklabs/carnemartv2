<?php
// Base URL para "Limpiar todo"
if (is_shop()) {
  $base_url = get_permalink(wc_get_page_id('shop'));
} elseif (is_tax(['product_cat', 'product_tag'])) {
  $term = get_queried_object();
  $base_url = $term ? get_term_link($term) : get_post_type_archive_link('product');
} else {
  $base_url = get_post_type_archive_link('product');
}

// Lee selección actual
$read = function ($k) {
  if (!isset($_GET[$k]) || $_GET[$k] === '') return [];
  $v = $_GET[$k];
  if (is_array($v)) return array_map('sanitize_title', $v);
  return array_map('sanitize_title', array_filter(explode(',', $v)));
};
$cats = $read('categorias');
$tags = $read('etiquetas');
$min_q = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : null;
$max_q = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;

$cat_terms = !empty($cats) ? get_terms(['taxonomy' => 'product_cat', 'slug' => $cats, 'hide_empty' => false]) : [];
$tag_terms = !empty($tags) ? get_terms(['taxonomy' => 'product_tag', 'slug' => $tags, 'hide_empty' => false]) : [];

$has_any = (!empty($cats) || !empty($tags) || $min_q !== null || $max_q !== null);

// Helpers para quitar chips
$remove_term = function ($param, $slug) use ($base_url) {
  $new = $_GET;
  $arr = isset($new[$param]) ? (is_array($new[$param]) ? $new[$param] : explode(',', $new[$param])) : [];
  $arr = array_map('sanitize_title', $arr);
  $arr = array_values(array_diff($arr, [$slug]));
  if (empty($arr)) unset($new[$param]);
  else $new[$param] = implode(',', $arr);
  unset($new['paged'], $new['page']);
  return esc_url(add_query_arg($new, $base_url));
};
$remove_price = function () use ($base_url) {
  $new = $_GET;
  unset($new['min_price'], $new['max_price'], $new['paged'], $new['page']);
  return esc_url(add_query_arg($new, $base_url));
};
?>

<div class="active-filters-card <?php echo $has_any ? 'is-filled' : 'is-empty'; ?>">
  <div class="af-header">
    <strong class="af-title">Filtros activos</strong>
  </div>

  <div class="af-body">
    <div class="af-row">
      <strong class="af-label">Categoría:</strong>
      <div class="af-chips">
        <?php if (!empty($cat_terms) && !is_wp_error($cat_terms)) : foreach ($cat_terms as $t): ?>
            <a class="af-chip" href="<?php echo $remove_term('categorias', $t->slug); ?>">
              <?php echo esc_html($t->name); ?><span class="af-x">&times;</span>
            </a>
          <?php endforeach;
        else: ?>
          <span class="af-placeholder">Sin selección</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="af-row">
      <strong class="af-label">Giros:</strong>
      <div class="af-chips">
        <?php if (!empty($tag_terms) && !is_wp_error($tag_terms)) : foreach ($tag_terms as $t): ?>
            <a class="af-chip af-chip--gray" href="<?php echo $remove_term('etiquetas', $t->slug); ?>">
              <?php echo esc_html($t->name); ?><span class="af-x">&times;</span>
            </a>
          <?php endforeach;
        else: ?>
          <span class="af-placeholder">Sin selección</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="af-row">
      <strong class="af-label">Precio:</strong>
      <div class="af-chips">
        <?php if ($min_q !== null || $max_q !== null):
          $min_txt = $min_q !== null ? '$' . number_format($min_q, 0, '.', ',') : 'min';
          $max_txt = $max_q !== null ? '$' . number_format($max_q, 0, '.', ',') : 'max'; ?>
          <a class="af-chip af-chip--yellow" href="<?php echo $remove_price(); ?>">
            <?php echo esc_html("$min_txt – $max_txt"); ?><span class="af-x">&times;</span>
          </a>
        <?php else: ?>
          <span class="af-placeholder">Sin selección</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="af-header">
    <div class="espacio"></div>
    <a class="af-clear-all w-100 text-center<?php echo $has_any ? '' : ' is-disabled'; ?>"
      href="<?php echo $has_any ? esc_url($base_url) : 'javascript:void(0)'; ?>">
      <i class="bi bi-arrow-clockwise"></i> Vaciar filtro
    </a>
  </div>


</div>