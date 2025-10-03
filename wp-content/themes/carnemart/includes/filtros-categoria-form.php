<?php
/**
 * =========================
 *  Helpers / Normalizadores
 * =========================
 */
if (!function_exists('cm_get_current_product_cat_term')) {
  function cm_get_current_product_cat_term()
  {
    $qo = get_queried_object();
    return ($qo instanceof WP_Term && $qo->taxonomy === 'product_cat') ? $qo : null;
  }
}
if (!function_exists('cm_get_store_termid')) {
  function cm_get_store_termid(): string
  {
    return isset($_COOKIE['wcmlim_selected_location_termid'])
      ? preg_replace('/\D/', '', $_COOKIE['wcmlim_selected_location_termid'])
      : '';
  }
}
if (!function_exists('cm_norm_array_from_query')) {
  function cm_norm_array_from_query($v): array
  {
    if ($v === null)
      return [];
    if (is_array($v)) {
      $arr = $v; // puede llegar como etiquetas[]=a&etiquetas[]=b
    } else {
      $arr = explode(',', (string) $v); // o etiquetas=a,b
    }
    return array_filter(array_map('sanitize_title', $arr));
  }
}
if (!function_exists('cm_cat_slugs_to_ids')) {
  function cm_cat_slugs_to_ids(array $slugs): array
  {
    if (!$slugs)
      return [];
    $ids = [];
    foreach ($slugs as $slug) {
      $t = get_term_by('slug', $slug, 'product_cat');
      if ($t && !is_wp_error($t))
        $ids[] = (int) $t->term_id;
    }
    return $ids;
  }
}

/**
 * =========================
 *  Query builders
 * =========================
 */
if (!function_exists('cm_build_tax_query_for_context')) {
  function cm_build_tax_query_for_context(array $args = []): array
  {
    $current_cat = $args['current_cat'] ?? cm_get_current_product_cat_term();
    $cat_ids_sel = $args['cat_ids_sel'] ?? [];
    $tags_sel = $args['tags_sel'] ?? [];

    // Detecta si estás en archivo de etiqueta
    $qo = get_queried_object();
    $current_tag_slug = null;
    if ($qo instanceof WP_Term && $qo->taxonomy === 'product_tag') {
      $current_tag_slug = $qo->slug; // ← ESTE es el tag del archivo
    }

    $tq = ['relation' => 'AND'];

    // Categoría (seleccionadas o la del archivo)
    if (!empty($cat_ids_sel)) {
      $tq[] = [
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => array_map('intval', $cat_ids_sel),
        'include_children' => false,
      ];
    } elseif ($current_cat) {
      $tq[] = [
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => [(int) $current_cat->term_id],
        'include_children' => true,
      ];
    }

    // TAGS (union OR/IN). Inyecta también el tag del archivo si existe.
    $tags_filter = $tags_sel;
    if ($current_tag_slug)
      $tags_filter[] = $current_tag_slug;
    $tags_filter = array_values(array_unique(array_filter($tags_filter)));

    if (!empty($tags_filter)) {
      $tq[] = [
        'taxonomy' => 'product_tag',
        'field' => 'slug',
        'terms' => $tags_filter,
        'operator' => 'IN',  // OR lógico entre tags
      ];
    }

    // Excluir ocultos del catálogo
    if (function_exists('wc_get_product_visibility_term_ids')) {
      $vis = wc_get_product_visibility_term_ids();
      if (!empty($vis['exclude-from-catalog'])) {
        $tq[] = [
          'taxonomy' => 'product_visibility',
          'field' => 'term_taxonomy_id',
          'terms' => [(int) $vis['exclude-from-catalog']],
          'operator' => 'NOT IN',
        ];
      }
    }

    return $tq;
  }
}

