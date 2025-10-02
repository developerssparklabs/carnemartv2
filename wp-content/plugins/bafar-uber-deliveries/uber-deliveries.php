<?php
/*
 * Plugin Name: Bafar :: Uber Deliveries 🏍️ 
 * Plugin URI: sparklabs.com.mx
 * Description: The plugin for uber deliveries.
 * Version: 1.0.0
 * Author: Naveed @ Sparklabs
 * Author URI: Naveed @ Sparklabs
 * Support: sparklabs.com.mx
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: rappie-integration
 * Domain Path: /languages/
 */


// Evitar el acceso directo al archivo
if (!defined('ABSPATH'))
	exit();

// Definir constantes para URLs y rutas del plugin
if (!defined('UD_URL')) {
	define('UD_URL', plugin_dir_url(__FILE__));
}
if (!defined('UD_DIR')) {
	define('UD_DIR', plugin_dir_path(__FILE__));
}

// Verificar si la clase principal ya existe
if (!class_exists('UD_Uber_Deliveries')) {

	class UD_Uber_Deliveries
	{

		public function __construct()
		{

			// Verificar si WooCommerce está activo
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				self::ud_init_plugin_files();

				// Filtrar las tarifas de envío
				add_filter('woocommerce_package_rates', array(__CLASS__, 'filtrar_tarifas_envio'), 10, 2);

				add_action('woocommerce_after_checkout_validation', [__CLASS__, 'ud_backend_validate_uber_session'], 10, 2);
			} else {
				// Mostrar aviso si WooCommerce no está activo
				add_action('admin_notices', array(__CLASS__, 'ud_admin_notice'));
			}
		}

		/**
		 * Filtrar las tarifas de envío para ajustar el costo según Uber.
		 */
		public static function filtrar_tarifas_envio($rates, $package)
		{

			return self::modificar_precio_flat_rate_personalizado($rates, $package);
		}

		/**
		 * Modificar el precio del método de envío "flat_rate:1" según el costo de Uber.
		 * Además, guarda en sesión si Uber es válido y el costo calculado,
		 * para que el back-end lo valide al enviar el checkout.
		 * (Con WC_Logger para depuración)
		 */
		public static function modificar_precio_flat_rate_personalizado($rates, $package)
		{
			$on_cart  = function_exists('is_cart') && is_cart();
			$on_ck    = function_exists('is_checkout') && is_checkout();
			if ( ! $on_cart && ! $on_ck ) {
				return $rates;
			}

			// Logger
			$logger = function_exists('wc_get_logger') ? wc_get_logger() : null;
			$ctx = ['source' => 'uber-deliveries', 'fn' => __METHOD__];

			$chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

			// índice del package (si no existe, asumimos 0)
			$pkg_index = isset($package['package_id']) ? (int) $package['package_id'] : 0;

			if ($logger) {
				$logger->debug('Entrando a modificar_precio_flat_rate_personalizado', array_merge($ctx, [
					'pkg_index' => $pkg_index,
					'has_flat_rate_1' => isset($rates['flat_rate:1']),
					'chosen_shipping_methods' => $chosen_shipping_methods
				]));
			}


			// obtener el valor de la cookie wcmlim_selected_location_termid
			$term_id = isset($_COOKIE['wcmlim_selected_location_termid']) ? $_COOKIE['wcmlim_selected_location_termid'] : null;

			if ($term_id) {
				// obtenemos el status del uber para esta tienda
				$uber_activo = (int) get_term_meta($term_id, 'uber_activo', true) ?? 0;
			} else {
				$uber_activo = 0;
			}

			if ($uber_activo == 0) {
				// Si el valor es 0, bloquear la opción de 'Envío por Uber'
				if (isset($rates['flat_rate:1'])) {
					$rates['flat_rate:1']->cost = 0; // Desactivar el costo
					$rates['flat_rate:1']->label = __('Envío por Uber no disponible en esta ubicación', 'woocommerce'); // Mensaje personalizado
					if (isset(WC()->session)) {
						$chosen_methods = WC()->session->get('chosen_shipping_methods');
						if (is_array($chosen_methods)) {
							$chosen_methods[$pkg_index] = 'local_pickup:2';
							WC()->session->set('chosen_shipping_methods', $chosen_methods);
						}
					}
					return $rates;
				}
			}

			if (isset($rates['flat_rate:1'])) {
				if (isset($chosen_shipping_methods[$pkg_index]) && $chosen_shipping_methods[$pkg_index] === 'flat_rate:1') {

					if ($logger) {
						$logger->debug('Uber seleccionado para este package', array_merge($ctx, [
							'pkg_index' => $pkg_index
						]));
					}

					// --- Destino & validaciones ---
					$destination = (array) $package['destination'];
					$street_address = $destination['address'] ?? '';
					$city = $destination['city'] ?? '';
					$state = $destination['state'] ?? '';
					$postcode = $destination['postcode'] ?? '';
					$country = $destination['country'] ?? '';
					$customer_id = get_option('ri_customer_id');

					if (empty($street_address) || empty($city) || empty($state) || empty($postcode) || empty($country) || empty($customer_id)) {
						// Verificamos si estamos en el /cart
						if (is_cart()) {
							wc_clear_notices();
							$rates['flat_rate:1']->cost = '';
							// $rates['flat_rate:1']->label = __('u', 'woocommerce');
							return $rates;
						}
					}

					$precio_uber = self::ajustar_costo_envio_por_ciudad_v2($package);

					if ($logger) {
						$logger->debug('Resultado ajustar_costo_envio_por_ciudad_v2', array_merge($ctx, [
							'resultado' => $precio_uber
						]));
					}

					$total_carrito = (float) WC()->cart->get_subtotal();
					$nuevo_precio = (!is_array($precio_uber) && $total_carrito >= 1000) ? 0.01 : $precio_uber;

					if (is_array($precio_uber)) {
						// ERROR: fuera de cobertura / faltan datos / api
						$distTxt = isset($precio_uber['distancia_km']) ? (' ' . $precio_uber['distancia_km'] . ' Km.') : '';
						$rates['flat_rate:1']->cost = 0;
						$rates['flat_rate:1']->label = __('Envío por Uber fuera de cobertura. Distancia calculada:' . $distTxt, 'woocommerce');

						// Marcar en sesión como inválido
						WC()->session->set("uber_valid_{$pkg_index}", false);
						WC()->session->set("uber_error_{$pkg_index}", $precio_uber);
						WC()->session->set("uber_cost_{$pkg_index}", null);

						if ($logger) {
							$logger->warning('Uber inválido para este package', array_merge($ctx, [
								'pkg_index' => $pkg_index,
								'error' => $precio_uber
							]));
						}

						// Deshabilitar botón en el front como ya haces
						add_filter('woocommerce_order_button_html', function ($button_html) {
							return '<button type="button" class="button disabled" disabled>' . __('Cobertura no disponible para Uber', 'woocommerce') . '</button>';
						}, 10, 1);

					} else {
						// OK
						$rates['flat_rate:1']->cost = (float) $nuevo_precio;
						$rates['flat_rate:1']->label = __('Envío por Uber', 'woocommerce');

						// Guardar en sesión como válido + costo
						WC()->session->set("uber_valid_{$pkg_index}", true);
						WC()->session->set("uber_error_{$pkg_index}", null);
						WC()->session->set("uber_cost_{$pkg_index}", (float) $nuevo_precio);
						WC()->session->set("uber_ts_{$pkg_index}", time()); // opcional: timestamp

						if ($logger) {
							$logger->info('Uber válido: costo aplicado y persistido en sesión', array_merge($ctx, [
								'pkg_index' => $pkg_index,
								'total_carrito' => $total_carrito,
								'costo_aplicado' => (float) $nuevo_precio
							]));
						}
					}
				} else {
					// No eligieron Uber: costo en 0 y limpiar flags para este package
					$rates['flat_rate:1']->cost = 0;
					WC()->session->set("uber_valid_{$pkg_index}", null);
					WC()->session->set("uber_error_{$pkg_index}", null);
					WC()->session->set("uber_cost_{$pkg_index}", null);

					if ($logger) {
						$logger->debug('Uber NO seleccionado: costo 0 y limpieza de sesión', array_merge($ctx, [
							'pkg_index' => $pkg_index
						]));
					}
				}
			} else {
				if ($logger) {
					$logger->debug('flat_rate:1 no existe en $rates para este package', array_merge($ctx, [
						'pkg_index' => $pkg_index,
						'rates_keys' => array_keys((array) $rates)
					]));
				}
			}

			return $rates;
		}


		/**
		 * Valida en back-end usando lo guardado en sesión por la función de tarifas.
		 * Si Uber no es válido, bloquea el checkout con un error.
		 * (Con WC_Logger para depuración)
		 */
		public static function ud_backend_validate_uber_session($data, $errors)
		{
			$on_cart  = function_exists('is_cart') && is_cart();
			$on_ck    = function_exists('is_checkout') && is_checkout();
			if ( ! $on_cart && ! $on_ck ) {
				return;
			}

			// Logger
			$logger = function_exists('wc_get_logger') ? wc_get_logger() : null;
			$ctx = ['source' => 'uber-deliveries', 'fn' => __METHOD__];

			// Si ya hay errores previos, no duplicamos
			if (is_object($errors) && method_exists($errors, 'get_error_codes') && $errors->get_error_codes()) {
				if ($logger) {
					$logger->debug('Abort: ya existen errores previos en $errors', $ctx);
				}
				return;
			}

			// Métodos posteados por package
			$posted_methods = isset($_POST['shipping_method']) && is_array($_POST['shipping_method'])
				? array_map('wc_clean', wp_unslash($_POST['shipping_method']))
				: [];

			if ($logger) {
				$logger->info('Checkout submit detectado (shipping_method posteado)', array_merge($ctx, [
					'posted_methods' => $posted_methods
				]));
			}

			if (empty($posted_methods)) {
				if ($logger) {
					$logger->warning('No hay shipping_method posteado', $ctx);
				}
				return;
			}

			foreach ($posted_methods as $idx => $method_id) {
				if ($logger) {
					$logger->debug('Evaluando package', array_merge($ctx, [
						'idx' => $idx,
						'method_id' => $method_id
					]));
				}

				if ($method_id !== 'flat_rate:1') {
					continue; // Solo Uber nos importa
				}

				$valid = WC()->session->get("uber_valid_{$idx}");
				$cost = WC()->session->get("uber_cost_{$idx}");
				$err = WC()->session->get("uber_error_{$idx}");

				if ($logger) {
					$logger->debug('Valores de sesión Uber', array_merge($ctx, [
						'idx' => $idx,
						'valid' => ($valid === true ? 'true' : (is_null($valid) ? 'null' : (string) $valid)),
						'cost' => $cost,
						'has_err' => is_array($err),
						'err_tipo' => (is_array($err) && isset($err['tipo'])) ? $err['tipo'] : null,
						'err_distancia' => (is_array($err) && isset($err['distancia_km'])) ? $err['distancia_km'] : null,
					]));
				}

				// Si no hay bandera o es falsa -> bloqueamos
				if (true !== $valid || $cost === null) {
					// Mensaje según error guardado
					if (is_array($err) && !empty($err['tipo'])) {
						switch ($err['tipo']) {
							case 'fuera_de_cobertura':
								$msg = __('Cobertura no disponible para Uber en su ubicación. Seleccione otra opción de envío.', 'woocommerce');
								break;
							case 'faltan_datos':
								$msg = __('Por favor, completa la dirección de envío antes de continuar.', 'woocommerce');
								break;
							default:
								$msg = __('Error en la comunicación con Uber. Intente nuevamente o elija otra opción.', 'woocommerce');
								break;
						}
					} else {
						$msg = __('No se pudo validar el envío por Uber. Intente nuevamente o elija otra opción.', 'woocommerce');
					}

					if ($logger) {
						$logger->error('Bloqueo checkout por Uber inválido', array_merge($ctx, [
							'idx' => $idx,
							'msg' => $msg,
							'err' => $err,
						]));
					}

					wc_add_notice($msg, 'error');
					return; // bloquea el submit
				}
			}

			if ($logger) {
				$logger->debug('Validación Uber OK: sesión válida; no se bloquea el checkout', $ctx);
			}
		}



		/**
		 * Inicializar archivos del plugin.
		 */
		public static function ud_init_plugin_files()
		{
			if (function_exists('load_plugin_textdomain')) {
				load_plugin_textdomain('rappie-integration', false, dirname(plugin_basename(__FILE__)) . '/languages/');
			}
			if (is_admin()) {
				require_once UD_DIR . 'include/class-backend.php';
			}
			if (!class_exists('Conekta')) {
				require_once WP_PLUGIN_DIR . '/bafar-conekta-payment-gateway/lib/conekta-php/lib/Conekta/Conekta.php';
			}
			require_once UD_DIR . 'include/class-frontend.php';
		}

		/**
		 * Mostrar aviso de error si WooCommerce no está activo.
		 */
		public static function ud_admin_notice()
		{
			global $pagenow;
			if ('plugins.php' === $pagenow) {
				$class = esc_attr('notice notice-error is-dismissible');
				$message = esc_html__('Uber Deliveries plugin needs WooCommerce to be installed and active.', 'rappie-integration');
				printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
			}
		}

		/**
		 * Ajustar el costo de envío según la ciudad.
		 */
		public static function ajustar_costo_envio_por_ciudad()
		{
			$chosen_methods = WC()->session->get('chosen_shipping_methods');
			$chosen_shipping = isset($chosen_methods[0]) ? $chosen_methods[0] : '';

			if ($chosen_shipping === 'flat_rate:1') {
				$customer = WC()->customer;
				$street_address = $customer->get_shipping_address_1();
				$city = $customer->get_shipping_city();
				$state = $customer->get_shipping_state();
				$postcode = $customer->get_shipping_postcode();
				$country = $customer->get_shipping_country();
				$customer_id = get_option('ri_customer_id');

				$required_fields = [
					'Street Address' => $street_address,
					'City' => $city,
					'State' => $state,
					'Postcode' => $postcode,
					'Country' => $country,
					'Customer ID' => $customer_id
				];

				foreach ($required_fields as $field_name => $value) {

					if (!is_checkout()) {
						return;
					}

					if (empty($value)) {
						wc_add_notice('Por favor, proporciona toda la información de envío antes de continuar.', 'error');
						return;
					}
				}

				error_log("Solicitud Uber: " . json_encode($required_fields));

				$response = wp_remote_post("https://api.uber.com/v1/customers/{$customer_id}/delivery_quotes", array(
					'body' => json_encode(array(
						'pickup_address' => self::pickup_location("", $_COOKIE['wcmlim_selected_location_termid']),
						'dropoff_address' => sprintf("{\"street_address\":[\"%s\"],\"city\":\"%s\",\"state\":\"%s\",\"zip_code\":\"%s\",\"country\":\"%s\"}", $street_address, $city, $state, $postcode, $country),
					)),
					'headers' => array(
						'Authorization' => 'Bearer ' . self::uber_access_token(),
						'Content-Type' => 'application/json',
					),
				));

				error_log("Respuesta Uber: " . json_encode($response));

				if (is_wp_error($response)) {
					error_log("Revisar el API de Uber");
					return 0;
				} else {
					$response_body = wp_remote_retrieve_body($response);
					$data = json_decode($response_body, associative: true);

					if (isset($data['fee'])) {
						$fee = $data['fee'] / 100; // Convertir el costo a la moneda base
						return $fee;
					}
				}
			}
		}
		public static function ajustar_costo_envio_por_ciudad_v2($package = null)
		{
			// --- Logger & contexto ---
			$logger = function_exists('wc_get_logger') ? wc_get_logger() : null;
			$ctx = ['source' => 'uber-deliveries', 'fn' => __METHOD__, 'req' => uniqid('ud_', true)];
			$t0 = microtime(true);

			$mask = static function ($s, $keep = 14) {
				$s = trim((string) $s);
				if ($s === '')
					return $s;
				$s = preg_replace('/\s+/', ' ', $s);
				return mb_substr($s, 0, $keep) . (mb_strlen($s) > $keep ? '…' : '');
			};
			$mi2km = static function ($mi) {
				return $mi * 1.60934;
			};

			try {
				$chosen_methods = WC()->session->get('chosen_shipping_methods');
				$chosen_shipping = isset($chosen_methods[0]) ? $chosen_methods[0] : '';

				if ($logger)
					$logger->debug('start', array_merge($ctx, [
						'chosen_shipping' => $chosen_shipping,
						'has_package' => (bool) $package,
					]));

				if ($chosen_shipping !== 'flat_rate:1') {
					if ($logger)
						$logger->debug('skip: not flat_rate:1', $ctx);
					return 0;
				}

				if (!$package) {
					$packages = WC()->shipping->get_packages();
					$package = $packages[0] ?? null;
				}
				if (!$package || empty($package['destination'])) {
					if ($logger)
						$logger->error('missing package/destination', array_merge($ctx, ['package_null' => !$package]));
					return ['error' => true, 'tipo' => 'faltan_datos', 'distancia_km' => null];
				}

				// --- Destino & validaciones ---
				$destination = (array) $package['destination'];
				$street_address = $destination['address'] ?? '';
				$city = $destination['city'] ?? '';
				$state = $destination['state'] ?? '';
				$postcode = $destination['postcode'] ?? '';
				$country = $destination['country'] ?? '';
				$customer_id = get_option('ri_customer_id');

				if (empty($street_address) || empty($city) || empty($state) || empty($postcode) || empty($country) || empty($customer_id)) {
					wc_clear_notices();
					
					// Check each field individually and add specific notices
					if (empty($street_address)) {
						wc_add_notice('Por favor ingresa la dirección de envío.', 'warning');
					}
					if (empty($city)) {
						wc_add_notice('Por favor ingresa la ciudad de envío.', 'warning'); 
					}
					if (empty($state)) {
						wc_add_notice('Por favor selecciona el estado de envío.', 'warning');
					}
					if (empty($postcode)) {
						wc_add_notice('Por favor ingresa el código postal.', 'warning');
					}
					if (empty($country)) {
						wc_add_notice('Por favor selecciona el país de envío.', 'warning');
					}
					if (empty($customer_id)) {
						wc_add_notice('Error: ID de cliente no disponible.', 'warning');
					}

					if ($logger) {
						$logger->warning('validation failed: missing fields', array_merge($ctx, [
							'addr' => $mask("$street_address, $city, $state, $postcode, $country"),
							'missing' => [
								'street' => (int) empty($street_address),
								'city' => (int) empty($city), 
								'state' => (int) empty($state),
								'zip' => (int) empty($postcode),
								'country' => (int) empty($country),
								'cust_id' => (int) empty($customer_id),
							],
						]));
					}
					return 0;
				}

				// --- Origen & geocoding ---
				$term_id = isset($_COOKIE['wcmlim_selected_location_termid']) ? intval($_COOKIE['wcmlim_selected_location_termid']) : 0;
				$pickup_address = self::pickup_location('', $term_id);
				$dropoff_text = "$street_address, $city, $state, $postcode, $country";

				$t_geo0 = microtime(true);
				$pickup_coords = self::get_coordinates_from_address($pickup_address);
				$dropoff_coords = self::get_coordinates_from_address($dropoff_text);
				$t_geo1 = microtime(true);

				if ($logger)
					$logger->debug('geocode results', array_merge($ctx, [
						'pickup_addr' => $mask($pickup_address),
						'dropoff_addr' => $mask($dropoff_text),
						'pickup_coords' => $pickup_coords,
						'drop_coords' => $dropoff_coords,
						'geo_ms' => round(($t_geo1 - $t_geo0) * 1000, 1),
						'store_term_id' => $term_id,
					]));

				if (!$pickup_coords || !$dropoff_coords) {
					if ($logger)
						$logger->error('geocode failed', $ctx);
					return ['error' => true, 'tipo' => 'faltan_datos', 'distancia_km' => null];
				}

				// --- Pre-check con Haversine: ahorra llamada a Uber si ya está fuera ---
				$distance_km = self::calculate_distance_km($pickup_coords, $dropoff_coords);
				$dist2 = floor($distance_km * 100) / 100;
				if ($logger)
					$logger->info('distance (pre-check)', array_merge($ctx, ['distance_km' => $dist2]));
				// Umbral “conservador” 8.0 km (≈ 4.97 mi). Ajusta si tu tienda tiene otro radio.
				if ($distance_km > 8.0) {
					if ($logger)
						$logger->info('pre-check out of coverage (>8km): skip Uber API', array_merge($ctx, ['distance_km' => $dist2]));
					return ['error' => true, 'tipo' => 'fuera_de_cobertura', 'distancia_km' => $dist2, 'radio_km' => 8.0, 'fuente' => 'precheck'];
				}

				// --- Llamada a Uber (direcciones como string) ---
				$body = wp_json_encode([
					'pickup_address' => $pickup_address,
					'dropoff_address' => trim(preg_replace('/\s+/', ' ', $dropoff_text)),
				]);

				$t_api0 = microtime(true);
				$response = wp_remote_post("https://api.uber.com/v1/customers/{$customer_id}/delivery_quotes", [
					'timeout' => 15,
					'body' => $body,
					'headers' => [
						'Authorization' => 'Bearer ' . self::uber_access_token(),
						'Content-Type' => 'application/json',
					],
				]);
				$t_api1 = microtime(true);

				if (is_wp_error($response)) {
					if ($logger)
						$logger->error('uber api WP_Error', array_merge($ctx, [
							'err' => $response->get_error_message(),
							'api_ms' => round(($t_api1 - $t_api0) * 1000, 1),
						]));
					return 0;
				}

				$status = wp_remote_retrieve_response_code($response);
				$resp_body = (string) wp_remote_retrieve_body($response);

				if ($logger)
					$logger->debug('uber api response', array_merge($ctx, [
						'status' => $status,
						'api_ms' => round(($t_api1 - $t_api0) * 1000, 1),
						'body_len' => strlen($resp_body),
						'body_head' => mb_substr($resp_body, 0, 400),
					]));

				$data = json_decode($resp_body, true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					if ($logger)
						$logger->error('json decode error', array_merge($ctx, ['json_error' => json_last_error_msg()]));
					return 0;
				}

				// --- Manejo específico: address_undeliverable (400) ---
				if ($status === 400 && isset($data['code']) && $data['code'] === 'address_undeliverable') {
					$details = (string) ($data['metadata']['details'] ?? '');

					// Intentar extraer radios/distancia en millas del mensaje
					$max_mi = null;
					$calc_mi = null;
					if (preg_match('/Max Radius:\s*([\d.]+)\s*miles/i', $details, $m))
						$max_mi = (float) $m[1];
					if (preg_match('/Calculated Distance:\s*([\d.]+)\s*miles/i', $details, $m))
						$calc_mi = (float) $m[1];

					$max_km = $max_mi !== null ? round($mi2km($max_mi), 2) : null;
					$calc_km = $calc_mi !== null ? round($mi2km($calc_mi), 2) : $dist2;

					if ($logger)
						$logger->warning('uber address_undeliverable', array_merge($ctx, [
							'radius_mi' => $max_mi,
							'distance_mi' => $calc_mi,
							'radius_km' => $max_km,
							'distance_km' => $calc_km,
							'details' => $details,
						]));

					return [
						'error' => true,
						'tipo' => 'fuera_de_cobertura',
						'distancia_km' => $calc_km,
						'radio_km' => $max_km ?? 8.0,
						'fuente' => 'uber',
					];
				}

				// --- Éxito (2xx con fee) ---
				if ($status >= 200 && $status < 300 && isset($data['fee'])) {
					$fee = ((float) $data['fee']) / 100;
					if ($logger)
						$logger->info('uber fee calculated', array_merge($ctx, [
							'fee' => $fee,
							'distance_km' => $dist2,
							'total_ms' => round((microtime(true) - $t0) * 1000, 1),
						]));
					return $fee;
				}

				// --- Otros casos de error/respuesta inesperada ---
				if ($logger)
					$logger->warning('uber response without fee or non-2xx', array_merge($ctx, [
						'status' => $status,
						'keys' => array_keys((array) $data),
						'total_ms' => round((microtime(true) - $t0) * 1000, 1),
					]));
				return 0;

			} catch (Throwable $e) {
				if ($logger)
					$logger->error('exception', array_merge($ctx, [
						'ex' => get_class($e),
						'message' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'elapsed' => round((microtime(true) - $t0) * 1000, 1),
					]));
				return 0;

			} finally {
				if ($logger)
					$logger->debug('end', array_merge($ctx, [
						'elapsed_ms' => round((microtime(true) - $t0) * 1000, 1),
					]));
			}
		}

		// Obtener coordenadas con la API de Google Maps
		public static function get_coordinates_from_address($address)
		{
			$api_key = 'AIzaSyB6dc8h2DsJrw9f7xqwZRfRfJ2aHripkvY';
			$address = urlencode($address);
			$response = wp_remote_get("https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$api_key}");
			error_log(json_encode(($response)));

			if (is_wp_error($response))
				return false;

			$body = json_decode(wp_remote_retrieve_body($response), true);



			if (!empty($body['results'][0]['geometry']['location'])) {
				return $body['results'][0]['geometry']['location']; // ['lat' => ..., 'lng' => ...]
			}

			return false;
		}

		// Calcular distancia en km entre dos coordenadas
		public static function calculate_distance_km($coord1, $coord2)
		{
			$earth_radius_km = 6371;

			$lat1 = deg2rad($coord1['lat']);
			$lon1 = deg2rad($coord1['lng']);
			$lat2 = deg2rad($coord2['lat']);
			$lon2 = deg2rad($coord2['lng']);

			$delta_lat = $lat2 - $lat1;
			$delta_lon = $lon2 - $lon1;

			$a = sin($delta_lat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($delta_lon / 2) ** 2;
			$c = 2 * atan2(sqrt(num: $a), sqrt(1 - $a));

			$distance = $earth_radius_km * $c;

			if ($distance > 7) {
				$factor = 1.25; // ciudad grande con más rodeos
			} elseif ($distance > 3) {
				$factor = 1.15; // distancia media
			} else {
				$factor = 1.10; // trayectos cortos
			}

			return $distance *= $factor;
		}

		/**
		 * Enviar el pedido a Uber y notificar al cliente.
		 * Con WC Logger detallado (canal: 'final-uber-compra')
		 */
		public static function enviar_pedido_a_uber_y_notificar_cliente($order_id)
		{
			$logger = wc_get_logger();
			$log_ctx = ['source' => 'final-uber-compra', 'order_id' => $order_id];
			$bench_t0 = microtime(true);

			$logger->info("== Inicio flujo Uber: enviar_pedido_a_uber_y_notificar_cliente ==", $log_ctx);
			$logger->debug("Parámetros de entrada", $log_ctx + ['input_order_id' => $order_id]);

			try {
				$order = wc_get_order($order_id);

				if (!$order) {
					$logger->error("No se pudo obtener la orden por ID", $log_ctx);
					echo 'No se pudo obtener la orden';
					return;
				}

				$logger->info("Orden obtenida correctamente", $log_ctx + [
					'wc_order_id' => $order->get_id(),
					'order_status' => $order->get_status(),
					'order_total' => $order->get_total(),
					'payment_method' => $order->get_payment_method(),
					'shipping_method' => implode(', ', wp_list_pluck($order->get_shipping_methods(), 'name')),
				]);

				$customer_id = get_option('ri_customer_id');
				$street_address = $order->get_shipping_address_1();
				$city = $order->get_shipping_city();
				$state = $order->get_billing_state();

				$billing_address = [
					'state' => '',
					'city' => $city,
				];

				// Cambiar el estado usando un switch
				switch ($state) {
					case 'DF':
						$billing_address['state'] = 'Ciudad de México';
						$billing_address['city'] = 'Ciudad de México';
						break;
					case 'JA':
						$billing_address['state'] = 'Jalisco';
						break;
					case 'NL':
						$billing_address['state'] = 'Nuevo León';
						break;
					case 'AG':
						$billing_address['state'] = 'Aguascalientes';
						break;
					case 'BC':
						$billing_address['state'] = 'Baja California';
						break;
					case 'BS':
						$billing_address['state'] = 'Baja California Sur';
						break;
					case 'CM':
						$billing_address['state'] = 'Campeche';
						break;
					case 'CH':
						$billing_address['state'] = 'Chiapas';
						break;
					case 'CO':
						$billing_address['state'] = 'Coahuila';
						break;
					case 'CL':
						$billing_address['state'] = 'Colima';
						break;
					case 'DG':
						$billing_address['state'] = 'Durango';
						break;
					case 'GT':
						$billing_address['state'] = 'Guanajuato';
						break;
					case 'GR':
						$billing_address['state'] = 'Guerrero';
						break;
					case 'HG':
						$billing_address['state'] = 'Hidalgo';
						break;
					case 'MX':
						$billing_address['state'] = 'Estado de México';
						break;
					case 'MI':
						$billing_address['state'] = 'Michoacán';
						break;
					case 'MO':
						$billing_address['state'] = 'Morelos';
						break;
					case 'NA':
						$billing_address['state'] = 'Nayarit';
						break;
					case 'OA':
						$billing_address['state'] = 'Oaxaca';
						break;
					case 'PU':
						$billing_address['state'] = 'Puebla';
						break;
					case 'QT':
						$billing_address['state'] = 'Querétaro';
						break;
					case 'QR':
						$billing_address['state'] = 'Quintana Roo';
						break;
					case 'SL':
						$billing_address['state'] = 'San Luis Potosí';
						break;
					case 'SI':
						$billing_address['state'] = 'Sinaloa';
						break;
					case 'SO':
						$billing_address['state'] = 'Sonora';
						break;
					case 'TB':
						$billing_address['state'] = 'Tabasco';
						break;
					case 'TM':
						$billing_address['state'] = 'Tamaulipas';
						break;
					case 'TL':
						$billing_address['state'] = 'Tlaxcala';
						break;
					case 'VE':
						$billing_address['state'] = 'Veracruz';
						break;
					case 'YU':
						$billing_address['state'] = 'Yucatán';
						break;
					case 'ZA':
						$billing_address['state'] = 'Zacatecas';
						break;
					default:
						$billing_address['state'] = 'Código de estado no válido';
				}

				$postcode = $order->get_shipping_postcode();
				$country = $order->get_shipping_country();
				$email = $order->get_billing_email();
				$location_name = $order->get_meta('location_name');
				$location_id = $order->get_meta('location_id');
				$order_items = [];
				$first_name = ucwords(strtolower($order->get_billing_first_name())); // Capitaliza correctamente
				$last_name = ucwords(strtolower($order->get_billing_last_name()));  // Capitaliza correctamente
				$customer_name = $first_name . ' ' . $last_name; // Nombre completo capitalizado
				$order_id = $order->get_id();
				$customer_note = $order->get_customer_note();
				$note_text = !empty($customer_note) ? '<p>📝 <strong>Instrucciones del cliente:</strong> ' . esc_html($customer_note) . '</p>' : '';

				$logger->debug("Datos base del pedido recopilados", $log_ctx + [
					'shipping_line1' => $street_address,
					'shipping_city' => $city,
					'shipping_state' => $state,
					'state_mapeado' => $billing_address['state'],
					'postcode' => $postcode,
					'country' => $country,
					'email' => $email,
					'location_name' => $location_name,
					'location_id' => $location_id,
					'customer_name' => $customer_name,
				]);

				$total = 0;

				foreach ($order->get_items() as $item_id => $item) {
					$product = $item->get_product();
					$quantity = round($item->get_quantity());
					$price = (int) $product->get_price();

					$order_items[] = [
						'quantity' => $quantity,
						'name' => $product->get_name(),
						'price' => $price
					];

					$total += $price * $quantity;

					$logger->debug("Item de pedido", $log_ctx + [
						'item_id' => $item_id,
						'product_id' => $product ? $product->get_id() : null,
						'product_name' => $product ? $product->get_name() : null,
						'qty' => $quantity,
						'unit_price' => $price,
						'subtotal' => $price * $quantity
					]);
				}

				$logger->info("Resumen de items y totales", $log_ctx + [
					'items_count' => count($order_items),
					'items_total' => $total,
					'order_total' => (int) $order->get_total(),
				]);

				$required_fields = [
					'Street Address' => $street_address,
					'City' => $city,
					'State' => $state,
					'Postcode' => $postcode,
					'Country' => $country,
					'Customer ID' => $customer_id,
				];

				foreach ($required_fields as $field_name => $value) {
					if (empty($value)) {
						$logger->error("Falta el campo requerido", $log_ctx + ['campo' => $field_name]);
						error_log("Falta el campo requerido: $field_name");
						return;
					}
				}
				$logger->info("Validación de campos requeridos OK", $log_ctx);

				$pickupDate = $order->get_meta('lp_pickup_date');
				$pickupTime = $order->get_meta('lp_pickup_time');

				$logger->debug("Valores iniciales de pickup (meta)", $log_ctx + [
					'meta_lp_pickup_date' => $pickupDate,
					'meta_lp_pickup_time' => $pickupTime
				]);

				$horario_entrega = self::getPickupDate($billing_address['state'], $pickupDate, $pickupTime);
				$logger->info("Horario de entrega calculado", $log_ctx + ['pickup_ready_dt_local' => $horario_entrega]);

				$utcTimestamp = strtotime($horario_entrega);
				$pickupDeadline = gmdate('Y-m-d\TH:i:s\Z', $utcTimestamp + 1800);

				$body_array = [
					"pickup_name" => "Tienda Carnemart",
					"pickup_address" => self::pickup_location($location_name, $location_id),
					"pickup_phone_number" => self::pickup_phone($location_name, $location_id),
					"dropoff_name" => $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
					"dropoff_address" => json_encode([
						"street_address" => [$order->get_shipping_address_1()],
						"city" => $order->get_shipping_city(),
						"state" => $order->get_shipping_state(),
						"zip_code" => $order->get_shipping_postcode(),
						"country" => $order->get_shipping_country()
					]),
					"dropoff_phone_number" => !empty($order->get_billing_phone()) ? "+521" . (string) $order->get_billing_phone() : "",
					// "manifest_items" => $order_items,
					"manifest_items" => [
						[
							"name" => "producto carnemart ",
							"quantity" => 1,
							"price" => (int) $order->get_total(),
							"weight" => 21000,
						]
					],
					"manifest_reference" => "CM-" . $order->get_id(),
					"manifest_total_value" => (int) $order->get_total(),
					"quote_id" => "dqt_" . uniqid(),
					"undeliverable_action" => "return",
					"pickup_ready_dt" => $horario_entrega,
					"pickup_deadline" => $pickupDeadline,
					"external_store_id" => "store_" . $order->get_id(),
					'courier' => [
						'vehicle_type' => 'car'
					],
					"return_notes" => $note_text,
					"dropoff_verification" => [
						"pincode" => [
							"enabled" => true,
							"type" => "default"
						]
					]
				];

				$body = json_encode($body_array);
				$order->update_meta_data('uber_request', $body);
				$order->save();

				$logger->debug("Payload preparado y guardado en meta 'uber_request'", $log_ctx + [
					'pickup_deadline_utc' => $pickupDeadline,
					'payload_preview' => mb_substr($body, 0, 1000) . (strlen($body) > 1000 ? '...[truncado]' : '')
				]);

				$api_url = "https://api.uber.com/v1/customers/{$customer_id}/deliveries/";
				$request_args = [
					'body' => $body,
					'headers' => [
						// IMPORTANTE: no logeamos el token completo por seguridad
						'Authorization' => 'Bearer ' . '[TOKEN_OCULTO]',
						'Content-Type' => 'application/json',
					],
				];

				$logger->info("Llamando a Uber Deliveries API", $log_ctx + [
					'api_url' => $api_url,
					'body_length' => strlen($body),
					'headers_safe' => array_keys($request_args['headers'])
				]);

				// Reemplazamos token real solo en la llamada
				$request_args_real = $request_args;
				$request_args_real['headers']['Authorization'] = 'Bearer ' . self::uber_access_token();

				$http_t0 = microtime(true);
				$response = wp_remote_post($api_url, $request_args_real);
				$http_dt = round((microtime(true) - $http_t0) * 1000);
				$logger->info("Respuesta HTTP recibida", $log_ctx + [
					'duration_ms' => $http_dt,
					'is_wp_error' => is_wp_error($response) ? 'yes' : 'no',
					'http_code' => is_wp_error($response) ? null : wp_remote_retrieve_response_code($response),
					'http_msg' => is_wp_error($response) ? null : wp_remote_retrieve_response_message($response),
				]);

				if (is_wp_error($response)) {
					$logger->error("Error al enviar el pedido a Uber (WP_Error)", $log_ctx + [
						'wp_error_code' => $response->get_error_code(),
						'wp_error_message' => $response->get_error_message(),
					]);
					return 'Error al enviar el pedido a Uber: ' . $response->get_error_message();
				}

				$response_body = wp_remote_retrieve_body($response);
				$data = json_decode($response_body, true);

				$logger->debug("Body de respuesta Uber (preview)", $log_ctx + [
					'response_len' => strlen($response_body),
					'response_preview' => mb_substr($response_body, 0, 1500) . (strlen($response_body) > 1500 ? '...[truncado]' : '')
				]);

				// Mantener error_log existente para compatibilidad
				error_log(print_r($data, true));

				if (isset($data['error_code'])) {
					$logger->error("Respuesta Uber con error_code", $log_ctx + [
						'error_code' => $data['error_code'],
						'error_message' => isset($data['message']) ? $data['message'] : null,
					]);
					return 'Error en la respuesta de Uber: ' . $data['error_code'];
				}

				if (isset($data['id'])) {
					$delivery_id = $data['id'];
					$uber_pin_code = $data['dropoff']['verification_requirements']['pincode']['value'] ?? null;
					$order->update_meta_data('uber_pin_code', $uber_pin_code);

					$order->add_order_note('Pedido registrado en Uber. ID de entrega: ' . $delivery_id);
					$order->update_meta_data('uber_delivery_id', $delivery_id);

					$logger->info("Entrega creada en Uber", $log_ctx + [
						'delivery_id' => $delivery_id,
						'tracking_url' => $data['tracking_url'] ?? null,
						'pin_present' => $uber_pin_code ? 'yes' : 'no',
					]);

					// Para mantener la zona horaria del pedido, traemos el datetimezone de la ciudad
					$getTimeZone = self::getTimeZone($billing_address['state']);
					$dateTimeZone = $getTimeZone[0];

					$pickup_ready = new DateTime($data['pickup_ready'], new DateTimeZone('UTC'));
					$pickup_ready->setTimezone(new DateTimeZone($dateTimeZone));
					$pickup_date = $pickup_ready->format('Y-m-d');
					$pickup_time = $pickup_ready->format('H:i:s');

					$pickup_deadline = new DateTime($data['pickup_deadline'], new DateTimeZone('UTC'));
					$pickup_deadline->setTimezone(new DateTimeZone($dateTimeZone));
					$pickup_time_deadline = $pickup_deadline->format('H:i:s');

					$dropoff_ready = new DateTime($data['dropoff_ready'], new DateTimeZone('UTC'));
					$dropoff_ready->setTimezone(new DateTimeZone($dateTimeZone));
					$start_time = $dropoff_ready->format('H:i:s');

					$dropoff_deadline = new DateTime($data['dropoff_deadline'], new DateTimeZone('UTC'));
					$dropoff_deadline->setTimezone(new DateTimeZone($dateTimeZone));
					$end_time = $dropoff_deadline->format('H:i:s');

					$url_delivery = $data['tracking_url'];
					$meta_data_delivery = json_encode($response);

					UD_Uber_Deliveries::cm_upsert_unique_order_meta($order_id, 'lp_pickup_time', $pickup_time);
					UD_Uber_Deliveries::cm_upsert_unique_order_meta($order_id, 'lp_pickup_date', $pickup_date);
					UD_Uber_Deliveries::cm_upsert_unique_order_meta($order_id, 'lp_pickup_time_deadline', $pickup_time_deadline);
					UD_Uber_Deliveries::cm_upsert_unique_order_meta($order_id, 'url_delivery', $url_delivery);
					UD_Uber_Deliveries::cm_upsert_unique_order_meta($order_id, 'meta_data_delivery', $meta_data_delivery);

					$logger->info("Metadatos de entrega guardados/actualizados", $log_ctx + [
						'pickup_date' => $pickup_date,
						'pickup_time' => $pickup_time,
						'pickup_time_deadline' => $pickup_time_deadline,
						'url_delivery' => $url_delivery
					]);

					//if (empty($order->get_meta('lp_pickup_time'))) {
					// 	$order->update_meta_data('lp_pickup_time', $pickup_time);
					// }

					// // Solo guarda lp_pickup_date si aún no existe
					// if (empty($order->get_meta('lp_pickup_date'))) {
					// 	$order->update_meta_data('lp_pickup_date', $pickup_date);
					// }

					// // Solo guarda lp_pickup_time_deadline si aún no existe
					// if (empty($order->get_meta('lp_pickup_time_deadline'))) {
					// 	$order->update_meta_data('lp_pickup_time_deadline', $pickup_time_deadline);
					// }

					// // Solo guarda url_delivery si aún no existe
					// if (empty($order->get_meta('url_delivery'))) {
					// 	$order->update_meta_data('url_delivery', $url_delivery);
					// }

					// // Solo guarda meta_data_delivery si aún no existe
					// if (empty($order->get_meta('meta_data_delivery'))) {
					// 	$order->update_meta_data('meta_data_delivery', $meta_data_delivery);
					// }


					// Guardamos todos los datos de la respuesta
					$created_uber_response = new DateTime($data['created'], new DateTimeZone('UTC'));
					$created_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$created_uber_response = $created_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_created', $created_uber_response);

					$updated_uber_response = new DateTime($data['updated'], new DateTimeZone('UTC'));
					$updated_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$updated_uber_response = $updated_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_updated', $updated_uber_response);

					$pickup_ready_uber_response = new DateTime($data['pickup_ready'], new DateTimeZone('UTC'));
					$pickup_ready_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$pickup_ready_uber_response = $pickup_ready_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_pickup_ready', $pickup_ready_uber_response);

					$pickup_deadline_uber_response = new DateTime($data['pickup_deadline'], new DateTimeZone('UTC'));
					$pickup_deadline_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$pickup_deadline_uber_response = $pickup_deadline_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_pickup_deadline', $pickup_deadline_uber_response);

					$dropoff_ready_uber_response = new DateTime($data['dropoff_ready'], new DateTimeZone('UTC'));
					$dropoff_ready_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$dropoff_ready_uber_response = $dropoff_ready_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_dropoff_ready', $dropoff_ready_uber_response);

					$dropoff_deadline_uber_response = new DateTime($data['dropoff_deadline'], new DateTimeZone('UTC'));
					$dropoff_deadline_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$dropoff_deadline_uber_response = $dropoff_deadline_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_dropoff_deadline', $dropoff_deadline_uber_response);

					$pickup_eta_uber_response = new DateTime($data['pickup_eta'], new DateTimeZone('UTC'));
					$pickup_eta_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$pickup_eta_uber_response = $pickup_eta_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_pickup_eta', $pickup_eta_uber_response);

					$dropoff_eta_uber_response = new DateTime($data['dropoff_eta'], new DateTimeZone('UTC'));
					$dropoff_eta_uber_response->setTimezone(new DateTimeZone($dateTimeZone));
					$dropoff_eta_uber_response = $dropoff_eta_uber_response->format('Y-m-d H:i:s');
					$order->update_meta_data('uber_dropoff_eta', $dropoff_eta_uber_response);

					$order->save();

					$logger->info("Timestamps Uber guardados", $log_ctx + [
						'uber_created' => $created_uber_response,
						'uber_updated' => $updated_uber_response,
						'uber_pickup_ready' => $pickup_ready_uber_response,
						'uber_pickup_deadline' => $pickup_deadline_uber_response,
						'uber_dropoff_ready' => $dropoff_ready_uber_response,
						'uber_dropoff_deadline' => $dropoff_deadline_uber_response,
						'uber_pickup_eta' => $pickup_eta_uber_response,
						'uber_dropoff_eta' => $dropoff_eta_uber_response
					]);

					// Obtener el mailer de WooCommerce
					$mailer = WC()->mailer();
					$subject = 'Tu pedido está en camino';

					$message = sprintf(
						'<p>🎉 ¡Buenas noticias, %s! 🎉</p>
				<p>Tu pedido <strong>#%d</strong> ya está en camino con Uber.</p>
				<p>🚀 <strong>ID de entrega:</strong> %s</p>
				<p>📍 Sigue tu pedido en tiempo real aquí: <a href="%s">%s</a></p>
				<p>🔐 <strong>PIN de entrega:</strong> %s</p>
				<p>⏳ ¡Prepárate! Muy pronto lo tendrás en tus manos.</p>
				<p>Gracias por confiar en nosotros. Carnemart.com</p>',
						esc_html($customer_name),
						$order_id,
						$delivery_id,
						esc_url($url_delivery),
						esc_url($url_delivery),
						isset($uber_pin_code) ? esc_html($uber_pin_code) : ''
					);

					$email_content = $mailer->wrap_message($subject, $message);
					$headers = ['Content-Type: text/html; charset=UTF-8'];

					$logger->info("Enviando email al cliente", $log_ctx + [
						'destination_email' => $email,
						'subject' => $subject,
					]);

					$send_t0 = microtime(true);
					$mailer->send($email, $subject, $email_content, $headers);
					$send_dt = round((microtime(true) - $send_t0) * 1000);

					$logger->info("Email enviado", $log_ctx + ['duration_ms' => $send_dt]);

					$bench_dt = round((microtime(true) - $bench_t0) * 1000);
					$logger->info("== Fin flujo Uber OK ==", $log_ctx + ['duration_ms_total' => $bench_dt]);

					return 'Correo enviado al cliente: ' . $email;
				} else {
					$order->update_meta_data('uber_response', $response_body);
					$order->save();

					$msg = isset($data["message"]) ? $data["message"] : 'Respuesta inesperada sin message ni id';
					$order->add_order_note('⚠️ Advertencia Uber: ' . $msg);

					$logger->warning("Uber no devolvió ID de entrega", $log_ctx + [
						'platform_message' => $msg,
						'body_preview' => mb_substr($response_body, 0, 1000) . (strlen($response_body) > 1000 ? '...[truncado]' : '')
					]);

					$bench_dt = round((microtime(true) - $bench_t0) * 1000);
					$logger->info("== Fin flujo Uber con advertencia ==", $log_ctx + ['duration_ms_total' => $bench_dt]);

					return 'Error en envío de Uber, mensaje de la plataforma: ' . $msg;
				}
			} catch (Throwable $e) {
				$logger->error("Excepción no controlada en flujo Uber", $log_ctx + [
					'exception_class' => get_class($e),
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => mb_substr($e->getTraceAsString(), 0, 2000)
				]);
				return 'Error inesperado al procesar el envío a Uber: ' . $e->getMessage();
			}
		}

		/**
		 * Guarda/actualiza un meta de pedido garantizando UNA sola fila visible.
		 * - Escribe con API de WooCommerce (HPOS-safe).
		 * - Normaliza duplicados en postmeta sin borrar el valor bueno.
		 * - No escribe si el valor es vacío/null (evita volatilizar metas).
		 */
		public static function cm_upsert_unique_order_meta($order_id, string $key, $value): void
		{
			$logger = wc_get_logger();
			$log_ctx = ['source' => 'final-uber-compra', 'order_id' => $order_id, 'meta_key' => $key];

			// 0) Valor vacío: no tocamos nada (evita borrar y "no re-crear")
			if ($value === null || $value === '') {
				$logger->warning("Skip upsert: valor vacío; no se modifica meta", $log_ctx);
				return;
			}

			// 1) Escribir con API WooCommerce (HPOS-safe)
			$order = wc_get_order($order_id);
			if (!$order) {
				$logger->error("Orden no encontrada; aborta upsert", $log_ctx);
				return;
			}

			$order->update_meta_data($key, $value);
			$order->save();

			// 2) Verificación via WC API
			$wc_val = $order->get_meta($key, true);

			// 3) Opcional: sincroniza también en postmeta (para que se vea en metabox clásico)
			//    update_post_meta es idempotente y crea si no existe.
			update_post_meta($order_id, $key, $value);

			// 4) Normaliza duplicados en postmeta dejando UNA fila con el valor correcto
			global $wpdb;
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC",
					$order_id,
					$key
				),
				ARRAY_A
			);

			if (is_array($rows) && count($rows) > 1) {
				// Mantén la primera fila que tenga el valor correcto (prioriza no vacías)
				$keep_mid = null;

				foreach ($rows as $i => $row) {
					$mid = (int) $row['meta_id'];
					$mval = (string) $row['meta_value'];

					if ($keep_mid === null && $mval !== '' && $mval == (string) $value) {
						$keep_mid = $mid;
						break;
					}
				}
				// Si no hubo exact match, conserva la fila más nueva (primer row)
				if ($keep_mid === null && !empty($rows)) {
					$keep_mid = (int) $rows[0]['meta_id'];
				}

				// Borra únicamente los que NO son keep_mid
				$deleted = 0;
				foreach ($rows as $row) {
					$mid = (int) $row['meta_id'];
					if ($mid !== $keep_mid) {
						// Borrado preciso por meta_id
						delete_metadata_by_mid('post', $mid);
						$deleted++;
					}
				}

				$logger->warning("Normalización de duplicados en postmeta", $log_ctx + [
					'total_rows' => count($rows),
					'deleted' => $deleted,
					'keep_mid' => $keep_mid,
				]);
			} elseif (empty($rows)) {
				// Si llegaste aquí sin filas (raro), recrea una explícitamente
				add_post_meta($order_id, $key, $value, true);
				$logger->warning("No había filas en postmeta; se creó una nueva", $log_ctx);
			}

			// 5) Verificación final
			$wp_val = get_post_meta($order_id, $key, true);

			$logger->info("Upsert verificado", $log_ctx + [
				'wc_val' => $wc_val,
				'wp_val' => $wp_val,
				'ok' => ($wc_val == (string) $value || $wp_val == (string) $value) ? 'sí' : 'no',
			]);
		}

		private static function get_token_option(): mixed
		{
			$token = get_option('uber_access_token');
			$expiration = get_option('uber_access_token_expiration');

			if (!$token || !$expiration || time() > $expiration) {
				// Expirado o no existe
				delete_option('uber_access_token');
				delete_option('uber_access_token_expiration');
				return false;
			}

			return $token;
		}

		private static function set_token_option($token, $expiration_in_seconds)
		{
			// Guarda el token y su fecha de expiración
			update_option('uber_access_token', $token);
			update_option('uber_access_token_expiration', time() + $expiration_in_seconds);
		}

		/**
		 * Obtener el token de acceso de Uber.
		 */
		private static function uber_access_token()
		{
			// Intentar obtener el token desde la base de datos
			$token = self::get_token_option();
			if ($token) {
				error_log('✅ Token Uber obtenido de opción DB, TOKEN:: ' . $token);
				return $token;
			}

			// Si no existe, solicitar uno nuevo
			$token_url = 'https://login.uber.com/oauth/v2/token';
			$client_id = get_option('ri_client_id');
			$client_secret = get_option('ri_client_secret_id');

			$response = wp_remote_post($token_url, array(
				'method' => 'POST',
				'body' => array(
					'client_id' => $client_id,
					'client_secret' => $client_secret,
					'grant_type' => 'client_credentials',
					'scope' => 'eats.deliveries',
				),
			));

			if (is_wp_error($response)) {
				error_log('❌ Error HTTP al obtener token Uber: ' . $response->get_error_message());
				return null;
			}

			$response_body = wp_remote_retrieve_body($response);
			$response_data = json_decode($response_body);

			if (isset($response_data->access_token)) {
				// Guardar por 28 días (o el tiempo que tú decidas)
				self::set_token_option($response_data->access_token, 28 * DAY_IN_SECONDS);
				return $response_data->access_token;
			} else {
				error_log('❌ Respuesta inválida de Uber: ' . print_r($response_data, true));
				return null;
			}
		}

		/**
		 * Obtener la ubicación de recogida.
		 */
		private static function pickup_location($term_name, $location_id)
		{
			if (!$location_id) {
				if ($term_name == "") {
					$location_id = $_COOKIE['wcmlim_selected_location_termid'];
				} else {
					$term = get_term_by('name', $term_name, "locations");
					$location_id = $term->term_id;
				}
			}
			$pstreet_no = get_term_meta($location_id, 'wcmlim_street_number', true);
			$pstreet_address = get_term_meta($location_id, 'wcmlim_route', true);
			$pcity = get_term_meta($location_id, 'wcmlim_locality', true);
			$pstate = get_term_meta($location_id, 'wcmlim_administrative_area_level_1', true);
			$ppostcode = get_term_meta($location_id, 'wcmlim_postal_code', true);
			$pcountry = get_term_meta($location_id, 'wcmlim_country', true);

			return sprintf("{\"street_address\":[\"%s %s\"],\"city\":\"%s\",\"state\":\"%s\",\"zip_code\":\"%s\",\"country\":\"%s\"}", $pstreet_no, $pstreet_address, $pcity, $pstate, $ppostcode, $pcountry);
		}

		/**
		 * Obtener la ubicación de recogida.
		 */
		private static function pickup_phone($term_name, $location_id)
		{
			if (!$location_id) {
				if ($term_name == "") {
					$location_id = $_COOKIE['wcmlim_selected_location_termid'];
				} else {
					$term = get_term_by('name', $term_name, "locations");
					$location_id = $term->term_id;
				}
			}

			$phone = get_term_meta($location_id, 'wcmlim_phone', true);
			$phone = "+521" . $phone;

			return $phone;
		}


		/**
		 * Respaldo esta versión, para revisar las reglas
		 * Obtiene la fecha para programar el envío.
		 * - El horario de las tiendas es de Lun-Sab de 08:00 a 19:00 y Domingo de 09:00 a 15:00.
		 * - La preparacion del pedido tardara hasta 2 horas.
		 * - Por lo tanto, los pedidos serian Lun-Sab de 10 a 17 y Dom de 11 a 13
		 * - Si es Domingo. [00:00 - 09:00] .: Mismo dia 11:00. (09:00 - 13:00] .: Mismo dia + 2 horas. (13:00 - 00:00) .: Siguiente dia a las 10:00
		 * - Si es Lunes a Viernes. [00:00 - 08:00].: Mismo dia 10:00. (08:00 - 17:00] .: Mismo dia + 2 horas. (17:00 - 00:00) .: Siguiente dia a las 10:00
		 * - Si es Sabado. [00:00 - 08:00].: Mismo dia 10:00. (08:00 - 17:00] .: Mismo dia + 2 horas. (17:00 - 00:00) .: Siguiente dia a las 11:00
		 * - El horario INICIAL de preparacion de pedido sera de 8:00 a 17:00. El primer pedido se entregaria a Uber a las 10:00 y el ultimo a las 19:00
		 *
		 * https://www.php.net/manual/en/datetime.format.php
		 *
		 * @param string $state
		 * @param string $deliveryDate  Format 2022-01-31, 2022-12-31
		 * @param string $deliveryHour  Format 00, 08, 09, 10, 11, 12, 13, 14, 23
		 * @return string $pickupDate
		 */
		private static function getPickupDateOld(string $state, $deliveryDate, $deliveryHour): string
		{
			error_log(print_r(["state" => $state, "deliveryDate" => $deliveryDate, "deliveryHour" => $deliveryHour], true));
			$timeZone = self::getTimeZone($state);
			date_default_timezone_set($timeZone[0]);

			error_log("Zona horaria debug: " . $timeZone[0]);
			// Fecha y hora actuales en la zona horaria de México
			// $deliveryDate = date('Y-m-d');
			// $deliveryHour = (int)date('H');
			if (empty($deliveryDate)) {
				$deliveryDate = date('Y-m-d');
			}

			// Si deliveryHour es como "11:00 AM", lo convertimos a formato 24h
			if (!empty($deliveryHour)) {
				$deliveryHour = date('H', strtotime($deliveryHour)); // ej: "11"
			} else {
				$deliveryHour = (int) date('H');
			}

			// Hora límite para evitar trucos
			$trickTimeLimit = strtotime('-2 hours');

			// Fecha y hora del pedido en zona local de México
			$deliveryTime = strtotime("$deliveryDate $deliveryHour:00:00");

			// Día de la semana
			$deliveryTimeDayOfWeek = date('l', $deliveryTime);

			// Horas de referencia
			$zeroHour = 0;
			$sundayOpeningHour = 9;
			$sundayLastUberShipHour = 13;
			$mondayToSaturdayOpeningHour = 8;
			$mondayToSaturdayLastUberShipHour = 17;

			// Si el usuario intenta forzar una entrega inmediata
			if ($deliveryTime < $trickTimeLimit) {
				$pickupDateLocal = date('Y-m-d\T11:00:00', strtotime('+1 day'));
			} else {
				switch ($deliveryTimeDayOfWeek) {
					case "Sunday":
						if ($deliveryHour >= $zeroHour && $deliveryHour < $sundayOpeningHour) {
							$pickupDateLocal = date('Y-m-d\T11:00:00', $deliveryTime);
						} elseif ($deliveryHour >= $sundayOpeningHour && $deliveryHour < $sundayLastUberShipHour) {
							$pickupDateLocal = date('Y-m-d\TH:i:s', strtotime('+2 hours', $deliveryTime));
						} else {
							$pickupDateLocal = date('Y-m-d\T10:00:00', strtotime('tomorrow'));
						}
						break;

					case "Saturday":
						if ($deliveryHour >= $zeroHour && $deliveryHour < $mondayToSaturdayOpeningHour) {
							$pickupDateLocal = date('Y-m-d\T10:00:00', $deliveryTime);
						} elseif ($deliveryHour >= $mondayToSaturdayOpeningHour && $deliveryHour < $mondayToSaturdayLastUberShipHour) {
							$pickupDateLocal = date('Y-m-d\TH:i:s', strtotime('+2 hours', $deliveryTime));
						} else {
							$pickupDateLocal = date('Y-m-d\T11:00:00', strtotime('tomorrow'));
						}
						break;

					default: // Lunes a Viernes
						if ($deliveryHour >= $zeroHour && $deliveryHour < $mondayToSaturdayOpeningHour) {
							$pickupDateLocal = date('Y-m-d\T10:00:00', $deliveryTime);
						} elseif ($deliveryHour >= $mondayToSaturdayOpeningHour && $deliveryHour < $mondayToSaturdayLastUberShipHour) {
							$pickupDateLocal = date('Y-m-d\TH:i:s', strtotime('+2 hours', $deliveryTime));
						} else {
							$pickupDateLocal = date('Y-m-d\T10:00:00', strtotime('tomorrow'));
						}
						break;
				}
			}
			error_log("Fecha y hora de recogida local: " . $pickupDateLocal);

			// Convertir la fecha y hora de México a UTC antes de enviarla a Uber
			$pickupDateUtc = self::convertToUtc($pickupDateLocal, $timeZone[0]);

			return $pickupDateUtc;
		}
		// FUncional sin restricciones ni validación de hora y fecha que se envie
		private static function getPickupDate_vFuncional(string $state, string $deliveryDate, $deliveryHour): string
		{
			// La zona horaria se establece según el estado
			$timeZone = self::getTimeZone($state);
			date_default_timezone_set($timeZone[0]);

			if (!is_numeric($deliveryHour)) {
				$deliveryHour = date('G', strtotime($deliveryHour));
			}

			$localTimestamp = strtotime("$deliveryDate $deliveryHour:00:00");

			$localFormatted = date('Y-m-d\TH:i:s', $localTimestamp);
			$utcTime = self::convertToUtc($localFormatted, $timeZone[0]);

			return $utcTime;
		}
		// Está versión lleva ya reglas de validación - modificado 2 junio 2025 by spark-jesus
		private static function getPickupDate(string $state, string $deliveryDate, $deliveryHour): string
		{
			$timeZone = self::getTimeZone($state);
			date_default_timezone_set($timeZone[0]);

			$now = new DateTime('now', new DateTimeZone($timeZone[0]));
			$today = (clone $now)->setTime(0, 0, 0);
			$selectedDate = DateTime::createFromFormat('Y-m-d', $deliveryDate, new DateTimeZone($timeZone[0]));

			if (!is_numeric($deliveryHour)) {
				$deliveryHour = (int) date('G', strtotime($deliveryHour));
			}

			if (!$selectedDate || $selectedDate < $today) {
				$selectedDate = (clone $today);
			}

			$dayOfWeek = (int) $selectedDate->format('w');
			$validHours = ($dayOfWeek === 0) ? [11, 12] : [10, 11, 12, 13, 14, 15, 16];

			if ($selectedDate->format('Y-m-d') === $now->format('Y-m-d')) {
				$currentTotalMinutes = ((int) $now->format('G') * 60) + (int) $now->format('i');
				$cutoffMinutes = $currentTotalMinutes + 120;

				$validHours = array_filter($validHours, function ($hour) use ($cutoffMinutes) {
					$slotStart = $hour * 60;
					$slotEnd = $slotStart + 60;
					return $slotEnd >= $cutoffMinutes;
				});
			}

			if (!in_array($deliveryHour, $validHours)) {
				if (count($validHours) > 0) {
					$deliveryHour = min($validHours);
				} else {
					do {
						$selectedDate->modify('+1 day');
						$nextDayOfWeek = (int) $selectedDate->format('w');
						$validHours = ($nextDayOfWeek === 0) ? [11, 12] : [10, 11, 12, 13, 14, 15, 16];
					} while (count($validHours) === 0);

					$deliveryHour = min($validHours);
				}
			}

			$localTimestamp = strtotime($selectedDate->format('Y-m-d') . " {$deliveryHour}:00:00");

			$localFormatted = date('Y-m-d\TH:i:s', $localTimestamp);
			$utcTime = self::convertToUtc($localFormatted, $timeZone[0]);

			return $utcTime;
		}


		/**
		 * Convierte una fecha en una zona horaria específica a UTC en formato ISO 8601.
		 */
		private static function convertToUtc(string $localDateTime, ?string $localTimeZone): string
		{
			// Usar "America/Mexico_City" como valor por defecto si no se proporciona una zona horaria válida
			$safeTimeZone = $localTimeZone ?: 'America/Mexico_City';

			try {
				$date = new DateTime($localDateTime, new DateTimeZone($safeTimeZone));
				$date->setTimezone(new DateTimeZone('UTC'));
				return $date->format('Y-m-d\TH:i:s\Z');
			} catch (Exception $e) {
				// En caso de error (por ejemplo, zona no válida), registrar y lanzar una excepción genérica
				error_log("Error al convertir fecha a UTC: " . $e->getMessage());
				return gmdate('Y-m-d\TH:i:s\Z', strtotime($localDateTime)); // fallback absoluto
			}
		}

		/**
		 * Obtener zona horaria.
		 * Obtiene la zona horaria relacionada al estado de la republica.
		 * @param string $state
		 * @return array
		 */
		private static function getTimeZone(string $state): array
		{
			//https://cambiohorario.com/zonas/mx/
			$timesZones = [
				'America/Tijuana' => [
					'Baja California',
					'time' => '08:00'
				],
				'America/Mazatlan' => [
					'Baja California Sur',
					'Nayarit',
					'Sinaloa',
					'Sonora',
					'time' => '07:00'
				],
				'America/Mexico_City' => [
					'Aguascalientes',
					'Campeche',
					'Chihuahua',
					'Coahuila',
					'Colima',
					'Chiapas',
					'Ciudad de México',
					'Durango',
					'Guanajuato',
					'Guerrero',
					'Hidalgo',
					'Jalisco',
					'Estado de México',
					'Michoacán',
					'Morelos',
					'Nuevo León',
					'Oaxaca',
					'Puebla',
					'Querétaro',
					'San Luis Potosí',
					'Tabasco',
					'Tamaulipas',
					'Tlaxcala',
					'Veracruz',
					'Yucatán',
					'Zacatecas',
					'time' => '06:00'
				],
				'America/Cancun' => [
					'Quintana Roo',
					'time' => '05:00'
				]
			];

			$timeZone = [];
			foreach ($timesZones as $key => $zone) {
				if (in_array($state, $zone)) {
					$timeZone = [$key, $zone['time']];
					break;
				}
			}

			if (empty($timeZone)) {
				$timeZone = ['America/Mexico_City', '06:00'];
			}

			return $timeZone;
		}
	}

	new UD_Uber_Deliveries();
}

