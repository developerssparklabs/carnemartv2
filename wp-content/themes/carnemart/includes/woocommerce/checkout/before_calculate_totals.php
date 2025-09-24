<?php

/**
 * =========================================================================
 * Aplica descuentos dinámicos basados en tienda, promociones u ofertas escalonadas.
 * -------------------------------------------------------------------------
 * Esta lógica se ejecuta antes de calcular los totales del carrito.
 * Prioriza los precios en el siguiente orden:
 *  1. Precio en oferta personalizado (ofertón)
 *  2. Descuento directo por configuración
 *  3. Precio escalonado según grupo
 *
 * Autor: Spark Team
 * Fecha: 06/07/2025
 * =========================================================================
 */
function apply_dynamic_discounts_by_location($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) return;

    $store_term_id = $_COOKIE['wcmlim_selected_location_termid'] ?? null;
    if (!$store_term_id) return;

    $customer_group = get_customer_group_from_location($store_term_id);
    if (!$customer_group) return;

    global $wpdb;
    $discount_config = obtener_configuracion_descuentos();

    foreach ($cart->get_cart() as $cart_item) {
        $product     = $cart_item['data'];
        $quantity    = $cart_item['quantity'];
        $sku         = $product->get_sku();
        $product_id  = $product->get_id();

        // Precio base desde metadato personalizado
        $meta_key    = 'wcmlim_regular_price_at_' . $store_term_id;
        $base_price  = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $meta_key
        ));
        $base_price = $base_price !== null ? floatval($base_price) : $product->get_regular_price();

        // 1. Buscar si tiene precio en ofertón
        $sale_key = 'wcmlim_sale_price_at_' . $store_term_id;
        $sale_price = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $sale_key
        ));

        if ($sale_price) {
            $product->set_price(floatval($sale_price));
            continue;
        }

        // 2. Aplicar descuento directo si está configurado
        if (isset($discount_config[$sku])) {
            $config = $discount_config[$sku];

            if ($quantity <= $config['limite']) {
                $product->set_price($config['precio_final']);
            } else {
                $product->set_price($base_price);
            }
            continue;
        }

        // 3. Verificar escalado por grupo
        $tier_key = "eib2bpro_price_tiers_group_{$customer_group}";
        $tier_json = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $tier_key
        ));

        if ($tier_json) {
            $tier_data = json_decode($tier_json, true);
            if (is_array($tier_data)) {
                $scaled_price = null;
                foreach ($tier_data as $threshold => $tier_price) {
                    if ($quantity >= floatval($threshold)) {
                        $scaled_price = floatval($tier_price);
                    }
                }
                $product->set_price($scaled_price ?? $base_price);
                continue;
            }
        }

        // Precio base por defecto si no se aplicó ninguna regla
        $product->set_price($base_price);
    }
}
