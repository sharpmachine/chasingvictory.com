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

	$('button.woods-active').click(function(){
		$('li.metals, li.gemstones, #woods, #gemstones').removeClass('active');
		$('li.woods, #woods').addClass('active');
	});
	$('button.metals-active').click(function(){
		$('li.woods, li.gemstones, #woods, #gemstones').removeClass('active');
		$('li.metals, #metals').addClass('active');
	});
	$('button.gemstones-active').click(function(){
		$('li.woods, li.metals, #woods, #metals').removeClass('active');
		$('li.gemstones, #gemstones').addClass('active');
	});
	
});


// Upload form style
document.getElementById("uploadBtn").onchange = function () {
    document.getElementById("uploadFile").value = this.value;
};




















