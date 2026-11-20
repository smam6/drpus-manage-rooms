<?php
/**
 * why-blocked.php — «چرا می‌گوید این متخصص رزرو دارد؟»
 *
 * Upload next to wp-load.php, open in a browser as an administrator, and it prints the
 * EXACT rows that make the capacity module refuse a booking. Read-only: it never writes
 * to the booking table, never creates or cancels anything.
 *
 * Why this exists: the message «این متخصص در این ساعت قبلاً رزرو دارد» is only produced
 * when a SQL query actually returned a row. From the outside the hour looks free, so the
 * row is invisible — it is stale, it points at a deleted order, it is filed under a
 * different spelling of the clinic, or the time is not the one you think. Guessing which
 * of those it is wastes time; this prints it.
 *
 * DELETE THIS FILE once the cause is known. It discloses booking data.
 *
 * @package DrPlusChild\Capacity
 */

// --- Gatekeeping ------------------------------------------------------------------------
$why_blocked_root = dirname( __FILE__ );
$why_blocked_loaded = false;
for ( $why_blocked_i = 0; $why_blocked_i < 8 && ! $why_blocked_loaded; $why_blocked_i++ ) {
	$why_blocked_try = ( 0 === $why_blocked_i ? $why_blocked_root : dirname( $why_blocked_root, $why_blocked_i ) ) . '/wp-load.php';
	if ( file_exists( $why_blocked_try ) ) {
		require_once $why_blocked_try;
		$why_blocked_loaded = true;
	}
}

if ( ! $why_blocked_loaded ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "wp-load.php پیدا نشد. این فایل را کنار wp-load.php بگذارید.\n";
	exit;
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'فقط مدیر سایت.' );
}

header( 'Content-Type: text/plain; charset=utf-8' );

global $wpdb;
$t_booking   = $wpdb->prefix . 'drplus_booking';
$t_special   = $wpdb->prefix . 'drplus_specialists';

$has_booking = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t_booking ) ) === $t_booking );
if ( ! $has_booking ) {
	echo "جدول {$t_booking} پیدا نشد.\n";
	exit;
}

// --- What the caller wants to look at ---------------------------------------------------
$look_date = isset( $_GET['date'] ) ? sanitize_text_field( wp_unslash( $_GET['date'] ) ) : gmdate( 'Y-m-d' );
$look_time = isset( $_GET['time'] ) ? sanitize_text_field( wp_unslash( $_GET['time'] ) ) : '';
$look_spec = isset( $_GET['specialist'] ) ? absint( $_GET['specialist'] ) : 0;
$look_off  = isset( $_GET['office'] ) ? sanitize_text_field( wp_unslash( $_GET['office'] ) ) : '';

echo "=== چرا می‌گوید «قبلاً رزرو دارد»؟ ===\n\n";
echo "تاریخ بررسی‌شده: {$look_date}\n";
if ( '' !== $look_time ) {
	echo "ساعت بررسی‌شده: {$look_time}\n";
}
if ( $look_spec > 0 ) {
	echo "متخصص: {$look_spec}\n";
}
if ( '' !== $look_off ) {
	echo "کلینیک: {$look_off}\n";
}
echo "\n";

// --- 1. Is the module even on? ----------------------------------------------------------
echo "--- ۱) وضعیت ماژول ---\n";
$enabled = class_exists( 'DrPlus_Child_Capacity_Service' ) ? DrPlus_Child_Capacity_Service::enabled() : null;
if ( null === $enabled ) {
	echo "  کلاس‌های ماژول بارگذاری نشده‌اند! قالب فرزند فعال نیست یا فایل‌ها ناقص‌اند.\n";
} else {
	echo '  enabled() = ' . ( $enabled ? 'true' : 'false' ) . "\n";
	echo '  conflict_mode() = ' . DrPlus_Child_Capacity_Service::conflict_mode() . "\n";
}

// --- 2. Every row for that day, with the reason it does or does not block ---------------
echo "\n--- ۲) ردیف‌های روز {$look_date} (هر کدام چرا قفل می‌کند یا نمی‌کند) ---\n";
$sql = "SELECT book_id, specialist_id, office_id, start_time, end_time, order_id, order_status, deleted\n"
	. "FROM {$t_booking} WHERE `date` = %s ORDER BY start_time, specialist_id";
