jQuery(document).ready(function () {
    "use strict";

    detectHash(eiB2BProGlobal.admin_url + "post.php?post=HASH&action=edit");

    jQuery('body').on('click', ".eib2bpro-OnOff", function () {
        var prnt = jQuery(this);

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            dataType: 'json',
            action: "eib2bpro",
            app: 'products',
            do: 'visible',
            id: jQuery(this).attr('data-id'),
            state: jQuery(this).prop('checked')
        }, function (r) {
            if (1 === r.status) {
                jQuery(".eib2bpro-OnOff[data-parent='" + prnt.attr('data-id') + "']").prop('checked', prnt.prop('checked'));
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            }
        }, 'json');
    });

    jQuery(".eib2bpro-StockAjax").on('change', function () {
        var prnt = jQuery(this);

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            dataType: 'json',
            action: "eib2bpro",
            app: 'products',
            do: 'quantity',
            id: jQuery(this).attr('data-id'),
            state: jQuery(this).prop('checked'),
            name: jQuery(this).attr('name'),
            val: jQuery(this).val()
        }, function (r) {
            if (1 === r.status) {
                jQuery("#eib2bpro-Stock_" + prnt.attr('data-id')).html(r.message);
                if ('outofstock' === prnt.attr('name') || 'unlimited' === prnt.attr('name')) {
                    jQuery('.eib2bpro-Item[data-id=' + prnt.attr('data-id') + '] .eib2bpro-StockAjax2').val('');
                }
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');
    });


    jQuery('body').on('click', ".eib2bpro-StockAjax1", function () {
        var prnt = jQuery(this);
        var data_id = prnt.attr('data-id');
        var obj = jQuery('.eib2bpro-StockAjax2[data-id=' + data_id + ']');

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            dataType: 'json',
            action: "eib2bpro",
            app: 'products',
            do: 'quantity',
            id: obj.attr('data-id'),
            state: obj.prop('checked'),
            name: obj.attr('name'),
            val: obj.val()
        }, function (r) {
            if (1 === r.status) {
                jQuery("#eib2bpro-Stock_" + prnt.attr('data-id')).html(r.message);
                jQuery('.eib2bpro-Item[data-id=' + prnt.attr('data-id') + '] .eib2bpro-Item_Details input[type=checkbox]').prop('checked', false);
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');
    });

    jQuery("#eib2bpro-products-2 .eib2bpro-PriceAjax").on('change', function () {
        var prnt = jQuery(this);

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            dataType: 'json',
            action: "eib2bpro",
            app: 'products',
            do: 'quantity',
            id: jQuery(this).attr('data-id'),
            state: false,
            name: jQuery(this).attr('name'),
            val: jQuery(this).val()
        }, function (r) {
            if (1 === r.status) {

                jQuery("#eib2bpro-Price_" + prnt.attr('data-id')).html(r.message);
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');
    });

    jQuery("body").on('click', "#eib2bpro-products-1 .eib2bpro-PriceAjax1", function () {

        var prnt = jQuery(this);
        var data_id = prnt.attr('data-id');

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            dataType: 'json',
            action: "eib2bpro",
            app: 'products',
            do: 'quantity',
            id: data_id,
            state: false,
            name: 'set_price',
            val: jQuery('.eib2bpro-PriceAjax_Regular[data-id=' + data_id + ']').val(),
            val1: jQuery('.eib2bpro-PriceAjax_Sale[data-id=' + data_id + ']').val(),
        }, function (r) {
            if (1 === r.status) {
                jQuery("#eib2bpro-Price_" + prnt.attr('data-id')).html(r.message);
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');
    });

    jQuery(".eib2bpro-AjaxButton").on('click', function (e) {
        e.preventDefault();

        if (jQuery(this).data('confirm')) {
            if (!confirm(jQuery(this).data('confirm'))) {
                return false;
            }
        }

        eiB2BProAjax();

        var id = jQuery(this).data('id');
        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery(this).data('nonce'),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            dataType: 'json',
            action: "eib2bpro",
            app: 'products',
            do: jQuery(this).data('do'),
            id: id,
        }, function (r) {
            if (1 === r.status) {
                jQuery("#item_" + id).slideUp();
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');
    });


    jQuery(".eib2bpro-ShowVariantProducts").on("click", function () {
        jQuery("tr[data-parent='" + jQuery(this).attr('data-id') + "']").toggle();
    });

    jQuery('.eib2bpro-Search_Button').on("click", function () {
        jQuery('.eib2bpro-Searching').toggleClass('eib2bpro-Overflow_Inherit');
        jQuery('.eib2bpro-Channels').toggle();
        jQuery('.eib2bpro-Cat_Title').toggle();
        jQuery('.eib2bpro-Right').toggleClass('col-lg-12');

        if (jQuery('.eib2bpro-Searching').hasClass('closed') === true) {

            jQuery('.eib2bpro-Right').addClass('col-lg-9');

        }
    });

    jQuery('.eib2bpro-Products_Cat_Dropdown').on("click", function () {
        jQuery('.eib2bpro-Searching button').text(jQuery(this).text());
        jQuery('.eib2bpro-Input_Status').val(jQuery(this).attr('data-slug'));
        window.searchMe();
    });


    jQuery(".eib2bpro-Bulk_Do").on("click", function () {

        eiB2BProAjax();
        var sList = "";
        var do_state = jQuery(this).attr('data-do');

        jQuery('.eib2bpro-Checkbox').each(function () {
            var sThisVal = jQuery(this).attr('data-id');
            if (this.checked) {
                sList += (sList === "" ? sThisVal : "," + sThisVal);
            }
        });

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            action: "eib2bpro",
            app: 'products',
            do: 'bulk',
            id: sList,
            state: jQuery(this).attr('data-do')
        }, function (r) {
            if (1 === r.status) {
                jQuery.each(r.id, function (i, item) {
                    jQuery('#eib2bpro-Stock_' + item.id).html(item.status);
                    jQuery('#item_' + item.id).removeClass('eib2bpro-ItemChecked');

                    if ('trash' === do_state || 'deleteforever' === do_state) {
                        jQuery('#item_' + item.id).hide('slow').remove();
                    }
                });
                sList = '';
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');
    });


    jQuery(".eib2bpro-Bulk_Change_Price").on("click", function () {

        var sList = "";

        jQuery('.eib2bpro-Checkbox').each(function () {
            var sThisVal = jQuery(this).attr('data-id');
            if (this.checked) {
                sList += (sList === "" ? sThisVal : "-" + sThisVal);
            }
        });

        jQuery(this).attr('href', jQuery('.eib2bpro-Bulk_Change_Price').attr('href') + '&ids=' + sList);
    });


    jQuery(".eib2bpro-Checkbox").on("click", function () {
        if (0 === jQuery(".eib2bpro-Checkbox:checked").length) {
            jQuery(".eib2bpro-Bulk").hide();

        } else {
            jQuery(".eib2bpro-Bulk").show();
            jQuery(".eib2bpro-Item.btnA").addClass('collapsed').attr('aria-expanded', false);
            jQuery(".eib2bpro-Item.btnA .collapse").removeClass('show');
            jQuery('.eib2bpro-Checkbox_Hidden').show();
        }

    });

    jQuery(".eib2bpro-CheckAll").on("click", function () {
        if (this.checked) {
            jQuery(".eib2bpro-Bulk").show();
        } else {
            jQuery(".eib2bpro-Bulk").hide();
        }

        jQuery(".eib2bpro-Checkbox").not("[disabled]").addClass('eib2bpro-NoHide').prop('checked', this.checked);
        jQuery(".eib2bpro-CheckAll").prop('checked', this.checked);
    });

    /* Bulk Prices */

    jQuery('.change_type').on("click", function () {
        if (jQuery(this).val() === '1') {
            jQuery('#collapseOne').addClass('showing show');
            jQuery('#collapseTwo').removeClass('show');
            jQuery('.percent_1').focus();
        } else {
            jQuery('#collapseTwo').addClass('showing show');
            jQuery('#collapseOne').removeClass('show');
            jQuery('.percent_2').focus();

        }
    });

    var number_format = function (number, decimals, dec_point, thousands_sep) {
        number = number.toFixed(decimals);

        var nstr = number.toString();
        nstr += '';
        x = nstr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? dec_point + x[1] : '';
        var rgx = /(\d+)(\d{3})/;

        while (rgx.test(x1))
            x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');

        return x1 + x2;
    };

    function addCommas(nStr) {
        nStr += '';
        var x = nStr.split('.');
        var x1 = x[0];
        var x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + '.' + '$2');
        }
        x2 = x2.replace('.', ',');
        return x1 + x2;
    }

    function calculate(type, percent, fixed) {
        var new_price;

        if (percent === '') {
            percent = 0;
        }
        if (fixed === '') {
            fixed = 0;
        }

        percent = parseFloat(percent);
        fixed = parseFloat(fixed);
        jQuery('.change_price').each(function () {
            var item = jQuery(this);
            var price = parseFloat(item.data('old'));
            if (0 < price) {
                if ('2' === type) {
                    new_price = (price * (1 + (percent * -1) / 100) + (fixed * -1)).toFixed(2);
                } else {
                    new_price = (price * (1 + percent / 100) + fixed).toFixed(2);
                }

                item.html(addCommas(new_price));
            }
        });
    }

    jQuery('.fixed_1, .percent_1, .fixed_2, .percent_2').on('keyup', function () {
        var increase_or_decrease = jQuery(".change_type:checked").val();
        calculate(increase_or_decrease, jQuery('.percent_' + increase_or_decrease).val(), jQuery('.fixed_' + increase_or_decrease).val());
    });

    jQuery('.change_type').on("click", function () {
        var increase_or_decrease = jQuery(".change_type:checked").val();
        calculate(increase_or_decrease, jQuery('.percent_' + increase_or_decrease).val(), jQuery('.fixed_' + increase_or_decrease).val());
    });

    /* Reorder */

    var handle = '.eib2bpro-Products_Hand';

    if (jQuery('.eib2bpro-Products_Sortable').hasClass('eib2bpro-Products_Sortable')) {} else {
        handle = false;
    }

    jQuery(".eib2bpro-Product_Sortable tbody, .eib2bpro-Products_Sortable").sortable({
        axis: "y",
        revert: true,
        scroll: false,
        placeholder: "sortable-placeholder",
        cursor: "move",
        opacity: 1,
        handle: handle,
        start: function (event, ui) {
            jQuery('.eib2bpro-Products_Sortable').addClass('eib2bpro-Sorting');
        },
        stop: function (event, ui) {
            jQuery('.eib2bpro-Products_Sortable').removeClass('eib2bpro-Sorting');
        },
        update: function (event, ui) {

            eiB2BProAjax();

            var current_id = ui.item.data('id');
            var next_id = ui.item.next().data('id');
            var prev_id = ui.item.prev().data('id');
            var arr = jQuery(this).sortable('toArray');

            jQuery.post(eiB2BProGlobal.ajax_url, {
                _wpnonce: jQuery('input[name=_wpnonce]').val(),
                _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: "woocommerce_product_ordering",
                id: current_id,
                previd: prev_id,
                nextid: next_id
            }, function (r) {
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            }, 'json');
        }
    });

    var ns_p = jQuery('.eib2bpro-Product_Sortablex').nestedSortable({
        forcePlaceholderSize: true,
        handle: 'tr',
        helper: 'clone',
        items: 'tr',
        listType: "table",
        opacity: 0.6,
        placeholder: 'placeholder',
        revert: 250,
        tabSize: 25,
        tolerance: 'pointer',
        xtoleranceElement: '> div',
        maxLevels: 1,
        isTree: false,
        expandOnHover: 700,
        startCollapsed: false,
        relocate: function () {
            arr = $('.eib2bpro-Product_Sortable').nestedSortable('toArray', {
                startDepthCount: 0
            });

            jQuery.post(eiB2BProGlobal.ajax_url, {
                _wpnonce: jQuery('input[name=_wpnonce]').val(),
                _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: "eib2bpro",
                app: 'products',
                do: 'products_reorder',
                ids: arr
            }, function (r) {
                if (1 === r.status) {}
            }, 'json');
        }
    });

    // Reorder categories

    if (jQuery('.eib2bpro-Depth_0eib2bpro-Sortable').length > 0) {
        var ns = jQuery('.eib2bpro-Depth_0eib2bpro-Sortable').nestedSortable({
            forcePlaceholderSize: true,
            handle: 'div',
            helper: 'clone',
            items: 'li',
            opacity: 0.6,
            placeholder: 'placeholder',
            revert: 250,
            tabSize: 25,
            tolerance: 'pointer',
            toleranceElement: '> div',
            maxLevels: 4,
            isTree: true,
            expandOnHover: 700,
            startCollapsed: false,
            relocate: function () {
                var arr = jQuery('.eib2bpro-Depth_0eib2bpro-Sortable').nestedSortable('toArray', {
                    startDepthCount: 0
                });

                eiB2BProAjax();

                jQuery.post(eiB2BProGlobal.ajax_url, {
                    _wpnonce: jQuery('input[name=_wpnonce]').val(),
                    _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                    asnonce: eiB2BProGlobal.asnonce,
                    action: "eib2bpro",
                    app: 'products',
                    do: 'categories_reorder',
                    ids: arr
                }, function (r) {
                    if (1 === r.status) {
                        eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
                    } else {
                        eiB2BProAjax('error', r.error);
                    }
                }, 'json');
            }
        });
    }


});