jQuery( document ).ready(function() {
    jQuery( ".report_type_tab" ).on( "click", function(e) {
		var div_id = jQuery(this).data('type');
	
		if(jQuery(this).hasClass('inactive')){ //this is the start of our condition 
			jQuery('.report_type_tab').addClass('inactive');
			jQuery('.report_type_tab').removeClass('active');			
			jQuery(this).removeClass('inactive');
			jQuery(this).addClass('active');
		
			jQuery('.bar_chart').hide();
			jQuery('#'+ div_id).fadeIn('slow');
		}
		e.preventDefault();
	});	
});

jQuery(document).on("change", "#sales_report_location_by", function(){
	"use strict";
    var value = jQuery(this).val();
    var url = document.location.href;
	window.location.search += "&report_country_by="+value;
});
  
  
