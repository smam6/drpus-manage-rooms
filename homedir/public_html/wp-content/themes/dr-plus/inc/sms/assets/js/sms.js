(function($) {
	const prefix = 'drplus_sms_';
	$(document).ready(function(){
		// Change tab
		$(`.${prefix}sidebar-item`).on('click', function() {
			if( $(this).hasClass('active') ) return;

			if( $(this).hasClass(`${prefix}sidebar-sub-item`) ) { // Sub item
				let tab = $(this).closest(`.${prefix}sidebar-item.has-subitems`).attr('data-tab'),
					section = $(this).attr('data-section');
				$(`#${prefix}${tab}-content .${prefix}section-content`).hide();
				$(`#${prefix}${tab}-content #${prefix}${tab}-${section}-content`).slideDown();
			} else { // Main item
				let tab = $(this).attr('data-tab');
				$(`.${prefix}tab-content`).hide();
				$(`#${prefix}${tab}-content`).slideDown();
				$(this).siblings('.active').find(`.${prefix}sidebar-sub-items`).slideUp();
				$(this).find(`.${prefix}sidebar-sub-items`).slideDown({
					start: function() {
						$(this).css({
							display: 'flex'
						});
					}
				});
			}
			$(this).siblings('.active').removeClass('active');
			$(this).addClass('active');
		});

		// Show/hide sections
		$(`.${prefix}settings-section-head`).on('click', function() {
			let section = $(this).closest(`.${prefix}settings-section`);
			section.toggleClass('expanded');
			section.find(`.${prefix}settings-section-body`).slideToggle();
		});

		// Show gateway settings
		$(`.${prefix}gateway`).on('click', function() {
			$(this).siblings('.active').removeClass('active');
			$(this).addClass('active');

			let gateway = $(this).attr('data-id');

			$(`.${prefix}gateway-fields`).hide();
			$(`.${prefix}gateway-${gateway}-fields`).show();
			$(`.${prefix}sidebar-item-settings`).removeClass('disabled');

			// Check variable mode
			if( typeof drplusSMS.gateways[gateway] != 'undefined' && drplusSMS.gateways[gateway].variable_mode ) {
				$(`.${prefix}settings-gateway-var-wrap`).show();
			} else {
				$(`.${prefix}settings-gateway-var-wrap`).hide();
			}
		})

		// Enable/Disable one form switch
		$(`#${prefix}settings-auth-login-status, #${prefix}settings-auth-register-status`).on('change', function() {
			let oneFormWrap = $(`#${prefix}settings-auth-one_form-wrap`),
				oneForm = $(`#${prefix}settings-auth-one_form`);
				
			if($(`#${prefix}settings-auth-login-status`).prop('checked') && $(`#${prefix}settings-auth-register-status`).prop('checked')) {
				oneFormWrap.removeClass('disabled');
				oneForm.prop('disabled', false);
			} else {
				oneFormWrap.addClass('disabled');
				oneForm.prop('disabled', true);
			}
		});

		// Add Remainder sms item
		$(`.${prefix}settings-repeater-add`).on('click', function() {			
			let repeaterWrap = $(this).closest('table').find(`.${prefix}settings-reminder-repeater-wrap`),
				type = repeaterWrap.attr('data-type'),
				length = repeaterWrap.find(`.${prefix}settings-reminder-notif-repeater`).length,
				template = wp.template(`drplus-reminder-item`);		

			let item = template({
				index: length+1,
				type: type,				
			});
			
			repeaterWrap.append(item);
			repeaterWrap.closest('table').find(`.${prefix}status-switch`).trigger('change');
			repeaterWrap.find(`.${prefix}settings-notif-select`).select2({
				width: '25em',
			});

			// get selected gateway
			let gateway = $(`.${prefix}gateway.active`).attr('data-id');
		});

		$(document).on('click', `.${prefix}settings-repeater-remove`, function() {
			$(this).closest(`.${prefix}settings-reminder-notif-repeater`).fadeOut({
				complete: function() {
					let wrap = $(this).closest(`.${prefix}settings-reminder-repeater-wrap`);
					$(this).remove();
					drplus.updateRepeaterIndexes(wrap, {
						item: `${prefix}settings-reminder-notif-repeater`,
						index: `${prefix}settings-reminder-notif-repeater-index`
					}, false);
				}
			});
		});

		// Show/Hide security - hide mobile custom text
		$(`#${prefix}security-hide-mobile`).on('change', function() {
			let customRow = $(`#${prefix}security-hide-mobile-custom-row`)
			if($(this).val() == 'custom') {
				customRow.show();
			} else {
				customRow.hide();
			}
		})

		// Add variable
		$(document).on('click', `.${prefix}variable-add`, function(e) {
			e.preventDefault();
			let wrap = $(this).closest(`.${prefix}variables`),
				isInRepeater = wrap.hasClass(`${prefix}variables-in-repeater`);
			if(isInRepeater) {
				wrap = wrap.closest(`.${prefix}settings-repeater-wrap`);
			}
			let templateId = wrap.find(`.${prefix}variable-template`).attr('id').substr(5),
				template = wp.template(templateId),
				templateArgs = {};
			if( isInRepeater ) {
				templateArgs.index = $(this).closest(`table`).attr('data-index');
			}
			let variable = template(templateArgs);
			$(variable).insertBefore($(this));

			// Check for variable mode
			let gateway = $(`.${prefix}gateway.active`).attr('data-id');
			if( typeof drplusSMS.gateways[gateway] != 'undefined' && drplusSMS.gateways[gateway].variable_mode ) {
				$(`.${prefix}settings-gateway-var-wrap`).show();
			} else {
				$(`.${prefix}settings-gateway-var-wrap`).hide();
			}
		})
		$(document).on('click', `.${prefix}variable-remove`, function(e) {
			e.preventDefault();
			$(this).parent().remove();
		})
	});
})(jQuery);