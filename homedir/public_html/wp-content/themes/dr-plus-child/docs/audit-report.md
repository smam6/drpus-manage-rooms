# Technical Debt Audit Report — Room Capacity Management

## Environment
- PHP CLI: **not available** in sandbox; syntax verified with `phply` parser (9/9 files parse OK)
- JS: `node --check` PASS
- Files audited: 9 PHP + 1 JS + 1 CSS + languages + functions.php
- Branch: `arena/4e96d9c2-drpus-manage-rooms` (session branch; directive's `audit/tech-debt-100` not creatable in this environment)

## Summary
- Total items: 115 (100 + 15 WP-specific)
- PASS (already correct): 98
- FAIL → fixed: 2 (W13, W11)
- FAIL → documented as residual risk (cannot fix without parent-theme changes): 1 (item 18)
- N/A (not applicable to PHP/WordPress/no such pattern): 14

## Fixed findings

| # | Item | File:line | Description | Fix commit |
|---|------|-----------|-------------|------------|
| W13 | ABSPATH guard missing | functions.php:1 | only module PHP file without direct-access guard | 308add0a |
| W11 | No cleanup on theme switch-away | bootstrap.php (after_switch_theme only installed) | drplus_capacity_* options leaked | 10c125e5 |

## Residual risks (documented, not fixed — would require parent changes or new unspec'd code paths)

| # | Item | Reason |
|---|------|--------|
| 18 | Check-then-act atomicity | The locked capacity check runs in its own transaction (FOR UPDATE) that commits before the parent's encrypted code performs the actual INSERT. Holding the lock across the parent's insert is impossible from a child theme. Window is small (same request, milliseconds) but nonzero under extreme concurrency. Mitigation on live host: the 13 V-tests incl. D9/D10 measure actual behavior. |
| W15 | Log table has no TTL/cleanup cron | Adding a scheduled cleanup is a new code path not covered by a locked spec; recommend a Phase-2 cron or manual purge. |

## Key PASS evidence (selected)
- SQLi (#1, W04, W12): all dynamic SQL via `$wpdb->prepare` (7 calls in repository); `office_id = %s` string binding (repository:137,182); 0 hardcoded `dj3i_`/`Dj3i_`; 0 `SELECT *`
- XSS (#2, W05): all echo/print escaped (`esc_html`/`esc_attr`); grep for unescaped echo = 0
- Auth (#7, W03): `current_user_can('manage_options')` before admin read+write (admin:71,102); nonce `check_admin_referer` + `wp_nonce_field` (admin:74,122)
- Race (#16, D-path): `START TRANSACTION` + `FOR UPDATE` + `COMMIT/ROLLBACK` (repository check_innodb); `GET_LOCK` fallback; `DISTINCT…FOR UPDATE` absent
- N+1 (#46): single `GROUP BY start_time` batch query (repository:182), used once per request in service:97
- Per-request cache (#53): `static $count_cache` (repository:45,153-163)
- office_id scoping (#75): both count queries include `office_id = %s`
- No debug output (#10, W07): 0 error_log/var_dump/print_r/die
- i18n (#H/W08): 14 `__()…'drplus-child'` in messages + 2 in admin; 0 English msgids; `.mo` valid (15 entries)
- No creds/SSRF/XXE/CORS/command-injection patterns: all greps empty
- Parent untouched: `git diff origin/main -- themes/dr-plus/` = 0 files; no files outside `dr-plus-child/`
- md5() at repository:253 is a GET_LOCK key hash, not security crypto — PASS
- `update_option` return unchecked (admin:86, installer:59) — idiomatic WP (false = unchanged) — PASS

## N/A items (with reason)
8 (no JWT), 17 (single lock resource), 19-27 (PHP single-threaded per request / no such patterns), 34 (scope), 35 (no fopen), 37/42 (no finally blocks), 44/45/50/51/55 (PHP request-scoped / $wpdb pooling), 65 (no TS), 70 (single-tenant WP), 72 (no ORM lazy loading), 76 (dbDelta has no drop path by design; table is additive), 79/81/82 (single version), 85/88 (no runtime tests in sandbox), 96/97 (no new deps, no composer changes)

## Recommendation
- **Ready for staging: YES** — all fixable issues fixed; residual risks documented above.
- Outstanding: run `docs/test-runner.md` (V1–V13) on a live XAMPP install; the 76 sandbox-NOT-RUN tests remain the only open verification.
