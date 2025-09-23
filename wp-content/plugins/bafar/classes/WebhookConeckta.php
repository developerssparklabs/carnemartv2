<?php


class WebhookConeckta
{
    private static ?WebhookConeckta $instance = null;

    public static function get_instance(): WebhookConeckta
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Punto de entrada principal del webhook.
     * Procesa la notificación enviada por Conekta y actualiza el pedido si es válido.
     */
    public function mi_webhook_actualizar_orden_conekta(WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        $this->log_info("Datos recibidos del webhook de Conekta", $data);

        if (!$this->is_transaction_paid($data)) {
            $this->log_info("El estado de la transacción no es 'paid'. No se realizará ninguna acción.");
            return new WP_REST_Response('El estado de la transacción no es paid', 200);
        }

        $conekta_order_id = $data['data']['object']['id'];
        $order_id = $this->get_order_id_by_conekta_id($conekta_order_id);

        if (!$order_id) {
            $this->log_error("ID de pedido no encontrado en la base de datos para conekta-order-id: $conekta_order_id");
            return new WP_REST_Response('ID de pedido no encontrado', 400);
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            $this->log_error("No se encontró el pedido con ID $order_id.");
            return new WP_REST_Response('Pedido no encontrado', 404);
        }

        return $this->handle_order_payment($order, $conekta_order_id);
    }

    /**
     * Verifica si la transacción fue marcada como "paid".
     */
    private function is_transaction_paid(array $data): bool
    {
        return isset($data['data']['object']['payment_status']) &&
            $data['data']['object']['payment_status'] === 'paid';
    }

    /**
     * Busca el ID del pedido de WooCommerce asociado a un ID de Conekta.
     */
    private function get_order_id_by_conekta_id(string $conekta_order_id): ?int
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            'conekta-order-id',
            $conekta_order_id
        ));
    }

    /**
     * Procesa el pedido: valida si ya está pagado, actualiza estado, y gestiona el método de entrega.
     */
    private function handle_order_payment(WC_Order $order, string $conekta_order_id): WP_REST_Response
    {
        if ($order->get_meta('conekta_pagado') == 1) {
            $order->add_order_note("⚠️ Este pedido ya fue marcado como pagado previamente vía webhook de Conekta.", false);
            return new WP_REST_Response('Pedido ya procesado anteriormente', 200);
        }

        $order_id = $order->get_id();

        // Actualizar metadatos
        $order->update_meta_data('conekta-order-id-auth', $conekta_order_id);
        $order->update_meta_data('conekta_pagado', 1);

        $this->handle_shipping_method($order);
        $order->update_status('processing', 'Tu pago ha sido validado desde nuestro Banco (1).');
        $order->save();

        $this->log_info("El estado del pedido con ID $order_id se ha actualizado a 'processing'.");
        return new WP_REST_Response('Pedido actualizado exitosamente', 200);
    }

    /**
     * Determina el método de envío y ejecuta lógica adicional según sea Uber o Pickup.
     */
    private function handle_shipping_method(WC_Order $order): void
    {
        foreach ($order->get_shipping_methods() as $shipping_method) {
            $method_title = strtolower($shipping_method->get_method_title());

            if (strpos($method_title, 'envío por uber') !== false) {
                if (class_exists('UD_Uber_Deliveries')) {
                    $response = UD_Uber_Deliveries::enviar_pedido_a_uber_y_notificar_cliente($order->get_id());
                    $order->update_meta_data('metodo_entrega', '01'); // Uber

                    if ($response === false) {
                        error_log('Error enviando pedido a Uber para el pedido: ' . $order->get_id());
                    }
                }
            } else {
                $order->update_meta_data('metodo_entrega', '02'); // Pickup
            }
        }
    }

    /**
     * Registra logs informativos en WC_Logger.
     */
    private function log_info(string $message, $data = null): void
    {
        $logger = new WC_Logger();
        $entry = $data ? $message . ': ' . json_encode($data) : $message;
        $logger->info($entry, ["source" => "Flujo Clearsales Conekta"]);
    }

    /**
     * Registra errores en WC_Logger.
     */
    private function log_error(string $message): void
    {
        $logger = new WC_Logger();
        $logger->error($message, ["source" => "Flujo Clearsales Conekta"]);
    }
}
