<?php
/**
 * Carnemart :: Envío a CRM (réplicas)
 *
 * - Mantiene la lógica original.
 * - Añade WC_Logger con contexto consistente.
 * - Sanea entradas GET.
 * - Corrige la validación de InternalID cuando `obtener_personas_implicadas()` retorna arreglo de personas.
 * - Docblocks + pequeños “guard clauses”.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** ============================== */
/** Constantes y helpers de logger */
/** ============================== */

if (!defined('CMR_REPL_LOG_SOURCE')) {
    define('CMR_REPL_LOG_SOURCE', 'carnemart-replicas');
}

/**
 * Devuelve el logger de WooCommerce.
 *
 * @return WC_Logger
 */
function cmr_logger()
{
    return function_exists('wc_get_logger') ? wc_get_logger() : new WC_Logger();
}

/** ================== */
/** Hooks de ejecución */
/** ================== */

/**
 * Hook principal: al pasar a "processing" enviamos pedido a CRM.
 */
add_action('woocommerce_order_status_processing', 'carnemart_send_order', 10, 1);

/**
 * Endpoint manual: ?idorden=123 (útil para debug o reproceso).
 */
add_action('wp_loaded', 'cmr_maybe_trigger_send_order');
function cmr_maybe_trigger_send_order()
{
    if (isset($_GET['idorden'])) {
        $id = absint($_GET['idorden']);
        if ($id > 0 && function_exists('carnemart_send_order')) {
            carnemart_send_order($id);
        }
    }
}

/** ========================= */
/** Utilidades de depuración  */
/** ========================= */

/**
 * Tabla comparativa de líneas vs meta_total (herramienta de validación).
 * Mantiene tu salida HTML y cálculo con umbral 5%.
 */
function obtener_tabla_detalle_pedido_2($order_id, $meta_total)
{
    $meta_total = floatval(str_replace(',', '', $meta_total));

    $order = wc_get_order($order_id);
    if (!$order) {
        echo 'Pedido no encontrado.';
        return;
    }

    $lineas = [];
    foreach ($order->get_items() as $item) {
        $q = (float) $item->get_quantity();
        if ($q <= 0) {
            continue;
        }

        $sub = (float) $item->get_subtotal();
        $tot = (float) $item->get_total();

        $psd = round($sub / $q, 2);
        $pcd = round($tot / $q, 2);
        $du = round($psd - $pcd, 2);

        $lineas[] = [
            'quantity' => $q,
            'precio_sin_descuento' => $psd,
            'discount_unitario' => $du,
            'subTotal' => 0,
            'total_discount' => 0,
            'total' => 0,
        ];
    }

    $total_real = 0.0;
    foreach ($lineas as &$l) {
        $l['subTotal'] = round($l['precio_sin_descuento'] * $l['quantity'], 2);
        $l['total_discount'] = round($l['discount_unitario'] * $l['quantity'], 2);
        $l['total'] = round($l['subTotal'] - $l['total_discount'], 2);
        $total_real += $l['total'];
    }
    unset($l);

    $diff = round($meta_total - $total_real, 2);
    $umbral = round($total_real * 0.05, 2); // 5%

    if (abs($diff) <= $umbral && count($lineas) > 0) {
        $pesos = array_map(
            static function ($l) {
                return abs($l['quantity'] * $l['discount_unitario']);
            },
            $lineas
        );
        $idx = array_keys($pesos, max($pesos))[0];

        $l = &$lineas[$idx];
        $ajuste_unitario = round($diff / $l['quantity'], 2);
        $l['discount_unitario'] = max(0, round($l['discount_unitario'] + $ajuste_unitario, 2));

        $l['total_discount'] = round($l['discount_unitario'] * $l['quantity'], 2);
        $l['total'] = round($l['subTotal'] - $l['total_discount'], 2);
        unset($l);
    } else {
        echo "<p style='color:orange;'>
				No se ajustó: diferencia de {$diff} supera el umbral de {$umbral}.
			  </p>";
    }

    $sum_sub = 0;
    $sum_disc = 0;
    $sum_tot = 0;

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr style='background:#f0f0f0;'>
			<th>quantity</th><th>price</th><th>subTotal</th>
			<th>discount</th><th>precio sin descuento</th>
			<th>total_discount</th><th>total</th>
		  </tr>";

    foreach ($lineas as $l) {
        $sum_sub += $l['subTotal'];
        $sum_disc += $l['total_discount'];
        $sum_tot += $l['total'];
        $price = round($l['precio_sin_descuento'] - $l['discount_unitario'], 2);

        echo '<tr>';
        echo '<td>' . number_format($l['quantity'], 2) . '</td>';
        echo '<td>' . number_format($price, 2) . '</td>';
        echo '<td>' . number_format($l['subTotal'], 2) . '</td>';
        echo '<td>' . number_format($l['discount_unitario'], 2) . '</td>';
        echo '<td>' . number_format($l['precio_sin_descuento'], 2) . '</td>';
        echo '<td>' . number_format($l['total_discount'], 2) . '</td>';
        echo '<td>' . number_format($l['total'], 2) . '</td>';
        echo '</tr>';
    }

    echo "<tr style='font-weight:bold; background:#e0f0ff;'>
			<td colspan='2'></td>
			<td>" . number_format($sum_sub, 2) . '</td>
			<td></td><td></td>
			<td>' . number_format($sum_disc, 2) . "</td>
			<td style='background:#ffff99;'>" . number_format($sum_tot, 2) . '</td>
		  </tr>';
    echo '</table>';
}

