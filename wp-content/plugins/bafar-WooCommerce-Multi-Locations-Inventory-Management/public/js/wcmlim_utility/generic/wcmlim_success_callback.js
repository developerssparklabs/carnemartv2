import * as wcmlim_set_location from "./wcmlim_set_location.js";
import * as alies_localization from "./wcmlim_localization.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";
import { showLoader, hideLoader } from "../wcmlim_ui/loader.js";

var localization = alies_localization.wcmlim_localization();

/**
 * Callback que se ejecuta al obtener correctamente la geolocalización del usuario.
 * Usa la API de Google Maps para obtener la dirección formateada y luego la guarda.
 * Finalmente, llama a la función para obtener tiendas cercanas usando lat/lng.
 * 
 * @param {GeolocationPosition} position - Objeto proporcionado por la API de geolocalización del navegador.
 */
export function successCallback(position) {
  const lat = position.coords.latitude;
  const lng = position.coords.longitude;

  // if (!window.google || !google.maps) {
  //   Swal.fire({ icon: "error", text: "No se puede acceder a Google Maps. Verifica la conexión." });
  //   return;
  // }

  const baseUrl = window.location.origin;

  const urlCleanCart = '/wp-json/carnemart/v1/clear-cart';

  verifyCart(baseUrl).then((cartStatus) => {
    getStores(lat, lng).then((statusStores) => {
      if (statusStores[0]) {
        const store = statusStores[1][0];
        const timeMinutes = ((store.distance_km / 30) * 60).toFixed(2); // Estimación de 30km/h
        const serviceType = store.distance_km < 8
          ? "Servicio a domicilio y recoger en tienda"
          : "Solo recoger en tienda";
        Swal.fire({
          icon: 'success',
          title: '<strong>¡Tienda más cercana encontrada!</strong>',
          html: `
                  <div style="text-align: left; font-size: 15px; color: #333;">
                    <p><strong>Tienda:</strong> ${store.name}</p>
                    <p><strong>Dirección:</strong> ${store.address}</p>
                    <p><strong>Coordenadas:</strong> ${store.latitude}, ${store.longitude}</p>
                    <p><strong>Distancia:</strong> ${store.distance_km.toFixed(2)} km</p>
                    <p><strong>Tiempo estimado:</strong> ${timeMinutes} minutos</p>
                    <p><strong>Servicio:</strong> ${serviceType}</p>
                    <div style="margin-top: 10px; color: #555;">
                      <em>Estamos dirigiéndote a tu tienda más cercana...</em>
                    </div>
                  </div>
                `,
          timer: 7500, // El tiempo que se mostrará el modal (3.5s)
          timerProgressBar: true,
          showConfirmButton: false,
          showCancelButton: false,
          customClass: {
            popup: 'swal2-border-radius location_shared-modal'
          },
          allowOutsideClick: false,
          allowEscapeKey: false,
          didClose: () => {
            const storeData = {
              storeid: store.storeid,
              name: store.name,
              loc_id: store.loc_id,
              address: store.address,
              termid: store.termid
            };

           // console.log("Tienda confirmada:", storeData);

            if (cartStatus) {
              Swal.fire({
                icon: 'warning',
                title: '<strong class="location_shared-title">Atención: acción requerida</strong>',
                html: `
                        <div class="location_shared-content">
                          <p>Hemos detectado que tienes productos en tu carrito de compras.</p>
                          <p>Para continuar, es necesario <strong>vaciar tu carrito</strong>.</p>
                          <p>¿Deseas proceder con esta acción?</p>
                        </div>
                      `,
                showCancelButton: true,
                confirmButtonText: 'Sí, vaciar carrito',
                cancelButtonText: 'No, mantener carrito',
                customClass: {
                  popup: 'swal2-border-radius location_shared-modal',
                  confirmButton: 'swal2-location_shared-confirm',
                  cancelButton: 'swal2-location_shared-cancel'
                },
                buttonsStyling: false,
                allowOutsideClick: false,
                allowEscapeKey: false
              }).then((result) => {
                if (result.isConfirmed) {
                  simpleClearCart(urlCleanCart).then((status) => {
                    if (status) {
                      alies_setcookies.setcookie("wcmlim_selected_location", storeData.loc_id);
                      alies_setcookies.setcookie("wcmlim_selected_location_termid", storeData.termid);
                      setCookie('geolocation_accepted', 'true');
                      showLoader();
                      setTimeout(() => {
                        window.location.search = '?r=refresh';
                      }, 500);
                    }
                  });
                }
              });
            } else {
              alies_setcookies.setcookie("wcmlim_selected_location", storeData.loc_id);
              alies_setcookies.setcookie("wcmlim_selected_location_termid", storeData.termid);
              setCookie('geolocation_accepted', 'true');
              showLoader();
              setTimeout(() => {
                window.location.search = '?r=refresh';
              }, 500);
            }
          }
        });
        hideLoader();
      } else {
        hideLoader();
      }
    });
  });
}

