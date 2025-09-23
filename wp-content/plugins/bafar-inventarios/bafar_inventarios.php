<?php
/******************************************************************************************************************************
 * Plugin Name: Bafar :: Inventarios 📦
 * Plugin URI: https://sparklabs.com.mx
 * Description: Soporte para gestión de inventarios 
 * Version: 1.0
 * Author: Sergio @  Sparklabs
 ******************************************************************************************************************************/

/*******************************************************
 * Funcionalidades inventario sincronización de stock
 ********************************************************/

// Agregar un logger global para WooCommerce
if (!function_exists('log_woocommerce')) {
    function log_woocommerce($message)
    {
        if (class_exists('WC_Logger')) {
            $logger = new WC_Logger();
            $logger->add('bafar_inventarios_stock_120225', $message);
        }
    }
    function log_woocommerce_tiers($message)
    {
        if (class_exists('WC_Logger')) {
            $logger = new WC_Logger();
            $log_name = 'bafar_inventarios_precios_tiers_110225';
            $logger->add($log_name, $message);
        }
    }
}

/**
 * Campos extra para la taxonomía 'locations'
 * - uber_activo:   1 (sí)     / 0 (no)
 *
 * Guarda en term meta: 'uber_activo'
 */

/* ---------- ADD (pantalla de crear término) ---------- */
function cm_locations_add_fields()
{
    // Valores por defecto al crear (ajusta si quieres)
    $default_uber = '1';
    ?>
    <?php wp_nonce_field('cm_locations_meta_nonce_action', 'cm_locations_meta_nonce'); ?>


    <div class="form-field term-group-wrap">
        <label for="uber_activo"><?php _e('Uber (1 sí, 0 no)', 'text_domain'); ?></label>
        <select id="uber_activo" name="uber_activo">
            <option value="1" <?php selected($default_uber, '1'); ?>><?php _e('Sí (disponible)', 'text_domain'); ?></option>
            <option value="0" <?php selected($default_uber, '0'); ?>><?php _e('No (no disponible)', 'text_domain'); ?>
            </option>
        </select>
        <p class="description"><?php _e('Habilita o deshabilita el envío por Uber para esta ubicación.', 'text_domain'); ?>
        </p>
    </div>
    <?php
}
add_action('locations_add_form_fields', 'cm_locations_add_fields');


