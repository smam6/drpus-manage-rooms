<?php
namespace DrPlus\Backend;

use DrPlus\Utils;
use DrPlus\Utils\Options;

class Dashboard {
	public static function enqueue_font() {
		$options = Options::get_options( [
			'wp-dashboard-font-change'	=> true,
			'wp-dashboard-font'			=> ['font-family' => 'IRANYekanX']
		] );
		if( !Utils::to_bool( $options['wp-dashboard-font-change'] ) ) return;

		wp_enqueue_style( 'drplus-wp-dashboard', DRPLUS_URI . "assets/css/backend/dashboard.min.css", [], DRPLUS_VERSION );
		$css_code = ":root{--dashboard-font: {$options['wp-dashboard-font']['font-family']}}";
		if( in_array( $options['wp-dashboard-font']['font-family'], array_keys( Utils::fonts() ) ) ) {
			wp_enqueue_style( 'drplus-wp-font', DRPLUS_URI . "assets/css/fonts/{$options['wp-dashboard-font']['font-family']}.min.css", [], DRPLUS_VERSION );
		}
		wp_add_inline_style( 'drplus-wp-dashboard', $css_code );
	}

	public static function avatar_disabled_notice() {
		if( !Utils::to_bool( get_option( 'show_avatars', true ) ) ) {
			$msg = __( 'Displaying user avatars on your site is disabled. Disabling this option will prevent the display of specialists images. You can enable it from the link below.', 'drplus' );
			?>
			<div class="notice notice-warning" style="padding-top:8px">
				<strong style="display:flex;align-items:center;gap:8px">
					<img src="<?php echo DRPLUS_URI ?>assets/images/logo-d.svg" alt="<?php esc_attr_e( "Doctor Plus", 'drplus' ) ?>" width="32">
					<?php echo $msg ?>
				</strong>
				<p>
					<a href="<?php echo admin_url( 'options-discussion.php#show_avatars' ) ?>" class="button button-primary"><?php esc_html_e( 'Change options', 'drplus' ) ?></a>
				</p>
			</div>
			<?php
		}
	}

	public static function disabled_wp_cron_notice() {
		if( defined( "DISABLE_WP_CRON" ) && DISABLE_WP_CRON ) {
			$hide = Utils::to_bool( get_option( 'drplus_hide_wp_cron_notice', false ) );
			if( !$hide ) {
				$msg = __( 'Cron jobs are disabled on your WordPress. Ensure that cron jobs are properly configured on your server (host). Otherwise, you can activate WP_Cron in your wp-config.php file.<br><strong>When cron jobs are disabled, some actions of the drplus cannot be done.</strong>', 'drplus' );
				?>
				<div class="notice notice-warning is-dismissible">
					<p><?php echo $msg ?></p>
					<a href="<?php echo add_query_arg( 'drplus-dismiss', wp_create_nonce( "drplus-dismiss-wp_cron" ), admin_url() ) ?>" type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.' ) ?></span></a>
				</div>
				<?php
			}
		}
	}

	public static function https_error() {
		if( DRPLUS_IS_LOCAL ) return;

		if( !is_ssl() ) {
			$msg = __( 'Your WordPress site is not using SSL. Not enabling SSL may disrupt the theme’s functionality.<br>In the <a href="%s">General Settings</a> section, enter both URLs with <code>https</code>. Also, submit a ticket to your hosting provider and ask them to configure SSL settings.', 'drplus' );
			$msg = sprintf( $msg, admin_url( "options-general.php" ) );
			?>
			<div class="notice notice-error">
				<p><strong><?php echo $msg ?></strong></p>
			</div>
			<?php
		}
	}

	public static function dismiss_notices() {
		if( empty( $_GET['drplus-dismiss'] ) ) return;

		$dismissible_notices_nonce_option = [
			'drplus-dismiss-wp_cron'	=> 'drplus_hide_wp_cron_notice'
		];
		$nonce = Utils::convert_chars( $_GET['drplus-dismiss'] );
		foreach( $dismissible_notices_nonce_option as $notice_nonce_action => $notice_option ) {
			if( wp_verify_nonce( $nonce, $notice_nonce_action ) ) {
				update_option( $notice_option, true, false );
				break;
			}
		}
	}

	public static function memory_limit_notice() {
		if( DRPLUS_IS_LOCAL ) return;
		
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$required_mb    = 768;
		$recommended_mb = 1024;

		$php_limit = self::memory_limit_to_bytes( ini_get( 'memory_limit' ) );

		if ( $php_limit >= ( $recommended_mb * MB_IN_BYTES ) ) {
			return;
		}

		$is_error = $php_limit < ( $required_mb * MB_IN_BYTES );

		$class = $is_error ? 'notice-error' : 'notice-warning';

		$message = $is_error
			? sprintf(
				__(
					'Your WordPress memory limit is only <strong>%1$d MB</strong>. The theme requires at least <strong>%2$d MB</strong>, and <strong>%3$d MB</strong> is recommended for best performance.',
					'drplus'
				),
				round( $php_limit / MB_IN_BYTES ),
				$required_mb,
				$recommended_mb
			)
			: sprintf(
				__(
					'Your WordPress memory limit is <strong>%1$d MB</strong>. For the best performance, increasing it to <strong>%2$d MB</strong> is recommended.',
					'drplus'
				),
				round( $php_limit / MB_IN_BYTES ),
				$recommended_mb
			);

		?>
		<div class="notice <?php echo esc_attr( $class ); ?>">
			<p><?php echo wp_kses_post( $message ); ?></p>
			<ul style="margin-inline-start:20px;list-style:disc;">
				<li><?php printf( 'PHP memory_limit: <code>%s</code>', esc_html( ini_get( 'memory_limit' ) ) ); ?></li>
			</ul>
			<p><?php printf( __( 'Please contact your hosting provider and ask them to increase the PHP memory limit and the WordPress memory limits to <code>%s</code>', 'drplus' ), "{$recommended_mb}M" ) ?></p>
		</div>
		<?php
	}

	private static function memory_limit_to_bytes( $value ) {
		$value = trim( $value );
		if ( '-1' === $value ) {
			return PHP_INT_MAX;
		}

		$unit   = strtolower( substr( $value, -1 ) );
		$number = (int) $value;

		switch ( $unit ) {
			case 'g':
				return $number * GB_IN_BYTES;
			case 'm':
				return $number * MB_IN_BYTES;
			case 'k':
				return $number * KB_IN_BYTES;
			default:
				return $number;
		}
	}
}
add_action( 'admin_enqueue_scripts', [Dashboard::class, 'enqueue_font'] );
add_action( 'elementor/editor/after_enqueue_styles', [Dashboard::class, 'enqueue_font'] );

add_action( 'admin_notices', [Dashboard::class, 'avatar_disabled_notice'] );
add_action( 'admin_notices', [Dashboard::class, 'disabled_wp_cron_notice'] );
add_action( 'admin_notices', [Dashboard::class, 'https_error'] );

add_action( 'admin_init', [Dashboard::class, "dismiss_notices"] );
add_action( 'admin_notices', [Dashboard::class, 'memory_limit_notice'] );