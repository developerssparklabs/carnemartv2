<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="eib2bpro-title--menu eib2bpro-Coupons_Mode_2">
    <?php if (!eib2bpro_get('mini')) { ?>
        <div class="row eib2bpro-gp">
            <ul>
                <li>
                    <a class="eib2bpro-Button1<?php eib2bpro_selected('group') ?>" href="<?php echo eib2bpro_admin('customers', array()); ?>">
                        <?php esc_html_e('All', 'eib2bpro'); ?> <?php echo esc_html($counts['total_users']) ?>
                    </a>
                </li>
                <?php
                foreach ($counts as $key => $count) {
                    if ($count > 0 && 'total_users' !== $key) { ?>
                        <li>
                            <a class="eib2bpro-Button1<?php eib2bpro_selected('group', (string)$key) ?>" href="<?php echo eib2bpro_admin('customers', array('group' => $key)); ?>">
                                <?php eib2bpro_e('b2c' === $key ? esc_html__('B2C', 'eib2bpro') : get_the_title($key)); ?> <?php echo esc_html($count) ?></a>
                        </li>
                <?php }
                } ?>

                <?php do_action('eib2bpro_submenu', 'customers'); ?>

                <li class="eib2bpro-Li_Search">
                    <a href="javascript:;" class="eib2bpro-Button1 eib2bpro-Search_Button"><?php esc_html_e('Search', 'eib2bpro'); ?></a>
                </li>
            </ul>
        </div>
    <?php } ?>
</div>