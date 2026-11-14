<?php
/**
 * Dr Plus Child Theme — Capacity module deployment verifier.
 *
 * Answers one question: "is the code running on THIS server the fixed code?"
 *
 * Why this file exists: a site can appear updated while still serving old code, because
 * PHP OPcache, a page cache, or a partial file upload all mask the swap. Reading hashes
 * from the filesystem settles it in one request.
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.1
 */

// Hardening. The content of this page discloses file paths and hashes, which is useful to an
// attacker probing a site and useless to the public, so it is limited to authenticated
// administrators. It is a plain PHP file (not routed through WordPress), so the WordPress
// bootstrap is loaded explicitly to reach current_user_can().
$drplus_capacity_vc_abspath = dirname( __DIR__, 3 ) . '/../../../wp-load.php';
$drplus_capacity_vc_loaded  = false;

if ( file_exists( $drplus_capacity_vc_abspath ) ) {
	require_once $drplus_capacity_vc_abspath;
	$drplus_capacity_vc_loaded = true;
}

// Resolve wp-load.php by walking up from this file when the relative guess is wrong
// (staging installs and non-standard directory layouts are common on shared hosting).
if ( ! $drplus_capacity_vc_loaded ) {
	$drplus_capacity_vc_dir = __DIR__;
	for ( $i = 0; $i < 8 && ! $drplus_capacity_vc_loaded; $i++ ) {
		$drplus_capacity_vc_dir = dirname( $drplus_capacity_vc_dir );
		$drplus_capacity_vc_try = $drplus_capacity_vc_dir . '/wp-load.php';
		if ( file_exists( $drplus_capacity_vc_try ) ) {
			require_once $drplus_capacity_vc_try;
			$drplus_capacity_vc_loaded = true;
		}
	}
}

if ( ! $drplus_capacity_vc_loaded || ! function_exists( 'current_user_can' ) ) {
	header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
	exit( 'WordPress could not be located from this file. Delete it and use the admin check instead.' );
}

if ( ! current_user_can( 'manage_options' ) ) {
	header( 'HTTP/1.1 403 Forbidden', true, 403 );
	exit( 'Forbidden. Log in as an administrator first, then reload this page.' );
}

header( 'Content-Type: text/html; charset=utf-8' );
header( 'X-Robots-Tag: noindex, nofollow', true );
header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );

/**
 * The checks this page performs. Each entry is a marker string that MUST be present in the
 * fixed code, plus the file it belongs in. A missing marker means old code is still being served.
 */
$drplus_capacity_vc_checks = array(
	array(
		'label'  => 'Gregorian date guard present',
		'file'   => 'class-capacity-repository.php',
		'marker' => 'assert_queryable_date',
		'hint'   => 'Repository is the FIXED version (Jalali dates cannot reach SQL).',
	),
	array(
		'label'  => 'Jalali detection in validator',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'looks_jalali',
		'hint'   => 'Validator is the FIXED version (Jalali dates rejected).',
	),
	array(
		'label'  => 'Missing-hour fail-closed guard',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'ERR_MISSING_TIME',
		'hint'   => 'Validator refuses an empty visit hour instead of counting 0 and allowing it.',
	),
	array(
		'label'  => 'Legacy online alias handled',
		'file'   => 'class-capacity-service.php',
		'marker' => 'ONLINE_ALIASES',
		'hint'   => 'Phone consultations using the legacy id are exempt, not counted as rooms.',
	),
	array(
		'label'  => 'Debug logging REMOVED',
		'file'   => 'class-capacity-repository.php',
		'marker' => 'DRPLUS COUNT SQL',
		'hint'   => 'This marker must be ABSENT. If found, the file being served still logs SQL.',
		'invert' => true,
	),
	array(
		'label'  => 'Capacity debug logging REMOVED',
		'file'   => 'class-capacity-validator.php',
		'marker' => 'DRPLUS CAPACITY',
		'hint'   => 'This marker must be ABSENT. If found, the file being served still logs capacity.',
		'invert' => true,
	),
);

$drplus_capacity_vc_dir = __DIR__;
$drplus_capacity_vc_fail = 0;
$drplus_capacity_vc_rows = array();

