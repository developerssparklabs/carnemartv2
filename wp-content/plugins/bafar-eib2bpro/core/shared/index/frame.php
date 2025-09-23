<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>

<?php echo eib2bpro_view('core', 0, 'shared.index.header-ei');
?>
<?php if (eib2bpro_get('in')) {
    echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => '', 'no_button' => ''));
} ?>

<?php if (
    FALSE !== stripos($page, 'wc-settings') ||
    FALSE !== stripos($page, 'wc-reports') ||
    FALSE !== stripos($page, 'wc-status') ||
    FALSE !== stripos($page, 'wc-addons') ||
    FALSE !== stripos($page, 'post_type=product') ||
    FALSE !== stripos($page, 'page=product_attributes') ||
    FALSE !== stripos($page, 'post_type=shop_order') ||
    FALSE !== stripos($page, 'post_type=shop_coupon')
) { // compatibility 
?>
    <div id="inbrowser--loading" class="inbrowser--loading position-absolute h-100 d-flex align-items-center align-middle h95">
        <div class="lds-ellipsis lds-ellipsis-black">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
<?php } ?>
<iframe src="<?php echo esc_url($page); ?>" id="eib2bpro-frame" class="eib2bpro-frame<?php if (eib2bpro_get('in')) {
                                                                                echo " eib2bpro-frame-in";
                                                                            }
                                                                            if (eib2bpro_get('to')) {
                                                                                echo " eib2bpro-frame-go";
                                                                            } ?>" frameborder=0></iframe>
</div>