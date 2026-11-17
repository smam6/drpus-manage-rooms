# Real WordPress + WooCommerce harness (real PHP, real DB, no MySQL server)

This directory runs the **actual `dr-plus-child/inc/capacity/` module on a real
WordPress + WooCommerce install, against the real `mysql/aghazga1_new.sql` dump**,
entirely inside this sandbox.

## Why it looks like this

There is no PHP CLI and no MySQL server in the sandbox, and GitHub release
assets are blocked. The only viable route is a **real PHP interpreter compiled
to WebAssembly**:

* `@php-wasm/node` — genuine PHP 8.3 (Zend engine, full standard library).
* `WordPress/sqlite-database-integration` — the official WordPress
  MySQL→SQLite driver. Crucially, the dump is **replayed through that driver**,
  so the MySQL DDL/DML is translated by WordPress's own code, not by hand.

## Layout

```
tools/realwp-harness/
  setup.sh                     # one-shot environment builder
  run.mjs                      # generic runner: mounts WP + dump, runs a PHP script
  php/
    00-bootstrap.php           # shared: boots WP as a real request
    01-import-dump.php         # replays mysql/aghazga1_new.sql through the driver
    02-doctor.php              # environment sanity report
```

## Usage

```bash
bash tools/realwp-harness/setup.sh          # ~2 min, builds /tmp/wpsite + /tmp/realwp
node tools/realwp-harness/run.mjs php/02-doctor.php
```

## Known sandbox limitations (do not affect the capacity module)

1. The parent theme `dr-plus` ships 10 ionCube-encoded files out of 877.
   ionCube cannot be installed here, so those 10 are replaced with empty stubs
   **in `/tmp` only**. The capacity module depends on none of them; the doctor
   script proves that by loading every capacity class for real.
2. The dump's own `active_plugins` lists 19 plugins that are not present in this
   checkout. Only WooCommerce is activated.
3. Log/cron tables (`actionscheduler_*`, `aiowps_*`, `rank_math_*`, …) are not
   imported — they carry no booking data and make the dump 20 MB of noise.
