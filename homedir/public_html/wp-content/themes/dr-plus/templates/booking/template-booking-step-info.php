<?php

use DrPlus\Components\Alert;
use DrPlus\Components\Button;
use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Date;
use DrPlus\Utils\Options;
use DrPlus\Utils\User;

$options = Options::get_options( [
	'booking-info-section-title'			=> esc_html__( 'Completing the information', 'drplus' ),
	'booking-info-section-icon'				=> 'drplus-icon-personalcard-bold',
	'booking-info-section-text'				=> esc_html__( 'To book an appointment, please enter your personal information.', 'drplus' ),
	'booking-info-field-phone-enabled'		=> true,
	'booking-info-field-email-enabled'		=> true,
	'booking-info-field-nid-enabled'		=> true,
	'booking-info-field-gender-enabled'		=> true,
	'booking-info-field-birthday-enabled'	=> true,
	'booking-info-field-reason-enabled'		=> true,
	'booking-info-field-phone-required'		=> true,
	'booking-info-field-email-required'		=> false,
	'booking-info-field-nid-required'		=> true,
	'booking-info-field-gender-required'	=> true,
	'booking-info-field-birthday-required'	=> true,
	'booking-info-field-reason-required'	=> false,

	'booking-info-field-birthday-format'	=> 'DD MMMM YYYY',

	'use-outside-iran'						=> false
] );

$use_outside_iran = Utils::to_bool( $options['use-outside-iran'] );

if( !Booking::check_booking_session( ['specialist_id', 'office', 'date', 'time'] ) ) return;

// Get current year
$current_year = Utils::convert_chars( date_i18n( 'Y' ), true, 'absint' );
$specialist = Booking::get_specialist();
if( !$specialist ) return;

$office_id = $_SESSION['booking']['office'];
$office = null;
foreach( $specialist->offices as $_office ) {
	if( $_office['id'] == $office_id ) {
		$office = $_office;
	}
}

if( empty( $office ) ) {
	Alert::view( [
		'type'	=> 'error',
		'icon'	=> 'drplus-icon-error',
		'text'	=> esc_html__( 'Something went wrong. please try again', 'drplus' ),
		'classes'	=> ['booking-alert']
	] );
	Button::view( [
		'text'		=> esc_html__( 'Back', 'drplus' ),
		'link'		=> Booking::get_booking_page_url(),
		'type'		=> 'bordered',
		'align' 	=> 'center',
	] );
}

// Get booking date and time
$date = $_SESSION['booking']['date'];
$time = $_SESSION['booking']['time'];
$week_name = date_i18n('l', $date);
$date = date_i18n( Utils::is_iran_timezone() ? 'd F Y' : get_option( 'date_format' ), $date );
if( $office_id != 'instant_chat_consultation' ) {
	$hour = date( "H", $time/1000 );
	$time = date( "H:i", $time/1000 );
	
	$time .= " " . Date::get_time_period( $hour );
} else {
	$time = esc_html__( 'Instant Chat', 'drplus' );
}

// Get user info
$user = wp_get_current_user();

$user_info = [
	'first_name'	=> !empty( $_SESSION['booking']['first_name'] ) ? $_SESSION['booking']['first_name'] : $user->first_name,
	'last_name'		=> !empty( $_SESSION['booking']['last_name'] ) ? $_SESSION['booking']['last_name'] : $user->last_name,
];

if( $options['booking-info-field-phone-enabled'] ) {
	$user_info['phone'] = !empty( $_SESSION['booking']['phone'] ) ? $_SESSION['booking']['phone'] : User::get_phone( $user->ID );
}
if( $options['booking-info-field-email-enabled'] ) {
	$user_info['email'] = !empty( $_SESSION['booking']['email'] ) ? $_SESSION['booking']['email'] : $user->user_email;
}
if( $options['booking-info-field-nid-enabled'] ) {
	$user_info['nid'] = !empty( $_SESSION['booking']['nid'] ) ? $_SESSION['booking']['email'] : User::get_nid( $user->ID );
}
if( $options['booking-info-field-birthday-enabled'] ) {
	$user_info['birthday'] = !empty( $_SESSION['booking']['birthday'] ) ? $_SESSION['booking']['email'] : Utils::convert_chars( User::get_birthday( $user->ID, 'Y' ), true, 'absint' );
}
if( $options['booking-info-field-gender-enabled'] ) {
	$user_info['gender'] = !empty( $_SESSION['booking']['gender'] ) ? $_SESSION['booking']['email'] : User::get_gender( $user->ID );
}

if( !empty( $_SESSION['booking']['foreign_customer'] ) ) {
	$user_info['nid'] = "";
}

wp_localize_script( 'drplus-booking', 'drplusBooking', [
	'i18n'	=> [
		'wrongEmail'	=> __( 'Please enter a valid email', 'drplus' ),
		'wrongIDCode'	=> __( 'Please enter a valid National ID', 'drplus' ),
		'wrongMobile'	=> __( 'Please enter a valid mobile', 'drplus' ),
	]
] );

