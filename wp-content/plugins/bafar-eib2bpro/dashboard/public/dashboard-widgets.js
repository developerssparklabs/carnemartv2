jQuery(function ($) {
    "use strict";

    function serialize() {

        var widgets = [];

        grid.getItems().forEach(function (item, i) {
            var widget = {};
            widget.id = jQuery(item.getElement()).attr('data-id');
            widget.w = jQuery(item.getElement()).attr('data-w');
            widget.h = jQuery(item.getElement()).attr('data-h');
            widgets.push(widget);
        });

        return widgets;

    }

    /* Remap widgets */

    function remap2(items) {

        eiB2BProAjax();

        jQuery.ajax({
            type: 'POST',
            url: eiB2BProGlobal.ajax_url,
            data: {
                _wpnonce: jQuery('input[name=_wpnonce]').val(),
                _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: 'eib2bpro',
                app: 'dashboard',
                do: 'remap',
                p: 'dashboard',
                widgets: items
            },
            cache: false,
            headers: {
                'cache-control': 'no-cache'
            },
            success: function (response) {
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            }
        }, 'json');
    }

    var gridster;
    if (jQuery(window).width() > 800) {
        var gw = ($('.eib2bpro-title >div').width() - 585) / 20;
        gridster = $(".gridster>ul").gridster({
            widget_base_dimensions: [gw, 14],
            autogenerate_stylesheet: true,
            min_cols: 20,
            max_cols: 20,
            extra_cols: 0,
            widget_margins: [15, 15],
            serialize_params: function ($w, wgd) {
                return {
                    id: $w.data('id'),
                    col: wgd.col,
                    row: wgd.row,
                    w: wgd.size_x,
                    h: wgd.size_y
                }
            },

            resize: {
                enabled: true,
                resize: function (e, ui, $widget) {},
                start: function (e, ui, $widget) {},
                stop: function (e, ui, $widget) {
                    remap2(gridster.serialize());
                }
            },

            draggable: {
                drag: function (event, ui) {},
                stop: function (event, ui) {
                    remap2(gridster.serialize());
                }
            }

        }).data('gridster');
        $('.gridster >ul').css({
            'padding': '0'
        });

        $('.gridster, .eib2bpro-Widget_Add').animate({
            opacity: 1
        }, 200);

        window.onresize = refreshGrid;
    } else {
        jQuery('.gridster').animate({
            opacity: 1
        }, 100);
    }

    function refreshGrid() {
        var gw = ($('.eib2bpro-title >div').width() - 585) / 20;
        gridster.destroy();
        gridster = $(".gridster>ul").gridster({
            widget_base_dimensions: [gw, 14],
            autogenerate_stylesheet: true,
            min_cols: 20,
            max_cols: 20,
            extra_cols: 0,
            widget_margins: [15, 15],
            serialize_params: function ($w, wgd) {
                return {
                    id: $w.data('id'),
                    col: wgd.col,
                    row: wgd.row,
                    w: wgd.size_x,
                    h: wgd.size_y
                }
            },

            resize: {
                enabled: true,
                resize: function (e, ui, $widget) {},
                start: function (e, ui, $widget) {},
                stop: function (e, ui, $widget) {
                    remap2(gridster.serialize());
                }
            },

            draggable: {
                drag: function (event, ui) {},
                stop: function (event, ui) {
                    remap2(gridster.serialize());
                }
            }

        }).data('gridster');
    }

    if ("1" === eiB2BProGlobal.remap_widgets) {
        remap2(gridster.serialize());
    }

    /* Widget Live */
    var lasttime = Date.now || function () {
        return +new Date();
    };

    var counter = 0;

    var audio = new Audio();
    audio.preload = 'auto';
    audio.volume = 0.1;

    var delay = 250;

    // For Safari
    if (/^((?!chrome|android).)*safari/i.test(navigator.userAgent)) {
        delay = 0;
    }

    window.audio = audio;

    window.reload_widgets = function (new_counter) {

        if (new_counter) {
            counter = new_counter;
        }

        jQuery.ajax({
            type: 'POST',
            url: eiB2BProGlobal.ajax_url,
            data: {
                _wpnonce: jQuery('input[name=_wpnonce]').val(),
                _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: 'eib2bpro',
                app: 'dashboard',
                do: 'get-widgets',
                t: lasttime,
                c: counter
            },
            cache: false,
            headers: {
                'cache-control': 'no-cache'
            },
            success: function (response) {

                jQuery.each(jQuery.parseJSON(response), function (i, item) {

                    switch (item.type) {

                        case 'system':
                            lasttime = item.lasttime;
                            break;

                        case 'lastactivity':
                            var offline_sess = item.result.off_time;

                            jQuery.each(item.result.updated, function (i, item) {
                                jQuery('#eib2bpro-Widget_Lastactivity_Sess_' + item).remove();
                                if (-2 === item) {
                                    jQuery('.eib2bpro-Widget_Lastactivity_row').remove();
                                }
                            });

                            jQuery('#eib2bpro-Widget_' + i + " .eib2bpro-Widget_Content .eib2bpro-Widget_Lastactivity_container").prepend(item.result.list);
                            jQuery('.bs-tooltip-bottom').remove();
                            jQuery('[data-toggle="tooltip"]').tooltip({
                                boundary: 'window'
                            });

                            if (jQuery(".eib2bpro-Widget_Lastactivity_container").hasClass("eib2bpro-Range_online")) {
                                jQuery(".eib2bpro-Time_" + offline_sess).remove();
                            } else {

                                jQuery.each(jQuery(".eib2bpro-Time_" + offline_sess), function (i, item) {
                                    jQuery(".badge-success", item).html(jQuery(".badge", item).attr("data-time")).removeClass('badge').removeClass('badge-success');
                                });
                            }

                            if (jQuery(".eib2bpro-Widget_Lastactivity_row").length === 0) {
                                jQuery(".eib2bpro-EmptyTable").addClass("animated").addClass("slideInUp").addClass("d-flex");
                            } else {
                                jQuery(".eib2bpro-EmptyTable").removeClass('d-flex').hide();

                            }

                            break;

                        case 'onlineusers':
                            setGaugeMax(i, parseInt(item.result), 0, "set");
                            break;

                        case 'funnel':
                            if (null !== item.result) {
                                jQuery('#eib2bpro-Widget_' + i + " .eib2bpro-Widget_Content").html(item.result);
                                eiDrawFunnel();
                            }
                            break;

                        case 'links':
                            break;

                        default:
                            if (null !== item.result) {
                                jQuery('#eib2bpro-Widget_' + i + " .eib2bpro-Widget_Content").html(item.result);
                            }
                            break;
                    }
                });
            }
        }, 'json');
    };

    /* Refresh widgets every X seconds */
    if (eiB2BProGlobal.refresh > 9000) {
        setInterval(function () {
            ++counter;
            reload_widgets();
        }, parseInt(eiB2BProGlobal.refresh));
    }

    /* Widget List */

    jQuery(".eib2bpro-Widget_Add_Now").on("click", function () {

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'dashboard',
            do: 'add-widget',
            id: jQuery(this).attr('data-id'),

        }, function (r) {
            if (1 === r.status) {
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
                window.parent.location.reload(true);
                window.parent.panel.slideReveal('hide');
            } else {
                alert(r.error);
            }
        }, 'json');

    });


    jQuery(".eib2bpro-Widget_Delete").on("click", function () {

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'dashboard',
            do: 'delete-widget',
            id: jQuery(this).attr('data-id'),

        }, function (r) {
            if (1 === r.status) {
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
                window.parent.location.reload(true);
                window.parent.panel.slideReveal('hide');
            } else {
                alert(r.error);
            }
        }, 'json');

    });

    $(document).on('click', ".eib2bpro-Widget_Settings_Range", function (e) {
        var widgetid = $(this).data('id');
        var widgettype = $(this).data('widgettype');
        $(".eib2bpro-Widget_Settings_Range[data-id=" + widgetid + "]").removeClass("eib2bpro-Selected");

        $(".eib2bpro-Widget_" + widgettype + "_container")
            .removeClass("eib2bpro-Range_online")
            .removeClass("eib2bpro-Range_all")
            .addClass("eib2bpro-Range_" + $(this).attr("data-range"));

        $('.eib2bpro-Widget_' + widgettype + '_container').css('opacity', '0.3');

        $(this).addClass("eib2bpro-Selected");

        $.ajax({
            type: 'POST',
            url: eiB2BProGlobal.ajax_url,
            data: {
                _wpnonce: $('input[name=_wpnonce]').val(),
                _wp_http_referer: $('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: "eib2bpro",
                app: 'dashboard',
                do: 'set-range',
                id: widgetid,
                set_id: 'range',
                s: $(this).attr("data-range")
            },
            cache: false,
            headers: {
                'cache-control': 'no-cache'
            },
            success: function (response) {
                window.reload_widgets(-2);
                setTimeout(function () {
                    $('.eib2bpro-Widget_' + widgettype + '_container').css('opacity', '1');
                }, 1000);

            }
        }, 'json');
    });


    if (jQuery('#eib2bpro-wp-notices').text().trim().length > 0 && jQuery('#eib2bpro-dashboard-wc-admin').length > 0) {
        jQuery('.eib2bpro-title > div > h3').append('<a href="javascript:;" class="eib2bpro-WP_Notice_Show"><span class="eib2bpro-WP_Notice">You have notice(s), click to show</span></a>');
    }

    jQuery('.eib2bpro-WP_Notice_Show').on("click", function () {
        jQuery('#eib2bpro-wp-notices').toggle();
    });


    /* ONLINE USERS */
    function eiDrawGauge() {
        var opts = {
            lines: 12, // The number of lines to draw
            angle: 0.06, // The span of the gauge arc
            lineWidth: 0.5, // The line thickness
            pointer: {
                length: 0.75, // The radius of the inner circle
                strokeWidth: 0.035, // The thickness
                color: '#000' // Fill color
            },
            limitMax: false,
            colorStart: '#6FADCF', // Colors
            colorStop: '#8FC0DA',
            strokeColor: '#E0E0E0',
            generateGradient: true,
            highDpiSupport: true
        };
        setGaugeMax($('.eib2bpro-Widget_onlineusers_Current').data('widgetid'), parseInt($('.eib2bpro-Widget_onlineusers_Current').text()), parseInt($('#bp-eib2bpro-wdg-ov--max').text()), "", opts);
    }
    eiDrawGauge();

    /* FUNNEL */
    function eiDrawFunnel() {
        // funnel
        var funnel_data = {
            labels: $('#eib2bpro-Chart_Conversation').data('labels'),
            colors: ['#8FC0DA', '#efefef'],
            values: $('#eib2bpro-Chart_Conversation').data('values'),
        }

        var graph = new FunnelGraph({
            container: '#eib2bpro-Chart_Conversation',
            gradientDirection: 'horizontal',
            data: funnel_data,
            displayPercent: true,
            direction: 'horizontal',
            height: 205
        });
        setTimeout(function () {
            graph.draw();
        }, 500);
    }
    eiDrawFunnel();

});