/** ========================= */
/** Matemáticas y utilidades  */
/** ========================= */

/**
 * Por compatibilidad con tu lógica actual: no trunca; castea a float.
 */
function truncar_dos_decimales($valor)
{
    return (float) $valor;
}

/** Retorno sin cambios (placeholder de redondeo). */
function redondear($valor)
{
    return $valor;
}

/** Total de cupones del pedido. */
function obtener_total_cupones($order)
{
    return truncar_dos_decimales($order->get_discount_total());
}

/**
 * Redención (descuento que NO proviene de cupones).
 * Redención = (descuento_total) - (cupones)
 */
function obtener_total_redencion($order)
{
    $subtotal = truncar_dos_decimales($order->get_subtotal());
    $total = truncar_dos_decimales($order->get_total());
    $shipping = truncar_dos_decimales($order->get_shipping_total());
    $cupones = obtener_total_cupones($order);

    $descuento_total = truncar_dos_decimales($subtotal - ($total - $shipping));
    $redencion = truncar_dos_decimales($descuento_total - $cupones);

    return $redencion;
}

/**
 * Desglose del descuento: total, cupones y redención (normalizado).
 */
function obtener_descuento_desglosado($order)
{
    $subtotal = truncar_dos_decimales($order->get_subtotal());
    $total = truncar_dos_decimales($order->get_total());
    $shipping = truncar_dos_decimales($order->get_shipping_total());
    $cupones = truncar_dos_decimales($order->get_discount_total());

    $descuento_total = truncar_dos_decimales($subtotal - ($total - $shipping));

    if (abs($descuento_total) < 0.01) {
        $descuento_total = 0.00;
    }
    if (abs($cupones) < 0.01) {
        $cupones = 0.00;
    }

    $redencion = 0.00;
    if ($cupones > 0 && $descuento_total > 0) {
        $redencion = truncar_dos_decimales($descuento_total - $cupones);
        if (abs($redencion) < 0.01) {
            $redencion = 0.00;
        }
    }

    return [
        'total_descuento' => $descuento_total,
        'cupones' => $cupones,
        'redencion' => $redencion,
    ];
}

/**
 * Puntos autorizados según % de descuento (solo si hay cupón).
 */
