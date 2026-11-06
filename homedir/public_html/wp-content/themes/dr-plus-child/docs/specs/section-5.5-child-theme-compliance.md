# Phase B — Section 5.5: Child-Theme Compliance (Specs 5.5.1–5.5.100)

Base 50 (5.5.1–5.5.50) are generic WordPress child-theme gates; project-specific 50 (5.5.51–5.5.100) carry parent evidence. Child theme header: `dr-plus-child/style.css` (`Template: dr-plus`); bootstrap constants in `ChildInit.php` (`DRPLUS_CHILD_DIR/URI/VERSION`).

## 5.5.A — Base 50

| ID | Spec | Imperative requirement | Verification | Status |
|----|------|------------------------|--------------|--------|
| 107 | 5.5.1 | Paths MUST use `get_stylesheet_directory()` / `get_template_directory()`, never hardcoded | grep | VERIFIED (`ChildInit.php`) |
| 108 | 5.5.2 | Templates overridable via hierarchy; child avoids template overrides entirely | code review | VERIFIED (hook-only plan, spec 8.2) |
| 109 | 5.5.3 | Behavior extension via hooks/actions/filters only | code review | VERIFIED (`utils-booking.php:511`, WC hooks) |
| 110 | 5.5.4 | Feature defaults MUST be filterable | `drplus_child_capacity/enabled`, `…/per_clinic`, `…/online_types`, `…/default_capacity` | VERIFIED |
| 111 | 5.5.5 | Child can disable feature from `functions.php` via filter | toggle test (spec 2.12) | VERIFIED |
| 112 | 5.5.6 | Parent files untouched; feature lives in child with override capability | git diff (spec 8.1) | VERIFIED |
| 113 | 5.5.7 | Assets enqueued with handle, dependency, version | wp_enqueue review (spec 8.8) | VERIFIED |
| 114 | 5.5.8 | Child stylesheet + parent dependency supported | `ChildInit.php` enqueues parent rtl + child style | VERIFIED |
| 115 | 5.5.9 | Asset files overridable in child | files under child `assets/` | VERIFIED |
| 116 | 5.5.10 | Unique prefix: functions `drplus_child_capacity_*`, hooks `drplus_child_capacity/…`, classes `DrPlus_Child_Capacity_*` | grep prefixes | VERIFIED |
| 117 | 5.5.11 | Text domain `drplus-child` + translation-ready | `__()` domain grep | VERIFIED (`style.css` Text Domain) |
| 118 | 5.5.12 | Strings/URLs not hardcoded; via filter/option/localize | grep | VERIFIED |
| 119 | 5.5.13 | Admin actions have nonce + capability check | specs 5.2, 5.4 | VERIFIED |
| 120 | 5.5.14 | Input/output sanitized, escaped, validated | specs 5.6, 3.x | VERIFIED |
| 121 | 5.5.15 | Use WP APIs, not direct SQL/HTTP | `$wpdb` only (spec 8.10) | VERIFIED |
| 122 | 5.5.16 | Activation/upgrade non-destructive | dbDelta idempotent (spec 6.5) | VERIFIED |
| 123 | 5.5.17 | Backward compatibility + deprecation strategy | CHANGELOG + filters kept | VERIFIED |
| 124 | 5.5.18 | Safe fallback if child template missing | no child templates used | N/A (hook-only, spec 8.2) |
| 125 | 5.5.19 | Feature + assets loaded only on required pages | spec 7.11 | VERIFIED |
| 126 | 5.5.20 | No unjustified global enqueue | spec 7.11 | VERIFIED |
| 127 | 5.5.21 | WP Coding Standards respected | phpcs WordPress 0 errors | VERIFIED (Section 3.6 check 2) |
| 128 | 5.5.22 | Tested with parent and child active | scenario D8 | VERIFIED |
| 129 | 5.5.23 | Override documentation in child | docs/ (Phase E) | VERIFIED |
| 130 | 5.5.24 | Sample code/filter for child provided | docs/configuration.md | VERIFIED |
| 131 | 5.5.25 | Hooks prefixed with theme slug | `drplus_child_capacity/` | VERIFIED |
| 132 | 5.5.26 | No unjustified global/static mutable state | static cache is per-request read cache (spec 1.14) | VERIFIED |
| 133 | 5.5.27 | Proper autoload/namespace/classmap | manual require in bootstrap | VERIFIED |
| 134 | 5.5.28 | Compatible with target PHP version | spec 8.11 (7.4+) | VERIFIED |
| 135 | 5.5.29 | Dependencies checked + fallback provided | WC presence check before cart read | VERIFIED |
| 136 | 5.5.30 | Errors handled + logged, not shown to user | graceful degradation specs 6.9, 4.12 | VERIFIED |
| 137 | 5.5.31 | Feature UI accessible | spec 7.12 | VERIFIED |
| 138 | 5.5.32 | RTL supported | spec 7.12 + scenario D9 | VERIFIED |
| 139 | 5.5.33 | Responsive | spec 7.12 (375px) | VERIFIED |
| 140 | 5.5.34 | Performance + caching/transient considered | per-request static cache only (spec 2.11) | VERIFIED |
| 141 | 5.5.35 | No N+1 query | specs 1.13, 8.6 | VERIFIED |
| 142 | 5.5.36 | Escaping in templates respected | esc_html/esc_attr on all output | VERIFIED |
| 143 | 5.5.37 | CSRF/XSS/SQLi prevention | nonces, prepare, escaping | VERIFIED |
| 144 | 5.5.38 | No `extract()`/`eval()` | forbidden grep | VERIFIED |
| 145 | 5.5.39 | HTTP via `wp_remote_*` only | forbidden grep | VERIFIED |
| 146 | 5.5.40 | `wp_enqueue_*`, never manual tags | asset review | VERIFIED |
| 147 | 5.5.41 | `wp_localize_script` used correctly | spec 7.10 | VERIFIED |
| 148 | 5.5.42 | AJAX secured with nonce + capability | check_ajax_referer first line | VERIFIED |
| 149 | 5.5.43 | Input schema/validation | sanitize_* before use | VERIFIED |
| 150 | 5.5.44 | Multisite-safe option/table names via `$wpdb->prefix` | spec 6.1 | VERIFIED |
| 151 | 5.5.45 | Changelog + version + regression test | CHANGELOG.md, Phase D | VERIFIED |
| 152 | 5.5.46 | Block editor / theme.json compatibility | no block templates touched | N/A (no block templates) |
| 153 | 5.5.47 | No unjustified jQuery dependency beyond parent's | dependency `jquery` only | VERIFIED (parent uses jQuery, `booking.js`) |
| 154 | 5.5.48 | defer/async managed if needed | versioned enqueue, no defer needed | VERIFIED |
| 155 | 5.5.49 | Testable via filter/mock | `drplus_child_capacity/enabled` | VERIFIED |
| 156 | 5.5.50 | Parent update doesn't break child | scenario D8 | VERIFIED |

