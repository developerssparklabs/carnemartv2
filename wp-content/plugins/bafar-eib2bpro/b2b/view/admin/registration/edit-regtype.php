<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Registration Type', 'eib2bpro') ?> <?php eib2bpro_ui('wpml_selector'); ?><?php if (0 < eib2bpro_get('id', 0)) { ?> <span class="eib2bpro-font-14 font-weight-normal text-muted">#<?php echo eib2bpro_e(eib2bpro_get('id', 0)); ?></span><?php } ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(['do' => 'edit-registration-regtype', 'id' => eib2bpro_get('id', 0)]); ?>

<div class="eib2bpro-app-new-item-content">
    <div class="container-fluid">

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Name', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'title', eib2bpro_clean2(get_the_title($id), ''), ['class' => 'eib2bpro-autofocus']); ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label class="pb-4"><?php esc_html_e('Auto approval', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('onoff', 'eib2bpro_automatic_approval', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_automatic_approval', true), 1)) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Show a brief information text (optional)', 'eib2bpro'); ?></label>
                <textarea name="eib2bpro_message" class="form-control w-100"><?php echo eib2bpro_r(wp_kses_post(get_post_meta($id, 'eib2bpro_message', true))) ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Add new customers to this group after approval', 'eib2bpro'); ?></label>
                <?php
                $params['options'] = [0 => esc_html__('B2C', 'eib2bpro')];
                $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                foreach ($groups as $group) {
                    $params['options'][$group->ID] = get_the_title($group->ID);
                }
                ?>
                <?php eib2bpro_ui('select', 'eib2bpro_approval_group', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_approval_group', true), 1), $params) ?>
            </div>
        </div>

        <div class="row text-right pt-4">
            <div class="col-12 text-right pr-5">
                <?php eib2bpro_save() ?>
            </div>
        </div>
        <div class="row mt-5 pt-5 text-left">
            <div class="col-12 mt-3 text-left pl-5">
                <div class="pb-2"><?php esc_html_e('Registration form shortcode', 'eib2bpro'); ?></div>
                <strong>
                    <?php if (0 < eib2bpro_get('id', 0, 'int')) { ?>
                        <pre>[b2bpro_registration regtype_id=<?php eib2bpro_e(eib2bpro_get('id', 0, 'int')) ?> regtype_selector=show]</pre>
                    <?php } ?>
                </strong>
            </div>
        </div>

    </div>
</div>

</form>