function obtener_puntosAutorizados($descuento)
{
    $descuento = number_format((float) $descuento, 2);

    $map = [
        '0.20' => 1000,
        '0.15' => 750,
        '0.10' => 500,
        '0.05' => 150,
    ];

    return $map[$descuento] ?? 0.00;
}

/** Porcentaje decimal de cupones vs subtotal (ej. 0.05). */
function obtener_porcentaje_cupones($order)
{
    $subtotal = (float) $order->get_subtotal();
    $cupones = (float) $order->get_discount_total();

    if ($subtotal <= 0 || $cupones <= 0) {
        return 0.0;
    }

    return $cupones / $subtotal;
}

/** Costo de envío del pedido. */
function obtener_costo_envio($order)
{
    return truncar_dos_decimales($order->get_shipping_total());
}

/** =========================== */
/** Envío de pedido a CRM (SOAP)*/
/** =========================== */

/**
 * Encola el envío a CRM; mantiene tu lógica y añade logs con contexto.
 *
 * @param int $order_id
 */
function carnemart_send_order($order_id)
{
    $logger = cmr_logger();
    $ctx = [
        'source' => CMR_REPL_LOG_SOURCE,
        'fn' => __FUNCTION__,
        'order_id' => (int) $order_id,
    ];

    $logger->info('Inicio de integración CRM: preparando pedido.', $ctx);

    $order = wc_get_order($order_id);
    if (!$order) {
        $logger->error('No se pudo obtener la orden.', $ctx);
        return;
    }

    list($productos, $total_descuento, $amt) = obtener_productos($order);
    $pedidoData = obtener_pedido_data($order, $total_descuento, $amt);
    $personasImplicadas = obtener_personas_implicadas($order);

    // InternalID a efectos de validación: tomamos el primero si viene como lista.
    $first_internal_id = null;
    if (is_array($personasImplicadas)) {
        $first = reset($personasImplicadas);
        if (is_array($first) && isset($first['InternalID'])) {
            $first_internal_id = (string) $first['InternalID'];
        }
    }

    $logger->debug(
        'Payload preparado.',
        $ctx + [
            'items_count' => is_array($productos) ? count($productos) : null,
            'internal_id' => $first_internal_id,
            'personas_implicadas' => $personasImplicadas,
        ]
    );

    // Si el primer InternalID es '888888' -> retener pedido (misma intención que tu código)
    if ($first_internal_id === '888888') {
        $logger->warning('Pedido: InternalID 888888 (usuario inválido).', $ctx);

        $order->add_order_note('CRM Error: Usuario inválido (InternalID 888888) | Se procesará de todas maneras.', false);
        //$order->update_status('on-hold', 'Pedido retenido por error CRM | ID');
        //$order->save();

        //$logger->info('Pedido puesto en hold y guardado.', $ctx);
        //return;
    }

    // Depuración manual con ?k=1
    if (!empty($_GET['k'])) {
        $logger->notice('Salida de depuración vía ?k=1 solicitada.', $ctx);
        echo '<pre>';
        print_r($pedidoData);
        echo '</pre><br>';
        echo '<pre>';
        print_r($productos);
        echo '</pre>';
        die();
    }

    $cupones = [];
    $wsdl_url = _WSDL_CARNEMART_REPLICA_PEDIDO;
    $soapClient = new CarnemartSoap($wsdl_url);

    try {
        $logger->info(
            'Llamando a CarnemartSoap->enviarPedido()',
            $ctx + [
                'wsdl' => (string) $wsdl_url,
                'productos_count' => is_array($productos) ? count($productos) : null,
            ]
        );

        if (isset($_GET['idorden'])) {
            // Modo debug (mantengo tu comportamiento)
            $logger->notice('Modo debug (?idorden) activado: se omitió el envío/clear cart.', $ctx);
        } else {
            $response = $soapClient->enviarPedido($pedidoData, $personasImplicadas, $productos, $cupones);

            $logger->info(
                'Respuesta recibida de CRM.',
                $ctx + ['response_type' => is_object($response) ? 'object' : (is_array($response) ? 'array' : gettype($response))]
            );

            if (function_exists('WC') && WC()->cart) {
                WC()->cart->empty_cart();
                $logger->info('Carrito vaciado tras envío a CRM.', $ctx);
            }
        }
    } catch (Throwable $th) {
        $logger->error(
            'Excepción al enviar pedido a CRM.',
            $ctx + [
                'error_message' => $th->getMessage(),
                'error_code' => $th->getCode(),
                'trace' => $th->getTraceAsString(),
            ]
        );
        error_log('Error en API Replicas ' . $th->getMessage());
    }
}

