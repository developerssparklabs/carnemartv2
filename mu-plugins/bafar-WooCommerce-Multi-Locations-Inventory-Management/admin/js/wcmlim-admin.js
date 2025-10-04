jQuery(document).ready(($) => {
  jQuery('#wcmlim_restrict_guest_user_location').hide();
  if (jQuery('#wcmlim_enable_restrict_guestuser_location').is(':checked')) {
    jQuery('#wcmlim_restrict_guest_user_location').show();
  }
  jQuery('#wcmlim_enable_restrict_guestuser_location').on('change', function () {
    if (this.checked) {
      jQuery('#wcmlim_restrict_guest_user_location').show();
    } else {
      jQuery('#wcmlim_restrict_guest_user_location').hide();
    }
  });
  var _locationWidget = locationWidget.keys;
  if (_locationWidget == 'on') {
    location_as_taxonomy();
  }
  function location_as_taxonomy() {

    location_as_taxonomy_helper();
    function location_as_taxonomy_helper() {
      var loc_arr = [];
      $.each($("input[name='tax_input[locations][]']:not(:checked)"), function () {
        loc_arr.push($(this).val());
      });
      for (let i = 0; i < loc_arr.length; i++) {
        $("#locationID_" + loc_arr[i] + "").hide();
        $(".locationID_" + loc_arr[i] + "").hide();

      }
    };
    //for variation products
    jQuery('body').on('click', '.woocommerce_variation', function () {
      location_as_taxonomy_helper();
    });

    jQuery('#taxonomy-locations').on('click', '.selectit', function () {
      var whatVal = jQuery(this).children("input").val();
      var isChecked = jQuery(this).children("input").prop("checked");
      var $_value = $("#wcmlim_draft_stock_at_" + whatVal + "").val();
      var productType = jQuery("#product-type :selected").val();
      if (productType == 'variable') {
        if (isChecked === true) {
          var $variation_input = $(".locationID_" + whatVal).find("input[type='number']");
          for (let i = 0; i < $variation_input.length; i++) {
            $variation_input[i].value = '';
          }
          $(".locationID_" + whatVal + "").show();
        } else {
          var $variation_input = $(".locationID_" + whatVal).find("input[type='number']");
          for (let i = 0; i < $variation_input.length; i++) {
            $variation_input[i].value = '';
          }
          $(".locationID_" + whatVal + "").hide();
        }
      } else if (productType == 'simple') {
        if (isChecked === true) {
          var $input = $("#locationID_" + whatVal).find("input[type='number']");
          $input.val($_value);
          $("#locationID_" + whatVal + "").show();
        }
        if (isChecked === false) {
          var $input = $("#locationID_" + whatVal).find("input[type='number']");
          $input.val('');
          $("#locationID_" + whatVal + "").hide();
        }
      }
    });
  }


  validation_add_location();
  function validation_add_location() {
    if ($(".taxonomy-locations").length > 0) {

      var tag_name = $('#tag-name').val();
      var postal_code = $('#postal_code').val();
      var country = $('#country').val();
      if ((postal_code != "") && (country != '') && (tag_name != '') && (tag_name != null)) {

        $('#submit').removeAttr('disabled');
        $('.alert-text').hide();
      } else {
        $('#submit').attr("disabled", true);
        $('.alert-text').text('Please fill all mandatory fields.').show();
      }
    }
  }
  jQuery("#postal_code, #tag-name,  #country").on('input', function (e) {
    validation_add_location();

  });
  jQuery('#submit').click(function(){
    var url = window.location.href;
    if (url.includes("location_group")) {
 jQuery(this).attr('disabled', true);
    setTimeout(function () {
      location.reload();
    }, 1000);    }
   
  });


  jQuery('#lcpriority').on('keyup', function () {
    var lcpriority = jQuery('#lcpriority').val();
    var url = window.location.href;
    url = new URL(url);
    var skip_location = url.searchParams.get("tag_ID");
    if (lcpriority != '') {
      $.ajax({
        url: multi_inventory.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "wcmlim_get_lcpriority",
          lcpriority: lcpriority,
          skip_location: skip_location,
        },
        success(response) {
          if (response == 1) {
            alertify.set("notifier", "position", "bottom-right");
            alertify.error(
              "This priority has been already asigned to other location"
            );
            jQuery('#lcpriority').val('');
          }
        }
      });
    }
  });



  // autocomplete places and added map on
  let autocomplete;
  const componentForm = {
    street_number: "short_name",
    route: "long_name",
    locality: "long_name",
    administrative_area_level_1: "long_name",
    country: "long_name",
    postal_code: "short_name",
  };

  const el = document.getElementById("wcmlim_autocomplete_address");

  if (el) {
    el.addEventListener("focus", (e) => {
      autocomplete = new google.maps.places.Autocomplete(
        document.getElementById("wcmlim_autocomplete_address"),
        {}
      );

      autocomplete.addListener("place_changed", fillInAddress);
    });

    function fillInAddress() {
      // Get the place details from the autocomplete object.
      const place = autocomplete.getPlace();
      if (!place.geometry) {
        window.alert("Autocomplete's returned place contains no geometry");
        return;
      }

      for (const component in componentForm) {
        document.getElementById(component).value = "";
        document.getElementById(component).disabled = false;
      }

      // Get each component of the address from the place details
      // and fill the corresponding field on the form.
      for (let i = 0; i < place.address_components.length; i++) {
        const addressType = place.address_components[i].types[0];
        if (componentForm[addressType]) {
          const val = place.address_components[i][componentForm[addressType]];
          document.getElementById(addressType).value = val;
        }
      }


      //get the co ordinates lat lng for location and fill

      var wcmlim_autocomplete_address = $("#wcmlim_autocomplete_address").val();
      $.ajax({
        url: multi_inventory.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "wcmlim_get_lat_lng",
          wcmlim_autocomplete_address: wcmlim_autocomplete_address,
          security: multi_inventory.check_nonce,
        },
        success(response) {
          var data = JSON.parse(JSON.stringify(response));
          let wcmlim_lat = data.latitude;
          let wcmlim_lng = data.longitude;
          if (($('#wcmlim_lat').length) && ($('#wcmlim_lat').length)) {
            $('#wcmlim_lat').val(wcmlim_lat);
            $('#wcmlim_lng').val(wcmlim_lng);
          }
        }
      });
      validation_add_location();


    }
  }

  const optionSelected = $(".locationsParent").val();
  if (optionSelected != -1) {
    $(
      ".term-address-wrap, .term-streetNumber-wrap, .term-route-wrap, .term-city-wrap, .term-state-wrap, .term-postcode-wrap, .term-country-wrap, .term-email-wrap, .term-shippingZone-wrap, .term-shopManager-wrap, .term-paymentMethods-wrap, .term-pos-wrap, .term-shippingMethod-wrap"
    ).hide();
  } else {
    $(
      ".term-address-wrap, .term-streetNumber-wrap, .term-route-wrap, .term-city-wrap, .term-state-wrap, .term-postcode-wrap, .term-country-wrap, .term-email-wrap, .term-shippingZone-wrap, .term-shopManager-wrap, .term-paymentMethods-wrap, .term-pos-wrap, .term-shippingMethod-wrap"
    ).show();
  }

  $(".locationsParent").on("change", function (e) {
    const valueSelected = this.value;
    if (valueSelected != -1) {
      $(
        ".term-address-wrap, .term-streetNumber-wrap, .term-route-wrap, .term-city-wrap, .term-state-wrap, .term-postcode-wrap, .term-country-wrap, .term-email-wrap, .term-shopManager-wrap, .term-paymentMethods-wrap, .term-pos-wrap, .term-shippingMethod-wrap"
      ).hide();
    } else {
      $(
        ".term-address-wrap, .term-streetNumber-wrap, .term-route-wrap, .term-city-wrap, .term-state-wrap, .term-postcode-wrap, .term-country-wrap, .term-email-wrap, .term-shopManager-wrap, .term-paymentMethods-wrap, .term-pos-wrap, .term-shippingMethod-wrap"
      ).show();
    }
  });

  const locationList = $("#locationList");

  $("#_manage_stock").change(function () {
    $(".wc_input_stock").prop("disabled", true);
    if (this.checked) {
      $("#inventory_product_data > #locationList").show();
    } else {
      $("#inventory_product_data > #locationList").hide();
    }
  });

  if ($("#_manage_stock").prop("checked")) {
    $("#locationList").show();
    $("#inventory_product_data > #locationList").show();
    $(".wc_input_stock").prop("disabled", true);

    const pp = $("#locationList > .locationInner  p > input[type='number']");
    let total = 0;
    pp.each((i, v) => {
      total += parseInt(v.value || 0);
    });
    const stockfieldValue = parseInt($(".wc_input_stock").val());
    if (total != stockfieldValue) {
      $("._stock_field").append(
        "<p style='color:red;'>The total stock doesn't match the sum of the locations stock. Please update location stock.</p>"
      );
    }
  } else {
    var productType = $("#product-type :selected").val();
    if (productType == "simple") {
      $("#locationList").hide();
      $("#inventory_product_data > #locationList").hide();
      $("._manage_stock_field").append(
        "<p style='color:red;'>To be able to manage stocks in Locations, please activate the <b>Stock Management</b> option.</p>"
      );
    }
  }
  $("#woocommerce-product-data").on("woocommerce_variations_loaded", () => {
    $(".woocommerce_variation").each((k, v) => {
      const chk = $(`input[name$="variable_manage_stock[${k}]"]`);
      if (chk.prop("checked") == false) {
        chk
          .closest("p.options")
          .append(
            "<p style='color:red;'>To be able to manage stocks in Locations, please activate the <b>Stock Management</b> option.</p>"
          );
      }
    });
    const ManageStock = $("input.variable_manage_stock");
    ManageStock.each((j, k) => {
      if (k.checked) {
        for (let i = 0; i < ManageStock.length; i++) {
          $(`input#variable_stock${i}`).prop("disabled", true);
        }
      }
    });
  });
  $(
    "#location,#chosen-select,#wcmlim_shipping_zone,#wcmlim_payment_methods,#wcmlim_shipping_method,#wcmlim_tax_locations"
  ).chosen({ width: "95%" });

  $("#wcmlim_shop_manager").chosen({ width: "95%" });

  $("#wcmlim_shop_regmanager").chosen({ width: "95%", max_selected_options: 1 });

  // Add Validation to "Exclude all locations" settings - User should not able to exclude all locations -code init
  var exclude_prod_onfront = passedData.keys;
  $("#wcmlim_exclude_locations_from_frontend").chosen({ max_selected_options: exclude_prod_onfront, width: "30%" });
  $("#wcmlim_exclude_locations_from_frontend").bind("chosen:maxselected", function () {
    jQuery('.exclude_prod_onfront').html('<p class="exclude_prod_onfront1">' + " You can't add all locations to exclude" + '</p>');
  });
  // Add Validation to "Exclude all locations" settings - User should not able to exclude all locations -code end

  // Add Validation to "Exclude all locations group" settings - User should not able to exclude all locations -code init
  var exclude_prodg_onfront = passedData_group.keys;
  $("#wcmlim_exclude_locations_group_frontend").chosen({ max_selected_options: exclude_prodg_onfront, width: "30%" });
  $("#wcmlim_exclude_locations_group_frontend").bind("chosen:maxselected", function () {
    jQuery('.exclude_prodg_onfront').html('<p class="exclude_prodg_onfront1">' + " You can't add all location groups to exclude" + '</p>');
  });
  // Add Validation to "Exclude all locations group" settings - User should not able to exclude all locations -code end

  $("#wcmlim_exclude_locations_group_frontend").chosen({ width: "30%" });
  $("#woocommerce-order-items").find('tr.item').each((index, elem) => {
    elem.classList.add('with-wcmlim');
  });
  $("#wcmlim_shipping_zone").on("change", () => {
    var selZone = $("#wcmlim_shipping_zone").val();
    $.ajax({
      url: multi_inventory.ajaxurl,
      type: "POST",
      dataType: "json",
      data: {
        action: "populate_shipping_methods",
        shippingMethods: selZone,
        security: multi_inventory.check_nonce,
      },
      success(response) {
        var data = JSON.parse(JSON.stringify(response));
        $('#wcmlim_shipping_method').empty();
        $.each(data, function (key, value) {
          $('#wcmlim_shipping_method')
            .append($("<option></option>")
              .attr("value", value.key)
              .text(value.value));
        });
        $('#wcmlim_shipping_method').trigger("chosen:updated");
      }
    });
  });

  $('#woocommerce-order-items').on('aftertablesort', '.woocommerce_order_items', (evt, data) => {

    const $table = $(evt.currentTarget);

    // Reposition the wcml rows after sorting.
    $table.find('tr.order-item-wcml-panel').each((index, elem) => {
      $(elem).insertAfter($table.find('tr.item.with-wcmlim').filter(`[data-order_item_id="${$(elem).data("order_item_id")}"]`));
    });

  })

  $(".keyEye").on("click", function (event) {
    event.preventDefault();
    $(".keyEye i").toggleClass("fa-eye fa-eye-slash");
    if ($("#wcmlim_google_api_key").attr("type") == "text") {
      $("#wcmlim_google_api_key").attr("type", "password");
    } else if ($("#wcmlim_google_api_key").attr("type") == "password") {
      $("#wcmlim_google_api_key").attr("type", "text");
    }
  });
  //validate google api 4 services
  $(".wcmlimvalidateGMAPI").on("click", function (event) {
    var gmapapi = $("#wcmlim_google_api_key").val();
    var apiKeyRegex = /^[A-Za-z0-9]{39}$/;

    if (gmapapi != null && gmapapi != "") {
      if (apiKeyRegex.test(gmapapi)) {
       
      
      $(".wcmlimvalidateGMAPI").html('<i class="fa fa-cog fa-spin"></i> Connecting');
      $.ajax({
        url: multi_inventory.ajaxurl,
        type: "POST",
        data: {
          action: "distance_matrix_validate_api",
          api: gmapapi,
        },
        success: function (response) {
          response = response.toString();
          response = response.trim();
          if (response == "valid") {
            alertify.set("notifier", "position", "bottom-right");
            alertify.success(
              "Google Map API is valid with Distance Matrix Service"
            );
          }
          else if (response == "You must enable Billing on the Google Cloud Project at https://console.cloud.google.com/project/_/billing/enable Learn more at https://developers.google.com/maps/gmp-get-started") {
            alertify.set("notifier", "position", "bottom-right");
            alertify.error(
              "You must enable Billing on the Google Cloud Project at https://console.cloud.google.com/project/_/billing/enable Learn more at https://developers.google.com/maps/gmp-get-started"
            );
          }
          else {
            alertify.set("notifier", "position", "bottom-right");
            alertify.error(
              "Google Map API is invalid with Distance Matrix Service, Please enable Distance Matrix Service"
            );
          }
        },
      });
      $.ajax({
        url: multi_inventory.ajaxurl,
        type: "POST",
        data: {
          action: "place_validate_api",
          api: gmapapi,
        },
        success: function (response) {
          response = response.toString();
          response = response.trim();
          if (response == "valid") {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.success("Google Map API is valid with Place API Service");
          }
          else if (response == "You must enable Billing on the Google Cloud Project at https://console.cloud.google.com/project/_/billing/enable Learn more at https://developers.google.com/maps/gmp-get-started") {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.error(
              "You must enable Billing on the Google Cloud Project at https://console.cloud.google.com/project/_/billing/enable Learn more at https://developers.google.com/maps/gmp-get-started"
            );
          }
          else {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.error(
              "Google Map API is invalid with Place API Service, Please enable Place API Service"
            );
          }
        },
      });
      //validate geocode
      $.ajax({
        url: multi_inventory.ajaxurl,
        type: "POST",
        data: {
          action: "geocode_validate_api",
          api: gmapapi,
        },
        success: function (response) {
          response = response.toString();
          response = response.trim();
          if (response == "valid") {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.success("Google Map API is valid with Geocode Service");
          } else {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.error(
              "Google Map API is invalid with Geocode Service, Please enable Geocode Service"
            );
          }
        },
      });
      //validate javascript api
      let mapjavascriptapi = document.createElement("script");
      var protocol = window.location.protocol == "https:" ? "https:" : "http:";
      mapjavascriptapi.setAttribute("src", `${protocol}//maps.googleapis.com/maps/api/js?libraries=geometry&sensor=false&key=${gmapapi}&callback`);
      document.body.appendChild(mapjavascriptapi);
      window.console = {
        error: function () {
          errorText = arguments["0"];
          if (errorText.includes("Google Maps JavaScript API error")) {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.error(
              "Google Map API is invalid with Map Javascript Service, Please enable Map Javascript Service"
            );
          }
          else {
            alertify.set("notifier", "position", "bottom-right");
            alertify.set("notifier", "delay", 8);
            alertify.success(
              "Google Map API is valid with Map Javascript Service"
            );
          }
        },
      };
      $(".wcmlimvalidateGMAPI").html("Validate");
      } 
      else
      {
        alertify.error(
          "Invalid Google APi key please check Google API Key"
        );
        $(".wcmlimvalidateGMAPI").html("Validate");
      }
    }else {
        alertify.set("notifier", "position", "bottom-right");
        alertify.set("notifier", "delay", 8);
        alertify.error(
          "Please enter Google API Key and try again"
        );
        $(".wcmlimvalidateGMAPI").html("Validate");
      }

  });

  if ($("#wcmlim_phone_validation").length > 0) {
    $('#wcmlim_phone_validation').on("input", function () {
      var phoneInput = this.value;
      if (phoneInput.length > 12 || phoneInput.length < 5) {
        $(".button").prop("disabled", true);
        $('#phonevalmsg').show();
        $("#phonevalmsg").css("color", "red");
        $('#phonevalmsg').html('<p style="color: #9c1d1d;margin: 4px 4px;font-weight: 400;text-align: left;"> Invalid Phone number, please enter digit beetween 5-12');
      }
      else {
        $(".button").prop("disabled", false);
        $('#phonevalmsg').hide();
      }
    });
  }
  //show time of parent location to sub location
  if ($(".locationsParent").length > 0) {
    var onloadlocationsParent = $("select.locationsParent").children("option:selected").val();
    if (onloadlocationsParent != "-1") {
      $("#start_time").prop("disabled", true);
      $("#end_time").prop("disabled", true);
      $("#wcmlim_phone_validation").prop("disabled", true);
      $(".term-time-wrap").hide();
      $(".term-phone-wrap").hide();
    }
    $("select.locationsParent").change(function () {
      $(".button").prop("disabled", true);
      $(".button").html("Fetching Details", true);
      var locationsParent = $(this).children("option:selected").val();
      if (locationsParent != "-1") {
        $.ajax({
          url: multi_inventory.ajaxurl,
          type: "post",
          data: {
            action: "show_parent_location_time",
            loc_id: locationsParent,
          },
          success(response) {
            var obj = JSON.parse(response);
            if (obj.error == 'yes') {
              window.alert("Something went wrong with the ajax.");
              return;
            }
            if (obj.start != "" && obj.end != "" && obj.error) {
              $("#start_time").val(obj.start);
              $("#end_time").val(obj.end);
              $("#wcmlim_phone_validation").val(obj.phone);
            }
            $("#start_time").prop("disabled", true);
            $("#end_time").prop("disabled", true);
            $("#wcmlim_phone_validation").prop("disabled", true);
            $(".term-time-wrap").hide();
            $(".term-phone-wrap").hide();
            $(".button").prop("disabled", false);
            $(".button").html("Update", true);
          },
        });
      } else {
        $("#start_time").prop("disabled", false);
        $("#end_time").prop("disabled", false);
        $("#wcmlim_phone_validation").prop("disabled", false);
        $(".term-time-wrap").show();
        $(".term-phone-wrap").show();
        $(".button").prop("disabled", false);
        $(".button").html("Update", true);
      }
    });
  }

  jQuery("#wcmlim_allow_only_backend").on('change', function () {
    if (jQuery(this).is(':checked')) {
      var switchStatus = jQuery(this).is(':checked');
      jQuery('#wcmlim_next_closest_location').prop('checked', false);
      jQuery('#wcmlim_hide_out_of_stock_location').prop('checked', false);
      jQuery('#wcmlim_clear_cart').prop('checked', false);
      jQuery('#wcmlim_enable_userspecific_location').prop('checked', false);
      jQuery('#wcmlim_preferred_location').prop('checked', false);
      jQuery('#wcmlim_enable_autodetect_location').prop('checked', false);
      jQuery('#wcmlim_enable_autodetect_location_by_maxmind').prop('checked', false);
      jQuery('#wcmlim_geo_location').prop('checked', false);
      jQuery('#wcmlim_suggestion_off').prop('checked', false);
      jQuery('#wcmlim_auto_billing_address').prop('checked', false);
      jQuery('#wcmlim_enable_price').prop('checked', false);
      jQuery('#wcmlim_hide_show_location_dropdown').prop('checked', false);
      jQuery('#wcmlim_enable_location_onshop').prop('checked', false);
      jQuery('#wcmlim_enable_location_price_onshop').prop('checked', false);
      jQuery('#wcmlim_sort_shop_asper_glocation').prop('checked', false);
      jQuery('#wcmlim_use_location_widget').prop('checked', false);
      jQuery('#wcmlim_enable_shipping_zones').prop('checked', false);
      jQuery('#wcmlim_enable_shipping_methods').prop('checked', false);
      jQuery('#wcmlim_assign_payment_methods_to_locations').prop('checked', false);

      jQuery('#wcmlim_order_fulfil_automatically').prop('checked', true);
      jQuery('#wcmlim_allow_local_pickup').prop('checked', true);
    }
  });

  jQuery("#wcmlim_allow_local_pickup").on('change', function () {
    if (jQuery(this).is(':checked')) {
      switchStatus = jQuery(this).is(':checked');
      //disable other settings
      jQuery('#wcmlim_next_closest_location').prop('checked', false);
      jQuery('#wcmlim_hide_out_of_stock_location').prop('checked', false);
      jQuery('#wcmlim_clear_cart').prop('checked', false);
      jQuery('#wcmlim_enable_userspecific_location').prop('checked', false);
      jQuery('#wcmlim_preferred_location').prop('checked', false);
      jQuery('#wcmlim_enable_autodetect_location').prop('checked', false);
      jQuery('#wcmlim_enable_autodetect_location_by_maxmind').prop('checked', false);
      jQuery('#wcmlim_geo_location').prop('checked', false);
      jQuery('#wcmlim_suggestion_off').prop('checked', false);
      jQuery('#wcmlim_auto_billing_address').prop('checked', false);
      jQuery('#wcmlim_enable_price').prop('checked', false);
      jQuery('#wcmlim_hide_show_location_dropdown').prop('checked', false);
      jQuery('#wcmlim_enable_location_onshop').prop('checked', false);
      jQuery('#wcmlim_enable_location_price_onshop').prop('checked', false);
      jQuery('#wcmlim_sort_shop_asper_glocation').prop('checked', false);
      jQuery('#wcmlim_use_location_widget').prop('checked', false);
      jQuery('#wcmlim_enable_shipping_zones').prop('checked', false);
      jQuery('#wcmlim_enable_shipping_methods').prop('checked', false);
      jQuery('#wcmlim_assign_payment_methods_to_locations').prop('checked', false);

      jQuery('#wcmlim_allow_only_backend').prop('checked', true);
      jQuery('#wcmlim_allow_local_pickup').prop('checked', true);
    }
  });
  //inline stock edit code starts here

  $(".wcmlim_update_inline_stock").on("click", (e) => {
    $(".wcmlim_update_inline_stock_msg").css('visibility', 'unset');
    $(".wcmlim_update_inline_stock_msg").html('<i class="fas fa-spinner fa-spin"></i> Loading');
    var location_stock = $('.wcmlim_stock_modal_location_stock').val();
    var product_id = $('.wcmlim_stock_modal_product_id').val();
    var location_id = $('.wcmlim_stock_modal_location_id').val();
    $('#stock_data_attr_change_' + product_id + '_' + location_id).attr('data-stock', location_stock);

    $('.change_' + product_id + '_' + location_id).html('(' + location_stock + ')');
    $.ajax({
      url: multi_inventory.ajaxurl,
      type: "post",
      data: {
        action: "update_stock_inline",
        location_stock: location_stock,
        product_id: product_id,
        location_id: location_id,
      },
      success(response) {
        if (response) {
          $(".wcmlim_update_inline_stock_msg").html('Updated Stock Successfully!').delay(5000).fadeIn();
          setTimeout(function () {
            $(".wcmlim_update_inline_stock_msg").css('visibility', 'hidden');
          }, 3000);
        }
        var modal = document.getElementById("stockModal");
        modal.style.display = "none";
        location.reload();
      },
    });

  });
  //inline price edit code starts here

  $(".wcmlim_update_inline_price").on("click", (e) => {
    $(".wcmlim_update_inline_stock_msg").css('visibility', 'unset');
    $(".wcmlim_update_inline_stock_msg").html('<i class="fas fa-spinner fa-spin"></i> Loading');
    var regular_price = $('.wcmlim_stock_modal_location_regular_price').val();
    var sale_price = $('.wcmlim_stock_modal_location_sale_price').val();

    var product_id = $('.wcmlim_stock_modal_product_id').val();
    var location_id = $('.wcmlim_stock_modal_location_id').val();
    var currency = $('.wcmlim_stock_modal_currency').val();
    var ophtml = '';
    if (parseFloat(regular_price) === 'NaN') {
      $(".wcmlim_update_inline_stock_msg").html('<span class="wcmlim_modal_validation">Entered Regular Price is Invalid</span>').delay(1000).fadeIn();
      setTimeout(function () {
        $(".wcmlim_update_inline_stock_msg").css('visibility', 'hidden');
      }, 1000000);
    }
    else if (parseFloat(sale_price) === 'NaN' && regular_price != null) {
      $(".wcmlim_update_inline_stock_msg").html('<span class="wcmlim_modal_validation">Entered Sale Price is Invalid</span>').delay(1000).fadeIn();
      setTimeout(function () {
        $(".wcmlim_update_inline_stock_msg").css('visibility', 'hidden');
      }, 1000000);
    }
    else if ((regular_price == '') && (sale_price != '')) {
      $(".wcmlim_update_inline_stock_msg").html('<span class="wcmlim_modal_validation">Regular Price is missing !</span>').delay(1000).fadeIn();
      setTimeout(function () {
        $(".wcmlim_update_inline_stock_msg").css('visibility', 'hidden');
      }, 1000000);
    }
    //regular price is not greater than sale price
    else if ((regular_price != '') && (sale_price != '') && (parseFloat(regular_price) < parseFloat(sale_price))) {
      $(".wcmlim_update_inline_stock_msg").html('<span class="wcmlim_modal_validation">Regular Price should be greater than Sale Price !</span>').delay(1000).fadeIn();
      setTimeout(function () {
        $(".wcmlim_update_inline_stock_msg").css('visibility', 'hidden');
      }, 1000000);
    }



    else {
      if (sale_price != '' && sale_price != null && sale_price != "NaN") {
        regular_price = parseFloat(regular_price).toFixed(2);
        sale_price = parseFloat(sale_price).toFixed(2);
        ophtml = '<del aria-hidden="true"><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + regular_price + '</span></del> <ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + sale_price + '</span></ins>';
      }
      else {
        regular_price = parseFloat(regular_price).toFixed(2);
        ophtml = '<ins><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + regular_price + '</span></ins>';
      }
      //reset data attr    
      $('#price_data_attr_change_' + product_id + '_' + location_id).attr('data-saleprice', sale_price);
      $('#price_data_attr_change_' + product_id + '_' + location_id).attr('data-regularprice', regular_price);
      $('.price_change_' + product_id + '_' + location_id).html('(' + ophtml + ')');
      $.ajax({
        url: multi_inventory.ajaxurl,
        type: "post",
        data: {
          action: "update_price_inline",
          sale_price: sale_price,
          regular_price: regular_price,
          product_id: product_id,
          location_id: location_id,
        },
        success(response) {
          if (response) {
            $(".wcmlim_update_inline_stock_msg").html('Updated Price Successfully!').delay(5000).fadeIn();
            setTimeout(function () {
              $(".wcmlim_update_inline_stock_msg").css('visibility', 'hidden');
            }, 3000);
          }
          var modal = document.getElementById("priceModal");
          modal.style.display = "none";
        },
      });
    }



  });




  $(".wcmlim_edit_stock_pro_list").on("click", (e) => {
    var product_id = e.target.getAttribute("data-id");
    var location_id = e.target.getAttribute("data-location");
    var stock = e.target.getAttribute("data-stock");
    var productname = e.target.getAttribute("data-productname");
    var locationname = e.target.getAttribute("data-locationname");
    $('.wcmlim_stock_modal_product_name').text(productname);
    $('.wcmlim_stock_modal_location_name').text('Stock At ' + locationname);
    $('.wcmlim_stock_modal_location_stock').val(stock);
    $('.wcmlim_stock_modal_product_id').val(product_id);
    $('.wcmlim_stock_modal_location_id').val(location_id);

    var modal = document.getElementById("stockModal");
    var span = document.getElementsByClassName("wcmlim-close")[0];
    modal.style.display = "block";
    span.onclick = function () {
      modal.style.display = "none";
    }
    window.onclick = function (event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }

  });

  $(".wcmlim_edit_price_pro_list").on("click", (e) => {
    $('#priceModal').css("padding-top", "13%");
    var product_id = e.target.getAttribute("data-id");
    var location_id = e.target.getAttribute("data-location");
    var regularprice = e.target.getAttribute("data-regularprice");
    var salesprice = e.target.getAttribute("data-saleprice");
    var currency = e.target.getAttribute("data-currency");
    var productname = e.target.getAttribute("data-productname");
    var locationname = e.target.getAttribute("data-locationname");
    $('.wcmlim_stock_modal_product_name').text(productname);
    $('.wcmlim_stock_modal_location_name').text('Selected Location - ' + locationname);
    $('.wcmlim_stock_modal_location_sale_price').val(salesprice);
    $('.wcmlim_stock_modal_location_regular_price').val(regularprice);
    $('.wcmlim_stock_modal_product_id').val(product_id);
    $('.wcmlim_stock_modal_location_id').val(location_id);
    $('.wcmlim_stock_modal_currency').val(currency);
    $('.wcmlim_price_currency').text(currency);
    var modal = document.getElementById("priceModal");
    var span = document.getElementsByClassName("wcmlim-price-modal-close")[0];
    modal.style.display = "block";
    span.onclick = function () {
      modal.style.display = "none";
    }
    window.onclick = function (event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }

  });

});


