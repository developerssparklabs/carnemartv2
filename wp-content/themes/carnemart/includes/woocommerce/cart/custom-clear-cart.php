<?php
/**
 * ============================================================
 * Custom Clear Cart Handler
 * ------------------------------------------------------------
 * Este archivo contiene el manejador de la lógica REST API para
 * vaciar el carrito del usuario actual en WooCommerce.
 *
 * Compatible con usuarios logueados y no logueados.
 * Garantiza que el objeto del carrito esté disponible antes de usarlo.
 *
 * Autor: Dens - Spark
 * Fecha: 10/05/2025
 * ============================================================
 */

/**
 * Callback del endpoint REST que vacía el carrito.
 *
 * @return WP_REST_Response
 */
function clear_cart_api_handler(): WP_REST_Response
{
    // Asegurar que el carrito esté cargado en contexto REST
    if (function_exists('wc_load_cart') && is_null(WC()->cart)) {
        wc_load_cart();
    }

    // Vaciar el carrito
    WC()->cart->empty_cart();

    // Devolver respuesta exitosa
    return new WP_REST_Response(['success' => true], 200);
}
