<?php

use DrPlus\Components\SectionTitle;
use DrPlus\Utils\Options;
use MJ\Whitebox\Utils;

extract($args);

$options = Utils::check_default( $options, [
	'single_specialist_sections_tag'	=> 'h3',

	'section_title'									=> esc_html__( 'Introduction and Speciality', 'drplus' ),
	'section_icon'									=> 'drplus-icon-stethoscope',
	'single_specialist_limit_introduction_height'	=> false,
	'single_specialist_introduction_limited_height'	=> [
		'height'	=> '300'
	]
], ['section_icon'] );

$section_args = [
	'class'			=> [
		$prefix . 'section',
		$prefix . 'introduction',
	],
	'role'			=> 'region',
	'aria-label'	=> esc_html__( 'Introduction and Speciality', 'drplus' ),
	'itemprop'		=> 'description',
];
if( $options['single_specialist_limit_introduction_height'] ) {
	$section_args['class'][] = $prefix . 'show-more-container';
	$height = intval( $options['single_specialist_introduction_limited_height']['height'] );
	$section_args['style'] = "height:{$height}px";
}
?>
<section <?php echo Utils::get_html_attributes( $section_args ) ?>>
	<?php
	if( !empty( $options['section_title'] ) ) {
		SectionTitle::view( [
			'icon'		=> $options['section_icon'],
			'tag'		=> $options['single_specialist_sections_tag'],
			'title'		=> sprintf( $options['section_title'], $specialist->display_name ),
			'classes'	=> [$prefix . "section-title"]
		] );
	}
	?>
	<div class="<?php echo $prefix ?>about-wrap">
		<?php echo wpautop( Utils::parse_text_editor( $specialist->about ), true ) ?>
	</div>
	<?php if( $options['single_specialist_limit_introduction_height'] ) { ?>
		<div class="<?php echo $prefix ?>introduction-show-more-wrap <?php echo $prefix ?>show-more-wrap" data-height="<?php echo $options['single_specialist_introduction_limited_height']['height'] ?>">
			<span><?php echo esc_html__( 'Show more', 'drplus' ) ?></span>
			<i class="drplus-icon-bottom"></i>
		</div>
	<?php } ?>
</section>