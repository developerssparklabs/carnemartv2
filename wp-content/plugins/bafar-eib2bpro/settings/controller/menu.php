<?php

namespace EIB2BPRO\Settings;

defined('ABSPATH') || exit;

class Menu extends \EIB2BPRO\Settings
{
    public static function settings()
    {
        return array(
            'title' => array(
                'icon' => 'ri-menu-4-fill',
                'title' => esc_html__('Menu', 'eib2bpro'),
                'description' => esc_html__('Change menu items', 'eib2bpro'),
                'save_button' => 'hidden',
                'buttons' => array(
                    array(
                        'text' => esc_html__('New', 'eib2bpro'),
                        'class' => 'eib2bpro-panel',
                        'width' => '500px',
                        'href' => eib2bpro_admin('settings', array('section' => 'menu', 'do' => 'new'))
                    )
                )
            )
        );
    }

    public static function index()
    {
        switch (eib2bpro_get('do')) {
            case 'new':
                self::newMenu();
                break;
            default:
                self::page();
                break;
        }
    }

    public static function newMenu()
    {
        if ($_POST) {
            echo "1";
        }

        $roles = array();
        $_roles = \EIB2BPRO\Admin::roles();
        $roles[0] = esc_html__('All', 'eib2bpro');
        foreach ($_roles as $_role_id => $_role) {
            $roles[$_role_id] = $_role['name'];
        }

        echo eib2bpro_view(self::app('name'), self::app('mode'), 'menu.new', array(
            'roles' => $roles
        ));
    }

    public static function page()
    {
        global $submenu;
        $roles = \EIB2BPRO\Admin::roles();
        $menu = array();

        $apps = apply_filters('eib2bpro_apps', array());
        $next = 100;

        $_menu = $GLOBALS['menu'];
        foreach ($roles as $_role_id => $_role) {
            $menu[$_role_id]['role'] = $_role['name'];
            $menu[$_role_id]['menu'] = eib2bpro_option('menu_' . $_role_id, array());

            foreach ($menu[$_role_id]['menu'] as $menu_id => $menu_v) {
                $menu_v['hide'] = 0;
                $menu[$_role_id]['menu'][$menu_id] = $menu_v;
            }

            foreach ($apps as $app_id => $app) {
                if (!isset($menu[$_role_id]['menu'][$app_id])) {
                    $menu[$_role_id]['menu'][$app_id]['hide'] = 0;
                    $menu[$_role_id]['menu'][$app_id]['active'] = 1;
                    $menu[$_role_id]['menu'][$app_id][0] = $app['title'];
                    $menu[$_role_id]['menu'][$app_id][6] = $app['icon'];
                    $menu[$_role_id]['menu'][$app_id]['order'] = $app['order'];
                    $menu[$_role_id]['menu'][$app_id][2] = eib2bpro_admin($app_id);
                } else {
                    $menu[$_role_id]['menu'][$app_id]['hide'] = 0;
                    $menu[$_role_id]['menu'][$app_id][0] = $app['title'];
                    $menu[$_role_id]['menu'][$app_id]['eix'] = 1;
                }
            }

            foreach ($_menu as $__menu) {
                ++$next;
                if (isset($__menu[5]) && $__menu[5] !== "toplevel_page_eib2bpro") {
                    $cap = $__menu[1];

                    $menu_id = md5($__menu[2]);
                    if (!isset($menu[$_role_id]['menu'][$menu_id])) {
                        $__menu['active'] = 1;
                        $__menu['order'] = $next;
                    } else {
                        $__menu['active'] = intval(isset($menu[$_role_id]['menu'][$menu_id]['active']) ?: 0);
                        $__menu['order'] = intval($menu[$_role_id]['menu'][$menu_id]['order']);
                        $__menu[6] = $menu[$_role_id]['menu'][$menu_id][6];
                    }
                    if (isset($_role['capabilities'][$cap])) {
                        $menu[$_role_id]['menu'][$menu_id] = $__menu;
                    } else {
                        if (isset($menu[$_role_id]['menu'][$menu_id])) {
                            unset($menu[$_role_id]['menu'][$menu_id]);
                        }
                    }
                }
            }

            array_multisort(array_map(function ($element) {
                return $element['order'];
            }, $menu[$_role_id]['menu']), SORT_ASC, $menu[$_role_id]['menu']);
        }

        echo eib2bpro_view(self::app('name'), self::app('mode'), 'menu.list', array(
            'settings' => self::settings(),
            'menu' => $menu
        ));
    }
}
