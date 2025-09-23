<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2">
    <div class="eib2bpro-Scroll">
        <div class="row eib2bpro-gp">
            <ul>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status') ?>" href="<?php echo eib2bpro_admin('orders', array()); ?>"><?php esc_html_e('All Orders', 'eib2bpro'); ?> <?php echo esc_html($list['statuses_count']['count']) ?></a>
                </li>
                <?php foreach ($list['statuses'] as $status_k => $status) {
                    if ($list['statuses_count'][$status_k] > 0) { ?>
                        <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status', $status_k) ?>" href="<?php echo eib2bpro_admin('orders', array('status' => $status_k)); ?>"><?php echo esc_attr($status) ?>
                                <span class="eib2bpro-Count"><?php echo esc_html($list['statuses_count'][$status_k]) ?></span></a>
                        </li>
                <?php }
                } ?>

                <?php if (0 < $list['statuses_count']['trash']) { ?>
                    <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status', 'trash') ?>" href="<?php echo eib2bpro_admin('orders', array('status' => 'trash')); ?>"><?php esc_html_e('Trash', 'eib2bpro'); ?>
                            <span class="eib2bpro-Count"><?php echo esc_html($list['statuses_count']['trash']) ?></span></a>
                    </li>
                <?php } ?>

                <?php do_action('eib2bpro_submenu', 'orders'); ?>

                <li class="eib2bpro-Li_Search">
                    <a href="javascript:;" class="eib2bpro-Button1 eib2bpro-Search_Button"><?php esc_html_e('Search', 'eib2bpro'); ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>