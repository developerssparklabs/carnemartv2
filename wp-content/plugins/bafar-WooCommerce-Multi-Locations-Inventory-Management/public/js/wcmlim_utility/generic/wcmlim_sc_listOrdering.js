import * as alies_localization from "./wcmlim_localization.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_setcookies from "./wcmlim_setcookies.js";

var localization = alies_localization.wcmlim_localization();
export function sc_listOrdering() {
  if (localization.sc_listmode == "off") {
    jQuery(
      ".wcmlim_sel_location #wcmlim-change-lc-select, .wcmlim_change_lc_to"
    ).hide();
    jQuery("#inline_wcmlim_lc").css("padding", "12px 0px !important;");

    var uniqueLi = {};
    jQuery("#wcmlim-change-lc-select option").each(function (i, e) {
      var thisVal = jQuery(this).text();
      if (!(thisVal in uniqueLi)) {
        uniqueLi[thisVal] = "";
        var scwclimrw_ = ".scwclimrw_" + i;
        jQuery('<div class="scwclimrow scwclimrw_' + i + '"></div>')
          .html("")
          .appendTo(".rlist_location");
        //get cookie 'wcmlim_selected_location'
        var wcmlim_selected_location = alies_getcookies.getCookie(
          "wcmlim_selected_location"
        );
        if (wcmlim_selected_location != null) {
          var isChecked_ = ++wcmlim_selected_location == i ? "checked" : "";
        }
        jQuery(
          "<input class='scwclimcol1 scwclim_inp" +
            i +
            "' type='radio' name='wcmlim_change_lc_to' " +
            isChecked_ +
            "/>"
        )
          .attr("value", jQuery(this).val())
          .click(function () {
            jQuery("#wcmlim-change-lc-select")
              .val(jQuery(this).val())
              .trigger("change");
            alies_setcookies.setcookie(
              "wcmlim_selected_location",
              jQuery(this).val()
            );
          })
          .appendTo(scwclimrw_);
        var label1 = jQuery(this).text();
        var label2 = label1.split("-");
        var label3 = label2[0].split(":");
        var currentclass = jQuery(
          "input[class='scwclimcol1 scwclim_inp" +
            i +
            "'][value='" +
            jQuery(this).val() +
            "']"
        );
        jQuery("<div class='scwclimcol2'>")
          .html("<p class='scwcmlim_optloc" + i + "'>" + label3 + "</p>")
          .insertAfter(currentclass);
      } else {
        jQuery(this).remove();
      }
    });
  } else {
    jQuery(".wcmlim_sel_location #wcmlim-change-lc-select").show();
  }
} /**End sc_listOrdering */
