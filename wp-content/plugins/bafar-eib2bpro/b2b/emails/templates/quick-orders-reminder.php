<?php

defined('ABSPATH') || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
do_action('woocommerce_email_header', $email_heading, $email);

$text_align = 'left';
?>

<p>
    <?php echo wp_kses_post(sprintf(esc_html__('We are sending you this e-mail because you want us to remind you of your quick order list called "%s".', 'eib2bpro'), get_the_title($post_id))); ?>
</p>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <th class="td" scope="col" style="text-align:center;"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $products = get_post_meta($post_id, 'eib2bpro_quickorder_products', true);
            if (is_array($products) && !empty($products)) {
                $margin_side = is_rtl() ? 'left' : 'right';
                foreach ($products as $item) {
                    $product       = wc_get_product($item['id']);
                    $image         = '';
                    if (!$product) {
                        continue;
                    }
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
    <a href="<?php echo esc_url(add_query_arg(array('id' => $post_id), wc_get_account_endpoint_url(eib2bpro_option('b2b_endpoints_quickorders', 'quick-orders')))) ?>#<?php echo esc_attr($post_id) ?>" style="background-color:<?php eib2bpro_a($eib2bpro_base) ?>;color:<?php eib2bpro_a($eib2bpro_base_text) ?>;padding:15px 20px; border-radius:5px;text-decoration:none;"><strong><?php esc_html_e('Order now', 'eib2bpro'); ?></strong></a>
</p>

<p>&nbsp;</p>

<p>
    <?php esc_html_e('If you don\'t want to receive these reminder emails anymore, please disable the "Set a reminder" option in your account.', 'eib2bpro'); ?>
</p>

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
