<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="site-wrapper">

<a class="skip-link screen-reader-text" href="#content">
	<?php esc_html_e( 'Skip to content', 'cleanlite' ); ?>
</a>

<?php
// We add the sticky class here if enabled in customizer
$sticky_class = get_theme_mod( 'cleanlite_sticky_header', false ) ? ' is-sticky' : '';
?>
<header class="site-header<?php echo esc_attr( $sticky_class ); ?>">
	<div class="wrap">
		<div class="site-branding">
			<?php 
			// FIX: Display the custom logo if one is set
			if ( has_custom_logo() ) {
				the_custom_logo();
			}
			?>

			<?php if ( get_header_textcolor() !== 'blank' ) : ?>
				<div class="site-identity">
					<?php if ( is_front_page() && is_home() ) : ?>
						<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php else : ?>
						<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
					<?php endif; ?>
	
					<?php
					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) :
						?>
						<p class="site-description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div><nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'cleanlite' ); ?>">
			<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
				<span class="menu-icon" aria-hidden="true">&#9776;</span>
				<span class="menu-text"><?php esc_html_e( 'Menu', 'cleanlite' ); ?></span>
			</button>

			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</header>

<main id="content" class="site-main">
	<div class="wrap">