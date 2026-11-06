<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\Search;
use DrPlus\Utils\Speciality;

$args = Utils::check_default( $args, [
	'search_icon'			=> 'drplus-icon-profile-tick',
	'search_placeholder'	=> esc_html__( "Enter the doctor's name...", 'drplus' ),
	'search_city'			=> true,
	'search_specialities'	=> true,
	'show_arrows'			=> false,
], ['search_icon'] );

$selected_city = Search::get_city_from_GET();

$fields_ids = [
	'search'	=> wp_unique_id( "specialists-search-input-" ),
];
?>
<form action="<?php echo home_url() ?>" method="get" class="specialists-search">
	<input type="hidden" name="section" value="specialist">
	<div class="specialists-search-input-wrap">
		<label for="<?php echo $fields_ids['search'] ?>" class="specialists-search-input-icon-wrap"><?php echo Sanitizers::icon( $args['search_icon'], 'specialists-search-input-icon' ) ?></label>
		<input type="search" name="s" id="<?php echo $fields_ids['search'] ?>" class="specialists-search-input input-transparent" placeholder="<?php echo esc_attr( $args['search_placeholder'] ) ?>" value="<?php echo get_search_query() ?>">
		<?php if( $args['search_city'] ) { ?>
			<input type="hidden" name="city" class="specialists-search-input-city" value="<?php echo $selected_city ? esc_attr( $selected_city->slug ) : '' ?>">
			<button type="button" class="outline specialists-search-input-select-city button-not-gradient">
				<i class="drplus-icon-location specialists-search-input-select-city-icon" aria-hidden="true"></i>
				<span class="specialists-search-input-select-city-name"><?php echo $selected_city ? esc_html( $selected_city->name ) : esc_html__( "All cities", 'drplus' ) ?></span>
				<i class="drplus-icon-bottom specialists-search-input-select-city-arrow" aria-hidden="true"></i>
			</button>

			<div class="specialists-search-city-popup">
				<div class="specialists-search-city-popup-head">
					<div class="specialists-search-city-popup-title"><?php esc_html_e( 'Select a city', 'drplus' ) ?></div>
					<button type="button" class="specialists-search-city-popup-close button-not-gradient"><i class="drplus-icon-close-circle" aria-hidden="true"></i></button>
				</div>

				<div class="specialists-search-city-popup-search-wrap">
					<div class="specialists-search-city-popup-field-wrap">
						<i class="drplus-icon-search-2 specialists-search-city-popup-search-icon" aria-hidden="true"></i>
						<input type="search" class="specialists-search-city-popup-search input-transparent" placeholder="<?php esc_attr_e( "Search cities...", 'drplus' ) ?>" data-nonce="<?php echo wp_create_nonce( 'drplus-search-cities' ) ?>">
					</div>
					<button type="button" class="specialists-search-city-popup-all-cities"><?php esc_html_e( 'All cities', 'drplus' ) ?></button>
				</div>

				<div class="specialists-search-city-popup-results">
					<?php get_template_part( "templates/components/template-components-loading", null, [
						'text'		=> esc_html__( "Searching...", 'drplus' ),
						'classes'	=> ['specialists-search-city-popup-loading'],
					] ) ?>
					<div class="drplus-search-input-popover-empty">
						<i class="drplus-icon-search-2"></i>
						<span class="drplus-search-input-popover-empty-text"><?php esc_html_e( 'No results. Please try again with a different text.', 'drplus' ) ?></span>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>

	<?php
	if( $args['search_specialities'] ) {
		$specialities_display_attrs = Elementor::get_display_attributes( [
			'desktop_slides_type'	=> 'count',
			'desktop_slides'		=> 3,
			'desktop_slides_space'	=> 20,
			'tablet_slides_type'	=> 'count',
			'tablet_slides'			=> 3,
			'tablet_slides_space'	=> 20,
			'mobile_slides_type'	=> 'count',
			'mobile_slides'			=> 2,
			'mobile_slides_space'	=> 16,
		], true );

		$specialities_main_html_attrs = [
			'classes'		=> array_merge( ['drplus-slider-wrap', 'specialists-search-specialities-wrap'], $specialities_display_attrs['wrap_classes'] ),
			'style'			=> $specialities_display_attrs['style'],
			'data-settings'	=> $specialities_display_attrs['args'],
		];
		$specialities_wrap_html_attrs = [
			'classes'	=> array_merge( ['wrapper'], $specialities_display_attrs['classes'] )
		];
		?>
		<div <?php echo Utils::get_html_attributes( $specialities_main_html_attrs ) ?>>
			<?php
			$is_rtl = is_rtl();
			if( $args['show_arrows'] ) {
				Button::view( [
					'classes'	=> ['drplus-slider-nav-btn', 'swiper-button-prev', 'drplus-slider-nav-prev'],
					'icon'		=> $is_rtl ? 'drplus-icon-right' : 'drplus-icon-left',
					'type'		=> 'gray',
					'atts'		=> [
						'type'	=> 'button'
					]
				] );
			}
			?>
			<div <?php echo Utils::get_html_attributes( $specialities_wrap_html_attrs ) ?>>
				<?php foreach( Speciality::all( [], true, true ) as $speciality ) { ?>
					<label class="slider-slide specialists-search-speciality">
						<input type="checkbox" name="specialities[]" value="<?php echo esc_attr( $speciality->ID ) ?>">
						<div class="specialists-search-speciality-icon-wrap">
							<?php
							if( $speciality->options['icon'] ) {
								echo Sanitizers::icon( $speciality->options['icon'], 'specialists-search-speciality-icon' );
							} else {
								echo get_the_post_thumbnail( $speciality, [56, 56] );
							}
							?>
						</div>

						<div class="specialists-search-speciality-texts">
							<div class="specialists-search-speciality-name line-clamp line-clamp-1"><?php echo esc_html( $speciality->post_title ) ?></div>
							<?php if( !empty( $speciality->options['subtitle'] ) ) { ?>
								<div class="specialists-search-speciality-subtitle line-clamp line-clamp-1"><?php echo esc_html( $speciality->options['subtitle'] ) ?></div>
							<?php } ?>
						</div>
					</label>
				<?php } ?>
			</div>
			<?php
			if( $args['show_arrows'] ) {
				Button::view( [
					'classes'	=> ['drplus-slider-nav-btn', 'swiper-button-next', 'drplus-slider-nav-next'],
					'icon'		=> $is_rtl ? 'drplus-icon-left' : 'drplus-icon-right',
					'type'		=> 'gray',
					'atts'		=> [
						'type'	=> 'button'
					]
				] );
			}
			?>
		</div>
		<?php
	}

	$args['prefix'] = 'button_';
	$args['button_classes'] = ['specialists-search-submit'];
	Button::view( $args );
	?>
</form>