<?php

namespace EIB2BPRO\Reports;

defined('ABSPATH') || exit;

class Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-pie-chart-fill',
            'title' => esc_html__('Reports', 'eib2bpro'),
            'description' => 'EnergyInc',
            'save_button' => '',
            'buttons' => array()
        );

        $settings['pages']['general'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];
        $settings['pages']['general']['options'][] = [
            'id' => 'reports-goals',
            'type' => 'func',
            'title' => esc_html__('Goals', 'eib2bpro'),
            'func' => '\EIB2BPRO\Reports\Settings::goals',
            'default' => false,
            'description' => '',
            'class' => 'rel-eib2bpro-simple',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['general']['options'][] = [
            'id' => 'reports-home',
            'type' => 'select',
            'title' => esc_html__('Home page of Reports', 'eib2bpro'),
            'opt' => [
                'overview' => esc_html__('Overview', 'eib2bpro'),
                'revenue' => esc_html__('Revenue', 'eib2bpro'),
                'orders' => esc_html__('Orders', 'eib2bpro'),
                'products' => esc_html__('Products', 'eib2bpro'),
                'categories' => esc_html__('Categories', 'eib2bpro'),
                'coupons' => esc_html__('Coupons', 'eib2bpro'),
                'taxes' => esc_html__('Taxes', 'eib2bpro'),
                'downloads' => esc_html__('Downloads', 'eib2bpro'),
                'stock' => esc_html__('Stock', 'eib2bpro'),
                'customers' => esc_html__('Customers', 'eib2bpro')
            ],
            'default' => 'overview',
            'description' => '',
            'class' => '',
            'style' => '',
            'col' => 12
        ];



        $settings['pages']['about'] = ['title' => esc_html__('About', 'eib2bpro'), 'save' => 0, 'function' => '\EIB2BPRO\Settings\App::about'];
        $settings['pages']['about']['content'] = '';

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }

    public static function goals()
    {
?>
        <div class="row">

            <div class="col-12 col-md-3 mb-3">
                <label class="text-uppercase text-muted pb-2"><?php esc_html_e('Daily', 'eib2bpro'); ?></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><?php echo esc_html(get_woocommerce_currency_symbol()) ?></div>
                    </div>
                    <input class="eib2bpro-Settings_Input form-control m-0" name="goals-daily" placeholder="<?php esc_attr_e('N/A', 'eib2bpro'); ?>" value='<?php eib2bpro_a(eib2bpro_option('goals-daily', '')) ?>' />
                </div>

            </div>

            <div class="col-12 col-md-3 mb-3">
                <label class="text-uppercase text-muted pb-2"><?php esc_html_e('Weekly', 'eib2bpro'); ?></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><?php echo esc_html(get_woocommerce_currency_symbol()) ?></div>
                    </div>
                    <input class="eib2bpro-Settings_Input form-control m-0" name="goals-weekly" placeholder="<?php esc_attr_e('N/A', 'eib2bpro'); ?>" value='<?php eib2bpro_a(eib2bpro_option('goals-weekly', '')) ?>' />
                </div>
            </div>
            <div class="col-12 col-md-3 mb-3">
                <label class="text-uppercase text-muted pb-2"><?php esc_html_e('Monthly', 'eib2bpro'); ?></label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><?php echo esc_html(get_woocommerce_currency_symbol()) ?></div>
                    </div>
                    <input class="eib2bpro-Settings_Input form-control m-0" name="goals-monthly" placeholder="<?php esc_attr_e('N/A', 'eib2bpro'); ?>" value='<?php eib2bpro_a(eib2bpro_option('goals-monthly', '')) ?>' />
                </div>
            </div>
        </div>
<?php
    }
}
