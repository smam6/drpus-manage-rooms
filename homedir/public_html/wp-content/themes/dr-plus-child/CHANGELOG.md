# Changelog — dr-plus-child Room Capacity Management

All notable changes to the Room Capacity module are documented here.
Format follows Keep a Changelog; versions follow SemVer.

## [1.0.3] — 2026-11-20

### Fixed
- **«اصلا کلا برای هیچ متخصصی اجازه رزرو نمیده» — no specialist could be booked at all, while every specialist's appointment list was empty.** Two independent causes; the second is new here.
  - **Abandoned checkouts pinned hours forever.** The customer reaches checkout, the parent INSERTs the booking row, then the payment fails or the tab closes. The row sits at `pending`/`on-hold`. The parent has a cron that cancels those after 30 minutes — but this module cannot assume it runs (wp-cron disabled, no traffic to trigger it, purged schedule). When it does not run, the unpaid row held its hour indefinitely, and it is *invisible* in the appointments list because the parent filters `pending` out. Every abandoned attempt pinned one more hour, so the damage grew with use. `row_holds_slot()` now releases a `pending`/`on-hold` row older than `abandoned_checkout_ttl()` (default 2 h — 4× the parent's window, filterable, floored at 30 min so it can never be set to "hold forever").
  - **Real appointments are still never released by age.** `processing`, `completed`, and any status outside `pending`/`on-hold` hold exactly as before, so no double-booking path was opened.
  - **`created_at` was missing from three SELECTs** that feed `row_holds_slot()`. The age guard could not read the clock and silently answered "still holding" — the fix looked correct and did nothing. Caught by the new suite, not by inspection.
  - **`created_at` carries no timezone, and the parent has written it both ways** (UTC via its ORM, site-local via `current_time()`). Reading it as the wrong zone shifts the age by the host's UTC offset, which on this site is 3.5 h — enough to release live payments or pin dead ones. The guard now takes the *later* of the two interpretations, so a mismatch can only extend a hold, never cut one short. An unreadable timestamp keeps holding (fail closed).

### Added
- Test suite `13-abandoned-checkout.php` (11 assertions) wired into `verify.sh` as step 14/15.
- `version-check.php`: two assertions for the above (34 total). One of them drives `row_holds_slot()` with two rows differing only in age, so a regression in the timestamp path fails on the host rather than shipping silently.

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
