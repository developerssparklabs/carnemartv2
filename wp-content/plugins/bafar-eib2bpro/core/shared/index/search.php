<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
} ?>
<script>
    jQuery(document).ready(function() {
        "use strict";

        jQuery.post("<?php echo admin_url("admin-ajax.php") ?>", {
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'customers',
            do: 'search',
            q: '<?php echo esc_attr($term) ?>',
            mode: 98,
            status: ''
        }, function(r) {

            jQuery('.eib2bpro-Search_Container_Searching').hide();
            if (-1 < r.indexOf('eib2bpro-EmptyTable')) {
                jQuery('.eib2bpro-Search_Container_No').addClass('eib2bpro-No3');
                jQuery(".eib2bpro-Search_Customers").addClass("eib2bpro-Search_Complete").html('');
            } else {
                jQuery('.eib2bpro-Search_Container_No').removeClass('eib2bpro-No3');
                jQuery(".eib2bpro-Search_Customers").addClass("eib2bpro-Search_Complete").html(r);
            }
        });



        jQuery.post("<?php echo admin_url("admin-ajax.php") ?>", {
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'products',
            do: 'search',
            q: '<?php echo esc_attr($term) ?>',
            mode: 98,
            status: ''
        }, function(r) {

            jQuery('.eib2bpro-Search_Container_Searching').hide();

            if ('\n' === r) {
                jQuery('.eib2bpro-Search_Container_No').addClass('eib2bpro-No2');
            } else {
                jQuery('.eib2bpro-Search_Container_No').removeClass('eib2bpro-No2');
            }
            jQuery(".eib2bpro-Search_Products").addClass("eib2bpro-Search_Complete").html(r);
        });

        jQuery.post("<?php echo admin_url("admin-ajax.php") ?>", {
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'orders',
            do: 'search',
            q: '<?php echo esc_attr($term) ?>',
            mode: 98,
            status: ''
        }, function(r) {

            jQuery('.eib2bpro-Search_Container_Searching').hide();

            if (-1 < r.indexOf('eib2bpro-EmptyTable')) {
                jQuery('.eib2bpro-Search_Container_No').addClass('eib2bpro-No1');
                jQuery(".eib2bpro-Search_Orders").addClass("eib2bpro-Search_Complete").html('');
            } else {
                jQuery('.eib2bpro-Search_Container_No').removeClass('eib2bpro-No1');
                jQuery(".eib2bpro-Search_Orders").addClass("eib2bpro-Search_Complete").html(r);
            }
        });

    });
</script>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>