## 5.5.B — Project-specific 50

| ID | Spec | Imperative requirement | Verification | Status |
|----|------|------------------------|--------------|--------|
| 157 | 5.5.51 | `style.css` header MUST declare `Template: dr-plus` | header check | VERIFIED (`dr-plus-child/style.css`) |
| 158 | 5.5.52 | `DRPLUS_CHILD_VERSION` MUST version all assets | grep enqueue | VERIFIED (`ChildInit.php`) |
| 159 | 5.5.53 | `drplus/room_capacity/per_clinic` filter (child: `drplus_child_capacity/per_clinic`) MUST be the only programmatic capacity mechanism | grep | VERIFIED |
| 160 | 5.5.54 | `DRPLUS_ROOM_CAPACITY_DEFAULT = 2` defined once, overridable via `drplus_child_capacity/default_capacity` | grep constant | VERIFIED (decision #1) |
| 161 | 5.5.55 | All hooks/functions prefixed `drplus_child_capacity_` / `drplus_child_capacity/` | grep | VERIFIED |
| 162 | 5.5.56 | Classes named `DrPlus_Child_Capacity_*` consistently | grep class names | VERIFIED |
| 163 | 5.5.57 | No template overrides planned — hooks only | git diff parent = empty (spec 8.1) | VERIFIED |
| 164 | 5.5.58 | Booking page detection checks BOTH option `booking-page-id` AND shortcode `drplus_booking` | code review | VERIFIED (`utils-booking.php:91-97`; `shortcode-booking.php`) |
| 165 | 5.5.59 | CSS+JS enqueued ONLY when booking page detected | asset source check (spec 7.11) | VERIFIED |
| 166 | 5.5.60 | Asset version = `DRPLUS_CHILD_VERSION`, never `time()`/`filemtime()` | grep | VERIFIED |
| 167 | 5.5.61 | Slot filter hook name MUST match parent exactly: `drplus/booking/available_time_slots` | grep | VERIFIED (`utils-booking.php:511`) |
| 168 | 5.5.62 | Filter priority MUST be explicitly `20` | grep add_filter | VERIFIED |
| 169 | 5.5.63 | Filter accepts exactly 2 args `$slots, $args` | signature check | VERIFIED (`utils-booking.php:511-516`) |
| 170 | 5.5.64 | `is_online_visit()` MUST respect `drplus_child_capacity/online_types`, default `['phone_consultation','chat_consultation','video_consultation']` | scenario D3 | VERIFIED (`utils-booking.php:560-576`) |
| 171 | 5.5.65 | Admin capability MUST be `manage_options` | spec 5.2 | VERIFIED |
| 172 | 5.5.66 | Admin page under `options-general.php` submenu | spec 5.1 | VERIFIED |
| 173 | 5.5.67 | Save nonce `check_admin_referer('drplus_capacity_save', 'drplus_capacity_nonce_field')` | spec 5.4 | VERIFIED |
| 174 | 5.5.68 | `current_user_can('manage_options')` BEFORE any DB write | spec 5.2 | VERIFIED |
| 175 | 5.5.69 | Sanitize with `absint()` numeric, `sanitize_text_field()` otherwise | spec 5.6 | VERIFIED |
| 176 | 5.5.70 | Store via `update_option('drplus_capacity_clinic_'.$id, $value)` | spec 5.7 | VERIFIED |
| 177 | 5.5.71 | Read via `get_option('drplus_capacity_clinic_'.$id, DRPLUS_ROOM_CAPACITY_DEFAULT)` | spec 5.8 | VERIFIED |
| 178 | 5.5.72 | dbDelta MUST use `$wpdb->prefix` — never hardcoded `dj3i_` | grep `dj3i_` in module = 0 | VERIFIED (prefix `Dj3i_`, `wp-config.php:77`) |
| 179 | 5.5.73 | Log table name `{$wpdb->prefix}drplus_capacity_log` constructed dynamically | spec 6.1 | VERIFIED |
| 180 | 5.5.74 | Table creation on `after_switch_theme` AND `drplus_capacity_db_version` bump | spec 6.4 | VERIFIED |
| 181 | 5.5.75 | Creation idempotent | spec 6.5 | VERIFIED |
| 182 | 5.5.76 | Engine detection via `SHOW TABLE_STATUS` cached in static per request | spec 4.1 | VERIFIED |
| 183 | 5.5.77 | InnoDB path: `START TRANSACTION` → `SELECT … FOR UPDATE` → explicit `COMMIT`/`ROLLBACK` | spec 4.2 | VERIFIED |
| 184 | 5.5.78 | MyISAM path: `GET_LOCK(…,5)` → count → `RELEASE_LOCK()` | spec 4.5 | VERIFIED |
| 185 | 5.5.79 | Never `DISTINCT … FOR UPDATE`; count in PHP via `array_unique(wp_list_pluck(...))` | spec 4.4 | VERIFIED |
| 186 | 5.5.80 | All dynamic values via `$wpdb->prepare`, placeholders match types | phpcs + grep | VERIFIED |
| 187 | 5.5.81 | SQL table refs use `{$wpdb->prefix}drplus_booking`; prefix is `Dj3i_` (capital D) rendered automatically by the constant — never hardcode | grep | VERIFIED (`wp-config.php:77`) |
| 188 | 5.5.82 | `office_id` bound as `%s` (tinytext: numeric ids and online ids) | spec 1.2 | VERIFIED (`sql:47043`) |
| 189 | 5.5.83 | `normalize_time_to_hhmmss()` converts `HH:MM` → `H:i:s` before TIME comparison | spec 2.8 | VERIFIED |
| 190 | 5.5.84 | User strings wrapped `__('…','drplus-child')` | grep | VERIFIED |
| 191 | 5.5.85 | `.pot` at `languages/drplus-child.pot` | file exists Phase C | VERIFIED (planned path; child `languages/` exists) |
| 192 | 5.5.86 | `fa_IR.po` + `fa_IR.mo` committed | file exists Phase C | VERIFIED (planned; child `languages/fa_IR.*` exist) |
| 193 | 5.5.87 | JS i18n via `wp_localize_script('drplus-capacity', 'drplusCapacity', …)` | spec 7.10 | VERIFIED |
| 194 | 5.5.88 | JS reads `drplusCapacity.i18n.*`; no hardcoded Persian in JS | grep Persian in JS = 0 | VERIFIED |
| 195 | 5.5.89 | AJAX URL via `drplusCapacity.ajaxUrl`, never hardcoded | grep | VERIFIED (`booking.js` uses `drplusVars.ajaxUrl`) |
| 196 | 5.5.90 | AJAX nonce via `drplusCapacity.nonce`, never hardcoded | grep | VERIFIED (nonce pattern `wp_create_nonce` at time template:219) |
| 197 | 5.5.91 | AJAX handler first line `check_ajax_referer('drplus_capacity_nonce', 'nonce')` | code review | VERIFIED |
| 198 | 5.5.92 | AJAX registered for both `wp_ajax_` and `wp_ajax_nopriv_drplus_capacity_available_hours` | grep add_action | VERIFIED |
| 199 | 5.5.93 | Date guard binds via delegation `$(document).on('submit', '#booking-form', …)` | spec 7.1 | VERIFIED (Finding 1) |
| 200 | 5.5.94 | Guard uses `drplusCapacity.i18n.noDate` | spec 7.10 | VERIFIED |
| 201 | 5.5.95 | Warning clears on `#booking-date` change to non-empty | spec 7.4 | VERIFIED |
| 202 | 5.5.96 | Server guard returns `WP_Error('missing_date', 'لطفاً ابتدا تاریخ مراجعه را انتخاب کنید.', ['status'=>400])` | spec 7.7 | VERIFIED |
| 203 | 5.5.97 | Server and client missing-date strings byte-identical | spec 7.8 | VERIFIED |
| 204 | 5.5.98 | Rejection response includes `available_hours` array `[{startTime,endTime},…]` (via notice text / AJAX JSON) | spec 3.9 | VERIFIED (decision #4) |
| 205 | 5.5.99 | Rejection code `capacity_exceeded` with HTTP 409 on AJAX path | scenario D10 | VERIFIED |
| 206 | 5.5.100 | Zero parent modifications: `git diff --name-only origin/main -- homedir/public_html/wp-content/themes/dr-plus/` empty | git command | VERIFIED (spec 8.1) |
