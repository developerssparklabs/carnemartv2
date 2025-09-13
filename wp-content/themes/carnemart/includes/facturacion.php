<?php
$campos = [
   [
      "id" => "deseas_facturar",
      "label" => "¿Deseas facturar?",
      "type" => "checkbox",
      "priority" => 120,
      "group" => "billing"
   ],
   [
      "id" => "num_ext_factura",
      "label" => "Número exterior facuración",
      "type" => "text",
      "maxlength" => 10,
      "class" => ["form-row-first"],
      "priority" => 51,
      "placeholder" => "Ej. 123",
      "group" => "billing",
      "required" => true
   ],
   [
      "id" => "num_int_factura",
      "label" => "Número interior facturación",
      "type" => "text",
      "maxlength" => 10,
      "class" => ["form-row-last"],
      "priority" => 51,
      "placeholder" => "Ej. 4B",
      "group" => "billing"
   ],
   [
      "id" => "num_ext_envio",
      "label" => "Número exterior envío",
      "type" => "text",
      "maxlength" => 10,
      "class" => ["form-row-first"],
      "priority" => 51,
      "placeholder" => "Ej. 123",
      "group" => "shipping"
   ],
   [
      "id" => "num_int_envio",
      "label" => "Número interior envío",
      "type" => "text",
      "maxlength" => 10,
      "class" => ["form-row-last"],
      "priority" => 51,
      "placeholder" => "Ej. 4B",
      "group" => "shipping"
   ],
   [
      "id" => "rfc_factura",
      "label" => "RFC",
      "type" => "text",
      "maxlength" => 13,
      "class" => ["form-row-first"],
      "priority" => 121,
      "placeholder" => "Ingresa tu RFC",
      "group" => "billing"
   ],
   [
      "id" => "razon_social_factura",
      "label" => "Razón Social",
      "type" => "text",
      "class" => ["form-row-last"],
      "priority" => 122,
      "placeholder" => "Ingresa tu Razón Social",
      "group" => "billing"
   ],
   [
      "id" => "regimen_fiscal_factura",
      "label" => "Regimen Fiscal",
      "type" => "select",
      "options" => [
         "601" => "General de Ley Personas Morales",
         "603" => "Personas Morales con Fines no Lucrativos",
         "605" => "Sueldos y Salarios e Ingresos Asimilados a Salarios",
         "606" => "Arrendamiento",
         "607" => "Régimen de Enajenación o Adquisición de Bienes",
         "608" => "Demás ingresos",
         "609" => "Consolidación",
         "610" => "Residentes en el Extranjero sin Establecimiento Permanente en México",
         "611" => "Ingresos por Dividendos (socios y accionistas)",
         "612" => "Personas Físicas con Actividades Empresariales y Profesionales",
         "614" => "Ingresos por intereses",
         "615" => "Régimen de los ingresos por obtención de premios",
         "616" => "Sin obligaciones fiscales",
         "620" => "Sociedades Cooperativas de Producción que optan por diferir sus ingresos",
         "621" => "Incorporación Fiscal",
         "622" => "Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras",
         "623" => "Opcional para Grupos de Sociedades",
         "624" => "Coordinados",
         "625" => "Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas",
         "626" => "Régimen Simplificado de Confianza"
      ],
      "class" => ["form-row-first"],
      "priority" => 123,
      "group" => "billing"
   ],
   [
      "id" => "uso_cfdi_factura",
      "label" => "Uso de CFDI",
      "type" => "select",
      "options" => [
         "G01" => "Adquisición de mercancías",
         "G02" => "Devoluciones, descuentos o bonificaciones",
         "G03" => "Gastos en general",
         "I01" => "Construcciones",
         "I02" => "Mobiliario y equipo de oficina por inversiones",
         "I03" => "Equipo de transporte",
         "I04" => "Equipo de cómputo y accesorios",
         "I05" => "Dados, troqueles, moldes, matrices y herramental",
         "I06" => "Comunicaciones telefónicas",
         "I07" => "Comunicaciones satelitales",
         "I08" => "Otra maquinaria y equipo",
         "D01" => "Honorarios médicos, dentales y gastos hospitalarios",
         "D02" => "Gastos médicos por incapacidad o discapacidad",
         "D03" => "Gastos funerarios",
         "D04" => "Donativos",
         "D05" => "Intereses reales efectivamente pagados por créditos hipotecarios",
         "D06" => "Aportaciones voluntarias al SAR",
         "D07" => "Primas por seguros de gastos médicos",
         "D08" => "Gastos de transportación escolar obligatoria",
         "D09" => "Depósitos en cuentas para el ahorro, primas de seguros y pensiones",
         "D10" => "Pagos por servicios educativos (colegiaturas)",
         "S01" => "Sin efectos fiscales",
         "P01" => "Por definir"
      ],
      "class" => ["form-row-last"],
      "priority" => 124,
      "group" => "billing"
   ]

];
new WoocommerceFacturacionLaNaval($campos);

