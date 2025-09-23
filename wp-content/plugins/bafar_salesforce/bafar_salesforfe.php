<?php
/**
 * Plugin Name:       Bafar :: Salesforce 🎫
 * Plugin URI:        https://sparklabs.com.mx/utilities/plugins/carnemart-connect
 * Description:       Utilización de API de Salesforce para registrar el usuario y obtener numero de cliente
 * Version:           1.0.0
 * Author:            Sergio Nava @ Sparklabs
 * Author URI:        https://sparklabs.com.mx
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       salesforce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** ========================================================================
 *  Constantes y helpers
 *  ===================================================================== */

/** Fuente (source) única para el logger */
if ( ! defined( 'BFR_SF_LOG_SOURCE' ) ) {
	define( 'BFR_SF_LOG_SOURCE', 'api_bafar_salesforce' );
}

/** Centro por defecto cuando no viene en la orden */
if ( ! defined( 'BFR_SF_DEFAULT_CENTER' ) ) {
	define( 'BFR_SF_DEFAULT_CENTER', 'M018' );
}

/**
 * Devuelve instancia de logger.
 *
 * @return WC_Logger
 */
function bfr_sf_logger() {
	return function_exists( 'wc_get_logger' ) ? wc_get_logger() : new WC_Logger();
}

/**
 * Normaliza/mapea el código de estado a nombre de estado (misma lógica del switch original).
 *
 * @param string $code Código de estado (ej. DF, JA, NL...).
 * @param string $city Ciudad actual (se usa para DF).
 * @return array [ 'state' => string, 'city' => string (posiblemente ajustada) ]
 */
function bfr_sf_map_state_code( $code, $city = '' ) {
	$code = strtoupper( (string) $code );

	$map = [
		'AG' => [ 'state' => 'Aguascalientes' ],
		'BC' => [ 'state' => 'Baja California' ],
		'BS' => [ 'state' => 'Baja California Sur' ],
		'CM' => [ 'state' => 'Campeche' ],
		'CS' => [ 'state' => 'Chiapas' ],
		'CH' => [ 'state' => 'Chihuahua' ],
		'CO' => [ 'state' => 'Coahuila' ],
		'CL' => [ 'state' => 'Colima' ],
		'DF' => [ 'state' => 'Ciudad de Mexico', 'city' => 'Ciudad de Mexico' ],
		'DG' => [ 'state' => 'Durango' ],
		'GT' => [ 'state' => 'Guanajuato' ],
		'GR' => [ 'state' => 'Guerrero' ],
		'HG' => [ 'state' => 'Hidalgo' ],
		'JA' => [ 'state' => 'Jalisco' ],
		'MX' => [ 'state' => 'Estado de Mexico' ],
		'MI' => [ 'state' => 'Michoacan' ],
		'MO' => [ 'state' => 'Morelos' ],
		'NA' => [ 'state' => 'Nayarit' ],
		'NL' => [ 'state' => 'Nuevo Leon' ],
		'OA' => [ 'state' => 'Oaxaca' ],
		'PU' => [ 'state' => 'Puebla' ],
		'QT' => [ 'state' => 'Queretaro' ],
		'QR' => [ 'state' => 'Quintana Roo' ],
		'SL' => [ 'state' => 'San Luis Potosi' ],
		'SI' => [ 'state' => 'Sinaloa' ],
		'SO' => [ 'state' => 'Sonora' ],
		'TB' => [ 'state' => 'Tabasco' ],
		'TM' => [ 'state' => 'Tamaulipas' ],
		'TL' => [ 'state' => 'Tlaxcala' ],
		'VE' => [ 'state' => 'Veracruz' ],
		'YU' => [ 'state' => 'Yucatan' ],
		'ZA' => [ 'state' => 'Zacatecas' ]
	];

	if ( isset( $map[ $code ] ) ) {
		$out = $map[ $code ];
		if ( 'DF' === $code ) {
			$out['city'] = 'Ciudad de Mexico';
		} elseif ( empty( $out['city'] ) ) {
			$out['city'] = $city;
		}
		return $out;
	}

	return [
		'state' => 'Codigo de estado no valido',
		'city'  => $city,
	];
}

/**
 * Limpia y normaliza teléfono a los últimos 10 dígitos.
 *
 * @param string $raw Teléfono crudo.
 * @return string Dígitos (10) o cadena vacía si no hay.
 */
