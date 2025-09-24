<?php

/**
 * Plugin Name: Bafar :: Multiservicios Bafar 🪓
 * Plugin URI: https://sparklabs.com.mx
 * Description: Plugin para añadir funcionalidades personalizadas para el sitio de carnemart.com de Multiservicios Bafar a WooCommerce.
 * Version: 1.0.0
 * Author: Sergio @ Sparklabs
 * Author URI: https://sparklabs.com.mx
 * Text Domain: multiservicios-bafar
 * Domain Path: /languages
 * License: GPLv2 or later
 */


// Asegurarse de que no se acceda directamente al archivo
if (!defined("ABSPATH")) {
    exit(); // Salir si se accede directamente.
}


wp_enqueue_script('script-personalizado', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);
/*
add_action('woocommerce_review_order_before_payment', 'mostrar_metodos_envio_checkout');
function mostrar_metodos_envio_checkout() {
    if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
       // echo '<h3>Métodos de Envío</h3>';
      //  wc_cart_totals_shipping_html();
    }
}*/


// Verificar si WooCommerce está activo
if (
    in_array(
        "woocommerce/woocommerce.php",
        apply_filters("active_plugins", get_option("active_plugins"))
    )
) {
    // Función para añadir una nueva funcionalidad en la página de producto
    function bafar_custom_product_functionality()
    {
        // Esta función podría personalizar la página de producto.
        // echo '<p>Este es un mensaje de prueba del plugin Multiservicios Bafar.</p>';
    }
    add_action("woocommerce_single_product_summary", "bafar_custom_product_functionality", 20);

    // Función para añadir un enlace a los ajustes de WooCommerce en la página de plugins
    function bafar_add_settings_link($links)
    {
        // Se añade un enlace a los ajustes de WooCommerce desde la lista de plugins.
        // $settings_link = '<a href="admin.php?page=wc-settings">Configuraciones</a>';
        // array_push($links, $settings_link);
        // return $links;
    }
    // add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'bafar_add_settings_link');

    // Función para vaciar el carrito después de realizar una compra
    function vaciar_carrito_despues_de_orden($order_id)
    {
        if (is_order_received_page()) {
            // WC()->cart->empty_cart(); // Vaciar el carrito al recibir la orden.
        }
    }
    // add_action('woocommerce_thankyou', 'vaciar_carrito_despues_de_orden');

    /***
     * YALO pedido
     */


    add_action('rest_api_init', function () {
        register_rest_route('custom/v1', '/create-order/', array(
            'methods' => 'POST',
            'callback' => 'custom_create_order',
            'permission_callback' => function () {
                return current_user_can('manage_woocommerce');
            }
        ));
    });

    /***
YALO API
     **/
    function custom_create_order($request)
    {

        // Registrar el payload utilizando WC_Logger
        $logger = new WC_Logger();
        $log_context = array('source' => 'API Yalo');
        $payload = json_encode($request->get_json_params());
        $logger->info('Yalo Payload: ' . $payload, $log_context);
        $params = $request->get_json_params();

        // Validación mínima de datos requeridos
        if (!isset($params['billing']['email']) || !isset($params['line_items']) || empty($params['line_items'])) {
            $logger->error('Datos faltantes o ningún producto enviado.', $log_context);
            return new WP_Error('missing_data', 'Faltan campos obligatorios o no se ha enviado ningún producto.', array('status' => 400));
        }

        // Obtener el correo electrónico del cliente
        $billing = $params['billing'];
        $email = sanitize_email($billing['email']);
        $customer_crm = isset($params['customer_crm']) ? sanitize_text_field($params['customer_crm']) : null;
        $puntos_reservados = isset($params['puntos_reservados']) ? sanitize_text_field($params['puntos_reservados']) : null;
        $origen = isset($params['origen']) ? sanitize_text_field($params['origen']) : 'Yalo'; // Establecer 'Yalo' como valor por defecto
        $transaction_id = isset($params['transaction_id']) ? sanitize_text_field($params['transaction_id']) : 'sin transacción'; // Establecer 'Yalo' como valor por defecto
        $amount = isset($params['metadata']['customFields']['conektaPayload']['amount']) ? $params['metadata']['customFields']['conektaPayload']['amount'] : null;
        // Nuevos campos
        $metodo_entrega = isset($params['metodo_entrega']) ? sanitize_text_field($params['metodo_entrega']) : null;
        $idPromocionCabecera = isset($params['idPromocionCabecera']) ? sanitize_text_field($params['idPromocionCabecera']) : null;
        $Rolperpim = isset($params['Rolperpim']) ? sanitize_text_field($params['Rolperpim']) : null;

        // Nuevos metadatos
        $centro = isset($params['centro']) ? sanitize_text_field($params['centro']) : null;
        $pickup_date = isset($params['pickup_date']) ? sanitize_text_field($params['pickup_date']) : null;
        $pickup_time = isset($params['pickup_time']) ? sanitize_text_field($params['pickup_time']) : null;
        $pickup_comments = isset($params['pickup_comments']) ? sanitize_text_field($params['pickup_comments']) : null;

        $logger->info('Iniciando proceso de creación de pedido para el cliente: ' . $email, $log_context);

        // Verificar si el cliente ya existe por correo electrónico
        $user = get_user_by('email', $email);

        if ($user) {
            // Cliente existe, usar su ID
            $customer_id = $user->ID;
            $logger->info('Cliente existente encontrado. ID del cliente: ' . $customer_id, $log_context);

            // Verificar si el campo 'customer_crm' existe
            $existing_customer_crm = get_user_meta($customer_id, 'customer_crm', true);

            if (empty($existing_customer_crm)) {
                // Si no existe, agregar el campo 'customer_crm'
                update_user_meta($customer_id, 'customer_crm', sanitize_text_field($customer_crm));
                $logger->info('Campo customer_crm agregado al cliente existente.', $log_context);
            } else {
                // Si existe, puedes actualizarlo si es necesario
                $logger->info('Campo customer_crm ya existe: ' . $existing_customer_crm, $log_context);
            }
        } else {

            // El cliente no existe, así que lo creamos con wp_insert_user
            $username = explode('@', $email)[0];
            // Capitalizar el primer nombre y el apellido
            $first_name = !empty($billing['first_name']) ? ucwords(strtolower(sanitize_text_field($billing['first_name']))) : 'Cliente';
            $last_name = !empty($billing['last_name']) ? ucwords(strtolower(sanitize_text_field($billing['last_name']))) : 'Anonimo';

            // Definir los datos del nuevo cliente
            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => wp_generate_password(),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => 'customer', // Asignar el rol 'customer' para WooCommerce
            );

            // Insertar el usuario
            $customer_id = wp_insert_user($userdata);

            if (is_wp_error($customer_id)) {
                $logger->error('Error al crear el cliente: ' . $customer_id->get_error_message(), $log_context);
                return new WP_Error('customer_creation_failed', 'No se pudo crear el cliente', array('status' => 500));
            }

            $logger->info('Cliente creado con éxito. ID del cliente: ' . $customer_id, $log_context);

            // Actualizar los datos de facturación y envío del cliente
            update_user_meta($customer_id, 'billing_address_1', sanitize_text_field($billing['address_1']));
            update_user_meta($customer_id, 'billing_address_2', sanitize_text_field($billing['address_2']));
            update_user_meta($customer_id, 'billing_city', sanitize_text_field($billing['city']));
            update_user_meta($customer_id, 'billing_state', sanitize_text_field($billing['state']));
            update_user_meta($customer_id, 'billing_postcode', sanitize_text_field($billing['postcode']));
            update_user_meta($customer_id, 'billing_country', sanitize_text_field($billing['country']));
            update_user_meta($customer_id, 'billing_phone', sanitize_text_field($billing['phone']));

            // Agregar el campo 'customer_crm' para el nuevo cliente
            update_user_meta($customer_id, 'customer_crm', sanitize_text_field($customer_crm));
            $logger->info('Campo customer_crm agregado al nuevo cliente.', $log_context);

            $logger->info('Datos de facturación del cliente actualizados.', $log_context);
        }

        // Crear el pedido y asignar al cliente
        $order = wc_create_order();
        $order->set_customer_id($customer_id); // Asignar cliente al pedido
        $logger->info('Pedido creado con el ID: ' . $order->get_id(), $log_context);


        // Inicializar total de productos
        $total_productos = 0;

        // Agregar productos por SKU
        // vamos a modificar para que el producto tengo el precio nuevo o se cree el producto


        // Agregar línea de envío como producto
        if (isset($params['shipping_lines']) && !empty($params['shipping_lines'])) {
            foreach ($params['shipping_lines'] as $shipping_line) {
                if (isset($shipping_line['total'])) {
                    $sku = '897';
                    $product_id = wc_get_product_id_by_sku($sku);
                    if ($product_id) {
                        $product = wc_get_product($product_id);
                        $order->add_product($product, 1, array('total' => $shipping_line['total']));
                        $logger->info('Producto de envío añadido: SKU ' . $sku . ' con total ' . $shipping_line['total'], $log_context);
                        $total_productos += $shipping_line['total'];
                    }
                }
            }
        }



        foreach ($params['line_items'] as $item) {
            $sku = sanitize_text_field($item['sku']);
            $product_id = wc_get_product_id_by_sku($sku); // Buscar el ID del producto por SKU

            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    // Actualizar precio y stock si es necesario
                    if ($product->get_price() != $item['price']) {
                        $product->set_price($item['price']); // Actualizar precio
                        $product->set_manage_stock(false);  // Quitar manejo de stock
                        $product->save();
                        $logger->info('Precio actualizado para SKU: ' . $sku, $log_context);
                    }

                    $product_stock = $product->get_stock_quantity();
                    if ($product_stock !== null && $product_stock < $item['quantity']) {
                        $product->set_stock_quantity($item['quantity']); // Incrementar stock si es necesario
                        $product->save();
                        $logger->info('Stock actualizado para SKU: ' . $sku, $log_context);
                    }

                    // Aplicar descuento al producto
                    $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
                    $final_price = max($product->get_price() - $discount, 0); // Asegurarse de no tener precios negativos

                    $order->add_product($product, $item['quantity'], array(
                        'subtotal' => $item['price'] * $item['quantity'],
                        'total' => $final_price * $item['quantity'],
                    ));

                    $logger->info('Producto añadido al pedido con descuento: SKU ' . $sku . ', Descuento: ' . $discount . ', Precio final: ' . $final_price, $log_context);

                    // Sumar al total de productos considerando el descuento
                    $total_productos += $final_price * $item['quantity'];
                } else {
                    $logger->error('Error: Producto no encontrado por ID después de buscar el SKU: ' . $sku, $log_context);
                }
            } else {
                // Crear producto si no existe
                $logger->info('Producto no encontrado por SKU: ' . $sku . '. Creando un nuevo producto.', $log_context);

                $product_name = isset($item['name']) ? sanitize_text_field($item['name']) : 'Producto sin nombre';
                $new_product = new WC_Product();
                $new_product->set_name($product_name);
                $new_product->set_sku($sku);
                $new_product->set_regular_price($item['price']);
                $new_product->set_manage_stock(false);
                $product_id = $new_product->save();

                $logger->info('Nuevo producto creado con SKU: ' . $sku . ', Nombre: ' . $product_name . ', ID del producto: ' . $product_id, $log_context);

                $product = wc_get_product($product_id);
                if ($product) {
                    // Aplicar descuento al producto creado
                    $discount = isset($item['discount']) ? floatval($item['discount']) : 0;
                    $final_price = max($product->get_price() - $discount, 0);

                    $order->add_product($product, $item['quantity'], array(
                        'subtotal' => $item['price'] * $item['quantity'],
                        'total' => $final_price * $item['quantity'],
                    ));

                    $logger->info('Nuevo producto añadido al pedido con descuento: SKU ' . $sku . ', Descuento: ' . $discount . ', Precio final: ' . $final_price, $log_context);

                    // Sumar al total de productos considerando el descuento
                    $total_productos += $final_price * $item['quantity'];
                } else {
                    $logger->error('Error al obtener el producto recién creado con SKU: ' . $sku, $log_context);
                }
            }
        }

        //foreach
        // Registro de valores para depuración
        // 14 de enero 2025 se agrega la tolerancia tenemos un problema en la comparación de los decimales y se agrega el descuento
        // Convertir 'amount' a decimales (suponiendo que está en centavos)
        $amount_decimal = $amount / 100;
        $total_productos = (float) $total_productos;
        $amount_decimal = (float) $amount_decimal;
        // Definir un umbral para la comparación
        $tolerancia = 0.0001;

        //error_log("total amount (decimal): " . $amount_decimal);

        // Verificar si hay una diferencia entre el total de los productos y el valor de 'amount'
        if ($amount_decimal !== null) {
            //error_log("si entro keikos");
            //error_log("Comparison values: total_productos=" . $total_productos . " | amount_decimal=" . $amount_decimal);

            //error_log("total productos: " . $total_productos);
            //error_log("amount decimal: " . $amount_decimal);

            if (abs($total_productos - $amount_decimal) > $tolerancia) {
                $diferencia = $total_productos - $amount_decimal;
                //error_log("keikos Entró en el if, diferencia: " . $diferencia);

                // Crear un nuevo objeto de tipo WC_Order_Item_Fee
                $fee = new WC_Order_Item_Fee();
                $fee->set_name('Descuento ');  // Nombre del descuento
                $fee->set_amount(-$diferencia);  // El monto debe ser negativo para un descuento
                $fee->set_total(-$diferencia);  // El total debe ser negativo también

                // Agregar el objeto fee al pedido
                $order->add_item($fee);


                $logger->info('Descuento aplicado: ' . $diferencia, $log_context);
            } else {
                error_log("No entró en el if, total_productos no es mayor que amount_decimal.");
            }
        } else {
            error_log("Amount decimal es null.");
        }


        // Configurar facturación, envío y pago
        $order->set_address($params['billing'], 'billing');
        if (isset($params['shipping'])) {
            $order->set_address($params['shipping'], 'shipping');
        }
        $order->set_payment_method($params['payment_method']);
        $logger->info('Direcciones de facturación y envío, y método de pago configurados.', $log_context);

        // Recalcular el total del pedido después de aplicar el descuento
        $order->calculate_totals();
        $logger->info('Totales del pedido recalculados.', $log_context);

        // Guardar el pedido
        $order->save();

        // Guardar los nuevos metadatos en el pedido
        guardar_o_actualizar_metadato($order->get_id(), 'puntos_reservados', $puntos_reservados, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'centro', $centro, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'lp_pickup_date', $pickup_date, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'lp_pickup_time', $pickup_time, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'lp_pickup_comments', $pickup_comments, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'origen', $origen, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'customer_crm', $customer_crm, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'total_descuento', $diferencia, $logger, $log_context);

        // Guardar los nuevos campos agregados
        guardar_o_actualizar_metadato($order->get_id(), 'metodo_entrega', $metodo_entrega, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'idPromocionCabecera', $idPromocionCabecera, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'Rolperpim', $Rolperpim, $logger, $log_context);
        guardar_o_actualizar_metadato($order->get_id(), 'transaction_id', $transaction_id, $logger, $log_context);

        // Retornar el ID del pedido
        $logger->info('Proceso completado exitosamente. ID del pedido: ' . $order->get_id(), $log_context);
        // Esperar un momento antes de procesar
        // Agregar el evento cron al guardar el pedido
        wp_schedule_single_event(time() + 10, 'cambiar_estado_pedido_a_procesando', array($order->get_id()));

        return array('order_id' => $order->get_id());
    }
    //fin de custom para YALO 3 de diciembre 2024 hack para sku


    function guardar_o_actualizar_metadato($order_id, $meta_key, $meta_value, $logger, $log_context)
    {
        if (!empty($meta_value)) {
            // Verificar si ya existe el metadato
            if (false !== get_post_meta($order_id, $meta_key, true)) {
                // Si ya existe, actualizar
                update_post_meta($order_id, $meta_key, $meta_value);
                $logger->info('Metadato actualizado orden:  ' . $order_id . ' ' . $meta_key . ' -> ' . $meta_value, $log_context);
            } else {
                // Si no existe, crearlo
                add_post_meta($order_id, $meta_key, $meta_value, true);
                $logger->info('Metadato creado orden: ' . $order_id . ' ' . $meta_key . ' -> ' . $meta_value, $log_context);
            }
        } else {
            $logger->warning('Valor de metadato vacío o no válido para la orden: ' . $order_id . ' ' . $meta_key, $log_context);
        }
    }
    // Hook para manejar el cambio de estado
    add_action('cambiar_estado_pedido_a_procesando', 'procesar_cambio_estado_pedido');

    function procesar_cambio_estado_pedido($order_id)
    {
        // Cargar el pedido
        $order = wc_get_order($order_id);

        if ($order) {
            // Cambiar el estado a "procesando"
            $order->set_status('processing');
            $order->save();

            // Opcional: Registrar en el log
            if (class_exists('WC_Logger')) {
                $logger = new WC_Logger();
                $logger->info('Estado del pedido cambiado a procesando para el ID: ' . $order_id);
            }
        }
    }


    //add_action('rest_pre_dispatch', 'registrar_solicitudes_wpjson', 10, 3);
    //add_filter('rest_post_dispatch', 'registrar_respuestas_wpjson', 10, 3);

    /**
     * Registrar las solicitudes entrantes al API REST de WordPress.
     */
    function registrar_solicitudes_wpjson($result, $server, $request)
    {
        // Ignorar rutas relacionadas con /wc-analytics/
        if (strpos($request->get_route(), '/wc-analytics/') !== false) {
            return $result; // No hacer nada para estas rutas.
        }

        // Registrar los datos de la solicitud utilizando WC_Logger
        $logger = new WC_Logger();
        $log_context = array('source' => 'wp_json_api_requests');

        // Obtener el cuerpo de la solicitud (si es un método POST, PUT, etc.)
        $payload = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            $payload = file_get_contents('php://input');
        }

        $log_message = sprintf(
            "Solicitud API:\nMétodo: %s\nRuta: %s\nParámetros: %s\nPayload: %s",
            $request->get_method(),
            $request->get_route(),
            json_encode($request->get_params()),
            $payload ? $payload : 'N/A'
        );

        $logger->info($log_message, $log_context);

        // Pasar el resultado al siguiente paso del flujo.
        return $result;
    }

    /**
     * Registrar las respuestas salientes del API REST de WordPress.
     */
    function registrar_respuestas_wpjson($response, $server, $request)
    {
        // Ignorar rutas relacionadas con /wc-analytics/
        if (strpos($request->get_route(), '/wc-analytics/') !== false) {
            return $response; // No hacer nada para estas rutas.
        }

        // Registrar los datos de la respuesta utilizando WC_Logger
        $logger = new WC_Logger();
        $log_context = array('source' => 'wp_json_api_responses');

        $log_message = sprintf(
            "Respuesta API:\nMétodo: %s\nRuta: %s\nRespuesta: %s",
            $request->get_method(),
            $request->get_route(),
            json_encode($response->get_data())
        );

        $logger->info($log_message, $log_context);

        // Pasar la respuesta al siguiente paso del flujo.
        return $response;
    }

    /**** Personalización del formulario de registro de WooCommerce ****/

    // Agregar los campos en el perfil del usuario en el admin
    add_action('show_user_profile', 'add_custom_user_fields');
    add_action('edit_user_profile', 'add_custom_user_fields');
    // add_action('user_new_form', 'add_custom_user_fields');

    add_action("woocommerce_register_form", "agregar_campos_registro");
    // Permitir la edición de los campos en el admin
    add_action('personal_options_update', 'save_custom_user_fields');
    add_action('edit_user_profile_update', 'save_custom_user_fields');
    add_action("woocommerce_created_customer", "guardar_campos_registro");

    // Función para agregar campos adicionales al formulario de registro de WooCommerce
    function agregar_campos_registro()
    {
        // Catálogos
        $tagPersonal = [
            ["name" => "Ocasión de Consumo", "sin_tilde" => "Ocasion de Consumo"],
        ];

        $tagsNegocio = [
            ["name" => "Pizzas", "sin_tilde" => "Pizzas"],
            ["name" => "Fondas o Cocinas", "sin_tilde" => "Fondas o Cocinas"],
            ["name" => "Hamburguesas", "sin_tilde" => "Hamburguesas"],
            ["name" => "Tortas", "sin_tilde" => "Tortas"],
            ["name" => "Taqueria", "sin_tilde" => "Taqueria"],
            ["name" => "Restaurante", "sin_tilde" => "Restaurante"],
            ["name" => "Hot dogs", "sin_tilde" => "Hot dogs"],
            ["name" => "Gorditas", "sin_tilde" => "Gorditas"],
            ["name" => "Cooperativas", "sin_tilde" => "Cooperativas"],
            ["name" => "Comedores Industriales", "sin_tilde" => "Comedores Industriales"],
            ["name" => "Banquetes", "sin_tilde" => "Banquetes"],
            ["name" => "Pollerias", "sin_tilde" => "Pollerias"],
            ["name" => "Bares y cantinas", "sin_tilde" => "Bares y cantinas"],
            ["name" => "Guarderias", "sin_tilde" => "Guarderias"],
            ["name" => "Carnitas", "sin_tilde" => "Carnitas"],
            ["name" => "Tendero o Cremerias", "sin_tilde" => "Tendero o Cremerias"],
            ["name" => "Carniceria", "sin_tilde" => "Carniceria"],
            ["name" => "Distribuidor", "sin_tilde" => "Distribuidor"],
            ["name" => "Tianguis", "sin_tilde" => "Tianguis"],
            ["name" => "Hoteles", "sin_tilde" => "Hoteles"],
            ["name" => "Burreros", "sin_tilde" => "Burreros"],
            ["name" => "Ferias", "sin_tilde" => "Ferias"],
            ["name" => "Dependencia de Gobierno", "sin_tilde" => "Dependencia de Gobierno"],
            ["name" => "Expendio de Pescados y Mariscos", "sin_tilde" => "Expendio de Pescados y Mariscos"],
            ["name" => "Sushi", "sin_tilde" => "Sushi"],
            ["name" => "Snack", "sin_tilde" => "Snack"],
            ["name" => "Eloteros", "sin_tilde" => "Eloteros"],
            ["name" => "Minisuper", "sin_tilde" => "Minisuper"],
            ["name" => "Barcos", "sin_tilde" => "Barcos"],
            ["name" => "Hospitales", "sin_tilde" => "Hospitales"],
            ["name" => "Cafeterias", "sin_tilde" => "Cafeterias"],
            ["name" => "Birrieria o Barbacoa", "sin_tilde" => "Birrieria o Barbacoa"],
            ["name" => "Menuderia", "sin_tilde" => "Menuderia"],
            ["name" => "Tamales", "sin_tilde" => "Tamales"],
            ["name" => "Restaurante de Mariscos", "sin_tilde" => "Restaurante de Mariscos"],
            ["name" => "Tortilleria o Panaderia", "sin_tilde" => "Tortilleria o Panaderia"],
        ];

        // Estado previo del form (por si se recarga)
        $tipo_uso_post = !empty($_POST['tipo_uso']) ? sanitize_text_field($_POST['tipo_uso']) : 'Business';
        $giro_post = !empty($_POST['giro_empresa']) ? sanitize_text_field($_POST['giro_empresa']) : '';
        $is_personal = ($tipo_uso_post === 'Customer');
        $catalogo_actual = $is_personal ? $tagPersonal : $tagsNegocio;
        ?>

        <p class="form-row form-row-wide">
            <label for="reg_billing_first_name"><?php _e("Nombre", "woocommerce"); ?> <span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name"
                value="<?php echo !empty($_POST['billing_first_name']) ? esc_attr($_POST['billing_first_name']) : ''; ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label for="reg_billing_last_name"><?php _e("Apellidos", "woocommerce"); ?> <span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name"
                value="<?php echo !empty($_POST['billing_last_name']) ? esc_attr($_POST['billing_last_name']) : ''; ?>" />
        </p>

        <p class="form-row form-row-wide">
            <label><?php _e("Tipo de uso", "woocommerce"); ?> <span class="required">*</span></label><br />
        <div style="display: flex;">
            <label style="margin-right: 12px;">
                <input type="radio" name="tipo_uso" value="Customer" id="uso_personal" <?php checked($is_personal); ?> />
                <?php _e("Uso personal", "woocommerce"); ?>
            </label>
            <label>
                <input type="radio" name="tipo_uso" value="Business" id="uso_negocio" <?php checked(!$is_personal); ?> />
                <?php _e("Negocio", "woocommerce"); ?>
            </label>
        </div>
        </p>

        <p class="form-row form-row-wide">
            <label id="reg_giro_label" for="reg_giro_empresa">
                <?php _e("Giro de la empresa", "woocommerce"); ?> <span class="required">*</span>
            </label>
            <select name="giro_empresa" id="reg_giro_empresa" class="input-select" required>
                <option value=""><?php _e("Selecciona el giro de la empresa", "woocommerce"); ?></option>
                <?php foreach ($catalogo_actual as $tag): ?>
                    <option value="<?php echo esc_attr($tag['name']); ?>" <?php selected($giro_post, $tag['name']); ?>>
                        <?php echo esc_html($tag['sin_tilde']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="form-row form-row-wide" id="nombre_empresa_field">
            <label for="billing_company"><?php _e("Nombre de la empresa", "woocommerce"); ?> <span
                    class="required">*</span></label>
            <input type="text" class="input-text " name="billing_company" id="billing_company" placeholder="" value=""
                data-required="1" autocomplete="organization" required="" aria-required="true" aria-invalid="true">
        </p>

        <script type="text/javascript">
            jQuery(function ($) {
                // Catálogos desde PHP → JS
                var catalogoPersonal = <?php echo wp_json_encode($tagPersonal); ?>;
                var catalogoNegocio = <?php echo wp_json_encode($tagsNegocio); ?>;

                // Textos dinámicos
                var labelPersonal = '<?php echo esc_js(__('Giro personal', 'woocommerce')); ?> <span class="required">*</span>';
                var labelEmpresa = '<?php echo esc_js(__('Giro de la empresa', 'woocommerce')); ?> <span class="required">*</span>';
                var phPersonal = '<?php echo esc_js(__('Selecciona el giro personal', 'woocommerce')); ?>';
                var phEmpresa = '<?php echo esc_js(__('Selecciona el giro de la empresa', 'woocommerce')); ?>';

                function poblarSelectGiro(lista, seleccionado, esPersonal) {
                    var $sel = $('#reg_giro_empresa');
                    var $label = $('#reg_giro_label');

                    // wrappers / inputs del campo "Nombre de la empresa"
                    var $companyWrap = $('#nombre_empresa_field'); // <p id="nombre_empresa_field">
                    var $companyInput = $('#billing_company');       // <input id="billing_company">

                    // Mostrar/ocultar por display y limpiar valor si es personal
                    if (esPersonal) {
                        $companyWrap.css('display', 'none');      // o $companyWrap.hide();
                        $companyInput.val('').trigger('change');  // limpia el valor
                        // (opcional) quitar required cuando es personal:
                        $companyInput.prop('required', false).attr('aria-required', 'false');
                    } else {
                        $companyWrap.css('display', 'block');     // o $companyWrap.show();
                        // (opcional) volver a exigirlo cuando es negocio:
                        $companyInput.prop('required', true).attr('aria-required', 'true');
                    }

                    // Cambia etiqueta
                    $label.html(esPersonal ? labelPersonal : labelEmpresa);

                    // placeholder
                    var placeholder = esPersonal ? phPersonal : phEmpresa;
                    $sel.empty().append(new Option(placeholder, ''));

                    // opciones
                    lista.forEach(function (item) {
                        var opt = new Option(item.sin_tilde, item.name, false, item.name === seleccionado);
                        $sel.append(opt);
                    });
                }

                // Inicial (según post o default)
                var inicialEsPersonal = <?php echo $is_personal ? 'true' : 'false'; ?>;
                poblarSelectGiro(
                    inicialEsPersonal ? catalogoPersonal : catalogoNegocio,
                    <?php echo wp_json_encode($giro_post); ?>,
                    inicialEsPersonal
                );

                // Cambio de radio
                $('input[name="tipo_uso"]').on('change', function () {
                    var esPersonal = $('#uso_personal').is(':checked');
                    // si es personal, le damos el valor por defecto ocasion
                    poblarSelectGiro(esPersonal ? catalogoPersonal : catalogoNegocio, esPersonal ? 'Ocasión de Consumo' : '', esPersonal);
                });
            });
        </script>
        <?php
    }

    function add_custom_user_fields($user)
    {
        // Meta actuales
        $tipo_uso = get_user_meta($user->ID, 'tipo_uso', true);      // 'Customer' | 'Business'
        $giro_empresa = get_user_meta($user->ID, 'giro_empresa', true);  // guardamos el "name"

        // Catálogo para "Uso personal"
        $tagPersonal = [
            ["name" => "Ocasión de Consumo", "sin_tilde" => "Ocasion de Consumo"],
        ];

        // Catálogo para "Negocio"
        $tagsNegocio = [
            ["name" => "Pizzas", "sin_tilde" => "Pizzas"],
            ["name" => "Fondas o Cocinas", "sin_tilde" => "Fondas o Cocinas"],
            ["name" => "Hamburguesas", "sin_tilde" => "Hamburguesas"],
            ["name" => "Tortas", "sin_tilde" => "Tortas"],
            ["name" => "Taqueria", "sin_tilde" => "Taqueria"],
            ["name" => "Restaurante", "sin_tilde" => "Restaurante"],
            ["name" => "Hot dogs", "sin_tilde" => "Hot dogs"],
            ["name" => "Gorditas", "sin_tilde" => "Gorditas"],
            ["name" => "Cooperativas", "sin_tilde" => "Cooperativas"],
            ["name" => "Comedores Industriales", "sin_tilde" => "Comedores Industriales"],
            ["name" => "Banquetes", "sin_tilde" => "Banquetes"],
            ["name" => "Pollerias", "sin_tilde" => "Pollerias"],
            ["name" => "Bares y cantinas", "sin_tilde" => "Bares y cantinas"],
            ["name" => "Guarderias", "sin_tilde" => "Guarderias"],
            ["name" => "Carnitas", "sin_tilde" => "Carnitas"],
            ["name" => "Tendero o Cremerias", "sin_tilde" => "Tendero o Cremerias"],
            ["name" => "Carniceria", "sin_tilde" => "Carniceria"],
            ["name" => "Distribuidor", "sin_tilde" => "Distribuidor"],
            ["name" => "Tianguis", "sin_tilde" => "Tianguis"],
            ["name" => "Hoteles", "sin_tilde" => "Hoteles"],
            ["name" => "Burreros", "sin_tilde" => "Burreros"],
            ["name" => "Ferias", "sin_tilde" => "Ferias"],
            ["name" => "Dependencia de Gobierno", "sin_tilde" => "Dependencia de Gobierno"],
            ["name" => "Expendio de Pescados y Mariscos", "sin_tilde" => "Expendio de Pescados y Mariscos"],
            ["name" => "Sushi", "sin_tilde" => "Sushi"],
            ["name" => "Snack", "sin_tilde" => "Snack"],
            ["name" => "Eloteros", "sin_tilde" => "Eloteros"],
            ["name" => "Minisuper", "sin_tilde" => "Minisuper"],
            ["name" => "Barcos", "sin_tilde" => "Barcos"],
            ["name" => "Hospitales", "sin_tilde" => "Hospitales"],
            ["name" => "Cafeterias", "sin_tilde" => "Cafeterias"],
            ["name" => "Birrieria o Barbacoa", "sin_tilde" => "Birrieria o Barbacoa"],
            ["name" => "Menuderia", "sin_tilde" => "Menuderia"],
            ["name" => "Tamales", "sin_tilde" => "Tamales"],
            ["name" => "Restaurante de Mariscos", "sin_tilde" => "Restaurante de Mariscos"],
            ["name" => "Tortilleria o Panaderia", "sin_tilde" => "Tortilleria o Panaderia"],
        ];

        // Si no hay meta, por defecto Negocio
        $is_personal = ($tipo_uso === 'Customer');
        $catalogo_actual = $is_personal ? $tagPersonal : $tagsNegocio;
        ?>
        <h3>Información adicional</h3>

        <table class="form-table">
            <tr>
                <th><label for="tipo_uso">Tipo de Uso</label></th>
                <td>
                    <label style="margin-right:12px;">
                        <input type="radio" name="tipo_uso" id="cm_tipo_uso_personal" value="Customer" <?php checked($is_personal); ?> />
                        Uso personal
                    </label>
                    <label>
                        <input type="radio" name="tipo_uso" id="cm_tipo_uso_negocio" value="Business" <?php checked(!$is_personal); ?> />
                        Negocio
                    </label>
                </td>
            </tr>

            <tr>
                <th>
                    <!-- Le damos un id para poder actualizar el texto desde JS -->
                    <label for="giro_empresa" id="cm_giro_label">
                        <?php echo $is_personal ? 'Giro personal' : 'Giro de Empresa'; ?>
                    </label>
                </th>
                <td>
                    <select name="giro_empresa" id="giro_empresa" class="regular-text" style="min-width:320px;">
                        <option value="">
                            <?php echo $is_personal ? 'Selecciona el giro personal' : 'Selecciona el giro de la empresa'; ?>
                        </option>
                        <?php
                        foreach ($catalogo_actual as $tag) {
                            echo '<option value="' . esc_attr($tag['name']) . '" ' .
                                selected($giro_empresa, $tag['name'], false) . '>' .
                                esc_html($tag['sin_tilde']) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>

        <script>
            jQuery(function ($) {
                var catalogoPersonal = <?php echo wp_json_encode($tagPersonal); ?>;
                var catalogoNegocio = <?php echo wp_json_encode($tagsNegocio); ?>;

                var LBL_EMPRESA = 'Giro de Empresa';
                var LBL_PERSONAL = 'Giro personal';
                var PH_EMPRESA = 'Selecciona el giro de la empresa';
                var PH_PERSONAL = 'Selecciona el giro personal';

                function setLabelText(isPersonal) {
                    // Actualiza el texto del label (dejando el DOM del <span class="description"> si WP lo añadiera)
                    $('#cm_giro_label').contents().first()[0].textContent = (isPersonal ? LBL_PERSONAL : LBL_EMPRESA);
                }

                function poblarSelect(lista, seleccionado, placeholderText) {
                    var $sel = $('#giro_empresa');
                    $sel.empty().append(new Option(placeholderText, ''));
                    lista.forEach(function (item) {
                        var opt = new Option(item.sin_tilde, item.name, false, item.name === seleccionado);
                        $sel.append(opt);
                    });
                    if (!seleccionado && lista.length === 1) {
                        $sel.val(lista[0].name); // autoselección para uso personal
                    }
                }

                // Cambio de tipo de uso → actualizar label, placeholder y opciones
                $('input[name="tipo_uso"]').on('change', function () {
                    var esPersonal = $('#cm_tipo_uso_personal').is(':checked');
                    setLabelText(esPersonal);
                    poblarSelect(esPersonal ? catalogoPersonal : catalogoNegocio, '', esPersonal ? PH_PERSONAL : PH_EMPRESA);
                });
            });
        </script>
        <?php
    }

    function save_custom_user_fields($user_id)
    {
        if (current_user_can('edit_user', $user_id)) {
            if (isset($_POST['tipo_uso'])) {
                update_user_meta($user_id, 'tipo_uso', sanitize_text_field($_POST['tipo_uso']));
            }
            if (isset($_POST['giro_empresa'])) {
                update_user_meta($user_id, 'giro_empresa', sanitize_text_field($_POST['giro_empresa']));
            }
        }
    }

    // Valida el formulario de registro ANTES de crear el usuario
    add_filter('woocommerce_process_registration_errors', 'cm_validate_register_fields', 10, 3);
    add_filter('woocommerce_registration_errors', 'cm_validate_register_fields', 10, 3); // compat

    function cm_validate_register_fields($errors, $username, $email)
    {
        $p = isset($_POST) ? wp_unslash($_POST) : array();

        // Nombre y Apellidos: mínimo 4 caracteres
        $first = isset($p['billing_first_name']) ? trim(sanitize_text_field($p['billing_first_name'])) : '';
        $last = isset($p['billing_last_name']) ? trim(sanitize_text_field($p['billing_last_name'])) : '';

        if ($first === '' || mb_strlen($first) < 4) {
            $errors->add('billing_first_name', __('El nombre debe tener al menos 4 caracteres.', 'woocommerce'));
        }

        if ($last === '' || mb_strlen($last) < 4) {
            $errors->add('billing_last_name', __('Los apellidos deben tener al menos 4 caracteres.', 'woocommerce'));
        }

        // Tipo de uso / Giro / Empresa
        $tipo = isset($p['tipo_uso']) ? sanitize_text_field($p['tipo_uso']) : '';
        $giro = isset($p['giro_empresa']) ? sanitize_text_field($p['giro_empresa']) : '';
        $company = isset($p['billing_company']) ? trim($p['billing_company']) : '';

        if ($tipo === '') {
            $errors->add('tipo_uso', __('Por favor, selecciona el tipo de uso.', 'woocommerce'));
            return $errors; // corto aquí para no duplicar mensajes dependientes
        }

        $is_business = (strcasecmp($tipo, 'Business') === 0) || (strcasecmp($tipo, 'Negocio') === 0);

        if ($is_business) {
            if ($giro === '') {
                $errors->add('giro_empresa', __('Por favor, selecciona el giro de la empresa.', 'woocommerce'));
            }

            if ($company === '') {
                $errors->add('billing_company', __('Por favor, ingresa el nombre de la empresa.', 'woocommerce'));
            } elseif (mb_strlen($company) < 3) {
                $errors->add('billing_company_len', __('El nombre de la empresa debe tener al menos 3 caracteres.', 'woocommerce'));
            }
        }

        return $errors;
    }

    // Guardar los campos personalizados en la base de datosadd_action( 'woocommerce_created_customer', 'guardar_campos_registro' );
    function guardar_campos_registro($customer_id)
    {
        $p = isset($_POST) ? wp_unslash($_POST) : array();

        $map = [
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'tipo_uso',
            'giro_empresa',
        ];

        foreach ($map as $key) {
            if (isset($p[$key])) {
                update_user_meta($customer_id, $key, sanitize_text_field($p[$key]));
            }
        }
    }

    add_action('woocommerce_edit_account_form', 'mostrar_customer_crm_en_detalles_de_cuenta', 20);
    function mostrar_customer_crm_en_detalles_de_cuenta()
    {

        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Verifica que el usuario esté logueado antes de proceder
        if (is_user_logged_in()) {

            if ($user_id) {
                $customer_crm = get_user_meta($user_id, 'customer_crm', true);

                // Solo muestra el campo si el meta existe y no está vacío
                if (!empty($customer_crm)) {
                    echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">';
                    echo '<label for="customer_crm">ID DE CLIENTE CRM: ' . esc_attr($customer_crm) . '</label>';
                    //     echo '<input type="text" id="customer_crm" name="customer_crm" value="' . esc_attr( $customer_crm ) . '" readonly />';
                    echo '</p>';
                }
            }
        }
    }


    // Agregar los campos al formulario de edición en "Mi Cuenta"
    add_action('woocommerce_edit_account_form', 'custom_edit_account_form');

    function custom_edit_account_form()
    {
        $user_id = get_current_user_id();
        $tipo_uso = get_user_meta($user_id, 'tipo_uso', true);        // 'Customer' | 'Business' (o vacío)
        $giro_empresa = get_user_meta($user_id, 'giro_empresa', true);    // guardamos el "name"

        // Catálogo para "Uso personal"
        $tagPersonal = [
            ["name" => "Ocasión de Consumo", "sin_tilde" => "Ocasion de Consumo"],
        ];

        // Catálogo para "Negocio"
        $tagsNegocio = [
            ["name" => "Pizzas", "sin_tilde" => "Pizzas"],
            ["name" => "Fondas o Cocinas", "sin_tilde" => "Fondas o Cocinas"],
            ["name" => "Hamburguesas", "sin_tilde" => "Hamburguesas"],
            ["name" => "Tortas", "sin_tilde" => "Tortas"],
            ["name" => "Taqueria", "sin_tilde" => "Taqueria"],
            ["name" => "Restaurante", "sin_tilde" => "Restaurante"],
            ["name" => "Hot dogs", "sin_tilde" => "Hot dogs"],
            ["name" => "Gorditas", "sin_tilde" => "Gorditas"],
            ["name" => "Cooperativas", "sin_tilde" => "Cooperativas"],
            ["name" => "Comedores Industriales", "sin_tilde" => "Comedores Industriales"],
            ["name" => "Banquetes", "sin_tilde" => "Banquetes"],
            ["name" => "Pollerias", "sin_tilde" => "Pollerias"],
            ["name" => "Bares y cantinas", "sin_tilde" => "Bares y cantinas"],
            ["name" => "Guarderias", "sin_tilde" => "Guarderias"],
            ["name" => "Carnitas", "sin_tilde" => "Carnitas"],
            ["name" => "Tendero o Cremerias", "sin_tilde" => "Tendero o Cremerias"],
            ["name" => "Carniceria", "sin_tilde" => "Carniceria"],
            ["name" => "Distribuidor", "sin_tilde" => "Distribuidor"],
            ["name" => "Tianguis", "sin_tilde" => "Tianguis"],
            ["name" => "Hoteles", "sin_tilde" => "Hoteles"],
            ["name" => "Burreros", "sin_tilde" => "Burreros"],
            ["name" => "Ferias", "sin_tilde" => "Ferias"],
            ["name" => "Dependencia de Gobierno", "sin_tilde" => "Dependencia de Gobierno"],
            ["name" => "Expendio de Pescados y Mariscos", "sin_tilde" => "Expendio de Pescados y Mariscos"],
            ["name" => "Sushi", "sin_tilde" => "Sushi"],
            ["name" => "Snack", "sin_tilde" => "Snack"],
            ["name" => "Eloteros", "sin_tilde" => "Eloteros"],
            ["name" => "Minisuper", "sin_tilde" => "Minisuper"],
            ["name" => "Barcos", "sin_tilde" => "Barcos"],
            ["name" => "Hospitales", "sin_tilde" => "Hospitales"],
            ["name" => "Cafeterias", "sin_tilde" => "Cafeterias"],
            ["name" => "Birrieria o Barbacoa", "sin_tilde" => "Birrieria o Barbacoa"],
            ["name" => "Menuderia", "sin_tilde" => "Menuderia"],
            ["name" => "Tamales", "sin_tilde" => "Tamales"],
            ["name" => "Restaurante de Mariscos", "sin_tilde" => "Restaurante de Mariscos"],
            ["name" => "Tortilleria o Panaderia", "sin_tilde" => "Tortilleria o Panaderia"],
        ];

        // Si $tipo_uso está vacío, tratamos como "Negocio" por defecto
        $is_personal = ($tipo_uso === 'Customer');
        $catalogo_actual = $is_personal ? $tagPersonal : $tagsNegocio;
        ?>

        <p class="form-row form-row-wide">
            <label>
                <?php _e("Tipo de uso", "woocommerce"); ?>
                <span class="required">*</span>
            </label>

            <span class="cm-inline-radios">
                <label>
                    <input type="radio" name="tipo_uso" id="cm_tipo_uso_personal" value="Customer" <?php checked($is_personal); ?> />
                    <?php _e("Uso personal", "woocommerce"); ?>
                </label>

                <label>
                    <input type="radio" name="tipo_uso" id="cm_tipo_uso_negocio" value="Business" <?php checked(!$is_personal); ?> />
                    <?php _e("Negocio", "woocommerce"); ?>
                </label>
            </span>
        </p>

        <style>
            /* Radios en línea (sin lógica móvil) */
            .cm-inline-radios {
                display: flex;
                justify-content: space-between;
                max-width: 60%;
                align-items: center;
                gap: 12px;
            }

            .cm-inline-radios label {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
            }
        </style>

        <p class="form-row form-row-wide">
            <label for="giro_empresa" id="cm_giro_label">
                <?php echo $is_personal ? esc_html__('Giro personal', 'woocommerce')
                    : esc_html__('Giro de la empresa', 'woocommerce'); ?>
                <span class="required">*</span>
            </label>
            <select name="giro_empresa" id="giro_empresa" class="woocommerce-Input woocommerce-Input--select input-select"
                required>
                <option value="">
                    <?php echo $is_personal ? esc_html__('Selecciona el giro personal', 'woocommerce')
                        : esc_html__('Selecciona el giro de la empresa', 'woocommerce'); ?>
                </option>
                <?php
                // HTML inicial acorde al tipo guardado y preseleccionando el giro guardado
                foreach ($catalogo_actual as $tag) {
                    echo '<option value="' . esc_attr($tag['name']) . '" ' .
                        selected($giro_empresa, $tag['name'], false) . '>' .
                        esc_html($tag['sin_tilde']) . '</option>';
                }
                ?>
            </select>
        </p>

        <script>
            jQuery(function ($) {
                var catalogoPersonal = <?php echo wp_json_encode($tagPersonal); ?>;
                var catalogoNegocio = <?php echo wp_json_encode($tagsNegocio); ?>;
                var savedGiro = <?php echo wp_json_encode((string) $giro_empresa); ?>;
                var esPersonalInit = $('#cm_tipo_uso_personal').is(':checked');

                // Textos
                var LBL_EMPRESA = '<?php echo esc_js(__('Giro de la empresa', 'woocommerce')); ?>';
                var LBL_PERSONAL = '<?php echo esc_js(__('Giro personal', 'woocommerce')); ?>';
                var PH_EMPRESA = '<?php echo esc_js(__('Selecciona el giro de la empresa', 'woocommerce')); ?>';
                var PH_PERSONAL = '<?php echo esc_js(__('Selecciona el giro personal', 'woocommerce')); ?>';

                function setLabelAndPlaceholder(esPersonal) {
                    $('#cm_giro_label').contents().first()[0].textContent = esPersonal ? LBL_PERSONAL + ' ' : LBL_EMPRESA + ' ';
                }

                function poblarSelect(lista, seleccionado, placeholderText) {
                    var $sel = $('#giro_empresa');
                    $sel.empty().append(new Option(placeholderText, ''));
                    lista.forEach(function (item) {
                        var opt = new Option(item.sin_tilde, item.name, false, item.name === seleccionado);
                        $sel.append(opt);
                    });
                    if (!seleccionado && lista.length === 1) {
                        $sel.val(lista[0].name);
                    }
                }

                // Inicial
                setLabelAndPlaceholder(esPersonalInit);
                poblarSelect(esPersonalInit ? catalogoPersonal : catalogoNegocio,
                    savedGiro,
                    esPersonalInit ? PH_PERSONAL : PH_EMPRESA);

                // Cambio de tipo → repoblar y actualizar textos
                $('input[name="tipo_uso"]').on('change', function () {
                    var esPersonal = $('#cm_tipo_uso_personal').is(':checked');
                    setLabelAndPlaceholder(esPersonal);
                    poblarSelect(esPersonal ? catalogoPersonal : catalogoNegocio,
                        '',
                        esPersonal ? PH_PERSONAL : PH_EMPRESA);
                });
            });
        </script>
        <?php
    }


    // Guardar los datos cuando el usuario edite su cuenta
    add_action('woocommerce_save_account_details', 'save_custom_account_fields');

    function save_custom_account_fields($user_id)
    {
        if (isset($_POST['tipo_uso'])) {
            update_user_meta($user_id, 'tipo_uso', sanitize_text_field($_POST['tipo_uso']));
        }

        if (isset($_POST['giro_empresa'])) {
            update_user_meta($user_id, 'giro_empresa', sanitize_text_field($_POST['giro_empresa']));
        }
    }

    /**EOF: DATOS DE USUARIO */

    /*** Funciones relacionadas con stock por ubicación ***/

    // Función para limitar la cantidad de productos según la ubicación seleccionada
    function limitar_cantidad_por_ubicacion($max, $product)
    {
        $term_id = isset($_COOKIE["wcmlim_selected_location_termid"])
            ? $_COOKIE["wcmlim_selected_location_termid"]
            : false;
        if ($term_id) {
            $stock_at_location = get_post_meta(
                $product->get_id(),
                "wcmlim_stock_at_{$term_id}",
                true
            );
            if ($stock_at_location !== "") {
                return (int) $stock_at_location;
            }
        }
        return $max;
    }
    add_filter(
        "woocommerce_quantity_input_max",
        "limitar_cantidad_por_ubicacion",
        10,
        2
    );

    // Función para validar el stock de acuerdo a la ubicación seleccionada
    function validar_stock_por_ubicacion(
        $passed,
        $product_id,
        $quantity,
        $variation_id = null,
        $cart_item_data = null
    ) {
        $term_id = isset($_COOKIE["wcmlim_selected_location_termid"])
            ? $_COOKIE["wcmlim_selected_location_termid"]
            : false;
        if ($term_id) {
            $stock_at_location = get_post_meta(
                $product_id,
                "wcmlim_stock_at_{$term_id}",
                true
            );
            if ($stock_at_location !== "" && $quantity > $stock_at_location) {
                wc_add_notice(
                    __(
                        "No hay suficiente stock disponible para esta ubicación."
                    ),
                    "error"
                );
                return false;
            }
        }
        return $passed;
    }
    add_filter(
        "woocommerce_add_to_cart_validation",
        "validar_stock_por_ubicacion",
        10,
        5
    );

    // Función para mostrar el stock según la ubicación seleccionada después del precio
    function mostrar_stock_ubicacion_despues_precio()
    {
        global $product;


        if (!is_product()) {
            wp_enqueue_script('script-personalizado', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);
        }


        // Obtener el term_id de la cookie para la ubicación seleccionada
        $term_id = isset($_COOKIE["wcmlim_selected_location_termid"])
            ? $_COOKIE["wcmlim_selected_location_termid"]
            : false;

        if ($term_id) {
            // Obtener el stock disponible en la ubicación específica
            $stock_at_location = get_post_meta(
                $product->get_id(),
                "wcmlim_stock_at_{$term_id}",
                true
            );

            //z echo '<div id="totalWeight_quantity_' . $product->get_id() . '" style="font-weight:bold; margin-bottom:10px;"></div>';

            /* Mostrar el stock disponible si existe, justo después del precio
            if ($stock_at_location !== "") {
                echo '<p class="stock-availability">Disponible: ' .
                    esc_html($stock_at_location) .
                    "</p>";
            } else {
                echo '<p style="display:none;" class="stock-availability">Stock no disponible.</p>';
            }*/
        }
    }

    // Añadir la función al hook después de que se muestre el precio
    add_action(
        "woocommerce_after_shop_loop_item_title",
        "mostrar_stock_ubicacion_despues_precio",
        15
    );

    /*** Funciones relacionadas con logs ***/
    // Función para vaciar los logs de WooCommerce
    function vaciar_logs_woocommerce()
    {
        $wp_upload_dir = wp_upload_dir();
        $logs_dir = $wp_upload_dir["basedir"] . "/wc-logs/";
        if (is_dir($logs_dir)) {
            $logs_files = glob($logs_dir . "*.log");
            foreach ($logs_files as $log_file) {
                unlink($log_file);
            }
            error_log("Logs de WooCommerce vaciados con éxito.");
        } else {
            error_log("No se encontró el directorio de logs de WooCommerce.");
        }
    }
    // add_action('init', 'vaciar_logs_woocommerce');

    /*** Shortcodes y funcionalidades adicionales ***/
    // Función para generar un número aleatorio y mostrar la fecha y hora del servidor
    function generar_numero_aleatorio_y_fecha()
    {
        $numero_aleatorio = rand(1, 1000);
        $fecha_hora_servidor = date("Y-m-d H:i:s");
        // return "Número aleatorio: $numero_aleatorio <br> Fecha y hora del servidor: $fecha_hora_servidor";
    }
    //add_shortcode("random_numero_fecha", "generar_numero_aleatorio_y_fecha");
    /*
    add_action("rest_api_init", function () {
        register_rest_route("custom/v1", "/create-order/", [
            "methods" => "POST",
            "callback" => "custom_create_order",
            "permission_callback" => function () {
                return current_user_can("manage_woocommerce");
            },
        ]);
    });*/
    // Función para agregar datos personalizados a una ubicación
    function agregar_datos_personalizados_a_locations(
        $response,
        $post,
        $request
    ) {
        if ("locations" === $post->taxonomy) {
            $response->data["centro_location"] = get_term_meta(
                $post->term_id,
                "centro_location",
                true
            );
            $response->data["shared_catalog"] = get_term_meta(
                $post->term_id,
                "shared_catalog",
                true
            );
            $response->data["source"] = get_term_meta(
                $post->term_id,
                "id_almacen",
                true
            );
            $response->data["customer_group"] = get_term_meta(
                $post->term_id,
                "customer_group",
                true
            );
            $response->data["url_maps"] = get_term_meta(
                $post->term_id,
                "url_maps",
                true
            );
            $response->data["activo"] = !empty(get_term_meta($post->term_id, "centro_activo", true))
                ? get_term_meta($post->term_id, "centro_activo", true)
                : 1;
        }
        return $response;
    }
    add_filter(
        "rest_prepare_locations",
        "agregar_datos_personalizados_a_locations",
        10,
        3
    );

    if (!function_exists('anaquel_home')) {
        function anaquel_home($atts)
        {

            // Atributos para el shortcode
            $atts = shortcode_atts(
                [
                    'columns' => 4,
                    'rows' => 3,
                    'total_products' => 20, // Total de productos a mostrar
                ],
                $atts,
                'home_productos_sb'
            );

            // Detectar tienda seleccionada
            $term_id = (!empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined')
                ? intval($_COOKIE['wcmlim_selected_location_termid'])
                : 0;

            // Cache key única por tienda y configuración
            $cache_key = 'anaquel_home_' . md5(serialize($atts) . '_' . $term_id);
            $cache_expiration = 15 * MINUTE_IN_SECONDS;

            // Intentar obtener del cache
            $cached_result = get_transient($cache_key);
            if ($cached_result !== false) {
                return $cached_result;
            }

            // Preparar los argumentos de consulta
            $args = [
                'post_type' => 'product',
                'posts_per_page' => intval($atts['total_products']) * 2, // Traer extra para filtrar
                'post_status' => 'publish',
                'orderby' => 'rand',
                'fields' => 'ids',   // solo IDs
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => '_stock_status',
                        'value' => 'instock',
                        'compare' => '=',
                    ],
                    // Excluir productos cuyo product_step NO sea entero <= 1 (chequeo fino más abajo)
                    [
                        'key' => 'product_step',
                        'value' => '.',
                        'compare' => 'NOT LIKE',
                    ],
                ],
            ];

            if ($term_id) {
                // Stock por tienda
                $args['meta_query'][] = [
                    'key' => "wcmlim_stock_at_{$term_id}",
                    'value' => '0',
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ];
                // Precio regular por tienda > 0
                $args['meta_query'][] = [
                    'key' => "wcmlim_regular_price_at_{$term_id}",
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ];
            } else {
                // Sin tienda: precio base > 0 y stock > 1
                $args['meta_query'][] = [
                    'key' => '_regular_price',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ];
                $args['meta_query'][] = [
                    'key' => '_stock',
                    'value' => '1',
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ];
            }

            // Ejecutar consulta
            $product_ids = get_posts($args);

            // Filtrar por product_step vía query directa (optimizada)
            $filtered = [];
            if (!empty($product_ids)) {
                global $wpdb;

                // Asegurar IDs enteros
                $safe_ids = array_map('intval', $product_ids);
                $ids_string = implode(',', $safe_ids);

                // Nota: %s solo para la meta_key, el IN() ya va saneado arriba.
                $meta_results = $wpdb->get_results(
                    $wpdb->prepare("
                    SELECT post_id, meta_value 
                    FROM {$wpdb->postmeta} 
                    WHERE post_id IN ($ids_string) 
                    AND meta_key = %s
                ", 'product_step')
                );

                // Lookup de product_step
                $step_meta = [];
                foreach ($meta_results as $result) {
                    $step_meta[intval($result->post_id)] = $result->meta_value;
                }

                // Filtrado final
                foreach ($safe_ids as $product_id) {
                    if (count($filtered) >= intval($atts['total_products'])) {
                        break;
                    }
                    $step = isset($step_meta[$product_id]) ? $step_meta[$product_id] : '';
                    if (is_numeric($step) && floor($step) == $step && $step <= 1) {
                        $filtered[] = $product_id;
                    }
                }
            }

            // Renderizar salida (usar buffer y luego RETURN)
            ob_start();

            if (!empty($filtered)) {
                // Pasar columnas al loop nativo
                wc_set_loop_prop('columns', intval($atts['columns']));

                echo '<div class="elementor-element elementor-element-5828161beto carnemart-loop-productos elementor-grid-mobile-2 elementor-grid-4 elementor-grid-tablet-3 elementor-products-grid elementor-wc-products elementor-widget elementor-widget-woocommerce-products"><div class="elementor-widget-container"><div class="woocommerce columns-' . esc_attr($atts['columns']) . '"><ul class="products elementor-grid columns-' . esc_attr($atts['columns']) . '">';

                foreach ($filtered as $product_id) {
                    $post_object = get_post($product_id);
                    if ($post_object) {
                        setup_postdata($GLOBALS['post'] = $post_object);
                        wc_get_template_part('content', 'product'); // item nativo
                    }
                }

                echo '</ul></div></div></div>';
                wp_reset_postdata();
            } else {
                echo '<div class="msg-general"><span class="cu-info-circle"></span><span class="msg-text">No hay productos disponibles en este momento.</span></div>';
            }

            $output = ob_get_clean();

            // Guardar cache y RETURN (no echo)
            set_transient($cache_key, $output, $cache_expiration);
            return $output;
        }
    }

    // Evitar registrar el shortcode dos veces
    if (!shortcode_exists('home_productos_sb')) {
        add_shortcode('home_productos_sb', 'anaquel_home');
    }

    // Limpiar cache cuando se actualicen productos o stock
    add_action('woocommerce_product_set_stock', 'clear_anaquel_home_cache');
    add_action('woocommerce_variation_set_stock', 'clear_anaquel_home_cache');
    add_action('save_post', 'clear_anaquel_home_cache_on_product_save');

    if (!function_exists('clear_anaquel_home_cache')) {
        function clear_anaquel_home_cache($product = null)
        {
            global $wpdb;
            // Borra transients de DB (funciona aunque no haya object cache)
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_anaquel_home_%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_anaquel_home_%'");
        }
    }

    if (!function_exists('clear_anaquel_home_cache_on_product_save')) {
        function clear_anaquel_home_cache_on_product_save($post_id)
        {
            if (get_post_type($post_id) === 'product') {
                clear_anaquel_home_cache();
            }
        }
    }

    /**
     * === Precio por tienda y step/mínimo decimal por producto ===
     * Requisitos:
     *  - Cookie: wcmlim_selected_location_termid
     *  - Metas de precio por tienda: wcmlim_regular_price_at_{TERM}, wcmlim_sale_price_at_{TERM}
     *  - Meta de step: product_step  (ej: "0.5"). Opcional: product_min (ej: "0.5")
     */

    if (!function_exists('sb_get_current_store_term_id')) {
        function sb_get_current_store_term_id(): int
        {
            if (!empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined') {
                return (int) $_COOKIE['wcmlim_selected_location_termid'];
            }
            return 0;
        }
    }

    if (!function_exists('sb_get_store_price_for_product')) {
        /**
         * Obtiene el precio (regular/sale) para un producto/variación según tienda.
         * Devuelve array [ 'regular' => float|null, 'sale' => float|null ] con null si no aplica.
         */
        function sb_get_store_price_for_product($product): array
        {
            $term_id = sb_get_current_store_term_id();
            $pid = $product instanceof WC_Product ? $product->get_id() : (int) $product;

            if ($term_id <= 0 || $pid <= 0) {
                return ['regular' => null, 'sale' => null];
            }

            $regular_meta_key = "wcmlim_regular_price_at_{$term_id}";
            $sale_meta_key = "wcmlim_sale_price_at_{$term_id}";

            // Intentar primero en la variación (si es variation), luego en el padre.
            $regular = get_post_meta($pid, $regular_meta_key, true);
            $sale = get_post_meta($pid, $sale_meta_key, true);

            if ($product instanceof WC_Product_Variation) {
                $parent_id = $product->get_parent_id();
                if (($regular === '' || $regular === null) && $parent_id) {
                    $regular = get_post_meta($parent_id, $regular_meta_key, true);
                }
                if (($sale === '' || $sale === null) && $parent_id) {
                    $sale = get_post_meta($parent_id, $sale_meta_key, true);
                }
            }

            $regular = is_numeric($regular) ? (float) $regular : null;
            $sale = is_numeric($sale) ? (float) $sale : null;

            // Normaliza: si sale no es menor que regular, ignóralo
            if ($sale !== null && $regular !== null && $sale >= $regular) {
                $sale = null;
            }

            return ['regular' => $regular, 'sale' => $sale];
        }
    }

    /**
     * 1) Forzar precio usado por WooCommerce (simples y variaciones)
     *    Esto afecta carrito/checkout y el get_price() interno.
     */
    add_filter('woocommerce_product_get_price', function ($price, $product) {
        $store = sb_get_store_price_for_product($product);
        if ($store['sale'] !== null)
            return $store['sale'];
        if ($store['regular'] !== null)
            return $store['regular'];
        return $price;
    }, 10, 2);

    add_filter('woocommerce_product_variation_get_price', function ($price, $product) {
        $store = sb_get_store_price_for_product($product);
        if ($store['sale'] !== null)
            return $store['sale'];
        if ($store['regular'] !== null)
            return $store['regular'];
        return $price;
    }, 10, 2);

    // También regular/sale para consistencia con reglas/promos.
    add_filter('woocommerce_product_get_regular_price', function ($reg, $product) {
        $store = sb_get_store_price_for_product($product);
        return $store['regular'] !== null ? $store['regular'] : $reg;
    }, 10, 2);

    add_filter('woocommerce_product_get_sale_price', function ($sale, $product) {
        $store = sb_get_store_price_for_product($product);
        return $store['sale'] !== null ? $store['sale'] : $sale;
    }, 10, 2);

    /**
     * 2) Mostrar el HTML del precio con los valores por tienda (simple/variable)
     *    Esto asegura que en la ficha se vea el precio correcto aunque el tema cachee el get_price_html().
     */
    add_filter('woocommerce_get_price_html', function ($price_html, $product) {
        $store = sb_get_store_price_for_product($product);
        if ($store['regular'] === null && $store['sale'] === null) {
            return $price_html; // sin cambio
        }

        if ($store['sale'] !== null) {
            // Precio en oferta
            $html = '<del>' . wc_price($store['regular']) . '</del> ';
            $html .= '<ins>' . wc_price($store['sale']) . '</ins>';
            return $html;
        }

        // Precio regular
        return wc_price($store['regular']);
    }, 10, 2);

    /**
     * 3) Step y mínimo de cantidad (decimales)
     *    Lee metas: product_step (ej "0.5") y opcional product_min (ej "0.5")
     */
    add_filter('woocommerce_quantity_input_args', function ($args, $product) {
        $pid = $product instanceof WC_Product ? $product->get_id() : 0;
        $step_meta = $pid ? get_post_meta($pid, 'product_step', true) : '';
        $min_meta = $pid ? get_post_meta($pid, 'product_min', true) : '';

        // Fallback: si no hay product_min, usa el mismo valor del step como mínimo.
        $step = is_numeric($step_meta) ? (float) $step_meta : 1;
        $min = is_numeric($min_meta) ? (float) $min_meta : $step;

        // Sanitiza (no permitir 0 o negativos)
        if ($step <= 0)
            $step = 1;
        if ($min <= 0)
            $min = $step;

        $args['step'] = $step;
        $args['min_value'] = $min;
        error_log("Producto {$pid}: step={$step}, min={$min}");
        // Permitir teclado decimal en móviles y patrón de decimales
        $args['inputmode'] = 'decimal';
        $args['pattern'] = '[0-9]*[.,]?[0-9]*';

        return $args;
    }, 10, 2);

    /**
     * 3.1) Asegurar que WooCommerce **no redondee a enteros** las cantidades.
     *      wc_stock_amount() aplica este filtro al normalizar cantidades.
     */
    add_filter('woocommerce_stock_amount', function ($qty, $product = null) {
        return is_numeric($qty) ? (float) $qty : $qty;
    }, 10, 2);

    /**
     * 3.2) Opcional: cantidad por defecto igual al mínimo (si el tema la pone en 1)
     */
    add_filter('woocommerce_quantity_input_min', function ($min, $product) {
        $pid = $product instanceof WC_Product ? $product->get_id() : 0;
        $min_meta = $pid ? get_post_meta($pid, 'product_min', true) : '';
        $step_meta = $pid ? get_post_meta($pid, 'product_step', true) : '';

        $step = is_numeric($step_meta) ? (float) $step_meta : 1;
        $minv = is_numeric($min_meta) ? (float) $min_meta : $step;

        if ($minv > 0)
            return $minv;
        return $min;
    }, 10, 2);

    //**** recomendados */

    // Shortcode para mostrar productos relacionados con la página actual
    // Shortcode para mostrar productos aleatorios y más vendidos en el home
    function anaquel_recomendados($atts)
    {
        // Atributos por defecto
        $atts = shortcode_atts(
            [
                'columns' => 4,  // Número de columnas
                'total_products' => 4,  // Total de productos a mostrar
            ],
            $atts,
            'anaquel_recomendados'
        );

        // Detectar tienda seleccionada
        $term_id = !empty($_COOKIE['wcmlim_selected_location_termid'])
            && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
            ? intval($_COOKIE['wcmlim_selected_location_termid'])
            : 0;

        // Construir argumentos de WP_Query
        $meta_query = [
            'relation' => 'AND',

            // 1) Sólo productos en stock
            [
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '=',
            ],

            // 2) Excluir product_step con decimales o > 1
            [
                'key' => 'product_step',
                'value' => '.',
                'compare' => 'NOT LIKE',
            ],
        ];

        // 3) Si hay tienda, filtrar por stock en esa tienda
        if ($term_id) {
            $meta_query[] = [
                'key' => "wcmlim_stock_at_{$term_id}",
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
            // 4a) Excluir si el precio base en tienda es 0 o no existe
            $meta_query[] = [
                'key' => "wcmlim_regular_price_at_{$term_id}",
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
        } else {
            // 4b) Sin tienda: exigir precio base nativo > 0
            $meta_query[] = [
                'key' => '_regular_price',
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
        }

        $args = [
            'post_type' => 'product',
            'posts_per_page' => intval($atts['total_products']),
            'post_status' => 'publish',
            'orderby' => 'meta_value_num rand',
            'meta_key' => 'total_sales',
            'meta_query' => $meta_query,
        ];

        // Ejecutar la consulta
        $loop = new WP_Query($args);

        // Filtrar PHP adicionalmente sólo product_step enteros ≤ 1
        $filtered = [];
        if ($loop->have_posts()) {
            while ($loop->have_posts()) {
                $loop->the_post();
                global $product;
                $step = get_post_meta($product->get_id(), 'product_step', true);
                if (is_numeric($step) && floor($step) == $step && $step <= 1) {
                    $filtered[] = $product->get_id();
                }
            }
            wp_reset_postdata();
        }

        // Renderizar productos
        if (!empty($filtered)) {
            echo '<div class="elementor-element elementor-element-5828161 carnemart-loop-productos elementor-grid-mobile-2 elementor-grid-'
                . esc_attr($atts['columns'])
                . ' elementor-grid-tablet-3 elementor-products-grid elementor-wc-products elementor-widget elementor-widget-woocommerce-products">
                  <div class="elementor-widget-container">
                    <div class="woocommerce columns-' . esc_attr($atts['columns']) . '">
                      <ul class="products elementor-grid columns-' . esc_attr($atts['columns']) . '">';

            foreach ($filtered as $product_id) {
                $post_object = get_post($product_id);
                setup_postdata($GLOBALS['post'] = &$post_object);
                wc_get_template_part('content', 'product');
            }

            echo '      </ul>
                    </div>
                  </div>
              </div>';
            wp_reset_postdata();
        } else {
            echo '<div class="msg-general">
                <span class="cu-info-circle"></span>
                <span class="msg-text">No hay productos disponibles en este momento.</span>
              </div>';
        }
    }
    add_shortcode('anaquel_recomendados', 'anaquel_recomendados');

    /**
     * Obtener el precio máximo de productos filtrados solo por categoría padre
     * @return float
     */
    function cm_ajax_get_max_price()
    {
        $term_id = (isset($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined')
            ? intval($_COOKIE['wcmlim_selected_location_termid'])
            : 0;

        // Detectar slug de la categoría padre desde la URL
        $parent_slug = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $pos = array_search('product-category', $parts);
            if ($pos !== false && isset($parts[$pos + 1])) {
                $parent_slug = sanitize_title($parts[$pos + 1]);
            }
        }

        // fallback: si estamos dentro de archivo de categoría de producto
        if (empty($parent_slug) && function_exists('is_product_category') && is_product_category()) {
            $qo = get_queried_object();
            if ($qo && !is_wp_error($qo) && !empty($qo->slug)) {
                $parent_slug = sanitize_title($qo->slug);
            }
        }

        $price_key = $term_id ? "wcmlim_regular_price_at_{$term_id}" : '_regular_price';

        // SOLO tax_query de la categoría padre
        $tax_query = [];
        if ($parent_slug) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $parent_slug,
                'include_children' => true,   // incluir subcategorías
                'operator' => 'IN',
            ];
        }

        // meta_query igual que antes
        $meta_query = ['relation' => 'AND'];
        if ($term_id) {
            $meta_query[] = [
                'key' => "wcmlim_stock_at_{$term_id}",
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
            $meta_query[] = [
                'key' => $price_key,
                'value' => 0,
                'compare' => '>',
                'type' => 'DECIMAL(10,2)',
            ];
        } else {
            $meta_query[] = [
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '=',
            ];
            $meta_query[] = [
                'key' => $price_key,
                'value' => 0,
                'compare' => '>',
                'type' => 'DECIMAL(10,2)',
            ];
        }

        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,  // solo el más caro
            'fields' => 'ids',
            'no_found_rows' => true,
            'tax_query' => $tax_query,
            'meta_query' => $meta_query,
            'meta_key' => $price_key,
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'ignore_sticky_posts' => true,
        ];

        $q = new WP_Query($args);

        if (!empty($q->posts)) {
            $pid = $q->posts[0];
            $raw = get_post_meta($pid, $price_key, true);
            $max = is_numeric($raw) ? (float) $raw : 0;
        } else {
            $max = 0;
        }

        return round($max, 2);
    }

    /**
     * AJAX: cm_load_products
     */
    add_action('wp_ajax_cm_load_products', 'cm_ajax_load_products');
    add_action('wp_ajax_nopriv_cm_load_products', 'cm_ajax_load_products');
    function cm_ajax_load_products()
    {
        $page = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
        $ppp = isset($_POST['posts_per_page']) ? max(1, (int) $_POST['posts_per_page']) : 12;
        $parent_slug = isset($_POST['parent_slug']) ? sanitize_title(wp_unslash($_POST['parent_slug'])) : '';
        $orderby_in = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'modified';
        $search_q = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';

        $min_price = (isset($_POST['min_price']) && $_POST['min_price'] !== '') ? (float) $_POST['min_price'] : null;
        $max_price = (isset($_POST['max_price']) && $_POST['max_price'] !== '') ? (float) $_POST['max_price'] : null;

        $child_ids = isset($_POST['child_ids'])
            ? array_values(array_filter(array_map('intval', (array) $_POST['child_ids']), fn($v) => $v > 0))
            : [];

        $tag_ids = isset($_POST['tag_ids'])
            ? array_values(array_filter(array_map('intval', (array) $_POST['tag_ids']), fn($v) => $v > 0))
            : [];

        if (!$parent_slug) {
            wp_send_json_success([
                'html' => '',
                'total' => 0,
            ]);
        }

        $tax_query = ['relation' => 'AND'];

        if (!empty($child_ids)) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $child_ids,
                'include_children' => false,
                'operator' => 'IN',
            ];
        } else {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $parent_slug,
                'include_children' => true,
            ];
        }

        if (!empty($tag_ids)) {
            $tax_query[] = [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => $tag_ids,
                'operator' => 'IN',
            ];
        }

        // Obtener el ID de la ubicacion actual
        $term_id = (isset($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined')
            ? (int) $_COOKIE['wcmlim_selected_location_termid']
            : 0;

        // Filtrado por precio
        $price_key = $term_id ? "wcmlim_regular_price_at_{$term_id}" : '_regular_price';

        $meta_query[] = [
            'key' => $price_key,
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];

        // Solo con Stock por tienda, de lo contrario stock en general
        if ($term_id) {
            $stock_key = "wcmlim_stock_at_{$term_id}";
            $meta_query[] = [
                'key' => $stock_key,
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];
        } else {
            $meta_query = [
                'relation' => 'AND',
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                ],
            ];
        }

        // Seguridad en precios
        if ($min_price !== null || $max_price !== null) {
            $range = [
                'key' => $price_key,
                'type' => 'DECIMAL(10,2)',
            ];

            if ($min_price !== null && $max_price !== null) {
                $range['value'] = [$min_price, $max_price];
                $range['compare'] = 'BETWEEN';
            } elseif ($min_price !== null) {
                $range['value'] = $min_price;
                $range['compare'] = '>=';
            } elseif ($max_price !== null) {
                $range['value'] = $max_price;
                $range['compare'] = '<=';
            }

            $meta_query[] = $range;
        }

        // Seguridad en orderby
        $orderby_safe = in_array($orderby_in, ['modified', 'title'], true) ? $orderby_in : 'modified';
        $order_args = ($orderby_safe === 'title')
            ? ['orderby' => 'title', 'order' => 'ASC']
            : ['orderby' => 'modified', 'order' => 'DESC'];

        $product_args = array_merge([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $ppp,
            'paged' => $page,
            'tax_query' => $tax_query,
            'meta_query' => $meta_query,
            'ignore_sticky_posts' => true,
            'no_found_rows' => false, // 👈 Necesario para found_posts y max_num_pages
            's' => $search_q,
        ], $order_args);

        ob_start();
        $loop = new WP_Query($product_args);

        if ($loop->have_posts()) {
            while ($loop->have_posts()) {
                $loop->the_post();
                wc_get_template_part('content', 'product');
            }
            wp_reset_postdata();
        }
        $html = ob_get_clean();

        // Cantidad total de productos
        $total = $loop->found_posts;

        wp_send_json_success([
            'html' => $html,
            'page' => $page,
            'total' => $total,
            'has_more' => $loop->max_num_pages > $page,
        ]);
    }

    add_shortcode('html_total_products', function () {
        return '<div class="nd-exta">
                    <strong style="color: white;">Total de productos: <span id="total-products-count">0</span></strong>
                </div>';
    });

    // Asegura el hook del AJAX (logueados y no logueados)
    add_action('wp_ajax_cm_load_products_giros', 'cm_ajax_load_products_giros');
    add_action('wp_ajax_nopriv_cm_load_products_giros', 'cm_ajax_load_products_giros');
    function cm_ajax_load_products_giros()
    {
        try {
            // --- Helpers ---
            $sanitize_int_array = function ($key) {
                if (!isset($_POST[$key]))
                    return [];
                $arr = (array) $_POST[$key];
                $arr = array_map('intval', $arr);
                $arr = array_filter($arr, fn($v) => $v > 0);
                return array_values($arr);
            };

            // --- Inputs ---
            $page = isset($_POST['page']) ? max(1, (int) $_POST['page']) : 1;
            $ppp = isset($_POST['posts_per_page']) ? max(1, (int) $_POST['posts_per_page']) : 12;
            $orderby_in = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'modified';
            $search_q = isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '';
            $giro_slug = isset($_POST['giro_slug']) ? sanitize_title(wp_unslash($_POST['giro_slug'])) : '';

            $min_price = (isset($_POST['min_price']) && $_POST['min_price'] !== '') ? (float) $_POST['min_price'] : null;
            $max_price = (isset($_POST['max_price']) && $_POST['max_price'] !== '') ? (float) $_POST['max_price'] : null;

            $parent_ids = $sanitize_int_array('parents_ids'); // categorías padre
            $child_ids = $sanitize_int_array('child_ids');    // (opcional) categorías hijo
            $tag_ids = $sanitize_int_array('tag_ids');      // (opcional) otras etiquetas por ID

            // Giro (etiqueta) obligatorio
            if (!$giro_slug) {
                wp_send_json_error(['reason' => 'Falta giro_slug'], 400);
            }

            // Confirma que la etiqueta existe
            $taxonomy = 'product_tag';
            if (!taxonomy_exists($taxonomy)) {
                wp_send_json_error(['reason' => "No existe la taxonomía '{$taxonomy}'"], 400);
            }
            $term_check = term_exists($giro_slug, $taxonomy);
            if (!$term_check) {
                wp_send_json_success([
                    'html' => '',
                    'page' => $page,
                    'total' => 0,
                    'has_more' => false,
                    'debug' => ['note' => "No existe la etiqueta (product_tag) con slug '{$giro_slug}'"]
                ]);
            }

            // --- Tax Query ---
            $tax_query = ['relation' => 'AND'];

            // Filtro por GIRO -> es una etiqueta (product_tag) filtrada por slug
            $tax_query[] = [
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => $giro_slug,
                'operator' => 'IN',
            ];

            // Filtro por categorías de producto (padres o hijos)
            if (!empty($parent_ids)) {
                $tax_query[] = [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $parent_ids,
                    'include_children' => true,
                    'operator' => 'IN',
                ];
            } elseif (!empty($child_ids)) {
                $tax_query[] = [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $child_ids,
                    'include_children' => false,
                    'operator' => 'IN',
                ];
            }

            // Filtro por otras etiquetas (IDs) — se intersecta con el giro
            if (!empty($tag_ids)) {
                $tax_query[] = [
                    'taxonomy' => 'product_tag',
                    'field' => 'term_id',
                    'terms' => $tag_ids,
                    'operator' => 'IN',
                ];
            }

            // Obtenemos la tienda actual
            $term_id = (isset($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined')
                ? (int) $_COOKIE['wcmlim_selected_location_termid']
                : 0;

            // Filtro por ubicación (si usas wcmlim_*), si no, caerá a _price
            $price_key_primary = $term_id ? "wcmlim_regular_price_at_{$term_id}" : '_regular_price';

            // Evitar precios 0
            $meta_query[] = [
                'key' => $price_key_primary,
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ];

            // Solo con Stock por tienda, de lo contrario stock en general
            if ($term_id) {
                $stock_key = "wcmlim_stock_at_{$term_id}";
                $meta_query[] = [
                    'key' => $stock_key,
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ];
            } else {
                $meta_query = [
                    'relation' => 'AND',
                    [
                        'key' => '_stock_status',
                        'value' => 'instock',
                        'compare' => '=',
                    ],
                ];
            }

            // Rango de precios
            if ($min_price !== null || $max_price !== null) {
                $range = [
                    'key' => $price_key_primary,
                    'type' => 'DECIMAL(10,2)',
                ];
                if ($min_price !== null && $max_price !== null) {
                    $range['value'] = [$min_price, $max_price];
                    $range['compare'] = 'BETWEEN';
                } elseif ($min_price !== null) {
                    $range['value'] = $min_price;
                    $range['compare'] = '>=';
                } else {
                    $range['value'] = $max_price;
                    $range['compare'] = '<=';
                }
                $meta_query[] = $range;
            }

            // --- Orderby seguro ---
            $orderby_safe = in_array($orderby_in, ['modified', 'title'], true) ? $orderby_in : 'modified';
            $order_args = ($orderby_safe === 'title')
                ? ['orderby' => 'title', 'order' => 'ASC']
                : ['orderby' => 'modified', 'order' => 'DESC'];

            // --- Query principal ---
            $args = array_merge([
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $ppp,
                'paged' => $page,
                'tax_query' => $tax_query,
                'meta_query' => $meta_query,
                'ignore_sticky_posts' => true,
                'no_found_rows' => false,
                's' => $search_q,
            ], $order_args);

            ob_start();
            $loop = new WP_Query($args);

            // Fallback de precio: si no hubo resultados, intenta con `_price`
            $used_fallback_price_key = false;
            if (!$loop->have_posts()) {
                $used_fallback_price_key = true;
                $args_fallback = $args;

                $meta_query_fb = [];
                foreach ($meta_query as $clause) {
                    if (is_array($clause) && isset($clause['key']) && $clause['key'] === $price_key_primary) {
                        $clause['key'] = '_price';
                        $meta_query_fb[] = $clause;
                    } else {
                        $meta_query_fb[] = $clause;
                    }
                }
                $args_fallback['meta_query'] = $meta_query_fb;

                $loop = new WP_Query($args_fallback);
            }

            if ($loop->have_posts()) {
                while ($loop->have_posts()) {
                    $loop->the_post();
                    wc_get_template_part('content', 'product');
                }
                wp_reset_postdata();
            }
            $html = ob_get_clean();

            $total = isset($loop->found_posts) ? (int) $loop->found_posts : 0;
            $has_more = isset($loop->max_num_pages) ? ($loop->max_num_pages > $page) : false;

            wp_send_json_success([
                'html' => $html,
                'page' => $page,
                'total' => $total,
                'has_more' => $has_more
            ]);

        } catch (Throwable $e) {
            wp_send_json_error([
                'reason' => 'Excepción en handler',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    function cm_html_filtros_actual_shortcode()
    {
        ob_start();
        ?>
        <div id="cm-filtros-actual" class="cm-filtros-actual">
            <ul
                style="list-style-type: none; padding: 0; display: flex; flex-wrap: wrap; padding-top: 5px; padding-bottom: 5px;">
                <li style="font-size: 16px; margin-right: 20px; margin-bottom: 10px; display: flex; align-items: center;">
                    <strong style="color: #021B6D; margin-right: 5px;">Categoría:</strong>
                    <p id="cm-filtro-cat" style="color: #555; font-weight: normal; display: flex; flex-wrap: wrap; gap: 10px;">
                        Sin filtros</p>
                </li>
                <li style="font-size: 16px; margin-right: 20px; margin-bottom: 10px; display: flex; align-items: center;">
                    <strong style="color: #021B6D; margin-right: 5px;">Tags:</strong>
                    <p id="cm-filtro-tags" style="color: #555; font-weight: normal; display: flex; flex-wrap: wrap; gap: 10px;">
                        Sin filtros</p>
                </li>
                <li style="font-size: 16px; margin-right: 20px; margin-bottom: 10px; display: flex; align-items: center;">
                    <strong style="color: #021B6D; margin-right: 5px;">Precio:</strong>
                    <p id="cm-filtro-precio"
                        style="color: #555; font-weight: normal; display: flex; flex-wrap: wrap; gap: 10px;">Sin filtros</p>
                </li>
            </ul>
        </div>
        <style>
            #cm-filtros-actual {
                border: 1px solid #e0e0e0;
                padding: 10px 9px;
                border-radius: 12px;
                box-sizing: border-box;
                max-width: 100%;
                /* Asegura que el contenedor no se expanda más allá del tamaño disponible */
            }

            #cm-filtros-actual ul {
                list-style-type: none;
                padding: 0;
                margin: 0;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            #cm-filtros-actual li {
                font-size: 16px;
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }

            #cm-filtros-actual li strong {
                color: #021B6D;
                margin-right: 8px;
                min-width: 100px;
            }

            #cm-filtros-actual li p {
                color: #555;
                font-weight: normal;
                margin: 0;
            }

            .filtro-chip {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #f9f9f9;
                color: #021B6D;
                border: 1px solid #ddd;
                border-radius: 999px;
                padding: 4px 12px;
                font-size: 13px;
                font-weight: 600;
                line-height: 1.6;
                white-space: nowrap;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease-in-out;
            }

            .filtro-chip:hover {
                background-color: #e1e1ff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            .filtro-chip .chip-x {
                appearance: none;
                background: transparent;
                border: 0;
                cursor: pointer;
                font-size: 16px;
                line-height: 1;
                padding: 0 2px;
                color: inherit;
                border-radius: 50%;
            }

            .filtro-chip .chip-x:hover {
                background: rgba(2, 27, 109, .12);
            }

            /* Estilos específicos para cada filtro */
            #cm-filtro-cat .filtro-chip {
                background: #D4E9F9;
                /* Fondo azul claro para categoría */
                border-color: #4A90E2;
                /* Borde azul */
            }

            #cm-filtro-tags .filtro-chip {
                background: #F1F1F1;
                /* Fondo gris claro para tags */
                border-color: #B0B0B0;
                /* Borde gris */
            }

            #cm-filtro-precio .filtro-chip {
                background: #FFF3CD;
                /* Fondo amarillo claro para precio */
                border-color: #F1C40F;
                /* Borde amarillo */
            }
        </style>
        <?php
        return ob_get_clean();
    }
    add_shortcode('html_filtros_actual', 'cm_html_filtros_actual_shortcode');

    /**
     * Shortcode [productos_giros]
     * - Detecta categoría padre desde /product-category/<padre>/
     * - Filtra por hijas: checkboxes name="cat_p_filter[]"
     * - Filtra por tags:  checkboxes name="tag_p_filter[]"
     * - Búsqueda y rango de precio leyendo los formularios nativos
     * - Infinite scroll por AJAX
     * - Render con content-product.php
     */
    add_shortcode('productos_giros', 'cm_productos_giros_shortcode');
    function cm_productos_giros_shortcode($atts)
    {
        $atts = shortcode_atts([
            'posts_per_page' => 12,
            'columns' => 4,
            'orderby' => 'modified', // 'modified' | 'title'
        ], $atts, 'productos_giros');

        // Detectar categoría padre
        $parent_slug = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $pos = array_search('product-category', $parts);
            if ($pos !== false && isset($parts[$pos + 1])) {
                $parent_slug = sanitize_title($parts[$pos + 1]);
            }
        }

        ob_start(); ?>
        <div id="cm-products-wrap" class="carnemart-loop-productos" data-parent="<?php echo esc_attr($parent_slug); ?>"
            data-ppp="<?php echo esc_attr((int) $atts['posts_per_page']); ?>"
            data-orderby="<?php echo esc_attr($atts['orderby']); ?>">

            <div class="woocommerce">
                <ul id="cm-product-list" class="products elementor-grid columns-<?php echo (int) $atts['columns']; ?>">
                    <!-- AJAX appendea aquí -->
                </ul>
            </div>

            <!-- Loader -->
            <div id="cm-loading" style="display:none;">
                <span class="msg-consulta">Consultando inventario ...</span>
            </div>

            <!-- No results found -->
            <div id="cm-no-results" style="display:none;">
                <span class="msg-consulta-no-results">No se encontraron más productos.</span>
            </div>

            <div id="cm-sentinel" style="height:1px;"></div>
        </div>

        <script>
            jQuery(function ($) {
                const wrap = $("#cm-products-wrap");
                const list = $("#cm-product-list");
                const loader = $("#cm-loading");
                const sent = $("#cm-sentinel");
                const noRes = $("#cm-no-results");
                const counterProducts = $("#total-products-count");

                if (!wrap.length || !list.length) return;

                let page = 1;
                let loading = false;
                let finished = false;
                let pendingReset = false;
                let req = null;
                let blockAuto = false;            // <- evita cargas automáticas del observer cuando recalculamos

                const parentSlug = wrap.data('parent') ?? '';
                const perPage = parseInt(wrap.data('ppp'), 10) || 12;
                const orderby = String(wrap.data('orderby') || 'modified');

                // Verificamos si en la URL hay giro   
                let url = window.location.href;
                let pathParts = window.location.pathname.split("/").filter(Boolean);

                let giroSlug = '';
                if (pathParts.includes("giros")) {
                    giroSlug = pathParts[pathParts.indexOf("giros") + 1] || '';
                }

                // Filtros Tag y Cat
                const filtersSelector = 'input.cat-filter-checkbox[name="cat_p_filter[]"], input.tag-filter-checkbox[name="tag_p_filter[]"]';

                // ---- Filtro categoría ----
                function getSelectedCategory() {
                    const items = $('input.cat-filter-checkbox[name="cat_p_filter[]"]:checked')
                        .map(function () {
                            const id = this.value;
                            let label = $(this).next('span').text().trim();
                            // si el label pasa los 14 caracteres, lo demas solo ...
                            if (label.length > 14) {
                                label = label.substring(0, 14) + '...';
                            }
                            return `<span class="filtro-chip" data-type="cat" data-id="${id}">
                                        <span>${(label)}</span>
                                        <button type="button" class="chip-x" aria-label="Quitar">×</button>
                                    </span>`;
                        }).get().join('');
                    return items || `<span class="filtro-placeholder">Sin filtros</span>`;
                }

                // ---- Filtro tags ----
                function getSelectedTags() {
                    const items = $('input.tag-filter-checkbox[name="tag_p_filter[]"]:checked')
                        .map(function () {
                            const id = this.value;
                            let label = $(this).next('span').text().trim();
                            if (label.length > 14) {
                                label = label.substring(0, 14) + '...';
                            }
                            return `<span class="filtro-chip" data-type="tag" data-id="${id}">
                                        <span>${(label)}</span>
                                        <button type="button" class="chip-x" aria-label="Quitar">×</button>
                                    </span>`;
                        }).get().join('');
                    return items || `<span class="filtro-placeholder">Sin filtros</span>`;
                }

                // ---- Filtro precio ----
                function getPriceRange() {
                    // Obtenemos el minimo y maximo actual del filtro de precio
                    const min = $('.price_label .from').text().trim();
                    const max = $('.price_label .to').text().trim();

                    if (!min && !max) return `<span class="filtro-placeholder">Sin filtros</span>`;
                    const text = `${min || '0'} – ${max || '∞'}`;
                    return `<span class="filtro-chip" data-type="price">
                                <span>${(text)}</span>
                            </span>`;
                }

                // ---- Actualiza los filtros actuales en el HTML ----
                function updateFiltersDisplay() {
                    $('#cm-filtro-cat').html(getSelectedCategory());
                    $('#cm-filtro-tags').html(getSelectedTags());
                    $('#cm-filtro-precio').html(getPriceRange());
                }

                // ---- Eliminar el filtro con la ✕ ----
                $('#cm-filtros-actual').on('click', '.chip-x', function (e) {
                    e.preventDefault();
                    const $chip = $(this).closest('.filtro-chip');
                    const type = $chip.data('type');
                    const id = $chip.data('id');

                    if (type === 'cat') {
                        $(`input.cat-filter-checkbox[name="cat_p_filter[]"][value="${id}"]`)
                            .prop('checked', false).trigger('change');
                    } else if (type === 'tag') {
                        $(`input.tag-filter-checkbox[name="tag_p_filter[]"][value="${id}"]`)
                            .prop('checked', false).trigger('change');
                    } else if (type === 'price') {
                        $('#min_price').val('');
                        $('#max_price').val('');
                        loadProducts(true, true); // Actualiza los productos según los nuevos filtros
                    }

                    updateFiltersDisplay(); // Actualiza la vista de los filtros
                });

                // inicializamos 
                updateFiltersDisplay();

                // Buscador
                const $searchForm = $('form.woocommerce-product-search').first();
                const $searchInput = $searchForm.find('input.search-field');
                const $searchButton = $searchForm.find('button[type="submit"]');

                // Filtros de precio
                const $priceForm = $('li.widget_price_filter form').first();
                const $minInput = $priceForm.find('input#min_price');
                const $maxInput = $priceForm.find('input#max_price');
                const $priceButton = $priceForm.find('button[type="submit"]');

                function setFiltersDisabled(disabled) {
                    $(filtersSelector).prop('disabled', disabled).closest('label').css('opacity', disabled ? 0.6 : 1);
                    $('body').css('cursor', disabled ? 'progress' : '');
                    if ($searchInput.length) $searchInput.prop('disabled', disabled);
                    if ($minInput.length) $minInput.prop('disabled', disabled);
                    if ($maxInput.length) $maxInput.prop('disabled', disabled);
                    if ($priceButton.length) $priceButton.prop('disabled', disabled).css('opacity', disabled ? 0.6 : 1);
                    if ($searchButton.length) $searchButton.prop('disabled', disabled).css('opacity', disabled ? 0.6 : 1);
                    // Desctivamos un div completa con id cm-filtros-actual
                    $('#cm-filtros-actual').css('opacity', disabled ? 0.6 : 1);
                }

                /**
                 * Funciona para obtener los IDs de las categorías hijas seleccionadas, y tambien las categorias padres, en caso estemos en un giro
                 */
                function getSelectedChildIds() {
                    return $('input.cat-filter-checkbox[name="cat_p_filter[]"]:checked')
                        .map(function () { return parseInt(this.value, 10) || null; })
                        .get().filter(Boolean);
                }

                function getSelectedTagIds() {
                    return $('input.tag-filter-checkbox[name="tag_p_filter[]"]:checked')
                        .map(function () { return parseInt(this.value, 10) || null; })
                        .get().filter(Boolean);
                }

                function getSearchTerm() {
                    return $searchInput.length ? String($searchInput.val() || '').trim() : '';
                }

                function getMinPrice() {
                    if (!$minInput.length) return '';
                    const v = String($minInput.val() || '').replace(',', '.');
                    const n = parseFloat(v);
                    return isNaN(n) ? '' : n.toFixed(2);
                }

                function getMaxPriceInput() {
                    if (!$maxInput.length) return '';
                    const v = String($maxInput.val() || '').replace(',', '.');
                    const n = parseFloat(v);
                    return isNaN(n) ? '' : n.toFixed(2);
                }

                // ---- Infinite scroll (con bloqueo seguro) ----
                const observer = new IntersectionObserver(entries => {
                    entries.forEach(e => {
                        if (!e.isIntersecting) return;
                        if (blockAuto) return;       // <- clave: no dispares si estamos recalculando
                        if (loading || finished) return;
                        loadProducts(false);
                    });
                }, { rootMargin: '320px 0px' });

                function pauseInfinite() {
                    try { observer.disconnect(); } catch (e) { }
                }
                function resumeInfinite() {
                    try { observer.observe(sent.get(0)); } catch (e) { }
                }

                function loadProducts(reset = false, ignorePrice = false) {
                    if (loading) return;

                    // Si ignoramos el precio (porque cambió cat/tag), no enviar min/max
                    const minP = ignorePrice ? '' : getMinPrice();
                    const maxP = ignorePrice ? '' : getMaxPriceInput();

                    loading = true;
                    setFiltersDisabled(true);
                    noRes.hide();
                    loader.show();

                    let previousCounter = counterProducts.text(); // Guardar el valor actual del contador de productos

                    if (reset) {
                        page = 1;
                        finished = false;
                        list.empty();
                        resumeInfinite();
                    }

                    // Comprobar si hay un giroSlug
                    if (giroSlug == '') {
                        req = $.ajax({
                            url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
                            type: "POST",
                            data: {
                                action: "cm_load_products",
                                page: page,
                                posts_per_page: perPage,
                                parent_slug: parentSlug,
                                orderby: orderby,
                                child_ids: getSelectedChildIds(),
                                tag_ids: getSelectedTagIds(),
                                s: getSearchTerm(),
                                min_price: minP,
                                max_price: maxP,
                            }
                        })
                            .done(function (res) {
                                if (res && res.success) {
                                    let counterStatus = false;
                                    if (reset) list.empty();
                                    if (res.data.html) {
                                        list.append(res.data.html);
                                        page++;
                                    } else {
                                        finished = true;
                                        observer && observer.disconnect();
                                        noRes.show();
                                        counterStatus = true;
                                    }

                                    // Si no se encontraron más productos
                                    if (counterStatus) {
                                        counterProducts.text(previousCounter);  // Mantener el contador previo
                                    } else {
                                        if (res.data.total) {
                                            counterProducts.text(res.data.total);
                                        } else {
                                            counterProducts.text(previousCounter);  // Mantener el contador previo si no hay más productos
                                        }
                                    }
                                }
                            })
                            .fail(function (xhr, status) {
                                if (status !== 'abort') {
                                    console.warn("Error al cargar productos.", status);
                                    noRes.show();
                                }
                            })
                            .always(function () {
                                loader.hide();
                                loading = false;
                                setFiltersDisabled(false);
                                if (pendingReset) {
                                    pendingReset = false;
                                    loadProducts(true, true);
                                }
                            });
                    } else {
                        req = $.ajax({
                            url: "<?php echo esc_url(admin_url('admin-ajax.php')); ?>",
                            type: "POST",
                            data: {
                                action: "cm_load_products_giros",
                                page: page,
                                posts_per_page: perPage,
                                giro_slug: giroSlug,
                                orderby: orderby,
                                parents_ids: getSelectedChildIds(),
                                tag_ids: getSelectedTagIds(),
                                s: getSearchTerm(),
                                min_price: minP,
                                max_price: maxP,
                            }
                        })
                            .done(function (res) {
                                if (res && res.success) {
                                    let counterStatus = true;
                                    if (reset) list.empty();
                                    if (res.data.html) {
                                        list.append(res.data.html);
                                        page++;
                                    } else {
                                        finished = true;
                                        observer && observer.disconnect();
                                        noRes.show();
                                        counterStatus = false;
                                    }

                                    // Si no se encontraron más productos
                                    if (counterStatus) {
                                        counterProducts.text(res.data.total || previousCounter); // Mantener el contador previo si no hay más productos
                                    } else {
                                        counterProducts.text(previousCounter); // Mantener el contador previo
                                    }
                                }
                            })
                            .fail(function (xhr, status) {
                                if (status !== 'abort') {
                                    console.warn("Error al cargar productos.", status);
                                    noRes.show();
                                }
                            })
                            .always(function () {
                                loader.hide();
                                loading = false;
                                setFiltersDisabled(false);
                                if (pendingReset) {
                                    pendingReset = false;
                                    loadProducts(true, true);
                                }
                            });
                    }
                }


                // ---- Debounce util ----
                const debounce = (fn, ms = 300) => {
                    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
                };

                // ---- Search events ----
                if ($searchForm.length) {
                    $searchForm.on('submit', function (e) {
                        e.preventDefault();
                        if (loading) { pendingReset = true; return; }
                        blockAuto = true;          // evita disparo del observer entre medio
                        // actualizamos display de filtros
                        updateFiltersDisplay();
                        pauseInfinite();
                        list.empty();
                        loadProducts(true);
                        blockAuto = false;
                        // Establecer en 0 el total
                        counterProducts.text('0');
                    });
                }

                // ---- Price widget events ----
                if ($priceForm.length) {
                    $priceForm.on('submit', function (e) {
                        e.preventDefault();
                        if (loading) { pendingReset = true; return; }
                        blockAuto = true;
                        // actualizamos display de filtros
                        updateFiltersDisplay();
                        pauseInfinite();
                        list.empty();
                        loadProducts(true);
                        blockAuto = false;
                        // Establecer en 0 el total
                        counterProducts.text('0');
                    });
                }

                function applyMaxPriceToWidget(maxPrice) {
                    const $widget = $('li.widget_price_filter');
                    const $amount = $widget.find('.price_slider_amount');
                    const $slider = $widget.find('.price_slider');
                    const $min = $('#min_price');
                    const $max = $('#max_price');

                    const step = 0.1; // 1 si no quieres centavos
                    const min = parseFloat($min.val() || $amount.data('min') || 0);
                    const max = parseFloat(maxPrice);

                    const snap = v => Math.round(v / step) * step;

                    $max.val(max.toFixed(2)).attr('data-max', max);
                    $amount
                        .data('max', max).attr('data-max', max)
                        .data('step', step).attr('data-step', step);

                    if ($slider.data('uiSlider')) {
                        const clampedMin = Math.min(snap(min), max);
                        $slider.slider('option', 'max', max);
                        $slider.slider('option', 'step', step);
                        $slider.slider('values', [clampedMin, snap(max)]);
                    }

                    const formatMoney = n => '$' + (Number(n).toFixed(2));
                    $amount.find('.to').text(formatMoney(max));

                    $(document.body).trigger('price_slider_change', [min, max]);
                    $(document.body).trigger('price_slider_updated', [min, max]);
                }

                // ---- Inicial: observar y cargar (sin recalcular max aún) ----
                resumeInfinite();
                loadProducts(true);

                // ---- Cuando cambian categorías / tags ----
                $(document).on('change', filtersSelector, function () {
                    if (loading) { pendingReset = true; return; }

                    // Establecer en 0 el total
                    counterProducts.text('0');

                    // actualizamos display de filtros
                    updateFiltersDisplay();

                    // Bloquear auto-carga y observer mientras recalculamos
                    blockAuto = true;
                    pauseInfinite();
                    noRes.hide();
                    list.empty();
                    loader.show();

                    // limpia los inputs del widget de precio para que no “contaminen” la request
                    if ($minInput.length) $minInput.val('');
                    if ($maxInput.length) $maxInput.val('');

                    // el backend devolverá el nuevo max y luego lo aplicamos
                    loadProducts(true, true);
                    blockAuto = false;
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    add_filter('woocommerce_price_filter_widget_max_amount', function ($max) {
        $custom = cm_ajax_get_max_price();
        return ($custom > 0) ? $custom : $max;
    });

    add_filter('woocommerce_price_filter_widget_step', function ($step, $instance = []) {
        return 0.1;
    }, 10, 2);

    // Forzar el valor mínimo del filtro de precio a 0 en el widget de WooCommerce
    add_filter('woocommerce_price_filter_widget_min_amount', function ($min) {
        return 0;
    });
}

add_action('wp_footer', 'mostrar_alert_valor_seleccionado');

function mostrar_alert_valor_seleccionado()
{
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            // Selecciona el elemento <select> por su ID
            var selectElement = document.getElementById('wcmlim_change_lc_to');

            // Verifica si el elemento existe en la página
            if (selectElement) {
                // Obtiene el valor seleccionado
                var selectedValue = selectElement.options[selectElement.selectedIndex].value;

                // Muestra el valor en un alert al cargar la página
                alert("Valor seleccionado: " + selectedValue);

                // Opcional: Escucha cambios en el select y muestra el nuevo valor seleccionado
                selectElement.addEventListener('change', function () {
                    alert("Nuevo valor seleccionado: " + this.value);
                });
            }
        });
    </script>
    <?php
}


/*** 
 * finger print
 */

function enqueue_fingerprintjs_script()
{
    // Encola FingerprintJS
    wp_enqueue_script('fingerprintjs', 'https://cdn.jsdelivr.net/npm/@fingerprintjs/fingerprintjs@3/dist/fp.min.js', array(), null, true);

    // Encola tu propio script para capturar la huella digital
    //  wp_enqueue_script('custom-device-id', get_template_directory_uri() . '/js/custom-device-id.js', array('jquery', 'fingerprintjs'), null, true);

    // Pasa la URL de admin-ajax.php a tu script personalizado
    wp_localize_script('custom-device-id', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_fingerprintjs_script');

function enqueue_loading_script()
{
    ?>
    <script type="text/javascript">
        /*
        jQuery(document).on({
            ajaxStart: function(event, xhr, settings) {
                // Operaciones que suelen tardar más
                const longOperations = ['update_cart', 'checkout', 'calculate_shipping'];
 
                // Mostrar el loading solo para operaciones largas
                if (longOperations.some(operation => settings.url.includes(operation))) {
                    jQuery('#msgLoading').show();
                }
            },
            ajaxStop: function() {
                jQuery('#msgLoading').hide();
            }
        });*/
    </script>
    <?php
}
add_action('wp_footer', 'enqueue_loading_script');

function desactivar_cambio_tienda_checkout()
{
    if (is_page(['checkout', 'pago-conekta'])) { // Reemplaza con el slug de tu página de pago si es necesario
        ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function () {
                function modificarEnlace() {
                    var element = document.querySelector('.elementor-widget-container a.material-wos');
                    if (element) {
                        // Elimina la parte "- (Cambiar tienda)" del texto
                        element.textContent = element.textContent.replace(' - (Cambiar tienda)', ' - En este punto no se puede cambiar de tienda.');

                        // Desactiva el enlace
                        element.style.pointerEvents = 'none'; // No permitirá clics
                        element.style.cursor = 'default'; // Cambia el cursor para indicar que no es clicable
                    }
                }

                // Llamada inicial
                modificarEnlace();

                // Configura la ejecución repetida cada minuto (60000 ms)
                let contador = 0;
                const intervalo = setInterval(function () {
                    if (contador >= 4) {
                        clearInterval(intervalo); // Detiene el intervalo después de cuatro ejecuciones
                    } else {
                        modificarEnlace();
                        contador++;
                    }
                }, 5000); // 60000 ms = 1 minuto
            });
        </script>

        <?php
    }
}
add_action('wp_footer', 'desactivar_cambio_tienda_checkout', 0); // Establece una prioridad alta



function cambiar_slug_product_tag($args, $taxonomy = 'product_tag')
{
    // Verifica si se está aplicando a la taxonomía 'product_tag'
    if ($taxonomy === 'product_tag') {
        $args['rewrite'] = array('slug' => 'giros');
    }
    return $args;
}
add_filter('woocommerce_taxonomy_args_product_tag', 'cambiar_slug_product_tag', 10, 2);


/***
 * Está funcion se utilizo para crear giros via api rest ya que no me dejaba via php xD
 */
function crear_tags_productos()
{
    // Token de autenticación Bearer
    $bearer_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2Nhcm5lbWFydC5teXN0YWdpbmd3ZWJzaXRlLmNvbSIsImlhdCI6MTcyMjk0NDU3OCwibmJmIjoxNzIyOTQ0NTc4LCJleHAiOjE3MjM1NDkzNzgsImRhdGEiOnsidXNlciI6eyJpZCI6IjEifX19.Bqe-vWUbIwgs3x7TETDlBs1rzgn0-cD2zY_8Bhv1Sa8';
    //API URL 
    $api_multiservicio_create_url = get_option('custom_plugin_api_multiservicio_create_url', '');

    $site_url = $$api_multiservicio_create_url;
    // Datos de las etiquetas
    $tags = [
        ['data-title' => 'Fondas o Cocinas', 'value' => '70'],
        ['data-title' => 'Hamburguesas', 'value' => '73'],
        ['data-title' => 'Tortas', 'value' => '76'],
        ['data-title' => 'Pollerías', 'value' => '79'],
        ['data-title' => 'Ocasión de Consumo', 'value' => '82'],
        ['data-title' => 'Bares y cantinas', 'value' => '85'],
        ['data-title' => 'Guarderías', 'value' => '88'],
        ['data-title' => 'Carnitas', 'value' => '91'],
        ['data-title' => 'Tendero o Cremerias', 'value' => '94'],
        ['data-title' => 'Carnicería', 'value' => '97'],
        ['data-title' => 'Distribuidor', 'value' => '100'],
        ['data-title' => 'Tianguis', 'value' => '103'],
        ['data-title' => 'Hoteles', 'value' => '106'],
        ['data-title' => 'Burreros', 'value' => '109'],
        ['data-title' => 'Ferias', 'value' => '112'],
        ['data-title' => 'Dependencia de Gobierno', 'value' => '115'],
        ['data-title' => 'Expendio de Pescados y Mariscos', 'value' => '118'],
        ['data-title' => 'Sushi', 'value' => '121'],
        ['data-title' => 'Snack', 'value' => '124'],
        ['data-title' => 'Eloteros', 'value' => '127'],
        ['data-title' => 'Minisuper', 'value' => '130'],
        ['data-title' => 'Barcos', 'value' => '133'],
        ['data-title' => 'Hospitales', 'value' => '136'],
        ['data-title' => 'Cafeterías', 'value' => '139'],
        ['data-title' => 'Birriería o Barbacoa', 'value' => '142'],
        ['data-title' => 'Menudería', 'value' => '145'],
        ['data-title' => 'Tamales', 'value' => '148'],
    ];

    foreach ($tags as $tag) {
        $nombre = $tag['data-title'];
        $descripcion = $tag['value'];
        $slug = sanitize_title($nombre);

        // Datos a enviar en la solicitud
        $data = [
            'name' => $nombre,
            'slug' => $slug,
            'description' => $descripcion
        ];

        // Configuración de la solicitud HTTP
        $response = wp_remote_post($site_url, [
            'method' => 'POST',
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $bearer_token,
                'Content-Type' => 'application/json',
            ],
        ]);

        // Manejo de la respuesta
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Error al crear la etiqueta '{$nombre}': {$error_message}<br>";
        } else {
            echo "Etiqueta creada: {$nombre}<br>";
        }
    }
}

//para inventarios


// Function to get all products from WooCommerce and their SKUs
function obtener_productos_woocommerce($page = 1, $per_page = 100)
{
    $args = [
        "post_type" => "product",
        "posts_per_page" => $per_page,
        "paged" => $page,
        "post_status" => "publish",
    ];
    $productos = get_posts($args);
    $productos_sku = [];

    foreach ($productos as $producto) {
        $product_obj = wc_get_product($producto->ID);
        if ($product_obj) {
            $sku = $product_obj->get_sku();
            if (!empty($sku)) {
                $productos_sku[] = $sku;
            }
        }
    }

    // Check if there are more pages
    $total_pages = ceil(wp_count_posts("product")->publish / $per_page);
    $has_more_pages = $page < $total_pages;

    return [
        "productos_sku" => $productos_sku,
        "has_more_pages" => $has_more_pages,
        "total" => $total_pages,
    ];
}

add_action("wp_ajax_mostrar_stock_centros", "mostrar_stock_centros_js");
add_action("wp_ajax_nopriv_mostrar_stock_centros", "mostrar_stock_centros_js");
add_action("wp_ajax_actualizar_stock", "actualizar_stock_js");
add_action("wp_ajax_nopriv_actualizar_stock", "actualizar_stock_js");

// Shortcode function to check stock in specified centers
function mostrar_stock_centros_js()
{
    // Medir el tiempo de inicio
    $start_time = microtime(true);
    $page = $_REQUEST["page"];
    $per_page = $_REQUEST["per_page"];
    /*$page = 1;
     $per_page = 10; //productos por llamada*/
    $product_data = obtener_productos_woocommerce($page, $per_page);
    // Enviar la respuesta como JSON
    wp_send_json($product_data);
    die();
}

// Function to consult stock from the API with authentication
function consultar_stock($centro, $producto)
{
    //API URL
    $api_multiservicio_stock_url = get_option('custom_plugin_api_multiservicio_stock_url', '');
    $url = $api_multiservicio_stock_url;
    $args = [
        "method" => "GET",
    ];

    $response = wp_remote_request($url, $args);
    if (is_wp_error($response)) {
        return false;
    }
    $body = wp_remote_retrieve_body($response);
    // Decode JSON response to an associative array
    $data = json_decode($body, true);
    return $data;
}



function actualizar_stock_js()
{
    // Process the SKUs retrieved for this page
    $productos = $_REQUEST["datos"];
    // Convertir la cadena en un array
    $array_productos = explode(",", $productos);
    $totalProductos = count($array_productos);
    $centro = "M018";
    $logger = new WC_Logger();
    for ($i = 0; $i <= $totalProductos - 1; $i++) {
        $sku = $array_productos[$i];
        $stock_data = consultar_stock($centro, $sku);
        if (
            $stock_data &&
            isset($stock_data["result"]["IT_STOCK"][0]["LABST"])
        ) {
            $stock = $stock_data["result"]["IT_STOCK"][0]["LABST"];
            // Obtiene el ID del producto a partir del SKU
            $product_id = wc_get_product_id_by_sku($sku);
            echo $product_id;
            echo "producto id";

            if ($product_id) {
                // Obtiene el objeto del producto
                $product = wc_get_product($product_id);

                if ($product) {
                    // Verifica si el inventario está habilitado
                    if (!$product->managing_stock()) {
                        // Activa la gestión de inventario
                        update_post_meta($product_id, "_manage_stock", "yes");
                        // Opcional: Establecer la cantidad inicial de stock (puede ser 0)
                        update_post_meta($product_id, "_stock", $stock);
                        echo "se actualiza stock managemet";
                    }

                    if ($product && $product->is_type("simple")) {
                        // Actualiza el stock para productos simples
                        // var_dump(wc_update_product_stock($product_id, $stock));
                        //  echo "es simple";
                    } elseif ($product && $product->is_type("variable")) {
                        // Para productos variables, actualiza el stock de todas las variaciones
                        $available_variations = $product->get_available_variations();
                        foreach ($available_variations as $variation) {
                            $variation_id = $variation["variation_id"];
                            wc_update_product_stock($variation_id, $stock);
                        }
                        echo "es variable";
                    }
                    // Opcional: Actualiza el meta dato de stock para asegurar que WooCommerce lo detecte correctamente
                    update_post_meta($product_id, "_stock", $stock);

                    echo "se actualiza";

                    $logger->info(
                        "Se actualizó producto SKU $sku en Centro: $centro con Stock: $stock ",
                        [
                            "source" => "Actualización de Stock Automática",
                        ]
                    );
                } else {
                    $logger->warning(
                        "Producto $sku en Centro: $centro con Stock: $stock no existente en sistema ",
                        [
                            "source" => "Actualización de Stock Automática",
                        ]
                    );
                }
            }
        } else {
            echo "0";
        }
    }
    wp_die();
}



// Agregar script personalizado a la página del carrito en WordPress
function custom_shipping_script()
{
    if (is_cart()) { // Verifica que estamos en la página del carrito
        ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function () {
                // Selecciona el input y el texto de descripción
                const envioDomicilioInput = document.querySelector('input[type="radio"][value="flat_rate:8"]');
                const envioTexto = document.querySelector('.wc-block-components-shipping-rates-control__package__description--free');

                // Selecciona los elementos a esconder
                const envioLabel = document.querySelector('.wc-block-components-totals-item__label');
                const envioValue = document.querySelector('.wc-block-components-totals-item__value');
                const envioDescription = document.querySelector('.wc-block-components-totals-item__description.wc-block-components-totals-shipping__via');

                if (envioDomicilioInput && envioTexto && envioLabel && envioValue && envioDescription) {
                    // Función para actualizar el texto y visibilidad de los elementos
                    function actualizarEnvio() {
                        if (envioDomicilioInput.checked) {
                            envioTexto.textContent = "se calculará el envío en la siguiente pantalla";
                            envioLabel.style.display = "none";
                            envioValue.style.display = "none";
                            envioDescription.style.display = "none";
                        } else {
                            envioTexto.textContent = "Gratis";
                            envioLabel.style.display = "block";
                            envioValue.style.display = "block";
                            envioDescription.style.display = "block";
                        }
                    }

                    // Escuchar el evento de cambio en el radio button
                    envioDomicilioInput.addEventListener("change", actualizarEnvio);

                    // Verificar el estado inicial
                    actualizarEnvio();
                }
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'custom_shipping_script');


/***
 * endpoint para login bafar
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/token', array(
        'methods' => 'POST',
        'callback' => 'generate_wp_login_token',
        'permission_callback' => '__return_true',
    ));
});

function generate_wp_login_token(WP_REST_Request $request)
{
    $username = $request->get_param('username');
    $password = $request->get_param('password');

    // Hacer la solicitud al endpoint JWT Authentication directamente
    $response = wp_remote_post(rest_url('/jwt-auth/v1/token'), array(
        'body' => array(
            'username' => $username,
            'password' => $password
        ),
    ));

    if (is_wp_error($response)) {
        return new WP_Error('authentication_failed', 'Error connecting to the authentication service', array('status' => 500));
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['token'])) {
        echo '"' . $body['token'] . '"';
        exit;
    } else {
        return new WP_Error('token_generation_failed', 'Invalid credentials or token generation failed', array('status' => 403));
    }
}





/*****
 * API's externa
 * 
 */

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/order/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_custom_order_data',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
});

function get_custom_order_data($data)
{
    $order_id = $data['id'];

    $order = wc_get_order($order_id);

    if (!$order) {
        // Si no se encuentra la orden en WooCommerce, consultar Yalo
        $yalo_auth_url = YALO_URL;
        $yalo_username = YALO_USERNAME;
        $yalo_password = YALO_PASSWORD;

        // Autenticación para obtener el token JWT
        $auth_response = wp_remote_post($yalo_auth_url, array(
            'body' => array(
                'username' => $yalo_username,
                'password' => $yalo_password,
            ),
        ));

        if (is_wp_error($auth_response)) {
            return new WP_Error('api_error', 'Error authenticating with Yalo API', array('status' => 500));
        }

        $auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
        if (empty($auth_data['token'])) {
            return new WP_Error('api_error', 'Failed to retrieve token from Yalo API', array('status' => 500));
        }

        $token = $auth_data['token'];

        // Consulta la API de Yalo WooCommerce para buscar la orden

        $yalo_order_url = YALO_URL_ORDEN . strval($order_id);
        $order_response = wp_remote_get($yalo_order_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
            ),
        ));


        if (is_wp_error($order_response)) {
            return new WP_Error('api_error', 'Error fetching order from Yalo API', array('status' => 500));
        }

        $order_data = json_decode(wp_remote_retrieve_body($order_response), true);
        echo json_encode($order_data);
        die();
    } else {
        // Si la orden existe en WooCommerce, continúa con la lógica
        $transaction_id = $order->get_meta('transaction_id');
        $order_data = $order->get_data();
    }

    if ($order_data["data"]["status"] == "404") {
        return new WP_Error('api_error', 'Orden no encontrada ni en Woo ni en Yalo', array('status' => 500));
    }

    if (isset($_GET['idStatus']) && $_GET['idStatus'] == "018") {
        // Verificar si la orden está en estado 'processing'
        if ($order_data['status'] == "processing") {
            // Inicializamos las variables para verificar el pago y el procesamiento de la réplica
            $conektaPagado = null;
            $replicaProcesada = null;

            // Recorremos los metadatos
            foreach ($order_data['meta_data'] as $meta) {
                if ($meta->key === 'conekta_pagado' && $meta->value == '1') {
                    $conektaPagado = true; // Conexión de pago de Conekta confirmada
                }

                if ($meta->key === 'replica_procesada' && $meta->value == '1') {
                    $replicaProcesada = true; // La réplica ha sido procesada
                }
            }

            // Ahora verificamos si ambos valores son verdaderos
            if ($conektaPagado && $replicaProcesada) {
                // Cambiamos el estado del pedido a completado
                $order->update_status('completed');

                // Definimos la respuesta
                $response = [
                    'message' => 'El pedido ha sido marcado como completado.',
                    'status' => 'success'
                ];
            } else {
                // En caso de que falten datos o no estén procesados completamente
                $response = [
                    'message' => 'Faltan datos o no se han procesado completamente.',
                    'status' => 'error'
                ];
            }

            // Devolver la respuesta
            return rest_ensure_response($response);
        } else {
            if ($order_data['status'] == "completed") {
                // Si el pedido esta en estado 'completed'
                $response = [
                    'message' => 'El pedido ya esta en estatus completado',
                ];
            } else {
                // Si el pedido no está en estado 'processing'
                $response = [
                    'message' => 'El pedido no está en estatus procesando',
                ];
            }

            // Devolver la respuesta
            return rest_ensure_response($response);
        }
    } else {
        $response = array(
            "base_currency_code" => get_woocommerce_currency(),
            "base_discount_amount" => $order->get_discount_total(),
            "base_discount_invoiced" => 0, // WooCommerce no tiene este campo directamente
            "base_grand_total" => $order->get_total(),
            "base_discount_tax_compensation_amount" => 0, // Campo no estándar en WooCommerce
            "base_discount_tax_compensation_invoiced" => 0, // Campo no estándar
            "base_shipping_amount" => $order->get_shipping_total(),
            "base_shipping_discount_amount" => 0, // WooCommerce no tiene este campo
            "base_shipping_discount_tax_compensation_amnt" => 0, // Campo no estándar
            "base_shipping_incl_tax" => $order->get_shipping_total() + $order->get_shipping_tax(),
            "base_shipping_invoiced" => 0, // Campo no estándar
            "base_shipping_tax_amount" => $order->get_shipping_tax(),
            "base_subtotal" => $order->get_subtotal(),
            "base_subtotal_incl_tax" => $order->get_subtotal() + $order->get_total_tax(),
            "base_subtotal_invoiced" => 0, // Campo no estándar
            "base_tax_amount" => $order->get_total_tax(),
            "base_tax_invoiced" => 0, // Campo no estándar
            "base_total_due" => 0, // Campo no estándar
            "base_total_invoiced" => 0, // Campo no estándar
            "base_total_invoiced_cost" => 0, // Campo no estándar
            "base_total_paid" => $order->get_total(),
            "base_to_global_rate" => 1, // Asume que es la misma moneda
            "base_to_order_rate" => 1, // Asume que es la misma moneda
            "billing_address_id" => $order_data['billing']['id'] ?? null,
            "created_at" => $order->get_date_created()->date('Y-m-d H:i:s'),
            "customer_email" => $order_data['billing']['email'],
            "customer_firstname" => $order_data['billing']['first_name'],
            "customer_gender" => null, // Campo no estándar en WooCommerce
            "customer_group_id" => null, // Campo no estándar en WooCommerce
            "customer_id" => $order->get_customer_id(),
            "customer_is_guest" => $order->get_customer_id() ? 0 : 1,
            "customer_lastname" => $order_data['billing']['last_name'],
            "customer_note_notify" => 1,
            "discount_amount" => $order->get_discount_total(),
            "discount_invoiced" => 0, // Campo no estándar
            "email_sent" => $order->get_meta('_email_sent', true), // Modificar si tienes un campo personalizado
            "entity_id" => $order->get_id(),
            "ext_order_id" => $order->get_order_key(),
            "global_currency_code" => get_woocommerce_currency(),
            "grand_total" => $order->get_total(),
            "discount_tax_compensation_amount" => 0, // Campo no estándar
            "discount_tax_compensation_invoiced" => 0, // Campo no estándar
            "increment_id" => $order->get_order_number(),
            "is_virtual" => $order->get_meta('_is_virtual', true), // Verificar con productos
            "order_currency_code" => $order->get_currency(),
            "protect_code" => md5($order->get_order_key()),
            "quote_id" => null, // WooCommerce no utiliza este campo
            "remote_ip" => $order->get_customer_ip_address(),
            "shipping_amount" => $order->get_shipping_total(),
            "shipping_description" => $order->get_shipping_method(),
            "shipping_discount_amount" => 0, // Campo no estándar
            "shipping_discount_tax_compensation_amount" => 0, // Campo no estándar
            "shipping_incl_tax" => $order->get_shipping_total() + $order->get_shipping_tax(),
            "shipping_invoiced" => 0, // Campo no estándar
            "shipping_tax_amount" => $order->get_shipping_tax(),
            "state" => $order->get_status(),
            "status" => $order->get_status(),
            "store_currency_code" => get_woocommerce_currency(),
            "store_id" => get_current_blog_id(),
            "store_name" => get_bloginfo('name'),
            "store_to_base_rate" => 1,
            "store_to_order_rate" => 1,
            "subtotal" => $order->get_subtotal(),
            "subtotal_incl_tax" => $order->get_subtotal() + $order->get_total_tax(),
            "subtotal_invoiced" => 0, // Campo no estándar
            "tax_amount" => $order->get_total_tax(),
            "tax_invoiced" => 0, // Campo no estándar
            "total_invoiced" => 0, // Campo no estándar
            "total_item_count" => $order->get_item_count(),
            "total_paid" => $order->get_total(),
            "total_qty_ordered" => array_sum(wp_list_pluck($order->get_items(), 'quantity')),
            "updated_at" => $order->get_date_modified()->date('Y-m-d H:i:s'),


            // Items
            "items" => array_map(function ($item) use ($order) {
                $product = $item->get_product(); // Obtén el producto asociado
                return array(
                    "name" => $item->get_name(),
                    "order_id" => $order->get_id(), // ID de la orden
                    "original_price" => $item->get_total() / $item->get_quantity(), // Precio original
                    "price" => $item->get_total() / $item->get_quantity(), // Precio total
                    "qty_ordered" => $item->get_quantity(), // Cantidad pedida
                    "row_total" => $item->get_total(), // Total del artículo
                    "sku" => $product && $product->get_sku() ? $product->get_sku() : 'N/A', // Devuelve 'N/A' si no hay SKU                    "base_row_total" => $item->get_subtotal(), // Subtotal base
                    "base_row_total" => $item->get_subtotal(), // Subtotal base
                    "row_total_incl_tax" => $item->get_total() + $item->get_total_tax(), // Total incluyendo impuestos
                    "base_row_total_incl_tax" => $item->get_subtotal() + $item->get_total_tax(), // Subtotal base incluyendo impuestos
                );
            }, $order->get_items()),

            // Payment Information
            "payment" => array(
                "method" => $payment_method,
                "additional_information" => array(
                    "Método de pago: " . $order->get_payment_method_title()
                )
            ),

            "payment_additional_info" => array(
                array(
                    "key" => "quote_id",
                    "value" => $order->get_meta('_quote_id') // Cambiar según sea necesario
                ),
                array(
                    "key" => "payment_method",
                    "value" => $payment_method
                ),
                array(
                    "key" => "iframe_payment",
                    "value" => "1" // Asumido como un valor estático, modificar si es necesario
                ),
                array(
                    "key" => "order_id",
                    "value" => $order->get_order_key()
                ),
                array(
                    "key" => "txn_id",
                    "value" => $transaction_id
                ),
                array(
                    "key" => "method_title",
                    "value" => $order->get_payment_method_title()
                ),
                array(
                    "key" => "additional_data",
                    "value" => json_encode(array(
                        "cc_type" => "visa", // Aquí puedes usar la información real del pedido si está disponible
                        "cc_last_4" => $order->get_meta('_cc_last4') // Cambiar meta key según tu configuración
                    ))
                )
            ),

            // Extension Attributes
            "extension_attributes" => array(
                "shipping_assignments" => array(
                    array(
                        "shipping" => array(
                            "address" => array(
                                "address_type" => "shipping",
                                "city" => $order_data['shipping']['city'],
                                "country_id" => $order_data['shipping']['country'],
                                "email" => $order_data['shipping']['email'],
                                "entity_id" => $order_data['shipping']['id'] ?? null,
                                "firstname" => $order_data['shipping']['first_name'],
                                "lastname" => $order_data['shipping']['last_name'],
                                "parent_id" => $order->get_id(),
                                "postcode" => $order_data['shipping']['postcode'],
                                "region" => $order_data['shipping']['state'],
                                "region_code" => $order_data['shipping']['region_code'],
                                "region_id" => $order_data['shipping']['region_id'] ?? null,
                                "street" => $order_data['shipping']['address_1'],
                                "telephone" => $order_data['shipping']['phone']
                            ),
                            "method" => $order->get_shipping_method(),
                            "total" => array(
                                "base_shipping_amount" => $order->get_shipping_total(),
                                "base_shipping_discount_amount" => 0,
                                "base_shipping_discount_tax_compensation_amnt" => 0,
                                "base_shipping_incl_tax" => $order->get_shipping_total() + $order->get_shipping_tax(),
                                "base_shipping_invoiced" => 0,
                                "base_shipping_tax_amount" => $order->get_shipping_tax(),
                                "shipping_amount" => $order->get_shipping_total(),
                                "shipping_discount_amount" => 0,
                                "shipping_discount_tax_compensation_amount" => 0,
                                "shipping_incl_tax" => $order->get_shipping_total() + $order->get_shipping_tax(),
                                "shipping_invoiced" => 0,
                                "shipping_tax_amount" => $order->get_shipping_tax()
                            )
                        ),
                        // Items
                        "items" => array_map(function ($item) use ($order) {
                            $product = $item->get_product(); // Obtén el producto asociado
                            return array(
                                "name" => $item->get_name(),
                                "order_id" => $order->get_id(), // ID de la orden
                                "original_price" => $item->get_total() / $item->get_quantity(), // Precio original
                                "price" => $item->get_total() / $item->get_quantity(), // Precio total
                                "qty_ordered" => $item->get_quantity(), // Cantidad pedida
                                "row_total" => $item->get_total(), // Total del artículo
                                "sku" => $product && $product->get_sku() ? $product->get_sku() : 'N/A', // Devuelve 'N/A' si no hay SKU                    "base_row_total" => $item->get_subtotal(), // Subtotal base
                                "row_total_incl_tax" => $item->get_total() + $item->get_total_tax(), // Total incluyendo impuestos
                                "base_row_total_incl_tax" => $item->get_subtotal() + $item->get_total_tax(), // Subtotal base incluyendo impuestos
                            );
                        }, $order->get_items()),
                    )
                ),
                "pickup_location_code" => $order->get_meta('_pickup_location_code'),
                "transaction_id" => "67996b053a0aa2001d680efe",
                "authorization_number" => $order->get_meta("conekta-order-id")
            ),
        );
        return rest_ensure_response($response);
    }
}



