<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if ( !class_exists('PQ_Quantity_Backend') ) {
	class PQ_Quantity_Backend {
		public function __construct() {
			add_filter('woocommerce_product_data_tabs', array($this, 'pq_quantity_tabs'), 10, 1 );
			add_action('woocommerce_product_data_panels', array($this, 'pq_quantity_data_panels') );
			add_action('woocommerce_process_product_meta', array($this, 'pq_quantity_save_tabs_data'), 99, 1 );
		}
		public function pq_quantity_tabs( $tabs ) {
			$tabs['pq_product_quantity'] = array(
				'label'  => esc_html__('Product Quantity', 'wc-spq'),
				'target' => 'pq_product_quantity_options',
				'class'  => 'show_if_simple',
			);
			return $tabs;
		}
		public function pq_quantity_data_panels() {
			global $post;
			$post_id = $post->ID;
			wp_nonce_field('ri_quantity_nonce', 'ri_quantity_nonce'); 
			?>
			<div id="pq_product_quantity_options" class="panel woocommerce_options_panel wc-metaboxes-wrapper">
				<?php
				$value = get_post_meta($post_id, 'ri_quantity_step', true);
				woocommerce_wp_text_input(
					array(
						'id' => 'ri_quantity_step',
						'placeholder' => esc_html__('Enter quantity step number', 'rappie-integration'),
						'label' => esc_html__('Quantity Step', 'rappie-integration'),
						'type' => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min' => '0'
						)
					)
				);
			$value = get_post_meta($post_id, 'ri_quantity_step_label', true);
				woocommerce_wp_text_input(
					array(
						'id' => 'ri_quantity_step_label',
						'placeholder' => esc_html__('Enter Label', 'rappie-integration'),
						'label' => esc_html__('Quantity Step', 'rappie-integration'),
						'custom_attributes' => array(
							'step' => 'any',
							'min' => '0'
						)
					)
				);
				?>
			</div>
			<?php
		}
		
		public function pq_quantity_save_tabs_data( $post_id ) {
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}
			if ( !isset($_POST['ri_quantity_nonce']) || !wp_verify_nonce( wc_clean($_POST['ri_quantity_nonce']), 'ri_quantity_nonce') ) {
				return;
			}
			if ( !current_user_can('edit_post', $post_id) ) {
				return;
			}
			if ( !empty($_POST['ri_quantity_step']) ) {
				update_post_meta( $post_id, 'ri_quantity_step', wc_clean($_POST['ri_quantity_step']) );
			} else {
				update_post_meta( $post_id, 'ri_quantity_step', '');
			}
			if ( !empty($_POST['ri_quantity_step_label']) ) {
				update_post_meta( $post_id, 'ri_quantity_step_label', wc_clean($_POST['ri_quantity_step_label']) );
			} else {
				update_post_meta( $post_id, 'ri_quantity_step_label', '');
			}
		}
	}
	new PQ_Quantity_Backend();
}
