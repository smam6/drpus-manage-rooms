# FINAL QA REPORT — Dr Plus Capacity Module

Date: 2026-11-16
Branch: `arena/f1a92a51-drpus-manage-rooms`
Parent commit: `b6faa952`

---

## 0. Method, and why you should trust these numbers

Every test below **executes the shipped classes** — `DrPlus_Child_Capacity_Repository`,
`DrPlus_Child_Capacity_Validator`, `DrPlus_Child_Capacity_Service`,
`DrPlus_Child_Capacity_Admin` — loaded verbatim from `inc/capacity/`, with WordPress,
WooCommerce and `$wpdb` replaced by stubs. No logic was copied into the tests.

The `$wpdb` stub records **every query issued**, so assertions like "no SQL was executed"
are measured, not assumed.

Each suite was executed **three times in one PHP process**. That matters: static caches and
`$wpdb` state survive between runs inside a single request, which is the only way a
repeat-loop can expose a leak. Results were identical in all three runs.

**Totals: 80 behavioural assertions, run 3× = 240 checks, 0 failures.**
Plus 15 admin save/retrieve assertions, and 11/11 files parse under `TOKEN_PARSE`.

Test artifacts (not shipped): `/tmp/qa/build.py` (harness), `/tmp/qa/tests.php` (suites),
`/tmp/qa/harness3.php` (3× runner), `/tmp/qa/admin_test.php`.

---

## 1. Test results

### Suite A — Jalali date guard (fail-closed) — 13/13 PASS

| # | Assertion | Observed |
|---|---|---|
| A1 | `assert_queryable_date('1405-08-28')` → `''` | `''` |
| A1b | ...and issues **no SQL** | 0 queries |
| A2 | `'۱۴۰۵-۰۸-۲۸'` (Persian digits) → `''` | `''` |
| A3 | `'2026-11-19'` → unchanged | `'2026-11-19'` |
| A4 | `''` → `''` | `''` |
| A5 | Jalali through public counter → `999` sentinel | `999` |
| A5b | ...no `COUNT(DISTINCT)` SQL issued | 0 count queries |
| A6 | Persian-digit Jalali through counter → `999` | `999` |
| A7 | `extract_date(['date'=>'1405-08-28'])` → `''` | `''` |
| A7b | ...Persian-digit variant → `''` | `''` |
| A8 | `2026-02-30`, `2026-13-01`, `2026-00-10` → `''` | all `''` |

### Suite B — Specialist double-booking (Bug C) — 11/11 PASS

DB precondition: specialist 11 holds `2026-11-19 14:00:00`.

| # | Assertion | Observed |
|---|---|---|
| B3 | exactly one notice raised | 1 |
| B3b | **exact** message «این متخصص در این ساعت قبلاً رزرو دارد. لطفاً ساعت دیگری را انتخاب کنید.» | byte-identical match |
| B3c | not the generic capacity message | confirmed |
| B4 | `COUNT(DISTINCT ...)` never ran (short-circuit) | 0 queries |
| B4b | `GET_LOCK` never acquired | confirmed |
| B5 | different specialist, same hour → allowed | 0 notices |
| B6 | same specialist, different hour → allowed | 0 notices |
| B7 | cancelled booking does not block | 0 notices |
| B8 | same specialist, **another room**, same hour → blocked | 1 notice |
| B9 | `specialist_id = 0` fails **closed** | returns `true` |

### Suite C — UUID vs numeric clinic (Bug B) — 13/13 PASS

Alias map: `UUID_A → 614`, `UUID_B → 614`.

| # | Assertion | Observed |
|---|---|---|
| C4 | bookings at `614` + `UUID_A` → count **2** | 2 |
| C4b | same count entered via `UUID_A` | 2 |
| C4c | same count entered via `UUID_B` | 2 |
| C4d | `UUID_B` resolves to all 3 identifiers | 3 ids |
| C6 | 3rd specialist via `UUID_B` → **rejected** | 1 notice |
| C6b | rejection is the capacity message | confirmed |
| C7 | unrelated clinic `596` NOT merged | count 0 |
| C8 | no alias map → only exact id counts (safe default) | 1 |
| C8b | ...and numeric clinic does **not** see the UUID booking | 0 |
| C9 | `capacity_for('614')` | 2 |
| **C10** | **clinic limit 3 read via UUID A** | **3** (was 2 — see §3) |
| C10c | ...via UUID B | 3 |
| C10d | clinic limit 1 via UUID A | 1 (would have allowed 2) |
| C11 | 3-of-3 rooms via UUID B → allowed | 0 notices |

