# Manual Test Runner — Room Capacity Management (Phase C V-Tests)

**Purpose:** step-by-step runbook for the clinic manager / developer to execute all
automated checks and the 13 V-tests on a live WordPress install (XAMPP or staging).
Nothing here modifies code. Execute top to bottom; record Pass/Fail per step.

**Assumptions:**
- WordPress root: your XAMPP htdocs, e.g. `C:\xampp\htdocs\clinic\` (adjust `$WP` below).
- Theme path: `$WP\wp-content\themes\dr-plus-child\`
- DB prefix: `Dj3i_` (from `wp-config.php`). In SQL below, replace `wp_Dj3i_` with your real
  prefixed table names if they differ — the code itself always uses `$wpdb->prefix`.
- Test date used everywhere: **2099-05-20** (isolated; never a real booking date).
- Test clinic (office/hospital post) ID: `<CLINIC_ID>` — find it in WP admin → Hospitals.
- Two test specialists: `<SPEC_A>`, `<SPEC_B>` (IDs from Specialists admin).
- WP_DEBUG is on; log at `$WP\wp-content\debug.log`.

---

## PART 1 — Automated static checks (run in a terminal with PHP available)

Set `$CHILD = "$WP\wp-content\themes\dr-plus-child"`.

### 1.1 php -l on every PHP file of the module

```bat
php -l "%CHILD%\functions.php"
php -l "%CHILD%\inc\capacity\bootstrap.php"
php -l "%CHILD%\inc\capacity\class-capacity-installer.php"
php -l "%CHILD%\inc\capacity\class-capacity-repository.php"
php -l "%CHILD%\inc\capacity\class-capacity-service.php"
php -l "%CHILD%\inc\capacity\class-capacity-logger.php"
php -l "%CHILD%\inc\capacity\class-capacity-validator.php"
php -l "%CHILD%\inc\capacity\class-capacity-admin.php"
php -l "%CHILD%\inc\capacity\class-capacity-messages.php"
```
**Expected:** every line prints `No syntax errors detected`. Any `Parse error` = FAIL.

### 1.2 WPCS (WordPress Coding Standards)

```bat
phpcs --standard=WordPress --extensions=php "%CHILD%\inc\capacity\"
phpcs --standard=WordPress --extensions=php "%CHILD%\functions.php"
```
**Expected:** 0 ERROR lines. WARNING lines are acceptable but list them in the report.

### 1.3 PHPCompatibility (PHP 8.0–8.3 target)

```bat
phpcs --standard=PHPCompatibility --runtime-set testVersion 8.0-8.3 "%CHILD%\inc\capacity\"
```
**Expected:** 0 errors.

### 1.4 JS syntax

```bat
node --check "%CHILD%\assets\js\capacity.js"
```
**Expected:** silent success (no output).

### 1.5 Forbidden-pattern grep (PowerShell)

```powershell
Select-String -Path "$env:CHILD\inc\capacity\*.php","$env:CHILD\assets\js\*.js" -Pattern "extract\(|eval\(|file_get_contents|curl_|mysqli_|new PDO|var_dump|print_r|error_log|dj3i_|Dj3i_"
```
**Expected:** no matches.

---

## PART 2 — Preparation (phpMyAdmin / SQL)

Run in phpMyAdmin (or `mysql` CLI). All inserts use the isolated date 2099-05-20.

```sql
-- 0. Confirm log table exists (created on theme activation)
SHOW TABLES LIKE '%drplus_capacity_log';
-- Expected: one row: Dj3i_drplus_capacity_log

-- 1. Confirm booking table engine
SHOW TABLE STATUS LIKE 'Dj3i_drplus_booking';
-- Expected: Engine = InnoDB

-- 2. Baseline row counts (record before tests)
SELECT COUNT(*) FROM Dj3i_drplus_booking WHERE `date` = '2099-05-20';
SELECT COUNT(*) FROM Dj3i_drplus_capacity_log WHERE booking_date = '2099-05-20';
-- Expected: both 0

-- 3. Seed V3/V4: two active in-person bookings at 10:00 for clinic <CLINIC_ID>
INSERT INTO Dj3i_drplus_booking
  (customer_id, specialist_id, office_id, `date`, start_time, end_time, total_price, order_status, created_at, updated_at, deleted)
VALUES
  (9001, <SPEC_A>, '<CLINIC_ID>', '2099-05-20', '10:00:00', '10:30:00', 100, 'processing', NOW(), NOW(), 0),
  (9002, <SPEC_B>, '<CLINIC_ID>', '2099-05-20', '10:00:00', '10:30:00', 100, 'processing', NOW(), NOW(), 0);

