(function($) {
	$(document).ready(function(){
		// Specialist profile
		$('.specialist_offline_visit').on('change', function() {
			let list = $(this).parent().siblings('.drplus-specialist-form-reserve-offices');
			if($(this).prop('checked')) {
				list.fadeIn({
					start: function() {
						$(this).css('display', 'grid')
					}
				});
			} else {
				list.fadeOut({
					start: function() {
						$(this).css('display', 'grid')
					}
				});
			}
		});
		$('.specialist_online_visit').on('change', function() {
			let list = $(this).parent().siblings('.drplus-specialist-form-reserve-consultation-offices');
			if($(this).prop('checked')) {
				list.fadeIn({
					start: function() {
						$(this).css('display', 'grid')
					}
				});
			} else {
				list.fadeOut({
					start: function() {
						$(this).css('display', 'grid')
					}
				});
			}
		});
		// Times
		// Switch status
		$(document).on('change', '.drplus-specialist-form-time-status', function() {
			let row = $(this).closest('.drplus-specialist-form-time-row');
			if($(this).prop('checked')) {
				row.removeClass('inactive');
			} else {
				row.addClass('inactive');
			}
		})
		// Remove time
		let reservationPrefix = 'drplus-specialist-form-time-',
			defaultTimeLength = $(`.drplus-specialist-form-default-time-row`).length;
		$(document).on('click', `.${reservationPrefix}remove`, function() {
			// alert
			if( confirm(drplusMyAccount.i18n.confirmRemoveTime) ) {
				let type = $(this).data('type');
				if( type == 'custom' ) {
					dayIndex = $(this).closest(`.${reservationPrefix}day`).data('day-index');
				}

				let times = $(this).closest('.drplus-specialist-form-times');
				$(this).closest(`.${reservationPrefix}row`).fadeOut({
					duration: 300,
					complete: function() {
						$(this).remove();
						if(type == 'custom') {
							times.find(`.${reservationPrefix}row`).each(function( index, el ) {
								let newIndex = index + 1;
								$(el).find(`.${reservationPrefix}from`).attr('name', `specialist_days[${dayIndex}][times][${newIndex}][time_from]`);
								$(el).find(`.${reservationPrefix}to`).attr('name', `specialist_days[${dayIndex}][times][${newIndex}][time_to]`);
								$(el).find(`.${reservationPrefix}id`).attr('name', `specialist_days[${dayIndex}][times][${newIndex}][id]`);
								$(el).find(`.${reservationPrefix}index`).text(newIndex);
							});
						} else {
							times.find(`.${reservationPrefix}row`).each(function( index, el ) {
								let newIndex = index + 1;
								$(el).find(`.${reservationPrefix}from`).attr('name', `specialist_default_times[${newIndex}][from]`);
								$(el).find(`.${reservationPrefix}to`).attr('name', `specialist_default_times[${newIndex}][to]`);
								$(el).find(`.${reservationPrefix}status`).attr('name', `specialist_default_times[${newIndex}][status]`);
								$(el).find(`.${reservationPrefix}index`).text(newIndex);
								defaultTimeLength = $(`.drplus-specialist-form-default-time-row`).length;									
							})
						}
					}
				})
			}
		});
		// New time
		$(`.drplus-specialist-form-times-new`).on('click', function(e) {
			e.preventDefault();
			let type = $(this).attr('data-type'),
				html;
			if( type == 'default' ) {
				let template = wp.template(`specialist_default_time`);			
				defaultTimeLength++;
				html = template({
					index: defaultTimeLength
				});
			} else {
				let template = wp.template('specialist_custom_time'),
					timesLength = $(this).closest(`.drplus-specialist-form-day-times-wrap`).find(`.drplus-specialist-form-time-row`).length+1,
					dayIndex = $(this).closest(`.drplus-specialist-form-day`).data('day-index');
				html = template({
					index: timesLength,
					day_index: dayIndex,
				});
			}
			$(html).appendTo($(this).siblings('.drplus-specialist-form-times'));

			// Focus on time from input
			$(this).siblings(`.drplus-specialist-form-times`).find(`.${reservationPrefix}from`).last().focus();
		});
		// Custom times
		$('.drplus-specialist-form-day-status').on('change', function() {
			let dayWrap = $(this).closest('.drplus-specialist-form-day');
			if( $(this).prop('checked') ) {
				dayWrap.removeClass('inactive');
			} else {
				dayWrap.addClass('inactive');
			}
			dayWrap.find('.drplus-specialist-form-day-default').trigger('change')
		})
		$('.drplus-specialist-form-day-default').on('change', function() {
			let dayWrap = $(this).closest('.drplus-specialist-form-day'),
				timesWrap = dayWrap.find('.drplus-specialist-form-day-times-wrap');
			if($(this).prop('checked')) {
				timesWrap.hide();
			} else {
				timesWrap.show();
			}
		});

		// Subscription plan
		$('.drplus_subscription_plan_buy_btn').on('click', function() {
			let $this = $(this),
				$planCart = $this.closest('.drplus_subscription_plan_wrap');
				nonce = $planCart.find('.drplus_subscription_plan_nonce').val();
				planId = $planCart.find('.drplus_subscription_plan_id').val();
			
			$this.addClass('loading');
			$('.drplus_subscription_plan_wrap').addClass('disabled');


			$.ajax({
				url: drplusVars.ajaxUrl,
				type: 'POST',
				data: {
					action: 'drplus_process_buy_plan',
					plan_id: planId,
					nonce: nonce,
				},
				success: function(res) {
					if( res.success ) {
						window.location.replace(res.data.checkoutUrl);
					} else {
						console.log(res);
					}
				},
				complete: function() {
					$this.removeClass('loading');
					$('.drplus_subscription_plan_wrap').removeClass('disabled');
				}
			});
		})
	});
})(jQuery);