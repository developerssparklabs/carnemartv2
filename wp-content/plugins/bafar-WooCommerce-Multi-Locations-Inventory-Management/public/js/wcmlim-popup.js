!(function (e) {
  var t = multi_inventory_popup.show_in_popup,
    s = multi_inventory_popup.force_to_select;
  e(".set-def-store-popup-btn").click(function (s) {
    s.preventDefault(),
      (function s() {
        switch (t) {
          case "select":
            e(
              "#set-def-store .rlist_location, #set-def-store .postcode-checker"
            ).hide(),
              e("#set-def-store #wcmlim-change-lc-select").is(":visible") ||
                (e("#set-def-store #wcmlim-change-lc-select").removeAttr(
                  "style"
                ),
                jQuery("#set-def-store #wcmlim-change-lc-select").css(
                  "display",
                  "block"
                ));
            break;
          case "input":
            e("#set-def-store .rlist_location").hide(),
              e("#set-def-store .wcmlim_sel_location").hide(),
              e("#set-def-store .postcode-checker").show();
            break;
          case "list":
            console.log("in list"),
              e(
                "#set-def-store .postcode-checker, #wcmlim-change-lc-select"
              ).hide(),
              e("#set-def-store .rlist_location").show();
        }
      })();
  }),
    e(".set-def-store-popup-btn").magnificPopup({
      type: "inline",
      fixedContentPos: !1,
      fixedBgPos: !0,
      overflowY: "auto",
      closeBtnInside: !0,
      closeOnBgClick: !1,
      enableEscapeKey: !1,
      preloader: !1,
      midClick: !0,
      removalDelay: 300,
    });
  var o = (function e(t) {
    let s = document.cookie,
      o = `${t}=`,
      c = s.indexOf(`; ${o}`);
    if (-1 == c) {
      if (0 != (c = s.indexOf(o))) return null;
    } else {
      c += 2;
      var l = document.cookie.indexOf(";", c);
      -1 == l && (l = s.length);
    }
    return decodeURI(s.substring(c + o.length, l));
  })("wcmlim_selected_location");
  "on" == s &&
    (null == o || "-1" == o) &&
    (jQuery(".mfp-close").hide(), e(".set-def-store-popup-btn").click());
})(jQuery);
