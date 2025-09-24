<?php

class ClearSalesOpenpay
{
    private $api_clearsales_clientsecret;
    private $api_clearsales_clientid;
    private $api_clearsales_apikey;
    private $api_clearsales_url;

    public function __construct()
    {
        $this->api_clearsales_clientsecret = get_option('custom_plugin_api_clearsales_clientsecret', '');
        $this->api_clearsales_clientid = get_option('custom_plugin_api_clearsales_clientid', '');
        $this->api_clearsales_apikey = get_option('custom_plugin_api_clearsales_apikey', '');
        $this->api_clearsales_url = get_option('custom_plugin_api_clearsales_url', '');
    }

    public function send_order($order_id, $charge)
    {
        $logger = new WC_Logger();
        $logger->info("[Openpay] Iniciando envío a ClearSale para la orden $order_id", ['source' => 'clearsales_openpay']);

        $order = wc_get_order($order_id);
        if (!$order) {
            $logger->error("Orden no encontrada: $order_id", ['source' => 'clearsales_openpay']);
            return [false, "Orden no encontrada: $order_id"];
        }

        $orden = $this->woo_get_order_data($order_id);
        if (!$orden) {
            $logger->error("Error al obtener los datos de la orden: $order_id", ['source' => 'clearsales_send_order']);
            return [false, "Error al obtener los datos de la orden # $order_id."];
        }
        [$auth_ok, $auth_response] = $this->clearsales_auth();
        if (!$auth_ok) {
            $logger->error("Error de autorizacion con clearSales # $order_id: $auth_response", ['source' => 'clearsales_send_order']);
            return [false, $auth_response];
        }
        $login_token = $auth_response;

        $payments = $this->get_openpay_payment_data($charge, $orden);

        foreach ($orden['products'] as &$producto) {
            $producto['quantity'] = round($producto['quantity']);
        }
        $payload = [
            "ApiKey" => $this->api_clearsales_apikey,
            "LoginToken" => $login_token["Token"]["Value"] ?? '',
            "AnalysisLocation" => "USA",
            "Orders" => [
                [
                    "ID" => $orden["orderId"],
                    "Date" => $orden["orderDate"],
                    "Email" => $orden["email"],
                    "TotalItems" => $orden["totalOrder"],
                    "TotalOrder" => $orden["totalOrder"],
                    "TotalShipping" => $orden["totalShipping"],
                    "Currency" => $orden["currency"],
                    "IP" => $orden["ip"],
                    "Origin" => $orden["origin"],
                    "Payments" => [
                        [
                            "Date" => date("c"),
                            "CardType" => $payments['CardType'],
                            "CardExpirationDate" => $payments['CardExpirationDate'],
                            "Type" => $payments['Type'],
                            "CardHolderName" => $payments['CardHolderName'],
                            "CardEndNumber" => $payments['CardEndNumber'],
                            "Amount" => $payments["Amount"],
                            "CardBin" => $payments['CardBin'],
                        ]
                    ],
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
                    "Items" => $orden["products"],
                    "SessionID" => uniqid(),
                    "Reanalysis" => false,
                ],
            ],
        ];
        $logger->info(print_r($payload, true), ['source' => 'clearsales_send_order']);

        $response = wp_remote_post($this->api_clearsales_url . "/api/order/send", [
            "headers" => ["Content-Type" => "application/json"],
            "body" => json_encode($payload),
            "method" => "POST",
            "data_format" => "body",
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $logger->error("Error al enviar la orden (HTTP): $error_message", ['source' => 'clearsales_send_order']);
            wp_send_json_error("Error al enviar orden a ClearSale: $error_message");
            return [false, "Error de conexión con ClearSale: $error_message"];
        }

        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        $logger->info(print_r(["Respuesta ClearSales" => $body], true), ['source' => 'clearsales_send_order']);

        if (isset($json['Message']) && strtolower($json['Message']) === 'the request is invalid.') {
            $logger->error("Orden rechazada por ClearSale: " . json_encode($json['ModelState'] ?? []), [
                'source' => 'clearsales_send_order'
            ]);
            wp_send_json_error("Error de validación al enviar orden a ClearSale.");
            return [false, "Orden rechazada por ClearSale: " . json_encode($json['ModelState'] ?? '')];
        }

        return [true, "✅ El pedido se envió al sistema anti-fraude Clearsales."];
    }

    /**
     * Procesa la información de Openpay y retorna el array de datos de pago para ClearSale
     */
    private function get_openpay_payment_data($charge, $orden)
    {
        $brand_map = [
            'diners' => 1,        // 1 - Diners
            'mastercard' => 2,    // 2 - MasterCard
            'visa' => 3,          // 3 - Visa
            'others' => 4,        // 4 - Others
            'american_express' => 5,          // 5 - American Express
            'hipercard' => 6,     // 6 - HiperCard
            'aura' => 7           // 7 - Aura
        ];
        $type_map = ['credit' => 1, 'debit' => 3];

        $card = $charge->card;

        $cardData = [];
        $bin = '';
        if ($card) {
            $reflection = new ReflectionObject($card);
            if ($reflection->hasProperty('serializableData')) {
                $property = $reflection->getProperty('serializableData');
                $property->setAccessible(true);
                $cardData = $property->getValue($card);
            }
            if (isset($cardData['card_number'])) {
                if (preg_match('/^(\d{6})/', $cardData['card_number'], $matches)) {
                    $bin = $matches[1];
                }
            }
        }

        $brand = isset($cardData['brand']) ? $cardData['brand'] : '';
        $type = isset($cardData['type']) ? $cardData['type'] : '';
        if ($card) {
            $reflection = new ReflectionObject($card);
            if (!$brand && $reflection->hasProperty('brand')) {
                $propBrand = $reflection->getProperty('brand');
                $propBrand->setAccessible(true);
                $brand = $propBrand->getValue($card);
            }
            if (!$type && $reflection->hasProperty('type')) {
                $propType = $reflection->getProperty('type');
                $propType->setAccessible(true);
                $type = $propType->getValue($card);
            }
        }

        error_log(print_r([
            "message" => "OpenPay Card Data",
            "data" => [
                "brand" => $brand,
                "type" => $type,
                "card" => $this->debug_object($card)
            ]
        ], true));

        $cardType = $brand_map[$brand] ?? null;
        $cardExpirationDate = (isset($cardData['expiration_month']) ? $cardData['expiration_month'] : '') . "/" . (isset($cardData['expiration_year']) ? $cardData['expiration_year'] : '');
        $paymentType = $type_map[$type] ?? null;
        $cardHolderName = isset($cardData['holder_name']) ? $cardData['holder_name'] : '';
        $cardEndNumber = isset($cardData['card_number']) ? substr($cardData['card_number'], -4) : '';
        $amount = $orden["totalOrder"];
        $cardBin = $bin;

        return [
            "Date" => date("c"),
            "CardType" => $cardType,
            "CardExpirationDate" => $cardExpirationDate,
            "Type" => $paymentType,
            "CardHolderName" => $cardHolderName,
            "CardEndNumber" => $cardEndNumber,
            "Amount" => $amount,
            "CardBin" => $cardBin,
        ];
    }


    private function clearsales_auth(): array
    {
        $logger = new WC_Logger();
        $url = "{$this->api_clearsales_url}/api/auth/login";

        $payload = json_encode([
            "Login" => [
                "ApiKey" => $this->api_clearsales_apikey,
                "ClientId" => $this->api_clearsales_clientid,
                "ClientSecret" => $this->api_clearsales_clientsecret,
            ],
        ]);

        $args = [
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Basic Og==",
            ],
            "body" => $payload,
            "method" => "POST",
            "data_format" => "body",
        ];

        $logger->info("Enviando solicitud de autenticación a ClearSale", ['source' => 'clearsales_auth']);
        $logger->debug("Payload de autenticación: $payload", ['source' => 'clearsales_auth']);

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $logger->error("Error al autenticar con ClearSale: $error_message", ['source' => 'clearsales_auth']);
            return [false, "Error de conexión con ClearSale: $error_message"];
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        $logger->debug([
            "decoded" => $decoded,
        ], ['source' => 'clearsales_auth']);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $logger->error("Error al decodificar JSON de ClearSale: " . json_last_error_msg(), ['source' => 'clearsales_auth']);
            return [false, "Respuesta JSON inválida desde ClearSale: " . json_last_error_msg()];
        }