class WoocommerceFacturacionLaNaval
{
   private $campos;

   private $state_codes = [
      'AG' => '1',  // Aguascalientes
      'BC' => '2',  // Baja California
      'BS' => '3',  // Baja California Sur
      'CM' => '4',  // Campeche
      'CS' => '7',  // Chiapas
      'CH' => '8',  // Chihuahua
      'DF' => '9',  // Distrito Federal (si aún es necesario)
      'CDMX' => '36', // Ciudad de México
      'CO' => '5',  // Coahuila
      'CL' => '6',  // Colima
      'DG' => '10', // Durango
      'EM' => '33', // Estado de México
      'GT' => '11', // Guanajuato
      'GR' => '12', // Guerrero
      'HG' => '13', // Hidalgo
      'JA' => '14', // Jalisco
      'MI' => '16', // Michoacán
      'MO' => '17', // Morelos
      'ME' => '15', // México (¿Doble referencia con Estado de México?)
      'NA' => '18', // Nayarit
      'NL' => '19', // Nuevo León
      'OA' => '20', // Oaxaca
      'PU' => '21', // Puebla
      'QT' => '22', // Querétaro
      'QR' => '23', // Quintana Roo
      'SL' => '24', // San Luis Potosí
      'SI' => '25', // Sinaloa
      'SO' => '26', // Sonora
      'TB' => '27', // Tabasco
      'TM' => '28', // Tamaulipas
      'TL' => '29', // Tlaxcala
      'VE' => '30', // Veracruz
      'YU' => '31', // Yucatán
      'ZA' => '32', // Zacatecas
   ];

   private $api_url = 'http://sab.elceller.com.mx/cgi-bin/DB_SA_WEBsvcs.pl';
   // private $api_url = 'https://webhook.site/1c457ff4-6206-4b27-9f8d-dfeef0445840';

   /**
    * Constructor
    * @param array $campos
    */
   public function __construct($campos)
   {
      $this->campos = $campos;
      $this->init();
   }

