<?php

function agregar_script_woocommerce_error()
{
    // Asegurarnos de que WooCommerce esté activo
    if (class_exists('WooCommerce')) {
        // Registrar el script con un handle único
        wp_register_script(
            'custom-woocommerce-error-script', // Handle del script
            '', // Ruta del archivo JS (en este caso vacío porque usamos inline)
            array('jquery'), // Dependencia de jQuery
            '1.0', // Versión del script
            true // Cargar en el footer
        );

        // Agregar código inline al script registrado
        $custom_script = "
        jQuery(document).ready(function($) {
            // Añadir el botón al elemento .woocommerce-error
            $('.woocommerce-error').each(function() {
                // Verificar si el botón ya no existe para evitar duplicados
                if (!$(this).find('.cta-close-msg-woo').length) {
                    $(this).append('<button class=\"cta-close-msg-woo\"><i class=\"bi bi-x-circle-fill\"></i></button>');
                }
            });

            // Añadir evento de clic al botón
            $(document).on('click', '.cta-close-msg-woo', function() {
                // Seleccionar el contenedor padre y cerrarlo con fadeOut
                $(this).closest('.woocommerce-error').fadeOut(1000, function() {
                    $(this).remove(); // Opcional: Eliminarlo del DOM después del fadeOut
                });
            });
        });
        ";

        // Agregar el script inline al handle registrado
        wp_add_inline_script('custom-woocommerce-error-script', $custom_script);

        // Encolar el script en el frontend
        wp_enqueue_script('custom-woocommerce-error-script');
    }
}
add_action('wp_enqueue_scripts', 'agregar_script_woocommerce_error');
