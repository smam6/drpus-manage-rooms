<?php
use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;

extract($args);

$options = Utils::check_default( $options, [
	'single_specialist_sections_tag'	=> 'h3',

	'section_title'				=> esc_html__( 'Covered insurances', 'drplus' ),
	'section_icon'				=> 'drplus-icon-mental-health',
], ['section_icon'] );

$display_attrs = Elementor::get_display_attributes( $args['display_settings'] );
$display_attrs['classes'][] = $prefix . 'insurances-list';

?>
<section class="<?php echo $prefix ?>section <?php echo $prefix ?>insurances" role="complementary" aria-label="<?php echo esc_html__( 'Covered insurances', 'drplus' ) ?>">
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
	<div <?php echo Utils::get_html_attributes( $display_attrs ) ?>>
		<?php
		foreach( $insurances as $insurance ) {
			?>
			<div class="<?php echo $prefix ?>insurance">
				<?php if( !empty( $insurance['icon'] ) ) { ?>
					<i class="<?php echo esc_attr( $insurance['icon'] ) ?> <?php echo $prefix ?>insurance-icon"></i>
				<?php } ?>
				<span class="<?php echo $prefix ?>insurance-name"><?php echo esc_html( $insurance['name'] ) ?></span>
			</div>
		<?php } ?>
	</div>
</section>