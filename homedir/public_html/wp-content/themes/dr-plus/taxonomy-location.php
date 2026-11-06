<?php

use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Options;
use DrPlus\Utils\UtilsSpecialists;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'archive_specialist_breadcrumb'		=> true,
	'archive_specialist_show_title'		=> true,
	'archive_specialist_title_icon'		=> 'drplus-icon-stethoscope',
	'archive_specialist_show_sidebar'	=> true,
	'archive_specialist_sidebar'		=> 'archive_specialist',
	'archive_specialist_desktop_cols'	=> 4,
	'archive_specialist_desktop_gap'	=> 16,
	'archive_specialist_tablet_cols'	=> 2,
	'archive_specialist_tablet_gap'		=> 16,
	'archive_specialist_mobile_cols'	=> 1,
	'archive_specialist_mobile_gap'		=> 16,
	'archive_specialist_card_type'		=> 'card-1',
	'archive_specialist_title_tag'		=> 'h2',
	'archive_specialist_verified_text'	=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
] );

$query_object = get_queried_object();

$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
$location_term = get_queried_object();
$archive_title = '';
$archive_title_filter = null;
if( $location_term && !is_wp_error( $location_term ) ) {
	$archive_title = sprintf( esc_html__( 'Specialists archive of %s', 'drplus' ), $location_term->name );
	$archive_title_filter = function() use ( $archive_title ) {
		return $archive_title;
	};
	add_filter( 'get_the_archive_title', $archive_title_filter );
}

$specialist_type = isset( $_GET['specialist-type'] ) ? Utils::ensure_values_in_array( Utils::convert_chars( $_GET['specialist-type'], 'sanitize_text_field' ), ['all', 'in-person', 'online'], 'all' ) : 'all';
$type_post_ids = [];
if( $specialist_type !== 'all' ) {
	$type_query = Specialists::query()
		->select( 'post_id' )
		->where( 'status', 'active' );

	if( $specialist_type === 'in-person' ) {
		$type_query->where( 'offline_visit', 1 );
	}
	if( $specialist_type === 'online' ) {
		$type_query->where( 'online_visit', 1 );
	}

	$type_post_ids = array_values( array_filter( $type_query->get()->pluck( 'post_id' ), 'absint' ) );
}

$sort_options = Options::get_options( [
	'default_archive_sort'	=> 'newest',
] );
$sorts = Archive::sorts();
$sort = !empty( $_GET['orderby'] ) ? Utils::convert_chars( $_GET['orderby'] ) : $sort_options['default_archive_sort'];
$sort = isset( $sorts[$sort] ) ? $sort : $sort_options['default_archive_sort'];

$orderby = '';
$order = 'DESC';
$meta_key = '';

switch( $sort ) {
	case 'oldest':
		$orderby = 'date';
		$order = 'ASC';
		break;
	case 'most-view':
		$orderby = 'meta_value_num';
		$order = 'ASC';
		$meta_key = '_views';
		break;
	case 'title-asc':
		$orderby = 'title';
		$order = 'ASC';
		break;
	case 'title-desc':
		$orderby = 'title';
		break;
	default:
		$orderby = 'date';
		break;
}

$specialist_query_args = [
	'post_type'			=> 'specialist',
	'post_status'		=> 'publish',
	'paged'				=> $paged,
	'tax_query'			=> [
		[
			'taxonomy'	=> 'location',
			'field'		=> 'term_id',
			'terms'		=> $location_term->term_id ?? 0,
		],
	],
	'orderby'			=> $orderby,
	'order'				=> $order,
];
if( $meta_key ) {
	$specialist_query_args['meta_key'] = $meta_key;
}
if( $specialist_type !== 'all' ) {
	$specialist_query_args['post__in'] = !empty( $type_post_ids ) ? $type_post_ids : [0];
}
$specialist_query = new WP_Query( $specialist_query_args );

global $wp_query;
$original_query = $wp_query;
$wp_query = $specialist_query; // Allow pagination template to work with the custom query.