/* ---------- EDIT (pantalla de editar término) ---------- */
function cm_locations_edit_fields($term)
{
    $uber_activo = get_term_meta($term->term_id, 'uber_activo', true);

    // Defaults si no existen aún
    $uber_activo = ($uber_activo === '' ? '1' : $uber_activo);
    ?>
    <?php wp_nonce_field('cm_locations_meta_nonce_action', 'cm_locations_meta_nonce'); ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="uber_activo"><?php _e('Uber (1 sí, 0 no)', 'text_domain'); ?></label></th>
        <td>
            <select id="uber_activo" name="uber_activo">
                <option value="1" <?php selected($uber_activo, '1'); ?>><?php _e('Sí (disponible)', 'text_domain'); ?>
                </option>
                <option value="0" <?php selected($uber_activo, '0'); ?>><?php _e('No (no disponible)', 'text_domain'); ?>
                </option>
            </select>
            <p class="description">
                <?php _e('Habilita o deshabilita el envío por Uber para esta ubicación.', 'text_domain'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('locations_edit_form_fields', 'cm_locations_edit_fields');


/* ---------- SAVE (crear y editar) ---------- */
function cm_locations_save_meta($term_id)
{
    // Nonce (opcional pero recomendado)
    if (
        !isset($_POST['cm_locations_meta_nonce']) ||
        !wp_verify_nonce($_POST['cm_locations_meta_nonce'], 'cm_locations_meta_nonce_action')
    ) {
        // Si no hay nonce, igualmente guarda de forma tolerante, o sal temprano:
        return;
    }

    // Sanitizar a 0/1
    $uber = isset($_POST['uber_activo']) ? (string) $_POST['uber_activo'] : '';

    if ($uber !== '') {
        $uber_val = in_array($uber, ['0', '1'], true) ? $uber : '1';
        update_term_meta($term_id, 'uber_activo', $uber_val);
    }
}
add_action('created_locations', 'cm_locations_save_meta', 10, 1);
add_action('edited_locations', 'cm_locations_save_meta', 10, 1);


/**
 * Agregar campo Centro personalizado a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_centro_location($tag)
{
    $centro_location = get_term_meta($tag->term_id, 'centro_location', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="centro_location"><?php _e('Centro', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="centro_location" name="centro_location"
                value="<?php echo esc_attr($centro_location) ? esc_attr($centro_location) : ''; ?>">
        </td>
    </tr>
    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_centro_location');

/**
 * Guardar el valor del campo personalizado Centro.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_centro_location($term_id)
{
    if (isset($_POST['centro_location'])) {
        update_term_meta($term_id, 'centro_location', sanitize_text_field($_POST['centro_location']));
    }
}
add_action('edited_locations', 'guardar_campo_centro_location');
add_action('create_locations', 'guardar_campo_centro_location');


/**
 * Agregar campo Centro personalizado a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_centro_activo($tag)
{
    $centro_activo = get_term_meta($tag->term_id, 'centro_activo', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="centro_activo"><?php _e('Activo (1 activo , 2 desactivado)', 'text_domain'); ?></label>
        </th>
        <td>
            <input type="text" id="centro_activo" name="centro_activo"
                value="<?php echo esc_attr($centro_activo) ? esc_attr($centro_activo) : ''; ?>">
        </td>
    </tr>
    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_centro_activo');

/**
 * Guardar el valor del campo personalizado Centro.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_centro_activo($term_id)
{
    if (isset($_POST['centro_activo'])) {
        update_term_meta($term_id, 'centro_activo', sanitize_text_field($_POST['centro_activo']));
    }
}
add_action('edited_locations', 'guardar_campo_centro_activo');
add_action('create_locations', 'guardar_campo_centro_activo');


/**
 * Agregar campo api_key_public personalizado a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_location_api_key_public($tag)
{

    ?>

    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_location_api_key_public');


/**
 * Agregar campo IDSharedCategory personalizado a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_shared_catalog($tag)
{
    $shared_catalog = get_term_meta($tag->term_id, 'shared_catalog', true);
    $api_key = get_term_meta($tag->term_id, 'location_api_key', true);


    $sandbox_api_key = get_term_meta($tag->term_id, 'sandbox_location_api_key', true);

    $location_api_key_public = get_term_meta($tag->term_id, 'location_api_key_public', true);
    $sandbox_location_api_key_public = get_term_meta($tag->term_id, 'sandbox_location_api_key_public', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="shared_catalog"><?php _e('ID Shared Category', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="shared_catalog" name="shared_catalog"
                value="<?php echo esc_attr($shared_catalog) ? esc_attr($shared_catalog) : ''; ?>">
        </td>
    </tr>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="location_api_key_public"><?php _e('Conekta API PUBLIC', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="location_api_key_public" name="location_api_key_public"
                value="<?php echo esc_attr($location_api_key_public) ? esc_attr($location_api_key_public) : ''; ?>">
        </td>
    </tr>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="location_api_key"><?php _e('Conekta API PRIVADA', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="location_api_key" name="location_api_key"
                value="<?php echo esc_attr($api_key) ? esc_attr($api_key) : ''; ?>">
        </td>
    </tr>

    <tr class="form-field term-group-wrap">
        <th scope="row"><label
                for="sandbox_location_api_key_public"><?php _e('SANDBOX Conekta API PUBLIC', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="sandbox_location_api_key_public" name="sandbox_location_api_key_public"
                value="<?php echo esc_attr($sandbox_location_api_key_public) ? esc_attr($sandbox_location_api_key_public) : ''; ?>">
        </td>
    </tr>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label
                for="sandbox_location_api_key"><?php _e('SANDBOX  Conekta API PRIVADA', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="sandbox_location_api_key" name="sandbox_location_api_key"
                value="<?php echo esc_attr($sandbox_api_key) ? esc_attr($sandbox_api_key) : ''; ?>">
        </td>
    </tr>

    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_shared_catalog');



/**
 * Guardar el valor del campo personalizado api_key_public.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_location_api_key_public($term_id)
{
    if (isset($_POST['location_api_key_public'])) {
        update_term_meta($term_id, 'location_api_key_public', sanitize_text_field($_POST['location_api_key_public']));
    }
    if (isset($_POST['sandbox_location_api_key_public'])) {
        update_term_meta($term_id, 'sandbox_location_api_key_public', sanitize_text_field($_POST['sandbox_location_api_key_public']));
    }
    if (isset($_POST['location_api_key'])) {
        update_term_meta($term_id, 'location_api_key', sanitize_text_field($_POST['location_api_key']));
    }
    if (isset($_POST['sandbox_location_api_key'])) {
        update_term_meta($term_id, 'sandbox_location_api_key', sanitize_text_field($_POST['sandbox_location_api_key']));
    }
}
add_action('edited_locations', 'guardar_campo_location_api_key_public');
add_action('create_locations', 'guardar_campo_location_api_key_public');



/**
 * Guardar el valor del campo personalizado IDSharedCategory.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_shared_catalog($term_id)
{
    if (isset($_POST['shared_catalog'])) {
        update_term_meta($term_id, 'shared_catalog', sanitize_text_field($_POST['shared_catalog']));
    }

}
add_action('edited_locations', 'guardar_campo_shared_catalog');
add_action('create_locations', 'guardar_campo_shared_catalog');

/**
 * Agregar campo ID SOURCES personalizado a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_id_almacen($tag)
{
    $id_almacen = get_term_meta($tag->term_id, 'id_almacen', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="id_almacen"><?php _e('ID SOURCES', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="id_almacen" name="id_almacen"
                value="<?php echo esc_attr($id_almacen) ? esc_attr($id_almacen) : ''; ?>">
        </td>
    </tr>
    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_id_almacen');

/**
 * Guardar el valor del campo personalizado ID SOURCES.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_id_almacen($term_id)
{
    if (isset($_POST['id_almacen'])) {
        update_term_meta($term_id, 'id_almacen', sanitize_text_field($_POST['id_almacen']));
    }
}
add_action('edited_locations', 'guardar_campo_id_almacen');
add_action('create_locations', 'guardar_campo_id_almacen');

/**
 * Agregar campo CUSTOMER GROUP personalizado a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_customer_group($tag)
{
    $customer_group = get_term_meta($tag->term_id, 'customer_group', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="customer_group"><?php _e('ID Customers', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="customer_group" name="customer_group"
                value="<?php echo esc_attr($customer_group) ? esc_attr($customer_group) : ''; ?>">
        </td>
    </tr>
    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_customer_group');

/**
 * Guardar el valor del campo personalizado CUSTOMER GROUP.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_customer_group($term_id)
{
    if (isset($_POST['customer_group'])) {
        update_term_meta($term_id, 'customer_group', sanitize_text_field($_POST['customer_group']));
    }
}
add_action('edited_locations', 'guardar_campo_customer_group');
add_action('create_locations', 'guardar_campo_customer_group');

/*******************************************************
 * WP API - Obtener todos los sources
 ********************************************************/

/**
 * Registrar ruta de la API REST para obtener todos los locations.
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/get-locations', array(
        'methods' => 'GET',
        'callback' => 'get_locations_callback',
        'permission_callback' => '__return_true',
    ));
});



/**
 * Agregar campo URL MAPS a la taxonomía "locations".
 * 
 * @param WP_Term $tag La taxonomía a la que se añadirá el campo personalizado.
 */
function agregar_campo_url_maps($tag)
{
    $url_maps = get_term_meta($tag->term_id, 'url_maps', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="url_maps"><?php _e('URL maps', 'text_domain'); ?></label></th>
        <td>
            <input type="text" id="url_maps" name="url_maps"
                value="<?php echo esc_attr($url_maps) ? esc_attr($url_maps) : ''; ?>">
        </td>
    </tr>
    <?php
}
add_action('locations_edit_form_fields', 'agregar_campo_url_maps');

/**
 * Guardar el valor del campo personalizado URL MAPS.
 * 
 * @param int $term_id El ID del término.
 */
function guardar_campo_url_maps($term_id)
{
    if (isset($_POST['url_maps'])) {
        update_term_meta($term_id, 'url_maps', sanitize_text_field($_POST['url_maps']));
    }
}
add_action('edited_locations', 'guardar_campo_url_maps');
add_action('create_locations', 'guardar_campo_url_maps');


/**
 * Callback para obtener todos los locations y sus metadatos.
 * 
 * @return WP_REST_Response La respuesta con la lista de locations y sus datos.
 */
function get_locations_callback()
{
    log_woocommerce('Llamada a get_locations_callback');

    $terms = get_terms(array(
        'taxonomy' => 'locations',
        'hide_empty' => false,
    ));

    if (is_wp_error($terms)) {
        return new WP_Error('cant_retrieve', 'Unable to retrieve locations', array('status' => 500));
    }

    $locations = array();

    foreach ($terms as $term) {
        $id_almacen = get_term_meta($term->term_id, 'id_almacen', true);
        $locations[] = array(
            'id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
            'description' => $term->description,
            'count' => $term->count,
            'id_almacen' => $id_almacen,
        );
    }

    return new WP_REST_Response($locations, 200);
}

/*******************************************************
 * WP API - Actualizar stock por source y SKU
 ********************************************************/

/**
 * Registrar ruta de la API REST para actualizar stock por source y SKU.
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/update-stock', array(
        'methods' => 'POST',
        'callback' => 'schedule_update_stock',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
});

/**
 * Callback para actualizar el stock basado en source y SKU.
 * 
 * @param WP_REST_Request $request La solicitud con los datos de stock.
 * @return WP_REST_Response La respuesta de éxito o error.
 */


function update_stock($request)
{
    log_woocommerce('Llamada a update_stock con datos: ' . json_encode($request->get_json_params()));

    $data = $request->get_json_params();

    if (!isset($data['sourceItems']) || !is_array($data['sourceItems'])) {
        return new WP_Error('invalid_data', 'Invalid data', array('status' => 400));
    }

    foreach ($data['sourceItems'] as $item) {
        $sku = $item['sku'];
        $source_code = $item['source_code'];
        $quantity = $item['quantity'];
        $status = $item['status'];

        // Busca el producto por SKU
        $product_id = wc_get_product_id_by_sku($sku);
        if (!$product_id) {
            continue;
        }

        // Busca las taxonomy locations y compara con el source_code
        $terms = get_terms(array(
            'taxonomy' => 'locations',
            'hide_empty' => false,
        ));

        foreach ($terms as $term) {
            $id_almacen = get_term_meta($term->term_id, 'id_almacen', true);

            if ($id_almacen == $source_code) {
                update_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, $quantity);

                if ($status == 1) {
                    wp_set_post_terms($product_id, array($term->term_id), 'locations', false);
                }
                break;
            }
        }

        $total_stock = 0;
        foreach ($terms as $term) {
            $location_stock = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
            if ($location_stock !== '') {
                $total_stock += intval($location_stock);
            }
        }

        $product = wc_get_product($product_id);
        if (!$product->managing_stock()) {
            $product->set_manage_stock(true);
        }
        $product->set_stock_quantity($total_stock);

        if ($total_stock > 0) {
            $product->set_stock_status('instock');
        } else {
            $product->set_stock_status('outofstock');
        }

        $product->save();
        wc_delete_product_transients($product_id);
        clean_post_cache($product_id);
    }

    return new WP_REST_Response('Stock updated successfully', 200);
}



//funcion para debuggear despues de que el woo está activo

add_action('woocommerce_init', 'mi_funcion_personalizada');
function mi_funcion_personalizada()
{
    // update_stock_batch('update_stock_66f9a97518699', 0, 4000); 
}

function limpiarSku($sku)
{
    // Eliminar espacios en blanco al principio y al final
    $sku = trim($sku);

    // Eliminar caracteres no imprimibles (como saltos de línea, tabulaciones, etc.)
    $sku = preg_replace('/[\x00-\x1F\x7F]/', '', $sku);

    // Si hay caracteres de espacio no estándar o invisibles, los eliminamos también
    $sku = preg_replace('/\s+/', '', $sku);

    return $sku;
}

function update_stock_batch($batch_id, $offset, $batch_size)
{
    global $wpdb;
    log_woocommerce('Inicio de update_stock_batch. batch_id: ' . json_encode($batch_id) . ', offset: ' . $offset . ', batch_size: ' . $batch_size);

    // Recupera los datos desde la opción
    $data = get_option($batch_id);
    log_woocommerce("batch id $batch_id");
    //log_woocommerce('Datos recuperados desde la opción: ' . json_encode($data));

    if (!isset($data['sourceItems']) || !is_array($data['sourceItems'])) {
        log_woocommerce('Error: Datos inválidos en batch_id: ' . $batch_id);
        return new WP_Error('invalid_data', 'Invalid data', array('status' => 400));
    }

    // Procesar el lote con paginación
    //$batch = array_slice($data['sourceItems'], $offset, $batch_size);

    if (!isset($data['sourceItems']) || !is_array($data['sourceItems'])) {
        //error_log("sourceItems no está definido o no es un array");
        log_woocommerce('Error: sourceItems no está definido o no es un array: ' . $batch_id);
        $batch = [];
    } else {
        $totalItems = count($data['sourceItems']);
        if ($offset >= $totalItems) {
            log_woocommerce("El offset ($offset) es mayor o igual al total de elementos ($totalItems)");
            $batch = [];
        } else {
            $batch = array_slice($data['sourceItems'], $offset, max(1, $batch_size));
        }
    }

    // Ahora puedes usar $batch sin miedo a errores 500

    //log_woocommerce('Batch a procesar: ' . json_encode($batch));

    foreach ($batch as $item) {
        log_woocommerce('Procesando item: ' . json_encode($item));

        $sku = sanitize_text_field($item['sku']);

        $source_code = $item['source_code'];
        $quantity = $item['quantity'];
        $status = $item['status'];

        // Eliminar espacios en blanco al principio y al final
        $sku = trim($sku);

        // Eliminar caracteres no imprimibles
        $sku = preg_replace('/[\x00-\x1F\x7F]/', '', $sku);

        // Eliminar espacios no estándar o invisibles
        $sku = preg_replace('/\s+/', '', $sku);

        // Realizar la consulta a la base de datos
        $query = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_value = %s",
            '%_sku%', // Filtra solo claves relacionadas con SKU
            $sku      // Filtra por el valor del SKU exacto
        );

        $product_id = $wpdb->get_var($query);

        log_woocommerce('SKU: ' . $sku . ', Product ID encontrado: ' . $product_id);

        if (!$product_id) {
            log_woocommerce('Producto no encontrado para el SKU .: ' . $sku . '. Omisión del item.');
            continue;
        }

        // Busca las taxonomy locations y compara con el source_code
        $terms = get_terms(array(
            'taxonomy' => 'locations',
            'hide_empty' => false,
        ));
        // log_woocommerce('Términos de taxonomy locations: ' . json_encode($terms));

        foreach ($terms as $term) {
            $id_almacen = get_term_meta($term->term_id, 'id_almacen', true);
            //  log_woocommerce('Comparando term ID: ' . $term->term_id . ', id_almacen: ' . $id_almacen . ' con source_code: ' . $source_code);

            if ($id_almacen == $source_code) {
                update_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, $quantity);
                log_woocommerce('Actualizado stock en term ID: ' . $term->term_id . ' con cantidad: ' . $quantity);

                if ($status == 1) {
                    wp_set_post_terms($product_id, array($term->term_id), 'locations', false);
                    log_woocommerce('Término asignado al producto: ' . $term->term_id);
                }
                break;
            }
        }

        $total_stock = 0;
        foreach ($terms as $term) {
            $location_stock = get_post_meta($product_id, 'wcmlim_stock_at_' . $term->term_id, true);
            // log_woocommerce('Stock encontrado en term ID ' . $term->term_id . ': ' . $location_stock);

            if ($location_stock !== '') {
                $total_stock += intval($location_stock);
            }
        }
        log_woocommerce('Stock total calculado para product_id ' . $product_id . ': ' . $total_stock);

        $product = wc_get_product($product_id);

        // Verifica que el producto exista y esté publicado
        if (!$product || $product->get_status() !== 'publish') {
            log_woocommerce('Error: Producto no válido o no publicado para product_id: ' . $product_id);
            continue;
        }

        if (!$product->managing_stock()) {
            $product->set_manage_stock(true);
            log_woocommerce('Habilitado manage_stock para product_id: ' . $product_id);
        }

        $product->set_stock_quantity($total_stock);

        if ($total_stock > 0) {
            $product->set_stock_status('instock');
        } else {
            $product->set_stock_status('outofstock');
        }

        $product->save();
        log_woocommerce('Producto guardado exitosamente. product_id: ' . $product_id);

        clean_post_cache($product_id);
        log_woocommerce('Cache limpia para product_id: ' . $product_id);
    }

    $new_offset = $offset + $batch_size;
    log_woocommerce('Siguiente offset calculado: ' . $new_offset);

    if ($new_offset < count($data['sourceItems'])) {
        // Programa el próximo lote usando el mismo batch_id
        wp_schedule_single_event(time() + 60, 'update_stock_cron', array($batch_id, $new_offset, $batch_size));
        log_woocommerce('Próximo lote programado. batch_id: ' . $batch_id . ', new_offset: ' . $new_offset . ', batch_size: ' . $batch_size);
    } else {
        // Limpia la opción después de completar el proceso
        delete_option($batch_id);
        log_woocommerce('Proceso completado para batch_id: ' . $batch_id . '. Opción eliminada.');
    }

    return new WP_REST_Response('Batch processed successfully', 200);
}

