# Phase C — Implementation Order

Branch: arena/b5e90f4b-drpus-manage-rooms | Parent theme read-only | All code in dr-plus-child/

## Execution order (17 commits)

| # | Commit message | Files | Specs implemented | Depends on |
|---|----------------|-------|-------------------|------------|
| 1 | docs(capacity): phase C implementation order | docs/specs/phase-c-order.md | all (planning) | - |
| 2 | feat(capacity): bootstrap constants and module loader | functions.php (require line), inc/capacity/bootstrap.php (skeleton) | 8.2, 8.8, 5.5.1-5.5.10 | 1 |
| 3 | feat(capacity): dbDelta installer for capacity_log table | inc/capacity/class-capacity-installer.php | 6.1-6.5, 5.5.72-5.5.75 | 2 |
| 4 | feat(capacity): repository with counting and locking | inc/capacity/class-capacity-repository.php | 1.1-1.15, 4.1-4.12, 5.6.51, 5.6.80-5.6.90 | 3 |
| 5 | feat(capacity): service with slot filtering logic | inc/capacity/class-capacity-service.php | 2.1-2.15, 5.6.52 | 4 |
| 6 | feat(capacity): logger for blocked attempts | inc/capacity/class-capacity-logger.php | 6.6-6.10, 5.6.54 | 3 |
| 7 | feat(capacity): checkout server-side validator | inc/capacity/class-capacity-validator.php | 3.1-3.17, 7.7-7.9 (server), 5.6.53 | 4, 6 |
| 8 | feat(capacity): admin settings page for per-clinic capacity | inc/capacity/class-capacity-admin.php | 5.1-5.12, 5.5.65-5.5.71, 5.6.55 | 2 |
| 9 | feat(capacity): centralized Persian messages | inc/capacity/class-capacity-messages.php | 3.16, 5.6.98, 7.2, 7.8 | 2 |
| 10 | style(capacity): RTL styles for warnings and slot list | assets/css/capacity.css | 7.3, 7.12, 5.5.32-5.5.33 | 9 |
| 11 | feat(capacity): client-side date guard and submit blocker | assets/js/capacity.js | 7.1-7.6, 7.10, 5.5.87-5.5.95 | 9 |
| 12 | i18n(capacity): translation template | languages/drplus-child.pot | 5.5.84-5.5.85, 8.5 | 9 |
| 13 | i18n(capacity): Persian translation | languages/fa_IR.po, languages/fa_IR.mo | 5.5.86, 8.5, 3.16 | 12 |
| 14 | feat(capacity): wire all hooks and enqueue assets | inc/capacity/bootstrap.php (final) | 2.1-2.2, 3.1, 7.11, 8.6-8.8, 5.5.58-5.5.63 | 3-13 |
| 15 | docs(capacity): installation and configuration guide | docs/installation.md, docs/configuration.md | 5.6.10, 5.5.23-5.5.24 | 14 |
| 16 | docs(capacity): troubleshooting and test report | docs/troubleshooting.md, docs/test-report.md | 5.6.100, V1-V13 evidence | 14 |
| 17 | chore(capacity): changelog v1.0.0 | CHANGELOG.md | 5.6.23, 5.6.99, 5.5.45 | 14 |

## Conflict resolutions (Part 1.2)

1. Category 3 vs 7 on woocommerce_checkout_process: date-presence check registers at priority 4 (fail fast), capacity check at priority 5. Both use wc_add_notice(...,'error'). Evidence: hook fires at plugins/woocommerce/includes/class-wc-checkout.php:1378 before create_order at :1411.
2. Category 2 (server slot filter) vs Category 7 (client submit blocker): no overlap — different layers (AJAX response vs form submit). Confirmed.
3. Option prefix drplus_capacity_*: grep of parent for 'drplus_capacity' returned 0 matches; existing drplus_* options are drplus_booking_product_id, drplus_sms_settings, etc. No collision.