function bfr_sf_sanitize_phone_10( $raw ) {
	$digits = preg_replace( '/\D+/', '', (string) $raw );
	if ( strlen( $digits ) > 10 ) {
		$digits = substr( $digits, -10 );
	}
	return $digits;
}

/** ========================================================================
 *  Metacampo en perfil de usuario (customer_crm)
 *  ===================================================================== */

/**
 * Agrega el campo "Cliente CRM" en el perfil de usuario (admin).
 *
 * @param WP_User $user
 */
function salesforce_custom_user_profile_fields( $user ) {
	wp_nonce_field( 'bfr_sf_save_user_meta', 'bfr_sf_user_meta_nonce' );
	?>
	<h3><?php esc_html_e( 'Información Adicional del Cliente', 'salesforce' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="customer_crm"><?php esc_html_e( 'Cliente CRM', 'salesforce' ); ?></label></th>
			<td>
				<input type="text" name="customer_crm" id="customer_crm"
					   value="<?php echo esc_attr( get_user_meta( $user->ID, 'customer_crm', true ) ); ?>"
					   class="regular-text" />
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'salesforce_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'salesforce_custom_user_profile_fields' );

/**
 * Guarda el metacampo "customer_crm" desde el perfil del usuario.
 *
 * @param int $user_id
 * @return void|false
 */
function salesforce_save_custom_user_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	// Nonce para seguridad
	if ( ! isset( $_POST['bfr_sf_user_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bfr_sf_user_meta_nonce'] ) ), 'bfr_sf_save_user_meta' ) ) {
		return false;
	}
	$val = isset( $_POST['customer_crm'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_crm'] ) ) : '';
	update_user_meta( $user_id, 'customer_crm', $val );
}
add_action( 'personal_options_update', 'salesforce_save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'salesforce_save_custom_user_profile_fields' );

/** ========================================================================
 *  Integración con API: crear/actualizar cliente y guardar BafarId
 *  ===================================================================== */

/**
 * Crea/actualiza el cliente en Salesforce (API) y guarda el BafarId en user meta.
 * Mantiene intacta la lógica original; se organizaron validaciones y logs.
 *
 * @param int    $customer_id ID de usuario.
 * @param string $centro      Centro (tienda). Si viene vacío, se usa el predeterminado.
 * @return string|null        BafarId guardado (o '888888' si no hay), o null si no se ejecuta.
 */
function api_y_guardar_bafarId( $customer_id, $centro ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	$log   = bfr_sf_logger();
	$ctx   = [ 'source' => BFR_SF_LOG_SOURCE, 'fn' => __FUNCTION__, 'customer_id' => (int) $customer_id ];
	$user  = get_userdata( $customer_id );

	// Datos base del usuario
	$first_name  = get_user_meta( $customer_id, 'billing_first_name', true );
	$last_name   = get_user_meta( $customer_id, 'billing_last_name', true );
	$phone_raw   = get_user_meta( $customer_id, 'billing_phone', true );
	$tipo_uso    = get_user_meta( $customer_id, 'tipo_uso', true );
	$giro_empresa = get_user_meta( $customer_id, 'giro_empresa', true );
	$email       = $user ? $user->user_email : '';

	$nombre_empresa = get_user_meta( $customer_id, 'billing_company', true ) ?? 'consumidor';

	$billing_address = [
		'streetNumber' => get_user_meta( $customer_id, 'billing_address_1', true ),
		'city'         => get_user_meta( $customer_id, 'billing_city', true ),
		'state'        => get_user_meta( $customer_id, 'billing_state', true ),
		'country'      => get_user_meta( $customer_id, 'billing_country', true ),
		'postalCode'   => get_user_meta( $customer_id, 'billing_postcode', true ),
	];

	// Normalizaciones
	$phone = bfr_sf_sanitize_phone_10( $phone_raw );
	if ( empty( $billing_address['streetNumber'] ) ) {
		$billing_address['streetNumber'] = 'Sin dirección';
	}

	// Validación mínima para ejecutar
	if ( empty( $phone ) || empty( $billing_address['streetNumber'] ) ) {
		$log->warning( 'No se ejecuta la API: teléfono o dirección incompletos.', $ctx );
		return null;
	}

	// Mapeo de estado y posible ajuste de ciudad para DF
	$mapped                  = bfr_sf_map_state_code( $billing_address['state'], $billing_address['city'] );
	$billing_address['state'] = $mapped['state'];
	$billing_address['city']  = $mapped['city'];

	// Centro por defecto si viene vacío
	if ( empty( $centro ) ) {
		$centro = BFR_SF_DEFAULT_CENTER;
	}

	// Construcción del payload conforme a la lógica original
	if ( 'Business' === $tipo_uso ) {
		$businessName = $nombre_empresa;
		$data_to_send = [
			'businessName' => $businessName,
			'type'         => 'Business',
			'phone'        => (int) $phone,
			'email'        => $email,
			'store'        => $centro,
			'businessType' => $giro_empresa,
			'contacts'     => [
				[
					'firstName' => $first_name,
					'lastName'  => $last_name,
					'phone'     => (int) $phone,
					'email'     => $email,
				],
			],
			'billingAddress' => [
				'streetNumber' => $billing_address['streetNumber'] ?? '',
				'city'         => $billing_address['city'] ?? '',
				'state'        => $billing_address['state'] ?? '',
				'country'      => 'Mexico',
				'postalCode'   => $billing_address['postalCode'] ?? '',
			],
		];
	} else {
		$data_to_send = [
			'firstName' => $first_name,
			'lastName'  => $last_name,
			'type'      => 'Customer',
			'phone'     => (int) $phone,
			'email'     => $email,
			'store'     => $centro,
			'billingAddress' => [
				'streetNumber' => $billing_address['streetNumber'] ?? '',
				'city'         => $billing_address['city'] ?? '',
				'state'        => $billing_address['state'] ?? '',
				'country'      => 'Mexico',
				'postalCode'   => $billing_address['postalCode'] ?? '',
			],
		];
	}

	// Log no sensible del payload
	$log->info( 'Preparando petición a la API de Salesforce.', $ctx + [
		'type'     => $data_to_send['type'] ?? null,
		'store'    => $data_to_send['store'] ?? null,
		'has_email'=> ! empty( $email ),
	] );

	// log data a enviar
	$log->info("Datos de usuario para enviar a Salesforce: " . print_r($data_to_send, true));

	// Credenciales/URL desde opciones
	$api_salesforce_url          = get_option( 'custom_plugin_api_salesforce_url', '' );
	$api_salesforce_clientsecret = get_option( 'custom_plugin_api_salesforce_clientsecret', '' );

	// La opción original tiene un espacio al final; soportamos ambas claves
	$api_salesforce_clientid = get_option( 'custom_plugin_api_salesforce_clientid', '' );
	if ( empty( $api_salesforce_clientid ) ) {
		$api_salesforce_clientid = get_option( 'custom_plugin_api_salesforce_clientid ', '' ); // compat
	}

	// Llamada HTTP
	$response = wp_remote_post(
		$api_salesforce_url,
		[
			'method'      => 'POST',
			'body'        => wp_json_encode( $data_to_send ),
			'headers'     => [
				'Content-Type'  => 'application/json',
				'client_secret' => $api_salesforce_clientsecret,
				'client_id'     => $api_salesforce_clientid,
			],
			'timeout'     => 30,
			'redirection' => 2,
			'httpversion' => '1.1',
		]
	);

	$log->info( 'Datos de la respuesta API: ' . print_r(  $response , true ) );


	// Manejo de errores HTTP
	if ( is_wp_error( $response ) ) {
		$log->error( 'Error HTTP al contactar API: ' . $response->get_error_message(), $ctx );
		return null;
	}

	$code      = wp_remote_retrieve_response_code( $response );
	$body_raw  = wp_remote_retrieve_body( $response );
	$api_data  = null;
	$bafar_id  = '';

	// Decodificar si es JSON válido
	if ( is_string( $body_raw ) && '' !== $body_raw ) {
		$decoded = json_decode( $body_raw, true );
		if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
			$api_data = $decoded;
		}
	}

	$log->info( 'Respuesta Code API recibida.', $ctx + [
		'http_code' => $code
	] );

	// Extraer bafarId si viene en JSON
	if ( is_array( $api_data ) && isset( $api_data['bafarId'] ) && ! empty( $api_data['bafarId'] ) ) {
		$bafar_id = sanitize_text_field( $api_data['bafarId'] );
	}

	// Si no hubo JSON o no hubo "bafarId", intentamos regex en el texto crudo
	if ( empty( $bafar_id ) && is_string( $body_raw ) && '' !== $body_raw ) {
		if ( preg_match_all( '/ID:\s*(\d+)/', $body_raw, $matches ) && ! empty( $matches[1] ) ) {
			$bafar_id = sanitize_text_field( $matches[1][0] );
		}
	}

	// Si no se encontró, usamos '888888'
	if ( empty( $bafar_id ) ) {
		$bafar_id = '888888';
	}

	// Guardar meta y log
	update_user_meta( $customer_id, 'customer_crm', $bafar_id );
	$log->info( 'bafarId guardado en el meta del cliente.', $ctx + [ 'bafar_id' => $bafar_id ] );

	return $bafar_id;
}

/** ========================================================================
 *  Hooks de disparo
 *  ===================================================================== */

/**
 * Ejecuta API en el primer pedido (o en thankyou).
 *
 * @param int $order_id
 * @return string|null
 */
function ejecutar_api_en_primer_pedido( $order_id ) {
	$log = bfr_sf_logger();
	$ctx = [ 'source' => BFR_SF_LOG_SOURCE, 'fn' => __FUNCTION__, 'order_id' => (int) $order_id ];

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		$log->error( 'No se pudo obtener la orden.', $ctx );
		return null;
	}

	$centro      = $order->get_meta( 'centro' );
	$customer_id = $order->get_user_id();

	if ( $customer_id ) {
		$log->info( 'Invocando API para cliente desde thankyou.', $ctx + [ 'customer_id' => (int) $customer_id ] );
		return api_y_guardar_bafarId( $customer_id, $centro );
	}

	return null;
}
// Si en algún punto decides reactivarlo desde thankyou, descomenta:
// add_action( 'woocommerce_thankyou', 'ejecutar_api_en_primer_pedido', 10, 1 );

