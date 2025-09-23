<?php

defined('ABSPATH') || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
*/
do_action('woocommerce_email_header', $email_heading, $email);

$text_align = 'left';
?>

<p>
    <?php echo wp_kses_post(__('We have an offer for you!', 'eib2bpro')); ?>
</p>
<p>&nbsp;</p>
<h2>
    <?php esc_html_e('Offer No:', 'eib2bpro'); ?> #<?php eib2bpro_a($post_id) ?>
</h2>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <thead>
        <tr>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr($text_align); ?>;"><?php esc_html_e('Product', 'woocommerce'); ?></th>
            <th class="td" scope="col" style="text-align:center;"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
            <th class="td" scope="col" style="text-align:center;"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = 0;
        $products = get_post_meta($post_id, 'eib2bpro_products', true);
        if (is_array($products) && !empty($products)) {
            $margin_side = is_rtl() ? 'left' : 'right';
            foreach ($products as $_product) {
                $product       = wc_get_product($_product['id']);
                $image         = '';
                if (!$product) {
                    continue;
                }
                $_product['price'] = wc_format_decimal($_product['price']);
                $_product['price'] = EIB2BPRO\B2b\Site\Offers::fix_price_by_tax($_product['price'], false); ?>

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
                        echo wp_kses_post($_product['unit']);
                        ?>
                    </td>

                    <td class="td" style="text-align:center; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                        <?php
                        $total += $_product['price'] * $_product['unit'];
                        echo wp_kses_post(wc_price(wc_format_decimal($_product['price']) * $_product['unit']));
                        ?>
                    </td>

                </tr>
        <?php }
        }
        ?>
    </tbody>
    <tfoot>

        <tr>
            <th class="td" scope="row" colspan="2" style="text-align:right; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;"><?php esc_html_e('Total', 'eib2bpro'); ?>:</th>
            <td class="td" style="text-align:center; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo wp_kses_post(wc_price($total)); ?>
            </td>
        </tr>
    </tfoot>
</table>
<br><br>
<?php if (!empty($promo_text = get_post_meta($post_id, 'eib2bpro_promo_text', true))) {
    echo '<div class="eib2bpro_offers_promo_text">' . wp_kses_post(do_shortcode(nl2br($promo_text))) . "<br><br></div>";
} ?>

<?php if ('incl' !== get_option('woocommerce_tax_display_shop')) {
    echo wp_kses_post(esc_html__('* Taxes not included', 'eib2bpro'));
} ?>

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