//no scroll for variation stocks -code init
document.addEventListener("wheel", function (event) {
  if (document.activeElement.type === "number" &&
    document.activeElement.classList.contains("noscroll")) {
    document.activeElement.blur();
  }
});
jQuery(document).ready(($) => {
  if ($('#wcmlim_use_location_widget').prop("checked")) {
    $("#wcmlim_option_for_selection").show();
  } else {
    $("#wcmlim_option_for_selection").hide();
  }

  $("#wcmlim_use_location_widget").click(function () {
    if ($('#wcmlim_use_location_widget').prop("checked")) {
      $("#wcmlim_option_for_selection").show();
    } else {
      $("#wcmlim_option_for_selection").hide();
    }
  });

  // Toggle Switch Enable/Disable
  jQuery("#wcmlim_allow_only_backend").on('change', function () {

    if (jQuery(this).is(':checked')) {

      var switchStatus = jQuery(this).is(':checked');
      jQuery("label[for='tab1']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
      jQuery("label[for='tab3']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
      jQuery("label[for='tab4']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
      jQuery("label[for='tab5']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
      jQuery("label[for='tab6']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });

      jQuery('#wcmlim_enable_userspecific_location').parents('tr').hide();
      jQuery('#wcmlim_exclude_locations_from_frontend').parents('tr').hide();
      jQuery('#wcmlim_next_closest_location').parents('tr').hide();
      jQuery('#wcmlim_distance_calculator_by_coordinates').parents('tr').hide();
      jQuery('#wcmlim_hide_out_of_stock_location').parents('tr').hide();
      jQuery('#wcmlim_clear_cart').parents('tr').hide();
      jQuery('#wcmlim_pos_compatiblity').parents('tr').hide();
      jQuery('#wcmlim_wc_pos_compatiblity').parents('tr').hide();
      jQuery('#general_setting_form').children('h2').hide();

      jQuery('#tab1').attr('disabled', 'true');
      jQuery('#tab3').attr('disabled', 'true');
      jQuery('#tab4').attr('disabled', 'true');
      jQuery('#tab5').attr('disabled', 'true');
      jQuery('#tab6').attr('disabled', 'true');

      //disable other settings
      jQuery('#wcmlim_next_closest_location').prop('checked', false);
      jQuery('#wcmlim_hide_out_of_stock_location').prop('checked', false);
      jQuery('#wcmlim_clear_cart').prop('checked', false);
      jQuery('#wcmlim_enable_userspecific_location').prop('checked', false);
      jQuery('#wcmlim_preferred_location').prop('checked', false);
      jQuery('#wcmlim_enable_autodetect_location').prop('checked', false);
      jQuery('#wcmlim_enable_autodetect_location_by_maxmind').prop('checked', false);
      jQuery('#wcmlim_geo_location').prop('checked', false);
      jQuery('#wcmlim_suggestion_off').prop('checked', false);
      jQuery('#wcmlim_auto_billing_address').prop('checked', false);
      jQuery('#wcmlim_enable_price').prop('checked', false);
      jQuery('#wcmlim_hide_show_location_dropdown').prop('checked', false);
      jQuery('#wcmlim_enable_location_onshop').prop('checked', false);
      jQuery('#wcmlim_enable_location_price_onshop').prop('checked', false);
      jQuery('#wcmlim_sort_shop_asper_glocation').prop('checked', false);
      jQuery('#wcmlim_use_location_widget').prop('checked', false);
      jQuery('#wcmlim_enable_shipping_zones').prop('checked', false);
      jQuery('#wcmlim_enable_shipping_methods').prop('checked', false);
      jQuery('#wcmlim_assign_payment_methods_to_locations').prop('checked', false);
      jQuery('#wcmlim_order_fulfil_automatically').prop('checked', true);
      jQuery('#wcmlim_allow_local_pickup').prop('checked', true);
    } else {

      jQuery('#wcmlim_enable_userspecific_location').parents('tr').show();
      jQuery('#wcmlim_exclude_locations_from_frontend').parents('tr').show();
      jQuery('#wcmlim_next_closest_location').parents('tr').show();
      jQuery('#wcmlim_distance_calculator_by_coordinates').parents('tr').show();
      jQuery('#wcmlim_hide_out_of_stock_location').parents('tr').show();
      jQuery('#wcmlim_clear_cart').parents('tr').show();
      jQuery('#wcmlim_pos_compatiblity').parents('tr').show();
      jQuery('#wcmlim_wc_pos_compatiblity').parents('tr').show();
      jQuery('#general_setting_form').children('h2').show();

      jQuery("label[for='tab1']").css({ 'cursor': '', 'filter': '' });
      jQuery("label[for='tab3']").css({ 'cursor': '', 'filter': '' });
      jQuery("label[for='tab4']").css({ 'cursor': '', 'filter': '' });
      jQuery("label[for='tab5']").css({ 'cursor': '', 'filter': '' });
      jQuery("label[for='tab6']").css({ 'cursor': '', 'filter': '' });
      jQuery('#tab1').removeAttr("disabled");
      jQuery('#tab3').removeAttr("disabled");
      jQuery('#tab4').removeAttr("disabled");
      jQuery('#tab5').removeAttr("disabled");
      jQuery('#tab6').removeAttr("disabled");
    }
  });

  if (jQuery('#wcmlim_allow_only_backend').is(':checked')) {
    jQuery('#wcmlim_enable_userspecific_location').parents('tr').hide();
    jQuery('#wcmlim_exclude_locations_from_frontend').parents('tr').hide();
    jQuery('#wcmlim_next_closest_location').parents('tr').hide();
    jQuery('#wcmlim_distance_calculator_by_coordinates').parents('tr').hide();
    jQuery('#wcmlim_hide_out_of_stock_location').parents('tr').hide();
    jQuery('#wcmlim_clear_cart').parents('tr').hide();
    jQuery('#wcmlim_pos_compatiblity').parents('tr').hide();
    jQuery('#wcmlim_wc_pos_compatiblity').parents('tr').hide();
    jQuery('#general_setting_form').children('h2').hide();

    jQuery("label[for='tab1']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
    jQuery("label[for='tab3']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
    jQuery("label[for='tab4']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
    jQuery("label[for='tab5']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });
    jQuery("label[for='tab6']").css({ 'cursor': 'not-allowed', 'filter': 'blur(1px)' });

    jQuery('#tab1').attr('disabled', 'true');
    jQuery('#tab3').attr('disabled', 'true');
    jQuery('#tab4').attr('disabled', 'true');
    jQuery('#tab5').attr('disabled', 'true');
    jQuery('#tab6').attr('disabled', 'true');
  } else {

    jQuery('#wcmlim_enable_userspecific_location').parents('tr').show();
    jQuery('#wcmlim_exclude_locations_from_frontend').parents('tr').show();
    jQuery('#wcmlim_next_closest_location').parents('tr').show();
    jQuery('#wcmlim_distance_calculator_by_coordinates').parents('tr').show();
    jQuery('#wcmlim_hide_out_of_stock_location').parents('tr').show();
    jQuery('#wcmlim_clear_cart').parents('tr').show();
    jQuery('#wcmlim_pos_compatiblity').parents('tr').show();
    jQuery('#wcmlim_wc_pos_compatiblity').parents('tr').show();
    jQuery('#general_setting_form').children('h2').show();

    jQuery("label[for='tab1']").css({ 'cursor': '', 'filter': '' });
    jQuery("label[for='tab3']").css({ 'cursor': '', 'filter': '' });
    jQuery("label[for='tab4']").css({ 'cursor': '', 'filter': '' });
    jQuery("label[for='tab5']").css({ 'cursor': '', 'filter': '' });
    jQuery("label[for='tab6']").css({ 'cursor': '', 'filter': '' });

    jQuery('#tab1').removeAttr("disabled");
    jQuery('#tab3').removeAttr("disabled");
    jQuery('#tab4').removeAttr("disabled");
    jQuery('#tab5').removeAttr("disabled");
    jQuery('#tab6').removeAttr("disabled");

  }
  jQuery(document).on('click', '.wcmlim_localpickup_instance_enabled', function () {

    var this_value = '';
    if (jQuery(this).is(':checked')) {
      this_value = 1;
    } else {
      this_value = 0;
    }

    var this_id = jQuery(this).attr('id');
    var instance_id = jQuery('input[name="instance_id').val();
    var url_string = window.location.href;
    var url = new URL(url_string);
    var zone_id = url.searchParams.get("zone_id");
    jQuery.ajax({
      url: multi_inventory.ajaxurl,
      type: 'POST',
      data: {
        action: 'wcmlim_save_localpickup_instance_enabled',
        instance_id: instance_id,
        zone_id: zone_id,
        this_value: this_value,
        this_id: this_id
      },
      success: function (response) {

      }
    });

  });
  //on click of class wc-shipping-zone-method-settings
  jQuery(document).on('click', '.wc-shipping-zone-method-settings', function () {
    //find the class wcmlim_localpickup_instance_enabled 
    load_shipping_instance_checkers();

  });

  function load_shipping_instance_checkers() {
    jQuery('.wcmlim_localpickup_instance_enabled').each(function () {

      var this_id = jQuery(this).attr('id');
      var instance_id = jQuery('input[name="instance_id').val();
      var url_string = window.location.href;
      var url = new URL(url_string);
      var zone_id = url.searchParams.get("zone_id");
      jQuery.ajax({
        url: multi_inventory.ajaxurl,
        type: 'POST',
        data: {
          action: 'wcmlim_fetch_localpickup_instance_enabled',
          instance_id: instance_id,
          zone_id: zone_id,
          this_id: this_id
        },
        success: function (response) {
          if (response == "checked") {
            jQuery('#' + this_id).prop('checked', true);
          }
          else {
            jQuery('#' + this_id).prop('checked', false);
          }
        }
      });
    });
  }
  $(document).on("keypress", ".woocommerce.wcmlim_product_regular_price", function (e) {
    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
      return false;
    }
  });
  $(document).on("keypress", ".woocommerce.wcmlim_product_sale_price", function (e) {
    if (e.which != 8 && e.which != 0 && e.which != 46 && (e.which < 48 || e.which > 57)) {
      return false;
    }
  });
  //check sale price is greater than regular price then show error below sale price
  $(document).on("blur", ".woocommerce.wcmlim_product_sale_price", function (e) {

    var sale_price = $(this).val();
    var regular_price = $(this).parent().parent().find('.wcmlim_product_regular_price').val();
    if (parseFloat(sale_price) > parseFloat(regular_price)) {
      Swal.fire({
        icon: "error",
        text: "Sale Price can not be greater than Regular Price !",
      });
      $(this).val('');
    }
  });
  jQuery('.userRestrictNote').length && jQuery(document).ready(function () {
  
	    
	  function checkIfRestrictTrue(snote){
		var sswitch = document.querySelector(".userRestrict input[type=checkbox]:checked");
    
		if (sswitch) {

		  snote.style.display = "block";
		}
		else {
		  snote.style.display = "none";
		}
	  
	}
	  
	  
  var snote = document.querySelector(".userRestrictNote");
  snote.style.display = "none";	  
  checkIfRestrictTrue(snote);
  jQuery(".userRestrict").click(function () {
   
	  checkIfRestrictTrue(snote);
  });
});
});

//bulk edit code starts here
// Get the modal

// Get the <span> element that closes the modal
jQuery(document).ready(function () {
  var modal = jQuery('#bulk-edit-modal');
  
  // When the user clicks on <span> (x), close the modal
  jQuery('.bulk-edit-modal-close').on('click', function() {
      modal.hide();
  });

  // When the user clicks anywhere outside of the modal, close it
  jQuery(window).on('click', function(event) {
      if (event.target === modal[0]) {
          modal.hide();
      }
  });
});

jQuery(document).ready (function($) {
    // Bulk edit button action

    jQuery('#cb-select-all-2').click(function() {
      if ($(this).is(':checked')) {
         // console.log('checked');
          $("input[type='checkbox']").prop('checked', true);
      } else {
        //  console.log('unchecked');
          $("input[type='checkbox']").prop('checked', false);
      }
  });

    $('#bulk-edit-btn').click(function() {
      // check setting is on
      var isChecked = $(".SIMS_options_li #cb").prop("checked"); 
      // Check if the checkbox is checked
      if (isChecked) { 
        // Get the selected products
        var products = [];
        
        $('.check-column input:checked').each(function() {
            products.push($(this).val());
            
        });

        // If no products are selected, display an alert
        if (products.length === 0) {
          //add alertify error 
            alertify.error('Please select multiple products for bulk editing.');
            return;
        } 
        // Show the modal
        $('#bulk-edit-modal').show();
      }
      else
      {
          alertify.error('Please enable bulk edit option from settings');
      }
    });
  
 

    // Apply changes button action
    $('#apply-changes').click(function() {
        // Get the selected products
        var products = [];
        $('.check-column input:checked').each(function() {
            products.push($(this).val());
            //reload page
            location.reload();
       
        });

        // Get the stock location
        var stockLocation = $('#stock_location').val();
        //get each input with class stock_location and its data-locid
        let bulk_update_loc = [];
          $('.stock_location').each(function() {
              var stock_location = $(this).val();
              var locid = $(this).data('locid');
              let obj = {
                  locid: locid,
                  value: stock_location
              };
              bulk_update_loc.push(obj);
          });
          

        // Make the AJAX call
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bulk_edit_products',
                products: products,
                bulk_update_loc: bulk_update_loc,
                nonce: $('#bulk-edit-nonce').val()
            },
            success: function(response) {
                // Hide the modal
                $('#bulk-edit-modal').hide();

                // Display a success message
                //alertify success message
                alertify.success(response.data);
            }
        });
      
   
    });

    // Check if the checkbox is checked
    $('#wcmlim_enable_location_group').change(function() {
      if ($(this).is(':checked')) {
          // AJAX request to check if at least one location group is added and mapped properly
          $.ajax({
              type: 'POST',
              url: ajaxurl,
              data: {
                  'action': 'check_location_group'
              },
              success: function(response) {
                  if (response === 'false') {
                      alert("Please add at least one location group and map it properly.");
                      // Uncheck the checkbox
                      $('#wcmlim_enable_location_group').prop('checked', true);
                  }
              }
          });
      }
  });
});
