(function ($) {
    "use strict";
    $(document).ready(function () {

        // Qty @ Products

        eiB2BProShowActivePrice(true);

        $('.variations_form.cart table.variations select').on('change', function () {
            setTimeout(function () {
                eiB2BProShowActivePrice(false);
            }, 300);
        });


        function eiB2BProShowActivePrice(exclude_current_input = false) {

            if ($('.eib2bpro_price_tiers_table').length === 0) {
                return;
            }

            var qty;
            var product_id = 0;
            var new_price = '';
            var new_discount = '';
            var active_qty = 0;

            if ($('input[name=variation_id]').length > 0) {
                product_id = $('input[name=variation_id]').val();
            } else {
                product_id = $('.eib2bpro_price_tiers_table').data('product_id');
            }

            qty = parseInt($('.eib2bpro_price_tiers_table[data-product_id=' + product_id + ']').data('current_qty'));

            if (!exclude_current_input) {
                qty += parseInt($('input[name=quantity]').val());
            }

            var tiers = $('.eib2bpro_price_tiers_table[data-product_id=' + product_id + '] tbody tr');

            $.each(tiers, function (i, item) {
                if (qty >= parseInt($(item).data('qty'))) {
                    new_price = $(item).find('.eib2bpro_tier_price').html();
                    active_qty = parseInt($(item).data('qty'));
                    if ($(item).find('.eib2bpro_tier_discount').length > 0) {
                        new_discount = $(item).find('.eib2bpro_tier_discount').html();
                    }
                }
            });
            if ('' !== new_price) {
                $('.eib2bpro_active_price').html(new_price).removeClass('eib2bpro-hidden-row');
            } else {
                $('.eib2bpro_active_price').html('').addClass('eib2bpro-hidden-row');
            }

        }

        // Request a Quotee @ Cart

        $('body').on('click', '#eib2bpro-b2b-cart-request-a-quote-button', function (e) {
            e.preventDefault();
            $('#eib2bpro-b2b-cart-request-a-quote-container').toggle();
        });

        // Registration

        eiB2BProRegistration();
        $('select#billing_country').on('change', eiB2BProRegistration);
        $('#eib2bpro_registration_regtype_selector').on('change', eiB2BProRegistration);
        $('.eib2bpro_customfield_countries_select select').on('change', eiB2BProRegistration);

        /* Select2 fix for Flatsome theme */
        $('body').on('select2:open', '.eib2bpro_customfield_countries_select select', function (e) {
            $('.theme-flatsome .off-canvas').removeAttr('tabindex');
        });


        function eiB2BProField(field, att) {
            field.css('display', att);
        }

        function eiB2BProGetCountries() {
            var eiCountries = eiB2BPublic.vat_countries;
            if (eiCountries === undefined || eiCountries === '') {
                eiCountries = $('.eib2bpro_customfield_countries').val();
            }
            return eiCountries;
        }

        function eiB2BProGetCountry() {
            var eiCountry = '';
            if ($('.eib2bpro_customfield_countries_select select').length > 0) {
                eiCountry = $('.eib2bpro_customfield_countries_select select').val();
            }
            if (eiCountry === undefined || eiCountry === '') {
                eiCountry = $('select#billing_country').val();
            }
            return eiCountry;
        }

        function eiB2BProRegistration() {
            $('.eib2bpro_customfield_required_1').removeAttr('required');
            eiB2BProField($('.eib2bpro_registration_container'), 'none');
            eiB2BProField($('.eib2bpro_registration_regtype_0'), 'block');
            $('.eib2bpro_registration_regtype_0 .eib2bpro_customfield_required_1').prop('required', 'true');

            // registration type
            var regtype = $('#eib2bpro_registration_regtype_selector').val();
            eiB2BProField($('.eib2bpro_registration_regtype_' + regtype), 'block');
            $('.eib2bpro_registration_regtype_' + regtype + ' .eib2bpro_customfield_required_1').prop('required', 'true');

            var eiCountries = eiB2BProGetCountries();
            var eiCountry = eiB2BProGetCountry();
            var any_country = ($('.eib2bpro_customfield_countries_select select').length !== 0 || $('select#billing_country').length !== 0) ? true : false;
            if (eiCountries !== undefined && any_country) {
                if (eiCountries.indexOf(eiCountry) === -1) {
                    eiB2BProField($('.eib2bpro_customfield_vat').parent(), 'none');
                    eiB2BProField($('.eib2bpro_customfield_vat_account'), 'none');
                    $('.eib2bpro_customfield_vat').removeAttr('required');
                    $('.eib2bpro_customfield_vat_account').removeAttr('required');
                } else {
                    eiB2BProField($('.eib2bpro_registration_regtype_' + regtype + ' .eib2bpro_customfield_vat').parent(), 'block');
                    $('.eib2bpro_customfield_vat_account.eib2bpro_customfield_required_1 .optional').after('<abbr class="required" title="required">*</abbr>');
                    $('.eib2bpro_customfield_vat_account.eib2bpro_customfield_required_1 .optional').remove();
                    $('.eib2bpro_customfield_vat_account.eib2bpro_customfield_required_1 input').attr('required', 'required');
                    eiB2BProField($('.eib2bpro_customfield_vat_account'), 'block');
                }
            }
        }

        // Offers

        $(document).on('click', '.eib2bpro_offer_add_to_cart', function () {

            var th = $(this);
            $(this).text(eiB2BPublic.i18n.wait);

            var product_or_offer = 'offer';
            var id = 0;
            if ($(this).data('offer')) {
                product_or_offer = 'offer';
                id = $(this).data('offer');
            }
            if ($(this).data('product')) {
                product_or_offer = 'product';
                id = $(this).data('product');
            }
            $.post(eiB2BPublic.ajax_url, {
                nonce: eiB2BPublic.nonce,
                action: 'eib2bpro_public',
                app: 'b2b',
                do: 'offer-add-to-cart',
                id: id,
                po: product_or_offer
            }, function (data) {
                if (1 === parseInt(data.status)) {
                    window.location = eiB2BPublic.cart_url;
                } else if (2 === parseInt(data.status)) {
                    th.text(data.message);
                } else {
                    alert(data.message);
                }
            }, 'json');
        });

        // Quote

        if (1 === parseInt(eiB2BPublic.replace_add_to_cart_with_quote) || $('.eib2bpro_enable_quote_popup').length > 0) {
            $('.ajax_add_to_cart').removeClass('ajax_add_to_cart');

            $('body').on('click', '.single_add_to_cart_button, .button.product_type_simple.add_to_cart_button, .ajax_add_to_cart', function (e) {
                e.preventDefault();
                e.stopPropagation();

                $('body').append('<div class="eib2bpro-modal"><div class="eib2bpro-modal-overlay modal-toggle"></div><div class="eib2bpro-modal-wrapper eib2bpro-modal-transition"><div class="eib2bpro-modal-close">x</div><div class="eib2bpro-modal-body"><div class="eib2bpro-modal-content">' + eiB2BPublic.i18n.wait + '</div></div></div></div>');
                $('.eib2bpro-modal').toggleClass('is-visible');

                var product_id = 0;

                if ($(this).data('product_id')) {
                    product_id = parseInt($(this).data('product_id'));
                }

                if (parseInt($(this).val()) > 0) {
                    product_id = parseInt($(this).val());
                }

                if ($('input[name="variation_id"]').length > 0) {
                    if (0 < parseInt($('input[name="variation_id"]').val())) {
                        product_id = parseInt($('input[name="variation_id"]').val());
                    }
                }

                $.post(eiB2BPublic.ajax_url, {
                    nonce: eiB2BPublic.nonce,
                    action: 'eib2bpro_public',
                    app: 'b2b',
                    do: 'quote-form',
                    product_id: product_id
                }, function (data) {
                    $('.eib2bpro-modal-body').html(data);
                    $('#eib2bpro-b2b-cart-request-a-quote-container').show();
                }, 'html');
            });
        }

        $('body').on('click', '#eib2bpro-b2b-cart-request-a-quote-button, .eib2bpro-b2b-cart-request-a-quote-button', function (e) {
            e.preventDefault();
            e.stopPropagation();

            $('body').append('<div class="eib2bpro-modal"><div class="eib2bpro-modal-overlay modal-toggle"></div><div class="eib2bpro-modal-wrapper eib2bpro-modal-transition"><div class="eib2bpro-modal-close">x</div><div class="eib2bpro-modal-body"><div class="eib2bpro-modal-content">' + eiB2BPublic.i18n.wait + '</div></div></div></div>');
            $('.eib2bpro-modal').toggleClass('is-visible');

            var product_id = 0;

            if ($(this).data('product_id')) {
                product_id = parseInt($(this).data('product_id'));
            }

            if (parseInt($(this).val()) > 0) {
                product_id = parseInt($(this).val());
            }

            if ($('input[name="variation_id"]').length > 0) {
                if (0 < parseInt($('input[name="variation_id"]').val())) {
                    product_id = parseInt($('input[name="variation_id"]').val());
                }
            }


            $.post(eiB2BPublic.ajax_url, {
                nonce: eiB2BPublic.nonce,
                action: 'eib2bpro_public',
                app: 'b2b',
                do: 'quote-form',
                product_id: product_id
            }, function (data) {
                $('.eib2bpro-modal-body').html(data);
                $('#eib2bpro-b2b-cart-request-a-quote-container').show();
            }, 'html');
        });

        $('body').on('click', '.eib2bpro-modal-close', function () {
            $('.eib2bpro-modal').toggleClass('is-visible');
            setTimeout(function () {
                $('.eib2bpro-modal').remove();
            }, 300);
        });


        $('body').on('click', '#eib2bpro-b2b-cart-request-a-quote-send-button', function (e) {
            e.preventDefault();

            var serialized = [];
            var error = false;

            $.each($('.eib2bpro_quote_form').serializeArray(), function (key, item) {
                serialized[item.name] = item.value;
            });

            $.each($('.eib2bpro_quote_field_required_1'), function (k, i) {
                if (typeof serialized[$(i).attr('name')] === 'undefined' || serialized[$(i).attr('name')] === '') {
                    error = true;
                }

            });

            if (error) {
                alert(eiB2BPublic.i18n.fill);
                return;
            }

            var button_text = $(this).text();

            $(this).text(eiB2BPublic.i18n.wait);

            var data = new FormData($('.eib2bpro_quote_form')[0]);
            data.append('nonce', eiB2BPublic.nonce);
            data.append('action', 'eib2bpro_public');
            data.append('app', 'b2b');
            data.append('do', 'quote-send');

            jQuery('body').find(".variations select").each(function (e) {
                var t = jQuery(this).data("attribute_name") || jQuery(this).attr("name"),
                    a = jQuery(this).val() || "";
                if ("" !== t) {
                    data.append('attributes_' + t, a);
                }
            });

            $.ajax({
                type: 'POST',
                url: eiB2BPublic.ajax_url,
                nonce: eiB2BPublic.nonce,
                action: 'eib2bpro_public',
                app: 'b2b',
                do: 'quote-send',
                data: data,
                processData: false,
                contentType: false,
                success: function (response) {
                    response = JSON.parse(response);
                    if (1 === response.status) {
                        $('#eib2bpro-b2b-cart-request-a-quote-container').html(response.message);
                    } else {
                        $('#eib2bpro-b2b-cart-request-a-quote-send-button').text(button_text);
                        alert(response.message);
                    }
                }
            });

        });

        /* Bulk order - Layout 1 */

        if ($('.eib2bpro-bulkorder-l1-table').length > 0) {

            eiB2BBulkOrder_L1_Get();
            eiB2BBulkOrder_L1_Calculate();

            function eiB2BBulkOrder_L1_Calculate(save = true, add_to_cart = false) {
                var arr = [];
                var decimal = $('.eib2bpro-bulkorder-l1-table').data('decimal');
                $.each($('.eib2bpro-bulkorder-l1-product'), function (index, item) {
                    if ('undefined' !== typeof $(item).attr('data-details')) {
                        var details = JSON.parse($(item).attr('data-details'));
                        var qty = parseInt($(item).find('.eib2bpro-bulkorder-l1-product-qty').val());
                        var price = details.price;

                        if (typeof qty === undefined || isNaN(qty)) {
                            qty = 0;
                        }

                        if (typeof price === undefined || isNaN(price)) {
                            price = 0;
                        }

                        if (qty > parseInt(details.max_qty)) {
                            $(item).find('.eib2bpro-bulkorder-l1-qty-max-alert').show();
                            setTimeout(function () {
                                $(item).find('.eib2bpro-bulkorder-l1-qty-max-alert').hide();
                            }, 5000);
                            $(item).find('.eib2bpro-bulkorder-l1-product-qty').val(parseInt(details.max_qty))
                        } else {
                            $(item).find('.eib2bpro-bulkorder-l1-qty-max-alert').hide();
                        }

                        if (qty < parseInt(details.step)) {
                            $(item).find('.eib2bpro-bulkorder-l1-product-qty').val(parseInt(details.step));
                            qty = details.step
                        }

                        $(item).find('.eib2bpro-bulkorder-l1-product-qty').attr('step', parseInt(details.step));
                        $(item).find('.eib2bpro-bulkorder-l1-product-qty').attr('min', parseInt(details.step));

                        $.each(details.price_tiers, function (tier_qty, tier_price) {
                            if (qty >= parseInt(tier_qty)) {
                                price = tier_price;
                            }
                        });

                        var subtotal = qty * price;

                        if ('NaN' === subtotal) {
                            subtotal = 0;
                        }

                        arr.push({
                            id: details.id,
                            qty: qty
                        });

                        $(item).find('.eib2bpro-bulkorder-l1-product-subtotal').text(Number(subtotal).toFixed(2).replace(/\./, decimal));
                    }
                });
            }

            $(document).on('change, input', '.eib2bpro-bulkorder-l1-product-qty', function (e) {
                if ($(this).val() !== '') {
                    eiB2BBulkOrder_L1_Calculate();
                }
            });

            $(document).on('click', '.eib2bpro-bulkorder-l1-page', function () {
                var current_page = parseInt($('.eib2bpro-bulkorder-l1-table').data('page'));
                if ($(this).hasClass('woocommerce-button--previous')) {
                    $('.eib2bpro-bulkorder-l1-table').data('page', current_page - 1);
                } else {
                    $('.eib2bpro-bulkorder-l1-table').data('page', current_page + 1);

                }
                eiB2BBulkOrder_L1_Get();
            });

            $(document).on('change', '.eib2bpro-bulkorder-l1-variations', function () {
                $(this).parent().parent().attr('data-details', $(this).children("option:selected").attr('data-details'));

                var details = JSON.parse($(this).children("option:selected").attr('data-details'));

                $(this).parent().parent().find('.eib2bpro-bulkorder-l1-product-add').attr('data-status', 'add');
                $(this).parent().parent().find('.eib2bpro-bulkorder-l1-product-add').attr('data-id', details.id);
                $(this).parent().parent().find('.eib2bpro-bulkorder-l1-product-add').removeAttr('disabled');
                eiB2BBulkOrder_L1_Calculate();
            });

            $('.eib2bpro-bulkorder-l1-categories').on('change', function () {
                $('.eib2bpro-bulkorder-l1-table').data('page', 1);
                eiB2BBulkOrder_L1_Get();
            });

            $(".eib2bpro-bulkorder-l1-search").doWithDelay("keyup", function (e) {
                if ($(this).val().length > 2 || $(this).val().length === 0) {
                    $('.eib2bpro-bulkorder-l1-table').data('page', 1);
                    eiB2BBulkOrder_L1_Get();
                }
            }, 200);

            $(document).on('click', '.eib2bpro-bulkorder-l1-product-add', function () {
                var id = $(this).attr('data-id');
                var status = $(this).attr('data-status');
                var qty = $(this).parent().parent().find('.eib2bpro-bulkorder-l1-product-qty').val();
                var th = $(this);
                var prev_html = th.html();

                $(this).text('••');

                $.post(eiB2BPublic.ajax_url, {
                    nonce: eiB2BPublic.nonce,
                    action: 'eib2bpro_public',
                    app: 'b2b',
                    do: 'bulkorder-add-to-cart',
                    id: id,
                    po: 'product',
                    qty: qty,
                    status: status
                }, function (data) {
                    if (1 === parseInt(data.status)) {
                        window.location = eiB2BPublic.cart_url;
                    } else if (2 === parseInt(data.status)) {
                        if (th.parent().parent().find('.eib2bpro-bulkorder-l1-variations').length > 0) {
                            // additional controls
                            th.html(prev_html);
                        } else {
                            th.html(data.message);
                            if ('add' === status) {
                                th.attr('data-status', 'remove');
                            } else {
                                th.attr('data-status', 'add');
                            }
                        }
                        jQuery(document.body).trigger('wc_fragment_refresh');
                    } else {
                        alert(data.message);
                    }
                }, 'json');
            })

            function eiB2BBulkOrder_L1_Get() {
                $('.eib2bpro-bulkorder-l1-body').css('opacity', 0.6);
                $.post(eiB2BPublic.ajax_url, {
                    nonce: eiB2BPublic.nonce,
                    action: 'eib2bpro_public',
                    app: 'b2b',
                    do: 'bulkorder-category',
                    category: $('.eib2bpro-bulkorder-l1-categories').val(),
                    page: parseInt($('.eib2bpro-bulkorder-l1-table').data('page')),
                    limit: parseInt($('.eib2bpro-bulkorder-l1-table').data('limit')),
                    prices: $('.eib2bpro-bulkorder-l1-table').data('prices'),
                    categories: $('.eib2bpro-bulkorder-l1-table').data('categories'),
                    q: $('.eib2bpro-bulkorder-l1-search').val()
                }, function (data) {
                    $('.eib2bpro-bulkorder-l1-body').html(data);
                    $('.eib2bpro-bulkorder-l1-body').css('opacity', 1);
                    eiB2BBulkOrder_L1_Calculate();
                }, 'html');
            }

            $(document).on('click', '.num-in span', function () {
                var $input = $(this).parents('.num-block').find('input');
                var cnt = $input.val();
                if (cnt === '') {
                    cnt = 1;
                }
                cnt = parseInt(cnt);
                if ($(this).hasClass('minus')) {
                    var count = cnt - 1;
                    count = count < 1 ? 1 : count;
                    if (count < 2) {
                        $(this).addClass('dis');
                    } else {
                        $(this).removeClass('dis');
                    }
                    $input.val(count);
                } else {
                    var count = cnt + 1
                    $input.val(count);
                    if (count > 1) {
                        $(this).parents('.num-block').find(('.minus')).removeClass('dis');
                    }
                }

                $input.change();
                eiB2BBulkOrder_L1_Calculate();
                return false;
            });


        }

        /* Bulk Order - Layout 2 */

        if ($('.eib2bpro-bulkorder-l2-table').length > 0) {

            var eiCurrentRequest = null;
            var eiProductList;

            function eiB2B_BulkOrder_L2_ShowProductList(t, products) {
                var product_list = t.next();

                product_list.html('');

                $.each(products, function (index, arr) {
                    product_list.append(arr.html);
                });
            }

            function eiB2B_BulkOrder_L2_Calculate(save = true, add_to_cart = false, table = false, quickorder = false) {
                var total = 0;
                var arr = [];
                var decimal = $('.eib2bpro-bulkorder-l2-table').data('decimal');

                $.each($('.eib2bpro-bulkorder-l2-product', table), function (index, item) {
                    if ('undefined' !== typeof $(item).attr('data-selected')) {
                        var details = JSON.parse($(item).attr('data-selected'));
                        var qty = parseInt($(item).parent().parent().parent().find('.eib2bpro-input-ei-bulkorder-qty').val());
                        var price = details.price;

                        if (typeof qty === undefined || isNaN(qty)) {
                            qty = 0;
                        }

                        if (typeof price === undefined || isNaN(price)) {
                            price = 0;
                        }

                        if (qty > parseInt(details.max_qty)) {
                            $(item).parent().parent().parent().find('.eib2bpro-bulkorder-l2-qty-max-alert').show();
                            setTimeout(function () {
                                $(item).parent().parent().parent().find('.eib2bpro-bulkorder-l2-qty-max-alert').hide();
                            }, 5000);
                            $(item).parent().parent().parent().find('.eib2bpro-input-ei-bulkorder-qty').val(parseInt(details.max_qty))
                        } else {
                            $(item).parent().parent().parent().find('.eib2bpro-bulkorder-l2-qty-max-alert').hide();
                        }

                        $.each(details.price_tiers, function (tier_qty, tier_price) {
                            if (qty >= parseInt(tier_qty)) {
                                price = tier_price;
                            }
                        });

                        var subtotal = qty * price;

                        if ('NaN' === subtotal) {
                            subtotal = 0;
                        }

                        total += subtotal;
                        arr.push({
                            id: details.id,
                            qty: qty
                        });
                        $(item).parent().parent().parent().find('.eib2bpro-bulkorder-subtotal').text(Number(subtotal).toFixed(2).replace(/\./, decimal));
                    }
                    $(item).parent().parent().parent().parent().parent().find('.eib2bpro-bulkorder-total').text(Number(total).toFixed(2).replace(/\./, decimal));
                });

                if (!$(table).hasClass('eib2bpro-quickorders-table')) {
                    eiCurrentRequest = jQuery.ajax({
                        type: 'POST',
                        data: {
                            nonce: eiB2BPublic.nonce,
                            action: 'eib2bpro_public',
                            app: 'b2b',
                            do: 'bulkorder-auto-save',
                            add_to_cart: add_to_cart,
                            save: save,
                            form: arr
                        },
                        dataType: 'json',
                        url: eiB2BPublic.ajax_url,
                        beforeSend: function () {
                            if (eiCurrentRequest !== null) {
                                eiCurrentRequest.abort();
                            }
                        },
                        success: function (data) {
                            if (1 === parseInt(data.status)) {
                                if (true === add_to_cart) {
                                    window.location = eiB2BPublic.cart_url;
                                }
                            } else {
                                alert(data.message);
                            }
                        },
                        error: function (e) {
                            // Error
                        }
                    });
                } else {
                    if (add_to_cart || quickorder) {
                        eiCurrentRequest = jQuery.ajax({
                            type: 'POST',
                            data: {
                                nonce: eiB2BPublic.nonce,
                                action: 'eib2bpro_public',
                                app: 'b2b',
                                do: 'quickorders-save',
                                id: parseInt($(table).attr('data-qoid')),
                                add_to_cart: add_to_cart,
                                save: quickorder,
                                form: arr,
                                switch: $(table).parent().parent().find('.eib2bpro-quickorders-reminder-switch').is(':checked'),
                                every: $(table).parent().parent().find('.eib2bpro-quickorders-reminder-every').val(),
                                start: $(table).parent().parent().find('.eib2bpro-quickorders-reminder-start').val(),
                                title: $(table).parent().parent().find('.eib2bpro-quickorders-title').val()
                            },
                            dataType: 'json',
                            url: eiB2BPublic.ajax_url,
                            beforeSend: function () {
                                if (eiCurrentRequest !== null) {
                                    eiCurrentRequest.abort();
                                }
                            },
                            success: function (data) {
                                if (1 === parseInt(data.status)) {
                                    if (true === add_to_cart) {
                                        window.location = eiB2BPublic.cart_url;
                                    }

                                    if (true === quickorder) {
                                        window.location = window.location;
                                    }
                                } else {
                                    alert(data.message);
                                }
                            },
                            error: function (e) {
                                // Error
                            }
                        });
                    }
                }

            }

            function eiB2B_BulkOrder_L2_NewLine(table) {
                if ($('.eib2bpro-bulkorder-l2-product:last', table).val() === '') {
                    return;
                }
                var tr = $('.eib2bpro-bulkorder-l2-table-tbody .eib2bpro-hidden-row', table).clone();
                tr.removeClass('eib2bpro-hidden-row');
                $('.eib2bpro-bulkorder-l2-table-tbody', table).append(tr);
                $('.eib2bpro-bulkorder-l2-product:last', table).focus();
            }

            $(document).on('click', '.eib2bpro-bulkorder-l2-item', function (e) {
                var details = JSON.parse($(this).attr('data-details'));
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product').val(details.name).attr('disabled', 'disabled');
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product-id').val(details.id);
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product').attr('data-selected', $(this).attr('data-details'))
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product-x').show();
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product-list').slideUp('fast');
                $(this).parent().parent().parent().parent().find('.eib2bpro-input-ei-bulkorder-qty').val(details.min_qty).attr('min', details.min_qty).focus();
                eiB2B_BulkOrder_L2_Calculate(true, false, $(this).parent().parent().parent().parent().parent().parent());
                eiB2B_BulkOrder_L2_NewLine($(this).parent().parent().parent().parent().parent().parent());
            });

            $(document).on('change, input', '.eib2bpro-input-ei-bulkorder-qty', function (e) {
                eiB2B_BulkOrder_L2_Calculate(true, false, $(this).parent().parent().parent().parent());
            });

            $(document).on('click', '.eib2bpro-bulkorder-l2-product-x', function (e) {
                e.preventDefault();
                $(this).hide();
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product').val('').removeAttr('disabled').focus();
                $(this).parent().parent().find('.eib2bpro-bulkorder-l2-product-id').val('');
                $(this).parent().parent().parent().find('.eib2bpro-input-ei-bulkorder-qty').val('0');
                eiB2B_BulkOrder_L2_Calculate(true, false, $(this).parent().parent().parent().parent().parent());
            });

            $(document).on('click', '.eib2bpro-bulkorder-l2-add-to-cart', function (e) {
                e.preventDefault();
                eiB2B_BulkOrder_L2_Calculate(false, true, $(this).parent().parent().parent().parent());
                $(this).text(eiB2BPublic.i18n.wait);
            });

            $(document).on('input', '.eib2bpro-bulkorder-l2-product', function (e) {
                var t = $(this);

                if (t.val().length < 2) {
                    return;
                }

                t.next().html(eiB2BPublic.i18n.wait);
                t.next().slideDown('fast');

                eiCurrentRequest = jQuery.ajax({
                    type: 'POST',
                    data: {
                        nonce: eiB2BPublic.nonce,
                        action: 'eib2bpro_public',
                        app: 'b2b',
                        do: 'search-product',
                        query: $(this).val()
                    },
                    dataType: 'json',
                    url: eiB2BPublic.ajax_url,
                    beforeSend: function () {
                        if (eiCurrentRequest !== null) {
                            eiCurrentRequest.abort();
                        }
                    },
                    success: function (data) {
                        if (1 === parseInt(data.status)) {
                            eiB2B_BulkOrder_L2_ShowProductList(t, data.result);
                        } else {
                            alert(data.message);
                        }
                    },
                    error: function (e) {
                        // Error
                    }
                });
            });
            $.each($('.eib2bpro-bulkorder-l2-table'), function (index, item) {
                eiB2B_BulkOrder_L2_Calculate(false, false, item);
            });


            /* QUICK ORDERS */

            $(document).on('click', '.eib2bpro-quickorders-summary', function () {
                $(this).parent().find('.eib2bpro-quickorders-details').slideToggle();
                $(this).parent().find('.eib2bpro-quickorders-details').parent().toggleClass("eib2bpro-opened");
            });

            $(document).on('click', '.eib2bpro-quickorders-save', function (e) {
                e.preventDefault();
                if ('0' === $(this).parent().parent().find('.eib2bpro-bulkorder-l2-table').attr("data-qoid")) {
                    var title = prompt(eiB2BPublic.i18n.quickorder_title, '');
                    if (title !== "") {
                        $(this).parent().parent().find('.eib2bpro-quickorders-title').val(title);
                    }
                }
                eiB2B_BulkOrder_L2_Calculate(false, false, $(this).parent().parent().find('.eib2bpro-bulkorder-l2-table'), true);
                $(this).text(eiB2BPublic.i18n.wait);
            });

            $(document).on('click', '.eib2bpro-quickorders-add-to-cart', function (e) {
                e.preventDefault();
                eiB2B_BulkOrder_L2_Calculate(false, true, $(this).parent().parent().find('.eib2bpro-bulkorder-l2-table'), false);
                $(this).text(eiB2BPublic.i18n.wait);
            });

            $(document).on('click', '.eib2bpro-quickorders-delete', function (e) {
                e.preventDefault();
                if (window.confirm(eiB2BPublic.i18n.are_you_sure)) {
                    $(this).text(eiB2BPublic.i18n.wait);

                    jQuery.ajax({
                        type: 'POST',
                        data: {
                            nonce: eiB2BPublic.nonce,
                            action: 'eib2bpro_public',
                            app: 'b2b',
                            do: 'quickorders-delete',
                            id: $(this).attr('data-id')
                        },
                        dataType: 'json',
                        url: eiB2BPublic.ajax_url,
                        success: function (data) {
                            if (1 === parseInt(data.status)) {
                                window.location.reload();
                            } else {
                                alert(data.message);
                            }
                        },
                        error: function (e) {
                            // Error
                        }
                    });

                }
            });

            $(document).on('change', '.eib2bpro-quickorders-reminder-switch', function () {
                if ($(this).is(':checked')) {
                    $('.eib2bpro-quickorders-reminder-container').slideDown();
                } else {
                    $('.eib2bpro-quickorders-reminder-container').slideUp();
                }
            });
        }
    });
})(jQuery);