<?php

defined('ABSPATH') || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
do_action('woocommerce_email_header', $email_heading, $email);

$text_align = 'left';
?>

<div style="margin-bottom: 40px;">

    <h2><?php esc_html_e('Details', 'eib2bpro'); ?></h2>
    <table class="td" cellspacing="0" cellpadding="6" border="1" style="color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
        <tbody>
            <tr class="item">
                <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                    <?php esc_html_e('Customer', 'eib2bpro'); ?>
                </td>
                <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                    <?php
                    $user_mail = '';
                    if (0 < ($customer_id = get_post_meta($post_id, 'eib2bpro_customer_id', true))) {
                        $customer = get_userdata($customer_id);
                        if ($customer) {
                            eib2bpro_e(sprintf('%s %s (%s)', $customer->first_name, $customer->last_name, $customer->user_email));
                            $user_mail = $customer->user_email;
                        }
                    } else {
                        eib2bpro_e(get_post_meta($post_id, 'eib2bpro_customer_email', true) ?: esc_html_e('Visitor', 'eib2bpro'));
                        $user_mail = get_post_meta($post_id, 'eib2bpro_customer_email', true) ?: '';
                    }
                    ?>
                </td>
            </tr>

            <?php
            $field_ids = wp_parse_id_list(get_post_meta($post_id, 'eib2bpro_field_ids', true));
            if ($field_ids) {
                foreach ($field_ids as $field_id) {
            ?>
                    <tr class="item">
                        <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                            <?php eib2bpro_e(get_post_meta($post_id, 'eib2bpro_field_' . $field_id . '_title', true)); ?>
                        </td>
                        <td class="td" style="color: #636363; border: 1px solid #e5e5e5; padding: 12px; text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                            <?php $values = get_post_meta($post_id, 'eib2bpro_field_' . $field_id, true);
                            if (is_array($values)) {
                                eib2bpro_e(implode(', ', $values));
                            } elseif (stripos($values, '://') !== false) {
                                echo '<a href="' . esc_url($values) . '" class="pl-0" target="_blank">' . esc_html__('View file', 'eib2bpro') . '</a>';
                            } else {
                                eib2bpro_e(get_post_meta($post_id, 'eib2bpro_field_' . $field_id, true) ?: '-');
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
    </table>
    <p>&nbsp;</p>
    <h2><?php esc_html_e('Products', 'eib2bpro'); ?></h2>
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <th class="td" scope="col" style="text-align:center;"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $products = get_post_meta($post_id, 'eib2bpro_products', true);
            if (is_array($products) && !empty($products)) {
                $margin_side = is_rtl() ? 'left' : 'right';
                foreach ($products as $item_id => $item) {
                    $product       = wc_get_product($item_id);
                    $image         = '';
            ?>
                    <tr>
                        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                            <?php
                            // Show title/image etc.
                            $image = get_the_post_thumbnail_url($product->get_id(), array(150, 150));
                            if ($image) {
                                echo '<img src="' . esc_url_raw($image) . '" title="' . esc_attr($product->get_title()) . '" class="eib2bpro-Product_Image" style="max-height:50px;max-width:150px;" >';
                            }
                            // Product name.
                            echo wp_kses_post($product->get_name());

                            if (isset($item['variation']) || !empty($item['variation'])) {
                                foreach ($item['variation'] as $taxonomy => $taxonomy_value) {
                                    $taxonomy = str_replace('attribute_', '', $taxonomy);
                                    echo '<br><span class="text-muted font-12">' . esc_html(wc_attribute_label($taxonomy, $product)) . ': ' . esc_html($taxonomy_value) . "</span>";
                                }
                            }

                            ?>
                        </td>
                        <td class="td" style="text-align:center; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                            <?php
                            echo wp_kses_post($item['qty']);
                            ?>
                        </td>

                    </tr>
            <?php }
            }
            ?>
        </tbody>
    </table>
</div>
<style>
    <?php
    $eib2bpro_base      = get_option('woocommerce_email_base_color');
    $eib2bpro_base_text = wc_light_or_dark($eib2bpro_base, '#202020', '#ffffff');
    ?>
</style>
<p>
    <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'quote']) ?>#<?php echo esc_attr($post_id) ?>" style="background-color:<?php eib2bpro_a($eib2bpro_base) ?>;color:<?php eib2bpro_a($eib2bpro_base_text) ?>;padding:15px 20px; border-radius:5px;text-decoration:none;"><strong><?php esc_html_e('View quote requests', 'eib2bpro'); ?></strong></a>
</p>

<p>&nbsp;</p>

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
