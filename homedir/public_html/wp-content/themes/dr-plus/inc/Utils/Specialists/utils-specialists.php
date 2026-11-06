<?php
namespace DrPlus\Utils;

use DrPlus\Cache\SpecialistCache;
use DrPlus\Model\SpecialistHospitalsRel as ModelSpecialistHospitalsRel;
use DrPlus\Model\SpecialistInsurancesRel as ModelSpecialistInsurancesRel;
use DrPlus\Model\Specialists;
use DrPlus\Model\SpecialistSpecialitiesRel as ModelSpecialistSpecialitiesRel;
use DrPlus\Model\Times;
use DrPlus\SMS\SMS;
use DrPlus\Utils;
use DrPlus\Utils\Search;
use DrPlus\Utils\SpecialistSpecialitiesRel;
use DrPlus\Utils\SpecialistHospitalsRel;
use DrPlus\Utils\SpecialistInsurancesRel;
use DrPlus\Utils\SMS as SMSUtils;

class UtilsSpecialists extends Utils {
	private static $hospitals_specialists = [];

	private static $specialists = []; // cache
	private static $specialists_by_user_id = []; // cache

	public static function get_by_user_id( $user_id = 0, $where = [] ) {
		$user_id = parent::get_user_id( $user_id );
		if( empty( $user_id ) ) return;

		// Get from local cache
		if( !empty( self::$specialists_by_user_id[$user_id] ) ) {
			return self::$specialists_by_user_id[$user_id];
		}

		$specialist = Specialists::query()->where( 'user_id', $user_id );
		if( $where ) {
			$specialist = $specialist->where( $where );
		}
		$specialist = $specialist->first();
		if( empty( $specialist ) ) return;

		if( !empty( $specialist->online_visit ) && !empty( $specialist->offices ) ) {
			$specialist->offices = self::inject_consultation_office( $specialist->offices, Booking::consultation_offices( true ) );
		}

		self::$specialists[$specialist->id] = $specialist;
		self::$specialists_by_user_id[$specialist->user_id] = $specialist;

		return $specialist;
	}

	public static function get_user_id_by_specialist_id( $specialist_id ) {
		$specialist = Specialists::query()->select( ['user_id'] )->where( 'id', $specialist_id )->first();
		if( !empty( $specialist ) ) {
			return $specialist->user_id;
		}
		return 0;
	}

	public static function is_user_specialist( $user_id = 0, $only_active = false ) {
		$user_id = parent::get_user_id( $user_id );
		if( !$user_id ) return;

		$where = [];
		if( $only_active ) {
			$where['status'] = 'active';
		}
		$specialist = self::get_by_user_id( $user_id, $where );
		return !empty( $specialist );
	}

