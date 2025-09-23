<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type'=> 1, 'title' => esc_html__('Reports', 'eib2bpro'), 'description' => '', 'buttons'=>'')); ?>

<?php echo eib2bpro_view('reports', 1, 'nav') ?>


<div id="eib2bpro-reports-woocommerce">
    <?php
  if (!empty($report)) {
      if ('customers' === $report) {
          $url =  admin_url('admin.php?page=wc-admin&path=/' . esc_attr($report));
      } else {
          $url =  admin_url('admin.php?page=wc-admin&path=/analytics/' . esc_attr($report));
      }
  } else {
      $url =  admin_url('admin.php?page=wc-reports');
  }
  ?>
    <iframe src="<?php echo esc_url($url)?>" id="eib2bpro-frame" frameborder=0></iframe>
</div>