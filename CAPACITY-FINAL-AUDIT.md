# Room Capacity — Final QA Audit Report

**Branch:** `arena/cdd6c83f-drpus-manage-rooms`
**Audited from:** `0560ff6b` (main) → **HEAD:** `84d13aa5` (pushed)
**Scope:** `homedir/public_html/wp-content/themes/dr-plus-child/inc/capacity/`
**Parent theme `dr-plus/`:** read only, **zero modifications** (`git diff 0560ff6b..HEAD -- .../dr-plus/` is empty).

---

## 1. Files Modified

Six files. All under the child theme; no parent-theme, plugin, or database file was touched.

| File | Purpose of change |
|---|---|
| `inc/capacity/class-capacity-repository.php` | Gregorian/time normalization hardened; dead lock code removed |
| `inc/capacity/class-capacity-validator.php` | Fail-closed guards, race guards, orphan duplicate removed |
| `inc/capacity/class-capacity-service.php` | Legacy `consultation` online alias |
| `inc/capacity/class-capacity-messages.php` | Two new Persian strings |
| `inc/capacity/bootstrap.php` | Exposes the new JS string |
| `assets/js/capacity.js` | Client-side hour guard |

```
 assets/js/capacity.js                          |  28 ++
 inc/capacity/bootstrap.php                     |   1 +
 inc/capacity/class-capacity-messages.php       |  25 ++
 inc/capacity/class-capacity-repository.php     | 299 +++++++++----------
 inc/capacity/class-capacity-service.php        |  34 ++-
 inc/capacity/class-capacity-validator.php      | 172 ++++++-----
 6 files changed, 327 insertions(+), 232 deletions(-)
```

---

## 2. Bugs Found and Fixed

Eight defects. Two were **critical** — each allowed a booking to bypass the capacity rule entirely.

### 2.1 Jalali date bypass — **CONFIRMED 100% RESOLVED** *(fixed earlier in `1c90960e`, re-verified here)*

This is the original production bug and the reason for the whole audit. It is **fully closed, in every code path.**

**What was wrong:** the booking payload carries `date` as a Persian/Jalali display string (`28 آبان 1405`) or a Jalali numeric string (`1405-08-28`). That value was passed to `$wpdb->prepare()` and compared to the `date` column, which stores Gregorian `Y-m-d`. Jalali year 1405 sits ~300 years ahead of Gregorian 2026, so the `COUNT()` always matched nothing, returned `0`, and the limit never triggered.

**Fix:** `raw_date` (a Unix timestamp) is now the absolute source of truth, converted with `date('Y-m-d')` — the same convention the parent theme uses at `dr-plus/templates/booking/template-booking-step-receipt.php:321`, which matches the DB rows. The localized `date` key is only a fallback, and it is now rejected unless it parses as a **real Gregorian** date.

**Explicit confirmation — Jalali cannot reach SQL by any route.** Verified by execution, not inspection:

| Attack shape | Result |
|---|---|
| `raw_date` present (live payload) | Gregorian `2026-11-19` used in the COUNT ✓ |
| `1405-08-28` with no `raw_date` | `normalize_date()` → `''` ✓ |
| `28 آبان 1405` | `''` ✓ |
| `1405/08/28` (slash) | `''` ✓ |
| `1405.08.28` (dot) | `''` ✓ |
| `1405-08-28T10:30:00+03:30` | `''` ✓ |
| `1405-08-28 10:30:00` | `''` ✓ |
| Real ISO timestamp | still accepted ✓ |
| Stale parseable `date` vs. correct `raw_date` | `raw_date` wins ✓ |

The last two are the ones that matter: hardening did **not** break legitimate input, and precedence was proved by a control test that fails against the pre-fix code.

**Database corroboration:** all 19 live rows cross-checked — `date('Y-m-d', raw_date)` and `gmdate('H:i:s', raw_time/1000)` reproduce the stored `date`/`start_time` **19/19**.

### 2.2 Time never resolved — every hour was unreadable *(critical, `74ed37e4`)*

`normalize_time()` required the millisecond value to be `>= 86400000` (one day). But `raw_time` is milliseconds **since midnight**, so the largest legal value is `86 399 999`. Every real value therefore failed the guard and no hour ever resolved — the same "count zero, allow everything" failure as the Jalali bug, on a second axis.

Fixed by `time_from_milliseconds()` (`class-capacity-repository.php:211`), which accepts whole-second values in `[60000, 86400000)`. **All 13 distinct live `raw_time` values now resolve correctly** (08:30 … 19:30), each verified against the fixture DB.

### 2.3 Empty hour bypassed capacity *(critical, `2430be76`)*

A cart with a date but no `start_time` matched `start_time = ''`, counted `0`, and was allowed. Now fails closed **before** the advisory lock is acquired, with `لطفاً ابتدا ساعت مراجعه را انتخاب کنید.` and log reason `missing_time`. Proved by a bypass test that previously allowed the order.

### 2.4 Legacy `consultation` counted as in-person *(rule 2, `f16a4d06`)*

The parent still emits `office_id = 'consultation'` as an alias of `phone_consultation` (`class-specialists-reserve.php:114`). It was not exempt, so a phone consultation was counted against — and blocked by — clinic room capacity. `ONLINE_ALIASES` + `normalize_office_id()` (`class-capacity-service.php:38,57`) resolve it before the exemption test.

