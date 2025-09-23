<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Fields', 'eib2bpro') ?> <?php eib2bpro_ui('wpml_selector'); ?> <?php if (0 < eib2bpro_get('id', 0)) { ?><span class="eib2bpro-font-14 font-weight-normal text-muted">#<?php echo eib2bpro_e(eib2bpro_get('id', 0)); ?></span><?php } ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(['do' => 'edit-registration-field', 'id' => eib2bpro_get('id', 0)]); ?>

<div class="eib2bpro-app-new-item-content eib2bpro-app-b2b-registration-fields-edit-form">
    <div class="container-fluid">

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Name', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'title', eib2bpro_clean2(get_the_title($id), ''), ['class' => 'eib2bpro-autofocus', 'attr' => 'required']) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Type', 'eib2bpro'); ?></label>
                <?php
                $field_type =  get_post_meta($id, 'eib2bpro_field_type', true);
                $params['attr'] = '';
                $params['class'] = '';
                $params['options'] = [
                    'text' => esc_html__('Textbox', 'eib2bpro'),
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
            <div class="eib2bpro-app-new-item-row col-12 eib2bpro-b2b-field-options<?php eib2bpro_a(('checkbox' !== get_post_meta($id, 'eib2bpro_field_type', true) && 'select' !== get_post_meta($id, 'eib2bpro_field_type', true)) ? ' hidden' : '') ?>">
                <label><?php esc_html_e('Options', 'eib2bpro'); ?></label>
                <?php eib2bpro_ui('input', 'eib2bpro_field_options', get_post_meta($id, 'eib2bpro_field_options', true), ['class' => '', 'attr' => 'placeholder="' . esc_attr__('Enter options sepereted by comma', 'eib2bpro') . '"']) ?>
            </div>
        </div>

        <div class="row">
            <div class="eib2bpro-app-new-item-row col-12">
                <label><?php esc_html_e('Enable this field for', 'eib2bpro'); ?></label>
                <?php

                $show_registration = intval(eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_registration_show', true), 0));

                echo "<div class='mt-1 mb-2 w-100'>";
                eib2bpro_ui('onoff', 'eib2bpro_field_registration_show', $show_registration, ['class' => 'switch-sm']);
                echo "<span class='eib2bpro-font-14 pl-2'>" . esc_html__('Registration form', 'eib2bpro') . '</span></div>';

                $billing_type = get_post_meta($id, 'eib2bpro_field_billing_type', true);
                $enable_billing = ('none' !== $billing_type && false !== $billing_type) ? 1 : 0;
                echo "<div class='mt-1 mb-2 w-100 eib2bpro-enable-billing-container" . esc_attr(in_array($field_type, ['text_vat-1', 'text_country-1', 'text_country_state-1']) ? ' hidden' : '') . "'>";
                eib2bpro_ui('onoff', 'eib2bpro_field_enable_billing', $enable_billing, ['class' => 'switch-sm']);
                echo "<span class='eib2bpro-font-14 pl-2'>" . esc_html__('Billing form', 'eib2bpro') . '</span></div>';

                ?>
                <input type="hidden" name="eib2bpro_field_billing_type2" class="eib2bpro-input-eib2bpro_field_billing_type" value="<?php eib2bpro_a($billing_type) ?>">

            </div>
        </div>
        <div class="eib2bpro-b2b-fields-registration-wrapper<?php eib2bpro_a(0 === $show_registration ? ' eib2bpro-hidden' : '') ?>">
            <div class="row">
                <div class="eib2bpro-app-new-item-group col-12">
                    <?php esc_html_e('Registration Form', 'eib2bpro'); ?>
                </div>
            </div>

            <div class="row eib2bpro-registration-all">

                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Which registration types can see', 'eib2bpro'); ?></label>

                    <input type="radio" value="0" name="eib2bpro_regtype_selector" id="rb1" class="eib2bpro_radio_selector eib2bpro-radio-2" data-hide='.eib2bpro-b2b-selected-regtypes' <?php checked(0, intval(get_post_meta($id, 'eib2bpro_regtype_selector', true))) ?> />
                    <label for="rb1" class="eib2bpro-radio-2"><?php esc_html_e('All', 'eib2bpro'); ?></label>
                    <input type="radio" value="1" name="eib2bpro_regtype_selector" id="rb2" class="eib2bpro_radio_selector eib2bpro-radio-2" data-show='.eib2bpro-b2b-selected-regtypes' <?php checked(1, get_post_meta($id, 'eib2bpro_regtype_selector', true)) ?> />
                    <label for="rb2" class="eib2bpro-radio-2"><?php esc_html_e('Selected', 'eib2bpro'); ?></label>

                    <div class="eib2bpro-b2b-selected-regtypes<?php eib2bpro_a(0 === intval(get_post_meta($id, 'eib2bpro_regtype_selector', true)) ? ' hidden' : '') ?>">
                        <?php
                        $regtypes = \EIB2BPRO\B2b\Admin\Registration::get_regtypes();
                        foreach ($regtypes as $regtype) {
                            echo "<div class='mt-1 mb-2'>";
                            eib2bpro_ui('onoff', 'eib2bpro_registration_regtypes[]', $regtype->ID, ['csv' => get_post_meta($id, 'eib2bpro_registration_regtypes', true), 'class' => 'switch-sm']);
                            echo "<span class='eib2bpro-font-14 pl-2'>" . esc_html(get_the_title($regtype->ID)) . '</span></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="row eib2bpro-registration-all">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label class="pb-4"><?php esc_html_e('Required', 'eib2bpro'); ?></label>
                    <?php eib2bpro_ui('onoff', 'eib2bpro_field_registration_required', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_registration_required', true), 1)) ?>
                </div>
            </div>
        </div>

        <div class="eib2bpro-b2b-fields-billing-wrapper<?php eib2bpro_a(0 === $enable_billing ? ' eib2bpro-hidden' : '') ?>">

            <div class="row">
                <div class="eib2bpro-app-new-item-group col-12">
                    <?php esc_html_e('Billing Form', 'eib2bpro'); ?>
                </div>
            </div>

            <?php $show_billing = intval(eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_billing_show', true), 1)); ?>

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Where will the data be saved?', 'eib2bpro'); ?></label>
                    <?php
                    $billing_type = get_post_meta($id, 'eib2bpro_field_billing_type', true);
                    $params['attr'] = '';
                    $params['class'] = '';
                    $params['options'] = [
                        'none' => esc_html__('None', 'eib2bpro'),
                        'new' => esc_html__('As a new field', 'eib2bpro'),
                        'billing_vat' => esc_html__('VAT Number', 'eib2bpro'),
                        'billing_first_name' => esc_html__('First Name', 'eib2bpro'),
                        'billing_last_name' => esc_html__('Last Name', 'eib2bpro'),
                        'billing_company' => esc_html__('Company', 'eib2bpro'),
                        'billing_country' => esc_html__('Country', 'eib2bpro'),
                        'billing_country_state' => esc_html__('Country/State', 'eib2bpro'),
                        'billing_state' => esc_html__('State', 'eib2bpro'),
                        'billing_address_1' => esc_html__('Address ', 'eib2bpro'),
                        'billing_address_2' => esc_html__('Address 2', 'eib2bpro'),
                        'billing_city' => esc_html__('City', 'eib2bpro'),
                        'billing_postcode' => esc_html__('Post Code', 'eib2bpro'),
                        'billing_phone' => esc_html__('Phone', 'eib2bpro'),
                    ];
                    ?>
                    <?php eib2bpro_ui('select', 'eib2bpro_field_billing_type', $billing_type, $params) ?>
                </div>
            </div>


            <div class="row eib2bpro-billing-show <?php eib2bpro_a(('new' !== $billing_type && 'billing_vat' !== $billing_type) ? ' eib2bpro-hidden' : '') ?>">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label class="pb-4"><?php esc_html_e('Show in form', 'eib2bpro'); ?></label>
                    <?php eib2bpro_ui('onoff', 'eib2bpro_field_billing_show', $show_billing, 0) ?>
                </div>
            </div>

            <div class="row eib2bpro-billing-groups eib2bpro-billing-vat eib2bpro-billing-all<?php eib2bpro_a((0 === $show_billing || ('new' !== $billing_type && 'billing_vat' !== $billing_type)) ? ' eib2bpro-hidden' : '') ?>">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Which groups can see?', 'eib2bpro'); ?></label>

                    <div class="eib2bpro-b2b-selected-groups">
                        <?php
                        $groups = [
                            (object)['ID' => 'guest', 'title' => esc_html__('Guests', 'eib2bpro')],
                            (object)['ID' => 'b2c', 'title' => esc_html__('B2C Users', 'eib2bpro')],
                        ];
                        $groups = array_merge($groups, \EIB2BPRO\B2b\Admin\Groups::get());
                        foreach ($groups as $group) {
                            echo "<div class='mt-1 mb-2'>";
                            eib2bpro_ui('onoff', 'eib2bpro_billing_groups[]', $group->ID, ['csv' => get_post_meta($id, 'eib2bpro_billing_groups', true), 'class' => 'switch-sm']);
                            echo "<span class='eib2bpro-font-14 pl-2'>" . esc_html(isset($group->title) ? $group->title : get_the_title($group->ID)) . '</span></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="row eib2bpro-billing-vat eib2bpro-billing-all<?php eib2bpro_a((0 === $show_billing || 'billing_vat' !== $billing_type) ? ' eib2bpro-hidden' : '') ?>">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('For which countries will it be visible?', 'eib2bpro'); ?></label>
                    <select class="from-control eib2bpro-input-select eib2bpro-country-list w-100 eib2bpro_field_billing_country" name="eib2bpro_field_billing_country[]" multiple>
                        <?php
                        $selected = (array)get_post_meta($id, 'eib2bpro_field_billing_country', true);
                        $selected_all = intval(get_post_meta($id, 'eib2bpro_field_billing_country_all', 1));
                        ?>
                        <optgroup label="<?php esc_html_e('All', 'eib2bpro') ?>">
                            <option value="0" <?php eib2bpro_a($selected_all === 1 ? ' selected' : '') ?>><?php esc_html_e('All countries', 'eib2bpro') ?></option>
                        </optgroup>
                        <?php
                        $WC_Countries = new \WC_Countries;
                        $continents = $WC_Countries->get_continents();
                        $countries = $WC_Countries->get_countries();
                        foreach ($continents as $continent) {
                            echo '<optgroup label="' . esc_html($continent['name']) . '">';
                            foreach ($continent['countries'] as $country_code_index => $country_code) {
                                echo '<option value="' . esc_attr($country_code) . '"' . ((in_array($country_code, $selected) && $selected_all === 0) ? ' selected' : '') . '>' . esc_html($countries[$country_code]) . '</option>';
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                </div>
            </div>


            <div class="eib2bpro-billing-form eib2bpro-billing-editable eib2bpro-billing-vat eib2bpro-billing-all<?php eib2bpro_a((0 === $show_billing || ('new' !== $billing_type && 'billing_vat' !== $billing_type))  ? ' eib2bpro-hidden' : '') ?>">
                <div class="row">
                    <div class="eib2bpro-app-new-item-row col-6">
                        <label class="pb-4"><?php esc_html_e('Required', 'eib2bpro'); ?></label>
                        <?php eib2bpro_ui('onoff', 'eib2bpro_field_billing_required', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_billing_required', true), 1)) ?>
                    </div>
                    <div class="eib2bpro-app-new-item-row col-6">
                        <label class="pb-4"><?php esc_html_e('Editable', 'eib2bpro'); ?></label>
                        <?php eib2bpro_ui('onoff', 'eib2bpro_field_billing_editable', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_billing_editable', true), 1)) ?>
                    </div>
                </div>
            </div>

            <div class="eib2bpro-billing-form eib2bpro-billing-vat eib2bpro-billing-all<?php eib2bpro_a((0 === $show_billing || ('new' !== $billing_type && 'billing_vat' !== $billing_type))  ? ' eib2bpro-hidden' : '') ?>">
                <div class="row">
                    <div class="eib2bpro-app-new-item-row col-12">
                        <label class="pb-4"><?php esc_html_e('Show in invoices', 'eib2bpro'); ?></label>
                        <?php eib2bpro_ui('onoff', 'eib2bpro_field_billing_show_invoice', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_billing_show_invoice', true), 1)) ?>
                    </div>
                </div>
            </div>

            <div class="row eib2bpro-billing-vat eib2bpro-billing-all<?php eib2bpro_a((0 === $show_billing || 'billing_vat' !== $billing_type) ? ' eib2bpro-hidden' : '') ?>">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label class="pb-4"><?php esc_html_e('VIES Validation', 'eib2bpro'); ?></label>
                    <?php eib2bpro_ui('onoff', 'eib2bpro_field_billing_vies', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_field_billing_vies', true), 1)) ?>
                </div>
            </div>
        </div>
        <div class="row text-right pt-4">
            <div class="col-12 text-right pr-5">
                <?php eib2bpro_save() ?>
            </div>
        </div>
    </div>
</div>

</form>