#!/usr/bin/env bash
# Full verification: build the harness, then run every suite against the real
# WordPress + WooCommerce install and the real production dump.
#
#   bash tools/realwp-harness/verify.sh
#
# Exit code 0 only when every suite is clean.
set -uo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
HERE="$REPO/tools/realwp-harness"
RUN="${WPRUN:-/tmp/realwp}"

fail=0
step() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }

step "setup (fresh)"
bash "$HERE/setup.sh" >/dev/null 2>&1 || { echo "setup failed"; exit 1; }
bash "$HERE/setup.sh" clean >/dev/null 2>&1
bash "$HERE/setup.sh" sync  >/dev/null 2>&1

run() { # run <label> <script> <grep-pattern>
  local label="$1" script="$2" pattern="$3"
  local out
  out="$(cd "$RUN" && node run.mjs "$HERE/php/$script" 2>&1)"
  if printf '%s' "$out" | grep -qE "$pattern"; then
    printf '  \033[32mPASS\033[0m  %s\n' "$label"
  else
    printf '  \033[31mFAIL\033[0m  %s\n' "$label"
    printf '%s\n' "$out" | grep -aE '✗|FAIL|Fatal|error' | head -20
    fail=1
  fi
}

step "1/8  WordPress install"
run "core install"                 00a-install-wp.php   'installed uid='
step "2/8  Production dump (MariaDB → SQLite, via the WordPress driver)"
run "dump replayed, 0 errors"      01-import-dump.php   'insert errors     : 0'
step "3/8  Clinic alias map from production data"
run "alias map seeded"             00b-seed-alias-map.php 'alias map saved'
step "4/8  Environment doctor"
run "environment + hooks"          02-doctor.php        'RESULT: 19 passed, 0 failed'
step "5/8  Six live scenarios ×3"
for i in 1 2 3; do
  run "live scenarios, loop $i"    03-live-scenarios.php 'RESULT: 12 passed, 0 failed'
done
step "6/8  Multi-UUID clinic identity (the reported ticket)"
run "multi-UUID clinic"            05-multi-uuid-clinic.php 'RESULT: 14 passed, 0 failed'
step "7/8  Cancelled bookings free the hour"
run "cancelled frees slot"       06-cancelled-frees-slot.php 'RESULT: 8 passed, 0 failed'
step "8/8  Shipped version-check.php"
vc="$(cd "$RUN" && node run.mjs "$HERE/php/04-version-check.php" 2>&1 | sed 's/<[^>]*>/ /g')"
vc_pass="$(printf '%s' "$vc" | grep -c 'PASS')"
vc_fail="$(printf '%s' "$vc" | grep -c 'FAIL')"
printf '  assertions: %s PASS / %s FAIL\n' "$vc_pass" "$vc_fail"
[ "$vc_fail" = "0" ] && [ "$vc_pass" -ge 20 ] \
  && printf '  \033[32mPASS\033[0m  version-check.php\n' \
  || { printf '  \033[31mFAIL\033[0m  version-check.php\n'; fail=1; }

printf '\n'
if [ "$fail" = "0" ]; then
  printf '\033[1;32mALL SUITES GREEN\033[0m — 19 doctor + 12×3 live + 14 multi-UUID + 8 cancelled + %s version-check assertions\n' "$vc_pass"
else
  printf '\033[1;31mFAILURES PRESENT\033[0m\n'
fi
exit "$fail"
