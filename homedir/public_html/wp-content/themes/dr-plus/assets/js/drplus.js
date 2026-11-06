// This file is loaded on both side (backend(WP Dashboard) and frontend)
(function($) {
	$(document).ready(function(){
		// Phone
		$(document).on('input', ".drplus-phone-input", function() {
			// Cache the jQuery object for the input to avoid repeated DOM lookups
			const $input = $(this);

			// Remove all non-digit characters and limit the input to 11 digits
			let value = drplus.convertChars($input.val()).trim().replace(/\D/g, '');
			if (value.startsWith('98')) value = '0' + value.slice(2);
			value = value.slice(0, 11);

			// Format the value into the #### ### #### pattern dynamically
			value = value.replace(/^(\d{4})(\d{0,3})(\d{0,4})$/, (_, a, b, c) => {
				// Join the captured groups (a, b, c) with spaces, filtering out empty groups
				return [a, b, c].filter(Boolean).join(' ');
			});

			// Update the input field with the formatted value
			$input.val(value);
		});

		// Card Number
		$(document).on('input', ".drplus-card-number-input", function() {
			// Cache the jQuery object for the input to avoid repeated DOM lookups
			const $input = $(this);

			// Remove all non-digit characters and limit the input to 11 digits
			let value = drplus.convertChars($input.val()).trim().replace(/\D/g, '').slice(0, 16);

			// Format the value into the #### #### #### #### pattern dynamically
			value = value.replace(/^(\d{4})(\d{0,4})(\d{0,4})(\d{0,4})$/, (_, a, b, c, d) => {
				// Join the captured groups (a, b, c, d) with spaces, filtering out empty groups
				return [a, b, c, d].filter(Boolean).join(' ');
			});

			// Update the input field with the formatted value
			$input.val(value);
		});

		// Shaba Number
		$(document).on('input', ".drplus-shaba-number-input", function() {
			// Cache the jQuery object for the input to avoid repeated DOM lookups
			const $input = $(this);

			// Remove all non-digit characters and limit the input to 16 digits
			let value = drplus.convertChars($input.val()).trim().replace(/\D/g, '').slice(0, 24);

			// Format the value into the IR## #### #### #### #### #### ## pattern dynamically
			value = value.replace(/^(\d{2})(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,2})$/, (_, a, b, c, d, e, f, g) => {
				// Join the captured groups (a, b, c, d, e, f, g) with spaces, filtering out empty groups
				return [a, b, c, d, e, f, g].filter(Boolean).join(' ');
			});

			// Update the input field with the formatted value
			$input.val(`IR${value}`);
		});

		// Slug input
		$(document).on('input', '.drplus-slug-input', function() {
			const $input = $(this);

			let value = drplus.convertChars($input.val())
				.trimStart()
				.toLowerCase()
				.replaceAll(' ', '-')
				.replaceAll('-_', '-')
				.replaceAll('_-', '-')
				.replaceAll('--', '-')
				.replace(/[^a-z0-9_-]/g, '');

			$input.val(value);
		});

		// Numeric
		$(document).on('input', '.drplus-numeric-input', function() {
			const $input = $(this);

			let value = drplus.convertChars($input.val())
				.trim()
				.replace(/[^0-9]/g, '');

			$input.val(value);
		});

		// Date picker
		let pdDefaultConfigs = {
			inline: false,
			format: "DD MMMM YYYY",
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
					enabled: true,
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
		};
		if($('.drplus-datepicker-input').length) {
			let pdDatePickerConfigs = {...pdDefaultConfigs}; // Create clone without reference
			pdDatePickerConfigs.toolbox.calendarSwitch.enabled = false;

			$('.drplus-datepicker-input').each(function() {
				let thisOptions = {...pdDatePickerConfigs};

				let options = $(this).attr('data-options') ?? "{}";
				thisOptions = $.extend( thisOptions, JSON.parse( options ) );

				if($(this).next().is('input[type="hidden"]')) {
					thisOptions.altField = $(this).next()[0];
				}
				let pd = $(this).mjpersianDatepicker(thisOptions);
				if(parseInt($(this).attr('data-time')) > 0) {
					pd.setDate($(this).attr('data-time')*1000);
				}
			})
		}
		
		$(document).on('input', '.drplus-price-input', function() {
			$(this).val(drplus.formatPrice($(this).val()));
		})

		drplus.initSelect2();
		drplus.initProvinceSelector();

		// Theme toggle button
		$('.drplus_theme_toggle_label').on('click', function() {
			var current = drplusTheme.get();
			var next = current === 'dark' ? 'light' : 'dark';
			drplusTheme.set(next);
			$('.drplus_theme_toggle_checkbox').prop('checked', next === 'dark');
		});
		$('.drplus_theme_toggle_checkbox').prop('checked', $('body').hasClass('is-dark'));
	});
})(jQuery);