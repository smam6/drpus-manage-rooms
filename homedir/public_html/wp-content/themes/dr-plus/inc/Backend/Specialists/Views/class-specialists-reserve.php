<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Formatters;

class SpecialistReserve extends SpecialistView {
	protected static $PREFIX = "";
	public static function view() {
		self::$PREFIX = parent::$PREFIX . "reservation_";

		// Check if office key is exist in GET url
		if( !empty( $_GET['office'] ) ) {
			$office_id = Utils::convert_chars( $_GET['office'] );
			self::manage_times_view( $office_id );
		} else {
			self::choose_office_view();
		}
	}

	public static function choose_office_view() {
		// int ID for hospital
		// uuid for office
		// 'consultation' for online consultation
		$offices = parent::$specialist->offices;
		$offline_offices = array_filter( $offices, fn($id) => $id['type'] != 'consultation' );
		$hospitals_ids = wp_list_pluck( array_filter( $offices, fn($office) => $office['type'] == 'hospital' ), 'id' );
		$hospitals = [];
		if( !empty( $hospitals_ids ) ) {
			$hospitals = get_posts( [
				'post_type' => 'hospital',
				'include'	=> $hospitals_ids,
			] );
		}

		AdminUI::switch( [
			'name'		=> 'specialist_offline_visit',
			'id'		=> 'specialist_offline_visit',
			'value'		=> 'true',
			'active'	=> parent::$specialist->offline_visit,
			'label'		=> esc_html__( 'Active online booking for offices', 'drplus' ),
			'wrap_id'	=> 'specialist_offline_visit-wrap',
		] );
		if( parent::$specialist->offline_visit ) {
			?>
			<div class="<?php echo self::$PREFIX ?>time-offices-wrap"<?php echo parent::$specialist->offline_visit ? '' : ' style="display:none"' ?>>
				<?php if( !empty( $offline_offices ) || !empty( $hospitals ) ) { ?>
					<?php foreach( $hospitals as $hospital ) { ?>
						<a href="<?php echo add_query_arg( ['office' => $hospital->ID] ) ?>" class="<?php echo self::$PREFIX ?>time-office-cart">
							<i class="drplus-icon-hospital"></i>
							<span><?php echo esc_html( $hospital->post_title ) ?></span>
						</a>
					<?php } ?>
					<?php foreach( $offices as $office ) { ?>
						<?php if( $office['type'] == 'hospital' || $office['type'] == 'consultation' ) continue; ?>
						<a href="<?php echo add_query_arg( ['office' => $office['id'] ] ) ?>" class="<?php echo self::$PREFIX ?>time-office-cart">
							<i class="drplus-icon-health-clinic"></i>
							<span><?php echo esc_html( $office['name'] ) ?></span>
						</a>
					<?php } ?>
				<?php } else { ?>
					<p class="<?php echo self::$PREFIX ?>warning"><?php esc_html_e( "You haven't defined any offices yet.", 'drplus' ) ?></p>
				<?php } ?>
			</div>
		<?php } ?>
		<?php
		AdminUI::switch( [
			'name'		=> 'specialist_online_visit',
			'id'		=> 'specialist_online_visit',
			'value'		=> 'true',
			'active'	=> parent::$specialist->online_visit,
			'label'		=> esc_html__( 'Active online booking for consultation', 'drplus' ),
			'wrap_id'	=> 'specialist_online_visit-wrap',
		] );
		if( parent::$specialist->online_visit ) {
			?>
			<div class="<?php echo self::$PREFIX ?>time-consultations-wrap">
				<?php foreach( Booking::consultation_offices( 1 ) as $key => $data ) { ?>
					<div class="<?php echo self::$PREFIX ?>time-consultation-wrap">
						<a href="<?php echo add_query_arg( ['office' => $key] ) ?>" class="<?php echo self::$PREFIX ?>time-consultation-cart">
							<i class="drplus-icon-<?php echo $data['icon'] ?>"></i>
							<span><?php echo $data['label'] ?></span>
						</a>
					</div>
				<?php } ?>
			</div>
			<?php
		}
	}

