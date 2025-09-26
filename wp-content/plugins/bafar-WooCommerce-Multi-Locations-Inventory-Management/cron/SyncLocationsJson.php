<?php

require_once dirname(__DIR__, 1) . '/includes/helpers/GetStatusService.php';
require_once dirname(__DIR__, 1) . '/includes/helpers/StoreLocatorService.php';

class SyncLocationsJson
{
    /**
     * Genera archivos JSON por cada ID de ubicación activo.
     * Guarda logs en WooCommerce.
     */
    // public static function run(): void
    // {
    //     $logger = wc_get_logger();
    //     $context = ['source' => 'sync_locations_json'];

    //     $logger->info("⏳ Iniciando ejecución del cron para sincronización de tiendas...", $context);

    //     $location_ids = GetStatusService::get_all_term_ids();

    //     foreach ($location_ids as $locator_id) {
    //         $stores = StoreLocatorService::get_stores_by_locator_id($locator_id);

    //         if (!empty($stores)) {
    //             $json_path = dirname(__DIR__, 2) . '/cache/locations/stores_' . $locator_id . '.json';

    //             $dir = dirname($json_path);
    //             if (!file_exists($dir)) {
    //                 mkdir($dir, 0755, true);
    //             }

    //             file_put_contents($json_path, json_encode($stores, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    //             $msg = "✅ JSON generado para locator_id: $locator_id con " . count($stores) . " tiendas.";
    //             error_log("📦 $msg");
    //             $logger->info($msg, $context);
    //         } else {
    //             $msg = "⚠️ No se encontraron tiendas activas para locator_id: $locator_id";
    //             error_log($msg);
    //             $logger->warning($msg, $context);
    //         }
    //     }

    //     $logger->info("✅ Finalizada la ejecución del cron de sincronización.", $context);
    // }
}