add_action('rest_api_init', function () {
    // Hook para capturar las solicitudes REST de WooCommerce antes de los callbacks.
    //add_action('woocommerce_rest_request_before_callbacks', 'log_woocommerce_api_requests', 10, 3);
});

function log_woocommerce_api_requests($response, $handler, $request)
{
    // Solo capturar rutas específicas de WooCommerce
    if (strpos($request->get_route(), 'wc/') !== false) {
        // Instanciar WC_Logger
        $logger = new WC_Logger();

        // Preparar los datos de la solicitud
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'method' => $request->get_method(),
            'route' => $request->get_route(),
            'params' => json_encode($request->get_params()),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        ];

        // Convertir los datos en un mensaje legible
        $log_message = sprintf(
            "WooCommerce API Request:\nTimestamp: %s\nMethod: %s\nRoute: %s\nParams: %s\nIP: %s\nUser-Agent: %s",
            $log_entry['timestamp'],
            $log_entry['method'],
            $log_entry['route'],
            $log_entry['params'],
            $log_entry['ip'],
            $log_entry['user_agent']
        );

        // Registrar los datos bajo el contexto `woocommerce_api`
        $logger->info($log_message, ['source' => 'woocommerce_api']);
    }

    return $response; // Continuar con el procesamiento de la solicitud.
}


