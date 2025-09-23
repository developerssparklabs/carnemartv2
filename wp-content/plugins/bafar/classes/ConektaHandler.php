<?php

/**
 * Clase encargada de manejar el formulario de pago con Conekta.
 * Se registra mediante un shortcode [conekta_checkout].
 */
class ConektaHandler
{
    /**
     * Clave pública de la API de Conekta.
     *
     * @var string
     */
    private $api_key_public;

    /**
     * Clave privada de la API de Conekta.
     */
    private $api_key_private;

    /**
     * Propiedad para almacenar la instancia de ClearSales.
     */

    private $clearSales = null;

    /**
     * Constructor de la clase.
     * Registra el shortcode que muestra el formulario de pago.
     *
     * @param string $api_key_public Clave pública de Conekta.
     */
    public function __construct(string $api_key_public = "", string $api_key_private = "")
    {

        // Agregar shortcode para el formulario de Conekta Checkout
        add_shortcode("conekta_checkout", [
            $this,
            "conekta_checkout_shortcode"
        ]);

        // Agregar acción AJAX para registrar cliente en Conekta
        add_action("wp_ajax_registra_cliente_conekta", [
            $this,
            "conekta_registra_cliente_callback",
        ]);
        add_action("wp_ajax_nopriv_registra_cliente_conekta", [
            $this,
            "conekta_registra_cliente_callback",
        ]);

        $this->api_key_public = $api_key_public;
        $this->api_key_private = $api_key_private;

        // instanciar ClearSales y guardar valor en la propiedad, verificamos si ya existe la instancia
        if ($this->clearSales == null) {
            $this->clearSales = new ClearSales(api_key_private: $this->api_key_private);
        }
    }

