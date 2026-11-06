<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$use_outside_iran = Utils::to_bool( Options::get_options( ['use-outside-iran' => false] )['use-outside-iran'] );

// If you want to change the fields, also change them in elementor file
$fields = [
	'first_name'	=> __( 'Firstname', 'drplus' ),
	'last_name'		=> __( 'Lastname', 'drplus' ),
	'nid'			=> __( 'National ID', 'drplus' ),
	'phone'			=> __( 'Phone', 'drplus' ),
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

$form_classes = ['drplus-consult-form-widget', "drplus-consult-form-desktop-{$args['columns']}", "drplus-consult-form-tablet-{$args['columns_tablet']}", "drplus-consult-form-mobile-{$args['columns_mobile']}"];
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
$button_args['button_classes'] = ['drplus-consult-form-widget-button'];
$button_args = Utils::unset( $button_args, ['button_link', 'button_new_tab'] );
?>
<form <?php echo Utils::get_html_attributes( $form_attributes ) ?>>
	<?php wp_nonce_field( 'booking_widget_nonce_value', 'booking_widget_nonce' ) ?>
	<input type="hidden" name="booking_office" value="consultation">
	<?php if( $args['first_name_status'] ) { ?>
		<div class="input-wrap input-wrap-white">
			<input 
				type="text"
				placeholder="<?php echo esc_attr( $args['first_name_placeholder'] ) ?>" 
				name="booking_customer_first_name"
				class="input-field drplus-consult-form-widget-input"

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
				class="input-field drplus-consult-form-widget-input"

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
					class="input-field drplus-consult-form-widget-input input-ltr drplus-nid-input drplus-numeric-input drplus-book-form-widget-nid"

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
			<label class="checkbox-wrap drplus-foreign-checkbox-wrap drplus-consult-form-widget-foreign-wrap">
				<input type="checkbox" name="booking_foreign_customer" class="drplus-foreign-checkbox">
				<span class="drplus-consult-form-widget-foreign-checkbox-text"><?php esc_html_e( 'I am a foreign national and do not have a national ID number.', 'drplus' ) ?></span>
			</label>
		</div>
	<?php } ?>

	<?php if( $args['phone_status'] ) { ?>
		<div class="input-group">
			<div class="input-wrap input-wrap-white">
				<input 
					type="text"
					placeholder="<?php echo esc_attr( $args['phone_placeholder'] ) ?>" 
					name="booking_customer_phone"
					class="input-field drplus-consult-form-widget-input <?php echo !$use_outside_iran ? 'drplus-phone-input' : 'drplus-numeric-input' ?> input-ltr drplus-book-form-widget-phone"

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

	<?php if( $args['specialist_status'] ) { ?>
		<div class="input-group">
			<?php
			get_template_part( 'templates/components/template-components-search', null, [
				'search_hospitals'		=> false,
				'search_specialities'	=> false,
				'only'					=> 'specialist_online_visit',
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

	<div class="drplus-consult-form-widget-button-wrap">
		<?php
		$button_args['button_atts'] = [
			'name'	=> 'booking_widget_submit'
		];
		Button::view( $button_args );
		?>
	</div>
</form>