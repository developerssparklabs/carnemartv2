jQuery(document).ready((o) => {
    o(".color_field, .map_shortcode_color_field").each(function () {
        o(this).wpColorPicker();
    }),
        o(".color_field, .map_shortcode_color_field").iris({
            change(c, e) {
                let t = o(this).attr("id");
                o(".wp-picker-open").css("background-color", o("#" + t).val());
                let l = o("#wcmlim_preview_stock_bgcolor").val();
                o(".Wcmlim_container").css("background-color", l);
                let i = o("#wcmlim_preview_stock_bordercolor").val();
                o(".Wcmlim_container").css("border-color", i);
                let s = o("#wcmlim_separator_linecolor").val();
                o(".Wcmlim_prefloc_box").css("border-color", s);
                let r = o("#wcmlim_txtcolor_stock_info").val();
                o(".Wcmlim_box_title").css("color", r);
                let a = o("#wcmlim_txtcolor_preferred_loc").val();
                o(".loc_dd").css("color", a);
                let m = o("#wcmlim_txtcolor_nearest_stock").val();
                o(".postcode-checker-title").css("color", m);
                let d = o("#wcmlim_oncheck_button_color").val();
                o("#submit_postcode_product").css("background-color", d);
                let n = o("#wcmlim_oncheck_button_text_color").val();
                o("#submit_postcode_product").css("color", n);
                let b = o("#wcmlim_soldout_button_color").val();
                o(".Wcmlim_over_stock").css("background-color", b);
                let h = o("#wcmlim_soldout_button_text_color").val();
                o(".Wcmlim_over_stock").css("color", h);
                let v = o("#wcmlim_instock_button_color").val();
                o(".Wcmlim_have_stock").css("background-color", v);
                let w = o("#wcmlim_instock_button_text_color").val();
                o(".Wcmlim_have_stock").css("color", w), o(".map_shortcode_color_field").val();
            },
        }),
        o("#wcmlim_preview_stock_borderoption").on("change", (c) => {
            let e = o("#wcmlim_preview_stock_borderoption").val();
            o(".Wcmlim_container").css("border-style", e);
        }),
        o("#wcmlim_preview_stock_border").on("change", (c) => {
            let e = o("#wcmlim_preview_stock_border").val();
            o(".Wcmlim_container").css("border-width", e);
        }),
        o("#wcmlim_preview_stock_borderradius").on("change", (c) => {
            let e = o("#wcmlim_preview_stock_borderradius").val();
            o(".Wcmlim_container").css("border-radius", e);
        }),
        o("#wcmlim_txt_stock_info").on("change", (c) => {
            let e = o("#wcmlim_txt_stock_info").val();
            o(".Wcmlim_box_title").text(e);
        }),
        o("#wcmlim_txt_preferred_location").on("change", (c) => {
            let e = o("#wcmlim_txt_preferred_location").val();
            o(".Wcmlim_sloc_label").text(e);
        }),
        o("#wcmlim_refbox_borderradius").on("change", (c) => {
            let e = o("#wcmlim_refbox_borderradius").val();
            o(".loc_dd.Wcmlim_prefloc_sel").css("border-radius", e);
        }),
        o("#wcmlim_txt_nearest_stock_loc").on("change", (c) => {
            let e = o("#wcmlim_txt_nearest_stock_loc").val();
            o(".postcode-checker-strong").text(e);
        }),
        o("#wcmlim_oncheck_button_text").on("change", (c) => {
            let e = o("#wcmlim_oncheck_button_text").val();
            o("#submit_postcode_product").text(e);
        }),
        o("#wcmlim_input_borderradius").on("change", (c) => {
            let e = o("#wcmlim_input_borderradius").val();
            o('.postcode-checker-div input[type="text"]').css("border-radius", e);
        }),
        o("#wcmlim_oncheck_borderradius").on("change", (c) => {
            let e = o("#wcmlim_oncheck_borderradius").val();
            o("#submit_postcode_product").css("border-radius", e);
        }),
        o("#wcmlim_soldout_button_text").on("change", (c) => {
            let e = o("#wcmlim_soldout_button_text").val();
            o(".Wcmlim_over_stock").text(e);
        }),
        o("#wcmlim_soldout_borderradius").on("change", (c) => {
            let e = o("#wcmlim_soldout_borderradius").val();
            o(".Wcmlim_over_stock").css("border-radius", e);
        }),
        o("#wcmlim_instock_button_text").on("change", (c) => {
            let e = o("#wcmlim_instock_button_text").val();
            o(".Wcmlim_have_stock").text(e);
        }),
        o("#wcmlim_instock_borderradius").on("change", (c) => {
            let e = o("#wcmlim_instock_borderradius").val();
            o(".Wcmlim_have_stock").css("border-radius", e);
        }),
        o("#wcmlim_preview_stock_borderoption").on("change", (c) => {
            let e = o("#wcmlim_preview_stock_borderoption").val();
            o(".Wcmlim_container").css("border-style", e);
        }),
        o("#stock_availabe").hide(),
        o(".Wcmlim_mssgerro").hide(),
        o("#not_stock_availabe").hide(),
        o("#losm").hide(),
      
        o("#globMsg").hide(),
        o(".postcode-location-distance").hide(),
        o(".class_post_code").val(""),
        o("#select_location").on("change", function (c) {
            let e = o(this).find("option:selected").val();
            "0" == e && (o(".Wcmlim_mssgerro").hide(), o("#not_stock_availabe").hide(), o("#losm").hide(), o("#globMsg").show(), o("#stock_availabe").show(), o(".postcode-location-distance").hide(), o(".class_post_code").val("")),
                "1" == e && (o("#not_stock_availabe").show(), o("#stock_availabe").hide(), o(".Wcmlim_mssgerro").hide(), o("#losm").show(), o("#globMsg").hide(), o(".postcode-location-distance").hide(), o(".class_post_code").val("")),
                "-1" == e && (o(".Wcmlim_mssgerro").hide(), o("#stock_availabe").hide(), o("#not_stock_availabe").hide(), o("#losm").hide(), o("#globMsg").hide(), o(".postcode-location-distance").hide(), o(".class_post_code").val(""));
        }),
        o(document).on("click", ".submit_postcode", (c) => {
            c.preventDefault();
            let e = o(".class_post_code").val();
            "location 1" == e
                ? (o("#not_stock_availabe").hide(), o("#stock_availabe").show(), o(".Wcmlim_mssgerro").hide(), o("#losm").hide(), o("#globMsg").show(), o(".postcode-location-distance").show())
                : "location 2" == e
                    ? (o("#not_stock_availabe").show(), o("#stock_availabe").hide(), o(".Wcmlim_mssgerro").hide(), o("#losm").show(), o("#globMsg").hide(), o(".postcode-location-distance").show())
                    : (o(".Wcmlim_mssgerro").show(), o("#not_stock_availabe").hide(), o("#stock_availabe").hide(), o("#losm").hide(), o("#globMsg").hide(), o(".postcode-location-distance").hide());
        });
});
