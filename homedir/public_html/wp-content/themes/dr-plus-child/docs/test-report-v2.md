# Test Report v2 — Runtime Verification Attempt (Honest Evidence Record)

## Environment (actual sandbox capabilities, measured 2026-11-07)

- Docker: **not available** (`docker: command not found`)
- PHP: **not available** (`php: command not found`; cannot install — outbound network restricted to github/npm/pypi)
- MySQL/MariaDB: **not available**
- Node.js: **v22.22.3 available**
- Python: **3.11.2 available** (+ `phply` PHP parser installed from PyPI)

**Decision per directive Section 2.3:** no real WordPress runtime is possible in this
sandbox. Executed instead: (a) real PHP **parse** of every module file via phply
(equivalent of `php -l`), (b) **real execution** of `assets/js/capacity.js` in Node with
mocked jQuery/DOM, (c) static/grep evidence for hook wiring, SQL, i18n, and hygiene,
(d) schema validation against the real SQL dump. Tests requiring HTTP/MySQL/browser are
marked **NOT RUN (no runtime)** — not claimed as passed.

## Results Summary

- Total tests defined: 100
- **Executed with real evidence: 24** (all PASS)
- **NOT RUN — no PHP/MySQL/browser runtime in sandbox: 76** (documented reason, not claimed)

## Executed tests — evidence

| ID | Result | Evidence |
|----|--------|----------|
| A04 (static part) | PASS | phply parse of all 9 PHP files: `PARSE-OK` ×9, 0 parse errors (`php -l` equivalent) |
| A08 | PASS | all 8 module files parse; classes declared in each file (bootstrap requires all 7 class files) |
| A10 | PASS | `bootstrap.php:81` — `add_filter('drplus/booking/available_time_slots', [Service,'filter_full_slots'], 20, 2)` verified by grep |
| B10/B11 (static) | PASS | service `filter_full_slots` ends with `array_values($filtered)`; slot shape untouched |
| B13/B14/B15 (static) | PASS | all 3 count queries in repository contain `deleted = 0` and `order_status NOT IN (...)` (grep count = 3) |
| C06/C08/C09 (static) | PASS | validator reads `date`→`raw_date` and `start_time`→`raw_time` fallbacks (class-capacity-validator.php extract methods) |
| D04/D05 (static) | PASS | `FOR UPDATE` present (grep = 3); `DISTINCT…FOR UPDATE` combo absent (grep = 0); `START TRANSACTION`/`COMMIT`/`ROLLBACK` present |
| D07 (query design) | PASS | batch query `GROUP BY start_time` = 1 (no N+1); `$wpdb->prepare` used 7× |
| E01 (static) | PASS | bootstrap enqueues `capacity.js` only on booking page, versioned `DRPLUS_CHILD_VERSION` |
| E03/E04 | **PASS (real JS execution)** | Node harness ran the actual capacity.js: empty `#booking-date` → `preventDefault()` called, warning shown with Persian text |
| E05 | **PASS (real JS execution)** | warning element text = `لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.` (asserted in harness) |
| E06 | **PASS (real JS execution)** | after `change` with date set, warning `hidden=true` |
| E07 | **PASS (real JS execution)** | valid date → submit NOT prevented |
| E-extra | **PASS** | `instant_chat_consultation` office → guard skipped; non-time section → guard skipped |
| F01 (static) | PASS | admin page registered under `options-general.php`, slug `drplus-room-capacity`, capability `manage_options` |
| F06/F07 (static) | PASS | save handler rejects values <1 or >99 without saving (`MIN_CAPACITY/MAX_CAPACITY` check) |
| G07/G08 (static) | PASS | `wp_mail`/`wp_notify` absent from module (grep = 0) |
| H01/H02 | PASS | exact Persian strings present in class-capacity-messages.php |
| H04 | PASS | 13 msgids in messages class, 0 containing English (python scan) |
| H08 | PASS | `fa_IR.mo` valid binary (magic 0x950412de, 15 entries) |
| H09 | PASS | all user strings via `__('…','drplus-child')` (14 domain usages in messages class + 2 in admin) |
| I08 | PASS | `git diff --name-only origin/main -- themes/dr-plus/` = 0 files |
| I09 | PASS | `git diff --name-only origin/main` outside `dr-plus-child/` = 0 files |
| I10 | PASS | `dj3i_`/`Dj3i_` hardcode in `inc/`+`assets/` = 0 matches |
| Schema A06/A07 | PASS | dump confirms `Dj3i_drplus_booking` has all 15 columns incl. `deleted`, `order_status`; `ENGINE=InnoDB`; installer dbDelta defines all 11 capacity_log columns |

## NOT RUN (76 tests) — reason: no PHP/MySQL/HTTP/browser runtime in this sandbox

Categories A (HTTP load), B03–B09/B12 (need DB+AJAX), C01–C15 (need WooCommerce checkout),
D01–D03/D06/D08–D10 (need DB + concurrent HTTP), E02/E09/E10 (need browser),
F02–F05/F08–F10 (need admin session), G01–G06/G09/G10 (need DB), H05–H07/H10 (need browser),
I01–I07 (need live site).

These are executable via `docs/test-runner.md` on a real XAMPP/staging install.

## Conclusion

All checks executable in this sandbox pass (24/24). The feature logic's client-side guard
was verified by real execution; PHP was verified by full parse + structural evidence; schema
was verified against the production dump. Runtime behavior on a live WordPress install
remains to be verified with `docs/test-runner.md` — honestly NOT RUN here.
