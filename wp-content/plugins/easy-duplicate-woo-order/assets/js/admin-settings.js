jQuery(document).ready(function($) {
            function toggleChildOptions() {
                if ($('#wizbee_duplicate_order_copy_old_price').is(':checked')) {
                    $('.coupons-options').closest('tr').show();
                } else {
                    $('.coupons-options').prop('checked', false).closest('tr').hide();
                }
            }
            toggleChildOptions();
            $('#wizbee_duplicate_order_copy_old_price').change(function() {
                toggleChildOptions();
            });
        });