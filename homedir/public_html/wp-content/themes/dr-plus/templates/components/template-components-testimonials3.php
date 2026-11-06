<?php
use DrPlus\Utils;
use MJ\Whitebox\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items' 				=> [],
	'show_score_stars'		=> true,
	'show_score_number'		=> true,
] );

$display_attributes = Elementor::get_display_attributes( $args );

$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'drplus-testimonials3-wrap',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'drplus-testimonials3',
	], $display_attributes['classes'] ),
];

?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php
	if( !empty( $args['section_title_title'] ) ) {
		get_template_part( "templates/components/template-components-section_title", null, [
			'icon'			=> $args['section_title_icon'],
			'icon_has_bg'	=> $args['section_title_icon_has_bg'],
			'tag'			=> $args['section_title_tag'],
			'title'			=> $args['section_title_title'],
			'subtitle'		=> $args['section_title_subtitle'],
			'link'			=> $args['section_title_link'],
			'nav_btns'		=> $args['show_arrows'],
			'classes'		=> ['testimonials3-section-title'],
		] );
	}
	?>
	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			?>
			<div class="testimonials3-item swiper-slide slider-slide">
				<div class="testimonials3-item-head">
					<?php if( !empty( $item['img'] ) ) {
						if( !empty( $item['img']['id'] ) )	{
							echo wp_get_attachment_image( $item['img']['id'], [48, 48], false, ['class' => 'testimonials3-item-avatar'] );
						} else {
							?> <img src="<?php echo $item['img']['url'] ?>" class="testimonials3-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
						}
					} else {
						?> <img src="<?php echo DRPLUS_URI . 'assets/images/user.svg' ?>" class="testimonials3-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
					} ?>
					<div class="testimonials3-item-info">
						<span class="testimonials3-item-name"><?php echo esc_html( $item['name'] ) ?></span>
						<?php if( !empty( $item['subtitle'] ) ) { ?>
							<span class="testimonials3-item-subtitle"><?php echo esc_html( $item['subtitle'] ) ?></span>
						<?php } ?>
						<?php if( $args['show_score_stars'] || $args['show_score_number'] ) { ?>
							<div class="testimonials3-item-score-wrap">
								<?php if( $args['show_score_stars'] ) { ?>
									<?php $score_count = min( ceil( $item['score'] ), 5 ); ?>
									<div class="testimonials3-item-score-stars">
										<?php
										for ($i=1; $i <= 5 - $score_count ; $i++) { 
											echo '<i class="drplus-icon-star testimonials3-item-score-icon"></i>';
										}
										for ($i=1; $i <= $score_count ; $i++) { 
											echo '<i class="drplus-icon-star-fill testimonials3-item-score-icon"></i>';
										}
										?>
									</div>
								<?php } ?>
								<?php if( $args['show_score_number'] ) { ?>
									<span class="testimonials3-item-score-number"><?php printf( '(%s)', esc_html( $item['score'] ) ) ?></span>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="testimonials3-item-content">
					<?php echo wpautop( $item['text'] ) ?>
					<i class="drplus-icon-qoute-1 testimonials3-item-quote-icon"></i>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>