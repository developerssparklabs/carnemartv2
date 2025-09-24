<?php

/**
 * Añade un botón para vaciar el carrito en la página de carrito
 * @snippet       Vaciar carrito
 * @author        spark-jesus
 */

add_action('woocommerce_before_cart_table', 'vaciar_carrito_via_ajax', 21);

function vaciar_carrito_via_ajax()
{
   $styles = '
      <style>
         .vaciar-carrito-wrapper {
            display: flex;
            justify-content: end;
            margin-bottom: 20px;
         }
         .boton-vaciar-carrito {
            font-size:12px !important;
            font-weight: 600!important;
            background: red !important;
            color: #fff !important;
            padding: 5px 15px!important;
            text-decoration: none;
            border-radius:15px;
            cursor: pointer;
            transition: all .3s ease-in-out;
         }
      </style>
   ';
   echo $styles;
   echo '<div class="vaciar-carrito-wrapper"><a role="button" class="boton-vaciar-carrito empty-button">Vaciar carrito</a></div>';

   wc_enqueue_js("
      $('.empty-button').click(function(e){
         e.preventDefault();
            $.post( '" . '/wp-admin/admin-ajax.php' . "', { action: 'empty_cart' }, function() {
            location.reload();
         });
        });
   ");
}

add_action('wp_ajax_empty_cart', 'carnemart_empty_cart');
add_action('wp_ajax_nopriv_empty_cart', 'carnemart_empty_cart');

function carnemart_empty_cart()
{
   WC()->cart->empty_cart();
}

/**
 * Borrar carrito a las 2 hrs si el usuario no ha comprado
 * @snippet       Borrar carrito automáticamente
 * @author        spark-jesus
 */
// Indicará a wc 1 min antes para renovar la sesión en caso de interacción del usuario
add_filter('wc_session_expiring', function () {
   return 60 * 60 * 2 - 60; // 2 horas - 1 minuto (7140 segundos)
   // return 60 * 2 - 60; // 1 min - 1 min (60 segundos)
});

// Define cuándo la sesión expira completamente
add_filter('wc_session_expiration', function () {

   return 60 * 60 * 2; // 2 horas (7200 segundos)
   // return 60 * 2; // 5 min (300 segundos)
});

/**
 * Vaciar carrito después de generar el pedido
 * A causa de que el carrito no se vacía una vez terminando el pedido se forza el empty cart y session set cart como array vacío
 * @snippet       Vaciar carrito después de generar el pedido
 * @author        spark-jesus
 */
add_action('woocommerce_thankyou', 'vaciar_carrito_despues_de_orgen_generada', 20);
add_action('woocommerce_checkout_order_processed', 'vaciar_carrito_despues_de_orgen_generada', 20);

function vaciar_carrito_despues_de_orgen_generada($order_id) {
    if (is_user_logged_in()) {
        WC()->cart->empty_cart();
    } else {
        WC()->session->set('cart', []);
    }
}