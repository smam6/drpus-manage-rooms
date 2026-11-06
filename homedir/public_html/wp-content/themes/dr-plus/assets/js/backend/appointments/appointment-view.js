(function($) {
	$(document).ready(function(){
		const prefix = "appointments_";
		$($(`#${prefix}edit-app-form *:is(input, select, textarea)`)).on('change', function() {
			let $input = $(this),
				value = $input.val(),
				fieldset = $input.closest('.drplus_form_fieldset'),
				error = $input.prop("required") && !value,
				errorText = "";				
			if(error) {
				errorText = drplusAppointment.i18n.requiredField;
			}
			if(!error) {
				if($input.is("[type=email]") && $input.val().length && !drplus.validateEmail(value)) {
					error = true;
					errorText = drplusAppointment.i18n.wrongEmail;
				} else if($input.attr('id') == `${prefix}edit_nid` && $input.val().length && !drplus.validateIDCode(value)) {
					error = true;
					errorText = drplusVars.i18n.wrongIDCode;
				} else if($input.attr('id') == `${prefix}edit_phone` && $input.hasClass('drplus-phone-input') && $input.val().length && !drplus.validateMobile(value)) {
					error = true;
					errorText = drplusVars.i18n.wrongMobile;
				}
			}
			if(!error) {
				fieldset.removeClass('drplus_form_error');
			} else {
				fieldset.find('.drplus_form_field_error-text').text(errorText);
				fieldset.addClass('drplus_form_error');
			}
			checkFromErrors();
		});

		$(`#${prefix}edit-app-form`).on('submit', function(e) {
			if($(this).find('.drplus_form_error').length) {
				e.preventDefault();
				checkFromErrors();
			}
		});

		function checkFromErrors() {
			let completeFields = true;
			$(`.${prefix}edit-app-field[required]`).each(function() {
				if($(this).val().length == 0) {
					completeFields = false;
				}
			});
			if(completeFields && !$('.drplus_form_error').length) {
				$(`.${prefix}edit-app-btn`).removeClass('disabled');
			} else {
				$(`.${prefix}edit-app-btn`).addClass('disabled');
			}
		}
		
		// Cancel booking alert
		$('.drplus-booking-receipt-cancel-booking-open-popup').on('click', function() {
			$('#drplus-overlay').fadeIn();
			$('.drplus-booking-receipt-cancel-booking-popup').fadeIn();
		})
		$('.drplus-booking-receipt-back-cancel-booking').on('click', function() {
			$('.drplus-booking-receipt-cancel-booking-popup').fadeOut();
			$('#drplus-overlay').fadeOut();
		});
		
	});
})(jQuery);