(function ($) {
    "use strict";



    
    async function geocodeAddress(address) {
        const geocoder = new google.maps.Geocoder();
        var coordinates;
        await geocoder.geocode({
            'address': address
        }, function (results, status) {
            if (status === 'OK') {
                const latLng = results[0].geometry.location;
                coordinates = {
                    lat: latLng.lat(),
                    lng: latLng.lng()
                };
            } else {
                console.error('Geocode was not successful for the following reason: ' + status);
            }
        });
        return coordinates;
    }


    async function distance_calculation() {

        return 0;
        const {
            select
        } = wp.data;
        const store = select('wc/store/cart');

        var customerData = store.getCustomerData();
        var checkoutData = customerData.billingAddress;


        var street_no = checkoutData.address_1;
        var city = checkoutData.city;
        var state = checkoutData.state;
        var postcode = checkoutData.postcode;
        var country = checkoutData.country;

        var pickupLocation = `${street_no}, ${city}, ${state}, ${postcode} ${country}`;
        var pickupLocation = await geocodeAddress(pickupLocation);

        if (!checkoutData || !checkoutData.address_1 || !checkoutData.city || !checkoutData.state || !checkoutData.postcode || !checkoutData.country) {
            console.error("Error: Algunos campos obligatorios están vacíos o no están definidos.");
            return; // Salir de la función si alguna variable es inválida
        }

        if (!pickupLocation) {
            return;
        }
        var dropoffLocation = uber_delivery.dropoff_location;
        dropoffLocation = await geocodeAddress(dropoffLocation);
        if (!dropoffLocation) {
            return;
        }
        const origin = new google.maps.LatLng(pickupLocation.lat, pickupLocation.lng);
        const destination = new google.maps.LatLng(dropoffLocation.lat, dropoffLocation.lng);
        const service = new google.maps.DistanceMatrixService();
        //if (jQuery('.distance_measurement_warning').length > 0) {
        //jQuery('.distance_measurement_warning').remove();
        // }
        service.getDistanceMatrix({
            origins: [origin],
            destinations: [destination],
            travelMode: 'DRIVING', // or WALKING, BICYCLING, TRANSIT
        },
            function (response, status) {
                if (status === 'OK') {
                    const results = response.rows[0].elements;
                    //console.log(results[0].distance.value);
                    if (results[0].status === 'ZERO_RESULTS') {
                        // 						jQuery('.deliveries-integration').hide();
                    } else {
                        if (results[0].distance.value > 8000) {
                            jQuery('.deliveries-integration').hide();
                            if (jQuery('.distance_measurement_warning').length == 0) {
                                var warningHTML = `<div class="wc-block-components-notice-banner is-warning distance_measurement_warning"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path></svg><div class="wc-block-components-notice-banner__content">No realizamos entregas en esta área a través de Uber o Rappi, ya que excede los 8 KM de distancia de la tienda más cercana.</div></div>`;
                                if (jQuery('#shipping-fields').length > 0) {
                                    jQuery(warningHTML).insertBefore('#shipping-fields');
                                } else {
                                    jQuery(warningHTML).insertBefore('#billing-fields');
                                }
                                flush_cookie();
                                update_cart();
                            }
                            // 							jQuery('.cart-blocks').hide();
                        } else {
                            // 							jQuery('.cart-blocks').show();
                            jQuery('.deliveries-integration').show();
                        }
                    }
                } else {
                    console.error('Error calculating distance:', status);
                }
            }
        );
    }


})(jQuery);



