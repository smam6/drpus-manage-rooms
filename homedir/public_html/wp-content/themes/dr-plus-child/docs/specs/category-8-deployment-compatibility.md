# Phase B — Category 8: Deployment, Compatibility, Non-Regression (Specs 8.1–8.12)

| ID | Spec | Imperative requirement | Verification method | Status |
|----|------|------------------------|---------------------|--------|
| 95 | 8.1 | ALL changes MUST live inside `homedir/public_html/wp-content/themes/dr-plus-child/`; `git diff --name-only origin/main -- …/themes/dr-plus/` MUST be empty | git diff check per commit | VERIFIED (locked rule) |
| 96 | 8.2 | Feature MUST be hook-only (filter `utils-booking.php:511`, action `woocommerce_checkout_process`, AJAX) — zero parent file edits, zero template overrides | grep child for parent paths | VERIFIED |
| 97 | 8.3 | Feature MUST survive parent theme updates (hooks survive; encrypted file untouched) | scenario D8: simulate parent update, re-run D2 | VERIFIED |
| 98 | 8.4 | WooCommerce online-visit flow (phone/chat/video) MUST be unaffected | regression V4 + scenario D3 | VERIFIED |
| 99 | 8.5 | All user-facing strings MUST be Persian wrapped in `__()/esc_html__()` domain `drplus-child`; code comments in English | grep + scenario D9 | VERIFIED |
| 100 | 8.6 | No N+1 queries in slot list (spec 1.13); all dynamic SQL via `$wpdb->prepare` | query log | VERIFIED |
| 101 | 8.7 | Log table via `dbDelta` with `{$wpdb->prefix}` (spec 6.1) | installer test | VERIFIED |
| 102 | 8.8 | Assets (CSS/JS) enqueued with handle, dependency `jquery`, version `DRPLUS_CHILD_VERSION`, only on booking page | asset check (spec 7.11) | VERIFIED (`ChildInit.php` defines `DRPLUS_CHILD_VERSION`) |
| 103 | 8.9 | `.gitignore` MUST exclude `wp-config.php`, `*.log`, `.vscode/`, `.idea/`, `*.bak`; never commit secrets | gitignore review | VERIFIED (Phase F rule) |
| 104 | 8.10 | No `extract()/eval()/create_function()`, no `file_get_contents` on URLs, no `curl_*`, no `mysqli_*`/`PDO`, no `SELECT *` in hot paths | forbidden-pattern grep (Section 3.6.2 check 6) | VERIFIED |
| 105 | 8.11 | PHP compatibility MUST be 7.4+ (parent `style.css` Requires PHP 7.4; child same) — no PHP 8-only syntax | PHPCompatibility phpcs 7.4-8.3 | VERIFIED (`dr-plus/style.css` header; child header) |
| 106 | 8.12 | Existing bookings MUST never be retroactively blocked or modified | scenario D7 + V5 data integrity | VERIFIED (locked rule) |
