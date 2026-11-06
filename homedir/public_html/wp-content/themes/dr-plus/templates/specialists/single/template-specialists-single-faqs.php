<?php

use DrPlus\Components\SectionTitle;
use MJ\Whitebox\Utils;

extract($args);

$options = Utils::check_default( $options, [
	'single_specialist_sections_tag'	=> 'h3',

	'section_title'			=> esc_html__( 'Frequently Asked Questions (FAQs)', 'drplus' ),
	'section_icon'			=> 'drplus-icon-faq',
	'desktop_two_cols'		=> true,
	'style'					=> 'style-1',
], ['section_icon', 'bg_icon'] );

?>									
<section class="<?php echo $prefix ?>section <?php echo $prefix ?>faqs" role="region" aria-label="<?php echo esc_html__( 'Frequently Asked Questions (FAQs)', 'drplus' ) ?>">
	<?php
	if( !empty( $options['section_title'] ) ) {
		SectionTitle::view( [
			'icon'		=> $options['section_icon'],
			'tag'		=> $options['single_specialist_sections_tag'],
			'title'		=> sprintf( $options['section_title'], $specialist->display_name ),
			'classes'	=> [$prefix . "side-section-title"]
		] );
	}
	?>
	<div class="<?php echo $prefix ?>faqs-list">
		<?php get_template_part( "templates/components/template-components-accordion", null, [
			'items'			=> $options['desktop_two_cols'] ? array_slice( $faqs, 0, round( count( $faqs ) / 2 ) ) : $faqs,
			'title_tag'		=> 'span',
			'faq_schema'	=> false,
			'item_style'	=> $options['style']
		] ); ?>
		<?php
		if( $options['desktop_two_cols'] ) {
			get_template_part( "templates/components/template-components-accordion", null, [
				'items'			=> array_slice( $faqs, round( count( $faqs ) / 2 ) ),
				'title_tag'		=> 'span',
				'faq_schema'	=> false,
				'item_style'	=> $options['style']
			] ); 
		}
		?>
	</div>
</section>