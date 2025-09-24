<?php

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if (!class_exists('External_Deliveries')) {
	/**
	 * Class for integrating with WooCommerce Blocks
	 * 
	 * @since 1.0.9
	 */
	class External_Deliveries implements IntegrationInterface
	{
		/**
		 * The name of the integration.
		 *
		 * @return string
		 * @since 1.0.9
		 */
		public function get_name()
		{
			return 'external-deliveries';
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
				'external-deliveries-integration',
				WC_PICKUP_URL . 'build/index.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_set_script_translations(
				'external-deliveries-integration',
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
			return array('external-deliveries-integration', 'external-deliveries-frontend');
		}
		/**
		 * Returns an array of script handles to enqueue in the editor context.
		 *
		 * @return string[]
		 * @since 1.0.9
		 */
		public function get_editor_script_handles()
		{
			return array('external-deliveries-integration', 'external-deliveries-editor');
		}
		/**
		 * An array of key, value pairs of data made available to the block on the client side.
		 *
		 * @return array
		 * @since 1.0.9
		 */
		public function get_script_data()
		{
			
			$data = array(
			);
			return $data;
		}
		public function register_share_cart_checkout_block_editor_styles()
		{
			wp_enqueue_style('external-deliveries', WC_PICKUP_URL . 'build/style-external-deliveries.css', array(), '1.0.9');
		}
		public function register_share_cart_checkout_block_editor_scripts()
		{
			$script_asset_path = WC_PICKUP_DIR . '/build/external-deliveries.asset.php';
			$script_asset = file_exists($script_asset_path)
				? require $script_asset_path
				: array(
					'dependencies' => array('wp-data'),
					'version' => ''
				);
			wp_register_script(
				'external-deliveries-editor',
				WC_PICKUP_URL . 'build/external-deliveries.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_set_script_translations(
				'external-deliveries-editor',
				'loyalty-program',
				dirname(__FILE__) . '/languages'
			);
		}
		public function register_share_cart_checkout_block_frontend_scripts()
		{
			$script_asset_path = WC_PICKUP_DIR . '/build/external-deliveries-frontend.asset.php';
			$script_asset = file_exists($script_asset_path)
				? require $script_asset_path
				: array(
					'dependencies' => array('wp-data'),
					'version' => ''
				);
			wp_register_script(
				'external-deliveries-frontend',
				WC_PICKUP_URL . 'build/external-deliveries-frontend.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_set_script_translations(
				'external-deliveries-frontend',
				'loyalty-program',
				dirname(__FILE__) . '/languages'
			);
		}
	}
}