/** ======================== */
/** Construcción de payloads */
/** ======================== */

/**
 * Arma la cabecera (pedidoData) para CRM.
 *
 * @param WC_Order $order
 * @param float    $total_descuento
 * @param float    $porcentaje_cupon  (decimal, ej. 0.05)
 * @return array
 */
function obtener_pedido_data($order, $total_descuento, $porcentaje_cupon)
{
    $porcentaje_cupones = obtener_porcentaje_cupones($order);

    $order_id = $order->get_id();
    $created_at = $order->get_date_created();
    $subtotal = truncar_dos_decimales($order->get_subtotal());
    $total = truncar_dos_decimales($order->get_total());
    $envio = truncar_dos_decimales($order->get_shipping_total());
    $total_tax = truncar_dos_decimales($order->get_total_tax());
    $currency = $order->get_currency();
    $payment_method = $order->get_payment_method();

    // Metas
    $pickup_time = get_post_meta($order_id, 'lp_pickup_time', true);
    $pickup_date = get_post_meta($order_id, 'lp_pickup_date', true);
    $metodo_entrega = get_post_meta($order_id, 'metodo_entrega', true);
    $metodo_pago = get_post_meta($order_id, 'metodo_pago', true);
    $metodo_entrega = !empty($metodo_entrega) ? $metodo_entrega : '01';
    $metodo_pago = !empty($metodo_pago) ? $metodo_pago : '01';
    $centro = get_post_meta($order_id, 'centro', true);
    $rolperpim = get_post_meta($order_id, 'Rolperpim', true) ?: 1001;
    $idPromCabecera = get_post_meta($order_id, 'idPromocionCabecera', true);

    $totalNeto = $total - $total_tax;
    $PorcDescDinero = $porcentaje_cupon;

    $puntosAutorizados = obtener_puntosAutorizados($PorcDescDinero);
    $DineroDesc = number_format((($totalNeto * 100) / (100 - ($PorcDescDinero * 100))) - $totalNeto, 2);

    $descuentos = obtener_descuento_desglosado($order);

    // Horarios
    $pickupTimeInterval = explode('-', (string) $pickup_time);
    $start_time = isset($pickupTimeInterval[0]) ? trim($pickupTimeInterval[0]) : '';
    $end_time = isset($pickupTimeInterval[1]) ? trim($pickupTimeInterval[1]) : '';

    $end_time_updated = '';
    if ($end_time) {
        $dt = DateTime::createFromFormat('g:i A', $end_time);
        if ($dt instanceof DateTime) {
            $dt->modify('+2 hours');
            $end_time_updated = $dt->format('H:i');
        }
    }

    // Mantengo tu timezone explícito para los campos por defecto
    date_default_timezone_set('America/Mexico_City');

    return [
        'ID' => $order_id,
        'BuyerID' => $order_id,
        'FechaHoraCreacion' => $created_at ? $created_at->format('Y-m-d H:i:s') : current_time('mysql'),
        'SalesOrganisationID' => '3200',
        'DistributionChannelCode' => '10',
        'InternalID' => $centro,
        'CurrencyCode' => $currency,
        'PriceDate' => $created_at ? $created_at->format('Y-m-d') : current_time('Y-m-d'),
        'MetodoPago' => $metodo_pago,
        'MetodoEntrega' => '01',
        'CanalVenta' => '01',
        'FechaCobro' => $created_at ? $created_at->format('Y-m-d') : current_time('Y-m-d'),
        'RequestedDate' => $pickup_date,
        'HorarioEntregaI' => $start_time ?: date('H:i'),
        'HorarioEntregaF' => $end_time_updated ?: date('H:i', strtotime('+2 hours')),
        'IdPromocionH' => $idPromCabecera,
        'idPromocionCabecera' => $idPromCabecera,
        'IdIDBBYH' => $idPromCabecera,
        'IdStatus' => '010',
        'PuntosAcumulados' => '',
        'PuntosAutorizados' => $puntosAutorizados,
        'TotalBruto' => $subtotal + $envio,
        'TotalDescuento' => $descuentos['total_descuento'] + $total_descuento,
        'TotalImpuestos' => $total_tax,
        'TotalNeto' => $totalNeto,
        'CostoEnvio' => $envio,
        'DineroDesc' => $DineroDesc,
        'PorcDescDinero' => $PorcDescDinero,
        'TotalPagar' => $total,
        'Rolperpim' => $rolperpim,
        'ReferenciaExterna' => $order_id,
    ];
}

