# Emergency Deployment — Capacity Fix

## ⚠️ Read this first: the log proves the server is running code that is not on GitHub

The log lines you quoted contain `[DRPLUS COUNT SQL]` and `[DRPLUS CAPACITY]`.

**Those strings have never existed in this repository.** Verified:

```
git log --all -S"DRPLUS COUNT SQL"      → no commit
git log --all -S"DRPLUS FILTER FIRED"   → no commit
grep across every commit and the ZIP    → absent
```

I also checked `themes.zip` (21 MB backup sitting in `themes/`) — it has **no `inc/capacity` directory at all**, so that is not the source either.

**What this means:** the files on your server came from somewhere else — an earlier local build, a different machine, or a previous agent session that was never pushed. No amount of rewriting GitHub code can fix that, because GitHub is not what the server is executing.

The version checker below is designed to prove this in one page load.

---

## Step 1 — Download

**Option A: whole theme (easiest)**  
`https://github.com/smam6/drpus-manage-rooms/raw/arena/dbdffc66-drpus-manage-rooms/dr-plus-child-production.zip`  
857 KB · 21 files · extracts to `dr-plus-child/`

**Option B: individual files (exact)**  
Open this folder and download each file via its **Raw** button:

```
https://github.com/smam6/drpus-manage-rooms/tree/arena/dbdffc66-drpus-manage-rooms/homedir/public_html/wp-content/themes/dr-plus-child/inc/capacity
```

| File | Must be uploaded? |
|---|---|
| `class-capacity-repository.php` | **Yes** — the SQL-boundary guard |
| `class-capacity-validator.php` | **Yes** — Jalali detection |
| `class-capacity-service.php` | **Yes** — fail-closed slot display |
| `version-check.php` | **Yes** — new file, the verifier |
| `class-capacity-messages.php` | No — unchanged since last release |
| `class-capacity-installer.php` | No — unchanged |
| `class-capacity-lock-manager.php` | No — unchanged |
| `class-capacity-logger.php` | No — unchanged |
| `class-capacity-admin.php` | No — unchanged |
| `assets/js/capacity.js` | Only if you have not deployed since 2026-11-14 |

---

## Step 2 — Upload

1. cPanel → **File Manager**
2. Navigate to `public_html/wp-content/themes/`
3. **Before overwriting**, select `dr-plus-child` → right-click → **Compress** → name it `dr-plus-child-BACKUP`. If this deployment goes wrong you can restore in one click.
4. Upload the ZIP (Option A) or the 4 files (Option B)
5. For the ZIP: right-click → **Extract**
6. **Delete the ZIP** afterwards

### ⚠️ Check the extract path

The archive contains a top-level `dr-plus-child/` folder. Extract it into `themes/`, **not** into `themes/dr-plus-child/`.

Wrong result: `themes/dr-plus-child/dr-plus-child/inc/capacity/...`  
Right result: `themes/dr-plus-child/inc/capacity/...`

If you end up nested, the fix is to move the inner folder up one level, not to re-upload.

---

## Step 3 — Clear caches (this is where it usually fails)

Old code survives in **four** independent places. All four must be cleared.

### 3a. WP Rocket / LiteSpeed Cache
- WP Rocket: **Clear and preload cache**
- LiteSpeed: **LiteSpeed Cache → Toolbox → Purge All**

### 3b. Cloudflare
- Dashboard → **Caching → Configuration → Purge Everything**
- If you use "Development Mode", leave it on for the duration of the test

### 3c. PHP OPcache — the usual culprit
Uploaded files do not take effect until OPcache is flushed. PHP caches compiled bytecode and keeps serving it until the process restarts or the cache is cleared.

**Check first** — does OPcache even apply to you?

`version-check.php` prints the validator's `mtime`. If that timestamp is newer than your upload but the page still says FAIL, OPcache is serving stale bytecode.

**How to clear it, easiest first:**

1. **cPanel → PHP → "OPcache" / "PHP OPcache"** — many hosts expose a *Flush* button. One click; done.
2. **cPanel → "Restart PHP"** or **"PHP-FPM Restart"** if present.
3. **Touch the files** — some hosts watch modification times:
   ```
   File Manager → right-click class-capacity-repository.php → Change Permissions → Save unchanged
   ```
   This updates `mtime`, which invalidates the cached entry on several configurations.
4. **Ask the host.** If none of the above exist, open a support ticket:
   > "Please flush PHP OPcache / restart PHP-FPM for my cPanel account (domain: `aghazgarclinic.ir`). I have uploaded updated PHP theme files and the old bytecode is still being served. Also please confirm whether `opcache.revalidate_freq` is set above 0."

   Ask for `opcache.revalidate_freq=0` or `opcache.validate_timestamps=1` so future uploads apply immediately without a restart.

### 3d. Object cache
If Redis/Memcached is enabled, flush it. The capacity module stores nothing persistent there, but WooCommerce session and option caches can hold old state.

---

## Step 4 — Verify (do not skip)

Visit:

```
https://aghazgarclinic.ir/wp-content/themes/dr-plus-child/inc/capacity/version-check.php
```

