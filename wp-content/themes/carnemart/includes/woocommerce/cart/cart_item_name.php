<?php


/**
 * Agrega etiquetas visuales (OFERTÓN, PROMOCIÓN, DISPONIBLE) junto con mensajes informativos
 * en el nombre del producto del carrito y checkout, dependiendo de la tienda y configuración de descuentos.
 */
function display_dynamic_product_label($product_name, $cart_item, $cart_item_key)
{
    global $wpdb;

    $product     = $cart_item['data'];
    $sku         = $product->get_sku();
    $quantity    = $cart_item['quantity'];
    $product_id  = $product->get_id();
    $term_id     = $_COOKIE['wcmlim_selected_location_termid'] ?? null;
    $discounts   = obtener_configuracion_descuentos();

    // Verificar si aplica etiqueta OFERTÓN según tienda seleccionada
    $has_oferton_tag = false;
    if ($term_id) {
        $sale_price_key = "wcmlim_sale_price_at_" . $term_id;
        $sale_price = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1",
            $product_id,
            $sale_price_key
        ));
        if ($sale_price) {
            $has_oferton_tag = true;
        }
    }

    // Mostrar nombre del producto en negrita
    $product_name = '<strong>' . $product_name . '</strong>';

    // Mostrar etiqueta OFERTÓN si aplica
    if ($has_oferton_tag) {
        $product_name .= '<span style="display:inline-block; background-color:#fee2e2; color:#b91c1c; padding:2px 8px; border-radius:4px; font-weight:bold; font-size:0.85rem;">OFERTÓN</span>';
    }

    // Verificar si hay descuento promocional por SKU
    if (isset($discounts[$sku])) {
        $config         = $discounts[$sku];
        $label_text     = $config['etiqueta'];
        $unit           = esc_html($config['unidad']);
        $formatted_price = wc_price($config['precio_final']);

        if ($quantity <= $config['limite']) {
            // Mostrar etiqueta PROMOCIÓN si no hay OFERTÓN
            if (!$has_oferton_tag) {
                $product_name .= '<span style="display:inline-block; background-color:#d1fae5; color:#065f46; padding:2px 8px; border-radius:4px; font-weight:bold; font-size:0.85rem;">PROMOCIÓN</span>';
            }

            // Mostrar información adicional solo en la página del carrito
            if (is_cart()) {
                $product_name .= '<span style="display:block; color: #1a7f37; font-weight: bold; font-size: 0.92em; word-wrap: break-word; line-height: 1.2;">' . $label_text . '</span>';
                $product_name .= '<span style="display:block; color: #6b7280; font-size: 0.88em; line-height: 1.2;">📦 Precio público limitado a ' . $config['limite'] . ' ' . $unit . '</span>';
            }
        }

    } else {
        // Si no tiene OFERTÓN ni PROMOCIÓN, mostrar DISPONIBLE
        if (!$has_oferton_tag) {
            $product_name .= '<span style="display:inline-block; background-color:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:4px; font-weight:bold; font-size:0.85rem;">DISPONIBLE</span>';
        }
    }

    return $product_name;
}