$rows = $wpdb->get_results( $wpdb->prepare( $sql, $look_date ), ARRAY_A );

if ( empty( $rows ) ) {
	echo "  هیچ ردیفی برای این روز نیست.\n";
	echo "  ⇒ اگر باز هم پیام می‌گیرید، تاریخ اشتباهی بررسی شده یا ردیف روز دیگری است.\n";
} else {
	printf( "  %-6s %-8s %-38s %-10s %-8s %-13s %-3s %s\n", 'book', 'spec', 'office_id', 'time', 'order', 'status', 'del', 'holds?' );
	foreach ( $rows as $r ) {
		$holds = 'n/a (deleted)';
		if ( '0' === (string) $r['deleted'] && class_exists( 'DrPlus_Child_Capacity_Repository' ) ) {
			$holds = DrPlus_Child_Capacity_Repository::row_holds_slot( $r ) ? 'HOLDS' : 'free';
		}
		printf(
			"  %-6s %-8s %-38s %-10s %-8s %-13s %-3s %s\n",
			$r['book_id'],
			$r['specialist_id'],
			$r['office_id'],
			$r['start_time'],
			$r['order_id'],
			$r['order_status'],
			$r['deleted'],
			$holds
		);
	}
}

// --- 3. Rows whose time is not a clean HH:MM:00 -----------------------------------------
echo "\n--- ۳) ردیف‌هایی با ساعت غیرعادی (منبع رایج «قفلِ نامرئی») ---\n";
$odd = $wpdb->get_results(
	"SELECT book_id, specialist_id, `date`, start_time, order_status, order_id
	 FROM {$t_booking}
	 WHERE deleted = 0
	   AND ( start_time NOT LIKE '__:__:__' OR end_time NOT LIKE '__:__:__' OR start_time = '00:00:00' )
	 ORDER BY book_id DESC LIMIT 40",
	ARRAY_A
);
if ( empty( $odd ) ) {
	echo "  هیچ ردیف غیرعادی نیست. ساعت‌ها همه HH:MM:SS هستند.\n";
} else {
	foreach ( $odd as $r ) {
		printf(
			"  book #%s  spec=%s  %s  ساعت=%s  order=%s  status=%s\n",
			$r['book_id'],
			$r['specialist_id'],
			$r['date'],
			$r['start_time'],
			$r['order_id'],
			$r['order_status']
		);
	}
	echo "\n  ⇒ اگر ساعتی «00:00:00» است، آن ردیف برای هر ساعتی می‌تواند تطابق پیدا کند\n";
	echo "    و عملاً همهٔ روز را می‌بندد. این بیشترین دلیلِ «رزرو ندارد ولی می‌گوید دارد» است.\n";
}

// --- 4. Rows with no order --------------------------------------------------------------
echo "\n--- ۴) ردیف‌های بدون سفارش (order_id = 0) که ساعت را برای همیشه قفل می‌کنند ---\n";
$noorder = $wpdb->get_results(
	"SELECT book_id, specialist_id, office_id, `date`, start_time, order_status
	 FROM {$t_booking} WHERE deleted = 0 AND ( order_id = 0 OR order_id IS NULL )
	 ORDER BY `date` DESC LIMIT 40",
	ARRAY_A
);
if ( empty( $noorder ) ) {
	echo "  هیچ ردیف بی‌سفارشی نیست.\n";
} else {
	foreach ( $noorder as $r ) {
		printf(
			"  book #%s  spec=%s  %s  %s  office=%s  status=%s\n",
			$r['book_id'],
			$r['specialist_id'],
			$r['date'],
			$r['start_time'],
			$r['office_id'],
			$r['order_status']
		);
	}
	echo "\n  ⇒ این‌ها قفل می‌کنند و در لیست سفارش‌های ووکامرس دیده نمی‌شوند.\n";
}

