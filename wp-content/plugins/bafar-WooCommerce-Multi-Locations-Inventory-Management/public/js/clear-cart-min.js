
!(function (t) {
  let { ajaxurl: e } = multi_inventory,
    { swal_cart_validation_message: a } = multi_inventory,
    { swal_cart_update_btn: r } = multi_inventory,
    { swal_cart_update_heading: l } = multi_inventory,
    { swal_cart_update_message: i } = multi_inventory;
  function c(t) {
    let e = t + "=",
      a = document.cookie.split(";");
    for (let r = 0; r < a.length; r++) {
      let l = a[r];
      for (; " " == l.charAt(0);) l = l.substring(1);
      if (0 == l.indexOf(e)) return l.substring(e.length, l.length);
    }
    return "";
  }
  t(document).on("click", "input:radio[name=select_location]", (l) => {
    t(".single_add_to_cart_button").prop("disabled", !0),
      t(".wcmlim_cart_valid_err").remove(),
      t(
        "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
      ).insertAfter(".Wcmlim_loc_label"),
      t(document.body).trigger("wc_fragments_refreshed"),
      t.ajax({
        type: "POST",
        url: e,
        data: { action: "wcmlim_ajax_cart_count" },
        success(e) {
          var i = JSON.parse(JSON.stringify(e)),
            n = t(l.target).val(),
            o = c("wcmlim_selected_location");
          0 != i
            ? ("" != o || null != o) &&
            (o != n
              ? (t(".single_add_to_cart_button").prop("disabled", !0),
                t(".wcmlim_cart_valid_err").remove(),
                t(
                  "<div class='wcmlim_cart_valid_err'>" +
                  a +
                  "<br/><button type='button' class='wcmlim_validation_clear_cart'>" +
                  r +
                  "</button></div>"
                ).insertBefore("#lc_regular_price"))
              : (t(".wcmlim_cart_valid_err").remove(),
                t(".single_add_to_cart_button").prop("disabled", !1)))
            : (t(".wcmlim_cart_valid_err").remove(),
              t(".single_add_to_cart_button").prop("disabled", !1));
        },
      });
  }),
    t(document).on("change", "#select_location", (l) => {
      var i;
      (i = l),
        t(".single_add_to_cart_button").prop("disabled", !0),
        t(".wcmlim_cart_valid_err").remove(),
        t(
          "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
        ).insertAfter(".Wcmlim_loc_label"),
        t(document.body).trigger("wc_fragments_refreshed"),
        t.ajax({
          type: "POST",
          url: e,
          data: { action: "wcmlim_ajax_cart_count" },
          success(e) {
            var l = JSON.parse(JSON.stringify(e)),
              n = t(i.target).val(),
              o = c("wcmlim_selected_location");
            0 != l
              ? ("" != o || null != o) &&
              (-1 != n && "" != n && o != n && null != n
                ? (t(".single_add_to_cart_button").prop("disabled", !0),
                  t(".wcmlim_cart_valid_err").remove(),
                  t(
                    "<div class='wcmlim_cart_valid_err'>" +
                    a +
                    "<br/><button type='button' class='wcmlim_validation_clear_cart'>" +
                    r +
                    "</button></div>"
                  ).insertAfter(".er_location"))
                : (t(".wcmlim_cart_valid_err").remove(),
                  t(".single_add_to_cart_button").prop("disabled", !1)))
              : (t(".wcmlim_cart_valid_err").remove(),
                t(".single_add_to_cart_button").prop("disabled", !1));
          },
        });
    }),

    t(document).on("click", ".wcmlim_validation_clear_cart", async (a) => {
      // Importar el modulo
      const alies_setcookies = await import(`${window.location.origin}/wp-content/plugins/bafar-WooCommerce-Multi-Locations-Inventory-Management/public/js/wcmlim_utility/generic/wcmlim_setcookies.js`);

      // Obtener el select y la opcion seleccionada
      const selectElement = document.getElementById("wcmlim-change-lc-select");
      let get_termID = null;

      if (selectElement) {
        const selectedOption = selectElement.querySelector("option:checked");
        get_termID = selectedOption ? selectedOption.getAttribute("data-lc-term") : null;
      }

      if (t(".wcmlim-lc-select").length > 0)
        var r = t(".wcmlim-lc-select").find(" [jsselect=jsselect]").val();
      else var r = t(".select_location").val();
      if (t(".variation_id").length) var c = t("input.variation_id").val();
      else var c = t(".single_add_to_cart_button").val();

      jQuery('#msgLoading').css('display', 'flex');

      jQuery.ajax({
        url: e,
        type: "post",
        data: { action: "wcmlim_empty_cart_content", loc_id: r, product_id: c },
        success(t) {
          jQuery('#msgLoading').css('display', 'none');
          Swal.fire({ 
            title: l, 
            text: i, 
            icon: "success" 
          }).then((result) => {
            if (result.isConfirmed) {
              alies_setcookies.setcookie("wcmlim_selected_location_termid", get_termID);
              jQuery('#msgLoading').css('display', 'flex');
              // Force page reload
              window.location.reload(true);
            }
          });
        },
      });
    });
})(jQuery);
