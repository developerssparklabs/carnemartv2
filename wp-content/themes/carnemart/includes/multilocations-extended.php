<?php

/**
 * 
 *   ██████  ██▓███   ▄▄▄       ██▀███   ██ ▄█▀    ██▓  ██████     ██░ ██ ▓█████  ██▀███  ▓█████ 
 * ▒██    ▒ ▓██░  ██▒▒████▄    ▓██ ▒ ██▒ ██▄█▒    ▓██▒▒██    ▒    ▓██░ ██▒▓█   ▀ ▓██ ▒ ██▒▓█   ▀ 
 * ░ ▓██▄   ▓██░ ██▓▒▒██  ▀█▄  ▓██ ░▄█ ▒▓███▄░    ▒██▒░ ▓██▄      ▒██▀▀██░▒███   ▓██ ░▄█ ▒▒███   
 *   ▒   ██▒▒██▄█▓▒ ▒░██▄▄▄▄██ ▒██▀▀█▄  ▓██ █▄    ░██░  ▒   ██▒   ░▓█ ░██ ▒▓█  ▄ ▒██▀▀█▄  ▒▓█  ▄ 
 * ▒██████▒▒▒██▒ ░  ░ ▓█   ▓██▒░██▓ ▒██▒▒██▒ █▄   ░██░▒██████▒▒   ░▓█▒░██▓░▒████▒░██▓ ▒██▒░▒████▒
 * ▒ ▒▓▒ ▒ ░▒▓▒░ ░  ░ ▒▒   ▓▒█░░ ▒▓ ░▒▓░▒ ▒▒ ▓▒   ░▓  ▒ ▒▓▒ ▒ ░    ▒ ░░▒░▒░░ ▒░ ░░ ▒▓ ░▒▓░░░ ▒░ ░
 * ░ ░▒  ░ ░░▒ ░       ▒   ▒▒ ░  ░▒ ░ ▒░░ ░▒ ▒░    ▒ ░░ ░▒  ░ ░    ▒ ░▒░ ░ ░ ░  ░  ░▒ ░ ▒░ ░ ░  ░
 * ░  ░  ░  ░░         ░   ▒     ░░   ░ ░ ░░ ░     ▒ ░░  ░  ░      ░  ░░ ░   ░     ░░   ░    ░   
 *       ░                 ░  ░   ░     ░  ░       ░        ░      ░  ░  ░   ░  ░   ░        ░  ░                                                    ░                                                                                                          ░              ░      
 * Funciones adicionales para multilocations
 * by spark-jesus
 */


if (!defined('ABSPATH')) {
   exit;
}

class MultiLocationsExtended
{

   public function __construct()
   {
      add_shortcode('show_cp_definied', array($this, 'show_cp_definied_shortcode'));
      add_shortcode('show_location_data_selected', array($this, 'show_data_location'));
   }

   public function check_cookie($cookie_name)
   {
      return isset($_COOKIE[$cookie_name]);
   }

   public function get_cookie_value($cookie_name)
   {
      return sanitize_text_field($_COOKIE[$cookie_name]);
   }

   public function show_cp_definied_shortcode()
   {

      $msg = "";
      $cookie_name = 'wcmlim_nearby_location';
      $this->show_data_location();
      if ($this->check_cookie($cookie_name)) {
         $cookie_value = $this->get_cookie_value($cookie_name);
         //  $msg = '<span id="ctaOpenBox" class="copy-msg msgEntrega">Dirección: ' . esc_html($cookie_value) . ', tienes entrega disponible</span>';
         return $msg;
      } else {
         return '';
      }
   }

   // Data de /wp-json/wp/v2/locations/247
   public function show_data_location()
   {
      $msg = "";
      $cookie_name = "wcmlim_selected_location_termid";
      if ($this->check_cookie($cookie_name)) {
         $location_id = $this->get_cookie_value($cookie_name);
         // $msg = "obtendremos la data de $location_id";

         $dataLoc = $this->get_location_data($location_id);
         // $msg = "Tienda: " . $dataLoc->name;
         /*print_r([
            $dataLoc->name,
            $dataLoc->meta->wcmlim_email,
            $dataLoc->meta->wcmlim_phone,
            $dataLoc->meta->wcmlim_start_time,
            $dataLoc->meta->wcmlim_end_time,
            $dataLoc->meta->wcmlim_street_number,
            $dataLoc->meta->wcmlim_route,
            $dataLoc->meta->wcmlim_locality
         ]);*/
         return $msg;
      } else {
         return '';
      }
   }