-- 4. Seed V6/V9: one booking at 11:00 (capacity 2 -> one free) and one at 14:00
INSERT INTO Dj3i_drplus_booking
  (customer_id, specialist_id, office_id, `date`, start_time, end_time, total_price, order_status, created_at, updated_at, deleted)
VALUES
  (9003, <SPEC_A>, '<CLINIC_ID>', '2099-05-20', '11:00:00', '11:30:00', 100, 'processing', NOW(), NOW(), 0),
  (9004, <SPEC_A>, '<CLINIC_ID>', '2099-05-20', '14:00:00', '14:30:00', 100, 'processing', NOW(), NOW(), 0);
```

---

## PART 3 — Browser V-tests

### V1 — Fatal error check
1. Truncate the log: `type nul > "%WP%\wp-content\debug.log"` (or delete it; WP recreates it).
2. Open in browser: `/wp-admin/` (dashboard), the booking page (`/booking/`), and
   `/wp-admin/options-general.php?page=drplus-room-capacity`.
3. Then run:
   ```powershell
   Select-String -Path "%WP%\wp-content\debug.log" -Pattern "PHP Fatal error","PHP Warning.*dr-plus-child"
   ```
**Expected:** no output (zero fatal errors, zero warnings from dr-plus-child/inc/capacity/). PASS only if empty.

### V2 — Filter fires (temporary probe)
1. Temporarily add as the FIRST line inside `DrPlus_Child_Capacity_Service::filter_full_slots()`:
   `error_log('drplus_capacity: filter fired office=' . (isset($args['office_id']) ? $args['office_id'] : ''));`
2. Reload the booking page → time step for `<CLINIC_ID>` with a date selected.
3. Check debug.log contains `drplus_capacity: filter fired`.
4. **Remove the probe line immediately** and save.
**Expected:** log line present with the office id; after removal, no such line on reload.

### V3 — Full slot hidden (10:00) — AJAX capture
1. Booking page → time step → select 2099-05-20.
2. DevTools → Network → filter `admin-ajax.php`. Select the date in the calendar to trigger
   the request `action=drplus_get_available_times`.
3. Open the response (Preview). Expected JSON: `success: true`, `data` is an array of
   `{from,to,available,label}` where **no element has `from = "10:00"`**, and an element
   with `from = "11:00"` IS present (11:00 has only 1 booking, capacity 2).
4. Also confirm the rendered grid (Elements tab) has no `.booking-time-slot` whose text starts with 10:00.
**curl alternative:**
```bash
curl -s -X POST "https://<host>/wp-admin/admin-ajax.php" \
  -d "action=drplus_get_available_times&date=2099-05-20&office=<CLINIC_ID>&specialist=<SPEC_A>&chunk=30&nonce=<NONCE>"
# nonce: copy the data-nonce attribute of #booking-time in the page source
```
**Expected:** `"10:00"` absent from `data[].from`; `"11:00"` present.

### V4 — Bypass POST to full slot rejected
Submit a booking directly for 10:00 (stale page / curl). Then verify:
```sql
SELECT COUNT(*) FROM Dj3i_drplus_booking
 WHERE office_id='<CLINIC_ID>' AND `date`='2099-05-20' AND start_time='10:00:00' AND deleted=0;
-- Expected: still 2 (no new row)

SELECT log_id, office_id, booking_date, start_time, current_count, capacity_limit, reason
  FROM Dj3i_drplus_capacity_log ORDER BY log_id DESC LIMIT 1;
-- Expected: current_count=2, capacity_limit=2, reason='capacity_exceeded', booking_date='2099-05-20'
```
Browser: the checkout page must show the Persian notice:
«ظرفیت این ساعت تکمیل شده است. لطفاً یکی از ساعت‌های موجود در آن روز را انتخاب کنید.» plus «ساعت‌های آزاد: …».
**Expected:** no new booking row; one new capacity_log row; Persian notice visible.

### V5 — Online visit exempt
Book a `video_consultation` at 10:00 on 2099-05-20 for a specialist not already booked there.
**Expected:** booking succeeds; `SELECT COUNT(*) FROM Dj3i_drplus_capacity_log WHERE booking_date='2099-05-20' AND start_time='10:00:00';` unchanged by this action.

### V6 — Soft-deleted booking not counted (server-side)
```sql
-- soft-delete one of the two 11:00 bookings
UPDATE Dj3i_drplus_booking SET deleted=1
 WHERE office_id='<CLINIC_ID>' AND `date`='2099-05-20' AND start_time='11:00:00' LIMIT 1;

