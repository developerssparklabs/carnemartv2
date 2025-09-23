<?php

class WebhookClearSales
{
    private static ?self $instance = null;

    /** @var ClearSales|null */
    private ?ClearSales $clearSales = null;

    /** @var ClearSalesOpenpay|null */
    private ?ClearSalesOpenpay $clearSalesOpenpay = null;

    /** Fuente homogénea para los logs */
    private const LOG_SOURCE = 'Clearsale Webhook';

    /** Lock por transiente (segundos) */
    private const LOCK_TTL = 180;

    public static function get_instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Endpoint principal
     */
    public function actualizar_orden_clearsales(WP_REST_Request $request): WP_REST_Response
    {
        $logger = wc_get_logger();
        $data = (array) $request->get_json_params();

        $this->log_request($data);
        $logger->info('Webhook recibido', ['source' => self::LOG_SOURCE, 'data' => $data]);

        // Validación de payload
        if (!$this->is_valid_payload($data)) {
            $logger->warning('Payload inválido', ['source' => self::LOG_SOURCE]);
            return new WP_REST_Response(['message' => 'Datos inválidos.'], 400);
        }

        $order_id = (int) $data['ID'];
        $newStatus = (string) $data['Status'];

        // Lock por transiente para evitar carreras
        $lock_key = "cs_wh_lock_{$order_id}";
        if (!$this->acquire_lock($lock_key)) {
            $logger->warning("Webhook concurrente/duplicado con lock activo: {$order_id}", ['source' => self::LOG_SOURCE]);
            return new WP_REST_Response(['message' => 'Evento duplicado, omitido.'], 200);
        }

        try {
            // Obtener pedido
            $order = wc_get_order($order_id);
            if (!$order) {
                $logger->warning("Pedido no encontrado: {$order_id}", ['source' => self::LOG_SOURCE]);
                return new WP_REST_RESPONSE(['message' => 'Pedido no encontrado.'], 404);
            }

            // Idempotencia por decisión ya procesada
            $ultima_decision = (string) $order->get_meta('decision_clearsale');
            if ($ultima_decision === $newStatus) {
                $logger->info("Decisión ya aplicada previamente ({$newStatus}) en #{$order_id}", ['source' => self::LOG_SOURCE]);
                return new WP_REST_Response(['message' => 'Webhook ya procesado previamente.'], 208);
            }

            // Inicializa cliente (Conekta por defecto u Openpay)
            $is_openpay = $this->is_openpay_order($order_id);
            if ($is_openpay) {
                $client = $this->bootstrap_openpay_client($logger);
                if (!$client) {
                    return new WP_REST_Response(['message' => 'Clase ClearSalesOpenpay no disponible.'], 500);
                }
                // Openpay delega todo en su clase
                $result = $client->clearsales_cambiar_pedido($data);
                return new WP_REST_Response([
                    'is_openpay' => true,
                    'order' => $order_id,
                    'cambiar_pedido' => $result
                ], 200);
            }

            // Cliente ClearSale/Conekta
            $this->init_clearsales_client($order_id);
            if (!$this->clearSales) {
                $logger->error("No se pudo inicializar ClearSales en #{$order_id}", ['source' => self::LOG_SOURCE]);
                return new WP_REST_Response(['message' => 'No se pudo inicializar ClearSales.'], 500);
            }

            // Aplica la decisión de ClearSale a nivel interno
            [$okCambio, $dataCambio] = $this->clearSales->clearsales_cambiar_pedido($data);
            if (!$okCambio) {
                $this->add_order_note_once($order, (string) $dataCambio, 'cs_err_cambiar_pedido');
                return new WP_REST_Response(['message' => $dataCambio], 422);
            }

            // dataCambio => [msgOK, order_id, wc_status, nota, should_update, conekta_order_id, conekta_customer_id]
            [$mensaje, $_oid, $wc_status, $nota, $should_update, $conekta_order_id, $conekta_customer_id] = $dataCambio;

            // Persistir meta con idempotencia
            $order->update_meta_data('decision_clearsale', (string) $newStatus);
            $order->save();

            // Actualizar estado y nota (un solo lugar para no duplicar)
            $this->set_status_and_note($order, $wc_status, $nota);

            // Si procede, actualizar en Conekta
            if ($should_update) {
                $data = $this->clearSales->actualizar_pedido($order_id, $conekta_order_id, $conekta_customer_id);
                if (!$data[0]) {
                    $location_id = $order->get_meta('location_id');
                    $key_prefix = (WP_ENVIRONMENT_TYPE === 'dev') ? 'sandbox_' : '';
                    $api_key_private = get_term_meta($location_id, $key_prefix . 'location_api_key', true);

                    // cambiamos el status
                    $this->set_status_and_note($order, $data[3], $data[2]);

                    if ($data[1]) {
                        $conekta_order_id = $order->get_meta('conekta-order-id');
                        try {
                            $checkoutUrl = ConektaService::conekta_get_checkout_url($conekta_order_id, $api_key_private);

                            if ($checkoutUrl) {
                                OrderHelper::update_status_and_note($order_id, '', "⚠️ El pago falló. Por favor comuníquese con el cliente y proporciónele esta liga: {$checkoutUrl}", false);
                                $this->enviar_email_fallo_coneckta($order->get_billing_email(), [
                                    'customer_name' => $order->get_billing_first_name(),
                                    'retry_url' => $checkoutUrl,
                                ]);
                            }
                        } catch (Throwable $e) {
                            $logger->error("Error al obtener URL de checkout en Conekta: " . $e->getMessage(), ['source' => self::LOG_SOURCE]);
                        }
                    }
                    return new WP_REST_Response(['message' => 'Error al actualizar pedido en Conekta.'], 500);
                } else {
                    $this->add_order_note_once($order, "✅ Pedido enviado/actualizado en Conekta.", 'cs_ok_update_conekta');
                }
            }

            return new WP_REST_Response(['message' => '✅ Pedido actualizado correctamente.'], 200);

        } finally {
            $this->release_lock($lock_key);
        }
    }

