<?php

namespace EIB2BPRO\Comments;

defined('ABSPATH') || exit;

/**
 * Main functions
 */

class Main extends \EIB2BPRO\Comments
{

    /**
     * Starts everything
     *
     * @return void
     */

    public static function run()
    {
        self::route();
    }

    /**
     * Router for sub pages
     *
     * @return void
     */

    private static function route()
    {
        switch (eib2bpro_get('action')) {

            case 'reply':
                self::reply();
                break;

            case 'hide':
                die('<script>"use strict"; if (self!==top && window.parent.EIB2BPRO_Window !== null && window.parent.EIB2BPRO_Window !== undefined) { window.parent.panel.slideReveal("hide"); }</script> ');
                break;

            default:
                self::index();
                break;
        }
    }

    public static function index($filter = false)
    {
        $filter = self::filter($filter);

        $comment_status = $filter['status'];

        switch ($mode = (!empty($filter['mode']) ? absint($filter['mode']) : parent::app('mode'))) {

                // 99: Woocommerce Native
            case 99:
                if (!\EIB2BPRO\Admin::is_full()) {
                    eib2bpro_frame(admin_url('edit-comments.php'));
                } else {
                    wp_redirect(admin_url('edit-comments.php'));
                }
                break;

                // 1-2: Standart
            case 0:
            case 1:
            case 2:
            case 95:

                if (!in_array($comment_status, array('all', '0', '1', 'spam', 'trash'))) {
                    $comment_status = 'all';
                }

                $c_status = str_replace(array('all', '0', '1'), array('total_comments', 'moderated', 'approved'), $comment_status);

                $filter['status'] = $comment_status;

                $comments = self::get_comments($filter);

                $ids = array_map(function ($e) {
                    return is_object($e) ? $e->comment_ID : $e['comment_ID'];
                }, $comments);

                $sub_args = array('parent__in' => $ids);
                $_replies = self::get_comments($sub_args);

                $replies = array();

                if (!empty($_replies)) {
                    foreach ($_replies as $comment) {
                        $replies[$comment->comment_parent][] = array(
                            'comment_ID' => $comment->comment_ID,
                            'comment_author' => $comment->comment_author,
                            'comment_date' => $comment->comment_date,
                            'comment_content' => $comment->comment_content,
                        );
                    }
                }

                $comments_count = wp_count_comments();

                $stars = self::get_stars();

                unset($filter['offset']);

                $filter['count'] = true;
                $comments_count_display = self::get_comments($filter);

                if (95 === $mode) {
                    $invalid = true;
                } else {
                    echo eib2bpro_view(self::app('name'), self::app('mode'), 'list', array('stars' => $stars, 'count' => $comments_count_display, 'counts' => $comments_count, 'per_page' => $filter['number'], 'comments' => $comments, 'replies' => $replies, 'search' => isset($filter['search']) ? $filter['search'] : '', 'ajax' => eib2bpro_is_ajax()));
                }
                break;
        }
    }

    /**
     * Prepare filter array for query
     *
     * @param mixed $filter array of filter or false
     * @return array           new filter array
     */

    public static function filter($filter = false)
    {
        if (!$filter) {
            $filter['status'] = "all";
            $filter['offset'] = 0;
            $filter['page'] = 1;
        }

        $filter['number'] = !isset($filter['number']) ? absint(eib2bpro_option('perpage_' . eib2bpro_get('app', 'default'), 10)) : 10;

        if (eib2bpro_get('go', null)) {
            $filter['mode'] = 95;
        }

        if ('' !== eib2bpro_get('status', '')) {
            $filter['status'] = eib2bpro_get('status', null);
        }

        if ('-1' === eib2bpro_get('status', '')) {
            $filter['status'] = 0;
        }


        if (eib2bpro_get('pg', null)) {
            $filter['offset'] = (intval(eib2bpro_get('pg', 1)) - 1) * $filter['number'];
        }

        if ('' !== eib2bpro_get('s', '')) {
            $filter['search'] = eib2bpro_get('s', '');
        }

        if (eib2bpro_get('orderby')) {
            if (false !== strpos(eib2bpro_get('orderby', ''), 'meta_')) {
                $filter['orderby'] = "meta_value_num";
                $filter['meta_key'] = sanitize_sql_orderby(str_replace('meta_', '', eib2bpro_get('orderby', '')));
            } else {
                $filter['orderby'] = sanitize_sql_orderby(eib2bpro_get('orderby', ''));
            }

            $filter['order'] = 'ASC' === eib2bpro_get('order', 'ASC') ? 'ASC' : 'DESC';
        }


        if (!in_array($filter['status'], array('all', '0', '1', 'spam', 'trash'))) {
            $filter['status'] = 'all';
        }

        if ('trash' !== $filter['status']) {

            // Get only main comments
            $filter['parent__in'] = array(0);
        }
        return $filter;
    }


    /**
     * Reply to a comment
     *
     * @return void
     */

    private static function reply()
    {

        $id      = absint(eib2bpro_get('id', 0));
        $post_id = absint(eib2bpro_get('post', 0));

        $post    = get_post($post_id);

        if (!$post) {
            wp_die(-1);
        }

        if (!current_user_can('edit_post', $post_id)) {
            wp_die(-1);
        }

        if (!$comment = get_comment($id)) {
            wp_die(-1);
        }

        if (!current_user_can('edit_comment', $comment->comment_ID)) {
            wp_die(-2);
        }

        if ($_POST) {

            $user = wp_get_current_user();
            if ($user->exists()) {

                $commentdata = array(
                    'comment_post_ID'      => $post->ID,
                    'comment_author'       => wp_slash($user->display_name),
                    'comment_author_email' => wp_slash(sanitize_email($user->user_email)),
                    'comment_author_url'   => wp_slash(esc_url_raw($user->user_url)),
                    'comment_content'      => wp_kses_data($_POST['reply']),
                    'comment_type'         => '',
                    'comment_parent'       => $id,
                    'user_id'              => wp_slash($user->ID),
                );

                $comment_id = wp_new_comment($commentdata);

                if (1 !== $comment->comment_approved) {
                    wp_set_comment_status($id, 'approve');
                }

                // Close sidebar
                echo '<script>"use strict"; window.parent.panel.slideReveal("hide");window.parent.location.reload(true);</script>';
            } else {
                wp_die(-2);
            }
        }

        echo eib2bpro_view('comments', 0, 'reply',  array('comment' => $comment));
    }

    /**
     * Query for get comments
     *
     * @param array $args Parameters for query
     * @return array
     */

    public static function get_comments($args = array())
    {

        // The comment query
        $comments_query = new \WP_Comment_Query;
        $comments = $comments_query->query($args);

        return $comments;
    }


    /**
     * Stars and comment count info
     *
     * @return array cnt = comment count, avarage = avarage of stars
     */

    private static function get_stars()
    {
        global $wpdb;

        $query = $wpdb->prepare("SELECT count(*) AS cnt, AVG(meta_value) as average FROM {$wpdb->prefix}commentmeta WHERE meta_key = %s", 'rating');
        $stars = $wpdb->get_results($query);

        return $stars;
    }
}
