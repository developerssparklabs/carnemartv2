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

                        const isPickupOnly = Number(store.distance_km) >= 8;
                        const chipBg = isPickupOnly ? '#fff7e6' : '#eaf7f0';
                        const chipColor = isPickupOnly ? '#8a5a00' : '#196d4f';
                        const chipBorder = isPickupOnly ? '#ffe3b3' : '#bfe7d6';

                        let storeHTML = `
<div style="display:none;background:#ffffff;border:1px solid #e6e9ef;border-radius:10px;padding:12px;margin:10px 0;box-shadow:0 2px 6px rgba(0,0,0,.06);">
  <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;align-items:flex-start;">
    
    <div style="flex:1;min-width:220px;">
      <h3 style="margin:0 0 6px 0;font-size:15px;line-height:1.25;color:#1f2937;font-weight:700;">
        ${store.name}
      </h3>
      <div style="font-size:13px;color:#475569;line-height:1.45;">
        <div style="margin:2px 0;">Distancia: <strong style="color:#1f2937;">${Number(store.distance_km).toFixed(2)} km</strong></div>
        <div style="margin:2px 0;">Tiempo estimado: <strong style="color:#1f2937;">${timeMinutes}</strong> min</div>
        <div style="margin:2px 0;">
          Servicio:
          <span style="display:inline-block;padding:2px 8px;border-radius:999px;background:${chipBg};border:1px solid ${chipBorder};color:${chipColor};font-weight:700;font-size:12px;margin-left:4px;">
            ${serviceType}
          </span>
        </div>
      </div>
    </div>

    <div style="flex-shrink:0;display:flex;align-items:center;gap:8px;">
      <a href="#"
         class="tienda-button btn tienda-button"
         data-lc-storeid="${store.storeid}"
         data-lc-name="${store.name}"
         data-lc-key="${store.loc_id}"
         data-lc-address="${store.address}"
         data-lc-term="${store.termid}"
         style="background:#19a463;color:#fff;padding:8px 12px;border:0;border-radius:8px;font-weight:700;font-size:13px;text-decoration:none;display:inline-block;">
        Seleccionar
      </a>
    </div>

  </div>
</div>`;


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
                                        // hideLoader();
                                        jQuery(".single_add_to_cart_button").prop("disabled", false);

                                        // recargar la página para actualizar la tienda sin limpiar el carrito
                                        window.location.reload();
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
