<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<?php if (!$ajax) { ?>
    <?php echo eib2bpro_view('core', 0, 'shared.index.header-ei'); ?>
    <?php
    $stars_avg = absint($stars[0]->average);
    $stars_avg_f = sprintf("%.1f", (float)$stars[0]->average);
    $stars_div = '
  <div class="eib2bpro-Stars eib2bpro-StarsBig">
  <span class="eib2bpro-StarsUp">' . str_repeat('★ ', $stars_avg) . '</span>
  <span class="eib2bpro-StarsDown">' . str_repeat('★ ', 5 - $stars_avg) . '</span>
  </div>
  <div class="eib2bpro-StarsInfo">' . sprintf(esc_html__('You have %1$s average in %2$s reviews', "eib2bpro"), $stars_avg_f, $stars[0]->cnt) . "</div>";
    ?>

    <?php echo eib2bpro_view('core', 0, 'shared.index.header-page', array('type' => 1, 'title' => esc_html__('Comments', 'eib2bpro'), 'description' => '', 'buttons' => $stars_div)); ?>
    <?php echo eib2bpro_view('comments', 0, 'nav', array('count' => $counts)) ?>

    <div id="eib2bpro-comments-2">

        <div class="eib2bpro-Searching<?php if ('' === eib2bpro_get('s', '')) {
                                    echo " closed";
                                } ?>">
            <div class="eib2bpro-Searching_In">
                <input type="text" class="form-control eib2bpro-Search_Input" placeholder="<?php esc_html_e('Search in comments...', 'eib2bpro'); ?>" value="<?php echo esc_attr(eib2bpro_get('s')); ?>"></span>
            </div>
        </div>

        <div class=" eib2bpro-GP eib2bpro-List_M1 eib2bpro-Container">
        <?php } ?>

        <div class="eib2bpro-List_M1_Bulk eib2bpro-Bulk">
            <?php if ('trash' === eib2bpro_get('status')) { ?>
                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="restore" href="javascript:;"><?php esc_html_e('Restore comments', 'eib2bpro'); ?></a> <a class="eib2bpro-Button1 eib2bpro-Bulk_Do" data-do="deleteforever" href="javascript:;"><?php esc_html_e('Delete forever', 'eib2bpro'); ?></a>
            <?php } else { ?>
                <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Approve" data-do="approve" href="javascript:;"><?php esc_html_e('Approve selected comments', 'eib2bpro'); ?></a> <a class="eib2bpro-Button1 eib2bpro-Bulk_Do eib2bpro-Bulk_Unapprove" data-do="unapprove" href="javascript:;"><?php esc_html_e('Unapprove selected comments', 'eib2bpro'); ?></a> <a class="eib2bpro-Button1  eib2bpro-Bulk_Do" data-do="trash" href="javascript:;"><?php esc_html_e('Delete them', 'eib2bpro'); ?></a>
            <?php } ?>
            <a class="eib2bpro-Select_All float-right" data-state='select' href="javascript:;"><?php esc_html_e('Select All', 'eib2bpro'); ?></a>
        </div>
        <div class="eib2bpro-Comments_Container">

            <?php if (0 === count($comments)) { ?>
                <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center">
                    <div>
                        <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
                    </div>
                </div>
            <?php } else { ?>

                <?php
                $comment_ids = array(-1);
                foreach ($comments as $comment) {
                    $comment_ids[] = $comment->comment_ID;
                ?>
                    <div class="btnA eib2bpro-Item collapsed" id="item_<?php echo esc_attr($comment->comment_ID) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($comment->comment_ID) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($comment->comment_ID) ?>">
                        <div class="liste  row d-flex align-items-center">
                            <div class="eib2bpro-Checkbox_Hidden">
                                <input type="checkbox" class="eib2bpro-Checkbox eib2bpro-StopPropagation" data-id='<?php echo esc_attr($comment->comment_ID) ?>' data-state='s<?php echo esc_attr($comment->comment_approved) ?>'>
                            </div>

                            <div class="col col-sm-1 text-center eib2bpro-Col_Post">
                                <?php if ($thumbnail = get_the_post_thumbnail_url($comment->comment_post_ID)) { ?>
                                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($comment->comment_post_ID)); ?>" class="eib2bpro-Product_Image eib2bpro-Product_Image_Com">
                                <?php } ?>
                                <div class="eib2bpro-Title"><?php echo esc_attr(get_post($comment->comment_post_ID)->post_title); ?></div>
                            </div>

                            <div class="col col-sm-2  eib2bpro-CommentInfo">
                                <div class="eib2bpro-CommentAuthor"><?php echo esc_html($comment->comment_author) ?></div>
                                <div class=""><?php echo esc_html($comment->comment_author_email) ?></div>
                                <div class="eib2bpro-CommentDate"><?php echo date("d M, Y H:i", strtotime($comment->comment_date)) ?></div>
                                <div class="eib2bpro-CommentStatus"><?php
                                                                if ("1" === $comment->comment_approved) {
                                                                    $status = "success";
                                                                } else {
                                                                    $status = "danger";
                                                                }
                                                                ?>
                                    <span class="badge badge-pill badge-<?php echo esc_attr($status); ?>"><?php echo ("1" === $comment->comment_approved) ? esc_html__('APPROVED', 'eib2bpro') : esc_html__('UNAPPROVED', 'eib2bpro') ?></span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-7 eib2bpro-Col_CommentInfo">
                                <?php if (0 < $comment->comment_parent) { ?>
                                    <div class="eib2bpro-ThisIsAReply"><?php sprintf(esc_html__('This is a reply to <a href="%1$s" class="eib2bpro-panel"></a> Comment #%2$s', "eib2bpro"), esc_url(admin_url('comment.php?action=editcomment&c=' . esc_attr($comment->comment_parent)), esc_attr($comment->comment_parent))) ?></div>
                                <?php } ?>

                                <?php $stars = intval(get_comment_meta($comment->comment_ID, 'rating', true)); ?>
                                <div class="eib2bpro-Stars">
                                    <span class="eib2bpro-StarsUp"><?php echo str_repeat('★ ', $stars); ?></span>
                                    <span class="eib2bpro-StarsDown"><?php echo str_repeat('★ ', 5 - $stars); ?></span>
                                </div>
                                <br>

                                <?php echo wp_kses_post($comment->comment_content); ?>
                                <?php if (isset($replies[$comment->comment_ID])) { ?>
                                    <a href="javascript:;" class="eib2bpro-Replies" data-id="<?php echo esc_attr($comment->comment_ID); ?>"><span class="dashicons dashicons-format-status eib2bpro-Comments_Icon">&nbsp; </span><?php esc_html_e('You replied it &mdash; See', 'eib2bpro'); ?>
                                    </a>
                                    <?php foreach ($replies[$comment->comment_ID] as $reply) { ?>
                                        <div class="eib2bpro-Reply eib2bpro-Reply_<?php echo esc_attr($comment->comment_ID); ?>">
                                            <div id='item_<?php echo esc_attr($reply['comment_ID']) ?>'>
                                                <div class="eib2bpro-Reply_Content"><?php echo wp_kses_post($reply['comment_content']) ?></div>
                                                <div class="eib2bpro-Reply_Author">
                                                    &mdash;<br> <?php esc_html_e('Replied by', 'eib2bpro'); ?>
                                                    <strong><?php echo esc_html($reply['comment_author']) ?></strong> -
                                                    <span class="eib2bpro-Reply_Date"><?php echo date_i18n("d M, Y H:i", strtotime($reply['comment_date'])) ?></span>
                                                </div>
                                                <div class="eib2bpro-Reply_Actions">
                                                    <a href="<?php echo esc_url(admin_url('comment.php?action=editcomment&c=' . esc_attr($reply['comment_ID']))) ?>" class="eib2bpro-panel"><?php esc_html_e('Edit this reply', 'eib2bpro'); ?></a> -
                                                    <a href="javascript:;" data-id="<?php echo esc_attr($reply['comment_ID']) ?>" data-do='status' data-state='forcedelete' class="eib2bpro-AjaxButton"><?php esc_html_e('Delete this reply forever', 'eib2bpro'); ?></a>
                                                </div>
                                            </div>

                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>

                            <div class="d-none d-sm-block col col-sm-2 text-right">
                                <?php if ('1' === $comment->comment_approved) { ?>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='unapprove' class="eib2bpro-AjaxButton eib2bpro-Button1 eib2bpro-MainButton eib2bpro-CommentStatusButton eib2bpro-NoH eib2bpro-CommentStatusButton_Unapprove"><?php esc_html_e('Unapprove', 'eib2bpro'); ?></a>
                                <?php } ?>
                                <?php if ('0' === $comment->comment_approved) { ?>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='approve' class="eib2bpro-AjaxButton eib2bpro-Button1 eib2bpro-MainButton eib2bpro-CommentStatusButton eib2bpro-NoH eib2bpro-CommentStatusButton_Approve"><?php esc_html_e('Approve', 'eib2bpro'); ?></a>
                                <?php } ?>
                            </div>

                            <div class="col col-sm-1  eib2bpro-Actions text-right eib2bpro-Display_None">
                                <span class="dashicons dashicons-arrow-down-alt2 bthidden1" aria-hidden="true"></span>
                                <span class="dashicons dashicons-no-alt bthidden" aria-hidden="true"></span>
                            </div>

                        </div>
                        <div class="collapse col-xs-12 col-sm-12 col-md-12 text-right" id="item_d_<?php echo esc_attr($comment->comment_ID) ?>">
                            <div class="eib2bpro-Item_Details">
                                <?php if ('trash' === $comment->comment_approved) { ?>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='restore' class="eib2bpro-AjaxButton"><?php esc_html_e('Restore', 'eib2bpro'); ?></a>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='forcedelete' class="eib2bpro-AjaxButton"><?php esc_html_e('Delete Forever', 'eib2bpro'); ?></a>
                                <?php } ?>

                                <?php if ('spam' === $comment->comment_approved) { ?>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='restore' class="eib2bpro-AjaxButton"><?php esc_html_e('Not Spam', 'eib2bpro'); ?></a>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='forcedelete' class="eib2bpro-AjaxButton"><?php esc_html_e('Delete Forever', 'eib2bpro'); ?></a>
                                <?php } ?>

                                <?php if ('1' === $comment->comment_approved) { ?>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='unapprove' class="d-inline d-md-none  eib2bpro-AjaxButton eib2bpro-Button1 eib2bpro-MainButton eib2bpro-CommentStatusButton eib2bpro-CommentStatusButton_Red"><?php esc_html_e('Unapprove', 'eib2bpro'); ?></a>
                                    <a href="<?php echo eib2bpro_secure_url('comments', esc_attr($comment->comment_ID), array('action' => 'reply', 'id' => esc_attr($comment->comment_ID), 'post' => esc_attr($comment->comment_post_ID))); ?>" class="eib2bpro-StopPropagation eib2bpro-panel"><?php esc_html_e('Reply', 'eib2bpro'); ?></a>
                                    <a href="<?php echo esc_url(admin_url('comment.php?action=editcomment&c=' . $comment->comment_ID)) ?>" class="eib2bpro-StopPropagation eib2bpro-panel" data-hash="<?php echo esc_attr($comment->comment_ID) ?>"><?php esc_html_e('Edit', 'eib2bpro'); ?></a>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='spam' class="eib2bpro-HideMe eib2bpro-AjaxButton "><?php esc_html_e('Spam', 'eib2bpro'); ?></a>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='trash' class="eib2bpro-HideMe eib2bpro-AjaxButton text-danger"><?php esc_html_e('Delete', 'eib2bpro'); ?></a>
                                <?php } ?>

                                <?php if ('0' === $comment->comment_approved) { ?>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='approve' class="d-inline d-md-none eib2bpro-AjaxButton eib2bpro-Button1 eib2bpro-MainButton eib2bpro-CommentStatusButton eib2bpro-CommentStatusButton_Green"><?php esc_html_e('Approve', 'eib2bpro'); ?></a>
                                    <a href="<?php echo eib2bpro_secure_url('comments', $comment->comment_ID, array('action' => 'reply', 'id' => esc_attr($comment->comment_ID), 'post' => esc_attr($comment->comment_post_ID))); ?>" class="eib2bpro-StopPropagation  eib2bpro-panel"><?php esc_html_e('Reply', 'eib2bpro'); ?></a>
                                    <a href="<?php echo esc_url(admin_url('comment.php?action=editcomment&c=' . $comment->comment_ID)) ?>" class="eib2bpro-StopPropagation eib2bpro-HideMe eib2bpro-panel"><?php esc_html_e('Edit', 'eib2bpro'); ?></a>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='spam' class="eib2bpro-HideMe eib2bpro-AjaxButton"><?php esc_html_e('Spam', 'eib2bpro'); ?></a>
                                    <a href="javascript:;" data-id="<?php echo esc_attr($comment->comment_ID) ?>" data-do='status' data-state='trash' class="eib2bpro-HideMe eib2bpro-AjaxButton text-danger"><?php esc_html_e('Delete', 'eib2bpro'); ?></a>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                <?php } ?>
        </div>
        <?php if ($count && $count > 0) { ?>
            <?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $count, 'per_page' => $per_page, 'page' => intval(eib2bpro_get('pg', 0)), 'url' => remove_query_arg('pg', eib2bpro_admin('comments', array('status' => eib2bpro_get('status'), 's' => $search))))); ?>
        <?php } ?>
        </div>
    <?php } ?>



    <?php if (!$ajax) { ?>
    </div>
<?php } ?>