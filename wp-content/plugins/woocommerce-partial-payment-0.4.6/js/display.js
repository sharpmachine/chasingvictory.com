(function ($) {

	'use strict';

	$(function () {
		
		$('input[name=partial_payment]').live('click', function() {
			
			var selected = $('input[name=partial_payment]:checked').val();

			if ( 'deposit' === selected )  {
				
				if ( true == $('#payment_method_cod').prop('checked') ) {
					
					$('#payment_method_cod').removeAttr('checked');
					
					$('input[name=payment_method]').not('#payment_method_cod').eq(0).attr('checked', 'checked').click();
					
				} else {
					
					$('input[name=payment_method]:checked').click();
					
				}
				
				$('#payment_method_cod').parent('li').hide();

				$('#wpp-calculate-deposit').show();
				
				$('#place_order').hide();

			} else {

				$('input[name=payment_method]:checked').click();
				
				$('#payment_method_cod').parent('li').show();

				$('#wpp-calculate-deposit').hide();
				
				$('#place_order').show();

			}

		});

		$('#wpp-calculate-deposit').live('click', function(event) {
			
			event.preventDefault();
			
			var request = $.ajax({
					url: woocommerce_params.ajax_url,
					type: 'GET',
					data: {
						action: 				'wpp_calculate_total_deposit',
						security:				$('#_n').val()
					},
				dataType: 'html'
			});
			
			$(this).before('<div id="wpp-spinner"></div>');

			request.done(function( data ) {
				
				$('#wpp-spinner').remove();

				$( data ).dialog({
					title: $('#wpp-calculate-deposit').text(),
					width: 'auto'
				});
				
			});
				
			request.fail(function(jqXHR, textStatus) {
				
				console.log( 'Request failed: ' + textStatus );
				
				$('#wpp-spinner').remove();
				
			});
			
		});
		
		$(window).resize(function() {
			
		    $('.ui-dialog-content').dialog('option', 'position', 'center');
		    
		});
		
		$('#wpp-close-deposit-window').live('click', function(event) {
			
			event.preventDefault();
			
			$('.ui-dialog-titlebar-close').click();
			
		});
		
		$('#wpp-pay-deposit').live('click', function(event) {
			
			event.preventDefault();
			
			$('#place_order').click();
			
			$('.ui-dialog-titlebar-close').click();
			
		});
		
		$('body').on('updated_checkout', function() {

			$('input[name=partial_payment]:checked').click();
			
		});

	});

}(jQuery));