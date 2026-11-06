<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'wrap'		=> [
		'class'		=> ['drplus-select'],
		'classes'	=> [], // Custom classes
	],
	'label'		=> esc_html__( "Select", 'drplus' ),
	'options'	=> [],
	'value'		=> '',
	'linked'	=> false,
	'query_var'	=> '',
] );

$wrap = $args['wrap'];
$wrap['class'] = array_merge( $wrap['class'], $wrap['classes'] );
unset( $wrap['classes'] );
?>
<div <?php echo Utils::get_html_attributes( $wrap ) ?>>
	<div class="drplus-select-head">
		<div class="drplus-select-label"><?php echo esc_html( $args['label'] ) ?></div>
		<div class="drplus-select-value"><?php echo isset( $args['options'][$args['value']] ) ? esc_html( $args['options'][$args['value']] ) : '' ?></div>
		<i class="drplus-icon-bottom drplus-select-head-icon"></i>
	</div>

	<div class="drplus-select-options">
		<?php foreach( $args['options'] as $value => $label ) { ?>
			<?php
			$tag = 'div';
			$attrs = [
				'class'			=> ['drplus-select-option'],
				'data-value'	=> $value,
			];
			if( $value == $args['value'] ) {
				$attrs['class'][] = 'selected';
			}
			if( $args['linked'] ) {
				$tag = 'a';
				$attrs['href'] = add_query_arg( $args['query_var'], $value );
			}
			?>
			<<?php echo tag_escape( $tag ); ?> <?php echo Utils::get_html_attributes( $attrs ) ?>><?php echo esc_html( $label ) ?></<?php echo tag_escape( $tag ) ?>>
		<?php } ?>
	</div>
</div>