add_action('rest_api_init', function () {
	register_rest_route('custom/v1', '/enviar-pedido-uber', [
		'methods' => 'POST',
		'callback' => function ($request) {
			$params = $request->get_params();

			if (!isset($params['order_id'])) {
				return new WP_Error('missing_param', 'El parámetro order_id es obligatorio', ['status' => 400]);
			}

			error_log('Order ID recibido: ' . $params['order_id']);

			return UD_Uber_Deliveries::enviar_pedido_a_uber_y_notificar_cliente($params['order_id']);
		},
		'args' => [
			'order_id' => [
				'required' => true,
				'type' => 'integer',
				'validate_callback' => function ($param) {
					return is_numeric($param) && $param > 0;
				},
			],
		],
	]);
});


add_action('rest_api_init', function () {
	register_rest_route('uber/v1', '/status', array(
		'methods' => 'POST',
		'callback' => 'handle_uber_webhook',
		'permission_callback' => '__return_true',
	));
});

/**
 * Handler del webhook de Uber Direct.
 */
function handle_uber_webhook(WP_REST_Request $request)
{
	$data = $request->get_json_params();
	$logger = function_exists('wc_get_logger') ? wc_get_logger() : null;
	$ctx = ['source' => 'uber-deliveries-webhook', 'fn' => __METHOD__];

	// --- Firma HMAC ---
	$signature = $request->get_header('X-Uber-Signature');
	$secret = '3c571fb1-cead-4f23-8e8c-3599ae08e22a'; // <-- tu secret
	$raw_body = $request->get_body();
	$calc = hash_hmac('sha256', $raw_body, $secret);

	if ($logger)
		$logger->info('Webhook recibido', $ctx + ['data' => $data]);

	if (!$signature || !hash_equals($calc, $signature)) {
		if ($logger)
			$logger->error('Firma no válida', $ctx + [
				'calculated_signature' => $calc,
				'received_signature' => $signature,
			]);
		return new WP_REST_Response('Firma no válida', 400);
	}

	// --- Campos mínimos ---
	if (empty($data['data']['external_id'])) {
		if ($logger)
			$logger->error('Falta external_id', $ctx + ['payload' => $data]);
		return new WP_REST_Response('Falta external_id', 400);
	}

	// Resolver order_id desde external_id: "CM-21879", "21879", "21901 segundo intento", etc.
	$external_id = (string) ($data['data']['external_id'] ?? '');
	$order_id = 0;

	if (strpos($external_id, '-') !== false) {
		$after = substr($external_id, strpos($external_id, '-') + 1);
		if (preg_match('/\d+/', $after, $m)) {
			$order_id = (int) $m[0];
		}
	}
	if ($order_id === 0 && preg_match('/\d+/', $external_id, $m)) {
		$order_id = (int) $m[0];
	}
	if ($order_id <= 0) {
		if ($logger)
			$logger->error('No se pudo resolver order_id desde external_id', $ctx + ['external_id' => $external_id]);
		return new WP_REST_Response('external_id inválido', 400);
	}

	$order = wc_get_order($order_id);
	if (!$order) {
		if ($logger)
			$logger->error('Pedido no encontrado', $ctx + ['order_id' => $order_id]);
		return new WP_REST_Response('Pedido no encontrado', 404);
	}

	// --- Identificadores del evento y de la entrega ---
	$evt_id = $data['id'] ?? $data['event_id'] ?? '';               // ej. evt_...
	$delivery_id = $data['delivery_id'] ?? ($data['data']['id'] ?? '');  // ej. del_...
	if (!$evt_id)
		return new WP_REST_Response('Falta evt_id', 400);
	if (!$delivery_id)
		return new WP_REST_Response('Falta delivery_id', 400);

	$status = $data['data']['status'] ?? '';                 // pending|pickup|dropoff|delivered|...
	$imminent = (bool) ($data['data']['courier_imminent'] ?? false);
	$evt_ts = $data['data']['updated'] ?? $data['created'] ?? $data['data']['created'] ?? current_time('mysql');
	if (!$status)
		return new WP_REST_Response('Falta status', 400);

	// --- Si cambia la entrega, reiniciar contexto ---
	$active_delivery = get_post_meta($order_id, '_uber_active_delivery_id', true);
	if ($active_delivery && $active_delivery !== $delivery_id) {
		update_post_meta($order_id, '_uber_evt_ids', []);       // limpiar anti-duplicado de evt_id
		update_post_meta($order_id, '_uber_status_level', -1);  // reiniciar a -1
		update_post_meta($order_id, '_uber_status_name', '');
		update_post_meta($order_id, '_uber_status_time', '');
		// reset flags de "a 1 minuto"
		delete_post_meta($order_id, '_uber_pickup_imminent_noted');
		delete_post_meta($order_id, '_uber_dropoff_imminent_noted');

		$order->add_order_note("🔁 Nueva entrega de Uber iniciada.\nDelivery ID: {$delivery_id}", false);
		if ($logger)
			$logger->info('Cambio de delivery_id: se resetea estado', $ctx + [
				'prev_delivery' => $active_delivery,
				'new_delivery' => $delivery_id,
			]);
	}
	update_post_meta($order_id, '_uber_active_delivery_id', $delivery_id);

	// --- Anti-duplicado por evt_id (reintentos) ---
	$processed = get_post_meta($order_id, '_uber_evt_ids', true);
	if (!is_array($processed))
		$processed = [];
	if (in_array($evt_id, $processed, true)) {
		if ($logger)
			$logger->info('Evento ya procesado (duplicado)', $ctx + [
				'evt_id' => $evt_id,
				'delivery_id' => $delivery_id
			]);
		return new WP_REST_Response('OK (duplicado)', 200);
	}

	// --- Progresión de estados ---
	$LEVEL = [
		'pending' => 0,
		'pickup' => 10,
		'pickup_complete' => 20,
		'dropoff' => 30,
		'delivered' => 40,
		'returned' => 90,
		'canceled' => 100,
	];
	$new_level = $LEVEL[$status] ?? -1;

	// Si no existe meta, arrancar en -1 para permitir el primer pending(0)
	$meta_exists = metadata_exists('post', $order_id, '_uber_status_level');
	$cur_level = (int) get_post_meta($order_id, '_uber_status_level', true);
	if (!$meta_exists)
		$cur_level = -1;

	// Ignorar SOLO si es estrictamente más viejo y no es cancel/returned
	if ($new_level !== -1 && $new_level < $cur_level && !in_array($status, ['returned', 'canceled'], true)) {
		$processed[] = $evt_id;
		$processed = array_slice($processed, -20);
		update_post_meta($order_id, '_uber_evt_ids', $processed);
		if ($logger)
			$logger->info('Evento fuera de orden ignorado (nivel menor)', $ctx + [
				'evt_id' => $evt_id,
				'status' => $status,
				'new_level' => $new_level,
				'cur_level' => $cur_level,
				'delivery_id' => $delivery_id
			]);
		return new WP_REST_Response('OK (out-of-order)', 200);
	}

	// ---------- NUEVO: decidir si añadimos nota ----------
	$should_add_note = false;

	// 1) Añadir nota cuando hay avance real de estado
	if ($new_level > $cur_level) {
		$should_add_note = true;
	}
	// 2) Para pickup/dropoff con "a 1 minuto", anotar solo una vez por fase
	elseif (in_array($status, ['pickup', 'dropoff'], true) && $imminent) {
		$flag_key = '_uber_' . $status . '_imminent_noted'; // _uber_pickup_imminent_noted | _uber_dropoff_imminent_noted
		if (!get_post_meta($order_id, $flag_key, true)) {
			$should_add_note = true;
			update_post_meta($order_id, $flag_key, 1);
		}
	}

	// Mensajes para notas
	if ($should_add_note) {
		$status_notes = [
			'pending' => '✅ El delivery fue aceptado; aún sin mensajero asignado.',
			'pickup' => $imminent
				? '📦 El mensajero está **a 1 minuto** de la tienda para recoger.'
				: '📦 Mensajero asignado: va hacia la tienda para recoger.',
			'pickup_complete' => '📬 El mensajero **ya recogió** el pedido en tienda.',
			'dropoff' => $imminent
				? '🚚 El mensajero está **a 1 minuto** del punto de entrega.'
				: '🚚 El mensajero va camino al punto de entrega.',
			'delivered' => '🎉 **Pedido entregado** al cliente.',
			'canceled' => '❌ El delivery fue **cancelado**.',
			'returned' => '↩️ El pedido fue **devuelto** al remitente.',
			'default' => "ℹ️ Actualización de Uber: estado **{$status}**.",
		];
		$note = $status_notes[$status] ?? $status_notes['default'];

		// Datos extra
		$pin = $data['data']['dropoff']['verification']['pin_code']['entered'] ?? '';
		$track = $data['data']['tracking_url'] ?? '';
		$extra = [];
		if ($pin)
			$extra[] = "PIN: {$pin}";
		if ($track)
			$extra[] = "Tracking: {$track}";
		$extra[] = "Delivery ID: {$delivery_id}";
		if ($extra)
			$note .= "\n" . implode(' · ', $extra);

		// Añadir nota
		$order->add_order_note($note, false);
	} else {
		if ($logger)
			$logger->info('Sin nota: estado repetido / update intermedio', $ctx + [
				'evt_id' => $evt_id,
				'status' => $status,
				'imminent' => $imminent,
				'cur_level' => $cur_level,
				'new_level' => $new_level,
			]);
	}

	// Guardar evt_id (cap 20) SIEMPRE para evitar reintentos
	$processed[] = $evt_id;
	$processed = array_slice($processed, -20);
	update_post_meta($order_id, '_uber_evt_ids', $processed);

	// Actualizar nivel/nombre/tiempo solo si avanzó
	if ($new_level > $cur_level) {
		update_post_meta($order_id, '_uber_status_level', $new_level);
		update_post_meta($order_id, '_uber_status_name', $status);
		update_post_meta($order_id, '_uber_status_time', $evt_ts);
	}

	// Al entregar: marcar y enviar correo (protegido contra duplicados)
	if ($status === 'delivered') {
		update_post_meta($order_id, '_uber_delivered_at', $evt_ts);
		if (!get_post_meta($order_id, '_uber_delivered_email_sent', true)) {
			// obtenemos el email
			$to = $order->get_billing_email();
			cm_send_order_delivered_email($to);
			update_post_meta($order_id, '_uber_delivered_email_sent', 1);
		}
	}

	if ($logger)
		$logger->info('Evento aplicado', $ctx + [
			'order_id' => $order_id,
			'evt_id' => $evt_id,
			'status' => $status,
			'new_level' => $new_level,
			'prev_level' => $cur_level,
			'delivery_id' => $delivery_id
		]);

	return new WP_REST_Response('OK', 200);
}

