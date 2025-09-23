<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2">
    <?php if (!eib2bpro_get('group')) { ?>
    <div class="row eib2bpro-gp">
        <ul>
            <li><a class="eib2bpro-Button1<?php eib2bpro_selected('role') ?>" href="<?php echo eib2bpro_admin('customers', array()); ?>"><?php esc_html_e('All', 'eib2bpro'); ?><?php echo esc_html($counts['total_users']) ?></a>
            </li>
            <?php
                if (isset($counts['avail_roles'])) {
                    foreach ($counts['avail_roles'] as $key => $count) {
                        if ($count > 0) { ?>
            <li>
                <a class="eib2bpro-Button1<?php eib2bpro_selected('role', $key) ?>" href="<?php echo eib2bpro_admin('customers', array('role' => $key)); ?>">
                    <?php echo esc_html($roles[$key]['name']); ?><?php echo esc_html($count) ?></a>
            </li>
            <?php }
                    }
                } ?>

            <?php do_action('eib2bpro_submenu', 'customers'); ?>

            <li class="eib2bpro-Li_Search">
                <a href="javascript:;" class="eib2bpro-Button1 eib2bpro-Search_Button"><?php esc_html_e('Search', 'eib2bpro'); ?></a>
            </li>
        </ul>
    </div>
    <?php } ?>
</div>