!function (t) {
    // FunciÃ³n para actualizar el carrito lateral con los fragments
    function actualizarCarritoLateral(fragments) {
        for (const selector in fragments) {
            const element = document.querySelector(selector);
            if (element) {
                element.outerHTML = fragments[selector];
            }
        }
    }
    t(document).on("click", ".wcmlim_ajax_add_to_cart", function (a) {
        // obtenemos la clase padre 'quantity-wrapper'
        const div = this.closest('.quantity-wrapper');
        if (!div) return;

        const quantityInput = div.querySelector('.quantity-input');
        if (quantityInput) {
            e = parseFloat(quantityInput.value) || 1;
        }

        // obtenemos la cantidad actual del carrito desde el atributo data-cantidad-carrito
        const cantidadCarrito = parseFloat(quantityInput.getAttribute('data-cantidad-carrito')) || 0;

        const quantityToAdd = quantityInput ? parseFloat(quantityInput.value) - cantidadCarrito : o.data("quantity") || 1;
        a.preventDefault();
        var o = t(this),
            e = quantityToAdd,
            i = o.data("product_id"),
            r = o.data("product_sku"),
            c = o.data("product_price"),
            n = o.data("selected_location"),
            d = o.data("location_key"),
            l = o.data("location_qty"),
            u = o.data("location_termid"),
            s = o.data("location_sale_price"),
            p = o.data("location_regular_price");

        product_backorder = o.data("product_backorder"),
            is_redirect = o.data("isredirect"),
            redirect_url = o.data("cart-url"),

            l <= 0 && 1 !== product_backorder && t.ajax({
                type: "post",
                url: wc_add_to_cart_params.ajax_url,
                data: { action: "wcmlim_ajax_validation_manage_stock", product_id: i },
                beforeSend: function (t) {
                    o.removeClass("added").addClass("loading")
                },
                complete: function (t) {
                    o.addClass("added").removeClass("loading")
                },
                success: function (t) {
                    if ("0" == t)
                        return Swal.fire({
                            icon: "error",
                            text: "Â¡El producto no tiene stock!"
                        }), !0
                }
            });

        var f = {
            action: "wcmlim_ajax_add_to_cart",
            product_id: i,
            product_sku: r,
            quantity: e,
            product_price: c,
            product_location: n,
            product_location_key: d,
            product_location_qty: l,
            product_location_termid: u,
            product_location_sale_price: s,
            product_location_regular_price: p
        };

        return t(document.body).trigger("adding_to_cart", [o, f]),
            t.ajax({
                type: "post",
                url: wc_add_to_cart_params.ajax_url,
                data: f,
                beforeSend: function (t) {
                    o.removeClass("added").addClass("loading")
                },
                complete: function (t) {
                    o.addClass("added").removeClass("loading")
                },
                success: function (b) {
                    if (b.fragments) {
                        actualizarCarritoLateral(b.fragments);
                    } else {
                        let a = parseInt(b, 10); // Convierte a nÃºmero ignorando espacios y saltos de lÃ­nea

                        if ("4" == a)
                            return Swal.fire({
                                title: "No disponible",
                                text: `La cantidad disponible es ${l} en la ubicaciÃ³n ${n}`,
                                icon: "warning"
                            }).then(() => {
                                // window.location.href = window.location.href
                            }), !1;

                        if ("1" == a)
                            Swal.fire({
                                title: "¡Producto sin stock!",
                                text: "Este producto no está disponible por el momento. Te invitamos a seguir explorando nuestro catálogo; seguramente encontrarás una alternativa que se ajuste a tus necesidades.",
                                icon: "warning",
                                showCancelButton: !0,
                                confirmButtonColor: "#3085d6",
                                cancelButtonColor: "#d33",
                                confirmButtonText: "¡Sí, actualizar carrito!"
                            }).then(t => {
                                if (t.isConfirmed) {
                                    let { ajaxurl: a } = multi_inventory;
                                    jQuery.ajax({
                                        url: a,
                                        type: "post",
                                        data: { action: "wcmlim_empty_cart_content" },
                                        success(t) {
                                            Swal.fire({
                                                title: "¡Carrito actualizado!",
                                                text: "Los artículos de tu carrito han sido actualizados, por favor añade el artículo de nuevo.",
                                                icon: "success"
                                            }).then(() => {
                                                window.location.href = window.location.href
                                            })
                                        }
                                    })
                                }
                            });
                        else if ("2" == a)
                            Swal.fire({
                                title: "Producto no disponible",
                                text: "El artículo no está disponible en esta ubicación, asegúrese de seleccionar una tienda.",
                                icon: "warning"
                            }).then(() => {
                                window.location.href = window.location.href
                            });
                        else if ("3" == a)
                            Swal.fire({
                                title: "Selecciona una tienda",
                                text: "Estimado cliente, por favor primero selecciona una tienda",
                                icon: "warning"
                            }).then(() => {
                                //window.location.href = window.location.href
                            });
                        else if ('9' == a)
                            Swal.fire({
                                title: "Cantidad no válida",
                                text: "La cantidad que intentas agregar no coincide con los incrementos permitidos para este producto. Por favor verifica y ajusta la cantidad correcta.",
                                icon: "warning",
                                confirmButtonText: "Entendido",
                                confirmButtonColor: "#3085d6"
                            }).then(() => {
                                //window.location.href = window.location.href
                            });
                        else if ('11' == a) 
                            // Mensaje relacionado a que el producto no esta disponible para la compra
                            Swal.fire({
                                title: "Producto no disponible para la compra",
                                text: "El artículo no está disponible para la compra en este momento. Por favor, revisa los detalles del producto o contacta al soporte para más información.",
                            }).then(() => {
                                window.location.href = window.location.href
                            });
                        if (a.error && a.product_url) {
                            window.location = a.product_url;
                            return
                        }
                    }

                    t(document.body).trigger("added_to_cart", [a.fragments, a.cart_hash, o]),
                        "yes" == is_redirect && (window.location = redirect_url)
                }
            }), !1
    })
}(jQuery);