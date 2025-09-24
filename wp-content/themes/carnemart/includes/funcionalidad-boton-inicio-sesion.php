<?php
// Mostrar el usuario en el header con ACF

if (!function_exists('mi_shortcode_iniciar_sesion_o_mostrar_menu')) {
    function mi_shortcode_iniciar_sesion_o_mostrar_menu()
    {
        // Obtener la URL desde el campo ACF
        $url_my_account = get_field('url_my_account', 'option'); // Usar 'option' para la página de opciones global
        if (!$url_my_account) {
            $url_my_account = '/mi-account/'; // URL predeterminada si no se ha configurado en ACF
        }

        // Verificar si el usuario está logueado
        if (is_user_logged_in()) {
            // Obtener los detalles del usuario
            $usuario = wp_get_current_user();
            $nombre_usuario = $usuario->display_name;

            // Mostrar contenido si el usuario está logueado
            return '<span class="enlace-inicio-sesion"><i class="bi bi-person-circle"></i> Bienvenido <a href="' . esc_url($url_my_account) . '" class="enlacemyAccount" aria-label="Mi cuenta">' . esc_html($nombre_usuario) . '!</a>, <a href="' . esc_url(wp_logout_url(home_url())) . '" class="enlaceOut">Salir</a></span>';
        } else {
            // Mostrar formulario de inicio de sesión si no está logueado
            return '<a href="' . esc_url($url_my_account) . '" class="enlace-inicio-sesion" aria-label="Iniciar sesión"><i class="bi bi-person-circle"></i> <span class="txt-inicio-sesion">Iniciar sesión</span></a>';
        }
    }
    // Registrar el shortcode
    add_shortcode('iniciar_sesion_o_mostrar_menu', 'mi_shortcode_iniciar_sesion_o_mostrar_menu');
    add_shortcode('iniciar_sesion_o_mostrar_menu', 'mi_shortcode_iniciar_sesion_o_mostrar_menu');


    //Quitar barra de administración para usuarios de wcommerce
    add_action('after_setup_theme', function () {
        if (!current_user_can('manage_options')) {
            add_filter('show_admin_bar', '__return_false'); // Ocultar la barra en el frontend
            remove_action('wp_footer', 'wp_admin_bar_render', 1000); // Evita que se cargue en el footer
            remove_action('wp_body_open', 'wp_admin_bar_render', 1000); // Evita que se cargue en el body
            remove_action('template_redirect', 'wp_admin_bar_render', 1000); // Evita que se renderice
        }
    });

    add_action('wp_footer', function () {
        if (!current_user_can('manage_options')) {
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.remove('admin-bar'); // Remueve la clase que genera el margen
                let header = document.querySelector('header.site__header');
                if (header) {
                    header.style.top = '0px'; // Ajusta el header si tiene desplazamiento
                }
                document.documentElement.style.marginTop = '0px'; // Elimina cualquier margen en el HTML
            });
        </script>";
        }
    });
}