jQuery(document).ready(function () {


    function getCookie(name) {
        // 	  let name = "ri_warning_message=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    //calcula la distancia entre la dirección de entrega y la dirección de la tienda con Uber
    function request_deliveries_quotes() {
        //  if (document.querySelector('.wc-block-checkout__shipping-method-option--selected')) return;

        // Verifica si la URL contiene '/checkout'
        if (window.location.href.includes('/checkout')) {

            try {
                const {
                    select
                } = wp.data;
                const stores = select('wc/store/cart');
                // Verifica que 'stores' y 'getCustomerData' existan antes de intentar acceder
                if (stores && typeof stores.getCustomerData === 'function') {
                    const customerData = stores.getCustomerData();
                    if (customerData && customerData.billingAddress) {
                        var data = {
                            action: 'uber_delivery_fee_calculation',
                            addressData: customerData.billingAddress,
                            nonce: uber_delivery.ajax_nonce
                        };
                        jQuery.post(uber_delivery.ajaxurl, data, function (resp) {
                           
                            if (resp.data.fee >= 0) {
                                //var total = "$" + (resp.data.fee / 100);
                                //  jQuery('.wc-block-components-totals-shipping .wc-block-components-totals-item__value').html(total);
                               
                                togglePlaceOrderButton(true);

                            } else {
                          
                                togglePlaceOrderButton(false);
                                

                            }

                        }).fail(function (error) {
                            console.error('Error in AJAX request:', error);
                        });

                    } else {
                        console.error('Customer data or billing address is not available.');
                    }
                } else {
                    console.error("'stores' or 'getCustomerData' is undefined or not a function.");
                }
            } catch (error) {
                console.error('An error occurred:', error);
            }
        } else {
            //console.log("Current page is not the checkout page. Script won't execute.");
        }
    }



    /*
    * activa desactiva el pedido
    */
        function togglePlaceOrderButton(isEnabled) {
            const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
           
            if (placeOrderButton) {
                if (isEnabled) {
                    // Habilitar el botón
                    placeOrderButton.removeAttribute('disabled');
                    placeOrderButton.style.opacity = '1';
        
                    // Eliminar el mensaje de error si existe
                    const errorMessage = placeOrderButton.parentNode.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                    update_cart();
                } else {
                    jQuery('.wc-block-components-totals-shipping .wc-block-components-totals-item__value').html("Cobertura no disponible");
                    // Seleccionar el botón "Realizar el pedido"
                    // Verificar si no hay métodos de envío disponibles

                    // Deshabilitar el botón
                    placeOrderButton.setAttribute('disabled', 'disabled');
                    placeOrderButton.style.opacity = '0.5';
        
                    // Agregar el mensaje de error si no existe
                    if (!placeOrderButton.parentNode.querySelector('.error-message')) {
                        const errorMessage = document.createElement('p');
                        errorMessage.className = 'error-message'; // Asignar una clase para identificarlo
                        errorMessage.style.color = 'red';
                        errorMessage.textContent = 'No hay cobertura de envío disponible. Por favor, revisa tu dirección.';
                        placeOrderButton.parentNode.insertBefore(errorMessage, placeOrderButton);
                    }
                }
            }
        }
        


    jQuery(document).on('click', '.uber-delivery', function (e) {
        

        const validationResult = validateBillingFields();
        if (!validationResult.success) {
            //alert(validationResult.errors.join('\n')); // Mostrar errores al usuario
           
            return 0;
        }

        e.preventDefault();
        flush_cookie();
        if (jQuery(this).hasClass('active')) {
            jQuery('.deliveries-integration').find('button').removeClass('active');
        } else {
            jQuery('.deliveries-integration').find('button').removeClass('active');
            jQuery(this).addClass('active');
            request_deliveries_quotes();
            //jQuery(".uber-delivery").click(); // Simula clic en el botón de Ube
        }

        setTimeout(function () {
           // wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
        }, 3000);

    });

    jQuery(document).on('change blur', '#shipping-postcode, #shipping-address_1, #shipping-city, #shipping-state', function () {

        // Validar que todos los campos necesarios estén completos
        const shippingAddress1 = jQuery('#shipping-address_1').val();
        const shippingCity = jQuery('#shipping-city').val();
        const shippingPostcode = jQuery('#shipping-postcode').val();
    
        if (!shippingAddress1 || !shippingCity || !shippingPostcode) {
            //alert("Por favor, complete todos los campos de dirección, ciudad y código postal para calcular el costo de envío.");
            return; // Salir de la función si falta algún campo
        }
    
        // Suspender la función si el ID contiene "billing"
        if (jQuery(this).attr('id') && jQuery(this).attr('id').includes('billing')) {
            //console.log("Validación suspendida porque el input pertenece a 'billing'");
            return; // Salir de la función
        }
    
        // Validar los campos de facturación
        const validationResult = validateBillingFields();
        if (!validationResult.success) {
            //alert(validationResult.errors.join('\n')); // Mostrar errores al usuario
            return; // Suspender la función
        }
    
        // Continuar con la lógica normal
        if ('date' === jQuery(this).attr('type') || !jQuery(this).attr('type')) {
            //console.log('test');
            return;
        } else {
           // request_deliveries_quotes();
            // distance_calculation(); // Si esta función es necesaria, descomentarla
            jQuery(".uber-delivery").click(); // Simula clic en el botón de Uber
        }
    });
    

    document.cookie = "ri_warning_message=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    if (jQuery('.wp-block-woocommerce-checkout').length > 0) {
        setInterval(function () {
            if (getCookie('ri_warning_message=') != '') {
                jQuery('.wc-block-components-notice-banner__content').find('div').html(getCookie('ri_warning_message='));
            }
        }, 1000);
    }

    //keikos
    setTimeout(function () {
        //flush_cookie();
        // add_delivery_btns(jQuery('.wc-block-checkout__shipping-method-option--selected').find('span').text());
        //request_deliveries_quotes();
        //update_cart();
    }, 3000);
});



