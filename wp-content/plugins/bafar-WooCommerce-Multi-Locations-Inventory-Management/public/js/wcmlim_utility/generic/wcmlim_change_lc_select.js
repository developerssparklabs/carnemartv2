import * as alies_localization from "./wcmlim_localization.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";
import * as alies_selectLocation from "./wcmlim_select_location.js";

var localization = alies_localization.wcmlim_localization();

export function ChangeLc_Select() {
  if (jQuery("#wcmlim-change-lc-select").length > 0) {
    jQuery(".wcmlim-lc-switch").delegate(
      "#wcmlim-change-lc-select",
      "change",
      function (e) {
       
        // verificiamos si se ha selecionado la primera opcion, ya que es 'selecionar' y no deberia hacer nada
        const selectedOption = jQuery(this).find("option:selected");
        const selectedValue = selectedOption.val();
        const firstOptionValue = jQuery(this).find("option:first").val();
        if (selectedValue === firstOptionValue) {
          return; // No hacer nada si se selecciona la primera opción
        }

        // darle valor flex
        jQuery('#msgLoading').css('display', 'flex');
        if (localization.isClearCart == "on") {
       
          var e_value = jQuery(e.target).val();
          jQuery(this).find("option[jsselect]").removeAttr("jsselect");
          jQuery(this)
            .find('option[value="' + e_value + '"]')
            .attr("jsselect", "jsselect");

          jQuery(".single_add_to_cart_button").prop("disabled", true);
          jQuery(".wcmlim_cart_valid_err").remove();
          jQuery(
            "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
          ).insertAfter(".Wcmlim_loc_label");
          jQuery(document.body).trigger("wc_fragments_refreshed");

          var ajaxcartcount = 0;

          jQuery.ajax({
            type: "POST",
            url: localization.ajaxurl,
            data: {
              action: "wcmlim_ajax_cart_count",
            },
            success(res) {
             e.preventDefault();
              ajaxcartcount = JSON.parse(JSON.stringify(res));
              const value = jQuery(e.target).val();
              const cck_selected_location = alies_getcookies.getCookie(
                "wcmlim_selected_location"
              );

              if (ajaxcartcount != 0) {
                if (cck_selected_location != "" || cck_selected_location != null) {
                  if (cck_selected_location != value) {
                    jQuery(".single_add_to_cart_button").prop("disabled", true);
                    jQuery(".wcmlim_cart_valid_err").remove();
                    jQuery(
                      "<div class='wcmlim_cart_valid_err'>" +
                        localization.swal_cart_validation_message +
                        "<br/><button type='button' class='wcmlim_validation_clear_cart'>" +
                        localization.swal_cart_update_btn +
                        "</button></div>"
                    ).appendTo(".er_location");
                    jQuery("#select_location")
                      .find(":selected")
                      .removeAttr("selected");
                    jQuery(
                      ".rselect_location input[name=select_location]"
                    ).prop("checked", false);
                    jQuery(
                      ".rlist_location input[name=wcmlim_change_lc_to]"
                    ).prop("checked", false);
                    jQuery("#select_location option[value=" + value + "]").prop(
                      "selected",
                      true
                    );
                    jQuery(
                      ".rselect_location input[name=select_location][value=" +
                        value +
                        "]"
                    ).prop("checked", true);
                    jQuery(
                      ".rlist_location input[name=wcmlim_change_lc_to][value=" +
                        value +
                        "]"
                    ).prop("checked", true);
                    jQuery("#select_location").val(value).trigger("change");
                  } else {
                    jQuery(".wcmlim_cart_valid_err").remove();
                    jQuery(".single_add_to_cart_button").prop(
                      "disabled",
                      false
                    );
                    jQuery("#wcmlim-change-lc-select").closest("form").submit();
                  }
                }
                jQuery('#msgLoading').css('display', 'none');
              } else {
                alies_setcookies.setcookie("wcmlim_selected_location", value);
                jQuery(".wcmlim_cart_valid_err").remove();
                jQuery(".single_add_to_cart_button").prop("disabled", false);
                const selectElement = document.getElementById("wcmlim-change-lc-select");
                let get_termID = null;
                
                if (selectElement) {
                  const selectedOption = selectElement.querySelector("option:checked");
                  get_termID = selectedOption ? selectedOption.getAttribute("data-lc-term") : null;
                }
                
                alies_setcookies.setcookie(
                  "wcmlim_selected_location_termid",
                  get_termID
                );
                
                jQuery("#wcmlim-change-lc-select").closest("form").submit();

                
                //window.location.href = window.location.href;
              }
            },
          });
        } else {
          jQuery(this).closest("form").submit();
        }
  
        if (localization.isLocationsGroup == "on") {
          const get_regID = jQuery(this)
            .find("option:selected")
            .attr("data-lc-storeid");
          /* alies_setcookies.setcookie(
            "wcmlim_selected_location_regid",
            get_regID
          );*/
          // jQuery('#msgLoading').css('display', 'none');
        } else {
          const get_regID = -1;
          /* alies_setcookies.setcookie(
            "wcmlim_selected_location_regid",
            get_regID
          );*/
        }

        // const get_termID = jQuery(this)
        //                     .find("option:selected")
        //                     .attr("data-lc-term");
        // console.log('get_termID: ', get_termID);
      }
    );
  }
}

export function ChangeLcSelectWithLocation() {
  if (
    jQuery("#wcmlim-change-lcselect").length > 0 &&
    localization.isLocationsGroup == "on"
  ) {
    jQuery(".wcmlim-lcswitch").delegate(
      "#wcmlim-change-lcselect",
      "change",
      function (e) {
        jQuery("#select_location").find(":selected").removeAttr("selected");
        const get_loc = jQuery(this).find("option:selected").val();
        jQuery(`#select_location option[value='${get_loc}']`).prop(
          "selected",
          true
        );
        alies_setcookies.setcookie("wcmlim_selected_location", get_loc);
        const get_term2 = jQuery(this)
          .find("option:selected")
          .attr("data-lc-term");
        alies_setcookies.setcookie(
          "wcmlim_selected_location_termid",
          get_term2
        );
        const get_regID = jQuery(this)
          .find("option:selected")
          .attr("data-lc-storeid");
        alies_setcookies.setcookie("wcmlim_selected_location_regid", get_regID);
        alies_selectLocation.select_location("#select_location");
      }
    );
  }
}
