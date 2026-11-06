<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'show-header-actions'	=> true,
	'show-header-menu'		=> true,
]);

if( Utils::to_bool( $args['show-header-actions'] ) ) {
	?>
	<div class="header-mobile-actions-wrap">
		<?php get_template_part( "templates/header/template-header-actions", null, [
		'mobile_mode'	=> true
	] ); ?>
	</div>						
	<?php
}
if( Utils::to_bool( $args['show-header-menu'] ) ) {
	?>
	<nav class="header-menu-wrap mobile-mode">
		<?php
		wp_nav_menu( [
			'theme_location'	=> 'mobile-menu',
			'container_class'	=> 'mobile-menu'
		] );
		?>
	</nav>						
	<?php
}