<?php
/**
 * Clase GetStatusService
 *
 * Proporciona métodos para obtener los IDs de los términos de todos los estados disponibles 
 * (términos padres) que tengan asociado el meta key 'wcmlim_locator'.
 */
class GetStatusService
{
    /**
     * Obtiene una lista de IDs únicos de términos (estados) que tienen el meta key 'wcmlim_locator'.
     *
     * @global wpdb $wpdb Objeto de abstracción de base de datos de WordPress.
     *
     * @return int[] Arreglo de IDs de términos como enteros.
     */
    public static function get_all_term_ids(): array
    {
        global $wpdb;

        // Consulta para obtener IDs distintos de términos con el meta key 'wcmlim_locator'.
        $results = $wpdb->get_col(
            "
            SELECT DISTINCT t.term_id
            FROM {$wpdb->terms} AS t
            INNER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id
            WHERE tm.meta_key = 'wcmlim_locator'
            "
        );

        return array_map('intval', $results);
    }
}