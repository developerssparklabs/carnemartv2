jQuery(document).ready(function ($) {
    // Evitar conflictos con otras bibliotecas
    $.noConflict();

    // Ocultar todas las opciones del selector de ubicación
    $(".wcmlim_storeloc #wcmlim-change-lc-select option").hide();

    // Obtener URL del AJAX desde objeto global
    const { ajaxurl } = multi_inventory;

    // Evento de cambio en el selector de tienda
    $("#wcmlim-change-sl-select, #wcmlim-change-sl-select option").on("change", function () {
        const selectedStore = $(this).find("option:selected").val();
        const selectedClass = $(this).find("option:selected").attr("class");

        // Deshabilitar elementos durante la carga
        $(".wcmlim-lc-select").prop("disabled", true);
        $("#wcmlim-change-lcselect").prop("disabled", true);
        $(".wcmlim-change-sl1").prop("disabled", true);

        // Si se seleccionó una tienda válida
        if (selectedStore != "-1") {
            $('#msgLoading').css('display', 'flex');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    selectedstoreValue: selectedStore,
                    action: "wcmlim_drop2_location"
                },
                dataType: "json",
                success: function (response) {
                    // Limpiar opciones actuales
                    $(".wcmlim-lc-select").empty();
                    $(".wcmlim_lcselect").empty();

                    // Validar que haya respuesta válida
                    if (JSON.parse(JSON.stringify(response))) {
                        // Agregar opción por defecto
                        const defaultOption = '<option value="-1" selected="selected">Seleccionar</option>';
                        $(".wcmlim-lc-select").prepend(defaultOption);
                        $("#wcmlim-change-lcselect").prepend(defaultOption);

                        // Recorrer cada ubicación recibida
                        $.each(response, function (index, item) {
                            const locationName = item.wcmlim_areaname || item.location_name;

                            const optionData = {
                                value: item.vkey,
                                text: locationName,
                                class: item.classname,
                                "data-lc-storeid": item.location_storeid,
                                "data-lc-name": locationName,
                                "data-lc-loc": item.location_slug,
                                "data-lc-term": item.term_id
                            };

                            // Crear opción para ambos select
                            const option1 = $("<option>", optionData);
                            const option2 = $("<option>", optionData);

                            // Marcar como seleccionada si corresponde
                            // if (item.selected == item.vkey) {
                            //     option1.attr("selected", "selected");
                            //     option2.attr("selected", "selected");
                            // }

                            // Agregar opciones a los selectores
                            $(".wcmlim-lc-select").append(option1);
                            $("#wcmlim-change-lcselect").append(option2);
                        });
                    }

                    // Rehabilitar selectores después de cargar
                    $(".wcmlim-lc-select").removeAttr("disabled");
                    $("#wcmlim-change-lcselect").removeAttr("disabled");
                    $(".wcmlim-change-sl1").removeAttr("disabled");
                    $("#wcmlim-change-sl-select").removeAttr("disabled");
                    $(this).removeAttr("disabled");
                    $(".wcmlim_changesl").removeAttr("disabled");

                    $('#msgLoading').css('display', 'none');
                },
                error: function (error) {
                    // console.log(error);
                    $('#msgLoading').css('display', 'none');
                }
            });
        }
    });
});
