jQuery(document).ready(function ($) {
    'use strict';

    var xhr = null;

    function eiSaveTodo(th) {

        var checked = th.find('.eib2bpro-todo-input-check').is(':checked');

        th.find('.eib2bpro-todo-checked').html('<i class="ri-loader-4-line eib2bpro-todo-spinner"></i>');

        if (xhr !== null) {
            xhr.abort();
            xhr = null;
        }
        xhr = $.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: $('input[name=_wpnonce]').val(),
            _wp_http_referer: $('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'core',
            do: 'save-todo',
            id: th.attr('data-id'),
            status: th.attr('data-status'),
            content: th.find('.eib2bpro-todo-input').html(),
            checked: checked,
            limit: $('.eib2bpro-me-todo-container').attr('data-limit')
        }, function (r) {
            if (1 === parseInt(r.status)) {
                $('.eib2bpro-me-todo-container').html(r.html);
                eiTodoSortable();
                $('.eib2bpro-autofocus')[0].focus({
                    preventScroll: true
                })
            } else if (0 === parseInt(r.status)) {
                eiB2BProAjax('error', r.message)
            }
        }, 'json');
    }

    $('body').on('click', '.eib2bpro-todo-show-more', function (e) {
        $('.eib2bpro-me-todo-container').attr('data-limit', parseInt($('.eib2bpro-me-todo-container').attr('data-limit')) + 50);
        eiSaveTodo($(this).parent());
    });

    $('body').on('click', '.eib2bpro-todo-delete', function (e) {
        $(this).parent().parent().attr('data-status', 0);
        eiSaveTodo($(this).parent().parent());
    });

    $('body').on('change', '.eib2bpro-todo-input-check', function (e) {
        eiSaveTodo($(this).parent().parent());
    });

    $('body').on('click', '.eib2bpro-todo-add', function (e) {
        $('.eib2bpro-todo-add').hide();
        $('.eib2bpro-todo-input').show();
        $('.eib2bpro-todo-input-check-0').removeClass('d-none');
        $('.eib2bpro-todo-new').trigger('click').focus();
    });
    $('body').on('click', '.eib2bpro-todo-input', function (e) {
        $('.eib2bpro-todo-editing').removeClass('eib2bpro-todo-editing');
        $(this).addClass('eib2bpro-todo-editing');
    });

    $('body').on('keypress', '.eib2bpro-todo-input', function (e) {
        e.stopPropagation();

        if (e.which === 13) {
            e.preventDefault();
            eiSaveTodo($(this).parent().parent());
            return;
        }
    });

    function eiTodoSortable() {

        $('.eib2bpro-todo-sortable').sortable({
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
                    do: 'sort-todos',
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
    }

    eiTodoSortable();

});