//alta de productos custom

add_action('rest_api_init', function () {
    // Añadir soporte para campos personalizados al endpoint de productos
    register_rest_field('product', 'custom_fields', [
        'get_callback' => null,
        'update_callback' => 'custom_update_product_fields',
        'schema' => [
            'description' => 'Campos personalizados para productos',
            'type' => 'object',
            'properties' => [
                'giros' => [
                    'description' => 'Taxonomía o etiquetas del producto, separadas por coma',
                    'type' => 'string',
                ],
                'incrementable' => [
                    'description' => 'Valor booleano o numérico para el meta _ri_quantity_step',
                    'type' => ['boolean', 'float'],
                ],
                'proceso' => [
                    'description' => 'Texto libre para meta personalizado',
                    'type' => 'string',
                ],
                'palabra_clave' => [
                    'description' => 'Palabra clave para SEO en Yoast',
                    'type' => 'string',
                ],
                'marca' => [
                    'description' => 'Texto de la marca del producto',
                    'type' => 'string',
                ],
                'presentacion' => [
                    'description' => 'Etiqueta para la presentación del producto',
                    'type' => 'string',
                ],
            ],
        ],
    ]);
});

function custom_update_product_fields($value, $object, $field_name)
{
    if (!is_object($object) || empty($value) || !is_array($value)) {
        return;
    }

    $product_id = $object->get_id();

    // Obtener datos enviados en la solicitud

    $descripciones = array_map('trim', explode(",", $value['giros']));


    $ids_etiquetas = [];

    foreach ($descripciones as $descripcion) {

        $args = array(
            'taxonomy' => 'product_tag',
            'hide_empty' => false

        );
        $etiquetas = get_terms($args);


        foreach ($etiquetas as $etiqueta) {
            if (strpos($etiqueta->description, $descripcion) !== false) {
                wp_set_object_terms($product_id, $etiqueta->term_id, 'product_tag', true);
            }
        }
    }


    // Procesar campo incrementable
    if (isset($value['incrementable'])) {
        $incrementable = is_bool($value['incrementable']) ? (int) $value['incrementable'] : $value['incrementable'];
        update_post_meta($product_id, '_ri_quantity_step', $incrementable);
    }

    // Procesar campo proceso
    if (!empty($value['proceso'])) {
        update_post_meta($product_id, 'proceso', sanitize_text_field($value['proceso']));
    }

    // Procesar campo palabra_clave
    if (!empty($value['palabra_clave'])) {
        update_post_meta($product_id, '_yoast_wpseo_focuskw', sanitize_text_field($value['palabra_clave']));
    }

    // Procesar campo marca
    if (!empty($value['marca'])) {
        update_post_meta($product_id, 'marca', sanitize_text_field($value['marca']));
    }

    // Procesar campo presentacion
    if (!empty($value['presentacion'])) {
        update_post_meta($product_id, '_ri_quantity_step_label', sanitize_text_field($value['presentacion']));
    }
}

