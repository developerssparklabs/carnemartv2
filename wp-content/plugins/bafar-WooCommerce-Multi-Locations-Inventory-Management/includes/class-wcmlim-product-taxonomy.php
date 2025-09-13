<?php

/**
 *
 * @since      1.0.0
 * @package    Wcmlim
 * @subpackage Wcmlim/includes
 * @author     Techspawn Solutions <contact@techspawn.com>
 */

class Wcmlim_Product_Taxonomy
{

  /**
   * Construct.
   *
   * @since 1.1.0
   */
  public function __construct()
  {
    add_action('init', array($this, 'create_taxonomy'), 1);
    add_action('locations_add_form', array($this, 'hideFields'));
    add_action('locations_edit_form', array($this, 'hideFields'));
    add_filter('manage_edit-locations_columns', array($this, 'editColumns'));
    add_action('locations_edit_form', array($this, 'formFields'), 100, 2);
    add_action('locations_add_form_fields', array($this, 'formFields'), 10, 2);
    add_action('edited_locations', array($this, 'formSave'), 10, 2);
    add_action('created_locations', array($this, 'formSave'), 10, 2);
    add_action("delete_locations", array($this, 'execute_on_delete_locations'), 10, 4);
    add_filter('locations_row_actions', array($this, 'disable_quick_edit'), 10, 2);
    add_filter('manage_edit-location_group_columns', array($this, 'editColumns_locgroup'));
    add_filter('location_group_row_actions', array($this, 'editColumnsactions'), 10, 2);
    /*** Location Group */
    $isLocationsGroup = get_option('wcmlim_enable_location_group');
    if ($isLocationsGroup == "on") {
      add_action('location_group_edit_form', array($this, 'formFields_location_group'), 100, 2);
      add_action('location_group_add_form_fields', array($this, 'formFields_location_group'), 10, 2);
      add_action('edited_location_group', array($this, 'formSave_location_group'), 10, 2);
      add_action('created_location_group', array($this, 'formSave_location_group'), 10, 2);
    }
  }

