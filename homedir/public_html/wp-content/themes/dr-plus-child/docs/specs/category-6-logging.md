# Phase B — Category 6: Logging (Specs 6.1–6.10)

Locked rules: table name `{$wpdb->prefix}drplus_capacity_log` (renders `Dj3i_drplus_capacity_log`, capital D — never hardcode `dj3i_`); only blocked attempts logged; no admin notification; creation via `dbDelta`.

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 72 | 6.1 | Log table MUST be created with `dbDelta()` using `{$wpdb->prefix}drplus_capacity_log` | `SHOW TABLES LIKE '%drplus_capacity_log'` after activation | VERIFIED (prefix `wp-config.php:77`) |
| 73 | 6.2 | Table MUST have columns: `id` BIGINT UNSIGNED AUTO_INCREMENT PK, `office_id` VARCHAR(255), `booking_date` DATE, `start_time` TIME, `specialist_id` BIGINT, `current_count` INT, `capacity_limit` INT, `ip_address` VARCHAR(45), `user_id` BIGINT NULL, `created_at` DATETIME | `DESCRIBE` after install | VERIFIED |
| 74 | 6.3 | Table MUST have indexes on `(office_id, booking_date, start_time)` and `created_at` | `SHOW INDEX` | VERIFIED |
| 75 | 6.4 | Creation MUST hook `after_switch_theme` AND run on version bump via option `drplus_capacity_db_version` | activate theme / bump version | VERIFIED (Section 5.5 spec 74) |
| 76 | 6.5 | `dbDelta` creation MUST be idempotent (safe to re-run) | run installer twice | VERIFIED |
| 77 | 6.6 | ONLY blocked attempts MUST be logged (capacity_exceeded, missing_date, lock_failed); successful bookings MUST NOT be logged | scenario D10: exactly 1 row per blocked attempt | VERIFIED (locked rule) |
| 78 | 6.7 | IP MUST be captured from `$_SERVER['REMOTE_ADDR']` sanitized via `inet_pton`-safe sanitize, stored as string | log row check | VERIFIED |
| 79 | 6.8 | NO admin notification/email MAY be sent on blocked attempts | grep for `wp_mail`/`wp_notify` in capacity module = 0 | VERIFIED (locked rule) |
| 80 | 6.9 | If table creation fails, feature MUST degrade gracefully: capacity still enforced, logging skipped | drop table + checkout test | VERIFIED (Section 5.6 spec 96) |
| 81 | 6.10 | Log writes MUST use `$wpdb->insert()` with format array (`%s,%s,%s,%d,%d,%d,%s,%d,%s`) | grep insert call | VERIFIED |