/***
 * Para actualizar por SKU
 */

add_filter('rest_pre_dispatch', 'handle_product_update_by_sku', 10, 3);

function handle_product_update_by_sku($result, $server, $request)
{


    // Detectar si es una solicitud de productos y contiene un SKU en la URL
    if (strpos($request->get_route(), '/wc/v3/products/') !== false && $request->get_method() === 'PUT') {

        $sku = basename($request->get_route()); // Obtener el SKU de la URL

        // Validar si el SKU es un número (probablemente sea un ID) o texto

        // Buscar el producto por SKU
        $product_id = wc_get_product_id_by_sku($sku);

        if (!$product_id) {
            // Devolver error si no se encuentra el producto con ese SKU
            return new WP_Error(
                'woocommerce_rest_invalid_sku',
                __('No se encontró ningún producto con ese SKU.', 'woocommerce'),
                ['status' => 404]
            );
        }

        // Modificar la ruta para usar el ID en lugar del SKU
        $new_route = str_replace($sku, $product_id, $request->get_route());
        $request->set_route($new_route);
    }


    return $result;
}

add_filter('rest_endpoints', function ($endpoints) {
    if (isset($endpoints['/wp/v2/locations'])) {
        $endpoints['/wp/v2/locations'][0]['callback'] = 'custom_get_locations';
    }
    return $endpoints;
});
function custom_get_locations($request)
{
    // Obtén todos los términos de la taxonomía "locations".
    $locations = get_terms([
        'taxonomy' => 'locations',
        'hide_empty' => false, // Incluye términos sin publicaciones asignadas.
    ]);

    if (is_wp_error($locations)) {
        return new WP_Error('no_locations', __('No locations found'), ['status' => 404]);
    }

    $result = [];

    // Recorre cada término y construye la respuesta personalizada.
    foreach ($locations as $location) {
        // Obtén los metadatos personalizados del término.
        $meta = get_term_meta($location->term_id);

        $result[] = [
            'id' => $location->term_id,
            'count' => $location->count,
            'description' => $location->description,
            'link' => get_term_link($location),
            'name' => $location->name,
            'slug' => $location->slug,
            'taxonomy' => $location->taxonomy,
            'parent' => $location->parent,
            'meta' => [
                'wcmlim_street_address' => $meta['wcmlim_street_address'][0] ?? '',
                'wcmlim_city' => $meta['wcmlim_city'][0] ?? '',
                'wcmlim_postcode' => $meta['wcmlim_postcode'][0] ?? '',
                'wcmlim_country_state' => $meta['wcmlim_country_state'][0] ?? '',
                'wcmlim_email' => $meta['wcmlim_email'] ?? [],
                'wcmlim_phone' => $meta['wcmlim_phone'] ?? [],
                'wcmlim_location_priority' => $meta['wcmlim_location_priority'][0] ?? '',
                'wcmlim_start_time' => $meta['wcmlim_start_time'][0] ?? '',
                'wcmlim_end_time' => $meta['wcmlim_end_time'][0] ?? '',
                'wcmlim_street_number' => $meta['wcmlim_street_number'][0] ?? '',
                'wcmlim_route' => $meta['wcmlim_route'][0] ?? '',
                'wcmlim_locality' => $meta['wcmlim_locality'][0] ?? '',
                'wcmlim_administrative_area_level_1' => $meta['wcmlim_administrative_area_level_1'][0] ?? '',
                'wcmlim_postal_code' => $meta['wcmlim_postal_code'][0] ?? '',
                'wcmlim_country' => $meta['wcmlim_country'][0] ?? '',
                'wcmlim_lat' => $meta['wcmlim_lat'][0] ?? '',
                'wcmlim_lng' => $meta['wcmlim_lng'][0] ?? '',
                'wcmlim_locator' => $meta['wcmlim_locator'][0] ?? '',
            ],
            'centro_location' => get_term_meta($location->term_id, 'centro_location', true),
            'shared_catalog' => get_term_meta($location->term_id, 'shared_catalog', true) ?: '0', // Por defecto "0"
            'source' => get_term_meta($location->term_id, 'id_almacen', true) ?: 'N/A', // Por defecto "N/A"
            'customer_group' => get_term_meta($location->term_id, 'customer_group', true) ?: '0', // Por defecto "0"
            'url_maps' => get_term_meta($location->term_id, 'url_maps', true),
            'activo' => !empty(get_term_meta($location->term_id, 'centro_activo', true))
                ? get_term_meta($location->term_id, 'centro_activo', true)
                : 1, // Por defecto "activo" es 1 si no está definido.
            'acf' => [], // Si necesitas campos personalizados ACF, agrégalos aquí.
            '_links' => [
                'self' => [
                    [
                        'href' => rest_url('/wp/v2/locations/' . $location->term_id),
                        'targetHints' => ['allow' => ['GET']],
                    ],
                ],
                'collection' => [
                    [
                        'href' => rest_url('/wp/v2/locations'),
                    ],
                ],
                'about' => [
                    [
                        'href' => rest_url('/wp/v2/taxonomies/locations'),
                    ],
                ],
                'wp:post_type' => [
                    [
                        'href' => rest_url('/wp/v2/product?locations=' . $location->term_id),
                    ],
                ],
                'curies' => [
                    [
                        'name' => 'wp',
                        'href' => 'https://api.w.org/{rel}',
                        'templated' => true,
                    ],
                ],
            ],
        ];
    }

    return rest_ensure_response($result);
}

