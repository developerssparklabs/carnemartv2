<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Rules
{
    public static function run()
    {
        switch (eib2bpro_get('action')) {
            default:
                self::index();
                break;
        }
    }

    public static function index()
    {
        echo eib2bpro_view('b2b', 'admin', 'rules.list', []);
    }
}
