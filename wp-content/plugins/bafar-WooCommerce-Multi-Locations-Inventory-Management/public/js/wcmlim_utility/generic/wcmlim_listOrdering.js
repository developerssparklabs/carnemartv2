import * as alies_localization from "./wcmlim_localization.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";

var localization = alies_localization.wcmlim_localization();
export function listOrdering() {
  if (localization.listmode == "on") {
    //dfault location then auto select
    

   
    const locationCookie = alies_getcookies.getCookie(
      "wcmlim_selected_location"
    );
    var unlistmode = {};
    jQuery(".loc_dd.Wcmlim_prefloc_sel #select_location").hide();
    jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_locmap").hide();
    jQuery("#losm").hide();
    jQuery("#globMsg").hide();

    jQuery("#select_location option").each(function (i, e) {
      var locvalue = jQuery(this).val();
      if (!(locvalue in unlistmode)) {
        unlistmode[locvalue] = "";
        var wclimrw_ = ".wclimrw_" + i;
        var locclass = jQuery(this).attr("class");
        var stockupp = jQuery(this).attr("data-lc-qty");
        var backorderl = jQuery(this).attr("data-backorder");
        var style = jQuery(this).attr("style");

        if (stockupp != undefined && stockupp != null) {
          jQuery('<div class="wclimrow wclimrw_' + i + '"></div>')
            .html("")
            .attr("style", style)
            .appendTo(".rselect_location");
        }
        if (locvalue == locationCookie) {
          var wclimcheck = true;
        } else {
          var wclimcheck = false;
        }
        jQuery(
          "<input class='wclimcol1 wclim_inp" +
            i +
            "' type='radio' name='select_location' />"
        )
          .attr("value", jQuery(this).val())
          .attr("checked", wclimcheck)
          .attr("data-lc-qty", jQuery(this).attr("data-lc-qty"))
          .attr("data-lc-address", jQuery(this).attr("data-lc-address"))
          .attr("data-lc-backorder", jQuery(this).attr("data-lc-backorder"))
          .attr("data-lc-stockstatus", jQuery(this).attr("data-lc-stockstatus"))
          .addClass(locclass)
          .click(function () {
            jQuery("#select_location")
              .val(jQuery(this).val())
              .trigger("change");
            if (localization.isClearCart != "on") {
              alies_setcookies.setcookie(
                "wcmlim_selected_location",
                jQuery(this).val()
              );
            }
          })
          .appendTo(wclimrw_);
        var labelText1 = jQuery(this).text();
        var labelText2 = labelText1.split("-");
        var labelText3 = labelText2[0].split(":");
        var labelText4 = jQuery(this).attr("data-lc-address");
        var backorder_allow = jQuery(this).attr("data-lc-backorder");
        var simple_product_backorder = jQuery("#backorderAllowed").val();
        var location_stock_status = jQuery(this).attr("data-lc-stockstatus");
        var currentItem = jQuery(
          "input[class='wclimcol1 wclim_inp" +
            i +
            " " +
            locclass +
            "'][value='" +
            jQuery(this).val() +
            "']"
        );
        jQuery("<div class='wclimcol2'>")
          .html(
            "<p class='wcmlim_optloc" +
              i +
              " " +
              locclass +
              "'>" +
              labelText3 +
              "</p>"
          )
          .insertAfter(currentItem);
        var pItem = ".wcmlim_optloc" + i;
        if (labelText4 == undefined) {
          jQuery("<p class='wcmlim_detadd wcmlim_optadd" + i + "'>")
            .text("")
            .insertAfter(pItem);
        } else {
          var labelText5 = String(labelText4);
          var decodedString = decodeURIComponent(escape(atob(labelText5)));
          jQuery("<p class='wcmlim_detadd wcmlim_optadd" + i + "'>")
            .text(decodedString)
            .insertAfter(pItem);
        }
        var paddress = ".wcmlim_optadd" + i;
        var stockupp = jQuery(this).attr("data-lc-qty");
        var onbackorder = passedbackorderbtn.keys;
        var instockbtntxt = passedinstockbtn.keys;
        if (location_stock_status == "instock") {
          jQuery("<p class='stockupp'>")
            .text(instockbtntxt + " " + ":" + stockupp)
            .insertBefore(paddress);
        }

        if (backorder_allow == "yes" || simple_product_backorder == 1) {
          var soldoutbtntxt = "Available on backorder";

          jQuery("<p class='outof_stockupp'>")
            .text(soldoutbtntxt)
            .insertAfter(".wcmlim_optloc" + i);
            
        } else {
          if (stockupp == 0) {
            if (location_stock_status == "instock") {
              jQuery("<p class='stockupp'>")
                .text("In Stock")
                .insertBefore(paddress);
            } else {
              if (backorder_allow == "yes" || simple_product_backorder == 1) {
                var soldoutbtntxt = "Available on backorder";
              } else {
                var soldoutbtntxt = passedSoldbtn.keys;
              }
              //allow backorder
              if (backorderl == "Yes" && localization.isBackorderOn == "on") {
                jQuery("<p class='outof_stockupp'>")
                  .text("Backorder")
                  .insertBefore(paddress);
              } else {
                var sale_price = jQuery(this).attr("data-lc-sale-price");
                if (typeof sale_price != "undefined") {
                  // sale_price = sale_price.replace(/(<([^>]+)>)/ig, "");
                  // var regular_price = jQuery(this).attr('data-lc-regular-price');
                  // regular_price = regular_price.replace(/(<([^>]+)>)/ig, "");
                  // jQuery("<p class='wcmlim_product_sale_price'>").text("@ " + sale_price).insertBefore(paddress);
                  // jQuery(".wcmlim_product_sale_price").css("font-weight", "bold");
                  // jQuery("<p class='wcmlim_product_regular_price'>").text("@ " + regular_price).insertBefore(paddress);
                  // jQuery(".wcmlim_product_regular_price").css("font-weight", "bold");
                  // if (sale_price != '') {
                  //   jQuery(".wcmlim_product_regular_price").css("text-decoration", "line-through");

                  jQuery("<p class='outof_stockupp'>")
                    .text(soldoutbtntxt)
                    .insertAfter(".wcmlim_optloc" + i);
                }
              }
            }
          } else if (stockupp == "undefined" || stockupp == null) {
            jQuery("<p class='stockupp'>").text("").insertBefore(paddress);
          } else {
            if (backorderl == "Yes" && localization.isBackorderOn == "on") {
              jQuery("<p class='outof_stockupp'>")
                .text("Backorder")
                .insertBefore(paddress);
            } else {
              if (localization.stock_format == "no_amount") {
                jQuery("<p class='stockupp'>")
                  .text(instockbtntxt + " " + ":" + stockupp)
                  .insertBefore(paddress);
              } else {
                // var sale_price = jQuery(this).attr('data-lc-sale-price');
                // sale_price = sale_price.replace(/(<([^>]+)>)/ig, "");

                // var regular_price = jQuery(this).attr('data-lc-regular-price');
                // regular_price = regular_price.replace(/(<([^>]+)>)/ig, "");

                // jQuery("<p class='wcmlim_product_sale_price'>").text("@ " +sale_price).insertBefore(paddress);
                // jQuery(".wcmlim_product_sale_price").css("font-weight", "bold");
                // jQuery("<p class='wcmlim_product_regular_price'>").text( "@ " + regular_price).insertBefore(paddress);
                // // jQuery("<p class='wcmlim_product_regular_price'>").text( "@ " + regular_price).insertAfter(".wclimfull .wclimcol2 .stockupp");
                // jQuery(".wcmlim_product_regular_price").css("font-weight", "bold");
                // if (sale_price != '') {
                //   jQuery(".wcmlim_product_regular_price").css("text-decoration", "line-through");

                jQuery("<p class='stockupp'>")
                  .text(instockbtntxt + " " + ":" + stockupp)
                  .insertBefore(paddress);
              }
            }
          }
        }
        if (localization.detailadd == "on") {
          jQuery(".wcmlim_detadd").show();
          jQuery(".wclimcol2").addClass("addShow");
        } else {
          jQuery(".wcmlim_detadd").hide();
          jQuery(".wclimcol2").removeClass("addShow");
        }
      } else {
        jQuery(this).remove();
      }
    });
    if (
      (localization.listformat == "full" ||
        localization.listformat == null ||
        localization.listformat == "") &&
      localization.listformat != "advanced_list_view"
    ) {
      jQuery(".rselect_location").removeClass("wclimscroll");
      jQuery(".rselect_location").removeClass("wclimhalf");
      jQuery(".rselect_location").removeClass("wclimthird");
      jQuery(".rselect_location").removeClass("wclimadvlist");
      jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").hide();
      jQuery(".rselect_location").addClass("wclimfull");
    } else if (localization.listformat == "half") {
      jQuery(".rselect_location").removeClass("wclimfull");
      jQuery(".rselect_location").removeClass("wclimthird");
      jQuery(".rselect_location").removeClass("wclimscroll");
      jQuery(".rselect_location").removeClass("wclimadvlist");
      jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").hide();
      jQuery(".rselect_location").addClass("wclimhalf");
    } else if (localization.listformat == "third") {
      jQuery(".rselect_location").removeClass("wclimfull");
      jQuery(".rselect_location").removeClass("wclimhalf");
      jQuery(".rselect_location").removeClass("wclimscroll");
      jQuery(".rselect_location").removeClass("wclimadvlist");
      jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").hide();
      jQuery(".rselect_location").addClass("wclimthird");
    } else if (localization.listformat == "scroll") {
      jQuery(".rselect_location").removeClass("wclimfull");
      jQuery(".rselect_location").removeClass("wclimhalf");
      jQuery(".rselect_location").removeClass("wclimthird");
      jQuery(".rselect_location").removeClass("wclimadvlist");
      jQuery(".rselect_location").addClass("wclimscroll");
      jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").show();
      jQuery(".rselect_location.wclimscroll").css("height", "200px");
    } else if (localization.listformat == "advanced_list_view") {
      jQuery(".rselect_location").removeClass("wclimfull");
      jQuery(".rselect_location").removeClass("wclimhalf");
      jQuery(".rselect_location").removeClass("wclimthird");
      jQuery(".rselect_location").addClass("wclimadvlist");
      jQuery(".rselect_location").removeClass("wclimscroll");
      jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").hide();
    } else {
      jQuery(".rselect_location").removeClass("wclimscroll");
      jQuery(".rselect_location").removeClass("wclimhalf");
      jQuery(".rselect_location").removeClass("wclimthird");
      jQuery(".rselect_location").removeClass("wclimadvlist");
      jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").hide();
      jQuery(".rselect_location").addClass("wclimfull");
    }
  } else {
    jQuery(".loc_dd.Wcmlim_prefloc_sel #select_location").show();
    jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_locmap").show();
    jQuery("#losm").show();
    jQuery("#globMsg").show();
    jQuery(".loc_dd.Wcmlim_prefloc_sel .wc_scrolldown").hide();
    jQuery(".rselect_location.wclimscroll").css("height", "0");
  }
} /**End listOrdering */
