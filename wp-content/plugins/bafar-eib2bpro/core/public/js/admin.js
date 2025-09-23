jQuery(document).ready(function ($) {
    'use strict';

    // Solves the conflict with jQuery UI and Bootstrap
    jQuery.noConflict();

    // Check if Bootstrap is enabled
    if ((typeof jQuery().emulateTransitionEnd === 'function')) {
        jQuery('[data-toggle="tooltip"]').tooltip();
    } else {
        jQuery('body').on('click', '.eib2bpro-Item.btnA', function (e) {
            jQuery(this).toggleClass('collapsed');
            jQuery(this).find('> div:not(.liste)').toggleClass('collapse');
        });
    }

    jQuery('.eib2bpro-MainMenu, .eib2bpro-MainMenu ul').css('overflow', 'unset');
    jQuery('#eib2bpro-panel, #eib2bpro-panel2').removeClass('d-none');
    jQuery('.eib2bpro #eib2bpro-wp-notices:not(.eib2bpro-WP_Notices_Container)').show();

    jQuery('body').on('click', '.eib2bpro-StopPropagation', function (e) {
        e.stopPropagation();
    });

    jQuery('body').on('click', '.btnA a', function (e) {
        e.stopPropagation();
    });

    if ($('.eib2bpro-autofocus').length > 0) {
        $('.eib2bpro-autofocus')[0].focus({
            preventScroll: true
        })
    }

    /* CAROUSEL */
    if ($('.carousel').length > 0) {
        jQuery('.carousel').carousel({
            interval: 100,
            ride: 'false',
            touch: false
        });

        jQuery('.carousel').carousel('pause');
        jQuery('.carousel-groups').css('pointer-events', 'auto');

        if (0 === $('.carousel-indicators > li.active').data('save')) {
            $('.eib2bpro-app-save-button').hide().animate({
                opacity: 0
            });
        } else {
            $('.eib2bpro-app-save-button').show().animate({
                opacity: 1
            });
        }


        jQuery('.carousel').on('slide.bs.carousel', function (event) {
            var height = jQuery(event.relatedTarget).height();
            var $innerCarousel = jQuery(event.target).find('.carousel-inner');

            $innerCarousel.animate({
                height: height
            });
        });

        $('body').on('click', '.carousel-indicators > li', function () {
            if (0 === $(this).data('save')) {
                $('.eib2bpro-app-save-button').hide().animate({
                    opacity: 0
                });
            } else {
                $('.eib2bpro-app-save-button').show().animate({
                    opacity: 1
                });
            }

            if (history.pushState && $(this).attr('data-location')) {
                history.pushState(null, null, window.location.href + "&tab=" + $(this).attr('data-location'));
            }

        });
    }

    $("body").on('submit', 'form', function (e) {
        if ($('.eib2bpro-app-save-button', $(this)).length > 0) {
            e.preventDefault();
            eiB2BProAppSave($('.eib2bpro-app-save-button', $(this)));
        }
    });


    /* GLOBAL */
    var openNotifications = 0;
    var notificationsId = 0;
    var sidebar_opened = false;
    window.refreshOnClose = 0;
    window.isMobile = false; //initiate as false

    // device detection
    if (jQuery(window).width() < 820) {
        window.isMobile = true;
    }

    $('body').on('click', '.eib2bpro-confirm', function (e) {

        if ($(this).data('confirm')) {
            if (!confirm($(this).data('confirm'))) {
                return false;
            }
        }
    });


    $('body').on('click', '.eib2bpro-app-ajax', function (e) {

        if (e.target.type !== 'checkbox') {
            e.preventDefault();
        }

        var t = $(this);
        var url = t.attr('href');
        var data = t.data();

        if (t.data('confirm')) {
            if (!confirm(t.data('confirm'))) {
                return false;
            }
        }

        if (e.target.type === 'checkbox') {
            data['checked'] = $(this).is(':checked');
        }

        eiB2BProAjax();

        $.ajax({
            url: eiB2BProGlobal.ajax_url,
            type: "POST",
            dataType: 'json',
            data: data,

            success: function (data) {

                if (1 === data.status) {

                    window.after = data.after;

                    $.each(data.after, function (k, v) {
                        if ('message' === k) {
                            eiB2BProAjax('success', v);
                        }
                        if ('close' === k) {
                            window.panel.slideReveal("hide");
                        }
                        if ('close2' === k) {
                            window.panel2.slideReveal("hide");
                        }
                        if ('html' === k) {
                            $(v.container).html(v.html);
                        }
                        if ('addClass' === k) {
                            $(v.container).addClass(v.class);
                        }
                        if ('removeClass' === k) {
                            $(v.container).removeClass(v.class);
                        }
                        if ('refresh_window' === k) {
                            window.location = window.location;
                        }
                        if ('refresh_iframe_window' === k) {
                            window.location = window.location;
                        }
                        if ('redirect' === k) {
                            window.location = v;
                        }
                        if ('redirect_parent' === k) {
                            window.parent.location = v;
                        }

                        if ('scrollTo' === k) {
                            $('html, body').animate({
                                scrollTop: $(v).position(true).top
                            }, 500);
                        }

                        if ('scrollMe' === k) {
                            $('html, body').animate({
                                scrollTop: t.position(true).top
                            }, 500);
                        }

                        if ('func' === k) {
                            $.each(v, function (kk, vv) {
                                window[vv]();
                            });
                        }

                    });
                    eiB2BProAjax('success', data.message);
                } else {
                    eiB2BProAjax('error', data.message);
                }
            }
        });
    });

    /* PANELS */


    function panelg(new_width) {

        window.mobilemenu = jQuery('#eib2bpro-panel3').slideReveal({
            position: "left",
            push: true,
            overlay: false,
            width: '200px',
            trigger: $('.mobilemenu'),
            shown: function (slider) {
                jQuery('html,body').css({
                    'overflow': 'hidden'
                });
            },
            hidden: function (slider, panelger) {
                jQuery('html,body').css({
                    'overflow': 'auto'
                });
            }
        }).removeClass('d-none');


        window.panel = jQuery('#eib2bpro-panel').slideReveal({
            position: "right",
            push: false,
            overlay: true,
            width: new_width,
            show: function (slider) {
                window.mobilemenu.slideReveal('hide');
            },
            shown: function (slider) {
                jQuery('body').css({
                    'overflow': 'hidden'
                });
                window.sidebar_opened = true;
                window.after = {};
            },
            hidden: function (slider, panelger) {
                jQuery('body').css({
                    'overflow': 'overlay'
                });

                jQuery("#inbrowser--loading").addClass('d-flex').removeClass('d-none hidden');

                window.sidebar_opened = false;
                checkNotifications();

                window.location.hash = '#-';

                if (1 === window.refreshOnClose) {
                    window.location.reload(true);
                }
            }
        });

        window.panel2 = jQuery('#eib2bpro-panel2').slideReveal({
            position: "right",
            push: false,
            overlay: true,
            width: new_width,
            shown: function (slider) {
                jQuery('body').css({
                    'overflow': 'hidden'
                });
                window.sidebar_opened = true;
                window.after = {};

            },
            hidden: function (slider, panelger) {
                window.location.hash = '#-';

            }
        });
    }

    panelg(eiB2BProGlobal.theme_panel_width);

    window.isMobile = false; //initiate as false

    // device detection
    if (jQuery(window).width() < 820) {
        window.isMobile = true;
    }

    $('body').on('click', '.eib2bpro-panel2', function (e) {
        e.preventDefault();
        window.parent.show_panel_window($(this).attr('href'), $(this).data('width'), '2');
    });

    /* Quick Link */
    if (1 === eiB2BProGlobal.enable.ui_quick_link) {
        $('body').on('click', '.eix-quick', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var new_width = eiB2BProGlobal.theme_panel_width;
            var panel_id = '2';

            if ($(this).data('width')) {
                new_width = $(this).data('width');
            }

            if (self === top) {
                panel_id = '';
            }

            window.parent.show_panel_window($(this).attr('data-href'), new_width, panel_id);
        });
    }

    $('body').on('mouseover', '.eib2bpro-panel, .eix-quick', function (e) {
        var new_width = eiB2BProGlobal.theme_panel_width;
        if ($(this).data('width')) {
            new_width = $(this).data('width');
        }
        $('#eib2bpro-panel').css('right', '-' + new_width);
        $('#eib2bpro-panel').css('width', new_width);
    });

    $('body').on('click', '.eib2bpro-panel', function (e) {

        if (!isMobile || 1 === 1) {
            e.preventDefault();

            if ($(this).data('confirm')) {
                if (!confirm($(this).data('confirm'))) {
                    return false;
                }
            }

            if ($(this).hasClass('trig-close')) {
                refreshOnClose = 1;
            } else {
                refreshOnClose = 0;
            }

            if ($(this).data('hide-close') && !isMobile) {
                $('.eib2bpro-Trig_Close').hide();
            } else {
                $('.eib2bpro-Trig_Close').show();
            }

            if (jQuery(this).attr('data-hash')) {
                window.location.hash = jQuery(this).attr('data-hash');
            }
            var bg = '#f5f5f5';
            var new_width = eiB2BProGlobal.theme_panel_width;

            if ($(this).data('width')) {
                new_width = $(this).data('width');
            }

            if ($(this).data('background')) {
                bg = $(this).data('background');
            }

            show_panel_window($(this).attr('href'), new_width, '', bg);

        } else {
            if (jQuery('body').hasClass('eib2bpro-half')) {
                window.location = eiB2BProGlobal.admin_url + 'admin.php?page=eib2bpro&app=go&in=' + encodeURIComponent(jQuery(this).attr('href')) + "&asnonce=" + eiB2BProGlobal.asnonce;
                return false;
            }
        }
    });

    window.show_panel_window = function (url, new_width, ti, bg) {

        if ($(window).width() < parseInt(new_width)) {
            new_width = '100%';
        }

        $('#eib2bpro-panel' + ti).css('width', new_width);

        jQuery("#inbrowser--loading" + ti).addClass('d-flex').removeClass('d-none hidden').attr('style',
            'background:' + bg + '!important'
        );
        jQuery(".eib2bpro-Trig_Close").addClass('d-none');

        jQuery('[data-toggle="tooltip"]').tooltip('hide');

        if (ti === '2')
            window.panel2.slideReveal("show");
        else
            window.panel.slideReveal("show");

        setTimeout(function () {
            jQuery("#inbrowser" + ti).attr("src", url);
        }, 200);

        jQuery('#inbrowser' + ti).on("load", function () {
            jQuery("#inbrowser" + ti).show();
            jQuery("#inbrowser--loading" + ti).removeClass('d-flex').addClass('d-none');
            jQuery(".eib2bpro-Trig_Close").removeClass('d-none');
        });
    }

    jQuery('#inbrowser, #inbrowser2, #eib2bpro-frame').on('load', function () {
        jQuery("#inbrowser").show();
        jQuery("#inbrowser2").show();
        setTimeout(function () {
            jQuery("#eib2bpro-frame").show();
        }, 300);
        jQuery("#inbrowser--loading").removeClass('d-flex').addClass('d-none');
        jQuery("#inbrowser--loading2").removeClass('d-flex').addClass('d-none');
        jQuery(".eib2bpro-Trig_Close").removeClass('d-none');
    });

    $('body').on('click', '.eib2bpro-Trig_CloseButton', function () {
        if (0 === parseInt(jQuery('#eib2bpro-panel2').css('right')))
            window.panel2.slideReveal("hide");
        else
            window.panel.slideReveal("hide");
    });

    /* AJAX */
    /* Ajax notifications */

    var eiAjaxCounter;

    window.eiB2BProAjax = function (type, message) {
        type = typeof type !== 'undefined' ? type : "clear";
        message = typeof message !== 'undefined' ? message : eiB2BProGlobal.i18n.wait;
        var an = jQuery('#eib2bpro-Ajax_Notification');

        an.stop();

        if (message) {
            an.find('.eib2bpro-Text').text(message);

        }

        if ('clear' === type) {

            clearTimeout(eiAjaxCounter);
            an.find('.eib2bpro-Ajax_Notification_Container').removeClass().addClass('eib2bpro-Ajax_Notification_Container badge badge-pill badge-warning');
            an.find('.eib2bpro-Loading').removeClass().addClass('eib2bpro-Loading');
            an.find('.eib2bpro-Error').removeClass().addClass('eib2bpro-Error d-none');
            an.find('.eib2bpro-OK').removeClass().addClass('eib2bpro-OK  d-none');
        }

        if ('success' === type) {
            an.find('.eib2bpro-Ajax_Notification_Container').removeClass().addClass('eib2bpro-Ajax_Notification_Container badge badge-pill badge-success');
            an.find('.eib2bpro-Loading').removeClass().addClass('eib2bpro-Loading d-none');
            an.find('.eib2bpro-Error').removeClass().addClass('eib2bpro-Error d-none');
            an.find('.eib2bpro-OK').removeClass().addClass('eib2bpro-OK');
            clearTimeout(eiAjaxCounter);
            eiAjaxCounter = setTimeout(function () {
                eiB2BProAjax('hide', message);
            }, 4000);
        }

        if ('error' === type) {
            an.find('.eib2bpro-Ajax_Notification_Container').removeClass().addClass('eib2bpro-Ajax_Notification_Container badge badge-pill badge-danger');
            an.find('.eib2bpro-Loading').removeClass().addClass('eib2bpro-Loading d-none');
            an.find('.eib2bpro-Error').removeClass().addClass('eib2bpro-Error');
            an.find('.eib2bpro-OK').removeClass().addClass('eib2bpro-OK d-none');
            clearTimeout(eiAjaxCounter);

            eiAjaxCounter = setTimeout(function () {
                eiB2BProAjax('hide');
            }, 20000);
        }

        if ('hide' === type) {
            an.removeClass().addClass('animated slideOutDown');
            clearTimeout(eiAjaxCounter);

            eiAjaxCounter = setTimeout(function () {
                an.find('.eib2bpro-Ajax_Notification_Container').removeClass().addClass('eib2bpro-Ajax_Notification_Container badge badge-pill badge-warning');
                an.find('.eib2bpro-Loading').removeClass().addClass('eib2bpro-Loading');
                an.find('.eib2bpro-Error').removeClass().addClass('eib2bpro-Error d-none');
                an.find('.eib2bpro-OK').removeClass().addClass('eib2bpro-OK  d-none');
            }, 800);
        } else {
            an.removeClass('d-none').addClass('animated slideInUp');
        }
    };


    /* Submenu position */
    jQuery(document).on('mouseover', '.eib2bpro-MainMenuV > li, .eib2bpro-MainMenuV li ul li', function () {

        var $document = jQuery(document),
            $window = jQuery(window),
            $body = jQuery(document.body),
            $wpwrap = jQuery('#wpwrap'),
            $menuItem = jQuery(this),
            bottomOffset, pageHeight, adjustment, theFold, menutop, wintop, maxtop,
            $submenu = $menuItem.find('> .eib2bpro-header-submenu');

        menutop = $menuItem.offset().top;
        wintop = $window.scrollTop();
        maxtop = menutop - wintop - 30; // max = make the top of the sub almost touch admin bar

        bottomOffset = menutop + $submenu.height() + 1; // Bottom offset of the menu
        pageHeight = $wpwrap.height(); // Height of the entire page
        adjustment = 60 + bottomOffset - pageHeight;
        theFold = $window.height() + wintop - 10; // The fold

        if (theFold < (bottomOffset - adjustment)) {
            adjustment = bottomOffset - theFold;
        }

        if (adjustment > maxtop) {
            adjustment = maxtop;
        }

        if (adjustment > 1) {
            $submenu.css('margin-top', '-' + adjustment + 'px');
        } else {
            $submenu.css('margin-top', '');
        }

    });

    /* Overflow Menu */

    window.onresize = navigationResize;
    navigationResize();

    function navigationResize() {
        if (self === top) {
            jQuery('.eib2bpro-MainMenuH li.more').before(jQuery('#overflow > li'));
            jQuery('.eib2bpro-MainMenuV li.more').before(jQuery('#overflow > li'));


            var $navItemMore = jQuery('.eib2bpro-MainMenuH > li.more'),
                $navItems = jQuery('.eib2bpro-MainMenuH > li:not(.more)'),
                navItemMoreWidth = $navItemMore.outerWidth(),
                navItemWidth = $navItemMore.outerWidth(),
                windowWidth = jQuery('.eib2bpro-MainMenuH').width(),
                navItemMoreLeft, offset, navOverflowWidth;

            var $navItemMoreV = jQuery('.eib2bpro-MainMenuV > li.more'),
                $navItemsV = jQuery('.eib2bpro-MainMenuV > li:not(.more)'),
                navItemMoreWidthV = $navItemMoreV.outerHeight(),
                navItemWidthV = $navItemMoreV.outerHeight(),
                windowWidthV = jQuery('.eib2bpro-MainMenuV').height(),
                navItemMoreLeftV, offsetV, navOverflowWidthV;

            $navItems.each(function () {
                navItemWidth += jQuery(this).outerWidth(true);
            });

            $navItemsV.each(function () {
                navItemWidthV += jQuery(this).outerHeight(true);
            });

            if (navItemWidthV > 0) {
                navItemWidthV += 30;
            }

            if (navItemWidth > windowWidth) {
                $navItemMore.show();
            } else {
                $navItemMore.hide();
            }

            if (navItemWidthV > windowWidthV) {
                $navItemMoreV.show();
            } else {
                $navItemMoreV.hide();
            }

            var i = 0;
            windowWidth -= 50;
            while (navItemWidth > windowWidth && i < 30) {
                navItemWidth -= $navItems.last().outerWidth(true);

                $navItems.last().prependTo('#overflow');
                $navItems.splice(-1, 1);
                ++i;
            }

            while (navItemWidthV > windowWidthV) {

                navItemWidthV -= $navItemsV.last().outerHeight();
                $navItemsV.last().prependTo('#overflow');
                $navItemsV.splice(-1, 1);
            }

            jQuery('#overflow').addClass('eib2bpro-header-submenu');
            jQuery('.eib2bpro-MainMenu').removeClass('overflow-hidden');
            jQuery('.eib2bpro-MainMenu .more').removeClass('d-none');

            if (jQuery('#overflow').height() > (jQuery(window).height() - 100)) {
                jQuery('#overflow').addClass('eib2bpro-more-double');
            }

            jQuery('.eib2bpro-MainMenu > ul > li').each(function () {
                if (jQuery(this).find('> ul').length > 0) {
                    if (true === window.isMobile) {
                        jQuery(this).find('> a').attr('href', '#');
                    } else {
                        jQuery(this).find('> a').attr('href', jQuery(this).find('> ul > li:first-child > a').attr('href'));
                    }
                }
            });

            jQuery('.eib2bpro-MainMenu > ul > li.more > ul > li').each(function () {
                if (jQuery(this).find('> ul').length > 0) {
                    if (true === window.isMobile) {
                        jQuery(this).find('> a').attr('href', '#');
                    } else {
                        jQuery(this).find('> a').attr('href', jQuery(this).find('> ul > li:first-child > a').attr('href'));
                    }
                }
            });
        }
    }

    $('body').on('click', '.eib2bpro-app-save-button, .eib2bpro-app-save-button-hidden', function (e) {
        e.preventDefault();
        if ($('.carousel-groups').length > 0) {
            var active_tab = $('.carousel-groups .active').attr('data-slide-to');
            $('.eib2bpro-app-current-tab').val(active_tab);
        }
        eiB2BProAppSave(this);
    });

    function eiB2BProAppSave(th) {

        var t = $(th);
        var form = $(th).closest('form');
        var url = form.attr('action');

        if (t.data('confirm')) {
            if (!confirm(t.data('confirm'))) {
                return false;
            }
        }

        var data = {
            form: form.serializeArray()
        };
        var previous = t.html();

        t.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span class="eib2bpro-os-saving pl-2"> ' + eiB2BProGlobal.i18n.saving + '</span>');

        if ($('.eib2bpro-app-save-button').length === 0) {
            eiB2BProAjax();
        }

        $.ajax({
            url: eiB2BProGlobal.ajax_url,
            type: "POST",
            dataType: 'json',
            data: form.serialize(),
            success: function (data) {
                if (1 === data.status) {

                    window.after = data.after;

                    if (false === window.sidebar_opened) {

                    } else {
                        if ($('.eib2bpro-app-save-button').length === 0) {
                            eiB2BProAjax('success', data.message);
                        }
                    }

                    $.each(data.after, function (k, v) {

                        if ('close' === k) {
                            window.parent.panel.slideReveal("hide");
                        }

                        if ('close2' === k) {
                            window.panel2.slideReveal("hide");
                        }

                        if ('refresh_window' === k) {
                            window.parent.location.reload(true);
                        }

                        if ('refresh_iframe_window' === k) {
                            window.location.reload(true);
                        }

                        if ('redirect' === k) {
                            window.parent.location = v;
                        }

                        if ('redirect_iframe' === k) {
                            window.location = v;
                        }

                        if ('html' === k) {
                            $(v.container, parent.document).html(v.html);
                        }

                        if ('val' === k) {
                            $(v.input).val(v.val);
                        }

                        if ('reload_widgets' === k) {
                            window.parent.reload_widgets();
                        }

                        if ('addClass' === k) {
                            $(v.container).addClass(v.class);
                        }
                        if ('removeClass' === k) {
                            $(v.container).removeClass(v.class);
                        }

                        if ('func' === k) {
                            $.each(v, function (kk, vv) {
                                window.parent[vv]();
                            });
                        }

                    });

                    t.removeClass('btn-danger').addClass('btn-success').html(eiB2BProGlobal.i18n.saved);

                } else {
                    eiB2BProAjax();
                    eiB2BProAjax('error', data.message);
                    alert(data.message);
                    t.html(eiB2BProGlobal.i18n.save);
                }
                t.find('.spinner-border, .eib2bpro-os-saving').remove();
                setTimeout(function () {
                    t.html(previous).removeClass('btn-success').addClass('btn-danger');
                }, 2500);
            }
        });
    };

    window.detectHash = function (url) {

        if ($.inArray(eiB2BProGlobal.current_app, ['dashboard', 'orders', 'customers', 'products', 'coupons', 'comments']) > -1) {

            var hash = window.location.hash.substr(1);

            if (hash && hash !== '-') {
                window.panel.slideReveal("show");
                jQuery("#inbrowser").attr("src", url.replace(/HASH/, hash));
                jQuery('#inbrowser').on("load", function () {
                    jQuery("#inbrowser--loading").removeClass('d-flex').addClass('d-none');
                    jQuery(".eib2bpro-Trig_Close").removeClass('d-none');
                    jQuery("#inbrowser").show();
                });
            }
        }
    };

    /* Get params from current url */

    window.getUrlVars = function () {
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
            vars[key] = value;
        });
        return vars;
    };

    window.getUrlParam = function (parameter, defaultvalue) {
        var urlparameter = defaultvalue;
        if (window.location.href.indexOf(parameter) > -1) {
            urlparameter = getUrlVars()[parameter].replace(/#-/, '');
        }
        return urlparameter;
    };


    /* Search for segments */

    window.searchMe = function (extra) {

        extra = typeof extra !== 'undefined' ? extra : "";

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: getUrlParam('app', ''),
            do: 'search',
            q: jQuery(".eib2bpro-Search_Input").val(),
            status: jQuery(".eib2bpro-Input_Status").val(),
            extra: extra
        }, function (r) {
            jQuery('.eib2bpro-Search_Input').removeClass('loading');
            jQuery(".eib2bpro-Container").html(r).addClass('eib2bpro-Ajax_Response');
        });
    };

    jQuery(".eib2bpro-Left_Search, .eib2bpro-global-search-button").on("click", function () {

        window.mobilemenu.slideReveal('hide');

        jQuery("body").css({
            overflow: 'hidden'
        });

        jQuery(".eib2bpro-search-1--overlay").addClass('eib2bpro-search-1--overlay-show');

        setTimeout(function () {
            jQuery(".eib2bpro-search-input").focus();
        }, 500);


    });

    jQuery(".eib2bpro-Search_Button").on("click", function () {
        jQuery('.eib2bpro-Searching').toggleClass('closed');
        jQuery('.eib2bpro-Search_Input').focus();
    });

    jQuery(".eib2bpro-Search_Input").doWithDelay("keyup", function (e) {
        jQuery(this).addClass('loading');
        window.searchMe(jQuery(this).data('status'));
    }, 200);

    jQuery(".eib2bpro-Select_All").on("click", function () {
        var doit = true;

        if ('select' === jQuery(this).attr('data-state')) {
            jQuery(this).attr('data-state', 'unselect');
        } else {
            doit = false;
            jQuery(this).attr('data-state', 'select');
        }

        jQuery('.eib2bpro-Checkbox').each(function () {
            jQuery(this).prop('checked', doit);
        });
    });

    jQuery('body').on('click', '.eib2bpro-Item-Ajax.collapsed', function (e) {
        var t = jQuery(this);

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: t.data('app'),
            do: t.data('do'),
            id: t.data('id'),
        }, function (r) {
            jQuery('.eib2bpro-Item-Ajax-Container', t).html(r);

        });
    });
    /* List 1 - Checbox */

    jQuery('.eib2bpro-List_M1 .eib2bpro-Checkbox').on("click", function () {
        jQuery('.eib2bpro-Checkbox_Hidden').show();
        jQuery(".eib2bpro-Item.btnA").addClass('collapsed').attr('aria-expanded', false);
        jQuery(".eib2bpro-Item.btnA .collapse").removeClass('show');
        jQuery('.eib2bpro-Checkbox_Hidden').show();
    });


    /* Desktop App */

    jQuery('body').on('click', '.eib2bpro-Desktop_Control', function (e) {
        e.preventDefault();
        jQuery('title').text(jQuery('title').text() + 'energy-asterisk-' + jQuery(this).data('do'));
    });

    /* Multi Lang */

    jQuery('body').on('click', '.eib2bpro_multi_lang', function (e) {
        $('.eib2bpro_multi_lang').removeClass('eib2bpro-selected');
        $(this).addClass('eib2bpro-selected');
        $('.eib2bpro-multi-lang-input').hide();
        $('.eib2bpro-multi-lang-input-' + $(this).data('lang')).show();
    });

    jQuery('body').on('click', '.eib2bpro-wpml-selector a', function (e) {
        $('.eib2bpro-wpml-selector a').css({
            'opacity': '0.3',
            'pointer-events': 'none'
        });
    });

    /* Search */
    jQuery(".eib2bpro-Left_Search, .dashicons-search").on("click", function () {

        jQuery("body").css({
            overflow: 'hidden'
        });

        jQuery(".eib2bpro-search-1--overlay").addClass('eib2bpro-search-1--overlay-show');

        setTimeout(function () {
            jQuery(".eib2bpro-search-input").focus();
        }, 500);


    });

    jQuery("#eib2bpro-search-1--close-button").on("click", function () {
        jQuery("body").css({
            overflow: 'auto'
        });
        jQuery(".eib2bpro-search-1--overlay").removeClass('eib2bpro-search-1--overlay-show');
        jQuery(".eib2bpro-search-input").val('');
    });


    jQuery(".eib2bpro-search-input").on("keyup", function (e) {
        jQuery(".eib2bpro-Search_Container_Searching").removeClass("hidden");
        jQuery(".eib2bpro-Search_Complete").removeClass("eib2bpro-Search_Complete");

        if (1 === jQuery(this).data('close-on-empty') && '' === jQuery(this).val()) {
            jQuery(".eib2bpro-search-1--overlay").removeClass('eib2bpro-search-1--overlay-show');
        }

    });

    var xhr = null;

    jQuery(".eib2bpro-search-input").doWithDelay("keyup", function (e) {

        if (!jQuery(".eib2bpro-search-1--overlay").hasClass('eib2bpro-search-1--overlay-show')) {
            jQuery("body").css({
                overflow: 'hidden'
            });

            jQuery(".eib2bpro-search-1--overlay").addClass('eib2bpro-search-1--overlay-show');
        }
        if (1 === jQuery(this).data('close-on-empty') && '' === jQuery(this).val()) {
            jQuery("body").css({
                overflow: 'auto'
            });

            jQuery(".eib2bpro-search-1--overlay").removeClass('eib2bpro-search-1--overlay-show');
        }


        if (xhr !== null) {
            xhr.abort();
            xhr = null;
        }

        xhr = jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'core',
            do: 'search',
            q: jQuery(this).val(),
            mode: 98,
            status: ''
        }, function (r) {

            jQuery(".eib2bpro-Search_Container").html(r);
        });
    }, 500);


    /* 2: Notifications etc */


    function notifyMe(title, body, link) {

        title = typeof title !== 'undefined' ? title : "";
        body = typeof body !== 'undefined' ? body : "";
        link = typeof link !== 'undefined' ? link : "";


        if (!("Notification" in window)) {
            
        } else if (Notification.permission === "granted") {
            var notification = new Notification(title, {
                body: body
            });
            notification.onclick = function (e) {
                e.preventDefault();
                window.focus();
            };
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission(function (permission) {
                if (permission === "granted") {
                    var notification = new Notification(title, {
                        body: body
                    });
                    notification.onclick = function (e) {
                        e.preventDefault();
                        window.focus();
                    };
                }
            });
        }
    }

    if ('Notification' in window) {
        Notification.requestPermission();
    }

    function spawnNotification(theBody, theIcon, theTitle) {
        var options = {
            body: theBody,
            icon: theIcon
        };
        var n = new Notification(theTitle, options);
    }

    function checkNotifications() {

        if (window.self !== window.top) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: eiB2BProGlobal.ajax_url,
            dataType: 'json',
            data: {
                _wpnonce: jQuery('input[name=_wpnonce]').val(),
                _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
                asnonce: eiB2BProGlobal.asnonce,
                action: 'eib2bpro',
                app: 'core',
                do: 'notifications',
            },
            cache: false,
            headers: {
                'cache-control': 'no-cache'
            },
            success: function (response) {

                $.each(response, function (i, item) {
                    switch (item.type) {

                        case 'system':
                            lasttime = item.lasttime;
                            break;

                        case 'top':
                            $.each(item.data, function (i2, item2) {
                                $('.eib2bpro-top-data-' + i2).html(item2);
                            });
                            break;
                        case 'alerts':
                            $.each(item.data, function (i3, item3) {
                                notifyMe(item3.title, item3.body, item3.link);
                            });
                            break;
                        case 'sounds':
                            $.each(item.data, function (i4, item4) {
                                var audio = new Audio(item4);
                                audio.preload = 'auto';
                                audio.volume = 0.2;
                                audio.play();
                            });
                            break;
                        case 'title':
                            if (item.data !== $("title").text()) {
                                $("title").text(item.data);
                            }
                            break;
                    }
                })
            }
        });
    }
    if (eiB2BProGlobal.refresh > 9000) {
        if (window.self === window.top) {
            setInterval(function () {
                    checkNotifications();
                },
                eiB2BProGlobal.refresh);
        }
    }

    // Instant search
    if (1 === eiB2BProGlobal.enable.ui_instant_search) {
        if ($.inArray(eiB2BProGlobal.current_app, ['dashboard', 'orders', 'customers', 'products', 'coupons', 'comments']) > -1) {
            $.key('', function (e) {
                if (window.sidebar_opened) {
                    return false;
                }
                if (
                    e.target.type === 'text' ||
                    e.target.type === 'textarea' ||
                    e.metaKey === true || e.which === 9 || e.which === 16 || e.which === 17 || e.which === 18 || e.which === 27 || e.which === 37 || e.which === 38 || e.which === 39 || e.which === 40 || e.which === 91 || e.which === 93)
                    return false;

                if (e.target.type === 'search') {
                    return false;
                }

                if ('dashboard' === eiB2BProGlobal.current_app) {
                    $('.eib2bpro-search-input').focus();
                } else {
                    $('.eib2bpro-Searching').removeClass('closed');
                    $('.eib2bpro-Search_Input').focus();
                }
                $('html, body').animate({
                    scrollTop: '0'
                }, 300);
            });
        }
    }

    // Hotkeys
    // Hotkey for admin bar

    jQuery(document).on('keydown', function (event) {

        var excludeInputs = [
            "text", "password", "number", "email", "url", "range", "date", "month", "week", "time", "datetime",
            "datetime-local", "search", "color", "tel", "textarea"
        ];

        if (this !== event.target && (event.target.isContentEditable || jQuery.inArray(event.target.type, excludeInputs) > -1)) {
            return;
        }

        if (event.which === 77 && event.ctrlKey) {
            if (jQuery('#wpadminbar').is(":visible")) {
                jQuery('#wpadminbar').hide();
            } else {
                jQuery('#wpadminbar').attr("style", "height:50px; opacity:0;padding-top:10px; display: inline !important").animate({
                    opacity: 1
                });
            }
            event.preventDefault();
            return false;
        }
    });

});