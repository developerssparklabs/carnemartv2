jQuery(document).ready(($) => {
  $.noConflict();
  $("#wcmlim-change-sl-select").on("change", function (e) {
    const selected = $(this).find("option:selected").val();
    if (selected == -1) {
      $("#wcmlim_get_direction_for_location").hide();
    }
  });
  $("#wcmlim-change-lcselect").on("change", function (e) {
    if ($("#wcmlim_get_direction_for_location").length > 0) {
      $("#wcmlim_get_direction_for_location").hide();
    }
    var value = $("#wcmlim-change-lcselect")
      .find(":selected")
      .attr("data-lc-loc");
    var getdirection_link = "https://www.google.com/maps?saddr=&daddr=" + value;
    $(
      '<a id="wcmlim_get_direction_for_location" target="_blank" href="' +
        getdirection_link +
        '">Get Direction</a>'
    ).insertAfter(" .Wcmlim_prefloc_sel");
  });
});
