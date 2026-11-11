#!/usr/bin/env bash
# Dr Plus Capacity Diagnostics — Read-only
# Run from WordPress root: bash diagnostics.sh
# Output: diag-output/ directory with D1.txt through D12.txt

# Intentionally NO `set -e` so every diagnostic item completes even if one fails.
set -u

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Detect WordPress root (assume script is at WP root; fallback to script directory).
WP_ROOT="$(pwd)"
if [ ! -d "$WP_ROOT/wp-content/themes" ] && [ -d "$SCRIPT_DIR/wp-content/themes" ]; then
	WP_ROOT="$SCRIPT_DIR"
fi

cd "$WP_ROOT" 2>/dev/null || true

OUT_DIR="$WP_ROOT/diag-output"
mkdir -p "$OUT_DIR"

# Locate wp-config.php (WP root or one directory above).
WP_CONFIG=""
if [ -f "$WP_ROOT/wp-config.php" ]; then
	WP_CONFIG="$WP_ROOT/wp-config.php"
elif [ -f "$(dirname "$WP_ROOT")/wp-config.php" ]; then
	WP_CONFIG="$(dirname "$WP_ROOT")/wp-config.php"
fi

# Detect table prefix safely from wp-config.php (never expose DB credentials).
TABLE_PREFIX=""
if [ -n "$WP_CONFIG" ]; then
	TABLE_PREFIX="$(grep -E "^[[:space:]]*\\\$table_prefix[[:space:]]*=" "$WP_CONFIG" 2>/dev/null | head -n 1 | sed -E "s/.*=[[:space:]]*['\"]([^'\"]+)['\"].*/\1/")"
fi

PARENT_THEME="wp-content/themes/dr-plus"
CHILD_THEME="wp-content/themes/dr-plus-child"

# Track status of each output file in parallel arrays.
declare -a FILE_LIST=()
declare -a STATUS_LIST=()

record_status() {
	FILE_LIST+=("$1")
	STATUS_LIST+=("$2")
}

append_missing() {
	local target="$1"
	local path="$2"
	echo "[MISSING] File not found: $path" >> "$target"
}

run_cat_numbered() {
	local target="$1"
	local path="$2"
	echo "--- FILE: $path ---" >> "$target"
	if [ -f "$path" ]; then
		nl -ba "$path" >> "$target" 2>&1
		return $?
	else
		append_missing "$target" "$path"
		return 1
	fi
}