	public static function manage_times_view( $office_id ) {
		$offices_type = wp_list_pluck( self::$specialist['offices'], 'type', 'id' );
		$is_instant_chat = $office_id == 'instant_chat_consultation';

		if( empty( $offices_type[$office_id] ) ) {
			?>
			<p class="<?php echo self::$PREFIX ?>warning"><?php esc_html_e( 'Wrong office ID', 'drplus' ) ?></p>
			<?php
			return;
		}

		// Get office name
		$office_name = "";
		if( $offices_type[$office_id] == 'hospital' ) {
			$office_name = get_the_title( $office_id );
		} else if( $offices_type[$office_id] == 'consultation' ) {
			if( $office_id == 'video_consultation' ) {
				$office_name = esc_html__( 'Video Consultation', 'drplus' );
			} else if( $office_id == 'chat_consultation' ) {
				$office_name = esc_html__( 'Chat Consultation', 'drplus' );
			} else if( $office_id == 'phone_consultation' || $office_id == 'consultation' ) {
				$office_name = esc_html__( 'Phone Consultation', 'drplus' );
			} else if( $office_id == 'instant_chat_consultation' ) {
				$office_name = esc_html__( 'Instant Chat Consultation', 'drplus' );
			} 
		} else { 
			foreach( self::$specialist['offices'] as $office ) {
				if( $office['id'] == $office_id ) {
					$office_name = $office['name'];
					break;
				}
			}
		}

		$office_times = Booking::get_times_by_office( $office_id, parent::$specialist );
		$default_times = $office_times['default_times'];
		$days = $office_times['days'];

		foreach( parent::$specialist->offices as $_office ) {
			if( $_office['id'] == $office_id ) {
				$office = $_office;
				break;
			}
		}
		?>
		<div class="<?php echo self::$PREFIX ?>reserve_office_data-wrap">
			<a class="<?php echo self::$PREFIX ?>reserve-back_btn" href="<?php echo remove_query_arg( ['office'] ) ?>" title="<?php esc_html_e( 'Back', 'drplus' ) ?>"><i class="drplus-icon-arrow-square-right"></i></a>
			<span class="<?php echo self::$PREFIX ?>reserve_office_name"><?php echo esc_html( $office_name ) ?></span>
		</div>
		<div class="<?php echo self::$PREFIX ?>general-settings-wrap">
			<span class="<?php echo self::$PREFIX ?>row-label"><?php esc_html_e( 'General settings', 'drplus' ) ?></span>
			<div class="<?php echo self::$PREFIX ?>general-settings">
				<?php
				AdminUI::switch( [
					'name'			=> parent::$PREFIX . 'enable_booking',
					'id'			=> parent::$PREFIX . 'enable_booking',
					'value'			=> 1,
					'active'		=> Utils::to_bool( $office['enable_booking'] ?? 1 ),
					'label'			=> esc_html__( 'Enable for booking', 'drplus' ),
					'input_classes'	=> ['regular-text'],
					'disabled'		=> false,
					'wrap_id'		=> parent::$PREFIX . 'enable_booking-wrap',
				] );
				if( !$is_instant_chat ) {
					AdminUI::input_with_label( [
						'label'				=> esc_html__( 'Visit time duration (minutes)', 'drplus' ),
						'type'				=> 'text',
						'input_classes'		=> ['regular-text', 'ltr', 'drplus-numeric-input'],
						'inputmode'			=> 'numeric',
						'value'				=> $office['visit_time'] ?? '',
						'id'				=> self::$PREFIX . "visit_time",
						'name'				=> parent::$PREFIX . "visit_time",
						'required'			=> true
					] );
				}
				$woocommerce_currency = get_woocommerce_currency();
				if( in_array( $woocommerce_currency, ['IRR', 'IRT', 'IRHR', 'IRHT'] ) ) {
					$visit_price_label = esc_html__( 'Visit price (Toman)', 'drplus' );
				} else {
					$visit_price_label = sprintf( esc_html__( 'Visit price (%s)', 'drplus' ), get_woocommerce_currency_symbol() );
				}
				AdminUI::input_with_label( [
					'label'			=> $visit_price_label,
					'type'			=> 'text',
					'value'			=> Formatters::price( $office['visit_price'] ?? 0 ),
					'id'			=> self::$PREFIX . "visit_price",
					'name'			=> parent::$PREFIX . "visit_price",
					'input_classes'	=> ['regular-text', 'ltr', 'drplus-price-input', 'drplus-numeric-input'],
					'inputmode'		=> 'numeric',
					'required'		=> true
				] );
				if( !$is_instant_chat ) {
					AdminUI::input_with_label( [
						'label'				=> esc_html__( 'Maximum bookable days', 'drplus' ),
						'type'				=> 'text',
						'input_classes'		=> ['regular-text', 'ltr', 'drplus-numeric-input'],
						'inputmode'			=> 'numeric',
						'value'				=> $office['max_booking_days'] ?? '',
						'id'				=> self::$PREFIX . "max_booking_days",
						'name'				=> parent::$PREFIX . "max_booking_days",
						'required'			=> false,
						'description'		=> esc_html__( 'Specify the maximum number of days in which the user can book an appointment. leave empty for no limit', 'drplus' ),
					] );
					AdminUI::input_with_label( [
						'label'				=> esc_html__( 'Minimum Time Before Appointment Booking', 'drplus' ),
						'type'				=> 'text',
						'input_classes'		=> ['regular-text', 'ltr', 'drplus-numeric-input'],
						'inputmode'			=> 'numeric',
						'value'				=> $office['min_time_before_book'] ?? '',
						'id'				=> self::$PREFIX . "min_time_before_book",
						'name'				=> parent::$PREFIX . "min_time_before_book",
						'required'			=> false,
						'description'		=> esc_html__( 'This option defines how many hours in advance a user can book an appointment relative to the current time. leave empty for no limit', 'drplus' ),
					] );
				}
				?>
				<div class="drplus_form_fieldset">
					<div class="drplus_form_group">
						<div class="drplus_form_group-input" id="<?php echo self::$PREFIX ?>custom_off_days">
							<!-- Each off days as input hidden -->
							<?php foreach( $office['custom_off_days'] ?? [] as $custom_off_day ) {
								self::off_day_item_template( date_i18n( 'j F Y', $custom_off_day ), $custom_off_day );
							} ?>
							<input type="text" id="<?php echo self::$PREFIX ?>add_off_days" value="<?php echo esc_html__( 'Add', 'drplus' ) ?>" readonly>
						</div>
						<label class="drplus_form_group-label" id="<?php echo self::$PREFIX ?>custom_off_days_label">
							<?php esc_html_e( 'Off days', 'drplus' ); ?>
						</label>
					</div>

					<p class="description"><?php esc_html_e( 'Select closed dates for disable booking', 'drplus' ) ?></p>
				</div>
			</div>
		</div>
		<div class="<?php echo self::$PREFIX ?>default_times-wrap">
			<span class="<?php echo self::$PREFIX ?>row-label"><?php esc_html_e( 'Default times', 'drplus' ) ?></span>

			<div class="<?php echo self::$PREFIX ?>default_times">
				<?php
				if( !empty( $default_times ) ) {
					foreach( $default_times as $index => $time ) {
						self::default_time_row_template( $index, $time['from'], $time['to'], $time['status'] );
					}	
				} else {
					self::default_time_row_template( 0 );
				}
				?>
			</div>
			<button class="<?php echo self::$PREFIX ?>default-time-add" type="button">
				<i class="dashicons dashicons-plus-alt2"></i>
				<span class="<?php echo self::$PREFIX ?>day-time-add-text"><?php esc_html_e( 'Add Time', 'drplus' ) ?></span>
			</button>
		</div>
		<div class="<?php echo self::$PREFIX ?>days-wrap">
			<span class="<?php echo self::$PREFIX ?>row-label"><?php esc_html_e( 'Available Days', 'drplus' ) ?></span>
			<div class="<?php echo self::$PREFIX ?>days">
				<?php
				foreach( $days as $index => $day ) {
					self::day_template( $index, $day['day'], $day['day_name'], $day['use_default'], $day['times'] ?? [], $day['status'] );	
				}
				?>
			</div>
		</div>
		<script type="text/html" id="tmpl-<?php echo self::$PREFIX ?>default_time_template">
			<?php echo self::default_time_row_template( '{{{data.index}}}', '', '', 1 ); ?>
		</script>
		<script type="text/html" id="tmpl-<?php echo self::$PREFIX ?>custom_time_template">
			<?php echo self::custom_time_row_template( '{{{data.index}}}', '{{{data.day_index}}}', '', '', '' ); ?>
		</script>
		<script type="text/html" id="tmpl-<?php echo self::$PREFIX ?>off_day_item_template">
			<?php echo self::off_day_item_template( '{{{data.text}}}', '{{{data.value}}}' ); ?>
		</script>
		<?php
	}

