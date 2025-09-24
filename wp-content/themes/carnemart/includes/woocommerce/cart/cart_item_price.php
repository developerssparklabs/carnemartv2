<?php

/**
 * =========================================================================
 * Mostrar precio unitario dinámico en el carrito de WooCommerce
 * -------------------------------------------------------------------------
 * Esta función determina y muestra el precio unitario correcto de un ítem
 * del carrito según:
 * - La tienda seleccionada (via cookie `wcmlim_selected_location_termid`)
 * - Descuentos directos configurados por SKU
 * - Precio en promoción (meta personalizada)
 * - Precio escalonado por grupo de cliente
 * 
 * ✅ Aplica solo en la vista del carrito.
 * 
 * Autor: Spark Team
 * Fecha: 06/07/2025
 * =========================================================================
 */
function display_correct_price_in_cart($price_html, $cart_item, $cart_item_key)
{
    global $wpdb;

    $product         = $cart_item['data'];
    $sku             = $product->get_sku();
    $quantity        = $cart_item['quantity'];
    $product_id      = $product->get_id();
    $store_term_id   = $_COOKIE['wcmlim_selected_location_termid'] ?? null;

    // Si no hay tienda seleccionada, usar precio regular global
    if (!$store_term_id) {
        return wc_price($product->get_regular_price());
    }

    // Obtener precio base por tienda (meta personalizada)
    $meta_key       = 'wcmlim_regular_price_at_' . $store_term_id;
    $store_price    = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
        $product_id,
        $meta_key
    ));

    $base_price     = $store_price !== null ? floatval($store_price) : $product->get_regular_price();
    $final_price    = $base_price;

    // Obtener descuentos y grupo de cliente por ubicación
    $customer_group = get_customer_group_from_location($store_term_id);
    $discounts      = obtener_configuracion_descuentos();

    // 1. Descuento directo configurado
    if (isset($discounts[$sku])) {
        $config = $discounts[$sku];

        if ($quantity <= $config['limite']) {
            $final_price = $config['precio_final'];
        }

    } else {
        // 2. Precio ofertón por tienda
        $sale_key  = 'wcmlim_sale_price_at_' . $store_term_id;
        $sale_price = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $sale_key
        ));

        if ($sale_price) {
            $final_price = floatval($sale_price);
        } else {
            // 3. Precio escalonado por grupo
            $tier_key   = "eib2bpro_price_tiers_group_{$customer_group}";
            $tier_json  = $wpdb->get_var($wpdb->prepare(
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

    // 🔥 Solo se devuelve el precio unitario (no multiplicar por cantidad)
    return wc_price($final_price);
}
