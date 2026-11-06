(function($) {
	$(document).ready(function(){
		const prefix = "specialist_",
			currentSection = $('#specialist_section').val();

		let dropzones = {};
		// Validate forms
		function checkFormError() {
			$('#specialist_submit').prop('disabled', !!$('.drplus_form_error').length)
		}
		$('#specialist-form').on('submit', function(e) {
			if($(this).find('.drplus_form_error').length) {
				e.preventDefault();
			}
		})
		$('#specialist-form *:is(input, select, textarea)').on('input', function() {
			let $input = $(this),
				value = $input.val(),
				fieldset = $input.closest('.drplus_form_fieldset'),
				error = $input.prop("required") && !value;
			if( error ) {
				fieldset.find('.drplus_form_field_error-text').text(drplusSpecialist.i18n.requiredField);
				fieldset.addClass('drplus_form_error')
			}
			if(!error && $input.is("[type=email]")) {
				if(value && !drplus.validateEmail(value)) {
					error = true;
					fieldset.find('.drplus_form_field_error-text').text(drplusSpecialist.i18n.wrongEmail);
					fieldset.addClass('drplus_form_error')
				}
			}
			if( !error ) {
				fieldset.removeClass('drplus_form_error');
			}
			checkFormError()
		});

		// Identity
		if ($('.drplus-dropzone').length) {
			Dropzone.autoDiscover = false;
			dropzones = drplus.initDropzone(dropzones);
		}
		$(document).on('click', '.drplus-dropzone-current-value-remove', function() {
			let wrap = $(this).closest('.drplus-dropzone-wrap'),
				index = wrap.attr('data-index');
			dropzones[index].removeAllFiles();
			$(this).closest('.drplus-dropzone-current-value').remove();
			wrap.find('input[type="hidden"]').val('').trigger('change');
		})
		// Check submit button
		$(document).on('change', '.drplus-dropzone-wrap input', function() {
			let active = true;
			$('.drplus-dropzone-wrap input').each(function() {
				if($(this).prop('required') && !$(this).val()) {
					active = false;
				}
			})
			if(active) {
				$('#specialist_submit').removeClass('disabled')
			} else {
				$('#specialist_submit').addClass('disabled')
			}
		});

		$(document).on('click', `.${prefix}repeater-remove`, function() {			
			let parent = $(this).closest(`.${prefix}repeater_container`);
			$(this).closest(`.${prefix}repeater_slot`).remove();
			drplus.updateRepeaterIndexes(parent);
		});

		if(currentSection == 'personal') { // Personal
			$(`#${prefix}select_user`).select2({
				width: "25em",
				placeholder: drplusSpecialist.i18n.selectUser,
				ajax: {
					url: drplusVars.ajaxUrl,
					type: 'POST',
					data: function(params) {
						return {
							action: `drplus_get_users`,
							name: params.term,
							nonce: drplusSpecialist.nonces.getUsers,
							type: 'non_specialists',
							template: "%display_name% (%email%)"
						}
					},
					processResults: function(data) {
						return {
							results: data.data,
						}
					},
					cache: false,
				},
				minimumInputLength: 3,
			});

			$(`#${prefix}status`).on('select2:select', function() {
				let elements = [
					`#${prefix}avatar-wrap`,
					`#${prefix}is_verified-wrap`,
					`.${prefix}personal_data`,
				];
				elements = elements.join(',');
				if( $(this).val() == 'rejected' ) {
					$(elements).hide();
					$(`.${prefix}rejected`).show();
					$('.drplus_metabox-tab:not(:first-child)').addClass('disabled')
				} else {
					$(elements).show();
					$(`.${prefix}rejected`).hide();
					$('.drplus_metabox-tab').removeClass('disabled')
				}
			});
	
			$(`#${prefix}select_user`).on('select2:select', function(e) {
				$(`.${prefix}personal_data, .${prefix}avatar`).fadeOut();
				var userID = e.params.data.id;
				$(`#${prefix}user_id`).val(userID);
				$('.specialist_loading-wrap').fadeIn({
					start: function() {
						$(this).css('display', 'flex');
					}
				});
				$(`#${prefix}new_specialist-note`).hide();
				$.ajax({
					url: drplusVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'drplus_get_user_data',
						user_id: userID,
						nonce: drplusSpecialist.nonces.getUserData
					},
					success: function(res) {
						for (const [key, value] of Object.entries(res.data)) {
							$(`#${prefix}first_name`).val(res.data.first_name);
							$(`#${prefix}last_name`).val(res.data.last_name);
							$(`#${prefix}email`).val(res.data.email);
							$(`#${prefix}mobile`).val(res.data.mobile);
							$(`#${prefix}nid`).val(res.data.nid);
							$(`#${prefix}specialist_code`).val(res.data.specialist_code);
							$(`#${prefix}avatar_file`).val(res.data.avatar);
							$(`.${prefix}avatar_img`).attr('src', res.data.avatar ? res.data.avatar_url : $(`.${prefix}avatar`).data('default-avatar'));
							if(res.data.avatar) {
								$(`#${prefix}remove_avatar`).show();
							} else {
								$(`#${prefix}remove_avatar`).hide();
							}
							if(res.data.gender)	$(`#${prefix}gender`).val(res.data.gender).trigger('change');
						}
					},
					complete: function() {
						$(`.${prefix}avatar`).slideDown({
							start: function() {
								$(this).css('display', 'flex');
							}
						});
						$(`.${prefix}personal_data`).slideDown({
							start: function() {
								$(this).css('display', 'grid');
							}
						});
						$(`.${prefix}switches_data`).slideDown({
							start: function() {
								$(this).css('display', 'grid');
							}
						});
						$('.specialist_loading-wrap').fadeOut();
					}
				});
			});
	
			$(`#${prefix}specialities`).select2({
				multiple: true,
				placeholder: drplusSpecialist.i18n.selectSpecialities,
			});
	
			$(`#${prefix}change_avatar`).on('click', function(e) {
				e.preventDefault();
				var $this = $(this),
					selectedIds = $(`#${prefix}avatar_file`);
				var file_frame = wp.media({
					editing : false,
					multiple : false,
					selection : "",
					library: { type: 'image' }
				});
	
				wp.media.gallery.ids = function(attachments) {
					var props = attachments.props.toJSON(), attrs = _.pick(props, 'orderby', 'order');
		
					if (attachments.gallery)
						_.extend(attrs, attachments.gallery.toJSON());
		
					return attachments.pluck('id');
				};
	
				file_frame.on('open', function() {
					var selection = file_frame.state().get('selection'),
						ids = selectedIds.val().split(",");
					ids.forEach(function(id) {
						attachment = wp.media.attachment(id);
						attachment.fetch();
						selection.add(attachment);
					});
				});
	
				file_frame.on('select', function(selection) {
					var selection = file_frame.state().get('selection'),
						ids = wp.media.gallery.ids(selection);
					selectedIds.val(ids.join(","));
	
					// Create preview
					$this.find(`img`).remove();
					selection.models.forEach(function(item) {
						var url = "";
						if( item.attributes.sizes ) {
							var firstSizeKey = Object.keys(item.attributes.sizes)[0],
								firstSize = item.attributes.sizes[firstSizeKey];
							url = firstSize.url;
						} else {
							url = item.attributes.url;
						}
						$(`.${prefix}avatar_img`).attr('src', url);
						$(`#${prefix}remove_avatar`).show();
					});
				});
	
				file_frame.open();
			});
	
			$(`#${prefix}remove_avatar`).on('click', function(e) {
				e.preventDefault();
				let defSrc = $(`.${prefix}avatar`).data('default-avatar');
				$(`.${prefix}avatar_img`).attr('src', defSrc);
				$(`#${prefix}avatar_file`).val("");
				$(this).hide();
			});

			$('#specialist_nid').on('change', function() {
				let fieldset = $(this).closest('.drplus_form_fieldset');
				let value = $(this).val();
				if(value && !drplus.validateIDCode(value)) {
					fieldset.find('.drplus_form_field_error-text').text(drplusVars.i18n.wrongIDCode);
					fieldset.addClass('drplus_form_error')
				} else {
					fieldset.removeClass('drplus_form_error');
				}
				checkFormError()
			})
			$('#specialist_mobile.drplus-phone-input').on('change', function() {
				let fieldset = $(this).closest('.drplus_form_fieldset');
				let value = $(this).val();
				if(value && !drplus.validateMobile(value)) {
					fieldset.find('.drplus_form_field_error-text').text(drplusVars.i18n.wrongMobile);
					fieldset.addClass('drplus_form_error')
				} else {
					fieldset.removeClass('drplus_form_error');
				}
				checkFormError()
			})
		} else if(currentSection == 'services') { // Services
			let serviceSwapy = Swapy.createSwapy(document.getElementById(`${prefix}services`)),
				servicesIndex = $(`#${prefix}services .${prefix}service_item`).length+1;

			$(`#${prefix}service-add`).on('click', function(e) {
				e.preventDefault();
				let template = wp.template(`${prefix}service_template`);
				let html = template({
					index: servicesIndex
				});
				$(html).appendTo(`#${prefix}services`);
				serviceSwapy.update();
				servicesIndex++;
			});

			let faqSwapy = Swapy.createSwapy(document.getElementById(`${prefix}faqs`)),
				faqsIndex = $(`#${prefix}faqs .${prefix}faq_item`).length+1;
			$(`#${prefix}faq-add`).on('click', function(e) {
				e.preventDefault();
				let template = wp.template(`${prefix}faq_template`);
				let html = template({
					index: faqsIndex
				});
				$(html).appendTo(`#${prefix}faqs`);
				faqSwapy.update();
				faqsIndex++;
			});

			serviceSwapy.onSwapEnd(function() {
				drplus.updateRepeaterIndexes($(`#${prefix}services`));
			});
			faqSwapy.onSwapEnd(function() {
				drplus.updateRepeaterIndexes($(`#${prefix}faqs`));
			});
		} else if(currentSection == 'offices') { // offices
			$(`#${prefix}select_hospitals`).select2({
				width: "25em",
				placeholder: drplusSpecialist.i18n.selectHospitals,
				ajax: {
					url: drplusVars.ajaxUrl,
					type: 'POST',
					data: function(params) {
						return {
							action: `drplus_find_hospital`,
							text: params.term,
							nonce: drplusSpecialist.nonces.findHospital,
						}
					},
					processResults: function(data) {
						return {
							results: data.data,
						}
					},
					cache: false,
				},
				minimumInputLength: 3,
			});
	
			let officeSwapy = Swapy.createSwapy(document.getElementById(`${prefix}offices`)),
				officesIndex = $(`#${prefix}offices .${prefix}office_item`).length+1;

			$(`#${prefix}office-add`).on('click', function(e) {
				e.preventDefault();
				let template = wp.template(`${prefix}office_template`);
				let html = template({
					index: officesIndex
				});
				$(html).appendTo(`#${prefix}offices`);
				officeSwapy.update();
				officesIndex++;

				drplus.initSelect2();
				drplus.initProvinceSelector();

				dropzones = drplus.initDropzone(dropzones);
			});
	
			officeSwapy.onSwapEnd(function() {
				drplus.updateRepeaterIndexes($(`#${prefix}offices`));
			});
		} else if(currentSection == 'reserve') { // Reserve
			// Show/Hide hospitals
			$('#specialist_offline_visit').on('change', function() {
				let elements = $(this).parent().siblings('.specialist_reservation_time-offices-wrap');
				if( $(this).prop('checked') ) {
					elements.show();
				} else {
					elements.hide();
				}
			})

			$('#specialist_online_visit').on('change', function() {
				let elements = $(this).parent().siblings('.specialist_reservation_time-consultations-wrap');
				if( $(this).prop('checked') ) {
					elements.show();
				} else {
					elements.hide();
				}
			})

			const reservationPrefix = prefix + "reservation_";

			// init datepicker for off days
			let pdDatePickerConfigs = {
				inline: false,
				format: drplusSpecialist.i18n.add,
				minDate: new Date().getTime(),
				viewMode: "day",
				initialValue: false,
				autoClose: true,
				position: "auto",
				altFormat: 'X',
				onlySelectOnDate: true,
				calendarType: !!drplusVars.PersianDatePicker ? 'persian' : 'gregorian',
				inputDelay: 1,
				observer: false,
				calendar: {
					persian: {
						locale: "fa",
						showHint: true,
						leapYearMode: "astronomical"
					},
					gregorian: {
						locale: "en",
						showHint: true
					}
				},
				navigator: {
					enabled: true,
					scroll: {
						enabled: true
					},
					text: {
						btnNextText: "<",
						btnPrevText: ">"
					}
				},
				toolbox: {
					enabled: true,
					calendarSwitch: {
						enabled: false,
						format: "MMMM"
					},
					todayButton: {
						enabled: true,
						text: {
							fa: drplusVars.i18n.today,
							en: "Today"
						}
					},
					submitButton: {
						enabled: true,
						text: {
							fa: drplusVars.i18n.submit,
							en: "Submit"
						}
					},
					text: {
						btnToday: drplusVars.i18n.today
					},
				},
				timePicker: {
					enabled: false,
					step: 1,
					hour: {
						enabled: true,
						step: null
					},
					minute: {
						enabled: true,
						step: null
					},
					second: {
						enabled: false,
						step: null
					},
					meridian: {
						enabled: false
					}
				},
				dayPicker: {
					enabled: true,
					titleFormat: "YYYY MMMM"
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
				template: "<div id=\"plotId\" class=\"mj-datepicker-plot-area datepicker-plot-area {{cssClass}}\">\n    {{#navigator.enabled}}\n        <div data-navigator class=\"datepicker-navigator\">\n            <div class=\"pwt-btn pwt-btn-next\">{{navigator.text.btnNextText}}</div>\n            <div class=\"pwt-btn pwt-btn-switch\">{{navigator.switch.text}}</div>\n            <div class=\"pwt-btn pwt-btn-prev\">{{navigator.text.btnPrevText}}</div>\n        </div>\n    {{/navigator.enabled}}\n    <div class=\"datepicker-grid-view\" >\n    {{#days.enabled}}\n        {{#days.viewMode}}\n        <div class=\"datepicker-day-view\" >    \n            <div class=\"month-grid-box\">\n                <div class=\"header\">\n                    <div class=\"title\"></div>\n                    <div class=\"header-row\">\n                        {{#weekdays.list}}\n                            <div class=\"header-row-cell\">{{.}}</div>\n                        {{/weekdays.list}}\n                    </div>\n                </div>    \n                <table cellspacing=\"0\" class=\"table-days\">\n                    <tbody>\n                        {{#days.list}}\n                           \n                            <tr>\n                                {{#.}}\n                                    {{#enabled}}\n                                        <td data-date=\"{{dataDate}}\" data-unix=\"{{dataUnix}}\" >\n                                            <span  class=\"{{#otherMonth}}other-month{{/otherMonth}}\">{{title}}</span>\n                                            {{#altCalendarShowHint}}\n                                            <i  class=\"alter-calendar-day\">{{alterCalTitle}}</i>\n                                            {{/altCalendarShowHint}}\n                                        </td>\n                                    {{/enabled}}\n                                    {{^enabled}}\n                                        <td data-date=\"{{dataDate}}\" data-unix=\"{{dataUnix}}\" class=\"disabled\">\n                                            <span class=\"{{#otherMonth}}other-month{{/otherMonth}}\">{{title}}</span>\n                                            {{#altCalendarShowHint}}\n                                            <i  class=\"alter-calendar-day\">{{alterCalTitle}}</i>\n                                            {{/altCalendarShowHint}}\n                                        </td>\n                                    {{/enabled}}\n                                    \n                                {{/.}}\n                            </tr>\n                        {{/days.list}}\n                    </tbody>\n                </table>\n            </div>\n        </div>\n        {{/days.viewMode}}\n    {{/days.enabled}}\n    \n    {{#month.enabled}}\n        {{#month.viewMode}}\n            <div class=\"datepicker-month-view\">\n                {{#month.list}}\n                    {{#enabled}}               \n                        <div data-month=\"{{dataMonth}}\" class=\"month-item {{#selected}}selected{{/selected}}\">{{title}}</small></div>\n                    {{/enabled}}\n                    {{^enabled}}               \n                        <div data-month=\"{{dataMonth}}\" class=\"month-item month-item-disable {{#selected}}selected{{/selected}}\">{{title}}</small></div>\n                    {{/enabled}}\n                {{/month.list}}\n            </div>\n        {{/month.viewMode}}\n    {{/month.enabled}}\n    \n    {{#year.enabled }}\n        {{#year.viewMode }}\n            <div class=\"datepicker-year-view\" >\n                {{#year.list}}\n                    {{#enabled}}\n                        <div data-year=\"{{dataYear}}\" class=\"year-item {{#selected}}selected{{/selected}}\">{{title}}</div>\n                    {{/enabled}}\n                    {{^enabled}}\n                        <div data-year=\"{{dataYear}}\" class=\"year-item year-item-disable {{#selected}}selected{{/selected}}\">{{title}}</div>\n                    {{/enabled}}                    \n                {{/year.list}}\n            </div>\n        {{/year.viewMode }}\n    {{/year.enabled }}\n    \n    </div>\n    {{#time}}\n    {{#enabled}}\n    <div class=\"datepicker-time-view\">\n        {{#hour.enabled}}\n            <div class=\"hour time-segment\" data-time-key=\"hour\">\n                <div class=\"up-btn\" data-time-key=\"hour\">▲</div>\n                <input value=\"{{hour.title}}\" type=\"text\" placeholder=\"hour\" class=\"hour-input\">\n                <div class=\"down-btn\" data-time-key=\"hour\">▼</div>                    \n            </div>       \n            <div class=\"divider\">\n                <span>:</span>\n            </div>\n        {{/hour.enabled}}\n        {{#minute.enabled}}\n            <div class=\"minute time-segment\" data-time-key=\"minute\" >\n                <div class=\"up-btn\" data-time-key=\"minute\">▲</div>\n                <input disabled value=\"{{minute.title}}\" type=\"text\" placeholder=\"minute\" class=\"minute-input\">\n                <div class=\"down-btn\" data-time-key=\"minute\">▼</div>\n            </div>        \n            <div class=\"divider second-divider\">\n                <span>:</span>\n            </div>\n        {{/minute.enabled}}\n        {{#second.enabled}}\n            <div class=\"second time-segment\" data-time-key=\"second\"  >\n                <div class=\"up-btn\" data-time-key=\"second\" >▲</div>\n                <input disabled value=\"{{second.title}}\"  type=\"text\" placeholder=\"second\" class=\"second-input\">\n                <div class=\"down-btn\" data-time-key=\"second\" >▼</div>\n            </div>\n            <div class=\"divider meridian-divider\"></div>\n            <div class=\"divider meridian-divider\"></div>\n        {{/second.enabled}}\n        {{#meridian.enabled}}\n            <div class=\"meridian time-segment\" data-time-key=\"meridian\" >\n                <div class=\"up-btn\" data-time-key=\"meridian\">▲</div>\n                <input disabled value=\"{{meridian.title}}\" type=\"text\" class=\"meridian-input\">\n                <div class=\"down-btn\" data-time-key=\"meridian\">▼</div>\n            </div>\n        {{/meridian.enabled}}\n    </div>\n    {{/enabled}}\n    {{/time}}\n    \n    {{#toolbox}}\n    {{#enabled}}\n    <div class=\"toolbox\">\n        {{#toolbox.submitButton.enabled}}\n            <div class=\"pwt-btn-submit\">{{submitButtonText}}</div>\n        {{/toolbox.submitButton.enabled}}        \n        {{#toolbox.todayButton.enabled}}\n            <div class=\"pwt-btn-today\">{{todayButtonText}}</div>\n        {{/toolbox.todayButton.enabled}}        \n        {{#toolbox.calendarSwitch.enabled}}\n            <div class=\"pwt-btn-calendar\">{{calendarSwitchText}}</div>\n        {{/toolbox.calendarSwitch.enabled}}\n    </div>\n    {{/enabled}}\n    {{^enabled}}\n        {{#onlyTimePicker}}\n        <div class=\"toolbox\">\n            <div class=\"pwt-btn-exit\">{{text.btnExit}}</div>\n        </div>\n        {{/onlyTimePicker}}\n    {{/enabled}}\n    {{/toolbox}}\n</div>\n",
				onSelect: function (unix) {
					let date = new persianDate(unix).format('DD MMMM YYYY');
					let template = wp.template(`${reservationPrefix}off_day_item_template`);
					let html = template({
						text: date,
						value: unix/1000
					});
					$(html).insertBefore(`#${reservationPrefix}add_off_days`);
				}
			};

			$('#specialist_reservation_add_off_days').mjpersianDatepicker(pdDatePickerConfigs);

			$(document).on('click', `.${reservationPrefix}remove_custom_off_day`, function() {
				$(this).closest(`.${reservationPrefix}custom_off_day`).fadeOut({
					complete: function() {
						$(this).remove();
					}
				});
			});

			let defaultTimeLength = $(`.${reservationPrefix}default_times .${reservationPrefix}default-time-row`).length;

			$(`.${reservationPrefix}default-time-add`).on('click', function(e) {
				e.preventDefault();
				let template = wp.template(`${reservationPrefix}default_time_template`);			
				defaultTimeLength++;
				let html = template({
					index: defaultTimeLength
				});
				$(html).appendTo(`.${reservationPrefix}default_times`);
	
				// Focus on time from input
				$(`.${reservationPrefix}default_times .${reservationPrefix}default-time-row`).last().find(`.${reservationPrefix}time-from .${reservationPrefix}time-input`).focus();
			});
			$(`.${reservationPrefix}day-time-add`).on('click', function(e) {
				e.preventDefault();
				let template = wp.template(`${reservationPrefix}custom_time_template`),
					timesLength = $(this).closest(`.${reservationPrefix}day`).find(`.${reservationPrefix}time-row`).length+1,
					dayIndex = $(this).closest(`.${reservationPrefix}day`).data('day-index'),
					html = template({
						index: timesLength,
						day_index: dayIndex,
					});
	
				let timesWrap = $(this).closest(`.${reservationPrefix}day`).find(`.${reservationPrefix}day-times`);
				$(html).appendTo(timesWrap);
	
				// Focus on time from input			
				$(`.${reservationPrefix}day[data-day-index="${dayIndex}"] .${reservationPrefix}time-row`).last().find(`.${reservationPrefix}time-from .${reservationPrefix}time-input`).focus();
			});
			$(document).on('click', `.${reservationPrefix}time-remove`, function() {
				// alert
				if( confirm(drplusSpecialist.i18n.confirmRemoveTime) ) {
					let type = $(this).data('type');
					if(type == 'custom') {
						dayIndex = $(this).closest(`.${reservationPrefix}day`).data('day-index');
					}
	
					let timeRowWraps = type == 'custom' ? $(this).closest(`.${reservationPrefix}day`) : $(this).closest(`.${reservationPrefix}default_times`);
					$(this).closest(`.${reservationPrefix}time-row`).fadeOut({
						duration: 300,
						complete: function() {
							$(this).remove();
							if(type == 'custom') {
								timeRowWraps.find(`.${reservationPrefix}time-row`).each(function( index, el ) {
									let newIndex = index + 1;
									$(el).find(`.${reservationPrefix}time-from input`).attr('name', `${prefix}days[${dayIndex}][times][${newIndex}][time_from]`);
									$(el).find(`.${reservationPrefix}time-to input`).attr('name', `${prefix}days[${dayIndex}][times][${newIndex}][time_to]`);
									$(el).find(`.${reservationPrefix}time-id`).attr('name', `${prefix}days[${dayIndex}][times][${newIndex}][id]`);
									$(el).find(`.${reservationPrefix}time-index`).text(newIndex);
								});
							} else {
								timeRowWraps.find(`.${reservationPrefix}time-row`).each(function( index, el ) {
									let newIndex = index + 1;
									$(el).find(`.${reservationPrefix}time-from input`).attr('name', `${prefix}default_times[${newIndex}][from]`);
									$(el).find(`.${reservationPrefix}time-to input`).attr('name', `${prefix}default_times[${newIndex}][to]`);
									$(el).find(`.${reservationPrefix}reserve_times-status`).attr('name', `${prefix}default_times[${newIndex}][status]`);
									$(el).find(`.${reservationPrefix}time-index`).text(newIndex);
									defaultTimeLength = $(`.${reservationPrefix}default_times .${reservationPrefix}default-time-row`).length;									
								})
							}
							
						}
					})
				}
			});
			$(document).on('change', `.${reservationPrefix}day-status`, function() {
				let checked = $(this).is(':checked');		
				
				let day = $(this).closest(`.${reservationPrefix}day`);
				if(!checked) {
					day.addClass('inactive');
				} else {
					day.removeClass('inactive');
				}
			});
			$(document).on('change', `.${reservationPrefix}reserve_times-status`, function() {
				let checked = $(this).is(':checked');			
				let timeRow = $(this).closest(`.${reservationPrefix}time-row`);
				if(!checked) {
					timeRow.addClass('inactive');
				} else {
					timeRow.removeClass('inactive');
				}
			});
			$(document).on('change', `.${reservationPrefix}day-default-times-checkbox`, function() {
				let checked = $(this).is(':checked');			
				let customTimes = $(this).closest(`.${reservationPrefix}day`).find(`.${reservationPrefix}day-times-wrap`);
				if(!checked) {
					customTimes.removeClass('hidden');
					customTimes.find('input').prop('disabled', false);
				} else {
					customTimes.addClass('hidden');
					customTimes.find('input').prop('disabled', true);
				}
			});
		} else if(currentSection == 'financial') { // Financial
			$(`#${prefix}card_number.drplus-card-number-input`).on('change', function() {
				let fieldset = $(this).closest('.drplus_form_fieldset');
				if(!drplus.validateCardNumber($(this).val())) {
					fieldset.find('.drplus_form_field_error-text').text(drplusSpecialist.i18n.wrongCardNumber);
					fieldset.addClass('drplus_form_error');
				} else {
					fieldset.removeClass('drplus_form_error');
				}
				checkFormError();
			});
	
			$(`#${prefix}shaba_number.drplus-shaba-number-input`).on('change', function() {
				let fieldset = $(this).closest('.drplus_form_fieldset');
				if(!drplus.validateShabaNumber($(this).val())) {
					fieldset.find('.drplus_form_field_error-text').text(drplusSpecialist.i18n.wrongShabaNumber);
					fieldset.addClass('drplus_form_error');
				} else {
					fieldset.removeClass('drplus_form_error');
				}
				checkFormError();
			});
		} else if(currentSection == 'certificates') { // Certificates
			let certificatesSwapy = Swapy.createSwapy(document.getElementById(`${prefix}certificates`)),
				certificatesIndex = $(`.${prefix}certificate`).length;
			
			$(`#${prefix}certificate-add`).on('click', function(e) {
				e.preventDefault();
				let template = wp.template(`${prefix}certificate`);
				let html = template({
					index: certificatesIndex,
					index1: certificatesIndex+1,
				});
				$(html).insertBefore(this);
				certificatesSwapy.update();
				certificatesIndex++;

				dropzones = drplus.initDropzone(dropzones);
			})

			certificatesSwapy.onSwapEnd(function() {
				drplus.updateRepeaterIndexes($(`#${prefix}certificates`));
			});

			// Open WP Media library for dropzone
			$(document).on('click', '.drplus-dropzone-wp .drplus-dropzone', function() {
				let wrap = $(this).closest('.drplus-dropzone-wrap'),
					selectedFileInput = wrap.find('input');
				var fileFrame = wp.media({
					frame: 'select',
					editing : false,
					multiple : false,
					library: {
						type: 'image'
					},
					selection : ""
				});

				fileFrame.on('open', function() {
					var selection = fileFrame.state().get('selection'),
						ids = [selectedFileInput.val()];
					ids.forEach( function( id ) {
						let attachment = wp.media.attachment( id );
						selection.add( attachment ? [ attachment ] : []);
					});
				});

				fileFrame.on('select', function() {
					let selection = fileFrame.state().get('selection').first();
					
					let template = wp.template(`drplus-dropzone-current-value`),
						html = template({
							img: selection.attributes.url,
							filename: selection.attributes.filename,
							size: selection.attributes.filesizeHumanReadable
						});
					wrap.find('.drplus-dropzone-current-value').remove();
					$(html).appendTo(wrap);
					
					selectedFileInput.val(selection.attributes.id).trigger('change');
				});

				fileFrame.open();
			});
		}
	});
})(jQuery);