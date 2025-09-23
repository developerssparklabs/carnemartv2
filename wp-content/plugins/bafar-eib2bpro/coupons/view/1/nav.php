<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2">
    <div class="row eib2bpro-gp">
        <ul>
            <li>
                <a class="eib2bpro-Button1<?php eib2bpro_selected('status') ?>" href="<?php echo eib2bpro_admin('coupons', array()); ?>"><?php esc_html_e('Active', 'eib2bpro'); ?> <?php eib2bpro_e(intval($counts->publish)) ?></a>
            </li>
            <li>
                <a class="eib2bpro-Button1<?php eib2bpro_selected('status', 'private') ?>" href="<?php echo eib2bpro_admin('coupons', array('status' => 'private')); ?>"><?php esc_html_e('Inactive', 'eib2bpro'); ?> <?php eib2bpro_e(intval($counts->private)) ?></a>
            </li>
            <li>
                <a class="eib2bpro-Button1<?php eib2bpro_selected('status', 'trash') ?>" href="<?php echo eib2bpro_admin('coupons', array('status' => 'trash')); ?>"><?php esc_html_e('Trash', 'eib2bpro'); ?> <?php eib2bpro_e(intval($counts->trash)) ?></a>
            </li>

            <?php do_action('eib2bpro_submenu', 'coupons'); ?>

            <li class="eib2bpro-Li_Search">
                <a href="javascript:;" class="eib2bpro-Button1 eib2bpro-Search_Button"><?php esc_html_e('Search', 'eib2bpro'); ?></a>
            </li>
        </ul>
    </div>
</div>