// Función para inicializar los listeners cuando los elementos estén listos
function initRadioClickListener() {

    // Verificar que estamos en la página de checkout
    if (window.location.href.includes('/checkout')) {
        // Observador para detectar cambios en el DOM
        const observer = new MutationObserver(() => {
            // Seleccionar todos los radio buttons
            const radioButtons = document.querySelectorAll('div[role="radio"]');

            if (radioButtons.length > 0) {
                // Agregar listeners a cada radio button
                radioButtons.forEach((radioButton) => {
                    if (!radioButton.hasAttribute('data-listener-added')) { // Prevenir listeners duplicados
                        radioButton.addEventListener('click', () => {

                            //console.log('Se ha hecho clic en un radio button.');
                            const titleElement = radioButton.querySelector('.wc-block-checkout__shipping-method-option-title');
                            if (titleElement) {
                                if (titleElement.textContent.includes('Recoger en tienda')) {
                                    const placeOrderButton = document.querySelector('.wc-block-components-checkout-place-order-button');
                                    // Habilitar el botón y restaurar su opacidad
                                    //placeOrderButton.removeAttribute('disabled');
                                    placeOrderButton.style.opacity = '1';

                                    // Verificar si el mensaje existe y eliminarlo
                                    const errorMessage = placeOrderButton.parentNode.querySelector('.error-message');
                                    if (errorMessage) {
                                        errorMessage.remove(); // Eliminar el mensaje del DOM
                                    }

                                    // Ocultar la integración de entregas
                                    jQuery('.deliveries-integration').hide();
                                    // Comprobar si el botón "uber-delivery" existe en el DOM
                                    const uberDeliveryButton = document.querySelector('.uber-delivery');
                                    if (uberDeliveryButton) {
                                        if (uberDeliveryButton.classList.contains('active')) {
                                            uberDeliveryButton.click();
                                            uberDeliveryButton.blur();
                                           // console.log('El botón "uber-delivery" tenía la clase "active". Foco quitado.');
                                        } else {
                                           // console.log('El botón "uber-delivery" no tiene la clase "active". No se realiza ninguna acción.');
                                        }


                                    } else {
                                      //  console.log('El botón "uber-delivery" no existe.');
                                    }
                                } else {
                                    // Mostrar alerta con el texto del título
                                    //console.log(`Has seleccionado: ${titleElement.textContent}`);
                                    add_delivery_btns(titleElement.textContent);
                                }
                            } else {
                                alert('No se pudo obtener el valor del radio seleccionado.');
                            }
                        });

                        // Marcar el radio button como ya procesado
                        radioButton.setAttribute('data-listener-added', 'true');
                    }



                    // Seleccionar todos los elementos que actúan como opciones de radio
                    const radioElements = document.querySelectorAll('.wc-block-checkout__shipping-method-option');

                    // Buscar el elemento que contiene el texto "Recoger en tienda"
                    const targetRadio = Array.from(radioElements).find(element =>
                        element.textContent.trim().includes('Recoger en tienda')
                    );

                    // Si el elemento existe, simular un clic
                    if (targetRadio) {
                        targetRadio.click();
                    }
                });

                // Una vez asignados los listeners, detener el observador
                observer.disconnect();
            }
        });

        // Configuramos el observador para monitorear el cuerpo del documento
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    } else {
       // console.log('No estamos en la página de checkout.');
    }
}

// Inicializar la función cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', initRadioClickListener);