    public function enviar_email_fallo_coneckta($to, $args = array())
    {
        // --- Parámetros por defecto (puedes sobreescribirlos al llamar el método) ---
        $defaults = array(
            'customer_name' => 'Cliente',
            'retry_url' => 'https://pay.conekta.com/checkout/0c699050d96f47d3ad32945a1f1e879e',
            'wa_number' => '5216141296248',
        );
        $v = wp_parse_args((array) $args, $defaults);

        // --- Forzar HTML y remitente de marca SOLO para este envío ---
        $content_type_cb = function () {
            return 'text/html; charset=UTF-8';
        };
        add_filter('wp_mail_content_type', $content_type_cb);

        $from_name_cb = function ($name) {
            return 'CarneMart';
        };
        add_filter('wp_mail_from_name', $from_name_cb);

        $from_addr_cb = function ($addr) {
            $host = parse_url(home_url(), PHP_URL_HOST);
            if (!$host) {
                $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'carnemart.com';
            }
            return 'no-reply@' . $host;
        };
        add_filter('wp_mail_from', $from_addr_cb);

        // --- Asunto y HTML ---
        $subject = 'Tu pago no se completó – vuelve a intentarlo | CarneMart';

        $wa_link = sprintf(
            'https://api.whatsapp.com/send/?phone=%s&text&type=phone_number&app_absent=0',
            rawurlencode($v['wa_number'])
        );

        $html = '
    <div style="font-family:Arial,Helvetica,sans-serif; background:#f6f6f6; padding:24px;">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden">
        <tr>
          <td style="padding:20px 24px; background:#021b6d; color:#fff; text-align:center;">
            <img src="https://carnemart.com/wp-content/uploads/2025/09/logo-carnemart@2x.png" alt="CarneMart" style="height:36px; width:auto; display:inline-block; vertical-align:middle;">
          </td>
        </tr>
        <tr>
          <td style="padding:24px 24px 8px; color:#222;">
            <p style="margin:0 0 12px; font-size:16px;">Hola <strong>' . esc_html($v['customer_name']) . '</strong>,</p>
            <p style="margin:0 0 12px; font-size:16px; line-height:1.45;">
              Notamos que tu pago no se pudo completar. No te preocupes, puedes intentarlo de nuevo de manera segura con el siguiente enlace:
            </p>
            <p style="text-align:center; margin:20px 0 16px;">
              <a href="' . esc_url($v['retry_url']) . '" style="display:inline-block; background:#0ea5e9; color:#fff; text-decoration:none; padding:12px 18px; border-radius:6px; font-weight:600;">
                Reintentar pago
              </a>
            </p>
            <p style="margin:0 0 8px; font-size:14px; color:#555; word-break:break-all;">
              Si lo prefieres, copia y pega este enlace en tu navegador: <br>
              <a href="' . esc_url($v['retry_url']) . '" style="color:#0ea5e9;">' . esc_html($v['retry_url']) . '</a>
            </p>
            <hr style="border:none;border-top:1px solid #eee; margin:20px 0;">
            <p style="margin:0 0 10px; font-size:16px;">
              ¿Dudas o necesitas ayuda? Contáctanos por WhatsApp:
            </p>
            <p style="margin:0 0 16px; font-size:16px;">
              <strong>+' . esc_html($v['wa_number']) . '</strong><br>
              <a href="' . esc_url($wa_link) . '" style="color:#0ea5e9;">' . esc_html($wa_link) . '</a>
            </p>
            <p style="margin:12px 0 0; font-size:16px;">¡Gracias por tu preferencia!<br>
              <strong>El equipo de CarneMart</strong>
            </p>
          </td>
        </tr>
      </table>
    </div>';

        // --- Enviar ---
        $sent = wp_mail($to, $subject, $html);

        // --- Limpiar filtros temporales ---
        remove_filter('wp_mail_content_type', $content_type_cb);
        remove_filter('wp_mail_from_name', $from_name_cb);
        remove_filter('wp_mail_from', $from_addr_cb);

        return (bool) $sent;
    }

