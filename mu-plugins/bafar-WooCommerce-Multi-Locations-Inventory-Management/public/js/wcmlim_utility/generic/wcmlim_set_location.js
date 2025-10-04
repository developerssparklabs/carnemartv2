// Importación de módulos necesarios
import * as alies_localization from "./wcmlim_localization.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_CommonFunction from "./wcmlim_common_functions.js";

import { showLoader, hideLoader } from "../wcmlim_ui/loader.js";

// Carga de la configuración de localización
var localization = alies_localization.wcmlim_localization();

//validar carga inicial
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(";").shift();
}

async function fetchLocationData() {
  //fetch location verifica si ya hay tiendas seleccionadas

  // Obtener el elemento del DOM
  const link = document.getElementById("btnBuscadorTienda");
  const span = link ? link.querySelector("span") : null;
  const txtentrega = document.querySelector(".txtentrega");
  const txthorarios = document.querySelector(".txthorarios");
  const txtubicacion = document.querySelector(".txtubicacion");
  // Obtener el valor de la cookie
  const location = getCookie("wcmlim_selected_location");
  const locationId = getCookie("wcmlim_selected_location_termid");

  // Obtener el elemento <a>
  //var linkenlace = document.getElementById("set-def-store-popup-btn");

  // Verificar si el texto del enlace contiene "Selecciona una tienda"
  //if (linkenlace && linkenlace.textContent.includes("Selecciona una tienda")) {

  // Verificar si alguna de las cookies no tiene valor o está en "undefined"
  //console.log('se actualizo el location js')
  if (
    typeof location === "undefined" ||
    typeof locationId === "undefined" ||
    locationId == null ||
    locationId === ""
  ) {
    span.textContent = "Buscar tienda"; // Texto alternativo si las cookies no son válidas
    var names = "wcmlim_selected_location_termid";
    document.cookie =
      names + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    return;
  }
  //}

  // Leer todas las cookies
  var cookies = document.cookie;
  var txtubicaciontext = "";

  // Buscar si la cookie 'ubicacioncerca' existe y tiene valor '1'
  if (cookies.includes("ubicacioncerca=1")) {
    txtubicaciontext = "Disponible envío con Uber y Rappi";
  } else {
    txtubicaciontext =
      "En tu ubicación no hay servicio a domicilio la distancia máxima deben ser 8 Kilómetros.";
  }

  txtubicacion.textContent = txtubicaciontext;

  // Construir la URL de la API con el valor de la cookie
  const url = `${window.location.origin}/wp-json/wp/v2/locations/${locationId}`;
  // alert(url); 
  try {
    // Llamada fetch a la URL (API REST de WordPress)
    const response = await fetch(url, {
      method: "GET",
    });

    if (!response.ok) {
      throw new Error("Error en la petición: " + response.statusText);
    }
    // Obtener los datos de la respuesta
    const data = await response.json();
    //   console.log("Datos de la ubicación: ", data);

    // Si el nombre existe, lo procesamos
    if (data.name) {
      // Dividimos el nombre en dos partes: antes y después del guion
      const parts = data.name.split(" - ");

      // Reemplazamos "CMT" solo en la primera parte (antes del guion)
      parts[0] = parts[0].replace(/CMT/g, "Sucursal");

      // Unimos nuevamente las partes, si existe la segunda parte
      const updatedName = parts[0];

      // Cambia el texto en el link
      if (span) {
        span.textContent = updatedName + " - (Cambiar tienda)";
      }

      const km_alberto_serch = getCookie("km_alberto_serch");
      const address =
        data.meta.wcmlim_street_number +
        " " +
        data.meta.wcmlim_route +
        ", C.P." +
        data.meta.wcmlim_postal_code +
        " " +
        data.meta.wcmlim_locality +
        ", " +
        data.meta.wcmlim_administrative_area_level_1;
      txtentrega.innerHTML =
        address
        +
        "<a class='resultados__ubicacion-inline-link' href='" +
        data.url_maps +
        "' target='_blank'> Ver en Google Maps </a>";
      //enlace a google maps
      // txthorarios.textContent = "Lunes a Domingo" + " " + data.meta.wcm lim_start_time + " a " + data.meta.wcmlim_end_time
    }
  } catch (error) {
    console.error("Error al obtener los datos de la ubicación:", error);
  }

  jQuery("#msgLoading").hide();
}

import { getNearestStores } from "./nearest-stores.js"; // Importar en la parte superior