	public static function off_day_item_template( $text = '', $value = '' ) {
		?>
		<div class="<?php echo self::$PREFIX ?>custom_off_day">
			<input type="hidden" name="<?php echo parent::$PREFIX ?>custom_off_days[]" value="<?php echo $value ?>">
			<span class="<?php echo self::$PREFIX ?>custom_off_day_text"><?php echo $text ?></span>
			<i class="drplus-icon-close <?php echo self::$PREFIX ?>remove_custom_off_day"></i>
		</div>
		<?php
	}

	public static function default_time_row_template( $index, $from = '', $to = '', $status = 1 ) {
		?>
		<div class="<?php echo self::$PREFIX ?>section <?php echo self::$PREFIX ?>default-time-row <?php echo self::$PREFIX ?>time-row <?php echo Utils::to_bool( $status ) ? '' : 'inactive' ?>">
			<span class="<?php echo self::$PREFIX ?>index <?php echo self::$PREFIX ?>time-index"><?php echo is_numeric( $index ) ? $index+1 : $index ?></span>
			<div class="<?php echo self::$PREFIX ?>time-fields">
				<span class="<?php echo self::$PREFIX ?>time-separator"><?php esc_html_e( 'from', 'drplus' ) ?></span>
				<div class="<?php echo self::$PREFIX ?>time-from <?php echo self::$PREFIX ?>row">
					<input type="time" class="<?php echo self::$PREFIX ?>time-input" name="<?php echo parent::$PREFIX ?>default_times[<?php echo $index ?>][from]" value="<?php echo $from ?>" required>
				</div>
				<span class="<?php echo self::$PREFIX ?>time-separator"><?php esc_html_e( 'to', 'drplus' ) ?></span>
				<div class="<?php echo self::$PREFIX ?>time-to <?php echo self::$PREFIX ?>row">
					<input type="time" class="<?php echo self::$PREFIX ?>time-input" name="<?php echo parent::$PREFIX ?>default_times[<?php echo $index ?>][to]" value="<?php echo $to ?>" required>
				</div>
			</div>
			<div class="<?php echo self::$PREFIX ?>time-actions">
				<?php AdminUI::switch( [
					'active'		=> Utils::to_bool( $status ),
					'name'			=> parent::$PREFIX . "default_times[{$index}][status]",
					'value'			=> '1',
					'input_classes'	=> [self::$PREFIX . 'reserve_times-status'],
				] ) ?>
				<i class="drplus-icon-trash <?php echo self::$PREFIX ?>time-remove" data-type="default"></i>
			</div>
		</div>
		<?php
	}

