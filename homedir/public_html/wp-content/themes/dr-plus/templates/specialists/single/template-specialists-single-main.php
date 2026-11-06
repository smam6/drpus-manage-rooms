<?php

use DrPlus\Utils;

extract($args);
?>
<div class="<?php echo $prefix ?>main_section">
	<?php
	add_action( 'drplus/specialist/single/main_start', $specialist );
	get_template_part( 'templates/specialists/single/template-specialists-single-stats', null, [
		'prefix'		=> $prefix,
		'specialist'	=> $specialist,
		'options'		=> $options,
		'stats'			=> $stats,
	] );
	add_action( 'drplus/specialist/single/after_stats', $specialist );
	if( Utils::to_bool( $options['single_specialist_show_introduction'] ) && !empty( $specialist->about ) ) {
		add_action( 'drplus/specialist/single/before_introduction', $specialist );
		get_template_part( 'templates/specialists/single/template-specialists-single-introduction', null, [
			'prefix'		=> $prefix,
			'specialist'	=> $specialist,
			'options'		=> $options,
		] );
		add_action( 'drplus/specialist/single/after_introduction', $specialist );
	}
	if( Utils::to_bool( $options['single_specialist_show_certificates'] ) && !empty( $specialist->meta['certificates'] ) ) {
		add_action( 'drplus/specialist/single/before_certificates', $specialist );
		get_template_part( 'templates/specialists/single/template-specialists-single-certificates', null, [
			'prefix'		=> $prefix,
			'specialist'	=> $specialist,
			'options'		=> $options,
			'display_settings'		=> [
				'desktop_cols'	=> 2,
				'desktop_gap'	=> 16,
				'tablet_cols'	=> 1,
				'tablet_gap'	=> 16,
				'mobile_cols'	=> 1,
				'mobile_gap'	=> 16,
			]
		] );
		add_action( 'drplus/specialist/single/after_certificates', $specialist );
	}
	if( Utils::to_bool( $options['single_specialist_show_faqs'] ) && !empty( $faqs ) ) {
		add_action( 'drplus/specialist/single/before_faqs', $specialist );
		get_template_part( 'templates/specialists/single/template-specialists-single-faqs', null, [
			'prefix'		=> $prefix,
			'specialist'	=> $specialist,
			'options'		=> $options,
			'faqs'			=> $faqs
		] );
		add_action( 'drplus/specialist/single/after_faqs', $specialist );
	}
	add_action( 'drplus/specialist/single/before_reviews', $specialist );
	get_template_part( 'templates/specialists/single/template-specialists-single-reviews', null, [
		'prefix'		=> $prefix,
		'specialist'	=> $specialist,
		'options'		=> $options,
	] );
	add_action( 'drplus/specialist/single/after_reviews', $specialist );
	?>
</div>