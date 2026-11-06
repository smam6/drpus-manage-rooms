<?php
use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items' 				=> [],
	'loop'					=> true,
	'autoplay'				=> 10,
	'desktop_slides'		=> 'auto',
	'desktop_slides_space'	=> 12,
	'tablet_slides'			=> 'auto',
	'tablet_slides_space'	=> 12,
	'mobile_slides'			=> 'auto',
	'mobile_slides_space'	=> 12,
] );

$thumbnail_attributes = [
	'class'	=> [
		"drplus-thumbnail-slider",
		"drplus-slider-wrap",
		"swiper",
		"testimonials2-slider-thumbs",
	],
	'data-settings'	=> [
		'slider'	=> [
			'grabCursor'			=> true,
			'loop'					=> $args['loop'],
			'slidesPerView'			=> $args['desktop_slides'],
			'spaceBetween'			=> $args['desktop_slides_space'],
			'watchSlidesProgress'	=> true,
			'centeredSlides'		=> true,
			'autoplay'				=> [
				'delay'	=> absint( $args['autoplay'] ),
			],
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'slidesPerView'	=> $args['tablet_slides'],
				'spaceBetween'	=> $args['tablet_slides_space'],
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'slidesPerView'	=> $args['mobile_slides'],
				'spaceBetween'	=> $args['mobile_slides_space'],
			],
		],
	],
];

$main_attributes = [
	'class'	=> [
		"drplus-main-slider",
		"drplus-slider-wrap",
		"swiper",
		"testimonials2-slider-wrap",
	],
	'data-settings'	=> [
		'slider'	=> [
			'loop'				=> true,
			'spaceBetween'		=> 16,
			'slidesPerView'		=> 1,
			'centeredSlides'	=> true,
			'autoplay'		=> [
				'delay'	=> absint( $args['autoplay'] ),
			],
			'thumbs'		=> [],
		],
		'desktop'	=> [
			'slider'	=> [
				'enabled'	=> true,
			],
		],
		'tablet'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'slidesPerView'	=> 1,
			],
		],
		'mobile'	=> [
			'slider'	=> [
				'enabled'		=> true,
				'slidesPerView'	=> 1,
			],
		],
	],
];

$wrap_attributes = [
	'class'	=> [
		'drplus-thumbnail-slider-wrap',
		'testimonials2-wrap'
	],
];

?>
<div <?php echo Utils::get_html_attributes( $wrap_attributes ) ?>>
	<div <?php echo Utils::get_html_attributes( $thumbnail_attributes ) ?>>
		<div class="swiper-wrapper wrapper">
			<?php foreach( $args['items'] as $item ) { ?>
				<div class="testimonials2-slider-thumb swiper-slide slider-slide">
					<div class="testimonials2-item-avatar-wrap">
						<?php if( !empty( $item['img'] ) ) {
							if( !empty( $item['img']['id'] ) )	{
								echo wp_get_attachment_image( $item['img']['id'], [48, 48], false, ['class' => 'testimonials2-item-avatar'] );
							} else {
								?> <img src="<?php echo $item['img']['url'] ?>" class="testimonials2-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
							}
						} else {
							?> <img src="<?php echo DRPLUS_URI . 'assets/images/user.svg' ?>" class="testimonials2-item-avatar" alt="<?php printf( __( '%s picture', 'drplus' ), Utils::convert_chars( $item['name'] ) ) ?>"> <?php
						} ?>
					</div>
					<div class="testimonials2-item-info">
						<span class="testimonials2-item-name"><?php echo esc_html( $item['name'] ) ?></span>
						<span class="testimonials2-item-position"><?php echo esc_html( $item['position'] ) ?></span>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
	<div <?php echo Utils::get_html_attributes( $main_attributes ) ?>>
		<div class="swiper-wrapper wrapper">
			<?php foreach( $args['items'] as $item ) { ?>
				<div class="testimonials2-slider-main swiper-slide slider-slide">
					<i class="drplus-icon-qoute testimonials2-quote-icon"></i>
					<div class="testimonials2-item-content">
						<?php echo wpautop( $item['text'] ) ?>
					</div>
					<i class="drplus-icon-qoute-1 testimonials2-quote-icon"></i>
				</div>
			<?php } ?>
		</div>
	</div>
</div>