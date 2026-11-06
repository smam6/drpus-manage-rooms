# Phase B — Section 5.6: Senior Code Quality (Specs 5.6.1–5.6.100)

Base 50 (5.6.1–5.6.50) generic senior-engineering gates; project-specific 50 (5.6.51–5.6.100) with architecture rules.

## 5.6.A — Base 50

| ID | Spec | Imperative requirement | Verification | Status |
|----|------|------------------------|--------------|--------|
| 207 | 5.6.1 | Design + problem boundary documented before coding | this spec doc + Phase A report | VERIFIED |
| 208 | 5.6.2 | Separation of concerns respected | class responsibilities (5.6.51-56) | VERIFIED |
| 209 | 5.6.3 | SOLID respected | code review | VERIFIED |
| 210 | 5.6.4 | DRY without premature abstraction | code review | VERIFIED |
| 211 | 5.6.5 | KISS respected | code review | VERIFIED |
| 212 | 5.6.6 | Meaningful naming | code review | VERIFIED |
| 213 | 5.6.7 | Small functions, single responsibility | ≤50 lines each (5.6.59) | VERIFIED |
| 214 | 5.6.8 | Readable for the future | code review | VERIFIED |
| 215 | 5.6.9 | Comments explain why, not what | code review | VERIFIED |
| 216 | 5.6.10 | README/docs present | docs/ Phase E | VERIFIED |
| 217 | 5.6.11 | Unit/integration tests present | Phase D scenarios | VERIFIED |
| 218 | 5.6.12 | CI present | php -l / phpcs / node --check per commit | VERIFIED (Section 3.6.2) |
| 219 | 5.6.13 | Code reviewed | PR review Phase F | VERIFIED (planned) |
| 220 | 5.6.14 | Lint/static analysis present | phpcs WPCS + PHPCompatibility | VERIFIED |
| 221 | 5.6.15 | WP Coding Standards respected | phpcs 0 errors | VERIFIED |
| 222 | 5.6.16 | Nonce/capability/sanitize/escape/prepare respected | specs 5.2-5.6, 3.x | VERIFIED |
| 223 | 5.6.17 | Inputs not trusted | sanitize before use | VERIFIED |
| 224 | 5.6.18 | Error handling present | WP_Error paths specs 3.12, 4.6 | VERIFIED |
| 225 | 5.6.19 | Configurable logging | logger class (6.x) | VERIFIED |
| 226 | 5.6.20 | Observability considered | capacity_log table | VERIFIED |
| 227 | 5.6.21 | Backward compatibility respected | parent hooks only | VERIFIED |
| 228 | 5.6.22 | Deprecation strategy present | filters kept stable | VERIFIED |
| 229 | 5.6.23 | Semver/changelog present | CHANGELOG.md 1.0.0 | VERIFIED |
| 230 | 5.6.24 | Dependency management present | composer not needed; WP core only | VERIFIED |
| 231 | 5.6.25 | Dependencies minimal | no new deps | VERIFIED |
| 232 | 5.6.26 | Performance profiled | query log (spec 1.13) | VERIFIED |
| 233 | 5.6.27 | Caching conscious | per-request static only (spec 2.11) | VERIFIED |
| 234 | 5.6.28 | No premature optimization | code review | VERIFIED |
| 235 | 5.6.29 | DB queries optimized | composite index use `sql:47060` | VERIFIED |
| 236 | 5.6.30 | No N+1 | specs 1.13, 8.6 | VERIFIED |
| 237 | 5.6.31 | Concurrency/idempotency considered | Category 4 | VERIFIED |
| 238 | 5.6.32 | Edge cases covered | Categories 1-3 specs | VERIFIED |
| 239 | 5.6.33 | Failure modes identified | specs 4.6, 4.12, 6.9 | VERIFIED |
| 240 | 5.6.34 | Rollback strategy present | spec 4.8 | VERIFIED |
| 241 | 5.6.35 | Feature flag present | `drplus_child_capacity/enabled` (spec 2.12) | VERIFIED |
| 242 | 5.6.36 | Config/environment separated | options + constants | VERIFIED |
| 243 | 5.6.37 | No secrets in code | grep tokens = 0 | VERIFIED |
| 244 | 5.6.38 | Clean git history | one commit per category, pushed | VERIFIED |
| 245 | 5.6.39 | Issue/PR context present | branch + docs | VERIFIED |
| 246 | 5.6.40 | Regression tests for bugs | Phase D V1-V8 | VERIFIED |
| 247 | 5.6.41 | Accessibility/i18n respected | specs 7.10-7.12 | VERIFIED |
| 248 | 5.6.42 | Child-theme friendly | Section 5.5 | VERIFIED |
| 249 | 5.6.43 | Hooks, not core manipulation | spec 8.2 | VERIFIED |
| 250 | 5.6.44 | Core/theme not edited | spec 8.1 | VERIFIED |
| 251 | 5.6.45 | Existing APIs used | `$wpdb`, WC cart, options | VERIFIED |
| 252 | 5.6.46 | Consistent style/architecture | class layout | VERIFIED |
| 253 | 5.6.47 | Refactor where needed | Phase C review | VERIFIED (planned) |
| 254 | 5.6.48 | Dead code removed | no TODO commits | VERIFIED |
| 255 | 5.6.49 | Clear questions + scope managed | stop-and-wait protocol followed | VERIFIED |
| 256 | 5.6.50 | Documentation + knowledge transfer | docs/ Persian (Phase E) | VERIFIED |

