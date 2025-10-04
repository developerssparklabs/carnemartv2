import * as alies_localization from "./wcmlim_localization.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";
import * as alies_showPosition from "./wcmlim_show_possition.js";

var localization = alies_localization.wcmlim_localization();

export function updateUserLocation(city, statecode, zipCode, country) {
  alies_setcookies.setcookie("wcmlim_city", city);
  alies_setcookies.setcookie("wcmlim_statecode", statecode);
  alies_setcookies.setcookie("wcmlim_zipCode", zipCode);
  alies_setcookies.setcookie("wcmlim_countryCode", country);
  // Make an AJAX request to update the user's location data
  jQuery.ajax({
    type: "POST",
    url: localization.ajaxurl, // Use the WordPress AJAX URL
    data: {
      action: "wcmlim_order_review", // WordPress AJAX action name
      city: city,
      state: statecode,
      zipCode: zipCode,
      country: country,
    },
    success: function (response) {
      // Handle the response, e.g., display a success message
      console.log("User city updated successfully");
    },
    error: function (xhr, textStatus, errorThrown) {
      console.error("Error: " + textStatus);
    },
  });
}

export function clearCart(e) {
  jQuery(".single_add_to_cart_button").prop("disabled", true);
  jQuery(".wcmlim_cart_valid_err").remove();
  jQuery(
    "<div class='wcmlim_cart_valid_err'><center><i class='fas fa-spinner fa-spin'></i></center></div>"
  ).insertAfter(".Wcmlim_loc_label");
  jQuery(document.body).trigger("wc_fragments_refreshed");
  jQuery.ajax({
    type: "POST",
    url: localization.ajaxurl,
    data: {
      action: "wcmlim_ajax_cart_count",
    },
    success(res) {
      var ajaxcartcount = JSON.parse(JSON.stringify(res));
      var value = jQuery(e.target).val();
      var cck_selected_location = alies_getcookies.getCookie(
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
            ).insertBefore("#lc_regular_price");
          } else {
            jQuery(".wcmlim_cart_valid_err").remove();
            jQuery(".single_add_to_cart_button").prop("disabled", false);
          }
        }
      } else {
        jQuery(".wcmlim_cart_valid_err").remove();
        jQuery(".single_add_to_cart_button").prop("disabled", false);
      }
    },
  });
}

export function hideDropdown() {
  if (localization.hideDropdown == "on") {
    jQuery(document).on("click", ".variations", function (e) {
      e.preventDefault();
      jQuery(".in-stock").hide();
      jQuery(".qty, .quantity").hide();
      jQuery(".single_add_to_cart_button").show();
      jQuery(".losm").hide();

      var locationCookie = alies_getcookies.getCookie(
        "wcmlim_selected_location_termid"
      );
      var product_id = jQuery("input.variation_id").val();
   
      if (locationCookie != "" && product_id != "0" && product_id != "") {
        jQuery.ajax({
          url: localization.ajaxurl,
          type: "POST",
          data: {
            action: "action_variation_dropdown",
            locationCookie: locationCookie,
            product_id: product_id,
          },
          success(response) {
            if (response > 0) {
              jQuery(".single_add_to_cart_button").show();
              jQuery(".qty, .quantity").show();
              jQuery(".in-stock").show();
              jQuery(".losm").hide();
            } else {
              jQuery(".single_add_to_cart_button").show();
              jQuery(".qty, .quantity, .in-stock").show();
              jQuery(".losm").hide();
            }
          },
        });
      }
    });
  }
}

export function post_code_checker_common(class_name) {
  if (jQuery("." + class_name).val()) {
    jQuery(".postcode-checker-change").show();
    jQuery(".postcode-checker-div")
      .removeClass("postcode-checker-div-show")
      .addClass("postcode-checker-div-hide");
    jQuery(".postcode-checker-response").html(
      `<i class="fa fa-search"></i>${jQuery("." + class_name).val()}`
    );
  }
}

