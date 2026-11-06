<?php
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'					=> [],
	'visit_text'			=> esc_html__( 'Visit', 'drplus' ),
	'score_icon'			=> [],
], ['score_icon'] );

$display_attributes = Elementor::get_display_attributes( $args, true );
$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'testimonials1-slider-wrap',
		'swiper',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];

$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'swiper-wrapper',
		'testimonials1-wrapper',
	], $display_attributes['classes']),
];

?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php
	if( !empty( $args['section_title_title'] ) ) {
		get_template_part( "templates/components/template-components-section_title", null, [
			'icon'		=> $args['section_title_icon'],
			'tag'		=> $args['section_title_tag'],
			'title'		=> $args['section_title_title'],
			'subtitle'	=> $args['section_title_subtitle'],
			'link'		=> $args['section_title_link'],
			'nav_btns'	=> true,
			'classes'	=> ['testimonials1-section-title'],
		] );
	}
	?>

	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php foreach( $args['items'] as $item ) { ?>
			<div class="slider-item swiper-slide testimonials1-item">
				<div class="testimonials1-item-header">
					<div class="testimonials1-item-avatar-wrap">
						<?php if( !empty( $item['img'] ) ) {
							if( !empty( $item['img']['id'] ) )	{
								echo wp_get_attachment_image( $item['img']['id'], [48, 48], false, ['class' => 'testimonials1-item-avatar'] );
							} else {
								?> <img src="<?php echo $item['img']['url'] ?>" class="testimonials1-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
							}
						} else {
							?> <img src="<?php echo DRPLUS_URI . 'assets/images/user.svg' ?>" class="testimonials1-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
						} ?>
					</div>
					<div class="testimonials1-item-name-wrap">
						<span class="testimonials1-item-name"><?php echo esc_html( $item['name'] ) ?></span>
						<span class="testimonials1-item-date"><?php echo esc_html( $item['date'] ) ?></span>
					</div>
					<div class="testimonials1-item-score-wrap">
						<?php
						if( !empty( $args['score_icon'] ) ) {
							echo Sanitizers::icon( $args['score_icon'], 'testimonials1-item-score-icon' );
						} else {
							?>
							<i class="drplus-icon-star-fill testimonials1-item-score-icon"></i>
							<?php
						}
						?>
						<span class="testimonials1-item-score"><?php echo esc_html( $item['score'] ) ?></span>
					</div>
				</div>
				<div class="testimonials1-item-content">
					<?php echo wpautop( $item['text'] ) ?>
				</div>
				<div class="testimonials1-item-footer">
					<span class="testimonials1-item-visit-text"><?php echo esc_html( $args['visit_text'] ) ?></span>
					<?php if( !empty( $item['specialist_link'] ) && !empty( $item['specialist_link']['url'] ) ) { ?>
						<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $item['specialist_link'] ) ) ?> class="testimonials1-item-specialist">
					<?php } else { ?>
						<span class="testimonials1-item-specialist">
					<?php } ?>
						<?php echo esc_html( $item['specialist'] ) ?>
					<?php if( !empty( $item['specialist_link'] ) && !empty( $item['specialist_link']['url'] ) ) { ?>
						</a>
					<?php } else { ?>
						</span>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>