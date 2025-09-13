import * as alies_localization from "./wcmlim_localization.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";
import * as alies_CommonFunction from "./wcmlim_common_functions.js";
var localization = alies_localization.wcmlim_localization();

export function submitPostcodeProduct(loader) {
  let lat;
  let lng;

  var elem_Glob_return = alies_CommonFunction.elementIdGlobalFn();
  if (elem_Glob_return) {
    lat = elem_Glob_return.lat;
    lng = elem_Glob_return.lng;
  }

  var elem_return = alies_CommonFunction.elementIdFn();
  if (elem_return) {
    lat = elem_return.lat;
    lng = elem_return.lng;
  }

  jQuery(document).on("click", "#submit_postcode_product", function (e) {
    e.preventDefault();
    const postal_code = jQuery(".class_post_code").val();
    if (postal_code == "") {
      Swal.fire({
        icon: "error",
        text: "Please Enter Location!",
      });
      return true;
    }

    //const product_id = jQuery( "#postal-product-id" ).val();
    if (jQuery(".variation_id").length) {
      var product_id = "";
      var variation_id = jQuery("input.variation_id").val();
    } else {
      var product_id = jQuery(".single_add_to_cart_button").val();
      var variation_id = "";
    }

    const globalPin = jQuery("#global-postal-check").val();
    const BoStatus = jQuery("#backorderAllowed").val();
    if (jQuery('[name="post_code"]', this).val() == "") {
      jQuery(this).addClass("wcmlim-shaker");
      setTimeout(() => {
        jQuery(".postcode-checker")
          .find(".wcmlim-shaker")
          .removeClass("wcmlim-shaker");
      }, 600);
      return;
    }

    jQuery(".postcode-checker-response").html(loader);
    jQuery
      .ajax({
        url: localization.ajaxurl,
        type: "post",
        data: {
          postcode: postal_code,
          product_id: product_id,
          variation_id: variation_id,
          globalPin,
          lat,
          lng,
          action: "wcmlim_closest_location",
        },
        dataType: "json",
        success(response) {
         
          if (localization.auto_billing_address == "on") {
            if (response.address) {
              // Initialize variables for location details
              let city = "";
              let statecode = "";
              let zipCode = "";
              let country = "";

              // Create a geocoder instance
              const geocoder = new google.maps.Geocoder();

              // Geocode the address
              geocoder.geocode(
                { address: response.address },
                function (results, status) {
                  if (status === google.maps.GeocoderStatus.OK) {
                    const location = results[0];
                    if (location) {
                      // Extract country, state, ZIP code, and city information
                      for (const component of location.address_components) {
                        for (const type of component.types) {
                          switch (type) {
                            case "country":
                              country = component.short_name;
                              break;
                            case "administrative_area_level_1":
                              statecode = component.short_name;
                              break;
                            case "postal_code":
                              zipCode = component.short_name;
                              break;
                            case "locality":
                              city = component.short_name;
                              break;
                          }
                        }
                      }
                      alies_setcookies.setcookie("wcmlim_city", city);
                      alies_setcookies.setcookie("wcmlim_statecode", statecode);
                      alies_setcookies.setcookie("wcmlim_zipCode", zipCode);
                      alies_setcookies.setcookie("wcmlim_countryCode", country);
                      // Make the AJAX request to update user data
                      alies_CommonFunction.updateUserLocation(
                        city,
                        statecode,
                        zipCode,
                        country
                      );
                    }
                  } else {
                  //  console.log("Geocoding failed with status: " + status);
                  }
                }
              );
            }
          }

          if (jQuery.trim(response.status) == "true") {
            jQuery(".postcode-checker-change").show();
            jQuery(".Wcmlim_loc_label").show();
            jQuery(".postcode-checker-div")
              .removeClass("postcode-checker-div-show")
              .addClass("postcode-checker-div-hide");
            jQuery(".postcode-checker-response").html(
              `<i class="fa fa-search"></i> ${postal_code}`
            );
            const glocunit = response.loc_dis_unit;
            const locationCookie = alies_getcookies.getCookie(
              "wcmlim_selected_location"
            );

            if (locationCookie == null || locationCookie == "undefined") {
              // do cookie doesn't exist stuff;
              var gLocation = response.loc_key;
              jQuery("#wcmlim-change-lc-select")
                .find(":selected")
                .removeAttr("selected");
              jQuery(".rselect_location input[name=select_location]").prop(
                "checked",
                false
              );
              jQuery(".rlist_location input[name=wcmlim_change_lc_to]").prop(
                "checked",
                false
              );
              jQuery("#select_location")
                .find(":selected")
                .removeAttr("selected");
              jQuery("#wcmlim-change-lc-select")
                .find(`option[value="${gLocation}"]`)
                .prop("selected", true);
              jQuery(`#select_location option[value='${gLocation}']`).prop(
                "selected",
                true
              );
              jQuery(
                ".rselect_location input[name=select_location][value=" +
                  gLocation +
                  "]"
              ).prop("checked", true);
              jQuery(
                ".rlist_location input[name=wcmlim_change_lc_to][value=" +
                  gLocation +
                  "]"
              ).prop("checked", true);
              alies_setcookies.setcookie("wcmlim_selected_location", gLocation);

              if (
                jQuery("#globMsg, #seloc, #losm, #locsoldImg, #locstockImg")
                  .length
              ) {
                jQuery(
                  "#globMsg, #seloc, #losm, #locsoldImg, #locstockImg"
                ).remove();
              }

              if (jQuery(".Wcmlim_accept_btn").length > 0) {
                jQuery(".Wcmlim_accept_btn").remove();
              }

              var stockDisplay = jQuery("#wcstdis_format").val();
              var optText = jQuery(
                `#select_location option[value='${gLocation}']`
              ).text();
              var stockQt = jQuery("#select_location")
                .find("option:selected")
                .attr("data-lc-qty");

              if (optText) {
                var selA = optText.split("-");
                if (1 in selA) {
                  if (
                    !stockDisplay ||
                    stockDisplay == "no_amount" ||
                    stockDisplay == "low_amount"
                  ) {
                    if (stockQt <= 0) {
                      if (
                        jQuery(
                          "#locsoldImg, #locstockImg, #losm, #seloc, #globMsg"
                        ).length > 0
                      ) {
                        jQuery(
                          "#locsoldImg, #locstockImg, #losm, #seloc, #globMsg"
                        ).remove();
                      }

                      jQuery(
                        "<p id='losm'>" + localization.soldout + " </p>"
                      ).insertAfter(".Wcmlim_prefloc_sel");
                      jQuery(
                        `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                      ).appendTo(".selected_location_detail");
                      jQuery(
                        ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compare"
                      ).hide();
                    } else {
                      if (
                        jQuery(
                          "#locsoldImg, #locstockImg, #losm, #seloc, #globMsg"
                        ).length > 0
                      ) {
                        jQuery(
                          "#locsoldImg, #locstockImg, #losm, #seloc, #globMsg"
                        ).remove();
                      }

                      if (stockDisplay == "no_amount") {
                        jQuery(
                          "<p id='globMsg'> " + localization.instock + " </p>"
                        ).insertAfter(".Wcmlim_prefloc_sel");
                        jQuery(
                          `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                        ).appendTo(".selected_location_detail");
                      } else {
                        jQuery(
                          `<p id='globMsg'><b> ${stockQt} </b> ` +
                            localization.instock +
                            `</p>`
                        ).insertAfter(".Wcmlim_prefloc_sel");
                        jQuery(
                          `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                        ).appendTo(".selected_location_detail");
                      }
                      jQuery(
                        ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compare"
                      ).show();
                    }
                  }

                  if (BoStatus == 0) {
                    if (stockQt) {
                      jQuery(".qty").attr({ max: stockQt });
                      document.getElementById("lc_qty").value = stockQt;
                    }
                  }
                }
              }

              if (stockQt <= 0 && glocunit != null) {
                jQuery(
                  `<div id="locsoldImg" class="Wcmlim_over_stock"><i class="fa fa-times"></i>${localization.soldout}</div>`
                ).appendTo(".Wcmlim_locstock");
                jQuery(".postcode-location-distance").show();
                jQuery(".postcode-location-distance").html(
                  `<i class="fa fa-map-marker-alt"></i> ${glocunit} ` +
                    localization.away
                );

                if (localization.nextloc == "on") {
                  jQuery(".next_closest_location_detail").html("");
                  jQuery(".next_closest_location_detail").show();
                  jQuery(
                    `<button id="" class="Wcmlim_accept_btn"><i class="fa fa-check"></i>Accept</button><input type="hidden" class="nextAcceptLoc" value="${response.secNearLocKey}" />`
                  ).appendTo(".Wcmlim_nextloc_label");
                  jQuery(
                    `<strong>` +
                      localization.NextClosestinStock +
                      `: <br/> ` +
                      response.secNearLocAddress +
                      ` <span class="next_km">(` +
                      response.secNearStoreDisUnit +
                      `)</span></strong>`
                  ).appendTo(".next_closest_location_detail");

                  if (jQuery(".Wcmlim_accept_btn").length) {
                    jQuery(".Wcmlim_accept_btn").click(() => {
                      jQuery("#select_location")
                        .val(response.secNearLocKey)
                        .trigger("change");
                      jQuery(".Wcmlim_accept_btn").remove();
                    });
                  }

                  if (jQuery(".postcode-location-distance").length) {
                    jQuery(".postcode-location-distance").hide();
                  }
                }
              } else if (stockQt > 0 && glocunit != null) {
                jQuery(
                  `<div id="locstockImg" class="Wcmlim_have_stock"><i class="fa fa-check"></i>${localization.instock}</div>`
                ).appendTo(".Wcmlim_locstock");
                jQuery(".postcode-location-distance").show();
                jQuery(".postcode-location-distance").html(
                  `<i class="fa fa-map-marker-alt"></i> ${glocunit} ` +
                    localization.away
                );

                if (jQuery(".next_closest_location_detail").length) {
                  jQuery(".next_closest_location_detail").hide();
                }
              } else if (
                (stockQt == null && glocunit != null) ||
                glocunit == null
              ) {
                jQuery("#locsoldImg, #locstockImg").remove();
                jQuery(".Wcmlim_accept_btn").remove();
                jQuery(".next_closest_location_detail").html("");
                jQuery(
                  '<div id="locstockImg" class="Wcmlim_noStore">No Store Found</div>'
                ).appendTo(".Wcmlim_messageerror");
              } else {
                jQuery("#locsoldImg, #locstockImg").remove();
                jQuery(
                  '<div id="locstockImg" class="Wcmlim_noStore">Please check the location</div>'
                ).appendTo(".Wcmlim_messageerror");

                if (jQuery(".next_closest_location_detail").length) {
                  jQuery(".next_closest_location_detail").hide();
                }
              }
            } else {
              var gLocation = response.loc_key;
              // if(locationCookie != $.trim(response.location)){
              jQuery("#wcmlim-change-lc-select")
                .find(":selected")
                .removeAttr("selected");
              jQuery("#select_location")
                .find(":selected")
                .removeAttr("selected");
              jQuery(".rselect_location input[name=select_location]").prop(
                "checked",
                false
              );
              jQuery(".rlist_location input[name=wcmlim_change_lc_to]").prop(
                "checked",
                false
              );
              jQuery("#wcmlim-change-lc-select")
                .find(`option[value="${gLocation}"]`)
                .prop("selected", true);
              jQuery(`#select_location option[value='${gLocation}']`).prop(
                "selected",
                true
              );
              jQuery(
                ".rselect_location input[name=select_location][value=" +
                  gLocation +
                  "]"
              ).prop("checked", true);
              jQuery(
                ".rlist_location input[name=wcmlim_change_lc_to][value=" +
                  gLocation +
                  "]"
              ).prop("checked", true);
              alies_setcookies.setcookie("wcmlim_selected_location", gLocation);

              if (localization.isClearCart == "on") {
                alies_CommonFunction.clearCart(e);
              }

              if (
                jQuery("#globMsg, #seloc, #losm, #locsoldImg, #locstockImg")
                  .length
              ) {
                jQuery(
                  "#globMsg, #seloc, #losm, #locsoldImg, #locstockImg"
                ).remove();
              }

              if (jQuery(".Wcmlim_accept_btn").length > 0) {
                jQuery(".Wcmlim_accept_btn").remove();
              }

              var stockDisplay = jQuery("#wcstdis_format").val();
              var optText = jQuery(
                `#select_location option[value='${gLocation}']`
              ).text();
              var stockQt = jQuery("#select_location")
                .find("option:selected")
                .attr("data-lc-qty");

              if (optText) {
                var selA = optText.split("-");
                if (1 in selA) {
                  if (
                    !stockDisplay ||
                    stockDisplay == "no_amount" ||
                    stockDisplay == "low_amount"
                  ) {
                    if (stockQt <= 0 && BoStatus == 0) {
                      if (
                        jQuery(
                          "#globMsg, #seloc, #losm, #locsoldImg, #locstockImg"
                        ).length
                      ) {
                        jQuery(
                          "#globMsg, #seloc, #losm, #locsoldImg, #locstockImg"
                        ).remove();
                      }
                      jQuery(
                        "<p id='losm'>" + localization.soldout + "</p>"
                      ).insertAfter(".Wcmlim_prefloc_sel");
                      jQuery(
                        `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                      ).appendTo(".selected_location_detail");
                      jQuery(
                        ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compare"
                      ).hide();
                    } else {
                      if (
                        jQuery(
                          "#globMsg, #seloc, #losm, #locsoldImg, #locstockImg"
                        ).length
                      ) {
                        jQuery(
                          "#globMsg, #seloc, #losm, #locsoldImg, #locstockImg"
                        ).remove();
                      }

                      if (stockDisplay == "no_amount") {
                        jQuery(
                          "<p id='globMsg'> " + localization.instock + " </p>"
                        ).insertAfter(".Wcmlim_prefloc_sel");
                        jQuery(
                          `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                        ).appendTo(".selected_location_detail");
                      } else {
                        if (typeof stockQt == "undefined") {
                          jQuery(
                            `<p id='globMsg'>${localization.instock}</p>`
                          ).insertAfter(".Wcmlim_prefloc_sel");
                        } else {
                          jQuery(
                            `<p id='globMsg'><b> ${stockQt} </b>${localization.instock}</p>`
                          ).insertAfter(".Wcmlim_prefloc_sel");
                        }
                        jQuery(
                          `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                        ).appendTo(".selected_location_detail");
                      }
                      jQuery(
                        ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compares"
                      ).show();
                    }
                  }

                  if (BoStatus == 0) {
                    if (stockQt) {
                      jQuery(".qty").attr({ max: stockQt });
                      document.getElementById("lc_qty").value = stockQt;
                    }
                  }
                }
              }

              if (stockQt <= 0 && glocunit != null && BoStatus == 0) {
                jQuery(
                  `<div id="locsoldImg" class="Wcmlim_over_stock"><i class="fa fa-times"></i>${localization.soldout}</div>`
                ).appendTo(".Wcmlim_locstock");
                jQuery(".postcode-location-distance").show();
                jQuery(".postcode-location-distance").html(
                  `<i class="fa fa-map-marker-alt"></i> ${glocunit} ` +
                    localization.away
                );

                if (localization.nextloc == "on") {
                  jQuery(".next_closest_location_detail").html("");
                  jQuery(".next_closest_location_detail").show();
                  jQuery(
                    `<button id="" class="Wcmlim_accept_btn"><i class="fa fa-check"></i>Accept</button><input type="hidden" class="nextAcceptLoc" value="${response.secNearLocKey}" />`
                  ).appendTo(".Wcmlim_nextloc_label");
                  jQuery(
                    `<strong>` +
                      localization.NextClosestinStock +
                      `: <br/> ` +
                      response.secNearLocAddress +
                      `<span class="next_km">(` +
                      response.secNearStoreDisUnit +
                      `)</span></strong>`
                  ).appendTo(".next_closest_location_detail");
                  if (jQuery(".Wcmlim_accept_btn").length) {
                    jQuery(".Wcmlim_accept_btn").click(() => {
                      jQuery("#select_location")
                        .val(response.secNearLocKey)
                        .trigger("change");
                      jQuery(".Wcmlim_accept_btn").remove();
                    });
                  }
                  if (jQuery(".postcode-location-distance").length) {
                    jQuery(".postcode-location-distance").hide();
                  }
                }
              } else if (stockQt > 0 && glocunit != null) {
                jQuery(
                  `<div id="locstockImg" class="Wcmlim_have_stock"><i class="fa fa-check"></i>${localization.instock}</div>`
                ).appendTo(".Wcmlim_locstock");
                jQuery(".postcode-location-distance").show();

                jQuery(".postcode-location-distance").html(
                  `<i class="fa fa-map-marker-alt"></i> ${glocunit} ` +
                    localization.away
                );
                if (jQuery(".next_closest_location_detail").length) {
                  jQuery(".next_closest_location_detail").hide();
                }
              } else if (
                (stockQt == null && glocunit != null) ||
                glocunit == null
              ) {
                jQuery("#locsoldImg, #locstockImg").remove();
                jQuery(".Wcmlim_accept_btn").remove();
                jQuery(
                  `<div id="locstockImg" class="Wcmlim_have_stock"><i class="fa fa-check"></i>${localization.instock}</div>`
                ).appendTo(".Wcmlim_locstock");
              } else {
                jQuery("#locsoldImg, #locstockImg").remove();
                if (jQuery(".next_closest_location_detail").length) {
                  jQuery(".next_closest_location_detail").hide();
                }
              }
            }
          }
          var postcode = jQuery(".class_post_code").val();
          if (gLocation == null) {
            jQuery(
              '<div id="locstockImg" class="Wcmlim_noStore"><b>No location Near ' +
                postcode +
                "</b> </div>"
            ).appendTo(".Wcmlim_messageerror");
          }
          jQuery(".wclimlocsearch").hide();
        },
        error: function (data, textStatus, errorThrown) {
          jQuery(".postcode-checker-response").empty();
        },
      })
      .done((response) => {
        // if bacorder not allowed update max value of quantity field
        if (response.backorder === false) {
          const stockAvailabe = response.stock_in_location;
          jQuery(".qty").attr({ max: stockAvailabe });
        }
      });
  });
}
