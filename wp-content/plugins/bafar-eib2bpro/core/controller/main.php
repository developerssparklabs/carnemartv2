<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;

class Main
{
    /**
     * Starts everything
     *
     * @return void
     */

    public static function run()
    {
        self::route();
    }

    public static function scripts()
    {
    }

    /**
     * Router for sub pages
     *
     * @return void
     */

    public static function route()
    {
        switch (eib2bpro_get('action')) {
            case 'me':
                \EIB2BPRO\Core\Me::run();
                break;
        }
    }
}
