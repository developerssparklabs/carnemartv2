<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;

class Me
{
    public static function run()
    {
        switch (eib2bpro_get('go')) {
            case 'settings':
                self::settings();
                break;
            default:
                self::index();
                break;
        }
    }

    public static function index()
    {
        echo eib2bpro_view('core', 0, 'shared.me.main');
    }

    public static function settings()
    {
        echo eib2bpro_view('core', 0, 'shared.me.settings');
    }

    public static function save_settings()
    {
        update_user_meta(get_current_user_id(), 'eib2bpro_avatar', intval(eib2bpro_post('eib2bpro_avatar')));
        eib2bpro_success('', ['after' => ['redirect_iframe' => eib2bpro_admin('core', ['action' => 'me'])]]);
    }
}
