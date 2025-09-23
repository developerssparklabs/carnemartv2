<?php

class ConektaService
{
    private string $api_key_private;

    public function __construct(string $api_key = '')
    {
        $this->api_key_private = $api_key;
    }

    public function getPaymentSource(string $customerId, $typeSource = null): array
    {
        $logger = new WC_Logger();

        $url = "https://api.conekta.io/customers/" . $customerId;
        $headers = [
            "Accept-Language" => "es",
            "Accept" => "application/vnd.conekta-v2.1.0+json",
            "Authorization" => "Bearer {$this->api_key_private}",
        ];

        $logger->info("Solicitando fuentes de pago para el cliente: $customerId", ['source' => $typeSource]);
        $logger->debug("Headers utilizados: " . json_encode($headers), ['source' => $typeSource]);

        $response = wp_remote_get($url, ["headers" => $headers]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $logger->error("❌ Error HTTP al obtener fuentes de pago de Conekta: $error_message", ['source' => $typeSource]);
            return [false, "Error de conexión con Conekta: $error_message"];
        }

        $body = json_decode($response["body"], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $logger->error("❌ Error al decodificar JSON de fuentes de pago: " . json_last_error_msg(), ['source' => $typeSource]);
            $logger->debug("Respuesta de Conekta: " . json_encode($body), ['source' => $typeSource]);
            return [false, "Respuesta inválida de Conekta al obtener fuentes de pago"];
        }

        $paymentSource = $body["payment_sources"]["data"][0] ?? null;

        if (!$paymentSource) {
            $logger->warning("⚠️ No se encontró ninguna fuente de pago para el cliente: $customerId", ['source' => $typeSource]);
            $logger->debug("Respuesta de Conekta: " . json_encode($body), ['source' => $typeSource]);
            return [false, "⚠️ No se encontró ninguna fuente de pago para el cliente: $customerId"];
        }

        $logger->info("✅ Fuente de pago obtenida para cliente: $customerId", ['source' => $typeSource]);
        $logger->debug("Fuente de pago: " . json_encode($paymentSource), ['source' => $typeSource]);

        return [true, $paymentSource];
    }   
    public function updateOrder($conekta_order_id, $conekta_customer_id, $order_id): array|bool
    {
        $logger = new WC_Logger();
        $logger->info("Iniciando actualización de orden en Conekta $order_id", ['source' => 'actualizar_orden_conekta']);

        if (empty($order_id)) {
            $logger->error("ID de orden vacío. No se puede actualizar la orden en Conekta.", ['source' => 'actualizar_orden_conekta']);
            return [false, "ID de orden vacío. No se puede actualizar la orden en Conekta."];
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            $logger->error("No se encontró la orden #$order_id", ['source' => 'actualizar_orden_conekta']);
            return [false, "No se encontró la orden #$order_id"];
        }

        $already_sent = $order->get_meta('enviado_a_conekta') == 1;
        $already_processing = $order->get_status() === 'processing';

        if ($already_sent || $already_processing) {
            $logger->warning("🚫 Intento duplicado para la orden #$order_id.", ['source' => 'actualizar_orden_conekta']);
            return [false, "🚫 Intento duplicado para la orden #$order_id."];
        }

        $location_id = $order->get_meta('location_id');
        $key_prefix = (WP_ENVIRONMENT_TYPE === 'dev') ? 'sandbox_' : '';
        $api_key_private = get_term_meta($location_id, $key_prefix . 'location_api_key', true);

        [$success, $payment_source_or_msg] = $this->getPaymentSource($conekta_customer_id, 'actualizar_orden_conekta');
        if (!$success) {
            $logger->error("No se pudo obtener la fuente de pago: $payment_source_or_msg", ['source' => 'actualizar_orden_conekta']);
            return [false, "❌ Error al obtener fuente de pago desde Conekta: $payment_source_or_msg"];
        }

        $payment_source = $payment_source_or_msg;
        $request_body = json_encode([
            "pre_authorize" => false,
            "customer_info" => ["customer_id" => $conekta_customer_id],
            "charges" => [
                [
                    "payment_method" => [
                        "payment_source_id" => $payment_source["id"],
                        "type" => $payment_source["type"],
                    ],
                ]
            ],
        ]);

        $logger->debug("Cuerpo de la solicitud (Datos): " . json_encode($request_body), ['source' => 'actualizar_orden_conekta']);

        $url = "https://api.conekta.io/orders/{$conekta_order_id}";
        $response = wp_remote_request($url, [
            "method" => "PUT",
            "body" => $request_body,
            "headers" => [
                "Accept-Language" => "es",
                "accept" => "application/vnd.conekta-v2.1.0+json",
                "authorization" => "Bearer $api_key_private",
                "content-type" => "application/json",
            ],
        ]);

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        $logger->debug("Código de estado HTTP: $status_code", ['source' => 'actualizar_orden_conekta']);
        $logger->debug("Respuesta completa de Conekta: " . json_encode($response_data), ['source' => 'actualizar_orden_conekta']);

        if ($status_code === 200) {
            $logger->info("Orden #$order_id enviada exitosamente a Conekta", ['source' => 'actualizar_orden_conekta']);

            if ($order->get_status() !== 'processing') {
                $order->update_meta_data('enviado_a_conekta', 1);
                $order->save();
                $logger->debug("Respuesta de Conekta (Final) orden #$order_id: " . json_encode($response_data), ['source' => 'actualizar_orden_conekta']);
                return [true, "✅ El pedido se envió a Conekta, en espera de confirmación para marcar como completado."];
            }
        }

        $error_message = $response_data['details'][0]['message'] ?? ($response_data['message'] ?? 'Error desconocido');

        $logger->error("Error al enviar la orden #$order_id a Conekta - $error_message", [
            'source' => 'actualizar_orden_conekta',
            'order_id' => $order_id,
        ]);

        foreach ($response_data['details'] ?? [] as $detail) {
            $logger->error("Detalle del error: " . json_encode($detail), [
                'source' => 'actualizar_orden_conekta',
                'order_id' => $order_id,
            ]);
        }
        $failedConekta = false;

        switch ($error_message) {
            case "La compra ha sido pagada previamente, si tienes dudas comunícate con el comercio.":
                $status = "processing";
                $note = "⚠️ El pago fue procesado en Conekta, pero ocurrió un error interno. Mensaje: $error_message";
                $failedConekta = true;
                break;

            case "El cliente no tiene fuentes de pago disponibles.":
                $status = "failed";
                $note = "❌ No se pudo procesar el pedido porque el cliente no tiene fuentes de pago disponibles. Mensaje: $error_message";
                $failedConekta = true;
                break;

            default:
                $status = "on-hold";
                $note = "❌ Error desde Conekta: $error_message";
                $failedConekta = true;
                break;
        }

        return [false, $failedConekta, $note, $status];
    }

    /**
     * Obtiene el checkout.url de una orden de Conekta (HostedPayment).
     * Retorna string|NULL
     */
    public static function conekta_get_checkout_url(string $conektaOrderId, string $conektaApiKey): ?string
    {
        $endpoint = 'https://api.conekta.io/orders/' . rawurlencode($conektaOrderId);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            // Basic Auth: username=API_KEY, password vacío
            CURLOPT_USERPWD        => $conektaApiKey . ':',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/vnd.conekta-v2.1.0+json',
            ],
            CURLOPT_TIMEOUT        => 20,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \RuntimeException('Conekta cURL error: ' . $curlErr);
        }

        $data = json_decode($response, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $msg = $data['details'][0]['message'] ?? ('HTTP ' . $httpCode);
            throw new \RuntimeException('Conekta API error: ' . $msg);
        }

        // Si la orden fue creada con HostedPayment, aquí viene el link
        return $data['checkout']['url'] ?? null;
    }
}