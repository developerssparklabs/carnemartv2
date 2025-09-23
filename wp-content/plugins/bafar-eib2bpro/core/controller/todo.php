<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;
class Todo
{
    public static function run()
    {
        // nothing to do
    }

    public static function render($args = [], $layout = 'mini')
    {
        if (!isset($args['limit'])) {
            $args['limit'] = 5;
        }

        if (isset($args['return'])) {
            return  eib2bpro_view('core', 0, 'shared.todo.' . $layout, $args);;
        }
        echo  eib2bpro_view('core', 0, 'shared.todo.' . $layout, $args);
    }

    public static function input()
    {
        $id = eib2bpro_post('id', 0, 'int');

        if (-1 === $id) {
            eib2bpro_success('', ['html' => self::render(['return' => true, 'limit' => eib2bpro_post('limit', 5, 'int')])]);
        }

        if (!eib2bpro_post('content')) {
            eib2bpro_error(esc_html__('Can not save', 'eib2bpro'));
        }

        $data = [
            'content' => eib2bpro_post('content'),
            'checked' => 'true' === eib2bpro_post('checked') ? 1 : 0,
            'status' => eib2bpro_post('status', 1, 'int')
        ];

        $new_id = self::save($id, $data);

        if (0 < $new_id) {
            eib2bpro_success('', ['html' => self::render(['return' => true, 'limit' => eib2bpro_post('limit', 5, 'int')])]);
        } else {
            eib2bpro_error(esc_html__('Can not save', 'eib2bpro'));
        }
    }

    public static function all($checked = 0, $args = [])
    {
        global $wpdb;

        $args = wp_parse_args($args, [
            'limit' => 5
        ]);

        $created_by = get_current_user_id();

        $all = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}eib2bpro_todo WHERE list_id=-1 AND checked = %d AND created_by = %d AND status=1 ORDER BY " . (1 === $checked ? 'updated_at DESC' : 'position ASC') . "  LIMIT %d ",
                $checked,
                $created_by,
                $args['limit']
            ),
            ARRAY_A
        );

        return $all;
    }

    public static function get($todo)
    {
        echo eib2bpro_view('core', 0, 'shared.todo.todo', ['item' => $todo]);
    }

    public static function delete($id = 0)
    {
        global $wpdb;

        $id = intval($id);

        $where = ['id' => $id, 'created_by' => get_current_user_id()];

        $affected = $wpdb->delete($wpdb->prefix . 'eib2bpro_note',  $where);

        if (1 === $affected) {
            eib2bpro_success('', ['after' => ['addClass' => ['container' => '.btnA[data-id=' . $id . ']', 'class' => 'd-none']]]);
        } else {
            eib2bpro_error(esc_html__('Error', 'eib2bpro'));
        }
    }

    public static function save($id = 0, $data = [])
    {
        global $wpdb;

        $id = intval($id);

        if (0 === $id) {
            $data['created_by'] = get_current_user_id();
            $data['list_id'] = -1;
            $data['section_id'] = 0;
            $data['created_at'] = eib2bpro_strtotime('now');
            $data['position'] = time();

            $wpdb->insert($wpdb->prefix . 'eib2bpro_todo', $data, array('%s', '%d', '%d', '%d', '%d',  '%d', '%s', '%d'));
            return $wpdb->insert_id;
        } else {
            $data['updated_at'] = eib2bpro_strtotime('now');
            $where = ['id' => $id];

            if (!\EIB2BPRO\Admin::is_admin()) {
                $where['created_by'] = get_current_user_id();
            }

            $affected = $wpdb->update($wpdb->prefix . 'eib2bpro_todo', $data, $where);
            if (1 === $affected) {
                return $id;
            }
            return 0;
        }
    }

    public static function sort()
    {
        global $wpdb;

        $where = ['created_by' => get_current_user_id()];

        $i = 0;

        foreach ($_POST['ids'] as $id) {
            if (0 < intval($id)) {
                $where['id'] = intval(sanitize_key($id));
                $wpdb->update($wpdb->prefix . 'eib2bpro_todo', ['position' => $i], $where);
                ++$i;
            }
        }

        eib2bpro_success();
    }
}
