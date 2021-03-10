jQuery(document).ready(function () {
	
	//show and hide instructions
    jQuery("#api_help").click(function () {
        jQuery("#api_instru").toggle();
    });
	
	//toggle content
	jQuery("#toggle2").click(function() {
		jQuery("#panel2").toggle();
	});
	jQuery("#toggle3").click(function() {
		jQuery("#panel3").toggle();
	});
	jQuery("#toggle4").click(function() {
		jQuery("#panel4").toggle();
	});
});