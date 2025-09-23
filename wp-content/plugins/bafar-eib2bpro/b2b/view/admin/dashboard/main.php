<?php defined('ABSPATH') || exit; ?>

<?php $range = eib2bpro_option('b2b_dashboard_range', 7); ?>
<div class="eib2bpro-container-fluid">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>

    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2 eib2bpro-b2b-nav-container eib2bpro-menu-2-right-border">
                <?php echo eib2bpro_view('b2b', 'admin', 'nav') ?>
            </div>
            <div class="col-12 col-lg-10 mt-4 pt-3 pl-5 s-0">
                <div class="eib2bpro-b2b-main-container">
                    <h3 class="eib2bpro-app-settings-sub-title float-left">
                        <img src="<?php eib2bpro_a(EIB2BPRO_PUBLIC . 'core/public/img/hi.png') ?>" class="align-bottom"> &nbsp;<?php esc_html_e('Hi', 'eib2bpro') ?>, <strong><?php eib2bpro_e(get_user_meta(get_current_user_id(), 'first_name', true)) ?></strong>
                    </h3>
                    <div id="eib2bpro-reports" class="float-right m-0">
                        <div class="eib2bpro-Reports_Range btn-group" regtype="group" aria-label="Button group with nested dropdown">
                            <a href="<?php eib2bpro_e(eib2bpro_admin('b2b', ['range' => 7])) ?>" class="<?php eib2bpro_a((7 === $range) ? 'btn btn-secondary btn-dark eib2bpro-Selected' : 'btn btn-secondary') ?>"><?php esc_html_e('7 DAYS', 'eib2bpro'); ?></a>
                            <a href="<?php eib2bpro_e(eib2bpro_admin('b2b', ['range' => 30])) ?>" class="<?php eib2bpro_a((30 === $range) ? 'btn btn-secondary btn-dark eib2bpro-Selected' : 'btn btn-secondary') ?>"><?php esc_html_e('30 DAYS', 'eib2bpro'); ?></a>
                        </div>
                    </div>
                </div>


                <div class="eib2bpro-b2b-dashboard eib2bpro-Container mt-4">
                    <div class="eib2bpro-b2b-dashboard-overview eib2bpro-b2b-dashboard-revenue">
                        <div class="eib2bpro-b2b-sales-title d-none d-lg-block">
                            <?php esc_html_e('Sales', 'eib2bpro'); ?>
                        </div>
                        <div class="eib2bpro-b2b-sales">
                            <?php $stats = \EIB2BPRO\B2b\Admin\Dashboard::stats(); ?>
                            <?php for ($i = $range; $i >= 0; --$i) {
                                $date = date('Ymd', strtotime("now - $i days"));
                                $date_labels[$date] = date('d D', strtotime("now - $i days"));
                                $stats_data['b2b'][$date] = isset($stats[$date]['b2b']['revenue']) ? $stats[$date]['b2b']['revenue'] : 0;
                                $stats_data['b2c'][$date] = isset($stats[$date]['b2c']['revenue']) ? $stats[$date]['b2c']['revenue'] : 0;
                            }
                            ?>
                            <div class="eib2bpro-charts" data-type="area" data-height="450" data-sparkline="true" id="eib2bpro-chart-1" data-labels='<?php echo eib2bpro_r(json_encode(array_values($date_labels))) ?>' data-series='<?php echo eib2bpro_r(json_encode(
                                                                                                                                                                                                                        [
                                                                                                                                                                                                                            ['name' => 'B2B', 'data' => array_values($stats_data['b2b'])],
                                                                                                                                                                                                                            ['name' => 'B2C', 'data' => array_values($stats_data['b2c'])]
                                                                                                                                                                                                                        ]
                                                                                                                                                                                                                    )) ?>'></div>
                        </div>
                        <div class="clear-both"></div>
                    </div>

                    <?php
                    $non_approved_users = \EIB2BPRO\B2b\Admin\Dashboard::non_approved_users();
                    if (!empty($non_approved_users)) { ?>
                        <div class="eib2bpro-b2b-dashboard-overview eib2bpro-b2b-non-approved-users pt-1 pb-3">
                            <div class="eib2bpro-Reports_Div_Inner table-responsive ">
                                <table class="eib2bpro-Reports_Table table table-hover text-center">
                                    <thead>
                                        <th class="text-left"><?php esc_html_e('Name', 'eib2bpro'); ?></th>
                                        <th class="text-center"><?php esc_html_e('Type', 'eib2bpro'); ?></th>
                                        <th class="text-center"><?php esc_html_e('Group', 'eib2bpro'); ?></th>
                                        <th class="text-right">&nbsp;</th>
                                    </thead>
                                    <tbody>

                                        <?php foreach ($non_approved_users as $user_obj) {
                                            if (get_userdata($user_obj->ID)) { ?>
                                                <tr class="eib2bpro-list-user-id-<?php eib2bpro_a($user_obj->ID) ?>">
                                                    <td class="text-left">
                                                        <a href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $user_obj->ID)) ?>" class="eib2bpro-panel">
                                                            <?php
                                                            if (!empty(get_user_meta($user_obj->ID, 'first_name', true))) { ?>
                                                                <?php eib2bpro_e(get_user_meta($user_obj->ID, 'first_name', true)) ?> <?php eib2bpro_e(get_user_meta($user_obj->ID, 'last_name', true)) ?> &nbsp;
                                                            <?php } else {
                                                                eib2bpro_e($user_obj->user_email);
                                                            } ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $regtype = intval(get_user_meta($user_obj->ID, 'eib2bpro_registration_regtype', true));
                                                        if (0 < $regtype) {
                                                            eib2bpro_e(get_the_title(get_user_meta($user_obj->ID, 'eib2bpro_registration_regtype', true)));
                                                        } else {
                                                            esc_html_e('B2C', 'eib2bpro');
                                                        } ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $move = intval(get_user_meta($user_obj->ID, 'eib2bpro_user_move_to', true));
                                                        if (0 < $move) {
                                                            eib2bpro_e(get_the_title(get_user_meta($user_obj->ID, 'eib2bpro_user_move_to', true)));
                                                        } else {
                                                            esc_html_e('B2C', 'eib2bpro');
                                                        } ?>
                                                    </td>
                                                    <td class="text-right">
                                                        <a class="eib2bpro-Button1 eib2bpro-panel" href="<?php echo esc_url(admin_url('user-edit.php?user_id=' . $user_obj->ID)) ?>"><?php esc_html_e('View', 'eib2bpro'); ?></a>
                                                        <?php eib2bpro_ui('ajax_button', 'user_approve', 1, ['title' => esc_html__('Approve', 'eib2bpro'), 'id' => $user_obj->ID, 'do' => 'approve-user', 'status' => 'approve', 'move' => $move, 'class' => 'eib2bpro-Button1 bg-success text-white', 'confirm' => esc_html__('Are you sure?', 'eib2bpro')]); ?>
                                                        <?php eib2bpro_ui('ajax_button', 'user_approve', 1, ['title' => esc_html__('Decline', 'eib2bpro'), 'id' => $user_obj->ID, 'do' => 'approve-user', 'status' => 'reject', 'move' => $move, 'class' => 'eib2bpro-Button1 bg-danger text-white', 'confirm' => esc_html__('Are you sure?', 'eib2bpro')]); ?>
                                                    </td>
                                                </tr>
                                        <?php }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="eib2bpro-b2b-dashboard-overview">
                        <div class="eib2bpro-Reports_Div_Inner table-responsive ">
                            <table class="eib2bpro-Reports_Table table table-hover text-center">
                                <thead>
                                    <th class="text-left"><?php esc_html_e('Date', 'eib2bpro'); ?></th>
                                    <th class="text-right"><?php esc_html_e('Orders (B2B)', 'eib2bpro'); ?></th>
                                    <th class="text-right"><?php esc_html_e('Orders (B2C)', 'eib2bpro'); ?></th>
                                    <th class="text-right"><?php esc_html_e('Sales (B2B)', 'eib2bpro'); ?></th>
                                    <th class="text-right"><?php esc_html_e('Sales (B2C)', 'eib2bpro'); ?></th>
                                    <th class="text-right"><?php esc_html_e('Customers (B2B)', 'eib2bpro'); ?></th>
                                    <th class="text-right"><?php esc_html_e('Customers (B2C)', 'eib2bpro'); ?> </th>
                                </thead>
                                <tbody>
                                    <?php for ($i = 0; $i < $range; ++$i) {
                                        $date = eib2bpro_strtotime("now - $i days", 'Ymd');
                                        if (!isset($stats[$date]['b2b']['count'])) {
                                            continue;
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-left text-uppercase"><?php eib2bpro_e(date_i18n('d D', strtotime("now - $i days"))) ?></td>
                                            <td class="text-right"><?php eib2bpro_e(0 < intval($stats[$date]['b2b']['count']) ? $stats[$date]['b2b']['count'] : '-') ?></td>
                                            <td class="text-right"><?php eib2bpro_e(0 < intval($stats[$date]['b2c']['count']) ? $stats[$date]['b2c']['count'] : '-') ?></td>
                                            <td class="text-right"><?php eib2bpro_e(0 < intval($stats[$date]['b2b']['revenue']) ? $stats[$date]['b2b']['revenue'] : '-') ?></td>
                                            <td class="text-right"><?php eib2bpro_e(0 < intval($stats[$date]['b2c']['revenue']) ? $stats[$date]['b2c']['revenue'] : '-') ?></td>
                                            <td class="text-right"><?php eib2bpro_e(0 < intval($stats[$date]['b2b']['customer']) ? $stats[$date]['b2b']['customer'] : '-') ?></td>
                                            <td class="text-right"><?php eib2bpro_e(0 < intval($stats[$date]['b2c']['customer']) ? $stats[$date]['b2c']['customer'] : '-') ?></td>

                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>


                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>