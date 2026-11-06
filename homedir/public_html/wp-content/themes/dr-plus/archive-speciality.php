<?php

use DrPlus\Components\ProIcon;
use DrPlus\Model\SpecialistSpecialitiesRel;
use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Speciality;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'archive_specialities_breadcrumb'				=> true,
	'archive_specialities_show_title'				=> true,
	'archive_specialities_title_icon'				=> 'drplus-icon-stethoscope',
	'archive_specialities_show_sidebar'				=> true,
	'archive_specialities_sidebar'					=> 'archive_specialities',
	'archive_specialities_desktop_cols'				=> 4,
	'archive_specialities_desktop_gap'				=> 24,
	'archive_specialities_tablet_cols'				=> 3,
	'archive_specialities_tablet_gap'				=> 16,
	'archive_specialities_mobile_cols'				=> 2,
	'archive_specialities_mobile_gap'				=> 16,
	'archive_specialities_title_tag'				=> 'h2',
	'archive_specialities_show_speciality_icon'		=> true,
	'archive_specialities_show_specialists_count'	=> true,
	'archive_specialities_show_specialists_arrow'	=> true,
] );

$has_sidebar = Utils::to_bool( $options['archive_specialities_show_sidebar'] ) && is_active_sidebar( $options['archive_specialities_sidebar'] );
get_header();
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
	?>
	<div id="page-body" class="page-width">
		<main id="page-main">
			<?php get_template_part( "templates/page/template-page-head", null, [
				'options'	=> [
					'show_breadcrumb'	=> Utils::to_bool( $options['archive_specialities_breadcrumb'] ),
					'show_title'		=> Utils::to_bool( $options['archive_specialities_show_title'] ),
					'use_content_style'	=> false,
					'page_icon'			=> $options['archive_specialities_title_icon'],
				],
				'is_archive'		=> true,
			] ); ?>

			<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
				<?php
				if( $has_sidebar ) {
					get_sidebar( $options['archive_specialities_sidebar'] );
				}

				$posts_args = [
					'class'	=> [
						'site-content', 'specialities', 'list-specialities' ,'desktop-columns', 'tablet-columns', 'mobile-columns',
						"desktop-columns-{$options['archive_specialities_desktop_cols']}",
						"tablet-columns-{$options['archive_specialities_tablet_cols']}",
						"mobile-columns-{$options['archive_specialities_mobile_cols']}",
					],
					'role'		=> 'main',
					'style'		=> [
						'--desktop-cols'	=> $options['archive_specialities_desktop_cols'],
						'--tablet-cols'		=> $options['archive_specialities_tablet_cols'],
						'--mobile-cols'		=> $options['archive_specialities_mobile_cols'],
						'--desktop-gap'		=> $options['archive_specialities_desktop_gap'] . "px",
						'--tablet-gap'		=> $options['archive_specialities_tablet_gap'] . "px",
						'--mobile-gap'		=> $options['archive_specialities_mobile_gap'] . "px",
					]
				];
				if( $has_sidebar ) {
					$posts_args['class'][] = 'col-md-9';
					$posts_args['class'][] = 'col-sm-12';
				} else {
					$posts_args['class'][] = 'col-12';
				}
				?>

				<div <?php echo Utils::get_html_attributes( $posts_args ) ?>>
					<?php
					if( have_posts() ) {
						while( have_posts() ) {
							the_post();

							if( Utils::to_bool( $options['archive_specialities_show_speciality_icon'] ) ) {
								$settings = Speciality::get_options( get_the_ID() );
							}
							if( Utils::to_bool( $options['archive_specialities_show_specialists_count'] ) ) {
								$specialists_count = SpecialistSpecialitiesRel::query()->distinct()->where( 'speciality_id', get_the_ID() )->count();
							}

							ProIcon::view( [
								'icon_type'		=> 'icon',
								'icon'			=> $settings['icon'] ?? "",
								'icon_align'	=> 'center',
								'title'			=> get_the_title(),
								'tag'			=> $options['archive_specialities_title_tag'],
								'subtitle'		=> !empty( $specialists_count ) ? esc_html( sprintf( _n( '%s Specialist', '%s Specialists', $specialists_count, 'drplus' ), $specialists_count ) ) : "",
								'link'			=> get_the_permalink(),
								'show_btn'		=> Utils::to_bool( $options['archive_specialities_show_specialists_arrow'] ),
							] );
						}
						get_template_part( "templates/archives/template-archives-pagination" );
					}
					?>
				</div>
			</div>
		</main>
	</div>
	<?php
}
get_footer();