import * as alies_localization from "./wcmlim_localization.js";
var localization = alies_localization.wcmlim_localization();

export function calculate_distance_search(search_lat, search_lng) {
  var i,
    x = "";
  jQuery.ajax({
    type: "POST",
    url: localization.ajaxurl,
    data: {
      action: "wcmlim_calculate_distance_search",
      search_lat: search_lat,
      search_lng: search_lng,
    },
    dataType: "json",
    success(res) {
      var maxdistance = 0;
      var xhtml = "";
      var comp_xhtml = "";
      for (i = 0; i < res.length; i++) {
        if (maxdistance < res[i]["distance"]) {
          maxdistance = res[i]["distance"];
        }
        x = res[i]["id"];
        jQuery(".wcmlim-map-sidebar-widgets #" + x + " .miles").remove();
        if (localization.setting_loc_dis_unit == "kms") {
          jQuery(".wcmlim-map-sidebar-widgets #" + x).append(
            '<p class="miles" data-id="' +
              x +
              '" data-value="' +
              (res[i]["distance"].toFixed(2) * 1.60934).toFixed(2) +
              '"><span class="fa fa-paper-plane" aria-hidden="true"></span>' +
              (res[i]["distance"].toFixed(2) * 1.60934).toFixed(2) +
              " " +
              localization.setting_loc_dis_unit +
              " " +
              localization.away +
              "</p>"
          );
        } else {
          jQuery(".wcmlim-map-sidebar-widgets #" + x).append(
            '<p class="miles" data-id="' +
              x +
              '" data-value="' +
              res[i]["distance"].toFixed(2) +
              '"><span class="fa fa-paper-plane" aria-hidden="true"></span>' +
              res[i]["distance"].toFixed(2) +
              " " +
              localization.setting_loc_dis_unit +
              " " +
              localization.away +
              "</p>"
          );
        }
        xhtml = jQuery("#" + x).html();
        comp_xhtml =
          comp_xhtml +
          '<div class="wcmlim-map-sidebar-list" id="' +
          x +
          '">' +
          xhtml +
          "</div>";
      }
      if (localization.setting_loc_dis_unit == "kms") {
        maxdistance = maxdistance * 1.60934;
      }
      maxdistance = maxdistance.toFixed(2);
      jQuery(".block-2").html(comp_xhtml);
      if (jQuery("#rangeInput").length != 0) {
        jQuery("#rangeInput").attr("max", maxdistance);
        document.getElementById("rangeInput").value = Math.round(maxdistance);
        document.getElementById("rangedisplay").innerHTML =
          Math.round(maxdistance) + " " + localization.setting_loc_dis_unit;
        jQuery(".distance-bar").show();
      }
    },
  });
}
