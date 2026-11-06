<?php

use DrPlus\Utils\Options;

$default_options = [
	'footer_social_bottom_logo_link'	=> home_url(),
	'footer_social_bottom_logo_type'	=> 'img',
	'footer_social_bottom_logo'			=> DRPLUS_URI . "assets/images/footer-logo.svg",
	'footer_social_bottom_logo_size'	=> [
		'width'		=> 140,
		'height'	=> 32,
	],
];
$options = Options::get_options( $default_options );
?>
<div class="site-title">
	<?php if( !empty( $options['footer_social_bottom_logo_link'] ) ) { ?>
		<a href="<?php echo esc_url( $options['footer_social_bottom_logo_link'] ) ?>" class="site-title-inner">
	<?php } else { ?>
		<div class="site-title-inner">
	<?php } ?>
			<span id="site-logo">
				<?php echo Options::get_logo( [
					'type'			=> 'footer_social_bottom_logo_type',
					'text-type'		=> 'footer_social_bottom_logo_text_type',
					'text-custom'	=> 'footer_social_bottom_logo_text_custom',
					'img'			=> 'footer_social_bottom_logo',
					'img-size'		=> 'footer_social_bottom_logo_size',
				], $default_options ) ?>
			</span>
	<?php if( !empty( $options['footer_social_bottom_logo_link'] ) ) { ?>
		</a>
	<?php } else { ?>
		</div>
	<?php } ?>
</div>