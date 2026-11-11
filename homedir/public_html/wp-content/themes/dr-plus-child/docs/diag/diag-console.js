/* Dr Plus Capacity Browser Diagnostics (Read-Only, D7 + D10)
 * Open the booking page in your browser, open DevTools -> Console,
 * paste this entire script and press Enter.
 * Optional: set `window.__diagTestDate = 'YYYY-MM-DD'` beforehand to test a specific date.
 */
(async function () {
	'use strict';

	function sleep(ms) {
		return new Promise(function (resolve) { setTimeout(resolve, ms); });
	}

	// Wait for jQuery up to 5 seconds (50 x 100ms).
	var waitedMs = 0;
	while (typeof window.jQuery === 'undefined' && waitedMs < 5000) {
		await sleep(100);
		waitedMs += 100;
	}

	var $ = window.jQuery || null;
	if ($ && typeof $.isReady === 'boolean' && !$.isReady) {
		await new Promise(function (resolve) {
			var timer = setTimeout(resolve, Math.max(0, 5000 - waitedMs));
			$(function () {
				clearTimeout(timer);
				resolve();
			});
		});
	}

	function safeClone(val) {
		if (typeof val === 'undefined') return 'UNDEFINED';
		try {
			return JSON.parse(JSON.stringify(val));
		} catch (e) {
			return 'UNSERIALIZABLE: ' + String(e);
		}
	}

	function toYmd(unixVal) {
		var num = Number(unixVal);
		if (!isFinite(num) || num <= 0) return '';
		var ms = num > 1e12 ? num : num * 1000;
		var d = new Date(ms);
		if (isNaN(d.getTime())) return '';
		var y = d.getFullYear();
		var m = String(d.getMonth() + 1).padStart(2, '0');
		var day = String(d.getDate()).padStart(2, '0');
		return y + '-' + m + '-' + day;
	}

	var result = {
		meta: {
			timestampIso: new Date().toISOString(),
			waitedForJQueryMs: waitedMs,
			url: window.location.href,
			title: document.title,
			readyState: document.readyState,
			hasJQuery: !!$,
			jQueryVersion: ($ && $.fn && $.fn.jquery) ? $.fn.jquery : null
		},
		globals: {
			drplusBooking: safeClone(window.drplusBooking),
			drplusCapacity: safeClone(window.drplusCapacity),
			drplusVars: safeClone(window.drplusVars)
		},
		loadedScripts: Array.from(document.scripts).map(function (s) {
			return {
				src: s.src || null,
				defer: !!s.defer,
				async: !!s.async,
				type: s.type || null
			};
		}).filter(function (s) {
			return s.src && /dr-plus|capacity|booking|persian|pdp|datepicker|litespeed|rocket|autoptimize|jquery/i.test(s.src);
		}),
		dom: {},
		persianDatepicker: {},
		warning: {},
		bookingDateBeforeAjax: null,
		ajaxTest: {},
		bookingDateAfterAjax: null
	};

	if ($) {
		var $form = $('form#booking-form');
		var $wrap = $('.booking_wrap');
		var $warn = $('.drplus-capacity-date-warning');
		var $calWrap = $('.booking-calendar-wrap');
		var dp = $calWrap.length ? $calWrap.data('datepicker') : null;

		var bookingDateBefore = $('#booking-date').length ? $('#booking-date').val() : null;
		result.bookingDateBeforeAjax = bookingDateBefore;

		var docEvents = null;
		var formEvents = null;
		try {
			var de = $._data ? $._data(document, 'events') : null;
			if (de && de.submit) {
				docEvents = de.submit.map(function (ev) { return { selector: ev.selector || null, namespace: ev.namespace || null }; });
			}
			var fe = ($form.length && $._data) ? $._data($form[0], 'events') : null;
			if (fe && fe.submit) {
				formEvents = fe.submit.map(function (ev) { return { selector: ev.selector || null, namespace: ev.namespace || null }; });
			}
		} catch (e) {
			docEvents = 'ERROR: ' + String(e);
		}

		result.dom = {
			bookingFormCount: $form.length,
			bookingFormAction: $form.attr('action') || null,
			bookingFormMethod: $form.attr('method') || null,
			bookingWrapCount: $wrap.length,
			bookingWrapSection: $wrap.attr('data-section') || null,
			bookingDateInputs: $('#booking-date, input[name="booking_date"], .booking-date-calendar').map(function () {
				return {
					tag: this.tagName,
					id: this.id || null,
					name: this.name || null,
					type: this.type || null,
					className: this.className || null,
					val: $(this).val(),
					attrVal: this.getAttribute('value'),
					required: !!this.required,
					disabled: !!this.disabled
				};
			}).get(),
			bookingTimeVal: $('#booking-time').length ? $('#booking-time').val() : null,
			bookingTimeNonce: $('#booking-time').attr('data-nonce') || null,
			checkedOffice: $('.booking-specialist-office-radio:checked').val() || null,
			allOffices: $('.booking-specialist-office-radio').map(function () {
				return {
					id: this.id || null,
					val: this.value,
					checked: !!this.checked,
					disabled: !!this.disabled
				};
			}).get(),
			nextBtnClass: $('.booking-next-step-btn').attr('class') || null,
			nextBtnDisabledProp: $('.booking-next-step-btn').prop('disabled'),
			emptySlotNoticeVisible: $('.booking_time_empty_slot_notice').is(':visible'),
			errorAlertVisible: $('.booking-times-error-alert').is(':visible'),
			renderedTimeSlotsCount: $('.booking-time-slot').length,
			renderedTimeSlots: $('.booking-time-slot').map(function () {
				return {
					time: $(this).find('.booking-time-slot-time').text().trim(),
					label: $(this).find('.booking-time-slot-label').text().trim(),
					timestamp: $(this).attr('data-timestamp') || null,
					disabled: $(this).hasClass('disabled'),
					selected: $(this).hasClass('selected')
				};
			}).get(),
			documentSubmitHandlers: docEvents,
			formSubmitHandlers: formEvents
		};

		result.warning = {
			exists: $warn.length > 0,
			count: $warn.length,
			hiddenProp: $warn.length ? $warn.prop('hidden') : null,
			hiddenAttr: $warn.length ? $warn.attr('hidden') : null,
			isVisible: $warn.length ? $warn.is(':visible') : false,
			computedDisplay: $warn.length ? window.getComputedStyle($warn[0]).display : null,
			text: $warn.length ? $warn.text().trim() : null
		};

		var dpModelSummary = null;
		if (dp && dp.model) {
			try {
				dpModelSummary = {
					options: {
						inline: dp.model.options ? dp.model.options.inline : null,
						initialValue: dp.model.options ? dp.model.options.initialValue : null,
						altField: dp.model.options ? dp.model.options.altField : null,
						altFormat: dp.model.options ? dp.model.options.altFormat : null,
						calendarType: dp.model.options ? dp.model.options.calendarType : null,
						observer: dp.model.options ? dp.model.options.observer : null,
						minDate: dp.model.options ? dp.model.options.minDate : null,
						maxDate: dp.model.options ? dp.model.options.maxDate : null
					},
					stateSelected: dp.model.state && dp.model.state.selected ? {
						unixDate: dp.model.state.selected.unixDate,
						year: dp.model.state.selected.year,
						month: dp.model.state.selected.month,
						date: dp.model.state.selected.date
					} : null,
					stateView: dp.model.state && dp.model.state.view ? {
						unixDate: dp.model.state.view.unixDate,
						year: dp.model.state.view.year,
						month: dp.model.state.view.month,
						date: dp.model.state.view.date
					} : null
				};
			} catch (e) {
				dpModelSummary = { error: String(e) };
			}
		}

		result.persianDatepicker = {
			hasPersianDateFn: typeof window.persianDate === 'function',
			hasMjPersianDatepickerPlugin: !!($.fn && $.fn.mjpersianDatepicker),
			globalPdType: typeof window.pd,
			calendarWrapCount: $calWrap.length,
			calendarWrapHasDataDatepicker: !!dp,
			datepickerContainersCount: $('.datepicker-container, .datepicker-container-inline, .datepicker-plot-area').length,
			datepickerSelectedDays: $('.datepicker-plot-area td.selected').map(function () {
				return {
					text: $(this).text().trim(),
					dataUnix: $(this).attr('data-unix') || null,
					disabled: $(this).hasClass('disabled')
				};
			}).get(),
			datepickerModel: dpModelSummary
		};

		// Determine date/office/specialist for D10 AJAX test.
		var office = $('.booking-specialist-office-radio:checked').val()
			|| (window.drplusBooking && (window.drplusBooking.selectedOffice || window.drplusBooking.mainOffice))
			|| '';
		var specialist = (window.drplusBooking && window.drplusBooking.specialistID)
			|| $('input[name="booking_specialist_id"]').val()
			|| '';
		var chunk = (window.drplusBooking && window.drplusBooking.chunkTimes && window.drplusBooking.chunkTimes[office]) || '';
		var nonce = $('#booking-time').attr('data-nonce') || '';

		var testDate = '';
		var dateSource = '';
		if (window.__diagTestDate) {
			testDate = String(window.__diagTestDate);
			dateSource = 'window.__diagTestDate';
		} else if (bookingDateBefore && toYmd(bookingDateBefore)) {
			testDate = toYmd(bookingDateBefore);
			dateSource = '#booking-date';
		} else if (dpModelSummary && dpModelSummary.stateSelected && toYmd(dpModelSummary.stateSelected.unixDate)) {
			testDate = toYmd(dpModelSummary.stateSelected.unixDate);
			dateSource = 'datepicker.state.selected.unixDate';
		} else if (window.drplusBooking && window.drplusBooking.nearestDateTimestamps && window.drplusBooking.nearestDateTimestamps[office]) {
			testDate = toYmd(window.drplusBooking.nearestDateTimestamps[office]);
			dateSource = 'drplusBooking.nearestDateTimestamps[' + office + ']';
		} else {
			testDate = toYmd(Date.now());
			dateSource = 'fallback-today';
		}

		var ajaxUrl = (window.drplusVars && window.drplusVars.ajaxUrl) || '/wp-admin/admin-ajax.php';
		var payload = {
			action: 'drplus_get_available_times',
			date: testDate,
			office: String(office),
			specialist: String(specialist),
			chunk: String(chunk),
			nonce: String(nonce)
		};

		result.ajaxTest = {
			ajaxUrl: ajaxUrl,
			dateSource: dateSource,
			requestPayload: payload,
			httpStatus: null,
			rawResponse: null,
			parsedResponse: null,
			error: null
		};

		try {
			var body = new URLSearchParams(payload);
			var response = await fetch(ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: body.toString()
			});
			var rawText = await response.text();
			result.ajaxTest.httpStatus = response.status;
			result.ajaxTest.rawResponse = rawText;
			try {
				result.ajaxTest.parsedResponse = JSON.parse(rawText);
			} catch (parseErr) {
				result.ajaxTest.parsedResponse = null;
			}
		} catch (fetchErr) {
			result.ajaxTest.error = String(fetchErr);
		}

		result.bookingDateAfterAjax = $('#booking-date').length ? $('#booking-date').val() : null;
	} else {
		result.ajaxTest = {
			skipped: true,
			reason: 'jQuery did not become ready within 5000ms'
		};
	}

	window.__drplusDiag = result;
	console.log('=== DRPLUS DIAG OUTPUT ===\n' + JSON.stringify(result, null, 2));
	return result;
})();
