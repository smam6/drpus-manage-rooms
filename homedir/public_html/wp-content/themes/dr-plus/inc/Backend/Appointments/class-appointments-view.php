<?php

namespace DrPlus\Backend\Appointments;

use DrPlus\Model\Booking;
use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;

class View extends AppointmentsList {
	protected static $PREFIX = '';
	public static function view() {
		self::$PREFIX = parent::$PREFIX;

		if( Utils::convert_chars( $_GET['action'] ?? '' ) == 'edit' && !empty( Utils::convert_chars( $_GET['book_id'] ) ) ) {
			return self::edit_appointment_view();
		}

		$book_id = Utils::convert_chars( $_GET['book_id'] ?? 0 );
		if( empty( $book_id ) ) {
			return self::select_appointment_view();
		} else {
			$order_id = Booking::query()->select( 'order_id' )->where( 'book_id', $book_id )->first()->order_id;
			$order = !empty( $order_id ) ? wc_get_order( $order_id ) : null;
			if( empty( $order ) ) {
				// Show message got unavailable book id
				self::unavailable_app();
				return;
			}
			$book_data = $order->get_meta( '_booking_data' );
			if( empty( $book_data ) ) {
				// Show message got unavailable book id
				self::unavailable_app( esc_html__( 'There was an error retrieving reservation information.', 'drplus' ) );
				return;
			}
			?>
			<div class="<?php echo self::$PREFIX ?>appointment-view">
				<?php
				if( !empty( $_GET['show_chat'] ) && ( $book_data['office_id'] == 'chat_consultation' || $book_data['office_id'] == 'instant_chat_consultation' ) ) {
					?>
					<div class="<?php echo self::$PREFIX ?>appointment-chat-detail">
						<p>
							<strong><?php echo esc_html__( 'Patient name', 'drplus' ) ?>: </strong>
							<span><?php echo "{$book_data['first_name']} {$book_data['last_name']}" ?></span>
						</p>
						<p>
							<strong><?php echo esc_html__( 'Specialist name', 'drplus' ) ?>: </strong>
							<span><?php echo "{$book_data['specialist_name']} ({$book_data['specialist_subtitle']})" ?></span>
						</p>
						<p>
							<a href="<?php echo remove_query_arg( 'show_chat' ); ?>"><?php echo esc_html__( 'Back', 'drplus' ) ?></a>
						</p>
					</div>
					<?php
					$chat_id = Utils::convert_chars( $_GET['show_chat'] );
					get_template_part( "templates/chats/template-chats", 'single', [
						'view_type'			=> 'admin',
						'chat_id'			=> $chat_id,
						'_customer_name'		=> "{$book_data['first_name']} {$book_data['last_name']}",
						'_specialist_name'	=> $book_data['specialist_name'],
					] );
				} else {
					get_template_part( "templates/booking/template-booking-step", 'receipt', [
						'book_data'		=> $book_data,
						'order'			=> $order,
						'show_back_btn'	=> false,
						'view_type'		=> 'admin',
					] );
				}
				?>
			</div>
			<?php
		}
	}

	public static function select_appointment_view() {
		?>
		<form method="get" action="" class="<?php echo self::$PREFIX ?>select-appointment-form">
			<input type="hidden" name="page" value="<?php echo Utils::convert_chars( $_GET['page'] ) ?>">
			<input type="hidden" name="tab" value="<?php echo Utils::convert_chars( $_GET['tab'] ) ?>">
			<h3 class="<?php echo self::$PREFIX ?>select-appointment-title">
				<?php esc_html_e( 'Please enter a appointment ID to show details', 'drplus' ) ?>
			</h3>
			<div class="<?php echo self::$PREFIX ?>select-appointment-wrap">
				<label class="<?php echo self::$PREFIX ?>select-appointment-label">
					<p><?php esc_html_e( 'Appointment ID', 'drplus' ) ?>:</p>
					<input type="number" name="book_id" class="<?php echo self::$PREFIX ?>select-appointment-input">
				</label>
				<button type="submit" class="<?php echo self::$PREFIX ?>select-appointment-btn <?php echo self::$PREFIX ?>submit-btn"><?php esc_html_e( 'Submit', 'drplus' ) ?></button>
			</div>
		</form>
		<?php
	}

