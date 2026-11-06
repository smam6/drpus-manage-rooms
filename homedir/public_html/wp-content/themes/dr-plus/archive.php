<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'archive-movie' ) ) {
	// Elementor rendered the template
} else {
	$options = Options::get_options( [
		'archive_posts_style'			=> 'style-1',

		'archive_breadcrumb'			=> true,
		'archive_show_title'			=> true,
		'archive_title_icon'			=> 'drplus-icon-discovery',
		'archive_show_sidebar'			=> true,
		'archive_sidebar'				=> 'blog',
		'archive_desktop_cols'			=> 3,
		'archive_desktop_gap'			=> 24,
		'archive_tablet_cols'			=> 2,
		'archive_tablet_gap'			=> 16,
		'archive_mobile_cols'			=> 1,
		'archive_mobile_gap'			=> 16,
		'archive_post_title_tag'		=> 'h2',
		'archive_post_show_time'		=> true,
		'archive_post_time_type'		=> 'date',
		'archive_post_show_read_more'	=> true,
		'archive_post_read_more_text'	=> __( "Read more", 'drplus' ),
		'archive_post_read_more_icon'	=> is_rtl() ? 'drplus-icon-arrow-left' : 'drplus-icon-arrow-right',
	] );

	$has_sidebar = Utils::to_bool( $options['archive_show_sidebar'] ) && is_active_sidebar( $options['archive_sidebar'] );

	if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
		?>
		<div id="page-body" class="page-width">
			<main id="page-main">
				<?php get_template_part( "templates/page/template-page-head", null, [
					'options'	=> [
						'show_breadcrumb'	=> Utils::to_bool( $options['archive_breadcrumb'] ),
						'show_title'		=> Utils::to_bool( $options['archive_show_title'] ),
						'use_content_style'	=> false,
						'page_icon'			=> $options['archive_title_icon'],
					],
					'is_archive'		=> true,
					'show_sort_archive'	=> true,
				] ); ?>

				<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
					<?php
					if( $has_sidebar ) {
						get_sidebar( $options['archive_sidebar'] );
					}

					$posts_args = [
						'id'	=> 'posts',
						'class'	=> [
							'site-content' ,'list-posts' ,'desktop-columns', 'tablet-columns', 'mobile-columns',
							"desktop-columns-{$options['archive_desktop_cols']}",
							"tablet-columns-{$options['archive_tablet_cols']}",
							"mobile-columns-{$options['archive_mobile_cols']}",
							"posts-{$options['archive_posts_style']}",
						],
						'role'		=> 'main',
						'style'		=> [
							'--desktop-cols'	=> $options['archive_desktop_cols'],
							'--tablet-cols'		=> $options['archive_tablet_cols'],
							'--mobile-cols'		=> $options['archive_mobile_cols'],
							'--desktop-gap'		=> $options['archive_desktop_gap'] . "px",
							'--tablet-gap'		=> $options['archive_tablet_gap'] . "px",
							'--mobile-gap'		=> $options['archive_mobile_gap'] . "px",
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

								get_template_part( "templates/archives/template-archives-post", $options['archive_posts_style'], [
									'title_tag'			=> $options['archive_post_title_tag'],
									'show_time'			=> $options['archive_post_show_time'],
									'time_type'			=> $options['archive_post_time_type'],
									'show_read_more'	=> $options['archive_post_show_read_more'],
									'read_more_text'	=> $options['archive_post_read_more_text'],
									'read_more_icon'	=> $options['archive_post_read_more_icon'],
								] );
							}
							get_template_part( "templates/archives/template-archives-pagination" );
						} else {
							if( is_search() ) {
								$general_search_no_results = Options::get_options( [
									'general_search_no_results'	=> __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'drplus' ),
								] )['general_search_no_results'];
								echo '<div class="empty-page">';
								echo '<i class="empty-page-icon drplus-icon-search-cross" aria-hidden="true"></i>';
								echo '<p class="empty-page-text no-results">' . $general_search_no_results . '</p>';
								echo '</div>';
							}
						}
						?>
					</div>
				</div>
			</main>
		</div>
		<?php
	}
}

get_footer();