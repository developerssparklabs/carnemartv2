debugger;

import * as alies_localization from "./wcmlim_localization.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";


var localization = alies_localization.wcmlim_localization();

export function ChangeLocationWithButton() {

  //console.log("Spark ready ");
  // Asegúrate de que los botones existen en la página
  if (jQuery(".btn.tienda-button").length > 0) {
    // Delegar evento click a los botones de ubicación
    jQuery(document).on("click", ".btn.tienda-button", function (e) {
   
      debugger;
      e.preventDefault();

      const button = jQuery(this);
      const locationKey = button.data("lc-key");
      const locationAddress = button.data("lc-address");
      const locationTermID = button.data("lc-term");

      // Actualiza visualmente el botón seleccionado
      jQuery(".btn.tienda-button").removeClass("seleccionado");
      button.addClass("seleccionado");

      // Setear cookies
      alies_setcookies.setcookie("wcmlim_selected_location", locationKey);
      alies_setcookies.setcookie(
        "wcmlim_selected_location_termid",
        locationTermID
      );

      if (localization.isLocationsGroup === "on") {
        const regionID = button.attr("data-lc-regionid") || -1; // Puedes agregar un data extra si es necesario
        alies_setcookies.setcookie("wcmlim_selected_location_regid", regionID);
      }

      // Acciones adicionales según los requerimientos (carrito, etc.)
      if (localization.isClearCart === "on") {
        jQuery(".single_add_to_cart_button").prop("disabled", true);
        jQuery(".wcmlim_cart_valid_err").remove();
        jQuery(
          "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
        ).insertAfter(".Wcmlim_loc_label");

        jQuery.ajax({
          type: "POST",
          url: localization.ajaxurl,
          data: { action: "wcmlim_ajax_cart_count" },
          success(res) {
            const ajaxCartCount = JSON.parse(JSON.stringify(res));
            if (ajaxCartCount != 0) {
              // Lógica para actualizar el carrito o manejar la validación
            } else {
              jQuery(".single_add_to_cart_button").prop("disabled", false);
              window.location.href = window.location.href; // Refrescar página
            }
          },
        });
      } else {
        // Realiza alguna acción directa si no se necesita limpiar el carrito
        jQuery("#wcmlim-change-lc-select").closest("form").submit();
      }

      console.log(`Ubicación seleccionada: ${locationKey}`);
    });
  }
}
