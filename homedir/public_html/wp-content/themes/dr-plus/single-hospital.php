<?php

use DrPlus\Components\SectionTitle;
use DrPlus\Components\SimpleIcon;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\UtilsSpecialists;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'single_hospital_show_breadcrumb'	=> true,
	'single_hospital_show_head'			=> true,
	'single_hospital_head_icon'			=> true,
	'single_hospital_head_title'		=> true,
	'single_hospital_head_subtitle'		=> true,
	'single_hospital_head_address'		=> true,
	'single_hospital_show_gallery'		=> true,
	'single_hospital_show_map'			=> true,
	'single_hospital_show_specialists'	=> true,
	'hospital-single-title-tag'			=> 'h1',
	'hospital-single-subtitle-tag'		=> 'h2',

	'single_hospital_use_content_style'	=> false,

	'seo-enable-hospital-schema'		=> true,
] );

$primary_classes = ['content-area', 'site-content'];
$show_sidebar = is_active_sidebar( 'single_hospital' );
if( $show_sidebar ) {
	$primary_classes[] = 'content-area-with-sidebar';
}
if( !$options['single_hospital_use_content_style'] ) {
	$primary_classes[] = 'content-area-empty';
}

get_header();

while ( have_posts() ) :
	the_post();

	$shares = drplus_single_share();

	$post_classes = ['entry-content'];

	$hospital_settings = Hospital::get_options( get_the_ID() );

	$_term_services = get_the_terms( $post, 'hospital-service' );
	$term_services = [];
	if( !empty( $_term_services ) && !is_wp_error( $_term_services ) ) {
		foreach( $_term_services as $service ) {
			$term_services[] = [
				'title'			=> $service->name,
				'description'	=> $service->description,
			];
		}
	}
	$hospital_settings['services'] = array_merge( $term_services, $hospital_settings['services'] );

	if( $options['seo-enable-hospital-schema'] ) {
		$schema = [
			"@context"		=> "https://schema.org",
			"@type"			=> 'Hospital',
			'name'			=> get_the_title(),
			'description'	=> get_the_excerpt(),
			'url'			=> esc_url( get_permalink() ),
			'address'		=> [
				'@type'				=> 'PostalAddress',
				'addressLocality'	=> $hospital_settings['city'],
				'streetAddress'		=> $hospital_settings['address'],
			],
			'makesOffer'	=> [
				'@type'			=> "Offer",
				'itemOffered'	=> [],
			],
		];
		if( !empty( $hospital_settings['province'] ) ) {
			$schema['address']['addressRegion'] = $hospital_settings['province'];
		}

		if( has_post_thumbnail() ) {
			$schema['image'] = get_the_post_thumbnail_url( null, 'full' );
		}
		if ( !empty( $hospital_settings['phones'] ) ) {
			$phones = array_filter( array_column( $hospital_settings['phones'], 'phone' ) );
			if ( !empty( $phones ) ) {
				$schema['telephone'] = $phones;
			}
		}
		if ( !empty( $hospital_settings['emails'] ) ) {
			$emails = array_filter( array_column( $hospital_settings['emails'], 'email' ) );
			if ( !empty( $emails ) ) {
				$schema['email'] = $emails;
			}
		}
		$social_profiles = [];
		if ( !empty( $hospital_settings['socials'] ) ) {
			foreach ( $hospital_settings['socials'] as $social ) {
				if ( !empty( $social['link'] ) ) {
					$social_profiles[] = esc_url( $social['link'] );
				}
			}
			if ( !empty( $social_profiles ) ) {
				$schema['sameAs'] = $social_profiles;
			}
		}
	}

	if( Utils::to_bool( $options['single_hospital_show_specialists'] ) ) {
		$hospital_specialists = UtilsSpecialists::get_hospital_specialists( get_the_ID() );
	}

	$post_title = drplus_get_post_title();
	?>
	<div id="page-body" class="page-width">
		<main id="page-main">
			
			<?php
			if( Utils::to_bool( $options['single_hospital_show_breadcrumb'] ) ) {
				drplus_breadcrumb();
			}
			?>

			<?php if( Utils::to_bool( $options['single_hospital_show_head'] ) ) { ?>
				<header id="page-header" aria-labelledby="post-title">
					<?php if( Utils::to_bool( $options['single_hospital_head_icon'] ) || Utils::to_bool( $options['single_hospital_head_title'] ) || Utils::to_bool( $options['single_hospital_head_subtitle'] ) ) { ?>
						<div id="head-title-wrap">
							<?php if( Utils::to_bool( $options['single_hospital_head_icon'] ) ) { ?>
								<?php echo Sanitizers::icon( $hospital_settings['icon'], 'hospital-head-icon' ) ?>
							<?php } ?>
							<?php if( Utils::to_bool( $options['single_hospital_head_title'] ) ) { ?>
								<<?php echo $options['hospital-single-title-tag'] ?> id="post-title" class="line-clamp line-clamp-2"><a href="<?php the_permalink() ?>" title="<?php echo esc_attr( $post_title ) ?>"><?php echo esc_html( $post_title ) ?></a></<?php echo $options['hospital-single-title-tag'] ?>>
							<?php } ?>
							<?php if( Utils::to_bool( $options['single_hospital_head_subtitle'] ) ) { ?>
								<<?php echo $options['hospital-single-subtitle-tag'] ?> id="post-subtitle" class="line-clamp line-clamp-2"><?php echo wp_kses_post( $hospital_settings['subtitle'] ) ?></<?php echo $options['hospital-single-subtitle-tag'] ?>>								
							<?php } ?>
						</div>
					<?php } ?>
	
					<?php if( Utils::to_bool( $options['single_hospital_head_address'] ) && !empty( $hospital_settings['map_address'] ) ) { ?>
						<a href="<?php echo esc_url( $hospital_settings['map_address'], ['https'] ) ?>" class="map-popup-opener" id="head-address-wrap" target="map-popup-iframe" title="<?php echo esc_attr( $hospital_settings['address'] ) ?>" data-title="<?php echo esc_attr( $post_title ) ?>">
							<?php
							SimpleIcon::view( [
								'icon'	=> 'drplus-icon-location-fill'
							] );
							?>
							<address id="head-address" class="line-clamp line-clamp-2"><?php echo esc_html( $hospital_settings['address'] ) ?></address>
						</a>
					<?php } ?>
				</header>
			<?php } ?>

			<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
				<div id="post-content" class="row">
					<div class="entry-container col-12">
						<div id="page-content" class="site-content single" role="main">
							<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
								<?php if( Utils::to_bool( $options['single_hospital_show_gallery'] ) && ( has_post_thumbnail() || !empty( $hospital_settings['gallery'] ) ) ) { ?>
									<section class="hospital-gallery<?php echo !$hospital_settings['gallery'] ? ' hospital-gallery-only-thumbnail' : '' ?>">
										<?php
										drplus_post_thumbnail( null, null, false );
										if( $hospital_settings['gallery'] ) {
											for( $img_index = 0; $img_index <= 3; $img_index++ ) {
												if( empty( $hospital_settings['gallery'][$img_index] ) ) break;

												echo '<div class="hospital-gallery-item">' . wp_get_attachment_image( $hospital_settings['gallery'][$img_index], [400, 400] ) . '</div>';
											}
										}
										?>
									</section>
									<?php
									$images = [];
									if( has_post_thumbnail() ) {
										$images[] = get_post_thumbnail_id( get_the_ID() );
									}
									$images = array_merge( $images, $hospital_settings['gallery'] );
									?>
									<div class="hospital-gallery-popup-overlay"></div>
									<div class="hospital-gallery-popup">
										<div class="hospital-gallery-popup-main-slider swiper">
											<?php
											get_template_part( 'templates/components/template-components-slider_arrows' );
											?>
											<div class="swiper-wrapper">
												<?php
												foreach( $images as $image_id ) {
													echo wp_get_attachment_image( $image_id, 'full', false, [
														'class'	=> 'swiper-slide'
													] );
												}
												?>
											</div>
										</div>

										<div class="hospital-gallery-popup-footer">
											<div class="hospital-gallery-popup-thumb-slider swiper">
												<?php
												get_template_part( 'templates/components/template-components-slider_arrows' );
												?>
												<div class="swiper-wrapper">
													<?php
													foreach( $images as $image_id ) {
														echo wp_get_attachment_image( $image_id, [128, 128], false, [
															'class'	=> 'swiper-slide'
														] );
													}
													?>
												</div>
											</div>
										</div>
									</div>
								<?php } ?>
								
								<?php if( get_the_content() ) { ?>
									<section id="descriptions" class="single-section" role="region">
										<?php
										SectionTitle::view( [
											'icon'			=> 'drplus-icon-stethoscope',
											'tag'			=> 'h3',
											'title'			=> sprintf( esc_html__( "Introducing %s Hospital", 'drplus' ), get_the_title() ),
											'aria-label'	=> sprintf( esc_html__( "Introducing %s Hospital", 'drplus' ), get_the_title() ),
											'classes'		=> ['single-section-title'],
											'link'			=> '#descriptions',
										] );
										?>

										<div class="single-section-content">
											<?php the_content() ?>
										</div>
									</section>
								<?php } ?>

								<div class="row hospital-details">
									<div class="hospital-info <?php echo $show_sidebar ? 'col-lg-9 col-md-12' : 'col-12' ?>">
										<?php if( !empty( $hospital_settings['services'] ) ) { ?>
											<section id="services" class="single-section" role="region">
												<?php
												SectionTitle::view( [
													'icon'			=> 'drplus-icon-personalcard-bold',
													'tag'			=> 'h3',
													'title'			=> sprintf( esc_html__( "The services of %s Hospital", 'drplus' ), get_the_title() ),
													'aria-label'	=> sprintf( esc_html__( "The services of %s Hospital", 'drplus' ), get_the_title() ),
													'classes'		=> ['single-section-title'],
													'link'			=> '#services',
												] );
												?>
	
												<div class="single-section-content" role="group">
													<?php
													foreach( $hospital_settings['services'] as $service ) {
														if( empty( $service['title'] ) ) continue;
														if( $options['seo-enable-hospital-schema'] ) {
															$schema['makesOffer']['itemOffered'][] = [
																'@type'			=> 'MedicalProcedure',
																'name'			=> $service['title'],
																'description'	=> $service['description'],
															];
														}
														get_template_part( "templates/hospital/services", null, [
															'title'			=> $service['title'],
															'description'	=> $service['description'],
														] );
													}
													?>
												</div>
											</section>
										<?php } ?>

										<?php if( $hospital_settings['address'] ) { ?>
											<section id="location" class="single-section" role="region">
												<?php
												SectionTitle::view( [
													'icon'			=> 'drplus-icon-hospital-pin',
													'tag'			=> 'h3',
													'title'			=> sprintf( esc_html__( "The address of %s Hospital", 'drplus' ), get_the_title() ),
													'aria-label'	=> sprintf( esc_html__( "The address of %s Hospital", 'drplus' ), get_the_title() ),
													'classes'		=> ['single-section-title'],
													'link'			=> '#location',
												] );
												?>
	
												<div class="single-section-content" role="application" aria-label="<?php esc_attr_e( "Location", 'drplus' ) ?>">
													<?php if( Utils::to_bool( $options['single_hospital_show_map'] ) && $hospital_settings['map_address'] ) { ?>
														<div class="hospital-map">
														<?php
														echo '<iframe width="100%" src="' . $hospital_settings['map_address'] . '" loading="lazy" allowfullscreen></iframe>';
														?>
														</div>
													<?php } ?>
													
													<div class="hospital-bottom-map">
														<a href="<?php echo esc_url( $hospital_settings['map_address'] ) ?>" target="map-popup-iframe" class="hospital-map-address-link map-popup-opener" data-title="<?php echo esc_attr( $post_title ) ?>">
															<?php
															SimpleIcon::view( [
																'icon'	=> 'drplus-icon-location-fill',
															] );
															?>
															<address class="hospital-map-address"><?php echo esc_html( $hospital_settings['address'] ) ?></address>
														</a>

														<?php if( Utils::to_bool( $options['single_hospital_show_map'] ) && $hospital_settings['map_address'] ) { ?>
															<a href="<?php echo esc_url( $hospital_settings['map_address'] ) ?>" target="map-popup-iframe" class="hospital-map-open map-popup-opener" data-title="<?php echo esc_attr( $post_title ) ?>">
																<i class="drplus-icon-routing"></i>
																<span class="hospital-map-open-text"><?php esc_html_e( 'Show on map', 'drplus' ) ?></span>
															</a>
														<?php } ?>
													</div>
												</div>
											</section>
										<?php } ?>
									</div>

									<?php
									if( $show_sidebar ) {
										get_sidebar( 'single_hospital' );
									}
									?>
								</div>

								<?php if( $options['single_hospital_show_specialists'] && !$hospital_specialists->isEmpty() ) { ?>
									<div class="row hospital-specialists-row">
										<div class="col-12 hospital-specialists">
											<?php
											$display_settings = [
												'desktop_slider'		=> true,
												'desktop_slides_type'	=> 'count',
												'desktop_slides'		=> 5,
												'desktop_slides_space'	=> 16,
												'desktop_cols'			=> 5,
												'desktop_gap'			=> 16,
												
												'tablet_slider'			=> true,
												'tablet_slides_type'	=> 'auto',
												'tablet_slides_space'	=> 16,
												'tablet_cols'			=> 2,
												'tablet_gap'			=> 16,

												'mobile_slider'			=> true,
												'mobile_slides_type'	=> 'auto',
												'mobile_slides_space'	=> 16,
												'mobile_cols'			=> 1,
												'mobile_gap'			=> 16,
											];
											$display_attributes = Elementor::get_display_attributes( $display_settings, true );

											$attributes = [
												'class'	=> array_merge( [
													'drplus-slider-wrap',
													'specialists-slider-wrap',
												], $display_attributes['wrap_classes'] ),
												'data-settings'	=> $display_attributes['args'],
												'style'			=> $display_attributes['style'],
											];
											echo '<div ' . Utils::get_html_attributes( $attributes ) . '>';
												echo '<div class="drplus-slider-head specialists-slider-head">';
													SectionTitle::view( [
														'icon'		=> 'drplus-icon-heart-smile',
														'title'		=> esc_html__( "Hospital specialists", 'drplus' ),
														'nav_btns'	=> true,
													] );
												echo '</div>';

												UtilsSpecialists::list_html( [
													'specialists'	=> $hospital_specialists,
													'settings'		=> $display_settings,
												] );
											echo '</div>';
											?>
										</div>
									</div>
								<?php } ?>
							</article>
						</div>
					</div>
				</div>

				<?php comments_template(); ?>
			</div>
		</main>
		<?php if( $options['seo-enable-hospital-schema'] ) { ?>
			<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ?></script>
		<?php } ?>
	</div>
	<?php
endwhile;

get_footer();