function agregar_tarjetas_guardadas_script()
{
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Selector del div objetivo
            const targetDiv = document.querySelector("figure.conekta-banner");

            // Verifica si el contenedor ya existe para evitar duplicados
            if (targetDiv && !document.querySelector(".saved-card-container")) {
                // Crea el contenedor para las tarjetas guardadas
                const savedCardContainer = document.createElement("div");
                savedCardContainer.className = "saved-card-container"; // Clase para identificarlo
                savedCardContainer.style.marginBottom = "100px"; // Margen inferior
                savedCardContainer.style.width = "50%"; // Ajusta el ancho al 50%
                savedCardContainer.style.margin = "0 auto"; // Centra el contenedor

                // Título para las tarjetas guardadas
                const title = document.createElement("h3");
                title.textContent = "Pagar con tarjeta guardada";
                title.style.fontSize = "16px";
                title.style.marginBottom = "10px";
                title.style.textAlign = "center"; // Centrar el título

                // Select para las tarjetas
                const select = document.createElement("select");
                select.style.width = "100%"; // Ajusta al ancho del contenedor
                select.style.padding = "10px";
                select.style.marginBottom = "10px";
                select.style.boxSizing = "border-box"; // Garantiza que se ajuste al contenedor

                // Agregar opción por defecto
                const defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.textContent = "Selecciona una tarjeta guardada";
                defaultOption.disabled = true;
                defaultOption.selected = true;
                select.appendChild(defaultOption);

                // Simulación de tarjetas guardadas
                const cards = [{
                    id: "1",
                    text: "Visa terminación 1234"
                },
                {
                    id: "2",
                    text: "Mastercard terminación 5678"
                },
                {
                    id: "3",
                    text: "Amex terminación 9876"
                },
                ];

                // Agrega tarjetas al select
                cards.forEach(card => {
                    const option = document.createElement("option");
                    option.value = card.id;
                    option.textContent = card.text;
                    select.appendChild(option);
                });

                // Botón para pagar
                const payButton = document.createElement("button");
                payButton.textContent = "Pagar con tarjeta guardada";
                payButton.style.width = "100%"; // Ajusta al ancho del contenedor
                payButton.style.padding = "10px";
                payButton.style.marginBottom = "30px";
                payButton.style.backgroundColor = "#007BFF"; // Azul diferente
                payButton.style.color = "white";
                payButton.style.border = "none";
                payButton.style.cursor = "pointer";
                payButton.style.boxSizing = "border-box"; // Garantiza que se ajuste al contenedor
                payButton.style.borderRadius = "5px"; // Opcional: bordes redondeados para mejor apariencia

                // Lógica para el botón de pago
                payButton.addEventListener("click", function () {
                    const selectedCard = select.value;
                    if (!selectedCard) {
                        alert("Por favor, selecciona una tarjeta.");
                    } else {
                        alert(`Pagando con: ${cards.find(card => card.id === selectedCard).text}`);
                        // Aquí va la lógica para procesar el pago
                    }
                });

                // Agregar elementos al contenedor
                savedCardContainer.appendChild(title);
                savedCardContainer.appendChild(select);
                savedCardContainer.appendChild(payButton);

                // Insertar el contenedor justo antes del div objetivo
                targetDiv.parentNode.insertBefore(savedCardContainer, targetDiv);
            }
        });
    </script>
    <?php
}