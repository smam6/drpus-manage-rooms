<?php

if( !defined( 'ABSPATH' ) ) exit;

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Page;

$options = Options::get_options( [
	'show_header'				=> true,
	'sticky_header'				=> true,
	'show-header-menu'			=> true,
	'header-menu-align'			=> is_rtl() ? 'right' : 'left',
	'show-cart'					=> true,
	'show-account-btn'			=> true,
	'show-header-action-btn'	=> true,
	'show-header-mobile-menu'	=> true,
] );

$disable_header = !Utils::to_bool( $options['show_header'] );
$is_sticky = Utils::to_bool( $options['sticky_header'] );

if( is_page() ) {
	$page_options = Page::get_options();
	if( $page_options['disable_header'] === true ) {
		$disable_header = true;
	}
	if( $page_options['disable_footer'] === true ) {
		$disable_footer = true;
	}
}

$body_classes = [];
if( $disable_header ) {
	$body_classes[] = 'header_disabled';
} else {
	$body_classes[] = $is_sticky ? 'sticky-header' : 'static-header';
}

$show_header_actions = $options['show-cart'] || $options['show-account-btn'] || $options['show-header-action-btn'];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="<?php echo apply_filters( 'drplus/meta/viewport', 'width=device-width, initial-scale=1.0, maximum-scale=5, user-scalable=yes' ) ?>">
		<?php wp_head(); ?>
	</head>

	<body <?php body_class( $body_classes ); ?>>
		<?php wp_body_open(); ?>
		<div id="container">
			<?php if( !$disable_header ) { ?>
				<?php if( !function_exists( 'elementor_theme_do_location' ) || !elementor_theme_do_location( 'header' ) ) { ?>
					<header id="header-container">
						<div id="header" class="page-width">
							<div id="drplus-header-overlay" class="drplus-overlay"></div>
							<div id="header_inner">
								<?php if( $options['show-header-mobile-menu'] ) { ?>
									<button class="button button-transparent header-mobile-menu-toggle-icons closed hide-desktop">
										<i class="drplus-icon-menu" aria-hidden="true"></i>
										<i class="drplus-icon-close" aria-hidden="true"></i>
									</button>
								<?php } ?>
								<div class="header-logo"><?php get_template_part( "templates/header/template-header-logo" ); ?></div>
								<?php if( Utils::to_bool( $options['show-header-menu'] ) ) { ?>
									<nav class="header-menu-wrap drplus-menu-wrap show-only-desktop <?php echo $options['header-menu-align'] ?>">
										<?php
										wp_nav_menu( [
											'theme_location'	=> 'main-menu',
											'container_class'	=> 'main-menu'
										] );
										?>
									</nav>
								<?php } ?>
								<?php if( Utils::to_bool( $show_header_actions ) ) { ?>
									<div class="header-actions-wrap"><?php get_template_part( "templates/header/template-header-actions" ); ?></div>								
								<?php } ?>
								<?php if( Utils::to_bool( $options['show-header-mobile-menu'] ) ) { ?>
									<div class="header-mobile-menu-wrap drplus-menu-wrap" style="display: none"><?php get_template_part( "templates/header/template-header-mobile-menu", null, [
										'show-header-actions'	=> $show_header_actions,
										'show-header-menu'		=> $options['show-header-menu']
									] ); ?></div>
								<?php } ?>
							</div>
						</div>
					</header>
				<?php } ?>
			<?php } ?>