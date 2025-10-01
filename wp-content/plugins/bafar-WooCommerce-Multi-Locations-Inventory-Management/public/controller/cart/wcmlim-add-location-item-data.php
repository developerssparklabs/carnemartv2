<?php
// Helpers
$to_float = function($v, $dec = 3) {
    if ($v === '' || $v === null) return 0.0;
    if (is_string($v)) $v = str_replace(',', '.', $v);
    return (float) wc_format_decimal($v, $dec);
};
$get_terms_root = function() {
    $exclude = get_option("wcmlim_exclude_locations_from_frontend");
    $args = ['taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0];
    if (!empty($exclude)) $args['exclude'] = $exclude;
    return get_terms($args);
};
$get_term_by_id = function($term_id) {
    if (!$term_id) return null;
    $t = get_term($term_id, 'locations');
    return ( $t && ! is_wp_error($t) ) ? $t : null;
};
$stock_at_location = function($product_id, $term_id) use ($to_float) {
    $meta_key = "wcmlim_stock_at_{$term_id}";
    return (int) $to_float(get_post_meta($product_id, $meta_key, true), 3);
};

// -------- Manage stock check (igual que tu código) --------
if ($variation_id == "0") {
    $manage_stock = get_post_meta($product_id, '_manage_stock', true);
} else {
    $manage_stock = get_post_meta($variation_id, '_manage_stock', true);
}
if ($manage_stock === "no") {
    return $cart_item_data;
}

$_isrspon = get_option("wcmlim_enable_price");

// -------- Obtener producto y precio base --------
$product = wc_get_product($variation_id ?: $product_id);
if ($product->is_type('simple') || $product->is_type('variation') || $product->is_type('variable')) {
    $productPrice  = $product->get_price();
    $sProductPrice = wc_price($productPrice); // (lo mantengo por compatibilidad con tu UI)
}

// -------- Resolver ubicación (POST o cookie) --------
$term_id = 0;
$location_key = ''; // índice visual (si lo usas en UI)

if (isset($_POST['select_location']) && $_POST['select_location'] !== '') {
    // Desde catálogo normalmente llega un "key" (posición). Puedes enviarte termId si prefieres.
    $location_key = (int) sanitize_text_field($_POST['select_location']);
    // Mapear key → term_id
    $roots = $get_terms_root();
    if (isset($roots[$location_key])) {
        $term_id = (int) $roots[$location_key]->term_id;
    }
} else {
    // Single product: tomar desde cookie (moderna) o legacy
    $term_id = isset($_COOKIE['wcmlim_selected_location_termid']) ? (int) $_COOKIE['wcmlim_selected_location_termid'] : 0;

    if (!$term_id && isset($_COOKIE['wcmlim_selected_location'])) {
        // Cookie legacy guarda "key", la convertimos a term_id
        $legacy_key = (int) $_COOKIE['wcmlim_selected_location'];
        $roots = $get_terms_root();
        if (isset($roots[$legacy_key])) {
            $term_id = (int) $roots[$legacy_key]->term_id;
            $location_key = $legacy_key;
        }
    } else {
        // si ya tenemos term_id moderno, calculamos su key para mantener mismo shape que catálogo
        $roots = $get_terms_root();
        foreach ($roots as $k => $t) {
            if ((int)$t->term_id === $term_id) {
                $location_key = (int) $k;
                break;
            }
        }
    }
}

// Si no hay term_id, no añadimos nada (quedará igual que tu log vacío)
if (!$term_id) {
    return $cart_item_data;
}

// Term y nombre
$term = $get_term_by_id($term_id);
if (!$term) {
    return $cart_item_data;
}

// Stock por ubicación para que coincida con catálogo
$qty_at_loc = $stock_at_location($product_id, $term_id);

// -------- Construir EXACTAMENTE el mismo bloque que catálogo --------
if (!isset($cart_item_data['select_location'])) {
    $cart_item_data['select_location'] = [];
}
$cart_item_data['select_location']['location_name']   = $term->name;
$cart_item_data['select_location']['location_key']    = ($location_key === '' ? 0 : (int)$location_key);
$cart_item_data['select_location']['location_qty']    = (int) $qty_at_loc;
$cart_item_data['select_location']['location_termId'] = (int) $term_id;

// -------- Precios por sucursal (mantengo tu lógica) --------
if ($_isrspon === "on") {
    if (!empty($_POST['location_regular_price']) && ($_POST['location_sale_price'] ?? 'undefined') === 'undefined') {
        $cart_item_data['select_location']['location_cart_price'] = strip_tags($_POST['location_regular_price']);
    } elseif (isset($_POST['location_sale_price']) && $_POST['location_sale_price'] !== 'undefined') {
        $cart_item_data['select_location']['location_cart_price'] = strip_tags($_POST['location_sale_price']);
    } else {
        // fallback: precio del producto formateado (como ya tenías)
        $cart_item_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode($sProductPrice));
    }
}
return $cart_item_data;