<?php

/**
 * Aplica precios dinámicos por sucursal y escalados (tiers) con llaves "1.00 (94.9) ...": 62.9
 * - Elimina cualquier uso de sale_price.
 * - Mantiene descuento directo por SKU si está en $discount_config.
 */
function apply_dynamic_discounts_by_location($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) return;

    // 1) Sucursal elegida
    $store_term_id = $_COOKIE['wcmlim_selected_location_termid'] ?? null;
    if (!$store_term_id) return;

    // 2) Grupo de cliente desde sucursal
    $customer_group = get_customer_group_from_location($store_term_id);
    if (!$customer_group) return;

    global $wpdb;

    // 3) Config de descuentos directos por SKU (si aplica)
    $discount_config = obtener_configuracion_descuentos();

    // --- Helper: parsea una llave tipo "1.00 (94.9) 🔥 ¡OFERTÓN! ..." y devuelve [threshold_float, regular_float|null] ---
    $parse_tier_key = function (string $key) {
        // Captura: inicio-numérico y opcional paréntesis con numérico
        // Ej: "  1.00 (94.9) 🔥 promo ..." -> threshold=1.00, regular=94.9
        $threshold = null;
        $regular   = null;

        if (preg_match('/^\s*([0-9]+(?:\.[0-9]+)?)\s*(?:\(([\d\.]+)\))?/u', $key, $m)) {
            $threshold = isset($m[1]) ? (float)$m[1] : null;
            if (isset($m[2]) && $m[2] !== '') {
                $regular = (float)$m[2];
            }
        }
        return [$threshold, $regular];
    };

    foreach ($cart->get_cart() as $cart_item) {
        $product     = $cart_item['data'];
        $quantity    = (float) $cart_item['quantity'];
        $sku         = $product->get_sku();
        $product_id  = $product->get_id();

        // 4) Precio base (regular) por sucursal
        $meta_key    = 'wcmlim_regular_price_at_' . $store_term_id;
        $base_price  = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $meta_key
        ));
        $base_price = ($base_price !== null && $base_price !== '') ? (float)$base_price : (float)$product->get_regular_price();

        // 5) Descuento directo por SKU (tiene prioridad sobre tiers)
        if (isset($discount_config[$sku])) {
            $config = $discount_config[$sku]; // ['limite' => int|float, 'precio_final' => float]

            if ($quantity <= (float)$config['limite']) {
                $product->set_price((float)$config['precio_final']);
                error_log("Aplicando descuento directo: " . $config['precio_final']);
            } else {
                $product->set_price($base_price);
                error_log("Cantidad mayor al límite, usando base_price: " . $base_price);
            }
            continue;
        }

        // 6) Escalados por grupo (tiers) – JSON con llaves no estándar
        $tier_key = "eib2bpro_price_tiers_group_{$customer_group}";
        $tier_json = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $tier_key
        ));

        $applied_price = null;

        if ($tier_json) {
            $tier_data = json_decode($tier_json, true); // array asociativo: "llave rara" => precio

            if (is_array($tier_data) && !empty($tier_data)) {
                // Normalizamos a [threshold => price]
                $tiers = []; // array de ['threshold'=>float,'price'=>float,'regular'=>float|null]

                foreach ($tier_data as $label => $valuePrice) {
                    // valor siempre se usa como precio final
                    $price = (float)$valuePrice;

                    [$threshold, $regular] = $parse_tier_key((string)$label);
                    if ($threshold === null) {
                        // Si no se pudo leer umbral del inicio de la llave, saltamos
                        continue;
                    }

                    $tiers[] = [
                        'threshold' => (float)$threshold,
                        'price'     => $price,
                        'regular'   => $regular !== null ? (float)$regular : null,
                    ];
                }

                if (!empty($tiers)) {
                    // Ordenamos por threshold ascendente
                    usort($tiers, function ($a, $b) {
                        if ($a['threshold'] == $b['threshold']) return 0;
                        return ($a['threshold'] < $b['threshold']) ? -1 : 1;
                    });

                    // Buscamos el mayor threshold <= cantidad
                    $candidate = null;
                    foreach ($tiers as $t) {
                        if ($quantity >= $t['threshold']) {
                            $candidate = $t;
                        } else {
                            // en cuanto la lista supera la cantidad, terminamos
                            break;
                        }
                    }

                    if ($candidate === null) {
                        // Si no hay threshold <= cantidad, aplicamos el más bajo (interpretación "desde X")
                        $candidate = $tiers[0];
                    } else {
                    }

                    $applied_price = (float)$candidate['price'];
                }
            }
        }

        if ($applied_price !== null) {
            $product->set_price($applied_price);
        } else {
            // 7) Sin reglas aplicables: usar base_price
            $product->set_price($base_price);
        }
    }
}