        if (!isset($decoded["Token"]["Value"])) {
            $logger->error("Respuesta de autenticación inválida: Token no presente", ['source' => 'clearsales_auth']);
            return [false, "Error al autenticarse en ClearSale: Token no recibido"];
        }

        $logger->info("Autenticación exitosa con ClearSale", ['source' => 'clearsales_auth']);
        $logger->debug("Respuesta decodificada: " . print_r($decoded, true), ['source' => 'clearsales_auth']);

        return [true, $decoded];
    }

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
        $legalDocument = "ine"; // Este valor debe ser determinado por tu lógica
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

    public function clearsales_cambiar_pedido($request)
    {
        $logger = new WC_Logger();

        $logger->info("Iniciando el proceso de cambio de pedido por ClearSale", ['source' => 'ClearSales OpenPay']);
        $logger->debug("Datos recibidos: " . json_encode($request), ['source' => 'ClearSales OpenPay']);

        $data = $request;

        $order_id = $data['ID'] ?? 0;
        $decision = $data['Status'] ?? '';

        if (!$order_id || !$decision) {
            $logger->warning("Faltan datos para procesar la decisión ClearSale", ['data' => $data]);
            return [false, "Faltan datos para procesar la decisión ClearSales"];
        } else {
            $logger->info("Datos recibidos para procesar la decisión ClearSale: ID: $order_id, Status: $decision", ['source' => 'ClearSales OpenPay']);
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            $logger->warning("No se encontró la orden #$order_id", ['source' => 'ClearSales OpenPay']);
            return [false, "No se encontró la orden  #$order_id"];
        } else {
            $logger->info("Orden encontrada: #$order_id", ['source' => 'ClearSales OpenPay']);
        }

        $ultima_decision = $order->get_meta('decision_clearsale');
        if ($ultima_decision === $decision) {
            $logger->info("⚠️ La decisión '$decision' ya fue procesada anteriormente por ClearSale.", context: false);
            return [false, "La decisión '$decision' ya fue procesada anteriormente por ClearSale."];
        } else {
            $logger->info("La decisión '$decision' no ha sido procesada anteriormente. Procediendo con el cambio de estado.", ['source' => 'ClearSales OpenPay']);
        }

        $acciones = [
            'APA' => ['pending', 'El pedido ha sido aprobado automáticamente en el sistema anti-fraude y se está enviando a Conekta para su procesamiento.', true],
            'APM' => ['pending', 'El pedido ha sido aprobado manualmente en el sistema anti-fraude y se está enviando a Conekta para su procesamiento.', true],
            'RPM' => ['failed', 'Estimado Cliente, el pedido ha sido rechazado por nuestro sistema anti-fraude Clearsales sin ningún prejuicio.', false],
            'AMA' => ['on-hold', 'Estimado Cliente, el pedido está en análisis manual y en espera de respuesta de Clearsales.', false],
            'ERR' => ['failed', 'Estimado Cliente, el pedido ha fallado debido a un error. Revise la consola de Clearsales para más detalles.', false],
            'NVO' => ['pending', 'Estimado Cliente, se ha recibido un nuevo pedido, en espera de procesamiento.', false],
            'SUS' => ['on-hold', 'Estimado Cliente, el pedido está en espera debido a sospecha de fraude.', false],
            'CAN' => ['cancelled', 'Estimado Cliente, ha solicitado la cancelación del pedido.', false],
            'FRD' => ['failed', 'Estimado Cliente, el pedido ha sido confirmado como fraude y ha fallado.', false],
            'RPA' => ['failed', 'Estimado Cliente, el pedido ha sido rechazado automáticamente por el sistema antifraude estaremos en contacto con usted.', false],
            'RPP' => ['cancelled', 'Estimado Cliente, el pedido ha sido rechazado por política interna.', false]
        ];

        $default = ['pending', 'Estimado Cliente, el estado del pedido no ha sido reconocido, por lo que permanece en pendiente.'];

        [$status, $note, $procesarOpenpay] = $acciones[$decision] ?? array_merge($default, [false]);

        $existing_decision = $order->get_meta('decision_clearsale', true);

        if ($existing_decision === '') {
            $logger->info("⚠️ El metadato 'decision_clearsale' no existía para la orden: $order_id. Se va a crear.", ['source' => 'ClearSales OpenPay']);
        } else {
            $logger->info("ℹ️ Ya existía un 'decision_clearsale' para la orden: $order_id. Valor previo: $existing_decision", ['source' => 'ClearSales OpenPay']);
        }

        $order->update_meta_data('decision_clearsale', $decision);

        if ($order->save()) {
            $logger->info("✅ Decisión ClearSale actualizada/creada: $decision", ['source' => 'ClearSales OpenPay']);
            try {
                $logger->info("Iniciando captura de pago para la orden #$order_id", ['source' => 'ClearSales OpenPay']);

                $openpay_cards = new Openpay_Cards();
                $openpay = $openpay_cards->getOpenpayInstance();

                $openpay_cards->init_settings();
                $settings = $openpay_cards->settings;

                $transaction_id = $order->get_meta('_transaction_id');
                if (!$transaction_id) {
                    throw new Exception('No se encontró el ID de transacción de OpenPay');
                }

                $customer_id = (isset($settings['sandbox']) && strcmp($settings['sandbox'], 'yes') === 0)
                    ? $order->get_meta('_openpay_customer_sandbox_id')
                    : $order->get_meta('_openpay_customer_id');
                if (!$customer_id) {
                    throw new Exception('No se encontró el ID de cliente de OpenPay');
                }

                $customer = $openpay->customers->get($customer_id);
                $charge = $customer->charges->get($transaction_id);

                if (!$procesarOpenpay) {
                    try {
                        // Para OpenPay, usamos refund con monto 0 para cancelar una preautorización
                        $refund = $charge->refund([
                            'description' => 'Cancelación de preautorización - ClearSales no autorizó el pago',
                            'amount' => floatval($order->get_total())
                        ]);

                        $order->update_status('cancelled', 'Preautorización cancelada - ClearSales no autorizó el pago');
                        $logger->info("Preautorización cancelada exitosamente para la orden #$order_id", [
                            'source' => 'ClearSales OpenPay',
                            'refund_id' => $refund->id ?? 'N/A'
                        ]);
                    } catch (Exception $e) {
                        $error_msg = 'Error al cancelar la preautorización: ' . $e->getMessage();
                        $order->add_order_note($error_msg);
                        $logger->error($error_msg, [
                            'source' => 'ClearSales OpenPay',
                            'error' => $e->getMessage()
                        ]);
                    }
                    throw new Exception('ClearSales no autorizó realizar el cargo a OpenPay - ' . $status);
                }

                $capture_result = $charge->capture(array(
                    'amount' => floatval($order->get_total())
                ));

                // Extraer datos relevantes del capture_result
                $datos_transaccion = [
                    'id_transaccion' => $capture_result->id,
                    'estado' => $capture_result->status,
                    'monto' => $capture_result->amount,
                    'moneda' => $capture_result->currency,
                    'fecha_operacion' => $capture_result->operation_date,
                    'fecha_creacion' => $capture_result->creation_date,
                    'autorizacion' => $capture_result->authorization,
                    'tipo_operacion' => $capture_result->operation_type,
                    'tipo_transaccion' => $capture_result->transaction_type,
                    'metodo_pago' => $capture_result->method,
                    'conciliado' => $capture_result->conciliated ? 'Sí' : 'No',
                    'descripcion' => $capture_result->description,
                    'order_id' => $capture_result->order_id,
                    'cliente' => [
                        'id' => $capture_result->customer_id,
                        'nombre' => $capture_result->parent->parent->parent->name ?? 'N/A',
                        'email' => $capture_result->parent->parent->parent->email ?? 'N/A',
                        'telefono' => $capture_result->parent->parent->parent->phone_number ?? 'N/A'
                    ],
                    'tarjeta' => [
                        'id' => $capture_result->card->id ?? null,
                        'tipo' => $capture_result->card->type ?? null,
                        'marca' => $capture_result->card->brand ?? null,
                        'ultimos_digitos' => isset($capture_result->card->card_number) ?
                            substr($capture_result->card->card_number, -4) : null,
                        'banco' => $capture_result->card->bank_name ?? null,
                        'codigo_banco' => $capture_result->card->bank_code ?? null
                    ]
                ];

                // Registrar los datos de la transacción en el log
                $logger->info("Datos de la transacción #$order_id", [
                    'source' => 'ClearSales OpenPay',
                    'transaccion' => $datos_transaccion
                ]);

                // Verificar que la captura fue exitosa
                if ($capture_result && $capture_result->status === 'completed') {
                    // Guardar los datos de la transacción en los metadatos de la orden
                    $order->update_meta_data('_openpay_transaction_data', $datos_transaccion);
                    $order->update_meta_data('_captured_total', $order->get_total());
                    $order->payment_complete();
                    $order->add_order_note('💳 Pago a través de Openpay');
                    $order->save();

                    $logger->info("Captura exitosa para la orden #$order_id", ['source' => 'ClearSales OpenPay']);

                    // Verificar si el plugin de Uber está activo y el envío es por Uber
                    if (class_exists('UD_Uber_Deliveries')) {
                        $logger->info("Iniciando envío a Uber para la orden #$order_id", ['source' => 'ClearSales OpenPay']);
                        $uber_response = UD_Uber_Deliveries::enviar_pedido_a_uber_y_notificar_cliente($order_id);

                        if ($uber_response === false) {
                            $logger->error("Error al enviar pedido a Uber para la orden #$order_id", ['source' => 'ClearSales OpenPay']);
                            $order->add_order_note('Error al enviar el pedido a Uber. Por favor, verifique manualmente.');
                        } else {
                            $logger->info("Pedido #$order_id enviado exitosamente a Uber", ['source' => 'ClearSales OpenPay']);
                            $order->add_order_note('Pedido enviado a Uber exitosamente.');
                        }
                        $order->save();
                    }

                    return [true, "Openpay: Captura de pago realizada exitosamente", $capture_result];
                } else {
                    throw new Exception('La captura no se completó correctamente');
                }
            } catch (Exception $e) {
                $error_message = "Openpay: " . $e->getMessage();
                $logger->error($error_message, ['source' => 'ClearSales OpenPay', 'exception' => $e]);

                $order->add_order_note($error_message);
                $order->save();

                return [false, $error_message];
            }
        } else {
            $logger->warning("❌ No se pudo guardar el metadato 'decision_clearsale' para la orden: $order_id", ['source' => 'ClearSales OpenPay']);
            return [false, "No se pudo procesar clearsales"];
        }
    }
    private function debug_object($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        $reflection = new ReflectionObject($object);
        $properties = $reflection->getProperties();
        $result = [];

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($object);

            // Evitar recursión infinita con objetos anidados
            if (is_object($value)) {
                $result[$property->getName()] = get_class($value) . ' Object';
            } else {
                $result[$property->getName()] = $value;
            }
        }

        return $result;
    }
}
