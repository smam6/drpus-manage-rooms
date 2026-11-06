<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'wrap'		=> [
		'class'		=> ['drplus-search-input-wrap', 'input-wrap'],
		'classes'	=> [], // Custom classes
	],
	'input'	=> [
		'name'		=> '',
		'id'		=> wp_generate_uuid4(),
		'class'		=> ['drplus-search-input'],
		'classes'	=> [], // Custom classes
	],
	'change_bg_when_filled'	=> true,
	'placeholder'	=> esc_html__( "Select", 'drplus' ),
	'value'			=> '',
] );

if( $args['change_bg_when_filled'] ) $args['wrap']['classes'][] = 'drplus-filled-change-bg';

if( $args['placeholder'] === '' ) {
	$args['placeholder'] = '&nbsp;';
}

$wrap = $args['wrap'];
$wrap['class'] = array_merge( $wrap['class'], $wrap['classes'] );
unset( $wrap['classes'] );

$input = $args['input'];
$input['type'] = 'search';
$input['class'] = array_merge( $input['class'], $input['classes'] );
unset( $input['classes'] );
if( $input['name'] === '' ) {
	unset( $input['name'] );
}
$input['placeholder'] = $args['placeholder'];
$input['value'] = $args['value'];
$input['data-value'] = $args['value'];

if( $input['value'] !== '' ) {
	$wrap['class'][] = 'filled';
}
?>
<label <?php echo Utils::get_html_attributes( $wrap ) ?>>
	<input <?php echo Utils::get_html_attributes( $input ) ?>>
</label>