export function wcmlim_locwid_dd() {
  if (jQuery(".wcmlim_locwid_dd").length > 0) {
    if (localization.widget_select_type == "simple") {
      jQuery(".WCMLIM_Widget").delegate(
        ".wcmlim_locwid_dd",
        "change",
        function () {
          var se = jQuery(this).closest("select").val();
          alies_setcookies.setcookie("wcmlim_widget_chosenlc", se);
          jQuery(this).closest("form").submit();
        }
      );
    }

    if (localization.widget_select_type == "multi") {
      jQuery(".wcmlim_locwid_dd option[value='-1']").remove();
      jQuery(".wcmlim_locwid_dd").chosen({ width: "100%" });
      jQuery(".wcmlim_submit_location_form").click(function () {
        var sem = jQuery(".wcmlim_locwid_dd").val();
        alies_setcookies.setcookie("wcmlim_widget_chosenlc", sem);
        jQuery(this).closest("form").submit();
      });

      jQuery(".wcmlim_reset_location_form").click(function () {
        //reset location and clear filter
        alies_setcookies.setcookie("wcmlim_widget_chosenlc", "");
        jQuery(".wcmlim_locwid_dd").val("");
        jQuery(".wcmlim_locwid_dd").trigger("chosen:updated");
        
        jQuery(this).closest("form").submit();
      });
    }
  }
}

export function isLocationsGroupFn() {
  if (localization.isLocationsGroup == "on") {
    
    const regidExists = alies_getcookies.getCookie("wcmlim_selected_location_regid");
    const termidExists = alies_getcookies.getCookie("wcmlim_selected_location");
    if (regidExists && termidExists) {
      jQuery("#wcmlim-change-sl-select").prop("disabled", true);
      jQuery("#wcmlim-change-lc-select").prop("disabled", true);
      jQuery("#wcmlim-change-sl-select option[value=" + regidExists + "]").prop(
        "selected",
        true
      );
      
      jQuery.ajax({
        type: "POST",
        url: localization.ajaxurl,
        data: {
          selectedstoreValue: regidExists,
          termidExists: termidExists,
          action: "wcmlim_drop2_location",
        },
        dataType: "json",
        success(data) {
          var location_group = "";
          jQuery(".wcmlim-lc-select").empty();
          jQuery(".wcmlim_lcselect").empty();
          var locatdata = JSON.parse(JSON.stringify(data));
          if (locatdata) {
            jQuery(".wcmlim-lc-select").prepend(
              `<option value="-1"  >Seleccionar</option>`
            );
            jQuery(".wcmlim_lcselect").prepend(
              `<option value="-1"  >Seleccionar</option>`
            );
            jQuery.each(data, function (i, value) {
              var name = value.wcmlim_areaname;
              location_group = value.location_storeid;
              if (name == null || name == "") {
                name = value.location_name;
              }
              var seled = value.selected;
              if (seled == value.vkey) {
                jQuery("<option></option>")
                  .attr("value", value.vkey)
                  .text(name)
                  .attr("class", value.classname)
                  .attr("selected", "selected")
                  .attr("data-lc-storeid", value.location_storeid)
                  .attr("data-lc-name", name)
                  .attr("data-lc-loc", value.location_slug)
                  .attr("data-lc-term", value.term_id)
                  .appendTo(".wcmlim-lc-select");
                jQuery("<option></option>")
                  .attr("value", value.vkey)
                  .text(name)
                  .attr("class", value.classname)
                  .attr("selected", "selected")
                  .attr("data-lc-storeid", value.location_storeid)
                  .attr("data-lc-name", name)
                  .attr("data-lc-loc", value.location_slug)
                  .attr("data-lc-term", value.term_id)
                  .appendTo(".wcmlim_lcselect");
              } else {
                jQuery("<option></option>")
                  .attr("value", value.vkey)
                  .text(name)
                  .attr("class", value.classname)
                  .attr("data-lc-storeid", value.location_storeid)
                  .attr("data-lc-name", name)
                  .attr("data-lc-loc", value.location_slug)
                  .attr("data-lc-term", value.term_id)
                  .appendTo(".wcmlim-lc-select");
                jQuery("<option></option>")
                  .attr("value", value.vkey)
                  .text(name)
                  .attr("class", value.classname)
                  .attr("data-lc-storeid", value.location_storeid)
                  .attr("data-lc-name", name)
                  .attr("data-lc-loc", value.location_slug)
                  .attr("data-lc-term", value.term_id)
                  .appendTo(".wcmlim_lcselect");
              }
            });
            jQuery(
              'select[name="wcmlim_change_sl_to"] option[value="' +
                location_group +
                '"]'
            ).attr("selected", "selected");
            jQuery("#wcmlim-change-sl-select").removeAttr("disabled");
            jQuery("#wcmlim-change-lc-select").removeAttr("disabled");
            jQuery("#wcmlim-change-lcselect").removeAttr("disabled");
          }
        },
        error(res) {},
      });
    }
  }
}