### Suite D — Slot filtering (frontend) — 9/9 PASS

`filter_full_slots()` with `office_id = UUID_B`, 2 bookings at 14:00 on the clinic.

| # | Assertion | Observed |
|---|---|---|
| D3 | 14:00 slot **removed**, not disabled | remaining `13:00,15:00` |
| D4 | 13:00 kept | yes |
| D4b | 15:00 kept | yes |
| D4c | exactly 2 slots remain | 2 |
| D4d | re-indexed via `array_values` | keys `0,1` |
| D5 | numeric entry point behaves identically | 14:00 removed |
| D6 | parent-marked `available=false` left untouched | kept |
| D7 | Jalali date → empty list (fail-closed display) | 0 slots |
| D8 | `phone_consultation` slots untouched | 3 slots |

### Suite E — Time normalization (Bug D) — 12/12 PASS

| # | Assertion | Observed |
|---|---|---|
| E1 | `'14:00'` finds the `14:00:00` booking | count 1 |
| E1b | SQL bound `'14:00:00'` | confirmed |
| E1c | never bound the unpadded value | confirmed |
| E2 | `'14:00:00'` → count 1 | 1 |
| E3 | `raw_time = 50400000` ms → `14:00:00` | `14:00:00` |
| E3b | sub-second noise → rejected | `''` |
| E4 | `'1405'`, `'abc'`, `'25:00'`, `'14:61'`, `''` → `''` | all `''` |
| E5 | unusable time through counter → `999` | 999 |
| E6 | validator passes `14:00:00` down to SQL | confirmed |

### Suite F — Online visit exemption — 10/10 PASS

| # | Assertion | Observed |
|---|---|---|
| F | `phone_consultation` exempt | 0 notices, **0 SQL** |
| F | `chat_consultation` exempt | 0 notices, 0 SQL |
| F | `video_consultation` exempt | 0 notices, 0 SQL |
| F | `consultation` (legacy alias) exempt | 0 notices, 0 SQL |
| F5 | `instant_chat_consultation` **NOT** exempt → blocked | 1 notice |
| F5b | `is_online_visit('instant_chat_consultation')` | `false` |

> Note on the directive: it asked to assert `validate_booking_capacity()` "returns `true`".
> The method is declared `: void` and cannot return anything, so that assertion is
> unexpressible. It is asserted by observable behaviour instead (no notice, no SQL), which is
> the stronger check.

### Suite G — extra hardening, beyond the directive — 8/8 PASS

| # | Assertion | Observed |
|---|---|---|
| G1 | lock timeout → rejected, never counts unlocked | 1 notice |
| G2 | 2nd of 2 rooms → allowed (boundary) | 0 notices |
| G3 | 3rd of 2 rooms → rejected (boundary) | 1 notice |
| G4 | cancelled / refunded / failed excluded | count 0 |
| G5 | soft-deleted rows excluded | count 0 |
| G6 | adjacent date not counted | count 0 |
| G7 | clinic with capacity 1 rejects the 2nd | 1 notice |
| G8 | a remapped alias resolves to its configured group | confirmed |

---

## 2. Zero-regression audit

| Check | Method | Result |
|---|---|---|
| Parent theme untouched | `git diff-index --quiet HEAD -- themes/dr-plus` over all **1952** tracked files | **PASS** — zero differences |
| `functions.php` | byte-compare vs `HEAD` | **PASS** — unchanged, still only the `require_once` |
| `ChildInit.php` | byte-compare | **PASS** |
| `style.css` | byte-compare | **PASS** |
| `screenshot.png` | byte-compare | **PASS** |
| `languages/fa_IR.mo` / `.po` / `.l10n.php` / `.pot` | byte-compare | **PASS** (all 4) |
| `assets/css/capacity.css`, `assets/js/capacity.js` | byte-compare | **PASS** |
| Only permitted files changed | `git status --porcelain` filtered to `inc/capacity` + `assets` | **PASS** — 1 file (`class-capacity-service.php`) |

