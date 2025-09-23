jQuery(document).ready(function ($) {
    "use strict";
    $(document).on('click', '.eib2bpro-stop', function (e) {
        e.stopPropagation();
    });

    function eiRulesInit() {
        $("li:not(#eib2bpro-rule-id--1) select.eib2bpro-rule-selectize:not('.selectized, .eib2bpro-rule-selectize-template'),li:not(#eib2bpro-rule-id--1) input.eib2bpro-rule-selectize:not('.selectized, .eib2bpro-rule-selectize-template')").removeClass('hidden').selectize({
            plugins: ["remove_button"],
            valueField: "id",
            delimeter: ',',
            maxItems: 999,
            labelField: "name",
            searchField: "name",
            create: false,
            render: {
                option: function (item, escape) {
                    return (
                        "<div class='eib2bpro-app-user-select-div'>" +
                        '<span class="eib2bpro-app-user-select-name">' +
                        escape(item.name) +
                        "</span>" +
                        "</div>"
                    );
                },
            },
            load: function (query, callback) {
                if (!query.length) return callback();
                $.post(eiB2BProGlobal.ajax_url, {
                    app: 'rules',
                    action: 'eib2bpro',
                    asnonce: eiB2BProGlobal.asnonce,
                    do: 'search',
                    for: $($(this)[0].$input[0]).attr('data-for'),
                    query: query
                }, function (res) {
                    callback(res);
                }, 'json');
            }
        });
    }

    if ($('.eib2bpro-rule-selectize').length > 0) {
        eiRulesInit();
    }

    $(document).on('click', '.eib2bpro-rule-delete-line', function (e) {
        e.preventDefault();
        if ($(this).data('type') === 'products') {
            $(this).parent().parent().parent().find('.eib2bpro-rule-new-line').removeClass('d-none').show();
        }
        $(this).closest('.eib2bpro-rule-line').remove();
    });

    $(document).on('click', '.eib2bpro-rule-new-line', function (e) {
        e.preventDefault();

        var uniq = Math.random().toString(36).substr(2, 9);
        var template = $(this).parent().find('.eib2bpro-rule-line-template').clone();

        $.each(template.find('.eib2bpro-rule-select, .eib2bpro-rule-selectize, .eib2bpro-rule-input'), function (i, k) {
            $(k).attr('name', $(k).attr('name') + '_X' + uniq + '');
        });
        template.removeClass('eib2bpro-rule-line-template').addClass('d-flex mb-3');
        template.find('.eib2bpro-rule-selectize').removeClass('eib2bpro-rule-selectize-template');
        template.show();

        $(this).parent().find('.eib2bpro-rule-lines').append(template);

        if ($(this).data('type') === 'products') {
            $(this).hide();
        }
        eiRulesInit();
    });


    $(document).on('change', '.eib2bpro-rule-select', function (e) {

        var selected = $(this).find(":selected");

        if (selected.data('cond')) {
            $('.eib2bpro-rule-all', selected.closest('.eib2bpro-parent.eib2bpro-rule-line')).hide();
            $.each(selected.data('cond'), function (showOrHide, selectors) {
                if ('hide' === showOrHide) {
                    $(selectors, selected.closest('.eib2bpro-parent')).hide();
                }
                if ('show' === showOrHide) {
                    $(selectors, selected.closest('.eib2bpro-parent')).show();
                }
            })
        }
    });

    $(document).on('keyup', '.eib2bpro-rule-input[name=title]', function (e) {
        $(this).closest('.btnA').find('.eib2bpro-rule--name').text($(this).val());
    });

    $(document).on('change', '.eib2bpro-rule-type', function (e) {
        var selected = $(this).find(":selected");
        $(this).closest('.btnA').find('.eib2bpro-rule--type').text(selected.text());
    });

    $(document).on('click', '.btn-save', function (e) {
        e.preventDefault();
    });


    $(document).on('click', '.eib2bpro-new-rule-button', function () {

        $('.eib2bpro-EmptyTable').removeClass('d-flex').addClass('d-none');

        var uniq = Math.random().toString(36).substr(2, 9);
        var template = $('.eib2bpro-rules-list > #eib2bpro-rule-id--1').clone();

        template.attr('id', 'eib2bpro-rule-id-' + uniq);
        template.find('.btnA').attr('data-id', 'item_' + uniq);
        template.find('.btnA').attr('data-target', '#item_d_' + uniq);
        template.find('input[name=status]').prop('checked', 'checked');
        template.find('.eib2bpro-rule-collapse').attr('id', 'item_d_' + uniq);
        template.find('.eib2bpro-rule--name').text('New rule');
        template.find('.eib2bpro-rule--type').html(template.find('.eib2bpro-rule-type').find(':selected').text());
        template.find('.eib2bpro-rule-collapse').addClass('show');
        template.find('.btnA').removeClass('collapsed');
        template.find('.eib2bpro-confirm').hide();
        template.show();

        $('.eib2bpro-rules-list').append(template);

        $(window).scrollTop(template.offset().top - 90);
        eiRulesInit();
    });


    // sorting
    $('.eib2bpro-sortable-rules').sortable({
        containment: "parent",
        axis: "y",
        handle: '.eib2bpro-os-move',
        tolerance: 'pointer'
    });

});