<?php

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if (!class_exists('Local_Pickup_Integration')) {
	/**
	 * Class for integrating with WooCommerce Blocks
	 * 
	 * @since 1.0.9
	 */
	class Local_Pickup_Integration implements IntegrationInterface
	{
		/**
		 * The name of the integration.
		 *
		 * @return string
		 * @since 1.0.9
		 */
		public function get_name()
		{
			return 'share-cart-checkout';
		}
		/**
		 * When called invokes any initialization/setup for the integration.
		 * 
		 * @since 1.0.9
		 */
		public function initialize()
		{
			$this->register_share_cart_checkout_block_frontend_scripts();
			$this->register_share_cart_checkout_block_editor_scripts();
			$this->register_share_cart_checkout_block_editor_styles();
			$this->register_main_integration();
		}
		private function register_main_integration()
		{
			$script_asset_path = WC_PICKUP_DIR . 'build/index.asset.php';
			$script_asset = file_exists($script_asset_path)
				? require $script_asset_path
				: array(
					'dependencies' => array('wp-data'),
					'version' => ''
				);
			wp_register_script(
				'sales-booster-checkout-blocks-integration',
				WC_PICKUP_URL . 'build/index.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_set_script_translations(
				'sales-booster-checkout-blocks-integration',
				'custom-field',
				dirname(__FILE__) . '/languages'
			);
		}
		/**
		 * Returns an array of script handles to enqueue in the frontend context.
		 *
		 * @return string[]
		 * @since 1.0.9
		 */
		public function get_script_handles()
		{
			return array('sales-booster-checkout-blocks-integration', 'sales-booster-checkout-block-frontend');
		}
		/**
		 * Returns an array of script handles to enqueue in the editor context.
		 *
		 * @return string[]
		 * @since 1.0.9
		 */
		public function get_editor_script_handles()
		{
			return array('sales-booster-checkout-blocks-integration', 'sales-booster-checkout-block-editor');
		}
		/**
		 * An array of key, value pairs of data made available to the block on the client side.
		 *
		 * @return array
		 * @since 1.0.9
		 */
		public function get_script_data()
		{
// 			$time_interval = array(
// 				'12:00 AM - 1:00 AM', '1:00 AM - 2:00 AM', '2:00 AM - 3:00 AM', '3:00 AM - 4:00 AM',
// 				'4:00 AM - 5:00 AM', '5:00 AM - 6:00 AM', '6:00 AM - 7:00 AM', '7:00 AM - 8:00 AM',
// 				'8:00 AM - 9:00 AM', '9:00 AM - 10:00 AM', '10:00 AM - 11:00 AM', '11:00 AM - 12:00 PM',
// 				'12:00 PM - 1:00 PM', '1:00 PM - 2:00 PM', '2:00 PM - 3:00 PM', '3:00 PM - 4:00 PM',
// 				'4:00 PM - 5:00 PM', '5:00 PM - 6:00 PM', '6:00 PM - 7:00 PM', '7:00 PM - 8:00 PM',
// 				'8:00 PM - 9:00 PM', '9:00 PM - 10:00 PM', '10:00 PM - 11:00 PM', '11:00 PM - 12:00 AM',
// 			);
			$time_interval = array(
				'00:00- 1:00', '1:00 - 2:00', '2:00 - 3:00', '3:00 - 4:00',
				'4:00- 5:00', '5:00 - 6:00', '6:00 - 7:00', '7:00 - 8:00',
				'8:00- 9:00', '9:00 - 10:00', '10:00 - 11:00', '11:00 - 12:00',
				'12:00- 13:00', '13:00 - 14:00', '14:00 - 15:00', '15:00 - 16:00',
				'16:00- 17:00', '17:00 - `8:00', '18:00 - 19:00', '19:00 - 20:00',
				'20:00- 21:00', '21:00 - 22:00', '22:00 - 23:00', '23:00 - 00:00',
			);
			$data = array(
				'time_interval' => $time_interval
			);
			return $data;
		}
		public function register_share_cart_checkout_block_editor_styles()
		{
			wp_enqueue_style('sales-booster-checkout-block', WC_PICKUP_URL . 'build/style-sales-booster-checkout-block.css', array(), '1.0.9');
		}
		public function register_share_cart_checkout_block_editor_scripts()
		{
			$script_asset_path = WC_PICKUP_DIR . '/build/sales-booster-checkout-block.asset.php';
			$script_asset = file_exists($script_asset_path)
				? require $script_asset_path
				: array(
					'dependencies' => array('wp-data'),
					'version' => ''
				);
			wp_register_script(
				'sales-booster-checkout-block-editor',
				WC_PICKUP_URL . 'build/sales-booster-checkout-block.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_set_script_translations(
				'sales-booster-checkout-block-editor',
				'loyalty-program',
				dirname(__FILE__) . '/languages'
			);
		}
		public function register_share_cart_checkout_block_frontend_scripts()
		{
			$script_asset_path = WC_PICKUP_DIR . '/build/sales-booster-checkout-block-frontend.asset.php';
			$script_asset = file_exists($script_asset_path)
				? require $script_asset_path
				: array(
					'dependencies' => array('wp-data'),
					'version' => ''
				);
			wp_register_script(
				'sales-booster-checkout-block-frontend',
				WC_PICKUP_URL . 'build/sales-booster-checkout-block-frontend.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_set_script_translations(
				'sales-booster-checkout-block-frontend',
				'loyalty-program',
				dirname(__FILE__) . '/languages'
			);
		}
	}
}
