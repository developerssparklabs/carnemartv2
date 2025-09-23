jQuery(document).ready(function ($) {
    "use strict";

    detectHash(eiB2BProGlobal.admin_url + "post.php?post=HASH&action=edit");

    $('body').on('click', '.eib2bpro-todo-carousel-indicators > li', function (e) {
        e.stopPropagation();
        $(this).parent().parent().parent().carousel($(this).data('slide-to'));
        $(this).parent().parent().parent().carousel('pause');

        var slideto = $(this).data('slide-to');

        if (0 < slideto) {

            jQuery.post(eiB2BProGlobal.ajax_url, {
                _wpnonce: jQuery('input[name=_wpnonce]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                action: "eib2bpro",
                app: 'orders',
                do: 'order-tabs',
                id: jQuery(this).data('id'),
                tab: slideto
            }, function (r) {
                if (1 === r.status) {
                    $('.carousel-item[data-tab=' + slideto + ']').html(r.html);


                } else {
                    eiB2BProAjax();
                    eiB2BProAjax('error', r.error);
                }
            }, 'json');
        }

    });

    jQuery('.eib2bpro-Ajax_Btn_SP').on("click", function (e) {
        e.stopPropagation();
        e.preventDefault();

        if (window.isMobile) {
            if (jQuery('body').hasClass('eib2bpro-half')) {
                window.location = eiB2BProGlobal.admin_url + 'admin.php?page=eib2bpro&app=go&in=' + encodeURIComponent(jQuery(this).attr('href')) + "&asnonce=" + eiB2BProGlobal.asnonce;
                return false;
            } else {
                window.location = jQuery(this).attr('href');
                return false;
            }
        }

        if (jQuery(this).attr('data-hash') && jQuery(this).attr('data-hash').length > 0) {
            window.location.hash = jQuery(this).attr('data-hash');
        }

        window.panel.slideReveal("show");
        jQuery("#inbrowser").attr("src", jQuery(this).attr('href'));
        jQuery('#inbrowser').on("load", function () {
            jQuery("#inbrowser--loading").removeClass('d-flex').addClass('d-none');
            jQuery(".eib2bpro-Trig_Close").removeClass('d-none');
            jQuery("#inbrowser").show();
        });
    });


    jQuery("body").on("click", ".eib2bpro-Ajax_Button", function (e) {
        e.preventDefault();

        eiB2BProAjax();

        var t = jQuery('#item_' + jQuery(this).attr('data-id'));
        var status = jQuery(this).data('status').replace(/wc-/, '');
        var text = jQuery(this).data('text');

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            action: "eib2bpro",
            app: 'orders',
            do: jQuery(this).data('do'),
            id: jQuery(this).data('id'),
            status: status
        }, function (r) {
            if (1 === r.status) {
                jQuery.each(r.success, function (i, item) {
                    jQuery('.eib2bpro-orders--item-badge > span', '#item_' + item).removeClass().addClass('siparisdurumu text-' + status);
                    jQuery('.eib2bpro-orders--item-badge > span > ', '#item_' + item).removeClass().addClass('bg-custom bg-' + status);
                    jQuery('.eib2bpro-orders--item-badge', '#item_' + item).html('<span class="siparisdurumu text-' + status + '"><span class="bg-custom bg-' + status + '" aria-hidden="true"></span><br>' + text + '</span>');
                    if ('trash' === status || 'restore' === status || 'deleteforever' === status) {
                        jQuery('#item_' + item).slideUp('fast');
                    } else {
                        jQuery('#item_' + item).removeClass('eib2bpro-ItemChecked');
                    }
                });
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);

            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');

    });

    jQuery(".eib2bpro-Bulk_Do").on("click", function () {

        eiB2BProAjax();

        var sThisVal = '',
            sList = "",
            status = jQuery(this).data('status');

        jQuery('.eib2bpro-Checkbox').each(function () {
            sThisVal = jQuery(this).attr('data-id');
            if (this.checked) {
                sList += (sList === "" ? sThisVal : "," + sThisVal);
            }
        });

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            action: "eib2bpro",
            app: 'orders',
            do: jQuery(this).data('do'),
            id: sList,
            status: status
        }, function (r) {
            if (1 === r.status) {
                jQuery.each(r.success, function (i, item) {
                    jQuery('.eib2bpro-orders--item-badge > span', '#item_' + item).html(status).removeClass().addClass('badge badge-pill badge-' + status);
                    if ('trash' === status || 'restore' === status || 'deleteforever' === status) {
                        jQuery('#item_' + item).hide('slow').remove();
                    } else {
                        jQuery('#item_' + item).removeClass('eib2bpro-ItemChecked');
                    }
                });

                sList = '';

                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');

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

        jQuery(".eib2bpro-Checkbox").addClass('eib2bpro-NoHide').prop('checked', this.checked);
        jQuery(".eib2bpro-CheckAll").prop('checked', this.checked);
    });

});