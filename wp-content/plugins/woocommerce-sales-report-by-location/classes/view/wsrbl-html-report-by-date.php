<?php
/**
 * Admin View: Report by Date (with date filters)
 *
 * @package WooCommerce/Admin/Reporting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$analyticsurl = admin_url('admin.php?page=wc-admin&path=/analytics/orders');

echo '<div class="notice notice-success">
		<p>'.esc_html__('Try our new WooCommerce Analytics from ', 'woocommerce').'
		<a href="'.$analyticsurl.'">here.</a></p></div>';

?>

<div id="poststuff" class="woocommerce-reports-wide sales-location-report">
	<div class="postbox">

	<?php if ( 'custom' === $current_range && isset( $_GET['start_date'], $_GET['end_date'] ) ) : ?>
		<h3 class="screen-reader-text">
			<?php
			/* translators: 1: start date 2: end date */
			printf(
				esc_html__( 'From %1$s to %2$s', 'woocommerce' ),
				esc_html( wc_clean( wp_unslash( $_GET['start_date'] ) ) ),
				esc_html( wc_clean( wp_unslash( $_GET['end_date'] ) ) )
			);
			?>
		</h3>
	<?php else : ?>
		<h3 class="screen-reader-text"><?php echo esc_html( $ranges[ $current_range ] ); ?></h3>
	<?php endif; ?>
	
		<div class="stats_range">
			<?php $this->wsrbl_get_export_button(); ?>
			<ul>
				<li class="custom" style="border-right: 1px solid #dfdfdf;">
					<select class="select wcmlim-select-option-report" id="sales_report_location_by" name="sales_report_location_by">
					  <option value="shipping" <?php if ( isset($_GET['report_country_by']) && $_GET['report_country_by'] == 'shipping' ){ echo "selected='selected'";}?> >By Shipping Address</option>
					  <option value="billing" <?php if ( isset($_GET['report_country_by']) && $_GET['report_country_by'] == 'billing' ){ echo "selected='selected'";}?> >By Billing Address</option>
					  <?php
					$all_apl=get_option('active_plugins');
							$all_plugins=get_plugins();
							$activated_wcmlim = '';
							foreach ($all_apl as $p){           
							if(isset($all_plugins[$p]['Name'])){
								if($all_plugins[$p]['Name'] == "WooCommerce Multi Locations Inventory Management")
								{
								$activated_wcmlim = 1;
								}
							}           
							}
					// //if multi location is on $activated_wcmlim = 1
					if(!empty($activated_wcmlim))
						{
					//   if (in_array('WooCommerce-Multi-Locations-Inventory-Management/wcmlim.php', apply_filters('active_plugins', get_option('active_plugins'))))
					//   {
						  ?>
						  <option value="report_wcmlim_locations" <?php if ( isset($_GET['report_country_by']) && $_GET['report_country_by'] == 'report_wcmlim_locations' ){ echo "selected='selected'";}?> >By Order Location (WCMLIM)</option>
						<?php
						}
						?>
					</select>
				</li>
				<?php
				foreach ( $ranges as $range => $name ) {
					echo '<li class="' . ( $current_range == $range ? 'active' : '' ) . '"><a href="' . esc_url( remove_query_arg( array( 'start_date', 'end_date' ), add_query_arg( 'range', $range ) ) ) . '">' . esc_html( $name ) . '</a></li>';
				}
				?>
				<li class="custom <?php echo ( 'custom' === $current_range ) ? 'active' : ''; ?>">
					<?php esc_html_e( 'Date:', 'woocommerce' ); ?>
					<form method="GET">
						<div>
							<?php
							// Maintain query string.
							foreach ( $_GET as $key => $value ) {
								if ( is_array( $value ) ) {
									foreach ( $value as $v ) {
										echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '[]" value="' . esc_attr( sanitize_text_field( $v ) ) . '" />';
									}
								} else {
									echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( $key ) ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '" />';
								}
							}
							?>
							<input type="hidden" name="range" value="custom" />
							<input type="text" size="11" placeholder="yyyy-mm-dd" value="<?php echo ( ! empty( $_GET['start_date'] ) ) ? esc_attr( wp_unslash( $_GET['start_date'] ) ) : ''; ?>" name="start_date" class="range_datepicker from" autocomplete="off" /><?php //@codingStandardsIgnoreLine ?>
							<span>&ndash;</span>
							<input type="text" size="11" placeholder="yyyy-mm-dd" value="<?php echo ( ! empty( $_GET['end_date'] ) ) ? esc_attr( wp_unslash( $_GET['end_date'] ) ) : ''; ?>" name="end_date" class="range_datepicker to" autocomplete="off" /><?php //@codingStandardsIgnoreLine ?>
							<button type="submit" class="button" value="<?php esc_attr_e( 'Go', 'woocommerce' ); ?>"><?php esc_html_e( 'Go', 'woocommerce' ); ?></button>
							<?php wp_nonce_field( 'custom_range', 'wc_reports_nonce', false ); ?>
						</div>
					</form>
				</li>
			</ul>
		</div>
		<?php 
		
		?>
		<?php if ( empty( $hide_sidebar ) ) : ?>
			<div class="inside chart-with-sidebar">
				<?php if ( $legends = $this->wsrbl_get_chart_legend() ) : ?>
					<ul class="chart-legend">
                    	<?php foreach ( $legends as $legend ) : ?>
                    		<?php // @codingStandardsIgnoreStart ?>
							<li style="border-color: <?php echo isset($legend['color']) ? $legend['color'] : '' ; ?>" <?php if ( isset( $legend['highlight_series'] ) ) echo 'class="highlight_series ' . ( isset( $legend['placeholder'] ) ? 'tips' : '' ) . '" data-series="' . esc_attr( $legend['highlight_series'] ) . '"'; ?> data-tip="<?php echo isset( $legend['placeholder'] ) ? $legend['placeholder'] : ''; ?>">
								<?php echo $legend['title']; ?>
							</li>
							<?php // @codingStandardsIgnoreEnd ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<div class="chart-sidebar">					
					<ul class="chart-widgets">
						<?php foreach ( $this->get_chart_widgets() as $widget ) : ?>
							<li class="chart-widget">
								<?php if ( $widget['title'] ) : ?>
									<h4><?php echo esc_html( $widget['title'] ); ?></h4>
								<?php endif; ?>
								<?php call_user_func( $widget['callback'] ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="main">
					<?php $this->get_main_chart(); ?>
				</div>
			</div>
		<?php else : ?>
			<div class="inside">
				<?php $this->get_main_chart(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
