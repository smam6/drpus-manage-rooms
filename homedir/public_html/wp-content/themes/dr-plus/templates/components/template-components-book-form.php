<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Options;
use DrPlus\Utils\User;

if( !defined( 'ABSPATH' ) ) exit;

$use_outside_iran = Utils::to_bool( Options::get_options( ['use-outside-iran' => false] )['use-outside-iran'] );

// If you want to change the fields, also change them in elementor file
$fields = [
	'first_name'	=> __( 'Firstname', 'drplus' ),
	'last_name'		=> __( 'Lastname', 'drplus' ),
	'nid'			=> __( 'National ID', 'drplus' ),
	'gender'		=> __( 'Gender', 'drplus' ),
	'birthday'		=> __( 'Birthday', 'drplus' ),
	'phone'			=> __( 'Phone', 'drplus' ),
	'email'			=> __( 'Email', 'drplus' ),
	'specialist'	=> __( "Specialist", 'drplus' ),
];

$default_args = [
	'columns'			=> 2,
	'columns_tablet'	=> 2,
	'columns_mobile'	=> 1,
];

foreach( $fields as $field_key => $label ) {
	$default_args["{$field_key}_status"] = true;
	$default_args["{$field_key}_placeholder"] = $label;
}

$args = Utils::check_default( $args, $default_args );

$form_classes = ['drplus-book-form-widget', "drplus-book-form-desktop-{$args['columns']}", "drplus-book-form-tablet-{$args['columns_tablet']}", "drplus-book-form-mobile-{$args['columns_mobile']}"];
$form_attributes = [
	'action'	=> Booking::get_booking_page_url(),
	'method'	=> 'post',
	'class'		=> $form_classes,
	'style'		=> [
		'--desktop-columns'	=> $args['columns'],
		'--tablet-columns'	=> $args['columns_tablet'],
		'--mobile-columns'	=> $args['columns_mobile'],
	],
];

