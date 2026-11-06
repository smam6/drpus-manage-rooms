<?php

use DrPlus\Components\SectionTitle;
use DrPlus\Utils\Elementor as UtilsElementor;
use DrPlus\Utils\UtilsSpecialists;
use MJ\Whitebox\Utils;
use MJ\Whitebox\Utils\Elementor;

extract($args);

if( empty( $related_specialists ) ) return;

$section_title_settings = Utils::check_default( $section_title_settings, [
	'icon'				=> 'drplus-icon-stethoscope',
	'icon_has_bg'		=> true,
	'tag'				=> 'h3',
	'title'				=> esc_html__( 'Related Specialists', 'drplus' ),
	'subtitle'			=> '',
	'link'				=> '',
	'nav_btns'			=> true,
	'next_arrow_icon'	=> '',
	'prev_arrow_icon'	=> '',
	'classes'			=> ['specialists_section-title'],
], ['link', 'icon', 'next_arrow_icon', 'prev_arrow_icon'] );

$options = Utils::check_default( $options, [
	'single_specialist_related_specialists_name_tag'		=> 'h3',
	'single_specialist_related_specialists_short_bio_tag'	=> 'div',
	'single_specialist_related_specialists_verified_text'	=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),	
], ['section_icon'] );

$settings = Utils::check_default( $settings, [
	'style'					=> 'card-1',
	'desktop_slider'		=> true,
	'desktop_cols'			=> 5,
	'desktop_slides_type'	=> 'auto',
	'desktop_slides_space'	=> 16,
	'tablet_slider'			=> true,
	'tablet_cols'			=> 3,
	'tablet_slides_type'	=> 'auto',
	'tablet_slides_space'	=> 16,
	'mobile_slides_type'	=> 'auto',
	'mobile_slider'			=> true,
	'mobile_cols'			=> 1,
	'mobile_slides_space'	=> 16,

	'name-tag'				=> $options['single_specialist_related_specialists_name_tag'],
	'short_bio-tag'			=> $options['single_specialist_related_specialists_short_bio_tag'],
	'verified-text'			=> $options['single_specialist_related_specialists_verified_text'],
] );

$devices = ['desktop', 'tablet', 'mobile'];
$display_attributes = Elementor::get_display_attributes( $settings );
$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		$prefix . 'related-specialists-slider-wrap',
		$prefix . "related-specialists",
		$prefix . "related-specialists-wrap",
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<div class="drplus-slider-head <?php echo $prefix ?>related-specialists-slider-head">
		<?php
		if( !empty( $section_title_settings['title'] ) ) {
			SectionTitle::view( $section_title_settings );
		}
		if( Utils::to_bool( $settings['button_show_button'] ?? false ) ) {
			$button_args = UtilsElementor::get_button_args( $settings );
			$button_args['prefix'] = 'button_';
			$button_args['button_classes'][] = 'specialist-read-more-btn';
			get_template_part( "templates/components/template-components-button", null, $button_args );
		}				
		?>
	</div>
	<?php UtilsSpecialists::list( $settings, 'all', $related_specialists ) ?>
</div>