export function extractMoney(string) {
  const amount = string.match(/[0-9]+([,.][0-9]+)?/);
  const unit = string.replace(/[0-9]+([,.][0-9]+)?/, "");
  if (amount && unit) {
    return {
      amount: +amount[0].replace(",", "."),
      currency: unit,
    };
  }
  return null;
}

export function Restricted_Location() {
  if (localization.restricted == "on") {
    if (localization.showLocationInRestricted == "on") {
      jQuery(".select_location-wrapper").show();
      jQuery(".Wcmlim_container").show();
    } else {
      jQuery(".select_location-wrapper").hide();
      jQuery(".Wcmlim_container").hide();
    }

    if (jQuery("body").hasClass("logged-in")) {
      if (jQuery("body").hasClass("product-template-default")) {
        if (sessionStorage.getItem("rsula")) {
          jQuery(".select_location-wrapper").show();
          jQuery(".Wcmlim_container").show();
        } else {
          var sll = jQuery("#select_location").val();
          var slt = jQuery("#select_location option:selected").text();
          var sltlna = slt.split("-");
          if (sltlna.length > 1) {
            var sltlN = sltlna[0].trim();
            var sltlS = sltlna[1].trim();
          }
          var sc = alies_getcookies.getCookie("wcmlim_selected_location");

          if (sll == -1 || sll == sc) {
            jQuery(`#select_location  option[value="${sc}"]`).prop(
              "selected",
              true
            );
            var onBackOrder = jQuery(".stock").hasClass(
              "available-on-backorder"
            );
            if (onBackOrder) {
              return;
            }
            if (sltlS == localization.soldout || sltlna.length == 1) {
             
              jQuery(".stock").removeClass("in-stock").addClass("out-of-stock");
              var lsText = `Out of stock from ${sltlN} location`;

              if (typeof sltlN == "undefined") {
                jQuery(".site-content .woocommerce").append(
                  `<ul class="woocommerce-error" role="alert"><li>Out of stock from ${sltlna} location</li></ul>`
                );
                jQuery("#nm-shop-notices-wrap").append(
                  `<ul class="nm-shop-notice woocommerce-error" role="alert"><li>Out of stock from ${sltlna} location</li></ul>`
                );
              } else {
                jQuery(".site-content .woocommerce").append(
                  `<ul class="woocommerce-error" role="alert"><li>${lsText}</li></ul>`
                );
              }
              jQuery(
                ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .compare, .stock"
              ).remove();
            }
          }
        }
      }
    } else {
      if (jQuery("body").hasClass("product-template-default")) {
        var msgForUser = sessionStorage.getItem("rsnlc");
        if (msgForUser) {
          jQuery(
            ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compare, .variations_form"
          ).remove();
          jQuery(".product-main").find(".product-summary").append(msgForUser);
          jQuery("#nm-shop-notices-wrap").append(msgForUser);
          jQuery("#nm-shop-notices-wrap .notice").css({
            "text-align": "center",
            "padding-top": "25px",
          });
        }
      }
    }
  }
}

