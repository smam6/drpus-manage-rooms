#!/usr/bin/env bash
# Run all load scenarios. Requires: k6, node, mysql client, WP_URL, BOOKING_PAGE.
set -u
cd "$(dirname "$0")"
export WP_URL="${WP_URL:?set WP_URL}" BOOKING_PAGE="${BOOKING_PAGE:?set BOOKING_PAGE}"
mkdir -p results
for t in scenarios/T*.js; do
  echo "=== $t ==="
  case "$t" in *T7.js|*T8.js|*T10.js) node "$t" | tee "results/$(basename "$t").log" ;; *) k6 run "$t" | tee "results/$(basename "$t").log" ;; esac
done
echo "All scenarios finished. Check results/*.log and run helpers/db-verify.js for invariants."
