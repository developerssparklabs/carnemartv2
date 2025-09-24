<?php
if (!defined('ABSPATH')) {
	exit;
}


if (!class_exists('PQ_Frontend')) {
	class PQ_Frontend
	{
		public function __construct()
		{
			add_action('wp_enqueue_scripts', array($this, 'pq_frontend_enqueue'));
			add_action('wp', array($this, 'pq_quantity_hooks'), 999999);
			add_filter('woocommerce_get_item_data', array($this, 'wpf_cart_item_description'), 10, 2);
			add_filter('woocommerce_store_api_product_quantity_maximum', array($this, 'maximum_quantity_limit'), 10, 3);
			add_action('woocommerce_add_to_cart_validation', array($this, '_quantity_validation'), 10, 2);
		}




		public function _quantity_validation($passed_validation, $product_id)
		{
			if (!empty($_COOKIE['wcmlim_selected_location_termid'])) {
				$term_id = $_COOKIE['wcmlim_selected_location_termid'];
				$max = get_post_meta($product_id, "wcmlim_stock_at_{$term_id}", true);
				if (empty($max) || $max <= 0) {
					$passed_validation = false;
				}
			}
			return $passed_validation;
		}
		public function maximum_quantity_limit($maximum_quantity, $product, $cart_item)
		{
			$max = '';
			if (!empty($_COOKIE['wcmlim_selected_location_termid'])) {
				$term_id = $_COOKIE['wcmlim_selected_location_termid'];
				$max = get_post_meta($cart_item['product_id'], "wcmlim_stock_at_{$term_id}", true);
				$max = !empty($max) ? $max : 1;
			}
			if (!empty($max)) {
				return $max;
			}
			return $maximum_quantity;
		}
		public function wpf_cart_item_description($item_data, $cart_item)
		{
			if (empty($_COOKIE['wcmlim_selected_location_termid'])) {
				return $item_data;
			}
			$term_id = $_COOKIE['wcmlim_selected_location_termid'];
			$item_data[] = array(
				'key' => 'Disponibilidad',
				'value' => get_post_meta($cart_item['product_id'], "wcmlim_stock_at_{$term_id}", true),
				'display' => '',
			);
			return $item_data;
		}
		private $has_run = false; // Agrega esta propiedad en la clase

		public function pq_quantity_hooks()
		{
			// Verifica si ya se ha ejecutado
			if ($this->has_run) {
				return;
			}

			// Marca la propiedad como `true` para indicar que se ejecutó
			$this->has_run = true;
			add_filter('woocommerce_loop_add_to_cart_link', array($this, 'pq_shop_quantity_field'), 1, 1);
			if (!is_product()) {
				add_filter('woocommerce_get_price_html', array($this, 'agregar_contenido_alado_precio'), 10, 2);
			}
			add_filter('woocommerce_quantity_input_args', array($this, 'pq_set_quantity'), 1, 2);
			add_filter('woocommerce_cart_item_quantity', array($this, 'pq_cart_item_quantity'), 99999, 3);
		}

		// Define la función `agregar_contenido_alado_precio` fuera de `pq_quantity_hooks`
		public function agregar_contenido_alado_precio($precio, $product)
		{
			$etiqueta = get_post_meta($product->get_id(), 'ri_quantity_step_label', true);
			$etiqueta = !empty($etiqueta) ? $etiqueta : 'KG';
			$final = '<div class="por-label"> por <b>' . $etiqueta . '</b></div>';

			if (!is_product()) {
				return $precio . " &nbsp; " . $final;
			} else {
				return $final . $precio;
			}
		}

		/*** este muestra el loop kg  */
		public function pq_shop_quantity_field($html)
		{
			global $product;
			if ($product->is_type('variable')) {
				return $html;
			}

			$before = '';
			if (!$product->is_sold_individually() && $product->is_purchasable()) {
				$before = $this->pq_get_field($product);
			}
			/*$label = get_post_meta( $product->get_id(), 'ri_quantity_step_label', true);
			$label = !empty($label) ? $label : 'KG';*/
			// echo sprintf('<div class="por-label">Por <b>%s</b></div>', $label);
			echo $before;
			// echo sprintf('%s <div class="increment-label"><b>%s</b></div>', $before, $label);
			return $html;
		}



		public function pq_set_quantity($args, $product)
		{
			if ($product->is_type('variable')) {
				return $args;
			}
			if (!is_product()) {
				return $args;
			}
			$product_id = $product->get_id();
			$qty_step = get_post_meta($product_id, 'ri_quantity_step', true);
			$step_qty = !empty($qty_step) ? floatval($qty_step) : 1;
			$args['input_value'] = 1;
			$args['step'] = $step_qty;
			return $args;
		}

		// Función para validar el stock de acuerdo a la ubicación seleccionada
		function validar_stock_por_ubicacion(
			$product_id
		) {
			$quantity = "";
			$stock_at_location = "";
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

					return 0;
				}
			}
			return $stock_at_location;
		}


		// public function pq_get_field_vold($product)
		// {
		// 	//cantidades keikos sergio spark
		// 	$qty_step = !empty($qty_step) ? floatval($qty_step) : 1;

		// 	//$x; // se cambia para mejorar la UX
		// 	$label = get_post_meta($product->get_id(), 'ri_quantity_step_label', true);
		// 	$maximk = $this->validar_stock_por_ubicacion($product->get_id());
		// 	// Generar el campo de cantidad con botones "minus" y "plus"
		// 	$field = '<div class="quantity">';
		// 	$field .= '<button type="button" class="minus">-</button>';
		// 	$field .= '<input type="number" id="quantity_' . $product->get_id() . '" class="keikos pq-qty-input input-text qty text"  max="' . $maximk . '" step="' . $qty_step . '" min="' . $qty_step . '" name="quantity" value="' . $qty_step . '" title="Qty" size="4" inputmode="numeric">';
		// 	$field .= '<input type="hidden" id="quantity_step_' . $product->get_id() . '" value=' . get_post_meta($product->get_id(), 'ri_quantity_step', true) . '">';
		// 	$field .= '<input type="hidden" id="id_producto_' . $product->get_id() . '" value=' . $product->get_id() . '>';
		// 	$field .= '<input type="hidden" id="unidad_' . $product->get_id() . '" value=' . $label . '>';
		// 	$field .= '<button type="button" class="plus">+</button>';
		// 	$field .= '</div>';


		// 	return $field;
		// }

		// Versión ajustada para incremento en grid
		public function pq_get_field($product)
		{
			//$product_id = $product->get_id();

			// $qty_step = get_post_meta($product_id, 'ri_quantity_step', true);
			// $qty_min = get_post_meta($product_id, 'min_quantity', true);
			// $label = get_post_meta($product_id, 'ri_quantity_step_label', true);
			// $maximk = $this->validar_stock_por_ubicacion($product_id);

			// Determinar decimales basados en qty_min
			//$decimal_places = $this->get_decimal_places($qty_min);

			// Valores por defecto si no están definidos
			// $qty_min = !empty($qty_min)
			// 	? number_format(floatval($qty_min), $decimal_places, '.', '')
			// 	: number_format(1, $decimal_places, '.', '');

			// $qty_step = !empty($qty_step)
			// 	? number_format(floatval($qty_step), $decimal_places, '.', '')
			// 	: $qty_min;

			// $field = '<div class="quantity">';
			// $field .= '<button type="button" class="minus">-</button>';
			// $field .= '<input type="number" id="quantity_' . $product_id . '" class="keikos pq-qty-input input-text qty text" ';
			// $field .= 'max="' . $maximk . '" step="' . $qty_step . '" min="' . $qty_min . '" name="quantity" value="' . $qty_min . '" ';
			// $field .= 'title="Qty" size="4" inputmode="numeric">';
			// $field .= '<input type="hidden" id="quantity_step_' . $product_id . '" value="' . $qty_step . '">';
			// $field .= '<input type="hidden" id="id_producto_' . $product_id . '" value="' . $product_id . '">';
			// $field .= '<input type="hidden" id="unidad_' . $product_id . '" value="' . $label . '">';
			// $field .= '<button type="button" class="plus">+</button>';
			// $field .= '</div>';

			return '';
		}
		/**
		 * Obtiene el número de decimales en un número
		 * @param float $number
		 * @return int
		 */
		private function get_decimal_places($number)
		{
			$number = (string) $number;
			if (strpos($number, '.') !== false) {
				return strlen(substr($number, strpos($number, '.') + 1));
			}
			return 0;
		}


		public function pq_frontend_enqueue()
		{
			wp_enqueue_script('pq-frontend-script', PQ_URL . 'assets/script.js', array('jquery', 'wp-data'), '1.0.4');
		}



		public function pq_cart_item_quantity($product_quantity, $cart_item_key, $cart_item)
		{
			$product_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product = wc_get_product($product_id);
			$qty_step = get_post_meta($product_id, 'ri_quantity_step', true);
			if ($product->is_sold_individually()) {
				$product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
			} else {
				$max = '';
				if (!empty($_COOKIE['wcmlim_selected_location_termid'])) {
					$term_id = $_COOKIE['wcmlim_selected_location_termid'];
					$max = get_post_meta($cart_item['product_id'], "wcmlim_stock_at_{$term_id}", true);
				}
				$product_quantity = woocommerce_quantity_input(
					array(
						'input_name' => "cart[{$cart_item_key}][qty]",
						'input_value' => $cart_item['quantity'],
						'step' => $qty_step,
						'product_name' => $product->get_name(),
					),
					$product,
					false
				);
			}
			return $product_quantity;

			/**
			 * para la vista de producto
			 */
		}
	}
	new PQ_Frontend();
}
