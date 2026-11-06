<?php

use DrPlus\Utils;

$is_rtl = is_rtl();

$default_args = [
	'small'		=> true,
	'classes'	=> ['drplus-slider-nav-btn'],
	'next_icon'	=> $is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
	'prev_icon'	=> $is_rtl ? 'drplus-icon-right' : 'drplus-icon-left',
	'inline'	=> false,
];
$args = Utils::check_default( $args, $default_args, ['next_icon', 'prev_icon'] );

$nav_btn = [
	'small'		=> $args['small'],
	'classes'	=> $args['classes'],
	'type'		=> 'gray',
];
$next_nav_btn = $nav_btn+['icon' => $args['next_icon']];
$next_nav_btn['classes'][] = 'drplus-slider-nav-next';
$next_nav_btn['classes'][] = 'swiper-button-next';
$prev_nav_btn = $nav_btn+['icon' => $args['prev_icon']];
$prev_nav_btn['classes'][] = 'drplus-slider-nav-prev';
$prev_nav_btn['classes'][] = 'swiper-button-prev';

$wrap_classes = ['drplus-slider-arrows'];
if( $args['inline'] ) {
	$wrap_classes[] = 'drplus-slider-arrows-inline';
}
?>
<div class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
	<?php
	get_template_part( "templates/components/template-components-button", null, $prev_nav_btn );
	get_template_part( "templates/components/template-components-button", null, $next_nav_btn );
	?>
</div>