/**
 * Delegación del evento de clic en los botones dentro del contenedor "#stores-get".
 * 
 * Esta función gestiona el comportamiento de selección de ubicación. Dependiendo del estado del carrito
 * y la configuración del sistema, puede vaciar el carrito, establecer cookies para guardar la tienda seleccionada,
 * y redirigir al usuario a una nueva URL correspondiente a la ubicación elegida.
 * 
 * @param {boolean} cartStatus - Indica si el carrito contiene productos o está vacío.
 * @param {string} url - URL que se utilizará para realizar la petición de vaciado del carrito.
 */
// function delegateEvent(cartStatus, url) {
//   // Delegar evento específicamente al contenedor de tiendas
//   jQuery("#stores-get").off("click", ".btn.tienda-button")
//     .on("click", ".btn.tienda-button", function (e) {
//       e.preventDefault();
//       showLoader();
//       const button = jQuery(this);
//       const locationKey = button.data("lc-key");
//       const locationTermID = button.data("lc-term");

//       if (localization.isLocationsGroup === "on") {
//         const regionID = button.attr("data-lc-regionid") || -1;
//         alies_setcookies.setcookie("wcmlim_selected_location_regid", regionID);
//       }

//       // Verificamos si esta habilitado la opcion de vaciar el carrito al cambiar de tienda
//       if (localization.isClearCart === "on") {
//         if (cartStatus) {
//           hideLoader();
//           Swal.fire({
//             title: localization.swal_cart_validation_message,
//             showCancelButton: true,
//             confirmButtonText: "Sí, vaciar carrito",
//             cancelButtonText: "No, cancelar",
//             allowOutsideClick: false,
//             allowEscapeKey: false,
//           }).then((result) => {
//             if (result.isConfirmed) {
//               showLoader();
//               simpleClearCart(url).then((status) => {
//                 if (status) {
//                   hideLoader();
//                   Swal.fire({
//                     icon: "success",
//                     title: "Carrito vaciado",
//                     text: "El carrito ha sido vaciado, ¡agrega de nuevo desde la nueva ubicación!",
//                     confirmButtonText: "OK",
//                     showConfirmButton: true,
//                     allowOutsideClick: false,
//                     allowEscapeKey: false,
//                   }).then(() => {
//                     alies_setcookies.setcookie("wcmlim_selected_location", locationKey);
//                     alies_setcookies.setcookie("wcmlim_selected_location_termid", locationTermID);
//                     // reloadWindow();
//                   })
//                 } else {
//                   hideLoader();
//                   Swal.fire({
//                     icon: "error",
//                     title: "Error",
//                     text: "No se pudo vaciar el carrito. Por favor, intenta de nuevo más tarde.",
//                     confirmButtonText: "OK",
//                     allowOutsideClick: false,
//                     allowEscapeKey: false,
//                   });
//                   // reloadWindow();
//                 }
//               });
//             }
//           })
//         } else {
//           hideLoader();
//           alies_setcookies.setcookie("wcmlim_selected_location", locationKey);
//           alies_setcookies.setcookie("wcmlim_selected_location_termid", locationTermID);
//           // reloadWindow();
//         }
//       } else {
//         const randomString = Math.random().toString(36).substring(7);
//         const timestamp = Math.floor(Date.now() / 1000);
//         window.location.href = `/?tiendas=${randomString}&mb=${timestamp}`;
//       }
//     });
// }

function delegateEvent_v2(cartStatus, url) {

}
/**
 * Genera el HTML para mostrar la lista de tiendas.
 *
 * @param {Array} stores - Lista de tiendas con sus datos (nombre, distancia, etc.).
 * @returns {string} HTML formateado para ser mostrado en un modal.
 */
// function generateStoreListHTML(stores) {
//   let htmlOutput = '<h4 style="color: #000;">Selecciona la tienda de tu preferencia:</h4><br>';

