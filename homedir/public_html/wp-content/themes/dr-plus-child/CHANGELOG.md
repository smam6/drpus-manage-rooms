# Changelog — dr-plus-child Room Capacity Management

All notable changes to the Room Capacity module are documented here.
Format follows Keep a Changelog; versions follow SemVer.

## [1.0.4] — 2026-11-06

### Fixed — defects introduced by 1.0.3, and the workflow gaps behind them

1.0.3 fixed the reported symptom and, in doing so, created a worse one. This release
starts from the operator's real database and follows the whole journey — book, abandon,
pay, cancel, expire, rebook — rather than the one case that was reported.

- **A PAID appointment was released after two hours, so the same specialist could be booked twice.** 1.0.3 decided "abandoned checkout" by reading `drplus_booking.order_status`. But that column is written **once**, when the parent INSERTs the row — which happens *before* the gateway settles — and nothing in the parent ever updates it on payment. The normal happy path is therefore `row.order_status = 'pending'` while `order.status = 'processing'`. After the TTL, 1.0.3 saw `pending`, called it abandoned, and handed a **paid** hour back to the pool: two patients, one room, both charged. Reproduced on a rebuilt database (the slot reopened at exactly 120 minutes) and present in the operator's own data (`book #61` / order `#2947`). The predicate now asks the **order** before calling anything abandoned; a row only counts as an unfinished checkout when the order is also still unpaid.
- **`on-hold` was about to become a permanent hold.** WooCommerce's `on-hold` means "waiting for payment", so it must remain releasable by age. Treating it as paid made an abandoned manual/BACS checkout pin its hour forever — the original bug in a new costume. Caught by the existing suite, which failed on the first attempt at the fix.
- **A hand-entered appointment (no order) was erased after two hours.** The TTL exists for checkouts, and a checkout row always has an order. A row with `order_id = 0` is an appointment the operator typed in or an import; there is no payment to time out. The site's database contains one, and its hour was being silently released. The TTL now applies only to rows that actually came from a checkout.
- **One clinic took two different booking locks.** Counting collapses a clinic's numeric id and its custom-office UUIDs onto one canonical id, but the lock hashed whatever string it was handed. Two customers booking the same hour through different spellings of the same clinic took two different `GET_LOCK` names, both passed the capacity count concurrently, and the clinic accepted `capacity + 1`. Only the compensating rollback stopped it — and that rollback **cancels the customer's paid order with no refund path**. The lock is now derived from the same canonical id the counter uses, so lock and count agree by construction.
- **The grid and the checkout disagreed about which hours are taken.** `count_day_slots()` (which builds the day picker) was a bare `COUNT(DISTINCT specialist_id)` in SQL. That form cannot call `row_holds_slot()` — it never selects `created_at` or `order_id`, which is exactly what the predicate needs — so every case the predicate releases still counted as taken on the grid, and every case it keeps could be under-counted. Proven divergences on a rebuilt database: a 4-hour-old abandoned `pending`, a stale copied status, and a row pointing at a non-existent order each reported `grid = 1, checkout = 0`. The symptom is the reported one: a day whose hours are missing from the picker. Both surfaces now run the same row-level predicate.
- **Released rows kept masquerading as appointments.** Releasing the hour inside this module is only half the job: the parent's appointments list, dashboard counters and per-specialist counts filter the copied column directly. Five abandoned attempts at one hour produced **five phantom appointments** in the operator's list; a sixth that really was paid made six, with no way to tell which was real. An expired hold now marks its row `cancelled` (and cancels the still-unpaid order), so every reader agrees. Verified: 5 phantoms → 0, the real appointment untouched.
- **`created_at` was still missing from three SELECTs.** 1.0.3 fixed three call sites; three more (`count_day_slots`, `specialist_booked_slots_for_day`, `conflict rows`) selected the status and the order but not the row id or clock, so the retirement step could not identify the row it had just decided to free. Same class of defect, same fix: select the columns the predicate consumes.
- **A compact date was read as a Unix timestamp.** `20271201` is numeric, so it went through the timestamp path and became **`1970-08-23`** — a date in the past, where the capacity check finds nothing and allows the booking. A booking for 2027-12-01 therefore passed with no room check at all. Eight-digit values that form a real calendar date are now read as dates; `raw_date` still declares itself explicitly as a timestamp.

- **The day grid re-did per-request work once per ROW.** `resolve_clinic_identifiers()` re-read the specialists table and the posts/postmeta alias index for every row, so a 300-row day issued 9 identical read pairs instead of one. Not a correctness bug, but it multiplies a fixed cost by the number of bookings in a day — the number that grows with the clinic's success. Measured on a rebuilt database: 30 rows went from 30 queries to 12, and a 300-row day renders in 91 queries instead of 120+. Order lookups are cached per request too, and the cache is flushed after any status write this module makes.

### Changed
- `woocommerce_order_status_changed` now copies the order's status onto its booking rows. The capacity predicates still ask the order directly (they must not depend on this hook), so this is a repair of the drifted column, not a new dependency. It is what keeps the parent's lists and the parent's own cron honest.
- Test suite `13-abandoned-checkout.php` grew from 11 to 28 assertions, covering every defect above with the exact shape that reproduced it.
- New suite `15-lifecycle.php` (12 assertions) walks one hour through book → abandon → pay → full → cancel → expire → rebook and asserts the capacity answer at every step.
- New suite `16-query-budget.php` (3 assertions) pins the query cost of one day-grid render, so the per-row regression cannot come back unnoticed.
- `verify.sh` is now 17 steps.
- `version-check.php`: 36 assertions (was 33). The new ones drive the real predicates — an aliased clinic must take one lock, a paid order must never be released by age, a hand-entered row must never be aged out.

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
