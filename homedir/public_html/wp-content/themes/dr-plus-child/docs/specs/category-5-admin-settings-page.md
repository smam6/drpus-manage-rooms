# Phase B — Category 5: Admin Settings Page (Specs 5.1–5.12)

Parent evidence base: clinics are hospital-type offices (post IDs; `utils-booking.php` `specialist_office_html()` uses `get_the_title($office['id'])` for `type == 'hospital'`); parent settings use Redux but child MUST use a standalone `add_submenu_page` (child-theme independence); booking page id from option `booking-page-id` (`utils-booking.php:91-97`).

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 60 | 5.1 | Page MUST be registered via `add_submenu_page('options-general.php', …, 'manage_options', 'drplus-room-capacity', …)` — Settings → Room Capacity | load wp-admin menu | VERIFIED (decision #7) |
| 61 | 5.2 | Capability MUST be `manage_options`, checked with `current_user_can('manage_options')` before any DB write | unauthorized access test → 403 | VERIFIED |
| 62 | 5.3 | Page MUST list every active clinic (hospital post type) with its current capacity value | admin page render test | VERIFIED (offices are hospital posts, `utils-booking.php` `specialist_office_html`) |
| 63 | 5.4 | Each clinic field MUST be a numeric input, min 1, max 99 | HTML attribute check | VERIFIED |
| 64 | 5.5 | Save handler MUST verify nonce via `check_admin_referer('drplus_capacity_save', 'drplus_capacity_nonce_field')` | forged POST test | VERIFIED (Section 5.5 spec 67) |
| 65 | 5.6 | Save handler MUST sanitize with `absint()` and reject values <1 or >99 with a Persian error notice, without saving | save 0 and 100 → error, option unchanged | VERIFIED (V-C8) |
| 66 | 5.7 | Capacity MUST be stored via `update_option('drplus_capacity_clinic_' . $clinic_id, $value)` | DB check after save | VERIFIED (locked storage key) |
| 67 | 5.8 | Capacity MUST be read via `get_option('drplus_capacity_clinic_' . $clinic_id, DRPLUS_ROOM_CAPACITY_DEFAULT)` | unit test with missing option | VERIFIED (default 2, decision #1) |
| 68 | 5.9 | Success save MUST show a Persian success notice | admin notice check | VERIFIED |
| 69 | 5.10 | Page MUST render without JS errors in console on desktop and mobile widths | browser console check (V4) | VERIFIED |
| 70 | 5.11 | Page MUST NOT depend on parent Redux APIs — only core Settings API | grep for Redux usage in child | VERIFIED (child-theme isolation) |
| 71 | 5.12 | Changing capacity from 2 to 3 MUST make a previously full hour reappear in the slot list on next load | manual V-C8 flow | VERIFIED (decision: manager-configurable) |
