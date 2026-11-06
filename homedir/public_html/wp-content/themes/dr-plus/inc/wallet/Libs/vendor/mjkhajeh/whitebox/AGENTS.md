# AGENTS.md

## What this is

A WordPress PHP utility library (`mjkhajeh/whitebox`) consumed via Composer. It provides static helper classes for Elementor widgets, WooCommerce, Persian/Iranian localization, and general WordPress utilities. This is a **library** — it has no `add_action()` calls, no bootstrap, and no side effects on load.

## Namespace & autoloading

PSR-4: `MJ\Whitebox\` maps to the repo root. All classes are static (no instantiation).

## No tests, no CI, no linter

There are no tests, no `phpunit.xml`, no CI workflows, no linting config, and no `.gitignore`. Do not look for these. If you need to verify changes, grep for usages and review manually.

## Do not add side effects

`whitebox.php` is a plugin header stub only. `ElementorControls.php` has a guard (`if(!defined('ABSPATH')) exit`) and skips Elementor's updater. The library must remain stateless — all WordPress hooks are registered by consuming plugins/themes.

## Key pattern: `check_default()`

Nearly every method starts with `Utils::check_default($args, $defaults)` to merge defaults with type checking. If adding a new method, follow this pattern.

## Key files by importance

| File | What it does |
|------|-------------|
| `Utils.php` | Core static utilities (~1,414 lines) — the backbone |
| `ElementorControls.php` | Elementor widget control builders (~1,808 lines) |
| `Utils/Date.php` | Jalali (Solar Hijri) calendar conversion |
| `Utils/Validators.php` | Iranian ID, bank card, IBAN, phone validation |
| `Utils/Elementor.php` | Elementor-specific helpers (selectors, display) |
| `Utils/WC.php` | WooCommerce helpers (cart, coupons, currency) |

## Persian/Iranian localization is first-class

`Utils::convert_chars()` maps Persian/Arabic numerals to ASCII. Date, validators, formatters, and sanitizers all have deep Persian support. Do not break these mappings.

## Elementor control modes

`ElementorControls::general_style_controls()` accepts a `mode` parameter (svg, icon, text, wrapper, image, input) that auto-excludes irrelevant controls. When modifying control logic, respect the mode system.

## WordPress filter hooks (extensibility points)

The library exposes filters via `apply_filters()` — these are part of the public API:
- `mj\whitebox\utils\custom_tags`
- `mj\whitebox\elementor_controls\query_controls\query_types`
- `mj\whitebox\elementor_controls\query_controls\date_types`
- `mj\whitebox\elementor_controls\query_controls\orderby`
- `mj\whitebox\elementor_controls\button\types`
- `mj\whitebox\elementor_controls\button\styles`

## Plugin detection caching

Methods like `is_wc_active()`, `is_elementor_active()` use `static $is = null;` for per-request caching. Preserve this pattern — do not call `is_plugin_active()` repeatedly.

## Documentation

20 Markdown files in `docs/`. If you change public API, update the corresponding doc file.

## Version

Current: `1.7.9.0` (defined in `whitebox.php` header and potentially elsewhere).
