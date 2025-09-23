<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="d-flex flex-nowrap">
    <?php foreach ($results as $item) { ?>
        <?php if ('1' === $item['active']) { ?>
            <div class="eib2bpro-I">
                <h2><?php echo (isset($item['is_price']) ? '<span class="woocommerce-Price-currencySymbol">' . get_woocommerce_currency_symbol() . '</span>' . wc_price($item['count'], array('decimals' => 0)) : $item['count']) ?></h2>
                <h4><?php echo esc_html($item['title']) ?></h4>
            </div>
        <?php } ?>
    <?php } ?>
</div>