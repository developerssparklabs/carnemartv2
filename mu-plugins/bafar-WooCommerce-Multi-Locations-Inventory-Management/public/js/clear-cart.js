(function ($) {
  const { ajaxurl } = multi_inventory;
  const { swal_cart_validation_message } = multi_inventory;
  const { swal_cart_update_btn } = multi_inventory;
  const { swal_cart_update_heading } = multi_inventory;
  const { swal_cart_update_message } = multi_inventory;
  $(document).on("click", "input:radio[name=select_location]", (e) => {
    $(".single_add_to_cart_button").prop("disabled", !0);
    $(".wcmlim_cart_valid_err").remove();
    $(
      "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
    ).insertAfter(".Wcmlim_loc_label");
    $(document.body).trigger("wc_fragments_refreshed");
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: { action: "wcmlim_ajax_cart_count" },
      success(res) {
        var ajaxcartcount = JSON.parse(JSON.stringify(res));
        var value = $(e.target).val();
        var cck_selected_location = getCookie("wcmlim_selected_location");
        if (ajaxcartcount != 0) {
          if (cck_selected_location != "" || cck_selected_location != null) {
            if (cck_selected_location != value) {
              $(".single_add_to_cart_button").prop("disabled", !0);
              $(".wcmlim_cart_valid_err").remove();
              $(
                "<div class='wcmlim_cart_valid_err'>" +
                  swal_cart_validation_message +
                  "<br/><button type='button' class='wcmlim_validation_clear_cart'>" +
                  swal_cart_update_btn +
                  "</button></div>"
              ).insertBefore("#lc_regular_price");
            } else {
              $(".wcmlim_cart_valid_err").remove();
              $(".single_add_to_cart_button").prop("disabled", !1);
            }
          }
        } else {
          $(".wcmlim_cart_valid_err").remove();
          $(".single_add_to_cart_button").prop("disabled", !1);
        }
      },
    });
  });
  $(document).on("change", "#select_location", (e) => {
    clearCart(e);
  });
  function clearCart(e) {
    $(".single_add_to_cart_button").prop("disabled", !0);
    $(".wcmlim_cart_valid_err").remove();
    $(
      "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
    ).insertAfter(".Wcmlim_loc_label");
    $(document.body).trigger("wc_fragments_refreshed");
    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: { action: "wcmlim_ajax_cart_count" },
      success(res) {
        var ajaxcartcount = JSON.parse(JSON.stringify(res));
        var value = $(e.target).val();
        var cck_selected_location = getCookie("wcmlim_selected_location");
        if (ajaxcartcount != 0) {
          if (cck_selected_location != "" || cck_selected_location != null) {
            if (
              value != -1 &&
              value != "" &&
              cck_selected_location != value &&
              value != null
            ) {
              $(".single_add_to_cart_button").prop("disabled", !0);
              $(".wcmlim_cart_valid_err").remove();
              $(
                "<div class='wcmlim_cart_valid_err'>" +
                  swal_cart_validation_message +
                  "<br/><button type='button' class='wcmlim_validation_clear_cart'>" +
                  swal_cart_update_btn +
                  "</button></div>"
              ).insertAfter(".Wcmlim_prefloc_box");
            } else {
              $(".wcmlim_cart_valid_err").remove();
              $(".single_add_to_cart_button").prop("disabled", !1);
            }
          }
        } else {
          $(".wcmlim_cart_valid_err").remove();
          $(".single_add_to_cart_button").prop("disabled", !1);
        }
      },
    });
  }
  function getCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(";");
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == " ") {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
  $(document).on("click", ".wcmlim_validation_clear_cart", async (e) => {

     // Importar el modulo
     const alies_setcookies = await import("./wcmlim_utility/generic/wcmlim_setcookies.js");

     // Obtener el select y la opcion seleccionada
     const selectElement = document.getElementById("wcmlim-change-lc-select");
     let get_termID = null;

     if (selectElement) {
       const selectedOption = selectElement.querySelector("option:checked");
       get_termID = selectedOption ? selectedOption.getAttribute("data-lc-term") : null;
     }

    if ($(".wcmlim-lc-select").length > 0) {
      var dropDownId = $(".wcmlim-lc-select")
        .find(" [jsselect=jsselect]")
        .val();
    } else {
      var dropDownId = $(".select_location").val();
    }

    if ($(".variation_id").length) {
      var product_id = $("input.variation_id").val();
    } else {
      var product_id = $(".single_add_to_cart_button").val();
    }
    jQuery.ajax({
      url: ajaxurl,
      type: "post",
      data: {
        action: "wcmlim_empty_cart_content",
        loc_id: dropDownId,
        product_id: product_id,
      },
      success(output) {
        Swal.fire({
          title: swal_cart_update_heading,
          text: swal_cart_update_message,
          icon: "success",
        }).then(() => {
          alies_setcookies.setcookie("wcmlim_selected_location_termid", get_termID);
          window.location.href = window.location.href;
        });
      },
    });
  });
})(jQuery);
