<?php

use DrPlus\CategoryWalker;
use DrPlus\Utils;

if( !function_exists( 'drplus_register_sidebars' ) ) {
	function drplus_register_sidebars() {
		register_sidebar( [
			'id'			=> 'general',
			'name'			=> __( 'General sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'page',
			'name'			=> __( 'Page sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'blog',
			'name'			=> __( 'Blog sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'single',
			'name'			=> __( 'Single sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'sidebar-shop',
			'name'			=> __( 'Shop', 'woocommerce' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'archive_hospital',
			'name'			=> __( 'Hospital archive sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'single_hospital',
			'name'			=> __( 'Single hospital sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'archive_specialist',
			'name'			=> __( 'Specialist archive sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'archive_specialities',
			'name'			=> __( 'Specialities archive sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'single_speciality',
			'name'			=> __( 'Single speciality sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );

		register_sidebar( [
			'id'			=> 'search',
			'name'			=> __( 'Search page sidebar', 'drplus' ),
			'before_widget'	=> '<div id="%1$s" class="widget %2$s">',
			'after_widget'	=> '</div>',
			'before_title'	=> '<h3 class="widgettitle">',
			'after_title'	=> '</h3>', 
		] );
	}
}
add_action( 'widgets_init', 'drplus_register_sidebars' );

if( !function_exists( 'drplus_register_widgets' ) ) {
	function drplus_register_widgets() {
		$widgets = [
			'recent-posts'			=> ['class' => 'RecentPosts'],
			'socials'				=> ['class' => 'Socials'],
			'search-sections'		=> ['class' => 'SearchSections'],
			'hospital-categories'	=> ['class' => 'HospitalCategories'],
			'hospital-city-filter'	=> ['class' => 'HospitalCityFilter'],
			'hospital-phones'		=> ['class' => 'HospitalPhones'],
			'hospital-emails'		=> ['class' => 'HospitalEmails'],
			'hospital-socials'		=> ['class' => 'HospitalSocials'],
			'specialists'			=> ['class' => 'Specialists'],
			'specialities'			=> ['class' => 'Specialities'],
			'search-specialists'	=> ['class' => 'SearchSpecialists'],
			'specialists-type'		=> ['class' => 'SpecialistsType'],
		];
		if( class_exists( "DrPlus\Utils" ) ) {
			foreach( $widgets as $filename => $widget ) {
				if( !empty( $widget['requirements'] ) && !Utils::should_include_module( $widget['requirements'] ) ) continue;
				$class = "\DrPlus\Widgets\\" . $widget['class'];

				if( file_exists( DRPLUS_DIR . "inc/Widgets/widgets-{$filename}.php" ) && !class_exists( $class ) ) {
					include( DRPLUS_DIR . "inc/Widgets/widgets-{$filename}.php" );
					register_widget( $class );
				}
			}
		}
	}
}
add_action( 'widgets_init', 'drplus_register_widgets' );

if( !function_exists( "drplus_widget_categories_args" ) ) {
	function drplus_widget_categories_args( $cat_args ) {
		include_once( DRPLUS_DIR . "inc/Classes/CategoryWalker.php" );
		$cat_args['walker'] = new CategoryWalker;
		return $cat_args;
	}
}
add_filter( 'widget_categories_args', 'drplus_widget_categories_args' );