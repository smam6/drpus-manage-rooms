#!/usr/bin/env bash
# Runs every conflict-policy scenario in its OWN PHP process.
#
# Why separate processes: the WooCommerce cart, the wc_notices store and the capacity
# advisory lock all persist for the life of a PHP process. Running the matrix in one
# process produced a false ACCEPT for online->online, which cost an hour to track down.
# One process per scenario cannot leak.

set -uo pipefail
HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
RUNNER="${DRPLUS_RUNNER:-/tmp/realwp/run.mjs}"

pass=0
fail=0

check() { # mode scenario expected
	local mode="$1" scenario="$2" expected="$3"
	# The runner only stages siblings referenced as `require __DIR__ . "/x.php"`,
	# so the wrapper has to sit next to the script, not in /tmp.
	local tmp="${HERE}/.tmp-scenario.php"
	{
		echo "<?php"
		echo "define('DRPLUS_MODE', '${mode}');"
		echo "define('DRPLUS_SCENARIO', '${scenario}');"
		# run.mjs stages only the wrapper's DIRECT requires, not the transitive ones,
		# so the bootstrap has to be named here as well.
		echo "require_once __DIR__ . '/00-bootstrap.php';"
		echo "require __DIR__ . '/10-conflict-policy.php';"
	} > "$tmp"
	local out
	out="$(node "$RUNNER" "$tmp" 2>/dev/null | grep -a '^RESULT ' | head -1)"
	rm -f "$tmp"

	local actual
	actual="$(echo "$out" | awk '{print $NF}')"
	if [ "$actual" = "$expected" ]; then
		printf '  \033[32mPASS\033[0m  %-5s %-24s %s\n' "$mode" "$scenario" "$actual"
		pass=$((pass+1))
	else
		printf '  \033[31mFAIL\033[0m  %-5s %-24s expected %s got %s\n' "$mode" "$scenario" "$expected" "${actual:-<no output>}"
		fail=$((fail+1))
	fi
}

echo "  --- room mode (default: rooms are exclusive, virtual visits are free) ---"
check room in_person_then_online ACCEPT
check room online_then_in_person ACCEPT
check room same_specialist_twice REJECT
check room full_room_then_online ACCEPT
check room full_room_then_person REJECT
check room online_then_online    ACCEPT

echo "  --- any mode (one specialist, one booking per hour, whatever the type) ---"
check any  in_person_then_online REJECT
check any  online_then_in_person REJECT
check any  same_specialist_twice REJECT
check any  full_room_then_online ACCEPT
check any  full_room_then_person REJECT
check any  online_then_online    REJECT

echo
echo "CONFLICT-POLICY RESULT: ${pass} passed, ${fail} failed"
[ "$fail" -eq 0 ] || exit 1