# ==============================================================================
# ENVIRONMENT.txt
# ==============================================================================
ENV_FILE="$OUT_DIR/ENVIRONMENT.txt"
{
	echo "=== DRPLUS LIVE ENVIRONMENT DIAGNOSTIC (READ-ONLY) ==="
	echo "Timestamp (UTC): $(date -u '+%Y-%m-%d %H:%M:%S UTC' 2>/dev/null || date)"
	echo "Detected WP_ROOT: $WP_ROOT"
	echo "wp-config.php path: ${WP_CONFIG:-NOT FOUND}"
	echo "Table prefix: ${TABLE_PREFIX:-NOT DETECTED}"
	echo ""

	echo "--- PHP Version ---"
	if command -v php >/dev/null 2>&1; then
		php -v 2>&1 | head -n 1
	else
		echo "php binary not found in PATH"
	fi
	echo ""

	echo "--- WordPress Version ---"
	if [ -f "wp-includes/version.php" ]; then
		grep -n "wp_version\b\|wp_db_version\b\|required_php_version\b" wp-includes/version.php 2>&1
	else
		echo "wp-includes/version.php not found"
	fi
	echo ""

	echo "--- MySQL Version & Active Theme (Read-Only DB Inspection) ---"
	if command -v wp >/dev/null 2>&1; then
		echo "[via wp-cli --skip-themes --skip-plugins]"
		MYSQL_VER="$(wp --skip-themes --skip-plugins eval 'global $wpdb; echo $wpdb->db_version();' 2>/dev/null || true)"
		echo "MySQL version: ${MYSQL_VER:-UNAVAILABLE}"
		echo "template: $(wp --skip-themes --skip-plugins option get template 2>/dev/null || echo 'UNAVAILABLE')"
		echo "stylesheet: $(wp --skip-themes --skip-plugins option get stylesheet 2>/dev/null || echo 'UNAVAILABLE')"
		echo "drplus_capacity_db_version: $(wp --skip-themes --skip-plugins option get drplus_capacity_db_version 2>/dev/null || echo 'UNSET')"
		wp --skip-themes --skip-plugins eval '
			global $wpdb;
			$rows = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE \"drplus_capacity_%\" ORDER BY option_name", ARRAY_A);
			foreach ((array)$rows as $r) { echo $r["option_name"] . ": " . $r["option_value"] . "\n"; }
			$o = get_option("drplus");
			if (is_array($o)) {
				foreach (["enable-booking", "booking-page-id", "booking-datepicker", "guest-booking"] as $k) {
					echo "drplus[" . $k . "]: " . (isset($o[$k]) ? var_export($o[$k], true) : "UNSET") . "\n";
				}
			}
			$p = get_option("active_plugins");
			if (is_array($p)) { echo "active_plugins: " . implode(", ", $p) . "\n"; }
		' 2>/dev/null || true
	elif command -v php >/dev/null 2>&1 && [ -f "wp-load.php" ]; then
		echo "[via php SHORTINIT read-only query]"
		php -r '
			define("SHORTINIT", true);
			$_SERVER["HTTP_HOST"] = $_SERVER["HTTP_HOST"] ?? "localhost";
			@require_once "wp-load.php";
			global $wpdb;
			if (isset($wpdb) && is_object($wpdb)) {
				echo "MySQL version: " . $wpdb->db_version() . "\n";
				$rows = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name IN (\x27template\x27,\x27stylesheet\x27,\x27drplus_capacity_db_version\x27) OR option_name LIKE \x27drplus_capacity_%\x27 ORDER BY option_name", ARRAY_A);
				foreach ((array)$rows as $r) {
					echo $r["option_name"] . ": " . $r["option_value"] . "\n";
				}
				$drplus_raw = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = \x27drplus\x27 LIMIT 1");
				if ($drplus_raw) {
					$o = @unserialize($drplus_raw);
					if (is_array($o)) {
						foreach (["enable-booking", "booking-page-id", "booking-datepicker", "guest-booking"] as $k) {
							echo "drplus[" . $k . "]: " . (isset($o[$k]) ? var_export($o[$k], true) : "UNSET") . "\n";
						}
					}
				}
				$plugins_raw = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = \x27active_plugins\x27 LIMIT 1");
				if ($plugins_raw) {
					$p = @unserialize($plugins_raw);
					if (is_array($p)) {
						echo "active_plugins: " . implode(", ", $p) . "\n";
					}
				}
			} else {
				echo "wpdb unavailable via SHORTINIT (check D9 SQL)\n";
			}
		' 2>&1 || true
	else
		echo "Neither wp-cli nor php/wp-load.php available — check D9 SQL."
	fi
	echo ""

	echo "--- wp-config.php Debug/Cache Flags (read-only, no credentials) ---"
	if [ -n "$WP_CONFIG" ]; then
		grep -nE "WP_DEBUG|WP_CACHE|SCRIPT_DEBUG|SAVEQUERIES|WP_ENVIRONMENT_TYPE|WP_MEMORY_LIMIT" "$WP_CONFIG" 2>&1 || echo "(no matching debug/cache constants)"
	else
		echo "wp-config.php not found"
	fi
	echo ""

	echo "--- Installed Plugins Directory ---"
	if [ -d "wp-content/plugins" ]; then
		ls -1 wp-content/plugins 2>&1
	else
		echo "wp-content/plugins not found"
	fi
} > "$ENV_FILE"
record_status "ENVIRONMENT.txt" "OK"

# ==============================================================================
# D1: Parent theme style.css head + functions.php grep
# ==============================================================================
D1_FILE="$OUT_DIR/D1.txt"
D1_OK=1
{
	echo "=== D1: Parent Theme style.css & DRPLUS_DEV ==="
	if [ -f "$PARENT_THEME/style.css" ]; then
		echo "--- head -n 25 $PARENT_THEME/style.css ---"
		head -n 25 "$PARENT_THEME/style.css" 2>&1
	else
		append_missing "$D1_FILE" "$PARENT_THEME/style.css"
		D1_OK=0
	fi
	echo ""
	if [ -f "$PARENT_THEME/functions.php" ]; then
		echo "--- grep DRPLUS_VERSION / DRPLUS_DEV in $PARENT_THEME/functions.php ---"
		grep -n "DRPLUS_VERSION\|DRPLUS_DEV" "$PARENT_THEME/functions.php" 2>&1 || echo "(no matches)"
	else
		append_missing "$D1_FILE" "$PARENT_THEME/functions.php"
		D1_OK=0
	fi
	echo ""
	if [ -f "$PARENT_THEME/inc/Scripts.php" ]; then
		echo "--- grep drplus-booking / pdp in $PARENT_THEME/inc/Scripts.php ---"
		grep -n -C 4 "drplus-booking\|persian-datepicker\|booking\.js\|booking\.min\.js" "$PARENT_THEME/inc/Scripts.php" 2>&1 || echo "(no matches)"
	fi
} > "$D1_FILE"
record_status "D1.txt" "$([ "$D1_OK" -eq 1 ] && echo 'OK' || echo 'PARTIAL')"

