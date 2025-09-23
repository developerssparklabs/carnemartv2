<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Settings
{
    public static function run()
    {
        self::all();
    }

    public static function all()
    {
        $settings = [];
        $settings['guests']['options'][] = [
            'id' => 'b2b_settings_visibility_guest',
            'type' => 'big_select',
            'title' => esc_html__('How will guests see the site?', 'eib2bpro'),
            'opt' => [
                'none' => [
                    'title' => esc_html__('Do nothing', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['hide' => '.rel-eib2bpro-0']
                ],

                'hide_prices' => [
                    'title' => esc_html__('Hide prices', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['hide' => '.rel-eib2bpro-0', 'show' => '.rel-hide-prices']
                ],

                'hide_shop' => [
                    'title' => esc_html__('Restrict pages', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['hide' => '.rel-eib2bpro-0', 'show' => '.rel-hide-shop']
                ],
            ],
            'default' => 'none',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['guests']['options'][] = [
            'id' => 'b2b_settings_visibility_guest_hide_prices',
            'type' => 'select_group',
            'title' => esc_html__('Hide prices: What will appear instead of product price', 'eib2bpro'),
            'opt' => [
                'only_hide' => esc_html__('Only hide', 'eib2bpro'),
                'login_to_view' => esc_html__('"Login to view" text', 'eib2bpro'),
                'request_a_qoute' => esc_html__('"Request a quote" button', 'eib2bpro'),
                'redirect_product_to_my_account' => esc_html__('Force product pages to login', 'eib2bpro'),
            ],
            'if' => array('b2b_settings_visibility_guest', array('hide_prices')),
            'default' => 'only_hide',
            'class' => 'rel-eib2bpro-0 rel-hide-prices',
            'style' => '',
            'col' => 12
        ];

        $settings['guests']['options'][] = [
            'id' => 'b2b_settings_visibility_guest_hide_shop',
            'type' => 'select_group',
            'title' => esc_html__('Which pages will be restricted', 'eib2bpro'),
            'opt' => [
                'shop' => esc_html__('Shop/Product pages', 'eib2bpro'),
                'full_website' => esc_html__('All pages', 'eib2bpro'),
                'redirect_to_login' => esc_html__('Lock the site', 'eib2bpro'),
            ],
            'if' => array('b2b_settings_visibility_guest', array('hide_shop')),
            'default' => 'shop',
            'class' => 'rel-eib2bpro-0 rel-hide-shop',
            'style' => '',
            'col' => 12
        ];


        $settings['guests']['options'][] = [
            'id' => 'settings-group',
            'type' => 'group',
            'title' => esc_html__('Registration', 'eib2bpro'),
            'default' => '',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['guests']['options'][] = [
            'id' => 'b2b_settings_registration_default_b2c',
            'type' => 'select_group',
            'title' => esc_html__('B2C registration approval', 'eib2bpro'),
            'opt' => [
                'automatic' => esc_html__('Automatic approval', 'eib2bpro'),
                'manual' => esc_html__('Manual approval', 'eib2bpro'),
            ],
            'default' => 'automatic',
            'class' => '',
            'col' => 12
        ];


        $settings['guests']['options'][] = [
            'id' => 'b2b_settings_registration_options',
            'type' => 'onoff_group',
            'title' => '',
            'opt' => [
                'b2b_settings_registration_enable_regtype_selector' => [
                    'label' => esc_html__('Enable "Registration Type" selector at registration form', 'eib2bpro'),
                    'default' => 1,
                ]
            ],
            'default' => '',
            'class' => '',
            'style' => '',
            'size' => 'sm',
            'col' => 12
        ];

        // QUOTE

        $settings['quote']['options'][] = [
            'id' => 'b2b_settings_quote_system',
            'type' => 'big_select',
            'title' => esc_html__('Product Page - "Request a quote" button behaviour', 'eib2bpro'),
            'opt' => [
                'popup' => [
                    'title' => esc_html__('Popup', 'eib2bpro'),
                    'description' =>  esc_html__('For a single product', 'eib2bpro'),
                    'conditions' => []
                ],

                'add_to_cart' => [
                    'title' => esc_html__('Add to cart', 'eib2bpro'),
                    'description' => esc_html__('For multiple products at the same time', 'eib2bpro'),
                    'conditions' => []
                ],
            ],
            'default' => 'popup',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $groups = [
            'guest' => esc_html__('Guests', 'eib2bpro'),
            'b2c' => esc_html__('B2C users', 'eib2bpro')
        ];
        $b2b_groups = \EIB2BPRO\B2b\Admin\Groups::get();
        foreach ($b2b_groups as $group) {
            $groups[$group->ID] = get_the_title($group->ID);
        }
        $settings['quote']['options'][] = [
            'id' => 'b2b_settings_request_a_quote_on_cart',
            'type' => 'onoff_multiple',
            'title' => esc_html__('Which groups will see the "Request a quote" button in the cart?', 'eib2bpro'),
            'opt' => $groups,
            'default' => '',
            'class' => 'rel-eib2bpro-01 rel-hide-shop1',
            'style' => '',
            'size' => 'sm',
            'col' => 12
        ];

        $settings['quote']['options'][] = [
            'id' => 'b2b_settings_quote_fields',
            'type' => 'func',
            'title' => esc_html__('Quote fields', 'eib2bpro'),
            'func' => '\EIB2BPRO\B2b\Admin\Settings::quote_fields',
            'default' => '',
            'class' => 'rel-eib2bpro-01 rel-hide-shop1',
            'style' => '',
            'size' => 'sm',
            'col' => 12
        ];


        // products

        $settings['appearance']['options'][] = [
            'id' => 'b2b_settings_appearance_price_tiers',
            'type' => 'onoff_group',
            'title' => esc_html__('Product Price Tiers', 'eib2bpro'),
            'opt' => [
                'b2b_settings_appearance_show_tiers_table' => [
                    'label' => esc_html__('Show Product Price Tiers table if available', 'eib2bpro'),
                    'default' => 1,
                ],

                'b2b_settings_appearance_show_discount' => [
                    'label' => esc_html__('Show Discount Rate in the table', 'eib2bpro'),
                    'default' => 0
                ],
                'b2b_settings_tiers_show_range' => [
                    'label' => esc_html__('Show price range instead of current price', 'eib2bpro'),
                    'default' => 0
                ],
                'b2b_settings_tiers_show_range_from' => [
                    'label' => esc_html__('Show "From X" instead of price range', 'eib2bpro'),
                    'default' => 0
                ]
            ],
            'default' => '',
            'class' => '',
            'style' => '',
            'size' => 'sm',
            'col' => 12
        ];


        // OTHERS

        $settings['others']['options'][] = [
            'id' => 'settings-group',
            'type' => 'group',
            'title' => esc_html__('Endpoints', 'eib2bpro'),
            'default' => '',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['others']['options'][] = [
            'id' => 'b2b_endpoints_offers',
            'type' => 'input',
            'title' => esc_html__('Offers', 'eib2bpro'),
            'default' => 'offers',
            'class' => '',
            'style' => '',
            'col' => 4
        ];

        $settings['others']['options'][] = [
            'id' => 'b2b_endpoints_bulkorder',
            'type' => 'input',
            'title' => esc_html__('Bulk Order', 'eib2bpro'),
            'default' => 'bulk-order',
            'class' => '',
            'style' => '',
            'col' => 4
        ];

        $settings['others']['options'][] = [
            'id' => 'b2b_endpoints_quickorders',
            'type' => 'input',
            'title' => esc_html__('Quick Orders', 'eib2bpro'),
            'default' => 'quick-orders',
            'class' => '',
            'style' => '',
            'col' => 4
        ];

        // COLORS

        $settings['lang']['options'][] = [
            'id' => 'settings-group',
            'type' => 'group',
            'title' => esc_html__('Bulk Order Form / Quick Orders List', 'eib2bpro'),
            'default' => '',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['lang']['options'][] = [
            'id' => 'b2b_color_bulkorder',
            'type' => 'color',
            'title' => esc_html__('Background', 'eib2bpro'),
            'default' => '#8224e3',
            'opt' => [
                'b2b_color_bulkorder_background' => [
                    'label' => '',
                    'default' => '#8224e3',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2'
        ];
        $settings['lang']['options'][] = [
            'id' => 'b2b_color_bulkorder',
            'type' => 'color',
            'title' => esc_html__('Text', 'eib2bpro'),
            'default' => '#ffffff',
            'opt' => [
                'b2b_color_bulkorder_text' => [
                    'label' => '',
                    'default' => '#ffffff',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2'
        ];
        $settings['lang']['options'][] = [
            'id' => 'b2b_color_bulkorder',
            'type' => 'color',
            'title' => esc_html__('Input Bg.', 'eib2bpro'),
            'default' => '#ffffff',
            'opt' => [
                'b2b_color_bulkorder_input_background' => [
                    'label' => '',
                    'default' => '#ffffff',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2'
        ];

        $settings['lang']['options'][] = [
            'id' => 'b2b_color_bulkorder',
            'type' => 'color',
            'title' => esc_html__('Input Text', 'eib2bpro'),
            'default' => '#000000',
            'opt' => [
                'b2b_color_bulkorder_input_text' => [
                    'label' => '',
                    'default' => '#000000',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2'
        ];


        $settings['lang']['options'][] = [
            'id' => 'b2b_color_bulkorder_radius',
            'type' => 'range',
            'title' => esc_html__('Border Radius', 'eib2bpro'),
            'default' => 'bulk-order',
            'opt' => ['min' => 0, 'max' => 40, 'step' => 1],
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-4'
        ];



        $settings['lang']['options'][] = [
            'id' => 'settings-group',
            'type' => 'group',
            'title' => esc_html__('Offers', 'eib2bpro'),
            'default' => '',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['lang']['options'][] = [
            'id' => 'b2b_color_offers',
            'type' => 'color',
            'title' => esc_html__('Background', 'eib2bpro'),
            'default' => '#8224e3',
            'opt' => [
                'b2b_color_offers_background' => [
                    'label' => '',
                    'default' => '#8224e3',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2 border-0'
        ];
        $settings['lang']['options'][] = [
            'id' => 'b2b_color_offers',
            'type' => 'color',
            'title' => esc_html__('Text', 'eib2bpro'),
            'default' => '#ffffff',
            'opt' => [
                'b2b_color_offers_text' => [
                    'label' => '',
                    'default' => '#ffffff',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2 border-0'
        ];
        $settings['lang']['options'][] = [
            'id' => 'b2b_color_offers',
            'type' => 'color',
            'title' => esc_html__('Button Bg.', 'eib2bpro'),
            'default' => '#ffffff',
            'opt' => [
                'b2b_color_offers_button_background' => [
                    'label' => '',
                    'default' => '#ffffff',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2 border-0'
        ];

        $settings['lang']['options'][] = [
            'id' => 'b2b_color_offers',
            'type' => 'color',
            'title' => esc_html__('Button Text', 'eib2bpro'),
            'default' => '#000000',
            'opt' => [
                'b2b_color_offers_button_text' => [
                    'label' => '',
                    'default' => '#000000',
                ]
            ],
            'newline' => true,
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-2 border-0'
        ];


        $settings['lang']['options'][] = [
            'id' => 'b2b_color_offers_radius',
            'type' => 'range',
            'title' => esc_html__('Border Radius', 'eib2bpro'),
            'default' => 'bulk-order',
            'opt' => ['min' => 0, 'max' => 40, 'step' => 1],
            'class' => '',
            'style' => '',
            'col' => '12 col-lg-4 border-0'
        ];


        $settings['lang']['options'][] = [
            'id' => 'b2b_settings_empty',
            'type' => 'html',
            'title' => '',
            'html' => '<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>',
            'default' => '',
            'class' => '',
            'style' => '',
            'size' => 'sm',
            'col' => 12
        ];

        // MY ACCOUNT

        $settings['myaccount']['options'][] = [
            'id' => 'settings-group',
            'type' => 'group',
            'title' => esc_html__('Bulk Order', 'eib2bpro'),
            'default' => '',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['myaccount']['options'][] = [
            'id' => 'b2b_settings_bulkorder_layout',
            'type' => 'big_select',
            'title' => esc_html__('Bulk Order Form Layout', 'eib2bpro'),
            'opt' => [
                '1' => [
                    'title' => esc_html__('Layout 1', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['hide' => '', 'show' => '.rel-l2']
                ],

                '2' => [
                    'title' => esc_html__('Layout 2', 'eib2bpro'),
                    'description' => '',
                    'conditions' => ['hide' => '.rel-l2', 'show' => '']
                ],
            ],
            'default' => '1',
            'class' => '',
            'style' => '',
            'col' => 12
        ];

        $settings['myaccount']['options'][] = [
            'id' => 'b2b_settings_bulkorder_orderby',
            'type' => 'select_group',
            'title' => esc_html__('Sort products by', 'eib2bpro'),
            'opt' => [
                'date' => esc_html__('Date', 'eib2bpro'),
                'title' => esc_html__('Title', 'eib2bpro'),
                'menu_order' => esc_html__('Position', 'eib2bpro'),
            ],
            'if' => array('b2b_settings_bulkorder_layout', array('1', '')),
            'default' => 'date',
            'class' => 'rel-l2',
            'col' => 6
        ];


        $settings['myaccount']['options'][] = [
            'id' => 'b2b_settings_bulkorder_limit',
            'type' => 'select_group',
            'title' => esc_html__('Products per page', 'eib2bpro'),
            'opt' => [
                5 => '5',
                10 => '10',
                20 => '20',
                50 => '50',
                100 => '100',
                9999999 => esc_html__('All', 'eib2bpro'),
            ],
            'if' => array('b2b_settings_bulkorder_layout', array('1', '')),
            'default' => 10,
            'class' => 'rel-l2',
            'col' => 6
        ];

        $settings['myaccount']['options'][] = [
            'id' => 'b2b_settings_bulkorder_options',
            'type' => 'onoff_group',
            'title' => esc_html__('Others', 'eib2bpro'),
            'opt' => [

                'b2b_settings_bulkorder_images' => [
                    'label' => esc_html__('Show product images', 'eib2bpro'),
                    'default' => 1,
                ],

                'b2b_settings_bulkorder_subtotal' => [
                    'label' => esc_html__('Show subtotals and prices', 'eib2bpro'),
                    'default' => 1,
                ],

                'b2b_settings_bulkorder_outofstock' => [
                    'label' => esc_html__('Show out of stock products', 'eib2bpro'),
                    'default' => 0
                ],

                'b2b_settings_bulkorder_b2c' => [
                    'label' => esc_html__('Show to B2C customers', 'eib2bpro'),
                    'default' => 0
                ]
            ],
            'default' => '',
            'class' => '',
            'style' => '',
            'size' => 'sm',
            'col' => 12
        ];



        echo eib2bpro_view('b2b', 'admin', 'settings', array('settings' => $settings));
    }

    public static function quote_fields()
    {
        do_action('wpml_set_translation_mode_for_post_type', 'eib2bpro_quote_field', 'translate');

        $fields = get_posts([
            'post_type' => 'eib2bpro_quote_field',
            'post_status' => ['publish', 'draft'],
            'numberposts' => -1,
            'meta_key' => 'eib2bpro_position',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'suppress_filters' => EIB2BPRO_SUPPRESS_FILTERS
        ]);

        if ($fields) { ?>
            <ol class="eib2bpro-b2b-quote-fields-list eib2bpro-sortable-x">
                <?php
                foreach ($fields as $field) {
                ?>
                    <li id="quote-<?php eib2bpro_a($field->ID) ?>" class="">

                        <i class="eib2bpro-os-move ri-more-2-fill"></i> &nbsp;<a href="<?php echo eib2bpro_admin('b2b', ['section' => 'quote', 'action' => 'edit-field', 'id' => $field->ID]) ?>" class="eib2bpro-panel" data-width="550px"><?php eib2bpro_e(get_the_title($field->ID)) ?>
                            <span class="eib2bpro-hidden">&nbsp; <?php esc_html_e('Edit', 'eib2bpro'); ?></span>
                            <input name="eib2bpro_quote_field_positions[]" value="<?php eib2bpro_a($field->ID) ?>" type="hidden">
                        </a>
                    </li>
                <?php } ?>
            </ol>
            <br>
        <?php } ?>
        <a href="<?php echo eib2bpro_admin('b2b', ['section' => 'quote', 'action' => 'edit-field']) ?>" data-width="550px" class="eib2bpro-panel"><?php esc_html_e('Add new field', 'eib2bpro') ?></a>
<?php
    }

    public static function save()
    {
        \EIB2BPRO\Settings\Ajax::editOptions(false);

        // rewrite endpoint rules
        \EIB2BPRO\B2b\Site\Main::endpoints();
        flush_rewrite_rules();

        \EIB2BPRO\B2b\Admin\Main::clear_cache();

        eib2bpro_success();
    }

    public static function enable_features()
    {
        $id = eib2bpro_post('id', 'default');

        if ($id) {
            eib2bpro_option('b2b_enable_' . sanitize_key($id), 'true' === eib2bpro_post('checked', 'false') ? 1 : 0, 'set');
        }

        // rewrite endpoint rules
        \EIB2BPRO\B2b\Site\Main::endpoints();
        flush_rewrite_rules();

        // fine
        if ('admin_panel' === $id) {
            if (0 === eib2bpro_option('b2b_enable_' . sanitize_key($id), 0)) {
                update_option('eib2bpro_theme_old', eib2bpro_option('theme', 'one'), false);
                $_roles = \EIB2BPRO\Admin::roles();
                foreach ($_roles as $_role_id => $_role) {
                    eib2bpro_option('full-' . $_role_id, 0, 'set');
                }
                eib2bpro_option('theme', 'one', 'set');
            } else {
                eib2bpro_option('theme', eib2bpro_option('theme_old', 'one'), 'set');
            }
            eib2bpro_success('', ['after' => ['redirect' => eib2bpro_admin('b2b', ['section' => 'settings'])]]);
        }
        eib2bpro_success();
    }
}