function schedule_update_stock($request)
{
    // Extraer solo los datos necesarios
    $data = $request->get_json_params();

    if (!isset($data['sourceItems']) || !is_array($data['sourceItems'])) {
        return new WP_Error('invalid_data', 'Invalid data', array('status' => 400));
    }

    // Genera un ID único para el lote y guarda los datos en una opción
    $batch_id = 'update_stock_' . uniqid();
    error_log("se agendo un batch id para stock $batch_id");
    if (!update_option($batch_id, $data)) {
        return new WP_Error('update_option_failed', 'No se pudo guardar la opción en la base de datos', array('status' => 500));
    }

    // Programa el primer evento de cron
    $offset = 500;

    // Verificar si WordPress cron está habilitado
    if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
        return new WP_Error('cron_disabled', 'El cron de WordPress está deshabilitado en wp-config.php', array('status' => 500));
    }

    // Verificar si ya hay un evento programado para evitar duplicados
    if (!wp_next_scheduled('update_stock_cron')) {
        // Programar el evento si no está ya programado
        $scheduled = wp_schedule_single_event(time() + 60, 'update_stock_cron', array($batch_id, 0, $offset));

        // Asegurarse de que el evento fue programado
        if (!$scheduled) {
            error_log('Error: No se pudo programar el evento update_stock_cron para ' . $batch_id);
            return new WP_Error('schedule_failed', 'No se pudo programar el evento', array('status' => 500));
        }
    } else {
        return new WP_REST_Response('El evento ya estaba programado', 200);
    }

    return new WP_REST_Response('Batch agendado para actualizar stock ' . $batch_id, 200);
}


