<?php
/**
* Plugin Name: Bafar :: Pickup en tienda 🛒
* Plugin URI: https://woocommerce.com/products/sales-booster-for-woocommerce/
* Description: Local Pickup.
* Version: 1.0.0
* Author: SparkLab
* Developer: SpartLab
* Text Domain: loyalty-program
**/
if ( !defined('ABSPATH') ) {
	exit; // Exit if accessed directly.
} 
if ( !defined('WC_PICKUP_DIR') ) {
	define('WC_PICKUP_DIR', plugin_dir_path(__FILE__));
}
if ( !defined('WC_PICKUP_URL') ) {
	define('WC_PICKUP_URL', plugin_dir_url(__FILE__));
}
if ( !class_exists('Local_Pickup') ) {
	class Local_Pickup {
		public function __construct() {

		

			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins')) ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				if ( function_exists( 'load_plugin_textdomain' ) ) {
					load_plugin_textdomain('loyalty-program', false, dirname(plugin_basename(__FILE__)) . '/languages/');
				}
			} else {
				add_action('admin_notices', array($this, 'lp_admin_notices'));
			}
			add_action( 'before_woocommerce_init', array($this, 'lp_woocommerce_hpos_compatible' ) );
			add_action('woocommerce_blocks_loaded', array($this, 'wc_load_blocks'));
			add_action('woocommerce_store_api_checkout_update_order_from_request', array($this, 'ri_checkout_field_update_order_meta'), 10, 2);
			add_action( 'woocommerce_admin_order_data_after_billing_address', array($this, 'display_custom_fields_in_admin_order_meta'), 10, 1 );
			add_action('wp_head', array($this, 'lp_header_style'));
			add_action('wp_footer', array($this, 'lp_header_script'));
			//add_action( 'woocommerce_cart_calculate_fees', array($this, 'ri_add_cart_fees_by_product_meta') );
// 			add_filter( 'woocommerce_package_rates', array($this, 'hide_shipping_when_free_is_available'), 10, 2 );
// 			add_filter('woocommerce_cart_needs_shipping', array($this, 'disable_shipping_requirement_for_cart'), 10, 1);
// 			add_action('wp', array($this, 'ri_quantity_hooks'), 999999);
			
		}

		
		/*
		public function ri_add_cart_fees_by_product_meta() {
			if ( !empty($_COOKIE['delivery_fee']) ) {
				$fee = $_COOKIE['delivery_fee']/100;
				WC()->cart->add_fee( $_COOKIE['delivery_fee_label'], $fee );
			}
	}
			*/
