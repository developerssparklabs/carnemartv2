<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;

class Ajax
{
    public static function run()
    {
        $do = eib2bpro_post('do');

        switch ($do) {
            case 'search':
                \EIB2BPRO\Core\Search::ajax();
                break;
            case 'notifications':
                \EIB2BPRO\Core\Notifications::ajax();
                break;
            case 'enable-notifications':
                \EIB2BPRO\Core\Notifications::enable();
                break;

            case 'me-settings-save':
                \EIB2BPRO\Core\Me::save_settings();
                break;

            case 'save-todo':
                \EIB2BPRO\Core\Todo::input();
                break;
            case 'sort-todos':
                \EIB2BPRO\Core\Todo::sort();
                break;
            case 'save-notes':
                $data = [
                    'resource_type' => 'private',
                ];

                if (isset($_POST['content'])) {
                    $data['content'] = wp_kses_post($_POST['content']);
                    if (empty(trim($data['content']))) {
                        return;
                    }
                } else {
                    return;
                }

                if (isset($_POST['collapsed'])) {
                    $data['collapsed'] = 'true' === eib2bpro_post('collapsed', false) ? 0 : 1;
                }

                if (isset($_POST['color'])) {
                    $data['color'] = eib2bpro_post('color', '#fff');
                }

                $id = \EIB2BPRO\Core\Note::save(intval(eib2bpro_post('id', 0)), $data);
                if (0 < $id) {
                    eib2bpro_success('', ['id' => $id]);
                } else {
                    eib2bpro_error(esc_html__('Can not save', 'eib2bpro'));
                }
                break;
            case 'sort-notes':
                \EIB2BPRO\Core\Note::sort();
                break;
            case 'delete-note':
                \EIB2BPRO\Core\Note::delete(eib2bpro_post('id', 0));
                break;
        }
    }

    public static function public()
    {
        $do = eib2bpro_post('do');

        switch ($do) {
            case 'tracker':
                \EIB2BPRO\Core\Tracker::record_by_ajax();
                break;
        }
    }
}
