<?php
/**
 * Mensajes para campos requeridos (WooCommerce los muestra junto al input)
 * — Todos en <strong>...</strong>
 */
add_filter('woocommerce_checkout_required_field_notice', function ($notice, $field_label, $field_key) {
    $mensajes = [
        'billing_first_name' => '<strong>Por favor, ingresa tu nombre.</strong>',
        'billing_last_name'  => '<strong>Por favor, ingresa tus apellidos.</strong>',
        'billing_address_1'  => '<strong>Por favor, escribe tu dirección.</strong>',
        'billing_address_2'  => '<strong>Por favor, proporciona tu colonia.</strong>',
        'billing_city'       => '<strong>Por favor, indica tu ciudad.</strong>',
        'billing_postcode'   => '<strong>Por favor, proporciona tu código postal.</strong>',
        'billing_phone'      => '<strong>Por favor, introduce tu número de teléfono.</strong>',
        'billing_email'      => '<strong>Por favor, proporciona tu correo electrónico.</strong>',
    ];

    // Si no está en nuestra lista, envolver el notice original en <strong>
    return $mensajes[$field_key] ?? ('<strong>' . $notice . '</strong>');
}, 10, 3);

/**
 * Validaciones personalizadas (solo wc_add_notice), mensajes en <strong>...</strong>
 */
add_action('woocommerce_after_checkout_validation', function ($data) {
    if (empty($_POST)) {
        return;
    }

    $post = wc_clean($_POST);

    // helper para añadir notices en negritas
    $add = function (string $msg) {
        wc_add_notice('<strong>' . $msg . '</strong>', 'error');
    };

    // 1) No permitir números en nombre/apellidos
    if (!empty($post['billing_first_name']) && !preg_match('/^[\p{L}\s]+$/u', $post['billing_first_name'])) {
        $add('El campo nombre solo debe contener letras y espacios.');
    }
    if (!empty($post['billing_last_name']) && !preg_match('/^[\p{L}\s]+$/u', $post['billing_last_name'])) {
        $add('El campo apellidos solo debe contener letras y espacios.');
    }

    // 2) Validar caracteres en empresa/dirección/colonia/ciudad (tildes y ñ permitidas)
    $regexCampos = [
        'billing_company'   => ['label' => 'Nombre de la empresa',  'regex' => '/^[\p{L}0-9\s\.,\-&]*$/u'],
        'billing_address_1' => ['label' => 'Dirección de la calle',  'regex' => '/^[\p{L}0-9\s\.,\-]*$/u'],
        'billing_address_2' => ['label' => 'Colonia',                'regex' => '/^[\p{L}0-9\s\.,\-]*$/u'],
        'billing_city'      => ['label' => 'Ciudad',                 'regex' => '/^[\p{L}0-9\s\.,\-]*$/u'],
    ];
    foreach ($regexCampos as $key => $info) {
        if (!empty($post[$key]) && !preg_match($info['regex'], $post[$key])) {
            $add('El campo ' . $info['label'] . ' contiene caracteres no permitidos.');
        }
    }

    // 3) Código postal (solo dígitos 4–10)
    if (!empty($post['billing_postcode']) && !preg_match('/^\d{4,10}$/', $post['billing_postcode'])) {
        $add('El código postal debe contener solo números.');
    }

    // 4) Teléfono (solo dígitos 10–15)
    if (!empty($post['billing_phone']) && !preg_match('/^\d{10,15}$/', $post['billing_phone'])) {
        $add('El número de teléfono debe contener solo números (10 a 15 dígitos).');
    }

    // 5) Fecha y hora de recogida (pick-up)
    if (empty($post['pickup_date'])) {
        $add('Por favor, selecciona una fecha de recogida.');
    } else {
        // Validar contra min/max si los envías como hidden o por POST
        $min  = $post['pickup_date_min'] ?? ($_POST['pickup_date_min'] ?? null);
        $max  = $post['pickup_date_max'] ?? ($_POST['pickup_date_max'] ?? null);
        $date = $post['pickup_date'];

        if (($min && $date < $min) || ($max && $date > $max)) {
            $add('Por favor, elige una fecha dentro del rango permitido.');
        }
    }

    if (empty($post['pickup_time'])) {
        $add('Por favor, selecciona una hora de recogida.');
    }
}, 10, 1);