## Audit Fixes (technical debt audit, 2026-11-07)

| Fix commit | Audit item | Change |
|------------|-----------|--------|
| 308add0a | W13 | ABSPATH guard added to functions.php |
| 10c125e5 | W11 | drplus_capacity_* options deleted on switch-away from child theme |

Residual risks documented in docs/audit-report.md: item 18 (check-then-act window before parent insert), W15 (no log TTL cron).

## Deviation log

(empty — populated in phase-c-deviations.md if a deviation occurs)

## Spec coverage checklist (306 rows)

| Spec ID | Implementing file(s) | Status |
|---------|----------------------|--------|
| 1.1 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.2 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.3 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.4 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.5 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.6 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.7 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.8 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.9 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.10 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.11 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.12 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.13 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.14 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 1.15 | inc/capacity/class-capacity-repository.php, class-capacity-service.php | covered |
| 2.1 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.2 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.3 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.4 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.5 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.6 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.7 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.8 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.9 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.10 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.11 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.12 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.13 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.14 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 2.15 | inc/capacity/class-capacity-service.php, bootstrap.php (2.13-2.15 dormant contingency) | covered |
| 3.1 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.2 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.3 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.4 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.5 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.6 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.7 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.8 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.9 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.10 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.11 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.12 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.13 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.14 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.15 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.16 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 3.17 | inc/capacity/class-capacity-validator.php, class-capacity-repository.php | covered |
| 4.1 | inc/capacity/class-capacity-repository.php | covered |
| 4.2 | inc/capacity/class-capacity-repository.php | covered |
| 4.3 | inc/capacity/class-capacity-repository.php | covered |
| 4.4 | inc/capacity/class-capacity-repository.php | covered |
| 4.5 | inc/capacity/class-capacity-repository.php | covered |
| 4.6 | inc/capacity/class-capacity-repository.php | covered |
| 4.7 | inc/capacity/class-capacity-repository.php | covered |
| 4.8 | inc/capacity/class-capacity-repository.php | covered |
| 4.9 | inc/capacity/class-capacity-repository.php | covered |
| 4.10 | inc/capacity/class-capacity-repository.php | covered |
| 4.11 | inc/capacity/class-capacity-repository.php | covered |
| 4.12 | inc/capacity/class-capacity-repository.php | covered |
| 5.1 | inc/capacity/class-capacity-admin.php | covered |
| 5.2 | inc/capacity/class-capacity-admin.php | covered |
| 5.3 | inc/capacity/class-capacity-admin.php | covered |
| 5.4 | inc/capacity/class-capacity-admin.php | covered |
| 5.5 | inc/capacity/class-capacity-admin.php | covered |
| 5.6 | inc/capacity/class-capacity-admin.php | covered |
| 5.7 | inc/capacity/class-capacity-admin.php | covered |
| 5.8 | inc/capacity/class-capacity-admin.php | covered |
| 5.9 | inc/capacity/class-capacity-admin.php | covered |
| 5.10 | inc/capacity/class-capacity-admin.php | covered |
| 5.11 | inc/capacity/class-capacity-admin.php | covered |
| 5.12 | inc/capacity/class-capacity-admin.php | covered |
| 6.1 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.2 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.3 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.4 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.5 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.6 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.7 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.8 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.9 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 6.10 | inc/capacity/class-capacity-logger.php, class-capacity-installer.php | covered |
| 7.1 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.2 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.3 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.4 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.5 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.6 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.7 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.8 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.9 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.10 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.11 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.12 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 7.13 | assets/js/capacity.js, assets/css/capacity.css, class-capacity-validator.php (server 400) | covered |
| 8.1 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.2 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.3 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.4 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.5 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.6 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.7 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.8 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.9 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.10 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.11 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 8.12 | bootstrap.php, functions.php, CHANGELOG.md, .gitignore | covered |
| 5.5.1 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.2 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.3 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.4 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.5 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.6 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.7 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.8 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.9 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.10 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.11 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.12 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.13 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.14 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.15 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.16 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.17 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.18 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.19 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.20 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.21 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.22 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.23 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.24 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.25 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.26 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.27 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.28 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.29 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.30 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.31 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.32 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.33 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.34 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.35 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.36 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.37 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.38 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.39 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.40 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.41 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.42 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.43 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.44 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.45 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.46 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.47 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.48 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.49 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.50 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.51 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.52 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.53 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.54 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.55 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.56 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.57 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.58 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.59 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.60 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.61 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.62 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.63 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.64 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.65 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.66 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.67 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.68 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.69 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.70 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.71 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.72 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.73 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.74 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.75 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.76 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.77 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.78 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.79 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.80 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.81 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.82 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.83 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.84 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.85 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.86 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.87 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.88 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.89 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.90 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.91 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.92 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.93 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.94 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.95 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.96 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.97 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.98 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.99 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.5.100 | whole module (bootstrap.php, admin, assets, languages) | covered |
| 5.6.1 | whole module (all classes) | covered |
| 5.6.2 | whole module (all classes) | covered |
| 5.6.3 | whole module (all classes) | covered |
| 5.6.4 | whole module (all classes) | covered |
| 5.6.5 | whole module (all classes) | covered |
| 5.6.6 | whole module (all classes) | covered |
| 5.6.7 | whole module (all classes) | covered |
| 5.6.8 | whole module (all classes) | covered |
| 5.6.9 | whole module (all classes) | covered |
| 5.6.10 | whole module (all classes) | covered |
| 5.6.11 | whole module (all classes) | covered |
| 5.6.12 | whole module (all classes) | covered |
| 5.6.13 | whole module (all classes) | covered |
| 5.6.14 | whole module (all classes) | covered |
| 5.6.15 | whole module (all classes) | covered |
| 5.6.16 | whole module (all classes) | covered |
| 5.6.17 | whole module (all classes) | covered |
| 5.6.18 | whole module (all classes) | covered |
| 5.6.19 | whole module (all classes) | covered |
| 5.6.20 | whole module (all classes) | covered |
| 5.6.21 | whole module (all classes) | covered |
| 5.6.22 | whole module (all classes) | covered |
| 5.6.23 | whole module (all classes) | covered |
| 5.6.24 | whole module (all classes) | covered |
| 5.6.25 | whole module (all classes) | covered |
| 5.6.26 | whole module (all classes) | covered |
| 5.6.27 | whole module (all classes) | covered |
| 5.6.28 | whole module (all classes) | covered |
| 5.6.29 | whole module (all classes) | covered |
| 5.6.30 | whole module (all classes) | covered |
| 5.6.31 | whole module (all classes) | covered |
| 5.6.32 | whole module (all classes) | covered |
| 5.6.33 | whole module (all classes) | covered |
| 5.6.34 | whole module (all classes) | covered |
| 5.6.35 | whole module (all classes) | covered |
| 5.6.36 | whole module (all classes) | covered |
| 5.6.37 | whole module (all classes) | covered |
| 5.6.38 | whole module (all classes) | covered |
| 5.6.39 | whole module (all classes) | covered |
| 5.6.40 | whole module (all classes) | covered |
| 5.6.41 | whole module (all classes) | covered |
| 5.6.42 | whole module (all classes) | covered |
| 5.6.43 | whole module (all classes) | covered |
| 5.6.44 | whole module (all classes) | covered |
| 5.6.45 | whole module (all classes) | covered |
| 5.6.46 | whole module (all classes) | covered |
| 5.6.47 | whole module (all classes) | covered |
| 5.6.48 | whole module (all classes) | covered |
| 5.6.49 | whole module (all classes) | covered |
| 5.6.50 | whole module (all classes) | covered |
| 5.6.51 | whole module (all classes) | covered |
| 5.6.52 | whole module (all classes) | covered |
| 5.6.53 | whole module (all classes) | covered |
| 5.6.54 | whole module (all classes) | covered |
| 5.6.55 | whole module (all classes) | covered |
| 5.6.56 | whole module (all classes) | covered |
| 5.6.57 | whole module (all classes) | covered |
| 5.6.58 | whole module (all classes) | covered |
| 5.6.59 | whole module (all classes) | covered |
| 5.6.60 | whole module (all classes) | covered |
| 5.6.61 | whole module (all classes) | covered |
| 5.6.62 | whole module (all classes) | covered |
| 5.6.63 | whole module (all classes) | covered |
| 5.6.64 | whole module (all classes) | covered |
| 5.6.65 | whole module (all classes) | covered |
| 5.6.66 | whole module (all classes) | covered |
| 5.6.67 | whole module (all classes) | covered |
| 5.6.68 | whole module (all classes) | covered |
| 5.6.69 | whole module (all classes) | covered |
| 5.6.70 | whole module (all classes) | covered |
| 5.6.71 | whole module (all classes) | covered |
| 5.6.72 | whole module (all classes) | covered |
| 5.6.73 | whole module (all classes) | covered |
| 5.6.74 | whole module (all classes) | covered |
| 5.6.75 | whole module (all classes) | covered |
| 5.6.76 | whole module (all classes) | covered |
| 5.6.77 | whole module (all classes) | covered |
| 5.6.78 | whole module (all classes) | covered |
| 5.6.79 | whole module (all classes) | covered |
| 5.6.80 | whole module (all classes) | covered |
| 5.6.81 | whole module (all classes) | covered |
| 5.6.82 | whole module (all classes) | covered |
| 5.6.83 | whole module (all classes) | covered |
| 5.6.84 | whole module (all classes) | covered |
| 5.6.85 | whole module (all classes) | covered |
| 5.6.86 | whole module (all classes) | covered |
| 5.6.87 | whole module (all classes) | covered |
| 5.6.88 | whole module (all classes) | covered |
| 5.6.89 | whole module (all classes) | covered |
| 5.6.90 | whole module (all classes) | covered |
| 5.6.91 | whole module (all classes) | covered |
| 5.6.92 | whole module (all classes) | covered |
| 5.6.93 | whole module (all classes) | covered |
| 5.6.94 | whole module (all classes) | covered |
| 5.6.95 | whole module (all classes) | covered |
| 5.6.96 | whole module (all classes) | covered |
| 5.6.97 | whole module (all classes) | covered |
| 5.6.98 | whole module (all classes) | covered |
| 5.6.99 | whole module (all classes) | covered |
| 5.6.100 | whole module (all classes) | covered |

Total: 306 specs

## v1.0.1 — Race condition fix (layered locking, specs 4.13–4.17)

| File | Change | Specs |
|------|--------|-------|
| `inc/capacity/class-capacity-lock-manager.php` | NEW — GET_LOCK/RELEASE_LOCK advisory lock manager, shutdown failsafe | 4.13, 4.14, 4.16 |
| `inc/capacity/class-capacity-validator.php` | MODIFIED — acquire lock before count, deferred release on `woocommerce_checkout_order_processed` (9999), Layer 2 compensating rollback | 4.13, 4.14, 4.15 |
| `inc/capacity/class-capacity-repository.php` | MODIFIED — new lock-free `count_distinct_specialists()`; `check_slot_with_lock()` kept, deprecated | 4.17 |
| `inc/capacity/class-capacity-messages.php` | MODIFIED — new Persian strings `system_busy()`, `compensating_rollback()` | 4.13, 4.15 |
| `inc/capacity/bootstrap.php` | MODIFIED — require lock manager; header lists 4.13–4.17 | — |
| `tests/load/mock-server/` | NEW `locked-v2` mode simulating Layer 1 + Layer 2 | 4.13–4.15 |
