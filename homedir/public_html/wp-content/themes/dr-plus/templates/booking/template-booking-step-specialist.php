<?php

use DrPlus\Components\Button;
use DrPlus\Components\SectionTitle;
use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Location;
use DrPlus\Utils\Options;
use DrPlus\Utils\User;
use DrPlus\Utils\UtilsSpecialists;

$options = Options::get_options( [
	'booking-search-city-field'				=> true,
	'booking-search-section-tag'			=> 'h1',
	'booking-search-head-text'				=> esc_html__( 'Please select the name of your desired specialist or specialty along with the city.', 'drplus' ),
	'booking-search-specialist-placeholder'	=> esc_html__( 'Search for specialist name', 'drplus' ),
	'booking-search-cities-placeholder'		=> esc_html__( 'All cities', 'drplus' ),
	'booking-search-result-count'			=> 10,
	'booking-search-no-result-text'			=> esc_html__( 'Unfortunately, no specialist was found with your specifications. Please check the information you entered and search again.', 'drplus' ),
	'booking-search-show-recent'			=> true,
	'booking-search-recent-title'			=> esc_html__( 'Recently visited', 'drplus' ),
	'booking-search-specialists-type'		=> 'latest_view',
] );
if( Utils::to_bool( $options['booking-search-city-field'] ) ) {
	$cities = Location::locations( null, false, [], true );
	if( !empty( $cities ) ) {
		$cities = wp_list_pluck( $cities, 'name', 'slug' );
	}
	$cities = array_merge( ['' => __( 'All cities', 'drplus' )], $cities );
}

// Search in specialist
$search_title = esc_html__( 'Search result', 'drplus' );
$search_results = [];
$search_name = Utils::convert_chars( $_GET['stext'] ?? "" );
$city_slug = Utils::convert_chars( $_GET['city'] ?? "", 'sanitize_title_with_dashes' );
$city_term = $city_slug ? get_term_by( 'slug', $city_slug, 'location' ) : null;
$city_title = $city_term && !is_wp_error( $city_term ) ? $city_term->name : $city_slug;
$searched = false;
$ppp = $options['booking-search-result-count']; // post per page
$page_number = Utils::convert_chars( $_GET['searched_page'] ?? 1, true, 'absint' );
$searched = isset( $_GET['city'] ) || isset( $_GET['stext'] );
if( $searched ) {
	$specialists_query = UtilsSpecialists::search( $search_name, [
		'number'		=> $ppp,
		'count_total'	=> true,
		'fields'		=> 'all',
		'paged'			=> $page_number,
		'city'			=> $city_slug,
	], 'query' );

	$total_specialists = $specialists_query->found_posts ?? 0;

	if( !empty( $specialists_query->drplus_specialists ) ) {
		$search_results = $specialists_query->drplus_specialists;
	} else {
		$specialist_posts = array_map( 'absint', (array) $specialists_query->posts );
		if( !empty( $specialist_posts ) ) {
			$query = Specialists::query()->whereIn( 'post_id', $specialist_posts );
			$query->orderByRaw( 'FIELD(post_id, ' . implode( ',', $specialist_posts ) . ')' );
			$search_results = $query->get();
		}
	}

	if( !empty( $search_name ) && !empty( $city_title ) ) {
		$search_title = sprintf( esc_html__( 'Search result for "%s" at "%s" city', 'drplus' ), $search_name, $city_title );
	} else if( !empty( $search_name ) ) {
		$search_title = sprintf( esc_html__( 'Search result for "%s"', 'drplus' ), $search_name );
	} else if( !empty( $city_title ) ) {
		$search_title = sprintf( esc_html__( 'Search result at "%s" city', 'drplus' ), $city_title );
	}
}

$display_settings = [
	'desktop_slider'	=> false,
	'desktop_cols'		=> 5,
	'desktop_gap'		=> 16,

	'tablet_slider'		=> false,
	'tablet_cols'		=> 3,
	'tablet_gap'		=> 16,

	'mobile_slider'		=> false,
	'mobile_cols'		=> 2,
	'mobile_gap'		=> 16,
];
$display_args = Elementor::get_display_attributes( $display_settings );
$div_attrs = [
	'class'			=> $display_args['wrap_classes'],
	'data-settings'	=> $display_args['args'],
	'style'			=> $display_args['style'],
];

