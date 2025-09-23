jQuery(document).ready(function () {
    "use strict";

    if (!window.isMobile) {
        var searchbox = jQuery('.search-box');
        var tmp = searchbox.html();

        searchbox.parent().prepend('<div class="eib2bpro-WP_searchbox"><a class="eib2bpro-WP_searchbox_Filter" href="javascript:;">Filter</a> &nbsp; &nbsp; <a class="eib2bpro-WP_searchbox_Search" href="javascript:;">Search</a></div>');

        jQuery('.eib2bpro-WP_searchbox .eib2bpro-WP_searchbox_Search').on('click', function () {
            jQuery(this).parent().remove();
            searchbox.show();
            jQuery('#post-search-input').focus();
        });

        jQuery('.eib2bpro-WP_searchbox .eib2bpro-WP_searchbox_Filter').on('click', function () {
            jQuery('.tablenav.top').slideToggle('fast');
        });

        jQuery('.check-column input[type="checkbox"]').on('click', function () {
            jQuery('.tablenav.top').slideDown('fast');
        });

        if ("1" === eib2bpro_style.openclick) {

            jQuery('.wp-list-table tbody tr').on('click', function (e) {
                var th = jQuery(this);
                var excludeInputs = [
                    "text", "password", "number", "email", "url", "range", "date", "month", "week", "time", "datetime",
                    "datetime-local", "search", "color", "tel", "textarea", "checkbox", "button", "a"
                ];
                if (!th.hasClass('type-shop_order') && !th.hasClass('plugin-update-tr') && e.target.tagName.toLowerCase() !== 'a' && jQuery.inArray(e.target.type, excludeInputs) === -1) {
                    jQuery('.wp-list-table tbody tr').not(this).removeClass('eib2bpro-Energizer_Click');
                    th.toggleClass('eib2bpro-Energizer_Click');
                }

            });
        }

    }

});