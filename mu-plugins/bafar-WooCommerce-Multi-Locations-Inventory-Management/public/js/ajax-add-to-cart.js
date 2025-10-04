(function ($) {
    $(document).on("click", ".wcmlim_ajax_add_to_cart", function (e) {
        debugger;
        e.preventDefault();
        var $thisbutton = $(this),
            product_qty = $thisbutton.data("quantity") || 1,
            product_id = $thisbutton.data("product_id"),
            product_sku = $thisbutton.data("product_sku"),
            product_price = $thisbutton.data("product_price"),
            product_location = $thisbutton.data("selected_location"),
            product_location_key = $thisbutton.data("location_key"),
            product_location_qty = $thisbutton.data("location_qty"),
            product_location_termid = $thisbutton.data("location_termid"),
            product_location_sale_price = $thisbutton.data("location_sale_price"),
            product_location_regular_price = $thisbutton.data("location_regular_price");
        product_backorder = $thisbutton.data("product_backorder");
        is_redirect = $thisbutton.data("isredirect");
        redirect_url = $thisbutton.data("cart-url");
        if (product_location_qty <= 0 && product_backorder !== 1) {
            //check if product is having manage stock enabled
            
            var manage_stock_validation_data = {
                action: "wcmlim_ajax_validation_manage_stock",
                product_id: product_id
            };


            $.ajax({
                type: "post",
                url: wc_add_to_cart_params.ajax_url,
                data: manage_stock_validation_data,
                beforeSend: function (response) {
                    $thisbutton.removeClass("added").addClass("loading");
                },
                complete: function (response) {
                    $thisbutton.addClass("added").removeClass("loading");
                },
                success: function (response) {
                    if (response == "0") {
                        Swal.fire({ icon: "error", text: "Product doesn't have a stock!" });
                        return !0;
                    }
                },
            });
        }
        var data = {
            action: "wcmlim_ajax_add_to_cart",
            product_id: product_id,
            product_sku: product_sku,
            quantity: product_qty,
            product_price: product_price,
            product_location: product_location,
            product_location_key: product_location_key,
            product_location_qty: product_location_qty,
            product_location_termid: product_location_termid,
            product_location_sale_price: product_location_sale_price,
            product_location_regular_price: product_location_regular_price,
        };
        $(document.body).trigger("adding_to_cart", [$thisbutton, data]);
        $.ajax({
            type: "post",
            url: wc_add_to_cart_params.ajax_url,
            data: data,
            beforeSend: function (response) {
                $thisbutton.removeClass("added").addClass("loading");
            },
            complete: function (response) {
                $thisbutton.addClass("added").removeClass("loading");
            },
            success: function (response) { 
                 if(response == "4"){
                    Swal.fire({
                        title: "Estimado Cliente",
                        text: `Available quantity is ${product_location_qty} at location ${product_location}`,
                        icon: "warning"
                    }).then(() => {
                        window.location.href = window.location.href;
                    });
                    return !1;
                }

                
                if (response == "1") {
                    Swal.fire({
                        title: "Cart Validation",
                        text: "Your cart contains items from another location, do you want to update the cart",
                        icon: "warning",
                        showCancelButton: !0,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, Update Cart!",
                    }).then((result) => {
          
                        if (result.isConfirmed) {
                            const { ajaxurl } = multi_inventory;
                            jQuery.ajax({
                                url: ajaxurl,
                                type: "post",
                                data: { action: "wcmlim_empty_cart_content" },
                                success(output) {
                                  
                                        Swal.fire({ title: "Updated Cart!", text: "Your cart items has been updated, Please add the item again!", icon: "success" }).then(() => {
                                            window.location.href = window.location.href;
                                        });
                                    
                                },
                            });
                        }
                    });
                }else if(response == "2"){
                    Swal.fire({ title: "Estimado Cliente", text: "Item is not available at this location!", icon: "warning" }).then(() => {
                        window.location.href = window.location.href;
                    });
                }else if(response == "3"){
                    Swal.fire({ title: "Estimado Cliente", text: "Por favor selecciona una tienda", icon: "warning" }).then(() => {
                        window.location.href = window.location.href;
                    });
                }

                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                } else {
                    $(document.body).trigger("added_to_cart", [response.fragments, response.cart_hash, $thisbutton]);
                    if (is_redirect == "yes") {
                        window.location = redirect_url;
                    }
                }
            },
        });
        return !1;
    });
})(jQuery);
