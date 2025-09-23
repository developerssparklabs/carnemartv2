<?php

namespace EIB2BPRO\Rules;

defined('ABSPATH') || exit;

class Ajax
{
    public static function run()
    {
        $do = eib2bpro_post('do');

        switch ($do) {
            case 'search':
                \EIB2BPRO\Rules\Main::search();
                break;
            case 'save-rule':
                \EIB2BPRO\Rules\Main::save();
                break;
            case 'delete-rule':
                \EIB2BPRO\Rules\Main::delete();
                break;
            case "change-status":
                $id = intval(eib2bpro_post('id'));

                $set = get_post($id);

                if (!is_wp_error($set) && "eib2bpro_rules" === $set->post_type) {
                    wp_update_post([
                        'ID' => $id,
                        'post_status' => eib2bpro_post('checked', 'false') === 'true' ? 'publish' : 'private'
                    ]);
                    \EIB2BPRO\Rules\Main::build_map();
                    eib2bpro_success('');
                } else {
                    eib2bpro_error(esc_html__('Error', 'eib2bpro'));
                }
                break;
        }
    }
}
