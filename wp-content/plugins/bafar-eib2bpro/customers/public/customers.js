jQuery(document).ready(function () {
    "use strict";

    detectHash(eiB2BProGlobal.admin_url + "user-edit.php&user_id=HASH");

    jQuery('body').on('click', '.eib2bpro-Customer_Details .trig', function (e) {
        e.stopPropagation();
    });


    jQuery(".eib2bpro-Edit_Text").on("click", function () {
        jQuery(this).addClass('d-none');
        jQuery('.eib2bpro-Edit_Save').removeClass('d-none');
        jQuery(".eib2bpro-Editable").each(function () {
            var text = jQuery(this).text();
            jQuery(this).html("<input type='textbox' class='input-group' name='" + jQuery(this).attr('data-name') + "' value='" + text + "'/>");
        });

        jQuery(".eib2bpro-Editable_C").each(function () {
            var text = jQuery(this).text();
            jQuery(".eib2bpro-H", jQuery(this)).hide();
            jQuery(".eib2bpro-S", jQuery(this)).show();
        });
    });

    jQuery(".country_select").on('change', function () {

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'customers',
            do: 'states',
            country: jQuery(this).find('option:selected').attr("value")

        }, function (r) {
            if (1 === r.status) {
                jQuery("span[data-name='billing_state']").html(r.message);
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            }
        }, 'json');
    });


    jQuery(".eib2bpro-Edit_Save").on("click", function () {

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'customers',
            do: 'update',
            id: jQuery(this).attr('data-id'),
            billing_first_name: jQuery("input[name=billing_first_name]").val(),
            billing_last_name: jQuery("input[name=billing_last_name]").val(),
            billing_company: jQuery("input[name=billing_company]").val(),
            billing_address_1: jQuery("input[name=billing_address_1]").val(),
            billing_address_2: jQuery("input[name=billing_address_2]").val(),
            billing_city: jQuery("input[name=billing_city]").val(),
            billing_state: jQuery("select[name=billing_state]").find('option:selected').attr("value") ? jQuery("select[name=billing_state]").find('option:selected').attr("value") : jQuery("input[name=billing_state]").val(),
            billing_postcode: jQuery("input[name=billing_postcode]").val(),
            billing_country: jQuery("select[name=billing_country]").find('option:selected').attr("value") ? jQuery("select[name=billing_country]").find('option:selected').attr("value") : jQuery("input[name=billing_country]").val(),
            billing_email: jQuery("input[name=billing_email]").val(),
            billing_phone: jQuery("input[name=billing_phone]").val()

        }, function (r) {
            if (1 === r.status) {
                jQuery('.eib2bpro-Edit_Save').text('Saved!').css({
                    color: 'green'
                });
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                alert(r.error);
            }
        }, 'json');
    });


    jQuery('body').on('click', '.btnA', function () {

        var customer_id = jQuery(this).attr('id').replace(/item_/, '');

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'customers',
            do: 'details',
            id: customer_id
        }, function (r) {
            jQuery('#item_' + customer_id).find('.eib2bpro-Customer_Details').html(r);
        });

    });

    jQuery(".eib2bpro-Checkbox").on("click", function () {
        if (0 === jQuery(".eib2bpro-Checkbox:checked").length) {
            jQuery(".eib2bpro-Bulk").hide();

        } else {
            jQuery(".eib2bpro-Bulk").show();
        }
        if (this.checked) {
            jQuery(this).parent().parent().addClass('eib2bpro-ItemChecked');
        } else {
            jQuery(this).parent().parent().removeClass('eib2bpro-ItemChecked');
        }

        jQuery(".eib2bpro-Checkbox").addClass('eib2bpro-NoHide');
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