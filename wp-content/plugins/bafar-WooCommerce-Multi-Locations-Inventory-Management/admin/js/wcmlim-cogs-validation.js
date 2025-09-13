jQuery(document).ready(($) => {
    $(".wcmlim_cogs_at").blur(function ($) {
        var wcmlim_selectedid = this.id;
        var wcmlim_selectedvalue = this.value;
        const wcmlim_selectedidarray = wcmlim_selectedid.split("_");
        var wcmlim_selectedpid = wcmlim_selectedidarray[2];
        var wcmlim_selectedlid = wcmlim_selectedidarray[5];
        $regular_pr = $("#wcmlim_product_" + wcmlim_selectedpid + "_regular_price_at_" + wcmlim_selectedlid).val();
        $sale_pr = $("#wcmlim_product_" + wcmlim_selectedpid + "_sale_price_at_" + wcmlim_selectedlid).val();
        $cogs_pr = $("#wcmlim_product_" + wcmlim_selectedpid + "_cogs_at_" + wcmlim_selectedlid).val();
        $regular_pr = parseFloat($regular_pr);
        $sale_pr = parseFloat($sale_pr);
        $cogs_pr = parseFloat($cogs_pr);
        if ($cogs_pr > $regular_pr && $sale_pr == "") {
            alertify.set("notifier", "position", "bottom");
            alertify.error("please enter price less than regular price");
        } else {
            if ($cogs_pr > $sale_pr && $sale_pr != "") {
                alertify.error("please enter price less than sale price");
                $("#wcmlim_product_" + wcmlim_selectedpid + "_cogs_at_" + wcmlim_selectedlid).val("");
            }
        }
    });

    function check_cogs_validations(variationid, locationid) {
        var wcmlim_selectedpid = variationid;
        var wcmlim_selectedlid = locationid;
        $regular_pr = $("#wcmlim_variation_" + wcmlim_selectedpid + "_regular_price_at_" + wcmlim_selectedlid).val();
        $sale_pr = $("#wcmlim_variation_" + wcmlim_selectedpid + "_sale_price_at_" + wcmlim_selectedlid).val();
        $cogs_pr = $("#wcmlim_variation_" + wcmlim_selectedpid + "_cogs_at_" + wcmlim_selectedlid).val();
        $regular_pr = parseFloat($regular_pr);
        $sale_pr = parseFloat($sale_pr);
        $cogs_pr = parseFloat($cogs_pr);
        if ($cogs_pr > $regular_pr && $sale_pr == "") {
            alertify.set("notifier", "position", "bottom");
            alertify.error("please enter price less than regular price");
        } else {
            if ($cogs_pr > $sale_pr && $sale_pr != "") {
                alertify.error("please enter price less than sale price");
                $("#wcmlim_variation_" + wcmlim_selectedpid + "_cogs_at_" + wcmlim_selectedlid).val("");
            }
        }
    }
});
