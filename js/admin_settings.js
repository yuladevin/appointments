jQuery(function($) {

	$('#dp_ui_content select[multiple!="multiple"]').selectric();

}); 

function showAccordionAppointments(div) {
	if(jQuery('#'+div).css('display') == 'none') {
		jQuery('#'+div).slideDown('fast');
	} else {
		jQuery('#'+div).slideUp('fast');
	}
}
