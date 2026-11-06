<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;

$home = home_url();
$default_options = [
	'show_footer' => true,

	'footer_show_back_to_top'	=> true,
	'footer_back_to_top_icon'	=> 'drplus-icon-arrow-up',

	'footer_show_about_us'	=> true,
	'footer_about_title'	=> __( 'About Us', 'drplus' ),
	'footer_about_text'		=> __( 'With the digital healthcare services of DoctorPlus, enjoy a fast and convenient experience with medical services. Online appointment scheduling, phone consultations, and access to clinics and medical centers are among our services. For an even better experience on mobile, you can download and install our app for Android phones.', 'drplus' ),

	'footer_show_menu'	=> true,
	'footer_menu_title'	=> __( 'Useful Links', 'drplus' ),

	'footer_show_contact_info'			=> true,
	'footer_contact_info_title'			=> __( 'Contact Methods', 'drplus' ),
	'footer_contact_info_text'			=> __( 'Service is available seven days a week from 9:00 AM to 12:00 PM.', 'drplus' ),
	'footer_contact_info'				=> [
		'footer_contact_icons'	=> [
			'drplus-icon-calling',
			'drplus-icon-mail',
			'drplus-icon-discovery',
		],
		'footer_contact_items'	=> [
			'021-258 14 56',
			'drplus@drplus.com',
			__( "Tehran", 'drplus' ),
		],
		'footer_contact_types'	=> [
			'phone',
			'email',
			'address',
		],
		'footer_contact_links'	=> [
			'',
			'',
			'#',
		],
	],

	'footer_show_org_logos'		=> true,
	'footer_orgs_logo_items'	=> [
		'org_logos'				=> [
			'<img src="https://amin-noorani.ir/dr-plus/wp-content/uploads/2025/03/Screenshot-1401-09-20-at-11.46.png" alt="">',
			'<img src="https://amin-noorani.ir/dr-plus/wp-content/uploads/2025/03/Screenshot-1401-09-20-at-11.47.png" alt="">',
			'<img src="https://amin-noorani.ir/dr-plus/wp-content/uploads/2025/03/Screenshot-1401-09-21-at-14.31.png" alt="">',
		]
	],

	'footer_show_social_info'	=> true,
	'footer_social_title'		=> __( 'Together with the DoctorPlus platform...', 'drplus' ),
	'footer_social_info'		=> [
		'footer_social_items'	=> [
			'#',
			'#',
			'#',
			'#',
		],
		'footer_social_icons'	=> [
			'drplus-icon-telegram',
			'drplus-icon-whatsapp',
			'drplus-icon-instagram',
			'drplus-icon-facebook',
		]
	],
	'footer_social_show_bottom_logo'	=> true,

	'footer_copyright'	=> __( 'All rights to this website are reserved by DoctorPlus.', 'drplus' ),
];
$options = Options::get_options( $default_options );

$disable_footer = !Utils::to_bool( $options['show_footer'] );

if( is_page() ) {
	$page_options = Page::get_options();
	if( $page_options['disable_footer'] === true ) {
		if(
			$page_options['disable_footer_user'] === 'all' ||
			( !$logged_in && $page_options['disable_footer_user'] === 'guests' ) ||
			( $logged_in && $page_options['disable_footer_user'] === 'users' )
		) {
			$disable_footer = true;
		}
	}
}
?>
		<?php if( $disable_footer === false ) { ?>
			<?php if( !function_exists( 'elementor_theme_do_location' ) || !elementor_theme_do_location( 'footer' ) ) { ?>
				<footer id="site-footer" class="site-footer">
					<div class="page-width" id="footer">
						<?php if( Utils::to_bool( $options['footer_show_back_to_top'] ) ) { ?>
							<div id="back_to_top">
								<a href="#" id="back_to_top_inner">
									<i class="<?php echo $options['footer_back_to_top_icon'] ?>"></i>
								</a>
							</div>
						<?php } ?>
						<div id="footer_content">
							<?php if( Utils::to_bool( $options['footer_show_about_us'] ) ) { ?>
								<?php echo get_template_part( 'templates/footer/footer-about-us', null, $options ) ?>
							<?php } ?>

							<?php if( Utils::to_bool( $options['footer_show_menu'] ) || ( Utils::to_bool( $options['footer_show_contact_info'] ) && !empty( $options['footer_contact_info']['footer_contact_items'] ) ) ) { ?>
								<?php echo get_template_part( 'templates/footer/footer-info', null, $options ) ?>
							<?php } ?>

							<?php if(
								( Utils::to_bool( $options['footer_show_org_logos'] ) && !empty( $options['footer_orgs_logo_items'] ) && !empty( $options['footer_orgs_logo_items']['org_logos'] ) && !empty( $options['footer_orgs_logo_items']['org_logos'][0] ) ) ||
								( Utils::to_bool( $options['footer_show_social_info'] ) && !empty( $options['footer_social_info']['footer_social_items'] ) ) ) { ?>
								<?php echo get_template_part( 'templates/footer/footer-social', null, $options ) ?>
							<?php } ?>
						</div>

						<?php if( !empty( $options['footer_copyright'] ) ) { ?>
							<div id="footer-copyright-wrap">
								<?php get_template_part( "templates/footer/footer-copyright", null, $options ) ?>
							</div>
						<?php } ?>
					</div>
				</footer>
			<?php } ?>
		<?php } ?>
		<?php wp_footer(); ?>
		</div>
	</body>
</html>