add_action('update_stock_cron', 'update_stock_batch', 10, 3);


/**
 * Inserta valores de precios personalizados en los metadatos de productos.
 * 
 * @param int $post_id El ID del producto.
 * @param string $location El identificador de la ubicación.
 * @param float $regular_price El precio regular.
 * @param float $sale_price El precio de venta.
 * @param array $price_tiers Niveles de precios personalizados.
 * @return bool Retorna true si los valores se insertaron correctamente.
 */
function insertar_valores_wp_postmeta($post_id, $location, $regular_price, $sale_price, $price_tiers)
{
    global $wpdb;

    if (!is_numeric($post_id) || $post_id <= 0) {
        return false;
    }

    $regular_price_meta_key = 'eib2bpro_regular_price_group_' . $location;
    $sale_price_meta_key = 'eib2bpro_sale_price_group_' . $location;
    $price_tiers_meta_key = 'eib2bpro_price_tiers_group_' . $location;

    $existing_regular_price = get_post_meta($post_id, $regular_price_meta_key, true);
    if ($existing_regular_price !== $regular_price) {
        update_post_meta($post_id, $regular_price_meta_key, $regular_price);
    }

    $existing_sale_price = get_post_meta($post_id, $sale_price_meta_key, true);
    if ($existing_sale_price !== $sale_price) {
        update_post_meta($post_id, $sale_price_meta_key, $sale_price);
    }

    $existing_price_tiers = get_post_meta($post_id, $price_tiers_meta_key, true);
    $encoded_price_tiers = json_encode($price_tiers);
    if ($existing_price_tiers !== $encoded_price_tiers) {
        update_post_meta($post_id, $price_tiers_meta_key, $encoded_price_tiers);
    }

    return true;
}

