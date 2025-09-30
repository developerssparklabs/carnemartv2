<?php

/**
 * =========================================================================
 * Mostrar precio unitario dinámico en el carrito de WooCommerce
 * -------------------------------------------------------------------------
 * - Usa precio base por tienda (wcmlim_regular_price_at_{termid})
 * - Descuento directo por SKU (si existe)
 * - Precio escalonado por grupo (tiers con llaves "1.00 (94.9) ...": 62.9)
 * - No usa sale_price
 * 
 * Autor: Spark Team
 * Fecha: 06/07/2025
 * =========================================================================
 */
function display_correct_price_in_cart($price_html, $cart_item, $cart_item_key)
{
    global $wpdb;

    $product       = $cart_item['data'];
    $sku           = $product->get_sku();
    $quantity      = (float) $cart_item['quantity'];
    $product_id    = $product->get_id();
    $store_term_id = $_COOKIE['wcmlim_selected_location_termid'] ?? null;

    // Helper: parsea llave "1.00 (94.9) ..." -> [threshold_float, regular_float|null]
    $parse_tier_key = function (string $key) {
        $threshold = null; $regular = null;
        if (preg_match('/^\s*([0-9]+(?:\.[0-9]+)?)\s*(?:\(([\d\.]+)\))?/u', $key, $m)) {
            $threshold = (float)$m[1];
            if (isset($m[2]) && $m[2] !== '') {
                $regular = (float)$m[2];
            }
        }
        return [$threshold, $regular];
    };

    // 1) Precio base por tienda (si no hay tienda, usar regular global)
    if (!$store_term_id) {
        $final_price = (float) $product->get_regular_price();
        return wc_price($final_price); // unitario
    }

    $meta_key    = 'wcmlim_regular_price_at_' . $store_term_id;
    $store_price = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
        $product_id,
        $meta_key
    ));
    $base_price  = ($store_price !== null && $store_price !== '') ? (float)$store_price : (float)$product->get_regular_price();
    $final_price = $base_price;

    // 2) Config de descuentos directos y grupo del cliente
    $customer_group = get_customer_group_from_location($store_term_id);
    $discounts      = obtener_configuracion_descuentos();

    // 2a) Descuento directo por SKU (tiene prioridad)
    if (isset($discounts[$sku])) {
        $config = $discounts[$sku]; // ['limite'=>x, 'precio_final'=>y]
        if ($quantity <= (float)$config['limite']) {
            $final_price = (float)$config['precio_final'];
            return wc_price($final_price);
        }
        // Si excede el límite, cae a tiers/base price
    }

    // 3) Tiers por grupo (JSON con llaves no estándar)
    if ($customer_group) {
        $tier_key  = "eib2bpro_price_tiers_group_{$customer_group}";
        $tier_json = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $tier_key
        ));

        if ($tier_json) {
            $tier_data = json_decode($tier_json, true); // "1.00 (94.9) ...": 62.9
            if (is_array($tier_data) && !empty($tier_data)) {
                $tiers = []; // ['threshold'=>float,'price'=>float,'regular'=>float|null]
                foreach ($tier_data as $label => $valuePrice) {
                    [$threshold, $regular] = $parse_tier_key((string)$label);
                    if ($threshold === null) {
                        continue; // si no hay número inicial, ignorar
                    }
                    $tiers[] = [
                        'threshold' => (float)$threshold,
                        'price'     => (float)$valuePrice, // el valor es el precio final
                        'regular'   => $regular !== null ? (float)$regular : null,
                    ];
                }

                if (!empty($tiers)) {
                    // Orden por umbral asc
                    usort($tiers, function ($a, $b) {
                        if ($a['threshold'] == $b['threshold']) return 0;
                        return ($a['threshold'] < $b['threshold']) ? -1 : 1;
                    });

                    // Mayor threshold <= cantidad
                    $candidate = null;
                    foreach ($tiers as $t) {
                        if ($quantity >= $t['threshold']) {
                            $candidate = $t;
                        } else {
                            break;
                        }
                    }
                    if ($candidate === null) {
                        // Si ninguno <= cantidad, aplicar el de menor umbral (interpretación "desde X")
                        $candidate = $tiers[0];
                    }
                    $final_price = (float)$candidate['price'];
                }
            }
        }
    }

    // 🔥 Devolver precio unitario (no multiplicar por cantidad)
    return wc_price($final_price);
}