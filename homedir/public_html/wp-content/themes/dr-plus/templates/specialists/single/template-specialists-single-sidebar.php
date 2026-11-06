<?php

use DrPlus\Utils;

extract($args);
?>
<div class="<?php echo $prefix ?>sidebar">
	<?php
	add_action( 'drplus/specialist/single/sidebar_start', $specialist );
	get_template_part( 'templates/specialists/single/template-specialists-single-bio', null, [
		'prefix'			=> $prefix,
		'specialist'		=> $specialist,
		'options'			=> $options,
		'avatar_url'		=> $avatar_url,
		'comments_count'	=> $comments_count
	] );
	add_action( 'drplus/specialist/single/after_bio', $specialist );
	if( Utils::to_bool( $options['single_specialist_show_offices'] ) && !empty( $specialist_offices ) ) {
		add_action( 'drplus/specialist/single/before_offices', $specialist );
		get_template_part( 'templates/specialists/single/template-specialists-single-offices', null, [
			'prefix'				=> $prefix,
			'specialist'			=> $specialist,
			'options'				=> $options,
			'specialist_offices'	=> $specialist_offices,
		] );
		add_action( 'drplus/specialist/single/after_offices', $specialist );
	}
	if( $options['single_specialist_show_services'] && !empty( $specialist->meta['services'] ) ) {
		add_action( 'drplus/specialist/single/before_services', $specialist );
		get_template_part( 'templates/specialists/single/template-specialists-single-services', null, [
			'prefix'				=> $prefix,
			'specialist'			=> $specialist,
			'options'				=> $options,
			'display_settings'		=> [
				'desktop_cols'	=> 1,
				'desktop_gap'	=> 16,
				'tablet_cols'	=> 1,
				'tablet_gap'	=> 16,
				'mobile_cols'	=> 1,
				'mobile_gap'	=> 16,
			]
		] );
		add_action( 'drplus/specialist/single/after_services', $specialist );
	}
	if( $options['insurance'] && !empty( $insurances ) ) {
		add_action( 'drplus/specialist/single/before_insurances', $specialist );
		get_template_part( 'templates/specialists/single/template-specialists-single-insurances', null, [
			'prefix'				=> $prefix,
			'specialist'			=> $specialist,
			'options'				=> $options,
			'insurances'			=> $insurances,
			'display_settings'		=> [
				'desktop_cols'	=> 2,
				'desktop_gap'	=> 8,
				'tablet_cols'	=> 2,
				'tablet_gap'	=> 8,
				'mobile_cols'	=> 2,
				'mobile_gap'	=> 8,
			]
		] );
		add_action( 'drplus/specialist/single/after_insurances', $specialist );
	}
	add_action( 'drplus/specialist/single/sidebar_end', $specialist );
	?>
</div>