    /* ----------------------- Helpers ----------------------- */

    private function is_valid_payload(array $data): bool
    {
        return isset($data['ID'], $data['Status']) && (string) $data['ID'] !== '' && (string) $data['Status'] !== '';
    }

    private function is_openpay_order(int $order_id): bool
    {
        $method = get_post_meta($order_id, '_payment_method', true);
        return $method === 'openpay_cards';
    }

    private function bootstrap_openpay_client(WC_Logger $logger): ?ClearSalesOpenpay
    {
        if (!class_exists('ClearSalesOpenpay')) {
            $path = WP_PLUGIN_DIR . '/bafar-openpay-cards/ClearSalesOpenpay.php';
            if (file_exists($path)) {
                require_once $path;
            }
        }
        if (!class_exists('ClearSalesOpenpay')) {
            $logger->error('Clase ClearSalesOpenpay no encontrada', ['source' => self::LOG_SOURCE]);
            return null;
        }
        $this->clearSalesOpenpay = new ClearSalesOpenpay();
        return $this->clearSalesOpenpay;
    }

    private function init_clearsales_client(int $order_id): void
    {
        // Mantengo tu estrategia de llaves para no romper integración actual
        $location_id = OrderHelper::obtener_location_id($order_id);
        if (!is_numeric($location_id)) {
            wc_get_logger()->error("location_id inválido: {$location_id}", ['source' => self::LOG_SOURCE]);
            return;
        }
        $keys = OrderHelper::get_keys($location_id);
        if (empty($keys['private'])) {
            wc_get_logger()->error("Clave privada no disponible para location_id: {$location_id}", ['source' => self::LOG_SOURCE]);
            return;
        }
        $this->clearSales = new ClearSales((string) $keys['private']);
    }

    private function set_status_and_note(WC_Order $order, string $wc_status, string $note): void
    {
        // Aplica estado solo si cambia (evita múltiples transiciones iguales)
        $current = $order->get_status(); // sin prefijo wc-
        if ($current !== $wc_status) {
            $order->update_status($wc_status);
        }
        $this->add_order_note_once($order, $note, "cs_note_{$wc_status}");
    }

    /**
     * Añade una nota solo una vez por clave (meta booleano)
     */
    private function add_order_note_once(WC_Order $order, string $note, string $meta_flag_key): void
    {
        $flag = (string) $order->get_meta($meta_flag_key);
        if ($flag === '1') {
            return;
        }
        $order->add_order_note($note);
        $order->update_meta_data($meta_flag_key, '1');
        $order->save();
    }

    private function acquire_lock(string $key): bool
    {
        if (get_transient($key)) {
            return false;
        }
        set_transient($key, 1, self::LOCK_TTL);
        return true;
    }

    private function release_lock(string $key): void
    {
        delete_transient($key);
    }

    private function log_request(array $data): void
    {
        // Log a archivo externo (opcional). Mantenemos tu patrón.
        $file = _CARNEMART_CORE_PLUGIN_DIR . '/Logs_Webhook_ClearSales.log';
        $ts = date('Y-m-d H:i:s');
        // Algunos entornos no tienen getallheaders()
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $entry = "[{$ts}] Webhook recibido\n";
        $entry .= "Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
        $entry .= "URL: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        $entry .= "Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
        $entry .= "Body: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        error_log($entry, 3, $file);
    }
}