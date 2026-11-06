<?php
use DrPlus\Model\Specialists;
use DrPlus\Model\SpecialistSpecialitiesRel as ModelSpecialistSpecialitiesRel;

use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Options;
use DrPlus\Utils\SpecialistInsurancesRel;
use DrPlus\Utils\SpecialistSpecialitiesRel;
use DrPlus\Utils\UtilsSpecialists;

if( !defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single-specialist' ) ) {
	// Elementor rendered the template
} else {
	$prefix = "specialist_";
	$primary_classes = ['content-area', 'content-area-empty', 'site-content'];

	$sitename = get_bloginfo( 'name' );

	$options = Options::get_options( [
		'insurance'	=> true,

		'single_specialist_title_tag'							=> 'h1',
		'single_specialist_subtitle_tag'						=> 'h2',
		'single_specialist_sections_tag'						=> 'h3',
		'single_specialist_show_breadcrumb'						=> true,
		'single_specialist_show_reviews'						=> true,
		'single_specialist_show_reviews_stars'					=> true,
		'single_specialist_show_specialist_code'				=> true,
		'single_specialist_show_reserve_btn'					=> true,
		'single_specialist_show_services'						=> true,
		'single_specialist_show_insurances'						=> true,
		'single_specialist_show_offices'						=> true,
		'single_specialist_show_patients_review_stat'			=> true,
		'single_specialist_show_online_consultation_stat'		=> true,
		'single_specialist_show_visits_count_stat'				=> true,
		'single_specialist_show_articles_stat'					=> true,
		'single_specialist_show_introduction'					=> true,
		'single_specialist_limit_introduction_height'			=> false,
		'single_specialist_introduction_limited_height'			=> [
			'height'	=> '400px'
		],
		'single_specialist_show_certificates'					=> true,
		'single_specialist_show_certificates_verified'			=> true,
		'single_specialist_certificates_verified_text'			=> sprintf( esc_html__( "All of {name}'s credentials have been verified by %s", 'drplus' ), $sitename ),
		'single_specialist_show_faqs'							=> true,
		'single_specialist_show_related_specialists'			=> true,
		'specialist-code-label'									=> esc_html__( 'Medical system number', 'drplus' ),
		'single_specialist_not_available_reserve_text'			=> esc_html__( 'Online appointment booking is not yet available for {name}', 'drplus' ),
		'single_specialist_related_specialists_name_tag'		=> 'h3',
		'single_specialist_related_specialists_short_bio_tag'	=> 'div',
		'single_specialist_related_specialists_verified_text'	=> sprintf( esc_html__( 'Verified by %s', 'drplus' ), $sitename ),

		'offline_reserve_time_text'				=> esc_html__( 'Book an appointment', 'drplus' ),
		'online_reserve_time_text'				=> esc_html__( 'Request Consultation', 'drplus' ),

		'onboard-info-field-subtitle-enabled'	=> true,

		'seo-enable-specialist-schema'			=> true,
	] );

	$post_classes = ['entry-content', $prefix . "article"];
	while ( have_posts() ) :
		the_post();
		$specialist = Specialists::query()->where( 'post_id', get_the_ID() )->first();
		if( empty( $specialist ) ) {
			wp_redirect( home_url() ); die;
		}

		$avatar_url = get_avatar_url( $specialist->user_id, [
			'size'	=> 132
		] );

		$stats = [];
		if( Utils::to_bool( $options['single_specialist_show_patients_review_stat'] ) && comments_open() ) {
			$comments = get_comments( [
				'fields'		=> 'ids',
				'post_id'		=> get_the_ID(),
				'meta_key'		=> '_drplus_patient_review',
				'meta_value'	=> true,
				'status'		=> 'approve',
			] );
			$comments_count = count( $comments );
			$patient_score = UtilsSpecialists::get_specialist_patients_score();
			$stats['patients_review'] = [
				'title'	=> esc_html__( 'Patient satisfaction', 'drplus' ),
				'value'	=> !empty( $patient_score ) ? round( $patient_score ) . "%" : esc_html__( 'No reviews', 'drplus' ),
				'icon'	=> 'drplus-icon-like'
			];
		}
		if( Utils::to_bool( $options['single_specialist_show_online_consultation_stat'] ) ) {
			$consultation_duration = Booking::get_specialist_consultations_duration( $specialist->id, ['completed'] );
			$stats['online_consultation'] = [
				'title'	=> esc_html__( 'Online consultation', 'drplus' ),
				'value'	=> $consultation_duration,
				'icon'	=> 'drplus-icon-headphone'
			];
		}
		if( Utils::to_bool( $options['single_specialist_show_visits_count_stat'] ) ) {
			$appointments_number = Booking::get_specialist_appointments_count( $specialist->id );
			$stats['visits_count'] = [
				'title'	=> esc_html__( 'Visits count', 'drplus' ),
				'value'	=> sprintf( esc_html__( '%s people', 'drplus' ), number_format_i18n( $appointments_number, 0 ) ),
				'icon'	=> 'drplus-icon-user-check'
			];
		}
		if( Utils::to_bool( $options['single_specialist_show_articles_stat'] ) ) {
			$stats['articles'] = [
				'title'	=> esc_html__( 'Number of articles', 'drplus' ),
				'value'	=> !empty( $specialist->meta['articles'] ) ? number_format_i18n( $specialist->meta['articles'], 0 ) . ( $specialist->meta['articles'] > 0 ? "+" : '' ) : 0,
				'icon'	=> 'drplus-icon-document-text'
			];
		}
		if( Utils::to_bool( $options['single_specialist_show_faqs'] ) && !empty( $specialist->meta['faqs'] ) ) {
			$faqs = [];
			foreach( $specialist->meta['faqs'] as $faq ) {
				if( empty( $faq['question'] ) || empty( $faq['answer'] ) ) continue;
				$faqs[] = [
					'title'	=> $faq['question'],
					'text'	=> $faq['answer'],
				];
			}
		}

		$specialities = SpecialistSpecialitiesRel::get_user_specialities( $specialist->user_id );
		$specialities = $specialities->pluck( 'speciality_id' );

		if( Utils::to_bool( $options['single_specialist_show_related_specialists'] ) ) {
			if( !empty( $specialities ) ) {
				// CACHE

				// Find related specialists

				// First: Get the specialists user ids from specialities rel table
				$specialists_table = Specialists::tableName();
				$specialities_table = ModelSpecialistSpecialitiesRel::tableName();
				$related_specialists_user_ids = ModelSpecialistSpecialitiesRel::query()
					->select( "{$specialities_table}.user_id" )
					->distinct()
					->leftJoin( $specialists_table, "{$specialists_table}.user_id", '=', "{$specialities_table}.user_id" )
					->whereNot( "{$specialities_table}.user_id", $specialist->user_id )
					->whereIn( "{$specialities_table}.speciality_id", $specialities )
					->limit( 8 )
					->get()
					->pluck( 'user_id' );
				if( !empty( $related_specialists_user_ids ) ) {
					$related_specialists = Specialists::query()->whereIn( 'user_id', $related_specialists_user_ids )->get();
				}
			}
		}

		// Get specialities name
		$specialities = get_posts( [
			'include'	=> $specialities,
			'post_type'	=> 'speciality'
		] );

		if( $options['seo-enable-specialist-schema'] ) {

			// Create schema for the specialist
			$specialist_schema = [
				"@context"	=> "https://schema.org",
				"@type"		=> "Physician",
				"name"		=> $specialist->display_name,
				"specialty"	=> implode(', ', wp_list_pluck( $specialities, 'post_title' )),
			];
		
			if( !empty( $specialist->meta['seo_about_same_as'] ) ) {
				$specialist_schema['sameAs'] = explode( PHP_EOL, $specialist->meta['seo_about_same_as'] );
			}
		
			if( $options['onboard-info-field-subtitle-enabled'] && !empty( $specialist->subtitle ) ) {
				$specialist_schema['subtitle'] = $specialist->subtitle;
			}
		
			if( !empty( $specialist->avatar ) ) {
				$specialist_schema['image'] = $avatar_url;
			}
		
			// Add schema for services
			$services = [];
			$services_schema = [];
			if( Utils::to_bool( $options['single_specialist_show_services'] ) && !empty( $specialist->meta['services'] ) ) {
				foreach( $specialist->meta['services'] as $service ) {
					$services_schema[] = [
						"@type" => "Service",
						"name" => $service['title'],
						"description" => $service['desc'] ?? "",
					];
				}
				$specialist_schema['offers'] = $services_schema;
			}

			// Add schema for FAQs
			$faqs_schema = [];
			if( Utils::to_bool( $options['single_specialist_show_faqs'] ) && !empty( $specialist->faqs ) ) {
				foreach( $specialist->faqs as $faq ) {
					$faqs_schema[] = [
						"@type"				=> "Question",
						"name"				=> $faq['question'],
						"acceptedAnswer"	=> [
							"@type"			=> "Answer",
							"text"			=> $faq['answer'],
						],
					];
				}
				$specialist_schema['faqPage'] = [
					"@type"			=> "FAQPage",
					"mainEntity"	=> $faqs_schema
				];
			}

			// Add schema for office locations
			$offices_schema = [];
		}

		$specialist_offices = [];
		$specialist_locations = get_the_terms( get_the_ID(), 'location' );
		if( Utils::to_bool( $options['single_specialist_show_offices'] ) && !empty( $specialist->offices ) ) {
			foreach( $specialist->offices as $office ) {
				if( $office['type'] == 'consultation' ) continue;

				// Get office image
				if( $office['type'] == 'hospital' ) {
					$office['image'] = get_the_post_thumbnail_url( $office['id'] );
				} else if( !empty( $office['image'] ) ) {
					$office['image'] = wp_get_attachment_image_url( $office['image'] );
				}
				if( empty( $office['image'] ) ) {
					$office['image'] = DRPLUS_URI . 'assets/images/hospital-placeholder.webp';
				}

				if( $office['type'] == 'hospital' ) {
					$hospital_settings = Hospital::get_options( $office['id'] );
					$office['name'] = get_the_title( $office['id'] );
					$office['phone'] = $hospital_settings['phones'][0]['phone'] ?? "";
					$office_phone = $office['phone'];
					$office['address'] = $hospital_settings['address'];
					$office['map_url'] = $hospital_settings['map_address'];
					$office['province'] = $hospital_settings['province'];
					$office['city'] = $hospital_settings['city'];
				} else {
					$office_phone = explode( PHP_EOL, $office['phone'] );
					if( !empty( $office_phone ) ) $office_phone = Utils::convert_chars( $office_phone[0] );
					
					if( !empty( $specialist_locations ) ) {
						if( !empty( $office['province'] ) ) {
							foreach( $specialist_locations as $location ) {
								if( $location->term_id == $office['province'] ) {
									$office['province_id'] = $office['province'];
									$office['province'] = $location->name;
									break;
								}
							}
						}
						if( !empty( $office['city'] ) ) {
							foreach( $specialist_locations as $location ) {
								if( $location->term_id == $office['city'] ) {
									$office['city_id'] = $office['city'];
									$office['city'] = $location->name;
									break;
								}
							}
						}
					}
				}
				$specialist_offices[] = $office;
				if( $options['seo-enable-specialist-schema'] ) {
					$office_schema = [
						"@type"		=> "Place",
						"name"		=> $office['name'],
						"address"	=> [
							"@type"				=> "PostalAddress",
							"streetAddress"		=> $office['address'],
							"addressLocality"	=> $office['city'],
							"addressCountry"	=> "IR",
						],
					];
					if( !empty( $office['province'] ) ) {
						$office_schema['address']['addressRegion'] = $office['province'];
					}
					if( !empty( $office_phone ) ) {
						$office_schema['telephone'] = $office_phone;
					}
					$offices_schema[] = $office_schema;
					unset( $office_schema, $office_phone );
					if( empty( $specialist_schema['hasMap'] ) && !empty( $office['map_url'] ) ) {
						$specialist_schema['hasMap'] = $office['map_url'];
					}
					if( empty( $specialist_schema['telephone'] ) && !empty( $office['phone'] ) ) {
						$specialist_schema['telephone'] = $office['phone'];
					}
				}
			}

			if( $options['seo-enable-specialist-schema'] ) {
				$specialist_schema['address'] = $offices_schema;
			}

			$specialist_schema = apply_filters( 'drplus/specialist/single/schema', $specialist_schema, $specialist );
		}

		if( $options['insurance'] && $options['single_specialist_show_insurances'] && !$specialist->insurances->isEmpty() ) {
			$specialist_insurances = SpecialistInsurancesRel::get_user_insurances( $specialist->user_id );
			$insurances = [];
			foreach( $specialist_insurances as $insurance ) {
				$insurance_term = get_term( $insurance->insurance_id, 'insurance' );
				if( empty( $insurance_term ) ) continue;
				$insurances[] = [
					'name' => $insurance_term->name,
					'icon'	=> get_term_meta( $insurance->insurance_id, 'icon', true ),
				];
			}
		}

		?>
		<div id="page-body" class="page-width">
			<main id="page-main">
				
				<?php
				if( Utils::to_bool( $options['single_specialist_show_breadcrumb'] ) ) {
					drplus_breadcrumb();
				}
				?>

				<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
					<div id="post-content" class="row">
						<div class="entry-container col-12">
							<div id="page-content" class="site-content single" role="main">
								<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?> aria-labelledby="<?php echo $prefix ?>name" itemscope itemtype="https://schema.org/Person">
									<?php
									get_template_part( 'templates/specialists/single/template-specialists-single-sidebar', null, [
										'prefix'				=> $prefix,
										'specialist'			=> $specialist,
										'options'				=> $options,
										'avatar_url'			=> $avatar_url,
										'comments_count'		=> $comments_count ?? 0,
										'specialist_offices'	=> $specialist_offices,
										'insurances'			=> $insurances ?? [],
									] );
									get_template_part( 'templates/specialists/single/template-specialists-single-main', null, [
										'prefix'		=> $prefix,
										'specialist'	=> $specialist,
										'options'		=> $options,
										'stats'			=> $stats,					
										'faqs'			=> $faqs ?? []
									] );
									?>
								</article>
							</div>
						</div>
					</div>
					<?php
					get_template_part( 'templates/specialists/single/template-specialists-single-related', null, [
						'prefix'					=> $prefix,
						'specialist'				=> $specialist,
						'options'					=> $options,
						'related_specialists'		=> $related_specialists ?? [],
						'section_title_settings'	=> [],
						'settings'					=> [],
					] );
					?>
					
				</div>
			</main>
			<?php if( $options['seo-enable-specialist-schema'] ) { ?>
				<script type="application/ld+json"><?php echo wp_json_encode( $specialist_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ?></script>
			<?php } ?>
		</div>
		<?php
	endwhile;
}

get_footer();