/**
 * Endpoint para actualización de precios y niveles de precios.
 */
// add_action('rest_api_init', function () {
//     register_rest_route('custom/v1', '/update-prices-tiers', array(
//         'methods' => 'PUT',
//       'callback' => 'schedule_update_prices_tiers',
//        //descomentar si se quiere usar en lguar de batch 
//        //'callback' => 'custom_update_prices_v2', 
//         'permission_callback' => function () {
//             return current_user_can('manage_options');
//         },
//     ));
// });


/**
 * Callback para actualizar precios y niveles de precios.
 * 
 * @param WP_REST_Request $request La solicitud con los datos de precios.
 * @return WP_REST_Response La respuesta de éxito o error.
 */
//function custom_update_prices(WP_REST_Request $request) {
function update_prices_tiers_batch($batch_id, $offset, $batch_size)
{

    // Log para depurar
    log_woocommerce_tiers('Llamada a update_prices_tiers_batch con datos: ' . json_encode($batch_id));

    // Recupera los datos desde la opción
    $data = get_option($batch_id);

    log_woocommerce_tiers('Datos del lote tiers: ' . json_encode($data));

    // Validación para asegurarse de que $data['prices'] existe y es un arreglo
    if (isset($data['prices']) && is_array($data['prices'])) {
        // Se parte la data
        $prices = array_slice($data['prices'], $offset, $batch_size);
        log_woocommerce_tiers('Batch a actualizar de precios: ' . json_encode($prices));
    } else {
        // Manejo de error: si $data['prices'] no es válido
        log_woocommerce_tiers('Error: $data["prices"] no es válido o no está definido. Datos recibidos: ' . json_encode($data));
        $prices = []; // Establece $prices como un arreglo vacío para evitar otros errores
    }

    // Variables iniciales para manejar los niveles de precios
    $price_tiers_array = [];
    $current_sku = null;
    $current_price_tiers = [];
    $current_customer_id = null; // Para rastrear el cambio de customer_id

    // Continuar con el resto de la lógica...


    // Itera sobre cada conjunto de datos de precios
    foreach ($prices as $price_data) {
        $sku = sanitize_text_field($price_data['sku']);
        $quantity = sanitize_text_field($price_data['quantity']);
        $price_value = $price_data['price'];

        $id_customer = isset($price_data['customerGroup']) ? $price_data['customerGroup'] :
            (isset($price_data['customer_group']) ? $price_data['customer_group'] : null);

        // Si el SKU o el customer_id cambia, guarda el array anterior y reinicia para el nuevo SKU
        if (($current_sku !== null && $sku !== $current_sku) || ($current_customer_id !== null && $id_customer !== $current_customer_id)) {
            $price_tiers_array[$current_sku] = $current_price_tiers;
            process_price_tiers($current_sku, $price_tiers_array, $current_customer_id);
            $current_price_tiers = [];
            $price_tiers_array = [];
        }

        // Actualiza el SKU actual y agrega el precio al array de niveles de precio
        $current_sku = $sku;
        $current_customer_id = $id_customer;
        log_woocommerce_tiers('el precio que va es: ' . $price_value);
        $current_price_tiers[$quantity] = $price_value;
    }

    // Procesa el último conjunto de precios
    if ($current_sku !== null) {
        $price_tiers_array[$current_sku] = $current_price_tiers;
        process_price_tiers($current_sku, $price_tiers_array, $current_customer_id);
        $current_price_tiers = [];
        $price_tiers_array = [];
    }

    /* se desactiva por que ya no se ocupo
    revisar si el offset sigue para agendar lo que sigue*/
    $new_offset = $offset + $batch_size;
    log_woocommerce_tiers('Siguiente offset: ' . $new_offset);



    error_log("data received: " . json_encode($data));

    // Asegúrate de que $data['prices'] existe y es un array antes de usarlo
    if (isset($data['prices']) && is_array($data['prices'])) {
        error_log("data['prices'] count: " . count($data['prices']));

        if ($new_offset < count($data['prices'])) {
            // Programa el próximo lote usando el mismo batch_id
            wp_schedule_single_event(
                time() + 60,
                'update_prices_tiers_cron',
                array($batch_id, $new_offset, $batch_size)
            );
        } else {
            // Limpia la opción después de completar el proceso
            delete_option($batch_id);
            log_woocommerce_tiers('Proceso de actualización de precios tiers completado.');
        }
    } else {
        // Registra un error si data['prices'] no es válida
        error_log("Error: data['prices'] is not set or not an array. Data: " . json_encode($data));
    }


}



/**
 * funciones para actualizar prices_tiers sin batch está se cambia desde la llamada del api v1/update-prices-tiers no se usa originalmente se usa la batch
 */

