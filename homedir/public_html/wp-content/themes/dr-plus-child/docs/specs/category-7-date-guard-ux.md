# Phase B — Category 7: Date Guard & UX (Specs 7.1–7.13)

Parent evidence base (Verification A): date selected on time step via calendar → hidden `#booking-date` (`template-booking-step-time.php:210`; `booking.js:45` altField); `#booking-date` usually pre-filled server-side (Finding 2); parent only toggles `.disabled` class on `.booking-next-step-btn` (`booking.js:241-247`) with NO submit handler on time step (Finding 1 — real gap); button is `type="submit"` (`template-booking-nav-btns.php:21-25`); no Persian warning exists (Q5); instant chat hides calendar (`booking.js:16-24`).

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 82 | 7.1 | Client guard MUST delegate `$(document).on('submit', '#booking-form', handler)` — a real submit blocker, not a class toggle | V8: clear `#booking-date`, press Enter → no POST, warning shown | VERIFIED (fills Finding 1 gap; parent has no time-step submit handler) |
| 83 | 7.2 | If `#booking-date` value is empty, handler MUST `event.preventDefault()` and show Persian warning `«لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.»` | V8 DOM assertion | VERIFIED (locked string) |
| 84 | 7.3 | Warning element MUST be created once, inserted near `#booking-date`/calendar, class `drplus-capacity-date-warning`, RTL styled | DOM check | VERIFIED |
| 85 | 7.4 | Warning MUST clear when `#booking-date` changes to non-empty (`on('change')`) | V8 + select date → warning gone | VERIFIED (spec 95 of 5.5.B) |
| 86 | 7.5 | Guard MUST apply to the time step for in-person visits; for `instant_chat_consultation` (calendar hidden) the guard MUST be skipped | booking.js:16-24 hides calendar for instant chat | VERIFIED |
| 87 | 7.6 | Guard MUST NOT rely on `.booking-next-step-btn` class toggle (insufficient per Finding 1) | code review | VERIFIED |
| 88 | 7.7 | Server-side, on `template_redirect` for booking time-step POST with valid nonce and empty `booking_date`, MUST respond `WP_Error('missing_date', 'لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.', ['status' => 400])` | curl POST without date → HTTP 400 | VERIFIED (new; parent only has generic alert `template-booking-step-info.php:34`) |
| 89 | 7.8 | Server-side message MUST be byte-identical to client-side string | string compare test | VERIFIED (locked) |
| 90 | 7.9 | Checkout-level missing date MUST also be rejected in `woocommerce_checkout_process` via `wc_add_notice` (spec 3.6) | scenario D11 | VERIFIED |
| 91 | 7.10 | JS strings MUST come from `wp_localize_script('drplus-capacity', 'drplusCapacity', …)` — no hardcoded Persian in JS | grep JS for Persian chars = 0 | VERIFIED (Section 5.5 spec 87-88) |
| 92 | 7.11 | Guard JS MUST be enqueued only on the booking page (page id option `booking-page-id` or shortcode `drplus_booking`) | asset source check on other pages | VERIFIED (`utils-booking.php:91-97`; shortcode `inc/Shortcodes/shortcode-booking.php`) |
| 93 | 7.12 | All warning UI MUST be RTL, responsive at 375px, and accessible (role=alert, aria-live) | mobile viewport + a11y check (V4) | VERIFIED |
| 94 | 7.13 | Guard MUST NOT break the parent's normal flow when a date IS selected (page advances normally) | regression V4 | VERIFIED |