function add_delivery_btns(btnText) {
    if (btnText == 'Recoger en tienda') {
        //jQuery('.distance_measurement_warning').hide();
        jQuery('.uber-delivery').hide();
        jQuery('.deliveries-integration').hide();
        flush_cookie();
        update_cart();
        return;
    }

    if (btnText == 'A domicilio' || getCookie('delivery_by=')) {
       
        //muestra el div donde se integran los botones de entrega
        //jQuery('.distance_measurement_warning').show();
        //si ya existe el div de integración de botones de entrega, se elimina
        if (jQuery('.deliveries-integration').length > 0) {
            jQuery('.deliveries-integration').remove();
        }

        var active_rappi = '';
        var active_uber = '';
        var rappi_btn = '';

        // si previamente se seleccionó un método de entrega, se activa el botón correspondiente
        if (getCookie('delivery_by=') == 'uber') {
            active_uber = 'active';
            active_rappi = '';
            jQuery(".uber-delivery").click(); // Simula clic en el botón de Ube
        } else if (getCookie('delivery_by=') == 'rappi') {
            active_rappi = 'active';
            active_uber = '';
            jQuery(".rappi-delivery").click(); i
        } else {
            flush_cookie();
            jQuery('.wc-block-components-totals-shipping .wc-block-components-totals-item__value').html("Seleccione metodo de envio");
        }

        //si está activo el método de entrega de Rappi, se muestra el botón
        if (uber_delivery.is_rappi_active) {
            rappi_btn = `<button class="wc-block-components-button rappie-delivery ${active_rappi}" style="width:49%; border: 1px solid #ccc; font-weight:700; background:inherit;">Rappi</button>`;
        }

        //muestra los botones de entrega
        var btnHTML = `<div class="wc-block-components-checkout-step__container deliveries-integration" style="margin-top:15px;"><h3 style="font-size:16px; color:var(--e-global-color-primary); font-weight:600;">Selecciona tu servicio de entrega preferido</h3>
                  ${rappi_btn}
                  <button class="wc-block-components-button uber-delivery ${active_uber}" style="width:49%; border: 1px solid #ccc; font-weight:700; margin-left:9px; background:inherit;">Uber</button>
                  </div>`;


        //se inserta el div de integración de botones de entrega
        if (btnText == 'A domicilio') {
            if (jQuery('#shipping-method').length > 0) {
                jQuery(btnHTML).insertAfter('#shipping-method');
            } else {
                jQuery(btnHTML).insertAfter('#shipping-method');
            }
            jQuery('.deliveries-integration').show();
        }

        /*  setTimeout(function() {
              update_cart();
              console.log("actualice");
          }, 2000);*/

        // //jQuery('.distance_measurement_warning').show();

    } else {

        //jQuery('.distance_measurement_warning').hide();
        jQuery('.deliveries-integration').hide();
        jQuery('.deliveries-integration').find('button').removeClass('active');
        flush_cookie();
        update_cart();

        // 			var addressHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="wc-block-editor-components-block-icon" aria-hidden="true" focusable="false"><path d="M12 9c-.8 0-1.5.7-1.5 1.5S11.2 12 12 12s1.5-.7 1.5-1.5S12.8 9 12 9zm0-5c-3.6 0-6.5 2.8-6.5 6.2 0 .8.3 1.8.9 3.1.5 1.1 1.2 2.3 2 3.6.7 1 3 3.8 3.2 3.9l.4.5.4-.5c.2-.2 2.6-2.9 3.2-3.9.8-1.2 1.5-2.5 2-3.6.6-1.3.9-2.3.9-3.1C18.5 6.8 15.6 4 12 4zm4.3 8.7c-.5 1-1.1 2.2-1.9 3.4-.5.7-1.7 2.2-2.4 3-.7-.8-1.9-2.3-2.4-3-.8-1.2-1.4-2.3-1.9-3.3-.6-1.4-.7-2.2-.7-2.5 0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7c0 .2-.1 1-.7 2.4z"></path></svg>${uber_delivery.dropoff_location}`;
        // 			jQuery('span.wc-block-components-radio-control__description').html(addressHTML);
        // 			jQuery('.wc-block-components-shipping-address').html(uber_delivery.dropoff_location);
    }
}
// Verificar si estamos en la página de checkout



function flush_cookie() {
    document.cookie = "delivery_by=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    document.cookie = "delivery_fee=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

function update_cart() {
    // Verifica si la URL actual contiene '/checkout'
    if (window.location.href.includes('/checkout')) {
        // Ejecuta la lógica solo si estamos en la página de checkout
        //jQuery(document.body).trigger('updated_cart_totals');
       // jQuery('body').trigger('update_checkout');
       wp.data.dispatch('wc/store/checkout').invalidateResolution('getCheckout');
        wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
      // console.log("Cart updatedada.");
    } else {
       // console.log("Not on the checkout page. Function 'update_cart' will not execute.");
    }
}

function validateBillingFields() {
    // Obtener los valores de los campos
    const address = document.getElementById('shipping-address_1')?.value.trim() || '';
    const city = document.getElementById('shipping-city')?.value.trim() || '';
    const state = document.getElementById('shipping-state')?.value.trim() || '';
    const postcode = document.getElementById('shipping-postcode')?.value.trim() || '';
    const phone = document.getElementById('shipping-phone')?.value.trim() || '';


    // Validar si los campos están vacíos
    let errors = [];
    if (!address) {
        //   errors.push("El campo 'Direccion' es obligatorio.");
    }
    if (!city) {
        // errors.push("El campo 'Ciudad' es obligatorio.");
    }
    if (!state) {
        //  errors.push("El campo 'Estado' es obligatorio.");
    }
    if (!postcode) {
        errors.push("El campo 'Código postal' es obligatorio.");
    } else if (!/^\d+$/.test(postcode)) { // Validar que sea un número
        errors.push("El campo 'Código postal' debe ser un número.");
    }
    if (!phone || phone.length < 10) {

    }

    // Mostrar errores o devolver resultado
    if (errors.length > 0) {
      //  console.log(errors.join('\n')); // Mostrar errores en la consola
        return {
            success: false,
            errors: errors
        };
    }
    return {
        success: true,
        errors: []
    };



}