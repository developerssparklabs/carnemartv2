<?php
/**
 * Plugin Name: Multilocations Acciones nueva
 * description: Realizamos acciones para multilocations, short_code y demas.
 */

define('DIR_MULTI_LOCATIONS_ACTIONS_NEW', plugin_dir_path(__FILE__));

// Incluir shortcodes
require_once DIR_MULTI_LOCATIONS_ACTIONS_NEW . 'short-code/home.php';