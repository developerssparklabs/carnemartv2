// demo.js
const { ajaxurl } = multi_inventory;

let loader = '<div class="wcmlim-chase-wrapper">';
loader += '<div class="wcmlim-chase">';
loader += '<div class="wcmlim-chase-dot"></div>';
loader += '<div class="wcmlim-chase-dot"></div>';
loader += '<div class="wcmlim-chase-dot"></div>';
loader += '<div class="wcmlim-chase-dot"></div>';
loader += '<div class="wcmlim-chase-dot"></div>';
loader += '<div class="wcmlim-chase-dot"></div>';
loader += "</div>";
loader += "</div>";

jQuery(".currentLoc").click(() => {
  debugger;
  fillInPage();

  $(".elementIdGlobal, #elementIdGlobal").on("click", function () {
    $(".wclimlocsearch").show();
  });
});
var fillInPage = (function () {
  var updateCityText = function (geoipResponse) {
    /*
     * It's possible that we won't have any names for this city.
     * For language codes with a special character such as pt-BR,
     * replace names.en with names['pt-BR'].
     */
    var cityName = geoipResponse.city.names.en || "your city";
  };

  var onSuccess = function (geoipResponse) {
    // let response = {"city":{"geoname_id":1259229,"names":{"de":"Pune","en":"Pune","fr":"Pune","ja":"\u30d7\u30cd\u30fc","pt-BR":"Pune","ru":"\u041f\u0443\u043d\u0430","zh-CN":"\u6d66\u90a3"}},"continent":{"code":"AS","geoname_id":6255147,"names":{"ja":"\u30a2\u30b8\u30a2","pt-BR":"\u00c1sia","ru":"\u0410\u0437\u0438\u044f","zh-CN":"\u4e9a\u6d32","de":"Asien","en":"Asia","es":"Asia","fr":"Asie"}},"country":{"iso_code":"IN","geoname_id":1269750,"names":{"en":"India","es":"India","fr":"Inde","ja":"\u30a4\u30f3\u30c9","pt-BR":"\u00cdndia","ru":"\u0418\u043d\u0434\u0438\u044f","zh-CN":"\u5370\u5ea6","de":"Indien"}},"location":{"accuracy_radius":200,"latitude":18.6161,"longitude":73.7286,"time_zone":"Asia\/Kolkata"},"postal":{"code":"411001"},"registered_country":{"iso_code":"IN","geoname_id":1269750,"names":{"pt-BR":"\u00cdndia","ru":"\u0418\u043d\u0434\u0438\u044f","zh-CN":"\u5370\u5ea6","de":"Indien","en":"India","es":"India","fr":"Inde","ja":"\u30a4\u30f3\u30c9"}},"subdivisions":[{"iso_code":"MH","geoname_id":1264418,"names":{"zh-CN":"\u9a6c\u54c8\u62c9\u65bd\u7279\u62c9\u90a6","en":"Maharashtra","es":"Maharastra","fr":"Maharashtra","ja":"\u30de\u30cf\u30fc\u30e9\u30fc\u30b7\u30e5\u30c8\u30e9\u5dde","pt-BR":"Maarastra","ru":"\u041c\u0430\u0445\u0430\u0440\u0430\u0448\u0442\u0440\u0430"}}],"traits":{"autonomous_system_number":45609,"autonomous_system_organization":"Bharti Airtel Ltd. AS for GPRS Service","isp":"Airtel","mobile_country_code":"404","mobile_network_code":"02","organization":"Airtel","ip_address":"2401:4900:531c:e1f5:7be1:dcb6:d357:3182","network":"2401:4900:5318::\/45"},"represented_country":{"names":{}}};
    // extract city, state, country name, postal code, latitude, longitude, timezone, and IP address
    let response = JSON.parse(JSON.stringify(geoipResponse));
    var city = response.city.names.en;
    var state = response.subdivisions[0].names.en;
    var country = response.country.names.en;
    var postal = response.postal.code;
    var latitude = response.location.latitude;
    var longitude = response.location.longitude;
    var timezone = response.location.time_zone;
    var ipAddress = response.traits.ip_address;
    //  combile city, state, country name, postal code, latitude, longitude, timezone, and IP address and replace space with %20
    var wcmlim_nearby_location =
      city + "%20" + state + "%20" + country + "%20" + postal;
    //replace space with %20
    wcmlim_nearby_location = wcmlim_nearby_location.replace(/\s/g, "%20");
    //set cookie wcmlim_nearby_location with value wcmlim_nearby_location
    //set cookie maxmind_wcmlim_nearby_location with value wcmlim_nearby_location
    setcookie("wcmlim_nearby_location", wcmlim_nearby_location, 1);
    setcookie("maxmind_wcmlim_nearby_location", wcmlim_nearby_location, 1);
    //wcmlimcode merge
    if (wcmlim_nearby_location) {
      var postal_code = postal;
    } else {
      var postal_code = jQuery(".class_post_code_global").val();
    }

    const globalPin = jQuery("#global-postal-check").val();

    if (jQuery('[name="post_code_global"]', this).val() == "") {
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
        url: ajaxurl,
        type: "post",
        data: {
          postcode: wcmlim_nearby_location,
          globalPin,
          action: "wcmlim_closest_location",
        },
        dataType: "json",
        success(response) {
          jQuery(".postcode-checker-response").html(loader);

          setcookie("wcmlim_selected_location", response.loc_key);
          setcookie("wcmlim_selected_location_regid", response.secgrouploc);
          jQuery(
            'select[name="wcmlim_change_sl_to"] option[value="' +
              response.secgrouploc +
              '"]'
          ).attr("selected", "selected");
          jQuery('select[name="wcmlim_change_sl_to"]').trigger("change");

          if (jQuery.trim(response.status) === "true") {
            var dunit = response.loc_dis_unit;
            if (dunit !== null) {
              var spu = dunit.split(" ");
              var n = spu[0];
            }
            if (response.locServiceRadius != "") {
              if (response.locServiceRadius <= n || !n) {
                if (response.cookie != "") {
                  Swal.fire({
                    title: "Ups...!",
                    text: "La distancia de tu ubicación parece estar muy lejos del punto de entrega...",
                    icon: "info",
                    timer: 2000,
                    showConfirmButton: false,
                  }).then(function () {
                    jQuery("#lc-switch-form").submit();
                  });
                } else {
                  jQuery(".postcode-checker-response").html();
                }
              }
            } else {
              jQuery(".postcode-checker-change").show();
              jQuery(".postcode-checker-div")
                .removeClass("postcode-checker-div-show")
                .addClass("postcode-checker-div-hide");
              if (wcmlim_nearby_location) {
                var wcmlim_nearby_location_str = wcmlim_nearby_location.replace(
                  /%20/g,
                  " "
                );
                jQuery(".postcode-checker-response").html(
                  `<i class="fa fa-search"></i> ${wcmlim_nearby_location_str}`
                );
              } else {
                var wcmlim_nearby_location_str = wcmlim_nearby_location.replace(
                  /%20/g,
                  " "
                );

                jQuery(".postcode-checker-response").html(
                  `<i class="fa fa-search"></i> ${wcmlim_nearby_location_str}`
                );
              }
              const locationCookie = getCookie("wcmlim_selected_location");

              if (locationCookie == null) {
                /* do cookie doesn't exist stuff; */
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
                setcookie("wcmlim_selected_location", gLocation);
                jQuery("#lc-switch-form").submit();
              } else {
                // if(locationCookie != jQuery.trim(response.location)){
                var gLocation = response.loc_key;
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
              }
            }
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
  };

  // If we get an error, we will display an error message
  var onError = function (error) {
   // console.log("an error!  Please try again..");
  };

  return function () {
    if (typeof geoip2 !== "undefined") {
      geoip2.city(onSuccess, onError);
    } else {
     // console.log("a browser that blocks GeoIP2 requests");
    }
  };
  //page reload
})();
//check if maxmind_wcmlim_nearby_location cookie is set or not
var maxmind_wcmlim_nearby_location = getCookie(
  "maxmind_wcmlim_nearby_location"
);
var wcmlim_nearby_location = getCookie("wcmlim_nearby_location");

//if cookie is not set then call fillInPage function
if (maxmind_wcmlim_nearby_location == "" || wcmlim_nearby_location == "") {
  fillInPage();
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
function setcookie(name, value, days) {
  let date = new Date();
  if (days) {
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    var expires = `; expires=${date.toUTCString()}`;
  } else {
    date.setTime(date.getTime() + 1 * 24 * 60 * 60 * 1000);
    var expires = `; expires=${date.toUTCString()}`;
  }
  document.cookie = `${name}=${value}${expires};path=/`;
}