> **Caveat, stated plainly:** this checkout is a shallow clone with a single commit, so
> `git diff origin/main` returns empty for *everything* and proves nothing. The audit above
> uses `git diff-index` against the committed tree plus per-file byte comparisons, which is
> real evidence. The one file legitimately modified this round is
> `inc/capacity/class-capacity-service.php` (the §3 fix); `version-check.php` is added to the
> same directory.

### Alias Map admin round-trip — 15/15 PASS

Exercising the real `DrPlus_Child_Capacity_Admin::handle_save()` and `render_page()`:

| # | Assertion | Observed |
|---|---|---|
| P1 | two UUIDs saved from one comma-separated field | 2 stored |
| P1b/c | both mapped to `614` | yes |
| P1d | capacity saved alongside aliases | 2 |
| P2 | resolver reads back 3 identifiers | 3 |
| P2b | both saved UUIDs in the group | yes |
| P3 | numeric value **rejected** (no clinic merge) | 0 stored |
| P3b | malformed uuid rejected | 0 stored |
| P4 | uppercase UUID lowercased | saved |
| P5/P5a/P5b | non-admin save aborts via `wp_die`, options unchanged | aborted |
| P6 | both UUIDs re-displayed after reload | yes |
| P6b | field named per clinic | `drplus_capacity_alias[614]` |
| P7 | UI-saved map yields combined count 2 via UUID B | 2 |

---

## 3. Defects found and fixed during this QA pass

### 3.1 REAL DEFECT — capacity was read from the wrong identifier

`capacity_for()` looked up `get_option( 'drplus_capacity_clinic_' . $office_id )` using the
raw id. The settings page stores the limit against the **clinic post id**, so a booking
arriving through a custom-office **UUID** missed the setting and silently fell back to the
default. Reproduced before the fix:

```
capacity via 614   : 3      <- clinic really allows 3 rooms
capacity via UUID  : 2      <- default, silently enforced instead
```

Impact is bidirectionally wrong: a clinic configured for 3 over-blocks at 2, and a clinic
configured for **1** would **allow 2** through a UUID — an overbooking hole created by the
Bug B fix itself. Fixed by resolving aliases first and taking the first configured value.
Now covered by C10/C10b/C10c/C10d/C11 and by a live assertion in `version-check.php`.

### 3.2 Harness bug — phantom SQL attributed to the wrong test

`qa_reset()` cleared the query log **before** releasing the advisory lock, so a deferred
`RELEASE_LOCK` from the previous test landed in the next test's log and failed
"no SQL was issued". Caught by the 3× loop. Fixed by releasing first, then clearing.
Verified load-bearing: restoring the old order reproduces the failure (79/80).

### 3.3 Harness bug — `wp_die()` stub did not terminate

A stub that merely echoed let execution continue past `current_user_can()` checks, inverting
the result of the authorization test. Made faithful (throws), so "non-admin is refused" is
now actually measured.

### 3.4 Three test premises were wrong, not the code

C8/E2 never populated the mock booking table, so `0` was the correct answer; F5 put the
bookings on `614` instead of the `instant_chat_consultation` office, making a genuinely empty
hour look "not blocked". Premises corrected — the code was right in all three cases. Flagged
rather than silently "fixed", because a test whose premise is wrong can hide a real bug.

---

## 4. `version-check.php` now executes assertions

Previously it only grepped for marker strings — which cannot tell you whether the code
*behaves*. It now also runs 8 live assertions against the loaded classes and prints the
observed value for each:

| Assertion | Observed on a correct install |
|---|---|
| Jalali date is refused | `assert_queryable_date('1405-08-28')` returned `''` |
| Jalali in Persian digits is refused | returned `''` |
| Gregorian date passes through | returned `'2026-11-19'` |
| `14:00` → `14:00:00` | returned `'14:00:00'` |
| Bare number is not a time | `normalize_time('1405')` returned `''` |
| Alias map is configured | `N alias(es) configured; … resolves to M identifier(s)` |
| Alias resolution is symmetric | same set from every entry point |
| Capacity identical via clinic id and UUID | `capacity_for(614)=2 vs capacity_for(<uuid>)=2` |

All are read-only: no rows written, no booking created.

**Sensitivity proven.** Running the same check against the pre-fix `capacity_for()` reports
`capacity_for(614)=3 vs capacity_for(<uuid>)=2` → **FAIL**. It detects the real defect; it is
not a substring test wearing a costume.

### Known limitation of the same page

