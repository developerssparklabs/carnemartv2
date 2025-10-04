<?php
if (!defined('ABSPATH')) exit;

// Carga las dependencias necesarias del módulo
require_once __DIR__ . '/view/SearchForm.php';
require_once __DIR__ . '/controller/SearchStoreController.php';

// Registrar el shortcode
add_action('init', function () {
    add_shortcode('tiendas_section', ['SearchStoreController', 'render_formulario']);
});
