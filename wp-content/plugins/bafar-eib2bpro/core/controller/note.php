<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;

class Note
{
    public static function run()
    {
        // nothing
    }

    public static function render($resource_type = '', $layout = 'mini')
    {
        $all = self::all($resource_type);
        echo eib2bpro_view('core', 0, 'shared.note.' . $layout, ['all' => $all]);
    }

    public static function all($resource_type = 'private', $args = [])
    {
        global $wpdb;
        $created_by = get_current_user_id();

        $all = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}eib2bpro_note WHERE resource_type=%s AND created_by = %d ORDER BY position ASC",
                $resource_type,
                $created_by
            ),
            ARRAY_A
        );

        array_push($all,  ['id' => 0, 'content' => '', 'created_by' => get_current_user_id(), 'color' => '000000', 'collapsed' => 1, 'resource_type' => 'private']);

        return $all;
    }

    public static function get($id = 0)
    {
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
            $data['created_at'] = eib2bpro_strtotime('now');
            $data['position'] = time();

            $wpdb->insert($wpdb->prefix . 'eib2bpro_note', $data, array('%s', '%s'));
            return $wpdb->insert_id;
        } else {
            $data['updated_at'] = eib2bpro_strtotime('now');
            $where = ['id' => $id];

            if (!\EIB2BPRO\Admin::is_admin()) {
                $where['created_by'] = get_current_user_id();
            }

            $affected = $wpdb->update($wpdb->prefix . 'eib2bpro_note', $data, $where);
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
                $wpdb->update($wpdb->prefix . 'eib2bpro_note', ['position' => $i], $where);
                ++$i;
            }
        }

        eib2bpro_success();
    }
}
