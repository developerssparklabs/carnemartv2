<?php

class ClearSales
{
    private string $api_clearsales_clientsecret;
    private string $api_clearsales_clientid;
    private string $api_clearsales_apikey;
    private string $api_clearsales_url;

    /** @var ConektaService */
    private $serviceConekta;

    private const LOG_SOURCE = 'Clearsale Service';

    public function __construct(string $api_key_private)
    {
        // Opciones persistidas
        $this->api_clearsales_clientsecret = (string) get_option('custom_plugin_api_clearsales_clientsecret', '');
        $this->api_clearsales_clientid = (string) get_option('custom_plugin_api_clearsales_clientid', '');
        $this->api_clearsales_apikey = (string) get_option('custom_plugin_api_clearsales_apikey', '');
        $this->api_clearsales_url = (string) get_option('custom_plugin_api_clearsales_url', '');

        $this->serviceConekta = new ConektaService($api_key_private);
    }

    /**
     * Enviar orden a ClearSale (no se usa en el webhook, pero dejo refactor limpio)
     * Retorna [bool, string|mixed]
     */
    public function send_order(int $order_id, string $raw_conekta_customer_payload): array
    {
        $logger = wc_get_logger();
        $logger->info("Iniciando envío de orden #{$order_id}", ['source' => self::LOG_SOURCE]);

        $order = wc_get_order($order_id);
        if (!$order) {
            return [false, "Orden no encontrada: {$order_id}"];
        }

        $cliente_data = json_decode($raw_conekta_customer_payload, true) ?: [];

        $orden = $this->woo_get_order_data($order_id);
        if (!$orden) {
            return [false, "Error al obtener los datos de la orden #{$order_id}"];
        }

        [$okAuth, $auth] = $this->clearsales_auth();
        if (!$okAuth) {
            return [false, $auth];
        }

        // parent_id de conekta
        $parent_id = $cliente_data['parent_id'] ?? ($cliente_data['payment_sources']['data'][0]['parent_id'] ?? null);
        if (empty($parent_id)) {
            return [false, "No se pudo determinar el parent_id para la orden #{$order_id}"];
        }

        // Guarda/actualiza meta conekta-customer-id
        $order->update_meta_data('conekta-customer-id', $parent_id);
        $order->save();

        // Fuente de pago
        [$okPS, $ps] = $this->serviceConekta->getPaymentSource($parent_id);
        if (!$okPS) {
            return [false, $ps];
        }

        // Mapas marca/tipo
        $brand_map = ['visa' => 3, 'mastercard' => 2];
        $type_map = ['credit' => 1, 'debit' => 3];

        $cardType = $brand_map[$ps['brand'] ?? ''] ?? null;
        $paymentType = $type_map[$ps['card_type'] ?? ''] ?? null;
        if (!$cardType || !$paymentType) {
            return [false, "Tipo de tarjeta/pago no reconocido para el cliente {$parent_id}"];
        }

        $cardEndNumber = (string) ($ps['last4'] ?? '');
        $cardExpirationDate = (string) (($ps['exp_month'] ?? '') . '/' . ($ps['exp_year'] ?? ''));
        $cardHolderName = (string) ($ps['name'] ?? '');
        $cardBin = (string) ($ps['bin'] ?? '');

        // Seguridad en productos: quantities redondeadas + array
        foreach ($orden['products'] as &$p) {
            $p['quantity'] = (int) round($p['quantity']);
        }
        unset($p);
        if (!is_array($orden['products'])) {
            $orden['products'] = [$orden['products']];
        }

        $payload = [
            'ApiKey' => $this->api_clearsales_apikey,
            'LoginToken' => $auth['Token']['Value'] ?? '',
            'AnalysisLocation' => 'USA',
            'Orders' => [
                [
                    'ID' => $orden['orderId'],
                    'Date' => $orden['orderDate'],
                    'Email' => $orden['email'],
                    'TotalItems' => $orden["totalOrder"],
                    'TotalOrder' => $orden['totalOrder'],
                    'TotalShipping' => $orden['totalShipping'],
                    'Currency' => $orden['currency'],
                    "IP" => $orden["ip"],
                    "Origin" => $orden["origin"],
                    'Payments' => [
                        [
                            'Date' => date('c'),
                            'CardType' => $cardType,
                            'CardExpirationDate' => $cardExpirationDate,
                            'Type' => $paymentType,
                            'CardHolderName' => $cardHolderName,
                            'CardEndNumber' => $cardEndNumber,
                            'Amount' => $orden['totalOrder'],
                            'CardBin' => $cardBin,
                        ]
                    ],
                    'Items' => $orden['products'],
                    'SessionID' => uniqid(),
                    'Reanalysis' => false,
                    "BillingData" => [
                        "ID" => $orden["billingId"],
                        "Type" => $orden["billingType"],
                        "Name" => $orden["billingName"],
                        "Email" => $orden["email"],
                        "LegalDocument" => $orden["legalDocument"],
                        "Address" => [
                            "AddressLine1" => $orden["addressLine1"],
                            "AddressLine2" => $orden["addressLine2"],
                            "City" => $orden["city"],
                            "State" => $orden["state"],
                            "ZipCode" => $orden["zipCode"],
                            "Country" => $orden["country"],
                        ],
                        "Phones" => [
                            [
                                "Type" => 0,
                                "AreaCode" => "",
                                "Number" => $orden["phoneNumber"],
                            ]
                        ],
                    ],
                    "ShippingData" => [
                        "ID" => $orden["shippingId"],
                        "Type" => "1",
                        "Name" => $orden["shippingFirstName"] . " " . $orden["shippingLastName"],
                        "Email" => $orden["email"],
                        "Address" => [
                            "AddressLine1" => $orden["shippingAddress1"],
                            "AddressLine2" => $orden["shippingAddress2"],
                            "City" => $orden["shippingCity"],
                            "State" => $orden["shippingState"],
                            "ZipCode" => $orden["shippingPostcode"],
                            "Country" => $orden["shippingCountry"],
                        ],
                        "Phones" => [
                            [
                                "Type" => 0,
                                "AreaCode" => "",
                                "Number" => $orden["phoneNumber"]
                            ]
                        ],
                    ],
                ]
            ],
        ];

        $logger->debug('Payload CS send_order: ' . wp_json_encode($payload), ['source' => self::LOG_SOURCE]);

        $response = wp_remote_post(rtrim($this->api_clearsales_url, '/') . '/api/order/send', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => wp_json_encode($payload),
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return [false, 'Error de conexión con ClearSale: ' . $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if (isset($json['Message']) && strtolower($json['Message']) === 'the request is invalid.') {
            return [false, 'Orden rechazada por ClearSale: ' . json_encode($json['ModelState'] ?? [])];
        }

        return [true, '✅ El pedido se envió al sistema anti-fraude ClearSale.'];
    }

    /**
     * Aplica la decisión del webhook de ClearSale.
     * Retorna [bool, mixed]
     *   true  => [mensajeOK, order_id, wc_status, nota, should_update_conekta, conekta_order_id, conekta_customer_id]
     *   false => "mensaje de error"
     */
    public function clearsales_cambiar_pedido(array $request): array
    {
        $logger = wc_get_logger();

        $order_id = (int) ($request['ID'] ?? 0);
        $decision = (string) ($request['Status'] ?? '');

        if (!$order_id || $decision === '') {
            return [false, 'Faltan datos para procesar la decisión ClearSale.'];
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return [false, "No se encontró la orden #{$order_id}"];
        }

        // Validaciones metadatos conekta
        $conekta_order_id = (string) $order->get_meta('conekta-order-id');
        $conekta_customer_id = (string) $order->get_meta('conekta-customer-id');

        if (empty($conekta_order_id) || empty($conekta_customer_id)) {
            return [false, 'Metadatos de Conekta ausentes (conekta-order-id / conekta-customer-id).'];
        }

        // Decisiones
        $acciones = [
            'APA' => ['pending', 'El pedido ha sido aprobado automáticamente en el sistema anti-fraude y se está enviando a Conekta para su procesamiento.', true],
            'APM' => ['pending', 'El pedido ha sido aprobado manualmente en el sistema anti-fraude y se está enviando a Conekta para su procesamiento.', true],
            'RPM' => ['failed', 'Estimado Cliente, el pedido ha sido rechazado por nuestro sistema anti-fraude ClearSale sin ningún perjuicio.', false],
            'AMA' => ['on-hold', 'Estimado Cliente, el pedido está en análisis manual y en espera de respuesta de ClearSale.', false],
            'ERR' => ['failed', 'Estimado Cliente, el pedido ha fallado debido a un error. Revise la consola de ClearSale para más detalles.', false],
            'NVO' => ['pending', 'Estimado Cliente, se ha recibido un nuevo pedido, en espera de procesamiento.', false],
            'SUS' => ['on-hold', 'Estimado Cliente, el pedido está en espera debido a sospecha de fraude.', false],
            'CAN' => ['cancelled', 'Estimado Cliente, ha solicitado la cancelación del pedido.', false],
            'FRD' => ['failed', 'Estimado Cliente, el pedido ha sido confirmado como fraude y ha fallado.', false],
            'RPA' => ['failed', 'Estimado Cliente, el pedido ha sido rechazado automáticamente por el sistema antifraude; estaremos en contacto con usted.', false],
            'RPP' => ['cancelled', 'Estimado Cliente, el pedido ha sido rechazado por política interna.', false],
        ];
        $default = ['pending', 'Estimado Cliente, el estado del pedido no ha sido reconocido, por lo que permanece en pendiente.', false];

        [$wc_status, $nota, $procesar_conekta] = $acciones[$decision] ?? $default;

        // OK
        return [
            true,
            [
                "✅ Decisión ClearSale aplicada: {$decision}",
                $order_id,
                $wc_status,
                $nota,
                $procesar_conekta,
                $conekta_order_id,
                $conekta_customer_id,
            ]
        ];
    }

    /**
     * Actualiza la orden en Conekta
     * Retorna [bool, string]
     */
    public function actualizar_pedido(int $order_id, ?string $conekta_order_id, ?string $conekta_customer_id): array
    {
        if (!$conekta_order_id || !$conekta_customer_id) {
            return [false, 'Faltan IDs de Conekta para actualizar el pedido.'];
        }
        return $this->serviceConekta->updateOrder($conekta_order_id, $conekta_customer_id, $order_id);
    }

    /* ----------------------- Internos ----------------------- */

    /**
     * Autenticación en ClearSale
     */
    private function clearsales_auth(): array
    {
        $payload = [
            'Login' => [
                'ApiKey' => $this->api_clearsales_apikey,
                'ClientId' => $this->api_clearsales_clientid,
                'ClientSecret' => $this->api_clearsales_clientsecret,
            ],
        ];

        $resp = wp_remote_post(rtrim($this->api_clearsales_url, '/') . '/api/auth/login', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic Og==',
            ],
            'body' => wp_json_encode($payload),
            'method' => 'POST',
            'data_format' => 'body',
            'timeout' => 20,
        ]);

        if (is_wp_error($resp)) {
            return [false, 'Error de conexión con ClearSale: ' . $resp->get_error_message()];
        }

        $body = wp_remote_retrieve_body($resp);
        $dec = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [false, 'Respuesta JSON inválida desde ClearSale: ' . json_last_error_msg()];
        }

