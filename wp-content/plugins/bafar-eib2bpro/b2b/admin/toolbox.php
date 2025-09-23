<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Toolbox
{
    public static function run()
    {
        self::index();
    }

    public static function index()
    {
        echo eib2bpro_view('b2b', 'admin', 'toolbox.main');
    }

    public static function actions()
    {
        switch (eib2bpro_post('run')) {
            case 'move-users':
                self::move_users();
                break;
            case 'clear-all-caches':
                Main::clear_cache(['all', 'users']);
                eib2bpro_success();
                break;
        }
    }


    public static function move_users()
    {

        $move = eib2bpro_post('move_users_to', 0, 'int');

        $all_users = get_users([
            'fields' => 'ids',
        ]);

        if (!empty($all_users)) {
            foreach ($all_users as $user_id) {
                if (0 === $move) { // b2c
                    update_user_meta($user_id, 'eib2bpro_user_type', 'b2c');
                    update_user_meta($user_id, 'eib2bpro_group', $move);
                } else {
                    update_user_meta($user_id, 'eib2bpro_user_type', 'b2b');
                    update_user_meta($user_id, 'eib2bpro_group', $move);
                }
            }
        }

        Main::clear_cache(['all', 'users']);
        eib2bpro_success();
    }

    public static function clear_sales_cache($order = 0)
    {
        Main::clear_cache(['sales']);
    }

    public static function clear_users_cache($user_id = 0)
    {
        Main::clear_cache(['users']);
    }

    public static function products_flush_cache($post)
    {
        if ('product' === get_post_type($post) || 'eib2bpro_rules' === get_post_type($post)) {
            Main::clear_cache(['all', 'users']);
        }
    }

    public static function activate()
    {
        // create default Offer product
        \EIB2BPRO\B2b\Site\Offers::create_default_offer_id();

        // add default group
        $groups = get_posts([
            'post_type' => 'eib2bpro_groups',
            'post_status' => ['publish', 'draft'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);


        if (0 === count($groups)) {
            $group = array(
                'post_title'  => sanitize_text_field(esc_html__('B2B Customers', 'eib2bpro')),
                'post_status' => 'publish',
                'post_type'   => 'eib2bpro_groups',
                'post_author' => get_current_user_id(),
            );

            $post = wp_insert_post($group);
            update_post_meta($post, 'eib2bpro_position', time() * -1);
        }

        // options
        eib2bpro_option('b2b_enable_offers', 1, 'set');
        eib2bpro_option('b2b_enable_bulkorder', 0, 'set');
        eib2bpro_option('b2b_enable_quote', 1, 'set');

        // rewrite endpoint rules
        \EIB2BPRO\B2b\Site\Main::endpoints();
        flush_rewrite_rules();
    }

    public static function duplicate_post_for_wpml($post_id = 0)
    {
        global $sitepress;
        $trid = eib2bpro_get('trid', 0, 'int');

        if (!function_exists('icl_object_id') or 0 === intval($trid)) {
            return;
        }

        $post_id = absint($post_id);
        $post = get_post($post_id);

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        if ($post) {

            // new post data array
            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => 'publish',
                'post_title'     => $post->post_title,
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order
            );

            $new_post_id = wp_insert_post($args);

            if (function_exists('icl_object_id') && 0 < intval($trid)) {
                $sitepress->set_element_language_details($post_id, 'post_' . get_post_type($post_id), intval(eib2bpro_get('trid')), eib2bpro_get('source_lang'));
            }

            // duplicate all post meta
            $post_meta = get_post_meta($post_id);
            if ($post_meta) {

                foreach ($post_meta as $meta_key => $meta_values) {

                    if ('_wp_old_slug' === $meta_key) {
                        continue;
                    }

                    foreach ($meta_values as $meta_value) {
                        $meta_value = maybe_unserialize($meta_value);
                        add_post_meta($new_post_id, $meta_key, $meta_value);
                    }
                }
            }
        }

        if (0 < intval($new_post_id)) {
            return $new_post_id;
        }

        wp_die(esc_html__('Error: Please contact us', 'eib2bpro'));
    }

    public static function import_columns($columns)
    {
        $groups = get_posts([
            'post_type' => 'eib2bpro_groups',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);

        $options = $columns['price']['options'];

        foreach ($groups as $group) {
            $options['eib2bpro_regular_price_group_' . $group->ID] = get_the_title($group->ID) . ' - ' . esc_html__('Regular Price', 'eib2bpro');
            $options['eib2bpro_sale_price_group_' . $group->ID] = get_the_title($group->ID)  . ' - ' . esc_html__('Sale Price', 'eib2bpro');
        }

        $columns['price']  = array(
            'name'    => esc_html__('Price', 'woocommerce'),
            'options' => $options,
        );

        return $columns;
    }

    public static function mapping_screen($columns)
    {

        $groups = get_posts([
            'post_type' => 'eib2bpro_groups',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);

        foreach ($groups as $group) {
            $columns[get_the_title($group->ID) . ' - ' . esc_html__('Regular Price', 'eib2bpro')] = 'eib2bpro_regular_price_group_' . $group->ID;
            $columns[get_the_title($group->ID) . ' - ' . esc_html__('Sale Price', 'eib2bpro')] = 'eib2bpro_sale_price_group_' . $group->ID;
        }

        return $columns;
    }

    public static function process_import($object, $data)
    {

        $groups = get_posts([
            'post_type' => 'eib2bpro_groups',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);
        foreach ($groups as $group) {
            if (!empty($data['eib2bpro_regular_price_group_' . $group->ID])) {
                $object->update_meta_data('eib2bpro_regular_price_group_' . $group->ID, floatval($data['eib2bpro_regular_price_group_' . $group->ID]));
            }

            if (!empty($data['eib2bpro_sale_price_group_' . $group->ID])) {
                $object->update_meta_data('eib2bpro_sale_price_group_' . $group->ID, floatval($data['eib2bpro_sale_price_group_' . $group->ID]));
            }
        }
        return $object;
    }

    public static function minify_css($css)
    {

        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        $css = str_replace(array('{ ', ' {'), '{', $css);
        $css = str_replace(array('} ', ' }'), '}', $css);
        $css = str_replace('; ', ';', $css);
        $css = str_replace(': ', ':', $css);
        $css = str_replace(', ', ',', $css);
        $css = str_replace(array('> ', ' >'), '>', $css);
        $css = str_replace(array('+ ', ' +'), '+', $css);
        $css = str_replace(array('~ ', ' ~'), '~', $css);
        $css = str_replace(';}', '}', $css);

        return $css;
    }
}
