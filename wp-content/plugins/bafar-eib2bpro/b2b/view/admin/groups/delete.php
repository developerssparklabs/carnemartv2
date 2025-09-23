<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Groups', 'eib2bpro') ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(['do' => 'delete-group', 'id' => eib2bpro_get('id', 0)]); ?>
<div class="eib2bpro-app-new-item-content">
    <div class="container-fluid">
        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <?php echo eib2bpro_r(sprintf(esc_html__('You have %d users in this group', 'eib2bpro'), count($users))); ?>
                <?php
                foreach ($users as $user) {
                    echo eib2bpro_r(sprintf('<span class="badge badge-pill badge-danger ml-1">%s</span>', $user->user_login));
                }
                ?>

                <?php
                if (0 < count($users)) {
                    $params['options'] = [0 => esc_html__('B2C', 'eib2bpro')];
                    $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                    foreach ($groups as $group) {
                        if ($id === $group->ID) {
                            continue;
                        }
                        $params['options'][$group->ID] = $group->post_title;
                    } ?>
                    <?php eib2bpro_ui('select', 'eib2bpro_move_to', 0, $params) ?>
                    <br>
                    <button type="button" class="btn-save eib2bpro-app-save-button eib2bpro-os-stop-propagation"><?php eib2bpro_e('Move users and delete group') ?></button>
                <?php
                } else { ?>
                    <button type="button" class="btn-save eib2bpro-app-save-button eib2bpro-os-stop-propagation"><?php eib2bpro_e('Delete group') ?></button>
                <?php } ?>
            </div>
        </div>
    </div>
    </form>