<?php

class CarnemartSoap
{
   private $wsdl_url;
   private $soapClient;

   public function __construct($wsdl_url, $options = array())
   {
      $this->wsdl_url = $wsdl_url;
   }

   public function enviarPedido($pedidoData, $personasImplicadas, $productos, $cupones)
   {

      $orden = wc_get_order($pedidoData['ID']);

      if ($orden->get_meta('replica_procesada') == 1) {
         $nota = sprintf(
            __("El pedido ya fue replicado a POS el %s.", "woocommerce"),
            current_time('Y-m-d H:i:s')
         );
         $orden->add_order_note($nota);
         $orden->save();
         return true;
      }

      // Construir la solicitud SOAP
      $soapRequest = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
      $soapRequest .= '<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">' . "\n";
      $soapRequest .= '    <soap12:Body>' . "\n";
      $soapRequest .= '        <ReplicaPedido xmlns="http://mcsa.mx/HybrisCRM/WS-BRHybris-CRM/">' . "\n";
      $soapRequest .= '            <DatosGenerales>' . "\n";
      foreach ($pedidoData as $key => $value) {
         $soapRequest .= '                <' . $key . '>' . $value . '</' . $key . '>' . "\n";
      }
      $soapRequest .= '            </DatosGenerales>' . "\n";
      $soapRequest .= '            <Cupones>' . "\n";
      $soapRequest .= '                <NumeroCupon>' . "\n";
      foreach ($cupones as $cupon) {
         $soapRequest .= '                    <string>' . $cupon . '</string>' . "\n";
      }
      $soapRequest .= '                </NumeroCupon>' . "\n";
      $soapRequest .= '            </Cupones>' . "\n";
      $soapRequest .= '            <PersonasImplicadas>' . "\n";
      foreach ($personasImplicadas as $persona) {
         $soapRequest .= '                <brPersonasImplicadas>' . "\n";
         foreach ($persona as $key => $value) {
            $soapRequest .= '                    <' . $key . '>' . $value . '</' . $key . '>' . "\n";
         }
         $soapRequest .= '                </brPersonasImplicadas>' . "\n";
      }
      $soapRequest .= '            </PersonasImplicadas>' . "\n";
      $soapRequest .= '            <Productos>' . "\n";
      foreach ($productos as $producto) {
         $soapRequest .= '                <brProductos>' . "\n";
         foreach ($producto as $key => $value) {
            $soapRequest .= '                    <' . $key . '>' . $value . '</' . $key . '>' . "\n";
         }
         $soapRequest .= '                </brProductos>' . "\n";
      }
      $soapRequest .= '            </Productos>' . "\n";
      $soapRequest .= '        </ReplicaPedido>' . "\n";
      $soapRequest .= '    </soap12:Body>' . "\n";
      $soapRequest .= '</soap12:Envelope>' . "\n";


      /*
         Log para el estado de Woo
      */
      $logger = new WC_Logger();
      $logger->info("Envío de pedido:  $soapRequest", ["source" => "API Replicas",]);

      // Guardamos la request en meta data de la orden
      if ($orden) {
         $orden->update_meta_data('replica_pos_request', base64_encode($soapRequest));
         $orden->save();
         error_log("Orden Meta guardado " . $pedidoData['ID']);
      }

      $options = array(
         'stream_context' => stream_context_create(array(
            'http' => array(
               'header' => 'Content-Type: application/soap+xml; charset=utf-8',
            )
         )),
         'soap_version' => SOAP_1_2,
      );
      $this->soapClient = new SoapClient($this->wsdl_url, $options);
      $response = $this->soapClient->__doRequest($soapRequest, $this->wsdl_url, 'ReplicaPedido', SOAP_1_2);
      $respuesta_procesada = $this->procesar_respuesta_soap($response);

      error_log(print_r($respuesta_procesada, true));



      if ($orden) {

         $orden->update_meta_data('replica_pos', base64_encode($response));

         error_log("Orden Meta guardado " . $pedidoData['ID']);

         $nota_respuesta = sprintf(
            __("Respuesta POS [%s]: %s", "woocommerce"),
            $respuesta_procesada['IdMensaje'],
            $respuesta_procesada['Descripcion']
         );
         $orden->add_order_note($nota_respuesta);

         if ($respuesta_procesada['Descripcion'] === "Error al replicar Pedido, sp: MC_HC_AGREGA_PEDIDO") {
            $orden->update_status('on-hold', __('Error en réplica de POS, contactar a sistemas | ', 'woocommerce'));
            error_log("Orden {$pedidoData['ID']} en on-hold por error MC_HC_AGREGA_PEDIDO");
         } else {
            if ($respuesta_procesada['Descripcion'] === "Pedido replicado") {
               $orden->update_meta_data('replica_procesada', 1);
            }
         }
         $orden->save();
      }
      $logger->info("Respuesta de pedido:      $response", ["source" => "API Replicas",]);
      return $response;
   }
   /**
    * Procesa la respuesta XML del SOAP y extrae IdMensaje y Descripción
    * 
    * @param string $xml_response Respuesta XML del servicio SOAP
    * @return array Array con los datos extraídos
    */
   public function procesar_respuesta_soap($xml_response)
   {
      $resultado = [
         'IdMensaje' => '',
         'Descripcion' => '',
         'raw_xml' => $xml_response // Opcional: guardar el XML original
      ];

      try {
         $xml = simplexml_load_string($xml_response);

         if ($xml === false) {
            throw new Exception("No se pudo parsear el XML");
         }

         $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
         $xml->registerXPathNamespace('hybris', 'http://mcsa.mx/HybrisCRM/WS-BRHybris-CRM/');

         $id_mensaje = $xml->xpath('//hybris:IdMensaje');
         $descripcion = $xml->xpath('//hybris:Descripcion');

         if (!empty($id_mensaje)) {
            $resultado['IdMensaje'] = (string)$id_mensaje[0];
         }

         if (!empty($descripcion)) {
            $resultado['Descripcion'] = (string)$descripcion[0];
         }
      } catch (Exception $e) {
         error_log("Error procesando respuesta SOAP: " . $e->getMessage());
         $resultado['Descripcion'] = 'Error al procesar respuesta: ' . $e->getMessage();
      }

      return $resultado;
   }
}
