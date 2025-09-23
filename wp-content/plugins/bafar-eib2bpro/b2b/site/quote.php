<?php

namespace EIB2BPRO\B2b\Site;

defined('ABSPATH') || exit;

class Quote
{
    public static function button()
    { ?>
        <button type="button" id="eib2bpro-b2b-cart-request-a-quote-button" class="button">
            <?php
            esc_html_e('Request a quote', 'eib2bpro');
            ?>
        </button>
    <?php
    }

    public static function show()
    {
        $fields = get_posts([
            'post_type' => 'eib2bpro_quote_field',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]); ?>

        <form action="" method="POST" class="eib2bpro_quote_form">
            <input name="quote_product_id" type="hidden" value="<?php eib2bpro_a(eib2bpro_post('product_id', 0)) ?>">
            <div id="eib2bpro-b2b-cart-request-a-quote-container">
                <h2><?php esc_html_e('Request a quote', 'eib2bpro'); ?></h2>
                <?php if (0 < eib2bpro_post('product_id', 0, 'int')) { ?>
                    <table class="eib2bpro-b2b-quote-table">
                        <tbody>
                            <tr>
                                <td class="eib2bpro-b2b-quote-table-image">
                                    <?php
                                    $product = wc_get_product(eib2bpro_post('product_id', 0, 'int'));
                                    if ($product) {
                                        $image = get_the_post_thumbnail_url(eib2bpro_post('product_id', 0, 'int'), 'full');
                                        if ($image) {
                                            echo '<img src="' . esc_url_raw($image) . '" title="' . esc_attr($product->get_title()) . '" class="eib2bpro-Product_Image">';
                                        }
                                    }

                                    ?>
                                </td>
                                <td>
                                    <h4><?php eib2bpro_a($product->get_name()); ?></h4>
                                    <?php esc_html_e('Quantity', 'eib2bpro'); ?>
                                    <br>
                                    <input name="eib2bpro_quote_qty" type="number" step="1" class="eib2bpro-b2b-quote-table-qty" value="1">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <?php } ?>
                <?php
                if (Main::offer_in_cart() && 0 === eib2bpro_post('product_id', 0, 'int')) {
                    esc_html_e('You cannot request a quote while there is an offer in your cart.', 'eib2bpro');
                    return;
                }

                foreach ($fields as $field) {
                    $available_groups = get_post_meta($field->ID, 'eib2bpro_groups', true);

                    if ('0' === $available_groups || in_array(Main::user('group'), wp_parse_list($available_groups))) {
                        $value = '';
                        $id = $field->ID;
                        $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
                        $label = get_post_meta($field->ID, 'eib2bpro_field_label', true);
                        $placeholder = trim(get_post_meta($field->ID, 'eib2bpro_field_placeholder', true));
                        $required = intval(get_post_meta($field->ID, 'eib2bpro_field_required', true));
                        $class =  '';

                        if (isset($_POST['eib2bpro_quote_field_' . esc_attr($id)])) {
                            $value = eib2bpro_post('eib2bpro_quote_field_' . esc_attr($id));
                        }

                        echo '<div class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide ' . esc_attr($class) . '">';
                        echo '<div class="eib2bpro_quote_field_label">' . esc_html($label);
                        if (1 === $required) {
                            echo '<span class="required">*</span>';
                        }
                        echo '</div>';

                        switch ($type) {
                            case 'text':
                                echo '<input type="text" id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '">';
                                break;
                            case 'number':
                                echo '<input type="number" step="1" id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '">';
                                break;
                            case 'textarea':
                                echo '<textarea id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '" placeholder="' . esc_attr($placeholder) . '">' . esc_attr($value) . '</textarea>';
                                break;
                            case 'date':
                                echo '<input type="date" id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '">';
                                break;
                            case 'email':
                                echo '<input type="email" id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '">';
                                break;
                            case 'file':
                                $allowed_types = apply_filters('eib2bpro_allowed_file_types', ["image/jpeg", "image/jpg", "image/png", "application/pdf"]);
                                $allowed_size = apply_filters('eib2bpro_allowed_file_size', 5 * 1024 * 1024);
                                echo '<input type="file" id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_0' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '">';
                                echo '<br>' . sprintf(esc_html__('(Allowed file types: jpg,png,pdf and max file size is %s)', 'eib2bpro'), ($allowed_size / 1024 / 1024) . 'MB');
                                break;
                            case 'select':
                                $options = explode(',', get_post_meta(apply_filters('wpml_object_id', $id, 'post', true), 'eib2bpro_field_options', true));

                                echo '<select  id="eib2bpro_quote_field_' . esc_attr($id) . '" class="eib2bpro_quote_field eib2bpro_quote_field_type_' . esc_attr($type) . ' eib2bpro_quote_field_required_' . esc_attr($required) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '">';

                                // if there is a placeholder, use it an option
                                if ('' !== $placeholder) {
                                    echo '<option value=""> ' . esc_html($placeholder) . '</option>';
                                }

                                foreach ($options as $option) {
                                    echo '<option value="' . esc_attr(trim($option)) . '" ' . ($option === $value ? ' selected' : '') . '> ' . esc_html(trim($option)) . '</option>';
                                }

                                echo '</select>';
                                break;
                            case 'checkbox':
                                $options = explode(',', get_post_meta(apply_filters('wpml_object_id', $id, 'post', true), 'eib2bpro_field_options', true));

                                $values = [];
                                if (isset($_POST['eib2bpro_quote_field_' . esc_attr($id)])) {
                                    foreach ($_POST['eib2bpro_quote_field_' . esc_attr($id)] as $post) {
                                        $values[] = (string)sanitize_text_field($post);
                                    }
                                }
                                foreach ($options as $option) {
                                    echo '<div class="eib2bpro_quote_field_checkbox_item">';
                                    echo '<input id="eib2bpro_quote_field_' . esc_attr($id) . '" type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox eib2bpro_quote_field  eib2bpro_quote_field_required_' . esc_attr($required) . ' eib2bpro_quote_field_type_' . esc_attr($type) . '" value="' . esc_attr(trim($option)) . '" name="eib2bpro_quote_field_' . esc_attr($id) . '[]" ' . (in_array((string)trim($option), $values) ? ' checked' : '') . '>';
                                    echo ' <span>' . trim(wp_kses_data($option)) . '</span>';
                                    echo '</div>';
                                }
                                break;
                        }
                        echo '</div>';
                    } ?>
                <?php
                } ?>
                <button type="button" id="eib2bpro-b2b-cart-request-a-quote-send-button" class="button">
                    <?php
                    esc_html_e('Send quote request', 'eib2bpro');
                    ?>
                </button>
            </div>
        </form>
<?php

        wp_die();
    }

