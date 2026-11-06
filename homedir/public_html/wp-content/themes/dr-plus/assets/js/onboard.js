(function($) {
	$(document).ready(function(){
		// Upload new avatar
		$('#drplus-edit-avatar').on('click', function(e) {
			e.preventDefault();
			let wrap = $(this).closest('.drplus-edit-avatar-wrap-row'),
				selectedFileInput = wrap.find('#account_avatar_id');
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
				var selection = fileFrame.state().get('selection');
				let attachment = wp.media.attachment( selectedFileInput.val() );
				selection.add( attachment ? [ attachment ] : []);
			});

			fileFrame.on('select', function() {
				var selection = fileFrame.state().get('selection').first();

				selectedFileInput.val(selection.attributes.id);
				// Show avatar
				wrap.find('.drplus-edit-avatar-wrap img').attr('src', selection.attributes.url).removeAttr('srcset');
			});

			fileFrame.open();
		});
		$('.drplus-delete-avatar-icon').on('click', function(e) {
			e.stopPropagation();
			$('#account_avatar_id').val('');
			$(this).siblings('img').attr('src', drplusVars.defaults.avatar).removeAttr('srcset');
		});

		function checkFormError() {
			let active = true;
			$('.onboard-form *:is(input, select, textarea)[required]').each(function() {
				if(!$(this).val() || $(this).closest('.error').length) {
					active = false;
				}
			});
			if(active) {
				$('#onboard-submit').removeClass('disabled')
			} else {
				$('#onboard-submit').addClass('disabled')
			}
		}

		function setError(fieldset, error = '') {
			if(error) {
				fieldset.find('.input-error-text').text(error);
				fieldset.addClass('error');
			} else {
				fieldset.removeClass('error');
			}
		}

		$(document).on('change', '.onboard-form input, .onboard-form textarea, .onboard-form select', function() { // Check for empty required fields
			let $input = $(this),
				value = $input.val(),
				fieldset = $input.closest('.input-group'),
				error = $input.prop("required") && !value;
			if( error ) {
				setError(fieldset, drplusOnboard.i18n.requiredField)
			}
			if(!error) { // Validate fields
				if( $input.is("[type=email]") && value && !drplus.validateEmail(value)) { // Email
					error = true;
					setError(fieldset, drplusOnboard.i18n.wrongEmail)
				} else if( $input.hasClass('drplus-nid-input') && value && !drplus.validateIDCode(value) ) {
					error = true;
					setError(fieldset, drplusVars.i18n.wrongIDCode);
				} else if( $input.hasClass('drplus-phone-input') && value && !drplus.validateMobile(value) ) {
					error = true;
					setError(fieldset, drplusVars.i18n.wrongMobile);
				}
			}
			if( !error ) {
				setError(fieldset)
			}
			checkFormError()
		});

		checkFormError();
		let dropzones = {};
		if($('.drplus-dropzone').length) {
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

		// Search
		$('.onboard-search').on('input', function() {
			let $this = $(this),
				term = $this.val(),
				errorElement = $this.closest('.onboard-search-wrap').find('.onboard-search-error'),
				body = $this.closest('.onboard-subsection-body'),
				items = body.find('.checkbox-box'),
				currentValues = [];

			errorElement.hide();
			body.find('.onboard-search-item').remove()
			if(term.length > 2) {
				body.find('.onboard-search-item').remove();
				items.hide();
				// Fill currentValues
				items.find('input:checked').each(function() {
					currentValues.push($(this).val())
				});

				// Send AJAX
				$.ajax({
					url: drplusVars.ajaxUrl,
					type: 'POST',
					data: {
						action: 'drplus_search_onboard',
						text: term,
						nonce: $this.attr('data-nonce'),
						type: $this.attr('data-type'),
						current_values: JSON.stringify(currentValues),
					},
					success: function(res) {
						if( res.success ) {
							$(res.data.html).appendTo(body);
							res.data.ids.forEach(function(id) {
								let duplicateIDs = body.find(`.checkbox-box[data-id="${id}"]`);
								if( duplicateIDs.length > 1) {
									duplicateIDs.slice(1).remove();
								}
								body.find(`.checkbox-box[data-id="${id}"]`).css('display', 'grid')
							});
						} else {
							errorElement.text(res.data).show();
						}
					}
				});
			} else {
				body.find('.checkbox-box').show();
			}
		})

		$(document).on('change', '.onboard-search-item input', function() {
			$(this).parent().removeClass('onboard-search-item');
		})

		$(document).on('click', '.repeater-remove', checkFormError);

		///// Custom off days in reserve settings  /////
		// init datepicker for off days
		let pdDatePickerConfigs = {
			inline: false,
			format: drplusOnboard.i18n.add,
			minDate: new Date().getTime(),
			viewMode: "day",
			initialValue: false,
			autoClose: true,
			position: "auto",
			altFormat: 'X',
			onlySelectOnDate: true,
			calendarType: drplusVars.isRtl ? 'persian' : 'gregorian',
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
				let template = wp.template(`specialist_off_day_item_template`);
				let html = template({
					text: date,
					value: unix/1000
				});
				$(html).insertBefore(`#specialist_add_off_days`);
			}
		};

		$('#specialist_add_off_days').mjpersianDatepicker(pdDatePickerConfigs);

		$(document).on('click', `.specialist_remove_custom_off_day`, function() {
			$(this).closest(`.specialist_custom_off_day`).fadeOut({
				complete: function() {
					$(this).remove();
				}
			});
		});
	});
})(jQuery);