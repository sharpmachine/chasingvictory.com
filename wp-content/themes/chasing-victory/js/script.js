/* Author: 

*/

// Allows you to use the $ shortcut.  Put all your code  inside this wrapper
jQuery(document).ready(function($) {
	
	// Forces WordPress to place nice with dropdowns
	$("li.page_item_has_children").addClass('dropdown');
	$("li.page_item_has_children > a").addClass('dropdown-toggle');
	$("li.page_item_has_children > a").attr('data-toggle','dropdown');
	$("a.dropdown-toggle").append('<b class="caret"></b>');
	$("ul.children").addClass('dropdown-menu');
	$("ul.page-numbers").addClass('pagination');

	// Add bootstrap pagination class to WordPress pagination
	$("ul.page-numbers").addClass('pagination');

	// HTML Placeholder for IE
	// $('[placeholder]').focus(function() {
	// 	var input = $(this);
	// 	if (input.val() == input.attr('placeholder')) {
	// 		input.val('');
	// 		input.removeClass('placeholder');
	// 	}
	// }).blur(function() {
	// 	var input = $(this);
	// 	if (input.val() == '' || input.val() == input.attr('placeholder')) {
	// 		input.addClass('placeholder');
	// 		input.val(input.attr('placeholder'));
	// 	}
	// }).blur().parents('form').submit(function() {
	// 	$(this).find('[placeholder]').each(function() {
	// 		var input = $(this);
	// 		if (input.val() == input.attr('placeholder')) {
	// 			input.val('');
	// 		}
	// 	})
	// });

	$('#jumbotron-standard .item:first-child, #jumbotron-cinematic .item:first-child').addClass("active");
	$('#jumbotron-standard li, #jumbotron-cinematic li').addClass("active");

	$('.woods-active').click(function(){
		$('li.metals, li.gemstones, #woods, #gemstones').removeClass('active');
		$('li.woods, #woods').addClass('active');
	});
	$('.metals-active').click(function(){
		$('li.woods, li.gemstones, #woods, #gemstones').removeClass('active');
		$('li.metals, #metals').addClass('active');
	});
	$('.gemstones-active').click(function(){
		$('li.woods, li.metals, #woods, #metals').removeClass('active');
		$('li.gemstones, #gemstones').addClass('active');
	});

	$('#layaway-info').popover('hide');

	$('.widget_product_categories select').addClass('form-control champagn-border');

	$('a.custom-options').click(function(e){
		e.preventDefault();
		$('.product-custom-options').slideToggle('fast');
	});

	$('.product-filter-dropdown').change(function() {
		var $this = $(this);
		location.href = "?product_cat=" + $this.val();
	});
	
});


// Upload form style
// document.getElementById("uploadBtn").onchange = function () {
//     document.getElementById("uploadFile").value = this.value;
// };




















