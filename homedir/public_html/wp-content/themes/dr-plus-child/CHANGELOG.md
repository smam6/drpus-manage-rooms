# Changelog — dr-plus-child Room Capacity Management

All notable changes to the Room Capacity module are documented here.
Format follows Keep a Changelog; versions follow SemVer.

## [1.0.2] — 2026-11-20

### Fixed
- **False «این متخصص در این ساعت قبلاً رزرو دارد» for a specialist with no bookings.** Reported by the operator: checkout refused an hour the specialist was free for. Reproduced end to end, then fixed.
  - Cause: the cart item often arrives without `specialist_id`, so `extract_booking_from_request()` fell through to the session. `$_SESSION['booking']['specialist_id']` still held the *previous* doctor — the parent only rewrites it when the booking wizard runs again, and going back one step and then straight to checkout skips that. The module checked the stale doctor, found **that** doctor's booking, and blamed the one the customer had actually chosen.
  - Fix: an id taken from the session is now marked `specialist_id_is_inferred`. It still counts towards room capacity, but a conflict is never reported against it — the customer sees the «متخصص این نوبت مشخص نشد…» notice, which asks them to re-select the specialist and actually repairs the state. A genuine booking against a specialist the cart *does* name is still refused exactly as before, so no double-booking path was opened.
  - Also fixed: when the cart and the session name different specialists, the cart now wins. It is customer-authored; the session is a leftover.
- `version-check.php` gained three assertions for the above (32 total), driving the real rescue rather than grepping for the flag name.

### Added
- `tools/host-probe/why-blocked.php` (not shipped in the theme zip): a read-only admin diagnostic that prints every booking row for a given day with the exact reason it holds or frees the slot, plus the query the validator runs. For pinning any future refusal to a specific `book_id` instead of guessing.

## [1.0.1] — 2026-11-07

### Fixed
- Added ABSPATH direct-access guard to `functions.php` (audit W13).
- Module options (`drplus_capacity_%`) are now removed when the site switches away from the child theme; log table kept for data retention (audit W11).
- **Race condition closed (audit residual #18, specs 4.13–4.17):** a MySQL advisory lock (`GET_LOCK('drplus_cap_'.md5(slot), 5)`) is now acquired at `woocommerce_checkout_process` and held across the parent theme's booking INSERT, released on `woocommerce_checkout_order_processed` (priority 9999) or on `shutdown`. A post-insert compensating check re-counts the slot and soft-deletes + cancels this order's booking (`reason = compensating_rollback`) if capacity is exceeded. Load-test mock measured 1000/1000 overbooking before the fix; `locked-v2` mode holds exactly 2/1000 after.

## [1.0.0] — 2026-11-06

### Added
- Per-clinic, per-hour capacity limit on in-person visits (default 2 specialists/hour), configurable via Settings → Room Capacity (`manage_options`).
- Full slots removed entirely from `drplus/booking/available_time_slots` responses (priority 20).
- Server-side validation on `woocommerce_checkout_process` (date check priority 4, capacity check priority 5) — rejects before any booking row is written; Persian notice includes the day's available hours.
- Online visits (`phone_consultation`, `chat_consultation`, `video_consultation`) exempt from capacity.
- Race-safe counting: InnoDB `SELECT … FOR UPDATE` in a transaction; `GET_LOCK` fallback; every count includes `deleted = 0` and excludes cancelled/refunded/failed/trash.
- Blocked-attempt logging to `{$wpdb->prefix}drplus_capacity_log` via dbDelta (no admin notifications).
- Client-side date guard: real submit blocker on `#booking-form` with Persian warning; server-side `missing_date` 400.
- Persian RTL styles + JS (all client strings via `wp_localize_script`), `languages/drplus-child.pot`, `fa_IR.po`, `fa_IR.mo`.
- Docs: installation, configuration, troubleshooting, test report, Phase B 306 specs, Phase C order.

### Notes
- Parent theme untouched; feature is hook-only and survives parent updates.
- Known parent limitation documented in troubleshooting: parent slot generator ignores soft-deleted bookings; server-side validation counts correctly with `deleted = 0`.
