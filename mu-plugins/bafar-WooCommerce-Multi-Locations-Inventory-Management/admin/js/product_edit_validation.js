
jQuery(document).ready(($) => {
   jQuery(".wcmlim_product_regular_price").change(function () {
      let locid = $(this).attr('loc-id');

      let proid = $(this).attr('pro-id');
      let current_sales_price = $("#wcmlim_product_" + proid + "_sale_price_at_" + locid).val();
      let current_regular_price = $("#wcmlim_product_" + proid + "_regular_price_at_" + locid).val();
      if (current_regular_price == '' && current_sales_price != '') {
         $("#wcmlim_product_" + proid + "_sale_price_at_" + locid).val('');
         $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);

      }
      else
         if (parseFloat(current_sales_price) > parseFloat(current_regular_price) && (current_regular_price != '')) {
            $("#wcmlim_product_" + proid + "_sale_price_at_" + locid).val('');
            $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);

         }
   });

   jQuery(".wcmlim_product_sale_price").change(function () {

      let locid = $(this).attr('loc-id');

      let proid = $(this).attr('pro-id');
      let current_sales_price = $("#wcmlim_product_" + proid + "_sale_price_at_" + locid).val();
      let current_regular_price = $("#wcmlim_product_" + proid + "_regular_price_at_" + locid).val();

      if (current_regular_price == '' && current_sales_price != '') {
         $("#wcmlim_product_" + proid + "_sale_price_at_" + locid).val('');
         $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);

      }
      else
         if (parseFloat(current_sales_price) > parseFloat(current_regular_price) && (current_regular_price != '')) {

            $("#wcmlim_product_" + proid + "_sale_price_at_" + locid).val('');
            $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);

         }
   });


   // For variable product


   $(document).on("keyup", '.wcmlim_variable_product_regular_price', function () {

      let locid = $(this).attr('loc-id');
      let proid = $(this).attr('pro-id');
      let current_sales_price = $("#wcmlim_variation_" + proid + "_sale_price_at_" + locid).val();
      let current_regular_price = $("#wcmlim_variation_" + proid + "_regular_price_at_" + locid).val();
      if (current_regular_price == '' && current_sales_price != '') {

         $("#wcmlim_variation_" + proid + "_sale_price_at_" + locid).val('');
         $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);
      }
      else
         if (parseFloat(current_sales_price) > parseFloat(current_regular_price) && (current_regular_price != '')) {
            $("#wcmlim_variation_" + proid + "_sale_price_at_" + locid).val('');
            $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);
         }
   });


   $(document).on("keyup", '.wcmlim_variable_product_sale_price', function () {
      let locid = $(this).attr('loc-id');
      let proid = $(this).attr('pro-id');
      let current_sales_price = $("#wcmlim_variation_" + proid + "_sale_price_at_" + locid).val();
      let current_regular_price = $("#wcmlim_variation_" + proid + "_regular_price_at_" + locid).val();

      if (current_regular_price == '' && current_sales_price != '') {
         $("#wcmlim_variation_" + proid + "_sale_price_at_" + locid).val('');
         $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);
      }
      else
         if (parseFloat(current_sales_price) > parseFloat(current_regular_price) && (current_regular_price != '')) {
            $("#wcmlim_variation_" + proid + "_sale_price_at_" + locid).val('');
            $(document.body).triggerHandler('wc_add_error_tip', [$(this), 'i18n_sale_less_than_regular_error']);
         }
   });

});