// 		public function ri_quantity_hooks() {
// 			if ( !empty($_COOKIE['delivery_by']) ) {
// 				if ( $_COOKIE['delivery_by'] == 'rappi' || $_COOKIE['delivery_by'] == 'uber' ) {
// 					add_filter('woocommerce_cart_needs_shipping', '__return_false');
// 				}	
// 			}
// 		}
// 		public function disable_shipping_requirement_for_cart($needs_shipping) {
// 			if ( !empty($_COOKIE['delivery_by']) ) {
// 				if ( $_COOKIE['delivery_by'] == 'rappi' || $_COOKIE['delivery_by'] == 'uber' ) {
// 					return false;
// 				}
// 			}
// 			return $needs_shipping;
// 		}
// 		function hide_shipping_when_free_is_available( $rates, $package  ) {
// 			if ( !empty($_COOKIE['delivery_by']) ) {
// 				if ( $_COOKIE['delivery_by'] == 'rappi' || $_COOKIE['delivery_by'] == 'uber' ) {
// 					 $rates = [];
// 				}	
// 			}
// 			return $rates;
// 		}
		public function lp_header_style() {
			?>
				<style>
					.custom-pickup-fields label{
						width:100%;
					}
					.custom-pickup-fields div{
						margin-bottom:10px;
					}
				</style>
			<?php
		}
		public function lp_header_script() {
			$dropoff_location = '';
			if ( !empty($_COOKIE['wcmlim_selected_location_termid']) ) {
				$location_id = $_COOKIE['wcmlim_selected_location_termid'];
				$street_address = get_term_meta($location_id, 'wcmlim_street_number', true);
// 				$street_address = get_term_meta($location_id, 'wcmlim_route', true);
				$city = get_term_meta($location_id, 'wcmlim_locality', true);
				$state = get_term_meta($location_id, 'wcmlim_administrative_area_level_1', true);
				$postcode = get_term_meta($location_id, 'wcmlim_postal_code', true);
// 				$country = get_term_meta($location_id, 'wcmlim_country', true);
				$dropoff_location = sprintf('%s, %s, %s, %s', $street_address, $city, $state, $postcode);
			}
			?>
		<script>
			function restrict_date() {
				const today = new Date();
				const nextDay = new Date(today);
				nextDay.setDate(today.getDate() + 1);
				const formatDate = (date) => {
					const year = date.getFullYear();
					const month = String(date.getMonth() + 1).padStart(2, '0');
					const day = String(date.getDate()).padStart(2, '0');
					return `${year}-${month}-${day}`;
				};
				var dateInput;
				// 	const dateInput = document.querySelector('.pickup_date');
				var timer = setInterval(function() {
					dateInput = document.getElementById('lp-date-picker');
					if ( dateInput ) {
						// 						var dateField = dateInput.querySelector('input');
						dateInput.min = formatDate(today);
						dateInput.max = formatDate(nextDay);	
						clearInterval(timer);
					}
				}, 500);
			}
				var address = '<?php echo $dropoff_location; ?>';
				var addressHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="wc-block-editor-components-block-icon" aria-hidden="true" focusable="false"><path d="M12 9c-.8 0-1.5.7-1.5 1.5S11.2 12 12 12s1.5-.7 1.5-1.5S12.8 9 12 9zm0-5c-3.6 0-6.5 2.8-6.5 6.2 0 .8.3 1.8.9 3.1.5 1.1 1.2 2.3 2 3.6.7 1 3 3.8 3.2 3.9l.4.5.4-.5c.2-.2 2.6-2.9 3.2-3.9.8-1.2 1.5-2.5 2-3.6.6-1.3.9-2.3.9-3.1C18.5 6.8 15.6 4 12 4zm4.3 8.7c-.5 1-1.1 2.2-1.9 3.4-.5.7-1.7 2.2-2.4 3-.7-.8-1.9-2.3-2.4-3-.8-1.2-1.4-2.3-1.9-3.3-.6-1.4-.7-2.2-.7-2.5 0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7c0 .2-.1 1-.7 2.4z"></path></svg>${address}`;
				jQuery(document).on('click', '.wc-block-checkout__shipping-method-option', function() {
					if ( jQuery(this).find('span').text() == 'Recoger en tienda') {
						jQuery('div.wc-block-components-radio-control__description').html(addressHTML);
						jQuery('.wc-block-components-shipping-address').html(address);
						jQuery('.wc-block-components-totals-shipping').show();
						jQuery('.wc-block-components-totals-shipping .wc-block-components-totals-item__value').html("Gratis");
					} else {
						//jQuery('.wc-block-components-totals-item__value').html("Uber");
						//jQuery('.wc-block-components-totals-shipping .wc-block-components-totals-item__value').html("Uber");
						//setTimeout(function() {
						//		jQuery('.wc-block-components-totals-shipping .wc-block-components-totals-item__value').html("A domicilio");
						//	}, 1000)
						//jQuery('.wc-block-components-totals-shipping').hide();
					}
				});
// 				window.addEventListener('load', function() {
					setTimeout(function() {
				
						if ( jQuery('.wc-block-components-radio-control__secondary-description').length > 0 ) {
							jQuery('.wc-block-components-radio-control__secondary-description').html(addressHTML);	
						}

// 						if ( jQuery('.wc-block-components-shipping-address').length > 0 ) {
// 							jQuery('.wc-block-components-shipping-address').html(address);
// 						}
						// restrict date field
						restrict_date();
					}, 1000)
// 				});
			jQuery(document).on('click', '.wc-block-checkout__shipping-method-option', function() {
				restrict_date();
				if ( jQuery('.wc-block-components-radio-control__secondary-description').length > 0 ) {
					jQuery('.wc-block-components-radio-control__secondary-description').html(addressHTML);	
				}
// 				if ( jQuery('.wc-block-components-shipping-address').length > 0 ) {
// 					jQuery('.wc-block-components-shipping-address').html(address);
// 				}
			});
		</script>
		<?php
		}
		function display_custom_fields_in_admin_order_meta( $order ) {
			$pickup_time = $order->get_meta( 'lp_pickup_time' );
			$pickup_date = $order->get_meta( 'lp_pickup_date' );
			$pickup_comments = $order->get_meta( 'lp_pickup_comments' );
		
			if ( $pickup_date ) {
				echo '<p><strong>Fecha de recolección:</strong> ' . esc_html( $pickup_date ) . '</p>';
			}
		
			if ( $pickup_time ) {
				echo '<p><strong>Ho:</strong> ' . esc_html( $pickup_time ) . '</p>';
			}
			if ( $pickup_comments ) {
				echo '<p><strong>Pickup Comments:</strong> ' . esc_html( $pickup_comments ) . '</p>';
			}
		}
		public function ri_checkout_field_update_order_meta(  $order, $request  ) {
			$body = json_decode( $request->get_body(), true );
			$body = isset($body['extensions']['wc-local-pickup']) ? $body['extensions']['wc-local-pickup'] : null;

			$shipping_methods = $order->get_shipping_methods();
			foreach ( $shipping_methods as $shipping_method ) {
				if ( $shipping_method->get_method_id() === 'pickup_location' ) {
					if ( !empty($_COOKIE['wcmlim_selected_location_termid']) ) {
						$location_id = $_COOKIE['wcmlim_selected_location_termid'];
						$street_no = get_term_meta($location_id, 'wcmlim_street_number', true);
						$street_address = get_term_meta($location_id, 'wcmlim_route', true);
						$city = get_term_meta($location_id, 'wcmlim_locality', true);
						$state = get_term_meta($location_id, 'wcmlim_administrative_area_level_1', true);
						$postcode = get_term_meta($location_id, 'wcmlim_postal_code', true);
						$country = get_term_meta($location_id, 'wcmlim_country', true);
						$dropoff_location = sprintf('%s %s, %s, %s, %s %s', $street_no, $street_address, $city, $state, $postcode, $country);
						
						$shipping_method->delete_meta_data( 'pickup_address' );
						$shipping_method->delete_meta_data( 'City' );
						$shipping_method->delete_meta_data( 'Postcode' );
						$shipping_method->delete_meta_data( 'Country' );
						
						$shipping_method->add_meta_data( 'pickup_address', $dropoff_location );
						$shipping_method->add_meta_data( 'City', $city );
						$shipping_method->add_meta_data( 'Postcode', $postcode );
						$shipping_method->add_meta_data( 'Country', $country );
					}
				}
			}
			
			$order->update_meta_data( 'lp_pickup_date', sanitize_text_field( $body['lp_pickup_date'] ) );
			$order->update_meta_data( 'lp_pickup_time', sanitize_text_field( $body['lp_pickup_time'] ) );
			$order->update_meta_data( 'lp_pickup_comments', sanitize_text_field( $body['lp_pickup_comments'] ) );
		}
		public function wc_load_blocks() {

			add_action('woocommerce_blocks_checkout_block_registration', array($this, 'wc_register_checkout_blocks'), 10, 1);
		}
		public function wc_register_checkout_blocks( $integration_registry ) {
			require_once WC_PICKUP_DIR . 'blocks/pickup-checkout-shipping.php';
			$integration_registry->register( new Local_Pickup_Integration() );
// 			require_once WC_PICKUP_DIR . 'blocks/external-deliveries.php';
// 			$integration_registry->register( new External_Deliveries() );
		}
		public function lp_woocommerce_hpos_compatible() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
		public function lp_admin_notices() {
			global $pagenow;
			if ( 'plugins.php' === $pagenow ) {
				$class = 'notice notice-error is-dismissible';
				$message = esc_html__('Local Pickup needs WooCommerce to be installed and active.', 'loyalty-program');
				printf('<div class="%1$s"><p>%2$s</p></div>', esc_html($class), esc_html($message));
			}
		}
	}
	new Local_Pickup();
}