function custom_update_prices_tier(WP_REST_Request $request)
{
    $data = $request->get_params();

    $price_tiers_array = [];
    $current_sku = null;
    $current_price_tiers = [];
    $current_customer_id = null; // Para rastrear el cambio de customer_id

    foreach ($data['prices'] as $price_data) {
        $sku = $price_data['sku'];
        $quantity = $price_data['quantity'];
        $price_value = $price_data['tier_price'];
        $id_customer = $price_data['customer_group'];

        // Si el SKU o el customer_id cambia, guarda el array anterior y reinicia para el nuevo SKU
        if (($current_sku !== null && $sku !== $current_sku) || ($current_customer_id !== null && $id_customer !== $current_customer_id)) {
            $price_tiers_array[$current_sku] = $current_price_tiers;
            $current_price_tiers = [];

            // Inserción de datos trabajando sobre el SKU y el customer_id actual
            $terms = get_terms(array(
                'taxonomy' => 'locations',
                'hide_empty' => false,
            ));
            $p = [];
            foreach ($terms as $term) {
                $id_customer_term = get_term_meta($term->term_id, 'customer_group', true);
                if ($id_customer_term == $current_customer_id) {
                    $product_id = wc_get_product_id_by_sku($current_sku);
                    if (!$product_id) {
                        $p[] = $current_sku . " no encontrado";
                        continue;
                    } else {
                        $p[] = $current_sku . " actualizado";
                    }
                    $regular_price = $price_data['regular_price'] ?? '';
                    $sale_price = $price_data['sale_price'] ?? '';
                    $flattened_array = array_values($price_tiers_array)[0];
                    $price_tiers_array = [];
                    insertar_valores_wp_postmeta($product_id, $id_customer_term, $regular_price, $sale_price, $flattened_array);
                    echo "inserte en $product_id - $id_customer_term  -" . json_encode($flattened_array);
                }
            }
        }

        // Actualiza el SKU y el customer_id actual y agrega el precio al array de niveles de precio
        $current_sku = $sku;
        $current_customer_id = $id_customer;
        $current_price_tiers[$quantity] = $price_value;
    }

    // Guarda el último conjunto de precios si existe y lo guardo
    if ($current_sku !== null) {
        $price_tiers_array[$current_sku] = $current_price_tiers;
        $terms = get_terms(array(
            'taxonomy' => 'locations',
            'hide_empty' => false,
        ));
        $p = [];
        foreach ($terms as $term) {
            $id_customer_term = get_term_meta($term->term_id, 'customer_group', true);
            if ($id_customer_term == $current_customer_id) {
                $product_id = wc_get_product_id_by_sku($current_sku);
                if (!$product_id) {
                    $p[] = $current_sku . " no encontrado";
                    continue;
                } else {
                    $p[] = $current_sku . " actualizado";
                }
                $regular_price = $price_data['regular_price'] ?? '';
                $sale_price = $price_data['sale_price'] ?? '';
                $flattened_array = array_values($price_tiers_array)[0];
                insertar_valores_wp_postmeta($product_id, $id_customer_term, $regular_price, $sale_price, $flattened_array);
                echo "inserte en $product_id - $id_customer_term  -" . json_encode($flattened_array);
            }
        }
    }
}


// Función para procesar y actualizar niveles de precios
function process_price_tiers($sku, $price_tiers_array, $current_customer_id)
{

    log_woocommerce_tiers("Voy a trabajar con el customer group: " . $current_customer_id . " con precios " . json_encode($price_tiers_array));


    // Obtener todos los términos de la taxonomía 'locations'
    $terms = get_terms(array(
        'taxonomy' => 'locations',
        'hide_empty' => false,
    ));

    $p = [];
    //esto lo debe ejecutar por cada grupo de customers, en teoria debe recibir todo el array de de sku
    foreach ($terms as $term) {
        $id_customer_term = get_term_meta($term->term_id, 'customer_group', true);
        if ($id_customer_term == $current_customer_id) {
            log_woocommerce_tiers("Buscando SKU: $sku");
            $product_id = wc_get_product_id_by_sku($sku);
            log_woocommerce_tiers("Producto ID: $product_id");

            if (!$product_id) {
                $p[] = "$sku no encontrado";
                continue;
            } else {
                $p[] = "$sku actualizado";
            }

            $regular_price = $price_data['regular_price'] ?? '';
            $sale_price = $price_data['sale_price'] ?? '';

            $flattened_array = array_values($price_tiers_array)[0];

            log_woocommerce_tiers("Insertado en $product_id - $id_customer_term - " . json_encode($flattened_array));

            log_woocommerce_tiers("Resumen de batch por producto: " . json_encode($p));

            // Inserta los valores en la base de datos
            insertar_valores_wp_postmeta($product_id, $id_customer_term, $regular_price, $sale_price, $flattened_array);


        }
    }

    // Limpia la caché del producto
    clean_post_cache($product_id);
}



/***
 * Update prices schedule
 */

function schedule_update_prices_tiers($request)
{
    // Extraer solo los datos necesarios
    $data = $request->get_json_params();

    error_log("prices_tiers produccion" . json_encode($data));

    if (!isset($data['prices']) || !is_array($data['prices'])) {
        return new WP_Error('invalid_data', 'Invalid data', array('status' => 400));
    }

    // Genera un ID único para el lote y guarda los datos en una opción
    $batch_id = 'update_prices_tiers' . uniqid();
    update_option($batch_id, $data);

    // Programa el primer evento de cron
    //parametros batch_id, offset, batchsize
    $offset = 100;

    // Verificar si ya existe un evento programado para evitar duplicados
    if (!wp_next_scheduled('update_prices_tiers_cron', array($batch_id, 0, $offset))) {
        // Programar el evento si no está ya programado
        $scheduled = wp_schedule_single_event(time() + 1, 'update_prices_tiers_cron', array($batch_id, 0, $offset));

        // Asegurarse de que el evento fue programado
        if (!$scheduled) {
            return new WP_Error('schedule_failed', 'No se pudo programar el evento', array('status' => 500));
        }
    } else {
        return new WP_REST_Response('El evento ya estaba programado', 200);
    }

    return new WP_REST_Response('Batch agendado para actualizar precios ' . $batch_id . ' - ' . json_encode($data), 200);

}

add_action('update_prices_tiers_cron', 'update_prices_tiers_batch', 10, 3);


/**
 * Endpoint para eliminación de niveles de precios específicos.
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/delete-prices-tiers', array(
        'methods' => 'POST',
        'callback' => 'custom_delete_price_tiers',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        },
    ));
});

/**
 * Callback para eliminar niveles de precios específicos.
 * 
 * @param WP_REST_Request $request La solicitud con los datos de los niveles de precios a eliminar.
 * @return WP_REST_Response La respuesta de éxito o error.
 */
