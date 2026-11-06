<?php

use DrPlus\Model\Specialists;
use DrPlus\Model\SpecialistSpecialitiesRel;
use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Options;
use DrPlus\Utils\Speciality;
use DrPlus\Utils\UtilsSpecialists;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'single_specialty_breadcrumb'		=> true,
	'single_specialty_show_title'		=> true,
	'single_specialty_show_icon'		=> true,
	'single_specialty_show_sidebar'		=> true,
	'single_specialty_sidebar'			=> 'single_speciality',
	'single_specialty_desktop_cols'		=> 4,
	'single_specialty_desktop_gap'		=> 16,
	'single_specialty_tablet_cols'		=> 2,
	'single_specialty_tablet_gap'		=> 16,
	'single_specialty_mobile_cols'		=> 1,
	'single_specialty_mobile_gap'		=> 16,
	'single_specialty_card_type'		=> 'card-1',
	'single_specialty_title_tag'		=> 'h2',
	'single_specialty_verified_text'	=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), get_bloginfo( 'name' ) ),
	'single_specialty_page_description'	=> 'top',
] );

$sorts = Archive::sorts();
$sorts = Utils::unset( $sorts, ['most-view', 'title-asc', 'title-desc'] );

$ppp = get_option( 'posts_per_page', 10 );
$paged = Utils::convert_chars( $_GET['s-page'] ?? 1, true, 'absint' );
if( $paged < 1 ) $paged = 1;
$offset = ($paged - 1) * $ppp;

$specialists = [];
$user_ids_by_speciality = SpecialistSpecialitiesRel::query()->select( 'user_id' )->distinct()->where( 'speciality_id', get_the_ID() )->get();
if( !$user_ids_by_speciality->isEmpty() ) {
	$include_users = $user_ids_by_speciality->pluck( 'user_id' );
	$specialists = Specialists::query()
		->whereIn( 'user_id', $include_users )
		->where( 'status', 'active' );

	$count = $specialists->count();

	$specialists = $specialists
		->limit( intval( $ppp ) )
		->offset( intval( $offset ) );

	$options = Options::get_options( ['default_archive_sort' => 'newest'] );
	$orderby = $options['default_archive_sort'];
	$orderby = apply_filters( 'drplus/archive/default_sort', $orderby, $sorts );
	if( !empty( $_GET['orderby'] ) ) {
		$orderby = Utils::convert_chars( $_GET['orderby'] );
	}

	if( $orderby == 'newest' ) {
		$specialists = $specialists->orderBy( 'created_at', 'DESC' );
	} else if( $orderby == 'oldest' ) {
		$specialists = $specialists->orderBy( 'created_at', 'ASC' );
	}

	$specialists = apply_filters( 'drplus/speciality/single/specialists_model', $specialists, ['orderby' => $orderby] ); // For modify orderby & where conditions

	$specialists = $specialists->get();
}

$has_sidebar = Utils::to_bool( $options['single_specialty_show_sidebar'] ) && is_active_sidebar( $options['single_specialty_sidebar'] );
get_header();

$description = trim( get_the_content() );
?>
<div id="page-body" class="page-width">
	<main id="page-main">
		<?php get_template_part( "templates/page/template-page-head", null, [
			'options'	=> [
				'show_breadcrumb'	=> Utils::to_bool( $options['single_specialty_breadcrumb'] ),
				'show_title'		=> Utils::to_bool( $options['single_specialty_show_title'] ),
				'use_content_style'	=> false,
				'page_icon'			=> Utils::to_bool( $options['single_specialty_show_icon'] ) ? Speciality::get_options()['icon'] : "",
			],
			'is_archive'		=> true,
			'show_sort_archive'	=> true,
			'sorts'				=> $sorts,
		] ); ?>

		<?php if( $description && $options['single_specialty_page_description'] == 'top' ) { ?>
			<div class="row page-descriptions-wrap page-descriptions-top">
				<div class="col-12" id="page-descriptions"><?php the_content() ?></div>
			</div>
		<?php } ?>

		<div id="primary" class="content-area archive row<?php echo $has_sidebar ? " content-area-with-sidebar" : '' ?>">
			<?php
			if( $has_sidebar ) {
				get_sidebar( $options['single_specialty_sidebar'] );
			}

			$posts_args = [
				'class'	=> [
					'site-content', 'specialists', "specialists-style-{$options['single_specialty_card_type']}",
					'list-specialists' ,'desktop-columns', 'tablet-columns', 'mobile-columns',
					"desktop-columns-{$options['single_specialty_desktop_cols']}",
					"tablet-columns-{$options['single_specialty_tablet_cols']}",
					"mobile-columns-{$options['single_specialty_mobile_cols']}",
				],
				'role'		=> 'main',
				'style'		=> [
					'--desktop-cols'	=> $options['single_specialty_desktop_cols'],
					'--tablet-cols'		=> $options['single_specialty_tablet_cols'],
					'--mobile-cols'		=> $options['single_specialty_mobile_cols'],
					'--desktop-gap'		=> $options['single_specialty_desktop_gap'] . "px",
					'--tablet-gap'		=> $options['single_specialty_tablet_gap'] . "px",
					'--mobile-gap'		=> $options['single_specialty_mobile_gap'] . "px",
				]
			];
			if( in_array( $options['single_specialty_card_type'], ['card-1', 'card-2'] ) ) {
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
							'style'			=> $options['single_specialty_card_type'],
							'verified-text'	=> $options['single_specialty_verified_text']
						],
						'remove_wrap'	=> true,
					] );
					$max_num_page = ceil( $count / $ppp );
					if( $max_num_page > 1 ) {
						get_template_part( 'templates/archives/template-archives-pagination', 'custom', [
							'max_num_pages'		=> $max_num_page,
							'paged'				=> $paged,
							'query_arg_name'	=> 's-page',
						] );
					}
				}
				?>
			</div>
		</div>

		<?php if( $description && $options['single_specialty_page_description'] == 'bottom' ) { ?>
			<div class="row page-descriptions-wrap page-descriptions-bottom">
				<div class="col-12" id="page-descriptions"><?php the_content() ?></div>
			</div>
		<?php } ?>
	</main>
</div>
<?php
get_footer();