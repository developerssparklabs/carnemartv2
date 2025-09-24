<?php

/**
 * Calcula y muestra el subtotal correcto en el checkout basado en ubicación, descuentos directos y precios escalonados.
 * Esta lógica se basa en reglas personalizadas por tienda, SKU y grupo de cliente.
 */
function display_correct_subtotal_checkout($subtotal_html, $cart_item, $cart_item_key)
{
    global $wpdb;

    $product     = $cart_item['data'];
    $sku         = $product->get_sku();
    $quantity    = $cart_item['quantity'];
    $product_id  = $product->get_id();
    $store_term_id = $_COOKIE['wcmlim_selected_location_termid'] ?? null;

    // Fallback si no hay tienda definida
    if (!$store_term_id) {
        $regular_price = $product->get_regular_price();
        return wc_price($regular_price * $quantity);
    }

    // Buscar precio regular personalizado por tienda
    $meta_key = 'wcmlim_regular_price_at_' . $store_term_id;
    $store_price = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
        $product_id,
        $meta_key
    ));

    $base_price = $store_price !== null ? floatval($store_price) : $product->get_regular_price();
    $final_price = $base_price;

    $customer_group = get_customer_group_from_location($store_term_id);
    $discounts = obtener_configuracion_descuentos();

    // 1. Descuento directo si aplica
    if (isset($discounts[$sku])) {
        $config = $discounts[$sku];

        if ($quantity <= $config['limite']) {
            $final_price = $config['precio_final'];
        }
    } else {
        // 2. Verificar si existe ofertón (sale price)
        $sale_key = "wcmlim_sale_price_at_" . $store_term_id;
        $sale_price = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $sale_key
        ));

        if ($sale_price) {
            $final_price = floatval($sale_price);
        } else {
            // 3. Evaluar escalado por grupo
            $tier_key = "eib2bpro_price_tiers_group_{$customer_group}";
            $tier_json = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
                $product_id,
                $tier_key
            ));

            if ($tier_json) {
                $tier_data = json_decode($tier_json, true);
                if (is_array($tier_data)) {
                    foreach ($tier_data as $threshold => $tier_price) {
                        if ($quantity >= floatval($threshold)) {
                            $final_price = floatval($tier_price);
                        }
                    }
                }
            }
        }
    }

    $calculated_subtotal = $final_price * $quantity;
    return wc_price($calculated_subtotal);
}