function custom_delete_price_tiers(WP_REST_Request $request)
{
    log_woocommerce('Llamada a custom_delete_price_tiers con datos: ' . json_encode($request->get_json_params()));

    $prices = $request->get_param('prices');

    if (empty($prices) || !is_array($prices)) {
        return new WP_Error('invalid_data', 'Datos inválidos proporcionados', array('status' => 400));
    }

    $total_coincidencia = 0;
    foreach ($prices as $price_data) {

        if (!isset($price_data['sku'], $price_data['customer_group'], $price_data['price'], $price_data['quantity'])) {
            continue;
        }

        $sku = $price_data['sku'];
        $customer_group = $price_data['customer_group'];
        $price = $price_data['price'];
        $quantity = $price_data['quantity'];

        // Buscar el ID del producto por SKU
        $product_id = wc_get_product_id_by_sku($sku);
        if (!$product_id) {
            return rest_ensure_response(array('message' => "Producto con SKU $sku no encontrado", 'status' => 404));
        }

        // Clave de meta a buscar
        $meta_key = "eib2bpro_price_tiers_group_$customer_group";
        // Obtener el valor actual del meta
        $current_price_tiers = get_post_meta($product_id, $meta_key, true);
        $decoded_price_tiers = json_decode($current_price_tiers, true);

        // Verificar si la decodificación fue exitosa
        if (json_last_error() === JSON_ERROR_NONE) {
            // Contar el número de elementos en el array
            $total_current_price_tiers = is_array($decoded_price_tiers) ? count($decoded_price_tiers) : 0;
        } else {
            // Manejo de error de decodificación
        }

        // Decodificar JSON a array
        $current_price_tiers = json_decode($current_price_tiers, true);

        // Buscar clave y valor
        foreach ($current_price_tiers as $key => $value) {
            if ($key === $quantity && $value === $price) {
                unset($current_price_tiers[$key]);
                update_post_meta($product_id, $meta_key, json_encode($current_price_tiers));
                $total_coincidencia++;
            }
        }

        // Por si no tiene niveles de precios
        if (empty($current_price_tiers) || !is_array($current_price_tiers)) {
            return rest_ensure_response(array('message' => "No se encontraron niveles de precios para el grupo de clientes $customer_group en el producto $sku", 'status' => 404));
        }
    }

    // Actualizar el meta con los nuevos datos
    if (!empty($current_price_tiers)) {
        $message = "Niveles de precios actualizados para el producto $sku";
    } else {
        $message = "Todos los niveles de precios eliminados para el producto $sku";
    }

    // Si coincide todo lo que hay con lo que se quiere borrar, borramos todo
    if ($total_current_price_tiers == $total_coincidencia) {
        delete_post_meta($product_id, $meta_key);
        delete_post_meta($product_id, "eib2bpro_regular_price_group_$customer_group");
        delete_post_meta($product_id, "eib2bpro_sale_price_group_$customer_group");
        return rest_ensure_response(array('message' => "Se eliminaron todas los niveles de precios $customer_group en el producto $sku", 'status' => 200));
    }

    return rest_ensure_response(array('message' => $message));
}


// Registrar el endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/base-prices/', array(
        'methods' => 'PUT',
        'callback' => 'handle_update_product_prices',
        'permission_callback' => function () {
            return current_user_can('manage_woocommerce'); // Permisos para acceder al endpoint
        },
    ));
});

/**
 * Callback para manejar la actualización de precios de productos.
 * 
 * @param WP_REST_Request $request La solicitud con los datos de precios.
 * @return WP_REST_Response La respuesta de éxito o error.
 */
function handle_update_product_prices(WP_REST_Request $request)
{
    $data = $request->get_json_params(); // Obtener los parámetros JSON de la solicitud

    if (!isset($data['prices']) || !is_array($data['prices'])) {
        return new WP_REST_Response(['error' => 'Invalid data'], 400);
    }

    // Llamar a la función para actualizar los precios
    $response = update_product_prices($data);

    return $response;
}

/**
 * Actualizar precios de productos.
 * 
 * @param array $data Los datos para actualizar precios.
 * @return WP_REST_Response La respuesta de éxito o error.
 */

/**
 * Actualizar precios de productos.
 * 
 * @param array $data Los datos para actualizar precios.
 * @return WP_REST_Response La respuesta de éxito o error.
 */
/**
 * Actualizar precios de productos.
 * 
 * @param array $data Los datos para actualizar precios.
 * @return WP_REST_Response La respuesta de éxito o error.
 */
function update_product_prices($data)
{

    $updated_products = [];

    foreach ($data['prices'] as $product) {
        // Validar que al menos el SKU y el precio base están presentes
        if (!isset($product['sku']) || !isset($product['price'])) {
            continue; // Saltar si faltan datos requeridos
        }

        $sku = sanitize_text_field($product['sku']);
        $price = floatval($product['price']);

        // Si `discount_price` no está definido, se asigna como null
        $discount_price = array_key_exists('discount_price', $product) ? floatval($product['discount_price']) : null;

        // Buscar el producto por SKU
        $product_id = wc_get_product_id_by_sku($sku);

        if (!$product_id) {
            continue; // Si no se encuentra el producto, pasar al siguiente
        }

        // Actualizar precios
        update_post_meta($product_id, '_price', $price); // Precio base
        update_post_meta($product_id, '_regular_price', $price); // Precio base

        // Si el precio de descuento está definido y es mayor a 0, actualizarlo
        if ($discount_price !== null && $discount_price > 0) {
            update_post_meta($product_id, '_sale_price', $discount_price);
        } else {
            // Si no se proporciona o es 0, eliminar cualquier precio de descuento
            delete_post_meta($product_id, '_sale_price');
        }

        // Añadir el producto actualizado a la lista de respuesta
        $updated_product = [
            'sku' => $sku,
            'price' => $price,
            'updated' => true,
        ];

        // Si se envió `discount_price`, añadirlo a la respuesta
        if ($discount_price !== null) {
            $updated_product['discount_price'] = $discount_price;
        }

        $updated_products[] = $updated_product;
    }



    if (empty($updated_products)) {
        return new WP_REST_Response(['error' => 'No se pudieron actualizar los productos'], 404);
    }

    return new WP_REST_Response(['updated_products' => $updated_products], 200);
}




/**
 * Callback para actualizar precios en lotes.
 * 
 * @param string $batch_id El ID del lote.
 * @param int $offset El desplazamiento actual en el lote.
 * @param int $batch_size El tamaño del lote.
 */
