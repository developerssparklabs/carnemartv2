import * as alies_localization from "./wcmlim_utility/generic/wcmlim_localization.js";
import * as alies_listOrdering from "./wcmlim_utility/generic/wcmlim_listOrdering.js";
import * as alies_sc_listOrdering from "./wcmlim_utility/generic/wcmlim_sc_listOrdering.js";
//import * as alies_getcookies from "./wcmlim_utility/generic/wcmlim_getcookies.js";
//import * as alies_setcookies from "./wcmlim_utility/generic/wcmlim_setcookies.js";
import * as alies_mapWidget from "./wcmlim_utility/generic/wcmlim_map_widget.js";
import * as alies_selectLocation from "./wcmlim_utility/generic/wcmlim_select_location.js";
//import * as alies_showPosition from "./wcmlim_utility/generic/wcmlim_show_possition.js";
import * as alies_GlobalMapList from "./wcmlim_utility/generic/wcmlim_global_maplist.js";
import * as alies_setLocation from "./wcmlim_utility/generic/wcmlim_set_location.js";
import * as alies_cookieExists from "./wcmlim_utility/generic/wcmlim_cookie_exists.js";
import * as alies_VariationIdCheck from "./wcmlim_utility/generic/wcmlim_variation_check.js";
import * as alies_CommonFunction from "./wcmlim_utility/generic/wcmlim_common_functions.js";
import * as alies_ChangeLc from "./wcmlim_utility/generic/wcmlim_change_lc_select.js";
import * as alies_SubmitPostcodeProduct from "./wcmlim_utility/generic/wcmlim_submit_postcode_product.js";

//import {test} from "./wcmlim_utlity/generic/wcmlim_localization.js";

var localization = alies_localization.wcmlim_localization();

