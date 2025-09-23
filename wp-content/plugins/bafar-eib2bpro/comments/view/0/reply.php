<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>


<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Reply', 'eib2bpro') ?></h5>
        </div>
    </div>
</div>


<form action="" method="POST">
    <div class="eib2bpro-app-new-item-content">

        <div class="container" id="eib2bpro-comments--new">
            <div class="row">
                <?php echo wp_kses_post($comment->comment_content); ?>
            </div>
            <div class="row eib2bpro-Info">
                <br>
                <?php echo esc_html($comment->comment_author); ?> &mdash;
                <?php echo date_i18n("d F Y, H:i", strtotime($comment->comment_date)); ?>
            </div>
            <div class="row">
                <div class="eib2bpro-Label">
                    <hr>
                </div>
                <div class="eib2bpro-Label"><strong><?php esc_html_e('Your Reply', 'eib2bpro'); ?></strong><br></div>
                <?php wp_editor("", "reply", $settings = array('teeny' => true, 'tinymce' => false, 'quicktags' => array('buttons' => 'strong,em,del,ul,ol,li,close'), 'media_buttons' => false)); ?>
            </div>
            <div class="row">
                <div class="eib2bpro-Label"></div>
                <?php if ("1" === $comment->comment_approved) { ?>
                    <input class="btn btn-primary" type="submit" name="submit" value="<?php esc_attr_e('Submit reply', 'eib2bpro'); ?>">
                <?php } else { ?>
                    <input class="btn btn-primary" type="submit" name="submit" value="<?php esc_attr_e('Approve comment and submit reply', 'eib2bpro'); ?>">
                <?php }  ?>
            </div>
        </div>
</form>

<p>&nbsp;</p>