	public static function edit_appointment_view() {
		$use_outside_iran = Utils::to_bool( Options::get_options( ['use-outside-iran' => false] )['use-outside-iran'] );
		$book_id = Utils::convert_chars( $_GET['book_id'] );
		$db_booking = Booking::query()->select( ['order_id', 'end_time'] )->where( 'book_id', $book_id )->first();
		$order_id = $db_booking->order_id;
		$order = !empty( $order_id ) ? wc_get_order( $order_id ) : null;
		if( empty( $order ) ) {
			// Show message got unavailable book id
			self::unavailable_app();
			return;
		}
		$book_data = $order->get_meta( '_booking_data' );
		if( empty( $book_data ) ) {
			// Show message got unavailable book id
			self::unavailable_app( esc_html__( 'There was an error retrieving reservation information.', 'drplus' ) );
			return;
		}
		$specialist_id = $book_data['specialist_id'];
		$specialist = (new Specialists)->find( $specialist_id );
		if( empty( $book_data ) ) {
			// Show message got unavailable book id
			self::unavailable_app( esc_html__( 'There was an error retrieving specialist information.', 'drplus' ) );
			return;
		}

		$offices = [];
		foreach( $specialist->offices as $office ) {
			if( $office['type'] == 'hospital' ) {
				$offices[$office['id']] = get_the_title( $office['id'] );
			} else {
				$offices[$office['id']] = $office['name'];
			}
		}
		?>
		<h2><?php printf( esc_html__( 'Edit appointment #%s', 'drplus' ), $book_id ) ?></h2>
		<form action="" method="post" id="<?php echo self::$PREFIX ?>edit-app-form">
			<?php wp_nonce_field( self::$PREFIX . "nonce", self::$PREFIX . "nonce_value" ) ?>
			<input type="hidden" name="<?php echo self::$PREFIX ?>book_id" value="<?php echo Utils::convert_chars( $_GET['book_id'] ) ?>">
			<?php
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Patient first name', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $book_data['first_name'],
				'id'			=> parent::$PREFIX . "edit_first_name",
				'name'			=> parent::$PREFIX . "edit_first_name",
				'input_classes'	=> ['regular-text', self::$PREFIX . 'edit-app-field'],
				'required'		=> true
			] );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Patient last name', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $book_data['last_name'],
				'id'			=> parent::$PREFIX . "edit_last_name",
				'name'			=> parent::$PREFIX . "edit_last_name",
				'input_classes'	=> ['regular-text', self::$PREFIX . 'edit-app-field'],
				'required'		=> true
			] );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Reason to visit', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $book_data['reason'],
				'id'			=> parent::$PREFIX . "edit_reason",
				'name'			=> parent::$PREFIX . "edit_reason",
				'input_classes'	=> ['regular-text', self::$PREFIX . 'edit-app-field'],
				'textarea'		=> true,
			] );
			$nid_input_args = [
				'label'			=> esc_html__( 'Patient national code', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $book_data['nid'],
				'id'			=> parent::$PREFIX . "edit_nid",
				'name'			=> parent::$PREFIX . "edit_nid",
				'input_classes'	=> ['regular-text', 'ltr', 'drplus-numeric-input', self::$PREFIX . 'edit-app-field'],
				'inputmode'		=> 'numeric',
			];
			if( !$use_outside_iran ) {
				$nid_input_args['minlength'] = 10;
				$nid_input_args['maxlength'] = 10;
			}
			AdminUI::input_with_label( $nid_input_args );
			unset( $nid_input_args );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Patient email', 'drplus' ),
				'type'			=> 'email',
				'value'			=> $book_data['email'],
				'id'			=> parent::$PREFIX . "edit_email",
				'name'			=> parent::$PREFIX . "edit_email",
				'input_classes'	=> ['regular-text', 'ltr', self::$PREFIX . 'edit-app-field'],
			] );
			$phone_input_args = [
				'label'			=> esc_html__( 'Patient phone number', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $book_data['phone'],
				'id'			=> parent::$PREFIX . "edit_phone",
				'name'			=> parent::$PREFIX . "edit_phone",
				'input_classes'	=> ['regular-text', 'ltr', self::$PREFIX . 'edit-app-field'],
				'inputmode'		=> 'tel',
			];
			if( !$use_outside_iran ) {
				$phone_input_args['maxlength'] = 13;
				$phone_input_args['minlength'] = 13;
				$phone_input_args['placeholder'] = '09...';
				$phone_input_args['input_classes'][] = 'drplus-phone-input';
			} else {
				$phone_input_args['input_classes'][] = 'drplus-numeric-input';
			}
			AdminUI::input_with_label( $phone_input_args );
			unset( $phone_input_args );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Visit date', 'drplus' ),
				'type'			=> 'text',
				'data-time'		=> $book_data['raw_date'],
				'id'			=> parent::$PREFIX . "edit_date",
				'input_classes'	=> ['regular-text', 'drplus-datepicker-input', self::$PREFIX . 'edit-app-field'],
				'required'		=> true,
				'readonly'		=> 'readonly',
				'data-options'	=> [
					'minDate'	=> date_i18n( 'U' )*1000,
				],
				'alt_field'		=> [
					'id'	=> parent::$PREFIX . "edit_date_alt", // Don't remove _alt
					'name'	=> parent::$PREFIX . "edit_date",
					'value'	=> $book_data['raw_date']
				],
			] );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Visit start time', 'drplus' ),
				'type'			=> 'time',
				'value'			=> $book_data['start_time'],
				'id'			=> parent::$PREFIX . "edit_start_time",
				'name'			=> parent::$PREFIX . "edit_start_time",
				'input_classes'	=> ['regular-text', 'ltr', self::$PREFIX . 'edit-app-field'],
				'required'		=> true
			] );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Visit end time', 'drplus' ),
				'type'			=> 'time',
				'value'			=> $db_booking->end_time,
				'id'			=> parent::$PREFIX . "edit_end_time",
				'name'			=> parent::$PREFIX . "edit_end_time",
				'input_classes'	=> ['regular-text', 'ltr', self::$PREFIX . 'edit-app-field'],
				'required'		=> true
			] );
			AdminUI::select_with_label( [
				'label'				=> esc_html__( 'Office', 'drplus' ),
				'value'				=> $book_data['office_id'],
				'id'				=> parent::$PREFIX . "edit_office",
				'name'				=> parent::$PREFIX . "edit_office",
				'select_classes'	=> ['drplus-select2'],
				'required'			=> true,
				'data-width'		=> '100%',
				'options'			=> $offices
			] );
			?>
			<button type="submit" class="<?php echo self::$PREFIX ?>edit-app-btn <?php echo self::$PREFIX ?>submit-btn"><?php esc_html_e( 'Submit', 'drplus' ) ?></button>
		</form>
		<?php
	}

	public static function unavailable_app( $message = "" ) {
		?>
		<div class="<?php echo self::$PREFIX ?>unavailable-app">
			<p>
				<strong><?php echo empty( $message ) ? esc_html__( 'Information for this appointment is not available.', 'drplus' ) : $message ?></strong>
			</p>
			<a href="<?php echo admin_url( '?page=appointments' ) ?>" class="<?php echo self::$PREFIX ?>unavailable-back-link"><?php esc_html_e( 'Return', 'drplus' ) ?></a>
		</div>
		<?php
	}

	public static function save() {
		self::$PREFIX = parent::$PREFIX;
		$book_id = Utils::convert_chars( $_POST[self::$PREFIX . 'book_id'] ?? "" );
		if( empty( $book_id ) ) {
			add_settings_error( 'drplus-appointments-settings', self::$PREFIX . 'settings', __( "Unable to get book id", 'drplus' ), 'error' );
			return;
		}

		$first_name = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_first_name'] );
		$last_name = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_last_name'] );
		$reason = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_reason'], 'sanitize_textarea_field' );
		$nid = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_nid'] );
		$email = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_email'] );
		$phone = Sanitizers::phone( $_POST[self::$PREFIX . 'edit_phone'] );
		$date = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_date'] );
		$start_time = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_start_time'] );
		$end_time = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_end_time'] );
		$office_id = Utils::convert_chars( $_POST[self::$PREFIX . 'edit_office'] );

		$db_booking = Booking::query()->select( ['book_id', 'order_id', 'specialist_id'] )->where( 'book_id', $book_id )->first();
		$order_id = $db_booking->order_id;
		if( empty( $order_id ) ) {
			add_settings_error( 'drplus-appointments-settings', self::$PREFIX . 'settings', __( "Unable to get book id", 'drplus' ), 'error' );
			return;
		}

		// Update order meta data
		$order = wc_get_order( $order_id );
		if( !empty( $order ) ) {
			$order_meta = $order->get_meta_data();
			foreach( $order_meta as $meta ) {
				if( $meta->key == '_booking_data' ) {
					$meta_id = $meta->id;
					$book_data = $meta->value;
					break;
				}
			}
		}
		if( empty( $order ) || empty( $book_data ) ) {
			add_settings_error( 'drplus-appointments-settings', self::$PREFIX . 'settings', __( "Unable to get appointment data", 'drplus' ), 'error' );
			return;
		}

		$specialist = (new Specialists)->find( $db_booking->specialist_id );
		if( !$specialist ) {
			add_settings_error( 'drplus-appointments-settings', self::$PREFIX . 'settings', __( "Unable to get specialist data", 'drplus' ), 'error' );
			return;
		}

		$office = null;
		foreach( $specialist->offices as $_office ) {
			if( $_office['id'] == $office_id ) {
				$office = $_office;
			}
		}
		if( $office['type'] == 'hospital' ) {
			$hospital_settings = Hospital::get_options( $office['id'] );
			$office['name'] = get_the_title( $office['id'] );
			$office['address'] = $hospital_settings['address'];
		}

		// Update DB Booking
		$db_booking->office_id = $office_id;
		$db_booking->start_time = $start_time;
		$db_booking->end_time = $end_time;
		$db_booking->date = date( 'Y-m-d', $date );
		$db_booking->save();

		// Update order meta data
		$book_data['first_name'] = $first_name;
		$book_data['last_name'] = $last_name;
		$book_data['reason'] = $reason;
		$book_data['office_id'] = $office_id;
		$book_data['office_name'] = $office['name'];
		$book_data['office_address'] = $office['address'];
		$book_data['phone'] = $phone;
		$book_data['email'] = $email;
		$book_data['nid'] = $nid;
		$book_data['date'] = date_i18n( "d F Y", $date );
		$book_data['start_time'] = $start_time;
		$book_data['raw_date'] = $date;
		$book_data['raw_time'] = strtotime( '1970-01-01 ' . $start_time ) * 1000;
		$order->update_meta_data( '_booking_data', $book_data, $meta_id );
		$order->save();

		add_settings_error( 'drplus-appointments-settings', self::$PREFIX . 'settings', __( "Appointment data updated successfully", 'drplus' ), 'updated' );
	}
}