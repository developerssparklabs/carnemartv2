<?php defined('ABSPATH') || exit; ?>
<?php $total = \EIB2BPRO\B2b\Admin\Groups::total_revenue(); ?>
<div class="eib2bpro-container-fluid">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2 eib2bpro-b2b-nav-container eib2bpro-menu-2-right-border">
                <?php echo eib2bpro_view('b2b', 'admin', 'nav') ?>
            </div>
            <div class="col-12 col-lg-10 pt-4 mt-2 pl-5 s-0 ">
                <div class="eib2bpro-b2b-main-container">
                    <div class="eib2bpro-app-settings-sub-title">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-lg-8">
                                    <h3 class="mt-2 pl-0 font-weight-bold"><?php esc_html_e('Groups', 'eib2bpro') ?></h3>
                                </div>
                                <div class="col-12 col-lg-4 mt-4 mt-lg-1 text-right">
                                    <a class="eib2bpro-panel btn btn-sm btn-danger eib2bpro-rounded" href="<?php echo eib2bpro_admin('b2b', ['section' => 'groups', 'action' => 'edit', 'id' => 0]) ?>" data-width="500px">
                                        + <?php esc_html_e('New group', 'eib2bpro'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="eib2bpro-List_M1 eib2bpro-Container mt-1">
                        <h6 class="text-muted mt-4 mb-0 pb-3 pl-3 ml-3 text-uppercase"><?php esc_html_e('B2B', 'eib2bpro') ?></h6>
                        <div class="eib2bpro-offers-container eib2bpro-list-container eib2bpro-shadow">
                            <?php if (empty($groups)) { ?>
                                <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center bg-white">
                                    <div>
                                        <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php foreach ($groups as $group) { ?>
                                <div class="btnA eib2bpro-Item eib2bpro-Item-Ajax collapsed p-0" id="item_<?php echo esc_attr($group->ID) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($group->ID) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($group->ID) ?>" data-app="b2b" data-do="b2b-group-details" data-id="<?php echo esc_attr($group->ID) ?>">
                                    <div class="liste d-flex align-items-center overflow-hidden">
                                        <div data-col="name" class="col-12 col-lg-4 col-id-name p-30">
                                            <h6 class="pl-3 eix-quick" data-href="<?php echo eib2bpro_admin('b2b', ['id' => $group->ID, 'section' => 'groups', 'action' => 'edit']) ?>" data-width="550px">
                                                <?php eib2bpro_e(get_the_title($group->ID)) ?>
                                            </h6>
                                            <div class="text-muted pl-3">#<?php eib2bpro_e($group->ID) ?></div>
                                        </div>

                                        <div data-col="slug" class="col-12 col-lg-8 pr-0 d-none d-md-block d-lg-block col-id-slug text-center">

                                            <div class="eib2bpro-offer-stats-box p-30">
                                                <div class="eib2bpro-offer-stats-number">
                                                    <?php if (0 < $total) {
                                                        echo eib2bpro_r(intval(intval(\EIB2BPRO\B2b\Admin\Groups::revenue($group->ID, false, true)) * 100 / $total));
                                                    } else {
                                                        echo '0';
                                                    }
                                                    ?>%
                                                </div>
                                                <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('of Revenue', 'eib2bpro'); ?></div>
                                            </div>

                                            <div class="eib2bpro-offer-stats-box p-30">
                                                <div class="eib2bpro-offer-stats-number"><?php eib2bpro_e(array_sum(wp_list_pluck(\EIB2BPRO\B2b\Admin\Groups::revenue($group->ID), 'count'))) ?></div>
                                                <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('Orders', 'eib2bpro'); ?></div>
                                            </div>

                                            <div class="eib2bpro-offer-stats-box p-30">
                                                <div class="eib2bpro-offer-stats-number"><?php eib2bpro_e(\EIB2BPRO\B2b\Admin\Groups::count_users($group->ID)) ?></div>
                                                <div class="eib2bpro-offer-stats-number text-muted text-uppercase"><?php esc_html_e('Users', 'eib2bpro'); ?></div>

                                            </div>

                                            <div data-col="slug" class="eib2bpro-b2b-chart-div d-none">
                                                <?php for ($i = 12; $i > 0; --$i) {
                                                    $date = date('Ym', strtotime("now - $i months"));
                                                    $date_labels[$date] = date('M, Y', strtotime("now - $i months"));
                                                }
                                                ?>
                                                <div class="eib2bpro-charts" data-type="bar" data-height="100" data-sparkline="true" id="eib2bpro-chart-<?php eib2bpro_a($group->ID) ?>" class="pr-3" data-labels='<?php echo eib2bpro_r(json_encode(array_values($date_labels))) ?>' data-series='<?php echo eib2bpro_r(json_encode([['name' => 'Revenue', 'data' => array_values(array_reverse(\EIB2BPRO\B2b\Admin\Groups::revenue($group->ID, true)))]])) ?>' class="pr-3"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="collapse col-xs-12 col-sm-12 col-md-12" id="item_d_<?php echo esc_attr($group->ID) ?>">
                                        <div class="eib2bpro-Item_Details pl-0">
                                            <div class="container">
                                                <div class="row">
                                                    <div class="col-12 d-none d-md-block d-lg-block">
                                                        <div class="eib2bpro-Item-Ajax-Container mb-3"><?php esc_html_e('Loading', 'eib2bpro'); ?></div>
                                                    </div>
                                                </div>
                                                <div class="row eib2bpro-Item_Details m-0 pr-0">
                                                    <div class="col-12 text-right">
                                                        <a href="<?php echo eib2bpro_admin('b2b', ['id' => $group->ID, 'section' => 'groups', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="550px"><?php esc_html_e('Edit', 'eib2bpro') ?></a>
                                                        <a href="<?php echo eib2bpro_admin('b2b', ['id' => $group->ID, 'section' => 'groups', 'action' => 'delete']) ?>" class="eib2bpro-panel text-danger" data-width="550px"><?php esc_html_e('Delete', 'eib2bpro') ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <h6 class="text-muted mt-4 mb-0 pb-3 pl-3 ml-3 text-uppercase"><?php esc_html_e('B2C', 'eib2bpro') ?></h6>
                        <div class="eib2bpro-Orders_Container">
                            <div class="btnA eib2bpro-Item collapsed eib2bpro-panel p-0" id="item_01" data-target="#item_d_01" aria-expanded="false" aria-controls="item_d_01" data-width="500px" href="<?php echo eib2bpro_admin('b2b', ['id' => -1, 'section' => 'groups', 'action' => 'edit']) ?>">
                                <div class="list  d-flex align-items-center">
                                    <div data-col="name" class="col col-12 p-30 col-id-name">
                                        <h6 class="m-0 pl-3"><?php esc_html_e('Settings for all B2C users', 'eib2bpro') ?></h6>
                                    </div>
                                </div>
                                <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_01">
                                    <div class="eib2bpro-Item_Details">
                                        <a href="<?php echo eib2bpro_admin('eib2bpro', ['id' => -1, 'section' => 'groups', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="500px"><?php esc_html_e('Edit', 'eib2bpro') ?></a>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted mt-4 mb-0 pb-3 pl-3 ml-3 text-uppercase"><?php esc_html_e('Guests', 'eib2bpro') ?></h6>
                        <div class="eib2bpro-Orders_Container">
                            <div class="btnA eib2bpro-Item collapsed eib2bpro-panel p-0" id="item_02" data-width="500px" href="<?php echo eib2bpro_admin('b2b', ['id' => -2, 'section' => 'groups', 'action' => 'edit']) ?>" data-target="#item_d_02" aria-expanded="false" aria-controls="item_d_02">
                                <div class="liste  d-flex align-items-center">
                                    <div data-col="name" class="col col-12 p-30 col-id-name">
                                        <h6 class="m-0 pl-3"><?php esc_html_e('Settings for Guests', 'eib2bpro') ?></h6>
                                    </div>
                                </div>
                                <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_02">
                                    <div class="eib2bpro-Item_Details">
                                        <a href="<?php echo eib2bpro_admin('eib2bpro', ['id' => -2, 'section' => 'groups', 'action' => 'edit']) ?>" class="eib2bpro-panel" data-width="500px"><?php esc_html_e('Edit', 'eib2bpro') ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>