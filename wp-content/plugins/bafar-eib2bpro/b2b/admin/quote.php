<?php

namespace EIB2BPRO\B2b\Admin;

use EIB2BPRO\Admin\Data;

defined('ABSPATH') || exit;

class Quote
{
    public static function run()
    {

        do_action('wpml_set_translation_mode_for_post_type', 'eib2bpro_quote_field', 'translate');

        switch (eib2bpro_get('action')) {
            case 'edit-field':
                self::edit_field();
                break;
            default:
                self::index();
                break;
        }
    }

    public static function index()
    {
        if (eib2bpro_option('badge-quote')) {
            delete_option('eib2bpro_badge-quote');
        }

        $list = new \WP_Query([
            'posts_per_page' => eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10),
            'paged' => (eib2bpro_get('pg') ? eib2bpro_get('pg') : 1),
            'post_type' => 'eib2bpro_quote',
            'post_status' => ['publish'],
            'numberposts' => -1,
            'suppress_filters' => 1
        ]);

        echo eib2bpro_view('b2b', 'admin', 'quote.list', ['list' => $list]);
    }

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
                        'post_type' => 'eib2bpro_quote_field',
                        'post_author' => get_current_user_id()
                    ]);
                }

                if (0 < $id) {
                    // update field
                    $group = get_post($id);

                    if (!is_wp_error($group) && 'eib2bpro_quote_field' === $group->post_type) {
                        wp_update_post([
                            'ID' => $id,
                            'post_title' => wp_strip_all_tags($data['title']),
                        ]);

                        $post = $group->ID;
                    }
                }

                if (0 === intval($post)) {
                    eib2bpro_error(esc_html__('Error:', 'eib2bpro') . '#133');
                }

                // regtypes
                if ('0' === eib2bpro_post('eib2bpro_group_selector')) {
                    $groups = '0';
                } else {
                    foreach ((array)$_POST['eib2bpro_groups'] as $k => $regtype) {
                        if ('0' !== $regtype) {
                            $groups[] = sanitize_key($regtype);
                        }
                    }
                    $groups = implode(',', $groups);
                }
                update_post_meta($post, 'eib2bpro_groups', $groups);

                update_post_meta($post, 'eib2bpro_group_selector', intval(eib2bpro_post('eib2bpro_group_selector', 0)));

                update_post_meta($post, 'eib2bpro_field_required', intval(eib2bpro_post('eib2bpro_field_required', 0)));
                update_post_meta($post, 'eib2bpro_field_type', eib2bpro_post('eib2bpro_field_type', 'input'));
                update_post_meta($post, 'eib2bpro_field_label', eib2bpro_post('eib2bpro_field_label', ''));
                update_post_meta($post, 'eib2bpro_field_placeholder', eib2bpro_post('eib2bpro_field_placeholder', ''));
                update_post_meta($post, 'eib2bpro_field_options', eib2bpro_post('eib2bpro_field_options', ''));

                if (0 === $id) {
                    update_post_meta($post, 'eib2bpro_position', time());
                }
            }
            eib2bpro_success('', ['after' => ['close' => true, 'redirect' => eib2bpro_admin('b2b', ['section' => 'settings', 'tab' => 'quote'])]]);
        }

        if (0 < eib2bpro_get('trid', 0, 'int')) {
            $redirect_id = \EIB2BPRO\B2B\Admin\Toolbox::duplicate_post_for_wpml(eib2bpro_get('original', 0, 'int'));
            wp_safe_redirect(
                eib2bpro_admin('b2b', ['section' => 'quote', 'action' => 'edit-field', 'id' => $redirect_id])
            );
        }

        echo eib2bpro_view('b2b', 'admin', 'quote.edit-field', ['id' => $id]);
    }

    public static function delete()
    {
        $id = eib2bpro_post('id', 0, 'int');

        $post = get_post($id);
        if (!$post) {
            return;
        }

        if (!current_user_can('delete_post', $post->ID)) {
            return;
        }

        wp_delete_post($post->ID, true);

        eib2bpro_success('', ['after' => ['message' => esc_html__('Done', 'eib2bpro'), 'close' => true, 'redirect_parent' => eib2bpro_admin('b2b', ['section' => 'settings', 'tab' => 'quote'])]]);
    }
}
