(function($) {
    'use strict';
    
    $(document).ready(function () {
        
        
        /**
         * For price Multiply, based on quantity
         * 
         * comment added @2.0.7.3
         * 
         * Fixed by Fazle Bari @2.0.8.2
         */
        // $( document.body ).on( 'woocommerce_variation_select_change','.variations_form', function(aaa,bbb) {
        //     var qtyBox = $('body.single-product input.input-text.qty.text');
        //     var qty = qtyBox.val();
        //             qty = parseFloat(qty);
    
        //         qtyBox.closest('div.product').find('.wcmmq-price-wrapper').each(function(){
        //         var targetDataElement = $(this).find('.wcmmq-unformatted-price');
        //         if( targetDataElement.length < 1 ){
        //             return;
        //         }

        //         var price_rate = targetDataElement.data('price');
        //         price_rate = parseFloat(price_rate);
        //         var decimal = targetDataElement.data('decimal');
        //         decimal = parseInt(decimal);
        //         var result = qty * price_rate;
        //         result = parseFloat(result);
        //         result = Math.abs(result).toFixed(decimal);

        //         var targetElement = targetDataElement.find('span.woocommerce-Price-amount.amount');
        //         var data = targetElement.html();
        //         var default_decimal_separator = '.';
        //         if(typeof WCMMQ_DATA.default_decimal_separator !== 'undefined' ){
        //             default_decimal_separator = WCMMQ_DATA.default_decimal_separator;
        //         }
        //         var final_result = data.replace(/[0-9,.]+/,result);
        //         var afterChangeFinalResult = final_result.replace(/[,.]+/,default_decimal_separator);
        //         targetElement.html(afterChangeFinalResult);
        //     });
 
        // });
        $(document.body).on('change','input.input-text.qty.text',function(){

                var qty = $(this).val();
                    qty = parseFloat(qty);
    
                $(this).closest('div.product').find('.wcmmq-price-wrapper').each(function(){
                var targetDataElement = $(this).find('.wcmmq-unformatted-price');
                if( targetDataElement.length < 1 ){
                    return;
                }

                var price_rate = targetDataElement.data('price');
                price_rate = parseFloat(price_rate);
                var decimal = targetDataElement.data('decimal');
                decimal = parseInt(decimal);
                var result = qty * price_rate;
                result = parseFloat(result);
                result = Math.abs(result).toFixed(decimal);

                var targetElement = targetDataElement.find('span.woocommerce-Price-amount.amount');
                var data = targetElement.html();
                var default_decimal_separator = '.';
                if(typeof WCMMQ_DATA.default_decimal_separator !== 'undefined' ){
                    default_decimal_separator = WCMMQ_DATA.default_decimal_separator;
                }
                var final_result = data.replace(/[0-9,.]+/,result);
                var afterChangeFinalResult = final_result.replace(/[,.]+/,default_decimal_separator);
                targetElement.html(afterChangeFinalResult);
            });
        });

        setTimeout(function(){
            $('body.single.single-product form.cart.variations_form').trigger('woocommerce_variation_select_change');
            $('body.single.single-product form.cart input.input-text.qty.text').trigger('change');
        }, 500);

        $(document.body).on('change','.qty-box-wrapper-dropdown select,.qty-box-wrapper-radio li .wcmmq-radio-button',function(){
            //var product_id = $(this).closest('.wcmmq-custom-qty-box-wrapper').data('product_id');
            var val = $(this).val();
            var targetInputBox =$(this).closest('.wcmmq-hidden-input-wrapper').find('input.input-text.qty');
            targetInputBox.val(val);
            targetInputBox.trigger('change');
            
        });
        
        $(document.body).on('wpt_added_to_cart',function(aaa,args){
            if(args.status === true){
                var product_id = args.product_id;
                var WrapperBox = $('.wcmmq-hidden-input-wrapper.wcmmq-hid-product_id-' + product_id + '.wcmmq-dropdown-radio-input');
                var select = WrapperBox.find('select');
                select.val(WrapperBox.find('select option:first').val());
                
                //If radio
                WrapperBox.find('.wcmmq-radio-button').first().prop('checked',true);
            }
            return;
        });
        
        $('.wcmmq-custom-dropdonw.wcmmq-hidden-input-wrapper').each(function(){

            var val = $(this).find('.wcmmq-custom-qty-box-wrapper select').val();

            var inputBox = $(this).find('input.wcmmq-hidden-input');
            inputBox.attr('max','');
            inputBox.attr('min',0);
            inputBox.attr('step','0.001');
            inputBox.val(val);
            inputBox.trigger('change');

        });

        QuantityChange();
        ourAttrChange();
        
        
    });

    // Make the code work after executing AJAX.
    $(document).ajaxComplete(function () {
        QuantityChange();
    });

    /**
     * When variation changed input value should be updated as per min, max attr
     * 
     * @since 1.9
     */
    function ourAttrChange(){

        if( WCMMQ_DATA.product_type != 'variable') return;

        $('div.quantity input[type=number]').attrchange({
            trackValues: true, /* Default to false, if set to true the event object is 
                        updated with old and new value.*/
            callback: function (event) { 
                // console.log(event);
                if(event.attributeName == 'min'){
                    // console.log(event.oldValue, event.newValue);
                    $($(event.target).val(event.newValue));
                }
            }        
        });
    }

    function QuantityChange() {
        $(document).off("click", ".qib-button").on("click", ".qib-button", function () {

            var qty = $(this).siblings(".quantity").find(".qty");
            // Read value and attributes min, max, step.
            var val = parseFloat(qty.val());
            var max = parseFloat(qty.attr("max"));
            var min = parseFloat(qty.attr("min"));
            var step = parseFloat(qty.attr("step"));

            if ($(this).is(".plus")) {
                if (val === max)
                    return false;
                if (isNaN(val)) {
                    qty.val(step);
                    return false;
                }
                if (val + step > max) {
                    qty.val(max);
                } else {
                    qty.val(val + step);
                }
            } else {
                if (val === min)
                    return false;
                if (isNaN(val)) {
                    qty.val(min);
                    return false;
                }
                if (val - step < min) {
                    qty.val(min);
                } else {
                    qty.val(val - step);
                }
            }

            qty.val(Math.round(qty.val() * 100000) / 100000);
            qty.trigger("change");
        });
    }
})(jQuery);