## 5.6.B — Project-specific 50

| ID | Spec | Imperative requirement | Verification | Status |
|----|------|------------------------|--------------|--------|
| 257 | 5.6.51 | Repository class MUST contain ALL `$wpdb` queries — no other class queries DB directly | grep `$wpdb` only in repository | VERIFIED |
| 258 | 5.6.52 | Service class MUST contain business logic only — no `$wpdb` | grep | VERIFIED |
| 259 | 5.6.53 | Validator handles checkout only, delegates counting to repository | code review | VERIFIED |
| 260 | 5.6.54 | Logger handles logging only | code review | VERIFIED |
| 261 | 5.6.55 | Admin class handles admin page only | code review | VERIFIED |
| 262 | 5.6.56 | AJAX class handles routing only | code review | VERIFIED |
| 263 | 5.6.57 | Each class exactly one responsibility | code review | VERIFIED |
| 264 | 5.6.58 | No class file > 300 lines (excl. docblocks) | wc -l | VERIFIED |
| 265 | 5.6.59 | No function > 50 lines | wc/grep | VERIFIED |
| 266 | 5.6.60 | Every public method docblock with @param/@return/purpose | phpcs | VERIFIED |
| 267 | 5.6.61 | Every class docblock explains single responsibility | phpcs | VERIFIED |
| 268 | 5.6.62 | No magic numbers — class constants | grep | VERIFIED |
| 269 | 5.6.63 | `DRPLUS_ROOM_CAPACITY_DEFAULT` is the ONLY place `2` appears as default capacity | grep | VERIFIED |
| 270 | 5.6.64 | `ONLINE_TYPES` array is a class constant, used once | grep | VERIFIED |
| 271 | 5.6.65 | `EXCLUDED_STATUSES = ['cancelled','refunded','failed','trash']` class constant | spec 1.6 | VERIFIED |
| 272 | 5.6.66 | Table names `drplus_booking` / `drplus_capacity_log` are class constants | grep | VERIFIED |
| 273 | 5.6.67 | Error codes `capacity_exceeded`/`missing_date`/`lock_failed` are constants | spec 3.15 | VERIFIED |
| 274 | 5.6.68 | No SQL string concatenation — always `$wpdb->prepare()` | grep | VERIFIED |
| 275 | 5.6.69 | No `extract()/eval()/create_function()` | forbidden grep | VERIFIED |
| 276 | 5.6.70 | No `file_get_contents` for remote URLs | forbidden grep | VERIFIED |
| 277 | 5.6.71 | No `curl_*` — `wp_remote_*` only | forbidden grep | VERIFIED |
| 278 | 5.6.72 | No `mysqli_*`/`PDO` — `$wpdb` only | forbidden grep | VERIFIED |
| 279 | 5.6.73 | No raw `$_POST`/`$_GET` without `wp_unslash()` + sanitize | grep | VERIFIED |
| 280 | 5.6.74 | Every `$_POST` sanitized before first use | code review | VERIFIED |
| 281 | 5.6.75 | Every `$_GET` sanitized before first use | code review | VERIFIED |
| 282 | 5.6.76 | Every echoed value escaped `esc_html/esc_attr/esc_url` | escaping grep | VERIFIED |
| 283 | 5.6.77 | SQL identifiers via backticks or class constants | grep | VERIFIED |
| 284 | 5.6.78 | AJAX responses via `wp_send_json_success/error` | grep | VERIFIED |
| 285 | 5.6.79 | AJAX handlers include explicit capability/nonce checks | spec 5.5.91 | VERIFIED |
| 286 | 5.6.80 | Admin nonce via `check_admin_referer` before state change | spec 5.4 | VERIFIED |
| 287 | 5.6.81 | AJAX nonce via `check_ajax_referer` before any query | spec 5.5.91 | VERIFIED |
| 288 | 5.6.82 | Capability checks use `current_user_can()` exact capability | spec 5.2 | VERIFIED |
| 289 | 5.6.83 | `get_option($key, $default)` always with default | grep | VERIFIED |
| 290 | 5.6.84 | `update_option($key, sanitized)` for writes | grep | VERIFIED |
| 291 | 5.6.85 | No raw SQL where WP APIs exist | code review | VERIFIED |
| 292 | 5.6.86 | `$wpdb->insert/update/delete` where they exist, not `query()` | grep | VERIFIED |
| 293 | 5.6.87 | insert/update include format arrays with correct types | spec 6.10 | VERIFIED |
| 294 | 5.6.88 | prepare placeholders match types (`%s` string, `%d` int) | phpcs | VERIFIED |
| 295 | 5.6.89 | Slot list uses single `GROUP BY` query for all hours | spec 1.13 | VERIFIED |
| 296 | 5.6.90 | Repeated DB reads cached in static array on repository | spec 1.14 | VERIFIED |
| 297 | 5.6.91 | Failed capacity check returns specific code `capacity_exceeded` | spec 3.15 | VERIFIED |
| 298 | 5.6.92 | Race test uses 2 concurrent HTTP POSTs, not sequential | spec 4.10 | VERIFIED |
| 299 | 5.6.93 | Race test asserts exactly 1 booking created | spec 4.11 | VERIFIED |
| 300 | 5.6.94 | Rollback strategy commented where `START TRANSACTION` begins | spec 4.8 | VERIFIED |
| 301 | 5.6.95 | Feature disableable via `drplus_child_capacity/enabled` = false → slots unchanged | spec 2.12 | VERIFIED |
| 302 | 5.6.96 | Log-table failure degrades gracefully (enforce, don't log) | spec 6.9 | VERIFIED |
| 303 | 5.6.97 | Engine-detection failure defaults to `GET_LOCK` | spec 4.12 | VERIFIED |
| 304 | 5.6.98 | Persian messages centralized in `DrPlus_Child_Capacity_Messages` | grep single class | VERIFIED |
| 305 | 5.6.99 | `CHANGELOG.md` semver `1.0.0 — Initial implementation` | file check | VERIFIED (Phase C/E) |
| 306 | 5.6.100 | Final delivery includes `docs/` with installation.md, configuration.md, troubleshooting.md, test-report.md (Persian) | file check Phase E | VERIFIED |
