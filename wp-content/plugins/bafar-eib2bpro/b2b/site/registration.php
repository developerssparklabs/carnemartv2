<?php

namespace EIB2BPRO\B2b\Site;

use EIB2BPRO\Core\Notifications;

defined('ABSPATH') || exit;

class Registration
{
    public static $registration_regtype_id = 0;
    public static $user_country = false;
    public static $vies_validated = false;

    public static function woocommerce_created_customer($user)
    {
        if ('yes' === get_user_meta($user, 'eib2bpro_registration_completed', true)) {
            return;
        } else {
            update_user_meta($user, 'eib2bpro_registration_completed', 'yes');
        }

        $regtype = -1;

        $all_regtypes = get_posts([
            'post_type' => 'eib2bpro_regtype',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        if (0 === eib2bpro_option('b2b_settings_registration_enable_regtype_selector', 1)) {
            if (0 < intval(eib2bpro_post('eib2bpro_registration_regtype_selector')) && get_post_status(intval(eib2bpro_post('eib2bpro_registration_regtype_selector')))) {
                $regtype = intval(eib2bpro_post('eib2bpro_registration_regtype_selector'));
            } else {
                $regtype = 0;
            }
        } else if (0 === count($all_regtypes)) {
            $regtype = 0;
        } elseif (1 === count($all_regtypes)) {
            $regtype = $all_regtypes[0]->ID;
        } else {
            if (eib2bpro_post('eib2bpro_registration_regtype_selector') && get_post_status(intval(eib2bpro_post('eib2bpro_registration_regtype_selector')))) {
                $regtype = intval(eib2bpro_post('eib2bpro_registration_regtype_selector'));
            }
        }

        update_user_meta($user, 'eib2bpro_registration_regtype', $regtype);

        $fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        $field_ids = [];

        foreach ($fields as $field) {
            $regtypes = get_post_meta($field->ID, 'eib2bpro_registration_regtypes', true);

            if (is_array($regtypes) && !in_array($regtype, $regtypes)) {
                continue;
            }

            $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
            $title = get_the_title($field->ID);

            $billing_type = get_post_meta($field->ID, 'eib2bpro_field_billing_type', true);

            if ('checkbox' === $type) {
                if (0 < count($_POST['eib2bpro_customfield_' . $field->ID])) {
                    $options = explode(',', get_post_meta(apply_filters('wpml_object_id', $field->ID, 'post', true), 'eib2bpro_field_options', true));
                    $selected = [];
                    foreach ($_POST['eib2bpro_customfield_' . $field->ID] as $post) {
                        if (in_array(trim($post), $options)) {
                            $selected[] = trim(wp_kses_data(sanitize_text_field($post)));
                        }
                    }

                    update_user_meta($user, 'eib2bpro_customfield_' . $field->ID, implode(',', $selected));
                    update_user_meta($user, 'eib2bpro_registration_title_' . $field->ID, $title);

                    if ('new' !== $billing_type && 'none' !== $billing_type && 'custom' !== $billing_type) {
                        update_user_meta($user, $billing_type, implode(',', $selected));
                    } else if ('custom' === $billing_type) {
                        $custom_meta_key = get_post_meta($field->ID, 'eib2bpro_field_billing_custom', true);
                        if ($custom_meta_key) {
                            update_user_meta($user, sanitize_key($custom_meta_key), implode(',', $selected));
                        }
                    }

                    $field_ids[] = $field->ID;
                }
            } elseif ('file' === $type) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $attachment_id = media_handle_upload('eib2bpro_customfield_' . $field->ID, 0);

                $attachment_post = array(
                    'ID' => $attachment_id,
                    'post_author' => $user
                );
                wp_update_post($attachment_post);

                update_user_meta($user, 'eib2bpro_customfield_' . $field->ID, $attachment_id);
                update_user_meta($user, 'eib2bpro_registration_title_' . $field->ID, $title);

                if ('custom' === $billing_type) {
                    $custom_meta_key = get_post_meta($field->ID, 'eib2bpro_field_billing_custom', true);
                    if ($custom_meta_key) {
                        update_user_meta($user, sanitize_key($custom_meta_key), sanitize_text_field($value));
                    }
                }

                $field_ids[] = $field->ID;
            } else {
                if ($value = eib2bpro_post('eib2bpro_customfield_' . esc_attr($field->ID))) {

                    update_user_meta($user, 'eib2bpro_customfield_' . $field->ID, sanitize_text_field($value));
                    update_user_meta($user, 'eib2bpro_registration_title_' . $field->ID, $title);

                    if ('new' !== $billing_type && 'none' !== $billing_type && 'custom' !== $billing_type) {
                        update_user_meta($user, $billing_type, sanitize_text_field($value));
                        if ($billing_type === 'billing_first_name') {
                            update_user_meta($user, 'first_name', sanitize_text_field($value));
                        } else if ($billing_type === 'billing_last_name') {
                            update_user_meta($user, 'last_name', sanitize_text_field($value));
                        } else if ($billing_type === 'billing_country_state') {
                            update_user_meta($user, 'billing_country', sanitize_text_field($value));
                            update_user_meta($user, 'billing_state', sanitize_text_field(eib2bpro_post('billing_state', '')));
                            update_user_meta($user, 'eib2bpro_customfield_' . $field->ID . '_state', sanitize_text_field(eib2bpro_post('billing_state', '')));
                        }
                    } else if ('custom' === $billing_type) {
                        $custom_meta_key = get_post_meta($field->ID, 'eib2bpro_field_billing_custom', true);
                        if ($custom_meta_key) {
                            update_user_meta($user, sanitize_key($custom_meta_key), sanitize_text_field($value));
                        }
                    }
                    $field_ids[] = $field->ID;
                }
            }
        }

        if (0 < count($field_ids)) {
            update_user_meta($user, 'eib2bpro_customfield_ids', implode(',', $field_ids));
        }
        if (static::$vies_validated) {
            update_user_meta($user, '_eib2bpro_vies_validated', static::$vies_validated);
        }

        if (-1 === $regtype or 0 === $regtype) { // if regtype = 0 - b2c
            $auto_approval = eib2bpro_option('b2b_settings_registration_default_b2c', 'automatic');
            update_user_meta($user, 'eib2bpro_user_type', 'b2c');
            update_user_meta($user, 'eib2bpro_group', 'b2c');
            update_user_meta($user, 'eib2bpro_user_approved', 'automatic' === $auto_approval ? 'yes' : 'no');
        } else {
            $auto_approval = intval(get_post_meta($regtype, 'eib2bpro_automatic_approval', true));
            $group = intval(get_post_meta($regtype, 'eib2bpro_approval_group', true));

            if (1 === $auto_approval) { // auto approval
                if (0 === $group) { // this is a b2b user
                    update_user_meta($user, 'eib2bpro_user_type', 'b2c');
                } elseif (0 < $group) {
                    update_user_meta($user, 'eib2bpro_user_type', 'b2b');
                    update_user_meta($user, 'eib2bpro_group', $group);
                }
                update_user_meta($user, 'eib2bpro_user_approved', 'yes');
            } elseif (0 === $auto_approval) { // manual approval
                update_user_meta($user, 'eib2bpro_user_approved', 'no');
                update_user_meta($user, 'eib2bpro_user_move_to', $group);
            }
        }

        Notifications::new_user($user);

        \EIB2BPRO\B2B\Admin\Toolbox::clear_users_cache();
    }

    public static function woocommerce_registration_redirect($redirection)
    {
        $approved = get_user_meta(get_current_user_id(), 'eib2bpro_user_approved', true);

        if ('yes' !== $approved) {
            wp_logout();
            do_action('woocommerce_set_cart_cookies', true);
            wc_add_notice(esc_html__('We will review your registration and get back to you as soon as possible.', 'eib2bpro'), 'success');
        }

        $redirection = add_query_arg('redirection', 1, get_permalink(wc_get_page_id('myaccount')));

        return $redirection;
    }

    public static function woocommerce_process_login_errors($validation, $username, $password)
    {
        if (!empty($username)) {
            $user = get_user_by('login', $username);
            if (!$user) {
                $user = get_user_by('email', $username);
                if (!$user) {
                    return $validation;
                }
            }
        }

        if (isset($user->ID)) {
            if ('no' === get_user_meta($user->ID, 'eib2bpro_user_approved', true)) {
                $validation->add('not_approved', esc_html__('Your account is still pending approval, you cannot log in now.', 'eib2bpro'));
            }
        }

        return $validation;
    }

    public static function woocommerce_register_form($regtype_selector = 'show')
    {
        $all_regtypes = get_posts([
            'post_type' => 'eib2bpro_regtype',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        $fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        if ('hide' === $regtype_selector) {
            $all_regtypes = [];
        } elseif ('' === $regtype_selector) {
            if (0 === eib2bpro_option('b2b_settings_registration_enable_regtype_selector', 1)) {
                $all_regtypes = [];
            }
        }

        $regtype_selector_visibility = (0 === self::$registration_regtype_id) ? true : false;

        echo '<div class="eib2bpro_registration_form">';

        if (0 === count($all_regtypes)) {
            echo '<input name="eib2bpro_registration_regtype_selector" value="0" type="hidden">';
        } elseif (1 === count($all_regtypes)) {
            echo '<select id="eib2bpro_registration_regtype_selector" name="eib2bpro_registration_regtype_selector" class="eib2bpro-hidden-row"><option value="' . esc_attr($all_regtypes[0]->ID) . '">' . esc_html(get_the_title($all_regtypes[0]->ID)) . '</option></select>';
        } elseif (1 < count($all_regtypes)) { ?>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide eib2bpro_registration_regtype_row<?php eib2bpro_a($regtype_selector_visibility ? '' : ' eib2bpro-hidden-row') ?>">
                <label for="eib2bpro_registration_regtype_selector">
                    <?php esc_html_e('Type', 'eib2bpro'); ?> <span class="required">*</span>
                </label>
                <select id="eib2bpro_registration_regtype_selector" name="eib2bpro_registration_regtype_selector">
                    <?php
                    foreach ($all_regtypes as $regtype) {
                        echo '<option value="' . esc_attr($regtype->ID) . '" ' . selected($regtype->ID, eib2bpro_post('eib2bpro_registration_regtype_selector', self::$registration_regtype_id), false) . '>' . esc_html(get_the_title($regtype->ID)) . '</option>';
                    } ?>
                </select>
            </p>
        <?php
        }

        foreach ($all_regtypes as $regtype) {
            $message = get_post_meta($regtype->ID, 'eib2bpro_message', true);
            if (!empty($message)) {
                echo '<div class="eib2bpro_registration_container eib2bpro_regtype_message eib2bpro_registration_regtype_' . esc_attr($regtype->ID) . '">' . eib2bpro_r(wp_kses_post($message)) . '</div>';
            }
        }

        foreach ($fields as $field) {
            $class = 'eib2bpro_registration_container ';
            $value = '';

            $id = $field->ID;
            $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
            $label = get_post_meta(apply_filters('wpml_object_id', $field->ID, 'post', true), 'eib2bpro_field_label', true);
            $placeholder = trim(get_post_meta(apply_filters('wpml_object_id', $field->ID, 'post', true), 'eib2bpro_field_placeholder', true));
            $required = intval(get_post_meta($field->ID, 'eib2bpro_field_registration_required', true));
            $billing_field_type = get_post_meta($field->ID, 'eib2bpro_field_billing_type', true);

            $regtypes = get_post_meta($field->ID, 'eib2bpro_registration_regtypes', true);

            if (0 === intval(get_post_meta($field->ID, 'eib2bpro_field_registration_show', true))) {
                continue;
            }

            if (is_checkout() && 'none' !== $billing_field_type && 'new' !== $billing_field_type && 'custom' !== $billing_field_type) {
                continue;
            }

            if (is_checkout() && 'file' === $type) {
                continue;
            }

            if (is_array($regtypes)) {
                // only for specific regtypes
                foreach ($regtypes as $regtype) {
                    $class .= ' eib2bpro_registration_regtype_' . $regtype;
                }
            } else {
                // available for all regtypes
                $class .= ' eib2bpro_registration_regtype_0';
            }

            if (isset($_POST['eib2bpro_customfield_' . esc_attr($id)])) {
                $value = eib2bpro_post('eib2bpro_customfield_' . esc_attr($id));
            }

            echo '<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide ' . esc_attr($class) . '">';
            echo '<label for="' . esc_attr('eib2bpro_customfield_' . esc_attr($id)) . '">' . esc_html($label);
            if (1 === $required) {
                echo '<span class="required">*</span>';
            }
            echo '</label>';

            if ($billing_field_type === 'billing_vat') {
                $countries = implode(',', (array)get_post_meta($id, 'eib2bpro_field_billing_country', true));
                echo '<input type="text" id="eib2bpro_customfield_' . esc_attr($id) . '" class="eib2bpro_customfield_vat eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>';
            } elseif ($billing_field_type === 'billing_country') {
                woocommerce_form_field(
                    'eib2bpro_customfield_' . esc_attr($id),
                    [
                        'type' => 'country',
                        'class' => ['eib2bpro_customfield_countries_select', 'eib2bpro_customfield', 'eib2bpro_customfield_type_' . esc_attr($type),  'eib2bpro_customfield_required_' . esc_attr($required)]
                    ],
                    eib2bpro_post('eib2bpro_customfield_' . esc_attr($id), '')
                );
            } else if ($billing_field_type === 'billing_country_state') {
                woocommerce_form_field(
                    'eib2bpro_customfield_' . esc_attr($id),
                    [
                        'type' => 'country',
                        'class' => ['eib2bpro_customfield_countries_select', 'eib2bpro_customfield', 'eib2bpro_customfield_type_' . esc_attr($type),  'eib2bpro_customfield_required_' . esc_attr($required)]
                    ],
                    eib2bpro_post('eib2bpro_customfield_' . esc_attr($id), '')
                );
                woocommerce_form_field(
                    'billing_state',
                    [
                        'type' => 'state',
                        'country' => eib2bpro_post('eib2bpro_customfield_' . esc_attr($id), ''),
                        'class' => ['eib2bpro_customfield_states', 'eib2bpro_customfield', 'eib2bpro_customfield_type_' . esc_attr($type),  'eib2bpro_customfield_required_' . esc_attr($required)]
                    ],
                    eib2bpro_post('billing_state', '')
                );
            } else {
                switch ($type) {
                    case 'text':
                        echo '<input type="text"  class="eib2bpro_customfield_' . esc_attr($id) . ' eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>';
                        break;
                    case 'number':
                        echo '<input type="number" step="1"  class="eib2bpro_customfield_' . esc_attr($id) . ' eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>';
                        break;
                    case 'textarea':
                        echo '<textarea  class="eib2bpro_customfield_' . esc_attr($id) . ' eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>' . esc_attr($value) . '</textarea>';
                        break;
                    case 'date':
                        echo '<input type="date" class="eib2bpro_customfield_' . esc_attr($id) . ' eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>';
                        break;
                    case 'email':
                        echo '<input type="email" class="eib2bpro_customfield_' . esc_attr($id) . ' eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>';
                        break;
                    case 'file':
                        $allowed_types = apply_filters('eib2bpro_allowed_file_types', ["image/jpeg", "image/jpg", "image/png", "application/pdf"]);
                        $allowed_size = apply_filters('eib2bpro_allowed_file_size', 5 * 1024 * 1024);
                        echo '<input type="file" id="eib2bpro_customfield_' . esc_attr($id) . '" class="eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . (1 === $required ? 'required' : '') . '>';
                        echo '' . sprintf(esc_html__('(Allowed file types: jpg,png,pdf and max file size is %s)', 'eib2bpro'), ($allowed_size / 1024 / 1024) . 'MB');
                        break;
                    case 'select':
                        $options = explode(',', get_post_meta(apply_filters('wpml_object_id', $id, 'post', true), 'eib2bpro_field_options', true));

                        echo '<select class="eib2bpro_customfield_' . esc_attr($id) . ' eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . ' eib2bpro_customfield_required_' . esc_attr($required) . '" name="eib2bpro_customfield_' . esc_attr($id) . '" ' . (1 === $required ? 'required' : '') . '>';

                        // if there is a placeholder, use it an option
                        if ('' !== $placeholder) {
                            echo '<option value="">' . esc_html($placeholder) . '</option>';
                        }

                        foreach ($options as $option) {
                            if (!empty(trim($option))) {
                                echo '<option value="' . esc_attr(trim($option)) . '" ' . ($option === $value ? ' selected' : '') . '>' . esc_html(trim($option)) . '</option>';
                            }
                        }
                        echo '</select>';
                        break;
                    case 'checkbox':
                        $options = explode(',', get_post_meta(apply_filters('wpml_object_id', $id, 'post', true), 'eib2bpro_field_options', true));

                        $values = [];
                        if (isset($_POST['eib2bpro_customfield_' . esc_attr($id)])) {
                            foreach ($_POST['eib2bpro_customfield_' . esc_attr($id)] as $post) {
                                $values[] = (string)sanitize_text_field($post);
                            }
                        }
                        foreach ($options as $option) {
                            if (!empty(trim($option))) {
                                echo '<span class="form-row"><label class="woocommerce-form__label woocommerce-form__label-for-checkbox">';
                                echo '<input type="checkbox" class="eib2bpro_customfield_' . esc_attr($id) . ' woocommerce-form__input woocommerce-form__input-checkbox eib2bpro_customfield eib2bpro_customfield_type_' . esc_attr($type) . '" value="' . esc_attr(trim($option)) . '" name="eib2bpro_customfield_' . esc_attr($id) . '[]" ' . (in_array((string)trim($option), $values) ? ' checked' : '') . '>';
                                echo '<span>' . trim(wp_kses_data($option)) . '</span>';
                                echo '</label></span>';
                            }
                        }
                        break;
                }
            }
            echo '</div>';
        }

        echo '</div>';
    }

    public static function woocommerce_process_registration_errors_at_checkout($data, $errors)
    {
        self::woocommerce_process_registration_errors($errors, null, null);
    }

    public static function woocommerce_process_registration_errors($validation_error, $username, $email)
    {
        if (is_checkout() && 0 === intval(eib2bpro_post('createaccount'))) {
            return $validation_error;
        }

        $regtype = intval(eib2bpro_post('eib2bpro_registration_regtype_selector'));

        $fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        $country = eib2bpro_post('billing_country', false);

        foreach ($fields as $field) {
            $billing_field_type = get_post_meta($field->ID, 'eib2bpro_field_billing_type', true);
            if ('billing_country' === $billing_field_type || 'billing_country_state' === $billing_field_type) {
                $country = eib2bpro_post('eib2bpro_customfield_' . $field->ID);
            }
        }

        foreach ($fields as $field) {
            $regtypes = get_post_meta($field->ID, 'eib2bpro_registration_regtypes', true);

            if (0 === intval(get_post_meta($field->ID, 'eib2bpro_field_registration_show', true))) {
                continue;
            }


            if (is_array($regtypes) && !in_array($regtype, $regtypes)) {
                continue;
            }

            $required = intval(get_post_meta($field->ID, 'eib2bpro_field_registration_required', true));
            $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
            $label = get_post_meta(apply_filters('wpml_object_id', $field->ID, 'post', true), 'eib2bpro_field_label', true);
            $billing_field_type = get_post_meta($field->ID, 'eib2bpro_field_billing_type', true);

            if (is_checkout() && 'none' !== $billing_field_type && 'new' !== $billing_field_type && 'custom' !== $billing_field_type) {
                continue;
            }

            if (is_checkout() && 'file' === $type) {
                continue;
            }


            if ($billing_field_type === 'billing_country' || $billing_field_type === 'billing_country_state') {
                static::$user_country = eib2bpro_post('eib2bpro_customfield_' . $field->ID);
            }

            if (1 === $required) {
                if ($billing_field_type === 'billing_vat') {
                    $countries = (array)get_post_meta($field->ID, 'eib2bpro_field_billing_country', true);
                    if (in_array($country, $countries) && !eib2bpro_post('eib2bpro_customfield_' . $field->ID)) {
                        $validation_error->add('error_' . $field->ID, sprintf(esc_html__('Please enter a valid VAT number (%s)', 'eib2bpro'), $label));
                    }
                    $vies_validation = intval(get_post_meta($field->ID, 'eib2bpro_field_billing_vies', true));
                    if (1 === $vies_validation) {
                        $validated = self::vies_validation([]);
                        if (0 === $validated) {
                            $validation_error->add('error_' . $field->ID, sprintf(esc_html__('Please enter a valid VAT number (%s)', 'eib2bpro'), $label));
                        }
                    }
                } else {
                    if ('checkbox' === $type) {
                        if (!isset($_POST['eib2bpro_customfield_' . $field->ID])) {
                            $validation_error->add('error_' . $field->ID, sprintf(esc_html__('%s is a required field', 'eib2bpro'), $label));
                        } else {
                            if (!is_array($_POST['eib2bpro_customfield_' . $field->ID]) || 0 === count((array)$_POST['eib2bpro_customfield_' . $field->ID])) {
                                $validation_error->add('error_' . $field->ID, sprintf(esc_html__('%s is a required field', 'eib2bpro'), $label));
                            }
                        }
                    } else {
                        if ('file' === $type) {
                            if (!isset($_FILES['eib2bpro_customfield_' . $field->ID]) || empty($_FILES['eib2bpro_customfield_' . $field->ID]['name']) || 0 === intval($_FILES['eib2bpro_customfield_' . $field->ID]['size'])) {
                                $validation_error->add('error_' . $field->ID, sprintf(esc_html__('Please upload a file for %s', 'eib2bpro'), $label));
                            }
                        } elseif (!eib2bpro_post('eib2bpro_customfield_' . $field->ID)) {
                            $validation_error->add('error_' . $field->ID, sprintf(esc_html__('%s is a required field', 'eib2bpro'), $label));
                        }
                    }
                }
            }

            // email validation
            if ('email' === $type && '' !== eib2bpro_post('eib2bpro_customfield_' . $field->ID, '') && !filter_var(eib2bpro_post('eib2bpro_customfield_' . $field->ID), FILTER_VALIDATE_EMAIL)) {
                // invalid email
                $validation_error->add('error_' . $field->ID, sprintf(esc_html__('Please enter a valid email address (%s)', 'eib2bpro'), $label));
            }

            // file validation
            if ('file' === $type) {
                $allowed_types = apply_filters('eib2bpro_allowed_file_types', ["image/jpeg", "image/jpg", "image/png", "application/pdf"]);
                $allowed_size = apply_filters('eib2bpro_allowed_file_size', 5 * 1024 * 1024);

                if (!empty($_FILES['eib2bpro_customfield_' . $field->ID]['name'])) {
                    if (!in_array($_FILES['eib2bpro_customfield_' . $field->ID]['type'], $allowed_types)) {
                        $validation_error->add('error_' . $field->ID, sprintf(esc_html__('%s: Your file type is not valid, supported file types are png, jpg, pdf', 'eib2bpro'), $label));
                    }
                    if ($allowed_size < $_FILES['eib2bpro_customfield_' . $field->ID]['size']) {
                        $validation_error->add('error_' . $field->ID, sprintf(esc_html__('%s: Your file size is too large, maximum size is %s', 'eib2bpro'), $label, ($allowed_size / 1024 / 1024) . 'MB'));
                    }
                }
            }
        }

        return $validation_error;
    }

    public static function woocommerce_billing_fields($fields)
    {

        $group = Main::user('group');

        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        $priority = apply_filters('b2bpro_customfield_default_priority', 300);

        foreach ($custom_fields as $custom) {
            $groups = get_post_meta($custom->ID, 'eib2bpro_billing_groups', true);

            if (is_array($groups) && !in_array($group, $groups)) {
                continue;
            }

            $id = $custom->ID;
            $type = get_post_meta($custom->ID, 'eib2bpro_field_type', true);
            $label = get_post_meta(apply_filters('wpml_object_id', $custom->ID, 'post', true), 'eib2bpro_field_label', true);
            $placeholder = trim(get_post_meta(apply_filters('wpml_object_id', $custom->ID, 'post', true), 'eib2bpro_field_placeholder', true));
            $required = 1 === intval(get_post_meta($custom->ID, 'eib2bpro_field_billing_required', true)) ? true : false;
            $editable = intval(get_post_meta($custom->ID, 'eib2bpro_field_billing_editable', true));
            $value = get_user_meta(Main::user('id'), 'eib2bpro_customfield_' . $custom->ID, true);
            $billing_field_type = get_post_meta($custom->ID, 'eib2bpro_field_billing_type', true);

            if ('file' === $type || ('new' !== $billing_field_type && 'billing_vat' !== $billing_field_type)) {
                continue;
            }

            if (0 === intval(get_post_meta($custom->ID, 'eib2bpro_field_billing_show', true))) {
                continue;
            }

            if (NULL === $value) {
                $value = '';
            }

            if (1 === eib2bpro_option('b2b_settings_registration_enable_at_checkout', 0) && is_checkout() && !is_user_logged_in() &&  'billing_vat' !== $billing_field_type) {
                continue;
            }


            $field = array(
                'label' => sanitize_text_field($label),
                'placeholder' => sanitize_text_field($placeholder),
                'required' => $required,
                'clear' => false,
                'type' => sanitize_text_field($type),
                'default' => $value,
                'priority' => apply_filters('b2bpro_customfield_priority', $priority, $custom->ID),
                'class' => array('eib2bpro_customfield_required_' . esc_attr($required)),
            );

            if ('billing_vat' === $billing_field_type) {
                $field['type'] = 'text';
                $field['class'] = ['eib2bpro_customfield_vat_account', 'eib2bpro_customfield_required_' . esc_attr($required)];

                global $woocommerce;
                $customer = $woocommerce->customer;

                if (is_a($customer, 'WC_Customer')) {
                    $billing_country = \WC()->customer->get_billing_country();
                } else {
                    $billing_country = '-';
                }

                $countries = (array)get_post_meta($custom->ID, 'eib2bpro_field_billing_country', true);

                if (!in_array($billing_country, $countries)) {
                    $field['required'] = 0;
                }
            } else {
                if ('select' === $type || 'checkbox' === $type) {
                    $options = array();
                    $options_values = explode(',', get_post_meta($custom->ID, 'eib2bpro_field_options', true));

                    foreach ($options_values as $opt) {
                        if ('' !== trim($opt)) {
                            $field['options'][sanitize_key(trim($opt))] = trim($opt);
                        }
                    }
                }
            }

            if (is_user_logged_in()) { // post registration
                if (0 === $editable && !\EIB2BPRO\Admin::is_admin()) {
                    $field['custom_attributes'] = ['readonly' => 'readonly'];
                }
            }
            if (is_admin() && is_object(get_current_screen()) && isset(get_current_screen()->id) && 'shop_order' === get_current_screen()->id) {
                unset($field['class']);
            }

            if (!empty($field['type'])) {
                $fields['eib2bpro_customfield_' . $custom->ID] = $field;
            }

            ++$priority;
        }

        return $fields;
    }
    public static function save_account_details($errors)
    {

        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'eib2bpro_field_billing_editable',
                    'value' => 1
                ),
                array(
                    'key' => 'eib2bpro_field_billing_type',
                    'value' => 'billing_vat'
                )
            )
        ]);

