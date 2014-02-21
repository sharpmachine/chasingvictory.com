(function ($) {

	$(function () {

        if( $('#deposit_price_base').val() == 'percent' ){

            $('strong.field_deposit_currency').hide();

            $('#deposit_price_value').next().find('.depo-value-percent-sign').show();

        } else {

            $('strong.field_deposit_currency').show();

            $('#deposit_price_value').next().find('.depo-value-currency-sign').show();

        }

		// Place your administration-specific JavaScript here

        $('#deposit_price_base').change(function(){

            if ( $(this).val() == 'percent' ) {

                $('strong.field_deposit_currency').hide();

                $('#deposit_price_value').next().find('.depo-value-currency-sign').hide();
                $('#deposit_price_value').next().find('.depo-value-percent-sign').show();

            } else {

                $('strong.field_deposit_currency').show();

                $('#deposit_price_value').next().find('.depo-value-percent-sign').hide();
                $('#deposit_price_value').next().find('.depo-value-currency-sign').show();

            }

        });
        
        //site-wide deposit options
        $('.hidden-option').parent().parent().css('display', 'none');
        
        $('#site_wide_deposit_option').change(function() {
        	
        	if ( 'yes' === $(this).val() ) {
        		
        		$('#deposit_price_base, #deposit_price_value').removeClass('hidden-option').parent().parent().show(1500);
        		
        	} else {
        		
        		$('#deposit_price_base, #deposit_price_value').addClass('hidden-option').parent().parent().hide(1000);
        		
        	}
        	
        }).change();
        
        $('#_deposit_type').change(function(){
        	
            if($(this).val() == 'percent'){

                $('span.prcnt').show();
                $('span.curs').hide();

            } else {

                $('span.prcnt').hide();
                $('span.curs').show();

            }
            
        }).change();
        
        //Reset Labels To Defaults
        $('#wpp_reset_labels_to_defaults').on('click', function(event) {
        	
        	event.preventDefault();
        	
        	$(this).after('<input type="hidden" name="wpp_reset_to_defaults" value="1" />');
        	
        	$(this).parent().find('p.submit').find('input[type="submit"]').click();
        	
        	return false;
        	
        });
        
        //individual product deposit options
        $('#_enable_deposit_options').change(function() {
        	
        	if( 'yes' === $(this).val() ) {

                $('._deposit_price_field').show();
                $('._deposit_type_field').show();

            } else {

            	$('._deposit_price_field').hide();
                $('._deposit_type_field').hide();

            }
        	
        }).change();
        
	});
	
	//edit order page
	$(function() {
		
		$('.remove-deposit').click(function(event) {
			event.preventDefault();
			
			$(this).parent().remove();
			
			return false;
		});
		
		$('#new-deposit').click(function(event) {
			event.preventDefault();
			
			$('#new-deposit-wrapper').show('slow');
			
			return false;
		});
		
		$('#add-new-deposit').click(function(event) {
			event.preventDefault();
			
			var new_deposit_sum = $('#new-deposit-amount').val();
			
			//TODO: apply totals format from woocommerce_writepanel_params instead
			//TODO: add security checks for data
			var new_deposit_status = $('#new-deposit-status').val();
			
			var new_deposit_payment_method = $('#new-deposit-payment-method').val();
			
			var security = $('#add-new-deposit-security').val();
			
			var request = $.ajax({
				  url: woocommerce_writepanel_params.ajax_url,
				  type: "POST",
				  data: {
					  action: 'wpp_add_new_deposit_record',
					  security: security,
					  order_id: woocommerce_writepanel_params.post_id,
					  new_deposit_sum: new_deposit_sum,
					  new_deposit_status: new_deposit_status,
					  new_deposit_payment_method: new_deposit_payment_method
				  },
				  dataType: "html"
			});
			
			request.done(function( response ) {
				$('#paid-deposits').append( response );
				$('#new-deposit-amount').val(0);
				$('#new-deposit-status>option:eq(0)').prop('selected', true);
				$('#new-deposit-payment-method>option:eq(0)').prop('selected', true);
			});
				
			request.fail(function(jqXHR, textStatus) {
				alert( "Request failed: " + textStatus );
			});
			
			return false;
		});
		
	});

}(jQuery));