   /**
    * Inicializa los hooks
    */
   private function init()
   {
      // Hook para añadir campos al checkout
      add_filter('woocommerce_checkout_fields', array($this, 'agregar_campos_facturacion'));

      // Hook para el script de mostrar/ocultar campos
      add_action('wp_footer', array($this, 'script_campos_facturacion'));

      // Hook para guardar campos en la orden
      add_action('woocommerce_checkout_update_order_meta', array($this, 'guardar_campos_facturacion'));

      // Hook para recuperar valores de campos en el checkout
      add_filter('woocommerce_checkout_get_value', array($this, 'recuperar_valor_campo'), 10, 2);

      // Hook para mostrar campos en el panel de administración
      add_action('woocommerce_admin_order_data_after_billing_address', function ($order) {
         $this->agregar_campos_facturacion_editable($order, 'billing');
      }, 10, 1);

      add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
         $this->agregar_campos_facturacion_editable($order, 'shipping');
      }, 10, 1);
      add_action('woocommerce_admin_order_totals_after_total', array($this, 'recuperar_respuesta_api'), 10, 1);

      // Hook para guardar campos en el panel de administración
      add_action('woocommerce_process_shop_order_meta', array($this, 'guardar_campos_facturacion_admin'), 10, 1);

      // Hook para enviar datos a la API cuando la orden es completada
      add_action('woocommerce_order_status_changed', array($this, 'accion_orden_pagada'), 10, 4);

      // Hook para validar campos de facturación
      add_action('woocommerce_checkout_process', [$this, 'validar_facturacion']);
   }


   /**
    * Campos personalizados para la facturación [Form Checkout WC]
    *
    * Este método añade campos personalizados al formulario de facturación de WooCommerce,
    * incluyendo opciones como RFC, razón social, régimen fiscal y uso de CFDI.
    *
    * @param array $fields {
    *     Array de campos de facturación predeterminados.
    *
    *     @type string $id      Identificador único del campo.
    *     @type string $label   Etiqueta visible del campo.
    *     @type string $type    Tipo de campo (checkbox, text, select, etc.).
    *     @type int    $maxlength (Opcional) Longitud máxima permitida en campos de texto.
    *     @type array  $class   Clases CSS aplicadas al campo.
    *     @type array  $options (Opcional) Opciones disponibles si el campo es un select.
    * }
    * @return array Campos de facturación modificados con los nuevos valores agregados.
    *
    * @since 1.0.0
    */
   public function agregar_campos_facturacion($fields)
   {
      foreach ($this->campos as $campo) {
         $field_args = array(
            'type' => $campo['type'],
            'label' => $campo['label'],
            'class' => $campo['class'] ?? "form-row-wide",
            'clear' => true,
            'priority' => $campo["priority"] ?? 120,
            'required' => $campo['required'] ?? false,
            'custom_attributes' => [
               'maxlength' => $campo['maxlength'] ?? '',

            ],
            'placeholder' => $campo['placeholder'] ?? ''
         );

         if ($campo['id'] === 'rfc_factura') {
            $field_args['validate'] = array('rfc');
            $field_args['custom_attributes']['pattern'] = '^([A-ZÑ&]{3,4})(\d{6})([A-Z\d]{3})$';
         }
         if ($campo['type'] === 'select' && isset($campo['options'])) {
            $field_args['options'] = $campo['options'];
         }

         $fields[$campo['group']][$campo['id']] = $field_args;
      }

      // Reubicación de email
      if (isset($fields['billing']['billing_email'])) {
         $fields['billing']['billing_email']['priority'] = 21;
      }
      // 10 digitos al teléfono
      if (isset($fields['billing']['billing_phone'])) {
         $fields['billing']['billing_phone']['custom_attributes']['maxlength'] = 10;
         $fields['billing']['billing_phone']['priority'] = 23;
      }

      return $fields;
   }

   public function script_campos_facturacion()
   {
      if (is_checkout()) {
?>
         <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
               const checkboxFacturar = document.querySelector("#deseas_facturar");

               // Lista de campos a ocultar/mostrar (exceptuando "Número Exterior" y "Número Interior")
               const camposFacturacion = [
                  <?php
                  foreach ($this->campos as $campo) {
                     if (!in_array($campo['id'], ['deseas_facturar', 'num_ext_factura', 'num_int_factura', 'num_ext_envio', 'num_int_envio'])) {
                        echo "'{$campo['id']}',";
                     }
                  }
                  ?>
               ];

               function toggleCamposFacturacion() {
                  const mostrarCampos = checkboxFacturar.checked;

                  camposFacturacion.forEach(id => {
                     const campo = document.querySelector(`#${id}_field`);
                     const input = document.querySelector(`#${id}`);

                     if (campo) {
                        campo.style.display = mostrarCampos ? "block" : "none";
                     }
                     if (input) {
                        if (mostrarCampos) {
                           input.setAttribute("required", "required");
                        } else {
                           input.removeAttribute("required");
                        }
                     }
                  });

                  // Asegurar que "Número Exterior" y "Número Interior" siempre estén visibles
                  ["num_ext_factura", "num_int_factura"].forEach(id => {
                     const campo = document.querySelector(`#${id}_field`);
                     if (campo) {
                        campo.style.display = "block"; // Siempre visible
                     }
                  });
               }

               // Ejecutar la función al cargar la página
               toggleCamposFacturacion();

               // Evento para detectar cambios en el checkbox
               checkboxFacturar.addEventListener("change", toggleCamposFacturacion);
            });
         </script>
      <?php
      }
   }

   /**
    * Validar campos de facturación
    * @return void
    */
   public function validar_facturacion()
   {
      if (array_key_exists('deseas_facturar', $_POST) && !empty($_POST['deseas_facturar'])) {
         foreach ($this->campos as $campo) {
            if ($campo['id'] === 'deseas_facturar') {
               continue;
            }
            // Ignorar num_int_factura, 'num_ext_envio', 'num_int_envio'
            if (empty($_POST[$campo['id']]) && !in_array($campo['id'], ['num_int_factura', 'num_ext_envio', 'num_int_envio'])) {
               wc_add_notice("El campo '{$campo['label']}' es obligatorio si deseas facturar.", 'error');
            }
         }
      }
      // Se añade la validación de num_ext_envio y num_int_envio si el checkbox ship_to_different_address fue checkeado
      if (array_key_exists('ship_to_different_address', $_POST) && !empty($_POST['ship_to_different_address'])) {
         foreach ($this->campos as $campo) {
            if (empty($_POST[$campo['id']]) && in_array($campo['id'], ['num_ext_envio'])) {
               wc_add_notice("El campo '{$campo['label']}' si deseas enviar a una dirección diferente", 'error');
            }
         }
      }
   }

   public function guardar_campos_facturacion($order_id)
   {
      $order = wc_get_order($order_id);

      $deseas_facturar = isset($_POST['deseas_facturar']) ? sanitize_text_field($_POST['deseas_facturar']) : '0';
      $envioDifDireccion = isset($_POST['ship_to_different_address']) ? true : false;

      foreach ($this->campos as $campo) {
         $campo_id = $campo['id'];
         $valor = isset($_POST[$campo_id]) ? sanitize_text_field($_POST[$campo_id]) : '';
         $meta_key = '_' . $campo['group'] . '_' . $campo_id;

         // Manejar campo deseas_facturar
         if ($campo_id === 'deseas_facturar') {
            $order->update_meta_data($meta_key, $deseas_facturar);
            continue;
         }

         // Validar campos requeridos para facturación
         $campos_no_requeridos = ['num_int_factura', 'num_ext_envio', 'num_int_envio'];
         if ($deseas_facturar === '1' && !in_array($campo_id, $campos_no_requeridos) && empty($valor)) {
            wc_add_notice(sprintf(__('El campo "%s" es requerido para facturación.', 'woocommerce'), $campo['label']), 'error');
            continue;
         }

         // Validar número exterior para envío diferente
         if ($campo_id === 'num_ext_envio' && $envioDifDireccion && empty($valor)) {
            wc_add_notice(sprintf(__('El campo "%s" es requerido para envío a una dirección diferente.', 'woocommerce'), $campo['label']), 'error');
            continue;
         }

         // Guardar el valor del campo
         $order->update_meta_data($meta_key, $valor);
      }
      $order->save();
   }
   public function recuperar_valor_campo($value, $input)
   {
      $customer_id = get_current_user_id();
      if ($customer_id === 0) {
         return $value;
      }
      foreach ($this->campos as $campo) {
         if ($input === $campo['id']) {
            $customer_orders = wc_get_orders(array(
               'customer_id' => $customer_id,
               'limit' => 1,
               'orderby' => 'date',
               'order' => 'DESC',
            ));

            if (!empty($customer_orders)) {
               $last_order = $customer_orders[0];
               $saved_value = $last_order->get_meta('_' . $campo['group'] . '_' . $campo['id']);

               if (!empty($saved_value)) {
                  return $saved_value;
               }
            }
         }
      }

      return $value;
   }

   public function agregar_campos_facturacion_editable($order, $group = 'billing')
   {
      ?>
      <style>
         #order_data .order_data_column .edit_address p {
            width: 100%;
            clear: both;
         }
      </style>
      <div class="address">
         <?php
         foreach ($this->campos as $campo) {
            if ($campo['group'] !== $group) continue; // Filtra los campos por grupo
            $valor = $order->get_meta('_' . $campo['group'] . '_' . $campo['id']);
         ?>
            <p>
               <strong><?php echo esc_html($campo['label']); ?></strong>
               <?php if ($campo['type'] === 'checkbox') : ?>
                  <span><?php echo $valor ? "Si" : "No"; ?></span>
               <?php else : ?>
                  <span><?php echo esc_attr($valor); ?></span>
               <?php endif; ?>
            </p>
         <?php
         }
         ?>
      </div>
      <div class="edit_address">
         <?php
         foreach ($this->campos as $campo) {
            if ($campo['group'] !== $group) continue; // Filtra los campos por grupo
            $valor = $order->get_meta('_' . $campo['group'] . '_' . $campo['id']);
         ?>
            <p class="form-field _<?php echo esc_attr($campo['group'] . '_' . $campo['id']); ?>_field">
               <label for="_<?php echo esc_attr($campo['group'] . '_' . $campo['id']); ?>"><?php echo esc_html($campo['label']); ?></label>
               <?php if ($campo['type'] === 'checkbox') : ?>
                  <input type="checkbox" class="checkbox" name="_<?php echo esc_attr($campo['group'] . '_' . $campo['id']); ?>" id="_<?php echo esc_attr($campo['group'] . '_' . $campo['id']); ?>" value="1" <?php checked($valor, '1'); ?>>
               <?php else : ?>
                  <input type="<?php echo esc_attr($campo['type']); ?>" class="short" name="_<?php echo esc_attr($campo['group'] . '_' . $campo['id']); ?>" id="_<?php echo esc_attr($campo['group'] . '_' . $campo['id']); ?>" value="<?php echo esc_attr($valor); ?>" placeholder="" <?php echo isset($campo['maxlength']) ? 'maxlength="' . esc_attr($campo['maxlength']) . '"' : ''; ?> <?php echo ($campo['id'] === 'rfc_factura') ? 'pattern="^([A-ZÑ&]{3,4})(\d{6})([A-Z\d]{3})$"' : ''; ?>>
               <?php endif; ?>
            </p>
         <?php
         }
         ?>
      </div>
   <?php
   }


   public function guardar_campos_facturacion_admin($order_id)
   {
      $order = wc_get_order($order_id);

      foreach ($this->campos as $campo) {
         if (isset($_POST['_' . $campo['group'] . '_' . $campo['id']])) {
            $order->update_meta_data('_' . $campo['group'] . '_' . $campo['id'], sanitize_text_field($_POST['_' . $campo['group'] . '_' . $campo['id']]));
         } elseif ($campo['type'] === 'checkbox') {
            $order->update_meta_data('_' . $campo['group'] . '_' . $campo['id'], '0');
         }
      }

      $order->save();
   }

   public function recuperar_respuesta_api($order_id)
   {
      $order = wc_get_order($order_id);
      $tarjeta = $order->get_meta('_mpgs_funding_method');

      $resApi = get_post_meta($order_id, 'facturacion_res_api', true);
      $reqApi = get_post_meta($order_id, 'facturacion_req_api', true);
   ?>
      <div style="margin-top: 30px; text-align:left;">
         <p>Pago con tarjeta: <?php echo $tarjeta; ?></p>
         <h2><b><?php _e('Petición API Facturación', 'textdomain'); ?></b></h2>
         <textarea style="background: #f2f2f2; border: 1px solid #cecece; width:100%;height:auto; min-height: 220px;color:#5a5a5a;padding:10px 25px;font-family:monospace;border:none" readonly disabled><?php echo esc_textarea($reqApi); ?></textarea>

      </div>
      <div style="margin-top: 30px; text-align:left;">
         <h2><b><?php _e('Respuesta API Facturación', 'textdomain'); ?></b></h2>
         <textarea style=" background: #f2f2f2; border: 1px solid #cecece; width:100%;height:auto; min-height: 80px;color:#5a5a5a;padding:10px 25px;font-family:monospace;border:none" readonly disabled><?php echo esc_textarea($resApi); ?></textarea>
      </div>
<?php
   }
   public function accion_orden_pagada($order_id, $old_status, $new_status, $order)
   {
      if ($order->is_paid()) {
         error_log("La orden #$order_id fue pagada. Estado actual: $new_status");
         $this->enviar_datos_a_api($order_id);
      }
   }

   public function enviar_datos_a_api($order_id)
   {
      $order = wc_get_order($order_id);
      // 4 pago con tarjeta crédito 28 pago con tarjeta débito
      $formPago = $order->get_meta('_mpgs_funding_method') === 'CREDIT' ? '4' : '28';

      $requiere_factura = $order->get_meta('_billing_deseas_facturar') === "1";
      $usar_shipping = (
         $order->get_shipping_address_1() !== $order->get_billing_address_1() ||
         $order->get_shipping_city() !== $order->get_billing_city() ||
         $order->get_shipping_postcode() !== $order->get_billing_postcode()
      );

      // Cadena de productos
      $cadenaProductos = $this->getCadenaProductos($order);

      $orderInfo = [
         "WebTraID"        => $order->get_id(),
         "WebPedNum"       => $order->get_id(),
         "SubTot"          => $order->get_subtotal(),
         "ImpTot"          => $order->get_total_tax(),
         "TotTot"          => $order->get_total(),

         "CveCte"          => "CTE-00236",
         "CveSuc"          => "wc-" . $order->get_user_id(),
         "NomCte"          => $order->get_billing_first_name(),
         "ApeCte"          => $order->get_billing_last_name(),
         "MaiCte"          => $order->get_billing_email(),
         "RfcCte"          => $order->get_meta('_billing_rfc_factura') ?: "NA",
         "ObsPed"          => $order->get_customer_note() ?: "NA",
         "DeptoPed"        => "wc-" . $order->get_user_id(),

         "CalCte"          => $order->get_billing_address_1(),
         "NumExtCte"       => $order->get_meta('_billing_num_ext_factura') ?: "NA",
         "NumIntCte"       => $order->get_meta('_billing_num_int_factura') ?: "NA",
         "ColCte"          => $order->get_billing_address_2() ?: "NA",
         "DelCte"          => $order->get_billing_address_2() ?: "NA",
         "CiuCte"          => $order->get_billing_city(),
         "EdoCte"          => isset($this->state_codes[$order->get_billing_state()]) ? $this->state_codes[$order->get_billing_state()] : '36',
         "CopCte"          => $order->get_billing_postcode(),
         "TelCte"          => $order->get_billing_phone(),
         "PaiCte"          => "MEXICO",

         "NomFac"     => $requiere_factura ? ($order->get_meta('_billing_razon_social_factura') ?: $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) : "NA",
         "RfcFac"     => $requiere_factura ? $order->get_meta('_billing_rfc_factura') : "NA",
         "CalFac"     => $requiere_factura ? ($order->get_meta('_billing_calle_factura') ?: $order->get_billing_address_1()) : "NA",
         "NumExtFac"  => $requiere_factura ? ($order->get_meta('_billing_num_ext_factura') ?: "NA") : "NA",
         "NumIntFac"  => $requiere_factura ? ($order->get_meta('_billing_num_int_factura') ?: "NA") : "NA",
         "ColFac"     => $requiere_factura ? ($order->get_meta('_billing_colonia_factura') ?: $order->get_billing_address_2()) : "NA",
         "DelFac"     => $requiere_factura ? ($order->get_meta('_billing_delegacion_factura') ?: $order->get_billing_address_2()) : "NA",
         "CiuFac"     => $requiere_factura ? ($order->get_meta('_billing_ciudad_factura') ?: $order->get_billing_city()) : "NA",
         "EdoFac"     => $requiere_factura ? (isset($this->state_codes[$order->get_billing_state()]) ? $this->state_codes[$order->get_billing_state()] : '36') : "NA",
         "CopFac"     => $requiere_factura ? ($order->get_meta('_billing_cp_factura') ?: $order->get_billing_postcode()) : "NA",
         "TelFac"     => $requiere_factura ? ($order->get_meta('_billing_telefono_factura') ?: $order->get_billing_phone()) : "NA",
         "PaiFac"     => "MEXICO",
         "RegFisFac"  => $requiere_factura ? ($order->get_meta('_billing_regimen_fiscal_factura') ?: 'NA') : "NA",
         "ForPagFac"  => $requiere_factura ? ($formPago ?: '28') : "NA",
         "UsoFac"     => $requiere_factura ? ($order->get_meta('_billing_uso_cfdi_factura') ?: 'NA') : "NA",

         // Datos de envío o facturación según corresponda
         "CalEnt"     => $usar_shipping ? $order->get_shipping_address_1() : $order->get_billing_address_1(),
         "NumExtEnt"  => $usar_shipping ? $order->get_meta('_shipping_num_ext_envio') : $order->get_meta('_billing_num_ext_factura'),
         "NumIntEnt"  => $usar_shipping ? $order->get_meta('_shipping_num_int_envio') : $order->get_meta('_billing_num_int_factura'),
         "ColEnt"     => $usar_shipping ? $order->get_shipping_address_2() : $order->get_billing_address_2(),
         "DelEnt"     => $usar_shipping ? $order->get_shipping_address_2() : $order->get_billing_address_2(),
         "CiuEnt"     => $usar_shipping ? $order->get_shipping_city() : $order->get_billing_city(),
         "EdoEnt"     => $usar_shipping
            ? (isset($this->state_codes[$order->get_shipping_state()]) ? $this->state_codes[$order->get_shipping_state()] : 'NA')
            : (isset($this->state_codes[$order->get_billing_state()]) ? $this->state_codes[$order->get_billing_state()] : 'NA'),
         "CopEnt"     => $usar_shipping ? $order->get_shipping_postcode() : $order->get_billing_postcode(),
         "TelEnt"     => $order->get_billing_phone(),
         "PaiEnt"     => "MEXICO",
         "NomEnt"     => $order->get_formatted_shipping_full_name()
      ];

      $requestData = array(
         'WEB'   => 'SI',
         'SysID' => 'vl',
         'mod'   => 'eComUPM',
         'Type'  => 'PedInsert',
      );

      $requestData['pedData'] = "\n";
      $requestData['pedData'] .= "WebTraSts\t" . $orderInfo["WebTraID"] . "\t" . $orderInfo["WebPedNum"] . "\t" . $orderInfo["SubTot"] . "\t" . $orderInfo["ImpTot"] . "\t" . $orderInfo["TotTot"] . "\tEND\n";
      $requestData['pedData'] .= $orderInfo["CveCte"] . "\t" . $orderInfo["CveSuc"] . "\t" . $orderInfo["NomCte"] . "\t" . $orderInfo["ApeCte"] . "\t" . $orderInfo["MaiCte"] . "\t" . $orderInfo["RfcCte"] . "\t" . $orderInfo["ObsPed"] . "\t" . $orderInfo["DeptoPed"] . "\tEND\n";
      $requestData['pedData'] .= $orderInfo["CalCte"] . "\t" . $orderInfo["NumExtCte"] . "\t" . $orderInfo["NumIntCte"] . "\t" . $orderInfo["ColCte"] . "\t" . $orderInfo["DelCte"] . "\t" . $orderInfo["CiuCte"] . "\t" . $orderInfo["EdoCte"] . "\t" . $orderInfo["CopCte"] . "\t" . $orderInfo["TelCte"] . "\t" . $orderInfo["PaiCte"] . "\tEND\n";
      $requestData['pedData'] .= $orderInfo["NomFac"] . "\t" . $orderInfo["RfcFac"] . "\t" . $orderInfo["CalFac"] . "\t" . $orderInfo["NumExtFac"] . "\t" . $orderInfo["NumIntFac"] . "\t" . $orderInfo["ColFac"] . "\t" . $orderInfo["DelFac"] . "\t" . $orderInfo["CiuFac"] . "\t" . $orderInfo["EdoFac"] . "\t" . $orderInfo["CopFac"] . "\t" . $orderInfo["TelFac"] . "\t" . $orderInfo["PaiFac"] . "\t" . $orderInfo["RegFisFac"] . "\t" . $orderInfo["ForPagFac"] . "\t" . $orderInfo["UsoFac"] . "\tEND\n";
      $requestData['pedData'] .= $orderInfo["CalEnt"] . "\t" . $orderInfo["NumExtEnt"] . "\t" . $orderInfo["NumIntEnt"] . "\t" . $orderInfo["ColEnt"] . "\t" . $orderInfo["DelEnt"] . "\t" . $orderInfo["CiuEnt"] . "\t" . $orderInfo["EdoEnt"] . "\t" . $orderInfo["CopEnt"] . "\t" . $orderInfo["TelEnt"] . "\t" . $orderInfo["PaiEnt"] . "\t" . $orderInfo["NomEnt"] . "\tEND\n";
      $requestData['pedData'] .= $cadenaProductos . "END\n";

      $response = $this->enviar_post_a_api($requestData);

      update_post_meta($order_id, 'facturacion_req_api', $requestData['pedData']);

      if (is_wp_error($response)) {
         error_log('Error al enviar datos a la API: ' . $response->get_error_message());
      } else {
         $resWS = wp_remote_retrieve_body($response);
         update_post_meta($order_id, 'facturacion_res_api', $resWS);
      }
   }


   /**
    * Crea una cadena separada por tabuladores con los productos de la orden
    * @param WC_Order $order
    * @return string
    * Ex:
    * CveArt	CanPed	DesPed	PrePed	ImpPed	END
    */
   public function getCadenaProductos($order)
   {
      // get totals of order
      $total = (float) $order->get_total();
      $totalTax = (float) $order->get_total_tax();

      foreach ($order->get_items() as $item_id => $item) {
         // Obtenemos el producto de cada línea
         $product = $item->get_product();

         // Obtener el porcentaje de impuesto del meta o usar 16% por defecto
         $tax_percentage = (float) get_post_meta($product->get_id(), '_impuesto_producto', true) ?: 16;
         $impuesto = $tax_percentage / 100;

         // Obtener precios con impuesto incluido y calcular el precio sin impuesto
         $regular_price_with_tax = (float) $product->get_regular_price();
         $regular_price = $regular_price_with_tax / (1 + $impuesto);

         // Si hay precio de oferta, también quitamos el impuesto
         $sale_price_with_tax = $product->get_sale_price() ? (float) $product->get_sale_price() : null;
         $sale_price = $sale_price_with_tax ? $sale_price_with_tax / (1 + $impuesto) : $regular_price;

         $total = (float) $item->get_total();
         $discount_percentage = ($regular_price - $sale_price) / $regular_price * 100;


         $productos[] = [
            $product->get_sku(),
            number_format($item->get_quantity(), 2),
            number_format($discount_percentage, 2),
            number_format($regular_price, 2),
            number_format($impuesto, 2),
         ];
      }
      $cadenaProducts = "";
      foreach ($productos as $producto) {
         $cadenaProducts .= implode("\t", $producto) . "\tEND\n";
      }

      error_log("Cadena de productos: \n" . $cadenaProducts);
      return $cadenaProducts;
   }

   private function enviar_post_a_api($datos)
   {
      $args = array(
         'method'    => 'POST',
         'body'      => $datos,
         'timeout'   => 45,
         'redirection' => 5,
         'blocking'  => true,
         'headers'   => array(
            'Content-Type' => 'application/x-www-form-urlencoded',
         ),
      );
      return wp_remote_post($this->api_url, $args);
   }
}


