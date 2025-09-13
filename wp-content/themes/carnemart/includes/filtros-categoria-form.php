<form id="filtros-categorias" class="filter-container" method="GET">
<?php
if ( ! function_exists('cm_get_current_product_cat_term') ) {
  function cm_get_current_product_cat_term() {
    $qo = get_queried_object();
    if ( $qo instanceof WP_Term && isset($qo->taxonomy) && $qo->taxonomy === 'product_cat' ) return $qo;
    return null;
  }
}

$current_category = cm_get_current_product_cat_term();
$parent_id = $current_category ? (int) $current_category->term_id : 0;

/* === Categorías (hijas si estás dentro de una, o top-level en la tienda) === */
$subcategories = get_terms([
  'taxonomy'   => 'product_cat',
  'hide_empty' => true,
  'parent'     => $parent_id,
]);

$categorias_sel = isset($_GET['categorias']) ? $_GET['categorias'] : [];
if (!is_array($categorias_sel)) $categorias_sel = explode(',', $categorias_sel);
$categorias_sel = array_map('sanitize_title', $categorias_sel);

if (!empty($subcategories)) : ?>
  <div class="filter-group">
    <div class="filter-title" data-toggle="filter" data-target="#filter-subcategories">
      Categorías <i class="bi bi-chevron-down"></i>
    </div>
    <ul class="filter-content" id="filter-subcategories">
      <?php foreach ($subcategories as $c): ?>
      <li>
        <input type="checkbox"
          class="form-check-input"
          id="cat-<?php echo esc_attr($c->slug); ?>"
          name="categorias[]"
          value="<?php echo esc_attr($c->slug); ?>"
          <?php checked(in_array($c->slug, $categorias_sel, true)); ?>>
        <label class="form-check-label" for="cat-<?php echo esc_attr($c->slug); ?>">
          <?php echo esc_html($c->name); ?> (<?php echo esc_html($c->count); ?>)
        </label>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php
/* === Giros de negocio (product_tag) === */
$product_tags = get_terms([
  'taxonomy'   => 'product_tag',
  'hide_empty' => true,
  'orderby'    => 'name',
  'order'      => 'ASC',
  'number'     => 100,
]);

$etiquetas_sel = isset($_GET['etiquetas']) ? $_GET['etiquetas'] : [];
if (!is_array($etiquetas_sel)) $etiquetas_sel = explode(',', $etiquetas_sel);
$etiquetas_sel = array_map('sanitize_title', $etiquetas_sel);

if (!empty($product_tags) && !is_wp_error($product_tags)) : ?>
  <div class="filter-group">
    <div class="filter-title" data-toggle="filter" data-target="#filter-giros">
      Giros de negocio <i class="bi bi-chevron-down"></i>
    </div>
    <ul class="filter-content" id="filter-giros">
      <?php foreach ($product_tags as $t): ?>
      <li>
        <input type="checkbox"
          class="form-check-input"
          id="giro-<?php echo esc_attr($t->slug); ?>"
          name="etiquetas[]"
          value="<?php echo esc_attr($t->slug); ?>"
          <?php checked(in_array($t->slug, $etiquetas_sel, true)); ?>>
        <label class="form-check-label" for="giro-<?php echo esc_attr($t->slug); ?>">
          <?php echo esc_html($t->name); ?> (<?php echo esc_html($t->count); ?>)
        </label>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php
/* === Rango de precios (usa helper eficiente del engine) === */
$price_range = get_filtered_price_range();
$min_abs = floor($price_range['min']);
$max_abs = ceil($price_range['max']);
$min_sel = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : $min_abs;
$max_sel = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : $max_abs;
?>
<div class="filter-group">
  <div class="filter-title" data-toggle="filter" data-target="#filter-price">
    Rango de precios <i class="bi bi-chevron-down"></i>
  </div>
  <div class="filter-content" id="filter-price">
    <div class="box-range-price">
      <small>Selecciona el rango de precios</small>
      <div class="range-slider">
        <input type="range" min="<?php echo esc_attr($min_abs); ?>" max="<?php echo esc_attr($max_abs); ?>" value="<?php echo esc_attr($min_sel); ?>" id="min-price" name="min_price" />
        <input type="range" min="<?php echo esc_attr($min_abs); ?>" max="<?php echo esc_attr($max_abs); ?>" value="<?php echo esc_attr($max_sel); ?>" id="max-price" name="max_price" />
        <div class="slider-track"></div>
      </div>
      <div class="price-labels">
        <span id="min-price-label">$<?php echo number_format($min_sel, 0, '.', ','); ?></span>
        <span id="max-price-label">$<?php echo number_format($max_sel, 0, '.', ','); ?></span>
      </div>
    </div>
  </div>
</div>

<button type="submit" class="cta-inline mt-3 w-100"><b>Filtrar</b></button>

<script>
(function(){
  const f = document.getElementById('filtros-categorias');
  const mi = f?.querySelector('#min-price');
  const ma = f?.querySelector('#max-price');
  const ml = document.getElementById('min-price-label');
  const xl = document.getElementById('max-price-label');
  function upd(){
    if(!mi||!ma) return;
    let a=+mi.value, b=+ma.value;
    if(a>b){ const t=a; a=b; b=t; mi.value=a; ma.value=b; }
    if(ml) ml.textContent = '$'+a.toLocaleString('en-US');
    if(xl) xl.textContent = '$'+b.toLocaleString('en-US');
  }
  ['input','change'].forEach(ev=>{ mi?.addEventListener(ev,upd); ma?.addEventListener(ev,upd);});
  upd();
})();
</script>
