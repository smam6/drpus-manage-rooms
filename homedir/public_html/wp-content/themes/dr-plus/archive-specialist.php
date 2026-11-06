<?php

use DrPlus\Model\Specialists;
use DrPlus\Utils;
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

$specialist_ids = [];
if( have_posts() ) {
	while( have_posts() ) {
		the_post();

		$specialist_ids[] = get_the_ID();
	}
}
if( $specialist_ids ) {
	$specialists = Specialists::query()
		->whereIn( 'post_id', $specialist_ids )
		->where( 'status', 'active' )
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
					'list-specialists' ,'desktop-columns', 'tablet-columns', 'mobile-columns',
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
				]
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
				if( !empty( $specialists ) && !$specialists->isEmpty() ) {
					UtilsSpecialists::list_html( [
						'specialists'	=> $specialists,
						'settings'		=> [
							'style'			=> $options['archive_specialist_card_type'],
							'verified-text'	=> $options['archive_specialist_verified_text']
						],
						'remove_wrap'	=> true,
					] );
					get_template_part( "templates/archives/template-archives-pagination");
				}
				?>
			</div>
		</div>
	</main>
</div>
<?php

get_footer();