<?php

class OrderHelper
{
    /**
     * Actualiza el estado de una orden de WooCommerce y agrega una nota.
     *
     * @param int    $order_id     ID de la orden.
     * @param string $new_status   Nuevo estado de la orden (ej. 'pending', 'completed', etc.).
     * @param string $note         Nota a agregar a la orden.
     * @param bool   $status_note  Indica si la nota será visible para el cliente (true) o solo para el administrador (false).
     *
     * @return void
     */
    public static function update_status_and_note(int $order_id, string $new_status = '', string $note = '', bool $status_note = true): void
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            // error_log("Orden no encontrada con ID: $order_id");
            return;
        }

        if (!empty($new_status)) {
            $order->update_status($new_status, $note, $status_note);
        }

        if (!empty($note)) {
            $order->add_order_note($note, $status_note);
        }
    }
    
    public static function obtener_location_id($order_id) {
        $location_id = get_post_meta($order_id, 'location_id', true);
    
        if (empty($location_id)) {
            // Fallback a la cookie si no existe el metadato
            if (isset($_COOKIE['wcmlim_selected_location_termid'])) {
                $location_id = sanitize_text_field($_COOKIE['wcmlim_selected_location_termid']);
            }
        }
    
        return $location_id;
    }
    
    public static function get_keys($location_id) {

        $public = $private = null;

        if (WP_ENVIRONMENT_TYPE === "dev") {
            $public = get_term_meta($location_id, 'sandbox_location_api_key_public', true);
            $private = get_term_meta($location_id, 'sandbox_location_api_key', true);
        } else {
            $public = get_term_meta($location_id, 'location_api_key_public', true);
            $private = get_term_meta($location_id, 'location_api_key', true);
        }

        return [
            'public' => $public,
            'private' => $private
        ];
    }
}