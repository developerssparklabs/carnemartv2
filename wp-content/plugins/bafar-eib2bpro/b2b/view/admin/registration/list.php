<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-container-fluid eib2bpro-app-b2b-settings-registration">
    <div class="eib2bpro-title">
        <h3><?php esc_html_e('B2B', 'eib2bpro'); ?></h3>
    </div>
    <div class="eib2bpro-gp">
        <div class="row">
            <div class="col-12 col-lg-2  eib2bpro-b2b-nav-container eib2bpro-menu-2-right-border">
                <?php echo eib2bpro_view('b2b', 'admin', 'nav') ?>
            </div>
            <div class="col-12 col-lg-10 pt-4 mt-2 pl-5 s-0">
                <div class="eib2bpro-b2b-main-container">
                    <div class="eib2bpro-app-settings-sub-title">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-lg-8">
                                    <h3 class="mt-2 font-weight-bold"><?php esc_html_e('Extend', 'eib2bpro') ?></h3>
                                </div>
                                <div class="col-12 col-lg-4 mt-4 mt-lg-1 text-right">
                                    <a class="eib2bpro-panel btn btn-sm btn-danger eib2bpro-rounded  eib2bpro-field-button <?php eib2bpro_a('regtypes' === eib2bpro_get('tab', 'fields') ? 'd-none' : '') ?>" href="<?php echo eib2bpro_admin('b2b', ['section' => 'fields', 'action' => 'edit-field', 'id' => 0]) ?>" data-width="550px">
                                        + <?php esc_html_e('New field', 'eib2bpro'); ?>
                                    </a>
                                    <a class="eib2bpro-panel btn btn-sm btn-danger eib2bpro-rounded  eib2bpro-regtype-button <?php eib2bpro_a('fields' === eib2bpro_get('tab', 'fields') ? 'd-none' : '') ?>" href="<?php echo eib2bpro_admin('b2b', ['section' => 'fields', 'action' => 'edit-regtype', 'id' => 0]) ?>" data-width="500px">
                                        + <?php esc_html_e('New type', 'eib2bpro'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="table-container eib2bpro-shadow mt-3">
                        <div id="carouselControls" class="carousel slide w-100">
                            <ul class="carousel-indicators carousel-groups">
                                <li data-save="0" data-target="#carouselControls" data-slide-to="0" data-location="fields" class="<?php eib2bpro_a('fields' === eib2bpro_get('tab', 'fields') ? 'active' : ''); ?>">
                                    <?php esc_html_e('Custom Fields', 'eib2bpro') ?>
                                </li>
                                <li data-save="1" data-target="#carouselControls" data-slide-to="1" data-location="regtypes" class="<?php eib2bpro_a('regtypes' === eib2bpro_get('tab', 'fields') ? 'active' : ''); ?>">
                                    <?php esc_html_e('Registration Types', 'eib2bpro') ?>
                                </li>
                            </ul>
                            <?php eib2bpro_form(['do' => 'registration-positions']) ?>
                            <div class="carousel-inner">


                                <div class="carousel-item <?php eib2bpro_a('fields' === eib2bpro_get('tab', 'fields') ? 'active' : '') ?>" data-id="0" data-do="registration-positions">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <input name="tab" type="hidden" value="fields">
                                            <div class="eib2bpro-Container">
                                                <div>
                                                    <ol class="eib2bpro-sortable eib2bpro-sortable-registration-regtypes">
                                                        <?php if (!empty($fields)) {
                                                            foreach ($fields as $field) { ?>
                                                                <li>
                                                                    <div class="btnA eib2bpro-Item eib2bpro-no-scale eib2bpro-p25 eib2bpro-panel collapsed" id="item_<?php echo esc_attr($field->ID) ?>" data-width="550px" href="<?php echo eib2bpro_admin('b2b', ['id' => $field->ID, 'section' => 'fields', 'action' => 'edit-field']) ?>">
                                                                        <div class="liste row d-flex align-items-center">


                                                                            <div data-col="name" class="col-12 col-lg-6 col-id-name">
                                                                                <input type="hidden" name="position[regtype][<?php eib2bpro_a($field->ID) ?>]" value="1">
                                                                                <h6 class="m-0">
                                                                                    <?php eib2bpro_ui('onoff_ajax', 'status', 'publish' === $field->post_status ? 1 : 0, ['app' => 'b2b', 'do' => 'change-field-status', 'id' => $field->ID, 'class' => 'mt-1']) ?>

                                                                                    &nbsp;&nbsp;&nbsp;
                                                                                    <a class="eib2bpro-panel" data-width="550px" href="<?php echo eib2bpro_admin('b2b', ['id' => $field->ID, 'section' => 'fields', 'action' => 'edit-field']) ?>"><?php eib2bpro_e(get_the_title($field->ID)) ?></a>
                                                                                </h6>
                                                                            </div>
                                                                            <div data-col="slug" class="col-6 col-lg-4 d-none d-lg-block col-id-regtype">
                                                                                <h6 class="m-0">
                                                                                    <?php
                                                                                    $registration_regtypes = get_post_meta($field->ID, 'eib2bpro_registration_regtypes', true);
                                                                                    $group = (array)\EIB2BPRO\B2b\Admin\Registration::get_regtypes();
                                                                                    if (is_array($registration_regtypes) && 0 !== intval($registration_regtypes[0])) {
                                                                                        foreach ((array)$registration_regtypes as $regtype) { ?>
                                                                                            <span class="badge badge-secondary text-uppercase"><?php eib2bpro_e(get_the_title($regtype)) ?></span>
                                                                                        <?php }
                                                                                    } else { ?>
                                                                                        <span class="badge badge-secondary text-uppercase"><?php esc_html_e('All', 'eib2bpro') ?></span>
                                                                                    <?php } ?>
                                                                                </h6>
                                                                            </div>

                                                                            <div data-col="type" class="col col-2 col-id-required d-none d-lg-block text-center">
                                                                                <h6 class="m-0">
                                                                                    <?php if (1 === intval(get_post_meta($field->ID, 'eib2bpro_field_registration_required', true))) {
                                                                                        echo "<span class='badge badge-border-danger text-bold'>" . esc_html__('Required', 'eib2bpro') . '</span>';
                                                                                    }
                                                                                    ?>
                                                                                </h6>
                                                                                <a href="<?php echo get_delete_post_link($field->ID, false, 'trash' === eib2bpro_get('status') ? true : false); ?>" class="eib2bpro-confirm eib2bpro-os-move text-danger mr-3" data-confirm="<?php esc_attr_e('Are you sure?', 'eib2bpro'); ?>"><i class="eib2bpro-os-move eib2bpro-os-delete ri-delete-bin-6-line"></i></a>
                                                                                <i class="eib2bpro-os-move eib2bpro-icon-move pr-0"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php }
                                                        } else { ?>
                                                            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                                                                <div>
                                                                    <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="carousel-item <?php eib2bpro_a('regtypes' === eib2bpro_get('tab', 'fields') ? 'active' : '') ?>" data-id="1" data-do="registration-positions">
                                    <div class="row">
                                        <div class="container-fluid">
                                            <input name="tab" type="hidden" value="regtypes">
                                            <div class="eib2bpro-Container">
                                                <div>
                                                    <ol class="eib2bpro-sortable eib2bpro-sortable-registration-regtypes">
                                                        <?php
                                                        if (!empty($regtypes)) {
                                                            foreach ($regtypes as $regtype) { ?>
                                                                <li>
                                                                    <div class="btnA eib2bpro-Item eib2bpro-no-scale eib2bpro-p25 eib2bpro-panel collapsed" id="item_<?php echo esc_attr($regtype->ID) ?>" data-width="500px" href="<?php echo eib2bpro_admin('b2b', ['id' => $regtype->ID, 'section' => 'fields', 'action' => 'edit-regtype']) ?>">
                                                                        <div class="liste row d-flex align-items-center">
                                                                            <div data-col="name" class="col-12 col-lg-5 col-id-name pl-3">
                                                                                <input type="hidden" name="position[regtype][<?php eib2bpro_a($regtype->ID) ?>]" value="1">
                                                                                <h6 class="m-0">
                                                                                    <?php eib2bpro_ui('onoff_ajax', 'status', 'publish' === $regtype->post_status ? 1 : 0, ['app' => 'b2b', 'do' => 'change-regtype-status', 'id' => $regtype->ID, 'class' => 'mt-1']) ?>
                                                                                    &nbsp;&nbsp;&nbsp;
                                                                                    <a class="eib2bpro-panel" data-width="500px" href="<?php echo eib2bpro_admin('b2b', ['id' => $regtype->ID, 'section' => 'fields', 'action' => 'edit-regtype']) ?>"><?php eib2bpro_e($regtype->post_title) ?></a>
                                                                                </h6>
                                                                            </div>
                                                                            <div data-col="slug" class="col-6 col-lg-5 col-id-slug  d-none d-lg-block">
                                                                                <h6 class="m-0">
                                                                                    <?php
                                                                                    $group_id = get_post_meta($regtype->ID, 'eib2bpro_approval_group', true);
                                                                                    $group = \EIB2BPRO\B2b\Admin\Groups::get($group_id);
                                                                                    if (0 < count($group) && 0 < $group_id) { ?>
                                                                                        <span class="badge badge-secondary text-uppercase"><?php eib2bpro_e($group[0]->post_title) ?></span>
                                                                                    <?php } else { ?>
                                                                                        <span class="badge badge-secondary text-uppercase"><?php esc_html_e('B2C', 'eib2bpro') ?></span>
                                                                                    <?php } ?>
                                                                                </h6>
                                                                            </div>
                                                                            <div data-col="type" class="col col-lg-2 col-id-type d-none d-lg-block text-left">
                                                                                <h6 class="m-0">
                                                                                    <?php if (1 === intval(get_post_meta($regtype->ID, 'eib2bpro_automatic_approval', true))) {
                                                                                        echo "<span class='badge badge-border-success text-bold'>" . esc_html__('Auto approval', 'eib2bpro') . '</span>';
                                                                                    } else {
                                                                                        echo "<span class='badge badge-border-danger text-bold'>" . esc_html__('Manual approval', 'eib2bpro') . '</span>';
                                                                                    }
                                                                                    ?>
                                                                                </h6>
                                                                                <a href="<?php echo get_delete_post_link($regtype->ID, false, 'trash' === eib2bpro_get('status') ? true : false); ?>" class="eib2bpro-confirm eib2bpro-os-move text-danger mr-3" data-confirm="<?php esc_attr_e('Are you sure?', 'eib2bpro'); ?>"><i class="eib2bpro-os-move eib2bpro-os-delete ri-delete-bin-6-line"></i></a>
                                                                                <i class="eib2bpro-os-move eib2bpro-icon-move pr-0"></i>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php }
                                                        } else { ?>
                                                            <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                                                                <div>
                                                                    <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <button class="eib2bpro-app-save-button-hidden"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>