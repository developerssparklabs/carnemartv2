<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Fields', 'eib2bpro') ?> <?php eib2bpro_ui('wpml_selector'); ?></h5>

        </div>
    </div>
</div>
</div>

<?php eib2bpro_form(['do' => 'edit-quote-field', 'id' => eib2bpro_get('id', 0)]); ?>

<div class="eib2bpro-app-new-item-content eib2bpro-app-b2b-registration-fields-edit-form">
    <div class="container-fluid">
        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Name', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'title', 0 < $id ? get_the_title($id) : '', ['class' => 'eib2bpro-autofocus', 'attr' => 'required']) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Type', 'eib2bpro'); ?></label>
                <?php
                $params['attr'] = '';
                $params['class'] = '';
                $params['options'] = [
                    'text' => esc_html__('Text', 'eib2bpro'),
                    'textarea' => esc_html__('Textarea', 'eib2bpro'),
                    'email' => esc_html__('Email', 'eib2bpro'),
                    'number' => esc_html__('Number', 'eib2bpro'),
                    'select' => esc_html__('Select', 'eib2bpro'),
                    'checkbox' => esc_html__('Checkbox', 'eib2bpro'),
                    'date' => esc_html__('Date', 'eib2bpro'),
                    'file' => esc_html__('File', 'eib2bpro'),
                ];
                ?>
                <?php eib2bpro_ui('select', 'eib2bpro_field_type', get_post_meta($id, 'eib2bpro_field_type', true), $params) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-6">
                <label><?php esc_html_e('Label', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'eib2bpro_field_label', get_post_meta($id, 'eib2bpro_field_label', true), ['class' => '', 'attr' => 'required']) ?>
            </div>

            <div class="eib2bpro-app-new-item-row col-6">
                <label><?php esc_html_e('Placeholder', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'eib2bpro_field_placeholder', get_post_meta($id, 'eib2bpro_field_placeholder', true), ['class' => '', 'attr' => '']) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12 eib2bpro-b2b-field-options<?php if ('checkbox' !== get_post_meta($id, 'eib2bpro_field_type', true) && 'select' !== get_post_meta($id, 'eib2bpro_field_type', true)) {
                                                                            echo ' hidden';
                                                                        } ?>">
                <label><?php esc_html_e('Options', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'eib2bpro_field_options', get_post_meta($id, 'eib2bpro_field_options', true), ['class' => '', 'attr' => 'placeholder="' . esc_attr__('Enter options sepereted by comma', 'eib2bpro') . '"']) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label class="pb-4"><?php esc_html_e('Required', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('onoff', 'eib2bpro_field_required', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_required', true), 1)) ?>
            </div>
        </div>
        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Who can see this field', 'eib2bpro'); ?></label>

                <input type="radio" value="0" name="eib2bpro_group_selector" id="rb1" class="eib2bpro_radio_selector eib2bpro-radio-2" data-hide='.eib2bpro-b2b-selected-groups' <?php checked(0, intval(get_post_meta($id, 'eib2bpro_group_selector', true))) ?> />
                <label for="rb1" class="eib2bpro-radio-2"><?php esc_html_e('All groups', 'eib2bpro'); ?></label>
                <input type="radio" value="1" name="eib2bpro_group_selector" id="rb2" class="eib2bpro_radio_selector eib2bpro-radio-2" data-show='.eib2bpro-b2b-selected-groups' <?php checked(1, get_post_meta($id, 'eib2bpro_group_selector', true)) ?> />
                <label for="rb2" class="eib2bpro-radio-2"><?php esc_html_e('Selected groups', 'eib2bpro'); ?></label>

                <div class="eib2bpro-b2b-selected-groups<?php eib2bpro_a(0 === intval(get_post_meta($id, 'eib2bpro_group_selector', true)) ? ' hidden' : '') ?>">
                    <?php
                    $groups = [
                        'guest' => esc_html__('Guests', 'eib2bpro'),
                        'b2c' => esc_html__('B2C users', 'eib2bpro')
                    ];
                    foreach ($groups as $group_id => $group) {
                        echo "<div class='mt-1 mb-1'>";
                        eib2bpro_ui('onoff', 'eib2bpro_groups[]', $group_id, ['csv' => get_post_meta($id, 'eib2bpro_groups', true), 'class' => 'switch-sm']);
                        echo "<span class='eib2bpro-font-14 pl-2'>" . $group . '</span></div>';
                    }
                    $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                    foreach ($groups as $group) {
                        echo "<div class='mt-1 mb-1'>";
                        eib2bpro_ui('onoff', 'eib2bpro_groups[]', $group->ID, ['csv' => get_post_meta($id, 'eib2bpro_groups', true), 'class' => 'switch-sm']);
                        echo "<span class='eib2bpro-font-14 pl-2'>" . esc_html(get_the_title($group->ID)) . '</span></div>';
                    } ?>

                </div>
            </div>
        </div>

        <div class="row d-none">
            <div class="eib2bpro-app-new-item-group col-12">
                <?php esc_html_e('Field options', 'eib2bpro'); ?>
            </div>
        </div>

        <div class="row text-right pt-4">
            <div class="col-6 text-left pl-5 pt-1">
                <?php if (0 < eib2bpro_get('id', 0)) { ?>
                    <a href="javascript:;" data-id="<?php eib2bpro_a($id) ?>" data-action="eib2bpro" data-app="b2b" data-asnonce="<?php eib2bpro_a(wp_create_nonce('eib2bpro-security')) ?>" data-do="delete-quote-field" class="eib2bpro-confitm eib2bpro-app-ajax text-danger font-weight-bold" data-confirm="<?php esc_html_e('Are you sure?', 'eib2bpro') ?>">
                        <i class="text-danger fas fa-trash-alt"></i> &nbsp; <?php esc_html_e('Delete', 'eib2bpro'); ?>
                    </a>
                <?php } ?>
            </div>
            <div class="col-6 text-right pr-5">
                <?php eib2bpro_save() ?>
            </div>
        </div>
    </div>
</div>

</form>