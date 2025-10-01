<?php
defined('ABSPATH') || exit;

/**
 * AJAX: Agregar al carrito validando step/mínimo/stock por ubicación.
 * - Acepta cantidades decimales (3.500, 52.5, etc.)
 * - Evita el operador % con floats (usa epsilon).
 *
 * Códigos de respuesta que mantienes:
 *   '2' -> sin stock / ubicación no permitida / incongruencias
 *   '3' -> faltan datos de ubicación cuando maneja stock
 *   '4' -> excede stock
 *   '9' -> cantidad inválida (mínimo o múltiplos)
 */
global $woocommerce;

/* ========= Helpers ========= */

// Normaliza string a float (admite coma)
function cmt_to_float($value, $decimals = 3) {
    if ($value === null || $value === '') return 0.0;
    $v = is_string($value) ? str_replace(',', '.', $value) : $value;
    $v = wc_format_decimal($v, $decimals);
    return (float) $v;
}

// ¿qty es múltiplo de step? (con epsilon para floats)
function cmt_is_multiple_of_step($qty, $step, $epsilon = 1e-5) {
    if ($step <= 0) return true;
    $nearest = round($qty / $step) * $step;
    return abs($qty - $nearest) < $epsilon;
}

// Cantidad ya presente en carrito para ese producto/variación
function cmt_cart_qty_for_product($product_id) {
    $qty = 0.0;
    if (!WC()->cart) return 0.0;
    foreach (WC()->cart->get_cart() as $item) {
        $pid = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
        if ((int) $pid === (int) $product_id) {
            $qty += (float) $item['quantity'];
        }
    }
    return $qty;
}

// Stock por ubicación: meta wcmlim_stock_at_{termId}
function cmt_stock_at_location($product_id, $term_id) {
    $meta_key = "wcmlim_stock_at_{$term_id}";
    $raw = get_post_meta($product_id, $meta_key, true);
    return cmt_to_float($raw, 3);
}

// Normaliza truthy para manage_stock
function cmt_is_yes($v) {
    return in_array(strtolower((string)$v), ['1','yes','true','y','on'], true);
}

/* ========= Entrada ========= */

$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id'] ?? 0));
if (!$product_id) {
    wp_die();
}

$requested_qty_raw = $_POST['quantity'] ?? 1;
$requested_qty     = cmt_to_float($requested_qty_raw, 3);  // lo que el usuario está agregando ahora
if ($requested_qty <= 0) {
    echo '9'; // cantidad inválida
    wp_die();
}

$product_location_termid = $_POST['product_location_termid'] ?? '';
$product_location        = $_POST['product_location'] ?? '';
$product_location_key    = (int)($_POST['product_location_key'] ?? 0);
$product_location_qty    = (int)($_POST['product_location_qty'] ?? 0);

$product_price                = cmt_to_float($_POST['product_price'] ?? '', 3);
$product_location_regular     = cmt_to_float($_POST['product_location_regular_price'] ?? '', 3);
$product_location_sale_raw    = $_POST['product_location_sale_price'] ?? 'undefined';
$product_location_sale        = $product_location_sale_raw === 'undefined' ? null : cmt_to_float($product_location_sale_raw, 3);

$_isrspon = get_option('wcmlim_enable_price');

/* ========= Step & mínimo ========= */

$product_step = cmt_to_float(get_post_meta($product_id, 'product_step', true), 3);
if ($product_step <= 0) $product_step = 1.0;

$min_quantity = cmt_to_float(get_post_meta($product_id, 'min_quantity', true), 3);
if ($min_quantity <= 0) $min_quantity = 1.0;

// qty final que habría en el carrito tras agregar
$existing_qty = cmt_cart_qty_for_product($product_id);
$total_qty    = $existing_qty + $requested_qty;

$epsilon = 1e-5;

// Validamos contra TOTAL (lo que quedará) pero agregamos REQUESTED
if ($total_qty + $epsilon < $min_quantity || ! cmt_is_multiple_of_step($total_qty, $product_step, $epsilon)) {
    echo '9';
    wp_die();
}

/* ========= Stock por ubicación ========= */
$manage_stock_meta = get_post_meta($product_id, '_manage_stock', true);
$manage_stock      = cmt_is_yes($manage_stock_meta) ? 'yes' : 'no';

$cookie_termid     = $_COOKIE['wcmlim_selected_location_termid'] ?? '';
$effective_termid  = (int)($product_location_termid ?: $cookie_termid);

if ($manage_stock === 'yes' && empty($effective_termid)) {
    echo '3';
    wp_die();
}

if ($manage_stock !== 'yes' && (!empty($product_location_termid) || !empty($cookie_termid))) {
    echo '2';
    wp_die();
}

if ($manage_stock === 'yes') {
    $stock_at_location = cmt_stock_at_location($product_id, $effective_termid);
    if ($stock_at_location <= 0) {
        echo '2';
        wp_die();
    }
    if ($total_qty - $stock_at_location > $epsilon) {
        echo '4';
        wp_die();
    }
}

/* ========= Ubicación permitida (si aplica) ========= */

if (get_option('wcmlim_enable_specific_location') === 'on') {
    $allow_specific_location = get_post_meta($product_id, 'wcmlim_allow_specific_location_at_' . $effective_termid, true);
    if ($allow_specific_location !== 'Yes') {
        echo '2';
        wp_die();
    }
}

/* ========= Validación WC y estado producto ========= */

$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $requested_qty);
$product_status    = get_post_status($product_id);

if (!$passed_validation || 'publish' !== $product_status) {
    echo '11';
    wp_die();
}

/* ========= Meta para el carrito ========= */

$_location_data = [
    'select_location' => [
        'location_name'   => $product_location,
        'location_key'    => $product_location_key,
        'location_qty'    => $product_location_qty,
        'location_termId' => (int)$product_location_termid,
    ],
];

if ($_isrspon === 'on') {
    if (!empty($product_location_regular) && empty($product_location_sale)) {
        $_location_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode(wc_price($product_location_regular)));
    } elseif (!empty($product_location_sale)) {
        $_location_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode(wc_price($product_location_sale)));
    } else {
        $_location_data['select_location']['location_cart_price'] = strip_tags(html_entity_decode(wc_price($product_price)));
    }
}

/* ========= Agregar al carrito ========= */

// ¡OJO! Agregamos **solo** lo solicitado, no el total.
$added = WC()->cart->add_to_cart($product_id, $requested_qty, 0, [], $_location_data);

if ($added) {
    WC_AJAX::get_refreshed_fragments();
}
wp_die();