/**
 * Arma la estructura de personas (Billing/Customer).
 * Mantiene tu salida como lista de personas.
 */
function obtener_personas_implicadas($order)
{
    $personasImplicadas = [];

    $customer_id = $order->get_customer_id();

    if ($customer_id) {
        $first_name = get_user_meta($customer_id, 'billing_first_name', true);
        $last_name = get_user_meta($customer_id, 'billing_last_name', true);
        $full_name = trim($first_name . ' ' . $last_name);

        $crm = get_user_meta($customer_id, 'customer_crm', true);
        $IdBP = $crm ? $crm : '';

        if (empty($IdBP)) {
            $order_id = $order->get_id();
            $IdBP = ejecutar_api_en_primer_pedido($order_id);
        }

        $customer = new WC_Customer($customer_id);

        $personasImplicadas[] = [
            'IdBP' => $IdBP,
            'RazonSocial' => $full_name,
            'RoleCode' => 1001,
            'InternalID' => $IdBP,
            'FirstNameLine' => $customer->get_first_name(),
            'RFC' => '',
            'OrderReasonCode' => '',
            'CountryCode' => $customer->get_billing_country(),
            'RegionCode' => $customer->get_billing_state(),
            'StreetPostalCode' => $customer->get_billing_postcode(),
            'StreetName' => $customer->get_billing_address_1(),
            'HouseID' => '',
            'NumeroInterior' => '',
            'Colonia' => $customer->get_billing_address_2(),
            'Municipio' => $customer->get_billing_city(),
            'Telefono' => $customer->get_billing_phone(),
            'TelefonoMovil' => '',
            'Email' => $customer->get_email(),
        ];
    } else {
        $first_name = $order->get_billing_first_name();
        $last_name = $order->get_billing_last_name();
        $full_name = trim($first_name . ' ' . $last_name);

        $personasImplicadas[] = [
            'IdBP' => '',
            'RazonSocial' => $full_name,
            'RoleCode' => 1001,
            'InternalID' => '',
            'FirstNameLine' => $first_name,
            'RFC' => '',
            'OrderReasonCode' => '',
            'CountryCode' => $order->get_billing_country(),
            'RegionCode' => $order->get_billing_state(),
            'StreetPostalCode' => $order->get_billing_postcode(),
            'StreetName' => $order->get_billing_address_1(),
            'HouseID' => '',
            'NumeroInterior' => '',
            'Colonia' => $order->get_billing_address_2(),
            'Municipio' => $order->get_billing_city(),
            'Telefono' => $order->get_billing_phone(),
            'TelefonoMovil' => '',
            'Email' => $order->get_billing_email(),
        ];
    }

    return $personasImplicadas;
}

