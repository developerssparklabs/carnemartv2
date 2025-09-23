<?php defined('ABSPATH') || exit; ?>
<div class="eib2bpro-edit-screen">
    <div class="container-fluid">
        <div class="row">
            <div class="eib2bpro-app-new-item-head">
                <h5 class="mb-0"><?php esc_html_e('Groups', 'eib2bpro') ?></h5>
            </div>
        </div>
    </div>

    <?php eib2bpro_form(['do' => 'edit-group', 'list' => eib2bpro_get('id', 0)]); ?>

    <input name="id" value="<?php echo eib2bpro_clean($id, '0') ?>" type="hidden">

    <div class="eib2bpro-app-new-item-content">
        <div class="container-fluid">
            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Name', 'eib2bpro'); ?></label>
                    <?php eib2bpro_ui('input', 'title', get_the_title($id), ['class' => 'eib2bpro-autofocus', 'attr' => 'required']) ?>
                </div>
            </div>

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Display prices', 'eib2bpro'); ?></label>
                    <input type="radio" value="default" name="display_price" id="rb2" class="eib2bpro_radio_selector  eib2bpro-radio-2" <?php checked('default', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_display_price_tax', true), 'default')) ?> />
                    <label for="rb2" class="eib2bpro-radio-2"><?php esc_html_e('Default', 'eib2bpro'); ?></label>
                    <input type="radio" value="incl" name="display_price" id="rb1" class="eib2bpro_radio_selector  eib2bpro-radio-2" <?php checked('incl', get_post_meta($id, 'eib2bpro_display_price_tax', true)) ?> />
                    <label for="rb1" class="eib2bpro-radio-2"><?php esc_html_e('Include Tax', 'eib2bpro'); ?></label>
                    <input type="radio" value="excl" name="display_price" id="rb3" class="eib2bpro_radio_selector  eib2bpro-radio-2" <?php checked('excl', get_post_meta($id, 'eib2bpro_display_price_tax', true)) ?> />
                    <label for="rb3" class="eib2bpro-radio-2"><?php esc_html_e('Exclude Tax ', 'eib2bpro'); ?></label>
                </div>
            </div>

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Payment Methods', 'eib2bpro'); ?></label>

                    <?php
                    $payment_methods = WC()->payment_gateways->payment_gateways();
                    foreach ($payment_methods as $payment_method) {
                        if ('yes' === $payment_method->enabled) {
                    ?>
                            <div class="pt-2">
                                <?php eib2bpro_ui('onoff', 'payment_methods_' . esc_attr($payment_method->id), eib2bpro_clean2(get_post_meta($id, 'eib2bpro_payment_method_' . $payment_method->id, true), 1), ['class' => 'switch-sm']) ?>
                                <?php echo esc_html($payment_method->title); ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>


            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Shipping Methods', 'eib2bpro'); ?></label>

                    <?php

                    $shipping_methods = \EIB2BPRO\B2b\Site\Shipping::get_all();
                    foreach ($shipping_methods as $shipping_method) {
                        if ('yes' === $shipping_method->enabled) {
                    ?>
                            <div class="pt-2">
                                <?php eib2bpro_ui('onoff', 'shipping_methods_' . esc_attr($shipping_method->id) . '_' . esc_attr($shipping_method->instance_id), eib2bpro_clean2(get_post_meta($id, 'eib2bpro_shipping_methods_' . $shipping_method->id . '_' . $shipping_method->instance_id, true), 1), ['class' => 'switch-sm']) ?>
                                <?php echo "<strong>" . esc_html($shipping_method->eib2bpro_zone_name) . ": </strong>" . esc_html(eib2bpro_clean($shipping_method->title, $shipping_method->method_title)); ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="eib2bpro-app-new-item-row col-12">
                    <label><?php esc_html_e('Show a message at product pages (optional)', 'eib2bpro'); ?></label>
                    <textarea name="product_message" class="form-control w-100"><?php echo eib2bpro_r(wp_kses_post(get_post_meta($id, 'eib2bpro_product_message', true))) ?></textarea>
                </div>
            </div>

            <div class="row text-right pt-4">
                <div class="col-12 text-right pr-5">
                    <?php eib2bpro_save() ?>
                </div>
            </div>
        </div>
    </div>

    </form>
</div>