?>
<div class="booking-info-wrap booking-has-sidebar">
	<div class="booking-info booking-section">
		<?php SectionTitle::view( [
			'title'	=> $options['booking-info-section-title'],
			'icon'	=> $options['booking-info-section-icon']
		] ); ?>
		<span class="booking-info-text"><?php echo esc_html( $options['booking-info-section-text'] ) ?></span>
		<div class="booking-info-customer-data-wrap">
			<div class="booking-info-customer-field-wrap">
				<label for="booking-info-customer-first-name" class="booking-info-customer-field-label">
					<?php esc_html_e( 'First name', 'drplus' ) ?>
					<span class="required">*</span>
				</label>
				<input 
					type="text"
					id="booking-info-customer-first-name"
					placeholder="<?php esc_attr_e( 'First name', 'drplus' ) ?>" 
					name="booking_customer_first_name"
					class="booking-info-customer-field"
					value="<?php echo esc_attr( $user_info['first_name'] ) ?>"
					required

					autocapitalize="off"
					autocomplete="given-name"
					spellcheck="off"
				>
				<div class="drplus_form_field_error">
					<i class="drplus-icon-error"></i>
					<div class="drplus_form_field_error-text"></div>
				</div>
			</div>
			<div class="booking-info-customer-field-wrap">
				<label for="booking-info-customer-last-name" class="booking-info-customer-field-label">
					<?php esc_html_e( 'Last name', 'drplus' ) ?>
					<span class="required">*</span>
				</label>
				<input
					type="text"
					id="booking-info-customer-last-name"
					placeholder="<?php esc_attr_e( 'Last name', 'drplus' ) ?>"
					name="booking_customer_last_name"
					class="booking-info-customer-field"
					value="<?php echo esc_attr( $user_info['last_name'] ) ?>"
					required

					autocapitalize="off"
					autocomplete="family-name"
					spellcheck="off"
				>
				<div class="drplus_form_field_error">
					<i class="drplus-icon-error"></i>
					<div class="drplus_form_field_error-text"></div>
				</div>
			</div>
			<?php if( $options['booking-info-field-phone-enabled'] ) { ?>
				<div class="booking-info-customer-field-wrap">
					<label for="booking-info-customer-phone" class="booking-info-customer-field-label">
						<?php esc_html_e( 'Phone', 'drplus' ) ?>
						<?php if( $options['booking-info-field-phone-required'] ) { ?>
							<span class="required">*</span>
						<?php } ?>
					</label>
					<input
						type="text"
						id="booking-info-customer-phone"
						placeholder="<?php esc_attr_e( 'Your phone number', 'drplus' ) ?>"
						name="booking_customer_phone"
						class="booking-info-customer-field <?php echo !$use_outside_iran ? 'drplus-phone-input' : 'drplus-numeric-input' ?> input-ltr"
						value="<?php echo esc_attr( $user_info['phone'] ) ?>"
						<?php echo $options['booking-info-field-phone-required'] ? 'required' : "" ?>
	
						autocapitalize="off"
						autocomplete="tel"
						spellcheck="off"
						inputmode="tel"
					>
					<div class="drplus_form_field_error">
						<i class="drplus-icon-error"></i>
						<div class="drplus_form_field_error-text"></div>
					</div>
				</div>
			<?php } ?>
			<?php if( $options['booking-info-field-email-enabled'] ) { ?>
				<div class="booking-info-customer-field-wrap">
					<label for="booking-info-customer-email" class="booking-info-customer-field-label">
						<?php esc_html_e( 'Email', 'drplus' ) ?>
						<?php if( $options['booking-info-field-email-required'] ) { ?>
							<span class="required">*</span>
						<?php } ?>
					</label>
					<input
						type="email"
						id="booking-info-customer-email"
						placeholder="example@gmail.com"
						name="booking_customer_email"
						class="booking-info-customer-field input-ltr"
						value="<?php echo esc_attr( $user_info['email'] ) ?>"
						<?php echo $options['booking-info-field-email-required'] ? 'required' : "" ?>
	
						autocapitalize="off"
						autocomplete="email"
						spellcheck="off"
					>
					<div class="drplus_form_field_error">
						<i class="drplus-icon-error"></i>
						<div class="drplus_form_field_error-text"></div>
					</div>
				</div>
			<?php } ?>
			<?php if( $options['booking-info-field-nid-enabled'] ) { ?>
				<div class="booking-info-customer-field-wrap booking-info-customer-nid-field-wrap">
					<label for="booking-info-customer-nid" class="booking-info-customer-field-label">
						<?php esc_html_e( 'National ID', 'drplus' ) ?>
						<?php if( $options['booking-info-field-nid-required'] ) { ?>
							<span class="required">*</span>							
						<?php } ?>
					</label>
					<input
						type="text"
						id="booking-info-customer-nid"
						placeholder="<?php esc_attr_e( 'Your national ID', 'drplus' ) ?>"
						name="booking_customer_nid"
						class="booking-info-customer-field input-ltr drplus-numeric-input"
						value="<?php echo esc_attr( $user_info['nid'] ) ?>"
						<?php echo $options['booking-info-field-email-required'] ? 'required' : "" ?>
	
						maxlength="10"
						autocapitalize="off"
						spellcheck="off"
						inputmode="numeric"
					>
					<div class="drplus_form_field_error">
						<i class="drplus-icon-error"></i>
						<div class="drplus_form_field_error-text"></div>
					</div>
					<label class="booking-info-customer-foreign-wrap">
						<input
							type="checkbox"
							name="booking_foreign_customer"
							id="booking-info-foreign-checkbox"
							<?php checked( !empty( $_SESSION['booking']['foreign_customer'] ), true ) ?>
						>
						<span class="booking-info-customer-foreign-checkbox-text"><?php esc_html_e( 'I am a foreign national and do not have a national ID number.', 'drplus' ) ?></span>
					</label>
				</div>
			<?php } ?>
			<?php if( $options['booking-info-field-gender-enabled'] ) { ?>
				<div class="booking-info-customer-field-wrap">
					<label for="booking-info-customer-gender" class="booking-info-customer-field-label">
						<?php esc_html_e( 'Gender', 'drplus' ) ?>
						<?php if( $options['booking-info-field-gender-required'] ) { ?>
								<span class="required">*</span>							
							<?php } ?>
					</label>
					<select
						name="booking_customer_gender"
						id="booking-info-customer-gender"
						class="booking-info-customer-field drplus-select2"
						data-width="100%"
						<?php echo $options['booking-info-field-gender-required'] ? 'required' : "" ?>
					>
						<option value="male" <?php echo selected( $user_info['gender'], 'male' ) ?>><?php esc_html_e( 'Male', 'drplus' ) ?></option>
						<option value="female" <?php echo selected( $user_info['gender'], 'female' ) ?>><?php esc_html_e( 'Female', 'drplus' ) ?></option>
					</select>
				</div>
			<?php } ?>
			<?php if( $options['booking-info-field-birthday-enabled'] ) { ?>				
				<div class="booking-info-customer-field-wrap">
					<label for="booking-info-customer-birthday" class="booking-info-customer-field-label">
						<?php esc_html_e( 'Date of birth', 'drplus' ) ?>
						<?php if( $options['booking-info-field-birthday-required'] ) { ?>
							<span class="required">*</span>							
						<?php } ?>
					</label>
					<input
						type="text"
						class="drplus-datepicker-input booking-info-customer-field"
						id="booking-info-customer-birthday"
						data-time='<?php echo $user_info['birthday'] ?>'
						data-options='<?php echo wp_json_encode( ['maxDate' => (int)date_i18n( 'U' )*1000, 'format' => $options['booking-info-field-birthday-format']] ) ?>'
						placeholder="<?php echo esc_html__( 'Date of birth', 'drplus' ) ?>"
						readonly
					>
					<input type="hidden" class="booking-info-customer-field" name="booking_customer_birthday" id="booking-info-customer-birthday_alt" <?php echo $options['booking-info-field-birthday-required'] ? 'required' : "" ?>>
				</div>
			<?php } ?>
			<?php if( $options['booking-info-field-reason-enabled'] ) { ?>
				<div class="booking-info-customer-field-wrap">
					<label for="booking-info-customer-reason" class="booking-info-customer-field-label">
						<?php esc_html_e( 'Reason for Visit', 'drplus' ) ?>
						<?php if( $options['booking-info-field-reason-required'] ) { ?>
							<span class="required">*</span>							
						<?php } ?>
					</label>
					<textarea
						type="text"
						class="booking-info-customer-field"
						id="booking-info-customer-reason"
						name="booking_customer_reason"
						placeholder="<?php echo esc_html__( 'Reason for Visit', 'drplus' ) ?>"
						<?php echo $options['booking-info-field-reason-required'] ? 'required' : "" ?>
					></textarea>
				</div>
			<?php } ?>
		</div>
		<?php get_template_part( 'templates/booking/template-booking', 'nav-btns', [
			'current_step'	=> 'info',
			'prev_step'		=> "time?sid={$specialist->id}",
			'booking_url'	=> $args['booking_url']
		] ) ?>
	</div>
	<div class="booking-info-sidebar booking-section">
		<?php Booking::specialist_info_html( $specialist ) ?>
		<?php Booking::specialist_office_html( $office, true ) ?>
		<?php  ?>
		<div class="booking-info-time-wrap">
			<div class="booking-info-date-wrap">
				<span class="booking-info-week"><?php echo esc_html( $week_name ) ?></span>
				<span class="booking-info-date"><?php echo esc_html( $date ) ?></span>
			</div>
			<span class="booking-info-time"><?php echo esc_html( $time ) ?></span>
		</div>
	</div>
</div>