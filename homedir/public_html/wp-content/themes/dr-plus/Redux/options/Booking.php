<?php

use DrPlus\Utils;
use DrPlus\Utils\Booking;

defined( 'ABSPATH' ) || exit;

$_pages = get_pages();
$pages = [];
foreach( $_pages as $page ) {
	$pages[$page->ID] = "{$page->post_title} ({$page->ID})";
}

$currency_symbol = esc_html__( 'Toman', 'drplus' );
if( Utils::is_wc_active() ) {
	$woocommerce_currency = get_woocommerce_currency();
	if( !in_array( get_woocommerce_currency(), ['IRR', 'IRT', 'IRHR', 'IRHT'] ) ) {
		$currency_symbol = get_woocommerce_currency_symbol();
	}
}

Redux::set_section( // General settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'General settings', 'drplus' ),
		'id'			=> 'booking-general-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // enable-booking
				'id'		=> 'enable-booking',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Booking', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
			],
			[ // booking-page-id
				'id'		=> 'booking-page-id',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Select your booking page', 'drplus' ),
				'subtitle'	=> esc_html__( 'Choose a page from the list.', 'drplus' ),
				'desc'		=> esc_html__( 'Use [drplus_booking] shortcode on this page', 'drplus' ),
				'options'	=> $pages,
				'required'	=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-datepicker',
				'type'		=> 'button_set',
				'title'		=> esc_html__( 'Datepicker', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Persian', 'drplus' ) ),
				'options'	=> [
					'persian'	=> esc_html__( 'Persian', 'drplus' ),
					'gregorian'	=> esc_html__( 'Gregorian', 'drplus' ),
				],
				'default'	=> 'persian',
				'required'	=> [
					['enable-booking','=',true]
				]
			],
			[ // enable-booking
				'id'		=> 'guest-booking',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Allow Appointment Booking for Guest Users', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'desc'		=> esc_html__( 'When enabled, guest users can select the doctor, date, and time without logging in and proceed to the “Information Completion” step. If they are not logged in at this stage, they will be redirected to the login page and returned to the same step after logging in. Note that this feature works only when Doctor Plus’s built‑in authentication system is active; if an external authentication plugin is used, appointments can only be made by logged‑in users', 'drplus' ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
				'required'	=> [
					['enable-booking','=',true],
					['auth', '=', true]
				]
			],
			[ // booking_info-notice
				'id'	=> 'booking_info-notice',
				'type'	=> 'info',
				'desc'	=> sprintf( __( "After changing the booking page please go to <a href='%s'>Permalink settings</a> and just save the settings again.", 'drplus' ), admin_url( "options-permalink.php" ) ),
				'style'	=> 'info',
				'icon'	=> 'el-icon-info-sign',
				'required'	=> [
					['enable-booking','=',true]
				],
			],
			[ // offline_reserve_time_text
				'id'			=> 'offline_reserve_time_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Reserve time button text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Book an appointment', 'drplus' ) ),
				'default'		=> esc_html__( 'Book an appointment', 'drplus' ),
				'placeholder'	=> esc_html__( 'Book an appointment', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // online_reserve_time_text
				'id'			=> 'online_reserve_time_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Request consultation button text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Request Consultation', 'drplus' ) ),
				'default'		=> esc_html__( 'Request Consultation', 'drplus' ),
				'placeholder'	=> esc_html__( 'Request Consultation', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // view_specialist_btn_text
				'id'			=> 'view_specialist_btn_text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'View specialist button text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'View Specialist', 'drplus' ) ),
				'default'		=> esc_html__( 'View Specialist', 'drplus' ),
				'placeholder'	=> esc_html__( 'View Specialist', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-specialist-not-found
				'id'			=> 'booking-specialist-not-found',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Message for when the selected specialist is not found', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Selected specialist not found', 'drplus' ) ),
				'default'		=> esc_html__( 'Selected specialist not found', 'drplus' ),
				'placeholder'	=> esc_html__( 'Selected specialist not found', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-commission-type (none, fixed, percentage)
				'id'			=> 'booking-commission-type',
				'type'			=> 'select',
				'title'			=> esc_html__( 'Booking commission type', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'None', 'drplus' ) ),
				'default'		=> 'none',
				'options'		=> array(
					'none'			=> esc_html__( 'None', 'drplus' ),
					'fixed'			=> esc_html__( 'Fixed', 'drplus' ),
					'percentage'	=> esc_html__( 'Percentage', 'drplus' ),
				),
				'desc'			=> esc_html__( 'Select the type of commission for booking.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-commission-calculate-type
				'id'			=> 'booking-commission-calculate-type',
				'type'			=> 'select',
				'title'			=> esc_html__( 'Booking commission calculate type', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Add to customer order', 'drplus' ) ),
				'default'		=> 'add_to_customer_order',
				'options'		=> array(
					'add_to_customer_order'				=> esc_html__( 'Add to customer order', 'drplus' ),
					'deduction_from_specialist_income'	=> esc_html__( 'Deduction from specialist income', 'drplus' ),
				),
				'desc'			=> esc_html__( 'Select the type of commission calculation for booking.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
					['booking-commission-type','!=','none'],
				],
			],
			[ // booking-commission-fixed-amount
				'id'			=> 'booking-commission-fixed-amount',
				'type'			=> 'text',
				'title'			=> sprintf( esc_html__( "Booking commission amount (%s)", 'drplus' ), $currency_symbol ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), 0 ),
				'default'		=> 0,
				'placeholder'	=> 0,
				'desc'			=> sprintf( esc_html__( "Enter the fixed amount (%s) of the commission for booking", 'drplus' ), $currency_symbol ),
				'validate' 		=> 'numeric',
				'required'		=> [
					['enable-booking','=',true],
					['booking-commission-type','=','fixed'],
				],
			],
			[ // booking-commission-amount
				'id'			=> 'booking-commission-percentage-amount',
				'type'			=> 'slider',
				'title'			=> esc_html__( 'Booking commission percentage', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '0' ),
				'default'		=> 0,
				"min"       	=> 0,
				"step"      	=> 1,
				"max"       	=> 100,
				'display_value'	=> 'text',
				'placeholder'	=> '0',
				'desc'			=> esc_html__( 'Enter the percentage of the commission for booking (percent of specialist visit price)', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
					['booking-commission-type','=','percentage'],
				],
			],
			[ // booking-commission-discount
				'id'		=> 'booking-commission-apply-coupon',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Apply Coupon on commission', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Enable', 'drplus' ) ),
				'on'		=> esc_html__( 'Enable', 'drplus' ),
				'off'		=> esc_html__( 'Disable', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true],
					['booking-commission-type','!=','none'],
				],
			],
			[
				'id'		=> 'booking-active-online-visits',
				'type'		=> 'checkbox',
				'title'		=> esc_html__( 'Active online visits', 'drplus' ),
				'default'	=> '1',
				'options' 	=> wp_list_pluck( Booking::consultation_offices(), 'label' ),
				'default' 	=> array_map( fn() => 1, Booking::consultation_offices() ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-check-status-to-cancellation
				'id'			=> 'booking-check-status-to-cancellation',
				'type'			=> 'spinner',
				'title'			=> esc_html__( "User's final reservation registration deadline until automatic cancellation (Minutes)", 'drplus' ), // مهلت ثبت نهایی رزرو کاربر تا کنسل شدن خودکار
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '30' ),
				'default'		=> '30',
				'min'			=> '10',
				'step'			=> '1',
				'max'			=> '60',
				'required'		=> [
					['enable-booking','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Search specialist settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Search specialist settings', 'drplus' ),
		'id'			=> 'booking-specialist-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // booking-search-city-field
				'id'		=> 'booking-search-city-field',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show city field in search section', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[ // booking-search-section-tag
				'id'		=> 'booking-search-section-tag',
				'type'		=> 'select',
				'title'		=> __( 'Search section title tag', 'drplus' ),
				'default'	=> 'h1',
				'options'	=> $tags,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[ // booking-search-head-text
				'id'			=> 'booking-search-head-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Search section title text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Please select the name of your desired specialist or specialty along with the city.', 'drplus' ) ),
				'default'		=> esc_html__( 'Please select the name of your desired specialist or specialty along with the city.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Please select the name of your desired specialist or specialty along with the city.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-search-specialist-placeholder
				'id'			=> 'booking-search-specialist-placeholder',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Search specialist field placeholder', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Search for specialist name', 'drplus' ) ),
				'default'		=> esc_html__( 'Search for specialist name', 'drplus' ),
				'placeholder'	=> esc_html__( 'Search for specialist name', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-search-cities-placeholder
				'id'			=> 'booking-search-cities-placeholder',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Search cities field placeholder', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'All cities', 'drplus' ) ),
				'default'		=> esc_html__( 'All cities', 'drplus' ),
				'placeholder'	=> esc_html__( 'All cities', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
					['booking-search-city-field','=',true],
				],
			],
			[ // booking-search-no-result-text
				'id'			=> 'booking-search-result-count',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Search result count to show', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %d", 'drplus' ), 10 ),
				'default'		=> 10,
				'placeholder'	=> 10,
				'validate' 		=> 'numeric',
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-search-no-result-text
				'id'			=> 'booking-search-no-result-text',
				'type'			=> 'text',
				'title'			=> esc_html__( 'No result search text', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Unfortunately, no specialist was found with your specifications. Please check the information you entered and search again.', 'drplus' ) ),
				'default'		=> esc_html__( 'Unfortunately, no specialist was found with your specifications. Please check the information you entered and search again.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Unfortunately, no specialist was found with your specifications. Please check the information you entered and search again.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-search-row-bg
				'id'			=> 'booking-search-row-bg',
				'type'			=> 'background',
				'title'			=> __( 'Search section background', 'drplus' ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> [
					'background-image'	=> DRPLUS_URI . "assets/images/booking-search-head.svg",
					'background-color'	=> '#1dbab5',
					'background-repeat'	=> 'no-repeat',
					'background-size'	=> 'cover',
				],
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[ // booking-search-row-bg_dark
				'id'			=> 'booking-search-row-bg_dark',
				'type'			=> 'background',
				'title'			=> sprintf( __( '%s (Dark mode)', 'drplus' ), __( 'Search section background', 'drplus' ) ),
				'compiler'		=> true,
				'transparent'	=> true,
				'default'		=> [
					'background-image'	=> DRPLUS_URI . "assets/images/booking-search-head.svg",
					'background-color'	=> '#0f3e4f',
					'background-repeat'	=> 'no-repeat',
					'background-size'	=> 'cover',
				],
				'required'		=> [
					['enable-booking','=',true],
					['color_mode','=', 'both'],
				]
			],

			[ // divider
				'id'	=> 'booking-search-divider',
				'type'	=> 'divide',
			],

			[ // booking-search-show-recent
				'id'		=> 'booking-search-show-recent',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Show specialists', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[ // booking-search-recent-title
				'id'			=> 'booking-search-recent-title',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Specialists section title', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Latest viewed specialists', 'drplus' ) ),
				'default'		=> esc_html__( 'Latest viewed specialists', 'drplus' ),
				'placeholder'	=> esc_html__( 'Latest viewed specialists', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
					['booking-search-show-recent','=',true],
				],
			],
			[ // booking-search-specialists-type
				'id'		=> 'booking-search-specialists-type',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Specialists type', 'drplus' ),
				'subtitle'	=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Latest viewed specialists', 'drplus' ) ),
				'default'	=> 'latest_view',
				'options'	=> [
					'latest_view'	=> esc_html__( 'Latest viewed specialists', 'drplus' ),
					'latests'		=> esc_html__( 'Latest added specialists', 'drplus' ),
				],
				'required'	=> [
					['enable-booking','=',true],
					['booking-search-show-recent','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Info fields
	$opt_name,
	array(
		'title'			=> esc_html__( 'Info step fields', 'drplus' ),
		'id'			=> 'booking-info-step-fields-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'booking-info-field-phone-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable phone field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-phone-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'is phone field Required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-phone-enabled','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-email-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable email field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-email-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is email field required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-email-enabled','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-nid-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable national ID field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-nid-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is national ID field required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-nid-enabled','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-gender-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable gender field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-gender-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is gender field required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-gender-enabled','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-birthday-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable birthday field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-birthday-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is birthday field required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-birthday-enabled','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-reason-enabled',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable reason for visit field', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'Yes', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> true,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[
				'id'		=> 'booking-info-field-reason-required',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Is reason for visit field required?', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-reason-enabled','=',true]
				]
			],

			[
				'id'	=> 'booking-info-fields-divider',
				'type'	=> 'divide'
			],

			[ // booking-info-field-birthday-format
				'id'			=> 'booking-info-field-birthday-format',
				'type'			=> 'select',
				'title'			=> esc_html__( 'Birthday date format', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), date_i18n( 'j F Y' ) ),
				'default'		=> 'DD MMMM YYYY',
				'options'		=> array(
					'DD MMMM YYYY'	=> date_i18n( 'd F Y' ),
					'DD-MMMM-YYYY'	=> date_i18n( 'd-F-Y' ),
					'DD/MMMM/YYYY'	=> date_i18n( 'd/F/Y' ),
					'YYYY-MM-DD'	=> date_i18n( 'Y-m-d' ),
					'YY-MM-DD'		=> date_i18n( 'y-m-d' ),
				),
				'required'		=> [
					['enable-booking','=',true],
					['booking-info-field-birthday-enabled','=',true]
				],
			],
		),
	)
);