        foreach ($custom_fields as $custom) {
            $value = eib2bpro_post('eib2bpro_customfield_' . $custom->ID, '');
            update_user_meta(get_current_user_id(), 'billing_vat', $value);
        }

        return $errors;
    }
    public static function save_billings_to_user_meta($user_id, $fields)
    {
        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        foreach ($custom_fields as $custom) {

            if (isset($fields['eib2bpro_customfield_' . $custom->ID])) {
                $value = sanitize_text_field($fields['eib2bpro_customfield_' . $custom->ID]);
                update_user_meta($user_id, 'eib2bpro_customfield_' . $custom->ID, $value);
            }
        }
    }

    public static function save_billings_to_order_meta($order_id)
    {
        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        foreach ($custom_fields as $custom) {

            $billing_field_type = get_post_meta($custom->ID, 'eib2bpro_field_billing_type', true);

            if (eib2bpro_post('eib2bpro_customfield_' . $custom->ID)) {
                if ('new' === $billing_field_type || 'billing_vat' === $billing_field_type) {
                    update_post_meta($order_id, '_billing_eib2bpro_customfield_' . $custom->ID, eib2bpro_post('eib2bpro_customfield_' . $custom->ID));
                }
            }
        }

        if (0 < get_current_user_id()) {
            update_user_meta(get_current_user_id(), '_eib2bpro_vies_validated', static::$vies_validated);
        }
    }

    public static function vies_validation($errors)
    {
        $pass = 1;

        $custom_fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'eib2bpro_field_billing_editable',
                    'value' => 1
                ),
                array(
                    'key' => 'eib2bpro_field_billing_type',
                    'value' => 'billing_vat'
                )
            )
        ]);

        foreach ($custom_fields as $custom) {

            if (
                (eib2bpro_post('eib2bpro_customfield_' . $custom->ID) && !empty(eib2bpro_post('eib2bpro_customfield_' . $custom->ID))) ||
                (eib2bpro_post('eib2bpro_customfield_' . $custom->ID) && !empty(eib2bpro_post('eib2bpro_customfield_' . $custom->ID)))
            ) {

                if (1 === intval(get_post_meta($custom->ID, 'eib2bpro_field_billing_vies', true))) {

                    $eu_countries = array('AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE');

                    $billing_country = eib2bpro_post('billing_country');
                    if (!$billing_country) {
                        $billing_country = static::$user_country;
                    }

                    if (!empty($billing_country)) {
                        $countries = (array)get_post_meta($custom->ID, 'eib2bpro_field_billing_country', true);
                        if (!in_array($billing_country, $countries)) {
                            continue;
                        }
                    }

                    $value = eib2bpro_post('eib2bpro_customfield_' . $custom->ID, eib2bpro_post('eib2bpro_customfield_' . $custom->ID));
                    $value = strtoupper(str_replace(array('.', ' '), '', $value));

                    if (!$billing_country) {
                        $billing_country = substr($value, 0, 2);
                    }

                    if (in_array($billing_country, $eu_countries)) {
                        $pass = 0;
                        $error = '';
                        try {
                            $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
                            $country_code = substr($value, 0, 2);
                            $vat = substr($value, 2);

                            $validation = $client->checkVat(array(
                                'countryCode' => $country_code,
                                'vatNumber' => $vat
                            ));
                        } catch (\Exception $e) {
                            $error_message = $e->getMessage();
                            $error .= ' ' . esc_html($error_message);
                        }

                        if (isset($validation)) {
                            if (intval($validation->valid) === 1) {
                                static::$vies_validated = 1;
                                $pass = 1;
                                if (0 < get_current_user_id()) {
                                    update_user_meta(get_current_user_id(), '_eib2bpro_vies_validated', 1);
                                }
                            } else {
                                static::$vies_validated = 0;
                                $pass = 0;
                                if (0 < get_current_user_id()) {
                                    update_user_meta(get_current_user_id(), '_eib2bpro_vies_validated', 0);
                                    update_user_meta(get_current_user_id(), 'eib2bpro_customfield_' . $custom->ID, '');
                                    update_user_meta(get_current_user_id(), 'billing_vat', '');
                                }
                                wc_add_notice(esc_html__('VIES validation error', 'eib2bpro') . $error, 'error');
                            }
                        } else {
                            static::$vies_validated = 0;
                            $pass = 0;
                            if (0 < get_current_user_id()) {
                                update_user_meta(get_current_user_id(), '_eib2bpro_vies_validated', 0);
                                update_user_meta(get_current_user_id(), 'eib2bpro_customfield_' . $custom->ID, '');
                                update_user_meta(get_current_user_id(), 'billing_vat', '');
                            }
                            wc_add_notice(esc_html__('VIES validation error', 'eib2bpro') . $error, 'error');
                        }
                    }
                }
            }
        }

        return $pass;
    }

    public static function shortcode_registration($atts, $content = null)
    {
        $params = shortcode_atts(array(
            'regtype_id' => 0,
            'regtype_selector' => '',
            'login_form' => 'hide'
        ), $atts);

        if (0 < intval($params["regtype_id"])) {
            self::$registration_regtype_id = intval($params["regtype_id"]);
        }

        add_filter('woocommerce_is_account_page', '__return_true');

        ob_start();

        if (is_user_logged_in()) {
            echo '<span class="eib2bpro_already_logged_in">' . esc_html__('You are already logged in.', 'eib2bpro') . '</span>';
        } else {

            $message = apply_filters('woocommerce_my_account_message', '');
            if (!empty($message)) {
                wc_add_notice($message);
            }
            wc_print_notices();

            $colset = '';
            if ('show' === $params['login_form']) {
                $colset = " col2-set1";
            }
        ?>
            <div class="woocommerce eib2bpro-registration-form-woocommerce">
                <div class="u-columns<?php eib2bpro_a($colset) ?>" id="customer_login">
                    <?php if ('show' === $params['login_form']) { ?>
                        <div class="u-column1 col-1">
                            <h2><?php esc_html_e('Login', 'woocommerce'); ?></h2>
                            <?php woocommerce_login_form(); ?>
                        </div>
                    <?php } ?>
                    <div class="u-column2 col-2">
                        <h2><?php esc_html_e('Register', 'woocommerce'); ?></h2>
                        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>

                            <?php do_action('woocommerce_register_form_start'); ?>

                            <?php if ('no' === get_option('woocommerce_registration_generate_username')) { ?>

                                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                    <label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" />
                                </p>

                            <?php } ?>

                            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                <label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                                <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" />
                            </p>

                            <?php if ('no' === get_option('woocommerce_registration_generate_password')) { ?>

                                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                                    <label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                                    <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" />
                                </p>

                            <?php } else { ?>

                                <p><?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?></p>

                            <?php } ?>

                            <?php do_action('woocommerce_register_form', $params['regtype_selector']); ?>

                            <p class="woocommerce-form-row form-row">
                                <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                                <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
                            </p>

                            <?php do_action('woocommerce_register_form_end'); ?>

                        </form>
                    </div>
                </div>
            </div>
            <?php

        }

        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function new_account_mail($email)
    {
        if ('customer_new_account' === $email->id) {
            $user = get_user_by('email', $email->user_email);
            if ('yes' !== get_user_meta($user->ID, 'eib2bpro_user_approved', true)) {
            ?>
                <p>
                    <?php
                    $text = esc_html__('Your account requires approval. We will review it as soon as possible.', 'eib2bpro');
                    echo wp_kses_post(wpautop(wptexturize($text)));
                    ?>
                </p>
<?php
            }
        }
    }

    public static function user_check_after_checkout()
    {
        $user_approval = get_user_meta(get_current_user_id(), 'eib2bpro_user_approved', true);

        if ('no' === $user_approval) {
            wp_logout();
            do_action('woocommerce_set_cart_cookies',  true);
            wc_add_notice(esc_html__('Your account requires approval. We will review it as soon as possible.', 'eib2bpro'), 'success');
        }
    }
    public static function woocommerce_register_form_tag()
    {
        echo 'enctype="multipart/form-data"';
    }
}