$specialist_ids = [];
if( $specialist_query->have_posts() ) {
	while( $specialist_query->have_posts() ) {
		$specialist_query->the_post();

		$specialist_ids[] = get_the_ID();
	}
}

$specialists = [];
if( !empty( $specialist_ids ) ) {
	$specialists_query = Specialists::query()
		->whereIn( 'post_id', $specialist_ids )
		->where( 'status', 'active' );

	if( $specialist_type === 'in-person' ) {
		$specialists_query->where( 'offline_visit', 1 );
	}
	if( $specialist_type === 'online' ) {
		$specialists_query->where( 'online_visit', 1 );
	}

	$specialists = $specialists_query
		->orderByRaw( 'FIELD(`post_id`, ' . Utils::db_placeholder( $specialist_ids, '%d' ) . ')', $specialist_ids )
		->get();
}

$has_sidebar = Utils::to_bool( $options['archive_specialist_show_sidebar'] ) && is_active_sidebar( $options['archive_specialist_sidebar'] );
get_header();
?>
<div id="page-body" class="page-width">
	<main id="page-main">
		<?php get_template_part( "templates/page/template-page-head", null, [
			'options'	=> [
				'show_breadcrumb'	=> Utils::to_bool( $options['archive_specialist_breadcrumb'] ),
				'show_title'		=> Utils::to_bool( $options['archive_specialist_show_title'] ),
				'use_content_style'	=> false,
				'page_icon'			=> $options['archive_specialist_title_icon'],
			],
			'is_archive'		=> true,
			'show_sort_archive'	=> true,
		] ); ?>

		<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
			<?php
			if( $has_sidebar ) {
				get_sidebar( $options['archive_specialist_sidebar'] );
			}

			$posts_args = [
				'class'	=> [
					'site-content', 'specialists', "specialists-style-{$options['archive_specialist_card_type']}",
					'list-specialists', 'desktop-columns', 'tablet-columns', 'mobile-columns',
					"desktop-columns-{$options['archive_specialist_desktop_cols']}",
					"tablet-columns-{$options['archive_specialist_tablet_cols']}",
					"mobile-columns-{$options['archive_specialist_mobile_cols']}",
				],
				'role'		=> 'main',
				'style'		=> [
					'--desktop-cols'	=> $options['archive_specialist_desktop_cols'],
					'--tablet-cols'		=> $options['archive_specialist_tablet_cols'],
					'--mobile-cols'		=> $options['archive_specialist_mobile_cols'],
					'--desktop-gap'		=> $options['archive_specialist_desktop_gap'] . "px",
					'--tablet-gap'		=> $options['archive_specialist_tablet_gap'] . "px",
					'--mobile-gap'		=> $options['archive_specialist_mobile_gap'] . "px",
				],
			];
			if( in_array( $options['archive_specialist_card_type'], ['card-1', 'card-2'] ) ) {
				$posts_args['class'][] = 'specialists-style-card';
			}
			if( $has_sidebar ) {
				$posts_args['class'][] = 'col-md-9';
				$posts_args['class'][] = 'col-sm-12';
			} else {
				$posts_args['class'][] = 'col-12';
			}
			?>
			<div <?php echo Utils::get_html_attributes( $posts_args ) ?>>
				<?php
				if( !empty( $specialists ) ) {
					UtilsSpecialists::list_html( [
						'specialists'	=> $specialists,
						'settings'		=> [
							'style'			=> $options['archive_specialist_card_type'],
							'verified-text'	=> $options['archive_specialist_verified_text'],
						],
						'remove_wrap'	=> true,
					] );
					get_template_part( "templates/archives/template-archives-pagination" );
				}
				?>
			</div>
			<?php if( !empty( $query_object->description ) ) { ?>
				<div class="term-description"><?php echo wp_kses_post( $query_object->description ); ?></div>
			<?php } ?>
		</div>
	</main>
</div>
<?php
$wp_query = $original_query;
wp_reset_postdata();
if( $archive_title_filter ) {
	remove_filter( 'get_the_archive_title', $archive_title_filter );
}
get_footer();