   public function get_location_data($id)
   {
      $url = home_url('/wp-json/wp/v2/locations/' . $id);
      try {
         $response = wp_remote_request($url, [
            'method' => 'GET'
         ]);
         if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Error: $error_message";
            return [];
         } else {
            $data = wp_remote_retrieve_body($response);

            return json_decode($data);
         }
      } catch (\Throwable $th) {
         return [];
      }
   }
}

$multi_locations_extended = new MultiLocationsExtended();

/**
 * SHORTCODE
 * Tiendas
 * Mostrar tiendas agrupadas por estado
 */


function crear_seccion_tiendas_shortcode($atts)
{
   $wcMultiPlg = new Wcmlim_Public('wcmlim', WCMLIM_VERSION);
   $locations_list = $wcMultiPlg->wcmlim_get_all_locations();

   $estados = [];
   foreach ($locations_list as $key => $loc) {
   
      $estado = $loc["ciudad"];
      if (!isset($estados[$estado])) {
         $estados[$estado] = [];
      }

      $nombretienda = $loc["location_name"];
      $parts = explode(" - ", $nombretienda);
      $parts[0] = str_replace("CMT", "", $parts[0]);
      $updatedName = $parts[0];

      $estados[$estado][] = [
         "key" => $key,
         "slug" => $loc["location_slug"],
         "address" => base64_encode($loc["location_address"]),
         "termid" => $loc["location_termid"],
         "nombretienda" => $updatedName,
         "ciudad" => $loc["ciudad"],
      ];
   }

   ob_start();
?>
   <!-- Buscador -->

   <h2 style="display:none;">Encuentra tu tienda más cercana</h2>

	<div class="buscador-tienda__header">
		<div class="buscador-tienda__buscador">
			<input type="text" id="buscador" class="buscador-tienda__input-buscar" placeholder="Buscar por estado o tienda...">
		</div>
		<div class="buscador-tienda__cta">
			<a href="#" class="buscador-tienda__btn"><i class="geo-alt"></i><span>Compartir mi ubicación</span></a>
		</div>
	</div>


   <div class="buscador-container mb-4">
      
   </div>

 
   
   <div class="buscador-tienda__grid" id="contenedor-estados">
      <?php foreach ($estados as $estado => $locations) { ?>
         <div class="estado col-md-6 mb-4" data-estado="<?php echo htmlspecialchars($estado); ?>">
            <h3 class="estado-titulo"><?php echo htmlspecialchars($estado); ?></h3>
            <hr>
            <ul class="lista-tiendas">
               <?php foreach ($locations as $loc) { ?>
                  <li data-tienda="<?php echo htmlspecialchars($loc["nombretienda"]); ?>">
                     <button
                        class="btn tienda-button<?php
                                                if (
                                                   (preg_match('/^\d+$/', $user_selected_location) && $user_selected_location == $loc["key"]) ||
                                                   (isset($_COOKIE["wcmlim_selected_location"]) && $_COOKIE["wcmlim_selected_location"] == $loc["key"])
                                                ) {
                                                   echo ' seleccionado';
                                                }
                                                ?>"
                        data-lc-key=" <?php echo $loc["key"]; ?>"
                        data-lc-address="<?php echo htmlspecialchars($loc["address"]); ?>"
                        data-lc-term="<?php echo $loc["termid"]; ?>">
                        <?php echo htmlspecialchars($loc["nombretienda"]); ?>
                     </button>
                  </li>
               <?php } ?>
            </ul>
         </div>
      <?php } ?>
   </div>
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const buscador = document.getElementById('buscador');
         const estados = document.querySelectorAll('.estado');

         buscador.addEventListener('input', function() {
            const query = buscador.value.toLowerCase();
            estados.forEach(estado => {
               const estadoNombre = estado.getAttribute('data-estado').toLowerCase();
               const tiendas = estado.querySelectorAll('li[data-tienda]');
               let tieneCoincidencias = false;

               tiendas.forEach(tienda => {
                  const tiendaNombre = tienda.getAttribute('data-tienda').toLowerCase();
                  if (estadoNombre.includes(query) || tiendaNombre.includes(query)) {
                     tienda.style.display = '';
                     tieneCoincidencias = true;
                  } else {
                     tienda.style.display = 'none';
                  }
               });

               if (tieneCoincidencias) {
                  estado.classList.remove('oculto');
               } else {
                  estado.classList.add('oculto');
               }
            });
         });
      });
   </script>