/** =========================== */
/** Vistas de ayuda (debug UI)  */
/** =========================== */

function obtener_tabla_detalle_pedido($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo '<tr>
			<th>quantity</th>
			<th>price</th>
			<th>subTotal</th>
			<th>discount</th>
			<th>precio sin descuento</th>
			<th>total_discount</th>
			<th>total</th>
		  </tr>';

    $subtotal_general = 0;
    $total_discount_general = 0;
    $total_final = 0;

    foreach ($order->get_items() as $item) {
        $quantity = $item->get_quantity();
        $price_with_discount = $item->get_total() / max(1, $quantity);
        $discount_unitario = ($item->get_subtotal() / max(1, $quantity)) - $price_with_discount;
        $precio_sin_descuento_unit = $item->get_subtotal() / max(1, $quantity);

        $subTotal = ($quantity * $price_with_discount) + ($quantity * $discount_unitario);
        $total_discount = $quantity * $discount_unitario;
        $total = $subTotal - $total_discount;

        $subtotal_general += $subTotal;
        $total_discount_general += $total_discount;
        $total_final += $total;

        echo '<tr>';
        echo '<td>' . number_format($quantity, 2) . '</td>';
        echo '<td>' . number_format($price_with_discount, 2) . '</td>';
        echo '<td>' . number_format($subTotal, 2) . '</td>';
        echo '<td>' . number_format($discount_unitario, 2) . '</td>';
        echo '<td>' . number_format($precio_sin_descuento_unit, 2) . '</td>';
        echo '<td>' . number_format($total_discount, 2) . '</td>';
        echo '<td>' . number_format($total, 2) . '</td>';
        echo '</tr>';
    }

    echo "<tr style='font-weight:bold;background:#e6f0ff;'>
			<td colspan='2'></td>
			<td>" . number_format($subtotal_general, 2) . '</td>
			<td></td><td></td>
			<td>' . number_format($total_discount_general, 2) . "</td>
			<td style='background:#ffff99;'>" . number_format($total_final, 2) . '</td>
		  </tr>';

    echo '</table>';
}

/** ========================= */
/** Detalle de productos (líneas)
 /** ========================= */

/**
 * Devuelve estructura de líneas para el CRM + totales de descuento + % cupón.
 *
 * @param WC_Order $order
 * @return array [ $productos, $total_descuento, $cup_decimal ]
 */
