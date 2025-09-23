<?php



add_filter(
	'woocommerce_locate_template',
	function ( $template, $template_name, $template_path ) {
    
                if( 'global/quantity-input.php' !== $template_name ) return $template;

		$show_on_product_page = apply_filters( 'wcmmq_show_on_product_page', true );
		$show_on_cart_page    = apply_filters( 'wcmmq_show_on_cart_page', true );

		if ( false === $show_on_product_page && is_product() ) {
			return $template;
		}

		if ( false === $show_on_cart_page && is_cart() ) {
			return $template;
		}

		global $woocommerce;

		$_template     = $template;
                
		$plugin_path   = WC_MMQ_PRO_BASE_DIR . '/template/';
		$template_path = ( ! $template_path ) ? $woocommerce->template_url : null;
		$template      = locate_template( array( $template_path . $template_name, $template_name ) );
		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		if ( ! $template ) {
			$template = $_template;
		}
		return $template;
	},
	11,
	3
);