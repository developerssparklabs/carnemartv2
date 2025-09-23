<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class Ajax extends \EIB2BPRO\Settings
{
    public static function run()
    {
        $do = eib2bpro_post('do', eib2bpro_get('do'));

        switch ($do) {
            case 'menu':
                self::saveMenu();
                break;
            case 'new-menu':
                self::newMenu();
                break;
            case 'delete-menu':
                self::deleteMenu();
                break;
            case 'reset-menu':
                self::resetMenu();
                break;
            case 'refresh-menu':
                self::refreshMenu();
                break;
            case 'colors':
                self::saveColors();
                break;
            case 'font':
                \EIB2BPRO\Settings\Theme::font();
                wp_die();
                break;
                // Theme: Top
            case 'top-widget-onoff':
                self::topWidgetOnOff();
                break;
            case 'top-widget-sort':
                self::topWidgetsSort();
                break;
            case 'edit':
                self::editOptions();
                break;
        }
    }

    public static function editOptions($success = true)
    {
        $forms = $_POST;

        // prepare
        if ('settings' === eib2bpro_post('app')) {
            if ('general' === eib2bpro_post('section')) {
                $roles = wp_roles();
                foreach ($roles->roles as $_role_id => $_role) {
                    delete_option('eib2bpro_full-' . $_role_id);
                }
            }
        }


        unset($forms['app']);
        unset($forms['action']);
        unset($forms['asnonce']);
        unset($forms['do']);
        unset($forms['section']);

        $options = [];
        foreach ($forms as $key => $value) {
            $key = sanitize_key($key);
            if (is_array($value)) {
                $value = wc_clean($value);
            } else {
                $value = sanitize_text_field($value);
            }
            $options[$key] = $value;

            if (stripos('__off', $key) > 0) {
                if (!isset($options[str_replace('__off', '', $key)])) {
                    $options[str_replace('__off', '', $key)] = 0;
                }

                unset($options[$key]);
            }
        }

        foreach ($options as $key => $value) {
            eib2bpro_option($key, $value, 'set');
        }

        if ('b2b' === eib2bpro_post('app')) {
            // Quote field positions
            if (2 === intval(eib2bpro_post('eib2bpro-app-current-tab')) || 'quote' === eib2bpro_post('tab')) {
                $i = 0;
                foreach ((array)$_POST['eib2bpro_quote_field_positions'] as $id) {
                    update_post_meta(intval($id), 'eib2bpro_position', $i);
                    ++$i;
                }
            }
        }

        if ('settings' === eib2bpro_post('app')) {
            if (0 === intval(eib2bpro_post('eib2bpro-app-current-tab')) && \EIB2BPRO\Admin::$theme !== eib2bpro_option('theme')) {
                eib2bpro_success('', ['after' => ['refresh_window' => true]]);
            }
            // Settings: Top
            if (3 === intval(eib2bpro_post('eib2bpro-app-current-tab'))) {
                self::topWidgetsSort();
                return;
            }
        }

        if ($success) {
            eib2bpro_success();
        }
    }

    public static function saveColors()
    {
        $own = ("1" === eib2bpro_option('own_themes')) ? true : false;

        $colors = array();
        foreach ($_POST['val'] as $k => $v) {
            $colors[sanitize_key($k)] = sanitize_text_field($v);
        }

        if ('custom' === $colors['key']) {
            $colors['header-top'] = wc_hex_darker(wc_format_hex($colors['header-background']), 10);
            $colors['header-more'] = wc_hex_darker(wc_format_hex($colors['header-background']), 60);
            $colors['header-text'] = wc_format_hex($colors['header-icons']);
        }

        eib2bpro_option('colors', $colors, 'set', $own);

        echo eib2bpro_r(json_encode(array(
            "status" => "success",
        )));
        wp_die();
    }

    public static function deleteMenu()
    {
        $id = sanitize_key(eib2bpro_post('id'));
        $roles = \EIB2BPRO\Admin::roles();

        foreach ($roles as $_role_id => $_role) {
            $menu = eib2bpro_option('menu_' . $_role_id, array());
            if (isset($menu[$id])) {
                unset($menu[$id]);
            }
            eib2bpro_option('menu_' . $_role_id, $menu, 'set');
        }

        eib2bpro_success('OK', array(
            'after' => array(
                'addClass' => array('container' => '#app_id_' . $id, 'class' => 'd-none')
            )
        ));
    }

    public static function newMenu()
    {
        $new = array(
            0 => eib2bpro_post('title', ''),
            'active' => 1,
            6 => eib2bpro_post('icon', ''),
            'order' => time(),
            2 => eib2bpro_post('url', '#'),
            'admin_link' => eib2bpro_post('url', '#')
        );

        if ('1' === eib2bpro_post('type', '0')) {
            $new['target'] = 1;
        }

        if ('2' === eib2bpro_post('type', '0')) {
            $new['target'] = 2;
        }

        if (!is_numeric(eib2bpro_post('type', '0'))) {
            $new['parent'] = eib2bpro_post('type', '0');
        }

        if ('0' === eib2bpro_post('role', '0')) {
            $roles = \EIB2BPRO\Admin::roles();
            foreach ($roles as $_role_id => $_role) {
                $menu = eib2bpro_option('menu_' . $_role_id, array());
                $menu["x" . md5(time() . $_role_id)] = $new;
                eib2bpro_option('menu_' . $_role_id, $menu, 'set');
            }
        } else {
            $menu = eib2bpro_option('menu_' . sanitize_key(eib2bpro_post('role')), array());
            $menu["x" . md5(time())] = $new;
            eib2bpro_option('menu_' . sanitize_key(eib2bpro_post('role')), $menu, 'set');
        }

        eib2bpro_success('OK', array(
            'after' => array(
                'refresh_window' => true
            )
        ));
    }

    public static function saveMenu()
    {
        $menus = $_POST['menu'];
        foreach ($menus as $role => $menu) {
            $next = 20;
            $new = array();
            foreach ($menu as $k => $v) {
                ++$next;
                $v['order'] = $next;
                $new[$k] = $v;
            }
            eib2bpro_option('menu_' . sanitize_key($role), wc_clean($new), 'set');
        }

        eib2bpro_success('', ['after' => ['html' => ['container' => '.eib2bpro-main-menu', 'html' => \EIB2BPRO\Admin::generateMenu()], 'addClass' => ['container' => '.eib2bpro-MainMenu', 'class' => 'overflow-hidden']]]);
    }

    public static function resetMenu()
    {
        $roles = wp_roles();
        foreach ($roles->roles as $_role_id => $_role) {
            delete_option('eib2bpro_menu_' . $_role_id);
        }

        eib2bpro_success('', array(
            'after' => array(
                'refresh_window' => true
            )
        ));
    }

    public static function refreshMenu()
    {
        return \EIB2BPRO\Admin::generateMenu();
    }

    // Themes: Top

    public static function topWidgetOnOff()
    {
        $allWidgets = \EIB2BPRO\Core\Top::allWidgets();
        $widgets = \EIB2BPRO\Core\Top::widgets();

        $id = sanitize_key(eib2bpro_post('id', 0));

        if (isset($allWidgets[$id])) {
            if (isset($widgets[$id]['active'])) {
                $widgets[$id]['active'] = ('false' === eib2bpro_post('checked', 'false')) ? 0 : 1;
            } else {
                $widgets[$id] = ['active' => ('false' === eib2bpro_post('checked', 'false')) ? 0 : 1];
            }

            if (is_array($widgets)) {
                eib2bpro_option('top-widgets', $widgets, 'set');
                eib2bpro_success('', ['after' => ['html' => ['container' => '.eib2bpro-top-widgets-container', 'html' => \EIB2BPRO\Core\Top::render()]]]);
            }
        } else {
            eib2bpro_error('');
        }
    }

    public static function topWidgetsSort()
    {
        $allWidgets = \EIB2BPRO\Core\Top::allWidgets();
        $widgets = \EIB2BPRO\Core\Top::widgets();
        $position = 0;

        foreach ($allWidgets as $w) {
            $id = $w['id'];

            if (isset($widgets[$id])) {
                $widgets[$id] = ['active' => 0, 'position' => 9999];
            }

            if (isset($_POST['top-widget-items'][$id])) {
                $widgets[$id]['active'] = (1 === intval($_POST['top-widget-items'][$id])) ? 1 : 0;
                $widgets[$id]['position'] = array_search($id, array_keys($_POST['top-widget-items']));
            }
        }

        array_multisort(array_map(function ($element) {
            return $element['position'];
        }, $widgets), SORT_ASC, $widgets);

        if (is_array($widgets)) {
            eib2bpro_option('top-widgets', $widgets, 'set');
            eib2bpro_success('', ['after' => ['html' => ['container' => '.eib2bpro-top-widgets-container', 'html' => \EIB2BPRO\Core\Top::render()]]]);
        } else {
            eib2bpro_error('');
        }
    }
}
