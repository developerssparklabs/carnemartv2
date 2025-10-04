
import * as alies_localization from "./wcmlim_localization.js";
import * as alies_CommonFunction from "./wcmlim_common_functions.js";

var localization = alies_localization.wcmlim_localization();

export function select_location(e) {
  const selectedText = jQuery(e).find("option:selected").text();
  const selectedValue = jQuery(e).find("option:selected").val();
  const stockDisplay = jQuery("#wcstdis_format").val();
  const clasname = jQuery(e).find("option:selected").attr("class");
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
  if (selectedValue) {
    jQuery(
      ".rselect_location input[name=select_location][value=" +
        selectedValue +
        "]"
    ).prop("checked", true);
    jQuery(
      ".rlist_location input[name=wcmlim_change_lc_to][value=" +
        selectedValue +
        "]"
    ).prop("checked", true);
  }

  const stockQt = jQuery(e).find("option:selected").attr("data-lc-qty");
  var prId;
  if (jQuery(".variation_id").val()) {
    prId = jQuery(".variation_id").val();
  } else {
    prId = jQuery(".single_add_to_cart_button").val();
  }
  const boStatus = jQuery("#backorderAllowed").val();
  jQuery(".Wcmlim_loc_label").show();
  jQuery(".postcode-checker-change").trigger("click");
  jQuery(".postcode-location-distance").remove();
  if (
    jQuery("#globMsg, #seloc, #locsoldImg, #locstockImg, .Wcmlim_accept_btn")
      .length > 0
  ) {
    jQuery(
      "#globMsg, #seloc, #locsoldImg, #locstockImg, .Wcmlim_accept_btn"
    ).remove();
  }

  if (selectedText) {
    const selA = selectedText.split("-");
    if (1 in selA) {
      if (
        !stockDisplay ||
        stockDisplay == "no_amount" ||
        stockDisplay == "low_amount"
      ) {
        if (stockQt <= 0 && boStatus == 0) {
          if (stockQt == "") {
            jQuery("#globMsg, #seloc, #losm").remove();
            jQuery(".Wcmlim_loc_label").hide();
          } else {
            jQuery(
              '<div id="load" style="display:none"><img src="//s.svgbox.net/loaders.svg?fill=maroon&ic=tail-spin" style="width:33px"></div>'
            ).appendTo(".Wcmlim_nextloc_label");
            jQuery("#load").show();
            jQuery.ajax({
              type: "POST",
              url: localization.ajaxurl,
              data: {
                action: "wcmlim_closest_location",
                selectedLocationId: selectedValue,
                product_id: prId,
              },
              dataType: "json",
              success(res) {
                jQuery("#load").hide();
              
                if (jQuery.trim(res.status) == "true") {
                  if (localization.nextloc == "on") {
                    jQuery(".next_closest_location_detail").html("");
                    jQuery(".next_closest_location_detail").show();
                    jQuery("#load").hide();
                    jQuery(
                      `<button id="" class="Wcmlim_accept_btn"><i class="fa fa-check"></i>Accept</button><input type="hidden" class="nextAcceptLoc" value="${res.secNearLocKey}" />`
                    ).appendTo(".Wcmlim_nextloc_label");
                    jQuery(
                      `<strong>` +
                        localization.NextClosestinStock +
                        `: <br/> ` +
                        res.loc_address +
                        ` </strong>`
                    ).appendTo(".next_closest_location_detail");

                    if (jQuery(".Wcmlim_accept_btn").length) {
                      jQuery(".Wcmlim_accept_btn").click(() => {
                        jQuery("#select_location")
                          .val(res.loc_key)
                          .trigger("change");
                        jQuery(".Wcmlim_accept_btn").remove();
                      });
                    }

                    if (jQuery(".postcode-location-distance").length) {
                      jQuery(".postcode-location-distance").hide();
                    }
                    var kmval = res.loc_dis_unit;
                    if (kmval) {
                      jQuery(
                        "<div class='postcode-location-distance'> <i class='fa fa-map-marker-alt'></i>" +
                          kmval +
                          " </div>"
                      ).insertAfter(".selected_location_detail");
                      jQuery(".postcode-location-distance").show();
                    }
                  }
                }
                jQuery(".wclimlocsearch").hide();
              },
              error(res) {
                jQuery("#load").show();
              },
            });

            if (
              jQuery("#globMsg, #losm, #seloc, #locsoldImg, #locstockImg")
                .length > 0
            ) {
              jQuery(
                "#globMsg, #losm, #seloc, #locsoldImg, #locstockImg"
              ).remove();
            }

            jQuery("<p id='losm'>" + localization.soldout + "</p>").insertAfter(
              ".Wcmlim_prefloc_sel"
            );
            jQuery(
              `<div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle"></i>${selA[0].trim()}</div>`
            ).appendTo(".selected_location_detail");
            jQuery(
              `<div id="locsoldImg" class="Wcmlim_over_stock"><i class="fa fa-times"></i>${localization.soldout}</div>`
            ).appendTo(".Wcmlim_locstock");
            jQuery(
              ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button"
            ).hide();
          }
        } else {
          if (jQuery(".variation_id").length) {
            var product_id = "";
            var variation_id = jQuery("input.variation_id").val();
          } else {
            var product_id = jQuery(".single_add_to_cart_button").val();
            var variation_id = "";
          }
          const svalue = jQuery("#select_location")
            .find("option:selected")
            .text();
          const sLValue = jQuery("#select_location")
            .find("option:selected")
            .val();
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
              product_id: product_id,
              variation_id: variation_id,
            },
            dataType: "json",
            success(res) {
              jQuery("#load").hide();
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
                      </div>`
                  ).appendTo(".selected_location_detail");
                  if (jQuery(".Wcmlim_accept_btn").length > 0) {
                    jQuery(".Wcmlim_accept_btn").remove();
                  }

                  if (jQuery(".postcode-location-distance").length) {
                    jQuery(".postcode-location-distance").hide();
                  }
                }
              }
              jQuery(".wclimlocsearch").hide();
            },
            error(res) {
              jQuery("#load").show();
            },
          });

          if (jQuery("#losm").length > 0) {
            jQuery("#losm").hide();
          }

          if (boStatus == 0) {
            jQuery(
              `<p id='globMsg'><b> ${stockQt} </b>${localization.instock}</p>`
            ).insertAfter(".Wcmlim_prefloc_sel");
          }
          jQuery(
            `<div id="locstockImg" class="Wcmlim_have_stock"><i class="fa fa-check"></i>${localization.instock}</div>`
          ).appendTo(".Wcmlim_locstock");
          jQuery(
            ".actions-button, .qty, .quantity, .single_add_to_cart_button, .add_to_cart_button, .stock, .compare"
          ).show();
        }
      }
      if (boStatus == 0) {
        if (stockQt) {
          jQuery(".qty").attr({ max: stockQt });
          document.getElementById("lc_qty").value = stockQt;
        }
      }
    }
  }

  if (localization.enable_price == "on") {
    const regularPrice = jQuery(e)
      .find("option:selected")
      .attr("data-lc-regular-price");
    let pp = 0;
    if (typeof regularPrice !== "undefined" && regularPrice.length > 9) {
      const extracted = alies_CommonFunction.extractMoney(regularPrice);
      pp = extracted.amount;
    }
    const salePrice = jQuery(e)
      .find("option:selected")
      .attr("data-lc-sale-price");

    if (
      (typeof regularPrice !== "undefined" && regularPrice.length > 9) ||
      (typeof salePrice !== "undefined" && salePrice.length > 9)
    ) {
      if (pp > 0) {
        if (typeof salePrice !== "undefined" && salePrice.length > 9) {
          jQuery(".price.wcmlim_product_price").html(
            `<del>${regularPrice}</del><ins>${salePrice}</ins>`
          );
        } else {
          jQuery(".price.wcmlim_product_price").html(regularPrice);
        }
        document.getElementById("lc_regular_price").value = regularPrice;
        document.getElementById("lc_sale_price").value = salePrice;
      } else {
        const pOp = document.getElementById("productOrgPrice").value;
        jQuery(".price.wcmlim_product_price").empty().append(pOp);
        document.getElementById("lc_regular_price").value = "";
        document.getElementById("lc_sale_price").value = "";
      }
    } else {
      const pOp = document.getElementById("productOrgPrice").value;
      jQuery(".price.wcmlim_product_price").empty().append(pOp);
      document.getElementById("lc_regular_price").value = "";
      document.getElementById("lc_sale_price").value = "";
    }
    if (typeof salePrice !== "undefined" && salePrice.length > 9) {
      const sale_Price = jQuery("#lc_sale_price").val();
      if (sale_Price.length > 9) {
        jQuery(".price.wcmlim_product_price").html(
          `<del>${regularPrice}</del><ins>${salePrice}</ins>`
        );
      } else {
        const reg_Price = jQuery("#lc_regular_price").val();
        if (reg_Price == "" || sale_Price == "") {
          const pOp2 = document.getElementById("productOrgPrice").value;
          jQuery(".price.wcmlim_product_price").empty().append(pOp2);
        } else {
          jQuery(".price.wcmlim_product_price").html(regularPrice);
        }
      }
    }
  }

  if (boStatus == 0) {
    if (selectedValue.split("|")[3] == 0) {
      const qty = selectedText.split(":");
      const updateQty = qty[1].trim();
      jQuery(".qty").attr({ max: updateQty });
    }
  }
}
