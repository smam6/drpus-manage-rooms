(function($) {
	$(document).ready(function(){
		let section = $('.booking_wrap').attr('data-section');
		let allTimeRanges = {};
		const bookingData = typeof drplusBooking != 'undefined' ? drplusBooking : null;
		
		if(section == 'time') {
			if( bookingData ) {
				let selectedOfficeID = bookingData.mainOffice;
				initCalendar();
				$('.booking-specialist-office-radio').on('change', initCalendar);

				function initCalendar() {
					selectedOfficeID = $('.booking-specialist-office-radio:checked').val();
					
					if(typeof pd != 'undefined') pd.destroy();

					if(selectedOfficeID == 'instant_chat_consultation') {
						$('.booking-calendar-wrap, .booking-times-wrap, .section-title-wrap').hide();
						if(!bookingData.isInstantChatAvailable) {
							$('.booking-instant-chat-notice').show();
							$('.booking-next-step-btn').addClass('disabled');
						} else {
							$('.booking-next-step-btn').removeClass('disabled');
						}
						return;
					} else {
						$('.booking-calendar-wrap, .booking-times-wrap, .section-title-wrap').show();
						$('.booking-instant-chat-notice').hide();
					}
					
					let setDate = bookingData.nearestDateTimestamps[selectedOfficeID]*1000;

					let maxBookingDays = bookingData.maxBookingDays[selectedOfficeID];
					
					let datePickerOptions = {
						inline: true,
						format: "LLLL",
						viewMode: "day",
						initialValue: !!setDate,
						minDate: new Date().getTime(),
						autoClose: false,
						position: "auto",
						altFormat: "X",
						altField: "#booking-date",
						onlyTimePicker: false,
						onlySelectOnDate: false,
						calendarType: !!bookingData.PersianDatePicker ? "persian" : "gregorian",
						inputDelay: 800,
						observer: false,
						calendar: {
							persian: {
								locale: "fa",
								showHint: false,
								leapYearMode: "astronomical"
							},
							gregorian: {
								locale: "en",
								showHint: false
							}
						},
						navigator: {
							enabled: true,
							scroll: {
								enabled: false
							},
							text: {
								btnNextText: '>',
								btnPrevText: "<"
							}
						},
						toolbox: {
							enabled: false,
						},
						timePicker: {
							enabled: false,
						},
						dayPicker: {
							enabled: true,
							titleFormat: !!bookingData.PersianDatePicker ? "MMMM / YYYY" : "YYYY MMMM"
						},
						monthPicker: {
							enabled: true,
							titleFormat: "YYYY"
						},
						yearPicker: {
							enabled: true,
							titleFormat: "YYYY"
						},
						responsive: true,
						checkDate: function(unix) {
							return checkDateAvailability(unix)
						},
						onSelect: function(unix) {
							setDayTimes(unix);
						}
					}

					// Set maxDate if maxBookingDays is not empty
					if( !!maxBookingDays || maxBookingDays === '0' ) {
						datePickerOptions['maxDate'] = new Date().getTime() + (maxBookingDays * 24 * 60 * 60 * 1000);
					}

					$('#booking-date').val("");
					pd = $('.booking-calendar-wrap').mjpersianDatepicker( datePickerOptions );
					if(!!setDate) {
						pd.setDate(setDate);
						setDayTimes(setDate);
					} else {
						$('.booking-time-slots').empty();
						$('#booking-time').val("").trigger('change');
						$('.booking_time_empty_slot_notice').fadeIn();
					}
				}

				function checkDateAvailability(unix) {
					if(typeof bookingData.offDays[selectedOfficeID] == 'undefined') return false;

					let officeOffDays = bookingData.offDays[selectedOfficeID].map(function(item) {
						return !!bookingData.PersianDatePicker ? drplus.IRdayIndex(parseInt(item)) : parseInt(item)+1;
					});
					// 'unix' is the timestamp of the date being checked
					let date = new persianDate(unix);
					
					// Disable off days
					if(officeOffDays.includes(date.day())) {
						return false; // Disable off days
					}

					// Disable custom off days							
					if(bookingData.customOffDays[selectedOfficeID].includes((unix/1000).toString())) {
						return false;
					}
	
					return true; // All other dates are enabled
				}
		
				function setDayTimes(unix) {		
					let timeRanges = [];
					(async function() {
						$('.booking-time-slots').empty();
						$('.booking-times-loading').fadeIn({
							start: function() {
								$(this).css('display', 'flex');
							}
						});
						$('.booking-time-slots').addClass('disabled');
						$('#booking-date').trigger('change');
						$('#booking-time').val("").trigger('change');
						timeRanges = await getTimeRanges(unix);
		
						createTimeSlotElements(timeRanges);
					})();
				}
		
				async function getTimeRanges(unix) {
					const date = new Date(unix);
					const year = date.getFullYear();
					const month = String(date.getMonth() + 1).padStart(2, '0');
					const day = String(date.getDate()).padStart(2, '0');
					let formattedDate = `${year}-${month}-${day}`;
					
					if (typeof allTimeRanges[selectedOfficeID] != 'undefined' && formattedDate in allTimeRanges[selectedOfficeID]) {
						return allTimeRanges[selectedOfficeID][formattedDate];
					} else {
						// Get from AJAX
						$('.booking-times-error-alert').hide();
						$('.booking-times-wrap').show();
						try {
							const res = await $.ajax({
								url: drplusVars.ajaxUrl,
								type: 'POST',
								data: {
									action: 'drplus_get_available_times',
									date: formattedDate,
									office: selectedOfficeID,
									specialist: bookingData.specialistID,
									chunk: bookingData.chunkTimes[selectedOfficeID],
									nonce: $('#booking-time').attr('data-nonce')
								}
							});

							// Check if the response is successful
							if( res.success ) {
								if(typeof allTimeRanges[selectedOfficeID] == "undefined") allTimeRanges[selectedOfficeID] = {}
								allTimeRanges[selectedOfficeID][formattedDate] = res.data;
								return res.data;
							} else {
								$('.booking-times-error-alert').css('display', 'flex');
								$('.booking-times-wrap').hide();
								console.error("Error fetching time ranges:", res.data || res);
								return [];
							}
						} catch (error) {
							$('.booking-times-error-alert').css('display', 'flex');
							$('.booking-times-wrap').hide();
							console.error("Error fetching time ranges:", error);
							return [];
						}
					}
				}
				
				// Function to create <li> elements for each time slot
				async function createTimeSlotElements(timeSlots) {
					const timeSlotsContainer = $('.booking-time-slots');
					if( !timeSlots ) return;
					
					timeSlots.forEach(slot => {
						// Set the data-timestamp attribute as Unix timestamp in milliseconds
						// Calculate the milliseconds since midnight
						const [hours, minutes] = slot['from'].split(":").map(Number);
						const timeInMs = (hours * 60 * 60 * 1000) + (minutes * 60 * 1000);
						
						let template = wp.template(`drplus-time-slot`);
						
						let timeSlot = template({
							timestamp: timeInMs,
							available: slot['available'],
							time: slot['from'],
							label: slot['label']
						});
						
						timeSlotsContainer.append(timeSlot);
					});
					if(timeSlotsContainer.find('.booking-time-slot').length > 0) {
						$('.booking-time-slots').removeClass('disabled');
						$('.booking_time_empty_slot_notice').hide();
					} else {
						$('.booking_time_empty_slot_notice').fadeIn();
					}
					$('.booking-times-loading').fadeOut();
				}

				$(document).on('click', '.booking-time-slot:not(.disabled)', function() {
					let timestamp = $(this).attr('data-timestamp');
					$('#booking-time').val(timestamp).trigger('change');
					$('.booking-time-slot.selected').removeClass('selected');
					$(this).addClass('selected');
				});

				$('#booking-time, #booking-date').on('change', function() {								
					if(!$('#booking-time').val().length || !$('#booking-date').val().length) {
						$('.booking-next-step-btn').addClass('disabled');
					} else {
						$('.booking-next-step-btn').removeClass('disabled');
					}
				});
			}
		} else if(section == 'info') {
			// Apply mask
			$('#booking-info-customer-phone').trigger('input');
			checkFromErrors();
			$('#booking-info-foreign-checkbox').on('change', function() {				
				if($(this).prop('checked')) {
					$('.booking-info-customer-nid-field-wrap .required').hide();
					$('#booking-info-customer-nid').attr('disabled', true).removeAttr('required').addClass('disabled').val("");
					$('.booking-info-customer-nid-field-wrap').removeClass('drplus_form_error')
				} else {
					$('.booking-info-customer-nid-field-wrap .required').show();
					$('#booking-info-customer-nid').attr('required', true).removeAttr('disabled').removeClass('disabled');
				}
				checkFromErrors();
			});
			$('#booking-info-foreign-checkbox').trigger('change');

			$('.booking-info-customer-field').on('change', function() {
				let $input = $(this),
					value = $input.val(),
					fieldset = $input.closest('.booking-info-customer-field-wrap'),
					error = $input.prop("required") && !value,
					errorText = "";
				if(error) {
					errorText = bookingData.i18n.requiredField;
				}
				if(!error) {
					if($input.is("[type=email]") && $input.val().length && !drplus.validateEmail(value)) {
						error = true;
						errorText = bookingData.i18n.wrongEmail;
					} else if($input.attr('id') == 'booking-info-customer-nid' && $input.val().length && !drplus.validateIDCode(value)) {
						error = true;
						errorText = drplusVars.i18n.wrongIDCode;
					} else if($input.attr('id') == 'booking-info-customer-phone' && $input.hasClass('drplus-phone-input') && !drplus.validateMobile(value)) {
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

			$('#booking-form').on('submit', function(e) {
				if($(this).find('.drplus_form_error').length) {
					e.preventDefault();
					checkFromErrors();
				}
			})

			function checkFromErrors() {
				let completeFields = true;
				$('.booking-info-customer-field[required]:not([disabled])').each(function() {
					if($(this).val().length == 0) {
						completeFields = false;
					}
				});
				if(completeFields && !$('.drplus_form_error').length) {
					$('.booking-next-step-btn').removeClass('disabled');
				} else {
					$('.booking-next-step-btn').addClass('disabled');
				}
			}
		}

		if($('.drplus-booking-receipt-create-room-notice').length) {
			// Create room link
			let data = $('.drplus-booking-receipt-create-room-notice').data('args');
			if(!data) return;
			
			$.ajax({
				url: drplusVars.ajaxUrl,
				type: 'post',
				data: {
					action: 'skyroom_get_room_link',
					order_id: data.orderID,
					user_id: data.userID,
					nonce: data.nonce
				},
				success: function(res) {
					if(res.success) {
						$('.drplus-booking-receipt-create-room-notice').remove();
						$('.drplus-booking-receipt-video-btn')
						$('.drplus-booking-receipt-video-btn').attr('href', res.data.link).removeClass('hidden');
					} else {
						$('.drplus-booking-receipt-create-room-notice-working, .drplus-booking-receipt-create-room-notice-loading').addClass('hidden');
						$('.drplus-booking-receipt-create-room-notice-error').removeClass('hidden');
					}
				},
			});
			
		}
	});
})(jQuery);