# ==============================================================================
# D2: template-booking-step-time.php (full file, line-numbered)
# ==============================================================================
D2_FILE="$OUT_DIR/D2.txt"
D2_OK=1
{
	echo "=== D2: Booking Step Time Template ==="
} > "$D2_FILE"
if ! run_cat_numbered "$D2_FILE" "$PARENT_THEME/templates/booking/template-booking-step-time.php"; then
	D2_OK=0
fi
{
	echo ""
	echo "--- Check template-booking-base.php & nav-btns for #booking-form / data-section ---"
	if [ -f "$PARENT_THEME/templates/booking/template-booking-base.php" ]; then
		grep -n -C 3 "booking-form\|booking_wrap\|data-section" "$PARENT_THEME/templates/booking/template-booking-base.php" 2>&1 || true
	fi
	if [ -f "$PARENT_THEME/templates/booking/template-booking-nav-btns.php" ]; then
		nl -ba "$PARENT_THEME/templates/booking/template-booking-nav-btns.php" 2>&1
	fi
} >> "$D2_FILE"
record_status "D2.txt" "$([ "$D2_OK" -eq 1 ] && echo 'OK' || echo 'PARTIAL')"

# ==============================================================================
# D3: booking.js and booking.min.js — check both exist, check DRPLUS_DEV
# ==============================================================================
D3_FILE="$OUT_DIR/D3.txt"
D3_OK=1
{
	echo "=== D3: Parent Theme booking.js & booking.min.js ==="
	echo "--- ls -lh $PARENT_THEME/assets/js/booking* ---"
	ls -lh "$PARENT_THEME/assets/js/booking"* 2>&1 || D3_OK=0
	echo ""
	echo "--- Checksums ---"
	if command -v sha256sum >/dev/null 2>&1; then
		sha256sum "$PARENT_THEME/assets/js/booking"* 2>&1 || true
	elif command -v md5sum >/dev/null 2>&1; then
		md5sum "$PARENT_THEME/assets/js/booking"* 2>&1 || true
	fi
	echo ""
	echo "--- DRPLUS_DEV in functions.php ---"
	if [ -f "$PARENT_THEME/functions.php" ]; then
		grep -n "DRPLUS_DEV" "$PARENT_THEME/functions.php" 2>&1 || true
	fi
	echo ""
	echo "--- Child theme booking JS override check ---"
	ls -lh "$CHILD_THEME/assets/js/" 2>&1 || true
	echo ""
} > "$D3_FILE"
if ! run_cat_numbered "$D3_FILE" "$PARENT_THEME/assets/js/booking.js"; then
	D3_OK=0
fi
{
	echo ""
	echo "--- booking.min.js key snippets (around booking-date, selectedOffice, mainOffice, drplus_get_available_times) ---"
	if [ -f "$PARENT_THEME/assets/js/booking.min.js" ]; then
		grep -oE ".{0,80}(booking-date|selectedOffice|mainOffice|drplus_get_available_times|mjpersianDatepicker).{0,80}" "$PARENT_THEME/assets/js/booking.min.js" 2>&1 | head -n 30 || true
	else
		echo "[MISSING] $PARENT_THEME/assets/js/booking.min.js"
		D3_OK=0
	fi
} >> "$D3_FILE"
record_status "D3.txt" "$([ "$D3_OK" -eq 1 ] && echo 'OK' || echo 'PARTIAL')"

# ==============================================================================
# D4: functions/booking.php (full)
# ==============================================================================
D4_FILE="$OUT_DIR/D4.txt"
{
	echo "=== D4: Parent Theme functions/booking.php ==="
} > "$D4_FILE"
if run_cat_numbered "$D4_FILE" "$PARENT_THEME/functions/booking.php"; then
	record_status "D4.txt" "OK"