// --- 5. Rows pointing at an order that no longer exists ---------------------------------
echo "\n--- ۵) ردیف‌هایی که به سفارش ناموجود اشاره می‌کنند ---\n";
$orphans = $wpdb->get_results(
	"SELECT b.book_id, b.specialist_id, b.`date`, b.start_time, b.order_id
	 FROM {$t_booking} b
	 LEFT JOIN {$wpdb->posts} p ON p.ID = b.order_id
	 WHERE b.deleted = 0 AND b.order_id > 0 AND p.ID IS NULL
	 ORDER BY b.book_id DESC LIMIT 40",
	ARRAY_A
);
if ( empty( $orphans ) ) {
	echo "  هیچ ردیف یتیمی نیست.\n";
} else {
	foreach ( $orphans as $r ) {
		printf(
			"  book #%s  spec=%s  %s  %s  → order #%s وجود ندارد\n",
			$r['book_id'],
			$r['specialist_id'],
			$r['date'],
			$r['start_time'],
			$r['order_id']
		);
	}
	echo "\n  ⇒ این‌ها باید خودکار آزاد شوند (order_releases_slot). اگر HOLDS نشان می‌دهند، باگ است.\n";
}

// --- 6. The exact query the validator runs ----------------------------------------------
if ( $look_spec > 0 && '' !== $look_date && class_exists( 'DrPlus_Child_Capacity_Repository' ) ) {
	echo "\n--- ۶) همان کوئری‌ای که اعتبارسنج اجرا می‌کند ---\n";

	$times = array();
	if ( '' !== $look_time ) {
		$norm  = DrPlus_Child_Capacity_Repository::normalize_time( $look_time );
		$times = array( $look_time, $norm, rtrim( $norm, ':00' ) . ':00' );
	} else {
		$times = $wpdb->get_col(
			$wpdb->prepare( "SELECT DISTINCT start_time FROM {$t_booking} WHERE `date` = %s AND deleted = 0", $look_date )
		);
	}

	foreach ( array_unique( array_filter( $times ) ) as $t ) {
		$blocked = DrPlus_Child_Capacity_Repository::specialist_already_booked( $look_spec, $look_date, $t );
		printf(
			"  normalise('%s') = '%s'   specialist_already_booked = %s\n",
			$t,
			DrPlus_Child_Capacity_Repository::normalize_time( $t ),
			$blocked ? 'TRUE  ← همین باعث پیام می‌شود' : 'false'
		);
		if ( '' !== $look_off ) {
			printf(
				"      scoped to clinic '%s' = %s\n",
				$look_off,
				DrPlus_Child_Capacity_Repository::specialist_conflict_in_scope( $look_spec, $look_date, $t, $look_off ) ? 'TRUE' : 'false'
			);
		}
	}

	$reason = DrPlus_Child_Capacity_Repository::last_unverifiable_reason();
	echo '  last_unverifiable_reason = ' . ( '' === $reason ? '(none — the inputs were usable)' : "'{$reason}'" ) . "\n";
	if ( '' !== $reason ) {
		echo "  ⇒ یعنی ورودی ناقص بوده، نه اینکه رزروی باشد. پیام باید «قابل بررسی نیست» باشد.\n";
	}
}

// --- 7. Office id spellings -------------------------------------------------------------
echo "\n--- ۷) شناسه‌های کلینیک (اگر رزرو با املای دیگری ذخیره شده باشد) ---\n";
$offices = $wpdb->get_results(
	"SELECT office_id, COUNT(*) AS n FROM {$t_booking} WHERE deleted = 0 GROUP BY office_id ORDER BY n DESC",
	ARRAY_A
);
foreach ( $offices as $o ) {
	$trailing_ws = ( trim( (string) $o['office_id'] ) !== (string) $o['office_id'] ) ? '   ← فاصلهٔ اضافه دارد!' : '';
	printf( "  %-42s %s ردیف%s\n", "'" . $o['office_id'] . "'", $o['n'], $trailing_ws );
}

echo "\n=== پایان ===\n";
echo "این خروجی را کامل کپی کنید. اگر ردیف مشکوکی دیدید، شمارهٔ book آن را هم بفرستید.\n";
