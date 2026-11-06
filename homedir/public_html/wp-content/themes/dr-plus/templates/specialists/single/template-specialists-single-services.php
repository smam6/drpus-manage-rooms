<?php
use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;

extract($args);

$options = Utils::check_default( $options, [
	'single_specialist_sections_tag'	=> 'h3',

	'section_title'				=> esc_html__( 'Specialized services of %s', 'drplus' ),
	'section_icon'				=> 'drplus-icon-mental-health',
	'service_icon'				=> 'drplus-icon-diamond',
	'show_more_btn'				=> true,
	'show_more_btn_min_height'	=> 350,
], ['section_icon', 'service_icon'] );

$section_args = [
	'class'			=> [
		$prefix . 'section',
		$prefix . 'services',
	],
	'role'			=> 'region',
	'aria-label'	=> sprintf( esc_html__( 'Specialized services of %s', 'drplus' ), $specialist->display_name )
];
if( $options['show_more_btn'] ) {
	$section_args['class'][] = $prefix . 'show-more-container';
	$section_args['style'] = "height:{$options['show_more_btn_min_height']}px";
}

$display_attrs = Elementor::get_display_attributes( $args['display_settings'] );
$display_attrs['classes'][] = $prefix . 'services-list';

?>
<section <?php echo Utils::get_html_attributes( $section_args ) ?>>
	<?php SectionTitle::view( [
		'icon'		=> $options['section_icon'],
		'tag'		=> $options['single_specialist_sections_tag'],
		'title'		=> sprintf( $options['section_title'], $specialist->display_name ),
		'classes'	=> [$prefix . "side-section-title"]
	] ); ?>
	<div <?php echo Utils::get_html_attributes( $display_attrs ) ?>>
		<?php foreach( $specialist->meta['services'] as $service ) { ?>
			<div class="<?php echo $prefix ?>service">
				<?php
				if( !empty( $options['section_title'] ) ) {
					SectionTitle::view( [
						'icon'			=> $options['service_icon'],
						'icon_has_bg'	=> false,
						'tag'			=> 'span',
						'title'			=> esc_html( $service['title'] ),
						'classes'		=> [$prefix . "service-title"]
					] );
				}
				?>
				<?php if( !empty( $service['desc'] ) ) { ?>
					<span class="<?php echo $prefix ?>service-desc"><?php echo esc_html( $service['desc'] ) ?></span>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
	<?php if( $options['show_more_btn'] ) { ?>
		<div class="<?php echo $prefix ?>services-show-more-wrap <?php echo $prefix ?>show-more-wrap" data-height="<?php echo $options['show_more_btn_min_height'] ?>">
			<span><?php echo esc_html__( 'Show more', 'drplus' ) ?></span>
			<i class="drplus-icon-bottom"></i>
		</div>
	<?php } ?>
</section>