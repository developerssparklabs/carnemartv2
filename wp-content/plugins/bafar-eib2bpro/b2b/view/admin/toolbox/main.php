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
                    <h4 class="eib2bpro-app-settings-sub-title pb-2">
                        <h3 class="mt-2 pl-3 font-weight-bold"><?php esc_html_e('Toolbox', 'eib2bpro') ?></h3>
                    </h4>

                    <div class="table-container eib2bpro-app-settings-form eib2bpro-shadow mb-5 mt-4">
                        <div class="table-item col-12">
                            <div class="p-30">
                                <h6 class=" pb-2"><?php esc_html_e('Clear all caches', 'eib2bpro'); ?></h6>
                                <?php eib2bpro_ui('ajax_button', 'clear_cache', '', ['title' => esc_html__('Clear now', 'eib2bpro'), 'do' => 'toolbox', 'run' => 'clear-all-caches', 'class' => 'btn-save']) ?>
                            </div>
                        </div>

                        <div class="table-item col-12">
                            <div class="p-30">
                                <h6 class=" pb-2"><?php esc_html_e('Move all users to', 'eib2bpro'); ?></h6>
                                <?php
                                eib2bpro_form(['do' => 'toolbox', 'run' => 'move-users']);
                                $params['options'] = [0 => esc_html__('B2C', 'eib2bpro')];
                                $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                                foreach ($groups as $group) {
                                    $params['options'][$group->ID] = get_the_title($group->ID);
                                }
                                ?>
                                <?php eib2bpro_ui('select', 'move_users_to', '', $params) ?>
                                <?php eib2bpro_save(esc_html__('Move', 'eib2bpro'), 'mt-3', ['data-confirm' => esc_html__('Are you sure to move all users to this group?', 'eib2bpro')]) ?>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>