<?php

defined('ABSPATH') || exit;

$rules = [
    'change_price' => esc_html__('Adjust Price', 'eib2bpro'),
    'hide_price' => esc_html__('Hide Price', 'eib2bpro'),
    'add_quote_button' => esc_html__('Add Quote Button', 'eib2bpro'),
    'step' => esc_html__('Step', 'eib2bpro'),
    'min_order' => esc_html__('Minimum Order', 'eib2bpro'),
    'max_order' => esc_html__('Maximum Order', 'eib2bpro'),
    'cart_discount' => esc_html__('Cart Discount', 'eib2bpro'),
    'free_shipping' => esc_html__('Free Shipping', 'eib2bpro'),
    'payment_discount' => esc_html__('Payment Method - Discount', 'eib2bpro'),
    'payment_minmax' => esc_html__('Payment Method - Min & Max', 'eib2bpro'),
    'add_fee' => esc_html__('Extra Fee', 'eib2bpro'),
    'tax_exemption' => esc_html__('Tax Exemption (Customer)', 'eib2bpro'),
    'tax_exemption_product' => esc_html__('Tax Exemption (Product)', 'eib2bpro'),
    'change_price_html' => esc_html__('Change Product Page Price Format (Experimental)', 'eib2bpro'),
];
?>
<?php if (2 > count(($items->posts))) { ?>
    <div class="eib2bpro-EmptyTable d-flex align-items-center justify-content-center text-center bg-white eib2bpro-shadow mt-3">
        <div>
            <span class="dashicons dashicons-marker"></span><br><?php esc_html_e('No records found', 'eib2bpro'); ?>
        </div>
    </div>
<?php } ?>

