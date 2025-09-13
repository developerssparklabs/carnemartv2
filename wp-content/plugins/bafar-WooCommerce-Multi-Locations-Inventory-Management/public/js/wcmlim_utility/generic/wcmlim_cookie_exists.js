import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_localization from "./wcmlim_localization.js";
import * as alies_CommonFunction from "./wcmlim_common_functions.js";

var localization = alies_localization.wcmlim_localization();

export function CookieExists() {
  const cookieExists = alies_getcookies.getCookie("wcmlim_selected_location");
  if (cookieExists) {
    const cookiregid = alies_getcookies.getCookie(
      "wcmlim_selected_location_regid"
    );
    jQuery(
      'select[name="wcmlim_change_sl_to"] option[value="' + cookiregid + '"]'
    ).attr("selected", "selected");
    jQuery('select[name="wcmlim_change_sl_to"]').trigger("change");
    const selectedLQ = jQuery("#select_location")
      .find("option:selected")
      .attr("data-lc-qty");
    const BoS = jQuery("#backorderAllowed").val();
    if (jQuery(".variation_id").length == 0) {
      if (BoS == 0) {
        if (selectedLQ) {
          document.getElementById("lc_qty").value = selectedLQ;
        }
      }
    }
    const regularPrice = jQuery("#select_location")
      .find("option:selected")
      .attr("data-lc-regular-price");
    const salePrice = jQuery("#select_location")
      .find("option:selected")
      .attr("data-lc-sale-price");
    const svalue = jQuery("#select_location").find("option:selected").text();
    const sLValue = jQuery("#select_location").find("option:selected").val();
    const stockDisplay = jQuery("#wcstdis_format").val();
    jQuery(document.body).trigger("wc_fragments_refreshed");
    const clasname = jQuery("#wcmlim-change-lc-select")
      .find("option:selected")
      .attr("class");
    jQuery("body").removeClass(function (index, css) {
      return (css.match(/\bwclimloc_\S+/g) || []).join(" ");
    });
    var body = document.body;
    body.classList.add(clasname);
    let undefinedclass = jQuery("body").hasClass("undefined");
    if (undefinedclass == true) {
      jQuery(body).removeClass("undefined");
      jQuery(body).addClass("wclimloc_none");
    }
    if (
      jQuery("#globMsg, #losm, #seloc,#locsoldImg, #locstockImg").length > 0
    ) {
      jQuery("#globMsg, #losm, #seloc,#locsoldImg, #locstockImg").remove();
    }
    /**Radio option */
    if (sLValue) {
      jQuery(
        ".rselect_location input[name=select_location][value=" + sLValue + "]"
      ).prop("checked", true);
      jQuery(
        ".rlist_location input[name=wcmlim_change_lc_to][value=" + sLValue + "]"
      ).prop("checked", true);
    }
    if (svalue) {
      const selA = svalue.split("-");
      if (1 in selA) {
        const selS = selA[1].split(":");
        const stockStatus = selS[0].trim();

        if (
          !stockDisplay ||
          stockDisplay == "no_amount" ||
          stockDisplay == "low_amount"
        ) {
          if (stockStatus == "Out of Stock" || selectedLQ <= 1) {
            if (sLValue == "-1") {
              jQuery(
                '<div id="load" style="display:none"><img src="//s.svgbox.net/loaders.svg?fill=maroon&ic=tail-spin" style="width:33px"></div>'
              ).appendTo(".Wcmlim_nextloc_label");
              jQuery("#load").show();
              jQuery.ajax({
                type: "POST",
                url: localization.ajaxurl,
                data: {
                  action: "wcmlim_closest_location",
                  selectedLocationId: sLValue,
                },
                dataType: "json",
                success(res) {
                  if (jQuery.trim(res.status) == "true") {
                    if (localization.nextloc == "on") {
                      jQuery(".next_closest_location_detail").html("");
                      jQuery(".next_closest_location_detail").show();
                      jQuery("#load").hide();
                      jQuery(
                        `<button id="" class="Wcmlim_accept_btn"><i class="fa fa-check"></i>Accept</button><input type="hidden" class="nextAcceptLoc" value="${res.secNearLocKey}" />`
                      ).appendTo(".Wcmlim_nextloc_label");
                      jQuery(
                        `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()} <br />
                      <span class="next_km">( ` +
                          res.fetch_origin_distance +
                          `)</span>
                      </div>`
                      ).appendTo(".selected_location_detail");
                      jQuery(
                        `<strong>` +
                          localization.NextClosestinStock +
                          `: <br/> ` +
                          res.secNearLocAddress +
                          ` <span class="next_km">( ` +
                          res.secNearStoreDisUnit +
                          `)</span></strong>`
                      ).appendTo(".next_closest_location_detail");

                      if (jQuery(".Wcmlim_accept_btn").length) {
                        jQuery(".Wcmlim_accept_btn").click(() => {
                          jQuery("#select_location")
                            .val(res.secNearLocKey)
                            .trigger("change");
                          jQuery(".Wcmlim_accept_btn").remove();
                        });
                      }

                      if (jQuery(".postcode-location-distance").length) {
                        jQuery(".postcode-location-distance").hide();
                      }
                    }
                  }
                  jQuery(".wclimlocsearch").hide();
                },
                error(res) {
                  jQuery("#load").hide();
                },
              });
            }
            if (
              jQuery("#globMsg, #losm, #seloc, #locsoldImg, #locstockImg")
                .length > 0
            ) {
              jQuery(
                "#globMsg, #losm, #seloc, #locsoldImg, #locstockImg"
              ).remove();
            }
            // jQuery( "<p id='losm'>" + localization.soldout + "</p>" ).insertAfter(
            //   ".Wcmlim_prefloc_sel"
            // );
            jQuery(
              `<div id="locsoldImg" class="Wcmlim_over_stock"><i class="fa fa-times"></i>${localization.soldout}</div>`
            ).appendTo(".Wcmlim_locstock");
            jQuery(
              ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compare"
            ).show();
          } else {
            if (
              jQuery("#globMsg, #losm, #seloc, #locsoldImg, #locstockImg")
                .length > 0
            ) {
              jQuery(
                "#globMsg, #losm, #seloc, #locsoldImg, #locstockImg"
              ).remove();
            }

            if (stockDisplay == "no_amount") {
              if (
                localization.listmode != "on" ||
                localization.listmode == null
              ) {
                jQuery(
                  "<p id='globMsg'> " + localization.instock + "</p>"
                ).insertAfter(".Wcmlim_prefloc_sel");
              }
              jQuery(
                `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
              ).appendTo(".selected_location_detail");
              var kmval = jQuery("#product-location-distance").val();
              if (kmval) {
                jQuery(".postcode-location-distance").html(
                  `<i class="fa fa-map-marker-alt"></i> ${kmval} ` +
                    localization.away
                );
              }
            } else {
              if (jQuery("#locsoldImg, #locstockImg").length > 0) {
                jQuery("#locsoldImg, #locstockImg").remove();
              }
              if (selectedLQ) {
                if (
                  localization.listmode != "on" ||
                  localization.listmode == null
                ) {
                  jQuery(
                    `<p id='globMsg'><b>${selectedLQ} </b> ` +
                      localization.instock +
                      `</p>`
                  ).insertAfter(".Wcmlim_prefloc_sel");
                }
                jQuery(
                  `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
                ).appendTo(".selected_location_detail");
                var kmval = jQuery("#product-location-distance").val();
                if (kmval) {
                  jQuery(".postcode-location-distance").html(
                    `<i class="fa fa-map-marker-alt"></i> ${kmval} away`
                  );
                }
                jQuery(
                  `<div id="locstockImg" class="Wcmlim_have_stock 33"><i class="fa fa-check"></i>${localization.instock}</div>`
                ).appendTo(".Wcmlim_locstock");
              }
            }
          }
        }
        if (BoS == 0) {
          if (selectedLQ) {
            jQuery(".qty").attr({ max: selectedLQ });
            document.getElementById("lc_qty").value = selectedLQ;
          }
        }
      }
    }

    if (localization.enable_price == "on") {
      if (typeof regularPrice !== "undefined" && regularPrice.length > 9) {
        const grp = alies_CommonFunction.extractMoney(regularPrice);
        var gpp = grp.amount;
      }
      if ((regularPrice || salePrice) && gpp > 0) {
        if (salePrice.length > 9) {
          jQuery(".price.wcmlim_product_price").html(
            `<del>${regularPrice}</del><ins>${salePrice}</ins>`
          );
        } else if (salePrice.length == 9) {
          jQuery(".price.wcmlim_product_price").html(regularPrice);
        } else {
          jQuery(".price.wcmlim_product_price").html(regularPrice);
        }
        document.getElementById("lc_regular_price").value = regularPrice;
        document.getElementById("lc_sale_price").value = salePrice;
      }
    }
  } else {
    jQuery("body").removeClass(function (index, css) {
      return (css.match(/\bwclimloc_\S+/g) || []).join(" ");
    });
    var body = document.body;
    body.classList.add("wclimloc_none");
  }
}
