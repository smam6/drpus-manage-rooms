<?php
/**
 * Dr Plus Child Theme — Room Capacity Management
 *
 * Implements: spec 5.1-5.12, 5.5.65-5.5.71, 5.6.55
 * Category: 5 — Admin settings page (Settings → Room Capacity)
 *
 * Clinics are 'hospital' posts (dr-plus/inc/PostTypes/post-type-hospital.php:40).
 *
 * @package DrPlusChild\Capacity
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin: per-clinic capacity settings page under Settings.
 *
 * Single responsibility: admin UI + save handler only (spec 5.6.55).
 */
class DrPlus_Child_Capacity_Admin {

	/**
	 * Settings page slug (spec 5.1).
	 */
	const PAGE_SLUG = 'drplus-room-capacity';

	/**
	 * Nonce action and field (spec 5.4, 5.5.67).
	 */
	const NONCE_ACTION = 'drplus_capacity_save';
	const NONCE_FIELD  = 'drplus_capacity_nonce_field';

	/**
	 * Min/max allowed capacity values (spec 5.4, 5.6).
	 */
	const MIN_CAPACITY = 1;
	const MAX_CAPACITY = 99;

	/**
	 * Register the Settings submenu (spec 5.1, 5.5.66).
	 *
	 * Implements: spec 5.1, 5.2, 5.5.66
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_submenu_page(
			'options-general.php',
			DrPlus_Child_Capacity_Messages::admin_title(),
			DrPlus_Child_Capacity_Messages::admin_menu(),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Handle the settings save: nonce + capability first, then sanitize (spec 5.2, 5.4, 5.5, 5.6).
	 *
	 * Implements: spec 5.2, 5.4, 5.5, 5.6, 5.7, 5.9
	 *
	 * @return void
	 */
	/**
	 * Handle the "sync stale bookings" button on the settings page.
	 *
	 * Copies each booking row's WooCommerce order status back into
	 * `{prefix}drplus_booking`.`order_status`. The order is authoritative and the column is a
	 * cache, so this cannot invent a state — it only teaches the cache what the order already
	 * says. No customer is notified, no money moves, nothing is cancelled.
	 *
	 * @return void
	 */
	public static function handle_sync() {
		if ( ! isset( $_POST['drplus_capacity_sync_submit'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$result  = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( true );
		$message = $result['repaired'] > 0
			? sprintf( DrPlus_Child_Capacity_Messages::admin_sync_repaired(), $result['repaired'] )
			: DrPlus_Child_Capacity_Messages::admin_sync_nothing_to_repair();

		set_transient(
			'drplus_capacity_admin_notice_' . get_current_user_id(),
			array( $message, 'success' ),
			60
		);

		wp_safe_redirect( add_query_arg( 'page', self::PAGE_SLUG, admin_url( 'options-general.php' ) ) );
		exit;
	}

	/**
	 * Handle the settings save: nonce + capability first, then sanitize (spec 5.2, 5.4, 5.5, 5.6).
	 *
	 * Implements: spec 5.2, 5.4, 5.5, 5.6, 5.7, 5.9
	 *
	 * @return void
	 */
	public static function handle_save() {
		if ( ! isset( $_POST['drplus_capacity_save_submit'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی غیرمجاز.', 'drplus-child' ) );
		}
		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );
		$capacities = isset( $_POST['drplus_capacity_clinic'] ) && is_array( $_POST['drplus_capacity_clinic'] )
			? wp_unslash( $_POST['drplus_capacity_clinic'] )
			: array();
		// Bug B: the parent has no column linking a custom-office UUID to the clinic it belongs
		// to, so the operator states the mapping explicitly. Stored as one option: uuid => clinic.
		$aliases = isset( $_POST['drplus_capacity_alias'] ) && is_array( $_POST['drplus_capacity_alias'] )
			? wp_unslash( $_POST['drplus_capacity_alias'] )
			: array();
		$clean_aliases = array();
		foreach ( $aliases as $clinic_id => $raw_alias ) {
			$clinic_id = absint( $clinic_id );
			if ( $clinic_id < 1 ) {
				continue;
			}
			// One clinic can legitimately own several UUIDs: the parent mints a NEW custom office
			// per specialist who registers the same clinic, and the live data has four for one
			// clinic. Accept a comma / space / newline separated list.
			$raw_alias = strtolower( (string) $raw_alias );
			$parts     = preg_split( '/[\s,]+/', $raw_alias, -1, PREG_SPLIT_NO_EMPTY );
			foreach ( (array) $parts as $uuid ) {
				$uuid = sanitize_text_field( $uuid );
				// Only a UUID-shaped value is accepted: a numeric id here would silently merge two
				// real hospitals, which is far worse than not resolving the alias at all.
				if ( ! preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $uuid ) ) {
					continue;
				}
				$clean_aliases[ $uuid ] = $clinic_id;
			}
		}
		update_option( 'drplus_capacity_clinic_aliases', $clean_aliases, false );

		$error = false;
		foreach ( $capacities as $clinic_id => $raw ) {
			$clinic_id = absint( $clinic_id );
			$value     = absint( sanitize_text_field( (string) $raw ) );
			if ( $clinic_id < 1 || $value < self::MIN_CAPACITY || $value > self::MAX_CAPACITY ) {
				$error = true;
				continue; // Invalid value: not saved (spec 5.6).
			}
			update_option( 'drplus_capacity_clinic_' . $clinic_id, $value, false );

			// Saving the clinic's own row is the operator's single source of truth for that
			// clinic. Any per-UUID value left behind by an import or an older release would
			// otherwise keep competing with it (capacity_for() takes the strictest, but a stale
			// stricter value would silently enforce a limit the operator believes they changed).
			// Clear the aliases this clinic owns so the saved number is the one that applies.
			foreach ( (array) $clean_aliases as $alias_uuid => $alias_clinic ) {
				if ( $alias_clinic === $clinic_id ) {
					delete_option( 'drplus_capacity_clinic_' . $alias_uuid );
				}
			}
		}
		$notice = $error
			? array( DrPlus_Child_Capacity_Messages::admin_save_error(), 'error' )
			: array( DrPlus_Child_Capacity_Messages::admin_save_success(), 'success' );
		set_transient( 'drplus_capacity_admin_notice_' . get_current_user_id(), $notice, 30 );
	}

	/**
	 * Render the settings page (spec 5.3, 5.10, 5.11).
	 *
	 * Implements: spec 5.3, 5.8, 5.9, 5.10, 5.11
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی غیرمجاز.', 'drplus-child' ) );
		}
		$clinics = get_posts( array(
			'post_type'      => 'hospital',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		// Reverse the stored uuid=>clinic map so each clinic's row can show its alias.
		$aliases_by_clinic = array();
		$stored_aliases    = get_option( 'drplus_capacity_clinic_aliases', array() );
		if ( is_array( $stored_aliases ) ) {
			foreach ( $stored_aliases as $uuid => $clinic_id ) {
				if ( ! is_array( $clinic_id ) ) {
					$aliases_by_clinic[ (int) $clinic_id ][] = (string) $uuid;
				}
			}
		}
		foreach ( $aliases_by_clinic as $clinic_key => $uuid_list ) {
			$aliases_by_clinic[ $clinic_key ] = implode( ', ', $uuid_list );
		}
		$notice = get_transient( 'drplus_capacity_admin_notice_' . get_current_user_id() );
		if ( $notice ) {
			delete_transient( 'drplus_capacity_admin_notice_' . get_current_user_id() );
			printf( '<div class="notice notice-%s"><p>%s</p></div>', esc_attr( $notice[1] ), esc_html( $notice[0] ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_title() ); ?></h1>
			<p><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_description() ); ?></p>
			<form method="post" action="">
				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_col_clinic() ); ?></th>
							<th><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_col_capacity() ); ?></th>
						<th><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_col_alias() ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $clinics as $clinic ) : ?>
						<?php $current = DrPlus_Child_Capacity_Service::capacity_for( (string) $clinic->ID ); ?>
						<tr>
							<td><?php echo esc_html( $clinic->post_title ); ?></td>
							<td>
								<input type="number" min="<?php echo esc_attr( self::MIN_CAPACITY ); ?>" max="<?php echo esc_attr( self::MAX_CAPACITY ); ?>"
									name="drplus_capacity_clinic[<?php echo esc_attr( $clinic->ID ); ?>]"
									value="<?php echo esc_attr( $current ); ?>" class="small-text">
							</td>
							<td>
								<input type="text" dir="ltr" class="large-text code"
									name="drplus_capacity_alias[<?php echo esc_attr( $clinic->ID ); ?>]"
									value="<?php echo esc_attr( $aliases_by_clinic[ (int) $clinic->ID ] ?? '' ); ?>"
									placeholder="uuid-1, uuid-2, …">
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if ( empty( $clinics ) ) : ?>
						<tr><td colspan="3"><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_no_clinics() ); ?></td></tr>
					<?php endif; ?>
					</tbody>
				</table>
				<?php submit_button( DrPlus_Child_Capacity_Messages::admin_save_button(), 'primary', 'drplus_capacity_save_submit' ); ?>
			</form>

			<?php
			// Is the copied order_status still in step with WooCommerce? A stale copy keeps a
			// cancelled appointment's hour blocked forever, with no error shown to anyone.
			$sync = DrPlus_Child_Capacity_Repository::reconcile_order_statuses( false );
			?>
			<h2><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_sync_title() ); ?></h2>
			<p><?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_sync_description() ); ?></p>
			<?php if ( 0 === $sync['stale'] ) : ?>
				<p style="color:#1a7f37"><strong>✓ <?php echo esc_html( DrPlus_Child_Capacity_Messages::admin_sync_clean() ); ?></strong>
					(<?php echo esc_html( (string) $sync['inspected'] ); ?>)</p>
			<?php else : ?>
				<div class="notice notice-warning inline">
					<p><strong><?php echo esc_html( (string) $sync['stale'] ); ?> <?php echo esc_html__( 'رزرو ناهم‌خوان پیدا شد — ساعت این رزروها بی‌دلیل بسته مانده است:', 'drplus-child' ); ?></strong></p>
					<ul style="list-style:disc;padding-inline-start:20px">
						<?php foreach ( $sync['examples'] as $example ) : ?>
							<li><code dir="ltr"><?php echo esc_html( $example ); ?></code></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<form method="post" action="">
					<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
					<?php submit_button( DrPlus_Child_Capacity_Messages::admin_sync_repair_button(), 'secondary', 'drplus_capacity_sync_submit', false ); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}
}
