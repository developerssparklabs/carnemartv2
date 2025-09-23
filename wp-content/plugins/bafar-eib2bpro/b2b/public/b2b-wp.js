(function ($) {
    "use strict";
    $(document).ready(function () {

        /* PRODUCTS */

        if ($('.eib2bpro-app-user-select').length > 0) {
            if ($('.eib2bpro-app-user-select').hasClass('eib2bpro-app-user-select-addnew')) {
                var createfunc = function (input) {
                    return {
                        value: input,
                        text: input,
                        name: input,
                        id: input,
                    };
                };
            } else {
                var createfunc = false;
            }
            $('.eib2bpro-app-user-select').removeClass('hidden').selectize({
                plugins: ["remove_button"],
                delimiter: ',',
                maxItems: 999,
                valueField: "id",
                labelField: "name",
                searchField: "name",
                create: createfunc,
                render: {
                    option: function (item, escape) {
                        return (
                            "<div class='eib2bpro-app-user-select-div'>" +
                            '<span class="eib2bpro-app-user-select-name">' +
                            escape(item.name) +
                            "</span>" +
                            "</span>" +
                            '<span class="eib2bpro-app-user-select-group">' +
                            escape(item.group) +
                            "</span>" +
                            "</div>"
                        );
                    },
                },
                load: function (query, callback) {
                    if (!query.length) return callback();
                    $.post(eiB2BProWPGlobal.ajax_url, {
                        app: 'b2b',
                        action: 'eib2bpro',
                        asnonce: eiB2BProWPGlobal.asnonce,
                        do: 'search-user',
                        query: query
                    }, function (res) {
                        callback(res);
                    }, 'json');
                }
            });
        }


        $('body').on('change', '.eib2bpro_product_visibility_manual', function (e) {
            if ("1" === $(this).val()) {
                $('.eib2bpro_product_visibility_manual_settings').slideDown();
            } else {
                $('.eib2bpro_product_visibility_manual_settings').slideUp();
            }
        });

        $('body').on('click', '.eib2bpro_price_tiers_add', function (e) {
            e.preventDefault();
            let temp = $('.eib2bpro_price_tiers_group_' + $(this).data('group') + '_' + $(this).data('id')).find('.eib2bpro_price_tiers_blank').html();
            let classes = 'form-row';
            if (0 === $(this).data('id')) {
                classes = '';
            }
            $('.eib2bpro_price_tiers_group_' + $(this).data('group') + '_' + $(this).data('id') + ' .eib2bpro_price_tiers_container').append('<span class="eib2bpro_price_tiers ' + classes + '">' + temp + '</span>');
        });

        $('body').on('click', '.eib2bpro_price_tiers_groups .eib2bpro_toggle', function (e) {
            e.preventDefault();

            let $this = $(this);

            if ($this.next().hasClass('show')) {
                $this.next().removeClass('show');
                $this.next().slideUp();
            } else {
                $this.parent().parent().find('li .inner').removeClass('show');
                $this.parent().parent().find('li .inner').slideUp();
                $this.next().toggleClass('show');
                $this.next().slideToggle();
            }
        });


        /* USERS */

        $('.eib2bpro_user_approve_button').on('click', function (e) {
            e.preventDefault();

            if (!confirm(eiB2BProWPGlobal.i18n.are_you_sure)) {
                return false;
            }

            $.post(eiB2BProWPGlobal.ajax_url, {
                action: 'eib2bpro',
                app: 'b2b',
                asnonce: $('#eib2bpro_user_profile_nonce').val(),
                do: 'approve-user',
                status: $(this).data('status'),
                id: $(this).data('user'),
                move: $('select[name=eib2bpro_group]').val(),
            }, function (d) {

                if ('approve' === $(this).data('status')) {
                    window.location.reload();
                } else {
                    if (self !== top && window.parent.EIB2BPRO_Window !== null && window.parent.EIB2BPRO_Window !== undefined) {
                        window.parent.location.reload();
                    } else {
                        window.location = eiB2BProWPGlobal.admin_url + '/users.php';
                    }
                }
            }, 'json');
        });

        $('.eib2bpro_payment_shipping_selector').on('click', function () {
            if ('custom' === $(this).val()) {
                $('#eib2bpro_payment_shipping_container').slideDown();
            } else {
                $('#eib2bpro_payment_shipping_container').slideUp();
            }
        });

    });
})(jQuery);