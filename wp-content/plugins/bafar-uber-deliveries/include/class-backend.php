<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if ( !class_exists('UD_Uber_Backend') ) {
	class UD_Uber_Backend {
		public function __construct() {
			add_action('admin_menu', array($this, 'ud_settings_menu') );
			add_action('admin_init', array($this, 'ud_reg_settings') );
		}
		public function ud_settings_menu() {
			add_menu_page(esc_html__('Delivery Integrations', 'rappie-integration'), esc_html__('Delivery Integrations', 'rappie-integration'), 'manage_options', 'delivery-integrations', array($this, 'ud_settings_page' ), 'dashicons-products', 57);
			add_submenu_page('delivery-integrations', esc_html__('Settings', 'rappie-integration'), esc_html__('Settings', 'rappie-integration'), 'manage_options', 'delivery-integrations', array($this, 'ud_settings_page'));
		}
		public function ud_settings_page() {
			?>
			<form method="post" action="options.php">
				<?php settings_errors();
				settings_fields('ri_reg_settings');
				do_settings_sections('ri_reg_settings'); ?>
				<h3><?php esc_html_e('Delivery Integration Settings', 'rappie-integration'); ?></h3>
				<table class="form-table">
					<tr>
						<?php $value = get_option('ri_customer_id'); ?>
						<th><label for="ri_customer_id"><?php esc_html_e('Uber Customer ID', 'rappie-integration'); ?></label></th>
						<td>
							<input type="text" name="ri_customer_id" class="regular-text" id="ri_customer_id" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<tr>
						<?php $value = get_option('ri_client_id'); ?>
						<th><label for="ri_client_id"><?php esc_html_e('Uber Client ID', 'rappie-integration'); ?></label></th>
						<td>
							<input type="text" name="ri_client_id" class="regular-text" id="ri_client_id" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<tr>
						<?php $value = get_option('ri_client_secret_id'); ?>
						<th><label for="ri_client_secret_id"><?php esc_html_e('Uber Client Secret ID', 'rappie-integration'); ?></label></th>
						<td>
							<input type="text" name="ri_client_secret_id" class="regular-text" id="ri_client_secret_id" value="<?php echo $value; ?>" />
						</td>
					</tr>
					<tr>
						<?php $value = get_option('ri_rappi_api'); ?>
						<th><label for="ri_rappi_api"><?php esc_html_e('Rappi API', 'rappie-integration'); ?></label></th>
						<td>
							<input type="text" name="ri_rappi_api" class="regular-text" id="ri_rappi_api" value="<?php echo $value; ?>" />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		<?php
		}
		public function ud_reg_settings() {
			register_setting('ri_reg_settings', 'ri_customer_id');
			register_setting('ri_reg_settings', 'ri_client_id');
			register_setting('ri_reg_settings', 'ri_client_secret_id');
			register_setting('ri_reg_settings', 'ri_rappi_api');
		}
	}
	new UD_Uber_Backend();
}
