<?php
defined('ABSPATH') || exit;

// Estamos en: .../public/res/
// Subimos a .../public/
$public_dir = dirname(__DIR__) . '/';

// Incluimos el controlador
require_once $public_dir . 'controller/store/stores-controler.php';

// Registramos las rutas en rest_api_init
add_action('rest_api_init', static function (): void {
    (new Store_Controller())->register_routes();
});