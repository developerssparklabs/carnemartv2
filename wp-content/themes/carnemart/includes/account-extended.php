<?php

/**
 * Deshabilita que el cliente pueda cambiar su correo en my account
 * 
 * @return void
 * @author spark-jesus
 * 
 */

add_filter('pre_user_email', function ($email) {

    if (isset($_POST['account_email'])) {
        $current_user = wp_get_current_user();

        if ($_POST['account_email'] !== $current_user->user_email) {
            error_log('Correo modificado: ' . $_POST['account_email']);
            $message = sprintf(
                __('Para poder cambiar tu correo debes ponerte en contacto con nosotros a través de <a href="%s" target="_blank">WhatsApp</a>', 'woocommerce'),
                'https://wa.me/5216141296248'
            );
            if (!wc_has_notice($message, 'error')) {
                wc_add_notice($message, 'error');
            }
            return $current_user->user_email;
        }
    }

    return $email;
}, 10, 1);

/**
 * Giro Negocio, Requerido cuando tipo de cuenta es business
 * 
 */
function enqueue_mi_cuenta_scripts()
{
    if (is_account_page()) {
        $script_path = get_stylesheet_directory() . '/assets/js/mi-cuenta/mi-cuenta.js';
        $script_version = file_exists($script_path) ? filemtime($script_path) : null;

        wp_enqueue_script(
            'myaccount-script',
            get_stylesheet_directory_uri() . '/assets/js/mi-cuenta/mi-cuenta.js',
            array('jquery'),
            $script_version,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_mi_cuenta_scripts');

/**
 * Endpoints para my-account wc
 */
add_action('init', 'add_wc_account_endpoints');

function add_wc_account_endpoints()
{
    add_rewrite_endpoint('mis-puntos', EP_ROOT | EP_PAGES | EP_PERMALINK);
}

add_action('woocommerce_account_mis-puntos_endpoint', 'mis_puntos_endpoint_content');
function mis_puntos_endpoint_content()
{
    echo '<h3>' . __('Mis Puntos', 'woocommerce') . '</h3>';

    wc_get_template('myaccount/mis-puntos.php', [
        'puntos' => []
    ]);
}


add_filter('woocommerce_account_menu_items', 'custom_menus_wc');
function custom_menus_wc($items)
{
    $new_items = [];
    foreach ($items as $key => $item) {
        $new_items[$key] = $item;
        if ($key === 'orders') {
            $new_items['mis-puntos'] = __('Mis Puntos', 'woocommerce');
        }
    }
    return $new_items;
}

/**
 * Necesario para que los endpoints funcionen
 */
add_action('init', function () {
    add_wc_account_endpoints();
    flush_rewrite_rules();
});
