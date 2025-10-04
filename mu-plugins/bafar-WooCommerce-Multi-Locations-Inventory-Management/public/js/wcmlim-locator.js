jQuery(document).ready(function (t) {
  t.noConflict(), t(".wcmlim_storeloc  #wcmlim-change-lc-select option").hide();
  let { ajaxurl: e } = multi_inventory;
  t("#wcmlim-change-sl-select, #wcmlim-change-sl-select option").on(
    "change",
    function () {
      let l = t(this).find("option:selected").val();
      t(this).find("option:selected").attr("class"),
        t(".wcmlim-lc-select").prop("disabled", !0),
        t("#wcmlim-change-lcselect").prop("disabled", !0),
        t(".wcmlim-change-sl1").prop("disabled", !0),
        t.ajax({
          type: "POST",
          url: e,
          data: { selectedstoreValue: l, action: "wcmlim_drop2_location" },
          dataType: "json",
          success(e) {
            t(".wcmlim-lc-select").empty(), t(".wcmlim_lcselect").empty();
            var l = JSON.parse(JSON.stringify(e));
            l &&
              (Object.keys(l).length,
              t(".wcmlim-lc-select").prepend(
                '<option value="-1" selected="selected" >Seleccionar</option>'
              ),
              t("#wcmlim-change-lcselect").prepend(
                '<option value="-1" selected="selected">Seleccionar</option>'
              ),
              t.each(e, function (e, l) {
                var a = l.wcmlim_areaname;
                (null == a || "" == a) && (a = l.location_name),
                  l.selected == l.vkey
                    ? (t("<option></option>")
                        .attr("value", l.vkey)
                        .text(a)
                        .attr("class", l.classname)
                        .attr("selected", "selected")
                        .attr("data-lc-storeid", l.location_storeid)
                        .attr("data-lc-name", a)
                        .attr("data-lc-loc", l.location_slug)
                        .attr("data-lc-term", l.term_id)
                        .appendTo(".wcmlim-lc-select"),
                      t("<option></option>")
                        .attr("value", l.vkey)
                        .text(a)
                        .attr("class", l.classname)
                        .attr("selected", "selected")
                        .attr("data-lc-storeid", l.location_storeid)
                        .attr("data-lc-name", a)
                        .attr("data-lc-loc", l.location_slug)
                        .attr("data-lc-term", l.term_id)
                        .appendTo("#wcmlim-change-lcselect"))
                    : (t("<option></option>")
                        .attr("value", l.vkey)
                        .text(a)
                        .attr("class", l.classname)
                        .attr("data-lc-storeid", l.location_storeid)
                        .attr("data-lc-name", a)
                        .attr("data-lc-loc", l.location_slug)
                        .attr("data-lc-term", l.term_id)
                        .appendTo(".wcmlim-lc-select"),
                      t("<option></option>")
                        .attr("value", l.vkey)
                        .text(a)
                        .attr("class", l.classname)
                        .attr("data-lc-storeid", l.location_storeid)
                        .attr("data-lc-name", a)
                        .attr("data-lc-loc", l.location_slug)
                        .attr("data-lc-term", l.term_id)
                        .appendTo("#wcmlim-change-lcselect"));
              })),
              t(".wcmlim-lc-select").removeAttr("disabled"),
              t("#wcmlim-change-lcselect").removeAttr("disabled"),
              t(".wcmlim-change-sl1").removeAttr("disabled"),
              t("#wcmlim-change-sl-select").removeAttr("disabled"),
              t(this).removeAttr("disabled"),
              t(".wcmlim_changesl").removeAttr("disabled");
          },
          error(t) {
//console.log(t);
          },
        });
    }
  );
});
