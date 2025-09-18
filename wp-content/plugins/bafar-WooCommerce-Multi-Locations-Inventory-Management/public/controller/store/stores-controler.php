<?php
if (!class_exists('Store_Controller')) {
    class Store_Controller
    {
        private string $namespace = 'wcmlim/v1';
        public function register_routes(): void
        {
            register_rest_route(
                $this->namespace,
                '/get-stores-by-coordinates',
                [
                    [
                        'methods' => 'GET',
                        'callback' => [$this, 'getStoresByCoor'],
                        'permission_callback' => '__return_true',
                        'args' => [
                            'lat' => [
                                'required' => true,
                                'type' => 'number',
                                'sanitize_callback' => function ($value, $request, $param): float|WP_Error {
                                    if (!is_numeric($value)) {
                                        return new WP_Error('rest_invalid_param', 'lat debe ser numérico.', ['status' => 400]);
                                    }
                                    $v = (float) $value;
                                    if ($v < -90 || $v > 90) {
                                        return new WP_Error('rest_invalid_param', 'lat fuera de rango (-90 a 90).', ['status' => 400]);
                                    }
                                    return $v;
                                },
                            ],
                            'lng' => [
                                'required' => true,
                                'type' => 'number',
                                'sanitize_callback' => function ($value, $request, $param): float|WP_Error {
                                    if (!is_numeric($value)) {
                                        return new WP_Error('rest_invalid_param', 'lng debe ser numérico.', ['status' => 400]);
                                    }
                                    $v = (float) $value;
                                    if ($v < -180 || $v > 180) {
                                        return new WP_Error('rest_invalid_param', 'lng fuera de rango (-180 a 180).', ['status' => 400]);
                                    }
                                    return $v;
                                },
                            ],
                        ],
                    ],
                ]
            );
            register_rest_route(
                $this->namespace,
                '/get-store-by-postal',
                [
                    [
                        'methods' => 'GET',
                        'callback' => [$this, 'getStoreByPostal'],
                        'permission_callback' => '__return_true',
                        'args' => [
                            'cp' => [
                                'required' => true,
                                'type' => 'string',
                                // Valida que traiga al menos un dígito
                                'validate_callback' => function ($value) {
                                    return is_string($value) && preg_match('/\d/', $value);
                                },
                                // Sanitiza a 5 dígitos (padding con ceros a la izquierda)
                                'sanitize_callback' => function ($value) {
                                    $digits = preg_replace('/\D/', '', (string) $value);
                                    if ($digits === '') {
                                        return new WP_Error('rest_invalid_param', 'cp debe contener dígitos.', ['status' => 400]);
                                    }
                                    return str_pad(substr($digits, 0, 5), 5, '0', STR_PAD_LEFT);
                                },
                            ],
                        ],
                    ]
                ]
            );
        }

        /**
         * Devuelve hasta 5 tiendas (términos de la taxonomía `locations`)
         * cercanas a las coordenadas recibidas y **solo** aquellas que estén
         * realmente asociadas a un Estado mediante `wcmlim_locator` → `location_group`.
         *
         * Cómo trabaja:
         *  - Calcula una caja de ~50 km en torno a las coordenadas para acotar el espacio.
         *  - Dentro de esa caja ordena por distancia Haversine y toma hasta 5 resultados.
         *  - Si no alcanza el cupo, completa con las más cercanas del universo completo,
         *    manteniendo el orden por distancia y sin repetir las ya seleccionadas.
         *  - Si se recibe `state_id`, filtra por ese término de `location_group`.
         *
         * Requisitos de datos en `wp_termmeta`:
         *  - `wcmlim_lat` y `wcmlim_lng` son necesarios para calcular distancias.
         *  - `wcmlim_locator` debe apuntar a un término válido de la taxonomía `location_group`.
         *  - Metadatos como calle, ruta, localidad, CP, etc., son opcionales pero recomendados.
         *
         * Indices sugeridos para buen rendimiento:
         *  - `CREATE INDEX idx_termmeta_key_term   ON wp_termmeta (meta_key(32), term_id);`
         *  - `CREATE INDEX idx_termmeta_key_value  ON wp_termmeta (meta_key(32), meta_value(16));`
         *  - `CREATE INDEX idx_tt_taxonomy_term    ON wp_term_taxonomy (taxonomy, term_id);`
         *
         * Entrada esperada (`$req`):
         *  - `lat`  (float)  coordenada del usuario
         *  - `lng`  (float)  coordenada del usuario
         *  - `state_id` (int, opcional) ID del término en `location_group` para filtrar
         *
         * Salida:
         *  - `WP_REST_Response` con `ok`, `received` y `stores` (arreglo de tiendas normalizado).
         */
        public function getStoresByCoor(WP_REST_Request $req): WP_REST_Response
        {
            global $wpdb;

            $lat = (float) $req->get_param('lat');
            $lng = (float) $req->get_param('lng');
            $stateId = (int) $req->get_param('state_id');

            if (!is_finite($lat) || !is_finite($lng)) {
                return new WP_REST_Response([
                    'ok' => false,
                    'message' => 'Parámetros inválidos: lat/lng',
                ], 400);
            }

            // Radio "prioritario" (km) para construir la caja de búsqueda
            $radiusKm = 50.0;

            // Cálculo de la bounding box alrededor del usuario
            $deg = $radiusKm / 111.045;
            $minLat = $lat - $deg;
            $maxLat = $lat + $deg;
            $cosLat = max(cos(deg2rad($lat)), 0.01); // evita divisiones extremas cerca de los polos
            $minLng = $lng - $deg / $cosLat;
            $maxLng = $lng + $deg / $cosLat;

            // Tiendas: términos de `locations`
            $joinLocations = "
        INNER JOIN {$wpdb->term_taxonomy} tt
           ON tt.term_id = t.term_id
          AND tt.taxonomy = 'locations'
    ";

            // Estado real: `wcmlim_locator` → término válido en `location_group`
            // Se usa INNER JOIN para descartar tiendas sin asociación
            $joinState = "
        INNER JOIN {$wpdb->termmeta} locmeta
           ON locmeta.term_id = t.term_id
          AND locmeta.meta_key = 'wcmlim_locator'
        INNER JOIN {$wpdb->terms} t_state
           ON t_state.term_id = CAST(locmeta.meta_value AS UNSIGNED)
        INNER JOIN {$wpdb->term_taxonomy} tt_state
           ON tt_state.term_id = t_state.term_id
          AND tt_state.taxonomy = 'location_group'
    ";

            // Distancia Haversine en km con las coordenadas del usuario
            $distanceExpr = $wpdb->prepare("
        (6371 * ACOS(
            COS(RADIANS(%f)) * COS(RADIANS(CAST(latmeta.meta_value AS DECIMAL(10,6))))
          * COS(RADIANS(CAST(lngmeta.meta_value AS DECIMAL(10,6))) - RADIANS(%f))
          + SIN(RADIANS(%f)) * SIN(RADIANS(CAST(latmeta.meta_value AS DECIMAL(10,6))))
        ))
    ", $lat, $lng, $lat);

            // Filtro opcional por estado
            $whereState = '';
            if ($stateId > 0) {
                $whereState = $wpdb->prepare(' AND tt_state.term_id = %d ', $stateId);
            }

            // SELECT base común a la búsqueda local y al fallback
            $selectBase = "SELECT
            t.term_id,
            t.name AS store_name,
            t_state.term_id AS state_term_id,
            t_state.name    AS state_name,
            CAST(latmeta.meta_value AS DECIMAL(10,6)) AS lat,
            CAST(lngmeta.meta_value AS DECIMAL(10,6)) AS lng,
            {$distanceExpr} AS distance_km,
            street.meta_value   AS street_number,
            route.meta_value    AS route,
            locality.meta_value AS locality,
            postal.meta_value   AS postal_code,
            country.meta_value  AS country,
            phone.meta_value    AS phone,
            email.meta_value    AS email
        FROM {$wpdb->terms} t
        {$joinLocations}
        {$joinState}
        INNER JOIN {$wpdb->termmeta} latmeta
            ON latmeta.term_id = t.term_id AND latmeta.meta_key = 'wcmlim_lat'
        INNER JOIN {$wpdb->termmeta} lngmeta
            ON lngmeta.term_id = t.term_id AND lngmeta.meta_key = 'wcmlim_lng'
        LEFT JOIN {$wpdb->termmeta} street
            ON street.term_id = t.term_id AND street.meta_key = 'wcmlim_street_number'
        LEFT JOIN {$wpdb->termmeta} route
            ON route.term_id  = t.term_id AND route.meta_key  = 'wcmlim_route'
        LEFT JOIN {$wpdb->termmeta} locality
            ON locality.term_id = t.term_id AND locality.meta_key = 'wcmlim_locality'
        LEFT JOIN {$wpdb->termmeta} postal
            ON postal.term_id = t.term_id AND postal.meta_key = 'wcmlim_postal_code'
        LEFT JOIN {$wpdb->termmeta} country
            ON country.term_id = t.term_id AND country.meta_key = 'wcmlim_country'
        LEFT JOIN {$wpdb->termmeta} phone
            ON phone.term_id = t.term_id AND phone.meta_key = 'wcmlim_phone'
        LEFT JOIN {$wpdb->termmeta} email
            ON email.term_id = t.term_id AND email.meta_key = 'wcmlim_email'
        WHERE latmeta.meta_value <> '' AND lngmeta.meta_value <> ''
        {$whereState}
    ";

            // Búsqueda prioritaria: solo dentro de la caja
            $sqlLocal = $selectBase . $wpdb->prepare("
        AND CAST(latmeta.meta_value AS DECIMAL(10,6)) BETWEEN %f AND %f
        AND CAST(lngmeta.meta_value AS DECIMAL(10,6)) BETWEEN %f AND %f
        ORDER BY distance_km ASC
        LIMIT 5
    ", $minLat, $maxLat, $minLng, $maxLng);

            $rows = $wpdb->get_results($sqlLocal, ARRAY_A);

            // Si no se alcanzó el cupo, completar con las más cercanas del resto
            if (count($rows) < 5) {
                $faltan = 5 - count($rows);
                $excluir = array_map(fn($r) => (int) $r['term_id'], $rows);

                $notIn = '';
                if (!empty($excluir)) {
                    $placeholders = implode(',', array_fill(0, count($excluir), '%d'));
                    $notIn = ' AND t.term_id NOT IN (' . $placeholders . ') ';
                    $notIn = $wpdb->prepare($notIn, ...$excluir);
                }

                $sqlFallback = $selectBase . $notIn . $wpdb->prepare("
            ORDER BY distance_km ASC
            LIMIT %d
        ", $faltan);

                $extra = $wpdb->get_results($sqlFallback, ARRAY_A);
                if (!empty($extra)) {
                    $rows = array_merge($rows, $extra);
                }
            }

            // Normalización de salida
            $stores = array_map(static function (array $r) {
                return [
                    'term_id' => (int) $r['term_id'],
                    'name' => (string) $r['store_name'],
                    'state_id' => (int) ($r['state_term_id'] ?? 0),
                    'state' => (string) ($r['state_name'] ?? ''),
                    'locality' => (string) ($r['locality'] ?? ''),
                    'route' => (string) ($r['route'] ?? ''),
                    'streetNumber' => (string) ($r['street_number'] ?? ''),
                    'postalCode' => (string) ($r['postal_code'] ?? ''),
                    'country' => (string) ($r['country'] ?? ''),
                    'phone' => (string) ($r['phone'] ?? ''),
                    'email' => (string) ($r['email'] ?? ''),
                    'lat' => (float) $r['lat'],
                    'lng' => (float) $r['lng'],
                    'distance_km' => round((float) $r['distance_km'], 3),
                ];
            }, $rows ?? []);

            return new WP_REST_Response([
                'ok' => true,
                'received' => ['lat' => $lat, 'lng' => $lng, 'state_id' => $stateId ?: null],
                'stores' => $stores,
            ], 200);
        }

        /**
         * Devuelve una o varias tiendas por **código postal** dentro de la taxonomía `locations`,
         * asegurando que cada tienda esté asociada a un Estado real mediante `wcmlim_locator`
         * que apunta a un término de `location_group`.
         *
         * Cómo trabaja:
         *  - Normaliza el CP con `TRIM` y `LPAD` a 5 caracteres usando la misma collation.
         *  - Aplica INNER JOIN contra el locator y la taxonomía `location_group` para garantizar
         *    la pertenencia a un Estado.
         *  - No calcula distancias.
         *
         * Parámetros de entrada:
         *  - `cp`    (string) requerido. Se normaliza a 5 dígitos con ceros a la izquierda.
         *  - `limit` (int)     opcional. Cantidad de tiendas a devolver, entre 1 y 10. Valor por defecto: 1.
         *
         * Respuesta:
         *  - `WP_REST_Response` con `ok`, `received` y `store` (arreglo), manteniendo la clave esperada en el front.
         *
         * Índices recomendados:
         *  - `CREATE INDEX idx_termmeta_key_term   ON wp_termmeta (meta_key(32), term_id);`
         *  - `CREATE INDEX idx_termmeta_key_value  ON wp_termmeta (meta_key(32), meta_value(16));`
         *  - `CREATE INDEX idx_tt_taxonomy_term    ON wp_term_taxonomy (taxonomy, term_id);`
         */
        public function getStoreByPostal(WP_REST_Request $req): WP_REST_Response
        {
            global $wpdb;

            $cpRaw = (string) $req->get_param('cp');
            $limit = (int) ($req->get_param('limit') ?? 1);
            $limit = max(1, min(10, $limit));

            // Tiendas: términos de `locations`
            $joinLocations = "
                INNER JOIN {$wpdb->term_taxonomy} tt
                ON tt.term_id = t.term_id
                AND tt.taxonomy = 'locations'
            ";

            // Estado real: `wcmlim_locator` → término válido en `location_group`
            $joinState = "
                INNER JOIN {$wpdb->termmeta} locmeta
                ON locmeta.term_id = t.term_id
                AND locmeta.meta_key = 'wcmlim_locator'
                INNER JOIN {$wpdb->terms} t_state
                ON t_state.term_id = CAST(locmeta.meta_value AS UNSIGNED)
                INNER JOIN {$wpdb->term_taxonomy} tt_state
                ON tt_state.term_id = t_state.term_id
                AND tt_state.taxonomy = 'location_group'
            ";

            $sql = "SELECT
                t.term_id,
                t.name AS store_name,
                t_state.term_id AS state_term_id,
                t_state.name    AS state_name,
                CAST(latmeta.meta_value AS DECIMAL(10,6)) AS lat,
                CAST(lngmeta.meta_value AS DECIMAL(10,6)) AS lng,
                street.meta_value   AS street_number,
                route.meta_value    AS route,
                locality.meta_value AS locality,
                postal.meta_value   AS postal_code,
                country.meta_value  AS country,
                phone.meta_value    AS phone,
                email.meta_value    AS email
            FROM {$wpdb->terms} t
            {$joinLocations}
            {$joinState}
            INNER JOIN {$wpdb->termmeta} postal
                ON postal.term_id = t.term_id AND postal.meta_key = 'wcmlim_postal_code'
            LEFT  JOIN {$wpdb->termmeta} latmeta
                ON latmeta.term_id = t.term_id AND latmeta.meta_key = 'wcmlim_lat'
            LEFT  JOIN {$wpdb->termmeta} lngmeta
                ON lngmeta.term_id = t.term_id AND lngmeta.meta_key = 'wcmlim_lng'
            LEFT  JOIN {$wpdb->termmeta} street
                ON street.term_id = t.term_id AND street.meta_key = 'wcmlim_street_number'
            LEFT  JOIN {$wpdb->termmeta} route
                ON route.term_id  = t.term_id AND route.meta_key  = 'wcmlim_route'
            LEFT  JOIN {$wpdb->termmeta} locality
                ON locality.term_id = t.term_id AND locality.meta_key = 'wcmlim_locality'
            LEFT  JOIN {$wpdb->termmeta} country
                ON country.term_id = t.term_id AND country.meta_key = 'wcmlim_country'
            LEFT  JOIN {$wpdb->termmeta} phone
                ON phone.term_id = t.term_id AND phone.meta_key = 'wcmlim_phone'
            LEFT  JOIN {$wpdb->termmeta} email
                ON email.term_id = t.term_id AND email.meta_key = 'wcmlim_email'
            WHERE
                -- Normalización del CP: TRIM + LPAD a 5 con la misma collation a ambos lados
                LPAD(TRIM(postal.meta_value) COLLATE utf8mb4_general_ci, 5, '0')
                =
                LPAD(TRIM(%s)                COLLATE utf8mb4_general_ci, 5, '0')
            ORDER BY t.name ASC
            LIMIT %d
        ";

            // Prepara e inyecta parámetros (cp y limit)
            $prepared = $wpdb->prepare($sql, $cpRaw, $limit);
            $rows = $wpdb->get_results($prepared, ARRAY_A);

            // Normalización de salida
            $stores = array_map(static function (array $r) {
                return [
                    'term_id' => (int) $r['term_id'],
                    'name' => (string) $r['store_name'],
                    'state_id' => (int) ($r['state_term_id'] ?? 0),
                    'state' => (string) ($r['state_name'] ?? ''),
                    'locality' => (string) ($r['locality'] ?? ''),
                    'route' => (string) ($r['route'] ?? ''),
                    'streetNumber' => (string) ($r['street_number'] ?? ''),
                    'postalCode' => (string) ($r['postal_code'] ?? ''),
                    'country' => (string) ($r['country'] ?? ''),
                    'phone' => (string) ($r['phone'] ?? ''),
                    'email' => (string) ($r['email'] ?? ''),
                    'lat' => is_null($r['lat']) ? null : (float) $r['lat'],
                    'lng' => is_null($r['lng']) ? null : (float) $r['lng'],
                    'distance_km' => null, // este endpoint no calcula distancia
                ];
            }, $rows ?? []);

            return new WP_REST_Response([
                'ok' => true,
                'received' => ['cp' => $cpRaw, 'limit' => $limit],
                'store' => $stores, // clave esperada en el front
            ], 200);
        }
    }
}