<ol class="eib2bpro-sortable eib2bpro-sortable-rules eib2bpro-rules-list">
    <?php foreach ($items->posts as $item) { ?>
        <li id="eib2bpro-rule-id-<?php eib2bpro_a($item->ID) ?>">
            <div class="btnA eib2bpro-Item collapsed" id="item_<?php echo esc_attr($item->ID) ?>" data-toggle="collapse" data-target="#item_d_<?php echo esc_attr($item->ID) ?>" aria-expanded="false" aria-controls="item_d_<?php echo esc_attr($item->ID) ?>">
                <div class="liste  d-flex align-items-center p-30">

                    <div data-col="name" class="col-4 col-lg-1 col-id-move">
                        <h6 class="m-0">
                            <input type="hidden" name="position[<?php eib2bpro_a($item->ID) ?>]" value="1">
                            <?php eib2bpro_ui('onoff_ajax', 'status', 'publish' === $item->post_status ? 1 : 0, ['app' => 'rules', 'do' => 'change-status', 'id' => $item->ID]) ?>
                        </h6>
                    </div>
                    <div data-col="name" class="col-8 col-lg-9 col-id-name">
                        <h6 class="eib2bpro-rule--name">
                            <?php eib2bpro_e(eib2bpro_clean2(get_the_title($item->ID), esc_html__('Rule #', 'eib2bpro') . $item->ID)) ?>
                        </h6>
                        <div class="text-muted eib2bpro-rule--type"><?php get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? eib2bpro_e($rules[get_post_meta($item->ID, 'eib2bpro_rule_type', true)]) : ''; ?></div>
                    </div>

                    <div data-col="type" class="col col-lg-2 col-id-type text-center">
                        <i class="eib2bpro-os-move eib2bpro-icon-move d-none"></i>
                    </div>

                </div>

                <div class="collapse col-xs-12 col-sm-12 col-md-12 eib2bpro-rule-collapse" id="item_d_<?php echo esc_attr($item->ID) ?>">
                    <?php eib2bpro_form(['app' => 'rules', 'do' => 'save-rule']); ?>
                    <div class="eib2bpro-Item_Details eib2bpro-parent eib2bpro-stop">
                        <input name="id" value="<?php eib2bpro_a($item->ID) ?>" type="hidden">

                        <div class="eib2bpro-rule-group-main eib2bpro-app-new-item-row">
                            <div class="row">
                                <div class="col-12 col-lg-6">

                                    <label><?php esc_html_e('Type', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <select class="form-control eib2bpro-rule-select eib2bpro-rule-type" name="type">
                                            <optgroup label="<?php esc_html_e('Products', 'eib2bpro'); ?>">
                                                <option value="change_price" <?php selected('change_price', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-change-price, .eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions, .eib2bpro-operator-not-in'])) ?>"><?php esc_html_e('Adjust Price', 'eib2bpro'); ?></option>
                                                <option value="hide_price" <?php selected('hide_price', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions, .eib2bpro-operator-not-in'])) ?>"><?php esc_html_e('Hide Price', 'eib2bpro'); ?></option>
                                                <option value="add_quote_button" <?php selected('add_quote_button', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions, .eib2bpro-rule-additional-quote, .eib2bpro-operator-not-in'])) ?>"><?php esc_html_e('Add Quote Button', 'eib2bpro'); ?></option>
                                                <option value="step" <?php selected('step', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group, .eib2bpro-operator-not-in', 'show' => '.eib2bpro-rule-step, .eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Step', 'eib2bpro'); ?></option>
                                            </optgroup>
                                            <optgroup label="<?php esc_html_e('Cart', 'eib2bpro'); ?>">
                                                <option value="min_order" <?php selected('min_order', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-minmax, .eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Minimum order', 'eib2bpro'); ?></option>
                                                <option value="max_order" <?php selected('max_order', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-minmax, .eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Maximum order', 'eib2bpro'); ?></option>
                                                <option value="free_shipping" <?php selected('free_shipping', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group, .eib2bpro-operator-not-in', 'show' => '.eib2bpro-rule-users, .eib2bpro-rule-products, .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Free shipping', 'eib2bpro'); ?></option>
                                                <option value="cart_discount" <?php selected('cart_discount', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-add-fee, .eib2bpro-rule-users,  .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Cart discount', 'eib2bpro'); ?></option>
                                                <option value="add_fee" <?php selected('add_fee', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-add-fee, .eib2bpro-rule-users,  .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Extra fee', 'eib2bpro'); ?></option>
                                            </optgroup>
                                            <optgroup label="<?php esc_html_e('Checkout', 'eib2bpro'); ?>">
                                                <option value="payment_discount" <?php selected('payment_discount', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-payment-discount, .eib2bpro-rule-users, .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Payment Method - Discount', 'eib2bpro'); ?></option>
                                                <option value="payment_minmax" <?php selected('payment_minmax', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-payment-minmax, .eib2bpro-rule-users, .eib2bpro-rule-conditions'])) ?>"><?php esc_html_e('Payment Method - Min & Max', 'eib2bpro'); ?></option>
                                                <option value="tax_exemption" <?php selected('tax_exemption', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-tax-exemption, .eib2bpro-rule-users'])) ?>"><?php esc_html_e('Tax Exemption (Customer)', 'eib2bpro'); ?></option>
                                                <option value="tax_exemption_product" <?php selected('tax_exemption_product', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-tax-exemption, .eib2bpro-rule-products, .eib2bpro-rule-users'])) ?>"><?php esc_html_e('Tax Exemption (Product)', 'eib2bpro'); ?></option>
                                            </optgroup>
                                            <optgroup label="<?php esc_html_e('Advanced', 'eib2bpro'); ?>">
                                                <option value="change_price_html" <?php selected('change_price_html', get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-group', 'show' => '.eib2bpro-rule-change-price-html, .eib2bpro-rule-products, .eib2bpro-rule-users'])) ?>"><?php esc_html_e('Change Product Page Price Format (Experimental)', 'eib2bpro'); ?></option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label><?php esc_html_e('Title', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <input class="form-control eib2bpro-rule-input" name="title" value="<?php eib2bpro_a(get_the_title($item->ID)) ?>">
                                    </div>
                                </div>
                            </div>


                        </div>

                        <!-- RULE: Adjust Price -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-change-price<?php eib2bpro_a(('' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) && 'change_price' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Amount', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <select class="form-control eib2bpro-rule-select" name="eib2bpro-rule-change-price-type">
                                            <option value="fixed_discount" <?php selected('fixed_discount', get_post_meta($item->ID, 'eib2bpro_rule_change_price_type', true)) ?>><?php esc_html_e('Fixed Discount', 'eib2bpro'); ?></option>
                                            <option value="percentage_discount" <?php selected('percentage_discount', get_post_meta($item->ID, 'eib2bpro_rule_change_price_type', true)) ?>><?php esc_html_e('Percentage Discount', 'eib2bpro'); ?></option>
                                            <option value="fixed_price" class="eib2bpro-rule-fixed-price" <?php selected('fixed_price', get_post_meta($item->ID, 'eib2bpro_rule_change_price_type', true)) ?>><?php esc_html_e('Fixed Price', 'eib2bpro'); ?></option>
                                        </select>

                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-change-price-values" name="eib2bpro-rule-change-price-values" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_change_price_values', true)) ?>">

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Step -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-step<?php eib2bpro_a('step' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-4">
                                    <label><?php esc_html_e('Step', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-step-values" name="eib2bpro-rule-step-values" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_step_values', true)) ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label><?php esc_html_e('Min (Optional)', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-step-min" name="eib2bpro-rule-step-min" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_step_min', true)) ?>">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label><?php esc_html_e('Max (Optional)', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-step-max" name="eib2bpro-rule-step-max" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_step_max', true)) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- RULE: MinMax -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-minmax<?php eib2bpro_a(!in_array(get_post_meta($item->ID, 'eib2bpro_rule_type', true), ['min_order', 'max_order']) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Amount', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <select class="form-control eib2bpro-rule-select eib2bpro-rule-minmax-select" name="eib2bpro-rule-minmax-type">
                                            <option value="qty" <?php selected('qty', get_post_meta($item->ID, 'eib2bpro_rule_minmax_type', true)) ?>><?php esc_html_e('Qty', 'eib2bpro'); ?></option>
                                            <option value="value" <?php selected('value', get_post_meta($item->ID, 'eib2bpro_rule_minmax_type', true)) ?>><?php esc_html_e('Value', 'eib2bpro'); ?></option>
                                        </select>

                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-minmax-values" name="eib2bpro-rule-minmax-values" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_minmax_values', true)) ?>">

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Payment Method- Discount -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-payment-discount<?php eib2bpro_a('payment_discount' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Payment Methods', 'eib2bpro'); ?></label>
                                    <div class="eib2bpro-rule-lines">
                                        <div class="d-flex">

                                            <select class="form-control eib2bpro-rule-select eib2bpro-rule-payment-discount-operator" name="eib2bpro-rule-payment-discount-operator">
                                                <option value="in" <?php selected('in', get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_operator', true)) ?>><?php esc_html_e('IN', 'eib2bpro'); ?></option>
                                                <option value="not_in" <?php selected('not_in', get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_operator', true)) ?>><?php esc_html_e('NOT IN', 'eib2bpro'); ?></option>
                                            </select>

                                            <select class="form-control eib2bpro-rule-selectize" name="eib2bpro-rule-payment-discount-values[]" data-for="payments" multiple>
                                                <?php
                                                $selected = wp_parse_list(get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_values', true));
                                                $payment_methods = \WC()->payment_gateways->payment_gateways();
                                                foreach ($payment_methods as $payment_method) {
                                                    if ('yes' === $payment_method->enabled) {
                                                        echo '<option value="' . esc_attr($payment_method->id) . '"' . (in_array($payment_method->id, $selected) ? ' selected' : '') . '>' . esc_html($payment_method->title) . '</option>';
                                                    }
                                                } ?>
                                            </select>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-payment-discount<?php eib2bpro_a('payment_discount' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Amount', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <select class="form-control eib2bpro-rule-select" name="eib2bpro-rule-payment-discount-type">
                                            <option value="decrease_fixed" <?php selected('decrease_fixed', get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_type', true)) ?>><?php esc_html_e('Decrease by fixed price', 'eib2bpro'); ?></option>
                                            <option value="decrease_percentage" <?php selected('decrease_percentage', get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_type', true)) ?>"><?php esc_html_e('Decrease by percentage', 'eib2bpro'); ?></option>
                                            <option value="increase_fixed" <?php selected('increase_fixed', get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_type', true)) ?>><?php esc_html_e('Increase by fixed price', 'eib2bpro'); ?></option>
                                            <option value="increase_percentage" <?php selected('increase_percentage', get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_type', true)) ?>"><?php esc_html_e('Increase by percentage', 'eib2bpro'); ?></option>

                                        </select>

                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-payment-discount-amount" name="eib2bpro-rule-payment-discount-amount" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_payment_discount_amount', true)) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Payment Method- Min & Max -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-payment-minmax<?php eib2bpro_a('payment_minmax' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Payment Methods', 'eib2bpro'); ?></label>
                                    <div class="eib2bpro-rule-lines">
                                        <div class="d-flex">
                                            <select class="form-control w-25 eib2bpro-rule-select eib2bpro-rule-payment-minmax-operator" name="eib2bpro-rule-payment-minmax-operator">
                                                <option value="in" <?php selected('in', get_post_meta($item->ID, 'eib2bpro_rule_payment_minmax_operator', true)) ?>><?php esc_html_e('IN', 'eib2bpro'); ?></option>
                                                <option value="not_in" <?php selected('not_in', get_post_meta($item->ID, 'eib2bpro_rule_payment_minmax_operator', true)) ?>><?php esc_html_e('NOT IN', 'eib2bpro'); ?></option>
                                            </select>

                                            <select class="flex-fill w-100 form-control eib2bpro-rule-selectize" name="eib2bpro-rule-payment-minmax-values[]" data-for="payments" multiple>
                                                <?php
                                                $selected = wp_parse_list(get_post_meta($item->ID, 'eib2bpro_rule_payment_minmax_values', true));
                                                $payment_methods = \WC()->payment_gateways->payment_gateways();
                                                foreach ($payment_methods as $payment_method) {
                                                    if ('yes' === $payment_method->enabled) {
                                                        echo '<option value="' . esc_attr($payment_method->id) . '"' . (in_array($payment_method->id, $selected) ? ' selected' : '') . '>' . esc_html($payment_method->title) . '</option>';
                                                    }
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-payment-minmax<?php eib2bpro_a('payment_minmax' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Amount', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text form-control mr-0"><?php esc_html_e('Minimum', 'eib2bpro'); ?>:</span>
                                            </div>
                                            <input type="text" class="flex-fill form-control eib2bpro-rule-input wc_input_priceeib2bpro-rule-payment-minmax-min" name="eib2bpro-rule-payment-minmax-min" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_payment_minmax_min', true)) ?>">
                                        </div>

                                        <div class="input-group flex-nowrap">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text  form-control mr-0"><?php esc_html_e('Maximum', 'eib2bpro'); ?>:</span>
                                            </div>
                                            <input class="flex-fill form-control eib2bpro-rule-input wc_input_price eib2bpro-rule-payment-minmax-max" name="eib2bpro-rule-payment-minmax-max" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_payment_minmax_max', true)) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Extra Fee -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-add-fee<?php eib2bpro_a('add_fee' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) && 'cart_discount' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">

                                    <label><?php esc_html_e('Amount', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <select class="form-control eib2bpro-rule-select" name="eib2bpro-rule-add-fee-type">
                                            <option value="fixed" <?php selected('fixed', get_post_meta($item->ID, 'eib2bpro_rule_add_fee_type', true)) ?>><?php esc_html_e('Fixed Amount', 'eib2bpro'); ?></option>
                                            <option value="percentage" <?php selected('percentage', get_post_meta($item->ID, 'eib2bpro_rule_add_fee_type', true)) ?>"><?php esc_html_e('Percentage Amount', 'eib2bpro'); ?></option>
                                        </select>
                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-add-fee-values" name="eib2bpro-rule-add-fee-values" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_add_fee_values', true)) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-add-fee<?php eib2bpro_a('add_fee' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) && 'cart_discount' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Name', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <input class="form-control eib2bpro-rule-input eib2bpro-rule-add-fee-name" name="eib2bpro-rule-add-fee-name" value="<?php eib2bpro_a(get_post_meta($item->ID, 'eib2bpro_rule_add_fee_name', true)) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Change Price Format -->

                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-change-price-html<?php eib2bpro_a('change_price_html' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Price Format (HTML)', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <textarea class="form-control eib2bpro-rule-input" name="eib2bpro-rule-change-price-html" rows="4"><?php echo wp_kses_post(get_post_meta($item->ID, 'eib2bpro_rule_change_price_html', true)) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Tax Exemption (User) -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-tax-exemption<?php eib2bpro_a('tax_exemption' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) && 'tax_exemption_product' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-8">
                                    <label><?php esc_html_e('Country', 'eib2bpro'); ?></label>
                                    <div class="d-flex">

                                        <select class="form-control eib2bpro-rule-selectize eib2bpro_rule_tax_exemption_country w-100" name="eib2bpro-rule-tax-exemption-country[]" multiple>
                                            <?php
                                            $selected = wp_parse_list(get_post_meta($item->ID, 'eib2bpro_rule_tax_exemption_country', true));
                                            ?>
                                            <optgroup label="<?php esc_html_e('All', 'eib2bpro') ?>">
                                                <option value="0" <?php eib2bpro_a(in_array('0', $selected) ? ' selected' : '') ?>><?php esc_html_e('All countries', 'eib2bpro') ?></option>
                                            </optgroup>
                                            <?php
                                            $WC_Countries = new \WC_Countries;
                                            $continents = $WC_Countries->get_continents();
                                            $countries = $WC_Countries->get_countries();
                                            foreach ($continents as $continent) {
                                                echo '<optgroup label="' . esc_html($continent['name']) . '">';
                                                foreach ($continent['countries'] as $country_code_index => $country_code) {
                                                    echo '<option value="' . esc_attr($country_code) . '"' . (in_array($country_code, $selected) ? ' selected' : '') . '>' . esc_html($countries[$country_code]) . '</option>';
                                                }
                                                echo '</optgroup>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label>&nbsp;</label>
                                    <div class="form-control pt-2 pr-3">
                                        <?php eib2bpro_ui('onoff', 'eib2bpro_rule_tax_exemption_vies_validation', get_post_meta($item->ID, 'eib2bpro_rule_tax_exemption_vies_validation', true), ['class' => 'switch-sm']); ?>
                                        &nbsp; <?php esc_html_e('Require VIES Validation', 'eib2bpro'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- RULE: Users  -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-users">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Users', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <select class="form-control eib2bpro-rule-select" name="eib2bpro-rule-users">
                                            <option value="all" <?php selected('all', get_post_meta($item->ID, 'eib2bpro_rule_users', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-users-operator, .eib2bpro-rule-users-values,.selectize-control.eib2bpro-rule-users-values2'])) ?>"><?php esc_html_e('Everyone', 'eib2bpro'); ?></option>
                                            <option value="guest" <?php selected('guest', get_post_meta($item->ID, 'eib2bpro_rule_users', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-users-operator, .eib2bpro-rule-users-values,.selectize-control.eib2bpro-rule-users-values2'])) ?>"><?php esc_html_e('Guests', 'eib2bpro'); ?></option>
                                            <option value="all_b2c" <?php selected('all_b2c', get_post_meta($item->ID, 'eib2bpro_rule_users', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-users-operator, .eib2bpro-rule-users-values,.selectize-control.eib2bpro-rule-users-values2'])) ?>"><?php esc_html_e('All B2C users', 'eib2bpro'); ?></option>
                                            <option value="all_b2b" <?php selected('all_b2b', get_post_meta($item->ID, 'eib2bpro_rule_users', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['hide' => '.eib2bpro-rule-users-operator, .eib2bpro-rule-users-values,.selectize-control.eib2bpro-rule-users-values2'])) ?>"><?php esc_html_e('All B2B users', 'eib2bpro'); ?></option>
                                            <option value="group" <?php selected('group', get_post_meta($item->ID, 'eib2bpro_rule_users', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-users-operator, .selectize-control.eib2bpro-rule-users-values', 'hide' => '.selectize-control.eib2bpro-rule-users-values2'])) ?>"><?php esc_html_e('User group', 'eib2bpro'); ?></option>
                                            <option value="user" <?php selected('user', get_post_meta($item->ID, 'eib2bpro_rule_users', true)) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-users-operator, .selectize-control.eib2bpro-rule-users-values2', 'hide' => '.selectize-control.eib2bpro-rule-users-values'])) ?>"><?php esc_html_e('Customer', 'eib2bpro'); ?></option>
                                        </select>

                                        <select class="form-control eib2bpro-rule-select eib2bpro-rule-users-operator<?php eib2bpro_a(!in_array(get_post_meta($item->ID, 'eib2bpro_rule_users', true), ['group', 'user']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-users-operator">
                                            <option value="in" <?php selected('in', get_post_meta($item->ID, 'eib2bpro_rule_users_operator', true)) ?>><?php esc_html_e('in', 'eib2bpro'); ?></option>
                                            <option value="not_in" <?php selected('not_in', get_post_meta($item->ID, 'eib2bpro_rule_users_operator', true)) ?>><?php esc_html_e('not in', 'eib2bpro'); ?></option>
                                        </select>

                                        <select class="form-control eib2bpro-rule-selectize eib2bpro-rule-users-values<?php eib2bpro_a(!in_array(get_post_meta($item->ID, 'eib2bpro_rule_users', true), ['group']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-users-values[]" data-for="group" multiple>
                                            <?php
                                            $selected = wp_parse_list(get_post_meta($item->ID, 'eib2bpro_rule_users_values', true));
                                            $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                                            foreach ($groups as $group) {
                                                echo '<option value="' . intval($group->ID) . '"' . (in_array($group->ID, $selected) ? ' selected' : '') . '>' . esc_html(get_the_title($group->ID)) . '</option>';
                                            } ?>
                                        </select>

                                        <?php
                                        $selected_users = false;
                                        if (in_array(get_post_meta($item->ID, 'eib2bpro_rule_users', true), ['user'])) {
                                            $users = wp_parse_list(get_post_meta($item->ID, 'eib2bpro_rule_users_values', true));
                                            if (is_array($users) && 0 < count($users)) {
                                                $selected_users = [];
                                                foreach ($users as $user) {
                                                    if (intval($user) === 0) {
                                                        $selected_users[] = ['id' => $user, 'name' => $user];
                                                    } else {
                                                        $selected_users[] = ['id' => $user, 'name' => sprintf('%s %s (%s)', get_user_meta($user, 'first_name', true), get_user_meta($user, 'last_name', true), get_userdata($user)->user_login)];
                                                    }
                                                }
                                            }
                                        } ?>

                                        <input class="form-control eib2bpro-rule-selectize eib2bpro-rule-users-values2<?php eib2bpro_a(!in_array(get_post_meta($item->ID, 'eib2bpro_rule_users', true), ['user']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-users-values2" data-for="user" data-data='<?php echo eib2bpro_r(!empty($selected_users) ? json_encode($selected_users) : '') ?>'>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Products  -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-products<?php eib2bpro_a(in_array(get_post_meta($item->ID, 'eib2bpro_rule_type', true), ['add_fee', 'cart_discount', 'tax_exemption', 'payment_minmax', 'payment_discount']) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Products', 'eib2bpro'); ?></label>

                                    <div class="eib2bpro-rule-lines">
                                        <?php
                                        $i = 0;
                                        $products = get_post_meta($item->ID, 'eib2bpro_rule_products', true);
                                        if ($products) {
                                            foreach ($products as $product) {
                                                ++$i; ?>
                                                <div class="eib2bpro-rule-line eib2bpro-parent d-flex mb-3">
                                                    <?php eib2bpro_rule_products_line($item->ID . '_' . $i, $product, $item); ?>
                                                    <button class="eib2bpro-rule-delete-line" data-type="products"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                        <?php }
                                        } ?>
                                        <div class="eib2bpro-rule-line eib2bpro-parent eib2bpro-rule-line-template">
                                            <?php eib2bpro_rule_products_line('template', ['key' => 'product', 'operator' => 'in', 'values' => ''], $item); ?>
                                            <button class="eib2bpro-rule-delete-line" data-type="products"><i class="ri-delete-bin-line"></i></button>
                                        </div>
                                    </div>

                                    <button class="eib2bpro-rule-new-line<?php eib2bpro_a($products ? ' d-none' : '') ?>" data-type="products"><?php esc_html_e('Add product', 'eib2bpro'); ?></button>

                                </div>
                            </div>
                        </div>


                        <!-- RULE: Conditions  -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-conditions<?php eib2bpro_a(in_array(get_post_meta($item->ID, 'eib2bpro_rule_type', true), ['tax_exemption', 'tax_exemption_product']) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Conditions', 'eib2bpro'); ?></label>
                                    <div class="eib2bpro-rule-lines">
                                        <?php
                                        $i = 0;
                                        $conditions = get_post_meta($item->ID, 'eib2bpro_rule_conditions', true);
                                        if ($conditions) {
                                            foreach ($conditions as $condition) {
                                                ++$i; ?>
                                                <div class="eib2bpro-rule-line  eib2bpro-parent d-flex mb-3">
                                                    <?php eib2bpro_rule_conditions_line($item->ID . '_' . $i, $condition); ?>
                                                    <button class="eib2bpro-rule-delete-line" data-type="conditions"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                        <?php }
                                        } ?>
                                        <div class="eib2bpro-rule-line  eib2bpro-parent eib2bpro-rule-line-template">
                                            <?php eib2bpro_rule_conditions_line('template', ['key' => 'cart_total_value', 'operator' => '', 'values' => '']); ?>
                                            <button class="eib2bpro-rule-delete-line" data-type="conditions"><i class="ri-delete-bin-line"></i></button>
                                        </div>
                                    </div>
                                    <button class="eib2bpro-rule-new-line" data-type="conditions"><?php esc_html_e('Add condition', 'eib2bpro'); ?></button>
                                </div>
                            </div>
                        </div>

                        <!-- RULE: Add Quote Button -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-additional-quote<?php eib2bpro_a('add_quote_button' !== get_post_meta($item->ID, 'eib2bpro_rule_type', true) ? ' eib2bpro-hidden' : '') ?>">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('"Add to cart" button', 'eib2bpro'); ?></label>
                                    <div class="d-flex">
                                        <select name="eib2bpro_rule_add_quote_button_remove_atc">
                                            <option value="0" <?php selected(0, intval(get_post_meta($item->ID, 'eib2bpro_rule_add_quote_button_remove_atc', true))) ?>><?php esc_html_e('Show', 'eib2bpro'); ?></option>
                                            <option value="1" <?php selected(1, intval(get_post_meta($item->ID, 'eib2bpro_rule_add_quote_button_remove_atc', true))) ?>><?php esc_html_e('Hide', 'eib2bpro'); ?></option>
                                            <option value="2" <?php selected(2, intval(get_post_meta($item->ID, 'eib2bpro_rule_add_quote_button_remove_atc', true))) ?>><?php esc_html_e('Replace with "Request a quote" button (Popup)', 'eib2bpro'); ?></option>
                                            <option value="3" <?php selected(3, intval(get_post_meta($item->ID, 'eib2bpro_rule_add_quote_button_remove_atc', true))) ?>><?php esc_html_e('Replace with "Request a quote" button (Cart)', 'eib2bpro'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- RULE: Conditions  -->
                        <div class="eib2bpro-rule-group eib2bpro-app-new-item-row eib2bpro-rule-additionals d-none">
                            <div class="row">
                                <div class="col-12">
                                    <label><?php esc_html_e('Additionals', 'eib2bpro'); ?></label>
                                    <div class="eib2bpro-rule-lines">
                                        <input type="checkbox" name="eib2bpro-rule-addition-track" value="1" <?php checked(1, get_post_meta($item->ID, 'eib2bpro_rule_type', true)) ?>> <?php esc_html_e('Track stats for this rule', 'eib2bpro'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4 mb-3">
                            <div class="container-fluid ml-1">
                                <?php eib2bpro_save('', 'ml-4') ?>
                                <?php eib2bpro_ui('ajax_button', 'delete_rule', '', ['app' => 'rules', 'do' => 'delete-rule', 'id' => $item->ID, 'title' => esc_html__('Delete', 'eib2bpro'), 'class' => 'eib2bpro-confirmx float-right mt-1 mr-4 text-danger', 'confirm' => esc_attr__('Are you sure?', 'eib2bpro')]); ?>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </li>
    <?php
    } ?>
</ol>
</div>
</div>
<?php echo eib2bpro_view('core', 0, 'shared.index.pagination', array('count' => $items->found_posts, 'page' => intval(eib2bpro_get('pg', 0)))); ?>

<?php

function eib2bpro_rule_conditions_line($id, $data = [])
{
    if ('template' === $id) {
        $id = '';
    } else {
        $id = '_' . $id;
    }
?>
    <select class="form-control eib2bpro-rule-select" name="eib2bpro-rule-conditions<?php eib2bpro_a($id) ?>">
        <optgroup label="<?php esc_html_e('Cart', 'eib2bpro'); ?>">
            <option value="cart_total_value" <?php selected('cart_total_value', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-conditions-values,.eib2bpro-rule-conditions-operator2'])) ?>"><?php esc_html_e('Cart - Total Value', 'eib2bpro'); ?></option>
            <option value="cart_total_qty" <?php selected('cart_total_qty', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-conditions-values, .eib2bpro-rule-conditions-operator2'])) ?>"><?php esc_html_e('Cart - Total Qty', 'eib2bpro'); ?></option>
        </optgroup>
        <optgroup label="<?php esc_html_e('Date - Time', 'eib2bpro'); ?>">
            <option value="date" <?php selected('date', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-conditions-operator3, .eib2bpro-rule-conditions-values5'])) ?>"><?php esc_html_e('Date', 'eib2bpro'); ?></option>
            <option value="time" <?php selected('time', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-conditions-operator3,.eib2bpro-rule-conditions-values6'])) ?>"><?php esc_html_e('Time', 'eib2bpro'); ?></option>
            <option value="date_time" <?php selected('date_time', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-conditions-operator3,.eib2bpro-rule-conditions-values7'])) ?>"><?php esc_html_e('Date & Time', 'eib2bpro'); ?></option>
        </optgroup>
    </select>

    <select class="form-control eib2bpro-rule-select eib2bpro-rule-all eib2bpro-rule-conditions-operator<?php eib2bpro_a(('cart_product' !== $data['key'] && 'cart_category' !== $data['key']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-operator<?php eib2bpro_a($id) ?>">
        <option value="at_least_one" <?php selected('at_least_one', $data['operator']) ?>><?php esc_html_e('at least one of', 'eib2bpro'); ?></option>
        <option value="all" <?php selected('all', $data['operator']) ?>><?php esc_html_e('all selected', 'eib2bpro'); ?></option>
        <option value="only" <?php selected('only', $data['operator']) ?>><?php esc_html_e('only selected', 'eib2bpro'); ?></option>
        <option value="one" <?php selected('one', $data['operator']) ?>><?php esc_html_e('none of selected', 'eib2bpro'); ?></option>
    </select>

    <select class="form-control eib2bpro-rule-select eib2bpro-rule-all eib2bpro-rule-conditions-operator2<?php eib2bpro_a(('cart_total_qty' !== $data['key'] && 'cart_total_value' !== $data['key']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-operator2<?php eib2bpro_a($id) ?>">
        <option value="equals" <?php selected('equals', $data['operator']) ?>><?php esc_html_e('equals', 'eib2bpro'); ?></option>
        <option value="more_than" <?php selected('more_than', $data['operator']) ?>><?php esc_html_e('more than', 'eib2bpro'); ?></option>
        <option value="less_than" <?php selected('less_than', $data['operator']) ?>><?php esc_html_e('less than', 'eib2bpro'); ?></option>
    </select>

    <select class="form-control eib2bpro-rule-select eib2bpro-rule-all eib2bpro-rule-conditions-operator3<?php eib2bpro_a(('date' !== $data['key'] && 'time' !== $data['key'] && 'date_time' !== $data['key']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-operator3<?php eib2bpro_a($id) ?>">
        <option value="from" <?php selected('from', $data['operator']) ?>><?php esc_html_e('from', 'eib2bpro'); ?></option>
        <option value="to" <?php selected('to', $data['operator']) ?>><?php esc_html_e('to', 'eib2bpro'); ?></option>
        <option value="equals" <?php selected('equals', $data['operator']) ?>><?php esc_html_e('equals', 'eib2bpro'); ?></option>
    </select>

    <input class="form-control eib2bpro-rule-input eib2bpro-rule-all eib2bpro-rule-conditions-values<?php eib2bpro_a(('cart_total_qty' !== $data['key'] && 'cart_total_value' !== $data['key']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values<?php eib2bpro_a($id) ?>" value="<?php eib2bpro_a($data['values']) ?>">


    <?php
    $products = [];
    $_products = wp_parse_list($data['values']);
    foreach ($_products as $_product) {
        $product = wc_get_product($_product);
        if ($product) {
            $products[] = ['id' => $_product, 'name' => $product->get_name()];
        }
    }
    ?>
    <input class="form-control eib2bpro-rule-selectize eib2bpro-rule-all eib2bpro-rule-conditions-values2<?php eib2bpro_a('' === $id ? ' eib2bpro-rule-selectize-template' : '') ?><?php eib2bpro_a(('cart_product' !== $data['key'] && 'cart_product_qty' !== $data['key']) ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values2<?php eib2bpro_a($id) ?>" data-for="product" data-data='<?php echo eib2bpro_r(!empty($products) ? json_encode($products) : '') ?>' multiple>

    <?php
    $categories = [];
    if ('cart_category' ===  $data['key']) {
        $_categories = wp_parse_list($data['values']);
        foreach ($_categories as $_category) {
            $category =  get_term_by('id', $_category, 'product_cat');
            if ($category) {
                $categories[] = ['id' => $_category, 'name' => $category->name];
            }
        }
    }
    ?>
    <input class="form-control eib2bpro-rule-selectize eib2bpro-rule-all eib2bpro-rule-conditions-values3<?php eib2bpro_a('' === $id ? ' eib2bpro-rule-selectize-template' : '') ?><?php eib2bpro_a('cart_category' !==  $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values3<?php eib2bpro_a($id) ?>" data-for="category" data-data='<?php echo eib2bpro_r(!empty($categories) ? json_encode($categories) : '') ?>' multiple>

    <input class="form-control eib2bpro-rule-input eib2bpro-rule-all eib2bpro-rule-conditions-values4<?php eib2bpro_a('cart_product_qty' !== $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values4<?php eib2bpro_a($id) ?>" value="<?php eib2bpro_a(eib2bpro_clean($data['values2'], '')) ?>">

    <input class="form-control eib2bpro-rule-input eib2bpro-rule-all eib2bpro-rule-conditions-values5<?php eib2bpro_a('date' !== $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values5<?php eib2bpro_a($id) ?>" value="<?php eib2bpro_a($data['values']) ?>" type="date">
    <input class="form-control eib2bpro-rule-input eib2bpro-rule-all eib2bpro-rule-conditions-values6<?php eib2bpro_a('time' !== $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values6<?php eib2bpro_a($id) ?>" value="<?php eib2bpro_a($data['values']) ?>" type="time">
    <input class="form-control eib2bpro-rule-input eib2bpro-rule-all eib2bpro-rule-conditions-values7<?php eib2bpro_a('date_time' !== $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-conditions-values7<?php eib2bpro_a($id) ?>" value="<?php eib2bpro_a($data['values']) ?>" type="datetime-local">

<?php
}

function eib2bpro_rule_products_line($id, $data = [], $item = false)
{

    if ('template' === $id) {
        $id = '';
    } else {
        $id = '_' . $id;
    }

    if (is_object($item) && $item->ID > -1) {
        $type = get_post_meta($item->ID, 'eib2bpro_rule_type', true);
    } else {
        $type = '';
    }


?>
    <select class="form-control eib2bpro-rule-select" name="eib2bpro-rule-products<?php eib2bpro_a($id) ?>">
        <option value="product" <?php selected('product', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-products-operator, .selectize-control.eib2bpro-rule-products-values'])) ?>"><?php esc_html_e('Products', 'eib2bpro'); ?></option>
        <option value="category" <?php selected('category', $data['key']) ?> data-cond="<?php eib2bpro_a(json_encode(['show' => '.eib2bpro-rule-products-operator, .selectize-control.eib2bpro-rule-products-values2'])) ?>"><?php esc_html_e('Category', 'eib2bpro'); ?></option>
    </select>

    <select class="form-control eib2bpro-rule-select eib2bpro-rule-all eib2bpro-rule-products-operator" name="eib2bpro-rule-products-operator<?php eib2bpro_a($id) ?>">
        <option value="in" <?php selected('in', $data['operator']) ?>><?php esc_html_e('in', 'eib2bpro'); ?></option>
        <?php if (!in_array($type, ['step', 'free_shipping'])) { ?>
            <option value="not_in" <?php selected('not_in', $data['operator']) ?> class="eib2bpro-operator-not-in"><?php esc_html_e('not in', 'eib2bpro'); ?></option>
        <?php } ?>
    </select>

    <?php
    $products = [];
    $_products = wp_parse_list($data['values']);
    foreach ($_products as $_product) {
        $product = wc_get_product($_product);
        if ($product) {
            $products[] = ['id' => $_product, 'name' => $product->get_name()];
        }
    }
    ?>
    <input class="form-control eib2bpro-rule-selectize eib2bpro-rule-all eib2bpro-rule-products-values<?php eib2bpro_a('' === $id ? ' eib2bpro-rule-selectize-template' : '') ?><?php eib2bpro_a('product' !==  $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-products-values<?php eib2bpro_a($id) ?>" data-for="product" data-data='<?php echo eib2bpro_r(!empty($products) ? json_encode($products) : '') ?>' multiple>

    <?php
    $categories = [];
    if ('category' ===  $data['key']) {
        $_categories = wp_parse_list($data['values']);
        foreach ($_categories as $_category) {
            $category =  get_term_by('id', $_category, 'product_cat');
            if ($category) {
                $categories[] = ['id' => $_category, 'name' => $category->name];
            }
        }
    }
    ?>
    <input class="form-control eib2bpro-rule-selectize eib2bpro-rule-all  eib2bpro-rule-products-values2<?php eib2bpro_a('' === $id ? ' eib2bpro-rule-selectize-template' : '') ?><?php eib2bpro_a('category' !==  $data['key'] ? ' eib2bpro-hidden' : '') ?>" name="eib2bpro-rule-products-values2<?php eib2bpro_a($id) ?>" data-for="category" data-data='<?php echo eib2bpro_r(!empty($categories) ? json_encode($categories) : '') ?>' multiple>

<?php
}