if (!function_exists('cm_build_meta_query_for_store')) {
  function cm_build_meta_query_for_store(string $store, ?float $min_price, ?float $max_price): array
  {
    $mq = [
      'relation' => 'AND',
      ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='],
      ['key' => "wcmlim_stock_at_{$store}", 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>'],
      ['key' => "wcmlim_regular_price_at_{$store}", 'value' => 0, 'type' => 'DECIMAL', 'compare' => '>'],
    ];
    if ($min_price !== null) {
      $mq[] = ['key' => "wcmlim_regular_price_at_{$store}", 'value' => $min_price, 'type' => 'DECIMAL', 'compare' => '>='];
    }
    if ($max_price !== null) {
      $mq[] = ['key' => "wcmlim_regular_price_at_{$store}", 'value' => $max_price, 'type' => 'DECIMAL', 'compare' => '<='];
    }
    return $mq;
  }
}

/**
 * =========================
 *  Núcleo de rendimiento
 * =========================
 */
if (!function_exists('cm_master_product_ids_for_context')) {
  function cm_master_product_ids_for_context(): array
  {
    $store = cm_get_store_termid();
    if ($store === '')
      return [];

    $current_cat = cm_get_current_product_cat_term();
    $cats_sel = cm_norm_array_from_query($_GET['categorias'] ?? null);
    $cat_ids_sel = cm_cat_slugs_to_ids($cats_sel);
    $tags_sel = cm_norm_array_from_query($_GET['etiquetas'] ?? null);
    $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;

    $ck = 'cm_masterIDs_' . $store
      . '_cat_' . ($current_cat ? $current_cat->term_id : 0)
      . '_sels_' . (empty($cat_ids_sel) ? 'none' : implode('-', $cat_ids_sel))
      . '_tags_' . (empty($tags_sel) ? 'none' : md5(implode(',', $tags_sel)))
      . '_min_' . ($min_price ?? 'x')
      . '_max_' . ($max_price ?? 'x');

    $cached = get_transient($ck);
    if ($cached !== false && is_array($cached)) {
      return $cached;
    }

    // 1. Hacemos el query normal, pero sin filtrar por precio (solo stock y store)
    $meta_query = [
      'relation' => 'AND',
      ['key' => '_stock_status', 'value' => 'instock', 'compare' => '='],
      ['key' => "wcmlim_stock_at_{$store}", 'value' => 0, 'type' => 'NUMERIC', 'compare' => '>'],
    ];

    $q = new WP_Query([
      'post_type' => 'product',
      'post_status' => 'publish',
      'fields' => 'ids',
      'posts_per_page' => 4000,
      'no_found_rows' => true,
      'cache_results' => false,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
      'tax_query' => cm_build_tax_query_for_context([
        'current_cat' => $current_cat,
        'cat_ids_sel' => $cat_ids_sel,
        'tags_sel' => $tags_sel,
      ]),
      'meta_query' => $meta_query,
    ]);
    $ids = array_map('intval', $q->posts ?? []);

    // 2. Filtramos por precio real (tier o regular)
    $filtered = [];
    foreach ($ids as $pid) {
      // a) Intentar leer el tier especial
      $tier_key = "eib2bpro_price_tiers_group_{$store}";
      $tier_json = get_post_meta($pid, $tier_key, true);
      $price = null;
      if ($tier_json) {
        $arr = json_decode($tier_json, true);
        if (is_array($arr) && count($arr) > 0) {
          $first_val = reset($arr);
          if (is_numeric($first_val)) {
            $price = (float) $first_val;
          }
        }
      }
      // b) Si no hay tier válido, usar el regular
      if ($price === null) {
        $price = (float) get_post_meta($pid, "wcmlim_regular_price_at_{$store}", true);
      }
      // c) Filtrar por rango
      if ($min_price !== null && $price < $min_price)
        continue;
      if ($max_price !== null && $price > $max_price)
        continue;
      $filtered[] = $pid;
    }
    set_transient($ck, $filtered, 5 * MINUTE_IN_SECONDS);
    return $filtered;
  }
}

if (!function_exists('cm_counts_for_child_categories')) {
  function cm_counts_for_child_categories(array $master_ids, int $parent_term_id): array
  {
    global $wpdb;
    if (empty($master_ids))
      return [];

    $children_ids = get_terms([
      'taxonomy' => 'product_cat',
      'hide_empty' => false,
      'parent' => $parent_term_id,
      'fields' => 'ids',
      'orderby' => 'name',
      'order' => 'ASC',
    ]);
    if (empty($children_ids) || is_wp_error($children_ids))
      return [];

    // term_id -> term_taxonomy_id map
    $ttids = [];
    $termid_by_ttid = [];
    foreach ($children_ids as $term_id) {
      $term = get_term((int) $term_id, 'product_cat');
      if ($term && !is_wp_error($term)) {
        $ttids[] = (int) $term->term_taxonomy_id;
        $termid_by_ttid[(int) $term->term_taxonomy_id] = (int) $term->term_id;
      }
    }
    if (empty($ttids))
      return [];

    $ck = 'cm_cntChildCats_' . md5($parent_term_id . '|' . implode(',', $master_ids));
    $cached = get_transient($ck);
    if ($cached !== false) {
      return $cached;
    }

    $tr = $wpdb->term_relationships;
    $in_ids = implode(',', array_map('intval', $master_ids));
    $in_ttid = implode(',', array_map('intval', $ttids));

    $sql = "
      SELECT tr.term_taxonomy_id, COUNT(*) as c
      FROM {$tr} tr
      WHERE tr.object_id IN ({$in_ids})
        AND tr.term_taxonomy_id IN ({$in_ttid})
      GROUP BY tr.term_taxonomy_id
    ";
    $rows = $wpdb->get_results($sql, ARRAY_A);

    $out = [];
    foreach ($rows as $r) {
      $ttid = (int) $r['term_taxonomy_id'];
      $count = (int) $r['c'];
      $termid = $termid_by_ttid[$ttid] ?? 0;
      if ($termid)
        $out[$termid] = $count;
    }
    // asegura clave para todas (0 si no salió en SQL)
    foreach ($children_ids as $tid)
      if (!isset($out[$tid]))
        $out[$tid] = 0;

    set_transient($ck, $out, 5 * MINUTE_IN_SECONDS);
    return $out;
  }
}

if (!function_exists('cm_counts_for_tags')) {
  function cm_counts_for_tags(array $master_ids): array
  {
    global $wpdb;
    if (empty($master_ids))
      return [];

    $ck = 'cm_cntTags_' . md5(implode(',', $master_ids));
    $cached = get_transient($ck);
    if ($cached !== false)
      return $cached;

    $tr = $wpdb->term_relationships;
    $tt = $wpdb->term_taxonomy;
    $t = $wpdb->terms;
    $in = implode(',', array_map('intval', $master_ids));

    $sql = "
      SELECT t.term_id, COUNT(*) as c
      FROM {$tr} rel
      INNER JOIN {$tt} tax ON tax.term_taxonomy_id = rel.term_taxonomy_id AND tax.taxonomy = 'product_tag'
      INNER JOIN {$t}  t   ON t.term_id = tax.term_id
      WHERE rel.object_id IN ({$in})
      GROUP BY t.term_id
    ";
    $rows = $wpdb->get_results($sql, ARRAY_A);
    $map = [];
    foreach ($rows as $r)
      $map[(int) $r['term_id']] = (int) $r['c'];

    set_transient($ck, $map, 5 * MINUTE_IN_SECONDS);
    return $map;
  }
}

if (!function_exists('cm_price_range_for_store_context')) {
  function cm_price_range_for_store_context(): array
  {
    $store = cm_get_store_termid();
    if ($store === '')
      return ['min' => 0.0, 'max' => 0.0];

    $master = cm_master_product_ids_for_context();
    if (empty($master))
      return ['min' => 0.0, 'max' => 0.0];

    $ck = 'cm_priceRange_ctx_' . md5($store . '|' . implode(',', $master));
    $cached = get_transient($ck);
    if ($cached !== false)
      return $cached;

    global $wpdb;
    $pm = $wpdb->postmeta;
    $in = implode(',', array_map('intval', $master));
    $key = esc_sql("wcmlim_regular_price_at_{$store}");

    // Por el momento no usamos el MIN 
    // $sqlMin = "
    //   SELECT MIN(CAST(REPLACE(pm.meta_value, ',', '.') AS DECIMAL(10,2)))
    //   FROM {$pm} pm
    //   WHERE pm.post_id IN ({$in})
    //     AND pm.meta_key = '{$key}'
    //     AND pm.meta_value <> ''
    //     AND pm.meta_value REGEXP '^[0-9]+([.,][0-9]+)?$'
    //     AND CAST(REPLACE(pm.meta_value, ',', '.') AS DECIMAL(10,2)) > 0
    // ";
    $sqlMax = "
      SELECT MAX(CAST(REPLACE(pm.meta_value, ',', '.') AS DECIMAL(10,2)))
      FROM {$pm} pm
      WHERE pm.post_id IN ({$in})
        AND pm.meta_key = '{$key}'
        AND pm.meta_value <> ''
        AND pm.meta_value REGEXP '^[0-9]+([.,][0-9]+)?$'
        AND CAST(REPLACE(pm.meta_value, ',', '.') AS DECIMAL(10,2)) > 0
    ";

    // $min = (float) $wpdb->get_var($sqlMin);
    $max = (float) $wpdb->get_var($sqlMax);

    $out = ['min' => 0.0, 'max' => $max];
    set_transient($ck, $out, 5 * MINUTE_IN_SECONDS);
    return $out;
  }
}

/**
 * =========================
 *  Render de filtros
 * =========================
 */
$store = cm_get_store_termid();
$current_category = cm_get_current_product_cat_term();
$parent_id = $current_category ? (int) $current_category->term_id : 0;

$cats_sel_slugs = cm_norm_array_from_query($_GET['categorias'] ?? null);
$tags_sel_slugs = cm_norm_array_from_query($_GET['etiquetas'] ?? null);

$master_ids = cm_master_product_ids_for_context();       // set maestro rápido
$counts_cats = cm_counts_for_child_categories($master_ids, $parent_id);
$counts_tags = cm_counts_for_tags($master_ids);

// Subcategorías (siempre mostrarlas, aunque tengan 0 → disabled)
$subcategories = get_terms([
  'taxonomy' => 'product_cat',
  'hide_empty' => false,
  'parent' => $parent_id,
  'orderby' => 'name',
  'order' => 'ASC',
]);

// Tags candidatas (mostramos las que tienen count>0 o están seleccionadas)
$tags_all = get_terms([
  'taxonomy' => 'product_tag',
  'hide_empty' => false,
  'orderby' => 'name',
  'order' => 'ASC',
  'number' => 300,
]);

// Rango de precios contextual
$range = cm_price_range_for_store_context();
$min_abs = (int) floor($range['min'] ?? 0);
$max_abs = (int) ceil($range['max'] ?? 0);
$min_sel = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : $min_abs;
$max_sel = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : $max_abs;
if ($min_sel > $max_sel) {
  $t = $min_sel;
  $min_sel = $max_sel;
  $max_sel = $t;
}
$min_sel = max($min_abs, min($min_sel, $max_abs));
$max_sel = min($max_abs, max($max_sel, $min_abs));
?>

<form id="filtros-categorias" class="filter-container" method="GET">
  <?php if (!empty($subcategories) && !is_wp_error($subcategories)): ?>
    <div class="filter-group">
      <div class="filter-title" data-toggle="filter" data-target="#filter-subcategories">
        Categorías <i class="bi bi-chevron-down"></i>
      </div>
      <ul class="filter-content" id="filter-subcategories">
        <?php foreach ($subcategories as $c):
          $is_selected = in_array($c->slug, $cats_sel_slugs, true);
          $count = (int) ($counts_cats[$c->term_id] ?? 0);
          // Skip if count is 0 and not selected
          if ($count <= 0 && !$is_selected) {
            continue;
          }
          ?>
          <li>
            <input type="checkbox" class="form-check-input" id="cat-<?php echo esc_attr($c->slug); ?>" name="categorias[]"
              value="<?php echo esc_attr($c->slug); ?>" <?php checked($is_selected); ?>>
            <label class="form-check-label" for="cat-<?php echo esc_attr($c->slug); ?>">
              <?php echo esc_html($c->name); ?> (<?php echo $count; ?>)
            </label>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php
  // Oculta el filtro de giros si estamos en una página de etiqueta (product_tag)
  $is_tag_archive = false;
  $qo = get_queried_object();
  if ($qo instanceof WP_Term && $qo->taxonomy === 'product_tag') {
    $is_tag_archive = true;
  }
  ?>
  <?php if (!empty($tags_all) && !is_wp_error($tags_all) && !$is_tag_archive): ?>
    <div class="filter-group">
      <div class="filter-title" data-toggle="filter" data-target="#filter-giros">
        Giros de negocio <i class="bi bi-chevron-down"></i>
      </div>
      <ul class="filter-content" id="filter-giros">
        <?php foreach ($tags_all as $t):
          $is_selected = in_array($t->slug, $tags_sel_slugs, true);
          $count = (int) ($counts_tags[$t->term_id] ?? 0);
          if ($count <= 0 && !$is_selected)
            continue; // solo mostramos las útiles
          ?>
          <li>
            <input type="checkbox" class="form-check-input" id="giro-<?php echo esc_attr($t->slug); ?>" name="etiquetas[]"
              value="<?php echo esc_attr($t->slug); ?>" <?php checked($is_selected); ?>>
            <label class="form-check-label" for="giro-<?php echo esc_attr($t->slug); ?>">
              <?php echo esc_html($t->name); ?> (<?php echo $count; ?>)
            </label>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="filter-group">
    <div class="filter-title" data-toggle="filter" data-target="#filter-price">
      Rango de precios <i class="bi bi-chevron-down"></i>
    </div>
    <div class="filter-content" id="filter-price">
      <div class="box-range-price">
        <small>Selecciona el rango de precios</small>
        <div class="range-slider">
          <input type="range" min="<?php echo esc_attr($min_abs); ?>" max="<?php echo esc_attr($max_abs); ?>"
            value="<?php echo esc_attr($min_sel); ?>" id="min-price" name="min_price" step="1" />
          <input type="range" min="<?php echo esc_attr($min_abs); ?>" max="<?php echo esc_attr($max_abs); ?>"
            value="<?php echo esc_attr($max_sel); ?>" id="max-price" name="max_price" step="1" />
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
</form>

<style>
  /* opcional: estilo para categorías sin productos */
  .filter-content li.is-empty label {
    opacity: .55;
  }

  .filter-content li.is-empty input {
    pointer-events: none;
  }

  /* Estilos para el range slider */
  .range-slider {
    position: relative;
    width: 100%;
    height: 30px;
    margin: 15px 0;
  }

  .range-slider input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    position: absolute;
    width: 100%;
    height: 5px;
    background: none;
    pointer-events: none;
  }

  .range-slider input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    pointer-events: auto;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #0d6efd;
    cursor: pointer;
    margin-top: -7px;
  }

  .range-slider input[type="range"]::-moz-range-thumb {
    pointer-events: auto;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #0d6efd;
    cursor: pointer;
  }

  .slider-track {
    position: absolute;
    width: 100%;
    height: 5px;
    background: #ddd;
    top: 50%;
    transform: translateY(-50%);
    border-radius: 3px;
  }

  .slider-track::before {
    content: "";
    position: absolute;
    height: 100%;
    background: #0d6efd;
    border-radius: 3px;
  }