**Exemption matrix, verified by execution — 8/8:** `phone_consultation`, `chat_consultation`, `video_consultation`, `consultation` → exempt; `instant_chat_consultation`, clinic `614`, clinic `596`, custom UUID → counted. Every exempt path returns **before** any `GET_LOCK` or `COUNT`.

`instant_chat_consultation` is deliberately **not** exempt: it is disabled in the live settings (`booking-active-online-visits` = `""`), so exempting it would silently disable enforcement.

### 2.5 Rejected checkout could re-count without the lock *(`733b7571`)*

On rejection the lock is released immediately. Since `woocommerce_checkout_process` can fire more than once per request, a second pass re-counted with **no lock held** — the exact check-then-act race the lock exists to prevent. Now short-circuits after a rejection. Verified: pass 1 → 1 lock, 1 count, 1 notice; pass 2 → **0 locks, 0 counts**.

### 2.6 Empty hour reached checkout page *(`84d13aa5`)*

`#booking-time` is a **hidden** input. HTML5 constraint validation skips `type=hidden`, so its `required` attribute is inert and the empty-hour case 2.3 fixes could only be caught on the server — as a full-page checkout error. `capacity.js` now blocks it in place with a Persian warning.

### 2.7 Orphaned duplicate validator *(`b87b7f39`)*

`validate_checkout()` had no caller (grep across all `.php`/`.js`). It also skipped `Service::enabled()`, so wiring it later would have enforced capacity on a site where the owner had disabled the module. Removed.

### 2.8 Dead lock code with a broken `prepare()` *(`9009fc04`)*

`check_slot_with_lock`, `check_innodb`, `check_get_lock`, `count_slot`, `slot_where`, `engine()` and two caches were unreachable. One contained `$wpdb->prepare('SELECT GET_LOCK(%s, 5)', array($lock))` — an array passed as a single replacement, which cannot work. Deleted rather than repaired; nothing called it.

### Audit checklist result

| Item | Status |
|---|---|
| **A** Dates converted before `prepare()`; `raw_date`/`raw_time` authoritative | ✅ `date('Y-m-d')` / `gmdate('H:i:s')`, 19/19 DB rows reproduced |
| **B** Full slots removed (not `available=false`); shape `from/to/available/label` kept | ✅ removed + `array_values`; malformed/`available=false` slots passed through untouched |
| **C** Exact exemption strings; early return before lock | ✅ 8/8 matrix; 0 locks on every exempt path |
| **D** Lock released before abort; shutdown failsafe | ✅ full/success/busy/failsafe all traced |
| **E** No diagnostic `error_log()` | ✅ zero matches theme-wide |
| **F** Placeholder counts/types; `deleted = 0` everywhere | ✅ 4 call sites checked; logger formats 10/10 match columns |

**Verification:** PHP parse **9/9 OK**; behavioural suite **28/28 PASS**; lint/JS syntax clean; `node --check` passes.

---

## 3. Deployment Instructions

### Upload these 4 files (overwrite in place)

```
wp-content/themes/dr-plus-child/inc/capacity/class-capacity-repository.php
wp-content/themes/dr-plus-child/inc/capacity/class-capacity-service.php
wp-content/themes/dr-plus-child/inc/capacity/class-capacity-validator.php
wp-content/themes/dr-plus-child/inc/capacity/class-capacity-messages.php
wp-content/themes/dr-plus-child/inc/capacity/bootstrap.php
wp-content/themes/dr-plus-child/assets/js/capacity.js
```

That is the complete list — six files, all in the **child** theme. Do **not** upload anything to `dr-plus/`, and do not upload the rest of the `capacity/` folder (installer, logger, lock-manager, admin and `capacity.css` are unchanged).

### Do NOT rename or delete anything

No file added, removed, or renamed. No database change: the schema version is still `1.0.0` (`DRPLUS_CAPACITY_DB_VERSION`), so no migration runs and the existing `capacity_log` table is reused as-is.

### Cache

**Yes — clear the cache.** The JS is versioned by `DRPLUS_CHILD_VERSION` (`bootstrap.php:135`), so a browser-CDN cache will keep serving the old `capacity.js` until it is purged. Clear:

1. Any page/CDN cache (Cloudflare, LiteSpeed, WP Rocket, etc.).
2. Any object cache (Redis/Memcached) if present.
3. Browser cache on a test machine — hard reload on the booking page.

There is **no** opcache/APCu action required beyond a normal PHP restart, and no persistent-option change to flush.

### Post-deploy smoke test (5 minutes)

1. Open a booking for a clinic with capacity 2.
2. Book the same in-person hour as two different specialists → the hour must **disappear** from the slot list.
3. Attempt a third booking for that hour → must be refused with `ظرفیت این ساعت تکمیل شده است…` listing the remaining hours.
4. Book a `phone_consultation` for that same hour → must **succeed** (online is exempt).
5. On the time step, submit without choosing an hour → inline Persian warning, no page reload, **no order created**.

### Rollback

Revert the six files from `0560ff6b`. No data cleanup needed — the log table is append-only and untouched by rollback.