else
	record_status "D4.txt" "FAILED"
fi

# ==============================================================================
# D5: utils-booking.php lines 305-525 + AJAX/GetAvailableTimes.php (full)
# ==============================================================================
D5_FILE="$OUT_DIR/D5.txt"
D5_OK=1
{
	echo "=== D5: Parent utils-booking.php (305-525) & GetAvailableTimes.php ==="
	echo "--- $PARENT_THEME/inc/Utils/utils-booking.php lines 305..525 ---"
	if [ -f "$PARENT_THEME/inc/Utils/utils-booking.php" ]; then
		sed -n '305,525p' "$PARENT_THEME/inc/Utils/utils-booking.php" | nl -v 305 2>&1
	else
		append_missing "$D5_FILE" "$PARENT_THEME/inc/Utils/utils-booking.php"
		D5_OK=0
	fi
	echo ""
} > "$D5_FILE"
if ! run_cat_numbered "$D5_FILE" "$PARENT_THEME/inc/AJAX/GetAvailableTimes.php"; then
	D5_OK=0
fi
{
	echo ""
	echo "--- Grep drplus/booking/available_time_slots across themes ---"
	grep -rn "available_time_slots" wp-content/themes/ 2>&1 || true
} >> "$D5_FILE"
record_status "D5.txt" "$([ "$D5_OK" -eq 1 ] && echo 'OK' || echo 'PARTIAL')"

# ==============================================================================
# D6: class-capacity-service.php (full) + inc/capacity directory check
# ==============================================================================
D6_FILE="$OUT_DIR/D6.txt"
D6_OK=1
{
	echo "=== D6: Child Theme class-capacity-service.php & Capacity Module State ==="
	echo "--- ls -la $CHILD_THEME/inc/capacity/ ---"
	ls -la "$CHILD_THEME/inc/capacity/" 2>&1 || D6_OK=0
	echo ""
} > "$D6_FILE"
if ! run_cat_numbered "$D6_FILE" "$CHILD_THEME/inc/capacity/class-capacity-service.php"; then
	D6_OK=0
fi
{
	echo ""
} >> "$D6_FILE"
run_cat_numbered "$D6_FILE" "$CHILD_THEME/inc/capacity/class-capacity-repository.php" || true
record_status "D6.txt" "$([ "$D6_OK" -eq 1 ] && echo 'OK' || echo 'PARTIAL')"

# ==============================================================================
# D7: Browser Console Placeholder
# ==============================================================================
D7_FILE="$OUT_DIR/D7.txt"
cat > "$D7_FILE" <<'EOF'
=== D7: Browser Console DOM & Network State ===
This diagnostic requires browser console — see diag-console.js.
Run diag-console.js in browser instead.
EOF
record_status "D7.txt" "PLACEHOLDER"

# ==============================================================================
# D8: debug.log tail -200 + error_log tail -100
# ==============================================================================
D8_FILE="$OUT_DIR/D8.txt"
{
	echo "=== D8: Error & Debug Logs (Read-Only Tail) ==="
	echo "--- wp-content/debug.log (last 200 lines) ---"
	if [ -f "wp-content/debug.log" ]; then
		tail -n 200 "wp-content/debug.log" 2>&1
	else
		echo "NO wp-content/debug.log"
	fi
	echo ""
	echo "--- error_log in WP root (last 100 lines) ---"
	if [ -f "error_log" ]; then
		tail -n 100 "error_log" 2>&1
	else
		echo "NO ./error_log"
	fi
	echo ""
	for extra_log in "wp-admin/error_log" "$CHILD_THEME/error_log" "$PARENT_THEME/error_log"; do
		if [ -f "$extra_log" ]; then
			echo "--- $extra_log (last 100 lines) ---"
			tail -n 100 "$extra_log" 2>&1
			echo ""
		fi
	done
} > "$D8_FILE"
record_status "D8.txt" "OK"

# ==============================================================================
# D9: phpMyAdmin SQL Placeholder
# ==============================================================================
D9_FILE="$OUT_DIR/D9.txt"
cat > "$D9_FILE" <<'EOF'
=== D9: Database State (phpMyAdmin) ===
This diagnostic requires phpMyAdmin — see diag-queries.sql.
Run diag-queries.sql in phpMyAdmin instead.
EOF
record_status "D9.txt" "PLACEHOLDER"

