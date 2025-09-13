<?php
/**
 * Clase SyncLocationsJson
 *
 * Esta clase se encarga de generar archivos JSON para cada ID de ubicación activo.
 * Obtiene todos los IDs de ubicación activos usando GetStatusService, y luego recupera
 * las tiendas correspondientes para cada ubicación mediante StoreLocatorService.
 *
 * Para cada ubicación que tenga tiendas, crea un archivo JSON dentro del directorio
 * cache/locations, con el nombre stores_{location_id}.json, que contiene los datos de las tiendas.
 *
 * Si el directorio de destino no existe, se crea con los permisos adecuados.
 * Los archivos JSON se generan con formato legible (pretty print) y soporte para Unicode.
 *
 * Métodos:
 *   - run(): void
 *       Ejecuta el proceso de generación de archivos JSON para cada ubicación activa.
 */
require_once dirname(__DIR__, 1) . '/includes/helpers/GetStatusService.php';
require_once dirname(__DIR__, 1) . '/includes/helpers/StoreLocatorService.php';

class SyncLocationsJson
{
    /**
     * Genera archivos JSON para cada ID de ubicación activo.
     * Si la ubicación tiene tiendas asociadas, se guarda un archivo con sus datos.
     * Los logs se registran en WooCommerce.
     */
    public static function run(): void
    {
        $location_ids = GetStatusService::get_all_term_ids();

        foreach ($location_ids as $locator_id) {
            $stores = StoreLocatorService::get_stores_by_locator_id($locator_id);

            if (empty($stores)) {
                continue;
            }

            $json_path = dirname(__DIR__, 2) . '/cache/locations/stores_' . $locator_id . '.json';
            $dir = dirname($json_path);

            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents(
                $json_path,
                json_encode($stores, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );
        }
    }
}