jQuery(document).ready(function ($) {
    'use strict';

    /* GENERAL */

    $('.eib2bpro_radio_selector').on('click', function () {
        var conditions;
        if ($(this).data('show')) {
            $($(this).data('show')).slideDown();
        }
        if ($(this).data('hide')) {
            $($(this).data('hide')).slideUp();
        }
    });

    /* OFFERS */

    if ($('.eib2bpro-b2b-offer-product_hidden').length > 0) {
        function eiB2BProOfferInit() {
            var selectize_options = [];
            $('select.eib2bpro-b2b-offer-product:not(.selectized)').removeClass('hidden').selectize({
                plugins: ["remove_button"],
                valueField: "id",
                maxItems: 1,
                labelField: "name",
                searchField: "name",
                create: false,
                render: {
                    option: function (item, escape) {
                        return (
                            "<div class='eib2bpro-app-user-select-div'>" +
                            '<span class="eib2bpro-app-user-select-name">' +
                            escape(item.name) +
                            "</span>" +
                            '<span class="eib2bpro-app-user-select-group">' +
                            escape(item.price_currency) +
                            "</span>" +
                            "</div>"
                        );
                    },
                },
                load: function (query, callback) {
                    if (!query.length) return callback();
                    $.post(eiB2BProGlobal.ajax_url, {
                        app: 'b2b',
                        action: 'eib2bpro',
                        asnonce: eiB2BProGlobal.asnonce,
                        do: 'search-product',
                        query: query
                    }, function (res) {
                        callback(res);
                        selectize_options = res;
                    }, 'json');
                },
                onChange: function (value) {
                    var prnt = $($(this)[0].$input[0]).parent().parent();
                    var decimal = $('.eib2bpro-b2b-offer-table').data('decimal');
                    $.each(selectize_options, function (i, k) {
                        if (parseInt(k.id) === parseInt(value)) {
                            prnt.find('.eib2bpro-input-offer-price').val(k.price.replace(/\./, decimal));
                            if (prnt.find('.eib2bpro-input-offer-unit').val() === "") {
                                prnt.find('.eib2bpro-input-offer-unit').val("1");
                            }
                            prnt.find('.eib2bpro-input-offer-unit').focus();
                            eiB2BProCalculateOffer();
                        }
                    })
                }
            });

            $('.eib2bpro-b2b-offer-table').sortable({
                handle: '.eib2bpro-os-move',
                axis: 'y'
            });
        }

        $('body').on('click', '.eib2bpro_offer_type_selector', function () {
            eiB2BProOfferType();
        });

        function eiB2BProOfferType() {
            var vl = $('.eib2bpro_offer_type_selector:checked').val();
            if ('suggestion' === vl) {
                $('.eib2bpro-b2b-offer-table-th-all').show();
                $('.eib2bpro-b2b-offer-table-th-qty').hide();
                $('.eib2bpro-b2b-offer-table-th-price').hide();
                $('.eib2bpro-b2b-offer-table-th-subtotal').hide();
                $('.eib2bpro-b2b-offer-table-th-total').hide();
            }

            if ('bundle' === vl) {
                $('.eib2bpro-b2b-offer-table-th-all').show();
                $('.eib2bpro-b2b-offer-table-th-qty').show();
                $('.eib2bpro-b2b-offer-table-th-price').show();
                $('.eib2bpro-b2b-offer-table-th-subtotal').show();
                $('.eib2bpro-b2b-offer-table-th-total').show();
            }

            if ('announcement' === vl) {
                $('.eib2bpro-b2b-offer-table-th-all').hide();
            }
        }


        $(document).on('change, input', '.eib2bpro-input-offer-unit, .eib2bpro-input-offer-price', function () {
            eiB2BProCalculateOffer();
        })

        function eiB2BProCalculateOffer() {
            var total = 0;
            var rows = $('.eib2bpro-b2b-offer-table').find('tr');
            var decimal = $('.eib2bpro-b2b-offer-table').data('decimal');
            $.each(rows, function (i, k) {

                var unit = $(k).find('.eib2bpro-input-offer-unit').val();
                var price = $(k).find('.eib2bpro-input-offer-price').val();

                if (unit !== undefined && price !== undefined) {
                    price = price.replace(/\,/, '.');
                    var subtotal = unit * price;
                    total += subtotal;
                    $(k).find('.eib2bpro-offer-subtotal').text(Number(subtotal).toFixed(2).replace(/\./, decimal));
                }
            });

            $('.eib2bpro-offer-total').text(Number(total).toFixed(2).replace(/\./, decimal));

        }

        $('.eib2bpro-b2b-offer-new-row').on('click', function (e) {
            e.preventDefault();
            var tr = $('.eib2bpro-b2b-offer-table .eib2bpro-hidden-row').clone();
            tr.removeClass('eib2bpro-hidden-row');
            tr.find('.eib2bpro-b2b-offer-product_hidden').addClass('eib2bpro-b2b-offer-product')
            $('.eib2bpro-b2b-offer-table').append(tr);
            eiB2BProOfferInit();
        });

        $(document).on('click', '.eib2bpro-b2b-offer-table-delete a', function () {
            $(this).parent().parent().remove();
        });

        eiB2BProOfferInit();
        eiB2BProCalculateOffer();
        eiB2BProOfferType();

    }

    $('body').on('click', '.eib2bpro-app-b2b-settings-registration .carousel-indicators > li', function () {
        var $index = $(this).data('slide-to');
        if (0 === $index) {
            $('.eib2bpro-regtype-button').addClass('d-none')
            $('.eib2bpro-field-button').removeClass('d-none');
        } else {
            $('.eib2bpro-field-button').addClass('d-none')
            $('.eib2bpro-regtype-button').removeClass('d-none');
        }
    })

    /* SORTING */
    if ($('.eib2bpro-sortable-registration-regtypes').length > 0 || $('.eib2bpro-sortable').length > 0 || $('.eib2bpro-sortable-x').length > 0) {
        $('.eib2bpro-sortable-registration-regtypes, .eib2bpro-sortable').sortable({
            containment: "parent",
            axis: "y",
            handle: '.eib2bpro-os-move',
            tolerance: 'pointer',
            sortAnimateDuration: 200,
            sortAnimate: true,
            stop: function (event, ui) {
                $('.eib2bpro-app-save-button-hidden').trigger('click');
            }
        });

        $('.eib2bpro-sortable-x').sortable({
            containment: "parent",
            axis: "x",
            tolerance: 'pointer',
            sortAnimateDuration: 200,
            sortAnimate: true,
            stop: function (event, ui) {

            }
        });
    }

    /* Registration Fields edit form */

    $('body').on('keyup', '.eib2bpro-app-b2b-registration-fields-edit-form .eib2bpro-input-title', function () {
        if ($('.eib2bpro-input-eib2bpro_field_label').val() !== $(this).val()) {
            $('.eib2bpro-input-eib2bpro_field_label').val($(this).val());
        }
    });

    $('body').on('change', 'input[name="eib2bpro_field_billing_show"]', function () {
        var vl = $(this).is(':checked');
        var vl_billing = $('.eib2bpro-input-eib2bpro_field_billing_type').val();

        if ('new' === vl_billing) {
            if (true === vl) {
                $('.eib2bpro-billing-form').slideDown();
                $('.eib2bpro-billing-groups').slideDown();
            } else {
                $('.eib2bpro-billing-all').slideUp();
            }
        } else if ('billing_vat' === vl_billing) {
            if (true === vl) {
                $('.eib2bpro-billing-all').slideDown();
                $('.eib2bpro-billing-vat').slideDown();
            } else {
                $('.eib2bpro-billing-all').slideUp();
            }
        } else if ('custom' === vl_billing) {
            if (true === vl) {
                $('.eib2bpro-billing-all').slideDown();
            } else {
                $('.eib2bpro-billing-all').slideUp();
            }
            $('.eib2bpro-billing-custom').slideDown();
        } else {
            if (true === vl) {
                $('.eib2bpro-billing-all').slideDown();
            } else {
                $('.eib2bpro-billing-all').slideUp();
            }
        }

    });

    $('body').on('change', '.eib2bpro-input-eib2bpro_field_billing_type', function () {
        var vl = $(this).val();

        if ('new' === vl) {
            $('.eib2bpro-billing-all').hide();
            $('.eib2bpro-billing-custom').slideUp();
            $('.eib2bpro-billing-vat').slideUp();
            $('.eib2bpro-billing-show').slideDown();
            $('.eib2bpro-billing-groups').slideDown();
        } else if ('billing_vat' === vl) {
            $('.eib2bpro-billing-all').slideUp();
            $('.eib2bpro-billing-vat').slideDown();
            $('.eib2bpro-billing-show').slideDown();
        } else if ('custom' === vl) {
            $('.eib2bpro-billing-all').slideUp();
            $('.eib2bpro-billing-custom').slideDown();
            $('.eib2bpro-billing-show').slideUp();
        } else {
            $('.eib2bpro-billing-all').slideUp();
            $('.eib2bpro-billing-show').slideUp();
        }
    });

    $('body').on('change', 'input[name="eib2bpro_field_registration_show"]', function () {
        var vl = $(this).is(':checked');
        if (true === vl) {
            $('.eib2bpro-b2b-fields-registration-wrapper').slideDown();
        } else {
            $('.eib2bpro-b2b-fields-registration-wrapper').slideUp();
        }
    });

    $('body').on('change', 'input[name="eib2bpro_field_enable_billing"]', function () {
        var vl = $(this).is(':checked');
        if (true === vl) {
            $('.eib2bpro-input-eib2bpro_field_billing_type').val('new');
            $('.eib2bpro-b2b-fields-billing-wrapper').slideDown();
            $('.eib2bpro-billing-all').slideDown();
            $('.eib2bpro-billing-custom').slideUp();
            $('.eib2bpro-billing-vat').slideUp();
            $('.eib2bpro-billing-show').slideDown();
            $('.eib2bpro-billing-groups').slideDown();
            $('.eib2bpro-billing-editable').slideDown();
        } else {
            $('.eib2bpro-input-eib2bpro_field_billing_type').val('none');
            $('.eib2bpro-b2b-fields-billing-wrapper').slideUp();
        }
    });

    $('body').on('change', '.eib2bpro-app-b2b-registration-fields-edit-form .eib2bpro-input-eib2bpro_field_type', function () {
        var vl = $(this).val();

        if ('checkbox' === vl || 'select' === vl) {
            $('.eib2bpro-app-b2b-registration-fields-edit-form .eib2bpro-b2b-field-options').slideDown();
        } else {
            $('.eib2bpro-app-b2b-registration-fields-edit-form .eib2bpro-b2b-field-options').slideUp();
        }

        if ('text_vat' === vl) {
            $('.eib2bpro-enable-billing-container').hide();
            $('.eib2bpro-input-eib2bpro_field_billing_type').val('billing_vat');
            $('input[name="eib2bpro_field_enable_billing"]').prop('checked', true);
            $('.eib2bpro-b2b-fields-billing-wrapper').slideDown();
            $('.eib2bpro-billing-all').hide();
            $('.eib2bpro-billing-vat').show();
            $('.eib2bpro-billing-show').show();
        } else if ('text_country' === vl) {
            $('.eib2bpro-input-eib2bpro_field_billing_type').val('billing_country');
            $('.eib2bpro-enable-billing-container').hide();
            $('.eib2bpro-b2b-fields-billing-wrapper').slideUp();
        } else if ('text_country_state' === vl) {
            $('.eib2bpro-input-eib2bpro_field_billing_type').val('billing_country_state');
            $('.eib2bpro-enable-billing-container').hide();
            $('.eib2bpro-b2b-fields-billing-wrapper').slideUp();
        } else {
            $('.eib2bpro-enable-billing-container').show();
            $('.eib2bpro-billing-all').hide();
            $('.eib2bpro-billing-custom').hide();
            $('.eib2bpro-billing-vat').hide();
            $('.eib2bpro-billing-show').show();
            $('.eib2bpro-billing-groups').show();
            var eb = $('input[name="eib2bpro_field_enable_billing"]').is(':checked');
            if (eb === true) {
                $('.eib2bpro-input-eib2bpro_field_billing_type').val('new');
            } else {
                $('.eib2bpro-input-eib2bpro_field_billing_type').val('none');
            }
        }

    });

    $('body').on('change', '.eib2bpro-app-b2b-registration-fields-edit-form input[data-name=eib2bpro_field_billing_enabled]', function () {
        var vl = $(this).is(':checked');

        if (true === vl) {
            $('.eib2bpro-app-b2b-registration-fields-edit-form .eib2bpro-b2b-edit-billing-fields').slideDown();
        } else {
            $('.eib2bpro-app-b2b-registration-fields-edit-form .eib2bpro-b2b-edit-billing-fields').slideUp();
        }
    });

    if ($('.eib2bpro-country-list').length > 0) {
        $(".eib2bpro-country-list").selectize({
            plugins: ["remove_button"],
            valueField: "id",
            delimeter: ',',
            maxItems: 999,
            labelField: "name",
            searchField: "name",
            create: false,
            render: {
                option: function (item, escape) {
                    return (
                        "<div class='eib2bpro-app-user-select-div'>" +
                        '<span class="eib2bpro-app-user-select-name">' +
                        escape(item.name) +
                        "</span>" +
                        "</div>"
                    );
                },
            },
            load: function (query, callback) {
                if (!query.length) return callback();
                $.post(eiB2BProGlobal.ajax_url, {
                    app: 'rules',
                    action: 'eib2bpro',
                    asnonce: eiB2BProGlobal.asnonce,
                    do: 'search',
                    for: $($(this)[0].$input[0]).attr('data-for'),
                    query: query
                }, function (res) {
                    callback(res);
                }, 'json');
            }
        });
    }

    /* BULK EDITOR */

    $('body').on('click', '.eib2bpro-b2b-bulk-category-selectall', function () {
        $('.eib2bpro-group-' + $(this).data('group') + '> input[type=checkbox]').prop('checked', this.checked);
    })

    $('body').on('click', '.eib2bpro-b2b-bulk-category-sub', function () {
        $('.eib2bpro-parent-' + $(this).data('sub')).toggle();
        if ($(this).data('toggle')) {
            if ($(this).text() === '-') {
                $(this).text('+');
            } else {
                $(this).text('-');
            }
        }
    });

});