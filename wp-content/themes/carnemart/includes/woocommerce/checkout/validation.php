<?php

// Agregar mensajes de error personalizados para campos obligatorios
add_filter('woocommerce_checkout_required_field_notice', function ($notice, $field_label, $field_key) {
    $mensajes = [
        'billing_first_name' => 'Por favor, ingresa tu <strong>nombre</strong>.',
        'billing_last_name' => 'Por favor, ingresa tus <strong>apellidos</strong>.',
        'billing_address_1' => 'Por favor, escribe tu <strong>dirección</strong>.',
        'billing_address_2' => 'Por favor, proporciona tu <strong>colonia</strong>.',
        'billing_city' => 'Por favor, indica tu <strong>ciudad</strong>.',
        'billing_postcode' => 'Por favor, proporciona tu <strong>código postal</strong>.',
        'billing_phone' => 'Por favor, introduce tu <strong>número de teléfono</strong>.',
        'billing_email' => 'Por favor, proporciona tu <strong>correo electrónico</strong>.',
    ];
    return $mensajes[$field_key] ?? $notice;
}, 10, 3);

// Validaciones personalizadas de campos
add_action('woocommerce_after_checkout_validation', 'custom_checkout_validations', 10, 2);

function custom_checkout_validations($data, $errors)
{
    if (!is_checkout()) return;

    $post = $_POST;

    // No permitir números en nombre/apellidos
    validate_no_numbers($post, $errors, [
        'billing_first_name' => 'nombre',
        'billing_last_name' => 'apellidos',
    ]);

    // Validar caracteres en ciertos campos, permitiendo tildes y ñ
    validate_regex($post, $errors, [
        'billing_company' => ['label' => 'Nombre de la empresa', 'regex' => '/^[\p{L}0-9\s\.,\-&]*$/u'],
        'billing_address_1' => ['label' => 'Dirección de la calle', 'regex' => '/^[\p{L}0-9\s\.,\-]*$/u'],
        'billing_address_2' => ['label' => 'Colonia', 'regex' => '/^[\p{L}0-9\s\.,\-]*$/u'],
        'billing_city' => ['label' => 'Ciudad', 'regex' => '/^[\p{L}0-9\s\.,\-]*$/u'],
    ]);

    // Código postal
    if (!empty($post['billing_postcode']) && !preg_match('/^\d{4,10}$/', $post['billing_postcode'])) {
        $errors->add('billing_postcode', 'El <strong>código postal</strong> debe contener solo números.');
    }

    // Teléfono
    if (!empty($post['billing_phone']) && !preg_match('/^\d{10,15}$/', $post['billing_phone'])) {
        $errors->add('billing_phone', 'El <strong>número de teléfono</strong> debe contener solo números.');
    }

    // Validar fecha y hora de recogida
    $chosen_method = WC()->session->get('chosen_shipping_methods')[0] ?? '';
    if (strpos($chosen_method, 'local_pickup:2') !== false) {
        if (empty($post['pickup_date'])) {
            $errors->add('pickup_date', 'Por favor, selecciona una <strong>fecha de recogida</strong>.');
        }
        if (empty($post['pickup_time'])) {
            $errors->add('pickup_time', 'Por favor, selecciona una <strong>hora de recogida</strong>.');
        }
    }
}

// No permitir números en campos de texto
function validate_no_numbers($post, $errors, $campos)
{
    foreach ($campos as $campo => $label) {
        if (!empty($post[$campo]) && !preg_match('/^[\p{L}\s]+$/u', $post[$campo])) {
            $errors->add($campo, "El campo <strong>$label</strong> solo debe contener letras y espacios.");
        }
    }
}

// Validar patrón de caracteres personalizados
function validate_regex($post, $errors, $campos)
{
    foreach ($campos as $campo => $info) {
        if (!empty($post[$campo]) && !preg_match($info['regex'], $post[$campo])) {
            $errors->add($campo, "El campo <strong>{$info['label']}</strong> contiene caracteres no permitidos.");
        }
    }
}