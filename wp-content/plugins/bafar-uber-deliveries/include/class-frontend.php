<?php
use Conekta\Conekta as BaseConekta;
if ( !defined('ABSPATH') ) {
    exit;
}

// $conekta = new Conekta();
if( !class_exists( 'UB_Frontend' ) ) {
	class UB_Frontend extends BaseConekta{

			private $parametro_adicional;

		
			
            public function __construct() {
			//	add_action('woocommerce_order_status_processing', array($this, 'uber_checkout_field_update_order_meta'), 10, 1);
				add_action('wp_enqueue_scripts', array($this, 'uber_frontend_enqueue'));
                add_action('woocommerce_order_status_changed', array($this, 'uber_send_email_on_order_status_change'), 10, 4);
                add_action('wp_ajax_uber_delivery_fee_calculation', array($this, 'uber_delivery_fee_calculation'));
                add_action('wp_ajax_nopriv_uber_delivery_fee_calculation', array($this, 'uber_delivery_fee_calculation'));
				// Modificar las tarifas de envío dinámicamente
				add_filter('woocommerce_package_rates', function ($rates, $package) {
					if (!empty($_COOKIE['delivery_fee'])) {
						$uber_fee = floatval($_COOKIE['delivery_fee']);
						foreach ($rates as $rate_id => $rate) {
							if ($rate->method_id === 'flat_rate') { // Cambia 'flat_rate' al método que deseas modificar
								$rates[$rate_id]->cost += $uber_fee; // Agregar el costo de Uber
							}
						}
					}
					return $rates;
				}, 10, 2);
            }



						

			public function uber_delivery_fee_calculation() {

						/*if (empty($_COOKIE['delivery_by']) || $_COOKIE['delivery_by'] !== 'uber') {
								wp_send_json_error("error en cookie uber");
						}*/

						check_ajax_referer( 'delivery_integration_nonce', 'nonce' );
						$addressData = $_POST['addressData'];
						// $first_name = $addressData['first_name'];
						// $last_name = $addressData['last_name'];
						$street_address = $addressData['address_1'];
						$city           = $addressData['city'];
						$state          = $addressData['state'];
						$postcode       = $addressData['postcode'];
						$country        = $addressData['country'];
						$customer_id    = get_option('ri_customer_id');

			//para simular
			if(0){

						$response = array(
							'success' => true,
							'body' => json_encode(array(
								'kind' => 'delivery_quote',
								'id' => 'dqt_OsZRGJ7kSmectzQRnKCXkQ',
								'created' => '2024-12-22T22:58:03.286Z',
								'expires' => '2024-12-22T23:13:03.286Z',
								'fee' => 3000,
								'currency' => 'mxn',
								'currency_type' => 'MXN',
								'dropoff_eta' => '2024-12-23T00:10:14Z',
								'duration' => 72,
								'pickup_duration' => 4,
								'dropoff_deadline' => '2024-12-23T00:56:50Z',
							)),
						);
									// Procesar la respuesta simulada
						$body = $response['body']; // Simula wp_remote_retrieve_body
						$data = json_decode($body, true); // true para obtener un arreglo asociativo


			}else{

				$body = json_encode(array(
					'pickup_address' => $this->pickup_location(""),
					'dropoff_address' => sprintf(
						"{\"street_address\":[\"%s\"],\"city\":\"%s\",\"state\":\"%s\",\"zip_code\":\"%s\",\"country\":\"%s\"}", 
						$street_address, 
						$city, 
						$state, 
						$postcode, 
						$country
					),
				));
				
				error_log('Request body: ' . $body); // Esto escribe en el log de errores de PHP.
				
						$response = wp_remote_post("https://api.uber.com/v1/customers/{$customer_id}/delivery_quotes", array(
							'body' => json_encode(array(
								'pickup_address' => $this->pickup_location(""),
								'dropoff_address' => sprintf("{\"street_address\":[\"%s\"],\"city\":\"%s\",\"state\":\"%s\",\"zip_code\":\"%s\",\"country\":\"%s\"}", $street_address, $city, $state, $postcode, $country),
							)),
							'headers' => array(
								'Authorization' => 'Bearer ' . $this->uber_access_token(),
								'Content-Type' => 'application/json',
							),
						));

					
						$cart                  = WC()->cart;
						$costo_envio           = 0;
						$cupones               = $cart->get_applied_coupons();
						$envio_gratis_activado = false;

						foreach ($cupones as $cupon_codigo) {
							$cupon = new WC_Coupon($cupon_codigo);
							if ($cupon->get_free_shipping()) {
								$envio_gratis_activado = true;
								break;
							}
						}

						// Acceder al cuerpo de la respuesta
						$body = wp_remote_retrieve_body($response);
						// Decodificar el cuerpo JSON
						$data = json_decode($body, true); // true para obtener un arreglo asociativo

			}//fin para simular respuesta

						// Verificar si el campo 'fee' está presente
						if (!empty($data['message'])) {
							setcookie('ri_warning_message', $data['message'], time() + (86400 * 30), "/");
						} else {
							// Parámetro adicional
							setcookie('delivery_by', 'uber', time() + (86400 * 30), "/");
							if (isset($data['fee'])) {
								$fee = ($data['fee']/100);
									// Guardar la tarifa de Uber en una cookie o en una variable transitoria
								setcookie('delivery_fee', $fee, time() + (86400 * 30), "/");
							} else {
								echo "No se encontró la clave 'fee' en la respuesta.";
								etcookie('delivery_fee', -1, time() + (86400 * 30), "/");
							}
						}
						wp_send_json_success($data);
		}


		


		public function uber_send_email_on_order_status_change($order_id, $old_status, $new_status, $order) {
			if (empty($_COOKIE['delivery_by'])) {
				return;
			}
			if ('processing' == $new_status) {
				// 				$to = 'mnvdafridi@gmail.com';
				$subject      = 'Order Status Changed - Order #' . $order->get_order_number();
				$to           = $order->get_billing_email();
				$tracking_url = get_post_meta($order_id, '_tracking_url', true);
				$message      = "The status of order #" . $order->get_order_number() . " has changed.\n\n";
				$message     .= sprintf('Tracking URL: %s', $tracking_url);
				$headers      = array('Content-Type: text/plain; charset=UTF-8');
				wp_mail($to, $subject, $message, $headers);
			}
		}

		public function uber_frontend_enqueue() {
			$dropoff_location = '';
			if (!empty($_COOKIE['wcmlim_selected_location_termid'])) {
				$location_id      = $_COOKIE['wcmlim_selected_location_termid'];
				$street_no        = get_term_meta($location_id, 'wcmlim_street_number', true);
				$street_address   = get_term_meta($location_id, 'wcmlim_route', true);
				$city             = get_term_meta($location_id, 'wcmlim_locality', true);
				$state            = get_term_meta($location_id, 'wcmlim_administrative_area_level_1', true);
				$postcode         = get_term_meta($location_id, 'wcmlim_postal_code', true);
				$country          = get_term_meta($location_id, 'wcmlim_country', true);
				$dropoff_location = sprintf('%s %s, %s, %s, %s %s', $street_no, $street_address, $city, $state, $postcode, $country);
			}

			wp_enqueue_script('ud-frontend-script', UD_URL . 'assets/script.js', array('jquery', 'wp-data'), '1.0.5');
			wp_localize_script('ud-frontend-script', 'uber_delivery', array(
				'ajaxurl'          => admin_url('admin-ajax.php'),
				'ajax_nonce'       => wp_create_nonce('delivery_integration_nonce'),
				'dropoff_location' => $dropoff_location,
				'is_rappi_active'  => in_array('bafar-rappi-delivery/rappie-delivery.php', apply_filters('active_plugins', get_option('active_plugins')))
			));
		}



		public function uber_checkout_field_update_order_meta($order_id) {
			error_log("si entre en processing");
			    // Cargar la instancia del pedido a partir del ID
				$order = wc_get_order($order_id);
				// Lógica personalizada según cookies y orden
				$first_name = $order->get_billing_first_name();
				$last_name  = $order->get_billing_last_name();
				$phone      = $order->get_billing_phone();
				$email      = $order->get_billing_email();
				$address    = $order->get_billing_address_1();
				$city       = $order->get_billing_city();
				$postcode   = $order->get_billing_postcode();
				$state      = $order->get_billing_state();
				$phone      = $order->get_billing_phone();
				$country    = $order->get_billing_country();


				// 				$full_address = array(
				// 					'street_address' => $address,
				// 					'city' => $city,
				// 					'state' => $state,
				// 					'zip_code' => $postcode,
				// 					'country' => $country
				// 				);

				// 				007b10d5-0847-4652-8c21-9f54c9bce673
						//	$location_id = $_COOKIE['wcmlim_selected_location_termid'];
				// 				$location = get_term_by('term_id', $location_id, 'locations');
				// 				$pstreet_no = get_term_meta($location_id, 'wcmlim_street_number', true);
							//$pphone = get_term_meta($location_id, 'wcmlim_phone', true);
				// 				$pstreet_address = get_term_meta($location_id, 'wcmlim_route', true);
				// 				$pcity = get_term_meta($location_id, 'wcmlim_locality', true);
				// 				$pstate = get_term_meta($location_id, 'wcmlim_administrative_area_level_1', true);
				// 				$ppostcode = get_term_meta($location_id, 'wcmlim_postal_code', true);
				// 				$pcountry = get_term_meta($location_id, 'wcmlim_country', true);
				// 				$pickup_address = "{$pstreet_address}, {$pcity}, {$pstate} {$ppostcode}, {p$country}";
				// 				$pickup_address = array(
				// 					'street_address' => $pstreet_address,
				// 					'city' => $pcity,
				// 					'state' => $pstate,
				// 					'zip_code' => $ppostcode,
				// 					'country' => $pcountry
				// 				);
				$order_items = array();
				$total = 0;
				foreach ($order->get_items() as $item_id => $item) {
					$product = $item->get_product();
					$order_items[] = array(
						'quantity' => $item->get_quantity(),
						'name' => $product->get_name(),
						'price' => (int)$product->get_price()
					);
					$total += $product->get_price();
				}

				$location_name = $order->get_meta('location_name');
				if (empty($location_name)) {
					error_log("pedido sin location , es importante ");
				}
				$params = [
					"pickup_address"=> $this->pickup_location($location_name),
					"pickup_phone_number"=> "5555555555",
					"pickup_name" => 'Carnemart.com',
					//     				"pickup_ready_dt" => '2024-08-19T14:30:00Z',
					"dropoff_address"=> sprintf("{\"street_address\":[\"%s\"],\"city\":\"%s\",\"state\":\"%s\",\"zip_code\":\"%s\",\"country\":\"%s\"}", $address, $city, $state, $postcode, $country),
					"dropoff_name" => $first_name . " " . $last_name,
					"dropoff_phone_number"=> $phone,
					// 					"pickup_name" => 'Google',//sprintf('%s %s', $first_name, $last_name),
					// 					"pickup_veriﬁcation" => ["signature" => true],
					'pickup_ready_dt' => gmdate('Y-m-d\TH:i:s\Z'),//'2024-08-19T14:30:00Z',
					// 					"dropoff_notes" => sprintf("Order #%s for %s %s", $order->get_id(), $first_name, $last_name),
					// 					"dropoff_verification" => ["signature" => true],
					"manifest_items" => $order_items,
					'manifest_total_value' => (int)$total,//$order->get_total(),
					'manifest_reference' => bin2hex(random_bytes(16))//strval($order->get_id())
				];

						
				error_log('Request body liberacion: ' . json_encode($params)); // Esto escribe en el log de errores de PHP.

			
				$customer_id = get_option('ri_customer_id');
				$api_url = "https://api.uber.com/v1/customers/{$customer_id}/deliveries/";
				$response = wp_remote_post($api_url, array(
					'method'    => 'POST',
					'headers'   => array(
						'Authorization' => 'Bearer ' . $this->uber_access_token(),
						'Content-Type'  => 'application/json',
					),
					'body'      => json_encode($params),
					'timeout'   => 20
				));
				$response_body = json_decode(wp_remote_retrieve_body($response));
				error_log("meta" . $response_body->tracking_url);
				error_log("keikos" . json_encode($response_body));

			
				if (!empty($response_body->tracking_url)) {
					// Actualizar los metadatos del pedido con la URL de seguimiento
					$order->update_meta_data('tracking_url', $response_body->tracking_url);
					$order->save();
				}
				
				// Obtener el mensaje de advertencia si está disponible
				$msg = !empty($response_body->message) 
					? $response_body->message 
					: (!empty($response_body->metadata->details) ? $response_body->metadata->details : null);
				
				if (!empty($msg)) {
					// Mostrar el mensaje y guardarlo en una cookie

					switch ($msg) {
						case "The specified location is not in a deliverable area.":
							$msg = "Tu ubicación no está dentro de la cobertura para recibir a domicilio, puedes acudir por el pedido a la tienda, lamentamos los incovenientes.";
							break;
					
						default:
							$msg = "Uber no está disponible, lamentamos la molestia.";
							break;
					}

					
					echo $msg;
					setcookie('ri_warning_message', $msg, time() + (86400 * 30), "/");
					die;
				}
				
	
			
			$shipping_items = $order->get_items('shipping');

			// Eliminar todos los métodos de envío que no sean "pickup_location".
			foreach ($shipping_items as $item_id => $shipping_item) {
				if ($shipping_item->get_method_id() !== 'pickup_location') {
					$order->remove_item($item_id);
				}
			}
			
			// Asignar el nuevo método de envío "uber_direct_shipping".
			// Verifica si la respuesta contiene el campo 'fee' y asígnalo a una variable
			
	
			if (isset($response_body->fee)) {
				$fee = $response_body->fee/100; // Asignar el valor del 'fee' a la variable $fee
			} else {
				$fee = 10; // Manejo de errores en caso de que 'fee' no exista
			}
			$shipping_item = new WC_Order_Item_Shipping();
			$shipping_item->set_method_id('uber_direct_shipping'); // El ID del método de envío.
			$shipping_item->set_method_title('Uber'); // Título del método de envío (opcional).
			$shipping_item->set_total($fee); // Establecer el costo de envío, si es aplicable.
			// Agregar el nuevo método de envío a la orden.
			$order->add_item($shipping_item);
			// Guardar los cambios en la orden.
			$order->save();
			

		}
		



		public function uber_access_token() {
			$token_url = 'https://login.uber.com/oauth/v2/token';
			$client_id = get_option('ri_client_id');
			$client_secret = get_option('ri_client_secret_id');
			$customer_id = get_option('ri_customer_id');
			// Make the POST request to the token endpoint
			$response = wp_remote_post($token_url, array(
				'method'    => 'POST',
				'body'      => array(
					'client_id'     => $client_id,//'cIWwQ-sBDfoqjVap19FOJB5-KaMrmR9j',
					'client_secret' => $client_secret,//'uA-fuS0R3d4eBZYMWnxk4o_ZEvKC6a8epne_sntR',
					'grant_type'    => 'client_credentials',
					'scope'         => 'eats.deliveries',  // Scope should be set based on Uber's API documentation
				),
			));
			$response_body = wp_remote_retrieve_body($response);
			$response_body = json_decode($response_body);
			return $response_body->access_token;
		}

		public function pickup_location($term_name) {

		
			// Obtener el término por nombre
			if($term_name==""){
				
				$location_id = $_COOKIE['wcmlim_selected_location_termid'];

			}else{
				$term = get_term_by('name', $term_name, "locations");
				$location_id = $term->term_id;


			}
			
			
			
			// $location = get_term_by('term_id', $location_id, 'locations');
			$pstreet_no = get_term_meta($location_id, 'wcmlim_street_number', true);
			// $pphone = get_term_meta($location_id, 'wcmlim_phone', true);
			$pstreet_address = get_term_meta($location_id, 'wcmlim_route', true);
			$pcity = get_term_meta($location_id, 'wcmlim_locality', true);
			$pstate = get_term_meta($location_id, 'wcmlim_administrative_area_level_1', true);
			$ppostcode = get_term_meta($location_id, 'wcmlim_postal_code', true);
			$pcountry = get_term_meta($location_id, 'wcmlim_country', true);
			return sprintf("{\"street_address\":[\"%s %s\"],\"city\":\"%s\",\"state\":\"%s\",\"zip_code\":\"%s\",\"country\":\"%s\"}", $pstreet_no, $pstreet_address, $pcity, $pstate, $ppostcode, $pcountry);
		}
    }
    new UB_Frontend();
}