<?php
   return ob_get_clean();
}
add_shortcode('tiendas_section', 'crear_seccion_tiendas_shortcode');






//hacking by serch keikos s8k
function get_nearest_stores_from_cache($lat, $lng, $limit = 5) {
   $upload_dir = wp_upload_dir();
   $file_path = $upload_dir['basedir'] . '/stores.json';

   // Si el archivo no existe, generar el caché
   if (!file_exists($file_path)) {
       generate_store_cache();
   }

   // Si aún no existe después de intentar generarlo, retornar vacío
   if (!file_exists($file_path)) {
       return [];
   }

   $stores = json_decode(file_get_contents($file_path), true);

   if (!$stores) {
       return [];
   }


   // Filtrar tiendas sin latitud o longitud
   $stores = array_filter($stores, function ($store) {
      return !empty($store['latitude']) && !empty($store['longitude']);
   });

   // Calcular distancias usando Haversine
   foreach ($stores as &$store) {
      $store['distance_km'] = haversine($lat, $lng, $store['latitude'], $store['longitude']);
   }

   // Ordenar por distancia y devolver los más cercanos
   usort($stores, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);

   return array_slice($stores, 0, $limit);
}



/**
* Genera el archivo JSON de tiendas en `wp-content/uploads/stores.json`
*/
function generate_store_cache() {
   global $wpdb;

   $upload_dir = wp_upload_dir();
   $file_path = $upload_dir['basedir'] . '/stores.json';

   $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
   $result = [];
   $i = 0; // Mantener el ID secuencial original, aunque no se agreguen algunos términos

   foreach ($terms as $term) {
       $term_meta = get_option("taxonomy_$term->term_id", []);
       $term_locator = get_term_meta($term->term_id, 'wcmlim_locator', true);
       $term_lat = get_term_meta($term->term_id, 'wcmlim_lat', true);
       $term_lng = get_term_meta($term->term_id, 'wcmlim_lng', true);
       $centro_activo = get_term_meta($term->term_id, 'centro_activo', true);

       // Asegurar que term_meta es un array
       if (!is_array($term_meta)) {
           $term_meta = [];
       }

       // Limpiar el array de term_meta
       $term_meta = array_map(function ($value) {
           return is_array($value) ? '' : $value;
       }, $term_meta);

       // Filtrar solo términos con centro_activo = 1
       if ($centro_activo == "1") {
           $result[] = [
               'address' => implode(" ", array_filter($term_meta)),
               'name' => $term->name,
               'slug' => $term->slug,
               'storeid' => $term_locator,
               'termid' => $term->term_id,
               'latitude' => $term_lat,
               'longitude' => $term_lng,
               'centro_activo' => $centro_activo,
               'loc_id' => $i // Se mantiene el ID secuencial original, aunque haya saltos
           ];
       }

       $i++; // Incrementar el contador siempre, aunque no se agregue al JSON
   }

   // Verificación del resultado antes de escribir en JSON

   if (!empty($result)) {
       $json_data = json_encode($result, JSON_PRETTY_PRINT);
       file_put_contents($file_path, $json_data);
   }
}


/**
* Función de Haversine para calcular distancias entre dos coordenadas
*/
function haversine($lat1, $lon1, $lat2, $lon2) {
   $earth_radius = 6371; // Radio de la Tierra en km

   $dLat = deg2rad($lat2 - $lat1);
   $dLon = deg2rad($lon2 - $lon1);

   $a = sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon/2) * sin($dLon/2);

   $c = 2 * atan2(sqrt($a), sqrt(1-$a));

   return $earth_radius * $c;
}


