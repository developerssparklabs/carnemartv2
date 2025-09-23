(function ($) {
    "use strict";

    $.ajax({
        type: 'POST',
        url: eiB2BProPublic.ajax_url,
        data: {
            nonce: eiB2BProPublic.nonce,
            action: 'eib2bpro_public',
            app: 'core',
            do: 'tracker',
            t: eiB2BProPublic.eib2bpro_t,
            i: eiB2BProPublic.eib2bpro_i
        },
        dataType: 'json',
        cache: false,
        headers: {
            'cache-control': 'no-cache'
        }
    });

})(jQuery);