export function Hide_OOS_Product_Allover() {
  // This code is responsible for hide out of stock product from all site as per locations qty
  if (localization.wchideoosproduct == "yes") {
    const ThemesArray = [
      "theme-astra",
      "theme-flatsome",
      "theme-woodmart",
      "theme-xstore",
      "theme-kuteshop-elementor",
      "theme-kuteshop",
    ];
    ThemesArray.forEach((entry) => {
      if (
        (jQuery("body.home").hasClass(entry) && entry == "theme-flatsome") ||
        (jQuery("body.home").hasClass(entry) && entry == "theme-xstore")
      ) {
        jQuery("body.home")
          .find(".locsoldout")
          .parent()
          .parent()
          .parent()
          .parent()
          .remove();
      }
      if (
        (jQuery("body.single-product").hasClass(entry) &&
          entry == "theme-flatsome") ||
        (jQuery("body.single-product").hasClass(entry) &&
          entry == "theme-xstore")
      ) {
        jQuery("body.single-product")
          .find(".locsoldout")
          .parent()
          .parent()
          .parent()
          .parent()
          .remove();
      }

      if (
        (jQuery("body.home").hasClass(entry) && entry == "theme-astra") ||
        (jQuery("body.home").hasClass(entry) &&
          entry == "theme-kuteshop-elementor") ||
        (jQuery("body.home").hasClass(entry) && entry == "theme-kuteshop")
      ) {
        jQuery("body.home")
          .find(".locsoldout")
          .parent()
          .parent()
          .parent()
          .remove();
      }

      if (
        (jQuery("body.single-product").hasClass(entry) &&
          entry == "theme-astra") ||
        (jQuery("body.single-product").hasClass(entry) &&
          entry == "theme-kuteshop-elementor")
      ) {
        jQuery("body.single-product")
          .find(".locsoldout")
          .parent()
          .parent()
          .parent()
          .remove();
      }
    });
  }
}

export function autodetectFn() {
  if (
    localization.autoDetect == "on" &&
    localization.autodetect_by_maxmind != "on"
  ) {
    jQuery("#showMe").click(() => {
      alies_showPosition.showPosition();
    });

    jQuery(".elementIdGlobal, #elementIdGlobal").on("click", function () {
      jQuery(".wclimlocsearch").show();
    });

    jQuery(".elementIdGlobal, #elementIdGlobal").on("change", function () {
      jQuery(".wclimlocsearch").hide();
    });

    jQuery(".elementIdGlobal, #elementIdGlobal").on("input", function () {
      jQuery(".wclimlocsearch").hide();
    });

    jQuery(".currentLoc").click(() => {
      alies_showPosition.showPosition();
    });
  }
}

export function loaderhtml() {
  var loader;
  loader = '<div class="wcmlim-chase-wrapper">';
  loader += '<div class="wcmlim-chase">';
  loader += '<div class="wcmlim-chase-dot"></div>';
  loader += '<div class="wcmlim-chase-dot"></div>';
  loader += '<div class="wcmlim-chase-dot"></div>';
  loader += '<div class="wcmlim-chase-dot"></div>';
  loader += '<div class="wcmlim-chase-dot"></div>';
  loader += '<div class="wcmlim-chase-dot"></div>';
  loader += "</div>";
  loader += "</div>";

  return loader;
}

export function elementIdGlobalFn() {
  const el = document.getElementById("elementIdGlobal");
  if (el) {
    el.addEventListener("focus", (e) => {
      const input = document.getElementById("elementIdGlobal");
      const options = {};
      const autocomplete = new google.maps.places.Autocomplete(input, options);
      google.maps.event.addListener(autocomplete, "place_changed", () => {
        const place = autocomplete.getPlace();
        let __return = {
          lat: place.geometry.location.lat(),
          lng: place.geometry.location.lng(),
        };
        return __return;
      });
    });
  }
}

export function elementIdFn() {
  const le = document.getElementById("elementId");
  if (le) {
    if (localization.hideSuggest != "on") {
      le.addEventListener("focus", (e) => {
        const input = document.getElementById("elementId");
        const options = {};
        const autocomplete = new google.maps.places.Autocomplete(
          input,
          options
        );
        google.maps.event.addListener(autocomplete, "place_changed", () => {
          const place = autocomplete.getPlace();
          //var lat = place.geometry.location.lat();
          //var lng = place.geometry.location.lng();
          let __return = {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
          };
          return __return;
        });
      });
    }
  }
}

export function NearbyLocationFn() {
  var gnl = document.cookie;
  if (
    localization.autoDetect == "on" &&
    localization.autodetect_by_maxmind != "on"
  ) {
    if (gnl.search("wcmlim_nearby_location") == -1) {
      var dialogShown = localStorage.getItem("dialogShown");
      if (!dialogShown) {
        // alies_showPosition.showPosition();
      }
    }
  }
}
