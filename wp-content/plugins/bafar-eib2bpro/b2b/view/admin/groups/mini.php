<?php defined('ABSPATH') || exit; ?>
<div class="row">
    <div class="col-6">
        <h6 class="text-uppercase text-muted mb-3"><?php esc_html_e('Users', 'eib2bpro'); ?></h6>
        <?php
        $users = \EIB2BPRO\B2b\Admin\Groups::users($group_id);
        if (!empty($users)) {
            foreach ($users as $user) {
                $userinfo = get_userdata($user);
        ?>
                <div class="eib2bpro-b2b-groups-mini-users clearfix">
                    <div class="float-left">
                        <a href="<?php echo esc_url(esc_url(admin_url('user-edit.php?user_id=' . $userinfo->ID))) ?>" class="eib2bpro-panel">
                            <?php eib2bpro_e(empty($userinfo->first_name) ? $userinfo->display_name : ($userinfo->first_name . ' ' . $userinfo->last_name)) ?>
                        </a>
                    </div>
                    <div class="float-right text-muted">
                        <?php eib2bpro_e(date_i18n('M, d', strtotime($userinfo->user_registered))); ?>
                    </div>
                </div>
            <?php
            }
            ?>
            <?php if (19 < count($users)) { ?>
                <div class="clearfix mt-2 mb-3">
                    <a href="<?php eib2bpro_a(eib2bpro_admin('customers', ['group' => $group_id])) ?>" class="eib2bpro-panel pl-1"><?php esc_html_e('Show all', 'eib2bpro'); ?></a>
                </div>
            <?php } ?>
        <?php } else { ?>
            <?php if (0 === count($orders['all']['orders'])) { ?>
                <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                    <div><span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                    </div>
                </div>
        <?php }
        } ?>
    </div>

    <div class="col-6">
        <div id="eib2bpro-customers-1">
            <div class="eib2bpro-Item_Details_X eib2bpro-Customer_Details border-0">
                <h6 class="text-uppercase text-muted mb-3"><?php esc_html_e('Orders', 'eib2bpro'); ?></h6>
                <?php
                $orders = \EIB2BPRO\Orders\Main::index(['group' => $group_id,  'post_status' => array_keys(wc_get_order_statuses()), 'limit' => 10, 'page' => 1, 'mode' => '97']);
                ?>
            </div>
        </div>
    </div>

</div>