	public static function save( array $data, int $sid = 0, $old_specialist = null ) {
		/**
		 * If you use `print_r()` or `die` before a `COMMIT` query, the database does not change.
		 */
		if( empty( $data ) || empty( $data['user_id'] ) ) {
			return new \WP_Error( 'user_id_is_empty', __( 'Please select a user', 'drplus' ) );
		}
		$original_data = $data;

		// print_r( $data ); die;

		$new = !$sid;
		$user_id = parent::convert_chars( $data['user_id'], true, 'absint' );

		// Reject the specialist
		if( !empty( $data['status'] ) && sanitize_text_field( $data['status'] ) == 'rejected' ) { // Rejected
			$data = [
				'status'	=> 'rejected',
				'reject'	=> $data['reject'],
			];
			if( $new ) { // Add new specialist and reject
				$specialist = new Specialists( $data );
				$specialist->save();
				do_action( 'drplus/specialist/created', $specialist, $original_data, $data );
			} else { // Update the reject data
				$specialist = Specialists::find( $sid );
				$specialist->fill( $data );
				$specialist->save();

				do_action( 'drplus/specialist/updated', $specialist, $original_data, $data );
			}
			do_action( 'drplus/specialist/saved', $specialist, $original_data, $data );
			return $sid;
		}
		if( isset( $data['reject'] ) ) {
			$data['reject'] = '';
		}

		$options = Options::get_options( [
			'use-outside-iran'	=> false,
			'insurance'			=> true,
			'sms'				=> true,
		] );
		$use_outside_iran = $options['use-outside-iran'];

		if( empty( $old_specialist ) ) {
			$old_specialist = UtilsSpecialists::get_by_user_id( $user_id );
			if( empty( $old_specialist ) ) {
				$old_specialist = new Specialists();
			}
		}
		if( empty( $old_specialist->meta ) ) {
			$old_specialist->meta = [];
		} else {
			if( parent::is_json( $old_specialist->meta ) ) {
				$old_specialist->meta = json_decode( $old_specialist->meta, true );
			}
			if( is_scalar( $old_specialist->meta ) ) {
				$old_specialist->meta = [];
			}
		}

		// Check user before saving and process the data
		$specialist = new Specialists;
		if( !$new ) {
			$specialist = $specialist->find( $sid );

			// Show error message if specialist not found
			if( empty( $specialist ) ) {
				return new \WP_Error( 'specialist_not_found', __( 'Specialist not found', 'drplus' ) );
			}
		}

		// Check the slug
		if( isset( $data['slug'] ) ) {
			if( empty( $data['slug'] ) ) {
				unset( $data['slug'] );
			} else {
				// Check for duplicate slug
				$slug = parent::convert_chars( $data['slug'], true, 'sanitize_title_with_dashes' );
				$find_by_slug = Specialists::query()->where( 'slug', $slug )->whereNot( 'id', $sid )->first();
				if( !empty( $find_by_slug ) ) {
					return new \WP_Error( 'duplicate_slug', __( 'The slug is duplicated.', 'drplus' ) );
				}
			}
		}

		if( !$new ) {
			$data = parent::unset( $data, ['user_id'] );
		}

		if( $data['section'] == 'personal' && empty( $data['is_onboard'] ) && !empty( $data['status'] ) ) {
			$old_status = $old_specialist->status ?? '';
			if( !$new && $data['status'] != $old_status ) {
				// Send sms
				if( $options['sms'] && !$use_outside_iran ) {
					$sms_settings = SMSUtils::get_specialist_panel_settings();
					if( $sms_settings['settings']['change_status'][$data['status']]['enabled'] ?? false && !empty( $data['mobile'] ) ) {
						$statuses = self::statuses();
						$sms_vars = [
							'user_fullname'		=> "{$data['first_name']} {$data['last_name']}",
							'old_status'		=> $statuses[$old_status],
							'new_status'		=> $statuses[$data['status']],
						];
						SMS::send( $data['mobile'], "specialist_panel.change_status.{$data['status']}", $sms_vars );
					}
				}
			}

			if( !isset( $data['is_verified'] ) ) {
				$data['is_verified'] = 0;
			}
		}

		// Sanitize and validate some info
		if( !empty( $data['nid'] ) ) {
			if( !Validators::id_code( $data['nid'] ) ) {
				return new \WP_Error( 'invalid_nid', __( 'Invalid national ID', 'drplus' ) );
			}
		}
		if( isset( $data['meta'] ) ) {
			if( !empty( $data['meta']['card_number'] ) ) {
				if( !Validators::card_number( $data['meta']['card_number'] ) ) {
					return new \WP_Error( 'invalid_card_number', __( 'Invalid card number', 'drplus' ) );
				}
				$data['meta']['card_number'] = $use_outside_iran ? $data['meta']['card_number'] : Sanitizers::card_number( $data['meta']['card_number'] );
			}

			if( !empty( $data['meta']['shaba_number'] ) ) {
				if( !Validators::shaba_number( $data['meta']['shaba_number'] ) ) {
					return new \WP_Error( 'invalid_shaba_number', __( 'Invalid shaba number', 'drplus' ) );
				}
				$data['meta']['shaba_number'] = $use_outside_iran ? $data['meta']['shaba_number'] : Sanitizers::shaba_number( $data['meta']['shaba_number'] );
			}

			if( !empty( $data['meta']['seo_about_same_as'] ) ) {
				$data['meta']['seo_about_same_as'] = Utils::convert_chars( $data['meta']['seo_about_same_as'], 'sanitize_textarea_field' );
			}
		}

		// Get all online visit offices
		$online_visit_offices = Booking::consultation_offices();

		if( $data['section'] == 'reserve' && empty( $_GET['office'] ) ) {
			$data['offline_visit'] = !empty( $data['offline_visit'] );
			$data['online_visit'] = !empty( $data['online_visit'] );
		}

		global $wpdb;

		$wpdb->query( "START TRANSACTION" );

		// Update user meta and user data
		$user_data_and_meta = ['first_name', 'last_name', 'birthday', 'specialist_code', 'nid', 'mobile', 'gender', 'avatar', 'email'];
		User::update_user( $data, $user_id, false );
		// Remove meta and user data keys to pass other data to custom tables
		$data = parent::unset( $data, $user_data_and_meta );

		// Save specialist
		$specialist_table_data = parent::extract( $data, $specialist->getFillable() );
		if( isset( $specialist_table_data['about'] ) ) {
			$specialist_table_data['about'] = wp_kses_post( $specialist_table_data['about'] );
		}
		
		if( !empty( $specialist_table_data['meta']['services'] ) && is_array( $specialist_table_data['meta']['services'] ) ) {
			foreach( $specialist_table_data['meta']['services'] as $index => $service_item ) {
				$specialist_table_data['meta']['services'][$index] = Utils::remove_empty_indexes( $service_item );
			}
			$specialist_table_data['meta']['services'] = Utils::remove_empty_indexes( $specialist_table_data['meta']['services'], true );
		}
		if( !empty( $specialist_table_data['meta']['faqs'] ) && is_array( $specialist_table_data['meta']['faqs'] ) ) {
			foreach( $specialist_table_data['meta']['faqs'] as $index => $service_item ) {
				$specialist_table_data['meta']['faqs'][$index] = Utils::remove_empty_indexes( $service_item );
			}
			$specialist_table_data['meta']['faqs'] = Utils::remove_empty_indexes( $specialist_table_data['meta']['faqs'], true );
		}
		if( !empty( $specialist_table_data['meta']['certificates'] ) && is_array( $specialist_table_data['meta']['certificates'] ) ) {
			foreach( $specialist_table_data['meta']['certificates'] as $index => $service_item ) {
				$specialist_table_data['meta']['certificates'][$index] = Utils::remove_empty_indexes( $service_item );
			}
			$specialist_table_data['meta']['certificates'] = Utils::remove_empty_indexes( $specialist_table_data['meta']['certificates'], true );
		}
		if( $data['section'] == 'services' && empty( $specialist_table_data['meta']['services'] ) ) $specialist_table_data['meta']['services'] = [];
		if( $data['section'] == 'services' && empty( $specialist_table_data['meta']['faqs'] ) ) $specialist_table_data['meta']['faqs'] = [];
		if( $data['section'] == 'certificates' && empty( $specialist_table_data['meta']['certificates'] ) ) $specialist_table_data['meta']['certificates'] = [];

		if( isset( $specialist_table_data['meta'] ) ) {
			if( is_array( $specialist_table_data['meta'] ) ) {
				$specialist_table_data['meta'] = array_merge( $old_specialist->meta, $specialist_table_data['meta'] );
			} else {
				$specialist_table_data['meta'] = [];
			}
		}

		if( $data['section'] == 'offices' && empty( $specialist_table_data['offices'] ) ) $specialist_table_data['offices'] = [];
		
		if( isset( $specialist_table_data['offices'] ) ) {
			$main_office = parent::convert_chars( $data['main_office'] ?? '' );
			$offices = [];
			$old_offices = $old_specialist->offices;

			$old_offices_reserve_data = []; // This will store the visit_time and visit_price of each office to combine with office info in the next
			if( !empty( $old_offices ) ) {
				foreach( $old_offices as $old_office ) {
					$old_offices_reserve_data[$old_office['id']] = [
						'visit_time'			=> $old_office['visit_time'] ?? "",
						'visit_price'			=> $old_office['visit_price'] ?? "",
						'enable_booking'		=> $old_office['enable_booking'] ?? 0,
						'max_booking_days'		=> $old_office['max_booking_days'] ?? "",
						'min_time_before_book'	=> $old_office['min_time_before_book'] ?? "",
						'custom_off_days'		=> $old_office['custom_off_days'] ?? [],
						'main'					=> false,
					];
				}
			}
			if( isset( $specialist_table_data['offices']['hospitals'] ) ) {
				foreach( $specialist_table_data['offices']['hospitals'] as $hospital_id ) {
					$hospital_office = [
						'type'					=> 'hospital',
						'id'					=> $hospital_id,
						'max_booking_days'		=> "",
						'min_time_before_book'	=> "",
						'custom_off_days'		=> [],
						'visit_time'			=> 0,
						'visit_price'			=> 0,
						'enable_booking'		=> 0,
					];

					if( isset( $old_offices_reserve_data[$hospital_id] ) ) {
						$hospital_office = array_merge( $hospital_office, $old_offices_reserve_data[$hospital_id] );

						if( isset( $hospital_office['custom_off_days'] ) ) {
							$hospital_office['custom_off_days'] = array_values( array_unique( $hospital_office['custom_off_days'] ) );
						}
					}

					if( $main_office == $hospital_id ) {
						$hospital_office['main'] = true;
					}
					$offices[$hospital_id] = $hospital_office;
				}
				unset( $specialist_table_data['offices']['hospitals'] );
			}
			// Store custom offices
			foreach( $specialist_table_data['offices'] as $office ) {
				if( !parent::check_requires( $office, ['name', 'address'] ) ) continue;
				$office['type'] = 'custom';
				$office['id'] = empty( $office['id'] ) ? wp_generate_uuid4() : $office['id'];

				if( isset( $old_offices_reserve_data[$office['id']] ) ) {
					$office = array_merge( $office, $old_offices_reserve_data[$office['id']] );
				}

				if( $main_office == $office['id'] ) {
					$office['main'] = true;
				}
				if( $office['map_url'] ) {
					$office['map_url'] = Utils::normalize_map_src( $office['map_url'] );
				}
				$offices[$office['id']] = $office;
			}
			if( empty( $main_office ) && !empty( $offices ) ) {
				// set first item as main
				$offices[array_key_first( $offices )]['main'] = true;
			}

			// Add consultation offices (online visit offices)
			foreach( array_keys( $online_visit_offices ) as $online_office ) {
				if( !empty( $old_offices[$online_office] ) ) {
					$offices[$online_office] = $old_offices[$online_office];
				}
			}
			$specialist_table_data['offices'] = $offices;
		}
		// Check consultation office
		if( $data['section'] == 'reserve' && empty( $_GET['office'] ) ) {
			if( isset( $specialist_table_data['online_visit'] ) ) {
				$old_offices = $old_specialist->offices;
				if( !parent::to_bool( $specialist_table_data['online_visit'] ) ) { // Online visit is off
					foreach( array_keys( $online_visit_offices ) as $online_office ) {
						if( isset( $old_offices[$online_office] ) ) {
							unset( $old_offices[$online_office] );
						}
					}
				} else { // Online visit in on
					$old_offices = self::inject_consultation_office( $old_offices, $online_visit_offices );
				}
				$specialist_table_data['offices'] = $old_offices;
			}
		}

		$specialist_table_data = apply_filters( "drplus/specialist/save/{$data['section']}", $specialist_table_data, $specialist );

		$specialist->fill( $specialist_table_data );
		$specialist->save();
		$sid = $specialist->id;

		// Remove specialist data from $data to pass other data to other tables
		$data = parent::unset( $data, $specialist->getFillable() );

		// Save specialities
		// Get previous specialities
		$old_specialities = SpecialistSpecialitiesRel::get_user_specialities( $user_id );
		$old_specialities_ids = $old_specialities->pluck( 'speciality_id' );
		if( !empty( $data['specialities'] ) && is_array( $data['specialities'] ) ) {
			$specialities = $data['specialities'];
			unset( $data['specialities'] );

			if( !$new ) {
				// remove not selected specialities
				$should_remove = [];
				foreach( $old_specialities as $old_speciality ) {
					if( !in_array( $old_speciality['speciality_id'], $specialities ) ) {
						$should_remove[] = $old_speciality['id'];
					}
				}
				if( !empty( $should_remove ) ) {
					ModelSpecialistSpecialitiesRel::query()->whereIn( 'id', $should_remove )->delete();
				}
				
				// Add new specialities
				$should_add = array_diff( $specialities, $old_specialities_ids );				
				if( !empty( $should_add ) ) {
					SpecialistSpecialitiesRel::add_user_specialities( $user_id, $should_add );
				}
			} else {
				// Add new specialities
				SpecialistSpecialitiesRel::add_user_specialities( $user_id, $specialities );
			}
		} else {
			if( $data['section'] == 'services' ) {
				foreach( $old_specialities as $old_speciality ) {
					$old_speciality->delete();
				}
			}
		}

		// Save hospital relations
		// Get previous hospitals
		$old_hospitals = SpecialistHospitalsRel::get_user_hospitals( $user_id );
		$old_hospitals_ids = $old_hospitals->pluck( 'hospital_id' );
		if( isset( $specialist_table_data['offices'] ) && is_array( $specialist_table_data['offices'] ) ) {
			$hospitals = [];
			foreach( $specialist_table_data['offices'] as $office ) {
				if( !is_array( $office ) || !parent::check_requires( $office, ['type', 'id'] ) || $office['type'] != 'hospital' ) continue;

				$hospitals[] = $office['id'];
			}
			
			if( !$new ) {
				// remove not selected hospitals
				$should_remove = [];
				foreach( $old_hospitals as $old_hospital ) {
					if( !in_array( $old_hospital['hospital_id'], $hospitals ) ) {
						$should_remove[] = $old_hospital['id'];
					}
				}
				if( !empty( $should_remove ) ) {
					ModelSpecialistHospitalsRel::query()->whereIn( 'id', $should_remove )->delete();
					Times::query()->whereIn( 'office', $should_remove )->where( 'user_id', $user_id )->delete();
				}
				
				// Add new hospitals
				$should_add = array_diff( $hospitals, $old_hospitals_ids );
				if( !empty( $should_add ) ) {
					SpecialistHospitalsRel::add_user_hospitals( $user_id, $should_add );
				}
			} else {
				// Add new hospitals
				SpecialistHospitalsRel::add_user_hospitals( $user_id, $hospitals );
			}
		} else {
			if( $data['section'] == 'offices' ) {
				foreach( $old_hospitals as $old_hospital ) {
					$old_hospital->delete();
				}
			}
		}

		if( $data['section'] == 'offices' && isset( $specialist_table_data['offices'] ) ) {
			Location::sync_specialist_location_terms_by_user( $user_id, $specialist_table_data['offices'] );
		}

		// Save insurance relations
		if( $options['insurance'] ) {
			if( $data['section'] == 'services' && empty( $data['insurances'] ) ) $data['insurances'] = [];
			if( $data['section'] == 'insurances' && empty( $data['insurances'] ) ) $data['insurances'] = [];
			// Get previous insurances
			$old_insurances = SpecialistInsurancesRel::get_user_insurances( $user_id );
			$old_insurances_ids = $old_insurances->pluck( 'insurance_id' );
			if( isset( $data['insurances'] ) && is_array( $data['insurances'] ) ) {
				$insurances = $data['insurances'];
				if( empty( $insurances ) ) $insurances = [];
				
				if( !$new ) {
					// remove not selected insurances
					$should_remove = [];
					foreach( $old_insurances as $old_insurance ) {
						if( !in_array( $old_insurance['insurance_id'], $insurances ) ) {
							$should_remove[] = $old_insurance['id'];
						}
					}
					if( !empty( $should_remove ) ) {
						ModelSpecialistInsurancesRel::query()->whereIn( 'id', $should_remove )->delete();
					}
					
					// Add new insurances
					$should_add = array_diff( $insurances, $old_insurances_ids );				
					if( !empty( $should_add ) ) {
						SpecialistInsurancesRel::add_user_insurances( $user_id, $should_add );
					}
				} else {
					// Add new insurances
					SpecialistInsurancesRel::add_user_insurances( $user_id, $insurances );
				}
			} else {
				if( $data['section'] == 'insurances' ) {
					foreach( $old_insurances as $old_insurance ) {
						$old_insurance->delete();
					}
				}
			}
		}

		// Save times
		if( $data['section'] == 'reserve' && !empty( $_GET['office'] ) ) {
			$office_id = parent::convert_chars( $_GET['office'] );
			
			// Update visit time and visit price and other data
			$max_booking_days = parent::convert_chars( $data['max_booking_days'] ?? "" );
			$min_time_before_book = parent::convert_chars( $data['min_time_before_book'] ?? "" );
			$custom_off_days = $data['custom_off_days'] ?? [];
			if( !empty( $custom_off_days ) ) {
				foreach( $custom_off_days as $custom_off_day_key => $custom_off_day_value ) {
					$custom_off_days[$custom_off_day_key] = Utils::convert_chars( $custom_off_day_value );
				}

				$custom_off_days = array_values( array_unique( $custom_off_days ) );
			}
			$visit_price = Sanitizers::price( $data['visit_price'] );
			$visit_time = parent::convert_chars( $data['visit_time'] ?? 0, true, 'absint' );
			$specialist = self::get_by_user_id( $user_id );
			$enable_booking = parent::to_bool( parent::convert_chars( $data['enable_booking'] ?? 0 ) );
			$offices = $specialist['offices'];
			foreach( $offices as $index => $office ) {
				if( $office['id'] == $office_id ) {
					$offices[$index]['max_booking_days']		= $max_booking_days;
					$offices[$index]['min_time_before_book']	= $min_time_before_book;
					$offices[$index]['custom_off_days']			= $custom_off_days;
					$offices[$index]['visit_time']				= $visit_time;
					$offices[$index]['visit_price']				= $visit_price;
					$offices[$index]['enable_booking']			= $enable_booking;
					break;
				}
			}
			$specialist->offices = $offices;
			$specialist->save();

			// Remove all user time for current office
			Times::query()->withoutGlobalScopes()->where( [
				'office'	=> $office_id,
				'user_id'	=> $user_id,
			] )->delete();
			
			// Set new times
			$times = [];
			if( isset( $data['default_times'] ) && is_array( $data['default_times'] ) ) {
				foreach( $data['default_times'] as $time ) {
					if( empty( $time['from'] ) || empty( $time['to'] ) ) continue;
					$times[] = [
						'from'			=> $time['from'],
						'to'			=> $time['to'],
						'status'		=> $time['status'] ?? false,
						'use_default'	=> false,
						'day'			=> 9,
					];
				}
				unset( $data['default_times'] );
			}
	
			if( isset( $data['days'] ) && is_array( $data['days'] ) ) {
				foreach( $data['days'] as $day ) {
					if( parent::to_bool( $day['default_time'] ?? false ) ) {
						// Use default time
						$times[] = [
							'use_default'	=> true,
							'day'			=> $day['day_index'],
							'status'		=> $day['status'] ?? false
						];
					} else {
						if( empty( $day['times'] ) ) continue;
						foreach( $day['times'] as $time ) {
							if( empty( $time['from'] ) || empty( $time['to'] ) ) continue;
							$times[] = [
								'from'			=> $time['from'],
								'to'			=> $time['to'],
								'use_default'	=> false,
								'day'			=> $day['day_index'],
								'status'		=> $day['status'] ?? false
							];
						}
					}
				}
				unset( $data['days'] );
			}

			// Insert into DB
			foreach( $times as $time ) {
				$db_time = new Times();
				$db_time->user_id = $user_id;
				$db_time->office = $office_id;
				$db_time->day = $time['day'];
				$db_time->from = !empty( $time['from'] ) ? $time['from'] . ":00" : "00:00:00";
				$db_time->to = !empty( $time['to'] ) ? $time['to'] . ":00" : "00:00:00";
				$db_time->use_default = $time['use_default'];
				$db_time->status = $time['status'];
				$db_time->save();
			}
		}

		do_action( 'drplus/specialist/before_commit', $specialist, $original_data, $data, $user_id, $new );

		$wpdb->query( 'COMMIT' );

		if( $new ) {
			do_action( 'drplus/specialist/created', $specialist, $original_data, $data, $user_id, $new );
		} else {
			do_action( 'drplus/specialist/updated', $specialist, $original_data, $data, $user_id, $new );
		}
		do_action( 'drplus/specialist/saved', $specialist, $original_data, $data, $user_id, $new );
		
		return $sid;
	}

