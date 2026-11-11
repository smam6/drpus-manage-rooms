-- =============================================================================
-- Dr Plus Capacity Diagnostics — Read-Only SQL Queries (D9)
--
-- INSTRUCTIONS:
--   1. Before running in phpMyAdmin, replace ALL occurrences of {{PREFIX}}
--      with your actual WordPress table prefix (from ENVIRONMENT.txt or wp-config.php),
--      for example: Dj3i_
--   2. ALL statements below are strictly READ-ONLY (SELECT / SHOW only).
--   3. Note on D9.2: If {{PREFIX}}drplus_capacity_log does not exist on live yet,
--      phpMyAdmin may show error #1146 on the SELECT from that table. If that
--      happens, report the #1146 error (it is important evidence!) and run D9.3
--      through the final query separately (or check "Ignore errors" in phpMyAdmin).
-- =============================================================================

-- D9.1: Check table engine and status for drplus_booking
SHOW TABLE STATUS LIKE '{{PREFIX}}drplus_booking';

-- D9.2: Check existence of drplus_capacity_log and all drplus tables, plus latest 30 capacity logs
SHOW TABLES LIKE '{{PREFIX}}drplus_capacity_log';

SELECT TABLE_NAME, ENGINE, TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME LIKE '%drplus%'
ORDER BY TABLE_NAME;

SELECT *
FROM {{PREFIX}}drplus_capacity_log
ORDER BY log_id DESC
LIMIT 30;

-- D9.3: Inspect specialist working hours (including day = 9 default hours)
SELECT id, user_id, office, `day`, `from`, `to`, use_default, status
FROM {{PREFIX}}drplus_times
ORDER BY user_id, office, `day`
LIMIT 200;

-- D9.4: Inspect specialists and registered office configurations
SELECT id, user_id, name, status, offline_visit, online_visit, offices
FROM {{PREFIX}}drplus_specialists
ORDER BY id;

-- D9.5: Inspect recent bookings, soft-delete flags, and distinct-specialist slot counts
SELECT book_id, specialist_id, office_id, `date`, start_time, end_time, order_id, order_status, deleted
FROM {{PREFIX}}drplus_booking
ORDER BY book_id DESC
LIMIT 50;

SELECT office_id, `date`, start_time,
       COUNT(DISTINCT specialist_id) AS distinct_specialists,
       COUNT(*) AS total_rows
FROM {{PREFIX}}drplus_booking
WHERE deleted = 0
  AND order_status NOT IN ('cancelled', 'refunded', 'failed', 'trash')
GROUP BY office_id, `date`, start_time
ORDER BY `date` DESC, start_time ASC
LIMIT 50;

-- D9.6: Check template, stylesheet, and drplus_capacity_% options
SELECT option_name, option_value
FROM {{PREFIX}}options
WHERE option_name IN ('template', 'stylesheet', 'drplus_capacity_db_version')
   OR option_name LIKE 'drplus_capacity_%'
ORDER BY option_name;

-- D9.7: Summary of active theme, active plugins, drplus theme options, and drplus_capacity_% options
SELECT option_name, LENGTH(option_value) AS value_bytes, option_value
FROM {{PREFIX}}options
WHERE option_name IN ('template', 'stylesheet', 'active_plugins', 'drplus_capacity_db_version', 'drplus')
   OR option_name LIKE 'drplus_capacity_%'
ORDER BY option_name;

SELECT 'D9 complete' AS status;
