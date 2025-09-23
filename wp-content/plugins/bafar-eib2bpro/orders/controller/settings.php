<?php

namespace EIB2BPRO\Orders;

defined('ABSPATH') || exit;

class Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-shopping-bag-3-fill',
            'title' => esc_html__('Orders', 'eib2bpro'),
            'description' => 'EnergyInc',
            'save_button' => 'hidden',
            'buttons' => array()
        );

        $settings['pages']['application'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];
        $settings['pages']['application']['options'][] = [
            'id' => 'orders-mode',
            'type' => 'big_select',
            'title' => esc_html__('App Mode', 'eib2bpro'),
            'opt' => [
                'simple' => [
                    'title' => 'Standard',
                    'description' => 'Energy',
                    'conditions' => ['show' => '.rel-eib2bpro-simple']
                ],

                'native' => [
                    'title' => esc_html__('WooCommerce Native', 'eib2bpro'),
                    'description' => 'WooCommerce',
                    'conditions' => ['hide' => '.rel-eib2bpro-simple']
                ],
            ],
            'default' => 'simple',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['application']['options'][] = [
            'id' => 'orders-statuses',
            'type' => 'func',
            'title' => esc_html__('Order statuses', 'eib2bpro'),
            'func' => '\EIB2BPRO\Orders\Settings::orderStatuses',
            'default' => false,
            'description' => esc_html__('Select which statuses will be shown when an order is clicked. You can show/hide or sort them.', 'eib2bpro'),
            'class' => 'rel-eib2bpro-simple',
            'if' => array('orders-mode', array('simple', '')),
            'style' => '',
            'col' => 12
        ];


        $settings['pages']['about'] = ['title' => esc_html__('About', 'eib2bpro'), 'save' => 0, 'function' => '\EIB2BPRO\Settings\App::about'];
        $settings['pages']['about']['content'] = '';

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }

    public static function orderStatuses()
    {
?>
        <div class="eib2bpro-settings-orders-status">
            <div class="row">
                <div class="nav flex-column nav-pills eib2bpro-Tweaks_SOS pr-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <?php foreach (wc_get_order_statuses() as $key => $value) {
                        if (!isset($status_active)) {
                            $status_active = " active";
                        } else {
                            $status_active = "";
                        } ?>
                        <a class="nav-link<?php echo esc_attr($status_active) ?>" id="v-pills-<?php echo esc_attr($key) ?>-tab" data-toggle="pill" href="#v-pills-<?php echo esc_attr($key) ?>" role="tab" aria-controls="v-pills-<?php echo esc_attr($key) ?>" aria-selected="true"><?php echo esc_html($value) ?></a>
                    <?php
                    } ?>
                </div>

                <div class="tab-content w-75 eib2bpro-Tweaks_SOS" id="v-pills-tabContent">
                    <?php

                    $wc_statuses = array_keys(wc_get_order_statuses());
                    $wc_statuses_all = wc_get_order_statuses();
                    $wc_statuses['trash'] = 'trash';
                    $wc_statuses_all['trash'] = esc_html__('Delete', 'eib2bpro');

                    $status_details = eib2bpro_option('orders-statuses', array());

                    foreach (wc_get_order_statuses() as $key => $value) {
                        if (isset($status_details[$key])) {
                            $status_detail = $status_details[$key];
                        } else {
                            $status_detail = array_keys(wc_get_order_statuses());
                        }

                        if (!isset($status_active2)) {
                            $status_active2 = " active";
                        } else {
                            $status_active2 = "";
                        } ?>
                        <div class="tab-pane fade show<?php echo esc_attr($status_active2) ?>" id="v-pills-<?php echo esc_attr($key) ?>" role="tabpanel" aria-labelledby="v-pills-<?php echo esc_attr($key) ?>-tab">
                            <ol class="eib2bpro_Sortable">
                                <?php foreach ($status_detail as $key1) {
                                    if ('-' === $key1) {
                                        continue;
                                    } ?>
                                    <li class="eib2bpro-Tweaks_OS eib2bpro-Settings_NCT row">
                                        <div class="form-check">
                                            <a href="javascript:;" class="text-muted" title="<?php esc_attr_e('Move', 'eib2bpro') ?>"><i class="eib2bpro-icon-move"></i></a>
                                            &nbsp; <input type="checkbox" value="<?php echo esc_attr($key1) ?>" name="orders-statuses[<?php echo esc_attr($key) ?>][]" <?php if (in_array($key1, $status_detail)) {
                                                                                                                                                                            echo " checked";
                                                                                                                                                                        } ?>>
                                            <?php echo esc_html($wc_statuses_all[$key1]) ?>
                                        </div>
                                    </li>
                                <?php
                                } ?>

                                <?php
                                if (is_array($wc_statuses) && is_array($status_detail)) {
                                    $other_status = array_diff($wc_statuses, $status_detail);
                                } else {
                                    $other_status = array();
                                }
                                foreach ($other_status as $key1) { ?>
                                    <li class="eib2bpro-Tweaks_OS eib2bpro-Settings_NCT row">
                                        <div class="form-check">
                                            <a href="javascript:;" class="text-muted" title="<?php esc_attr_e('Move', 'eib2bpro') ?>">
                                                <i class="eib2bpro-icon-move"></i>
                                            </a>
                                            &nbsp; <input type="checkbox" value="<?php echo esc_attr($key1) ?>" name="orders-statuses[<?php echo esc_attr($key) ?>][]" <?php if (in_array($key1, $status_detail)) {
                                                                                                                                                                            echo " checked";
                                                                                                                                                                        } ?>>
                                            <?php echo esc_html($wc_statuses_all[$key1]) ?>
                                        </div>
                                    </li>
                                <?php } ?>
                                <input type="checkbox" class="eib2bpro-Tweaks_OS-hidden" value="-" name="orders-statuses[<?php echo esc_attr($key) ?>][-]" checked>
                            </ol>
                        </div>
                    <?php
                    } ?>
                </div>
            </div>
        </div>
<?php
    }
}
