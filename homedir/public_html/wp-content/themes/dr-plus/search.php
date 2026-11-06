<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Search;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'search_breadcrumb'			=> true,
	'search_show_sidebar'		=> true,
	'search_sidebar'			=> 'search',

	'search_show_cities'	=> true,

	'archive_posts_style'			=> 'style-1',
	'search_post_title_tag'			=> 'h2',
	'search_post_time_type'			=> 'date',
	'search_post_show_read_more'	=> true,
	'search_post_read_more_text'	=> __( "Read more", 'drplus' ),
	'search_post_read_more_icon'	=> is_rtl() ? 'drplus-icon-arrow-left' : 'drplus-icon-arrow-right',
] );


$has_sidebar = Utils::to_bool( $options['search_show_sidebar'] ) && is_active_sidebar( $options['search_sidebar'] );
get_header();
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
	?>
	<div id="page-body" class="page-width">
		<main id="page-main">
			<?php get_template_part( "templates/page/template-page-head", null, [
				'options'	=> [
					'show_breadcrumb'	=> Utils::to_bool( $options['search_breadcrumb'] ),
					'show_title'		=> false,
					'use_content_style'	=> false,
				],
				'is_archive'		=> true,
				'show_sort_archive'	=> false,
			] ); ?>

			<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
				<div id="search-header" class="col-12">
					<?php get_template_part( "templates/components/template-components-search", null, [
						'city_field'	=> Utils::to_bool( $options['search_show_cities'] ),
						'city_value'	=> !empty( $_GET['city'] ) ? esc_attr( $_GET['city'] ) : '',
						'button_icon'	=> 'drplus-icon-search',
						'change_bg_when_filled'	=> false,
					] ); ?>
				</div>

				<?php
				if( $has_sidebar ) {
					get_sidebar( $options['search_sidebar'] );
				}
				?>

				<div id="search-results" class="<?php echo $has_sidebar ? 'col-md-9 col-sm-12' : 'col-12' ?>">
					<?php
					$current_post_type = Search::get_post_type();
					if( !$current_post_type ) {
						get_template_part( "templates/search/templates-search-main", null, [
							'options'	=> $options,
						] );
					} else {
						get_template_part( "templates/search/templates-search-results", null, [
							'post_type'	=> $current_post_type,
							'options'	=> $options,
						] );
					}
					?>
				</div>
			</div>
		</main>
	</div>
	<?php
}
get_footer();