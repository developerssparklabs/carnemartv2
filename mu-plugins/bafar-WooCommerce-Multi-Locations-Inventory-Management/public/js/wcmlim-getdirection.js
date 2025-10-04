jQuery(document).ready(function ($) {
  $.noConflict();

  $("#select_location").on("change", function (e) {
    let selectedValue = $(this).val();
    if (selectedValue === "-1") {
      $("#wcmlim_get_direction_for_location").hide();
    } else {
      $("#wcmlim_get_direction_for_location").remove();

      let encodedAddress = $(this).find(":selected").attr("data-lc-address");
      let decodedAddress = atob(encodedAddress);

      $(
        '<a id="wcmlim_get_direction_for_location" target="_blank" href="https://www.google.com/maps?saddr=&daddr=' +
          decodedAddress +
          '">Get Direction</a>'
      ).insertAfter("#globMsg");
    }
  });

  $("select#select_location").change();
});