	public static function custom_time_row_template( $index, $day_index, $from = '', $to = '' ) {
		?>
		<div class="<?php echo self::$PREFIX ?>section <?php echo self::$PREFIX ?>custom-time-row <?php echo self::$PREFIX ?>time-row">
			<span class="<?php echo self::$PREFIX ?>index <?php echo self::$PREFIX ?>time-index"><?php echo is_numeric( $index ) ? $index+1 : $index ?></span>
			<div class="<?php echo self::$PREFIX ?>time-fields">
				<div class="<?php echo self::$PREFIX ?>time-from <?php echo self::$PREFIX ?>row">
					<input type="time" class="<?php echo self::$PREFIX ?>time-input" name="<?php echo parent::$PREFIX ?>days[<?php echo $day_index ?>][times][<?php echo $index ?>][from]" value="<?php echo $from ?>" required>
				</div>
				<span class="<?php echo self::$PREFIX ?>time-separator"><?php esc_html_e( 'to', 'drplus' ) ?></span>
				<div class="<?php echo self::$PREFIX ?>time-to <?php echo self::$PREFIX ?>row">
					<input type="time" class="<?php echo self::$PREFIX ?>time-input" name="<?php echo parent::$PREFIX ?>days[<?php echo $day_index ?>][times][<?php echo $index ?>][to]" value="<?php echo $to ?>" required>
				</div>
			</div>
			<div class="<?php echo self::$PREFIX ?>time-actions">
				<i class="drplus-icon-trash <?php echo self::$PREFIX ?>time-remove" data-type="custom"></i>
			</div>
		</div>
		<?php
	}