</style>

<script>
  (function () {
    const form = document.getElementById('filtros-categorias');
    if (!form) return;

    const minInput = form.querySelector('#min-price');
    const maxInput = form.querySelector('#max-price');
    const minLabel = document.getElementById('min-price-label');
    const maxLabel = document.getElementById('max-price-label');
    const track = form.querySelector('.slider-track');

    function formatPrice(value) {
      return '$' + (+value).toLocaleString('es-MX');
    }

    function updateTrack() {
      if (!track) return;
      const min = +minInput.value;
      const max = +maxInput.value;
      const range = maxInput.max - maxInput.min;
      const left = ((min - maxInput.min) / range) * 100;
      const right = ((max - maxInput.min) / range) * 100;

      track.style.background = `linear-gradient(
        to right,
        #ddd 0%, #ddd ${left}%,
        #0d6efd ${left}%, #0d6efd ${right}%,
        #ddd ${right}%, #ddd 100%
      )`;
    }

    function syncRangeLabels() {
      if (!minInput || !maxInput) return;
      let min = +minInput.value, max = +maxInput.value;

      if (min > max) {
        const t = min;
        min = max;
        max = t;
        minInput.value = min;
        maxInput.value = max;
      }

      if (minLabel) minLabel.textContent = formatPrice(min);
      if (maxLabel) maxLabel.textContent = formatPrice(max);

      updateTrack();
    }

    // Serializamos como "comma-separated": categorias=slug1,slug2  etiquetas=a,b
    function buildAndGo() {
      const params = new URLSearchParams();

      const cats = [];
      form.querySelectorAll('input[name="categorias[]"]:checked').forEach(el => cats.push(el.value));
      if (cats.length) params.set('categorias', cats.join(','));

      const tags = [];
      form.querySelectorAll('input[name="etiquetas[]"]:checked').forEach(el => tags.push(el.value));
      if (tags.length) params.set('etiquetas', tags.join(','));

      if (minInput && minInput.value !== '') params.set('min_price', minInput.value.trim());
      if (maxInput && maxInput.value !== '') params.set('max_price', maxInput.value.trim());

      // conserva orderby / s
      const keep = ['orderby', 's'];
      const current = new URLSearchParams(window.location.search);
      keep.forEach(k => { if (current.has(k)) params.set(k, current.get(k)); });

      const base = window.location.pathname.replace(/\?+.*/, '');
      const qs = params.toString();
      window.location.assign(qs ? base + '?' + qs : base);
    }

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      syncRangeLabels();
      buildAndGo();
    });

    ['input', 'change'].forEach(ev => {
      minInput?.addEventListener(ev, syncRangeLabels);
      maxInput?.addEventListener(ev, syncRangeLabels);
    });
    syncRangeLabels();
  })();
</script>