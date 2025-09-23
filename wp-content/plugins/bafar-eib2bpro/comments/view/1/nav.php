<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2">
    <div class="row eib2bpro-gp">

        <ul>
            <?php if (0 < absint($count->total_comments)) { ?>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status') ?>" href="<?php echo eib2bpro_admin('comments', array()); ?>"><?php esc_html_e('All', 'eib2bpro'); ?>
                        <?php echo esc_html(absint($count->total_comments)) ?></a>
                </li>
            <?php } ?>
            <?php if (0 < absint($count->moderated)) { ?>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status', '-1') ?>" href="<?php echo eib2bpro_admin('comments', array('status' => '-1')); ?>"><?php esc_html_e('Pending', 'eib2bpro'); ?>
                        <span class="badge badge-secondary"><?php echo esc_html(absint($count->moderated)) ?></span></a>
                </li>
            <?php } ?>
            <?php if (0 < absint($count->approved)) { ?>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status', '1') ?>" href="<?php echo eib2bpro_admin('comments', array('status' => '1')); ?>"><?php esc_html_e('Approved', 'eib2bpro'); ?>
                        <span class="badge badge-secondary"><?php echo esc_html(absint($count->approved)) ?></span></a>
                </li>
            <?php } ?>
            <?php if (0 < absint($count->spam)) { ?>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status', 'spam') ?>" href="<?php echo eib2bpro_admin('comments', array('status' => 'spam')); ?>"><?php esc_html_e('Spam', 'eib2bpro'); ?>
                        <span class="badge badge-secondary"><?php echo esc_html(absint($count->spam)) ?></span></a>
                </li>
            <?php } ?>
            <?php if (0 < absint($count->trash)) { ?>
                <li><a class="eib2bpro-Button1<?php eib2bpro_selected('status', 'trash') ?>" href="<?php echo eib2bpro_admin('comments', array('status' => 'trash')); ?>"><?php esc_html_e('Deleted', 'eib2bpro'); ?>
                        <span class="badge badge-secondary"><?php echo esc_html(absint($count->trash)) ?></span></a>
                </li>
            <?php } ?>

            <?php do_action('eib2bpro_submenu', 'comments'); ?>

            <li class="eib2bpro-Li_Search">
                <a href="javascript:;" class="eib2bpro-Button1 eib2bpro-Search_Button"><?php esc_html_e('Search', 'eib2bpro'); ?></a>
            </li>
        </ul>
    </div>
</div>