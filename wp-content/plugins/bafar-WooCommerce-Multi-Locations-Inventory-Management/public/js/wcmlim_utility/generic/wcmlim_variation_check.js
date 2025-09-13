import * as alies_localization from "./wcmlim_localization.js";
import * as alies_getcookies from "./wcmlim_getcookies.js";
import * as alies_listOrdering from "./wcmlim_listOrdering.js";
import * as alies_CommonFunction from "./wcmlim_common_functions.js";

var localization = alies_localization.wcmlim_localization();

export function variationIdExists() {

  if (jQuery(".variation_id").length) {
    jQuery(".variation_id").change(() => {
      jQuery(".wcmlim_product").hide();
      if (
        jQuery("#globMsg, #losm, #seloc, #locsoldImg, #locstockImg").length > 0
      ) {
        jQuery("#globMsg, #losm, #seloc, #locsoldImg, #locstockImg").remove();
      }
      if (jQuery(".variation_id").val() != "") {
        jQuery(".quantity").show();
        const product_id = jQuery("input.variation_id").val();
        jQuery.ajax({
          type: "POST",
          url: localization.ajaxurl,
          data: {
            product_id,
            action: "wcmlim_display_location",
          },
          success(output) {
            const select = JSON.parse(output);
			  
            
             if (select && select.manage_stock && select.manage_stock == 'no') {
              return true;
          }
          
            // Hide elements
            jQuery("#locations_time, .sel_location.Wcmlim_sel_loc").hide();
            jQuery(".wcmlim-lcswitch").show();
            jQuery(".rselect_location").empty();
        
            // Show/hide wcmlim_product based on configuration
            if (select.show_wcmlim_product === "hide") {
                jQuery(".wcmlim_product").hide();
            } else {
                jQuery(".wcmlim_product").show();
            }
        
            const sel_stock_status = String(select.stock_status);
        
            // Show/hide add to cart button based on stock status
			if (sel_stock_status === "outofstock") {
			 
				jQuery(".woocommerce-variation-add-to-cart.variations_button.woocommerce-variation-add-to-cart-enabled").hide();
				jQuery(".qty, .single_add_to_cart_button").hide();
			} else {
				 
				jQuery(".single_add_to_cart_button").show();
       			jQuery(".quantity").show();
			}
        
            const size = Object.keys(select).length;
            const pop = jQuery("#productOrgPrice").val();
            jQuery(".select_location").empty();
            const slv = jQuery("#select_location").val();
        
            // Show/hide elements based on selected location
            if (!slv) {
                if (jQuery("#losm").length > 0) {
                    jQuery("#losm").hide();
                }
                if (localization.hideDropdown !== "on") {
                    jQuery(".qty, .single_add_to_cart_button").show();
                }
            }
            const locationCookie = alies_getcookies.getCookie("wcmlim_selected_location");
            if (locationCookie === "-1" || locationCookie == null || locationCookie === "undefined") {
                jQuery(".qty, .single_add_to_cart_button").hide();
            }
        // wcmlim_selected_location_termid cookie
            const locationCookieTermId = alies_getcookies.getCookie("wcmlim_selected_location_termid");
            
            jQuery(".select_location").prepend(`<option data-lc-qty="" data-lc-sale-price="" data-lc-regular-price='${pop}' value="-1"> - Select Location - </option>`);
        
            jQuery.each(select, (key, value) => {
                if (key !== "backorder") {
                    const defl = value.default_location;
                    const allow_specific_location = value.allow_specific_location;
                    const allow = (allow_specific_location === "No") ? "none" : "";
        
                    const option = jQuery("<option></option>")
                        .attr({
                            "value": key,
                            "class": value.location_class,
                            "data-lc-qty": value.location_qty,
                            "data-lc-address": value.location_address,
                            "data-lc-regular-price": value.regular_price,
                            "data-lc-sale-price": value.sale_price,
                            "data-lc-backorder": value.variation_backorder,
                            "data-lc-stockstatus": value.location_stock_status,
                            "location_start_time": value.start_time,
                            "location_end_time": value.end_time
                        })
                        .text(value.text)
                        .css("display", allow);
        
                    jQuery(".select_location").append(option);
        
                    if (localization.isdefault === "on" && defl && defl !== "" && defl !== null && defl !== undefined) {
                        jQuery(`.select_location option[class="${value.location_class}"]`).prop("selected", true);
                        jQuery(".quantity, .single_add_to_cart_button").show();
                    }
                  //  get only selected location from location cookietermid
                    if(locationCookieTermId == value.term_id)
                    
                      if(value.location_qty <= 0 && value.variation_backorder == "no"){
                       
                        jQuery(".quantity, .single_add_to_cart_button").hide();
                      }
                  
                      else
                      {
                        // set out of stock status to red colour
                       
                        jQuery(".quantity, .single_add_to_cart_button").show();
                      }
                    
                      

              }
            });
        
            // Handle selected location
            jQuery(".select_location").find("option").each(function () {
                const $this = jQuery(this);
                const loc = $this.val();
        
                const cookieArr = document.cookie.split(";");
                for (let i = 0; i < cookieArr.length; i++) {
                    const cookiePair = cookieArr[i].split("=");
                    if (cookiePair[0].trim() === "wcmlim_selected_location" && decodeURIComponent(cookiePair[1].trim()) === loc) {
                        if (localization.isdefault !== "on" && decodeURIComponent(cookiePair[1].trim()) > -1) {
                            $this.prop("selected", true);
                        }
        
                        // Handle display based on stock status
                        // Handle price display
                        // Handle other elements based on stock status
                    }
                }
            });
        
            // Perform additional actions
            alies_listOrdering.listOrdering();
        
            // Handle radio button selection
            jQuery.each(select, function (key, value) {
                const defl = value.default_location;
                const location_class = value.location_class;
                const checked = (defl && defl !== '' && defl !== null && defl !== undefined && defl !== 0);
                jQuery(`.${location_class}`).prop('checked', checked);
            });
        
          }
        });
      }
    });
  }
}