        if (!isset($dec['Token']['Value'])) {
            return [false, 'Error al autenticarse en ClearSale: Token no recibido'];
        }

        return [true, $dec];
    }

    /**
     * Datos mínimos de la orden para ClearSale
     */
    public function woo_get_order_data($order_id)
    {
        // Obtén la orden
        $order = wc_get_order($order_id);

        if (!$order) {
            return false;
        }

        // Extrae los datos y asigna a variables
        $orderId = $order->get_id();
        $orderDate = $order->get_date_created()->date("c");
        $email = $order->get_billing_email();
        $totalItems = $order->get_item_count();
        $totalOrder = $order->get_total();
        $totalShipping = $order->get_shipping_total();
        $currency = $order->get_currency();
        $ip = $order->get_customer_ip_address();
        $origin = "Mobile"; // Esto debe ser determinado por algún criterio en tu lógica
        $paymentDate = $order->get_date_paid() ? $order->get_date_paid()->date("Y-m-d") : "";
        $cardType = 3; // Este valor debe ser determinado por tu lógica
        $cardExpirationDate = ""; // Este valor debe ser determinado por tu lógica
        $paymentType = 3; // Este valor debe ser determinado por tu lógica
        $cardHolderName = ""; // Este valor debe ser determinado por tu lógica
        $cardEndNumber = ""; // Este valor debe ser determinado por tu lógica
        $amount = $totalOrder;
        $cardBin = ""; // Este valor debe ser determinado por tu lógica
        $billingId = $orderId;
        $billingType = "1"; // Este valor debe ser determinado por tu lógica
        $billingName = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
        // el legalDocument sera el ID del client que hizo el pedido 
        $legalDocument = (int) $order->get_customer_id();
        $addressLine1 = $order->get_billing_address_1();
        $addressLine2 = $order->get_billing_address_2();
        $city = $order->get_billing_city();
        $state = $order->get_billing_state();
        $zipCode = $order->get_billing_postcode();
        $country = $order->get_billing_country();
        $billing_phone = $order->get_billing_phone();
        $shipping_phone = $order->get_shipping_phone();
        $phoneNumber = $order->get_billing_phone();
        $shippingId = $order->get_id(); // Puede necesitar lógica adicional si es diferente
        $productId = "";
        $productTitle = "";
        $price = 0;
        // Detalles de envío
        $shippingFirstName = $order->get_shipping_first_name();
        $shippingLastName = $order->get_shipping_last_name();
        $shippingName = $shippingFirstName . " " . $shippingLastName;
        $shippingAddress1 = $order->get_shipping_address_1();
        $shippingAddress2 = $order->get_shipping_address_2();
        $shippingCity = $order->get_shipping_city();
        $shippingState = $order->get_shipping_state();
        $shippingPostcode = $order->get_shipping_postcode();
        $shippingCountry = $order->get_shipping_country();

        // Variables para los productos en la orden
        $products = [];

        // Si hay productos en la orden, almacénalos en el array
        $items = $order->get_items();
        foreach ($items as $item) {
            $products[] = [
                "productId" => $item->get_product_id(),
                "productTitle" => $item->get_name(),
                "price" => $item->get_total() / $item->get_quantity(), // Precio por unidad
                "quantity" => $item->get_quantity(),
                "total" => $item->get_total(),
            ];
        }

        // Devuelve los datos como un array
        return compact(
            "billing_phone",
            "shipping_phone",
            "shippingFirstName",
            "shippingLastName",
            "shippingName",
            "shippingAddress1",
            "shippingAddress2",
            "shippingCity",
            "shippingState",
            "shippingCountry",
            "shippingPostcode",
            "orderId",
            "orderDate",
            "email",
            "totalItems",
            "totalOrder",
            "totalShipping",
            "currency",
            "ip",
            "origin",
            "paymentDate",
            "cardType",
            "cardExpirationDate",
            "paymentType",
            "cardHolderName",
            "cardEndNumber",
            "amount",
            "cardBin",
            "billingId",
            "billingType",
            "billingName",
            "legalDocument",
            "addressLine1",
            "addressLine2",
            "city",
            "state",
            "zipCode",
            "country",
            "phoneType",
            "areaCode",
            "phoneNumber",
            "shippingId",
            "productId",
            "productTitle",
            "price",
            "products"
        );
    }
}