/**
 * Endpoint de pruebas
 * 
 * URL: https://tudominio.com/wp-json/hacktolito/v54/tests/123
 */
function tester_endpoint()
{
   register_rest_route('hacktolito/v54', '/tests/(?P<id>\d+)', [
      'methods'  => 'GET',
      'callback' => 'tester_callback',
      'permission_callback' => '__return_true', // Para no pedir atuhorización
   ]);
}

add_action('rest_api_init', 'tester_endpoint');

function tester_callback($request)
{
   global $campos;
   $id = $request['id'];
   $order = wc_get_order($id);

   $obj = new WoocommerceFacturacionLaNaval($campos);
   $cadenaProductos = $obj->getCadenaProductos($order);

   // Return text response
   return rest_ensure_response($cadenaProductos, ['headers' => ['Content-Type' => 'text/plain']]);
}

/**
 * Endpoint para obtener las clases de impuestos de un producto
 * 
 * URL: https://tudominio.com/wp-json/hacktolito/v54/taxes/(?P<id>\d+)
 */
function taxes_endpoint()
{
   register_rest_route('hacktolito/v54', '/taxes/(?P<id>\d+)', [
      'methods'  => 'GET',
      'callback' => 'taxes_callback',
      'permission_callback' => '__return_true', // Para no pedir atuhorización
   ]);
}

