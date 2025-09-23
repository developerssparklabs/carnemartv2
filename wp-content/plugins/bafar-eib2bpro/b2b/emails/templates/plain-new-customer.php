<?php

defined('ABSPATH') || exit;

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html(wp_strip_all_tags($email_heading));
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";


$user = get_user_by('login', $user_login);
$user_id = $user->ID;
$user_mail = $user->user_email;
?>

<?php echo "\n\n----------------------------------------\n\n"; ?>
<?php esc_html_e('E-Mail', 'eib2bpro'); ?>: <?php eib2bpro_e($user_mail) ?>
<?php echo "\n\n----------------------------------------\n\n"; ?>
<?php esc_html_e('Type', 'eib2bpro'); ?>: <?php eib2bpro_e(get_the_title(get_user_meta($user_id, 'eib2bpro_registration_regtype', true))) ?>
<?php echo "\n\n----------------------------------------\n\n"; ?>
<?php
$field_ids = wp_parse_id_list(get_user_meta($user_id, 'eib2bpro_customfield_ids', true));
foreach ($field_ids as $field_id) {
    $field = get_post($field_id);
    if ($field) {
        $type = get_post_meta($field->ID, 'eib2bpro_field_type', true);
?>
        <?php eib2bpro_e(get_post_meta($field->ID, 'eib2bpro_field_label', true)) ?>
        <?php
        $value = get_user_meta($user_id, 'eib2bpro_customfield_' . $field->ID, true);
        switch ($type) {
            case 'file':
                $file = wp_get_attachment_url($value);
                echo '<a href="' . esc_url($file) . '" target="_blank">' . esc_html__('View or download the file', 'eib2bpro') . '<a>';
                break;
            default:
                eib2bpro_e($value);
                break;
        } ?>
    <?php } ?>
    <?php echo "\n\n----------------------------------------\n\n"; ?>
<?php } ?>

<?php

// If user is not approved
if ('yes' !== get_user_meta($user_id, 'eib2bpro_user_approved', true)) { ?>
    <p>&nbsp;</p>
    <p><?php esc_html_e('To approve or decline the user, please go to admin panel', 'eib2bpro'); ?></p>
<?php } ?>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if (isset($additional_content)) {
    echo esc_html(wp_strip_all_tags(wptexturize($additional_content)));
    echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post(apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text')));