jQuery(document).ready(($) => {
  $.noConflict();
  alies_CommonFunction.Hide_OOS_Product_Allover();

  alies_CommonFunction.NearbyLocationFn();

  alies_listOrdering.listOrdering();
  alies_sc_listOrdering.sc_listOrdering();
  alies_mapWidget.mapWidget();
  alies_GlobalMapList.GlobalMapList();
  alies_GlobalMapList.rangeInputCallback();

  alies_CommonFunction.autodetectFn();

  alies_CommonFunction.Restricted_Location();
  alies_cookieExists.CookieExists();
  alies_VariationIdCheck.variationIdExists();
  alies_CommonFunction.isLocationsGroupFn();

  $("#select_location").on("change", function (e) {
    if ($(".wclimadvlist").length < 1) {
      alies_selectLocation.select_location(this);
    }
  });

  let loader = alies_CommonFunction.loaderhtml();
  alies_SubmitPostcodeProduct.submitPostcodeProduct(loader);

  /**
   * Pincode change.
   */
  $(document).on("click", "[data-wpzc-form-open]", (e) => {
    e.preventDefault();

    if ($(".postcode-checker-div").hasClass("postcode-checker-div-show")) {
      $(".class_post_code").val("");
      $(".postcode-checker-change").hide();
      $(".postcode-location-distance").hide();
      $(".postcode-checker-div")
        .removeClass("postcode-checker-div-show")
        .addClass("postcode-checker-div-hide");
      $(".postcode-checker-response").empty();
    } else {
      $(".postcode-checker-div")
        .removeClass("postcode-checker-div-hide")
        .addClass("postcode-checker-div-show");
      $(".postcode-checker-change").show();
      $(".postcode-location-distance").show();
    }
  });

  alies_ChangeLc.ChangeLcSelectWithLocation();
  alies_ChangeLc.ChangeLc_Select();
  alies_CommonFunction.wcmlim_locwid_dd();
  alies_CommonFunction.post_code_checker_common("class_post_code");
  alies_CommonFunction.post_code_checker_common("class_post_code_global");

  $("input[type=radio][name=select_location]").change(function () {
    var stockQt = $("#select_location")
      .find("option:selected")
      .attr("data-lc-qty");
    var text_qty = document.getElementsByClassName("qty")[0].value;
    const boStatus = $("#backorderAllowed").val();

    if (boStatus != 1) {
      if (stockQt < text_qty) {
        $(".qty").attr({ max: stockQt });
        document.getElementById("lc_qty").value = stockQt;
        document.getElementsByClassName("qty")[0].value = 1;
      } else {
        $(".qty").attr({ max: stockQt });
        document.getElementById("lc_qty").value = stockQt;
      }
    }
  });

  $("#select_location").on("change", function () {
    var addcartid = $(this).data("lc-id");
    var term_key = $(this).val();
    var product_simple = jQuery(".single_add_to_cart_button").val();
    var product_vid = jQuery("input.variation_id").val();
    var prodsaleprice = $(this).find(":selected").data("lc-sale-price");
    var prodcity = $(this).find(":selected").data("lc-city");
    var productprice = productprice ? prodregularprice : prodsaleprice;
    var product_qty = $(".qty").val();
    var prodregularprice = $(this).find(":selected").data("lc-regular-price");
    var prod_backorder = $(this).find(":selected").data("lc-backorder");
    if (localization.isBackorderOn == "on") {
      $("#globMsg").hide();
      $("#losm").hide();
      $.ajax({
        url: localization.ajaxurl,
        type: "post",
        data: {
          action: "wcmlim_backorder4el",
          term_key: term_key,
          addcartid: addcartid,
          prodregularprice: prodregularprice,
          prodsaleprice: prodsaleprice,
          productprice: productprice,
          product_qty: product_qty,
          prodcity: prodcity,
          product_vid: product_vid,
          product_simple: product_simple,
        },
        success(response) {
          if (response == "") {
            $(".single_add_to_cart_button").hide();
            $(".quantity").hide();
          }
          if (response == "show_btn" || prod_backorder == "yes") {
            var quantityInput = document.getElementsByName("quantity")[0];

            $(".single_add_to_cart_button").show();
            $(".single_add_to_cart_button").removeClass("disabled");
            $(".input-text").show();
            $(".quantity").show();
            $(".qty").removeAttr("max");
            $("#losm").hide();
            $(".stock").hide();
            $("#globMsg").hide();

            // Create a new input element with type "number"
            var newInput = document.createElement("input");
            newInput.type = "number";
            newInput.className = quantityInput.className; // Copy classes
            newInput.name = quantityInput.name;
            newInput.value = quantityInput.value;
            newInput.setAttribute(
              "aria-label",
              quantityInput.getAttribute("aria-label")
            );
            newInput.setAttribute("size", quantityInput.getAttribute("size"));
            newInput.setAttribute("min", quantityInput.getAttribute("min"));
            newInput.setAttribute("step", quantityInput.getAttribute("step"));
            newInput.setAttribute(
              "placeholder",
              quantityInput.getAttribute("placeholder")
            );
            newInput.setAttribute(
              "inputmode",
              quantityInput.getAttribute("inputmode")
            );
            newInput.setAttribute(
              "autocomplete",
              quantityInput.getAttribute("autocomplete")
            );
            newInput.style = quantityInput.style; // Copy styles

            // Replace the existing input with the new one
            quantityInput.parentNode.replaceChild(newInput, quantityInput);
          } else {
            //console.log(response);
            if (response == "ofs") {
              $(".single_add_to_cart_button").addClass("disabled");
              $("#losm").show();
            }
            if (response == "instk") {
              $(".single_add_to_cart_button").removeClass("disabled");
              $("#losm").hide();
              $(".stock").show();
              $("#globMsg").show();
            } else {
              $("#globMsg").show();
            }
          }
        },
      });
    }
  });
  //trigger change event after 5 second
  setTimeout(function () {
    $("#select_location").trigger("change");
  }, 2000);

  //removed as allowed specific location is default

  $(document).ready(function () {
    //fetch product name of current selected item
    function getProductName(button) {
      return jQuery(button)
        .closest(".product-wrapper")
        .find("h3.wd-entities-title")
        .text()
        .trim();
    }
    //on click of +/-/add to cart trigger action for shop page
    jQuery(".wd-add-btn-replace").on(
      "click",
      ".plus, .minus,.add_to_cart_button.wcmlim_ajax_add_to_cart",
      function () {
        var quantityInput = jQuery(this)
          .closest(".wd-add-btn-replace")
          .find(".input-text.qty.text");
        var locationQty = jQuery(this)
          .closest(".wd-add-btn-replace")
          .find("a.add_to_cart_button")
          .data("location_qty");
        var locationName = jQuery(this)
          .closest(".wd-add-btn-replace")
          .find("a.add_to_cart_button")
          .data("selected_location");
        var productName = getProductName(this);

        var enteredQuantity = parseInt(quantityInput.val()); // Get the entered quantity

        quantityInput.attr("max", locationQty, locationName);

        // Check if the entered quantity is less than 1
        if (enteredQuantity < 1) {
          Swal.fire({ icon: "error", text: "Quantity cannot be 0." });
          return;
        }

        // Check if the entered quantity exceeds the maximum allowed value
        if (enteredQuantity > locationQty) {
          Swal.fire({
            icon: "error",
            text:
              "Sorry, we do not have enough <strong>" +
              productName +
              "</strong> in stock to fulfill your order (" +
              locationQty +
              " available) for " +
              locationName +
              ". We apologize for any inconvenience caused.",
          });
          return;
        }
      }
    );

    setTimeout(function () {
      if (jQuery(".variations").length > 0) {
        $(".variations").trigger("click");
      }
    }, 2000);
  });

  // hide location dropdwon variation
  alies_CommonFunction.hideDropdown();
  // code -end
});
jQuery(document).ready(function ($) {
  if (wcmlim_public.hideDetailsOnHover) {
    // Apply the CSS to hide the element on hover using jQuery
    $("#wcmlim_select_or_dropdown").hover(
      function () {
        // Mouse over
        $(this).find(".quantity").hide(); // Corrected class selectors
      },
      function () {
        // Mouse out
        $(this).find(".quantity").show(); // Corrected class selectors
      }
    );
  }
  /* AST Code for diable the Apply button Starts */
  var zipcode_val = $("#elementIdGlobal").val();
  $("#elementIdGlobal").keyup(function () {
    zipcode_val = $(this).val();
    if (zipcode_val == "") {
      $("#submit_postcode_global").attr("disabled", true);
    } else {
      $("#submit_postcode_global").attr("disabled", false);
    }
  });

  /* AST Code for diable the Apply button End */

  var hiddenValue = jQuery('input[name="global_postal_location"]').val();

  // Set the retrieved value as the placeholder for the text input
  jQuery('input[name="post_code_global"]').attr("placeholder", hiddenValue);
});