  /**
   * Creates the taxonomy.
   *
   * @since 1.0.0
   * @return void
   */
  public function create_taxonomy()
  {
    $labels = array(
      'name' => esc_html('Locations', 'wcmlim'),
      'singular_name' => esc_html('Location', 'wcmlim'),
      'menu_name' => esc_html('Location', 'wcmlim'),
      'all_items' => esc_html('All Locations', 'wcmlim'),
      'parent_item' => esc_html('Parent Item', 'wcmlim'),
      'parent_item_colon' => esc_html('Parent Item:', 'wcmlim'),
      'new_item_name' => esc_html('New Location Name', 'wcmlim'),
      'add_new_item' => esc_html('Add New Location', 'wcmlim'),
      'edit_item' => esc_html('Edit Location', 'wcmlim'),
      'update_item' => esc_html('Update Location', 'wcmlim'),
      'separate_items_with_commas' => esc_html('Separate Location with commas', 'wcmlim'),
      'search_items' => esc_html('Search Locations', 'wcmlim'),
      'add_or_remove_items' => esc_html('Add or remove Location', 'wcmlim'),
      'choose_from_most_used' => esc_html('Choose from the most used Location', 'wcmlim'),
      'lat' => esc_html('Lat', 'wcmlim'),
      'lng' => esc_html('Lng', 'wcmlim'),

    );
    $capabilities = array(
      'manage_terms' => 'manage_woocommerce',
      'edit_terms' => 'manage_woocommerce',
      'delete_terms' => 'manage_woocommerce',
      'assign_terms' => 'manage_woocommerce',
    );

    $locationWidget = get_option('wcmlim_enable_location_widget');
    if ($locationWidget == 'on') {
      $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_in_quick_edit' => false,
        'show_admin_column' => true,
        'show_in_menu' => true,
        'show_tagcloud' => true,
        'capabilities' => $capabilities,
      );
    } else {
      $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_in_quick_edit' => false,
        'show_admin_column' => true,
        'show_in_menu' => true,
        'show_tagcloud' => true,
        'capabilities' => $capabilities,
        'meta_box_cb' => false
      );
    }





    register_taxonomy('locations', 'product', $args);

    register_term_meta('locations', 'wcmlim_street_address', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_city', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_postcode', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_country_state', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_email', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_phone', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_location_priority', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_start_time', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_end_time', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_street_number', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_route', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_locality', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_administrative_area_level_1', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_postal_code', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_country', ['show_in_rest' => true]);
    register_taxonomy_for_object_type('locations', 'product');
    register_term_meta('locations', 'wcmlim_lat', ['show_in_rest' => true]);
    register_term_meta('locations', 'wcmlim_lng', ['show_in_rest' => true]);

    /**start Location Group */
    register_term_meta('locations', 'wcmlim_locator', ['show_in_rest' => true]);

    register_taxonomy('location_group', 'product', array(
      'labels' => array(
        'name' => _x('Location Group', 'taxonomy general name'),
        'singular_name' => _x('Location Group', 'taxonomy singular name'),
        'search_items' => __('Search Location Groups'),
        'all_items' => __('All Location Group'),
        'parent_item' => __('Parent Location Group'),
        'parent_item_colon' => __('Parent Location Group:'),
        'edit_item' => __('Edit Location Group'),
        'update_item' => __('Update Location Group'),
        'add_new_item' => __('Add New Location Group'),
        'new_item_name' => __('New Location Group Name'),
        'menu_name' => __('Location Groups'),
      ),
      'meta_box_cb' => false,
    ));
    register_taxonomy_for_object_type('location_group', 'product');
    /** End Location Group */
  }

  /**
   * Hide unused fields from admin
   */
  public function hideFields()
  {
    echo '<style> #wpseo_meta { position: absolute; margin-top: 75%; } .term-description-wrap, td.description.column-description { display:none; } </style>';
  }

  /**
   * Change columns displayed in table
   *
   * @param $columns
   *
   * @return mixed
   */
  public function editColumns($columns)
  {
    if (isset($columns['description'])) {
      unset($columns['description']);
    }
    return $columns;
  }
  /**
   * Remove view for location group
   *
   * @param $columns
   *
   * @return mixed
   */
  public function editColumnsactions($actions, $tag)
  {
    unset($actions['view']);
    return $actions;
  }

  /**
   * Change columns displayed in table for location_group
   *
   * @param $columns
   *
   * @return mixed
   */
  public function editColumns_locgroup($columns)
  {

    if (isset($columns['count'])) {
      unset($columns['count']);
    }
    return $columns;
  }

  /**
   * Hide quick edit on locations taxonomy
   * 
   */

  public function disable_quick_edit($actions = array(), $post = null)
  {
    if ($post->taxonomy !== "locations") {
      return $actions;
    }

    if (isset($actions['inline hide-if-no-js'])) {
      unset($actions['inline hide-if-no-js']);
    }

    return $actions;
  }
  /**
   * Update stock on deleting locations
   * 
   */
  public function execute_on_delete_locations($term, $tt_id, $deleted_term, $object_ids)
  {
    //You can write code here to be executed when this action occurs in WordPress. Use the parameters received in the function arguments & implement the required additional custom functionality according to your website requirements.
    $args = array(
      'post_type' => 'product',
      'numberposts' => -1,
    );
    $product_info = get_posts($args);
    foreach ($product_info as $key => $value) {
      $total = 0;
      $product_id = $value->ID;
      $product = wc_get_product($product_id);
      $locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
      foreach ($locations as $location) {
        $total += intval(get_post_meta($product_id, "wcmlim_stock_at_{$location->term_id}", true));
      }
      if (intval($total) > 0) {
        update_post_meta($product_id, '_stock', $total);
      }

    }
  }
  /**
   * Form fields
   *
   * @param $tag
   */
  public function formFields_location_group($term)
  {
    if (is_object($term)) {
      $groupshopManager = get_term_meta($term->term_id, 'wcmlim_shop_regmanager', true);
      (isset($groupshopManager)) ? $groupshopManager : $groupshopManager = '';
      ob_start();
      include_once plugin_dir_path(dirname(__FILE__)) . 'includes/views/lgroup-taxonomy-fields-edit.php';
      $template = ob_get_contents();
      ob_end_clean();
      if (!empty($template)) {
        printf($template);
      }
    } else {
      ob_start();
      include_once plugin_dir_path(dirname(__FILE__)) . 'includes/views/lgroup-taxonomy-fields-new.php';
      $template = ob_get_contents();
      ob_end_clean();
      if (!empty($template)) {
        printf($template);
      }
    }
  }

  /**
   * Form fields
   *
   * @param $tag
   */
  public function formFields($term)
  {
    if (is_object($term)) {
      $street_address = get_term_meta($term->term_id, 'wcmlim_street_address', true);
      (isset($street_address)) ? $street_address : $street_address = '';

      $city = get_term_meta($term->term_id, 'wcmlim_city', true);
      (isset($city)) ? $city : $city = '';

      $postcode = get_term_meta($term->term_id, 'wcmlim_postcode', true);
      (isset($postcode)) ? $postcode : $postcode = '';

      $country_state = get_term_meta($term->term_id, 'wcmlim_country_state', true);
      (isset($country_state)) ? $country_state : $country_state = '';

      $wcmlim_shipping_zone = get_term_meta($term->term_id, 'wcmlim_shipping_zone', true);
      $wcmlim_tax_location = get_term_meta($term->term_id, 'wcmlim_tax_locations', true);
      $wcmlim_payment_methods = get_term_meta($term->term_id, 'wcmlim_payment_methods', true);
      $wcmlim_dimension_unit = get_term_meta($term->term_id, 'wcmlim_dimension_unit', true);
      // (isset($wcmlim_shipping_zone)) ? $wcmlim_shipping_zone : $wcmlim_shipping_zone = '';

      $streetNumber = get_term_meta($term->term_id, 'wcmlim_street_number', true);
      (isset($streetNumber)) ? $streetNumber : $streetNumber = '';
      $route = get_term_meta($term->term_id, 'wcmlim_route', true);
      (isset($route)) ? $route : $route = '';
      $locality = get_term_meta($term->term_id, 'wcmlim_locality', true);
      (isset($locality)) ? $locality : $locality = '';
      $state = get_term_meta($term->term_id, 'wcmlim_administrative_area_level_1', true);
      (isset($state)) ? $state : $state = '';
      $postal_code = get_term_meta($term->term_id, 'wcmlim_postal_code', true);
      (isset($postal_code)) ? $postal_code : $postal_code = '';
      $country = get_term_meta($term->term_id, 'wcmlim_country', true);
      (isset($country)) ? $country : $country = '';
      $locator = get_term_meta($term->term_id, 'wcmlim_locator', true);
      (isset($locator)) ? $locator : $locator = '';
      $email = get_term_meta($term->term_id, 'wcmlim_email', true);
      (isset($email)) ? $email : $email = '';
      $phone = get_term_meta($term->term_id, 'wcmlim_phone', true);
      (isset($phone)) ? $phone : $phone = '';
      $locPriority = get_term_meta($term->term_id, 'wcmlim_location_priority', true);
      (isset($locPriority)) ? $locPriority : $locPriority = '';

      $start_time = get_term_meta($term->term_id, 'wcmlim_start_time', true);
      (isset($start_time)) ? $start_time : $start_time = '';
      $end_time = get_term_meta($term->term_id, 'wcmlim_end_time', true);
      (isset($end_time)) ? $end_time : $end_time = '';

      $lshopManager = get_term_meta($term->term_id, 'wcmlim_shop_manager', true);
      (isset($lshopManager)) ? $lshopManager : $lshopManager = '';

      $wcmlim_shipping_method = get_term_meta($term->term_id, 'wcmlim_shipping_method', true);
      (isset($wcmlim_shipping_method)) ? $wcmlim_shipping_method : $wcmlim_shipping_method = '';

      $wcmlim_pos_compatiblity = get_term_meta($term->term_id, 'wcmlim_pos_compatiblity', true);
      (isset($wcmlim_pos_compatiblity)) ? $wcmlim_pos_compatiblity : $wcmlim_pos_compatiblity = '';

      $wcpos_selected_outlet = get_term_meta($term->term_id, 'wcmlim_wcpos_compatiblity', true);
      (isset($wcpos_selected_outlet)) ? $wcpos_selected_outlet : $wcpos_selected_outlet = '';

      $allow_pickup = get_term_meta($term->term_id, 'wcmlim_allow_pickup', true);
      (isset($allow_pickup)) ? $allow_pickup : $allow_pickup = 'no';

      $location_radius = get_term_meta($term->term_id, 'wcmlim_service_radius_for_location', true);
      (isset($location_radius)) ? $location_radius : $location_radius = '';

      $location_fee = get_term_meta($term->term_id, 'wcmlim_location_fee', true);
      (isset($location_fee)) ? $location_fee : $location_fee = '';


      $get_direction = get_term_meta($term->term_id, 'wcmlim_get_direction_for_location', true);
      (isset($get_direction)) ? $get_direction : $get_direction = '';
      $wcmlim_lat = get_term_meta($term->term_id, 'wcmlim_lat', true);
      (isset($wcmlim_lat)) ? $wcmlim_lat : $wcmlim_lat = '';
      $wcmlim_lng = get_term_meta($term->term_id, 'wcmlim_lng', true);
      (isset($wcmlim_lng)) ? $wcmlim_lng : $wcmlim_lng = '';

      $wcmlim_stockupp_pos = get_term_meta($term->term_id, 'wcmlim_stockupp_pos', true);
      (isset($wcmlim_stockupp_pos)) ? $wcmlim_stockupp_pos : $wcmlim_stockupp_pos = '';

      ob_start();
      include_once plugin_dir_path(dirname(__FILE__)) . 'includes/views/taxonomy-fields-edit.php';
      $template = ob_get_contents();
      ob_end_clean();
      if (!empty($template)) {
        printf($template);
      }
    } else {
      ob_start();
      include_once plugin_dir_path(dirname(__FILE__)) . 'includes/views/taxonomy-fields-new.php';
      $template = ob_get_contents();
      ob_end_clean();
      if (!empty($template)) {
        printf($template);
      }
    }
  }
  /**
   * Save term meta
   *
   * @param $term_id
   */
  public function formSave_location_group($term_id)
  {
    if (isset($_POST['wcmlim_email_regmanager'])) {
      update_term_meta($term_id, 'wcmlim_email_regmanager', sanitize_text_field($_POST['wcmlim_email_regmanager']));
    }
    if ($_POST) {
      $groupshopManager = isset($_POST['wcmlim_shop_regmanager']) ? (array) $_POST['wcmlim_shop_regmanager'] : array();
      $groupshopManager = array_map('esc_attr', $groupshopManager);
      update_term_meta($term_id, 'wcmlim_shop_regmanager', $groupshopManager);


      if (isset($_POST['wcmlim_location'])) {

        $wcmlim_location = get_term_meta($term_id, 'wcmlim_location', true);
        $wcmlim_location = is_array($wcmlim_location) ? $wcmlim_location : array();

        if (!empty($_POST['wcmlim_location']) && is_array($_POST['wcmlim_location'])) {
          $term_id_to_remove = array_diff($wcmlim_location, $_POST['wcmlim_location']);
          if (!empty($term_id_to_remove)) {
            foreach ($term_id_to_remove as $removable_term_id) {
              $sanitized_removable_term_id = sanitize_text_field($removable_term_id);
              update_term_meta($sanitized_removable_term_id, 'wcmlim_locator', '');
            }
          }
        } else {
          $term_id_to_remove = array(); // Set an empty array if $_POST['wcmlim_location'] is not an array or is empty
        }

        $locations = array_map('sanitize_text_field', $_POST['wcmlim_location']);
        update_term_meta($term_id, 'wcmlim_location', $locations);

        foreach ($locations as $location_id) {
          $sanitized_location_id = sanitize_text_field($location_id);
          update_term_meta($sanitized_location_id, 'wcmlim_locator', $term_id);
        }
      }
    }

    $wcmlim_shop_regmanager = get_term_meta($term_id, 'wcmlim_shop_regmanager', true);
    if ($_POST) {
      $term_meta = [
        'wcmlim_shop_regmanager' => $wcmlim_shop_regmanager,
      ];
    } else {
      $wcmlim_shop_regmanager = get_term_meta($term_id, 'wcmlim_shop_regmanager', true);
      $term_meta = [
        'wcmlim_shop_regmanager' => $wcmlim_shop_regmanager,
      ];
    }
    if (isset($term_meta)) {
      // Save the option array.
      update_option("taxonomy_$term_id", $term_meta);
    }
    // Regenerar JSON de location groups
    $this->wcmlim_save_location_groups_json();

    // Resync metas 'wcmlim_locator' en tiendas
    $this->wcmlim_resync_locator_meta_from_groups();
  }


  /**
   * ====================================================
   * Obtener Location Groups (taxonomy: location_group)
   * ====================================================
   *
   * Devuelve los grupos (estados). Por defecto **omite** los grupos
   * que no tengan tiendas asociadas (location_ids vacío) para evitar
   * ruido en el front.
   *
   * Puedes desactivar el filtrado pasando $skip_empty = false.
   *
   * @param array $args        Filtros para get_terms (hide_empty, parent, exclude, etc.).
   * @param bool  $skip_empty  Si TRUE, omite grupos sin tiendas vinculadas. Default: TRUE.
   * @return array             Lista de grupos con campos útiles.
   */
  public function wcmlim_get_all_location_groups(array $args = [], bool $skip_empty = true): array
  {
    // Defaults razonables: grupos raíz
    $defaults = [
      'taxonomy' => 'location_group',
      'hide_empty' => false,
      'parent' => 0,
    ];
    $q = wp_parse_args($args, $defaults);

    $terms = get_terms($q);
    if (is_wp_error($terms)) {
      return [];
    }

    $out = [];
    foreach ($terms as $t) {
      // Metas opcionales del plugin
      $email = get_term_meta($t->term_id, 'wcmlim_email_regmanager', true);
      $locations = get_term_meta($t->term_id, 'wcmlim_location', true);
      $shopManagers = get_term_meta($t->term_id, 'wcmlim_shop_regmanager', true);

      // Normalizar arrays
      $locations = is_array($locations)
        ? array_values(array_filter(array_map('intval', $locations)))
        : ($locations ? [(int) $locations] : []);

      // Validar que los location_ids existan en 'locations'
      $locations = array_values(array_filter($locations, function ($loc_id) {
        return $loc_id > 0 && term_exists($loc_id, 'locations');
      }));

      $shopManagers = is_array($shopManagers)
        ? array_map('sanitize_text_field', $shopManagers)
        : ($shopManagers ? [sanitize_text_field($shopManagers)] : []);

      // Si se pide omitir vacíos y este grupo no tiene tiendas, saltar
      if ($skip_empty && empty($locations)) {
        continue;
      }

      $out[] = [
        'term_id' => (int) $t->term_id,
        'group_name' => (string) $t->name,
        'slug' => (string) $t->slug,
        'email' => (string) $email,
        'location_ids' => $locations,      // IDs válidos de 'locations' ligados a este grupo
        'shop_regmanager' => $shopManagers,
      ];
    }

    return $out;
  }

  /**
   * =========================================================
   * Exporta Location Groups a /uploads/wcmlim/states.json
   * =========================================================
   * - No retorna datos para el front; solo escribe el archivo.
   * - Cualquier fallo se registra en error_log/debug.log.
   *
   * @param array  $args       Filtros para get_terms().
   * @param string $filename   Nombre del archivo destino.
   * @param bool   $skip_empty Si TRUE, omite grupos sin tiendas.
   */
  public function wcmlim_save_location_groups_json(
    array $args = [],
    string $filename = 'states.json',
    bool $skip_empty = true
  ): void {
    // Obtener datos ya armados
    $groups = $this->wcmlim_get_all_location_groups($args, $skip_empty);

    // Codificar JSON
    $json = wp_json_encode(
      $groups,
      JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    if ($json === false) {
      error_log('[wcmlim] JSON encode failed for states.json');
      return;
    }

    // Paths destino
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
      error_log('[wcmlim] Upload dir error: ' . $uploads['error']);
      return;
    }

    $dir = trailingslashit($uploads['basedir']) . 'wcmlim/';
    $path = $dir . $filename;

    if (!wp_mkdir_p($dir)) {
      error_log('[wcmlim] mkdir failed: ' . $dir);
      return;
    }

    // Escribir (LOCK_EX para evitar lecturas parciales)
    $bytes = @file_put_contents($path, $json, LOCK_EX);
    if ($bytes === false) {
      error_log('[wcmlim] write failed: ' . $path);
      return;
    }
  }

  /**
   * Resync: para cada location_group, asigna en cada tienda (locations)
   * la meta 'wcmlim_locator' con el term_id del grupo.
   */
  public function wcmlim_resync_locator_meta_from_groups(): array
  {
    $groups = get_terms([
      'taxonomy' => 'location_group',
      'hide_empty' => false,
      'parent' => 0,
    ]);
    if (is_wp_error($groups)) {
      return ['error' => $groups->get_error_message()];
    }

    $updated = 0;

    foreach ($groups as $g) {
      $loc_ids = get_term_meta($g->term_id, 'wcmlim_location', true);
      $loc_ids = is_array($loc_ids) ? $loc_ids : ($loc_ids ? [(int) $loc_ids] : []);
      $loc_ids = array_values(array_filter(array_map('intval', $loc_ids)));

      if (!$loc_ids) {
        continue;
      }

      foreach ($loc_ids as $loc_id) {
        if (!term_exists($loc_id, 'locations')) {
          continue;
        }
        update_term_meta($loc_id, 'wcmlim_locator', (int) $g->term_id);
        $updated++;
      }
    }

    return ['updated' => $updated];
  }

  /**
   * Save term meta
   *
   * @param $term_id
   */
  public function formSave($term_id)
  {

    $autofill = get_option('wcmlim_enable_autocomplete_address');
    if ($_POST) {
      $shippingZone = isset($_POST['wcmlim_shipping_zone']) ? (array) $_POST['wcmlim_shipping_zone'] : array();
      $shippingZone = array_map('esc_attr', $shippingZone);

      $shippingMethod = isset($_POST['wcmlim_shipping_method']) ? (array) $_POST['wcmlim_shipping_method'] : array();
      $shippingMethod = array_map('esc_attr', $shippingMethod);

      $paymentMethods = isset($_POST['wcmlim_payment_methods']) ? (array) $_POST['wcmlim_payment_methods'] : array();
      $paymentMethods = array_map('esc_attr', $paymentMethods);
      $wcmlim_dimension_unit = isset($_POST['wcmlim_dimension_unit']) ? $_POST['wcmlim_dimension_unit'] : '';

      $shopManager = isset($_POST['wcmlim_shop_manager']) ? (array) $_POST['wcmlim_shop_manager'] : array();
      $shopManager = array_map('esc_attr', $shopManager);

      update_term_meta($term_id, 'wcmlim_street_number', sanitize_text_field($_POST['wcmlim_street_number']));
      update_term_meta($term_id, 'wcmlim_locator', sanitize_text_field($_POST['wcmlim_locator']));
      update_term_meta($term_id, 'wcmlim_route', sanitize_text_field($_POST['wcmlim_route']));
      update_term_meta($term_id, 'wcmlim_locality', sanitize_text_field($_POST['wcmlim_locality']));
      update_term_meta($term_id, 'wcmlim_administrative_area_level_1', sanitize_text_field($_POST['wcmlim_administrative_area_level_1']));
      update_term_meta($term_id, 'wcmlim_postal_code', sanitize_text_field($_POST['wcmlim_postal_code']));
      update_term_meta($term_id, 'wcmlim_country', sanitize_text_field($_POST['wcmlim_country']));
      update_term_meta($term_id, 'wcmlim_email', sanitize_text_field($_POST['wcmlim_email']));
      update_term_meta($term_id, 'wcmlim_phone', sanitize_text_field($_POST['wcmlim_phone']));
      update_term_meta($term_id, 'wcmlim_location_priority', sanitize_text_field($_POST['wcmlim_location_priority']));
      update_term_meta($term_id, 'wcmlim_start_time', sanitize_text_field($_POST['wcmlim_start_time']));
      update_term_meta($term_id, 'wcmlim_end_time', sanitize_text_field($_POST['wcmlim_end_time']));
      update_term_meta($term_id, 'wcmlim_shipping_zone', $shippingZone);
      update_term_meta($term_id, 'wcmlim_payment_methods', $paymentMethods);
      update_term_meta($term_id, 'wcmlim_shop_manager', $shopManager);
      update_term_meta($term_id, 'wcmlim_pos_compatiblity', $_POST['wcmlim_pos_compatiblity']);
      update_term_meta($term_id, 'wcmlim_wcpos_compatiblity', $_POST['wcmlim_wcpos_compatiblity']);
      update_term_meta($term_id, 'wcmlim_shipping_method', $shippingMethod);
      update_term_meta($term_id, 'wcmlim_allow_pickup', sanitize_text_field($_POST['wcmlim_allow_pickup']));
      update_term_meta($term_id, 'wcmlim_service_radius_for_location', sanitize_text_field($_POST['wcmlim_service_radius']));
      update_term_meta($term_id, 'wcmlim_location_fee', sanitize_text_field($_POST['wcmlim_location_fee']));
      update_term_meta($term_id, 'wcmlim_dimension_unit', $wcmlim_dimension_unit);
      update_term_meta($term_id, 'wcmlim_lat', sanitize_text_field($_POST['wcmlim_lat']));
      update_term_meta($term_id, 'wcmlim_lng', sanitize_text_field($_POST['wcmlim_lng']));
      update_term_meta($term_id, 'wcmlim_stockupp_pos', $_POST['wcmlim_stockupp_pos']);
      // for group field   
      update_term_meta(sanitize_text_field($_POST['wcmlim_locator']), 'wcmlim_location', $term_id);
    }

    if ($_POST && isset($_POST['wcmlim_street_address']) && isset($_POST['wcmlim_city']) && isset($_POST['wcmlim_postcode']) && isset($_POST['wcmlim_country_state'])) {
      update_term_meta($term_id, 'wcmlim_street_address', sanitize_text_field($_POST['wcmlim_street_address']));
      update_term_meta($term_id, 'wcmlim_city', sanitize_text_field($_POST['wcmlim_city']));
      update_term_meta($term_id, 'wcmlim_postcode', intval($_POST['wcmlim_postcode']));
      update_term_meta($term_id, 'wcmlim_country_state', sanitize_text_field($_POST['wcmlim_country_state']));
      update_term_meta($term_id, 'wcmlim_email', sanitize_text_field($_POST['wcmlim_email']));
      update_term_meta($term_id, 'wcmlim_phone', sanitize_text_field($_POST['wcmlim_phone']));
      update_term_meta($term_id, 'wcmlim_location_priority', sanitize_text_field($_POST['wcmlim_location_priority']));
      update_term_meta($term_id, 'wcmlim_start_time', sanitize_text_field($_POST['wcmlim_start_time']));
      update_term_meta($term_id, 'wcmlim_end_time', sanitize_text_field($_POST['wcmlim_end_time']));
      update_term_meta($term_id, 'wcmlim_dimension_unit', sanitize_text_field($_POST['wcmlim_dimension_unit']));
      $pwsz = isset($_POST['wcmlim_shipping_zone']) ? (array) $_POST['wcmlim_shipping_zone'] : array();
      $pwsz = array_map('esc_attr', $pwsz);
      update_term_meta($term_id, 'wcmlim_shipping_zone', $pwsz);

      $pwsm = isset($_POST['wcmlim_shipping_method']) ? (array) $_POST['wcmlim_shipping_method'] : array();
      $pwsm = array_map('esc_attr', $pwsm);
      update_term_meta($term_id, 'wcmlim_shipping_method', $pwsm);

      $pwpm = isset($_POST['wcmlim_payment_methods']) ? (array) $_POST['wcmlim_payment_methods'] : array();
      $pwpm = array_map('esc_attr', $pwpm);
      update_term_meta($term_id, 'wcmlim_payment_methods', $pwpm);
      $psm = isset($_POST['wcmlim_payment_methods']) ? (array) $_POST['wcmlim_shop_manager'] : array();
      $psm = array_map('esc_attr', $psm);
      update_term_meta($term_id, 'wcmlim_shop_manager', $psm);
      update_term_meta($term_id, 'wcmlim_pos_compatiblity', $_POST['wcmlim_pos_compatiblity']);
      update_term_meta($term_id, 'wcmlim_wcpos_compatiblity', $_POST['wcmlim_wcpos_compatiblity']);
      update_term_meta($term_id, 'wcmlim_allow_pickup', sanitize_text_field($_POST['wcmlim_allow_pickup']));
      update_term_meta($term_id, 'wcmlim_service_radius_for_location', sanitize_text_field($_POST['wcmlim_service_radius']));
      update_term_meta($term_id, 'wcmlim_location_fee', sanitize_text_field($_POST['wcmlim_location_fee']));
      update_term_meta($term_id, 'wcmlim_lat', sanitize_text_field($_POST['wcmlim_lat']));
      update_term_meta($term_id, 'wcmlim_lng', sanitize_text_field($_POST['wcmlim_lng']));
      update_term_meta($term_id, 'wcmlim_stockupp_pos', sanitize_text_field($_POST['wcmlim_stockupp_pos']));

    }
    $wsz = isset($_POST['wcmlim_shipping_zone']) ? (array) $_POST['wcmlim_shipping_zone'] : array();
    $wsz = array_map('esc_attr', $wsz);
    $wsm = isset($_POST['wcmlim_shipping_method']) ? (array) $_POST['wcmlim_shipping_method'] : array();
    $wsm = array_map('esc_attr', $wsm);
    $wst = isset($_POST['wcmlim_tax_locations']) ? (array) $_POST['wcmlim_tax_locations'] : array();
    $wst = array_map('esc_attr', $wst);
    $wpm = isset($_POST['wcmlim_payment_methods']) ? (array) $_POST['wcmlim_payment_methods'] : array();
    $wpm = array_map('esc_attr', $wpm);

    update_term_meta($term_id, 'wcmlim_shipping_zone', $wsz);
    update_term_meta($term_id, 'wcmlim_tax_locations', $wst);
    update_term_meta($term_id, 'wcmlim_payment_methods', $wpm);
    update_term_meta($term_id, 'wcmlim_shipping_method', $wsm);

    $wcmlim_payment_methods[] = get_term_meta($term_id, 'wcmlim_payment_methods', true);
    $wcmlim_shipping_zone[] = get_term_meta($term_id, 'wcmlim_shipping_zone', true);
    $wcmlim_tax_locations[] = get_term_meta($term_id, 'wcmlim_tax_locations', true);
    $wcmlim_shop_manager = get_term_meta($term_id, 'wcmlim_shop_manager', true);
    $wcmlim_shipping_method[] = get_term_meta($term_id, 'wcmlim_shipping_method', true);



    if ($_POST) {

      $streetNumber = get_term_meta($term_id, 'wcmlim_street_number', true);
      $locator = get_term_meta($term_id, 'wcmlim_locator', true);
      $route = get_term_meta($term_id, 'wcmlim_route', true);
      $locality = get_term_meta($term_id, 'wcmlim_locality', true);
      $state = get_term_meta($term_id, 'wcmlim_administrative_area_level_1', true);
      $postal_code = get_term_meta($term_id, 'wcmlim_postal_code', true);
      $country = get_term_meta($term_id, 'wcmlim_country', true);
      $wcmlim_pos_compatiblity = get_term_meta($term_id, 'wcmlim_pos_compatiblity', true);
      $wcpos_selected_outlet = get_term_meta($term_id, 'wcmlim_wcpos_compatiblity', true);
      $allow_pickup = get_term_meta($term_id, 'wcmlim_allow_pickup', true);
      $service_radius = get_term_meta($term_id, 'wcmlim_service_radius_for_location', true);
      $location_fee = get_term_meta($term_id, 'wcmlim_location_fee', true);

      $wcmlim_dimension_unit = get_term_meta($term_id, 'wcmlim_dimension_unit', true);
      $wcmlim_tax_locations = get_term_meta($term_id, 'wcmlim_tax_locations', true);
      $wcmlim_lat = get_term_meta($term_id, 'wcmlim_lat', true);
      $wcmlim_lng = get_term_meta($term_id, 'wcmlim_lng', true);
      $wcmlim_stockupp_pos = get_term_meta($term_id, 'wcmlim_stockupp_pos', true);


      $term_meta = [
        'wcmlim_street_number' => $streetNumber,
        'wcmlim_locator' => $locator,
        'wcmlim_route' => $route,
        'wcmlim_locality' => $locality,
        'wcmlim_administrative_area_level_1' => $state,
        'wcmlim_postal_code' => $postal_code,
        'wcmlim_shipping_zone' => $wcmlim_shipping_zone,
        'wcmlim_tax_locations' => $wcmlim_tax_locations,
        'wcmlim_payment_methods' => $wcmlim_payment_methods,
        'wcmlim_dimension_unit' => $wcmlim_dimension_unit,
        'wcmlim_country' => $country,
        'wcmlim_shop_manager' => $wcmlim_shop_manager,
        'wcmlim_pos_compatiblity' => $wcmlim_pos_compatiblity,
        'wcmlim_wcpos_compatiblity' => $wcpos_selected_outlet,
        'wcmlim_shipping_method' => $wcmlim_shipping_method,
        'wcmlim_stockupp_pos' => $wcmlim_stockupp_pos,
        'wcmlim_allow_pickup' => $allow_pickup,
        'wcmlim_service_radius' => $service_radius,
        'wcmlim_location_fee' => $location_fee,
        'wcmlim_lat' => $wcmlim_lat,
        'wcmlim_lng' => $wcmlim_lng
      ];
    } else {
      $street_address = get_term_meta($term_id, 'wcmlim_street_address', true);
      $locator = get_term_meta($term_id, 'wcmlim_locator', true);
      $city = get_term_meta($term_id, 'wcmlim_city', true);
      $postcode = get_term_meta($term_id, 'wcmlim_postcode', true);
      $country_state = get_term_meta($term_id, 'wcmlim_country_state', true);
      $wcmlim_email = get_term_meta($term_id, 'wcmlim_email', true);
      $wcmlim_phone = get_term_meta($term_id, 'wcmlim_phone', true);
      $wcmlim_location_priority = get_term_meta($term_id, 'wcmlim_location_priority', true);
      $wcmlim_start_time = get_term_meta($term_id, 'wcmlim_start_time', true);
      $wcmlim_end_time = get_term_meta($term_id, 'wcmlim_end_time', true);
      $wcmlim_dimension_unit = get_term_meta($term_id, 'wcmlim_dimension_unit', true);
      $wcmlim_shipping_zone = array();
      $wcmlim_shipping_zone[] = get_term_meta($term_id, 'wcmlim_shipping_zone', true);
      $wcmlim_shipping_method = array();
      $wcmlim_shipping_method[] = get_term_meta($term_id, 'wcmlim_shipping_method', true);
      $wcmlim_tax_locations = array();
      $wcmlim_tax_locations[] = get_term_meta($term_id, 'wcmlim_tax_locations', true);
      $wcmlim_shop_manager = get_term_meta($term_id, 'wcmlim_shop_manager', true);
      $wcmlim_payment_methods = array();
      $wcmlim_payment_methods[] = get_term_meta($term_id, 'wcmlim_payment_methods', true);
      $wcmlim_pos_compatiblity = get_term_meta($term_id, 'wcmlim_pos_compatiblity', true);
      $wcpos_selected_outlet = get_term_meta($term_id, 'wcmlim_wcpos_compatiblity', true);
      $wcmlim_lat = get_term_meta($term_id, 'wcmlim_lat', true);
      $wcmlim_lng = get_term_meta($term_id, 'wcmlim_lng', true);
      $wcmlim_stockupp_pos = get_term_meta($term_id, 'wcmlim_stockupp_pos', true);
      $location_fee = get_term_meta($term_id, 'wcmlim_location_fee', true);

      $allow_pickup = get_term_meta($term_id, 'wcmlim_allow_pickup', true);
      $service_radius = get_term_meta($term_id, 'wcmlim_service_radius_for_location', true);
      $term_meta = [
        'wcmlim_street_address' => $street_address,
        'wcmlim_locator' => $locator,
        'wcmlim_city' => $city,
        'wcmlim_postcode' => $postcode,
        'wcmlim_country_state' => $country_state,
        'wcmlim_shipping_zone' => $wcmlim_shipping_zone,
        'wcmlim_tax_locations' => $wcmlim_tax_locations,
        'wcmlim_payment_methods' => $wcmlim_payment_methods,
        'wcmlim_dimension_unit' => $wcmlim_dimension_unit,
        'wcmlim_email' => $wcmlim_email,
        'wcmlim_phone' => $wcmlim_phone,
        'wcmlim_location_priority' => $wcmlim_location_priority,
        'wcmlim_start_time' => $wcmlim_start_time,
        'wcmlim_end_time' => $wcmlim_end_time,
        'wcmlim_shop_manager' => $wcmlim_shop_manager,
        'wcmlim_pos_compatiblity' => $wcmlim_pos_compatiblity,
        'wcmlim_wcpos_compatiblity' => $wcpos_selected_outlet,
        'wcmlim_shipping_method' => $wcmlim_shipping_method,
        'wcmlim_stockupp_pos' => $wcmlim_stockupp_pos,
        'wcmlim_allow_pickup' => $allow_pickup,
        'wcmlim_lat' => $wcmlim_lat,
        'wcmlim_lng' => $wcmlim_lng,
        'wcmlim_service_radius' => $service_radius,
        'wcmlim_location_fee' => $location_fee
      ];
    }
    if (isset($term_meta)) {
      // Save the option array.
      update_option("taxonomy_$term_id", $term_meta);
    }
    // Regenerar JSON de locations
    $this->wcmlim_generate_store_shards();
  }

  /**
   * =========================================================
   * Genera shards por estado y un índice:
   *   /uploads/wcmlim/stores/state-<ID>.json
   *   /uploads/wcmlim/stores/index.json
   * =========================================================
   * - Ignora tiendas sin 'wcmlim_locator' válido o estado inexistente.
   * - No retorna datos para el front; solo escribe archivos.
   * - Errores se registran en error_log/debug.log.
   */
  public function wcmlim_generate_store_shards(): void
  {
    // Traer todas las tiendas
    $terms = get_terms([
      'taxonomy' => 'locations',
      'hide_empty' => false,
    ]);
    if (is_wp_error($terms)) {
      error_log('[wcmlim] get_terms error: ' . $terms->get_error_message());
      return;
    }

    // Metakeys permitidos para el payload
    $allow = [
      'wcmlim_street_number',
      'wcmlim_route',
      'wcmlim_locality',
      'wcmlim_postal_code',
      'wcmlim_lat',
      'wcmlim_lng',
      'wcmlim_locator', // term_id del estado (location_group)
    ];

    // Paths destino
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) {
      error_log('[wcmlim] Upload dir error: ' . $uploads['error']);
      return;
    }

    $base_dir = trailingslashit($uploads['basedir']) . 'wcmlim/stores/';
    $base_url = trailingslashit($uploads['baseurl']) . 'wcmlim/stores/';

    if (!wp_mkdir_p($base_dir)) {
      error_log('[wcmlim] mkdir failed: ' . $base_dir);
      return;
    }

    // Agrupar tiendas por estado
    $by_state = []; // state_id => [stores...]
    foreach ($terms as $t) {
      $raw = get_term_meta($t->term_id);

      // whitelist meta
      $meta = [];
      foreach ($allow as $k) {
        $val = isset($raw[$k]) ? maybe_unserialize(reset($raw[$k])) : '';
        $meta[$k] = is_scalar($val) ? $val : '';
      }

      // estado válido
      $state_id = isset($meta['wcmlim_locator']) && is_numeric($meta['wcmlim_locator'])
        ? (int) $meta['wcmlim_locator']
        : 0;

      if ($state_id <= 0 || !term_exists($state_id, 'location_group')) {
        continue; // ignorar tiendas sin estado válido
      }

      $by_state[$state_id][] = [
        'id' => (int) $t->term_id,
        'name' => $t->name,
        'slug' => $t->slug,
        'meta' => [
          'wcmlim_street_number' => (string) ($meta['wcmlim_street_number'] ?? ''),
          'wcmlim_route' => (string) ($meta['wcmlim_route'] ?? ''),
          'wcmlim_locality' => (string) ($meta['wcmlim_locality'] ?? ''),
          'wcmlim_postal_code' => (string) ($meta['wcmlim_postal_code'] ?? ''),
          'wcmlim_lat' => (string) ($meta['wcmlim_lat'] ?? ''),
          'wcmlim_lng' => (string) ($meta['wcmlim_lng'] ?? ''),
        ],
      ];
    }

    // Escribir shards e index (sin ?v en paths; el ?v lo harás al consumir)
    $index = []; // state_id => ['count'=>N, 'url'=>...]
    foreach ($by_state as $state_id => $stores) {
      $state_path = $base_dir . "state-{$state_id}.json";
      $json = wp_json_encode($stores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

      if ($json === false) {
        error_log("[wcmlim] JSON encode failed for state {$state_id}");
        continue;
      }

      if (@file_put_contents($state_path, $json, LOCK_EX) === false) {
        error_log('[wcmlim] write failed: ' . $state_path);
        continue;
      }

      // Guarda URL base en el índice (sin ?v). El ?v se añade en el front.
      $index[$state_id] = [
        'count' => count($stores),
        'url' => $base_url . "state-{$state_id}.json",
      ];
    }

    // Escribir índice
    $index_path = $base_dir . 'index.json';
    $index_json = wp_json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($index_json === false) {
      error_log('[wcmlim] JSON encode failed for index.json');
      return;
    }

    if (@file_put_contents($index_path, $index_json, LOCK_EX) === false) {
      error_log('[wcmlim] write failed: ' . $index_path);
      return;
    }
  }
}
