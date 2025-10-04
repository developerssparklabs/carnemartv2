jQuery(document).ready(e => {
    e.noConflict();
    let t, o,
      { ajaxurl: l } = multi_inventory,
      c = multi_inventory.autodetect,
      a = multi_inventory.autodetect_by_maxmind,
      { enable_price: i } = multi_inventory,
      s = multi_inventory.user_specific_location,
      n = multi_inventory.show_location_selection,
      { instock: r } = multi_inventory,
      { soldout: d } = multi_inventory,
      m = multi_inventory.stock_format,
      { widget_select_type: p } = multi_inventory,
      h = multi_inventory.nxtloc;
  
    if ("yes" == multi_inventory.wchideoosproduct) {
      let M = ["theme-astra", "theme-flatsome", "theme-woodmart", "theme-xstore", "theme-kuteshop-elementor", "theme-kuteshop"];
      M.forEach(t => {
        e("body.home").hasClass(t) && e("body.home").find(".locsoldout").remove();
        e("body.single-product").hasClass(t) && e("body.single-product").find(".locsoldout").remove();
      });
    }
  
    function handleGeolocationSuccess(e) {
      let t = e.coords.latitude,
        o = e.coords.longitude,
        l = new google.maps.LatLng(t, o),
        c = new google.maps.Geocoder();
      c.geocode({ latLng: l }, (e, t) => {
        if (t == google.maps.GeocoderStatus.OK && e[0]) {
          let o = e[0].formatted_address;
          current_setLocation(o);
        }
      });
    }
  
    function handleGeolocationError(e) {
      switch (e.code) {
        case e.PERMISSION_DENIED:
          Swal.fire({ icon: "error", text: "Has decidido no compartir tu ubicación, pero está bien. No volveremos a pedirlo." });
          break;
        case e.POSITION_UNAVAILABLE:
          Swal.fire({ icon: "error", text: "La información de la ubicación no está disponible." });
          break;
        case e.TIMEOUT:
          Swal.fire({ icon: "error", text: "La solicitud para obtener la ubicación del usuario expiró." });
          break;
        case e.UNKNOWN_ERROR:
          Swal.fire({ icon: "error", text: "Ocurrió un error desconocido." });
      }
    }
  
    if ("on" == c && "on" != a && -1 == document.cookie.indexOf("wcmlim_nearby_location")) {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(handleGeolocationSuccess, handleGeolocationError);
      }
    }
  
    if ("on" == s) {
      if ("on" == n) {
        e(".select_location-wrapper").show();
      } else {
        e(".select_location-wrapper").hide();
      }
  
      if (e("body").hasClass("logged-in")) {
        if (e("body").hasClass("product-template-default")) {
          let selectedLocation = e("#select_location").val();
          let stockStatus = e(".stock").hasClass("available-on-backorder");
  
          if (-1 == selectedLocation || selectedLocation == eN("wcmlim_selected_location")) {
            e(`#select_location option[value="${eN("wcmlim_selected_location")}"]`).prop("selected", !0);
  
            if (stockStatus) {
              return;
            }
  
            if (em == multi_inventory.soldout) {
              e(".stock").removeClass("in-stock").addClass("out-of-stock");
              let stockMessage = `Agotado en la ubicación ${er[0]}`;
              e(".site-content .woocommerce").append(`<ul class="woocommerce-error"><li>${stockMessage}</li></ul>`);
              e("#nm-shop-notices-wrap").append(`<ul class="nm-shop-notice woocommerce-error"><li>${stockMessage}</li></ul>`);
              e(".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .compare, .stock").remove();
            }
          }
        }
      }
    }
  });
  