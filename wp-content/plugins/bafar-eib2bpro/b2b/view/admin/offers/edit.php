<?php defined('ABSPATH') || exit; ?>
<div class="container-fluid">
    <div class="row">
        <div class="eib2bpro-app-new-item-head">
            <h5 class="mb-0"><?php esc_html_e('Offers', 'eib2bpro') ?> <?php eib2bpro_ui('wpml_selector'); ?></h5>
        </div>
    </div>
</div>

<?php eib2bpro_form(['do' => 'edit-offer', 'id' => eib2bpro_get('id', 0)]); ?>

<input name="id" value="<?php echo eib2bpro_clean($id, '0') ?>" type="hidden">
<input name="quote_id" value="<?php echo eib2bpro_a(eib2bpro_get('quote_id', 0)) ?>" type="hidden">

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
                <label><?php esc_html_e('Type', 'eib2bpro'); ?></label>
                <input type="radio" value="bundle" name="eib2bpro_offer_type" id="rb2" class="eib2bpro_radio_selector eib2bpro_offer_type_selector eib2bpro-radio-2" <?php checked('bundle', eib2bpro_clean2(get_post_meta($id, 'eib2bpro_offer_type', true), 'bundle')) ?> />
                <label for="rb2" class="eib2bpro-radio-2"><?php esc_html_e('Product bundle', 'eib2bpro'); ?></label>
                <input type="radio" value="suggestion" name="eib2bpro_offer_type" id="rb1" class="eib2bpro_radio_selector eib2bpro_offer_type_selector eib2bpro-radio-2" <?php checked('suggestion', get_post_meta($id, 'eib2bpro_offer_type', true)) ?> />
                <label for="rb1" class="eib2bpro-radio-2"><?php esc_html_e('Product suggestions', 'eib2bpro'); ?></label>
                <input type="radio" value="announcement" name="eib2bpro_offer_type" id="rb3" class="eib2bpro_radio_selector eib2bpro_offer_type_selector eib2bpro-radio-2" <?php checked('announcement', get_post_meta($id, 'eib2bpro_offer_type', true)) ?> />
                <label for="rb3" class="eib2bpro-radio-2"><?php esc_html_e('Announcement ', 'eib2bpro'); ?></label>
            </div>
        </div>


        <div class="row">
            <div class="eib2bpro-app-new-item-row col-6">
                <label><?php esc_html_e('Groups', 'eib2bpro'); ?></label>
                <?php
                $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                foreach ($groups as $group) {
                    echo "<div class='mt-1 mb-1'>";
                    eib2bpro_ui('onoff', 'eib2bpro_groups[]', $group->ID, ['csv' => get_post_meta($id, 'eib2bpro_groups', true), 'class' => 'switch-sm']);
                    echo "<span class='eib2bpro-font-14 pl-2'>" . esc_html(get_the_title($group->ID)) . '</span></div>';
                } ?>

                <div class="eib2bpro-app-new-item-row-sub">
                    <label><?php esc_html_e('Customers', 'eib2bpro'); ?></label>
                    <?php $users = wp_parse_list(get_post_meta($id, 'eib2bpro_users', true)); ?>
                    <input name="eib2bpro_users" class="eib2bpro-app-user-select hidden eib2bpro-app-user-select-addnew" <?php
                                                                                                                            $selected_users = false;
                                                                                                                            if (is_array($users) && 0 < count($users)) {
                                                                                                                                $selected_users = [];
                                                                                                                                foreach ($users as $user) {
                                                                                                                                    if (!is_numeric($user)) {
                                                                                                                                        $selected_users[] = ['id' => $user, 'name' => $user];
                                                                                                                                    } else {
                                                                                                                                        $selected_users[] = ['id' => $user, 'name' => sprintf('%s %s (%s)', get_user_meta($user, 'first_name', true), get_user_meta($user, 'last_name', true), get_userdata($user)->user_login)];
                                                                                                                                    }
                                                                                                                                }
                                                                                                                            } ?> data-data='<?php echo eib2bpro_r(!empty($selected_users) ? json_encode($selected_users) : '') ?>'>
                </div>

            </div>
            <div class=" eib2bpro-app-new-item-row col-6">

                <div class="mt-2 eib2bpro-offer-edit-cart-image">
                    <label><?php esc_html_e('Cart Image', 'eib2bpro'); ?></label>
                    <div class="w-100 d-block text-center">
                        <?php eib2bpro_ui('media', 'eib2bpro_cart_img', get_post_meta($id, 'eib2bpro_cart_img', true)) ?>
                    </div>
                </div>

                <div class="eib2bpro-app-new-item-row-sub">

                    <div class="mt-2">
                        <label><?php esc_html_e('Promotion Image', 'eib2bpro'); ?></label>
                        <div class="w-100 d-block text-center">
                            <?php eib2bpro_ui('media', 'eib2bpro_promo_img', get_post_meta($id, 'eib2bpro_promo_img', true)) ?>
                        </div>
                    </div>
                </div>

                <div class="eib2bpro-app-new-item-row-sub">
                    <label><?php esc_html_e('Promotion Text', 'eib2bpro'); ?></label>
                    <div class="w-100 d-block">
                        <textarea name="eib2bpro_promo_text" rows=5 class="w-100 form-control eib2bpro-ui-input"><?php echo eib2bpro_r(get_post_meta($id, 'eib2bpro_promo_text', true)) ?></textarea>
                    </div>
                </div>

            </div>

        </div>

        <div class="row eib2bpro-b2b-offer-table-th-all">
            <div class="eib2bpro-app-new-item-row col-12 p-0 m-0">
                <table class="table table-borderless p-0 m-0">
                    <thead class="eib2bpro-b2b-offer-table-head">
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php esc_html_e('Product', 'eib2bpro'); ?></th>
                            <th class="eib2bpro-b2b-offer-table-th-qty"><?php esc_html_e('Qty', 'eib2bpro'); ?></th>
                            <th class="eib2bpro-b2b-offer-table-th-price"><?php esc_html_e('Price', 'eib2bpro'); ?></th>
                            <th class="eib2bpro-b2b-offer-table-th-subtotal"><?php esc_html_e('Subtotal', 'eib2bpro'); ?></th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody class="eib2bpro-b2b-offer-table" data-decimal="<?php eib2bpro_a(wc_get_price_decimal_separator()) ?>">
                        <tr class="eib2bpro-hidden-row w-100">
                            <td class="eib2bpro-b2b-offer-table-move">
                                <i class="eib2bpro-os-move eib2bpro-icon-move"></i>
                            </td>
                            <td class="eib2bpro-b2b-offer-table-product">
                                <select name="offer-product[]" class="eib2bpro-b2b-offer-product_hidden" placeholder="<?php esc_attr__('Please type to search products', 'eib2bpro') ?>">
                                    <option value=""></option>
                                </select>
                            </td>
                            <td class="text-center eib2bpro-b2b-offer-table-unit eib2bpro-b2b-offer-table-th-qty">
                                <?php eib2bpro_ui('input', 'offer-unit[]', '', ['attr' => 'type="number" step="1" min="1"']) ?>
                            </td>
                            <td class="text-center eib2bpro-b2b-offer-table-price eib2bpro-b2b-offer-table-th-price">
                                <div class="w-100 d-flex flex-nowrap align-items-center">
                                    <?php eib2bpro_ui('input', 'offer-price[]', '') ?>
                                </div>
                            </td>
                            <td class="eib2bpro-b2b-offer-table-subtotal eib2bpro-b2b-offer-table-th-subtotal">
                                <div class="w-100 d-flex flex-nowrap align-items-center">
                                    <h6>
                                        <span><?php echo get_woocommerce_currency_symbol() ?></span>
                                        <span class="eib2bpro-offer-subtotal"><?php echo wc_price(0); ?></span>
                                    </h6>
                                </div>
                            </td>
                            <td class="eib2bpro-b2b-offer-table-delete">
                                <a href="javascript:;" class="text-danger"><i class="ri-delete-bin-6-line"></i></a>
                            </td>
                        </tr>
                        <?php
                        if (0 < intval(eib2bpro_get('id', 0))) {
                            $products = get_post_meta(intval(eib2bpro_get('id', 0)), 'eib2bpro_products', true);
                        } else {
                            $products = [['id' => '', 'unit' => '', 'price' => '']];
                        }

                        foreach ($products as $product) { ?>
                            <tr class="w-100">
                                <td class="eib2bpro-b2b-offer-table-move">
                                    <i class="eib2bpro-os-move eib2bpro-icon-move"></i>
                                </td>
                                <td class="eib2bpro-b2b-offer-table-product">
                                    <?php eib2bpro_ui('b2b_product_select', 'offer-product', [$product['id']], ['placeholder' => esc_attr__('Please type to search products', 'eib2bpro'), 'class' => 'eib2bpro-b2b-offer-product']); ?>
                                </td>
                                <td class="text-center eib2bpro-b2b-offer-table-unit eib2bpro-b2b-offer-table-th-qty">
                                    <?php eib2bpro_ui('input', 'offer-unit[]', $product['unit'], ['class' => 'text-center', 'attr' => 'type="number" step="1" min="1"']) ?>
                                </td>
                                <td class="text-center eib2bpro-b2b-offer-table-price eib2bpro-b2b-offer-table-th-price">
                                    <div class="w-100 d-flex flex-nowrap align-items-center">
                                        <?php eib2bpro_ui('input', 'offer-price[]', number_format(wc_format_decimal((float)$product['price']), 2, wc_get_price_decimal_separator(), ''), ['class' => 'text-right wc-input-price']) ?>
                                    </div>
                                </td>
                                <td class="eib2bpro-b2b-offer-table-subtotal eib2bpro-b2b-offer-table-th-subtotal">
                                    <div class="w-100 d-flex flex-nowrap align-items-center">
                                        <h6>
                                            <span><?php echo get_woocommerce_currency_symbol() ?></span>
                                            <span class="eib2bpro-offer-subtotal"></span>
                                        </h6>
                                    </div>
                                </td>
                                <td class="eib2bpro-b2b-offer-table-delete">
                                    <a href="javascript:;" class="text-danger"><i class="ri-delete-bin-6-line"></i></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row eib2bpro-b2b-offer-table-th-all">
            <div class="eib2bpro-app-new-item-row col-12 text-right">
                <a href="javascript:;" class="eib2bpro-b2b-offer-new-row float-left"><?php esc_html_e('Add new item', 'eib2bpro'); ?></a>
                <div class="float-right eib2bpro-b2b-offer-table-th-total">
                    <?php esc_html_e('Total: ', 'eib2bpro'); ?>
                    <div class="eib2bpro-offer-total-div">
                        <span><?php echo get_woocommerce_currency_symbol() ?></span>
                        <span class="eib2bpro-offer-total">0</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="row text-right pt-4">
            <div class="col-12 text-right pr-5">
                <?php eib2bpro_save('') ?>
            </div>
        </div>
    </div>
</div>

</form>