/** Content-Type HTML (por si no lo tienes ya) */
if (!function_exists('cm_set_html_content_type')) {
	function cm_set_html_content_type($ct = '')
	{
		return 'text/html; charset=UTF-8';
	}
}

/** Host del sitio para construir no-reply@dominio */
if (!function_exists('cm_brand_site_host')) {
	function cm_brand_site_host()
	{
		$host = parse_url(home_url(), PHP_URL_HOST);
		if (!$host) {
			$host = $_SERVER['HTTP_HOST'] ?? 'carnemart.com';
		}
		return $host;
	}
}

/** Host del sitio (para construir no-reply@dominio) */
if (!function_exists('cm_ev_get_site_host')) {
	function cm_ev_get_site_host()
	{
		$host = parse_url(home_url(), PHP_URL_HOST);
		if (!$host) {
			$host = $_SERVER['HTTP_HOST'] ?? 'carnemart.com';
		}
		return $host;
	}
}
if (!function_exists('cm_send_order_delivered_email')) {
	function cm_send_order_delivered_email($to, $vars = array())
	{
		$defaults = array(
			'customer_name' => 'Cliente',
			'order_id' => 'CM-000000',
			'site_url' => 'https://carnemart.com/',
			'wa_url' => 'https://wa.me/5216141296248?text=Hola%2C%20deseo%20comunicarme%20con%20ustedes',
			'subject' => '¡Tu pedido ha sido entregado! – Carnemart',
			'logo_url' => 'https://carnemart.com/wp-content/uploads/2025/09/logo-carnemart@2x.png',
			'banner_url' => 'https://carnemart.com/wp-content/uploads/2025/09/mapa@2x.png',
			'cta_site_img' => 'https://carnemart.com/wp-content/uploads/2025/09/cta-carnemart@2x.png',
			'cta_phone_img' => 'https://carnemart.com/wp-content/uploads/2025/09/cta-telefono@2x.png',
		);
		$v = array_merge($defaults, (array) $vars);

		$headers = array();
		// $headers[] = 'From: Carnemart <no-reply@carnemart.com>';

		// ======= HTML EXACTO ======= //
		$html = <<<'HTML'
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
	xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="x-apple-disable-message-reformatting">
	<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
	<style>
		@font-face {
			font-family: 'Poppins';
			font-style: normal;
			font-weight: 300;
			font-display: swap;
			src:
				url(https://fonts.gstatic.com/s/poppins/v21/pxiByp8kv8JHgFVrLDz8Z1xlFQ.woff2) format('woff2'),
				url(https://fonts.gstatic.com/s/poppins/v21/pxiByp8kv8JHgFVrLDz8Z1xlEA.woff) format('woff');
		}

		@font-face {
			font-family: 'Poppins';
			font-style: normal;
			font-weight: 400;
			font-display: swap;
			src:
				url(https://fonts.gstatic.com/s/poppins/v21/pxiEyp8kv8JHgFVrJJfecnFHGPc.woff2) format('woff2'),
				url(https://fonts.gstatic.com/s/poppins/v21/pxiEyp8kv8JHgFVrJJfecg.woff) format('woff');
		}

		@font-face {
			font-family: 'Poppins';
			font-style: normal;
			font-weight: 700;
			font-display: swap;
			src:
				url(https://fonts.gstatic.com/s/poppins/v21/pxiByp8kv8JHgFVrLEj6Z19VFQ.woff2) format('woff2'),
				url(https://fonts.gstatic.com/s/poppins/v21/pxiByp8kv8JHgFVrLEj6Z1xlEA.woff) format('woff');
		}

		@media (prefers-color-scheme: dark) {
			.bg-white {
				background: #0b0b0b !important;
			}

			.text-dark {
				color: #ffffff !important;
			}

			.bg-brand {
				background: #021b6d !important;
			}

			/* tu franja azul */
			.text-brand {
				color: #ffffff !important;
			}

			/* Evita invertir logos si algún cliente los invierte */
			.no-invert img {
				filter: none !important;
			}
		}


		/* Global base */
		body,
		table,
		td,
		p,
		a,
		span,
		strong,
		em,
		h1,
		h2,
		h3,
		h4,
		h5,
		h6 {
			font-family: 'Poppins', Arial, sans-serif !important;
		}

		table {
			border-collapse: collapse !important;
		}

		@media screen and (max-width: 616px) {

			.block {
				display: block !important;
				width: 100% !important;
			}

			table.responsive-table {
				width: 100% !important;
				display: block !important;
			}

			.col-lge {
				max-width: 100% !important;
			}

			img[class="img-max"] {
				max-width: 100% !important;
				height: auto !important;
			}

			img.pasosimgmob {
				width: 90% !important;
				max-width: 90% !important;
			}

			img.header-logo-img {
				width: 160px !important;
				max-width: 160px !important;
			}

			p.texto-cu-big-extra {
				font-size: 26px !important;
			}

			p.texto-cu-big {
				font-size: 17px !important;
			}

			p.texto-cu-card {
				font-size: 17px !important;
			}

			span.mini-info {
				font-size: 14px !important;
			}

			p.cta-low {
				font-size: 12px !important;
			}

			table.table-content {
				width: 100% !important;
			}

			.stack-column,
			.stack-column td {
				display: block !important;
				width: 100% !important;
				text-align: center !important;
			}

			.cta-footer {
				margin: 0 auto !important;
				/* centra las imágenes */
				height: auto !important;
			}


			.no-stack td {
				display: table-cell !important;
				width: auto !important;
				text-align: center !important;
			}

			.td-54 {
				width: 54% !important;
			}

			.td-46 {
				width: 46% !important;
			}

			.td-33 {
				width: 33% !important;
			}

			.ico-30 {
				height: 30px !important;
				width: auto !important;
				max-width: none !important;
			}

			/* Mantén 2 columnas pero hazlas 50/50 para evitar redondeos */
			.tbl-2 {
				width: 100% !important;
				table-layout: fixed !important;
			}

			.td-54 {
				width: 54% !important;
				text-align: right !important;
			}

			.td-46 {
				width: 46% !important;
				text-align: left !important;
			}

			.ico-30 {
				height: 30px !important;
				width: auto !important;
				display: block !important;
			}



		}

		@media screen and (min-width: 615px) {
			.col-sml {
				max-width: 27% !important;
			}

			.col-lge {
				max-width: 73% !important;
			}
		}


		@media screen and (max-width: 390px) {
			p.texto-cu-big-extra {
				font-size: 18px !important;
			}
		}
	</style>


</head>
<div style="display:none;max-height:0;overflow:hidden;line-height:1px;opacity:0;">
	¡Tu pedido fue entregado! Gracias por elegir Carnemart. Suma puntos en cada compra.
	&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
</div>

<body
	style="margin:0;padding:0;word-spacing:normal;background-color:#f6f6f6; font-family:'Poppins', Arial, sans-serif !important;"
	class="body-text">
	<div role="article" aria-roledescription="email"
		style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#f6f6f6">
		<table role="presentation" style="width:100%;border:none;border-spacing:0;">
			<tr>

				<td align="center" style="padding:0;">
					<!--[if mso]>
          <table role="presentation" align="center" style="width:600px;">
          <tr>
          <td>
          <![endif]-->

					<table cellpadding="0" cellspacing="0" border="0" role="presentation" bgcolor="#ffffff" width="600"
						valign="top"
						style="width:100%;max-width:600px;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;">

						<!-- Call to action -->
						<tr>
							<td border="0" cellspacing="0" cellpadding="0" align="center"
								style="line-height:0;border-collapse:collapse!important; border: none!important; padding-top:20px;padding-bottom:20px;padding-left: 10px;padding-right: 10px;">
								<a href="https://carnemart.com/" target="_blank"
									style="line-height:0;border-collapse:collapse!important; border: none!important;">
									<img alt="CarneMart" class="header-logo-img"
										src="https://carnemart.com/wp-content/uploads/2025/09/logo-carnemart@2x.png"
										width="200"
										style="width: 200px; max-width: 100%; font-family: sans-serif; color: #ffffff; font-size: 0; display: block; border: 0px;"
										border="0">
								</a>
							</td>
						</tr>

						<!-- Mensaje incial -->
						<tr>
							<td align="center"
								style="padding-top:5px;padding-bottom:5px;padding-left: 5px;padding-right: 5px; background-color:#ffffff;">
								<table class="table-content" style="width:100%;" role="presentation">
									<tbody>
										<tr>
											<td align="center">

												<table style="width: 100%;"
													style="margin:0;padding:0;word-spacing:normal;" role="presentation">
													<tbody>
														<tr>
															<td>&nbsp;</td>
															<td width="75%"
																style="background: #021b6d; background-color: #021b6d; padding-top:35px;padding-bottom:35px;">
																<p class="texto-cu-big-extra"
																	style="font-size:40px; font-family:'Poppins', Arial, sans-serif; font-weight:700!important; color: #ffffff; padding:0!important;margin:0!important;letter-spacing:0px;line-height:1.2!important;text-align: center;">
																	<strong>¡Tú pedido ha<br> sido entregado!</strong>
																</p>
															</td>
															<td>&nbsp;</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>

						<!-- Bloque de contenido -->
						<tr>
							<td align="left"
								style="padding-top:25px;padding-bottom:25px;padding-left: 20px;padding-right: 20px; background-color:#ffffff;">
								<table class="table-content" style="width:100%;" role="presentation">
									<tbody>
										<tr>
											<td align="center">

												<p
													style="font-size:16px;font-family:'Poppins', Arial, sans-serif; font-weight:400; color: #0866fd; padding:0!important;margin-bottom:4px!important;font-weight:normal;letter-spacing:0px;line-height:1.2!important;">
													Tu pedido fue entregado con éxito ✅ <br>¡Gracias por elegir
													Carnemart!
												</p>
												<p
													style="font-size:16px;font-family:'Poppins', Arial, sans-serif; font-weight:400; color: #0866fd; padding:0!important;margin-bottom:4px!important;font-weight:normal;letter-spacing:0px;line-height:1.2!important;">
													No olvides que con cada compra sumas puntos GANAmásLANA.
												</p>

												<p
													style="font-size:16px;font-family:'Poppins', Arial, sans-serif; font-weight:400; color: #0866fd; padding:0!important;margin-bottom:4px!important;font-weight:normal;letter-spacing:0px;line-height:1.2!important;">
													Te esperamos en tu próxima compra en <a
														href="https://carnemart.com/" target="_blank"
														style="color: #0866fd!important;"><span
															style="color: #0866fd!important;">www.carnemart.com</span></a>
													para seguir acumulando y disfrutando de más beneficios.
												</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>

						<!-- Banner -->
						<tr>
							<td border="0" cellspacing="0" cellpadding="0"
								style="line-height:0;border-collapse:collapse!important; border: none!important;">
								<img alt="Entrega realizada con éxito Carnemart"
									src="https://carnemart.com/wp-content/uploads/2025/09/mapa@2x.png" width="600"
									style="width: 100%; max-width: 100%; font-family: sans-serif; color: #ffffff; font-size: 0; display: block; border: 0px;"
									border="0">
							</td>
						</tr>

						<!-- Enlaces Footer -->
						<tr>
							<td align="center" border="0" cellspacing="0" cellpadding="0" align="center"
								style="line-height:0;border-collapse:collapse!important; border: none!important; padding-top:50px;padding-bottom:20px;padding-left: 10px;padding-right: 10px;">


								<table style="width:100%; display: none;" border="0" cellpadding="4" cellspacing="0"
									role="presentation">
									<tbody>
										<tr>
											<!-- Columna 1 -->
											<td class="stack-column" align="right" width="54%">
												<a href="https://carnemart.com/" target="_blank"
													style="line-height:0;border-collapse:collapse!important; border:none!important;">
													<img alt="CarneMart sitio web" class="cta-footer"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-carnemart@2x-1.png"
														width="180"
														style="width:180px; max-width:100%; display:block; border:0;"
														border="0">
												</a>
											</td>

											<!-- Columna 2 -->
											<td class="stack-column" align="left" width="46%">
												<a href="https://wa.me/5216141296248?text=Hola%2C%20deseo%20comunicarme%20con%20ustedes"
													target="_blank" rel="noopener noreferrer"
													style="line-height:0;border-collapse:collapse!important; border:none!important;">
													<img alt="CarneMart Atención a clientes" class="cta-footer"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-telefono@2x-1.png"
														width="133"
														style="width:133px; max-width:100%; display:block; border:0;"
														border="0">
												</a>
											</td>
										</tr>
									</tbody>
								</table>



								<table style="width:100%; display: none;" border="0" cellpadding="4" cellspacing="0"
									role="presentation">
									<tbody>
										<tr>
											<td class="stack-column" align="right" width="33%">
												<a href="https://www.youtube.com/@carnemart5388" target="_blank"
													style="line-height:0;border-collapse:collapse!important; border:none!important;">
													<img alt="CarneMart Youtube" class="cta-footer"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-youtube@2x.png"
														width="156"
														style="width:156px; max-width:100%; display:block; border:0;"
														border="0">
												</a>
											</td>

											<td class="stack-column" align="center" width="33%">
												<a href="https://www.facebook.com/Carnemart/" target="_blank"
													rel="noopener noreferrer"
													style="line-height:0;border-collapse:collapse!important; border:none!important;">
													<img alt="CarneMart Facebook" class="cta-footer"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-facebook@2x.png"
														width="177"
														style="width:177px; max-width:100%; display:block; border:0;"
														border="0">
												</a>
											</td>

											<td class="stack-column" align="left" width="33%">
												<a href="https://www.instagram.com/carnemart/" target="_blank"
													rel="noopener noreferrer"
													style="line-height:0;border-collapse:collapse!important; border:none!important;">
													<img alt="CarneMart Instagram" class="cta-footer"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-instagram@2x.png"
														width="132"
														style="width:132px; max-width:100%; display:block; border:0;"
														border="0">
												</a>
											</td>
										</tr>
									</tbody>
								</table>

								<!-- sin apilar -->

								<table class="tbl-2" style="width:100%;" border="0" cellpadding="4" cellspacing="0"
									role="presentation">
									<tbody>
										<tr style="font-size:0; line-height:0;">
											<!-- Columna 1 (queda a la izquierda en desktop y a la derecha del TD en mobile) -->
											<td class="td-54" align="right" width="54%">
												<a href="https://carnemart.com/" target="_blank"
													style="line-height:0; border:none!important;">
													<img alt="CarneMart sitio web" class="ico-30"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-carnemart@2x-1.png"
														width="180" height="30"
														style="display:block; margin:0 0 0 auto; border:0; height:30px; width:auto;"
														border="0">
												</a>
											</td>

											<!-- Columna 2 -->
											<td class="td-46" align="left" width="46%">
												<a href="https://wa.me/5216141296248?text=Hola%2C%20deseo%20comunicarm e%20con%20ustedes"
													target="_blank" rel="noopener noreferrer"
													style="line-height:0; border:none!important;">
													<img alt="CarneMart Atención a clientes" class="ico-30"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-telefono@2x-1.png"
														width="133" height="30"
														style="display:block; margin:0 auto 0 0; border:0; height:30px; width:auto;"
														border="0">
												</a>
											</td>
										</tr>
									</tbody>
								</table>


								<table class="no-stack" style="width:100%;" border="0" cellpadding="4" cellspacing="0"
									role="presentation">
									<tbody>
										<tr style="font-size:0; line-height:0;">
											<td class="td-33" align="right" width="33%">
												<a href="https://www.youtube.com/@carnemart5388" target="_blank"
													style="line-height:0; border:none !important;">
													<img alt="CarneMart Youtube" class="ico-30"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-youtube@2x.png"
														width="156" height="30"
														style="display:block; border:0; height:30px; width:auto; font-size:0; line-height:0;"
														border="0">
												</a>
											</td>
											<td class="td-33" align="center" width="33%">
												<a href="https://www.facebook.com/CarneMartOficial" target="_blank"
													rel="noopener noreferrer"
													style="line-height:0; border:none !important;">
													<img alt="CarneMart Facebook" class="ico-30"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-facebook@2x.png"
														width="177" height="30"
														style="display:block; border:0; height:30px; width:auto; font-size:0; line-height:0;"
														border="0">
												</a>
											</td>
											<td class="td-33" align="left" width="33%">
												<a href="https://www.instagram.com/carnemart/" target="_blank"
													rel="noopener noreferrer"
													style="line-height:0; border:none !important;">
													<img alt="CarneMart Instagram" class="ico-30"
														src="https://carnemart.com/wp-content/uploads/2025/09/cta-instagram@2x.png"
														width="132" height="30"
														style="display:block; border:0; height:30px; width:auto; font-size:0; line-height:0;"
														border="0">
												</a>
											</td>
										</tr>
									</tbody>
								</table>



							</td>
						</tr>

						<!-- Salida -->
						<tr>
							<td align="center"
								style="padding-top:10px;padding-bottom:10px;padding-left: 45px;padding-right: 45px; background-color:#ffffff;">
								<table class="table-content" style="width:100%;" role="presentation">
									<tbody>
										<tr>
											<td align="center" height="30">
												<p
													style="font-size:18px;font-family:Arial, sans-serif; color: #000000; padding:0!important;margin-top:4px!important;margin-bottom:4px!important;font-weight:normal;letter-spacing:-0.02em;line-height:1.1!important;">
												</p>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>


					</table>
					<!--[if mso]>
          </td>
          </tr>
          </table>
          <![endif]-->
				</td>
			</tr>
		</table>
	</div>
</body>

</html>
HTML;
		// Filtros temporales (closures) para evitar colisiones de nombres
		$content_type_cb = function () {
			return 'text/html; charset=UTF-8';
		};
		$from_name_cb = function () {
			return 'CarneMart';
		};
		$from_addr_cb = function () {
			return 'no-reply@' . cm_brand_site_host();
		};

		add_filter('wp_mail_content_type', $content_type_cb, 999);
		add_filter('wp_mail_from_name', $from_name_cb, 999);
		add_filter('wp_mail_from', $from_addr_cb, 999);

		// Cabeceras opcionales (visible para respuestas)
		$headers = array(
			'Reply-To: Atención a clientes <atencion@' . cm_brand_site_host() . '>',
			// Refuerza el From también por cabecera (además de los filtros)
			'From: CarneMart <no-reply@' . cm_brand_site_host() . '>',
		);

		// Reemplazar placeholders
		foreach ($v as $key => $val) {
			$html = str_replace('{{' . $key . '}}', (string) $val, $html);
		}

		try {
			$sent = wp_mail($to, $v['subject'], $html, $headers);
		} finally {
			// Quitamos filtros para no afectar otros correos
			remove_filter('wp_mail_content_type', $content_type_cb, 999);
			remove_filter('wp_mail_from_name', $from_name_cb, 999);
			remove_filter('wp_mail_from', $from_addr_cb, 999);
		}

		return (bool) $sent;
	}
}

// // Solo admins + nonce pueden disparar el test por URL
// add_action('init', function () {
// 	if (!isset($_GET['test_uber_email']) || $_GET['test_uber_email'] !== '1') {
// 		return;
// 	}

// 	// 1) Debe estar logueado y ser admin
// 	if (!is_user_logged_in() || !current_user_can('manage_options')) {
// 		wp_die('No autorizado.', '403 Forbidden', array('response' => 403));
// 	}

// 	// 2) Nonce obligatorio (añádelo a la URL con wp_nonce_url)
// 	if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'test_uber_email')) {
// 		wp_die('Nonce inválido.', '403 Forbidden', array('response' => 403));
// 	}

// 	// 3) Ejecuta el envío de prueba
// 	$ok = cm_send_order_delivered_email('test@gmail.com');
// 	wp_die($ok ? 'OK: email enviado.' : 'ERROR: wp_mail devolvió false.');
// });