function obtener_productos($order)
{
    global $wpdb;

    $productos = [];
    $items = $order->get_items();
    $order_id = $order->get_id();
    $envio = $order->get_shipping_total();
    $location_id = (string) get_post_meta($order_id, 'location_id', true);

    $customer_group = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value 
			   FROM {$wpdb->prefix}termmeta 
			  WHERE term_id = %d 
			    AND meta_key = 'customer_group'",
            $location_id
        )
    );

    // Lee los cupones y toma la última definición (se mantiene tu enfoque)
    $amt = 0.0;
    $type = '';
    foreach ($order->get_items('coupon') as $ci) {
        $coupon = new WC_Coupon($ci->get_code());
        $amt = (float) $coupon->get_amount();
        $type = (string) $coupon->get_discount_type();
    }

    $isCupon = ($amt > 0 && 'percent' === $type);

    $total_descuento = 0.0;
    $counter = 10;

    foreach ($items as $item_id => $item) {
        $product = $item->get_product();
        if (!$product) {
            continue;
        }
        $product_id = $product->get_id();
        $cantidad = (float) $item->get_quantity();
        $is_entera = floor($cantidad) == $cantidad;
        $location_key = 'wcmlim_regular_price_at_' . $location_id;
        $escalado_key = 'eib2bpro_price_tiers_group_' . $customer_group;

        $sku = $product->get_sku();
        $step_label = get_post_meta($product_id, 'ri_quantity_step_label', true) ?: '';

        $data = $is_entera
            ? processIntegerQuantity($item, $cantidad, $location_key, $escalado_key, $isCupon)
            : processDecimalQuantity($item, $cantidad, $location_key, $escalado_key, $isCupon);

        $productos[] = [
            'IDITem' => $counter,
            'InternalID' => $sku,
            'RequestQuantity' => $data['quantity'],
            'QuantityUnitCode' => $step_label,
            'PrecioUnitario' => round($data['unit_only'], 2, PHP_ROUND_HALF_UP),
            'Descuentonitario' => round($data['unit_discount'], 2, PHP_ROUND_HALF_UP),
            'PrecioLinea' => round($data['line_price'], 2, PHP_ROUND_HALF_UP),
            'DescuentoLinea' => round($data['line_discount'], 2, PHP_ROUND_HALF_UP),
            'TotalLinea' => round($data['line_total'], 2, PHP_ROUND_HALF_UP),
            'IdDBBYI' => '',
            'IdPromocionI' => '',
            'IdCuponPI' => '',
            'IdProcesoI' => '',
            'TextContent' => $data['name'],
            'UnidadMedida' => $step_label,
        ];

        $total_descuento += $data['line_discount'];
        $counter += 10;
    }

    // Línea de envío (si aplica)
    $costo_envio = obtener_costo_envio($order);
    if ($costo_envio > 0) {
        $productos[] = [
            'IDITem' => $counter,
            'InternalID' => '897',
            'RequestQuantity' => 1,
            'QuantityUnitCode' => 'Pza.',
            'PrecioUnitario' => $costo_envio,
            'Descuentonitario' => 0,
            'PrecioLinea' => $costo_envio,
            'DescuentoLinea' => 0,
            'TotalLinea' => $costo_envio,
            'IdDBBYI' => '',
            'IdPromocionI' => '',
            'IdCuponPI' => '',
            'IdProcesoI' => '',
            'TextContent' => 'Envío a domicilio',
            'UnidadMedida' => 'Pza.',
        ];
    }

    // Retorna % cupón como decimal (si hubo percent), igual que tu implementación
    return [$productos, $total_descuento, ($amt > 0 ? $amt / 100 : 0)];
}

/** =============================== */
/** Helpers de cálculo de escalados */
/** =============================== */

/**
 * Cantidad ENTERA: calcula precios/desc. (misma lógica, formateada)
 */
function processIntegerQuantity($item, $quantity, $location_key, $escalado_key, $isCupon): array
{
    global $wpdb;

    $product = $item->get_product();
    $id = $product->get_id();

    // 1) Precio base tienda (fallback)
    $precio_base_tienda = (float) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value 
			   FROM {$wpdb->prefix}postmeta 
			  WHERE post_id = %d 
			    AND meta_key = %s",
            $id,
            $location_key
        )
    );

    // 2) JSON de escalado
    $json = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value 
			   FROM {$wpdb->prefix}postmeta 
			  WHERE post_id = %d 
			    AND meta_key = %s",
            $id,
            $escalado_key
        )
    );
    $escalado = json_decode($json, true);

    // 3) Precios limpios
    $clean = cleanEscalationPrices(is_array($escalado) ? $escalado : []);

    // 4) Precio escalado con fallback
    $unit_price = calculateEscalatedPrice($clean, $quantity, $precio_base_tienda);

    // 5) Regular escalado para detectar oferta
    $regular_escalado = firstEscalatedPrice(is_array($escalado) ? $escalado : [], $quantity);
    $existOferta = $regular_escalado > $unit_price;

    // 6) Nombre limpio
    $name = preg_replace('/[^a-zA-Z0-9\s]/u', '', $item->get_name());

    // 7) Cálculos
    $unit_only = $item->get_total() / max(1, $quantity);
    $unit_discount = $existOferta ? ($regular_escalado - $unit_price) : 0.0;
    $unit_discount = $isCupon ? $unit_discount + ($unit_price - $unit_only) : $unit_discount;

    $price = $isCupon ? $unit_only : redondear($unit_price + $unit_discount);
    $line_price = $isCupon ? (($price + $unit_discount) * $quantity) : ($quantity * $price);
    $line_discount = $unit_discount * $quantity;
    $line_total = $line_price - $line_discount;

    return compact('quantity', 'unit_only', 'unit_price', 'unit_discount', 'price', 'line_price', 'line_discount', 'line_total', 'name');
}

