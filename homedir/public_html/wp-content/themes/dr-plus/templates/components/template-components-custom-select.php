<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'wrap'		=> [
		'class'		=> ['drplus-custom-select-wrap'],
		'classes'	=> [], // Custom classes
	],
	'select'	=> [
		'name'		=> '',
		'id'		=> wp_generate_uuid4(),
		'class'		=> ['drplus-custom-select'],
		'classes'	=> [], // Custom classes
	],
	'placeholder'	=> esc_html__( "Select", 'drplus' ),
	'options'		=> [],
	'value'			=> '',
	'change_bg_when_filled'	=> true,
] );
$wrap = $args['wrap'];
$select = $args['select'];

if( $args['change_bg_when_filled'] ) $wrap['classes'][] = 'drplus-filled-change-bg';

$has_value = $args['value'] && isset( $args['options'][$args['value']] );
if( $has_value ) {
	$wrap['class'][] = 'selected';
}

$wrap['class'] = array_merge( $wrap['class'], $wrap['classes'] );
unset( $wrap['classes'] );

$select['class'] = array_merge( $select['class'], $select['classes'] );
unset( $select['classes'] );
if( $select['name'] === '' ) {
	unset( $select['name'] );
}
?>
<label <?php echo Utils::get_html_attributes( $wrap ) ?>>
	<select <?php echo Utils::get_html_attributes( $select ) ?>>
		<?php foreach( $args['options'] as $value => $label ) { ?>
			<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $value, $args['value'] ) ?>><?php echo esc_html( $label ) ?></option>
		<?php } ?>
	</select>
	<span class="drplus-custom-select-placeholder"><?php echo $has_value ? $args['options'][$args['value']] : esc_html( $args['placeholder'] ) ?></span>
	<i class="drplus-icon-bottom drplus-custom-select-icon"></i>
</label>