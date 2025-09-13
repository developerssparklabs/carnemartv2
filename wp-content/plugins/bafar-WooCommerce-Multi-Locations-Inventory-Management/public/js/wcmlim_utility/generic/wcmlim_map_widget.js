import * as alies_calDistanceSearch from "./wcmlim_cal_distance_search.js";
import * as alies_showPosition from "./wcmlim_show_possition.js";
import * as alies_localization from "./wcmlim_localization.js";

export function mapWidget() {
  var localization = alies_localization.wcmlim_localization();
  var mapcontent = "";
  var dataindex = "";
  var type = "";
  var map;
  var marker;
  var infowindow = new google.maps.InfoWindow();

  if (jQuery(".wcmlim-map-widgets").length != 0) {
    if (jQuery(".map-view-locations").length != 0) {
      jQuery("#wcmlim_map_prodct_filter").chosen({ width: "60%" });
      jQuery("#wcmlim_map_prodct_category_filter").chosen({ width: "60%" });
      jQuery(".map-view-locations").hide();
      jQuery(".map-view-locations").after(
        '<div class="wcmlim-map-loader"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i><br> Loading</div>'
      );
      jQuery(".wcmlim-map-loader").hide(2000);
      jQuery(".map-view-locations").delay(2000).fadeIn(500);
    }
    if (jQuery(".list-view-locations").length != 0) {
      jQuery("#wcmlim_map_prodct_filter").chosen({ width: "60%" });
      jQuery("#wcmlim_map_prodct_category_filter").chosen({ width: "60%" });
      jQuery(".list-view-locations").hide();
      jQuery(".list-view-locations").after(
        '<div class="wcmlim-map-loader"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i><br> Loading</div>'
      );
      jQuery(".wcmlim-map-loader").hide(2000);
      jQuery(".list-view-locations").delay(2000).fadeIn(500);
    }
    if (
      localization.default_zoom == "" ||
      localization.default_zoom == "undefined"
    ) {
      localization.default_zoom = 10;
    }
    var default_origin_center = localization.default_origin_center;
    var def_search_lng = 0;
    var def_search_lat = 0;
    var icon = {
      path: "M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0z", //SVG path of awesomefont marker
      fillColor: "#045590", //color of the marker
      fillOpacity: 1,
      strokeWeight: 0,
      anchor: new google.maps.Point(200, 510), //position of the icon, careful! this is affected by scale
      labelOrigin: new google.maps.Point(205, 190), //position of the label, careful! this is affected by scale
      scale: 0.06, //size of the marker, careful! this scale also affects anchor and labelOrigin
    };
    jQuery(".distance-bar").hide(100);
    if (
      jQuery(".list-view-locations").length != 0 &&
      jQuery(".map-view-locations").length != 0
    ) {
      jQuery(".list-view-locations .search-filter-toggle_parameter_cat").hide();
      jQuery(".list-view-locations .search-filter-toggle_parameter").hide();
      jQuery(
        ".list-view-locations .search-filter-toggle_parameter_prod"
      ).hide();
      jQuery(".list-view-locations").css("margin", "8rem 0");
    }
    jQuery(".search-filter-toggle_parameter_cat").hide(100);
    jQuery("#btn-filter-toggle_parameter2").click(function () {
      jQuery(".search-filter-toggle_parameter_prod").hide(100);
      jQuery(".search-filter-toggle_parameter_cat").show(100);
    });
    jQuery("#btn-filter-toggle_parameter1").click(function () {
      jQuery(".search-filter-toggle_parameter_cat").hide(100);
      jQuery(".search-filter-toggle_parameter_prod").show(100);
    });
    //   var storeOnMapArr_inpt = [JSON.parse(localization.storeOnMapArr)];
    var storeOnMapArr_inpt = { origin: [] };

    var geocoder = new google.maps.Geocoder();

    if (
      default_origin_center != "" &&
      default_origin_center != null &&
      default_origin_center != "undefined"
    ) {
      geocoder.geocode(
        { address: default_origin_center },
        function (results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            def_search_lat = results[0].geometry.location.lat();
            def_search_lng = results[0].geometry.location.lng();
            mapcontent =
              "<div class='locator-store-block origin-location-marker'><h4>" +
              default_origin_center +
              "</h4></div>";
            dataindex = localization.storeOnMapArr.length + 1;
            type = "origin";
            storeOnMapArr_inpt["origin"].push({
              mapcontent,
              def_search_lat,
              def_search_lng,
              dataindex,
              type,
            });
            alies_calDistanceSearch.calculate_distance_search(
              def_search_lat,
              def_search_lng
            );
          }
        }
      );
    } else {
      def_search_lat = 0;
      def_search_lng = 0;
      mapcontent =
        "<div class='locator-store-block origin-location-marker'><h4>Map Center</h4></div>";
      if (localization.storeOnMapArr == "undefined") {
        dataindex = 1;
      } else {
        dataindex = localization.storeOnMapArr.length + 1;
      }
      type = "origin";
      storeOnMapArr_inpt["origin"].push({
        mapcontent,
        def_search_lat,
        def_search_lng,
        dataindex,
        type,
      });
      alies_calDistanceSearch.calculate_distance_search(
        def_search_lat,
        def_search_lng
      );
    }
    //nearby map location marker code starts here
    if (
      localization.autoDetect == "on" &&
      localization.autodetect_by_maxmind != "on"
    ) {
    //   if (localization.autodetect_by_maxmind == "on") {
    //     jQuery("#showMe").click(() => {
    //       alies_showPosition.showPosition();
    //     });
    //   }
    //   jQuery(".elementIdGlobal, #elementIdGlobal").on("click", function () {
    //     jQuery(".wclimlocsearch").show();
    //   });
    //   jQuery(".elementIdGlobal, #elementIdGlobal").on("change", function () {
    //     jQuery(".wclimlocsearch").hide();
    //   });
    //   jQuery(".elementIdGlobal, #elementIdGlobal").on("input", function () {
    //     jQuery(".wclimlocsearch").hide();
    //   });
    //   if (localization.autodetect_by_maxmind == "on") {
    //     jQuery(".currentLoc").click(() => {

    //       alies_showPosition.showPosition();
    //     });
    //   }
    }

    if (localization.storeOnMapArr !== undefined) {
      try {
        var locations = JSON.parse(localization.storeOnMapArr);
        var map;
        var marker;
        for (var i = 0; i < locations.length; i++) {
          if (locations[i][4] == "origin") {
            map = new google.maps.Map(document.getElementById("map"), {
              zoom: parseInt(localization.default_zoom),
              center: new google.maps.LatLng(locations[i][1], locations[i][2]),
              mapTypeId: google.maps.MapTypeId.ROADMAP,
            });
            marker = new google.maps.Marker({
              position: new google.maps.LatLng(
                locations[i][1],
                locations[i][2]
              ),
              map: map,
              label: {
                fontFamily: "'Font Awesome 5 Free'",
                fontWeight: "900", //careful! some icons in FA5 only exist for specific font weights
                color: "#FFFFFF", //color of the text inside marker
              },
            });

            google.maps.event.addListener(
              marker,
              "click",
              function (marker, i) {
                return function () {
                  infowindow.setContent(
                    "<div class='locator-store-block'><h4>" +
                      locations[i][0] +
                      "</h4></div>"
                  );
                  infowindow.open(map, marker);
                };
              }
            );
          } else {
            var markers = locations.map(function (location, i) {
              var infowindow = new google.maps.InfoWindow({
                maxWidth: 250,
              });
              var marker = new google.maps.Marker({
                position: new google.maps.LatLng(
                  location[1],
                  location[2]
                ),
                map: map,
              });
              google.maps.event.addListener(
                marker,
                "click",
                (function (marker, i) {
                  return function () {
                    infowindow.setContent(location[0]);
                    infowindow.open(map, marker);
                  };
                })(marker, i)
              );
              return marker;
            });
            // // Add a marker clusterer to manage the markers.
            new MarkerClusterer(map, markers, {
              imagePath:
                "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
            });
          }
        }
        const elmapgrid = document.getElementById("elementIdGlobalMap");

        var search_lat, search_lng;
        var infowindow = new google.maps.InfoWindow();
        var marker, i;
        if (elmapgrid) {
          elmapgrid.addEventListener("focus", (e) => {
            const input = document.getElementById("elementIdGlobalMap");

            const options = {};
            const autocomplete = new google.maps.places.Autocomplete(
              input,
              options
            );
            google.maps.event.addListener(autocomplete, "place_changed", () => {
              const place = autocomplete.getPlace();
              var searchedaddress = jQuery("#elementIdGlobalMap").val();

              search_lat = place.geometry.location.lat();
              search_lng = place.geometry.location.lng();
              for (i = 0; i < locations.length; i++) {
                if (locations[i][4] == "origin") {
                  map = new google.maps.Map(document.getElementById("map"), {
                    zoom: parseInt(localization.default_zoom),
                    center: new google.maps.LatLng(search_lat, search_lng),
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                  });
                  marker = new google.maps.Marker({
                    position: new google.maps.LatLng(search_lat, search_lng),
                    map: map,
                    icon: icon,
                    label: {
                      fontFamily: "'Font Awesome 5 Free'",
                      fontWeight: "900", //careful! some icons in FA5 only exist for specific font weights
                      color: "#FFFFFF", //color of the text inside marker
                    },
                  });

                  google.maps.event.addListener(
                    marker,
                    "click",
                    (function (marker) {
                      return function () {
                        infowindow.setContent(
                          "<div class='locator-store-block'><h4>" +
                            searchedaddress +
                            "</h4></div>"
                        );
                        infowindow.open(map, marker);
                      };
                    })(marker)
                  );
                } else {
                  var markers = locations.map(function (location) {
                    var infowindow = new google.maps.InfoWindow({
                      maxWidth: 250,
                    });
                    var marker = new google.maps.Marker({
                      position: new google.maps.LatLng(
                        location[1],
                        location[2]
                      ),
                      map: map,
                    });
                    google.maps.event.addListener(
                      marker,
                      "click",
                      (function (marker, i) {
                        return function () {
                          infowindow.setContent(location[0]);
                          infowindow.open(map, marker);
                        };
                      })(marker, i)
                    );
                    return marker;
                  });
                  // // Add a marker clusterer to manage the markers.
                  new MarkerClusterer(map, markers, {
                    imagePath:
                      "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
                  });
                }
              }
              alies_calDistanceSearch.calculate_distance_search(
                search_lat,
                search_lng
              );
            });
          });
        }

        const my_current_location = document.getElementById(
          "my_current_location"
        );
        var current_search_lat, current_search_lng;
        my_current_location.addEventListener("click", (e) => {
          infoWindow = new google.maps.InfoWindow();
          var marker, i;
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
              current_search_lat = position.coords.latitude;
              current_search_lng = position.coords.longitude;
              for (i = 0; i < locations.length; i++) {
                if (locations[i][4] != "origin") {
                  var markers = locations.map(function (location, i) {
                    var infowindow = new google.maps.InfoWindow({
                      maxWidth: 250,
                    });
                    var marker = new google.maps.Marker({
                      position: new google.maps.LatLng(
                        location[1],
                        location[2]
                      ),
                      map: map,
                    });
                    google.maps.event.addListener(
                      marker,
                      "click",
                      (function (marker, i) {
                        return function () {
                          infowindow.setContent(location[0]);
                          infowindow.open(map, marker);
                        };
                      })(marker, i)
                    );
                    return marker;
                  });
                  // // Add a marker clusterer to manage the markers.
                  new MarkerClusterer(map, markers, {
                    imagePath:
                      "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
                  });
                } else {
                  map = new google.maps.Map(document.getElementById("map"), {
                    zoom: parseInt(localization.default_zoom),
                    center: new google.maps.LatLng(
                      current_search_lat,
                      current_search_lng
                    ),
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                  });
                  marker = new google.maps.Marker({
                    position: new google.maps.LatLng(
                      current_search_lat,
                      current_search_lng
                    ),
                    map: map,
                    icon: icon,
                    label: {
                      fontFamily: "'Font Awesome 5 Free'",
                      fontWeight: "900", //careful! some icons in FA5 only exist for specific font weights
                      color: "#FFFFFF", //color of the text inside marker
                    },
                  });

                  google.maps.event.addListener(
                    marker,
                    "click",
                    (function (marker) {
                      return function () {
                        infowindow.setContent(
                          "<div class='locator-store-block'><h4>" +
                            searchedaddress +
                            "</h4></div>"
                        );
                        infowindow.open(map, marker);
                      };
                    })(marker)
                  );
                }
              }
              alies_calDistanceSearch.calculate_distance_search(
                current_search_lat,
                current_search_lng
              );
            });
          }
        });
        jQuery(
          "#search-parametered-btn-pro, #search-parametered-btn-cat"
        ).click(function () {
          jQuery("#map").hide();
          jQuery(".wcmlim-map-loader").remove();
          jQuery("#map").after(
            '<div class="wcmlim-map-loader"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i><br> Loading</div>'
          );
          var searchtype = jQuery(this).data("type");
          var selectedProduct = [];
          if (searchtype == "product") {
            jQuery.each(
              jQuery("#wcmlim_map_prodct_filter option:selected"),
              function () {
                selectedProduct.push(jQuery(this).val());
              }
            );
          } else {
            jQuery.each(
              jQuery("#wcmlim_map_prodct_category_filter option:selected"),
              function () {
                selectedProduct.push(jQuery(this).val());
              }
            );
          }
          jQuery.ajax({
            type: "POST",
            url: localization.ajaxurl,
            data: {
              action: "wcmlim_filter_map_product_wise",
              parameter_id: selectedProduct,
              searchtype: searchtype,
            },
            dataType: "json",
            success(res) {
              var locations = JSON.parse(JSON.stringify(res));
              for (var i = 0; i < locations.length; i++) {
                if (locations[i][4] != "origin") {
                  marker = new google.maps.Marker({
                    position: new google.maps.LatLng(
                      locations[i][1],
                      locations[i][2]
                    ),
                    map: map,
                  });
                  google.maps.event.addListener(
                    marker,
                    "click",
                    (function (marker, i) {
                      return function () {
                        infowindow.setContent(locations[i][0]);
                        infowindow.open(map, marker);
                      };
                    })(marker, i)
                  );
                  if (jQuery(".wcmlim-map-widgets").length != 0) {
                    var x = locations[i][4];
                    jQuery(
                      ".wcmlim-map-widgets #" +
                        x +
                        " .location-address .search-prod-details"
                    ).remove();
                    jQuery(
                      ".wcmlim-map-widgets #" + x + " .location-address"
                    ).append(
                      '<div class="search-prod-details" data-id="' +
                        x +
                        '">' +
                        locations[i][0] +
                        " </div>"
                    );
                  }
                  jQuery("#map").show();
                  jQuery(".wcmlim-map-loader").remove();
                }
              }
            },
          });
          jQuery("#map").show();
          jQuery(".wcmlim-map-loader").remove();
        });
        document
          .getElementById("rangeInput")
          .addEventListener("change", alies_GlobalMapList.rangeInputCallback);
      } catch (error) {
        console.error(error);
      }
    }
  }
}
