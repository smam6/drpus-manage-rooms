<?php

use DrPlus\Utils;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;
use DrPlus\Utils\UI;
use DrPlus\Utils\User;

$options = Options::get_options( [
	'onboard-info-field-name-enabled'				=> true,
	'onboard-info-field-subtitle-enabled'			=> true,
	'onboard-info-field-email-enabled'				=> true,
	'onboard-info-field-birthday-enabled'			=> true,
	'onboard-info-field-nid-enabled'				=> true,
	'onboard-info-field-specialist-code-enabled'	=> true,
	'onboard-info-field-phone-enabled'				=> true,
	'onboard-info-field-gender-enabled'				=> true,
	'onboard-info-field-bio-enabled'				=> true,
	'onboard-info-field-name-required'				=> true,
	'onboard-info-field-subtitle-required'			=> true,
	'onboard-info-field-email-required'				=> true,
	'onboard-info-field-birthday-required'			=> true,
	'onboard-info-field-nid-required'				=> true,
	'onboard-info-field-specialist-code-required'	=> true,
	'onboard-info-field-phone-required'				=> true,
	'onboard-info-field-gender-required'			=> true,
	'onboard-info-field-bio-required'				=> false,

	'use-outside-iran'								=> false,
] );

$use_outside_iran = Utils::to_bool( $options['use-outside-iran'] );

extract( $args );

$birthday = User::get_birthday( $specialist->user_id );
if( !is_numeric( $birthday ) ) {
	$birthday = strtotime( $birthday );
}
$eighteen_years_ago = (int)date_i18n( 'U' ) - (18 * YEAR_IN_SECONDS);
if( !empty( $birthday ) && $birthday > $eighteen_years_ago ) {
	$birthday = $eighteen_years_ago;
}

if( function_exists( 'drplus_wc_my_account_avatar' ) ) {
	drplus_wc_my_account_avatar();
}
UI::input_with_label( [ // Firstname
	'label'			=> __( 'Firstname', 'drplus' ),
	'placeholder'	=> __( "Your firstname", 'drplus' ),
	'value'			=> $specialist->user->first_name,
	'id'			=> 'onboard-first_name-input',
	'name'			=> 'specialist_first_name',
	'minlength'		=> '1',
	'autofocus'		=> 'autofocus',
	'autocomplete'	=> 'given-name',
	'required'		=> 'required',
	
	'group_classes'	=> ['onboard-input-group', 'onboard-first_name-group'],
	'input_classes'	=> ['onboard-input'],
] );

UI::input_with_label( [ // Lastname
	'label'			=> __( 'Lastname', 'drplus' ),
	'placeholder'	=> __( "Your lastname", 'drplus' ),
	'value'			=> $specialist->user->last_name,
	'id'			=> 'onboard-last_name-input',
	'name'			=> 'specialist_last_name',
	'minlength'		=> '1',
	'autocomplete'	=> 'family-name',
	'required'		=> 'required',
	
	'group_classes'	=> ['onboard-input-group', 'onboard-last_name-group'],
	'input_classes'	=> ['onboard-input'],
] );

if( $options['onboard-info-field-name-enabled'] ) {
	UI::input_with_label( [ // Display name
		'label'			=> __( 'Display name', 'drplus' ),
		'placeholder'	=> __( "Display name for your card and profile in website", 'drplus' ),
		'value'			=> $specialist->name,
		'id'			=> 'onboard-name-input',
		'name'			=> 'specialist_name',
		'required'		=> $options['onboard-info-field-name-required'],
		
		'group_classes'	=> ['onboard-input-group', 'onboard-name-group'],
		'input_classes'	=> ['onboard-input'],
	] );
}

if( $options['onboard-info-field-subtitle-enabled'] ) {
	UI::input_with_label( [ // Subtitle
		'label'			=> __( 'Subtitle', 'drplus' ),
		'placeholder'	=> __( "Subtitle for your card in website", 'drplus' ),
		'value'			=> $specialist->subtitle,
		'id'			=> 'onboard-subtitle-input',
		'name'			=> 'specialist_subtitle',
		'minlength'		=> '1',
		'required'		=> $options['onboard-info-field-subtitle-required'],
		
		'group_classes'	=> ['onboard-input-group', 'onboard-subtitle-group'],
		'input_classes'	=> ['onboard-input'],
	] );
}

UI::input_with_label( [ // Slug
	'label'			=> __( 'Slug', 'drplus' ),
	'placeholder'	=> trailingslashit( home_url() ) . "specialist/...",
	'value'			=> $specialist->slug,
	'id'			=> 'onboard-slug-input',
	'name'			=> 'specialist_slug',
	'minlength'		=> '1',
	'maxlength'		=> '255',
	'required'		=> 'required',
	'disabled'		=> !empty( $disable_slug ),
	
	'group_classes'	=> ['onboard-input-group', 'onboard-slug-group'],
	'input_classes'	=> ['onboard-input', 'input-ltr', 'drplus-slug-input'],
] );

if( $options['onboard-info-field-email-enabled'] ) {
	UI::input_with_label( [ // Email
		'label'			=> __( 'Email', 'drplus' ),
		'placeholder'	=> __( "Your email address", 'drplus' ),
		'type'			=> 'email',
		'value'			=> $specialist->user->user_email,
		'id'			=> 'onboard-email-input',
		'name'			=> 'specialist_email',
		'minlength'		=> '1',
		'required'		=> $options['onboard-info-field-email-required'],
		'autocomplete'	=> 'email',
		
		'group_classes'	=> ['onboard-input-group', 'onboard-email-group'],
		'input_classes'	=> ['onboard-input', 'input-ltr'],
	] );
}

