# Phase B — Category 4: Race Condition (Specs 4.1–4.12)

Parent evidence base: table engine InnoDB (`mysql/aghazga1_new.sql:47066`) → `SELECT … FOR UPDATE` strategy; composite index on `(specialist_id, office_id(255), date, start_time, order_status)` at `sql:47060`; checkout insert happens at/after `class-wc-checkout.php:1411`.

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 48 | 4.1 | Engine MUST be detected once per request via `SHOW TABLE STATUS LIKE {$wpdb->prefix}drplus_booking` and cached in a static var | code review + runtime log | VERIFIED (InnoDB confirmed in dump) |
| 49 | 4.2 | InnoDB path MUST run `START TRANSACTION` → `SELECT specialist_id … FOR UPDATE` → decision → explicit `COMMIT`/`ROLLBACK` | scenario D4 (2 concurrent POSTs: exactly 1 succeeds) | VERIFIED (InnoDB, `sql:47066`) |
| 50 | 4.3 | The `FOR UPDATE` query MUST include `AND deleted = 0` and the excluded statuses | grep query | VERIFIED (locked: blocking for race test; Verification 1) |
| 51 | 4.4 | `DISTINCT` MUST never be combined with `FOR UPDATE`; locked rows are counted in PHP via `array_unique(wp_list_pluck($rows,'specialist_id'))` | grep forbidden combo | VERIFIED (MySQL restriction; spec 1.5) |
| 52 | 4.5 | MyISAM fallback MUST use `GET_LOCK('drplus_capacity_'.md5(office|date|time), 5)` then `RELEASE_LOCK()` | engine-spoof test | VERIFIED (fallback; current engine InnoDB) |
| 53 | 4.6 | If lock acquisition fails or times out, validator MUST reject with `lock_failed` code and Persian message, and log the attempt | timeout simulation | VERIFIED |
| 54 | 4.7 | Transaction MUST wrap count + decision atomically; the actual booking insert stays in the parent flow (we never insert bookings) | code review | VERIFIED (read-only feature) |
| 55 | 4.8 | Rollback strategy MUST be documented in a code comment where `START TRANSACTION` begins | grep comment | VERIFIED (Section 5.6 spec 94) |
| 56 | 4.9 | Phantom reads MUST be impossible: the same `(office_id, date, start_time)` rows are locked by `FOR UPDATE` keyed on the composite index | concurrency test D4 asserts final DB count == capacity | VERIFIED (`sql:47060`) |
| 57 | 4.10 | Race test MUST fire 2 concurrent HTTP POSTs (curl_multi / parallel fetch), never sequential | test script review | VERIFIED (Section 3.6, V-C3) |
| 58 | 4.11 | Exactly 1 of the 2 concurrent bookings MUST succeed and `{$wpdb->prefix}drplus_capacity_log` MUST get exactly 1 new row | scenario D4 assertions | VERIFIED |
| 59 | 4.12 | If engine detection fails, default MUST be the `GET_LOCK` path (MyISAM-safe), never bare `FOR UPDATE` | failure-injection test | VERIFIED (Section 5.6 spec 97) |

## Phase C — v1.0.1 Race Condition Fix (Specs 4.13–4.17)

Load-test evidence (mock, 1000 concurrent): the 4.2–4.12 check-then-act design commits the
count transaction BEFORE the parent's INSERT, so every concurrent request sees `count < capacity`
(audit residual #18, measured: 1000/1000 successes at capacity 2). Closed by layered locking:

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 60 | 4.13 | Validator MUST acquire `GET_LOCK('drplus_cap_'.md5(office_id\|date\|start_time), 5)` at `woocommerce_checkout_process` (priority 5) for in-person bookings; on timeout reject with Persian "system busy" notice and log `lock_failed` | locked-v2 mock T7/T10 | VERIFIED (locked-v2 mock: T7 2/998, T10 10/10, T8 20 total) |
| 61 | 4.14 | Lock MUST be released only AFTER the parent's INSERT: on `woocommerce_checkout_order_processed` priority 9999, or on rejection immediately, or on `shutdown` as failsafe — NEVER before the INSERT | code review + locked-v2 mock | VERIFIED (locked-v2 mock: T7 2/998, T10 10/10, T8 20 total) |
| 62 | 4.15 | On `woocommerce_checkout_order_processed`, re-count the slot; if count > capacity, soft-delete THIS order's booking row (`deleted=1, order_status='cancelled'`), log reason `compensating_rollback`, cancel the WC order with Persian notice | code review + mock layer-2 assertion | VERIFIED (locked-v2 mock: T7 2/998, T10 10/10, T8 20 total) |
| 63 | 4.16 | Lock names MUST be `'drplus_cap_' . md5(...)` (44 chars) — direct concatenation of office_id\|date\|time is forbidden (>64 chars limit) | unit: build_lock_name length | VERIFIED (locked-v2 mock: T7 2/998, T10 10/10, T8 20 total) |
| 64 | 4.17 | `DrPlus_Child_Capacity_Repository::count_distinct_specialists()` MUST be lock-free/transaction-free; the validator MUST call it instead of `check_slot_with_lock()` (kept, deprecated) | grep call sites | VERIFIED (locked-v2 mock: T7 2/998, T10 10/10, T8 20 total) |
