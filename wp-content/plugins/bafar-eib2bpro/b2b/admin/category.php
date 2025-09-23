<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Category
{
    public static function edited_product_cat($term_id = 0)
    {
        if (1 === eib2bpro_post('eib2bpro_category_visibility_save', 0, 'int')) {
            $map['b2c'] = intval(eib2bpro_post('eib2bpro_category_visibility_group_b2c', 0));
            $map['guests'] = intval(eib2bpro_post('eib2bpro_category_visibility_group_guests', 0));
            if (isset($_POST['eib2bpro_users'])) {
                $map['users'] = sanitize_text_field(implode(',', array_map('trim', (array)$_POST['eib2bpro_users'])));
            } else {
                $map['users'] = '';
            }
            $map['groups'] = [];

            update_term_meta($term_id, 'eib2bpro_group_b2c', $map['b2c']);
            update_term_meta($term_id, 'eib2bpro_group_guests', $map['guests']);
            update_term_meta($term_id, 'eib2bpro_users', $map['users']);

            $groups = Groups::get();
            foreach ($groups as $group) {
                $map['groups'][$group->ID] = intval(eib2bpro_post('eib2bpro_category_visibility_group_' . $group->ID, 0));
                update_term_meta($term_id, 'eib2bpro_group_' . $group->ID, intval(eib2bpro_post('eib2bpro_category_visibility_group_' . $group->ID, 0)));
            }

            eib2bpro_option('b2b_last_category_edit_visibility', $map, 'set');
        }

        Main::clear_cache();
    }


    public static function product_cat_add_form_fields($term_id = 0)
    {
        if (is_object($term_id)) {
            $term_id = $term_id->term_id;
        }

        $last_edit = eib2bpro_option('b2b_last_category_edit_visibility', array());
?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label><?php esc_html_e('Visibility', 'eib2bpro'); ?></label>
            </th>
            <td>
                <div class="eib2bpro-postbox">
                    <div class="eib2bpro-postbox-content">
                        <h4 class="mt-1"><?php esc_html_e('Groups', 'eib2bpro'); ?></h4>
                        <input type="hidden" name="eib2bpro_category_visibility_save" value="1">
                        <div class="mb-1">
                            <?php eib2bpro_ui('onoff', 'eib2bpro_category_visibility_group_guests', eib2bpro_clean2(get_term_meta($term_id, 'eib2bpro_group_guests', true), 1), ['class' => 'switch-sm mr-2']); ?><?php esc_html_e('Guests', 'eib2bpro'); ?>
                        </div>

                        <div class="mb-1">
                            <?php eib2bpro_ui('onoff', 'eib2bpro_category_visibility_group_b2c', eib2bpro_clean2(get_term_meta($term_id, 'eib2bpro_group_b2c', true), 1), ['class' => 'switch-sm mr-2']); ?><?php esc_html_e('B2C Users', 'eib2bpro'); ?>
                        </div>

                        <?php
                        $groups = Groups::get();
                        foreach ($groups as $group) {
                            echo '<div class="mb-1">';
                            $value = eib2bpro_clean2(get_term_meta($term_id, 'eib2bpro_group_' . $group->ID, true), 1);
                            eib2bpro_ui('onoff', 'eib2bpro_category_visibility_group_' . $group->ID, $value, ['class' => 'switch-sm mr-2']);
                            echo esc_html(get_the_title($group->ID));
                            echo '</div>';
                        } ?>

                        <h4><?php esc_html_e('Users', 'eib2bpro'); ?></h4>
                        <div>
                            <?php eib2bpro_ui('b2b_users_select', 'eib2bpro_users', get_term_meta($term_id, 'eib2bpro_users', true), ['placeholder' => esc_html__('Please type to search users', 'eib2bpro')]); ?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
<?php
    }
}
