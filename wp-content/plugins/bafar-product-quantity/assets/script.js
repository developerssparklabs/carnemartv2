( function( $ ) {
"use strict";

	jQuery(document).ready( function() {
	
		jQuery(document).on('change', '.pq-qty-input', function() {
            jQuery(this).closest('li').find('a').attr('data-quantity', jQuery(this).val());
			// 			for some themes
			if ( jQuery(this).closest('div.col-inner').length > 0 ) {
				jQuery(this).closest('div.col-inner').find('a').attr('data-quantity', jQuery(this).val());
			}
        });
		jQuery('ul.products').find('li').each(function() {
            jQuery(this).closest('li').find('a.add_to_cart_button').attr('data-quantity', jQuery(this).find('.spq-qty-input').val());
        });
	});

	
	
	
	
})(jQuery);

