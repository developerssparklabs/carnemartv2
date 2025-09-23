<?php

namespace EIB2BPRO\Comments;

defined('ABSPATH') || exit;

/**
 * Ajax router
 */

class Ajax
{

    public static function run()
    {
        $do = eib2bpro_post('do');
        $id = eib2bpro_post('id');

        switch ($do) {

                // Bulk operations
            case 'bulk':

                if ('' === $id) {
                    wp_die(-1);
                }

                $ids = explode(',', $id);

                if (!is_array($ids) or (0 === count($ids))) {
                    wp_die(-2);
                }

                $success = array();

                $ids = array_map('absint', $ids);

                foreach ($ids as $id) {

                    if (!$comment = get_comment($id)) {
                        return eib2bpro_error(esc_html__('Error', 'eib2bpro'));
                        break;
                    }

                    if (!current_user_can('edit_comment', $comment->comment_ID)) {
                        wp_die(-1);
                    }

                    if ('approve' === eib2bpro_post('state')) {
                        $result = wp_set_comment_status($id, 'approve');
                        $success[] = array('id' => $id, 'state' => 'approve');
                    }

                    if ('unapprove' === eib2bpro_post('state')) {
                        $result = wp_set_comment_status($id, 'hold');
                        $success[] = array('id' => $id, 'state' => 'unapprove');
                    }

                    if ('trash' === eib2bpro_post('state')) {
                        $result = wp_delete_comment($id);
                        $success[] = array('id' => $id, 'state' => 'trash');
                    }

                    if ('restore' === eib2bpro_post('state')) {
                        $result = wp_untrash_comment($id);
                        $success[] = array('id' => $id, 'state' => 'trash');
                    }

                    if ('deleteforever' === eib2bpro_post('state')) {
                        $result = wp_delete_comment($id, 'true');
                        $success[] = array('id' => $id, 'state' => 'trash');
                    }
                }

                return eib2bpro_success('Comments status has been changed', array('id' => $success));

                break;

                // Search
            case 'search':

                $filter['search'] = eib2bpro_post('q');
                $filter['status'] = eib2bpro_post('status', 'all');

                if (!$filter['search']) {
                    wp_die();
                }

                echo \EIB2BPRO\Comments\Main::index($filter);
                wp_die();

                break;


                // Change status of comment
            case 'status':

                $result = false;
                $state = eib2bpro_post('state');
                $id = absint($id);


                if (!$comment = get_comment($id)) {
                    wp_die(-1);
                }

                if (!current_user_can('edit_comment', $comment->comment_ID)) {
                    wp_die(-1);
                }


                if ('approve' === $state) {
                    $undostate = 'approve';
                    $result = wp_set_comment_status($id, 'approve');
                    $message = esc_html__('Comment has been approved', 'eib2bpro');
                }

                if ('unapprove' === $state) {
                    $undostate = 'unapprove';
                    $result = wp_set_comment_status($id, 'hold');
                    $message = esc_html__('Comment has been unapproved', 'eib2bpro');
                }

                if ('restore' === $state) {
                    $undostate = 'restore';
                    $result = wp_untrash_comment($id);
                    $message = esc_html__('Comment has been restored', 'eib2bpro');
                }

                if ('spam' === $state) {
                    $undostate = 'spam';
                    $result = wp_spam_comment($id);
                    $message = 'Comment has been flagged as spam &mdash; <a class="eib2bpro-AjaxButton" href="javascript:;" data-id="' . $id . '" data-do="status" data-state="unspam">Undo</a>';
                }

                if ('unspam' === $state) {
                    $undostate = 'unspam';
                    $result = wp_unspam_comment($id);
                    $message = esc_html__('Comment has been restored', 'eib2bpro');
                }

                if ('untrash' === $state) {
                    $undostate = 'untrash';
                    $result = wp_untrash_comment($id);
                    $message = esc_html__('Comment has been restored', 'eib2bpro');
                }

                if ('trash' === $state) {
                    $undostate = 'trash';
                    $result = wp_delete_comment($id);
                    $message = 'Comment moved to the trash &mdash; <a class="eib2bpro-AjaxButton" href="javascript:;" data-id="' . $id . '" data-do="status" data-state="untrash">Undo</a>';
                }

                if ('forcedelete' === $state) {
                    $undostate = 'forcedelete';
                    $result = wp_delete_comment($id, 'true');
                    $message = esc_html__('Comment has been deleted forever', 'eib2bpro');
                }


                if (TRUE === $result) {
                    return eib2bpro_success(esc_html__('Comment deleted', 'eib2bpro'), array('id' => $id, 'message' => $message, 'state' => $undostate));
                } else {
                    return eib2bpro_error(esc_html__('Comment can not be deleted', 'eib2bpro'));
                }

                break;
        }
    }
}
