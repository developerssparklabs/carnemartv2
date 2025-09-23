jQuery(document).ready(function ($) {
    'use strict';

    var xhr = null;

    function eiSaveNote(th, show = true) {

        var txt = th.find('.eib2bpro-note-textarea');

        if (xhr !== null) {
            xhr.abort();
            xhr = null;
        }
        setTimeout(function () {
            xhr = $.post(eiB2BProGlobal.ajax_url, {
                _wpnonce: $('input[name=_wpnonce]').val(),
                _wp_http_referer: $('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: "eib2bpro",
                app: 'core',
                do: 'save-notes',
                id: th.attr('data-id'),
                type: th.attr('data-type'),
                color: th.attr('data-color'),
                collapsed: th.hasClass('collapsed'),
                content: txt.html()
            }, function (r) {
                if (1 === parseInt(r.status)) {
                    th.attr('data-id', r.id);
                    if (show) {
                        eiB2BProAjax();
                        eiB2BProAjax('success', eiB2BProGlobal.i18n.done)
                    }
                } else if (0 === parseInt(r.status)) {
                    eiB2BProAjax('error', r.message)
                }
            }, 'json');
        }, 300);
    }
    $('body').on('click', '.eib2bpro-note-color', function (e) {
        e.stopPropagation();

        var color = $(this).data('color');
        var th = $(this).parent().parent().parent().parent().parent();

        th.attr('data-color', color);
        th.attr('style',
            'background: #' + color + '!important').removeClass().addClass('btnA eib2bpro-note eib2bpro-shadow eib2bpro-Item eib2bpro-note-' + color);


        $('.eib2bpro-note-color').removeClass('eib2bpro-selected');
        $(this).addClass('eib2bpro-selected');

        eiSaveNote(th);
    });

    $('body').on('click', '.eib2bpro-note-b', function (e) {
        e.stopPropagation();
        document.execCommand('bold', false, null);
        eiSaveNote($(this).parent().parent().parent().parent().parent());
    });

    $('body').on('click', '.eib2bpro-note-i', function (e) {
        e.stopPropagation();
        document.execCommand('italic', false, null);
        eiSaveNote($(this).parent().parent().parent().parent().parent());
    });

    $('.eib2bpro-note-c').on('click', function (e) {
        e.stopPropagation();
        $(this).parent().parent().parent().parent().parent().find('.eib2bpro-note-textarea').select();
        document.execCommand("copy");
        $(this).parent().parent().parent().parent().parent().find('.eib2bpro-note-textarea').prop('selectionStart', 0).prop('selectionEnd', 0).blur()
        eiB2BProAjax('success', 'Copied');
    });

    $('body').on('click', '.btnA .liste', function (e) {
        var th = $(this).parent();

        th.find('.eib2bpro-note-textarea').select(0);

        if (th.hasClass('collapsed')) {
            th.find('.eib2bpro-note-title').removeClass('hidden').slideUp();
            th.find('.eib2bpro-note-buttons').removeClass('hidden').slideDown();
        } else {
            th.find('.eib2bpro-note-title').removeClass('hidden').slideDown();
            th.find('.eib2bpro-note-buttons').removeClass('hidden').slideUp();
        }

        eiSaveNote($(this).parent(), false);

    })
    $('.eib2bpro-note-textarea').doWithDelay("change keyup keydown paste cut", function (e) {
        var th = $(this);
        eiSaveNote($(this).parent().parent());
    }, 500);

    $('body').on('click', '.eib2bpro-note-empty-1', function () {
        $(this).removeClass('eib2bpro-note-empty-1').html('');
    })

    $('.eib2bpro-notes-sortable').sortable({
        axis: 'y',
        handle: '.eib2bpro-os-move',
        stop: function (event, ui) {
            var arr = $(this).sortable('toArray', {
                attribute: 'data-id'
            });
            eiB2BProAjax();
            $.post(eiB2BProGlobal.ajax_url, {
                _wpnonce: $('input[name=_wpnonce]').val(),
                _wp_http_referer: $('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: "eib2bpro",
                app: 'core',
                do: 'sort-notes',
                ids: arr
            }, function (r) {
                if (1 === parseInt(r.status)) {
                    eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
                } else {
                    eiB2BProAjax('error', r.message)
                }
            }, 'json');
        }

    });

});