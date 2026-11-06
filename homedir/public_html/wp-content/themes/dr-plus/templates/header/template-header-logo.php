<?php
use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$default_options = [
	'show-logo'					=> true,
	'logo-link'					=> home_url(),
	'logo-type'					=> 'img',
	'logo-img'					=> DRPLUS_URI . "assets/images/logo.svg",
	'homepage-site-title-tag'	=> 'h1',
	'otherpage-site-title-tag'	=> 'div',
];
$options = Options::get_options( $default_options );
if( !Utils::to_bool( $options['show-logo'] ) ) return;

$site_title_tag = $options['otherpage-site-title-tag'];
if( is_front_page() || is_home() ) {
	$site_title_tag = $options['homepage-site-title-tag'];
}
?>
<<?php echo tag_escape( $site_title_tag ) ?> class="site-title">
	<?php if( !empty( $options['logo-link'] ) ) { ?>
		<a href="<?php echo esc_url( $options['logo-link'] ) ?>" class="site-title-inner" title="<?php echo esc_attr( get_bloginfo( 'name' ) ) ?>">
	<?php } else { ?>
		<div class="site-title-inner">
	<?php } ?>
			<span class="site-logo">
				<?php echo Options::get_logo( [
					'type'			=> 'logo-type',
					'text-type'		=> 'logo-text-type',
					'text-custom'	=> 'logo-text-custom',
					'img'			=> 'logo-img',
					'img-size'		=> 'logo-img-size',
				], $options ) ?>
			</span>
	<?php if( !empty( $options['logo-link'] ) ) { ?>
		</a>
	<?php } else { ?>
		</div>
	<?php } ?>
</<?php echo tag_escape( $site_title_tag ) ?>>