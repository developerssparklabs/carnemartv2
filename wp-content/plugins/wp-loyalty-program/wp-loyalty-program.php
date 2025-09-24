<?php

/**
 * Plugin Name: Bafar :: Puntos Gana más 💲
 * Plugin URI: https://woocommerce.com/products/sales-booster-for-woocommerce/
 * Description: Loyalty Program.
 * Version: 1.0.0
 * Author: SparkLabs
 * Developer: SpartLabs
 * Text Domain: loyalty-program
 **/
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
if (!defined('WC_LP_DIR')) {
	define('WC_LP_DIR', plugin_dir_path(__FILE__));
}
if (!defined('WC_LP_URL')) {
	define('WC_LP_URL', plugin_dir_url(__FILE__));
}
if (!class_exists('Loyalty_Program')) {
	class Loyalty_Program
	{
		public function __construct()
		{
			if (!function_exists('is_plugin_active_for_network')) {
				require_once(ABSPATH . '/wp-admin/includes/plugin.php');
			}
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_plugin_active_for_network('woocommerce/woocommerce.php')) {
				if (function_exists('load_plugin_textdomain')) {
					load_plugin_textdomain('loyalty-program', false, dirname(plugin_basename(__FILE__)) . '/languages/');
				}
			} else {
				add_action('admin_notices', array($this, 'lp_admin_notices'));
			}
			//	add_action( 'before_woocommerce_init', array($this, 'lp_woocommerce_hpos_compatible' ) );
			//add_action('woocommerce_blocks_loaded', array($this, 'wc_load_blocks'));
			// Agregar la opción de "Puntos Gana Más" antes del total del pedido en checkout
			add_action('woocommerce_review_order_before_order_total', array($this, 'lp_display_loyalty_points_section'));
			// Manejar la aplicación de cupones de lealtad vía AJAX
			add_action('wp_ajax_apply_loyalty_coupons', array($this, 'apply_loyalty_coupons'));
			add_action('wp_ajax_nopriv_apply_loyalty_coupons', array($this, 'apply_loyalty_coupons'));
			add_action('wp_ajax_lp_apply_coupon', array($this, 'lp_apply_coupon'));
			add_action('wp_ajax_nopriv_lp_apply_coupon', array($this, 'lp_apply_coupon'));
			add_shortcode('lp_display_points', array($this, 'lp_display_points'));
			add_action('wp_head', array($this, 'lp_header_script'));
			// Hook para agregar el menú
			add_action('admin_menu', array($this, 'lp_add_admin_menu'));
			add_action('woocommerce_applied_coupon', function () {
				WC()->cart->calculate_totals();
				WC()->cart->maybe_set_cart_cookies();
			});
		}


		/*
		 * Obtiene las credenciales de la API
		 */
		public function lp_get_api_credentials()
		{
			// Verifica si la constante WP_ENVIRONMENT_TYPE está definida y prioriza su valor
			$environment = defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : get_option('lp_mode', 'production');

			if ($environment === 'dev') {
				return array(
					'url' => get_option('lp_url_development', ''),
					'username' => get_option('lp_user_development', ''),
					'password' => get_option('lp_password_development', ''),
				);
			} else {
				return array(
					'url' => get_option('lp_url_production', ''),
					'username' => get_option('lp_user_production', ''),
					'password' => get_option('lp_password_production', ''),
				);
			}
		}

		public function lp_header_script()
		{
			?>
			<style>
				.lp-customers-points {
					display: flex;
					justify-content: center;
					align-items: center;
				}

				.container {
					display: flex;
					gap: 20px;
				}

				.box {
					padding: 20px;
					display: flex;
					justify-content: center;
					align-items: center;
					width: 150px;
					height: 150px;
					background-color: #021b6d;
					color: white;
					font-size: 20px;
					border-radius: 10px;
				}
			</style>
			<?php
		}
		// 		public function lp_checkout_field_update_order_meta(  $order, $request  ) {
		// 			if ( empty($_COOKIE['selectedPoints']) ) {
		// 				return;
		// 			}
		// 			$points = $_COOKIE['selectedPoints'];
		// 			$user_id = $order->get_customer_id();
		// 			$crm = get_user_meta($user_id, 'customer_crm', true);
		// 			$username = 'SY_LOYALTY';
		// 			$password = 'aPibafar01*';
		// 			$location_id = $_COOKIE['wcmlim_selected_location_termid'];
		// 			$centro = get_term_meta($location_id, 'centro_location', true);
		// 			wp_remote_post(
		//                 'https://gwd.lineamccoy.com.mx/neptune/api/LOYALTY/INT042',
		//                 array(
		//                     'headers' => array(
		//                         'Content-Type' => 'application/json',
		// 						'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password )
		//                     ),
		//                     'body' => '[
		//   {
		//     "KEY": "IT_MP_PETICION",
		//     "VALUE": "[{\"id_organizacion_ventas\": \"3200\", \"id_cliente\": \"'.$crm.'\", \"id_tienda\": \"'.$centro.'\", \"fechahora\": \"2024-09-11T10:31:00\", \"id_tipomovimiento\": \"03\", \"valorpuntos\": \"'.$points.'\"}]"
		//   }
		// ]',
		//                 )
		//             );
		// 		}





		public function lp_display_points()
		{
			// ob_start();

			// Verificar si el usuario está logueado
			if (!is_user_logged_in()) {
				echo '<center><h2><a href="/mi-cuenta">Inicia sesión o Regístrate para obtener tus beneficios.</a></h2></center>';
				return;
			}

			// Obtener credenciales dinámicas
			$credentials = $this->lp_get_api_credentials();
			if (!$credentials || empty($credentials['url']) || empty($credentials['username']) || empty($credentials['password'])) {
				echo 'Error: Credenciales de API no configuradas correctamente.';
				return;
			}


			$user_id = get_current_user_id();
			$crm = get_user_meta($user_id, 'customer_crm', true);
			if (empty($crm)) {
				echo "<p><center><h2>Haz tu primer compra y empieza a participar en el programa de lealtad</h2></center></p>";
				// echo 'Error: No se encontró el CRM del usuario.';
				return;
			}


			if (!isset($credentials['url'], $credentials['username'], $credentials['password'])) {
				return [
					'error' => true,
					'message' => 'Faltan parámetros de credenciales (url, username, password).',
				];
			}


			// Crear el cuerpo de la solicitud
			$body = json_encode([
				[
					"username" => $credentials['username'],
					"password" => $credentials['password'],
					"url" => $credentials['url'] . "/INT0307", // Corrige la URL
					"KEY" => "IT_CSP_PETICION",
					"VALUE" => json_encode([ // Mantiene el array dentro del VALUE
						[
							"id_organizacion_ventas" => "3200",
							"id_cuenta" => $crm,
						]
					]),
				]
			]);


			// Realizar la solicitud API
			$response = wp_remote_post(
				"https://sistema.asofom.online/api/ganamas.php",
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'body' => $body,
				)
			);



			// Manejar errores
			if (is_wp_error($response)) {
				return [
					'error' => true,
					'message' => $response->get_error_message(),
				];
			}

			/*
	if ($response['error']) {
		echo "Error: " . $response['message'];
	} else {
		echo "Respuesta de la API: ";-|
	}1 cvfrt5
*/

			// Decodificar JSON
			$data = json_decode($response["body"], true);

			// Acceder a todos los campos
			$error = $data["error"];
			$http_code = $data["http_code"];

			if ($http_code == 200) {

				$it_csp_respuesta = $data["response"]["result"]["IT_CSP_RESPUESTA"];

				// Extraer información detallada dentro de IT_CSP_RESPUESTA
				foreach ($it_csp_respuesta as $respuesta) {
					$edo_result = $respuesta["EDO_RESULT"];
					$s_edo_result = $respuesta["S_EDO_RESULT"];
					$puntos_clienter = $respuesta["PUNTOSCLIENTER"];

					foreach ($puntos_clienter as $punto) {
						$cliente_id = $punto["CLIENTE_ID"];
						$disponibles = $punto["DISPONIBLES"];
						$vigencia = $punto["VIGENCIA"];
						$id_grupo = $punto["ID_GRUPO"];

						// Imprimir todos los valores
						/* echo "Error: " . ($error ? "true" : "false") . "\n";
		echo "HTTP Code: " . $http_code . "\n";
		echo "Estado Resultado: " . $edo_result . "\n";
		echo "Mensaje Resultado: " . $s_edo_result . "\n";
		echo "Cliente ID: " . $cliente_id . "\n";
		echo "Disponibles: " . $disponibles . "\n";
		echo "Vigencia: " . $vigencia . "\n";
		echo "ID Grupo: " . $id_grupo . "\n";
		echo "----------------------------------\n";*/
					}
				}
			}
			if (empty($disponibles)) {
				echo '<center><h2>Estimado cliente en este momento no pudimos consultar tus puntos</h2></center>';
				return;
			}



			if ($s_edo_result == "Cuenta no registrada") {
				echo '<center><h2><a href="/tienda">Realiza tu primer compra para obtener beneficios de Gana Más.</a></h2></center>';
				return;
			}


			if (!isset($puntos_clienter)) {
				echo 'Error: No se encontraron puntos en la respuesta API.';
				return;
			}

			$points = reset($puntos_clienter);

			// Verificar que los puntos existan
			if (empty($disponibles) || empty($vigencia)) {
				echo 'Error: Puntos o vigencia no disponibles en la respuesta API.';
				return;
			}

			// Mostrar puntos
			?>
			<div class="lp-customers-points">
				<div class="container">
					<div class="box">ID de cliente: <?php echo esc_html($crm); ?></div>
					<div class="box">Puntos Disponibles: <?php echo esc_html($disponibles); ?></div>
					<div class="box">Vigencia de tus puntos: <?php echo esc_html($vigencia); ?></div>
				</div>
			</div>
			<?php

			// return ob_get_clean();
		}

		public function lp_apply_coupon()
		{
			if (empty($_POST['coupon_code'])) {
				wp_send_json_error('No coupon code found');
			}
			$coupon_code = sanitize_text_field($_POST['coupon_code']);
			setcookie('selectedPoints', $_POST['selectedPoints'], time() + (86400 * 30), "/");
			;
			WC()->cart->remove_coupon('aprgsqfb');
			WC()->cart->remove_coupon('mpnbvmkr');
			WC()->cart->remove_coupon('nceybpp2');
			WC()->cart->remove_coupon('vzkrtwhq');
			WC()->cart->apply_coupon($coupon_code);
			if (WC()->cart->has_discount($coupon_code)) {
				wp_send_json_success('Coupon applied successfully.');
			} else {
				wp_send_json_error('Coupon application failed.');
			}
		}
		/*public function wc_load_blocks() {
		//	add_action('woocommerce_blocks_checkout_block_registration', array($this, 'wc_register_checkout_blocks'), 10, 1);
		}*/
		public function wc_register_checkout_blocks($integration_registry)
		{
			require_once WC_LP_DIR . 'blocks/sales-booster-checkout-integration.php';
			if (class_exists('Sales_Booster_Checkout_Integration')) {
				new Sales_Booster_Checkout_Integration();
			}
		}
		/*public function lp_woocommerce_hpos_compatible() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}*/
		public function lp_admin_notices()
		{
			global $pagenow;
			if ('plugins.php' === $pagenow) {
				$class = 'notice notice-error is-dismissible';
				$message = esc_html__('Loyalty Program needs WooCommerce to be installed and active.', 'loyalty-program');
				printf('<div class="%1$s"><p>%2$s</p></div>', esc_html($class), esc_html($message));
			}
		}




		public function lp_add_admin_menu()
		{
			add_menu_page(
				__('Puntos Gana Más', 'loyalty-program'),
				__('Puntos Gana Más', 'loyalty-program'),
				'manage_options',
				'lp-settings',
				array($this, 'lp_settings_page'),
				'dashicons-tickets', // Icono para el menú
				56 // Posición en el menú
			);
		}

		public function lp_settings_page()
		{
			// Comprueba si el usuario tiene permisos
			if (!current_user_can('manage_options')) {
				return;
			}

			// Guarda los datos al enviar el formulario
			if (isset($_POST['lp_save_settings'])) {
				update_option('lp_mode', isset($_POST['lp_mode']) ? sanitize_text_field($_POST['lp_mode']) : 'production');

				update_option('lp_user_development', sanitize_text_field($_POST['lp_user_development']));
				update_option('lp_password_development', sanitize_text_field($_POST['lp_password_development']));

				update_option('lp_user_production', sanitize_text_field($_POST['lp_user_production']));
				update_option('lp_password_production', sanitize_text_field($_POST['lp_password_production']));

				update_option('lp_url_development', sanitize_text_field($_POST['lp_url_development']));
				update_option('lp_url_production', sanitize_text_field($_POST['lp_url_production']));





				echo '<div class="updated"><p>' . __('Settings saved successfully.', 'loyalty-program') . '</p></div>';
			}

			// Página de configuración
			?>
			<div class="wrap">
				<h1><?php esc_html_e('Puntos Gana Más Settings', 'loyalty-program'); ?></h1>
				<form method="post">
					<table class="form-table">

						<tr>
							<th scope="row"><?php esc_html_e('Url dev', 'loyalty-program'); ?></th>
							<td>
								<input type="text" name="lp_url_development"
									value="<?php echo esc_attr(get_option('lp_url_development', '')); ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Development User', 'loyalty-program'); ?></th>
							<td>
								<input type="text" name="lp_user_development"
									value="<?php echo esc_attr(get_option('lp_user_development', '')); ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Development Password', 'loyalty-program'); ?></th>
							<td>
								<input type="text" name="lp_password_development"
									value="<?php echo esc_attr(get_option('lp_password_development', '')); ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('URL Producción', 'loyalty-program'); ?></th>
							<td>
								<input type="text" name="lp_url_production"
									value="<?php echo esc_attr(get_option('lp_url_production', '')); ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Production User', 'loyalty-program'); ?></th>
							<td>
								<input type="text" name="lp_user_production"
									value="<?php echo esc_attr(get_option('lp_user_production', '')); ?>" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e('Production Password', 'loyalty-program'); ?></th>
							<td>
								<input type="text" name="lp_password_production"
									value="<?php echo esc_attr(get_option('lp_password_production', '')); ?>" class="regular-text">
							</td>
						</tr>
					</table>
					<?php submit_button(__('Save Settings', 'loyalty-program'), 'primary', 'lp_save_settings'); ?>
				</form>
			</div>
			<?php
		}


		public function lp_display_loyalty_points_section()
		{
			// Verifica si el usuario tiene puntos disponibles
			$points_data = $this->lp_get_customer_points();

			if (empty($points_data) || !isset($points_data['points']) || $points_data['points'] <= 0) {
				return; // No mostrar si no hay puntos disponibles
			}

			?>
			<tr class="woocommerce-loyalty-points">
				<th colspan="2"><strong>Puntos Gana Más</strong></th>
			</tr>
			<tr class="woocommerce-loyalty-points-options">
				<td colspan="2">
					<br>
					<label><input type="radio" name="loyalty_coupon" value="" class="loyalty-coupon">No usar puntos</label><br>
					<label><input type="radio" name="loyalty_coupon" value="aprgsqfb" class="loyalty-coupon"> 5%
						Descuento</label><br>
					<label><input type="radio" name="loyalty_coupon" value="mpnbvmkr" class="loyalty-coupon"> 10%
						Descuento</label><br>
					<label><input type="radio" name="loyalty_coupon" value="nceybpp2" class="loyalty-coupon"> 15%
						Descuento</label><br>
					<label><input type="radio" name="loyalty_coupon" value="vzkrtwhq" class="loyalty-coupon"> 20% Descuento</label>
				</td>
			</tr>

			<script>
				jQuery(document).ready(function ($) {
					var storedCoupon = localStorage.getItem('selectedLoyaltyCoupon');
					if (storedCoupon) {
						$('input.loyalty-coupon[value="' + storedCoupon + '"]').prop('checked', true);
					}

					$('.loyalty-coupon, .shipping_method').on('change', function () {
						var selectedCoupon = $('.loyalty-coupon:checked').val();
						localStorage.setItem('selectedLoyaltyCoupon', selectedCoupon);

						$.ajax({
							url: wc_checkout_params.ajax_url,
							type: 'POST',
							data: {
								action: 'apply_loyalty_coupons',
								coupon: selectedCoupon
							},
							success: function (response) {
								if (response.success) {
									console.log('Cupón aplicado: ', selectedCoupon);

									// Actualizar la UI del checkout con el descuento correspondiente
									updateCheckoutTotal(selectedCoupon);

									jQuery('body').trigger('update_checkout');
									jQuery('body').trigger('wc_update_shipping_method'); // Refrescar métodos de envío
									setTimeout(function () {
										$('body').trigger('wc_update_shipping_method');
									}, 500);
								} else {
									alert('Error al aplicar el cupón.');
								}
							}
						});
					});


					function updateCheckoutTotal(couponCode) {
						// Obtener el subtotal (valor fijo)
						var subtotal = parseFloat($('.cart-subtotal .woocommerce-Price-amount bdi').text().replace('$', '').replace(',', '')) || 0;
						var shippingCost = 0;
						// Buscar el input seleccionado dentro de la lista de métodos de envío
						var selectedShipping = $('#shipping_method input:checked').closest('li').find('.woocommerce-Price-amount bdi');
						if (selectedShipping.length) {
							shippingCost = parseFloat(selectedShipping.text().replace('$', '').replace(',', '')) || 0;
						}
						// Definir el porcentaje de descuento según el cupón seleccionado
						var discountPercentage = 0;

						switch (couponCode) {
							case "aprgsqfb":
								discountPercentage = 0.05;
								break;
							case "mpnbvmkr":
								discountPercentage = 0.10;
								break;
							case "nceybpp2":
								discountPercentage = 0.15;
								break;
							case "vzkrtwhq":
								discountPercentage = 0.20;
								break;
							default:
								discountPercentage = 0;
						}

						var discountAmount = subtotal * discountPercentage;
						var newTotal = (subtotal - discountAmount) + shippingCost;

						// Actualizar el "Order Total" en la interfaz
						$('.order-total .woocommerce-Price-amount bdi').each(function () {
							$(this).text('$' + newTotal.toFixed(2));
						});
						console.log(`Nuevo total actualizado: $${newTotal.toFixed(2)} (Descuento aplicado: ${discountPercentage}%)`);
					}
				});
			</script>
			<?php
		}

		public function apply_loyalty_coupons()
		{
			if (!isset($_POST['coupon'])) {
				wp_send_json_error(['message' => 'No se recibió un cupón válido.']);
			}

			$coupon = sanitize_text_field($_POST['coupon']);

			// Remueve todos los cupones anteriores antes de aplicar el nuevo
			foreach (WC()->cart->get_applied_coupons() as $existing_coupon) {
				WC()->cart->remove_coupon($existing_coupon);
			}

			// Aplica el nuevo cupón seleccionado
			WC()->cart->apply_coupon($coupon);

			// ⚡ Forzar el recálculo del total en el checkout
			WC()->cart->calculate_totals();
			WC()->session->set('cart_totals', WC()->cart->get_totals());

			wp_send_json_success([
				'message' => 'Cupón aplicado correctamente.',
				'coupon' => $coupon
			]);
		}


		public function lp_get_customer_points()
		{
			// Verificar si el usuario está logueado
			if (!is_user_logged_in()) {
				return 0;
			}

			// Obtener credenciales dinámicas
			$credentials = $this->lp_get_api_credentials();
			if (!$credentials || empty($credentials['url']) || empty($credentials['username']) || empty($credentials['password'])) {
				return 0;
			}

			$user_id = get_current_user_id();
			$crm = get_user_meta($user_id, 'customer_crm', true);

			if (empty($crm)) {
				return 0;
			}

			// Crear el cuerpo de la solicitud
			$body = json_encode([
				[
					"username" => $credentials['username'],
					"password" => $credentials['password'],
					"url" => $credentials['url'] . "/INT0307",
					"KEY" => "IT_CSP_PETICION",
					"VALUE" => json_encode([
						[
							"id_organizacion_ventas" => "3200",
							"id_cuenta" => $crm,
						]
					]),
				]
			]);

			// Realizar la solicitud API
			$response = wp_remote_post(
				"https://sistema.asofom.online/api/ganamas_dev.php",
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'body' => $body,
				)
			);

			// Manejar errores
			if (is_wp_error($response)) {
				return 0;
			}

			// Decodificar JSON
			$data = json_decode($response["body"], true);

			if (!isset($data["http_code"]) || $data["http_code"] != 200) {
				return 0;
			}

			$it_csp_respuesta = $data["response"]["result"]["IT_CSP_RESPUESTA"] ?? [];

			foreach ($it_csp_respuesta as $respuesta) {
				$puntos_clienter = $respuesta["PUNTOSCLIENTER"] ?? [];

				foreach ($puntos_clienter as $punto) {
					return (int) $punto["DISPONIBLES"];
				}
			}

			return 0;
		}
	}
	new Loyalty_Program();
}