Redux::set_section( // receipt page settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Appointment receipt page settings', 'drplus' ),
		'id'			=> 'booking-appointment-receipt-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // booking-receipt-section-title-success
				'id'			=> 'booking-receipt-section-title-success',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Receipt title for success booking', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Thank you. your requested appointment has been booked.', 'drplus' ) ),
				'default'		=> esc_html__( 'Thank you. your requested appointment has been booked.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Thank you. your requested appointment has been booked.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-title-pending
				'id'			=> 'booking-receipt-section-title-pending',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Receipt title for pending booking', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Your appointment has been reserved, but payment is still pending.', 'drplus' ) ),
				'default'		=> esc_html__( 'Your appointment has been reserved, but payment is still pending.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Your appointment has been reserved, but payment is still pending.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-title-failed
				'id'			=> 'booking-receipt-section-title-failed',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Receipt title for failed booking', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'We\'re sorry; your appointment booking was unsuccessful. Please attempt your book again.', 'drplus' ) ),
				'default'		=> esc_html__( 'We\'re sorry; your appointment booking was unsuccessful. Please attempt your book again.', 'drplus' ),
				'placeholder'	=> esc_html__( 'We\'re sorry; your appointment booking was unsuccessful. Please attempt your book again.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-title-cancelled
				'id'			=> 'booking-receipt-section-title-cancelled',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Receipt title for cancelled booking', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Your appointment has been cancelled as requested.', 'drplus' ) ),
				'default'		=> esc_html__( 'Your appointment has been cancelled as requested.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Your appointment has been cancelled as requested.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-title-refunded
				'id'			=> 'booking-receipt-section-title-refunded',
				'type'			=> 'text',
				'title'			=> esc_html__( 'Receipt title for refunded booking', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Your payment has been refunded successfully.', 'drplus' ) ),
				'default'		=> esc_html__( 'Your payment has been refunded successfully.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Your payment has been refunded successfully.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-offline-description
				'id'			=> 'booking-receipt-section-offline-description',
				'type'			=> 'textarea',
				'rows'			=> 5,
				'title'			=> esc_html__( 'Receipt note for visit at office customer', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'Please arrive at the medical center 30 minutes before your scheduled appointment time.
In case of cancellation, half of the consultation fee will be deducted.', 'drplus' ) ),
				'default'		=> esc_html__( 'Please arrive at the medical center 30 minutes before your scheduled appointment time.
In case of cancellation, half of the consultation fee will be deducted.', 'drplus' ),
				'placeholder'	=> esc_html__( 'Please arrive at the medical center 30 minutes before your scheduled appointment time.
In case of cancellation, half of the consultation fee will be deducted.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-online-description (for phone-consultation)
				'id'			=> 'booking-receipt-section-online-description',
				'type'			=> 'textarea',
				'rows'			=> 5,
				'title'			=> esc_html__( 'Receipt note for phone consultation customer', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'At the scheduled consultation time, the specialist will contact you using the phone number you provided during the booking.
Please make sure to be available at that time and ensure your phone is turned on and has proper signal coverage.', 'drplus' ) ),
				'default'		=> esc_html__( 'At the scheduled consultation time, the specialist will contact you using the phone number you provided during the booking.
Please make sure to be available at that time and ensure your phone is turned on and has proper signal coverage.', 'drplus' ),
				'placeholder'	=> esc_html__( 'At the scheduled consultation time, the specialist will contact you using the phone number you provided during the booking.
Please make sure to be available at that time and ensure your phone is turned on and has proper signal coverage.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-chat-consultation-description
				'id'			=> 'booking-receipt-section-chat-consultation-description',
				'type'			=> 'textarea',
				'rows'			=> 5,
				'title'			=> esc_html__( 'Receipt note for chat consultation customer', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'At the scheduled time for your consultation, your online chat will open on the DoctorPlus website.
You can access your chat list from the Chats section of your account.', 'drplus' ) ),
				'default'		=> esc_html__( 'At the scheduled time for your consultation, your online chat will open on the DoctorPlus website.
You can access your chat list from the Chats section of your account.', 'drplus' ),
				'placeholder'	=> esc_html__( 'At the scheduled time for your consultation, your online chat will open on the DoctorPlus website.
You can access your chat list from the Chats section of your account.', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-instant-chat-consultation-description
				'id'			=> 'booking-receipt-section-instant-chat-consultation-description',
				'type'			=> 'textarea',
				'rows'			=> 5,
				'title'			=> esc_html__( 'Receipt note for instant chat consultation customer', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), '' ),
				'default'		=> '',
				'placeholder'	=> '',
				'required'		=> [
					['enable-booking','=',true],
				],
			],
			[ // booking-receipt-section-video-consultation-description
				'id'			=> 'booking-receipt-section-video-consultation-description',
				'type'			=> 'textarea',
				'rows'			=> 5,
				'title'			=> esc_html__( 'Receipt note for video consultation customer', 'drplus' ),
				'subtitle'		=> sprintf( __( "Default: %s", 'drplus' ), esc_html__( 'At the scheduled time for your consultation, your video meeting will open on the DoctorPlus website. Meeting link will show here', 'drplus' ) ),
				'default'		=> esc_html__( 'At the scheduled time for your consultation, your video meeting will open on the DoctorPlus website. Meeting link will show here', 'drplus' ),
				'placeholder'	=> esc_html__( 'At the scheduled time for your consultation, your video meeting will open on the DoctorPlus website. Meeting link will show here', 'drplus' ),
				'required'		=> [
					['enable-booking','=',true],
				],
			],
		),
	)
);

Redux::set_section( // Cancellation settings
	$opt_name,
	array(
		'title'			=> esc_html__( 'Cancel appointment settings', 'drplus' ),
		'id'			=> 'booking-cancel-app-settings-section',
		'subsection'	=> true,
		'fields'		=> array(
			[
				'id'		=> 'booking-cancel-by-customer',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Cancel booking by customer', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'		=> [
					['enable-booking','=',true]
				],
			],
			[ // Refund - Specialist Share
				'id'            => 'booking-cancel-by-customer-refund-percentage-specialist',
				'type'          => 'slider',
				'title'         => sprintf( '%s: %s', esc_html__( 'Cancel by customer', 'drplus' ), esc_html__( "Refund percentage deducted from specialist's payout", 'drplus' ) ),
				'subtitle'      => sprintf( __( "Default: %s%%", 'drplus' ), '100' ),
				'default'       => 100,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'placeholder'   => '0',
				'desc'          => esc_html__( "The selected percentage will be deducted from the specialist's payout and refunded to the user. The remaining amount is paid to the specialist.", 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
					['booking-cancel-by-customer', '=', true],
				],
			],
			[ // Refund - Commission Share
				'id'            => 'booking-cancel-by-customer-refund-percentage-commission',
				'type'          => 'slider',
				'title'         => sprintf( '%s: %s', esc_html__( 'Cancel by customer', 'drplus' ), esc_html__( "Refund percentage deducted from site commission", 'drplus' ) ),
				'subtitle'      => sprintf( __( "Default: %s%%", 'drplus' ), '100' ),
				'default'       => 100,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'placeholder'   => '0',
				'desc'          => esc_html__( "The selected percentage will be refunded from the commission share held by the platform. The remaining commission stays with the site.", 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
					['booking-cancel-by-customer', '=', true],
					['booking-commission-type','!=','none'],
				],
			],
			[ // Minimum cancellation time by user
				'id'            => 'booking-min-cancellation-hours',
				'type'          => 'spinner',
				'title'         => esc_html__( 'Minimum cancellation time before appointment (hours)', 'drplus' ),
				'subtitle'      => sprintf( __( 'Default: %s hours', 'drplus' ), '24' ),
				'default'       => 24,
				'min'           => 0,
				'step'          => 1,
				'max'           => 720, // up to 30 days before
				'placeholder'   => '24',
				'desc'          => esc_html__( 'Customers can cancel the appointment only within the selected number of hours before the appointment time.', 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
					['booking-cancel-by-customer', '=', true]
				],
			],
			[
				'id'	=> 'booking-cancel-fields-divider',
				'type'	=> 'divide'
			],
			[
				'id'		=> 'booking-cancel-by-specialist',
				'type'		=> 'switch',
				'title'		=> esc_html__( 'Enable Cancel booking by specialist', 'drplus' ),
				'subtitle'	=> sprintf( esc_html__( 'Default: %s', 'drplus' ), esc_html__( 'No', 'drplus' ) ),
				'on'		=> esc_html__( 'Yes', 'drplus' ),
				'off'		=> esc_html__( 'No', 'drplus' ),
				'default'	=> false,
				'required'		=> [
					['enable-booking','=',true]
				]
			],
			[ // Refund - Specialist Share
				'id'            => 'booking-cancel-by-specialist-refund-percentage-specialist',
				'type'          => 'slider',
				'title'         => sprintf( '%s: %s', esc_html__( 'Cancel by specialist', 'drplus' ), esc_html__( "Refund percentage deducted from specialist's payout", 'drplus' ) ),
				'subtitle'      => sprintf( __( "Default: %s%%", 'drplus' ), '100' ),
				'default'       => 100,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'placeholder'   => '0',
				'desc'          => esc_html__( "The selected percentage will be deducted from the specialist's payout and refunded to the user. The remaining amount is paid to the specialist.", 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
					['booking-cancel-by-specialist', '=', true],
				],
			],
			[ // Refund - Commission Share
				'id'            => 'booking-cancel-by-specialist-refund-percentage-commission',
				'type'          => 'slider',
				'title'         => sprintf( '%s: %s', esc_html__( 'Cancel by specialist', 'drplus' ), esc_html__( "Refund percentage deducted from site commission", 'drplus' ) ),
				'subtitle'      => sprintf( __( "Default: %s%%", 'drplus' ), '100' ),
				'default'       => 100,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'placeholder'   => '0',
				'desc'          => esc_html__( "The selected percentage will be refunded from the commission share held by the platform. The remaining commission stays with the site.", 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
					['booking-cancel-by-specialist', '=', true],
					['booking-commission-type','!=','none'],
				],
			],
			[
				'id'	=> 'booking-cancel-fields-divider-2',
				'type'	=> 'divide'
			],
			[ // Refund - Specialist Share
				'id'            => 'booking-cancel-by-admin-refund-percentage-specialist',
				'type'          => 'slider',
				'title'         => sprintf( '%s: %s', esc_html__( 'Cancel by admin', 'drplus' ), esc_html__( "Refund percentage deducted from specialist's payout", 'drplus' ) ),
				'subtitle'      => sprintf( __( "Default: %s%%", 'drplus' ), '100' ),
				'default'       => 100,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'placeholder'   => '0',
				'desc'          => esc_html__( "The selected percentage will be deducted from the specialist's payout and refunded to the user. The remaining amount is paid to the specialist.", 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
				],
			],
			[ // Refund - Commission Share
				'id'            => 'booking-cancel-by-admin-refund-percentage-commission',
				'type'          => 'slider',
				'title'         => sprintf( '%s: %s', esc_html__( 'Cancel by admin', 'drplus' ), esc_html__( "Refund percentage deducted from site commission", 'drplus' ) ),
				'subtitle'      => sprintf( __( "Default: %s%%", 'drplus' ), '100' ),
				'default'       => 100,
				'min'           => 0,
				'step'          => 1,
				'max'           => 100,
				'display_value' => 'text',
				'placeholder'   => '0',
				'desc'          => esc_html__( "The selected percentage will be refunded from the commission share held by the platform. The remaining commission stays with the site.", 'drplus' ),
				'required'      => [
					['enable-booking', '=', true],
					['booking-commission-type','!=','none'],
				],
			],
		),
	)
);