$button_args = Elementor::get_button_args( $args );
$button_args['prefix'] = 'button_';
$button_args['button_classes'] = ['drplus-book-form-widget-button'];
$button_args = Utils::unset( $button_args, ['button_link', 'button_new_tab'] );
?>
<form <?php echo Utils::get_html_attributes( $form_attributes ) ?>>
	<?php wp_nonce_field( 'booking_widget_nonce_value', 'booking_widget_nonce' ) ?>
	<?php if( $args['first_name_status'] ) { ?>
		<div class="input-wrap input-wrap-white">
			<input 
				type="text"
				placeholder="<?php echo esc_attr( $args['first_name_placeholder'] ) ?>" 
				name="booking_customer_first_name"
				class="input-field drplus-book-form-widget-input"

				autocapitalize="off"
				autocomplete="given-name"
				spellcheck="off"
			>
		</div>
	<?php } ?>

	<?php if( $args['last_name_status'] ) { ?>
		<div class="input-wrap input-wrap-white">
			<input 
				type="text"
				placeholder="<?php echo esc_attr( $args['last_name_placeholder'] ) ?>" 
				name="booking_customer_last_name"
				class="input-field drplus-book-form-widget-input"

				autocapitalize="off"
				autocomplete="family-name"
				spellcheck="off"
			>
		</div>
	<?php } ?>

	<?php if( $args['nid_status'] ) { ?>
		<div class="input-group">
			<div class="input-wrap input-wrap-white">
				<input 
					type="text"
					placeholder="<?php echo esc_attr( $args['nid_placeholder'] ) ?>" 
					name="booking_customer_nid"
					class="input-field drplus-book-form-widget-input input-ltr drplus-nid-input drplus-numeric-input drplus-book-form-widget-nid"

					minlength="10"
					maxlength="10"
					autocapitalize="off"
					inputmode="numeric"
					spellcheck="off"
				>
			</div>
			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
			<?php if( $args['foreign_customer_status'] ) { ?>
				<?php if( $args['foreign_customer_input_style'] ) { ?>
					<div class="input-wrap input-wrap-white">
				<?php } ?>
				<label class="checkbox-wrap drplus-foreign-checkbox-wrap drplus-book-form-widget-foreign-wrap input-field drplus-book-form-widget-input">
					<input type="checkbox" name="booking_foreign_customer" class="drplus-foreign-checkbox">
					<span class="drplus-book-form-widget-foreign-checkbox-text"><?php echo esc_attr( $args['foreign_customer_placeholder'] ) ?></span>
				</label>
				<?php if( $args['foreign_customer_input_style'] ) { ?>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	<?php } ?>

	<?php
	if( $args['gender_status'] ) {
		$user_gender = User::get_gender();
		?>
		<div class="input-wrap input-wrap-white">
			<select
				name="booking_customer_gender"
				class="drplus-book-form-widget-field drplus-select2"
				data-width="100%"
				required
			>
				<option value="male" <?php echo selected( $user_gender, 'male' ) ?>><?php esc_html_e( 'Male', 'drplus' ) ?></option>
				<option value="female" <?php echo selected( $user_gender, 'female' ) ?>><?php esc_html_e( 'Female', 'drplus' ) ?></option>
			</select>
		</div>
	<?php } ?>

	<?php
	if( $args['birthday_status'] ) {
		$user_birthday = Utils::convert_chars( User::get_birthday(), true, 'absint' );
		$unique_id = wp_unique_id();
		?>
		<div class="input-wrap input-wrap-white">
			<input
				type="text"
				class="drplus-datepicker-input drplus-book-form-widget-field"
				id="booking-info-customer-birthday<?php echo $unique_id ?>"
				data-time=''
				data-options='<?php echo wp_json_encode( ['maxDate' => date_i18n( 'U' )*1000] ) ?>'
				placeholder="<?php echo esc_html__( 'Date of birth', 'drplus' ) ?>"
				readonly
			>
			<input type="hidden" class="booking-info-customer-field" name="booking_customer_birthday" id="booking-info-customer-birthday<?php echo $unique_id ?>_alt" required>
		</div>
	<?php } ?>

	<?php if( $args['phone_status'] ) { ?>
		<div class="input-group">
			<div class="input-wrap input-wrap-white">
				<input 
					type="text"
					placeholder="<?php echo esc_attr( $args['phone_placeholder'] ) ?>" 
					name="booking_customer_phone"
					class="input-field drplus-book-form-widget-input <?php echo !$use_outside_iran ? 'drplus-phone-input' : 'drplus-numeric-input' ?> input-ltr drplus-book-form-widget-phone"

					<?php if( !$use_outside_iran ) { ?>
						minlength="13"
						maxlength="13"
					<?php } ?>
					autocapitalize="off"
					autocomplete="tel"
					inputmode="tel"
					spellcheck="off"
				>
			</div>
			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
		</div>
	<?php } ?>

	<?php if( $args['email_status'] ) { ?>
		<div class="input-wrap input-wrap-white">
			<input 
				type="email"
				placeholder="<?php echo esc_attr( $args['email_placeholder'] ) ?>" 
				name="booking_customer_email"
				class="input-field drplus-book-form-widget-input input-ltr"

				autocapitalize="off"
				autocomplete="email"
				spellcheck="off"
			>
		</div>
	<?php } ?>

	<?php if( $args['specialist_status'] ) { ?>
		<div class="input-group">
			<?php
			get_template_part( 'templates/components/template-components-search', null, [
				'search_hospitals'		=> false,
				'search_specialities'	=> false,
				'only'					=> 'specialist',
				'city_field'			=> false,
				'search_placeholder'	=> $args['specialist_placeholder'],
				'button_show_button'	=> false,
				'change_bg_when_filled'	=> false,
				'remove_form_tag'		=> true,
				'search_field_args'		=> [
					'input'	=> [
						'name'	=> ''
					]
				],
			] );

			$specialist_alt_input_args = [
				'type'	=> 'hidden',
				'name'	=> 'sid',
				'class'	=> 'drplus-search-input-alt',
			];
			echo "<input " . Utils::get_html_attributes( $specialist_alt_input_args ) . ">";
			?>
		</div>
	<?php } ?>

	<?php if( $args['btn_divider'] ) { ?>
		<span class="drplus-book-form-widget-button-divider"></span>
	<?php } ?>

	<div class="drplus-book-form-widget-button-wrap">
		<?php
		$button_args['button_atts'] = [
			'name'	=> 'booking_widget_submit'
		];
		Button::view( $button_args );
		?>
	</div>
</form>