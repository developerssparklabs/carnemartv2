import * as alies_localization from "./wcmlim_localization.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";
import { showLoader, hideLoader } from "../wcmlim_ui/loader.js";

// Exportar la función para que pueda ser usada desde otro script
export function getNearestStores(latManual = null, lonManual = null) {
    function procesarUbicacion(lat, lon) {
        fetch("/wp-admin/admin-ajax.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_nearest_stores&lat=${lat}&lng=${lon}&limit=5`
        })
            .then(response => response.json())
            .then(data => {

                let localization = alies_localization.wcmlim_localization();

                jQuery('.er_location').html('');

                // Crear y animar título en todos los casos
                let title = jQuery('<p>Selecciona la tienda de tu preferencia:</p>').css('display', 'none');
                title.appendTo('.er_location');
                title.show().addClass('show');

                if (data.success && data.data.length > 0) {
                    let stores = data.data;
                    stores.sort((a, b) => a.distance_km - b.distance_km);
                    stores = stores.slice(0, 5);

                    stores.forEach((store, index) => {
                        let timeMinutes = ((store.distance_km / 30) * 60).toFixed(2);
                        let serviceType = store.distance_km < 8 ? "Servicio a domicilio y recoger en tienda" : "Solo recoger en tienda";

                        let storeHTML = `<p style="display:none;"><a href="#" class="btn tienda-button" data-lc-storeid="${store.storeid}" data-lc-name="${store.name}" data-lc-key="${store.loc_id}" data-lc-address="${store.address}" data-lc-term="${store.termid}">${store.name} - ${store.distance_km.toFixed(2)} km</a> 
                            <br><strong>Tiempo estimado:</strong> ${timeMinutes} minutos.
                            <br><strong>${serviceType}</strong></p>`;

                        let $storeEl = jQuery(storeHTML).appendTo('.er_location');
                        setTimeout(() => {
                            $storeEl.show().addClass('show');
                        }, 500 * index);
                    });
                    // ocultamos el loader una vez cargo las tiendas
                    hideLoader();

                    jQuery(document).on("click", ".btn.tienda-button", function (e) {
                        showLoader();
                        e.preventDefault();
                        const button = jQuery(this);
                        const locationKey = button.data("lc-key");
                        const locationTermID = button.data("lc-term");

                        jQuery(".btn.tienda-button").removeClass("seleccionado");
                        button.addClass("seleccionado");

                        if (localization.isLocationsGroup === "on") {
                            const regionID = button.attr("data-lc-regionid") || -1;
                            alies_setcookies.setcookie("wcmlim_selected_location_regid", regionID);
                        }

                        if (localization.isClearCart === "on") {
                            jQuery(".single_add_to_cart_button").prop("disabled", true);
                            jQuery(".wcmlim_cart_valid_err").remove();
                            jQuery("<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>").insertAfter(".Wcmlim_loc_label");

                            jQuery.ajax({
                                type: "POST",
                                url: localization.ajaxurl,
                                data: { action: "wcmlim_ajax_cart_count" },
                                success(res) {
                                    const ajaxCartCount = JSON.parse(JSON.stringify(res));
                                    if (ajaxCartCount != 0) {
                                        hideLoader();
                                        Swal.fire({
                                            icon: 'warning',
                                            title: '<strong>Cambiar de tienda</strong>',
                                            html: `
                                                <div style="text-align:left; font-size:15px; color:#333;">
                                                <p>Para cambiar de tienda, es necesario limpiar tu carrito actual.</p>
                                                <p>¿Deseas continuar?</p>
                                                </div>
                                            `,
                                            showCancelButton: true,
                                            confirmButtonText: 'Sí, limpiar y cambiar',
                                            cancelButtonText: 'No, volver',
                                            reverseButtons: true,
                                            customClass: {
                                                popup: 'swal2-border-radius clear-cart-confirm-modal'
                                            },
                                            allowOutsideClick: false
                                        }).then(choice => {
                                            if (choice.isConfirmed) {
                                                const urlClearCart = "/wp-json/carnemart/v1/clear-cart";
                                                showLoader();
                                                // Llamada para vaciar el carrito
                                                fetch(urlClearCart, {
                                                    method: "POST",
                                                })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        hideLoader();
                                                        if (data.success) {
                                                            Swal.fire({
                                                                icon: 'success',
                                                                title: '<strong>¡Carrito vaciado!</strong>',
                                                                html: `
                                                                        <div style="text-align:left; font-size:16px; color:#333;">
                                                                        <p>Tu carrito ha sido limpiado correctamente.</p>
                                                                        </div>
                                                                    `,
                                                                timer: 2000,
                                                                timerProgressBar: true,
                                                                showConfirmButton: false,
                                                                customClass: {
                                                                    popup: 'swal2-border-radius clear-cart-modal'
                                                                },
                                                                allowOutsideClick: false
                                                            }).then(() => {
        
                                                                alies_setcookies.setcookie("wcmlim_selected_location", locationKey);
                                                                alies_setcookies.setcookie("wcmlim_selected_location_termid", locationTermID);
        
                                                                 // Segundo modal de “redirigiéndote…”
                                                                return Swal.fire({
                                                                    icon: 'info',
                                                                    title: '<strong>Redirigiéndote a la tienda seleccionada…</strong>',
                                                                    html: `
                                                                    <div style="text-align:left; font-size:15px; color:#333;">
                                                                        <p>Un momento, por favor…</p>
                                                                    </div>
                                                                    `,
                                                                    timer: 2000,
                                                                    timerProgressBar: true,
                                                                    showConfirmButton: false,
                                                                    customClass: {
                                                                    popup: 'swal2-border-radius redirect-modal'
                                                                    },
                                                                    allowOutsideClick: false
                                                                }).then(() => {
                                                                    // Aquí la redirección real, por ejemplo:
                                                                    window.location.href = '/?r=refresh';
                                                                });
                                                            });
                                                        } else {
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: '<strong>Error</strong>',
                                                                html: `
                                                                        <div style="text-align:left; font-size:16px; color:#333;">
                                                                        <p>No pudimos limpiar tu carrito. Intenta de nuevo más tarde.</p>
                                                                        </div>
                                                                    `,
                                                                confirmButtonText: 'Entendido',
                                                                customClass: {
                                                                    popup: 'swal2-border-radius clear-cart-modal'
                                                                }
                                                            });
                                                            jQuery(".single_add_to_cart_button").prop("disabled", false);
                                                        }
                                                    })
                                                    .catch(error => {
                                                        hideLoader();
                                                        console.error("Error al limpiar carrito:", error);
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: '<strong>Error</strong>',
                                                            html: `
                                                            <div style="text-align:left; font-size:16px; color:#333;">
                                                                <p>Ocurrió un problema al limpiar el carrito.</p>
                                                            </div>
                                                            `,
                                                            confirmButtonText: 'Entendido',
                                                            customClass: {
                                                            popup: 'swal2-border-radius clear-cart-modal'
                                                            }
                                                        });
                                                        jQuery(".single_add_to_cart_button").prop("disabled", false);
                                                    });
                                            } else {
                                                hideLoader();
                                                // Si cancela, reactivamos el botón
                                                jQuery(".single_add_to_cart_button").prop("disabled", false);
                                            }
                                        });
                                    } else {
                                        alies_setcookies.setcookie("wcmlim_selected_location", locationKey);
                                        alies_setcookies.setcookie("wcmlim_selected_location_termid", locationTermID);
                                        hideLoader();
                                        jQuery(".single_add_to_cart_button").prop("disabled", false);
                                        window.location.href = window.location.href + "/?r=refresh";
                                    }
                                },
                            });
                        } else {
                            const randomString = Math.random().toString(36).substring(7);
                            const timestamp = Math.floor(Date.now() / 1000);
                            window.location.href = `/?tiendas=${randomString}&mb=${timestamp}`;
                        }
                    });
                } else {
                    // Mensaje de "sin tiendas" con animación
                    jQuery(`
                    <p style="display:none;">
                        Lo sentimos, no encontramos tiendas cercanas a tu ubicación.  
                        Puedes intentarlo de nuevo o ingresar tu <strong>código postal</strong> para buscar manualmente.
                    </p>
                    `)
                    .appendTo('.er_location')
                    .show()
                    .addClass('show');

                    hideLoader();
                }
            })
            .catch(error => {
                console.error("Error en AJAX:", error);

                jQuery('.er_location').html('');

                let title = jQuery('<p>Selecciona la tienda de tu preferencia:</p>').css('display', 'none');
                title.appendTo('.er_location');
                title.show().addClass('show');

                jQuery('<p style="display:none;">Hubo un problema al obtener la información. Por favor, intenta de nuevo más tarde.</p>')
                    .appendTo('.er_location')
                    .show()
                    .addClass('show');

                hideLoader();
            });
    }

    if (latManual !== null && lonManual !== null) {
        procesarUbicacion(latManual, lonManual);
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                procesarUbicacion(position.coords.latitude, position.coords.longitude);
            },
            function (error) {
                console.error("Error obteniendo ubicación:", error);
                jQuery('.er_location').html('');

                let title = jQuery('<p>Selecciona la tienda de tu preferencia:</p>').css('display', 'none');
                title.appendTo('.er_location');
                title.show().addClass('show');

                jQuery('<p style="display:none;">No pudimos acceder a tu ubicación. Por favor, verifica los permisos de tu navegador.</p>')
                    .appendTo('.er_location')
                    .show()
                    .addClass('show');

                hideLoader();
            }
        );
    } else {
        console.log("Geolocalización no soportada.");
        jQuery('.er_location').html('');

        let title = jQuery('<p>Selecciona la tienda de tu preferencia:</p>').css('display', 'none');
        title.appendTo('.er_location');
        title.show().addClass('show');

        jQuery('<p style="display:none;">Tu navegador no soporta geolocalización.</p>')
            .appendTo('.er_location')
            .show()
            .addClass('show');

        hideLoader();
    }
}
