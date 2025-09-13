<?php
/**
 * La clase StoreLocatorService proporciona métodos auxiliares para recuperar información de tiendas
 * basada en metadatos personalizados en WooCommerce Multi-Locations Inventory Management.
 *
 * Métodos:
 *   - get_stores_by_locator_id(int $locator_id): array
 *       Devuelve un arreglo de tiendas activas asociadas a un ID de sucursal (locator).
 *       Cada tienda contiene 'term_id', 'vkey' y 'name'.
 *
 * Uso:
 *   StoreLocatorService::get_stores_by_locator_id($locator_id);
 *
 * @package bafar-WooCommerce-Multi-Locations-Inventory-Management
 */
class StoreLocatorService
{
    /**
     * Devuelve todas las tiendas activas asociadas a un ID de sucursal (locator).
     *
     * @param int $locator_id El ID de la sucursal (metadato 'wcmlim_locator').
     * @return array Lista de tiendas filtradas.
     */
    public static function get_stores_by_locator_id(int $locator_id): array
    {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT t.term_id, t.name
            FROM {$wpdb->terms} AS t
            INNER JOIN {$wpdb->termmeta} AS tm1 ON t.term_id = tm1.term_id
            INNER JOIN {$wpdb->termmeta} AS tm2 ON t.term_id = tm2.term_id
            WHERE tm1.meta_key = 'wcmlim_locator' AND tm1.meta_value = %s
            AND tm2.meta_key = 'centro_activo' AND tm2.meta_value = '1'
        ", $locator_id));

        $stores = [];

        foreach ($results as $i => $row) {
            $stores[] = [
                'term_id' => strval($row->term_id),
                'vkey' => $i,
                'name' => $row->name,
            ];
        }

        return $stores;
    }
}
