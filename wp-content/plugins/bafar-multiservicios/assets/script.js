
/*jQuery(document).on('click', '.plus, .minus', function() {

    // Encuentra el input de cantidad más cercano
    var $qty = jQuery(this).siblings('.qty');
    var currentVal = parseFloat($qty.val());
    var max = parseFloat($qty.attr('max'));
    var min = parseFloat($qty.attr('min'));
    var step = parseFloat($qty.attr('step'));

    // Incrementa o decrementa el valor según el botón presionado
    if (jQuery(this).hasClass('plus')) {
        if (!max || currentVal < max) {
            $qty.val(currentVal + step).change();
        }
    } else {
        if (!min || currentVal > min) {
            $qty.val(currentVal - step).change();
        }
    }

    // Actualiza el valor de currentVal después del cambio
    currentVal = parseFloat($qty.val());

    let container = this.closest(".quantity");
    let stepInput = container.querySelector("input[id^='quantity_step_']");
    let idProducto = container.querySelector("input[id^='id_producto_']");
    let unidad = container.querySelector("input[id^='unidad_']").value;
    let stepValue = parseFloat(stepInput.value.replace('&quot;', ''));
    
    updateTotalWeight(); // Llamar a la función con el valor actualizado


    function updateTotalWeight() {
        var quantity = parseFloat(stepValue);
        var totalWeight = (quantity * currentVal).toFixed(2);

        if(unidad == "Pza." || unidad == "Pzas.") {
            if(totalWeight >= 1.5) {
                unidad = "Pzas.";
            } else {
                unidad = "Pza.";
            }
        } else if(unidad == "Kg." || unidad == "Kgs.") {
            if(totalWeight >= 1.5) {
                unidad = "Kgs.";
            } else {
                unidad = "Kg.";
            }
}   
      jQuery("#totalWeight_quantity_" + idProducto.value).text("Total a pedir " + totalWeight + " " + unidad );
    }
});
*/