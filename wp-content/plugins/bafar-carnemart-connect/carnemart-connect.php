<?php

/**
 *
 * @link              https://sparklabs.com.mx/utilities/plugins/carnemart-connect
 * @since             1.0.0
 * @package           Bafar :: Replicas de pedidos
 *
 * @wordpress-plugin
 * Plugin Name:       Bafar :: Replicas de pedidos  📠 
 * Plugin URI:        https://sparklabs.com.mx/utilities/plugins/carnemart-connect
 * Description:       Utilización de API para replicar videos una vez que se envía el pedido al POS de las tiendas.
 * Version:           1.0.0
 * Author:            Jesús Cortés @ Sparklabs
 * Author URI:        https://sparklabs.com.mx
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('WPINC')) {
   die;
}

define('_CARNEMART_CONNECT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('_CARNEMART_CONNECT_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Usamos 
 * ReplicaPedido
 */

$pos_url = get_option('pos_url', '');

//define('_WSDL_CARNEMART_REPLICA_PEDIDO', 'https://pilot-serv.uxserv.com/Des/C4C/WS-BRHybris-CRM/ws-BRHybris-CRM2.asmx?WSDL');
//define('_WSDL_CARNEMART_REPLICA_PEDIDO', 'https://serv.uxserv.com/srv/WS-BRHybris-CRM%20Fase%20II/ws-BRHybris-CRM2.asmx');

if (WP_ENVIRONMENT_TYPE == "production") {
   define('_WSDL_CARNEMART_REPLICA_PEDIDO', "https://serv.uxserv.com/srv/WS-BRHybris-CRM%20Fase%20II/ws-BRHybris-CRM2.asmx?WSDL");
} else {
   define('_WSDL_CARNEMART_REPLICA_PEDIDO', $pos_url);
}
//define('_WSDL_CARNEMART_REPLICA_PEDIDO', 'https://serv.uxserv.com/srv/WS-BRHybris-CRM%20Fase%20II/ws-BRHybris-CRM2.asmx?WSDL');

include_once(_CARNEMART_CONNECT_PLUGIN_DIR . "classes/CarnemartSoap.php");
include_once(_CARNEMART_CONNECT_PLUGIN_DIR . "includes/functions.php");

/**
 * Hook para mostrar la respuesta del API Replicas en el admin
 */
add_action('woocommerce_admin_order_totals_after_total', 'bafar_carnemart_connect_show_api_replicas_response', 10, 1);

function bafar_carnemart_connect_show_api_replicas_response($order_id)
{
   $order = wc_get_order($order_id);
   // Mostramos request
   $request_encoded = $order->get_meta('replica_pos_request', true);
   if ($request_encoded) {
      $xml_request = base64_decode($request_encoded);
      $formatted_request = format_xml_string($xml_request);
      echo '<div class="carnemart-replicas-request">';
      echo '<h3><strong>Request de Réplica Carnemart (XML)</strong></h3>';
      echo '<textarea style="width: 100%; height: 300px; font-family: monospace; padding: 12px;border: none; background:rgb(221, 221, 221); resize: none; outline: none;cursor: default;" readonly onfocus="this.blur()">' . esc_textarea($formatted_request) . '</textarea>';
      echo '</div>';
   } else {
      error_log("No se encontró request de réplica para la orden $order_id");
   }

   $response_encoded = $order->get_meta('replica_pos', true);

   if ($response_encoded) {
      $xml_response = base64_decode($response_encoded);
      $formatted_xml = format_xml_string($xml_response);

      echo '<div class="carnemart-replicas-response">';
      echo '<h3><strong>Respuesta de Réplica Carnemart (XML)</strong></h3>';
      echo '<textarea style="width: 100%; height: 300px; font-family: monospace; padding: 12px;border: none; background:rgb(221, 221, 221); resize: none; outline: none;cursor: default;" readonly onfocus="this.blur()">' . esc_textarea($formatted_xml) . '</textarea>';
      echo '</div>';
   } else {
      error_log("No se encontró respuesta de réplica para la orden $order_id");
   }
}

function format_xml_string($xml)
{
   $dom = new DOMDocument();
   $dom->preserveWhiteSpace = false;
   $dom->formatOutput = true;

   if (@$dom->loadXML($xml)) {
      return $dom->saveXML();
   } else {
      return $xml;
   }
}