    public static function save()
    {

        $error = '';
        $email = false;
        $form = [];
        $input = [];

        $fields = get_posts([
            'post_type' => 'eib2bpro_quote_field',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        foreach ($_POST as $id => $post) {
            $key = str_replace(['[', ']'], '', $id);
            $form[$key][] = wc_clean($post);
        }

        foreach ($fields as $field) {
            $available_groups = get_post_meta($field->ID, 'eib2bpro_groups', true);

            if ('0' !== $available_groups && !in_array(Main::user('group'), wp_parse_list($available_groups))) {
                continue;
            }

            $required = intval(get_post_meta($field->ID, 'eib2bpro_field_required', true));
            $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
            $label = get_post_meta(apply_filters('wpml_object_id', $field->ID, 'post', true), 'eib2bpro_field_label', true);

            if (1 === $required) {
                if ('checkbox' === $type) {
                    if (0 === count($form['eib2bpro_quote_field_' . $field->ID])) {
                        $error .= sprintf(esc_html__('Please enter %s', 'eib2bpro'), $label) . PHP_EOL;
                    }
                } else {
                    if ('file' === $type) {
                        if (!isset($_FILES['eib2bpro_quote_field_' . $field->ID]) || empty($_FILES['eib2bpro_quote_field_' . $field->ID]['name']) || 0 === intval($_FILES['eib2bpro_quote_field_' . $field->ID]['size'])) {
                            $error .= sprintf(esc_html__('Please upload a file for %s', 'eib2bpro'), $label) . PHP_EOL;
                        }
                        $allowed_types = apply_filters('eib2bpro_allowed_file_types', ["image/jpeg", "image/jpg", "image/png", "application/pdf"]);
                        $allowed_size = apply_filters('eib2bpro_allowed_file_size', 5 * 1024 * 1024);

                        if (!in_array($_FILES['eib2bpro_quote_field_' . $field->ID]['type'], $allowed_types)) {
                            $error .= sprintf(esc_html__('%s: Your file type is not valid, supported file types are png, jpg, pdf', 'eib2bpro'), $label) . PHP_EOL;
                        }
                        if ($allowed_size < $_FILES['eib2bpro_quote_field_' . $field->ID]['size']) {
                            $error .= sprintf(esc_html__('%s: Your file size is too large, maximum size is %s', 'eib2bpro'), $label, ($allowed_size / 1024 / 1024) . 'MB') . PHP_EOL;
                        }
                    } elseif (!isset($form['eib2bpro_quote_field_' . $field->ID][0]) || empty($form['eib2bpro_quote_field_' . $field->ID][0]) || '' === trim($form['eib2bpro_quote_field_' . $field->ID][0])) {
                        $error .= sprintf(esc_html__('Please enter %s', 'eib2bpro'), $label) . PHP_EOL;
                    }
                }
            }

            // email validation
            if ('email' === $type && '' !== $form['eib2bpro_quote_field_' . $field->ID][0] && !filter_var($form['eib2bpro_quote_field_' . $field->ID][0], FILTER_VALIDATE_EMAIL)) {
                // invalid email
                $error .= sprintf(esc_html__('Please enter a valid email address (%s)', 'eib2bpro'), $label) . PHP_EOL;
            }

            if (empty($error)) {
                // get values
                if (isset($form['eib2bpro_quote_field_' . $field->ID]) && 1 === count($form['eib2bpro_quote_field_' . $field->ID])) {
                    $input[$field->ID] = $form['eib2bpro_quote_field_' . $field->ID][0];
                    if ('email' === $type && filter_var($form['eib2bpro_quote_field_' . $field->ID][0], FILTER_VALIDATE_EMAIL)) {
                        $email = $form['eib2bpro_quote_field_' . $field->ID][0];
                    }
                } else {

                    if ('file' === $type) {
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                        require_once(ABSPATH . 'wp-admin/includes/media.php');

                        // Upload the file
                        $attachment_id = media_handle_upload('eib2bpro_quote_field_' . $field->ID, 'full');
                        $attachment_post = array(
                            'ID'          => $attachment_id,
                            'post_author' => get_current_user_id()
                        );
                        wp_update_post($attachment_post);

                        $input[$field->ID] = wp_get_attachment_url($attachment_id);
                    } else {
                        $input[$field->ID] = implode(',', wc_clean($form['eib2bpro_quote_field_' . $field->ID]));
                    }
                }
            }
        }

        if (!empty($error)) {
            \eib2bpro_error($error);
        }

        // everything fine

        $default = array(
            'post_title' => esc_html__('Quote', 'eib2bpro'),
            'post_status' => 'publish',
            'post_type' => 'eib2bpro_quote',
            'post_author' => 1,
        );

        $post_id = wp_insert_post($default);

        foreach ($input as $id => $value) {
            update_post_meta($post_id, 'eib2bpro_field_' . $id, $value);
            update_post_meta($post_id, 'eib2bpro_field_' . $id . '_title', get_the_title($id));
        }

        update_post_meta($post_id, 'eib2bpro_field_ids', implode(',', array_keys($input)));

        // cart items

        $products = [];

        if (0 === eib2bpro_post('quote_product_id', 0, 'int')) {
            $cart = \WC()->cart->get_cart();
            foreach ($cart as $item => $values) {
                $product =  wc_get_product($values['data']->get_id());
                $products[$product->get_id()] = [
                    'name' => $product->get_formatted_name(),
                    'qty' => $values['quantity'],
                    'variation' => isset($values['variation']) ? $values['variation'] : []
                ];
            }
        } else {
            $product =  wc_get_product(intval(eib2bpro_post('quote_product_id', 0)));
            $attributes = [];
            if (isset($_POST)) {
                foreach ($_POST as $k => $v) {
                    if (stripos($k, 'attributes_attribute') !== false) {
                        $k = str_replace('attributes_', '', $k);
                        $attributes[sanitize_key($k)] = sanitize_text_field($v);
                    }
                }
            }
            $products[$product->get_id()] = [
                'name' => $product->get_formatted_name(),
                'qty' => intval(eib2bpro_post('eib2bpro_quote_qty', 1)),
                'variation' => $attributes
            ];
        }

        update_post_meta($post_id, 'eib2bpro_products', $products);
        update_post_meta($post_id, 'eib2bpro_customer_id', get_current_user_id());

        if ($email) {
            update_post_meta($post_id, 'eib2bpro_customer_email', $email);
        }

        \EIB2BPRO\Core\Notifications::new_quote($post_id);

        eib2bpro_success('<div class="eib2bpro-b2b-cart-request-a-quote-ok">' . esc_html__('Your request for quotation has been received, we will contact you as soon as possible.', 'eib2bpro') . '</div>');
    }

    public static function hide_price($q)
    {
        return eib2bpro_option('b2b_settings_localization_quote', esc_html__('Quote', 'eib2bpro'));
    }
}