The 6 file-marker checks read files from disk. In my sandbox they cannot read the host
filesystem, so they report FAIL there — a harness artifact, not a code fault. On your server
they read the real files and are valid. The 8 behaviour checks do not touch disk and are
unaffected.

---

## 5. Deliverable

- ZIP: `dr-plus-child.zip` — 22 files, top folder `dr-plus-child`, hash-verified against
  source, SHA-256 recorded in `SHA256.txt`.
- Persian guide: `راهنمای-نصب-از-صفر-فارسی.md`, including the Alias Map section.

## 6. What still requires a human

These cannot be closed offline, and I am not claiming they are:

1. **Real `book_data['specialist_id']`.** Every Bug C test assumes the field is populated. If
   the live cart sends `0`, the check fails closed and blocks **all** bookings (B9 documents
   this). Scenario 1 passing is the signal that the payload is intact.
2. **The alias map must be filled in on the live site.** With it empty, `version-check.php`
   shows the "Clinic alias map is configured" assertion as FAIL by design, and Bug B is not
   mitigated.
3. **The six live scenarios.** Code correctness is established here; production confirmation
   is not.

---

# ADDENDUM — Dynamic capacity (future bookings only)

Rule: a capacity change applies to **future** bookings only. Existing bookings are never
cancelled or altered, and a new limit takes effect on the next booking attempt with no cache
flush.

**Verdict: no code changes were required.** `capacity_for()` reads the option on every call;
there is no static, transient or object cache anywhere in the capacity module.

## Why it is correct by construction

| Check | Finding |
|---|---|
| Static/memo cache in the module | **None.** Only 4 statics exist: held locks, diag flag, request id, and a per-request "already rejected" guard — none of them capacity values |
| `wp_cache_*` / transients for capacity | None (the only transient is the admin success notice text) |
| Alias map cached | No — `load_clinic_aliases()` calls `get_option()` on every resolution |
| Options autoloaded | Both stored with `$autoload = false`, so they are not even in the `alloptions` blob |
| Persistent object cache drop-in (`object-cache.php`) | **Absent** on this install → `get_option()` is a real DB read per request |
| Anything reacting to a settings change | **No** `update_option_*` / `updated_option` hook, no cron, no background job |
| Anything that cancels existing bookings | Only `rollback_this_order()`, which is scoped to the order currently being placed |

## Suite H results — 32/32, stable across 3 runs

| Group | Assertions | Result |
|---|---|---|
| **H0 — no memoisation** | same id re-read 3× in a row sees 5, then 1, then 4; UUID path sees the change; removing/restoring an alias takes effect immediately | 7/7 |
| **H1 — increase 2→3** | 3rd specialist rejected at 2; **allowed at 3 in the same request** | 2/2 |
| **H2 — decrease 3→2 with 3 existing** | 4th specialist rejected before *and* after the reduction; count still reports 3 | 4/4 |
| **H3 — no retroactive cancellation** | rejected attempt issues **0** UPDATE/INSERT; 3 rows byte-identical; all still `completed`, none soft-deleted; no `update_option_`/cron hook in the shipped source | 6/6 |
| **H4 — 0 / empty / negative / unset / non-numeric** | all fall back to the default 2, no crash; a 0-capacity clinic still accepts a booking at the default | 6/6 |
| **H5 — real admin save flow** | `handle_save()` → next `capacity_for()` sees the new value in the **same request**, via both the clinic id and a UUID; raising the limit lets the 4th booking through on the very next validation | 7/7 |

### Sensitivity proof

The suite is not passing by construction. Injecting a one-line `static $memo` cache into
`capacity_for()` — code that does not exist in the shipped file — produces **17 failures**,
including every H0/H5 assertion. With the shipped code: **0 failures**.

### Two test bugs found and fixed during this suite

- `qa_attempt()` counted the **cumulative** notice log, so a rejection in one attempt was
  re-counted in the next and faked two failures (H1b, H2b). The code was correct.
- A helper was placed before `<?php` and got stripped during harness assembly, producing a
  fatal that looked like a code break.

## Practical note for the operator

Bookings are **counted**, not stored per capacity. Reducing capacity from 3 to 2 leaves three
active bookings in a 2-room hour; the module reports the count as 3 and refuses any further
booking for that hour. Nothing is cancelled, and the over-capacity state is visible rather
than hidden. This is the requested behaviour.
