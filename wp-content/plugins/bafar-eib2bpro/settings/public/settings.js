jQuery(document).ready(function ($) {
    'use strict';


    $('.carousel').on('slide.bs.carousel', function (event) {
        $('input[name=eib2bpro-app-current-tab]').val($(event.relatedTarget).data('id'));
        $('input[name=do]').val($(event.relatedTarget).data('do'));
    });


    /* LOGO */

    var frame,
        metaBox = jQuery('#eib2bpro-user-logo'),
        addImgLink = metaBox.find('.upload-custom-img'),
        imgContainer = jQuery(".eib2bpro-Settings_Logo"),
        imgIdInput = metaBox.find('.custom-img-id');

    $(document).on('click', '.upload-custom-img', function (event) {

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
            imgContainer.html('<a href="javascript:;" class="upload-custom-img upload-custom-img-text"><img src="' + attachment.url + '" alt="" class="eib2bpro-Settings_Logo_New" /></a>');
            imgIdInput.val(attachment.id);
            $('.eib2bpro-Settings_Logo').addClass('p-2');
        });

        frame.open();
    });


    /* THEMES */

    $(document).on('click', '.theme-select-a', function () {
        $('#selected_theme').val($(this).data('id'));
        $('.eib2bpro-app-save-button').trigger('click');
    })

    /* MENU */
    if (jQuery('.eib2bpro-change-icon').length > 0) {
        jQuery('.eib2bpro-change-icon').iconpicker();
    }

    if (jQuery('.eib2bpro-os-change-icon').length > 0) {
        jQuery('.eib2bpro-os-change-icon').iconpicker()
            .on('change', function (e) {

                var id = jQuery(this).attr('data-id');
                var icon = e.icon;
                jQuery('#app_id_' + id + ' .eib2bpro-app-icon').removeClass().addClass('eib2bpro-app-icon ' + icon);
                jQuery('#eib2bpro-menu-app-' + id + ' .eib2bpro-Custom_Icon_Container').removeClass().addClass('eib2bpro-Custom_Icon_Container eib2bpro-custom-icon ' + icon);
                jQuery(this).parent().parent().find('.icon_' + id).val(icon);

                jQuery('.eib2bpro-app-save-button-hidden').trigger('click');

            });

        jQuery('.eib2bpro-os-change-icon').text(' Change icon');
    }

    jQuery('body').on('change', '.app-swtich-onoff ', function () {
        jQuery('.eib2bpro-app-save-button-hidden').trigger('click');
    });

    jQuery('.submenu').sortable({
        connectWith: '.submenu',
        containment: "parent",
        axis: "y",
        tolerance: 'pointer',
        sortAnimateDuration: 200,
        sortAnimate: true,
        stop: function (event, ui) {
            jQuery('.app-save-button').trigger('click');
        }
    });

    // Reorder menu items
    var currentlyScrolling = false;

    var SCROLL_AREA_HEIB2BPROGHT = 80; // Distance from window's top and bottom edge.

    if (jQuery('.eib2bpro-app-settings-sortable').length > 0) {
        var ns = jQuery('.eib2bpro-app-settings-sortable').sortable({
            axis: "y",
            handle: '.eib2bpro-os-move',
            scroll: true,
            animation: 150,
            sort: function (event, ui) {

                if (currentlyScrolling) {
                    return;
                }

                var windowHeight = jQuery(window).height();
                var mouseYPosition = event.clientY;

                if (mouseYPosition < SCROLL_AREA_HEIB2BPROGHT) {
                    currentlyScrolling = true;

                    jQuery('body').animate({
                            scrollTop: "-=" + windowHeight / 2 + "px" // Scroll up half of window height.
                        },
                        400, // 400ms animation.
                        function () {
                            currentlyScrolling = false;
                        });

                } else if (mouseYPosition > (windowHeight - SCROLL_AREA_HEIB2BPROGHT)) {
                    currentlyScrolling = true;

                    jQuery('body').animate({
                            scrollTop: "+=" + windowHeight / 2 + "px" // Scroll down half of window height.
                        },
                        400, // 400ms animation.
                        function () {
                            currentlyScrolling = false;
                        });

                }
            },
            start: function (event, ui) {},
            stop: function (event, ui) {
                jQuery('.eib2bpro-app-save-button-hidden').trigger('click');
                jQuery('.eib2bpro-app-save-button').trigger('click');
            }
        }).disableSelection();
    }

    // FONTS

    $('.eib2bpro-app-settings-theme-font-container').jscroll({
        padding: 80
    });


    $('.eib2bpro-app-font-search').doWithDelay("keyup", function (e) {

        var t = $(this);

        if (t.val() === '') {
            $('.eib2bpro-app-settings-theme-font-container-disabled').hide();
            $('.eib2bpro-app-settings-theme-font-container').show();
        } else {
            $('.eib2bpro-app-settings-theme-font-container').hide();
            $('.eib2bpro-app-settings-theme-font-container-disabled').show();
        }
        $.ajax({
            url: eiB2BProGlobal.ajax_url,
            type: "GET",
            dataType: 'html',
            data: {
                action: 'eib2bpro',
                app: 'settings',
                do: 'font',
                s: t.val()
            },
            success: function (data) {
                if (t.val() === '') {
                    $('.eib2bpro-app-settings-theme-font-container').html(data);
                } else {
                    $('.eib2bpro-app-settings-theme-font-container-disabled').html(data);
                }
            }
        });
    }, 200)

    $(document).on('click', '.eib2bpro-app-font-selection', function (e) {
        e.preventDefault();

        $('#eib2bpro-theme').css({
            'font-family': "'" + $(this).data('id') + "'"
        });
        $('#selected_font').val($(this).data('id'));
        $('.eib2bpro-app-settings-theme-font-container,.eib2bpro-app-settings-theme-font-container-disabled').find('.card.selected').removeClass('selected');
        $(this).parent().addClass('selected');
    })

    /* COLORS */
    function LightenDarkenColor(color, percent) {

        var R = parseInt(color.substring(1, 3), 16);
        var G = parseInt(color.substring(3, 5), 16);
        var B = parseInt(color.substring(5, 7), 16);

        R = parseInt(R * (100 + percent) / 100);
        G = parseInt(G * (100 + percent) / 100);
        B = parseInt(B * (100 + percent) / 100);

        R = (R < 255) ? R : 255;
        G = (G < 255) ? G : 255;
        B = (B < 255) ? B : 255;

        var RR = ((R.toString(16).length === 1) ? "0" + R.toString(16) : R.toString(16));
        var GG = ((G.toString(16).length === 1) ? "0" + G.toString(16) : G.toString(16));
        var BB = ((B.toString(16).length === 1) ? "0" + B.toString(16) : B.toString(16));

        return "#" + RR + GG + BB;

    }

    jQuery(".eib2bpro-Settings_Color").on("mouseover", function () {
        var colors = jQuery(this).data('colors');
        var $innerCarousel = jQuery('.carousel-inner');

        if (!jQuery(this).hasClass('eib2bpro-Settings_Color_Own')) {
            jQuery(".eib2bpro-Settings_Color_Own_Div").addClass('d-none').removeClass('d-flex');

            $innerCarousel.stop().animate({
                height: '160px'
            });
        }
        jQuery.each(colors, function (key, value) {
            document.documentElement.style.setProperty("--" + key, value);
        });
    });

    jQuery(".eib2bpro-Settings_Color_Own").on("mouseover", function (e) {
        e.preventDefault();
        eiOwnColors(this);
    });

    function eiOwnColors(t) {

        var colors = jQuery(t).data('colors');
        var colors_temp = colors;

        jQuery(".eib2bpro-Settings_Color_Own_Div").removeClass('d-none').addClass('d-flex');
        jQuery('.eib2bpro-header-top').css({
            'border-bottom': '0px'
        });

        var $innerCarousel = jQuery('.carousel-inner');

        $innerCarousel.stop().animate({
            height: '450px'
        });

        jQuery.each(colors, function (key, value) {
            jQuery('.eib2bpro-Settings_Color_Own-' + key).wpColorPicker({
                width: 160,
                change: function (event, ui) {
                    document.documentElement.style.setProperty("--" + key, ui.color.toString());
                    colors_temp[key] = ui.color.toString();
                    jQuery(".eib2bpro-Settings_Color_Own").attr('data-colors', JSON.stringify(colors_temp));
                    if ('header-background' === key) {
                        document.documentElement.style.setProperty("--header-more", LightenDarkenColor(ui.color.toString(), -60));
                    } else if ('header-icons' === key) {
                        document.documentElement.style.setProperty("--header-text", ui.color.toString());
                    }

                }
            });

            jQuery('.eib2bpro-Settings_Color_Own-' + key).wpColorPicker('color', value);
        });

    }

    if ($('.eib2bpro-Settings_Color_Own').hasClass('eib2bpro-Settings_Color_Selected')) {
        eiOwnColors($('.eib2bpro-Settings_Color_Own'));
    }

    jQuery(".eib2bpro-Settings_Color, .eib2bpro-Settings_Color_Own_Save").on("click", function (e) {
        e.preventDefault();

        var colors;

        jQuery('.eib2bpro-Settings_Color_Selected').removeClass('eib2bpro-Settings_Color_Selected');
        jQuery(this).addClass('eib2bpro-Settings_Color_Selected');

        if (jQuery(this).hasClass('eib2bpro-Settings_Color_Own_Save')) {
            colors = jQuery(".eib2bpro-Settings_Color_Own").data('colors');
        } else {
            colors = jQuery(this).data('colors');
        }

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'settings',
            do: 'colors',
            val: colors
        }, function () {
            eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
        });
    });

    // OPTIONS

    $('body').on('click', '.eib2bpro-app-button-opt-click', function () {
        var t = $(this);

        if (t.data('type')) {
            if ('big_select' === t.data('type')) {
                $('.table_row_' + t.data('item')).find('.card').removeClass('btn-danger').removeClass('selected');
                t.parent().addClass('btn-danger').addClass('selected');
            }

            if ('select_group' === t.data('type')) {
                $('.table_row_' + t.data('item')).find('.btn').removeClass('btn-danger').removeClass('selected').addClass('btn-group-light');
                t.addClass('btn-danger').addClass('selected');
            }
        }

        var cond = t.data('conditions');
        if (cond) {
            $.each(cond, function (k, v) {

                if ('onoff_group' === t.data('type')) {
                    if (true === t.is(':checked')) {
                        if ('show_1' === k) {
                            $(v).slideDown().removeClass('eib2bpro-os-hidden')
                        }
                        if ('hide_1' === k) {
                            $(v).slideUp().removeClass('eib2bpro-os-hidden');
                        }
                    } else {
                        if ('show_0' === k) {
                            $(v).slideDown().removeClass('eib2bpro-os-hidden')
                        }
                        if ('hide_0' === k) {
                            $(v).slideUp().removeClass('eib2bpro-os-hidden');
                        }
                    }
                } else {
                    if ('show' === k) {
                        $(v).slideDown().removeClass('eib2bpro-os-hidden')
                    }

                    if ('hide' === k) {
                        $(v).slideUp().removeClass('eib2bpro-os-hidden');
                    }
                }

            })
        }
        if (t.data('item')) {
            $('.opt-' + t.data('item')).val(t.data('rel'));
        }

        var height = $('.carousel-item.active').height();
        var $innerCarousel = $('.carousel-inner');

        $('.carousel-inner').css({
            'height': 'auto'
        })
    });

    // Sortable
    jQuery(".eib2bpro_Sortable").sortable({
        axis: "y",
        revert: true,
        scroll: false,
        containment: 'parent',
        placeholderx: "sortable-placeholder",
        cursor: "move",
        opacity: 1,
        animation: 150
    });

    $('body').on('click', '.eib2bpro-activate-btn', function () {
        $(this).text('Please wait');
    });

})