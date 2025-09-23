<?php

/**
 * User related functions 
 * 
 * @author ENERGY <support@en.er.gy>
 */

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class User
{
    /**
     * Search users via ajax
     *
     * @return void
     */

    public static function search()
    {
        $str = eib2bpro_post('query');

        //search from the user table
        $users_query_table = new \WP_User_Query(
            array(
                'search' => "*{$str}*",
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email',
                ),
            )
        );
        $users_via_table = $users_query_table->get_results();

        //search from the usermeta
        $users_query_meta = new \WP_User_Query(
            array(
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'first_name',
                        'value' => $str,
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'last_name',
                        'value' => $str,
                        'compare' => 'LIKE'
                    )
                )
            )
        );

        $users_via_meta = $users_query_meta->get_results();

        // Merge
        $combined_users = array_merge($users_via_table, $users_via_meta);

        // Get unique user
        $users = array_unique($combined_users, SORT_REGULAR);

        $json = [];

        if (0 < count($users)) {
            foreach ($users as $user) {
                $group = get_user_meta($user->ID, 'eib2bpro_user_type', true);
                if ('b2b' === $group) {
                    $group = get_user_meta($user->ID, 'eib2bpro_group', true);
                    $json[] = ['id' => $user->ID, 'name' => sprintf('%s %s (%s)', $user->first_name, $user->last_name, $user->user_login), 'group' => eib2bpro_clean2(get_the_title(intval($group)), '')];
                } else {
                    $json[] = ['id' => $user->ID, 'name' => sprintf('%s %s (%s)', $user->first_name, $user->last_name, $user->user_login), 'group' => esc_html__('B2C', 'eib2bpro')];
                }
            }
        }

        echo eib2bpro_r(json_encode($json));
        die;
    }

    /**
     * Approve user
     *
     * @return void
     */

    public static function approve_user()
    {
        $user_id = intval(eib2bpro_post('id', 0));
        $move = intval(eib2bpro_post('move', 0));
        $do = 'approve' === eib2bpro_post('status') ? 'approve' : 'reject';
        $status = get_user_meta($user_id, 'eib2bpro_user_approved', true);

            if (!current_user_can('edit_user', $user_id)) {
                return false;
            }

        if ('approve' === $do && 'no' === $status) {
            if (0 === $move) { // b2c
                update_user_meta($user_id, 'eib2bpro_user_type', 'b2c');
                update_user_meta($user_id, 'eib2bpro_group', $move);
            } else {
                update_user_meta($user_id, 'eib2bpro_user_type', 'b2b');
                update_user_meta($user_id, 'eib2bpro_group', $move);
            }
            update_user_meta($user_id, 'eib2bpro_user_approved', 'yes');

            $mailer = WC()->mailer();
            $user_data = get_userdata($user_id);
            do_action('eib2bpro_account_approved', $user_id, $user_data->user_email);
        } elseif ('reject' === $do && 'no' === $status) {
            if (!current_user_can('delete_users', $user_id)) {
                eib2bpro_error(esc_html__('You do not have succifent permission to delete the user', 'eib2bpro'));
                return false;
            }
            wp_delete_user($user_id);
        }

        Main::clear_cache(['users']);

        eib2bpro_success('', ['after' => [
            'addClass' => ['container' => '.eib2bpro-list-user-id-' . $user_id, 'class' => 'animated fadeOut d-none'],
            'html' => ['container' => '.eib2bpro-html-user-id-' . $user_id, 'html' => '<ul><li class="text-right text-uppercase text' . ('reject' === $do ? '-danger">' . esc_html__('Declined', 'eib2bpro') : '-success">' . esc_html__('Approved', 'eib2bpro')) . '</li></ul>']
        ]]);
    }

    public static function customer_meta_fields($fields)
    {
        $user_id = intval(eib2bpro_get('user_id', 0));
        if (0 < $user_id) {

            $user_group = intval(get_user_meta($user_id, 'eib2bpro_group', true));
            if (0 === $user_group) {
                $group = 'b2c';
            } else {
                $group = $user_group;
            }
        } else {
            $group = 0;
        }

        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        if ($custom_fields) {
            foreach ($custom_fields as $custom_field) {

                $groups = get_post_meta($custom_field->ID, 'eib2bpro_billing_groups', true);

                if (0 < intval(eib2bpro_get('user_id', 0)) && is_array($groups) && !in_array($group, $groups)) {
                    continue;
                }

                $type = get_post_meta($custom_field->ID, 'eib2bpro_field_billing_type', true);
                if ('new' === $type || 'billing_vat' === $type) {
                    $fields['billing']['fields']['eib2bpro_customfield_' . esc_attr($custom_field->ID)] = array(
                        'label'         => get_the_title($custom_field->ID),
                        'description' => ''
                    );
                }
            }
        }

        return $fields;
    }
    /**
     * Update user meta fields with metabox
     *
     * @param int $user_id
     * @return void
     */

    public static function edit_user_profile_update($user_id)
    {
            if (!current_user_can('edit_user', $user_id)) {
                return false;
            }

        if ('no' === get_user_meta($user_id, 'eib2bpro_user_approved', true)) {
            return;
        }


        //update group
        $group = intval(eib2bpro_post('eib2bpro_group'));

        if (0 === $group) { // b2c
            update_user_meta($user_id, 'eib2bpro_user_type', 'b2c');
            update_user_meta($user_id, 'eib2bpro_group', $group);
        } else {
            update_user_meta($user_id, 'eib2bpro_user_type', 'b2b');
            update_user_meta($user_id, 'eib2bpro_group', $group);
            update_user_meta($user_id, 'eib2bpro_user_approved', 'yes');
        }

        // user info
        $fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
        ]);

        $regtype = get_user_meta($user_id, 'eib2bpro_registration_regtype', true);

        $field_ids = wp_parse_id_list(get_user_meta($user_id, 'eib2bpro_customfield_ids', true));

        foreach ($field_ids as $field_id) {

            $field = get_post($field_id);

            if (!$field) {
                continue;
            }

            $regtypes = get_post_meta($field->ID, 'eib2bpro_registration_regtypes', true);

            if (is_array($regtypes) && !in_array($regtype, $regtypes)) {
                continue;
            }

            $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);

            if ('checkbox' === $type) {
                if (0 < count($_POST['eib2bpro_customfield_' . esc_attr($field->ID)])) {
                    $options = array_map('trim', explode(',', get_post_meta(apply_filters('wpml_object_id', $field->ID, 'post', true), 'eib2bpro_field_options', true)));
                    $selected = [];
                    foreach ($_POST['eib2bpro_customfield_' . esc_attr($field->ID)] as $post) {
                        if (in_array(trim($post), $options)) {
                            $selected[] = trim(wp_kses_data(sanitize_text_field($post)));
                        }
                    }
                    update_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, implode(',', $selected));
                } else {
                    update_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, '');
                }
            } else {
                if ($value = eib2bpro_post('eib2bpro_customfield_' . $field->ID)) {
                    update_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, trim(sanitize_text_field($value)));
                }

                if ($value = eib2bpro_post('eib2bpro_customfield_' . $field->ID . '_state')) {
                    update_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID . '_state', trim(sanitize_text_field($value)));
                }
            }

            $field_ids[] = $field->ID;
        }

        if (0 < count($field_ids)) {
            update_user_meta($user_id, 'eib2bpro_customfield_ids', implode(',', $field_ids));
        }

        // tax
        $vies = get_user_meta($user_id, '_eib2bpro_vies_validated', true);
        if ((!$vies || '' === $vies) && 0 === intval(eib2bpro_post('eib2bpro_vies_validated', 0))) {
            // future
        } else {
            update_user_meta($user_id, '_eib2bpro_vies_validated', intval(eib2bpro_post('eib2bpro_vies_validated', 0)));
        }

        update_user_meta($user_id, '_eib2bpro_tax_exemption', eib2bpro_post('eib2bpro_tax_exemption', 'default'));


        // payment & shipping
        update_user_meta($user_id, 'eib2bpro_payment_shipping', eib2bpro_post('eib2bpro_payment_shipping_selector'));

        // payment methods
        $payment_methods = \WC()->payment_gateways->payment_gateways();
        foreach ($payment_methods as $payment_method) {
            if (1 === intval(eib2bpro_post('payment_methods_' . $payment_method->id))) {
                update_user_meta($user_id, 'eib2bpro_payment_method_' . $payment_method->id, '1');
            } else {
                update_user_meta($user_id, 'eib2bpro_payment_method_' . $payment_method->id, '0');
            }
        }

        // shipping methods
        $shipping_methods = \EIB2BPRO\B2b\Site\Shipping::get_all();
        foreach ($shipping_methods as $shipping_method) {
            if (1 === intval(eib2bpro_post('shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id))) {
                update_user_meta($user_id, 'eib2bpro_shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id, '1');
            } else {
                update_user_meta($user_id, 'eib2bpro_shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id, '0');
            }
        }

        // clear cache data
        Main::clear_cache(['users']);
    }

    /**
     * User metabox
     *
     * @param object|int $user
     * @return void
     */
    public static function show_user_profile($user)
    {
        if (isset($user->ID)) {
            $user_id = $user->ID;
        } else {
            $user_id = 0;
        }

        $status = get_user_meta($user_id, 'eib2bpro_user_approved', true);
?>
        <h2><?php esc_html_e('B2B Pro', 'eib2bpro'); ?></h2>

        <div class="eib2bpro-postbox eib2bpro-shadow">
            <?php if ('no' !== $status) { ?>
                <div class="eib2bpro-postbox-header"><?php esc_html_e('User Group', 'eib2bpro'); ?></div>
                <div class="eib2bpro-postbox-content">
                    <select name="eib2bpro_group">
                        <option value="0" selected><?php esc_html_e('B2C', 'eib2bpro'); ?></option>
                        <?php
                        $groups = Groups::get();
                        $user_group = intval(get_user_meta($user_id, 'eib2bpro_group', true));
                        foreach ($groups as $group) {
                            $selected = ($user_group === $group->ID) ? ' selected' : '';
                            echo "<option value='" . esc_attr($group->ID) . "'" . $selected . ">" . esc_html(get_the_title($group->ID)) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="eib2bpro-postbox-header"><?php esc_html_e('Tax Status', 'eib2bpro'); ?></div>
                <div class="eib2bpro-postbox-content">

                    <div class="eib2bpro_customfield_label">
                        <?php esc_html_e('VAT Number', 'eib2bpro'); ?>
                    </div>
                    <div class="eib2bpro_customfield_value">
                        <?php eib2bpro_e(eib2bpro_clean2(get_user_meta($user_id, 'billing_vat', true), '---')) ?>
                    </div>

                    <div class="eib2bpro_customfield_label">
                        <?php esc_html_e('Tax Exemption', 'eib2bpro'); ?>
                    </div>
                    <div class="eib2bpro_customfield_value">
                        <input type="radio" value="default" name="eib2bpro_tax_exemption" id="rb2" class="form-control" <?php checked('default', eib2bpro_clean2(get_user_meta($user_id, '_eib2bpro_tax_exemption', true), 'default')) ?> />
                        <label for="rb2" class="pl-1 pr-4"><?php esc_html_e('Default', 'eib2bpro'); ?></label>
                        <input type="radio" value="yes" name="eib2bpro_tax_exemption" id="rb1" class="form-control" <?php checked('yes', get_user_meta($user_id, '_eib2bpro_tax_exemption', true)) ?> />
                        <label for="rb1" class="pl-1 pr-4"><?php esc_html_e('Yes', 'eib2bpro'); ?></label>
                        <input type="radio" value="no" name="eib2bpro_tax_exemption" id="rb3" class="form-control" <?php checked('no', get_user_meta($user_id, '_eib2bpro_tax_exemption', true)) ?> />
                        <label for="rb3" class="pl-1 pr-4"><?php esc_html_e('No', 'eib2bpro'); ?></label>
                    </div>

                    <div class=" eib2bpro_customfield_label">
                        <?php esc_html_e('VIES Validated', 'eib2bpro'); ?>
                    </div>
                    <div class="eib2bpro_customfield_value">
                        <?php eib2bpro_ui('onoff', 'eib2bpro_vies_validated', eib2bpro_clean2(get_user_meta($user_id, '_eib2bpro_vies_validated', true), 0), ['class' => 'switch-sm']) ?>
                    </div>

                </div>

                <div class="eib2bpro-postbox-header"><?php esc_html_e('Payment and Shipping Methods', 'eib2bpro'); ?></div>
                <div class="eib2bpro-postbox-content">
                    <div class="eib2bpro_customfield_value">
                        <input type="radio" value="default" name="eib2bpro_payment_shipping_selector" id="rb21" class="form-control eib2bpro_payment_shipping_selector" <?php checked('default', eib2bpro_clean2(get_user_meta($user_id, 'eib2bpro_payment_shipping', true), 'default')) ?> />
                        <label for="rb21" class="pl-1 pr-4"><?php esc_html_e('Default', 'eib2bpro'); ?></label>
                        <input type="radio" value="custom" name="eib2bpro_payment_shipping_selector" id="rb11" class="form-control eib2bpro_payment_shipping_selector" <?php checked('custom', get_user_meta($user_id, 'eib2bpro_payment_shipping', true)) ?> />
                        <label for="rb11" class="pl-1 pr-4"><?php esc_html_e('Custom', 'eib2bpro'); ?></label>
                    </div>
                    <div id="eib2bpro_payment_shipping_container" class="<?php eib2bpro_a('custom' !== get_user_meta($user_id, 'eib2bpro_payment_shipping', true) ? 'hidden' : '') ?>">
                        <div class="row">
                            <div class="eib2bpro-app-new-item-row col-12 mt-3 pl-3">
                                <label class="pb-3"><?php esc_html_e('Payment Methods', 'eib2bpro'); ?></label>
                                <?php
                                $payment_methods = WC()->payment_gateways->payment_gateways();
                                foreach ($payment_methods as $payment_method) {
                                    if ('yes' === $payment_method->enabled) {
                                ?>
                                        <div class="mt-1 mb-2">
                                            <?php eib2bpro_ui('onoff', 'payment_methods_' . esc_attr($payment_method->id), eib2bpro_clean2(get_user_meta($user_id, 'eib2bpro_payment_method_' . $payment_method->id, true), 1), ['class' => 'switch-sm mr-2']) ?>
                                            <?php echo esc_html($payment_method->title); ?>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="eib2bpro-app-new-item-row col-12 mt-3 pl-3">
                                <label class="pb-3"><?php esc_html_e('Shipping Methods', 'eib2bpro'); ?></label>
                                <?php
                                $shipping_methods = \EIB2BPRO\B2b\Site\Shipping::get_all();
                                foreach ($shipping_methods as $shipping_method) {
                                    if ($shipping_method->enabled === 'yes') {
                                ?>
                                        <div class="mt-1 mb-2">
                                            <?php eib2bpro_ui('onoff', 'shipping_methods_' . esc_attr($shipping_method->id) . '_' . esc_attr($shipping_method->instance_id), eib2bpro_clean2(get_user_meta($user_id, 'eib2bpro_shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id, true), 1), ['class' => 'switch-sm mr-2']) ?>
                                            <?php echo "<strong>" . esc_html($shipping_method->eib2bpro_zone_name) . ": </strong>" .  esc_html($shipping_method->title); ?>
                                        </div>
                                <?php }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if (get_user_meta($user_id, 'eib2bpro_customfield_ids', true)) { ?>
                <div class="eib2bpro-postbox-header"><?php esc_html_e('Registration', 'eib2bpro'); ?></div>
                <div class="eib2bpro-postbox-content">
                    <div class="eib2bpro_customfield_label"><?php esc_html_e('Type', 'eib2bpro'); ?></div>
                    <div class="eib2bpro_customfield_value"><input type="text" value="<?php eib2bpro_a(eib2bpro_clean2(get_the_title(get_user_meta($user_id, 'eib2bpro_registration_regtype', true)), esc_html__('B2C', 'eib2bpro'))) ?>" disabled>
                    </div>
                    <?php
                    $readonly = 'no' === $status ? ' disabled' : '';
                    $field_ids = wp_parse_id_list(get_user_meta($user_id, 'eib2bpro_customfield_ids', true));

                    foreach ($field_ids as $field_id) {
                        $field = get_post($field_id);
                        if ($field) {
                            $type = get_post_meta($field->ID, 'eib2bpro_field_type', true); ?>
                            <div class="eib2bpro_customfield_label"><?php eib2bpro_e(get_post_meta($field->ID, 'eib2bpro_field_label', true)) ?></div>

                    <?php $value = get_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, true);
                            switch ($type) {
                                case 'checkbox':
                                    $values = (array)explode(',', $value);
                                    $options = array_map('trim', explode(',', get_post_meta($field->ID, 'eib2bpro_field_options', true)));
                                    foreach ($options as $option) {
                                        $selected = '';
                                        if (in_array($option, $values)) {
                                            $selected = ' checked';
                                        }
                                        if (!empty(trim($option))) {
                                            echo '<div class="eib2bpro_customfield_value"><input name="eib2bpro_customfield_' . esc_attr($field->ID) . '[]" type="checkbox" value="' . esc_attr($option) . '" ' . $selected . $readonly . '>' . esc_html($option) . '</div>';
                                        }
                                    }
                                    break;
                                case 'select':
                                    $options = (array)explode(',', get_post_meta($field->ID, 'eib2bpro_field_options', true));
                                    echo '<div class="eib2bpro_customfield_value"><select name="eib2bpro_customfield_' . esc_attr($field->ID) . '" type="select" ' . $readonly . '>';
                                    foreach ($options as $option) {
                                        $selected = '';
                                        if (trim($option) === trim($value)) {
                                            $selected = ' selected';
                                        }
                                        if (!empty(trim($option))) {
                                            echo '<option value="' . esc_attr(trim($option)) . '" ' . $selected . $readonly . '>' . esc_html(trim($option)) . '</option>';
                                        }
                                    }
                                    echo "</select></div>";
                                    break;
                                case 'file':
                                    $file = wp_get_attachment_url($value);
                                    echo '<a href="' . esc_url($file) . '" target="_blank">' . esc_html__('View file', 'eib2bpro') . '</a>';
                                    break;
                                default:
                                    $billing_type = get_post_meta($field->ID, 'eib2bpro_field_billing_type', true);
                                    if ($billing_type === 'billing_country_state') {
                                        woocommerce_form_field(
                                            'eib2bpro_customfield_' . esc_attr($field->ID),
                                            [
                                                'type' => 'country',
                                                'class' => []
                                            ],
                                            get_user_meta($user_id, 'eib2bpro_customfield_' . esc_attr($field->ID), true)
                                        );
                                        woocommerce_form_field(
                                            'eib2bpro_customfield_' . esc_attr($field->ID) . '_state',
                                            [
                                                'type' => 'state',
                                                'country' => get_user_meta($user_id, 'eib2bpro_customfield_' . esc_attr($field->ID), true),
                                                'class' => []
                                            ],
                                            get_user_meta($user_id, 'eib2bpro_customfield_' . esc_attr($field->ID) . '_state', true)
                                        );
                                    } else {
                                        if ('new' === $billing_type || 'billing_vat' === $billing_type) {
                                            $readonly = ' disabled';
                                        } else {
                                            $readonly = 'no' === $status ? ' disabled' : '';
                                        }
                                        echo '<div class="eib2bpro_customfield_value"><input name="eib2bpro_customfield_' . esc_attr($field->ID) . '" value="' . esc_attr($value) . '"' . $readonly . '></div>';
                                    }
                                    break;
                            }
                        }
                    }
                    ?>
                    <?php
                    if ('no' === $status) { ?>
                        <div class="eib2bpro_customfield_label">
                            <?php esc_html_e('Assign user to this group', 'eib2bpro'); ?>
                        </div>
                        <div class="eib2bpro_customfield_value">
                            <select name="eib2bpro_group">
                                <option value="0" selected><?php esc_html_e('B2C', 'eib2bpro'); ?></option>
                                <?php
                                $groups = Groups::get();
                                $user_group = intval(get_user_meta($user_id, 'eib2bpro_user_move_to', true));

                                foreach ($groups as $group) {
                                    $selected = ($user_group === $group->ID) ? ' selected' : '';
                                    echo "<option value='" . esc_attr($group->ID) . "'" . $selected . ">" . esc_html(get_the_title($group->ID)) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mt-3">
                            <input type="hidden" id="eib2bpro_user_profile_nonce" value="<?php eib2bpro_a(wp_create_nonce('eib2bpro-security')) ?>">
                            <button class="eib2bpro_user_approve_button" data-user="<?php eib2bpro_a($user_id) ?>" data-status="approve" data-move="<?php eib2bpro_a($user_group) ?>"><?php esc_html_e('Approve', 'eib2bpro'); ?></button>
                            <button class="eib2bpro_user_approve_button eib2bpro_user_reject_button" data-user="<?php eib2bpro_a($user_id) ?>" data-status="reject" data-move="-1"><?php esc_html_e('Decline', 'eib2bpro'); ?></button>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
<?php
    }
}