Log in as an administrator first — the page returns **403** to anyone else by design (it discloses file paths and hashes).

### Expected result

```
All checks passed — this server is running the fixed code.

Gregorian date guard present            class-capacity-repository.php   PASS
Jalali detection in validator           class-capacity-validator.php    PASS
Missing-hour fail-closed guard          class-capacity-validator.php    PASS
Legacy online alias handled             class-capacity-service.php      PASS
Debug logging REMOVED                   class-capacity-repository.php   PASS
Capacity debug logging REMOVED          class-capacity-validator.php    PASS
```

### If it says FAIL

| Symptom | Meaning | Fix |
|---|---|---|
| **503** "WordPress could not be located" | File is in the wrong directory | Confirm the path is `themes/dr-plus-child/inc/capacity/` |
| **403 Forbidden** | Not logged in as admin | Log in to `/wp-admin` in the same browser, then reload |
| Any row **FAIL** | Old file still on disk | Re-upload that specific file; check for a nested `dr-plus-child/dr-plus-child/` |
| All **PASS** but bookings still overbook | OPcache or page cache | Do step 3c, then reload this page and compare `mtime` |

**Send me a screenshot of this page.** That single image tells us definitively whether the code is live, and I can stop guessing.

---

## Step 5 — Test the actual behaviour

Do this on the **same clinic** where you saw 3 bookings against 2 rooms.

1. Book 2 in-person appointments for the **same clinic, same date, same hour** with 2 different specialists → both should succeed
2. Reload the booking page for that hour → **the hour must no longer appear in the slot list**
3. Try to book a **3rd** in-person appointment for that hour → must be **REFUSED**, with:
   `ظرفیت این ساعت تکمیل شده است. لطفاً یکی از ساعت‌های موجود در آن روز را انتخاب کنید.`
4. Book a **`phone_consultation`** at that same hour → must **SUCCEED** (online visits are exempt by design; they do not occupy a room)

### Interpreting the results

| Result | Diagnosis |
|---|---|
| 3rd booking **rejected** | ✅ Working. The fix is live. |
| Slot disappeared but 3rd booking still succeeds | The slot filter is live but the validator is not — a partial upload. Re-upload `class-capacity-validator.php`. |
| Hour still shows in the list after 2 bookings | The filter is not running — check that `capacity.js` and the module are actually loaded, and that the clinic ID matches the one in the admin settings page. |
| Version check FAILs | Code is not live. Nothing else matters until it passes. |

---

## About `raw_date` — why the fix does not "convert" Jalali

Your directive asked for `strtotime(str_replace('-', '/', $date))` to convert the Jalali date. **That does not convert anything.** Tested in real PHP:

```
strtotime('1405/08/28')  → 32-bit: FALSE   ("Epoch doesn't fit in a PHP integer")
                         → 64-bit: 1405-08-28   ← unchanged
```

`strtotime()` has no calendar awareness. It reads `1405` **as a Gregorian year** and hands back the same date. On a 64-bit server that code would pass the identical Jalali string into `$wpdb->prepare()` — the exact bug it claims to fix. On a 32-bit server it would return `999` for *every* booking, valid ones included.

The `raw_date` key is the fix, because it is a Unix timestamp: calendar-neutral and unambiguous. The parent theme writes it (`template-booking-step-receipt.php:321`) and the database stores Gregorian, so `date('Y-m-d', raw_date)` reproduces the stored value **19/19** against your live dump.

If the cart ever arrives with **no** `raw_date` and only a Jalali display string, the new code refuses the booking and shows *"please select a visit date"*. Refusing is correct: there is no reliable way to know whether `1405-08-28` is Jalali, a typo, or a truncated timestamp, and guessing is precisely how the original bug happened.

A genuine converter does exist in the parent theme (`DrPlus\Utils\Date::jalali_to_gregorian`, `dr-plus/inc/Utils/utils-date.php:340`) if you ever want true conversion. I did not wire it in, because converting silently is riskier than refusing visibly.

---

## What changed in this release

| File | Change |
|---|---|
| `class-capacity-repository.php` | `assert_queryable_date()` guard before SQL; `UNRESOLVED_DATE_COUNT` (999) returns fail-closed with **no query issued** |
| `class-capacity-validator.php` | `looks_jalali()` + `extract_date()` refuses Jalali, normalizes Persian digits |
| `class-capacity-service.php` | Unresolvable date → no slots shown, instead of showing a full bookable day |
| `version-check.php` | **New.** Authenticated deployment verifier |

All PHP 7.4-compatible. `str_starts_with()` (PHP 8.0+) is deliberately **not** used — `style.css:10` declares `Requires PHP: 7.4`, so a direct call would fatal inside the capacity check on a 7.4 server.

---

## Final rule — I am not calling this fixed

Per your instruction: **this is not fixed until you confirm**

1. `version-check.php` shows **all six PASS**
2. the **3rd booking is rejected**

Send the version-check screenshot and the result of step 5. If the checker passes but overbooking continues, the cause is no longer in this code — it would be a second, conflicting plugin or the parent theme writing rows the count cannot see, and I would investigate that next.