/**
 * Ejecuta API cuando el cliente guarda su perfil o dirección.
 *
 * @param int $customer_id
 * @return void
 */
function ejecutar_api_si_actualiza_perfil( $customer_id ) {
	$log = bfr_sf_logger();
	$ctx = [ 'source' => BFR_SF_LOG_SOURCE, 'fn' => __FUNCTION__, 'customer_id' => (int) $customer_id ];

	// Solo para usuarios con rol de customer
	if ( user_can( $customer_id, 'customer' ) ) {
		$log->info( 'Actualización de perfil/dirección: invocando API.', $ctx );
		api_y_guardar_bafarId( $customer_id, '' );
	}
}
add_action( 'personal_options_update', 'ejecutar_api_si_actualiza_perfil' );
add_action( 'edit_user_profile_update', 'ejecutar_api_si_actualiza_perfil' );
add_action( 'woocommerce_customer_save_address', 'ejecutar_api_si_actualiza_perfil', 10, 2 );

/** ========================================================================
 *  REST: /custom/v1/cliente-salesforce  (POST)
 *  ===================================================================== */

add_action( 'rest_api_init', function () {
	register_rest_route(
		'custom/v1',
		'/cliente-salesforce',
		[
			'methods'             => 'POST',
			'permission_callback' => '__return_true', // Mantengo abierta como tu enfoque actual
			'callback'            => function ( WP_REST_Request $request ) {
				$params = $request->get_params();

				if ( ! isset( $params['id_pedido'] ) || '' === $params['id_pedido'] ) {
					return new WP_Error( 'missing_param', 'El parámetro id_pedido es obligatorio', [ 'status' => 400 ] );
				}

				$id_pedido = intval( $params['id_pedido'] );
				if ( $id_pedido <= 0 ) {
					return new WP_Error( 'invalid_param', 'El id_pedido debe ser un número entero positivo', [ 'status' => 400 ] );
				}

				$resultado = ejecutar_api_en_primer_pedido( $id_pedido );
				return rest_ensure_response( $resultado );
			},
			'args'                => [
				'id_pedido' => [
					'required'          => true,
					'type'              => 'integer',
					'validate_callback' => function ( $param ) {
						return is_numeric( $param ) && $param > 0;
					},
				],
			],
		]
	);
} );