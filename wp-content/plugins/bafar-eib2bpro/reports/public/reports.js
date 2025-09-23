jQuery(document).ready(function ($) {
    "use strict";

    var $gal = $("#eib2bpro-eeez"),
        mmv = false,
        cnt = 1,
        galW = $gal.outerWidth(true),
        galSW = $gal[0].scrollWidth,
        wDiff = (galSW / galW) - 1,
        mPadd = 60,
        damp = 20,
        mX = 0,
        mX2 = 0,
        posX = 0,
        wNew = 0,
        mmAA = galW - (mPadd * 1.2),
        mmAAr = (galW / mmAA);

    var intv = setInterval(function () {
        ++cnt;

        if (!mmv) {
            posX = mX = mX2 = $(window).width();

            $("#eib2bpro-eeez").scrollLeft($(window).width() * $("#eib2bpro-eeez").data('max')).css({
                opacity: 1
            });
        }

        posX += (mX2 - posX) / damp; // catching delay
        if (wNew !== (posX * wDiff)) {
            $gal.scrollLeft(posX * wDiff);
            wNew = posX * wDiff;
        }

    }, 10);

    $gal.on('mousemove', function (e) {
        mmv = true
        if (cnt < 100) {
            return;
        }
        mX = e.pageX - $(this).parent().offset().left - this.offsetLeft;
        mX2 = Math.min(Math.max(0, mX - mPadd), mmAA) * mmAAr;
    });

    $("#eib2bpro-eeez").scrollLeft($(window).width() * $("#eib2bpro-eeez").data('max')).css({
        opacity: 1
    });
});