add_action('rest_api_init', 'taxes_endpoint');

function taxes_callback($request)
{
   $id = $request['id'];
   $product = wc_get_product($id);

   if (!$product) {
      return rest_ensure_response(['error' => 'Producto no encontrado'], 404);
   }

   $tax_class = $product->get_tax_class(); // Slug de la clase de impuesto

   // Obtener todas las clases, incluyendo 'Standard'
   $tax_classes = WC_Tax::get_tax_classes();
   $tax_classes = array_merge(['' => 'Standard'], array_combine($tax_classes, $tax_classes));

   // Preparar la respuesta legible
   $response = [
      'slug' => $tax_class,
      'label' => isset($tax_classes[$tax_class]) ? $tax_classes[$tax_class] : 'Desconocido',
      'tax_class' => $tax_class,
   ];

   return rest_ensure_response($response, 200);
}

/**
 * Endpoint para obtener el método de envío de un pedido
 * 
 * URL: https://tudominio.com/wp-json/hacktolito/v54/shipping-method/(?P<id>\d+)
 */
function shipping_method_endpoint() {
   register_rest_route('hacktolito/v54', '/shipping-method/(?P<id>\d+)', [
      'methods'  => 'GET',
      'callback' => 'shipping_method_callback',
      'permission_callback' => '__return_true',
   ]);
}

add_action('rest_api_init', 'shipping_method_endpoint');

function shipping_method_callback($request) {
   $order_id = $request['id'];
   $order = wc_get_order($order_id);

   if (!$order) {
      return rest_ensure_response(['error' => 'Pedido no encontrado'], 404);
   }

   // Obtener los métodos de envío del pedido
   $shipping_methods = $order->get_shipping_methods();
   $shipping_info = [];

   foreach ($shipping_methods as $shipping_method) {
      $shipping_info[] = [
         'method_id' => $shipping_method->get_method_id(),
         'instance_id' => $shipping_method->get_instance_id(),
         'name' => $shipping_method->get_name(),
         'method_title' => $shipping_method->get_method_title(),
         'total' => $shipping_method->get_total(),
         'taxes' => $shipping_method->get_taxes(),
         'meta_data' => $shipping_method->get_meta_data(),
      ];
   }

   return rest_ensure_response([
      'order_id' => $order_id,
      'shipping_methods' => $shipping_info
   ], 200);
}
