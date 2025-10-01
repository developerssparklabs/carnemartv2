<?php

/******************************************************************************************************************************
Plugin Name: Bafar :: ClearSales + Conekta 💰 v2
Plugin URI: https://sparklabs.com.mx
Description:  Proceso personalizado de flujos de compra Bafar entre el Agregador y Clear Sales, esto es requerido por finanzas.
Version: 1.0
Author: Dens @ Team Sparklabs

*****************************************************************************************************************************/

if (!defined("ABSPATH") || !defined('WPINC')) {
    exit();
}

// Constantes del plugin
define('_CARNEMART_CORE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('_CARNEMART_CORE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Incluir clases necesarias
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/OrderHelper.php');
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/ConektaService.php');
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/uber.php');
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/ClearSales.php');
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/WebhookClearSales.php');
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/ConektaHandler.php');
require_once(_CARNEMART_CORE_PLUGIN_DIR . 'classes/WebhookConeckta.php');

class BafarPurchaseFlow
{
    private static ?self $instance = null;
    private static ?string $apiKey_private = null;
    private static ?string $apiKey_public = null;

    private function __construct()
    {
        $location_id = $_COOKIE['wcmlim_selected_location_termid'] ?? '';

        $keys = OrderHelper::get_keys($location_id);

        //error_log("Keys: " . json_encode($keys), 3, _CARNEMART_CORE_PLUGIN_DIR . '/logs/logs_keys.log');

        new ConektaHandler($keys['public'], $keys['private']);
    }

    public static function getInstance(): ?self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function isOnConektaPage(): bool
    {
        // Solo pasa si estamos en una carga HTML de la página de pago
        if (!isset($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $uri = $_SERVER['REQUEST_URI'];

        // Verifica si es exactamente esa ruta (ajusta según tu slug real)
        return preg_match('#^/pago-conekta/?(\?.*)?$#', $uri);
    }


    public static function getPublicKey(): ?string
    {
        return self::$apiKey_public;
    }

    public static function getPrivateKey(): ?string
    {
        return self::$apiKey_private;
    }
}

$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Registrar logs de TODAS las peticiones HTTP entrantes
// add_action('init', function () use ($log_dir) {
//     $log_file = $log_dir . '/logs_http_requests.log';
//     $timestamp = date('Y-m-d H:i:s');
//     $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
//     $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
//     $headers = json_encode(getallheaders(), JSON_PRETTY_PRINT);

//     $entry = "[$timestamp] $method $uri\nHEADERS:\n$headers\n\n";
//     error_log($entry, 3, $log_file);
// });

// Desactivar reducción automática de stock
add_filter('woocommerce_payment_complete_reduce_order_stock', '__return_false');

// Controlar si se instancia o no BafarPurchaseFlow
add_action('init', function () use ($log_dir) {

    $log_cond_file = $log_dir . '/logs_flow_ignorado.log';
    $log_cond_file_executed = $log_dir . '/logs_flow_ejecutado.log';
    $timestamp = date('Y-m-d H:i:s');

    $headers = getallheaders();
    $fromConektaHeader = isset($headers['X-From-Conekta']) && $headers['X-From-Conekta'] === 'true';

    $shouldRun = BafarPurchaseFlow::isOnConektaPage() || $fromConektaHeader;

    if ($shouldRun) {
        $entry = "[$timestamp] ✅ Se ejecutó BafarPurchaseFlow (desde página o header personalizado)\n";
        //error_log($entry, 3, $log_cond_file_executed);
        BafarPurchaseFlow::getInstance();
    } else {
        $entry = "[$timestamp] 🔁 NO se ejecutó BafarPurchaseFlow\n";
        $entry .= "URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        //error_log($entry, 3, $log_cond_file);
    }
});

add_action('rest_api_init', function () use ($log_dir) {
    $log_file = $log_dir . '/logs_rest_init.log';
    $timestamp = date('Y-m-d H:i:s');
    $current_route = $_SERVER['REQUEST_URI'] ?? '';

    // ✅ Verifica si es clearsales
    if (strpos($current_route, '/wp-json/clearsales/v1/status') !== false) {
        $entry = "[$timestamp] ✅ rest_api_init ejecutado - Registro de ruta ClearSales\n";
        //error_log($entry, 3, $log_file);

        register_rest_route('clearsales/v1', '/status/', [
            'methods' => 'POST',
            'callback' => [WebhookClearSales::get_instance(), 'actualizar_orden_clearsales'],
            'permission_callback' => '__return_true',
        ]);
        return; // ⛔ No registrar conekta también
    }

    // ✅ Verifica si es conekta
    if (strpos($current_route, '/wp-json/conekta/v1/status') !== false) {
        $entry = "[$timestamp] ✅ rest_api_init ejecutado - Registro de ruta Conekta\n";
        //error_log($entry, 3, $log_file);

        register_rest_route('conekta/v1', '/status/', [
            'methods' => 'POST',
            'callback' => [WebhookConeckta::get_instance(), 'mi_webhook_actualizar_orden_conekta'],
            'permission_callback' => '__return_true',
        ]);
        return;
    }

    // ⛔ No coincide con ninguna
    $entry = "[$timestamp] ⛔ rest_api_init ignorado - No coincide con clearsales/status ni con conekta/status\n";
    //error_log($entry, 3, $log_file);
});