-- server-side count (must be 1)
SELECT COUNT(DISTINCT specialist_id) FROM Dj3i_drplus_booking
 WHERE office_id='<CLINIC_ID>' AND `date`='2099-05-20' AND start_time='11:00:00'
   AND deleted=0 AND order_status NOT IN ('cancelled','refunded','failed','trash');
-- Expected result: 1
```
Then attempt a new in-person booking at 11:00 from a third specialist.
**Expected:** the new booking is allowed (count 1 < capacity 2).

### V7 — Front-end soft-delete behavior preserved (documented limitation)
With the same soft-deleted 11:00 booking: reload the slot list.
**Expected:** 11:00 may still be absent from the front-end (parent bug preserved — our filter must NOT re-enable it). Confirm `docs/troubleshooting.md` contains the "Known parent-theme limitation" paragraph.

### V8 — Date guard real blocker
1. Booking page → time step. In DevTools console:
   `document.querySelector('#booking-date').value='';`
2. Focus any text input and press **Enter**.
**Expected:** no network POST to the next step, no navigation; the warning element
`.drplus-capacity-date-warning` becomes visible with text
«لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.». Then select a date → warning hides.

### V9 — Race condition
Fire 2 concurrent booking POSTs for 14:00 (capacity 2, 1 already booked):
```bash
# run both in parallel (two terminals or curl & curl & wait)
curl -s -o resp1.json -w "%{http_code}\n" -X POST "<checkout-url>" -d "<booking-post-body-for-14:00-specialist-B>"
curl -s -o resp2.json -w "%{http_code}\n" -X POST "<checkout-url>" -d "<booking-post-body-for-14:00-specialist-C>"
```
Then:
```sql
SELECT COUNT(DISTINCT specialist_id) FROM Dj3i_drplus_booking
 WHERE office_id='<CLINIC_ID>' AND `date`='2099-05-20' AND start_time='14:00:00' AND deleted=0
   AND order_status NOT IN ('cancelled','refunded','failed','trash');
-- Expected: exactly 2

SELECT COUNT(*) FROM Dj3i_drplus_capacity_log
 WHERE booking_date='2099-05-20' AND start_time='14:00:00';
-- Expected: exactly 1 new row (one blocked attempt)
```
**Expected:** exactly one of resp1/resp2 succeeds; the other carries the capacity_exceeded notice; DB count = 2; one log row.

### V10 — Admin settings save
1. WP admin → Settings → Room Capacity. Note current value for `<CLINIC_ID>` (2).
2. Change to 3, save. Expected: green Persian notice «تنظیمات با موفقیت ذخیره شد.»
3. Run: `SELECT option_value FROM Dj3i_options WHERE option_name='drplus_capacity_clinic_<CLINIC_ID>';` → `3`.
4. Reload booking page for 2099-05-20 → 10:00 slot must REAPPEAR (3 bookings needed to fill).
5. Set an invalid value (0 or 100), save → error notice «برخی مقادیر نامعتبر هستند (بازه مجاز ۱ تا ۹۹) و ذخیره نشدند.» and value unchanged.
6. Set back to 2 → 10:00 hidden again.

### V11 — Regression
- Book an online visit at any hour → succeeds.
- Book an in-person visit at an empty hour (e.g. 09:00) → succeeds.
- Cancel an existing booking in admin → slot list still renders; a previously full hour reappears.
- Admin dashboard: zero JS console errors.
- Booking page at 375px width: usable (warning and slots visible).

### V12 — Persian RTL
Inspect: date-missing warning, capacity notice, admin page labels, checkout error notices.
**Expected:** all in Persian, right-to-left, no English UI leakage.

---

## PART 4 — Cleanup (V13)

```sql
DELETE FROM Dj3i_drplus_booking      WHERE `date` = '2099-05-20';
DELETE FROM Dj3i_drplus_capacity_log  WHERE booking_date = '2099-05-20';
SELECT COUNT(*) FROM Dj3i_drplus_booking     WHERE `date` = '2099-05-20';  -- must be 0
SELECT COUNT(*) FROM Dj3i_drplus_capacity_log WHERE booking_date = '2099-05-20'; -- must be 0
```
**Expected:** both counts return 0. Never TRUNCATE; never delete without WHERE.

---

## Result recording
Copy this table into `docs/test-report.md` on the branch after execution:

| Test | Input | Expected | Actual | Pass/Fail |
|------|-------|----------|--------|-----------|
| V1..V13 | … | … | … | … |
