<?php

defined('ABSPATH') || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
do_action('woocommerce_email_header', $email_heading, $email); ?>


<?php

$user = get_user_by('login', $user_login);
$user_id = $user->ID;
$user_mail = $user->user_email;
?>

<table class="td" cellspacing="0" cellpadding="6" border="1" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
    <tbody>
        <tr class="item">
            <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                <?php esc_html_e('E-Mail', 'eib2bpro'); ?>
            </td>
            <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                <?php eib2bpro_e($user_mail) ?>
            </td>
        </tr>

        <tr class="item">
            <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                <?php esc_html_e('Type', 'eib2bpro'); ?>
            </td>
            <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                <?php $regtype = intval(get_user_meta($user_id, 'eib2bpro_registration_regtype', true));
                if (0 < $regtype) {
                    eib2bpro_e(get_the_title(get_user_meta($user_id, 'eib2bpro_registration_regtype', true)));
                } else {
                    esc_html_e('B2C', 'eib2bpro');
                } ?>
            </td>
        </tr>

        <?php
        $field_ids = wp_parse_id_list(get_user_meta($user_id, 'eib2bpro_customfield_ids', true));
        foreach ($field_ids as $field_id) {
            $field = get_post($field_id);
            if ($field) {
                $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
        ?>
                <tr class="item">
                    <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                        <?php eib2bpro_e(get_post_meta($field->ID, 'eib2bpro_field_label', true)) ?>
                    </td>
                    <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                        <?php
                        $value = get_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, true);
                        switch ($type) {
                            case 'file':
                                $file = wp_get_attachment_url($value);
                                echo '<a href="' . esc_url($file) . '" target="_blank">' . esc_html__('View file', 'eib2bpro') . '<a>';
                                break;
                            default:
                                eib2bpro_e($value);
                                break;
                        } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
</table>
<?php

// If user is not approved
if ('yes' !== get_user_meta($user_id, 'eib2bpro_user_approved', true)) { ?>
    <p>&nbsp;</p>
    <p><?php esc_html_e('To approve or decline the user, please go to admin panel', 'eib2bpro'); ?></p>
    <p><a href="<?php echo eib2bpro_admin('b2b', []) ?>"><strong><?php esc_html_e('Go to admin panel', 'eib2bpro'); ?></strong></a></p>

<?php } ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if (isset($additional_content) && $additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action('woocommerce_email_footer', $email);
