(function ($) {
    "use strict";

    /*
    WithDelay jQuery plugin
    Author: Brian Grinstead
    MIT license: http://www.opensource.org/licenses/mit-license.php
    http://github.com/bgrins/bindWithDelay
    http://briangrinstead.com/files/bindWithDelay
    */

    $.fn.doWithDelay = function (type, data, fn, timeout, throttle) {

        if ($.isFunction(data)) {
            throttle = timeout;
            timeout = fn;
            fn = data;
            data = undefined;
        }

        // Allow delayed function to be removed with fn in unbind function
        fn.guid = fn.guid || ($.guid && $.guid++);

        // Bind each separately so that each element has its own delay
        return this.each(function () {

            var wait = null;

            var cb = function () {
                var e = $.extend(true, {}, arguments[0]);
                var ctx = this;
                var throttler = function () {
                    wait = null;
                    fn.apply(ctx, [e]);
                };

                if (!throttle) {
                    clearTimeout(wait);
                    wait = null;
                }
                if (!wait) {
                    wait = setTimeout(throttler, timeout);
                }
            }

            cb.guid = fn.guid;

            $(this).on(type, data, cb);
        });
    };
})(jQuery);