//   htmlOutput += '<div id="stores-get">';
//   stores.forEach(store => {
//     const timeMinutes = ((store.distance_km / 30) * 60).toFixed(2);
//     const serviceType = store.distance_km < 8
//       ? "Servicio a domicilio y recoger en tienda"
//       : "Solo recoger en tienda";

//     htmlOutput += `
//       <p>
//         <a href="#" class="btn tienda-button"
//            data-lc-storeid="${store.storeid}"
//            data-lc-name="${store.name}"
//            data-lc-key="${store.loc_id}"
//            data-lc-address="${store.address}"
//            data-lc-term="${store.termid}">
//            ${store.name} - ${store.distance_km.toFixed(2)} km
//         </a><br>
//         <strong>Tiempo estimado:</strong> ${timeMinutes} minutos.<br>
//         <strong>${serviceType}</strong>
//       </p>`;
//   });
//   htmlOutput += '</div>';

//   return htmlOutput;
// }

/**
 * Obtiene las tiendas más cercanas según latitud y longitud.
 * Muestra un modal con la lista de tiendas o un mensaje de que no hay disponibles.
 *
 * @param {number} lat - Latitud actual del usuario.
 * @param {number} lng - Longitud actual del usuario.
 */
async function getStores(lat, lng, cartStatus) {
  try {
    const response = await fetch("/wp-admin/admin-ajax.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: new URLSearchParams({
        action: "get_nearest_stores",
        lat,
        lng,
        limit: 1
      })
    });

    const data = await response.json();

    if (data.success && data.data.length > 0) {
      let stores = data.data;

      // Ordenar por distancia y limitar a 5 resultados
      stores.sort((a, b) => a.distance_km - b.distance_km);
      stores = stores.slice(0, 5);

      //const htmlOutput = generateStoreListHTML(stores);

      // Swal.fire({
      //   icon: 'success',
      //   title: 'Ubicación obtenida con éxito.',
      //   html: htmlOutput,
      //   confirmButtonText: 'OK',
      //   allowOutsideClick: false,
      //   allowEscapeKey: false,
      //   showClass: {
      //     popup: 'animate__animated animate__fadeInDown'
      //   },
      //   hideClass: {
      //     popup: 'animate__animated animate__fadeOutUp'
      //   }
      // });
      return [true, stores];
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Ubicación obtenida',
        html: '<p>Lo sentimos, no hay tiendas cercanas disponibles en este momento.</p>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: 'OK'
      });
      return false
    }
  } catch (error) {
    console.error("Error en AJAX:", error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No se pudo obtener la información de las tiendas.',
      confirmButtonText: 'OK',
      allowOutsideClick: false,
      allowEscapeKey: false,
    });
  }
}

/**
 * Verifica si el carrito de WooCommerce contiene productos.
 * 
 * Esta función primero intenta verificar si la cookie 'woocommerce_items_in_cart' 
 * indica que hay al menos tiene el valor de 1. Si no se encuentra o es 0,
 * se hace una solicitud fetch al admin-ajax para obtener el conteo actual del carrito.
 * 
 * @param {string} baseUrl - URL base del sitio para construir la URL del admin-ajax.
 * @returns {Promise<boolean>} - true si el carrito tiene productos, false si está vacío.
 */
async function verifyCart(url) {
  // Verificamos si existe la cookie 'woocommerce_items_in_cart'
  const cartCookie = document.cookie.split('; ')
    .find(row => row.startsWith('woocommerce_items_in_cart='));

  if (cartCookie && cartCookie.split('=')[1] === '1') {
    return true;
  }

  // Si no hay cookie válida, hacemos la consulta vía fetch
  try {
    const response = await fetch(`${url}/wp-admin/admin-ajax.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ action: 'wcmlim_ajax_cart_count' }),
    });

    const cartCount = parseInt(await response.text(), 10);
    return cartCount > 0;
  } catch (error) {
    console.error("Error al verificar el carrito:", error);
    return false;
  }
}

/**
 * Vacía el carrito de WooCommerce mediante una llamada AJAX al servidor.
 * 
 * @returns {Promise<boolean>} - true si el carrito fue vaciado correctamente, false en caso contrario.
 */
async function simpleClearCart(url) {
  try {
    const response = await fetch(url, {
      method: 'POST'
    });

    const result = await response.json();

    return result.success === true;
  } catch (error) {
    console.error("Error al vaciar el carrito:", error);
    return false;
  }
}

function reloadWindow() {
  setTimeout(() => {
    window.location.reload();
  }, 400);
}