	public static function day_template( $index, $day_index, $day_name, $default_time, $times, $status ) {
		?>
		<div class="<?php echo self::$PREFIX ?>day <?php echo self::$PREFIX ?>section <?php echo Utils::to_bool( $status ) ? '' : 'inactive' ?>" data-day-index="<?php echo $day_index ?>">
			<span class="<?php echo self::$PREFIX ?>index <?php echo self::$PREFIX ?>day-index"><?php echo $index+1 ?></span>
			<input type="hidden" name="<?php echo parent::$PREFIX ?>days[<?php echo $day_index ?>][day_index]" value="<?php echo $day_index ?>">
			<div class="<?php echo self::$PREFIX ?>day-name <?php echo self::$PREFIX ?>row">
				<span class="<?php echo self::$PREFIX ?>day-name-text"><?php echo $day_name ?></span>
			</div>
			<div class="<?php echo self::$PREFIX ?>day-default-times <?php echo self::$PREFIX ?>row">
				<label>
					<input type="checkbox" class="<?php echo self::$PREFIX ?>day-default-times-checkbox" name="<?php echo parent::$PREFIX ?>days[<?php echo $day_index ?>][default_time]" value="1" <?php echo Utils::to_bool( $default_time ) ? 'checked' : '' ?>>
					<span class="<?php echo self::$PREFIX ?>day-default-times-text"><?php esc_html_e( 'Use Default Times', 'drplus' ) ?></span>
				</label>
			</div>
			<?php
			AdminUI::switch( [
				'active'		=> Utils::to_bool( $status ),
				'name'			=> parent::$PREFIX . "days[{$day_index}][status]",
				'value'			=> '1',
				'input_classes'	=> [self::$PREFIX . 'day-status'],
			] );
			?>
			<div class="<?php echo self::$PREFIX ?>day-times-wrap <?php echo self::$PREFIX ?>row <?php echo !Utils::to_bool( $default_time ) ? '' : 'hidden' ?>">
				<label class="<?php echo self::$PREFIX ?>row-label <?php echo self::$PREFIX ?>day-times-label"><?php printf( __( 'Available Times for %s', 'drplus' ), $day_name ) ?></label>
				<div class="<?php echo self::$PREFIX ?>day-times">
					<?php
					if( !$default_time ) {
						foreach( $times as $_index => $time ) {
							self::custom_time_row_template( $_index, $day_index, $time['from'], $time['to'] );
						}
					}
					?>
				</div>
				<button class="<?php echo self::$PREFIX ?>day-time-add" type="button">
					<i class="dashicons dashicons-plus-alt2"></i>
					<span class="<?php echo self::$PREFIX ?>day-time-add-text"><?php esc_html_e( 'Add Time', 'drplus' ) ?></span>
				</button>
			</div>
		</div>
		<?php
	}
}