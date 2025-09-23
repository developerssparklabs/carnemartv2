<?php

/**
 * Admin interface for registration types and fields
 */

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

use EIB2BPRO\Admin\Data;

class Registration
{
    /**
     * Start
     *
     * @return void
     */

    public static function run()
    {

        do_action('wpml_set_translation_mode_for_post_type', 'eib2bpro_regtype', 'translate');
        do_action('wpml_set_translation_mode_for_post_type', 'eib2bpro_fields', 'translate');

        switch (eib2bpro_get('action')) {
            case 'edit-regtype':
                self::edit_regtype();
                break;
            case 'edit-field':
                self::edit_field();
                break;
            default:
                self::all();
                break;
        }
    }

    /**
     * Show all regtypes and fields
     */

    public static function all()
    {
        $regtypes = get_posts([
            'post_type' => 'eib2bpro_regtype',
            'post_status' => 'trash' === eib2bpro_get('status') ? ['trash'] : ['publish', 'private'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        $fields = get_posts([
            'post_type' => 'eib2bpro_fields',
            'post_status' => 'trash' === eib2bpro_get('status') ? ['trash'] : ['publish', 'private'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        echo eib2bpro_view('b2b', 'admin', 'registration.list', ['regtypes' => $regtypes, 'fields' => $fields]);
    }

    /**
     * Get regtypes list
     */

    public static function get_regtypes($id = 0, $fields = 'all')
    {
        $params = [
            'post_type' => 'eib2bpro_regtype',
            'post_status' => ['publish', 'private'],
            'numberposts' => -1,
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ];

        if (0 < $id || is_array($id)) {
            if (!is_array($id)) {
                $id = [intval($id)];
            }
            $params['include'] = $id;
        }

        if ('all' !== $fields) {
            $params['fields'] = $fields;
        }

        return get_posts($params);
    }

    /**
     * Edit regtypes
     *
     * @return void
     */
    public static function edit_regtype()
    {

        $id = intval(eib2bpro_get('id', 0));

        if ($_POST) {
            $id = intval(eib2bpro_post('id'));

            // create new
            if (-1 < $id) {
                $data = Data::validate([
                    'title' => ['required' => true, 'default' => '-']
                ], $_POST);

                if (0 === $id) {
                    // insert regtype
                    $post = wp_insert_post([
                        'post_title' => wp_strip_all_tags($data['title']),
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_type' => 'eib2bpro_regtype',
                        'post_author' => get_current_user_id()
                    ]);
                }

                if (0 < $id) {
                    // update regtype
                    $group = get_post($id);

                    if (!is_wp_error($group) && 'eib2bpro_regtype' === $group->post_type) {
                        wp_update_post([
                            'ID' => $id,
                            'post_title' => wp_strip_all_tags($data['title']),
                        ]);

                        $post = $group->ID;
                    }
                }

                if (0 === intval($post)) {
                    eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#12');
                }

                update_post_meta($post, 'eib2bpro_automatic_approval', intval(eib2bpro_post('eib2bpro_automatic_approval', 0)));
                update_post_meta($post, 'eib2bpro_approval_group', intval(eib2bpro_post('eib2bpro_approval_group', 0)));
                update_post_meta($post, 'eib2bpro_message', eib2bpro_r(wp_kses_post(trim($_POST['eib2bpro_message']))));

                if (0 === $id) {
                    update_post_meta($post, 'eib2bpro_position', time());
                }
            }
            eib2bpro_success('', ['after' => ['close' => true, 'redirect' => eib2bpro_admin('b2b', ['section' => 'fields', 'tab' => 'regtypes'])]]);
        }

        if (0 < eib2bpro_get('trid', 0, 'int')) {
            $redirect_id = \EIB2BPRO\B2B\Admin\Toolbox::duplicate_post_for_wpml(eib2bpro_get('original', 0, 'int'));
            wp_safe_redirect(
                eib2bpro_admin('b2b', ['section' => 'fields', 'action' => 'edit-regtype', 'id' => $redirect_id])
            );
        }

        echo eib2bpro_view('b2b', 'admin', 'registration.edit-regtype', ['id' => $id]);
    }

    /**
     * Edit fields
     */

    public static function edit_field()
    {
        $id = intval(eib2bpro_get('id', 0));

        if ($_POST) {
            $id = intval(eib2bpro_post('id'));

            // create new
            if (-1 < $id) {
                $data = Data::validate([
                    'title' => ['required' => true, 'default' => '-']
                ], $_POST);

                if (0 === $id) {
                    // insert field
                    $post = wp_insert_post([
                        'post_title' => wp_strip_all_tags($data['title']),
                        'post_content' => '',
                        'post_status' => 'publish',
                        'post_type' => 'eib2bpro_fields',
                        'post_author' => get_current_user_id()
                    ]);
                }

                if (0 < $id) {
                    // update field
                    $group = get_post($id);

                    if (!is_wp_error($group) && 'eib2bpro_fields' === $group->post_type) {
                        wp_update_post([
                            'ID' => $id,
                            'post_title' => wp_strip_all_tags($data['title']),
                        ]);

                        $post = $group->ID;
                    }
                }

                if (0 === intval($post)) {
                    eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#16');
                }

                // regtypes
                if ('0' === eib2bpro_post('eib2bpro_regtype_selector')) {
                    $regtypes = '0';
                } else {
                    foreach ((array)$_POST['eib2bpro_registration_regtypes'] as $k => $regtype) {
                        if (0 < intval($regtype)) {
                            $regtypes[] = intval($regtype);
                        }
                    }
                }

                // groups
                foreach ((array)$_POST['eib2bpro_billing_groups'] as $k => $group) {
                    $groups[] = wc_clean($group);
                }

                update_post_meta($post, 'eib2bpro_field_registration_show', intval(eib2bpro_post('eib2bpro_field_registration_show', 0)));
                update_post_meta($post, 'eib2bpro_registration_regtypes', $regtypes);
                update_post_meta($post, 'eib2bpro_regtype_selector', intval(eib2bpro_post('eib2bpro_regtype_selector')));
                update_post_meta($post, 'eib2bpro_field_registration_required', intval(eib2bpro_post('eib2bpro_field_registration_required', 0)));

                update_post_meta($post, 'eib2bpro_field_billing_show', intval(eib2bpro_post('eib2bpro_field_billing_show', 0)));
                update_post_meta($post, 'eib2bpro_field_billing_type', eib2bpro_post('eib2bpro_field_billing_type', 0));

                if ('new' === eib2bpro_post('eib2bpro_field_billing_type') || 'billing_vat' === eib2bpro_post('eib2bpro_field_billing_type')) {
                    update_post_meta($post, 'eib2bpro_billing_groups', $groups);
                }

                if ('billing_vat' === eib2bpro_post('eib2bpro_field_billing_type')) {
                    if ((isset($_POST['eib2bpro_field_billing_country'][0]) && "0" === wc_clean($_POST['eib2bpro_field_billing_country'][0])) || empty((array)wc_clean($_POST['eib2bpro_field_billing_country']))) {
                        $WC_Countries = new \WC_Countries;
                        $countries = $WC_Countries->get_countries();
                        update_post_meta($post, 'eib2bpro_field_billing_country', array_keys($countries));
                        update_post_meta($post, 'eib2bpro_field_billing_country_all', 1);
                        eib2bpro_option('b2b_field_vat_countries', array_keys($countries), 'set');
                    } else {
                        update_post_meta($post, 'eib2bpro_field_billing_country', (array)wc_clean($_POST['eib2bpro_field_billing_country']));
                        update_post_meta($post, 'eib2bpro_field_billing_country_all', 0);
                        eib2bpro_option('b2b_field_vat_countries', (array)wc_clean($_POST['eib2bpro_field_billing_country']), 'set');
                    }
                    update_post_meta($post, 'eib2bpro_field_billing_vies', intval(eib2bpro_post('eib2bpro_field_billing_vies', 0)));
                }

                if ('custom' === eib2bpro_post('eib2bpro_field_billing_type')) {
                    update_post_meta($post, 'eib2bpro_field_billing_custom', eib2bpro_post('eib2bpro_field_billing_custom', ''));
                }

                update_post_meta($post, 'eib2bpro_field_billing_required', intval(eib2bpro_post('eib2bpro_field_billing_required', 0)));
                update_post_meta($post, 'eib2bpro_field_billing_editable', intval(eib2bpro_post('eib2bpro_field_billing_editable', 0)));
                update_post_meta($post, 'eib2bpro_field_billing_show_invoice', intval(eib2bpro_post('eib2bpro_field_billing_show_invoice', 0)));

                update_post_meta($post, 'eib2bpro_field_type', eib2bpro_post('eib2bpro_field_type', 'input'));
                update_post_meta($post, 'eib2bpro_field_label', eib2bpro_post('eib2bpro_field_label', ''));
                update_post_meta($post, 'eib2bpro_field_placeholder', eib2bpro_post('eib2bpro_field_placeholder', ''));
                update_post_meta($post, 'eib2bpro_field_options', wp_kses_data($_POST['eib2bpro_field_options']));

                if (0 === $id) {
                    update_post_meta($post, 'eib2bpro_position', time());
                }
            }
            eib2bpro_success('', ['after' => ['close' => true, 'redirect' => eib2bpro_admin('b2b', ['section' => 'fields', 'tab' => 'fields'])]]);
        }

        if (0 < eib2bpro_get('trid', 0, 'int')) {
            $redirect_id = \EIB2BPRO\B2B\Admin\Toolbox::duplicate_post_for_wpml(eib2bpro_get('original', 0, 'int'));
            wp_safe_redirect(
                eib2bpro_admin('b2b', ['section' => 'fields', 'action' => 'edit-field', 'id' => $redirect_id])
            );
        }

        echo eib2bpro_view('b2b', 'admin', 'registration.edit-field', ['id' => $id]);
    }

    /**
     * Change post status
     *
     * @param string $setting
     * @return void
     */

    public static function change_post_status($setting = 'regtype')
    {
        $id = intval(eib2bpro_post('id'));

        $set = get_post($id);

        if (!is_wp_error($set) && "eib2bpro_$setting" === $set->post_type) {
            wp_update_post([
                'ID' => $id,
                'post_status' => eib2bpro_post('checked', 'false') === 'true' ? 'publish' : 'private'
            ]);
            eib2bpro_success();
        } else {
            eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#12');
        }
    }

    /**
     * Change positions of regtypes and fields
     *
     * @return void
     */

    public static function edit_positions()
    {
        $ids = [];
        $index = 0;

        foreach ($_POST['position'] as $post => $value) {
            foreach ($value as $id => $value2) {
                ++$index;
                $post_type = eib2bpro_clean($post);
                $post_id = $id;
                update_post_meta($post_id, 'eib2bpro_position', $index);
            }
        }

        eib2bpro_success();
    }
}
