<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Location;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'search_hospitals'		=> true, // Backward
	'search_specialists'	=> true, // Backward
	'search_specialities'	=> true, // Backward
	'excludes'				=> [],
	'only'					=> '', // For when want search only in one thing
	'search_field'			=> true,
	'city_field'			=> true,
	'search_placeholder'	=> __( 'Search for doctor name, clinic, specialty and more', 'drplus' ),
	'city_placeholder'		=> __( 'All cities', 'drplus' ),
	'city_value'			=> '',
	'button_show_button'	=> true,
	'change_bg_when_filled'	=> true,
	'remove_form_tag'		=> false, // True when you want to use this component inside another form

	'search_field_args'		=> [], // These args will directly passing to search input component
] );
if( $args['button_show_button'] ) {
	$args = Elementor::check_button_defaults( $args );
	$args['prefix'] = 'button_';
	$args['button_classes'][] = 'drplus-search-button';
}

if( DRPLUS_SINGLE_SPECIALIST_MODE ) $args['search_specialist'] = false;

if( Utils::to_bool( $args['city_field'] ) ) {
	$cities = Location::locations( null, false, [], true );
	if( !empty( $cities ) ) {
		$cities = wp_list_pluck( $cities, 'name', 'slug' );
	}
	$cities = array_merge( ['' => __( 'All cities', 'drplus' )], $cities );
}
?>
<?php if( !$args['remove_form_tag'] ) { ?>
	<form method="get" action="<?php echo home_url() ?>" class="drplus-search-form">
<?php } ?>
	<?php
	if( Utils::to_bool( $args['search_field'] ) ) {
		$input_args = [
			'classes'		=> ['drplus-search-text', 'drplus-search-with-ajax'],
			'data-nonce'	=> wp_create_nonce( 'drplus-search' ),
			'name'			=> 's',
		];
		if( !$args['only'] ) {
			$excludes = $args['excludes'];
			if( !$args['search_hospitals'] ) { // Backward
				$excludes[] = 'hospital';
			}
			if( !$args['search_specialists'] ) { // Backward
				$excludes[] = 'specialist';
			}
			if( !$args['search_specialities'] ) { // Backward
				$excludes[] = 'speciality';
			}
			$input_args['data-excludes'] = array_values( array_unique( $excludes ) );
		} else {
			$input_args['data-only'] = $args['only'];
		}

		$search_input_args = [
			'wrap'					=> [
				'classes'	=> ['drplus-search-field-group', 'drplus-search-text-field-group'],
			],
			'input'					=> $input_args,
			'value'					=> get_search_query(),
			'change_bg_when_filled'	=> $args['change_bg_when_filled'],
			'placeholder'			=> $args['search_placeholder'],
		];
		$search_input_args = Utils::check_default( $args['search_field_args'], $search_input_args );

		get_template_part( "templates/components/template-components-search-input", null, $search_input_args );
	}	

	if( Utils::to_bool( $args['city_field'] ) ) {
		get_template_part( "templates/components/template-components-custom-select", null, [
			'wrap'			=> [
				'classes'	=> ['drplus-search-field-group', 'drplus-search-city-field-group'],
			],
			'select'		=> [
				'classes'	=> ['drplus-search-city'],
				'name'		=> 'city',
			],
			'value'			=> $args['city_value'],
			'placeholder'	=> $args['city_placeholder'],
			'options'		=> $cities,
			'change_bg_when_filled'	=> $args['change_bg_when_filled'],
		] );
	}
	if( $args['button_show_button'] ) {
		get_template_part( "templates/components/template-components-button", null, $args );
	}
	?>
<?php if( !$args['remove_form_tag'] ) { ?>
	</form>
<?php } ?>