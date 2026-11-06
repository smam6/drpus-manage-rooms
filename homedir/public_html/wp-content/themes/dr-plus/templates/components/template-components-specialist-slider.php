<?php
use DrPlus\Utils;
use DrPlus\Utils\Elementor as UtilsElementor;
use MJ\Whitebox\Utils\Elementor;
use MJ\Whitebox\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$is_rtl = is_rtl();
$args = Utils::check_default( $args, [
	'items' 			=> [],
	'title'				=> '',
	'title_tag'			=> 'h2',
	'next_arrow_icon'	=> $is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
	'prev_arrow_icon'	=> !$is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
], ['prev_arrow_icon', 'next_arrow_icon'] );

$args['title_tag'] = Sanitizers::tag( $args['title_tag'] );

$devices = ['desktop', 'tablet', 'mobile'];
foreach( $devices as $device ) {
	$args["{$device}_slider"] = 1;
	$args["{$device}_slides"] = 1;
	$args["{$device}_slides_space"] = 24;
	$args["{$device}_slides_type"] = 'count';
}

$display_attributes = Elementor::get_display_attributes( $args );

$attributes = [
	'class'	=> array_merge( [
		'drplus-slider-wrap',
		'specialist-slider-wrap',
	], $display_attributes['wrap_classes'] ),
	'data-settings'	=> $display_attributes['args'],
	'style'			=> $display_attributes['style'],
];
$wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'specialist-slider',
	], $display_attributes['classes'] ),
];

if( $args['show_arrows'] ) {
	$attributes['class'][] = 'slider-has-arrows';
}

?>
<div <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php if( $args['show_arrows'] ) { ?>
		<div class="specialist-slider-arrows-wrap">
			<?php get_template_part( 'templates/components/template-components-slider_arrows', null, [
				'inline'	=> true,
				'next_icon'	=> $args['next_arrow_icon'],
				'prev_icon'	=> $args['prev_arrow_icon'],
			] ); ?>
		</div>
	<?php } ?>
	<?php if( !empty( $args['title'] ) ) { ?>
		<<?php echo tag_escape( $args['title_tag'] ) ?> class="specialist-slider-title">
			<?php echo esc_html( $args['title'] ) ?>
		</<?php echo tag_escape( $args['title_tag'] ) ?>>
	<?php } ?>
	<div <?php echo Utils::get_html_attributes( $wrapper_attributes ) ?>>
		<?php
		foreach( $args['items'] as $item ) {
			$info_tag = 'div';
			$has_link = !empty( $item['link'] ) && !empty( $item['link']['url'] );
			$link_attributes = Elementor::get_link_attributes( $item['link'] );
			if( $has_link ) {
				$info_tag = 'a';
			}
			?>
			<div class="specialist-slider-item swiper-slide slider-slide">
				<div class="specialist-slider-item-inner">
					<?php if( !empty( $item['img'] ) ) {
						if( !empty( $item['img']['id'] ) )	{
							echo wp_get_attachment_image( $item['img']['id'], 'full', false, ['class' => 'specialist-slider-item-avatar'] );
						} else {
							?> <img src="<?php echo $item['img']['url'] ?>" class="specialist-slider-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
						}
					} else {
						?> <img src="<?php echo DRPLUS_URI . 'assets/images/user.svg' ?>" class="specialist-slider-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
					} ?>
					<<?php echo $info_tag ?> class="specialist-slider-item-info" <?php echo Utils::get_html_attributes( $link_attributes ) ?>>
						<span class="specialist-slider-item-name"><?php echo esc_html( $item['name'] ) ?></span>
						<?php if( !empty( $item['subtitle'] ) ) { ?>
							<span class="specialist-slider-item-subtitle"><?php echo esc_html( $item['subtitle'] ) ?></span>
						<?php } ?>
					</<?php echo $info_tag ?>>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>