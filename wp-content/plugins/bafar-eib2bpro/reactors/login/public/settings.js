jQuery(document).ready(function () {
    "use strict";

    var frame, selectedImg;

    jQuery(document).on('click', '.remove-image', function (event) {

        selectedImg = jQuery(this);
        jQuery('.eib2bpro-Settings_Logo_' + selectedImg.data('pr')).html('<a href="javascript:;" data-pr="' + selectedImg.data('pr') + '" class="upload-custom-img upload-custom-img-text">' + jQuery('.custom-img-container').data('i18n') + '</a>');
        jQuery('.custom-img-' + selectedImg.data('pr')).val('');

    });


    jQuery(document).on('click', '.upload-custom-img', function (event) {

        selectedImg = jQuery(this);

        event.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            selectedImg.parent().html('<img src="' + attachment.url + '" class="upload-custom-img eib2bpro-max-h-120" />');
            jQuery('.custom-img-' + selectedImg.data('pr')).val(attachment.id);
        });

        frame.open();
    });

});