# ==============================================================================
# D10: Browser Console AJAX Test Placeholder
# ==============================================================================
D10_FILE="$OUT_DIR/D10.txt"
cat > "$D10_FILE" <<'EOF'
=== D10: Browser Console Live AJAX Test ===
This diagnostic requires browser console — see diag-console.js.
Run diag-console.js in browser instead.
EOF
record_status "D10.txt" "PLACEHOLDER"

# ==============================================================================
# D11: Child theme style.css + ChildInit.php + functions.php + capacity.js (full)
# ==============================================================================
D11_FILE="$OUT_DIR/D11.txt"
D11_OK=1
{
	echo "=== D11: Child Theme Entry Files & capacity.js ==="
	echo "--- head -n 25 $CHILD_THEME/style.css ---"
	if [ -f "$CHILD_THEME/style.css" ]; then
		head -n 25 "$CHILD_THEME/style.css" 2>&1
	else
		append_missing "$D11_FILE" "$CHILD_THEME/style.css"
		D11_OK=0
	fi
	echo ""
} > "$D11_FILE"
run_cat_numbered "$D11_FILE" "$CHILD_THEME/ChildInit.php" || D11_OK=0
echo "" >> "$D11_FILE"
run_cat_numbered "$D11_FILE" "$CHILD_THEME/functions.php" || D11_OK=0
echo "" >> "$D11_FILE"
run_cat_numbered "$D11_FILE" "$CHILD_THEME/assets/js/capacity.js" || D11_OK=0
echo "" >> "$D11_FILE"
run_cat_numbered "$D11_FILE" "$CHILD_THEME/assets/css/capacity.css" || true
record_status "D11.txt" "$([ "$D11_OK" -eq 1 ] && echo 'OK' || echo 'PARTIAL')"

# ==============================================================================
# D12: Child theme bootstrap.php (full, line-numbered)
# ==============================================================================
D12_FILE="$OUT_DIR/D12.txt"
D12_OK=1
{
	echo "=== D12: Child Theme inc/capacity/bootstrap.php ==="
} > "$D12_FILE"
if ! run_cat_numbered "$D12_FILE" "$CHILD_THEME/inc/capacity/bootstrap.php"; then
	D12_OK=0
fi
{
	echo ""
	echo "--- Require/Lock-manager verification ---"
	if [ -f "$CHILD_THEME/inc/capacity/bootstrap.php" ]; then
		grep -n "require\|lock-manager" "$CHILD_THEME/inc/capacity/bootstrap.php" 2>&1 || true
	fi
	echo "--- Files present in $CHILD_THEME/inc/capacity/ ---"
	ls -1 "$CHILD_THEME/inc/capacity/" 2>&1 || true
} >> "$D12_FILE"
record_status "D12.txt" "$([ "$D12_OK" -eq 1 ] && echo 'OK' || echo 'FAILED')"

# ==============================================================================
# SUMMARY.txt
# ==============================================================================
SUMMARY_FILE="$OUT_DIR/SUMMARY.txt"
{
	echo "=== DRPLUS CAPACITY DIAGNOSTICS SUMMARY ==="
	echo "Generated: $(date -u '+%Y-%m-%d %H:%M:%S UTC' 2>/dev/null || date)"
	echo "WordPress Root: $WP_ROOT"
	echo "Table Prefix: ${TABLE_PREFIX:-UNKNOWN}"
	echo ""
	printf "%-18s %-10s %-10s %s\n" "FILE" "BYTES" "LINES" "STATUS"
	printf "%-18s %-10s %-10s %s\n" "----" "-----" "-----" "------"
	for i in "${!FILE_LIST[@]}"; do
		fname="${FILE_LIST[$i]}"
		fstatus="${STATUS_LIST[$i]}"
		fpath="$OUT_DIR/$fname"
		if [ -f "$fpath" ]; then
			fsize="$(wc -c < "$fpath" | tr -d ' ')"
			flines="$(wc -l < "$fpath" | tr -d ' ')"
		else
			fsize="0"
			flines="0"
		fi
		printf "%-18s %-10s %-10s %s\n" "$fname" "$fsize" "$flines" "$fstatus"
	done
} > "$SUMMARY_FILE"

cat "$SUMMARY_FILE"
echo ""
echo "Done. Output written to: $OUT_DIR"
exit 0