foreach ( $drplus_capacity_vc_checks as $drplus_capacity_vc_check ) {
	$drplus_capacity_vc_path = $drplus_capacity_vc_dir . '/' . $drplus_capacity_vc_check['file'];
	$drplus_capacity_vc_src  = file_exists( $drplus_capacity_vc_path ) ? file_get_contents( $drplus_capacity_vc_path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions
	$drplus_capacity_vc_has  = ( false !== $drplus_capacity_vc_src && false !== strpos( $drplus_capacity_vc_src, $drplus_capacity_vc_check['marker'] ) );

	// A normal check wants the marker present; an "invert" check wants it absent.
	$drplus_capacity_vc_ok = empty( $drplus_capacity_vc_check['invert'] ) ? $drplus_capacity_vc_has : ! $drplus_capacity_vc_has;

	if ( ! $drplus_capacity_vc_ok ) {
		$drplus_capacity_vc_fail++;
	}

	$drplus_capacity_vc_rows[] = array(
		'label' => $drplus_capacity_vc_check['label'],
		'file'  => $drplus_capacity_vc_check['file'],
		'ok'    => $drplus_capacity_vc_ok,
		'hint'  => $drplus_capacity_vc_check['hint'],
	);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex,nofollow">
	<title>Dr Plus Capacity — Deployment Check</title>
	<style>
		body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;margin:0;padding:32px;background:#f6f7f7;color:#1d2327;line-height:1.6}
		.wrap{max-width:860px;margin:0 auto;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:28px 32px}
		h1{font-size:1.5rem;margin:0 0 4px}
		.sub{color:#646970;margin:0 0 24px;font-size:.9rem}
		.banner{padding:14px 18px;border-radius:6px;font-weight:600;margin:0 0 24px;border-left:4px solid}
		.pass{background:#edfaef;border-color:#00a32a;color:#005c12}
		.fail{background:#fcf0f1;border-color:#d63638;color:#8a1f21}
		table{width:100%;border-collapse:collapse;font-size:.9rem}
		th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #f0f0f1;vertical-align:top}
		th{background:#f6f7f7;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;color:#646970}
		.ok{color:#00a32a;font-weight:700}
		.no{color:#d63638;font-weight:700}
		code{background:#f0f0f1;padding:2px 5px;border-radius:3px;font-size:.85em;word-break:break-all}
		.hint{color:#646970;font-size:.85rem;display:block;margin-top:3px}
		.hash{font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.8rem;color:#646970}
		.files{margin:28px 0 0}
		.files h2{font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:#646970;margin:0 0 10px}
		.files td{font-size:.8rem;padding:6px 12px}
	</style>
</head>
<body>
<div class="wrap">
	<h1>Dr Plus Capacity — Deployment Check</h1>
	<p class="sub">Reads the files on this server directly. If every row is green, the fixed code is what PHP will execute.</p>

	<?php if ( 0 === $drplus_capacity_vc_fail ) : ?>
		<div class="banner pass">All checks passed — this server is running the fixed code.</div>
	<?php else : ?>
		<div class="banner fail"><?php echo (int) $drplus_capacity_vc_fail; ?> check(s) failed — this server is NOT running the fixed code. Do not trust capacity enforcement yet.</div>
	<?php endif; ?>

	<table>
		<thead>
			<tr><th>Check</th><th>File</th><th>Result</th></tr>
		</thead>
		<tbody>
		<?php foreach ( $drplus_capacity_vc_rows as $drplus_capacity_vc_row ) : ?>
			<tr>
				<td>
					<?php echo esc_html( $drplus_capacity_vc_row['label'] ); ?>
					<span class="hint"><?php echo esc_html( $drplus_capacity_vc_row['hint'] ); ?></span>
				</td>
				<td><code><?php echo esc_html( $drplus_capacity_vc_row['file'] ); ?></code></td>
				<td>
					<?php if ( $drplus_capacity_vc_row['ok'] ) : ?>
						<span class="ok">PASS</span>
					<?php else : ?>
						<span class="no">FAIL</span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="files">
		<h2>File fingerprints</h2>
		<table>
			<tbody>
			<?php
			$drplus_capacity_vc_list = glob( $drplus_capacity_vc_dir . '/*.php' );
			sort( $drplus_capacity_vc_list );
			foreach ( $drplus_capacity_vc_list as $drplus_capacity_vc_file ) :
				if ( 'version-check.php' === basename( $drplus_capacity_vc_file ) ) {
					continue;
				}
				?>
				<tr>
					<td><code><?php echo esc_html( basename( $drplus_capacity_vc_file ) ); ?></code></td>
					<td class="hash"><?php echo esc_html( md5_file( $drplus_capacity_vc_file ) ); ?></td>
					<td class="hash"><?php echo esc_html( gmdate( 'Y-m-d H:i:s', filemtime( $drplus_capacity_vc_file ) ) ); ?> UTC</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<p class="sub" style="margin-top:24px">Delete this file once deployment is confirmed — it is diagnostic tooling, not runtime code.</p>
</div>
</body>
</html>