function showLoading() {
  let loader = document.createElement("div");
  loader.id = "customLoader";
  loader.innerHTML = `
        <div class="loader-container">
            <div class="loader"></div>
            <p>Un momento por favor, estamos buscando las tiendas más cercanas para ti...</p>
        </div>
    `;
  document.body.appendChild(loader);
}

function hideLoading() {
  let loader = document.getElementById("customLoader");
  if (loader) {
    loader.remove();
  }
}


jQuery("#btnLocaliza").click(function () {
  // Mostrar loading
  jQuery('#msgLoading').css('display', 'flex');
  getNearestStores()
});

jQuery(document).on("click", "#submit_postcode_global", function (e) {
  var postal = jQuery("#elementIdGlobalCode").val();
  jQuery('#msgLoading').css('display', 'flex');
  const data = new URLSearchParams();
  data.append("action", "get_coordinates_by_zip");
  data.append("zip_code", postal);

  fetch("/wp-admin/admin-ajax.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: data,
  })
    .then((response) => response.json())
    .then((result) => {
      if (!result.success) {
        hideLoader();
        jQuery('.er_location').html("");
        let MessageError = jQuery(`<p>No fue posible encontrar tu código postal con la ubicación actual. Puedes buscar una tienda manualmente.</p>`).css('display', 'none');
        MessageError.appendTo('.er_location');
        MessageError.show().addClass('show');
      } else {
        getNearestStores(result.data.latitude, result.data.longitude);
      }
    })
    .catch((error) => console.error("Fetch error:", error));
});

document.addEventListener("DOMContentLoaded", function () {
  fetchLocationData();
});

// Función para establecer la ubicación basada en la dirección dada
export function setLocation(address) {
  let lat;
  let lng;

  // Limpiar mensajes anteriores
  jQuery("#mensajesPopup").html("");
  // Obtener coordenadas globales si están disponibles
  var elem_Glob_return = alies_CommonFunction.elementIdGlobalFn();
  if (elem_Glob_return) {
    lat = elem_Glob_return.lat;
    lng = elem_Glob_return.lng;
  }

  // Obtener coordenadas locales si están disponibles
  var elem_return = alies_CommonFunction.elementIdFn();
  if (elem_return) {
    lat = elem_return.lat;
    lng = elem_return.lng;
  }

  // Determinar el código postal a utilizar
  var postal_code = address || jQuery(".class_post_code_global").val();

  // Obtener el pin global, si existe
  const globalPin = jQuery("#global-postal-check").val();

  // Comprobación del valor del código postal y animación si está vacío
  if (jQuery('[name="post_code_global"]', this).val() == "") {
    jQuery(this).addClass("wcmlim-shaker");
    setTimeout(() => {
      jQuery(".postcode-checker")
        .find(".wcmlim-shaker")
        .removeClass("wcmlim-shaker");
    }, 600);
    return;
  }

  // Mostrar loader durante la carga
  let loader = alies_CommonFunction.loaderhtml();
  jQuery(".postcode-checker-response").html(loader);

  // Solicitud AJAX para encontrar la ubicación más cercana
  jQuery.ajax({
    url: localization.ajaxurl,
    type: "post",
    data: {
      postcode: postal_code,
      globalPin,
      lat,
      lng,
      action: "wcmlim_closest_location",
    },
    dataType: "json",
    success(response) {
      //sparkanos2
      if (jQuery.trim(response.status) === "true") {

        processLocationResponse(response);
      }
    },
    error: function () {
      jQuery(".postcode-checker-response").empty();
    },
  });
}

// Procesar la respuesta de ubicación exitosa
function processLocationResponse(response) {
  // Descomponer la unidad de distancia
  var dunit = response.loc_dis_unit;
  var n = dunit ? dunit.split(" ")[0] : null;

  // Verificar si la ubicación está dentro del radio de servicio
  //si la distancia es menor a 40 km puede ir a recoger

  if (n !== null) {
    //40 kilometros da la opcoion de ir a recoger
    if (n <= Number(40)) {
      // si la distancia es menor al radio 8km puede aceptar delivery y pickup
      // if (response.locServiceRadius && (Number(response.locServiceRadius) >= Number(n) || !n)) { //está opción puede ser disponible si se configura en cada tienda el diametro, puede servicir si queremos personalizar el radio de servicio que en alghunas tiendas si aplica
      // 8km
      if (n <= Number(8)) {
        // Mostrar detalles y configurar cookies si hay cobertura
        displayLocationDetails(response, n, 1);
        return 0;
      } else {
        // todo: manejar el caso de no cobertura
        displayLocationDetails(response, n, 2);
        return 0;
      }
    } else {
      // 0 cobertura
      displayInfoCustomerDetails(response, n);
    }
  } else {
    displayInfoCustomerDetails(response, n);
  }
}

