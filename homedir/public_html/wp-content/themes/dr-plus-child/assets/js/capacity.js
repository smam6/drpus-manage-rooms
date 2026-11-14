/**
 * Dr Plus Child Theme — Room Capacity Management (client)
 *
 * Implements: spec 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.10, 5.5.87, 5.5.88, 5.5.93, 5.5.94, 5.5.95
 * Category: 7 — Date guard & UX
 *
 * Real submit blocker for the booking time step: the parent only toggles a CSS
 * class on .booking-next-step-btn (assets/js/booking.js of the parent), which
 * does not stop a native form submit. This module blocks the submit itself.
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $form = $('#booking-form');
		if (!$form.length || typeof drplusCapacity === 'undefined') {
			return;
		}

		// Create the warning element once, near the calendar (spec 7.3).
		var $warning = $('<div>', {
			'class': 'drplus-capacity-date-warning',
			'role': 'alert',
			'aria-live': 'polite',
			'text': drplusCapacity.i18n.noDate,
			'hidden': true
		});
		$('.booking-calendar-wrap').first().after($warning);

		// Second warning for the time step, next to the slot list (spec 7.3).
		// #booking-time is a HIDDEN input, so the `required` attribute on it is
		// ignored by native constraint validation — an empty hour would submit
		// and only the server could catch it. Block it here first.
		var $timeWarning = $('<div>', {
			'class': 'drplus-capacity-date-warning',
			'role': 'alert',
			'aria-live': 'polite',
			'text': drplusCapacity.i18n.noTime,
			'hidden': true
		});
		$('.booking-times-wrap').first().before($timeWarning);

		// Delegate the submit event: a real blocker, not a class toggle (spec 7.1, 7.6).
		$(document).on('submit', '#booking-form', function (event) {
			var section = $('.booking_wrap').attr('data-section');
			if (section !== 'time') {
				return; // Guard applies to the time step only.
			}
			var office = $('.booking-specialist-office-radio:checked').val();
			if (office === 'instant_chat_consultation') {
				return; // Instant chat hides the calendar; no date needed (spec 7.5).
			}
			if (!$('#booking-date').val()) {
				event.preventDefault();
				$warning.prop('hidden', false); // Persian warning (spec 7.2).
				return;
			}

			// Hour guard: mirrors the server-side fail-closed time check (spec 7.1).
			if (!$('#booking-time').val()) {
				event.preventDefault();
				$timeWarning.prop('hidden', false);
			}
		});

		// Clear the warning as soon as a date is chosen (spec 7.4).
		$(document).on('change', '#booking-date', function () {
			if ($(this).val()) {
				$warning.prop('hidden', true);
			}
		});

		// Clear the time warning once an hour is picked. The parent fills the
		// hidden input from a delegated click, so listen on change + click.
		$(document).on('change click', '#booking-time', function () {
			if ($(this).val()) {
				$timeWarning.prop('hidden', true);
			}
		});
	});
})(jQuery);
