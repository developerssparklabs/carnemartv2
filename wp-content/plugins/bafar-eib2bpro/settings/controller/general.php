<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class General extends \EIB2BPRO\Settings
{
    public static function settings()
    {
        $settings = array();

        $settings['title'] = array(
            'icon' => 'ri-compass-3-fill',
            'title' => esc_html__('General', 'eib2bpro'),
            'description' => esc_html__('Settings about the panel', 'eib2bpro'),
            'save_button' => 'hidden',
            'buttons' => array()
        );

        $settings['pages']['general'] = ['title' => esc_html__('General', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];
        $settings['pages']['tracker'] = ['title' => esc_html__('Tracker', 'eib2bpro'), 'function' => '\EIB2BPRO\Settings\Options::options'];


        $settings['pages']['general']['options'][] = [
            'id' => 'logo',
            'type' => 'func',
            'title' => '',
            'func' => '\EIB2BPRO\Settings\General::logo',
            'default' => false,
            'description' => esc_html__('Sometimes we need your logo to use in the E+', 'eib2bpro'),
            'class' => 'mt-3',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['general']['options'][] = [
            'id' => 'full',
            'type' => 'func',
            'title' => esc_html__('Full Mode', 'eib2bpro'),
            'func' => '\EIB2BPRO\Settings\General::full_mode',
            'default' => false,
            'description' => esc_html__('If you do not enable full mode, users can use both E+ and classic WP Admin', 'eib2bpro'),
            'class' => 'rel-eib2bpro-simple',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['general']['options'][] = [
            'id' => 'autostart',
            'type' => 'onoff_single',
            'title' => esc_html__('Auto start', 'eib2bpro'),
            'default' => 0,
            'label' => esc_html__('Starts E+ automatically when full mode is disabled', 'eib2bpro'),
            'class' => 'rel-eib2bpro-simple',
            'size' => 'sm',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['general']['options'][] = [
            'id' => 'keep_data',
            'type' => 'onoff_single',
            'title' => esc_html__('Keep data on uninstall', 'eib2bpro'),
            'default' => 1,
            'label' => esc_html__("Don't delete data while uninstalling so you don't lose anything when you reinstall E+ (Recommended)", 'eib2bpro'),
            'class' => 'rel-eib2bpro-simple',
            'size' => 'sm',
            'style' => '',
            'col' => 12
        ];


        // TRACKER

        $settings['pages']['tracker']['options'][] = [
            'id' => 'tracker',
            'type' => 'big_select',
            'title' => esc_html__('Enable tracker', 'eib2bpro'),
            'opt' => [
                '1' => [
                    'title' => esc_html__('Enable', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['show' => '.rel-eib2bpro-keep']
                ],

                '0' => [
                    'title' => esc_html__('Disable', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['hide' => '.rel-eib2bpro-keep']
                ]
            ],
            'default' => 1,
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['tracker']['options'][] = [
            'id' => 'tracker-keep-data',
            'type' => 'select_group',
            'title' => esc_html__('How many days will we keep the request data?', 'eib2bpro'),
            'opt' => [1 => esc_html__('1 day', 'eib2bpro'), 7 => esc_html__('7 days', 'eib2bpro'), 31 => esc_html__('30 days', 'eib2bpro'), 93 => esc_html__('3 months', 'eib2bpro'), 186 => esc_html__('6 months', 'eib2bpro'), 366 => esc_html__('1 year', 'eib2bpro'), 726 => esc_html__('2 years', 'eib2bpro')],
            'if' => array('tracker', array(1, 2)),
            'default' => 7,
            'class' => 'rel-eib2bpro-keep',
            'style' => '',
            'col' => 12
        ];

        $settings['pages']['tracker']['options'][] = [
            'id' => 'tracker-geo',
            'type' => 'select_group',
            'title' => esc_html__('Enable Geolocation', 'eib2bpro'),
            'opt' => [1 => 'Yes', 0 => 'No'],
            'default' => 1,
            'description' => esc_html__("Try to guess the visitor's country. If you want to improve performance, you may consider turning it off.", 'eib2bpro'),
            'if' => array('tracker', array(1, 2)),
            'class' => 'rel-eib2bpro-keep',
            'size' => 'sm',
            'style' => '',
            'col' => 12
        ];

        \EIB2BPRO\Settings\Options::$settings = $settings;

        return $settings;
    }

    public static function index()
    {
        if ('active' === eib2bpro_get('a')) {
            Main::active();
            die;
        }
        echo eib2bpro_view(self::app('name'), self::app('mode'), 'main', array(
            'settings' => self::settings()
        ));
    }

    public static function logo()
    {
?>
        <div id="eib2bpro-user-logo">
            <div class="custom-img-container">
                <?php

                $upload_link = esc_url(get_upload_iframe_src('image', eib2bpro_option('logo', 0)));
                $eib2bpro_img_src = wp_get_attachment_image_src(eib2bpro_option('logo', 0), 'full');
                // Check if the array is valid
                $valid_img = is_array($eib2bpro_img_src); ?>

                <?php if ($valid_img) { ?>
                    <div class="eib2bpro-Settings_Logo p-2">
                        <a href="javascript:;" class="upload-custom-img"><img src="<?php echo esc_url($eib2bpro_img_src[0]) ?>" /></a>
                    </div>
                    <a href="javascript:;" class="upload-custom-img upload-custom-img-text"><?php esc_html_e('Set your logo', 'eib2bpro'); ?></a>
                <?php } else { ?>
                    <div class="eib2bpro-Settings_Logo text-center">
                        <a href="javascript:;" class="upload-custom-img upload-custom-img-text"><?php esc_html_e('Set your logo', 'eib2bpro'); ?></a>
                    </div>
                <?php } ?>
                <input class="custom-img-id" name="logo" type="hidden" value="<?php echo esc_attr(eib2bpro_option('logo', 0)); ?>" />
            </div>
        </div>
        <?php
    }

    public static function full_mode()
    {
        $_roles = \EIB2BPRO\Admin::roles();
        foreach ($_roles as $_role_id => $_role) {
        ?>
            <div class="pt-2"><?php eib2bpro_ui('onoff', 'full-' . $_role_id, eib2bpro_option('full-' . $_role_id, 0), ['class' => 'mr-2 switch-sm mb-1']) ?><?php eib2bpro_e($_role['name']) ?></div>
<?php
        }
    }
}
