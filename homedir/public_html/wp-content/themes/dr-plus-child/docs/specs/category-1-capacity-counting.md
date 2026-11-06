# Phase B — Category 1: Capacity Counting (Specs 1.1–1.15)

Parent evidence base: booking table schema `mysql/aghazga1_new.sql:47043-47066` (15 columns, 7 indexes, ENGINE=InnoDB); parent slot query `dr-plus/inc/Utils/utils-booking.php:424-431` (no `deleted` filter — Verification 1); model `dr-plus/inc/Models/Booking.php` (no `$softDeletes`); prefix `Dj3i_` from `wp-config.php:77` via `$wpdb->prefix`.

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 1 | 1.1 | Every capacity counting query MUST be built with `$wpdb->prepare()` and MUST include `AND deleted = 0` plus `AND order_status NOT IN ('cancelled','refunded','failed','trash')` | `php -l` + grep for `deleted = 0` in `inc/capacity/class-capacity-repository.php` | VERIFIED (schema `sql:47043-47066`; parent gap `utils-booking.php:424-431`) |
| 2 | 1.2 | `office_id` MUST be bound as string `%s` because the column is `tinytext` holding both numeric clinic ids ('596') and online ids ('phone_consultation') | grep `%s` binding for office_id; schema `sql:47043` (`office_id tinytext`) | VERIFIED |
| 3 | 1.3 | Booking date MUST be normalized to `Y-m-d` before any query (accept `Y-m-d` or unix `raw_date`) | unit check with inputs `2099-05-20` and unix ts | VERIFIED (dual keys `template-booking-step-checkout.php:33`, Verification B Q3) |
| 4 | 1.4 | Start time MUST be normalized to `H:i:s` before any query (accept `H:i:s`, `H:i`, or `raw_time` ms-since-midnight) | unit check normalizer | VERIFIED (dual keys `template-booking-step-receipt.php:259-260`, Verification B Q4) |
| 5 | 1.5 | Display-mode count MUST use `COUNT(DISTINCT specialist_id)`; the `FOR UPDATE` path MUST count in PHP via `array_unique(wp_list_pluck($rows,'specialist_id'))` — never `DISTINCT … FOR UPDATE` | grep forbidden combo; race test D4 | VERIFIED (MySQL rejects DISTINCT+FOR UPDATE; locked rule) |
| 6 | 1.6 | Excluded statuses MUST be exactly the class constant `['cancelled','refunded','failed','trash']` | grep constant on repository class | VERIFIED (theme statuses `utils-booking.php:25-35`) |
| 7 | 1.7 | `deleted = 0` MUST be present in every counting query, including the race query | grep all queries; test V6 | VERIFIED (Verification 1: parent never filters `deleted`; `Models/Booking.php` has no soft-delete) |
| 8 | 1.8 | Capacity scope MUST be per `(office_id, date, start_time)` — never per specialist alone | review repository WHERE clause | VERIFIED (index `sql:47060` covers this tuple) |
| 9 | 1.9 | A specialist's own existing booking at the same hour MUST count toward capacity (distinct-specialist count includes them; only the new booking is blocked) | scenario test D7 | VERIFIED (business rule: default capacity 2 specialists/hour) |
| 10 | 1.10 | `NULL`/empty `office_id` MUST never match a clinic capacity rule (column is NOT NULL per schema) | schema check `sql:47043` | VERIFIED |
| 11 | 1.11 | "Today" comparisons MUST use `wp_timezone()`-based date, never server-local time | code review; parent uses `wp_timezone()` at `utils-booking.php:361, 389` | VERIFIED |
| 12 | 1.12 | Date comparisons MUST be string-based `Y-m-d`, never unix-timestamp diffs (DST-safe) | code review | VERIFIED |
| 13 | 1.13 | Slot filtering MUST fetch all hour counts of the day in ONE `GROUP BY start_time` query — no N+1 | query log during slot request | VERIFIED (locked rule: no N+1) |
| 14 | 1.14 | Repeated identical counts within one request MUST be cached in a static array on the repository class | code review + micro-benchmark | VERIFIED (Section 5.6 spec 90) |
| 15 | 1.15 | Capacity limit MUST be read via `get_option('drplus_capacity_clinic_' . $office_id, DRPLUS_ROOM_CAPACITY_DEFAULT)` filtered by `drplus_child_capacity/per_clinic` | admin save test V-C8; option default 2 | VERIFIED (locked decision #1, #7) |