if( $options['onboard-info-field-birthday-enabled'] ) {
	UI::input_with_label( [ // Birthday
		'label'			=> __( 'Birthday', 'drplus' ),
		'data-time'		=> !empty( $birthday ) ? $birthday : $eighteen_years_ago,
		'id'			=> 'onboard-birthday-input',
		'required'		=> $options['onboard-info-field-birthday-required'],
		'readonly'		=> 'readonly',
		'data-options'	=> [
			'maxDate'	=> $eighteen_years_ago*1000,
		],
		
		'group_classes'	=> ['onboard-input-group', 'onboard-birthday-group'],
		'input_classes'	=> ['onboard-input', 'drplus-datepicker-input'],
	
		'alt_field'		=> [
			'id'	=> "onboard-birthday-input_alt", // Don't remove _alt
			'name'	=> "specialist_birthday",
			'value'	=> $birthday
		],
	] );
}

if( $options['onboard-info-field-nid-enabled'] ) {
	UI::input_with_label( [ // National ID
		'label'			=> __( 'National ID', 'drplus' ),
		'placeholder'	=> __( "Your National ID", 'drplus' ),
		'value'			=> User::get_nid( $specialist->user_id ),
		'id'			=> 'onboard-nid-input',
		'name'			=> 'specialist_nid',
		'required'		=> $options['onboard-info-field-nid-required'],
		'minlength'		=> 10,
		'maxlength'		=> 10,
		'inputmode'		=> 'numeric',
		
		'group_classes'	=> ['onboard-input-group', 'onboard-nid-group'],
		'input_classes'	=> ['onboard-input', 'input-ltr', 'drplus-numeric-input', 'drplus-nid-input'],
	] );
}

if( $options['onboard-info-field-specialist-code-enabled'] ) {
	UI::input_with_label( [ // Medical ID
		'label'			=> __( 'Medical ID', 'drplus' ),
		'placeholder'	=> __( "Your Medical ID", 'drplus' ),
		'value'			=> User::get_specialist_code( $specialist->user_id ),
		'id'			=> 'onboard-specialist_code-input',
		'name'			=> 'specialist_specialist_code',
		'required'		=> $options['onboard-info-field-specialist-code-required'],
		'minlength'		=> 1,
		
		'group_classes'	=> ['onboard-input-group', 'onboard-specialist_code-group'],
		'input_classes'	=> ['onboard-input'],
	] );
}

if( $options['onboard-info-field-phone-enabled'] ) {
	$phone_input_args = [ // Phone number
		'label'			=> __( 'Phone number', 'drplus' ),
		'value'			=> !$use_outside_iran ? Formatters::phone( User::get_phone( $specialist->user_id ) ) : User::get_phone( $specialist->user_id ),
		'id'			=> 'onboard-mobile-input',
		'name'			=> 'specialist_mobile',
		'required'		=> $options['onboard-info-field-phone-required'],
		'inputmode'		=> 'tel',
		'autocomplete'	=> 'tel',
		
		'group_classes'	=> ['onboard-input-group', 'onboard-mobile-group'],
		'input_classes'	=> ['onboard-input', 'input-ltr'],
	];
	if( !$use_outside_iran ) {
		$phone_input_args['minlength'] = 13;
		$phone_input_args['maxlength'] = 13;
		$phone_input_args['placeholder'] = "09...";
		$phone_input_args['input_classes'][] = 'drplus-phone-input';
	} else {
		$phone_input_args['input_classes'][] = 'drplus-numeric-input';
	}
	UI::input_with_label( $phone_input_args );
	unset( $phone_input_args );
}

if( $options['onboard-info-field-gender-enabled'] ) {
	UI::select_with_label( [ // Gender
		'label'			=> __( 'Gender', 'drplus' ),
		'value'			=> User::get_gender( $specialist->user_id ),
		'id'			=> 'onboard-gender-input',
		'name'			=> 'specialist_gender',
		'required'		=> $options['onboard-info-field-gender-required'],
		'data-width'	=> '100%',
		'options'			=> [
			'male'		=> __( 'Male', 'drplus' ),
			'female'	=> __( 'Female', 'drplus' ),
		],
		
		'group_classes'		=> ['onboard-input-group', 'onboard-gender-group'],
		'select_classes'	=> ['onboard-input', 'drplus-select2'],
	] );
}

if( $options['onboard-info-field-bio-enabled'] ) {
	if( !empty( $args['my-account'] ) ) {
		?>
		<div class="input-group onboard-input-group onboard-about-group">
			<label for="onboard-about-input" id="onboard-about-input_label" class="input-label"><?php esc_html_e( 'Biography', 'drplus' ) ?></label>
			<?php wp_editor( $specialist->about, 'specialist_about' ) ?>
		</div>
		<?php
	} else {
		UI::input_with_label( [ // Biography
			'label'			=> __( 'Biography', 'drplus' ),
			'placeholder'	=> __( "A few brief lines about yourself...", 'drplus' ),
			'value'			=> wp_strip_all_tags( $specialist->about ),
			'id'			=> 'onboard-about-input',
			'name'			=> 'specialist_about',
			'required'		=> $options['onboard-info-field-bio-required'],
			
			'group_classes'	=> ['onboard-input-group', 'onboard-about-group'],
			'input_classes'	=> ['onboard-input'],
		
			'textarea'	=> true,
		] );
	}
}