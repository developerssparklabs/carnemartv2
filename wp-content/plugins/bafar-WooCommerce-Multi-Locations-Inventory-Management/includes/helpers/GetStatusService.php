<?php

/**
 * Clase GetStatusService
 *
 * Recupera únicamente los term_id de todos los estados disponibles,
 * es decir, términos padres que tengan asociado un wcmlim_locator.
 */
class GetStatusService
{
    /**
     * Obtiene una lista de term_id de los estados únicos.
     *
     * @return array Lista de term_id como enteros.
     */
    public static function get_all_term_ids(): array
    {
        global $wpdb;

        $results = $wpdb->get_col("
            SELECT DISTINCT t.term_id
            FROM {$wpdb->terms} AS t
            INNER JOIN {$wpdb->termmeta} AS tm ON t.term_id = tm.term_id
            WHERE tm.meta_key = 'wcmlim_locator'
        ");

        return array_map('intval', $results);
    }
}