function update_product_prices_batch($batch_id, $offset, $batch_size)
{
    log_woocommerce('Llamada a update_product_prices_batch con datos: ' . json_encode($batch_id));

    // Recupera los datos desde la opción
    $data = get_option($batch_id);
    log_woocommerce('Datos del lote: ' . json_encode($data));

    $prices = array_slice($data['prices'], $offset, $batch_size);
    log_woocommerce('Batch a actualizar de precios: ' . json_encode($prices));

    foreach ($prices as $price_data) {
        if (!isset($price_data['sku']) || !isset($price_data['price']) || !isset($price_data['price_type'])) {
            continue; // Saltar si faltan datos esenciales
        }

        $sku = sanitize_text_field($price_data['sku']);
        $price = floatval($price_data['price']);
        $price_type = sanitize_text_field($price_data['price_type']);

        // Obtener el ID del producto por SKU
        $product_id = wc_get_product_id_by_sku($sku);

        if ($product_id) {
            $product = wc_get_product($product_id);

            // Verificar el tipo de precio y actualizarlo
            if ($price_type === 'price') {
                $product->set_regular_price($price);
            } elseif ($price_type === 'sale_price') {
                $product->set_sale_price($price);
            }

            // Guardar los cambios
            $product->save();
            log_woocommerce("Producto con SKU $sku actualizado.");
        } else {
            log_woocommerce("Producto con SKU $sku no encontrado.");
        }
    }

    $new_offset = $offset + $batch_size;
    log_woocommerce('Siguiente offset: ' . $new_offset);

    if ($new_offset < count($data['prices'])) {
        // Programa el próximo lote usando el mismo batch_id
        wp_schedule_single_event(time() + 60, 'update_product_prices_cron', array($batch_id, $new_offset, $batch_size));
    } else {
        // Limpia la opción después de completar el proceso
        delete_option($batch_id);
        log_woocommerce('Proceso de actualización de precios completado.');
    }
}

/**
 * Programar la actualización de precios en lotes.
 * 
 * @param WP_REST_Request $request La solicitud con los datos de precios.
 * @return WP_REST_Response La respuesta de éxito o error.
 */
function schedule_update_product_prices($request)
{
    // Extraer solo los datos necesarios
    $data = $request->get_json_params();

    if (!isset($data['prices']) || !is_array($data['prices'])) {
        return new WP_Error('invalid_data', 'Invalid data', array('status' => 400));
    }

    // Genera un ID único para el lote y guarda los datos en una opción
    $batch_id = 'update_product_prices' . uniqid();
    update_option($batch_id, $data);

    // Programa el primer evento de cron
    $batch_size = 50; // Define el tamaño del lote
    $ok = wp_schedule_single_event(time() + 2, 'update_product_prices_cron', array($batch_id, 0, $batch_size));
    error_log("webkep" . $ok);
    return new WP_REST_Response('Batch agendado para actualizar precios ' . $batch_id, 200);
}

add_action('update_product_prices_cron', 'update_product_prices_batch', 10, 3);

/**
 * Registrar la ruta de la API REST para programar la actualización de precios.
 */
add_action('rest_api_init', function () {
    register_rest_route('custom-woocommerce/v1', '/schedule-update-prices/', array(
        'methods' => 'POST',
        'callback' => 'schedule_update_product_prices',
        'permission_callback' => function () {
            return current_user_can('manage_woocommerce'); // Permisos para acceder al endpoint
        },
    ));
});



//borar por sku

// Agregar un endpoint personalizado para eliminar un producto por SKU
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/products/(?P<sku>[a-zA-Z0-9_-]+)', [
        'methods' => 'DELETE',
        'callback' => 'delete_product_by_sku',
        'permission_callback' => function () {
            return current_user_can('manage_woocommerce'); // Permiso de administrador
        }
    ]);
});

function delete_product_by_sku($request)
{
    $sku = sanitize_text_field($request['sku']);

    // Obtener el producto usando el SKU
    $product = wc_get_product_id_by_sku($sku);

    // Verificar si el producto existe
    if (!$product) {
        return new WP_REST_Response(['message' => 'Producto no encontrado'], 404);
    }

    // Eliminar el producto
    $deleted = wp_delete_post($product, true);

    if ($deleted) {
        return new WP_REST_Response(['message' => 'Producto eliminado exitosamente'], 200);
    } else {
        return new WP_REST_Response(['message' => 'Error al eliminar el producto'], 500);
    }
}




function get_product_info_by_sku($request)
{
    // Obtener el SKU del parámetro de la solicitud
    $sku = $request->get_param('sku');

    // Validar que el SKU no esté vacío
    if (empty($sku)) {
        return new WP_REST_Response(array(
            'error' => true,
            'message' => 'El SKU es requerido.'
        ), 400);
    }

    // Buscar el producto por SKU
    $product_id = wc_get_product_id_by_sku($sku);

    // Validar si se encontró el producto
    if (!$product_id) {
        return new WP_REST_Response(array(
            'error' => true,
            'message' => 'Producto no encontrado para el SKU proporcionado.'
        ), 404);
    }

    // Obtener el producto
    $product = wc_get_product($product_id);

    // Validar si el producto existe
    if (!$product) {
        return new WP_REST_Response(array(
            'error' => true,
            'message' => 'El producto no existe.'
        ), 404);
    }

    // Preparar la información del producto
    $product_data = array(
        'id' => $product->get_id(),
        'name' => $product->get_name(),
        'sku' => $product->get_sku(),
        'price' => $product->get_price(),
        'stock_status' => $product->get_stock_status(),
        'stock_quantity' => $product->get_stock_quantity(),
        'description' => $product->get_description(),
        'short_description' => $product->get_short_description(),
        'categories' => wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names')),
        'tags' => wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names')),
        'images' => $product->get_gallery_image_ids(),
        'attributes' => $product->get_attributes(),
    );

    // Devolver la respuesta
    return new WP_REST_Response(array(
        'error' => false,
        'product' => $product_data
    ), 200);
}

// Registrar la ruta en la API REST
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/product-info/', array(
        'methods' => 'GET',
        'callback' => 'get_product_info_by_sku',
        'args' => array(
            'sku' => array(
                'required' => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_string($param) && !empty($param);
                }
            )
        )
    ));
});

?>