// Get recent specialists
if( $options['booking-search-show-recent'] ) {
	if( $options['booking-search-specialists-type'] == 'latest_view' ) {
		$specialists_lists = User::get_user_recently_visited_specialists_ids();
		$specialists_lists = Specialists::query()->where( 'status', 'active' )->whereIn( 'id', $specialists_lists )->get();
	} else {
		$specialists_lists = Specialists::query()->where( 'status', 'active' )->orderBy( 'created_at', 'desc' )->limit( 10 )->get();
	}
}
?>
<div class="booking-search-head">
	<<?php echo tag_escape( $options['booking-search-section-tag'] ) ?> class="booking-search-text">
		<?php echo $options['booking-search-head-text']; ?>
	</<?php echo tag_escape( $options['booking-search-section-tag'] ) ?>>
	<div class="booking-search-form-wrap">
		<form method="get" action="" class="drplus-search-form booking-search-form">
			<?php
			$input_args = [
				'classes'		=> ['drplus-search-text'],
				'data-nonce'	=> wp_create_nonce( 'booking_search_nonce' ),
				'name'			=> 'stext',
			];

			get_template_part( "templates/components/template-components-search-input", null, [
				'wrap'	=> [
					'classes'	=> ['drplus-search-field-group', 'drplus-search-text-field-group'],
				],
				'input'			=> $input_args,
				'value'			=> $search_name,
				'change_bg_when_filled'	=> false,
				'placeholder'	=> $options['booking-search-specialist-placeholder'],
			] );

			if( Utils::to_bool( $options['booking-search-city-field'] ) ) {
				get_template_part( "templates/components/template-components-custom-select", null, [
					'wrap'			=> [
						'classes'	=> ['drplus-search-field-group', 'drplus-search-city-field-group'],
					],
					'select'		=> [
						'classes'	=> ['drplus-search-city'],
						'name'		=> 'city',
					],
					'value'			=> $city_slug,
					'placeholder'	=> $options['booking-search-cities-placeholder'],
					'options'		=> $cities,
				] );
			}
			Button::view( [
				'icon'	=> 'drplus-icon-search'
			] );
			?>
		</form>
	</div>
</div>
<?php if( !empty( $searched ) ) { ?>
	<div id="booking-searched_specialists-wrap" <?php echo Utils::get_html_attributes( $div_attrs ) ?>>
		<?php
		SectionTitle::view( [
			'icon'	=> 'drplus-icon-search-2-fill',
			'title'	=> $search_title,
			'tag'	=> 'h3',
		] );
		if( !empty( $search_results ) ) {
			UtilsSpecialists::list_html( [
				'specialists'	=> $search_results,
				'settings'		=> $display_settings,
				'mode'			=> 'all',
			] );

			get_template_part( "templates/archives/template-archives-pagination", 'custom', [
				'max_num_pages'		=> ceil( $total_specialists / $ppp ),
				'paged'				=> $page_number,
				'query_arg_name'	=> 'searched_page'
			] );
		} else { ?>
			<p class="booking-search-no-result">
				<?php echo $options['booking-search-no-result-text'] ?>
			</p>
		<?php } ?>
	</div>
<?php } ?>
<?php if( $options['booking-search-show-recent'] && !empty( $specialists_lists ) ) { ?>
	<div id="booking-recent_specialists-wrap" <?php echo Utils::get_html_attributes( $div_attrs ) ?>>
		<?php
		SectionTitle::view( [
			'icon'	=> 'drplus-icon-clock-fill',
			'title'	=> $options['booking-search-recent-title'],
			'tag'	=> 'h3',
		] );
		UtilsSpecialists::list_html( [
			'specialists'	=> $specialists_lists,
			'settings'		=> $display_settings,
			'mode'			=> 'all',
		] ); ?>
	</div>
<?php } ?>
