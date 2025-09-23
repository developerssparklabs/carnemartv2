jQuery(document).ready(function () {
    "use strict";

    detectHash(eiB2BProGlobal.admin_url + "post.php?post=HASH&action=edit");

    jQuery("body").on("click", ".eib2bpro-ActivePassive", function () {

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery(this).attr('data-nonce') || jQuery('input[name=_wpnonce]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            action: "eib2bpro",
            app: 'coupons',
            do: 'active',
            id: jQuery(this).attr('data-id'),
            state: jQuery(this).prop('checked')
        }, function (r) {
            if (1 === r.status) {
                jQuery('#item_' + r.id).removeClass('eib2bpro-Statusprivate').removeClass('eib2bpro-Statuspublish').addClass('eib2bpro-Status' + r.new);
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');

    });

    jQuery(".eib2bpro-Bulk_Do").on("click", function () {
        var sList = "";

        jQuery('.eib2bpro-Checkbox').each(function () {

            sThisVal = jQuery(this).attr('data-id');

            if (this.checked) {
                sList += (sList === "" ? sThisVal : "," + sThisVal);
            }
        });

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            action: "eib2bpro",
            app: 'coupons',
            do: 'bulk',
            id: sList,
            state: jQuery(this).attr('data-do')
        }, function (r) {
            if (1 === r.status) {
                jQuery.each(r.id, function (i, item) {
                    jQuery('#item_' + item).removeClass('eib2bpro-Statusprivate eib2bpro-Statuspublish eib2bpro-ItemChecked').addClass('eib2bpro-Status' + r.new);
                });
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');

    });

    jQuery(".eib2bpro-Checkbox").on("click", function () {
        if (0 === jQuery(".eib2bpro-Checkbox:checked").length) {
            jQuery(".eib2bpro-Bulk").hide();
            jQuery(".eib2bpro-Standart").show();

        } else {
            jQuery(".eib2bpro-Standart").hide();
            jQuery(".eib2bpro-Bulk").show();
        }
        if (this.checked) {
            jQuery(this).parent().parent().addClass('eib2bpro-ItemChecked');
        } else {
            jQuery(this).parent().parent().removeClass('eib2bpro-ItemChecked');
        }

        if (0 < jQuery(".eib2bpro-Checkbox[data-state=publish]:checked").length) {
            jQuery(".eib2bpro-Bulk_private").show();
        } else {
            jQuery(".eib2bpro-Bulk_private").hide();
        }

        if (0 < jQuery(".eib2bpro-Checkbox[data-state=private]:checked").length) {
            jQuery(".eib2bpro-Bulk_publish").show();
        } else {
            jQuery(".eib2bpro-Bulk_publish").hide();
        }

        jQuery(".eib2bpro-Checkbox").addClass('eib2bpro-NoHide');
    });

    jQuery(".eib2bpro-CheckAll").on("click", function () {
        if (this.checked) {
            jQuery(".eib2bpro-Standart").hide();
            jQuery(".eib2bpro-Bulk").show();
        } else {
            jQuery(".eib2bpro-Bulk").hide();
            jQuery(".eib2bpro-Standart").show();
        }

        jQuery(".eib2bpro-Checkbox").addClass('eib2bpro-NoHide').prop('checked', this.checked);
        jQuery(".eib2bpro-CheckAll").prop('checked', this.checked);


        if (0 < jQuery(".eib2bpro-Checkbox[data-state=publish]:checked").length) {
            jQuery(".eib2bpro-Bulk_private").show();
        } else {
            jQuery(".eib2bpro-Bulk_private").hide();
        }

        if (0 < jQuery(".eib2bpro-Checkbox[data-state=private]:checked").length) {
            jQuery(".eib2bpro-Bulk_publish").show();
        } else {
            jQuery(".eib2bpro-Bulk_publish").hide();
        }

    });
});