/**
 * Cantidad DECIMAL: idem a enteras, con fallback a precio base/regular.
 */
function processDecimalQuantity($item, $quantity, $location_key, $escalado_key, $isCupon): array
{
    global $wpdb;

    $product = $item->get_product();
    $id = $product->get_id();

    $meta_base = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value 
			   FROM {$wpdb->prefix}postmeta 
			  WHERE post_id = %d 
			    AND meta_key = %s",
            $id,
            $location_key
        )
    );
    $precio_base = ((float) $meta_base > 0) ? (float) $meta_base : (float) $product->get_regular_price();

    $json = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value 
			   FROM {$wpdb->prefix}postmeta 
			  WHERE post_id = %d 
			    AND meta_key = %s",
            $id,
            $escalado_key
        )
    );
    $escalado = json_decode($json, true);

    $clean = cleanEscalationPrices(is_array($escalado) ? $escalado : []);
    $unit_price = calculateEscalatedPrice($clean, $quantity, $precio_base);

    $regular_escalado = firstEscalatedPrice(is_array($escalado) ? $escalado : [], $quantity);
    $existOferta = ($regular_escalado > $unit_price);

    $name = preg_replace('/[^a-zA-Z0-9\s]/u', '', $item->get_name());

    $unit_only = $existOferta ? ($item->get_total() / max(1, $quantity)) : $unit_price;
    $unit_discount = $existOferta ? ($regular_escalado - $unit_price) : 0.0;
    $unit_discount = $isCupon ? $unit_discount + ($unit_price - $unit_only) : $unit_discount;

    $price = $isCupon ? $unit_only : redondear($unit_price + $unit_discount);
    $line_price = $isCupon ? (($price + $unit_discount) * $quantity) : ($quantity * $price);
    $line_discount = $unit_discount * $quantity;
    $line_total = $line_price - $line_discount;

    return compact('quantity', 'unit_only', 'unit_price', 'unit_discount', 'price', 'line_price', 'line_discount', 'line_total', 'name');
}

/**
 * Calcula precio escalado o retorna fallback si no aplica ningún escalón.
 *
 * @param array $clean     [min_qty => price, ...] (asc)
 * @param float $cantidad
 * @param float $fallback
 * @return float
 */
function calculateEscalatedPrice(array $clean, $cantidad, $fallback)
{
    $price = 0.0;
    foreach ($clean as $min_qty => $val) {
        if ($cantidad >= $min_qty) {
            $price = (float) $val;
        }
    }
    return $price > 0 ? $price : (float) $fallback;
}

/**
 * Primer “precio regular escalado” (entre paréntesis) para detectar oferta.
 */
function firstEscalatedPrice(array $escalado_array, $cantidad)
{
    if (!is_array($escalado_array)) {
        return 0.0;
    }
    foreach ($escalado_array as $key => $_val) {
        $raw = strtok((string) $key, ' ');
        if (is_numeric($raw) && $cantidad >= (float) $raw) {
            if (preg_match('/\(([\d.]+)\)/', (string) $key, $m)) {
                return (float) $m[1];
            }
        }
    }
    return 0.0;
}

/**
 * Limpia el JSON de escalados -> [int => float] ordenado.
 */
function cleanEscalationPrices(array $escalado_array): array
{
    $result = [];
    foreach ($escalado_array as $key => $value) {
        $raw = strtok((string) $key, ' ');
        if (is_numeric($raw)) {
            $result[(int) $raw] = (float) $value;
        }
    }
    ksort($result);
    return $result;
}