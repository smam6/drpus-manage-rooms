<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'archive_hospital_breadcrumb'		=> true,
	'archive_hospital_show_title'		=> true,
	'archive_hospital_title_icon'		=> 'drplus-icon-stethoscope',
	'archive_hospital_show_sidebar'		=> true,
	'archive_hospital_sidebar'			=> 'archive_hospital',
	'archive_hospital_desktop_cols'		=> 3,
	'archive_hospital_desktop_gap'		=> 24,
	'archive_hospital_tablet_cols'		=> 2,
	'archive_hospital_tablet_gap'		=> 16,
	'archive_hospital_mobile_cols'		=> 1,
	'archive_hospital_mobile_gap'		=> 16,
	'archive_hospital_title_tag'		=> 'h2',
	'archive_hospital_show_subtitle'	=> true,
	'archive_hospital_show_address'		=> true,
	'archive_hospital_show_read_more'	=> true,
	'archive_hospital_read_more_text'	=> __( "View details", 'drplus' ),
	'archive_hospital_read_more_icon'	=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
] );

$has_sidebar = Utils::to_bool( $options['archive_hospital_show_sidebar'] ) && is_active_sidebar( $options['archive_hospital_sidebar'] );
get_header();
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
	?>
	<div id="page-body" class="page-width">
		<main id="page-main">
			<?php get_template_part( "templates/page/template-page-head", null, [
				'options'	=> [
					'show_breadcrumb'	=> Utils::to_bool( $options['archive_hospital_breadcrumb'] ),
					'show_title'		=> Utils::to_bool( $options['archive_hospital_show_title'] ),
					'use_content_style'	=> false,
					'page_icon'			=> $options['archive_hospital_title_icon'],
				],
				'is_archive'		=> true,
				'show_sort_archive'	=> true,
			] ); ?>

			<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
				<?php
				if( $has_sidebar ) {
					get_sidebar( $options['archive_hospital_sidebar'] );
				}

				$posts_args = [
					'class'	=> [
						'site-content', 'hospitals', 'list-hospitals' ,'desktop-columns', 'tablet-columns', 'mobile-columns',
						"desktop-columns-{$options['archive_hospital_desktop_cols']}",
						"tablet-columns-{$options['archive_hospital_tablet_cols']}",
						"mobile-columns-{$options['archive_hospital_mobile_cols']}",
					],
					'role'		=> 'main',
					'style'		=> [
						'--desktop-cols'	=> $options['archive_hospital_desktop_cols'],
						'--tablet-cols'		=> $options['archive_hospital_tablet_cols'],
						'--mobile-cols'		=> $options['archive_hospital_mobile_cols'],
						'--desktop-gap'		=> $options['archive_hospital_desktop_gap'] . "px",
						'--tablet-gap'		=> $options['archive_hospital_tablet_gap'] . "px",
						'--mobile-gap'		=> $options['archive_hospital_mobile_gap'] . "px",
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

							get_template_part( "templates/archives/template-archives-hospital", null, [
								'archive_hospital_title_tag'		=> $options['archive_hospital_title_tag'],
								'archive_hospital_show_subtitle'	=> $options['archive_hospital_show_subtitle'],
								'archive_hospital_show_address'		=> $options['archive_hospital_show_address'],
								'archive_hospital_show_read_more'	=> $options['archive_hospital_show_read_more'],
								'archive_hospital_read_more_text'	=> $options['archive_hospital_read_more_text'],
								'archive_hospital_read_more_icon'	=> $options['archive_hospital_read_more_icon'],
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