// Mostrar detalles de la ubicación y configurar cookies
function displayLocationDetails(response, distance, tipo) {
  //tipo = 1 cobertura en todo delivery y pickup , tipo = 2 solo pickup
  var hora =
    response.tiempotexto || (distance <= 40 ? "1 hora" : "más de 4 horas");
  var kilometraje = response.loc_dis_unit;
  const txtentrega = document.querySelector(".txtentrega");

  if (tipo == 1) {
    jQuery("#mensajesPopup").html(
      "Si cuentas con envío a domicilio y ubicación para recoger cercana: <strong>" +
      response.secNearLocAddress +
      ", a " +
      hora +
      ", " +
      kilometraje +
      ".</strong>"
    );
    document.cookie =
      "ubicacioncerca=1; path=/;| expires=" +
      new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000).toUTCString();
    alies_setcookies.setcookie("km_alberto_serch", kilometraje);
  } else {
    jQuery("#mensajesPopup").html(
      "No cuentas con envío domicilio, pero puedes ir a recoger en :  <strong>" +
      response.secNearLocAddress +
      ", a " +
      hora +
      ", " +
      kilometraje +
      ".</strong>"
    );
    document.cookie =
      "ubicacioncerca=0; path=/; expires=" +
      new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000).toUTCString();
    alies_setcookies.setcookie("km_alberto_serch", kilometraje);
  }

  // Obtiene el atributo 'data-lc-term' de la opción seleccionada
  var selectElement = document.getElementById("wcmlim-change-lc-select");
  var selectedOption = selectElement.options[selectElement.selectedIndex];
  var lcTerm = selectedOption.getAttribute("data-lc-term");

  // Configuración de cookies para la ubicación

  alies_setcookies.setcookie("wcmlim_selected_location", response.loc_key);
  alies_setcookies.setcookie("wcmlim_selected_location_regid", "");
  alies_setcookies.setcookie(
    "wcmlim_selected_location_termid",
    response.locationid
  );
  setTimeout(() => {
    // Generar una cadena aleatoria para evitar el caché
    var randomString = Math.random().toString(36).substring(7);
    var currentUrl = window.location.href; // Obtiene la URL actual
    var separator = currentUrl.includes("?") ? "&" : "?"; // Verifica si ya hay parámetros en la URL

    // Redirigir a la misma página con el parámetro aleatorio
    window.location.href = currentUrl + separator + "rand=" + randomString;
  }, 6000);
}

// Mostrar detalles de la ubicación y configurar cookies
function displayInfoCustomerDetails(response, distance) {
  // jQuery(".postcode-checker-response").html("Fuera del radio de servicio.");
  var hora =
    response.tiempotexto || (distance <= 40 ? "1 hora" : "más de 4 horas");
  var kilometraje =
    response.loc_dis_unit !== null ? response.loc_dis_unit : "más de 400 Km";

  jQuery("#mensajesPopup").html(
    "Estimado cliente por el momento no contamos con cobertura su ubicación más cercana: " +
    response.secNearLocAddress +
    ", a " +
    hora +
    ", " +
    kilometraje +
    ". Sugerimos seleccionar cualquier tienda para que pueda revisar nuestro catálogo."
  );

  // Obtiene el atributo 'data-lc-term' de la opción seleccionada
  var lcTerm = selectedOption.getAttribute("data-lc-term");

  // Configuración de cookies para la ubicación
  alies_setcookies.setcookie("nombretienda", response.loc_key);
  //alies_setcookies.setcookie("wcmlim_selected_location", response.loc_key);
  //alies_setcookies.setcookie("wcmlim_selected_location_regid", response.secgrouploc);
  //alies_setcookies.setcookie("wcmlim_selected_location_termid", lcTerm);

  // Redireccionar a la página principal después de 5 segundos
  setTimeout(() => {
    // Actualizar selección en la interfaz de usuario
    jQuery(
      'select[name="wcmlim_change_lc_to"] option[value="' +
      response.loc_key +
      '"]'
    ).attr("selected", "selected");
    jQuery('select[name="wcmlim_change_lc_to"]').trigger("change");

    var randomString = Math.random().toString(36).substring(7);
    const timestamp = Math.floor(Date.now() / 1000); // Obtiene el timestamp en segundos
    window.location.href =
      "https://carnemart.mystagingwebsite.com/?tiendas=" +
      randomString +
      "&mb=" +
      timestamp;
  }, 10000);
}