    /**
     * Renderiza el formulario de pago de Conekta Checkout cuando se usa el shortcode [conekta_checkout].
     *
     * En el proceso de pago:
     * - Cuando el usuario completa el formulario y hace clic en "Continuar",
     *   Conekta genera un `token.id` que representa de forma segura los datos de tarjeta.
     * - Ese token es enviado vía AJAX (`admin-ajax.php`) al backend de WordPress mediante una acción personalizada (`registra_cliente_conekta`).
     * - En ese punto, puedes usar el token para crear un cargo o suscripción desde tu servidor con la clave privada.
     *
     * @return string HTML renderizado para el formulario de Conekta.
     */
    public function conekta_checkout_shortcode()
    {
        ob_start();

        // Decodificar el ID de la orden desde la URL
        $order_id = isset($_GET["id"]) ? base64_decode($_GET["id"]) : null;
        $order = $order_id ? wc_get_order($order_id) : null;

        if (!$order) {
            echo esc_html__("La orden no fue encontrada.", "text-domain");
            return ob_get_clean();
        }

        // Extraer datos de facturación
        $order_data = $order->get_data();
        $billing = $order_data["billing"] ?? [];

        $first_name = $billing["first_name"] ?? "";
        $last_name = $billing["last_name"] ?? "";
        $email = $billing["email"] ?? "";
        $order_total = $order->get_total();

        ?>
        <div id="conekta_widget" style="height: 524px"></div>

        <!-- Script de Conekta -->
        <script src="https://pay.conekta.com/v1.0/js/conekta-checkout.min.js" crossorigin></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Mostrar nombre y monto a pagar en la página
                const quienpagaDiv = document.getElementById('quienapagar');
                if (quienpagaDiv) {
                    quienpagaDiv.textContent = <?php echo json_encode(trim($first_name . " " . $last_name)); ?>;
                }

                const precioapagarDiv = document.getElementById('precioapagar');
                if (precioapagarDiv) {
                    precioapagarDiv.textContent = "$" + <?php echo json_encode(number_format($order_total, 2)); ?>;
                }

                // Opciones visuales del widget
                const options = {
                    backgroundMode: 'lightMode',
                    colorPrimary: '#081133',
                    colorText: '#585987',
                    colorLabel: '#585987',
                    inputType: 'minimalMode'
                };

                // Configuración del widget de Conekta
                const apiKey = <?php echo json_encode($this->api_key_public); ?>;

                if (!apiKey) {
                    const widgetContainer = document.getElementById("conekta_widget");

                    if (widgetContainer) {
                        widgetContainer.innerHTML = `
                            <div style="padding: 1rem; background: #ffeaea; border: 1px solid #ff5f5f; border-radius: 8px; color: #990000; font-weight: bold;">
                                ⚠️ Hemos tenido un error con la plataforma de pago. <br> Por favor, inténtelo más tarde.
                            </div>
                        `;
                    }
                    return;
                }

                const config = {
                    locale: 'es',
                    publicKey: apiKey,
                    targetIFrame: '#conekta_widget'
                };

                // Callbacks del proceso de tokenización
                const callbacks = {
                    onCreateTokenSucceeded: function (token) {
                        jQuery(document).ready(function ($) {
                            const loader = document.getElementById('msgLoading');
                            if (loader) {
                                loader.style.display = 'flex'; // Muestra loader
                            }

                            $.ajax({
                                url: "/wp-admin/admin-ajax.php",
                                type: 'POST',
                                headers: {
                                    'X-From-Conekta': 'true'
                                },
                                data: {
                                    action: 'registra_cliente_conekta',
                                    email: <?php echo json_encode($email); ?>,
                                    name: <?php echo json_encode(trim($first_name . " " . $last_name)); ?>,
                                    id_orden: <?php echo json_encode($order_id); ?>,
                                    token: token.id
                                },
                                success: function (response) {
                                    const appElement = document.getElementById("example");
                                    if (appElement) {
                                        appElement.style.transition = "opacity 0.5s";
                                        appElement.style.opacity = 0;
                                    }

                                    const nombreTxtElement = document.querySelector(".nombretxt");
                                    if (nombreTxtElement) {
                                        nombreTxtElement.textContent = <?php echo json_encode(trim($first_name . " " . $last_name)); ?>;
                                    }

                                    const nopedidotxtElement = document.querySelector(".nopedidotxt");
                                    if (nopedidotxtElement) {
                                        nopedidotxtElement.textContent = <?php echo json_encode($order_id); ?>;
                                    }

                                    const pagoConfirmacionElement = document.getElementById("pagoConfirmacion");
                                    const widgetConekta = document.getElementById("conekta_widget");

                                    if (pagoConfirmacionElement) {
                                        pagoConfirmacionElement.style.display = "block";
                                        if (widgetConekta) widgetConekta.style.display = "none";
                                    }
                                },
                                error: function (xhr, status, error) {
                                    const mensajesElement = document.getElementById("mensajes");
                                    if (mensajesElement) {
                                        mensajesElement.style.display = "block";
                                        mensajesElement.textContent = "Error en sistema: " + error;
                                    }
                                },
                                complete: function () {
                                    if (loader) {
                                        loader.style.display = 'none';
                                    }
                                }
                            });
                        });
                    },
                    onCreateTokenError: function (error) {
                        console.error(error);
                    },
                    onGetInfoSuccess: function (loadingTime) {

                    }
                };

                // Inicializar widget de Conekta
                window.ConektaCheckoutComponents.Card({
                    config,
                    callbacks,
                    options
                });
            });
        </script>

        <?php
        return ob_get_clean();
    }

    public function conekta_registra_cliente_callback()
    {
        $logger = new WC_Logger();

        try {
            // Obtener y sanitizar los datos recibidos
            $email = isset($_POST["email"]) ? sanitize_email($_POST["email"]) : "";
            $name = isset($_POST["name"]) ? sanitize_text_field($_POST["name"]) : "";
            $token = isset($_POST["token"]) ? sanitize_text_field($_POST["token"]) : "";
            $id_orden = isset($_POST["id_orden"]) ? sanitize_text_field($_POST["id_orden"]) : "";

            $logger->info("Inicio de procesamiento para orden $id_orden", ['source' => 'conekta_registra_cliente_callback']);
            $logger->debug("Datos recibidos - Email: $email, Nombre: $name, Token: [oculto], ID Orden: $id_orden", ['source' => 'conekta_registra_cliente_callback']);

            // Validar orden
            $order = wc_get_order($id_orden);
            if (!$order) {
                $logger->error("Orden no encontrada: $id_orden", ['source' => 'conekta_registra_cliente_callback']);
                wp_send_json_error("Orden no encontrada");
                wp_die();
            }

            // Verificar si ya fue enviado a ClearSale
            $ya_enviado = $order->get_meta('enviado_clear_sales');
            if ($ya_enviado == 1) {
                $logger->notice("Orden $id_orden ya fue enviada a ClearSale anteriormente", ['source' => 'conekta_registra_cliente_callback']);
                OrderHelper::update_status_and_note($id_orden, '', "⚠️ Este pedido ya fue enviado a ClearSale anteriormente.", false);
                wp_send_json_success("Ya fue enviado anteriormente");
                wp_die();
            }

            $existeCliente = false;

            // Procesar cliente en Conekta
            $logger->info("Verificando cliente con email: $email en Conekta", ['source' => 'conekta_registra_cliente_callback']);
            $data_cliente = $this->conekta_get_customer($email);
            $logger->info("Datos del cliente en coneckta 1- : " . json_encode($data_cliente), ['source' => 'conekta_registra_cliente_callback']);
            if ($data_cliente["count"] > 0) {
                $allDataCliente = $data_cliente["data"];
                $logger->info("Datos del cliente en coneckta : " . json_encode($allDataCliente), ['source' => 'conekta_registra_cliente_callback']);

                $telefono_clienteConeckta = $allDataCliente['phone'] ?? '';
                $telefono_cliente = "";
                // buscamos por email
                $user = get_user_by('email', $email);
                if ($user) {
                    $user_id = $user->ID;
                }

                // Aqui buscamos el telefono del cliente, ya que su cuenta ya se creo en wordpress
                $telefono_cliente = get_user_meta($user_id, 'billing_phone', true);

                if (empty($telefono_cliente)) {
                    // buscamos el telefono de direccion
                    $telefono_cliente = get_user_meta($user_id, 'shipping_phone', true);
                }

                // verificamos si esta el phone
                if ($telefono_clienteConeckta != '') {
                    $logger->info("El cliente ya tiene teléfono registrado en Conekta: $telefono_clienteConeckta", ['source' => 'conekta_registra_cliente_callback']);
                }

                $isKei = $allDataCliente['name'] == 'kei' ? true : false;
                if ($isKei) {
                    $first_name = get_user_meta($user_id, 'first_name', true);
                    $last_name = get_user_meta($user_id, 'last_name', true);
                    $name_completo = trim($first_name . ' ' . $last_name);
                    $logger->info("El nombre en coneckta es kei, lo cambiamos al nombre completo del wordpress: $name_completo", ['source' => 'conekta_registra_cliente_callback']);
                    $status = $this->conekta_update_customer_name($data_cliente['customers'][0] ?? '', $name_completo);
                    if ($status === null) {
                        $logger->error("Error al actualizar el nombre del cliente en Conekta", ['source' => 'conekta_registra_cliente_callback']);
                    } else {
                        $logger->info("Nombre del cliente actualizado en Conekta: " . json_encode($status), ['source' => 'conekta_registra_cliente_callback']);
                    }
                }

                if ($telefono_clienteConeckta != $telefono_cliente) {
                    $logger->info("Teléfono diferente de coneckta al de wordpress", ['source' => 'conekta_registra_cliente_callback']);
                    $logger->info("Teléfono wordpress: $telefono_cliente | telefono Conekta: $telefono_clienteConeckta", ['source' => 'conekta_registra_cliente_callback']);
                    // actualizamos el telefono del cliente
                    $statusTelefono = $this->conekta_update_customer_phone($data_cliente['customers'][0], $telefono_cliente);

                    if (is_wp_error($statusTelefono)) {
                        // ERROR
                        $logger->error(
                            'Teléfono NO actualizado en Conekta',
                            [
                                'source' => 'conekta_registra_cliente_callback',
                                'customer_id' => $data_cliente['customers'][0],
                                'error_code' => $statusTelefono->get_error_code(),
                                'error_message' => $statusTelefono->get_error_message(),
                                'error_data' => $statusTelefono->get_error_data(), // puede traer el body de Conekta
                            ]
                        );
                    } else {
                        // ÉXITO
                        $logger->info(
                            'Teléfono actualizado en Conekta',
                            [
                                'source' => 'conekta_registra_cliente_callback',
                                'customer_id' => $statusTelefono['customer_id'] ?? $data_cliente['customers'][0],
                                'phone' => $statusTelefono['phone'] ?? null,
                                'http_code' => $statusTelefono['http_code'] ?? null,
                                'response_id' => $statusTelefono['response']['id'] ?? null, // id del customer
                            ]
                        );
                    }
                }
                // Cliente existente - Agregar método de pago
                $cliente_id = $data_cliente["customers"][0];
                $logger->info("Cliente existente encontrado en Conekta. ID: $cliente_id", ['source' => 'conekta_registra_cliente_callback']);

                $request_body = json_encode([
                    "type" => "card",
                    "token_id" => $token,
                ]);

                $url = "https://api.conekta.io/customers/$cliente_id/payment_sources";
                $existeCliente = true;
            } else {

                // buscamos por email
                $user = get_user_by('email', $email);
                if ($user) {
                    $user_id = $user->ID;
                }

                // Aqui buscamos el telefono del cliente, ya que su cuenta ya se creo en wordpress
                $telefono_cliente = get_user_meta($user_id, 'billing_phone', true);

                if (empty($telefono_cliente)) {
                    // buscamos el telefono de direccion
                    $telefono_cliente = get_user_meta($user_id, 'shipping_phone', true);
                }

                // Cliente nuevo - Crear con método de pago
                $logger->info("Cliente no encontrado. Registrando nuevo cliente en Conekta", ['source' => 'conekta_registra_cliente_callback']);

                $request_body = json_encode([
                    "corporate" => false,
                    "email" => $email,
                    "name" => $name,
                    'phone' => $telefono_cliente ?? '',
                    "payment_sources" => [
                        [
                            "type" => "card",
                            "token_id" => $token,
                        ],
                    ],
                ]);

                $url = "https://api.conekta.io/customers";
            }

            // Enviar solicitud a Conekta
            $logger->debug("Enviando solicitud a Conekta. URL: $url", ['source' => 'conekta_registra_cliente_callback']);
            $response = wp_remote_post($url, [
                "body" => $request_body,
                "headers" => [
                    "Accept-Language" => "es",
                    "accept" => "application/vnd.conekta-v2.1.0+json",
                    "authorization" => "Bearer " . $this->api_key_private,
                    "content-type" => "application/json",
                ],
            ]);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $logger->error("Error en la respuesta de Conekta: $error_message", ['source' => 'conekta_registra_cliente_callback']);
                wp_send_json_error("Error al comunicarse con Conekta: $error_message");
                wp_die();
            } else {
                $logger->info('Respuesta de Conekta (Customers): ' . json_encode($response), ['source' => 'conekta_registra_cliente_callback']);
            }

            $response_body = wp_remote_retrieve_body($response);
            $logger->info("Respuesta exitosa de Conekta", ['source' => 'conekta_registra_cliente_callback']);
            $logger->debug("Detalles de respuesta Conekta: $response_body", ['source' => 'conekta_registra_cliente_callback']);

            // Procesar tipo de tarjeta y validaciones, en caso no se cumpla, simplemente salimos de la función
            $response_data = json_decode($response_body, true);

            $type = $existeCliente
                ? ($response_data['type'] ?? null)
                : ($response_data['payment_sources']['data'][0]['type'] ?? null);

            if ($type === null) {
                $logger->error("Error al procesar el tipo de tarjeta", ['source' => 'conekta_registra_cliente_callback']);
                OrderHelper::update_status_and_note($id_orden, 'failed', '❌ Error crítico: No se pudo procesar el tipo de tarjeta.', true);
                wp_send_json_error("Error crítico: No se pudo procesar el tipo de tarjeta.");
                wp_die();
            }

            if (isset($type)) {

                $card_type = $existeCliente
                    ? ($response_data['card_type'] ?? null)
                    : ($response_data['payment_sources']['data'][0]['card_type'] ?? null);

                $metodo_pago = ($card_type === 'credit') ? '01' : (($card_type === 'debit') ? '02' : '');

                if (!empty($metodo_pago)) {
                    $order->update_meta_data('metodo_pago', $metodo_pago);
                    $logger->info("Tipo de tarjeta identificado: $card_type (Código: $metodo_pago)", ['source' => 'conekta_registra_cliente_callback']);
                    OrderHelper::update_status_and_note($id_orden, '', "💳 Método de pago identificado como: $card_type ($metodo_pago)", false);
                } else {
                    $logger->info("Tipo de tarjeta no soportado: $card_type", ['source' => 'conekta_registra_cliente_callback']);
                    OrderHelper::update_status_and_note($id_orden, '', "⚠️ Tipo de tarjeta no soportado: $card_type", false);
                    return;
                }
            } else {
                $logger->info("No se pudo identificar el tipo de tarjeta en la respuesta", ['source' => 'conekta_registra_cliente_callback']);
                OrderHelper::update_status_and_note($id_orden, '', 'No se pudo identificar el tipo de tarjeta en la respuesta', false);
                return;
            }

            // Enviar a ClearSale
            $logger->info("Iniciando envío de orden $id_orden a ClearSales", ['source' => 'conekta_registra_cliente_callback']);

            $response_clearSales = $this->clearSales->send_order($id_orden, $response_body);
            $logger->info("Finalizacion de ResponseClearSales:: ", ['source' => 'conekta_registra_cliente_callback']);
            $logger->debug("Detalles de respuesta ClearSales: " . json_encode($response_clearSales), ['source' => 'conekta_registra_cliente_callback']);

            if ($response_clearSales[0]) {
                $order->update_meta_data('enviado_clear_sales', 1);
                $order->save();

                $logger->info("Orden $id_orden enviada exitosamente a ClearSales", ['source' => 'conekta_registra_cliente_callback']);
                OrderHelper::update_status_and_note($id_orden, 'pending', $response_clearSales[1], false);
                wp_send_json_success($response_clearSales);
            } else {
                $logger->error("Error al enviar orden $id_orden a ClearSales:: " . $response_clearSales[1], ['source' => 'conekta_registra_cliente_callback']);
                OrderHelper::update_status_and_note($id_orden, 'pending', $response_clearSales[1], false);
                wp_send_json_error($response);
            }

        } catch (Exception $e) {
            $logger->error("Excepción no controlada: " . $e->getMessage(), ['source' => 'conekta_registra_cliente_callback']);
            wp_send_json_error("Error interno del servidor");
        } finally {
            wp_die();
        }
    }

    /**
     * Obtiene clientes desde la API de Conekta usando su correo electrónico.
     *
     * @param string $email Correo electrónico a buscar.
     * @return array|WP_Error Retorna un arreglo con los IDs de cliente y la cantidad, o un error en formato JSON.
     */
    public function conekta_get_customer(string $email)
    {
        $url = "https://api.conekta.io/customers?limit=1&search=" . urlencode($email);

        $args = [
            'headers' => [
                'accept' => 'application/vnd.conekta-v2.1.0+json',
                'authorization' => 'Bearer ' . $this->api_key_private,
            ],
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            return wp_send_json_error("Error en la solicitud: " . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return wp_send_json_error("Error al decodificar JSON: " . json_last_error_msg());
        }

        if (!isset($data['data'])) {
            return wp_send_json_error("Formato inesperado de respuesta. Verifica la documentación de la API de Conekta.");
        }

        return [
            'customers' => array_column($data['data'], 'id'),
            'count' => count($data['data']),
            'data' => $data['data'],
        ];
    }

    private function conekta_headers(): array
    {
        return [
            'Accept-Language' => 'es',
            'Accept' => 'application/vnd.conekta-v2.1.0+json',
            'Authorization' => 'Bearer ' . $this->api_key_private,
            'Content-Type' => 'application/json',
        ];
    }

    /** 3) Actualiza el "name" del cliente */
    public function conekta_update_customer_name(string $customer_id, string $new_name): ?array
    {
        $url = "https://api.conekta.io/customers/{$customer_id}";

        // Headers base (Authorization, Accept, etc.) desde tu helper
        $headers = $this->conekta_headers();

        // Aseguramos content-type y (opcional) el idioma
        $headers['content-type'] = 'application/json';
        $headers['Accept-Language'] = $headers['Accept-Language'] ?? 'es';

        $args = [
            'method' => 'PUT',
            'headers' => $headers,
            'body' => json_encode(
                ['name' => trim($new_name)],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'timeout' => 20,
        ];

        $resp = wp_remote_request($url, $args);
        if (is_wp_error($resp)) {
            return null;
        }

        $code = wp_remote_retrieve_response_code($resp);
        if ($code < 200 || $code >= 300) {
            // Opcional: loguear el error con wp_remote_retrieve_body($resp)
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($resp), true);
        return is_array($body) ? $body : null;
    }

    /**
     * Actualiza el teléfono de un cliente en Conekta.
     *
     * @param string $customer_id  ID del cliente en Conekta (p.ej. "cus_xxx").
     * @param string $raw_phone    Teléfono del cliente (cualquier formato).
     * @return array|WP_Error      Respuesta decodificada de Conekta o WP_Error en fallo.
     */
    public function conekta_update_customer_phone(string $customer_id, string $raw_phone)
    {
        // 1) Normalizar teléfono
        $phone = preg_replace('/\D+/', '', (string) $raw_phone);
        if (strlen($phone) === 10) {

        }
        if ($phone === '') {
            return new WP_Error('invalid_phone', 'Teléfono vacío o inválido después de normalizar.');
        }

        // 2) Request
        $url = "https://api.conekta.io/customers/{$customer_id}";
        $args = [
            'method' => 'PUT',
            'headers' => [
                'Accept-Language' => 'es',
                'Accept' => 'application/vnd.conekta-v2.1.0+json',
                'Authorization' => 'Bearer ' . $this->api_key_private,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode(['phone' => $phone]),
            'timeout' => 30,
        ];

        // 3) Enviar
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return new WP_Error('http_error', 'Error HTTP: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code < 200 || $code >= 300) {
            // Mensaje de error útil de Conekta
            $msg = $body['message_to_purchaser']
                ?? ($body['details'][0]['message'] ?? null)
                ?? $body['message']
                ?? 'Error al actualizar el teléfono en Conekta.';
            return new WP_Error('conekta_error', "HTTP {$code}: {$msg}", ['response' => $body]);
        }

        // 4) OK
        return [
            'ok' => true,
            'http_code' => $code,
            'customer_id' => $customer_id,
            'phone' => $phone,
            'response' => $body, // objeto del cliente actualizado
        ];
    }
}