/**
* AJAX: Obtiene las tiendas más cercanas y las devuelve en JSON
*/
function ajax_get_nearest_stores() {
   // Verificar si se recibieron los parámetros de latitud y longitud
   if (!isset($_POST['lat']) || !isset($_POST['lng'])) {
       wp_send_json_error("Latitud y longitud no recibidas.");
   }

   $lat = floatval($_POST['lat']);
   $lng = floatval($_POST['lng']);
   $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 5;

   // Obtener las tiendas más cercanas desde el caché JSON
   $stores = get_nearest_stores_from_cache($lat, $lng, $limit);

   if (!empty($stores)) {
       wp_send_json_success($stores);
   } else {
       wp_send_json_error("No se encontraron tiendas cercanas.");
   }
}
// Registrar el AJAX para usuarios logueados y no logueados
add_action('wp_ajax_get_nearest_stores', 'ajax_get_nearest_stores');
add_action('wp_ajax_nopriv_get_nearest_stores', 'ajax_get_nearest_stores');

function enqueue_nearest_stores_script() {
   wp_enqueue_script(
       'nearest-stores-js',
       get_template_directory_uri() . '/js/nearest-stores.js', 
       array(), 
       null, 
       true
   );

   // Agregar el atributo `type="module"` al script
   add_filter('script_loader_tag', function ($tag, $handle) {
       if ('nearest-stores-js' === $handle) {
           return str_replace('src=', 'type="module" src=', $tag);
       }
       return $tag;
   }, 10, 2);
}


function get_coordinates_by_zip($zip_code, $country = "Mexico") {
   $api_key = 'AIzaSyDvAWcrFvjJbMuHKilsG1tD0zVutGCvmSs'; // <-- Pon aquí tu API Key de Google
   $address = urlencode("{$zip_code}, {$country}");
   $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$api_key}";

   $response = wp_remote_get($url, ['timeout' => 10]);

   if (is_wp_error($response)) {
       return ['error' => 'No se pudo conectar a la API de Google'];
   }

   $body = wp_remote_retrieve_body($response);
   $data = json_decode($body, true);

   if (!empty($data['results']) && isset($data['results'][0]['geometry']['location'])) {
       $location = $data['results'][0]['geometry']['location'];
       return [
           'latitude' => $location['lat'],
           'longitude' => $location['lng']
       ];
   } else {
       return ['error' => 'No se encontraron coordenadas en Google'];
   }
}


// Función para manejar la solicitud AJAX
function ajax_get_coordinates_by_zip() {
   // Verifica si el código postal fue enviado
   if (!isset($_POST['zip_code']) || empty($_POST['zip_code'])) {
       wp_send_json_error(['error' => 'El código postal es obligatorio']);
       wp_die();
   }

   // Sanitizar el código postal
   $zip_code = sanitize_text_field($_POST['zip_code']);
   $coordinates = get_coordinates_by_zip($zip_code);

   if (isset($coordinates['error'])) {
       wp_send_json_error($coordinates);
   } else {
       wp_send_json_success($coordinates);
   }

   wp_die();
}

// Registra las funciones AJAX para usuarios autenticados y no autenticados
add_action('wp_ajax_get_coordinates_by_zip', 'ajax_get_coordinates_by_zip');
add_action('wp_ajax_nopriv_get_coordinates_by_zip', 'ajax_get_coordinates_by_zip');

// Encola el script y pasa la URL de AJAX a JavaScript
// function enqueue_ajax_script() {
//    wp_enqueue_script('ajax-coordinates', get_template_directory_uri() . '/js/ajax-coordinates.js', ['jquery'], null, true);

//    wp_localize_script('ajax-coordinates', 'ajax_object', [
//        'ajax_url' => admin_url('admin-ajax.php'),
//        'nonce'    => wp_create_nonce('get_coordinates_nonce')
//    ]);
// }
// add_action('wp_enqueue_scripts', 'enqueue_ajax_script'); de AJAX a JavaScript
// function enqueue_ajax_script() {
//    wp_enqueue_script('ajax-coordinates', get_template_directory_uri() . '/js/ajax-coordinates.js', ['jquery'], null, true);

//    wp_localize_script('ajax-coordinates', 'ajax_object', [
//        'ajax_url' => admin_url('admin-ajax.php'),
//        'nonce'    => wp_create_nonce('get_coordinates_nonce')
//    ]);
// }
// add_action('wp_enqueue_scripts', 'enqueue_ajax_script');