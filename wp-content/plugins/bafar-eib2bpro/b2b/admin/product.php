<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Product
{
    public static function woocommerce_product_data_tabs($array)
    {
        $array['eib2bpro'] = array(
            'label' => esc_html__('B2B Pro', 'eib2bpro'),
            'target' => 'eib2bpro_product',
            'class' => array('eib2bpro', 'show_if_simple', 'show_if_variable'),
            'priority' => 9999,
        );

        return $array;
    }

    public static function woocommerce_product_data_panels_save($post_id)
    {
        if ('product' !== get_post_type($post_id)) {
            return;
        }

        if (isset($_REQUEST['bulk_edit'])) {
            return;
        }

        if (isset($_POST['eib2bpro_product_visibility_manual'])) {
            if ('1' === eib2bpro_post('eib2bpro_product_visibility_manual', '0')) {
                $map = [];

                $map['b2c'] = intval(eib2bpro_post('eib2bpro_product_visibility_group_b2c', 0));
                $map['guest'] = intval(eib2bpro_post('eib2bpro_product_visibility_group_guest', 0));

                update_post_meta($post_id, 'eib2bpro_group_b2c', $map['b2c']);
                update_post_meta($post_id, 'eib2bpro_group_guest', $map['guest']);


                if (isset($_POST['eib2bpro_users'])) {
                    $map['users'] = sanitize_text_field(implode(',', array_map('trim', (array)$_POST['eib2bpro_users'])));
                    update_post_meta($post_id, 'eib2bpro_users', $map['users']);
                    foreach ((array)$_POST['eib2bpro_users'] as $user_id) {
                        update_post_meta($post_id, 'eib2bpro_user_' . intval(trim($user_id)), $post_id);
                    }
                } else {
                    delete_post_meta($post_id, 'eib2bpro_users');
                    $map['users'] = '';
                }

                $map['groups'] = [];


                $groups = Groups::get();
                foreach ($groups as $group) {
                    $map['groups'][$group->ID] = intval(eib2bpro_post('eib2bpro_category_visibility_group_' . $group->ID, 0));
                    update_post_meta($post_id, 'eib2bpro_group_' . $group->ID, intval(eib2bpro_post('eib2bpro_product_visibility_group_' . $group->ID, 0)));
                }
                update_post_meta($post_id, 'eib2bpro_visibility_manual', '1');
            } else {
                delete_post_meta($post_id, 'eib2bpro_visibility_manual');
            }
        }

        // save qty step/min/max
        $groups = Groups::get();
        $groups['b2c'] = (object)array('ID' => 'b2c');
        foreach ($groups as $group) {
            foreach (['step', 'min', 'max'] as $type) {
                if (0 < eib2bpro_post("eib2bpro_product_qty_" . $type . "_group_" . $group->ID, "int")) {
                    update_post_meta($post_id, "eib2bpro_product_qty_" . $type . "_group_" . $group->ID, eib2bpro_post("eib2bpro_product_qty_" . $type . "_group_" . $group->ID, "int"));
                } else {
                    delete_post_meta($post_id,  "eib2bpro_product_qty_" . $type . "_group_" . $group->ID);
                }
            }
        }



        Main::clear_cache(['all']);
    }

    public static function woocommerce_product_data_panels($show_pointer_info)
    {
        global $post;

        // get defaults
        $term_id = 0;
        $categories = wc_get_product_term_ids($post->ID, 'product_cat');
        if (!empty($categories)) {
            $term_id = $categories[0];
        } ?>
        <div id='eib2bpro_product' class='panel woocommerce_options_panel'>
            <div class="options_group eib2bpro_product_data_panel">
                <h4 class="mb-2"><?php esc_html_e('Visibility', 'eib2bpro'); ?></h4>
                <p class="form-field">
                    <select class="eib2bpro_product_visibility_manual" name="eib2bpro_product_visibility_manual">
                        <option value="0" <?php selected(0, intval(get_post_meta($post->ID, 'eib2bpro_visibility_manual', true))); ?>><?php esc_html_e('Default', 'eib2bpro'); ?></option>
                        <option value="1" <?php selected(1, intval(get_post_meta($post->ID, 'eib2bpro_visibility_manual', true))); ?>><?php esc_html_e('Custom settings', 'eib2bpro'); ?></option>
                    </select>
                </p>
                <div class="eib2bpro_product_visibility_manual_settings<?php eib2bpro_a(1 === intval(get_post_meta($post->ID, 'eib2bpro_visibility_manual', true)) ? '' : ' eib2bpro-hidden') ?>">
                    <h4><?php esc_html_e('Groups', 'eib2bpro'); ?></h4>
                    <input type="hidden" name="eib2bpro_product_visibility_save" value="1">
                    <div class="mb-1">
                        <?php eib2bpro_ui('onoff', 'eib2bpro_product_visibility_group_guest', eib2bpro_clean2(get_post_meta($post->ID, 'eib2bpro_group_guest', true), (!is_null($default = get_term_meta($term_id, 'eib2bpro_group_guest', true))) ? $default : 1), ['class' => 'switch-sm mr-2']); ?><?php esc_html_e('Guests', 'eib2bpro'); ?>
                    </div>

                    <div class="mb-1">
                        <?php eib2bpro_ui('onoff', 'eib2bpro_product_visibility_group_b2c', eib2bpro_clean2(get_post_meta($post->ID, 'eib2bpro_group_b2c', true), (!is_null($default = get_term_meta($term_id, 'eib2bpro_group_b2c', true))) ? $default : 1), ['class' => 'switch-sm mr-2']); ?><?php esc_html_e('B2C', 'eib2bpro'); ?>
                    </div>

                    <?php
                    $groups = Groups::get();
                    foreach ($groups as $group) {
                        echo '<div class="mb-1">';
                        eib2bpro_ui('onoff', 'eib2bpro_product_visibility_group_' . $group->ID, '' !== ($set = get_post_meta($post->ID, 'eib2bpro_group_' . $group->ID, true)) ? $set : (((!is_null($defaultgroup = get_term_meta($term_id, 'eib2bpro_group_' . $group->ID, true)) ? $defaultgroup : 1))), ['class' => 'switch-sm mr-2']);
                        echo esc_html(get_the_title($group->ID));
                        echo '</div>';
                    } ?>

                    <h4><?php esc_html_e('Users', 'eib2bpro'); ?></h4>
                    <div class="mb-3">
                        <?php eib2bpro_ui('b2b_users_select', 'eib2bpro_users', get_post_meta($post->ID, 'eib2bpro_users', true), ['placeholder' => esc_html__('Please type to search users', 'eib2bpro')]); ?>
                    </div>
                </div>
            </div>
            <div class="options_group eib2bpro_product_data_panel_qty">
                <h4 class="mb-2"><?php esc_html_e('Quantity', 'eib2bpro'); ?></h4>
                <?php
                $groups = Groups::get();
                $groups['b2c'] = (object)array('ID' => 'b2c', 'post_title' => esc_html__('B2C', 'eib2bpro'));
                foreach ($groups as $group) { ?>
                    <p class="form-field">
                        <label for="eib2bpro_regular_price"><?php eib2bpro_e($group->post_title); ?></label>
                        <span class="eib2bpro_price_tiers_container">
                            <input name="eib2bpro_product_qty_step_group_<?php eib2bpro_a($group->ID) ?>" class="eib2bpro_qty_step_input" type="number" min="0" step="1" value="<?php eib2bpro_a(get_post_meta($post->ID, 'eib2bpro_product_qty_step_group_' .  $group->ID, true)) ?>" placeholder="<?php esc_html_e('Step', 'eib2bpro'); ?>">
                            <input name="eib2bpro_product_qty_min_group_<?php eib2bpro_a($group->ID) ?>" class="eib2bpro_qty_step_input" type="number" min="0" step="1" value="<?php eib2bpro_a(get_post_meta($post->ID, 'eib2bpro_product_qty_min_group_' .  $group->ID, true)) ?>" placeholder="<?php esc_html_e('Min', 'eib2bpro'); ?>">
                            <input name="eib2bpro_product_qty_max_group_<?php eib2bpro_a($group->ID) ?>" class="eib2bpro_qty_step_input" type="number" min="0" step="1" value="<?php eib2bpro_a(get_post_meta($post->ID, 'eib2bpro_product_qty_max_group_' .  $group->ID, true)) ?>" placeholder="<?php esc_html_e('Max', 'eib2bpro'); ?>">
                        </span>
                    <?php } ?>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
            </div>
        </div>
    <?php
    }

    public static function woocommerce_product_options_pricing()
    {
       
       global $post; 
       
       global $wpdb;

       // ID del producto
       $product_id = $post->ID; // Cambia por el ID de tu producto
       
       // Consulta para obtener los meta_keys y meta_values
       $query = $wpdb->prepare("
           SELECT meta_key, meta_value 
           FROM {$wpdb->postmeta} 
           WHERE post_id = %d 
           AND meta_key LIKE 'eib2bpro_price_tiers_group_%%'
       ", $product_id);
       
       $results = $wpdb->get_results($query);
   

       // Iterar sobre los resultados
        foreach ($results as $row) {
            $meta_key = $row->meta_key;
            $meta_value = json_decode($row->meta_value, true); // Decodificar JSON en array asociativo

            if (json_last_error() === JSON_ERROR_NONE && is_array($meta_value)) {
                // Obtener el último número del meta_key
                preg_match('/(\d+)$/', $meta_key, $matches);
                $customer_group = $matches[1] ?? 'Unknown'; // Si no encuentra, pone "Unknown"

                // Agregar al array resultante
                $price_tiers[] = [
                    'customer_group' => $customer_group,
                    'meta_value' => $meta_value,
                ];
            }
        }

        // Imprimir los resultados en el nuevo formato
        if (!empty($price_tiers)) {
            foreach ($price_tiers as $tier) {
                echo "<br><br>Customer group: " . $tier['customer_group'] . "\n<br><br>";
                echo "Niveles de precios:\n<br><br>";

                // Convertir el JSON en el formato solicitado
                foreach ($tier['meta_value'] as $cantidad => $precio) {
                    echo "  cantidad: " . $cantidad . ", precio: " . $precio . "<br>";
                }
                echo "\n";
            }
        } else {
            echo "No se encontraron resultados para el producto con ID $product_id.";
        }


       
       
       ?>
      
        <p class="form-field eib2bpro_price_tiers_group_b2c_0">
            <label for="eib2bpro_regular_price"><?php esc_html_e('Price Tiers', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
            <span class="eib2bpro_price_tiers_container">
                <?php

                $price_tiers = (array)json_decode(get_post_meta($post->ID, 'eib2bpro_price_tiers_group_b2c', true));

                foreach ($price_tiers as $tier_qty => $tier_price) {
                ?>
                    <span class="eib2bpro_price_tiers">
                        <input name="eib2bpro_price_tiers_qty_group_b2c[]" class="eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>" value="<?php eib2bpro_a($tier_qty) ?>">
                        <input name="eib2bpro_price_tiers_price_group_b2c[]" class="eib2bpro_price_tiers_input wc_input_price" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>" value="<?php eib2bpro_a(wc_format_localized_price($tier_price)) ?>">
                    </span>
                <?php
                } ?>
                <span class="eib2bpro_price_tiers eib2bpro_price_tiers_blank">
                    <input name="eib2bpro_price_tiers_qty_group_b2c[]" class="eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>">
                    <input name="eib2bpro_price_tiers_price_group_b2c[]" class="eib2bpro_price_tiers_input wc_input_price" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>">
                </span>
            </span>

            <span class="eib2bpro_price_tiers">
                <button class="button eib2bpro_price_tiers_add" data-group="b2c" data-id="0"><?php esc_html_e('Add tier', 'eib2bpro'); ?></button>
            </span>
        </p>

        <div class="form-field">
            <ul class="eib2bpro_price_tiers_groups">
                <?php
                $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                foreach ($groups as $group) { ?>
                    <li class="eib2bpro_price_tiers_group_<?php eib2bpro_a($group->ID) ?>_0">
                        <a class="eib2bpro_toggle" href=#>
                            <strong><?php eib2bpro_e(get_the_title($group->ID)) ?></strong>
                            <span class="eib2bpro_price_range">
                                <?php
                                if (!empty($eib2bpro_regular_price = get_post_meta($post->ID, 'eib2bpro_regular_price_group_' . $group->ID, true))) {
                                    echo " &nbsp; &mdash; &nbsp " . wc_price($eib2bpro_regular_price);
                                } ?>
                                <?php
                                if (!empty($eib2bpro_sale_price = get_post_meta($post->ID, 'eib2bpro_sale_price_group_' . $group->ID, true))) {
                                    echo " &nbsp; - &nbsp; " . wc_price($eib2bpro_sale_price);
                                } ?>
                            </span>
                        </a>
                        <div class="inner">

                            <p class="form-field _regular_price_field_group_<?php eib2bpro_a($group->ID) ?>">
                                <label for="eib2bpro_regular_price_group_<?php eib2bpro_a($group->ID) ?>"><?php esc_html_e('Regular Price', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
                                <input type="text" class="short wc_input_price" name="eib2bpro_regular_price_group_<?php eib2bpro_a($group->ID) ?>" value="<?php eib2bpro_a(wc_format_localized_price(get_post_meta($post->ID, 'eib2bpro_regular_price_group_' . $group->ID, true))) ?>" placeholder="">
                            </p>

                            <p class="form-field _sale_price_field_group_<?php eib2bpro_a($group->ID) ?>">
                                <label for="eib2bpro_sale_price_group_<?php eib2bpro_a($group->ID) ?>"><?php esc_html_e('Sale Price', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
                                <input type="text" class="short wc_input_price" name="eib2bpro_sale_price_group_<?php eib2bpro_a($group->ID) ?>" value="<?php eib2bpro_a(wc_format_localized_price(get_post_meta($post->ID, 'eib2bpro_sale_price_group_' . $group->ID, true))) ?>" placeholder="">
                            </p>

                            <p class="form-field eib2bpro_price_tiers_group_<?php eib2bpro_a($group->ID) ?>">
                                <label for="_regular_price"><?php esc_html_e('Price Tiers', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
                                <span class="eib2bpro_price_tiers_container">
                                    <?php

                                    $price_tiers = (array)json_decode(get_post_meta($post->ID, 'eib2bpro_price_tiers_group_' . $group->ID, true));

                                    foreach ($price_tiers as $tier_qty => $tier_price) {
                                    ?>
                                        <span class="eib2bpro_price_tiers">
                                            <input name="eib2bpro_price_tiers_qty_group_<?php eib2bpro_a($group->ID) ?>[]" class="eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>" value="<?php eib2bpro_a($tier_qty) ?>">
                                            <input name="eib2bpro_price_tiers_price_group_<?php eib2bpro_a($group->ID) ?>[]" class="eib2bpro_price_tiers_input wc_input_price" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>" value="<?php eib2bpro_a(wc_format_localized_price($tier_price)) ?>">
                                        </span>
                                    <?php
                                    }
                                    ?>
                                    <span class="eib2bpro_price_tiers eib2bpro_price_tiers_blank">
                                        <input name="eib2bpro_price_tiers_qty_group_<?php eib2bpro_a($group->ID) ?>[]" class="eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>">
                                        <input name="eib2bpro_price_tiers_price_group_<?php eib2bpro_a($group->ID) ?>[]" class="eib2bpro_price_tiers_input wc_input_price" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>">
                                    </span>
                                </span>

                                <span class="eib2bpro_price_tiers">
                                    <button class="button eib2bpro_price_tiers_add" data-group="<?php eib2bpro_a($group->ID) ?>" data-id="0"><?php esc_html_e('Add tier', 'eib2bpro'); ?></button>
                                </span>
                            </p>

                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php
    }

    public static function woocommerce_process_product_meta($post)
    {
        $groups = \EIB2BPRO\B2b\Admin\Groups::get();
        $groups['b2c'] = (object)array('ID' => 'b2c');

        foreach ($groups as $group) {
            $tiers = [];

            if (isset($_POST['eib2bpro_price_tiers_qty_group_' . $group->ID])) {
                $tier_qty = $_POST['eib2bpro_price_tiers_qty_group_' . $group->ID];
            }

            if (isset($_POST['eib2bpro_price_tiers_price_group_' . $group->ID])) {
                $tier_price = $_POST['eib2bpro_price_tiers_price_group_' . $group->ID];
            }

            if (is_array($tier_qty)) {
                foreach ($tier_qty as $key => $value) {
                    if (isset($tier_qty[$key]) && isset($tier_price[$key])) {
                        if (!is_array($tier_qty[$key]) && !is_array($tier_price[$key])) {
                            if (!empty(trim($tier_qty[$key])) && !empty(trim($tier_price[$key]))) {
                                $tiers[intval(sanitize_text_field(trim($tier_qty[$key])))] = wc_format_decimal(sanitize_text_field(trim($tier_price[$key])));
                            }
                        }
                    }
                }
            }

            update_post_meta($post, 'eib2bpro_regular_price_group_' . $group->ID, esc_attr(wc_format_decimal(eib2bpro_post('eib2bpro_regular_price_group_' . $group->ID))));
            update_post_meta($post, 'eib2bpro_sale_price_group_' . $group->ID, esc_attr(wc_format_decimal(eib2bpro_post('eib2bpro_sale_price_group_' . $group->ID))));

            if (0 < count($tiers)) {
                ksort($tiers);
                update_post_meta($post, 'eib2bpro_price_tiers_group_' . $group->ID, eib2bpro_r(json_encode($tiers)));
            } else {
                delete_post_meta($post, 'eib2bpro_price_tiers_group_' . $group->ID);
            }
        }
    }

    public static function woocommerce_variation_options_pricing($loop, $variation_data, $variation)
    {
    ?>
        <p class="form-field form-row eib2bpro_price_tiers_group_b2c_<?php eib2bpro_a($variation->ID) ?>">
            <label for="eib2bpro_regular_price"><?php esc_html_e('Price Tiers', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
            <span class="eib2bpro_price_tiers_container">
                <?php

                $price_tiers = (array)json_decode(get_post_meta($variation->ID, 'eib2bpro_price_tiers_group_b2c', true));

                foreach ($price_tiers as $tier_qty => $tier_price) {
                ?>
                    <span class="eib2bpro_price_tiers form-row">
                        <input name="eib2bpro_price_tiers_qty_group_b2c[<?php eib2bpro_a($variation->ID) ?>][]" class="short wc_input_price eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>" value="<?php eib2bpro_a($tier_qty) ?>">
                        <input name="eib2bpro_price_tiers_price_group_b2c[<?php eib2bpro_a($variation->ID) ?>][]" class="short wc_input_price eib2bpro_price_tiers_input" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>" value="<?php eib2bpro_a(wc_format_localized_price($tier_price)) ?>">
                    </span>
                <?php
                } ?>
                <span class="eib2bpro_price_tiers form-row eib2bpro_price_tiers_blank">
                    <input name="eib2bpro_price_tiers_qty_group_b2c[<?php eib2bpro_a($variation->ID) ?>][]" class="short wc_input_price eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>">
                    <input name="eib2bpro_price_tiers_price_group_b2c[<?php eib2bpro_a($variation->ID) ?>][]" class="short wc_input_price eib2bpro_price_tiers_input" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>">
                </span>
            </span>

            <span class="eib2bpro_price_tiers">
                <button class="button eib2bpro_price_tiers_add" data-group="b2c" data-id="<?php eib2bpro_a($variation->ID) ?>"><?php esc_html_e('Add tier', 'eib2bpro'); ?></button>
            </span>
        </p>

        <div class="form-field eib2bpro_price_tiers_variation">
            <ul class="eib2bpro_price_tiers_groups">
                <?php
                $groups = \EIB2BPRO\B2b\Admin\Groups::get();
                foreach ($groups as $group) { ?>
                    <li class="eib2bpro_price_tiers_group_<?php eib2bpro_a($group->ID) ?>_<?php eib2bpro_a($variation->ID) ?>">
                        <a class="eib2bpro_toggle" href=#>
                            <strong><?php eib2bpro_e(get_the_title($group->ID)) ?></strong>
                            <span class="eib2bpro_price_range">
                                <?php
                                if (!empty($eib2bpro_regular_price = get_post_meta($variation->ID, 'eib2bpro_regular_price_group_' . $group->ID, true))) {
                                    echo " &nbsp; &mdash; &nbsp " . wc_price($eib2bpro_regular_price);
                                } ?>
                                <?php
                                if (!empty($eib2bpro_sale_price = get_post_meta($variation->ID, 'eib2bpro_sale_price_group_' . $group->ID, true))) {
                                    echo " &nbsp; - &nbsp; " . wc_price($eib2bpro_sale_price);
                                } ?>
                            </span>
                        </a>
                        <div class="inner">

                            <p class="form-field form-row _regular_price_field_group_<?php eib2bpro_a($group->ID) ?>">
                                <label for="eib2bpro_regular_price_group_<?php eib2bpro_a($group->ID) ?>"><?php esc_html_e('Regular Price', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
                                <input type="text" class="short wc_input_price" name="eib2bpro_regular_price_group_<?php eib2bpro_a($group->ID) ?>[<?php eib2bpro_a($variation->ID) ?>]" id="eib2bpro_regular_price_group_<?php eib2bpro_a($group->ID) ?>" value="<?php eib2bpro_a(wc_format_localized_price(get_post_meta($variation->ID, 'eib2bpro_regular_price_group_' . $group->ID, true))) ?>" placeholder="">
                            </p>

                            <p class="form-field form-row _sale_price_field_group_<?php eib2bpro_a($group->ID) ?>">
                                <label for="eib2bpro_sale_price_group_<?php eib2bpro_a($group->ID) ?>"><?php esc_html_e('Sale Price', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
                                <input type="text" class="short wc_input_price" name="eib2bpro_sale_price_group_<?php eib2bpro_a($group->ID) ?>[<?php eib2bpro_a($variation->ID) ?>]" id="eib2bpro_sale_price_group_<?php eib2bpro_a($group->ID) ?>" value="<?php eib2bpro_a(wc_format_localized_price(get_post_meta($variation->ID, 'eib2bpro_sale_price_group_' . $group->ID, true))) ?>" placeholder="">
                            </p>

                            <p class="form-field eib2bpro_price_tiers_group_<?php eib2bpro_a($group->ID) ?>">
                                <label for="_regular_price"><?php esc_html_e('Price Tiers', 'eib2bpro'); ?> (<?php eib2bpro_e(get_woocommerce_currency_symbol()) ?>)</label>
                                <span class="eib2bpro_price_tiers_container">
                                    <?php

                                    $price_tiers = (array)json_decode(get_post_meta($variation->ID, 'eib2bpro_price_tiers_group_' . $group->ID, true));

                                    foreach ($price_tiers as $tier_qty => $tier_price) {
                                    ?>
                                        <span class="eib2bpro_price_tiers form-row">
                                            <input name="eib2bpro_price_tiers_qty_group_<?php eib2bpro_a($group->ID) ?>[<?php eib2bpro_a($variation->ID) ?>][]" class="eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>" value="<?php eib2bpro_a($tier_qty) ?>">
                                            <input name="eib2bpro_price_tiers_price_group_<?php eib2bpro_a($group->ID) ?>[<?php eib2bpro_a($variation->ID) ?>][]" class="eib2bpro_price_tiers_input wc_input_price" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>" value="<?php eib2bpro_a(wc_format_localized_price($tier_price)) ?>">
                                        </span>
                                    <?php
                                    }
                                    ?>
                                    <span class="eib2bpro_price_tiers form-row eib2bpro_price_tiers_blank">
                                        <input name="eib2bpro_price_tiers_qty_group_<?php eib2bpro_a($group->ID) ?>[<?php eib2bpro_a($variation->ID) ?>][]" class="eib2bpro_price_tiers_input" type="number" min="1" step="1" placeholder="<?php esc_html_e('Min. Qty.', 'eib2bpro'); ?>">
                                        <input name="eib2bpro_price_tiers_price_group_<?php eib2bpro_a($group->ID) ?>[<?php eib2bpro_a($variation->ID) ?>][]" class="eib2bpro_price_tiers_input wc_input_price" type="text" placeholder="<?php esc_html_e('Price', 'eib2bpro'); ?>">
                                    </span>
                                </span>

                                <span class="eib2bpro_price_tiers">
                                    <button class="button eib2bpro_price_tiers_add" data-group="<?php eib2bpro_a($group->ID) ?>" data-id="<?php eib2bpro_a($variation->ID) ?>"><?php esc_html_e('Add tier', 'eib2bpro'); ?></button>
                                </span>
                            </p>

                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>

<?php
    }

    public static function woocommerce_save_product_variation($post)
    {
        if (!is_array($_POST['variable_post_id'])) {
            return;
        }
        foreach ($_POST['variable_post_id'] as $key => $id) {
            $variations[] = intval($id);
        }
        $groups = \EIB2BPRO\B2b\Admin\Groups::get();
        $groups['b2c'] = (object)array('ID' => 'b2c');

        foreach ($groups as $group) {
            $tiers = [];

            if (isset($_POST['eib2bpro_price_tiers_qty_group_' . $group->ID])) {
                $_tier_qty = $_POST['eib2bpro_price_tiers_qty_group_' . $group->ID];
            }

            if (isset($_POST['eib2bpro_price_tiers_price_group_' . $group->ID])) {
                $_tier_price = $_POST['eib2bpro_price_tiers_price_group_' . $group->ID];
            }

            if (is_array($_tier_qty)) {
                foreach ($_tier_qty as $id => $tier_qty) {
                    foreach ($tier_qty as $key => $value) {
                        if (!empty(trim($tier_qty[$key])) && !empty(trim($_tier_price[$id][$key]))) {
                            $tiers[intval(sanitize_text_field(trim($tier_qty[$key])))] = wc_format_decimal(sanitize_text_field(trim($_tier_price[$id][$key])));
                        }
                    }
                    if (0 < count($tiers)) {
                        update_post_meta($id, 'eib2bpro_price_tiers_group_' . $group->ID, eib2bpro_r(json_encode($tiers)));
                    } else {
                        delete_post_meta($id, 'eib2bpro_price_tiers_group_' . $group->ID);
                    }
                }
            }

            foreach ($variations as $variation_id) {
                update_post_meta($variation_id, 'eib2bpro_regular_price_group_' . $group->ID, esc_attr(wc_format_decimal(sanitize_text_field($_POST['eib2bpro_regular_price_group_' . $group->ID][$variation_id]))));
                update_post_meta($variation_id, 'eib2bpro_sale_price_group_' . $group->ID, esc_attr(wc_format_decimal(sanitize_text_field($_POST['eib2bpro_sale_price_group_' . $group->ID][$variation_id]))));
            }
        }
    }

    public static function search()
    {
        global $wpdb;
        $str = eib2bpro_post('query');

        $args = array(
            'posts_per_page' => -1,
            'post_type' => array('product', 'product_variation'),
            's' => $str,
            'suppress_filters' => 1,
            'fields' => 'ids'
        );

        $products = new \WP_Query($args);

        $result = [];

        if ($products->posts) {
            foreach ($products->posts as $product_id) {
                $product = wc_get_product($product_id);
                $result[] = ['id' => $product->get_id(), 'name' => html_entity_decode(get_the_title($product->get_id())), 'price' => $product->get_price(), 'price_currency' => html_entity_decode(strip_tags(wc_price($product->get_price())))];
            }
        }
        echo eib2bpro_r(json_encode($result));
        exit;
    }
}
