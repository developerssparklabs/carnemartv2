<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;

class Search
{
    public static function ajax()
    {
        $term = eib2bpro_post('q', '');
        echo eib2bpro_view('core', 0, 'shared.index.search', ['term' => $term]);
        wp_die();
    }
}