	public static function delete_all_specialist_data( $user_id ) {
		$specialist = Specialists::query()->where( 'user_id', $user_id )->first();
		if( empty( $specialist ) ) return;
		
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );

		// Delete from specialist table
		self::delete( $specialist->id, $specialist->post_id, false );

		// Delete hospital rel
		ModelSpecialistHospitalsRel::query()->withoutGlobalScopes()->where( 'user_id', $user_id )->delete();

		// Delete insurances rel
		ModelSpecialistInsurancesRel::query()->withoutGlobalScopes()->where( 'user_id', $user_id )->delete();

		// Delete specialities rel
		ModelSpecialistSpecialitiesRel::query()->withoutGlobalScopes()->where( 'user_id', $user_id )->delete();

		// Delete booking times data
		Times::query()->withoutGlobalScopes()->where( 'user_id', $user_id )->delete();

		$wpdb->query( 'COMMIT' );
	}

	public static function delete( int $id, int $post_id = 0, bool $soft_delete = true ) {
		$specialist = (new Specialists())->find( $id );
		if( empty( $post_id ) ) {
			$post_id = $specialist->post_id;
		}
		if( $soft_delete ) {
			$args = [
				'ID'			=> $post_id,
				'post_status'	=> 'draft',
			];
			$post_id = wp_update_post( $args, true );
			$specialist->status = 'deleted';
			$specialist->save();
		} else {
			wp_delete_post( $post_id );
			$specialist->delete();
		}

		self::delete_group_caches( [$specialist->id] );
	}

	public static function activate( int $id ) {
		$specialist = (new Specialists)->find( $id );
		$specialist->status = 'active';
		$specialist->save();
	}

	public static function deactivate( int $id ) {
		$specialist = (new Specialists)->find( $id );
		$specialist->status = 'inactive';
		$specialist->save();
	}

	public static function get_specialists_by_user_query( array $args = [] ) {
		$args['specialists'] = true;

		if( empty( $args['city'] ) ) {
			$city = Search::get_city_from_GET();
			if( !$city && is_tax( 'location' ) ) {
				$term = get_queried_object();
				$city = $term && !is_wp_error( $term ) ? $term->slug : '';
			}
			if( $city ) {
				$args['city'] = $city;
			}
		}

		$result = [
			'specialists'	=> [],
			'total'			=> 0,
		];
		$args['fields'] = 'ID';
		$limit = $args['number'];
		$paged = $args['paged'] ?? 1;
		if( !empty( $args['show_pagination'] ) ) {
			if( isset( $args['paged'] ) ) unset( $args['paged'] );
			if( isset( $args['number'] ) ) unset( $args['number'] );
		}
		$users = new \WP_User_Query( $args );
		if( $users_results = $users->get_results() ) {
			if( !empty( $args['show_pagination'] ) ) {
				$result['total'] = Specialists::query()->whereIn( 'user_id', $users_results )->count();
			}
			$limited_users = $limit > 0 ? array_slice( $users_results, ($paged -1) * $limit, $limit ) : $users_results;
			$result['specialists'] = Specialists::query()->whereIn( 'user_id', $limited_users )->orderByRaw('FIELD(user_id, ' . implode(',', $limited_users) . ')')->get();
		}

		return $result;
	}

	/**
	 * Create different lists of specialists
	 *
	 * @param array $settings Elementor settings
	 * @param array $mode Accepts: offline_visits | online_visits | all
	 * @return void HTML
	 */
	public static function list( array $settings = [], string $mode = 'all', $specialists = [] ) {
		$mode = Utils::ensure_values_in_array( $mode, ['offline_visits', 'online_visits', 'all'], 'all' );

		if( empty( $specialists ) ) {
			$query_type = parent::ensure_values_in_array( sanitize_text_field( $settings['query_type'] ), ['default', 'by_id'], 'default' );

			$include_users = $query_type == 'default' || empty( $settings['query_include_ids'] ) ? [] : array_unique( array_filter( array_map( fn( $user_id ) => parent::convert_chars( $user_id, true, 'absint' ), $settings['query_include_ids'] ) ) );

			if( is_singular( 'speciality' ) && $settings['query_type'] == 'default' && empty( $settings['specialities'] ) ) {
				$settings['specialities'] = [get_the_ID()];
			}

			if( !empty( $settings['specialities'] ) ) {
				$user_ids_by_specialities = ModelSpecialistSpecialitiesRel::query()->select( 'user_id' )->distinct()->whereIn( 'speciality_id', $settings['specialities'] )->get();
				if( !$user_ids_by_specialities->isEmpty() ) {
					$include_users = array_merge( $include_users, $user_ids_by_specialities->pluck( 'user_id' ) );
				}
			}

			$orderby = !empty( $settings['orderby'] ) ? $settings['orderby'] : 'login';
			$auto_orderby = $orderby == 'auto';
			if( $orderby === 'id' ) {
				$settings['orderby'] = 'ID';
			}
			if( $auto_orderby ) {
				$orderby = Utils::convert_chars( $_GET['orderby'] ?? 'login' );
			}
			$result = [];

			if( $auto_orderby ) {
				// orderby set on posts
				
			} else {
				// order by set on users
				$user_query_args = [
					'only_verified'			=> isset( $settings['only_verified'] ) && parent::to_bool( $settings['only_verified'] ),
					'only_offline_visits'	=> isset( $settings['only_offline_visits'] ) && parent::to_bool( $settings['only_offline_visits'] ),
					'only_online_visits'	=> isset( $settings['only_online_visits'] ) && parent::to_bool( $settings['only_online_visits'] ),
					'include'				=> array_values( array_unique( $include_users ) ),
					'number'				=> parent::convert_chars( $settings['ppp'], true, 'absint' ),
					'offset'				=> parent::convert_chars( $settings['offset'], true, 'absint' ),
					'paged'					=> is_archive() ? Archive::get_paged() : parent::convert_chars( $_GET['s-page'] ?? 1 ),
					'order'					=> !empty( $settings['order'] ) ? strtoupper( $settings['order'] ) : 'ASC',
					'orderby'				=> $orderby,
					'show_pagination'		=> isset( $settings['show_pagination'] ) && parent::to_bool( $settings['show_pagination'] ),
				];
				$user_query_args = apply_filters( 'drplus/specialists/list/user_query_args', $user_query_args, $settings, $mode ); // Specialists is empty
				$result = self::get_specialists_by_user_query( $user_query_args );
			}

			$specialists = $result['specialists'];
			$total = $result['total'];
		} else {
			$total = count( $specialists );
		}
		self::list_html( [
			'specialists'	=> $specialists,
			'total'			=> $total,
			'settings'		=> $settings,
			'mode'			=> $mode,
		] );
	}

	public static function list_html( $args ) {
		$args = parent::check_default( $args, [
			'specialists'	=> [],
			'total'			=> 0,
			'settings'		=> [
				'style'					=> 'card-1',
				'only_verified'			=> false,
				'only_offline_visits'	=> false,
				'only_online_visits'	=> false,
				'style_args'			=> [], // Pass to template part
				'show_pagination'		=> false,
			],
			'mode'			=> 'all',
			'remove_wrap'	=> false,
		], ['specialists'] );
		$settings = $args['settings'];

		if( !$args['remove_wrap'] ) {
			$display_attributes = Elementor::get_display_attributes( $settings );
		}
		$wrap_classes = ['wrapper', 'specialists', "specialists-style-{$settings['style']}"];

		$is_card = in_array( $settings['style'], ['card-1', 'card-2', 'card-3'] );
		if( $is_card ) {
			$wrap_classes[] = 'specialists-style-card';
		}

		if( isset( $settings['only_verified'] ) && parent::to_bool( $settings['only_verified'] ) ) {
			$wrap_classes[] = 'verified-specialists';
		}
		if( isset( $settings['only_offline_visits'] ) && parent::to_bool( $settings['only_offline_visits'] ) ) {
			$wrap_classes[] = 'offline-visits';
		}
		if( isset( $settings['only_online_visits'] ) && parent::to_bool( $settings['only_online_visits'] ) ) {
			$wrap_classes[] = 'online-visits';
		}
		$wrap_attrs = [
			'class'	=> $wrap_classes,
		];
		if( !$args['remove_wrap'] ) {
			$wrap_attrs['class'] = array_merge( $wrap_attrs['class'], $display_attributes['classes'] );
		}

		if( !$args['remove_wrap'] ) {
			echo '<div ' . parent::get_html_attributes( $wrap_attrs ) . '>';
		}

		foreach( $args['specialists'] as $specialist ) {
			if( empty( $specialist->id ) || $specialist->status != 'active' ) {
				continue;
			}
			$item_classes = ['specialist', "specialist-{$specialist->id}", "specialist-user-{$specialist->user_id}", 'slider-slide'];

			if( $is_card ) {
				$item_classes[] = 'specialist-card';
			}
			
			if( !empty( $specialist->offline_visit ) ) {
				$item_classes[] = 'offline-visit';
			}
			if( !empty( $specialist->online_visit ) ) {
				$item_classes[] = 'online-visit';
			}
			if( !empty( $specialist->is_verified ) ) {
				$item_classes[] = 'verified';
			}

			$item_attrs = [
				'class'		=> $item_classes,
				'data-id'	=> $specialist->user_id,
			];
			echo '<div ' . parent::get_html_attributes( $item_attrs ) . '>';
			if( $args['mode'] == 'all' ) {
				if( $specialist->offline_visit ) {
					$mode = 'offline_visits';
				} else {
					$mode = 'online_visits';
				}
			} else {
				$mode = $args['mode'];
			}
			$style_args = [
				'specialist'		=> $specialist,
				'mode'				=> $mode,
				'name-tag'			=> $settings['name-tag'] ?? 'h2',
				'short_bio-tag'		=> $settings['short_bio-tag'] ?? 'div',
				'verified-text'		=> $settings['verified-text'] ?? '',
				'show_score'		=> $settings['show_score'] ?? false,
				'reserve_btn_icon'	=> $settings['reserve_btn_icon'] ?? ''
			];
			get_template_part( 'templates/specialists/template-specialists', $settings['style'], array_merge( $style_args, $args['settings']['style_args'] ) );
			echo '</div>';
		}

		if( isset( $settings['show_pagination'] ) && parent::to_bool( $settings['show_pagination'] ) ) {
			$max_num_page = ceil( $args['total'] / parent::convert_chars( $settings['ppp'], true, 'absint' ) );
			$paged = is_archive() ? Archive::get_paged() : parent::convert_chars( $_GET['s-page'] ?? 1 );
			if( $max_num_page > 1 ) {
				get_template_part( 'templates/archives/template-archives-pagination', 'custom', [
					'max_num_pages'		=> $max_num_page,
					'paged'				=> $paged,
					'query_arg_name'	=> 's-page',
				] );
			}
		}
		
		if( !$args['remove_wrap'] ) {
			echo '</div>';
		}
	}

	/**
	 * Get specialist page link
	 *
	 * @param int|array $specialist
	 * @return string The URL of the page
	 */
	public static function get_page_link( $specialist ) : string {
		$url = home_url( '/specialist' );
		if( is_array( $specialist ) && !empty( $specialist['post_id'] ) ) {
			$post_id = $specialist['post_id'];
		}
		if( is_object( $specialist ) && !empty( $specialist->post_id ) ) {
			$post_id = $specialist->post_id;
		}

		if( is_numeric( $specialist ) ) {
			$specialist = Specialists::query()->select( 'post_id' )->where( 'id', $specialist )->limit( 1 )->first();
			$post_id = $specialist->post_id;
		}

		return !empty( $post_id ) ? get_permalink( $post_id ) : "";
	}

	/**
	 * Searches for specialists using a text query.
	 *
	 * @param string|object $text_or_query_object The search text.
	 * @param array $args Optional. Additional query arguments.
	 * @param string $return Optional. Return format: 'object' (default) for Specialist objects or 'query' for WP_Query.
	 * @return array|\WP_Query Returns results based on $return.
	 */
	public static function search( $text_or_query_object, array $args = [], string $return = 'object' ) {
		$args = parent::check_default( $args, [
			'number'				=> 5,
			'count_total'			=> false,
			'fields'				=> 'ids',
			'paged'					=> 1,
			'offset'				=> 0,
			'only_online_visits'	=> false,
			'only_offline_visits'	=> false,
			'only_verified'			=> false,
			'city'					=> '',
			'post_status'			=> 'publish',
		] );
		$fields = strtolower( $args['fields'] );

		if( is_string( $text_or_query_object ) ) {
			$posts_per_page = isset( $args['posts_per_page'] ) ? parent::convert_chars( $args['posts_per_page'], true, 'absint' ) : parent::convert_chars( $args['number'], true, 'absint' );
			$paged = max( 1, parent::convert_chars( $args['paged'], true, 'absint' ) );
			$offset = parent::convert_chars( $args['offset'], true, 'absint' );

			if( $fields === 'id' ) {
				$fields = 'ids';
			}
			if( !in_array( $fields, ['ids', 'all'] ) ) {
				$fields = 'ids';
			}

			$city_term_id = 0;
			if( !empty( $args['city'] ) ) {
				if( is_numeric( $args['city'] ) ) {
					$city_term_id = parent::convert_chars( $args['city'], true, 'absint' );
				} else {
					$city = parent::convert_chars( $args['city'], 'sanitize_title_with_dashes' );
					$city_term = get_term_by( 'slug', $city, 'location' );
					if( $city_term && !is_wp_error( $city_term ) ) {
						$city_term_id = $city_term->term_id;
					}
				}
			} else {
				$city_term_id = Search::get_city_from_GET( "term_id" );
			}

			// Base allowed posts filtered by specialist flags
			$specialists_post_ids_query = Specialists::query()
				->select( 'post_id' )
				->whereNotNull( 'post_id' )
				->where( 'status', 'active' );

			if( !empty( $args['only_online_visits'] ) ) {
				$specialists_post_ids_query->where( 'online_visit', 1 );
			}
			if( !empty( $args['only_offline_visits'] ) ) {
				$specialists_post_ids_query->where( 'offline_visit', 1 );
			}
			if( !empty( $args['only_verified'] ) ) {
				$specialists_post_ids_query->where( 'is_verified', 1 );
			}

			$allowed_post_ids = $specialists_post_ids_query->get()->pluck( 'post_id' );
			$allowed_post_ids = array_values( array_unique( array_filter( array_map( 'absint', $allowed_post_ids ) ) ) );

			if( empty( $allowed_post_ids ) ) {
				return $return === 'query' ? new \WP_Query( [ 'post_type' => 'specialist', 'post__in' => [0] ] ) : [];
			}

			$query_args = [
				'post_type'				=> 'specialist',
				's'						=> $text_or_query_object,
				'post_status'			=> $args['post_status'],
				'posts_per_page'		=> $posts_per_page,
				'paged'					=> $paged,
				'offset'				=> $offset,
				'ignore_sticky_posts'	=> true,
				'no_found_rows'			=> !$args['count_total'],
				'post__in'				=> $allowed_post_ids,
			];

			if( $fields === 'ids' ) {
				$query_args['fields'] = 'ids';
			}

			if( !empty( $args['orderby'] ) ) {
				$query_args['orderby'] = $args['orderby'];
			}
			if( !empty( $args['order'] ) ) {
				$query_args['order'] = $args['order'];
			}

			if( $city_term_id ) {
				$query_args['tax_query'] = [[
					'taxonomy'	=> 'location',
					'field'		=> 'term_id',
					'terms'		=> $city_term_id,
				]];
			}

			$query = new \WP_Query( $query_args );
		} else {
			$query = $text_or_query_object;
		}
		$post_ids = is_array( $query->posts ) && !empty( $query->posts[0] ) && is_object( $query->posts[0] ) ? wp_list_pluck( (array) $query->posts, 'ID' ) : $query->posts;
		$post_ids = array_map( 'absint', $post_ids );

		$results = [];
		if( !empty( $post_ids ) ) {
			$specialists_query = Specialists::query()
				->whereIn( 'post_id', $post_ids )
				->where( 'status', 'active' );

			if( !empty( $args['only_online_visits'] ) ) {
				$specialists_query->where( 'online_visit', 1 );
			}
			if( !empty( $args['only_offline_visits'] ) ) {
				$specialists_query->where( 'offline_visit', 1 );
			}
			if( !empty( $args['only_verified'] ) ) {
				$specialists_query->where( 'is_verified', 1 );
			}
			
			if( !empty( $post_ids ) ) {
				$specialists_query->orderByRaw( 'FIELD(post_id, ' . implode( ',', $post_ids ) . ')' );
			}

			$results = $specialists_query->get();
		}
		
		if( $return == 'query' ) {
			$query->drplus_specialists = $results;
			return $query;
		}
		return $results;
	}

	public static function get_identity_types_terms() : array {
		static $types = null;
		if( $types === null ) {
			$terms = get_terms( [
				'taxonomy'		=> 'identity_type',
				'hide_empty'	=> false,
			] );
			$types = [];
			foreach( $terms as $term ) {
				if( !$term->name ) continue;
				$types[] = [
					'id'			=> $term->term_id,
					'name'			=> $term->name,
					'description'	=> $term->description,
				];
			}
		}
		return $types;
	}

	public static function get_insurances_terms() : array {
		static $insurances = null;
		if( $insurances === null ) {
			$options = Options::get_options( [
				'insurance'	=> true,
			] );
			if( !$options['insurance'] ) {
				$insurances = [];
				return $insurances;
			}
			$terms = get_terms( [
				'taxonomy'		=> 'insurance',
				'hide_empty'	=> false,
			] );
			$insurances = [];
			foreach( $terms as $term ) {
				if( !$term->name ) continue;
				$insurances[] = [
					'id'			=> $term->term_id,
					'name'			=> $term->name,
					'description'	=> $term->description,
					'icon'			=> get_term_meta( $term->term_id, 'icon', true ),
				];
			}
		}
		return $insurances;
	}

	public static function get_identity_max_upload_size( bool $add_suffix = true, int $decimal_places = 0 ) {
		static $max_upload_size = null;
		if( $max_upload_size === null ) {
			$default = parent::get_max_upload_size();
			$options = Options::get_options( [
				'specialist_identity_max_upload_size'	=> parent::convert_bytes_to_mb( $default, false ),
			] );
			$max_upload_size = parent::convert_mb_to_bytes( $options['specialist_identity_max_upload_size'] );
			if( $max_upload_size > $default ) {
				$max_upload_size = $default;
			}
		}
		if( $add_suffix ) {
			return parent::convert_bytes_to_mb( $max_upload_size, $add_suffix, $decimal_places );
		} else {
			return $max_upload_size;
		}
	}

	public static function get_current_specialist() {
		static $specialist = null;
		if( $specialist === null ) {
			$specialist = new Specialists;
			if( is_user_logged_in() ) {
				$specialist = $specialist->where( 'user_id', get_current_user_id() )->first();
			}
		}
		return $specialist;
	}

	public static function statuses( bool $add_deleted = false ) {
		$statuses = [
			'pending'		=> __( "Pending", 'drplus' ),
			'incomplete'	=> __( "Incomplete", 'drplus' ),
			'active'		=> __( "Active", 'drplus' ),
			'inactive'		=> __( "Inactive", 'drplus' ),
			'rejected'		=> __( "Rejected", 'drplus' ),
		];
		if( $add_deleted ) {
			$statuses['deleted'] = __( "Deleted", 'drplus' );
		}
		return $statuses;
	}

	public static function get_hospital_specialists( $hospital_id = 0, int $limit = 10, int $offset = 0 ) {
		$hospital_id = parent::get_post_id( $hospital_id );

		// Local cache
		$local_cache_key = "{$hospital_id}-{$limit}-{$offset}";
		if( isset( self::$hospitals_specialists[$local_cache_key] ) ) {
			return self::$hospitals_specialists[$local_cache_key];
		}

		$specialist_hospitals_rel_table = ModelSpecialistHospitalsRel::tableName();
		$specialists_table = Specialists::tableName();

		$specialists = Specialists::query()
			->distinct()
			->select( [
					"`{$specialists_table}`.`id` as id",
					"`{$specialists_table}`.`user_id`",
					"`{$specialists_table}`.`post_id`",
					"`{$specialists_table}`.`slug`",
					"`{$specialists_table}`.`subtitle`",
					"`{$specialists_table}`.`offline_visit`",
					"`{$specialists_table}`.`online_visit`",
					"`{$specialists_table}`.`is_verified`",
					"`{$specialists_table}`.`meta`",
					"`{$specialists_table}`.`offices`",
					"`{$specialists_table}`.`status`"
				] )
			->leftJoin( $specialist_hospitals_rel_table, "`{$specialist_hospitals_rel_table}`.`user_id`", '=', "`{$specialists_table}`.`user_id`" )
			->where( "`{$specialist_hospitals_rel_table}`.`hospital_id`", $hospital_id )
			->where( "`{$specialists_table}`.`status`", 'active' )
			->limit( $limit )
			->offset( $offset )
			->get();

		self::$hospitals_specialists[$local_cache_key] = $specialists;
		return self::$hospitals_specialists[$local_cache_key];
	}

	public static function get_specialist_post_id( $specialist ) : int {
		$post_id = get_posts( [			
			'post_type'		=> 'specialist',
			'meta_query'	=> [
				[
					'key'     => '_drplus_specialist_id',
					'value'   => $specialist->id,
					'compare' => '='
				]
			],
			'post_status'	=> 'any',
			'numberposts'	=> 1,
			'fields'		=> 'ids'
		] );
		return !empty( $post_id ) ? $post_id[0] : 0;
	}

	public static function inject_consultation_office( $offices, $online_visit_offices ) {
		foreach( $online_visit_offices as $key => $data ) {
			$offices[$key] = parent::check_default( $offices[$key] ?? [], [
				'type'					=> 'consultation',
				'id'					=> $key,
				'name'					=> $data['label'],
				'max_booking_days'		=> '',
				'min_time_before_book'	=> '',
				'custom_off_days'		=> [],
				'visit_time'			=> '30',
				'visit_price'			=> '',
				'enable_booking'		=> false,
				'main'					=> false,
			] );

			if( isset( $offices[$key]['custom_off_days'] ) ) {
				$offices[$key]['custom_off_days'] = array_values( array_unique( $offices[$key]['custom_off_days'] ) );
			}
		}

		return $offices;
	}

	public static function restore( $specialist_id ) {
		$specialist = (new Specialists)->find( $specialist_id );
		$specialist->status = 'pending';
		$specialist->save();
	}

	/**
	 * Delete caches that affects on groups
	 *
	 * @param array $specialists_ids
	 * @return void
	 */
	public static function delete_group_caches( array $specialists_ids = [] ) {
		$deletable_types = [
			'get_specialists_by_user_query',
		];

		$cache = new SpecialistCache();
		$cache->delete_by( [
			'type'		=> $deletable_types,
			'object_id'	=> $specialists_ids,
		] );
	}

	public static function get_specialist_code( $specialist_user_id ) {
		$specialist_user_id = parent::get_user_id( $specialist_user_id );
		if( !$specialist_user_id ) return '';
		return get_user_meta( $specialist_user_id, 'specialist_code', true );
	}

	public static function get_specialist_from_post( $post_id = null ) {
		if( is_singular( 'specialist' ) ) {
			if( $post_id === null ) {
				$post_id = get_the_ID();
			}
			if( empty( $post_id ) ) return false;
			
			$specialist_id = get_post_meta( $post_id, '_drplus_specialist_id', true );
			if( !empty( self::$specialists[$specialist_id] ) ) return self::$specialists[$specialist_id];
	
			$specialist = Specialists::query()->where( 'id', $specialist_id )->with( 'insurances' )->first();
			self::$specialists[$specialist_id] = $specialist;
			return $specialist;
		} else if (
			class_exists( '\Elementor\Plugin' )
			&& ( \Elementor\Plugin::$instance->editor->is_edit_mode()
			|| \Elementor\Plugin::$instance->preview->is_preview_mode() )
		) {
			if( !empty( self::$specialists[array_key_first( self::$specialists )] ) ) return self::$specialists[array_key_first( self::$specialists )];

			// Get first Specialist data to show in elementor editor
			$specialist = Specialists::query()->where( ['status' => 'active'] )->limit(1)->orderBy('id', 'ASC')->with( 'insurances' )->first();
			return $specialist;
		}
	}

	public static function get_specialist_patients_score( $post_id = 0 ) {
		if( ! $post_id ) {
			$post_id = get_the_ID();
		}
		if( empty( $post_id ) ) return false;

		$score =  get_post_meta(
			$post_id,
			'_drplus_patients_score',
			true
		);

		if( $score === '' ) {
			$score = self::update_specialist_score( $post_id );
		}

		return $score;
	}

	public static function update_specialist_score( int $specialist_post_id = 0) {
		if( ! $specialist_post_id ) {
			$specialist_post_id = get_the_ID();
		}
		if ( get_post_type( $specialist_post_id ) !== 'specialist' ) {
			return;
		}

		$comments = get_comments( [
			'fields'    => 'ids',
			'post_id'   => $specialist_post_id,
			'meta_key'  => '_drplus_patient_review',
			'meta_value'=> true,
			'status'    => 'approve',
		] );

		$comments_count = count( $comments );

		$full_avg_score = Utils::get_post_avg(
			$specialist_post_id,
			false,
			$comments_count
		);

		$score = $comments_count > 0
			? round( ( $full_avg_score / 5 ) * 100 )
			: 0;

		update_post_meta(
			$specialist_post_id,
			'_drplus_patients_score',
			$score
		);

		return $score;
	}
}
