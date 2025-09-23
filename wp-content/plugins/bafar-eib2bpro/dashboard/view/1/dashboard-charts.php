<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
<?php
echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_attr__('Dashboard', 'eib2bpro'), 'description' => '', 'buttons' => '<a href="' . eib2bpro_admin('dashboard', array('action' => 'default')) . '" class="eib2bpro-Dashboard_Buttons">' . esc_html__('Overview', 'eib2bpro') . '</a> <a href="' . eib2bpro_admin('dashboard', array('action' => 'wc-admin')) . '" class="eib2bpro-Dashboard_Buttons eib2bpro-Selected">' . esc_html__('Charts', 'eib2bpro') . '</a>'));
?>

<div id="eib2bpro-wp-notices" class="eib2bpro-WP_Notices_Container eib2bpro-GP"><?php apply_filters('admin_notices', array()); ?></div>

<div id="eib2bpro-dashboard-wc-admin">
    <iframe src="<?php echo esc_url(admin_url('admin.php?page=wc-admin&path=/analytics/revenue')) ?> " id="eib2bpro-frame" frameborder=0></iframe>
</div>