<?php
namespace DrPlus\Utils;

use DrPlus\Components\Alert;
use DrPlus\Components\Button;
use DrPlus\Model\Booking as ModelBooking;
use DrPlus\Model\Specialists;
use DrPlus\Model\Times;
use DrPlus\SMS\SMS;
use DrPlus\Utils;
use DrPlus\Utils\SMS as UtilsSMS;
use SheydaWalletUtils as WalletUtils;

class Booking extends Utils {
	public static function booking_steps() {
		$steps = [
			'specialist'	=> esc_html__( 'Select specialist', 'drplus' ),
			'time'			=> esc_html__( 'Select time', 'drplus' ),
			'info'			=> esc_html__( 'Completing the information', 'drplus' ),
			'checkout'		=> esc_html__( 'Checkout', 'drplus' ),
			'receipt'		=> esc_html__( 'See receipt', 'drplus' )
		];

		if( DRPLUS_SINGLE_SPECIALIST_MODE ) {
			unset( $steps['specialist'] );
		}
		return $steps;
	}

	public static function get_order_statuses() {
		return [
			'completed'		=> esc_html_x( 'Completed', 'book status', 'drplus' ),
			'processing'	=> esc_html_x( 'Processing', 'book status', 'drplus' ),
			'on-hold'		=> esc_html_x( 'On Hold', 'book status', 'drplus' ),
			'pending'		=> esc_html_x( 'Pending', 'book status', 'drplus' ),
			'failed'		=> esc_html_x( 'Failed', 'book status', 'drplus' ),
			'cancelled'		=> esc_html_x( 'Cancelled', 'book status', 'drplus' ),
			'trash'			=> esc_html_x( 'Cancelled', 'book status', 'drplus' ),
			'refunded'		=> esc_html_x( 'Refunded', 'book status', 'drplus' ),
		];
	}

	public static function get_status_filters( $status_key = "" ) {
		$statuses = [
			'all'	=> [
				'statuses'	=> array_keys( self::get_order_statuses() ),
				'text'		=> __( 'All', 'drplus' ),
				'value'		=> 0,
				'link'		=> remove_query_arg( 'filter' )
			],
			'ongoing'	=> [
				'statuses'	=> ['processing'],
				'text'		=> __( 'Ongoing', 'drplus' ),
				'value'		=> 0,
				'link'		=> add_query_arg( ['filter' => 'ongoing'] )
			],
			'completed'	=> [
				'statuses'	=> ['completed'],
				'text'		=> _x( 'Completed', 'book status', 'drplus' ),
				'value'		=> 0,
				'link'		=> add_query_arg( ['filter' => 'completed'] )
			],
			'cancelled'	=> [
				'statuses'	=> ['failed', 'cancelled', 'refunded'],
				'text'		=> __( 'Cancelled', 'drplus' ),
				'value'		=> 0,
				'link'		=> add_query_arg( ['filter' => 'cancelled'] )
			],
			'pending'	=> [
				'statuses'	=> ['pending'],
				'text'		=> _x( 'Pending', 'book status', 'drplus' ),
				'value'		=> 0,
				'link'		=> add_query_arg( ['filter' => 'pending'] )
			],
		];

		if( !empty( $status_key ) ) {
			if( array_key_exists( $status_key, $statuses ) ) return $statuses[$status_key];
			return [];
		}
		return $statuses;
	}

	public static function is_booking_active() {
		return Options::get_options( [
			'enable-booking'	=> true,
		] )['enable-booking'];
	}


	public static function get_booking_page_id() {
		static $page_id = null;
		if( $page_id === null ) {
			$page_id = Options::get_options( [
				'booking-page-id'	=> 0,
			] )['booking-page-id'];
		}
		return $page_id;
	}

	public static function get_booking_page_url( $step = "", $raw = false ) {
		if( empty( $step ) && DRPLUS_SINGLE_SPECIALIST_MODE && !$raw ) {
			$step = 'time';
			$step .= '/?sid=' . DRPLUS_SPECIALIST_ID;
		}
		$booking_page_id = self::get_booking_page_id();
		$url = get_permalink( $booking_page_id );
		return esc_url( stripslashes( $url ) . $step );
	}

	public static function get_current_step() {
		$booking_steps = self::booking_steps();
		
		// Get current step from URL
		$request_uri = $_SERVER['REQUEST_URI'];
		$request_uri = remove_query_arg( 'book_id', $request_uri );
		$path_parts = explode('/', trim( $request_uri, '/') );
		
		// remove GET params
		$path_parts = array_filter( $path_parts, function( $part ) {
			return !empty( $part ) && strpos( $part, '?' ) === false;
		} );
		$path_parts = array_map( 'sanitize_text_field', $path_parts );
		$endpoint = end( $path_parts );

		$active_step = $endpoint;
		$active_step = Utils::ensure_values_in_array( $active_step, array_keys( $booking_steps ), array_key_first( $booking_steps ) );

		return $active_step;
	}

	public static function get_specialist( $only_id = false ) {
		$general_options = Options::get_options( [
			'booking-specialist-not-found'	=> esc_html__( "Selected specialist not found", 'drplus' ),
		] );

		$specialist_id = 0;
		if( !empty( $_GET['sid'] ) ) {
			$specialist_id = parent::convert_chars( $_GET['sid'], true, 'absint' );
		} else if( !empty( $_SESSION['booking']['specialist_id'] ) ) {
			$specialist_id = parent::convert_chars( $_SESSION['booking']['specialist_id'] , true, 'absint' );
		}

		if( empty( $specialist_id ) ) {
			Alert::view( [
				'type'	=> 'error',
				'icon'	=> 'drplus-icon-error',
				'text'	=> $general_options['booking-specialist-not-found'],
				'classes'	=> ['booking-alert']
			] );
			// Get prev page url
			$prev_page_url = wp_get_referer();
			if( empty( $prev_page_url ) ) {
				$prev_page_url = self::get_booking_page_url();
			}
			Button::view( [
				'text'	=> esc_html__( 'Return to previous page', 'drplus' ),
				'link'	=> esc_url( $prev_page_url ),
				'type'	=> 'bordered',
				'align' 	=> 'center',
				'small'		=> true,
			] );
			return false;
		}
		if( $only_id ) return $specialist_id;

		$specialist = (new Specialists)->find( $specialist_id );
		if( empty( $specialist->id ) ) {
			Alert::view( [
				'type'	=> 'error',
				'icon'	=> 'drplus-icon-error',
				'text'	=> $general_options['booking-specialist-not-found'],
				'classes'	=> ['booking-alert']
			] );
			Button::view( [
				'text'	=> esc_html__( 'Back', 'drplus' ),
				'link'	=> self::get_booking_page_url(),
				'type'	=> 'bordered',
				'align' 	=> 'center',
			] );
			return false;
		}
		return $specialist;
	}

	public static function check_booking_session( array $keys ) {
		$error = false;
		if( empty( $_SESSION['booking'] ) ) {
			$error = true;
		} else {
			foreach( $keys as $key ) {
				if( empty( $_SESSION['booking'][$key] ) ) {
					$error = true;
					break;
				}
			}
		}

		if( $error ) {
			Alert::view( [
				'type'	=> 'error',
				'icon'	=> 'drplus-icon-error',
				'text'	=> esc_html__( 'Something went wrong. please try again', 'drplus' ),
				'classes'	=> ['booking-alert']
			] );
			Button::view( [
				'text'	=> esc_html__( 'Back', 'drplus' ),
				'link'	=> self::get_booking_page_url(),
				'type'	=> 'bordered',
				'align' 	=> 'center',
			] );
		}
		return !$error;
	}

	public static function specialist_info_html( $specialist ) {
		$avatar_url = get_avatar_url( $specialist->user_id );

		if( empty( $specialist->removed_specialist ) && $specialist->status == 'active' ) {
			$specialist_page = esc_url( UtilsSpecialists::get_page_link( $specialist ) );
		}
		?>
		<div class="booking-specialist-info">
			<a href="<?php echo $specialist_page ?? '#' ?>" <?php echo !empty( $specialist_page ) ? 'target="_blank"' : '' ?> class="booking-specialist-avatar-wrap">
				<img src="<?php echo $avatar_url ?>" class="booking-specialist-avatar" alt="<?php echo esc_attr( $specialist->display_name ) ?>">
			</a>
			<a href="<?php echo $specialist_page ?? '#' ?>" <?php echo !empty( $specialist_page ) ? 'target="_blank"' : '' ?> class="booking-specialist-name"><?php echo esc_html( $specialist->display_name ) ?></a>
			<span class="booking-specialist-subtitle"><?php echo esc_html( $specialist->subtitle ) ?></span>
		</div>
		<?php
	}

	public static function specialist_office_html( $office, $icon_as_image = false, $show_map_url = false, $show_price = false ) {
		if( $office['type'] == 'hospital' ) {
			$hospital_settings = Hospital::get_options( $office['id'] );
			$office['name'] = get_the_title( $office['id'] );
			$office['phone'] = $hospital_settings['phones'][0]['phone'] ?? "";
			$office['address'] = $hospital_settings['address'];
			$office['map_url'] = $hospital_settings['map_address'];
			if( !$icon_as_image ) {
				$office['image'] = get_the_post_thumbnail_url( $office['id'] );
			}
			$office_url = esc_url( get_permalink( $office['id'] ) );
		} else {
			if( $office['type'] == 'consultation' ) {
				if( $office['id'] == 'video_consultation' ) {
					$office['name'] = esc_html__( 'Video Consultation', 'drplus' );
				} else if( $office['id'] == 'chat_consultation' ) {
					$office['name'] = esc_html__( 'Chat Consultation', 'drplus' );
				} else if( $office['id'] == 'phone_consultation' || $office['id'] == 'consultation' ) {
					$office['name'] = esc_html__( 'Phone Consultation', 'drplus' );
				} else if( $office['id'] == 'instant_chat_consultation' ) {
					$office['name'] = esc_html__( 'Instant Chat Consultation', 'drplus' );
				}
			}
			if( !$icon_as_image ) {
				if( $office['type'] == 'consultation' ) {
					$office['image'] = DRPLUS_URI . 'assets/images/online-consultation.webp';
				} else {
					$office['image'] = wp_get_attachment_image_url( $office['image'] );
				}
			}
		}
		if( empty( $office['image'] ) && !$icon_as_image ) {
			$office['image'] = DRPLUS_URI . 'assets/images/hospital-placeholder.webp';
		}
		?>
		<div class="booking-specialist-office">
			<div class="booking-specialist-office-head">
				<?php if( $icon_as_image ) { ?>
					<div class="booking-specialist-office-img-wrap">
						<div class="booking-specialist-office-icon"><i class="drplus-icon-location-fill"></i></div>
					</div>
				<?php } else { ?>
					<a href="<?php echo !empty( $office_url ) ? $office_url : '#' ?>" title="<?php echo esc_attr( $office['name'] ) ?>" class="booking-specialist-office-img-wrap">
						<img src="<?php echo $office['image'] ?>" class="booking-specialist-office-img" alt="<?php echo esc_attr( $office['name'] ) ?>">
					</a>
				<?php } ?>
				<div class="booking-specialist-office-info">																
					<a href="<?php echo !empty( $office_url ) ? $office_url : '#' ?>" class="booking-specialist-office-name line-clamp line-clamp-1"><?php echo esc_html( $office['name'] ) ?></a>
					<?php if( !empty( $office['phone'] ) ) { ?>
						<a href="tel:<?php echo esc_attr( $office['phone'] ) ?>" class="booking-specialist-office-phone">
							<i class="drplus-icon-calling"></i>
							<?php echo esc_html( $office['phone'] ) ?>
						</a>
					<?php } ?>
				</div>
				<?php if( $show_price ) { ?>
					<div class="booking-specialist-office-price">
						<?php
						$price = !empty( $office['visit_price'] ) ? sprintf( Formatters::price( $office['visit_price'], true ) ) : esc_html__( 'Free!', 'drplus' );
						echo apply_filters( 'drplus/booking/specialist_office/visit_price_text', $price, $office );
						?>
					</div>
				<?php } ?>
			</div>
			<?php if( !empty( $office['address'] ) ) { ?>
				<p class="booking-specialist-office-address"><?php echo esc_html( $office['address'] ) ?></p>
			<?php } ?>
			<?php if( !empty( $show_map_url ) && !empty( $office['map_url'] ) ) { ?>
				<a href="<?php echo esc_url( $office['map_url'] ) ?>" class="booking-specialist-office-office-map map-popup-opener" target="map-popup-iframe" title="<?php echo esc_attr( $office['name'] ) ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show %s on the map', 'drplus' ), $office['name'] ) ) ?>" data-title="<?php echo esc_attr( $office['name'] ) ?>">
					<i class="drplus-icon-routing"></i>
					<?php esc_html_e( 'Show on the map', 'drplus' ) ?>
				</a>
			<?php } ?>
		</div>
		<?php
	}

	public static function get_times_by_office( $office_id, $specialist ) : array {
		$return = [
			'default_times'	=> [],
			'days'			=> [],
		];
		if( empty( $specialist ) || empty( $specialist->id ) || empty( $specialist->user_id ) ) return $return;

		// Get times for specific user and office
		$db_times = Times::query()->withoutGlobalScopes()->where( 'office', $office_id )->where( 'user_id', $specialist->user_id )->get();

		$default_times = [];
		$days = [];

		foreach( $db_times as $db_time ) {
			if( $db_time['day'] === '9' ) {
				$default_times[] = $db_time;
			} else {
				$days[$db_time['day']]['status'] = $db_time['status'];
				$days[$db_time['day']]['day'] = $db_time['day'];
				if( $db_time['use_default'] == true ) {
					$days[$db_time['day']]['use_default'] = true;
				} else {
					$days[$db_time['day']]['use_default'] = false;
				}
				$days[$db_time['day']]['times'][] = $db_time;
			}
		}

		$US_week_days = [__( 'Sunday', 'drplus' ), __( 'Monday', 'drplus' ), __( 'Tuesday', 'drplus' ), __( 'Wednesday', 'drplus' ), __( 'Thursday', 'drplus' ), __( 'Friday', 'drplus' ), __( 'Saturday', 'drplus' )];
		for( $day_index = 0; $day_index <= 6; $day_index++ ) {
			$days[$day_index]['day_name'] =  $US_week_days[$day_index];
			if( count( $days[$day_index] ) > 1 ) continue;

			$days[$day_index]['status'] = false;
			$days[$day_index]['day'] = $day_index;
			$days[$day_index]['use_default'] = true;
		}

		// Convert to Iran week template
		ksort( $days );
		$days = array_merge(array_slice($days, 6), array_slice($days, 0, 6));

		return [
			'default_times'	=> $default_times,
			'days'			=> $days,
		];
	}

	public static function get_available_time_slots( $date, $specialist_id, $office_id, $chunk_duration ) {
		$timezone = wp_timezone();
		$day_index = date( 'w', strtotime( $date ) ); // Index (0 for Sunday, 6 for Saturday)
		$date = date( 'Y-m-d', strtotime( $date ) );
		
		// Check specialist office custom off day and max bookable days
		$specialist = (new Specialists)->find( $specialist_id );
		if( !isset( $specialist->offices[$office_id] ) ) {
			return [];
		}
		$office = $specialist->offices[$office_id];

		$office_time = Booking::get_times_by_office( $office_id, $specialist );
		$off_days = [];
		foreach( $office_time['days'] as $day ) {
			if( empty( $day['times'] ) || !Utils::to_bool( $day['status'] ) ) {
				$off_days[] = $day['day']; // plus two because our week start from saturday!
			}
		}
		$date_time = new \DateTime( $date, $timezone );
		if( in_array( (int)$date_time->format('w'), $off_days ) ) {
			return [];
		}

		// Normalize custom off days to Y-m-d strings for comparison
		$custom_off_days = [];
		if ( isset( $office['custom_off_days'] ) && is_array( $office['custom_off_days'] ) ) {
			$custom_off_days = array_map( function( $ts ) use ( $timezone ) {
				if ( empty( $ts ) ) return '';
				if ( $ts instanceof \DateTime ) {
					$dt = $ts;
				} elseif ( is_numeric( $ts ) ) {
					$dt = ( new \DateTime( '@' . intval( $ts ) ) )->setTimezone( $timezone );
				} else {
					$dt = new \DateTime( $ts, $timezone );
				}
				return $dt->format( 'Y-m-d' );
			}, $office['custom_off_days'] );
		}

		// If the requested date is a custom off day, no slots
		if ( in_array( $date, $custom_off_days, true ) ) {
			return [];
		}

		// Current date and time
		$now = new \DateTime( 'now', $timezone );
		$current_date = date( 'Y-m-d' );
		$current_time = Utils::convert_chars( date_i18n( 'H:i:s' ) );

		// Enforce max booking days: compute days difference from today
		if ( isset( $office['max_booking_days'] ) && $office['max_booking_days'] !== '' && is_numeric( $office['max_booking_days'] ) ) {
			$max_days = intval( $office['max_booking_days'] );
			$target = new \DateTime( $date, $timezone );
			$day_from_now_dt = clone $now;
			$days_from_now = (int) $day_from_now_dt->diff( $target )->format( '%a' ) + 1;
			if ( $days_from_now > $max_days ) {
				return [];
			}
		}

		// Prepare min time before book
		$min_time_before_book = isset($office['min_time_before_book']) ? (int) $office['min_time_before_book'] : 0;
		$min_book_dt = clone $now;
		$min_book_dt->modify("+{$min_time_before_book} hours");
		

		// Get specialist user ID
		$user_id = Specialists::query()->select( 'user_id' )->where( 'id', $specialist_id )->first()->user_id;

		// Get availability times of requested day index and 9 (default times)
		$day_times_db = Times::query()->whereIn( 'day', [$day_index, 9] )->where( 'office', $office_id )->where( 'user_id', $user_id )->get();
		$default_times = [];
		$available_times = [];
		$use_default = true;
		foreach( $day_times_db as $time_db ) {
			if( $time_db->day == 9 ) {
				$default_times[] = $time_db;
			} else {
				$available_times[] = $time_db;
				$use_default = Utils::to_bool( $time_db->use_default );
			}
		}
		if( $use_default ) {
			$available_times = $default_times;
		}
		if( empty( $available_times ) ) return [];
	
		// Get all bookings for the day
		$exclude_statuses = ['cancelled', 'refunded', 'failed'];
		$where = [
			'`date`'		=> $date,
			'specialist_id'	=> $specialist_id,
			'office_id'		=> $office_id
		];
		$existing_bookings = ModelBooking::query()
			->select( ['start_time', 'end_time'] )
			->where( $where )
			->whereNotIn( 'order_status', $exclude_statuses )
			->get();

		$existing_bookings = $existing_bookings->toArray();
	
		// Convert existing bookings to an array of time ranges
		$booked_ranges = array_map(function($booking) {
			return ['start' => $booking['start_time'], 'end' => $booking['end_time']];
		}, $existing_bookings);
	
		// Generate available slots based on chunk duration
		$available_slots = [];
		foreach( $available_times as $time_range ) {
			$start_time = strtotime( $time_range['from'] );
			$end_time = strtotime( $time_range['to'] );
	
			while ($start_time + ($chunk_duration * 60) <= $end_time) {
				$slot_start = date('H:i', $start_time);
				$slot_end = date('H:i', $start_time + ($chunk_duration * 60));
				$slot_dt = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $slot_start, $timezone);
				$is_available = true;

				// Check min time before book
				if ( $slot_dt < $min_book_dt ) {
					$is_available = false;
				}

				if( $date == $current_date && $slot_start < $current_time ) {
					$is_available = false;
				} else {
					// Check if this slot overlaps with any booked range
					foreach( $booked_ranges as $range ) {
						if( $slot_start . ":00" < $range['end'] && $slot_end > $range['start'] ) {
							$is_available = false;
							break;
						}
					}
				}

				// set time name (morning, noon, evening)
				$time_name = '';
				$time_name = Date::get_time_period( (int) date( 'H', $start_time ) );

				$available_slots[] = [
					'from'		=> $slot_start,
					'to'		=> $slot_end,
					'available'	=> $is_available,
					'label'		=> $time_name,
				];
	
				// Move to the next slot
				$start_time += $chunk_duration * 60;
			}
		}
	
		return apply_filters( 'drplus/booking/available_time_slots', $available_slots, [
			'date'			=> $date,
			'specialist_id'	=> $specialist_id,
			'office_id'		=> $office_id
		] );
	}

	public static function get_booking_product_id() {
		$booking_product_id = get_option( 'drplus_booking_product_id', "" );
		if ( $booking_product_id && !empty( $product = get_post( $booking_product_id ) ) && $product->post_status == 'private' ) {
			return $booking_product_id;
		} else {
			return self::create_booking_product();
		}
	}

	public static function set_booking_product_id( $product_id ) {
		update_option( 'drplus_booking_product_id', $product_id, false );
	}

	public static function create_booking_product() {
		if( !parent::is_wc_active() ) {
			return false;
		}
		$product = new \WC_Product_Simple();
		$product->set_name( 'Book Product (Drplus Theme) - please do not remove or edit.' );
		$product->set_status( 'private' ); // Hidden from shop
		$product->set_catalog_visibility( 'hidden' ); // Hides from search results
		
		// Set virtual
		$product->set_virtual( true );
		$product->set_manage_stock( false );
		$product->set_sold_individually( true );

		// Save the variable product to get an ID
		$product_id = $product->save();
		self::set_booking_product_id( $product_id );
		return $product_id;
	}

	public static function get_specialist_appointments_count( $specialist_id, $office = "", $date = "", $where = [] ) {
		$exclude_statuses = ['cancelled', 'refunded', 'failed', 'on-hold'];
		
		$_where['specialist_id']	= $specialist_id;
		if( !empty( $office ) ) {
			$_where['`office_id`'] = $office;
		}
		if( !empty( $date ) ) {
			$_where['`date`'] = $date;
		}
		
		$count = ModelBooking::query()->where( $_where )->whereNotIn( 'order_status' , $exclude_statuses );
		if( !empty( $where ) ) {
			$count = $count->where( $where );
		}
		return $count->count();
	}

	public static function get_specialist_consultations_duration( $specialist_id, $statuses = [], $where = [], $no_consultation_text = "" ) {
		$bookings = ModelBooking::query()
			->select( 'SUM(TIME_TO_SEC(TIMEDIFF(end_time, start_time))) AS total_duration' )
			->where( ['specialist_id'	=> $specialist_id] )
			->whereIn( 'office_id', array_keys( self::consultation_offices() ) );
		if( !empty( $statuses ) ) {
			if( !is_array( $statuses ) ) {
				$statuses = [$statuses];
			}
			$bookings = $bookings->whereIn( 'order_status', $statuses );
		}
		$result = $bookings->first();

		$duration_in_sec = (int) $result->total_duration;

		if( empty( $no_consultation_text ) ) $no_consultation_text = esc_html_x( 'No consultations', 'consultation duration', 'drplus' );

		if ( $duration_in_sec == 0 ) {
			$duration_in_hours = $no_consultation_text;
		} else if( $duration_in_sec < 3600 ) {
			$duration_in_hours = esc_html__( 'Less than 1 hour', 'drplus' );
		} else {
			$duration_in_hours = sprintf( esc_html__( '+%d hours', 'drplus' ), floor( $duration_in_sec / 3600 ) );
		}
		return $duration_in_hours;
	}

	public static function consultation_offices( $only_actives = false ) {
		$online_visits = [
			'phone_consultation'	=> [
				'label'	=> esc_html__( 'Phone Consultation', 'drplus' ),
				'icon'	=> 'headphone'
			],
			'chat_consultation'		=> [
				'label'	=> esc_html__( 'Chat Consultation', 'drplus' ),
				'icon'	=> 'messages-2'
			],
			'instant_chat_consultation'		=> [
				'label'	=> esc_html__( 'Instant Chat Consultation', 'drplus' ),
				'icon'	=> 'messages-2'
			],
			'video_consultation'	=> [
				'label'	=> esc_html__( 'Video Consultation', 'drplus' ),
				'icon'	=> 'video'
			],
		];

		if( $only_actives ) {
			$active_online_visits = Options::get_options( ['booking-active-online-visits' => []] )['booking-active-online-visits'];
			foreach( $online_visits as $key => $value ) {
				if( empty( $active_online_visits[$key] ) ) {
					unset( $online_visits[$key] );
				}
			}
		}

		return $online_visits;
	}

	public static function cancel_booking( $book, $order = "", $cancel_by_key = "" ) {
		if( is_numeric( $book ) ) {
			$book = ModelBooking::find( $book );
		}
		if( empty( $book ) ) return \WP_Error( 'cancel_failed', esc_html__( 'Failed to cancel appointment, Booking not found', 'drplus' ) );

		if( $book->order_status == 'cancelled' || $book->order_status == 'refunded' || $book->order_status == 'failed' ) {
			return new \WP_Error( 'cancel_failed', esc_html__( 'Failed to cancel appointment, Booking is already cancelled', 'drplus' ) );
		} else if( $book->order_status == 'completed' ) {
			return new \WP_Error( 'cancel_failed', esc_html__( 'Failed to cancel appointment, Completed bookings cannot be cancelled', 'drplus' ) );
		}

		$options = Options::get_options( [
			'booking-cancel-by-customer'								=> false,
			'booking-cancel-by-customer-refund-percentage-specialist'	=> 100,
			'booking-cancel-by-customer-refund-percentage-commission'	=> 100,
			'booking-max-cancellation-hours'							=> 24,

			'booking-cancel-by-specialist'								=> false,
			'booking-cancel-by-specialist-refund-percentage-specialist'	=> 100,
			'booking-cancel-by-specialist-refund-percentage-commission'	=> 100,

			'booking-cancel-by-admin-refund-percentage-specialist'	=> 100,
			'booking-cancel-by-admin-refund-percentage-commission'	=> 100,

			'use-outside-iran'	=> false,
			'sms'				=> true,
		] );

		$cancel_by_key = parent::ensure_values_in_array( $cancel_by_key, ['admin', 'customer', 'specialist'], "" );
		if( empty( $cancel_by_key ) ) {
			if( !is_admin() ) {
				$check = false;
				$cancel_by = "";
				// check cancelling request is from customer or specialist
				$user_id = get_current_user_id();
				if( $book->customer_id == $user_id ) {
					$check = true;
					$cancel_by = esc_html__( "customer", 'drplus' );
					$cancel_by_key = 'customer';
				} else {
					$specialist = UtilsSpecialists::get_by_user_id( $user_id );
					if( !empty( $specialist ) && $specialist->user_id == $user_id ) {
						$check = true;
						$cancel_by = esc_html__( "specialist", 'drplus' );
						$cancel_by_key = 'specialist';
					}
				}
	
				if( !$check ) {
					return \WP_Error( 'cancel_failed', esc_html__( 'Failed to cancel appointment, You are not allowed to cancel this booking', 'drplus' ) );
				}
			} else {
				$cancel_by = esc_html__( "admin", 'drplus' );
				$cancel_by_key = 'admin';
			}
		} else {
			switch( $cancel_by_key ) {
				case 'specialist':
					$cancel_by = esc_html__( 'specialist', 'drplus' );
					break;
				case 'customer':
					$cancel_by = esc_html__( 'customer', 'drplus' );
					break;
				default:
					$cancel_by = esc_html__( 'admin', 'drplus' );
			}
		}

		$canceled_before = false;
		if( !empty( $book->order_id ) ) {
			// Update order status, then booking status is updating automatically
			if( empty( $order ) ) {
				$order = wc_get_order( $book->order_id );
				if( empty( $order ) ) {
					return new \WP_Error( 'cancel_failed', esc_html__( 'Order not found', 'drplus' ) );
				}
			}
			$order->update_status( 'cancelled', sprintf( esc_html__( 'Booking cancelled by %s', 'drplus' ), $cancel_by ) );

			$book_data = $order->get_meta( '_booking_data' );
			if( !empty( $book_data['canceled_at'] ) ) {
				$canceled_before = true;
			}
			$book_data['canceled_by'] = $cancel_by;
			$book_data['canceled_by_id'] = get_current_user_id();
			$book_data['canceled_at'] = date_i18n( 'j F Y H:i:s' );
		}

		// Calc refund price
		if( !$canceled_before && $book->total_price > 0 ) {
			$total_refund_to_user = 0;
			$specialist_income = 0;
			$old_specialist_income = floatval( $book->specialist_income );
			$old_commission_value = floatval( $book->commission );

			if( $old_commission_value <= 0 ) {
				$old_commission_value = max( 0, floatval( $book->total_price ) - $old_specialist_income );
			}

			$calc_refund_amounts = function( $refund_percent_specialist, $refund_percent_commission ) use ( $old_specialist_income, $old_commission_value ) {
				$refund_percent_specialist = max( 0, min( 100, floatval( $refund_percent_specialist ) ) );
				$refund_percent_commission = max( 0, min( 100, floatval( $refund_percent_commission ) ) );

				$refunded_specialist_income = ( $old_specialist_income * $refund_percent_specialist ) / 100;
				$new_specialist_income = $old_specialist_income - $refunded_specialist_income;
				$refunded_commission = ( $old_commission_value * $refund_percent_commission ) / 100;
				$new_commission = $old_commission_value - $refunded_commission;
				$total_refund = $refunded_specialist_income + $refunded_commission;

				return [
					'new_specialist_income'	=> max( 0, $new_specialist_income ),
					'new_commission'		=> max( 0, $new_commission ),
					'total_refund'			=> max( 0, min( $total_refund, $old_specialist_income + $old_commission_value ) ),
				];
			};

			if( $cancel_by_key == 'specialist' ) {
				$refund_percent_specialist = $options['booking-cancel-by-specialist-refund-percentage-specialist'];
				$refund_percent_commission = $options['booking-cancel-by-specialist-refund-percentage-commission'];
			} else if( $cancel_by_key == 'customer' ) {
				$refund_percent_specialist = $options['booking-cancel-by-customer-refund-percentage-specialist'];
				$refund_percent_commission = $options['booking-cancel-by-customer-refund-percentage-commission'];
			} else if ( $cancel_by_key == 'admin' ) {
				$refund_percent_specialist = $options['booking-cancel-by-admin-refund-percentage-specialist'];
				$refund_percent_commission = $options['booking-cancel-by-admin-refund-percentage-commission'];
			}

			$refund_data = $calc_refund_amounts( $refund_percent_specialist, $refund_percent_commission );
			$specialist_income = $refund_data['new_specialist_income'];
			$total_refund_to_user = $refund_data['total_refund'];

			if( $specialist_income > 0 && empty( $book_data['specialist_payout_id'] ) ) {
				if( empty( $specialist ) ) {
					$specialist = (new Specialists)->find( $book_data['specialist_id'] );
				}

				$meta = [
					'book_id'	=> $book_data['book_id']
				];
				$meta['description'] = sprintf( esc_html__( 'Booking income for cancel appointment #%s', 'drplus' ), $book_data['book_id'] );
				$booking_payout_id = WalletUtils::add_ledger_record( $specialist->user->ID, 'booking_payout', $specialist_income, 0, $order->get_id(), $meta );
				$book_data['specialist_payout_id'] = $booking_payout_id;
			}
			if( $total_refund_to_user > 0 ) {
				$meta = [
					'book_id'	=> $book_data['book_id']
				];
				$meta['description'] = sprintf( esc_html__( 'Refund of cancel appointment #%s', 'drplus' ), $book_data['book_id'] );
				$refund_id = WalletUtils::add_user_refund_record( $total_refund_to_user, $book->customer_id, 0, $order->get_id(), $meta );
				$book_data['customer_refund_id'] = $refund_id;
			}

			// Update Book table
			$book->specialist_income = $specialist_income;
			$book->commission = $refund_data['new_commission'];
			$book->total_price = max( 0, $book->total_price - $total_refund_to_user );
			$book_data['specialist_income'] = $specialist_income;
			$book_data['commission_value'] = $refund_data['new_commission'];
		}

		$book->order_status = 'cancelled';
		$book->save();
		// Update order booking meta 
		$order->update_meta_data( '_booking_data', $book_data );
		$order->save();

		// Send sms
		if( $options['sms'] && !$options['use-outside-iran'] && !empty( $book_data ) ) {
			$sms_book_canceled_settings = UtilsSMS::get_reserve_notif_book_canceled_sms_settings();
			do_action( 'drplus/booking/appointment_canceled', $book_data );
			if( empty( $specialist ) ) {
				$specialist = (new Specialists)->find( $book_data['specialist_id'] );
				$specialist_mobile = User::get_phone( $specialist->user_id );
			}
			$sms_vars = [
				'book_id'				=> $book_data['book_id'],
				'specialist_name'		=> $specialist->display_name,
				'specialist_mobile'		=> $specialist_mobile,
				'patient_name'			=> "{$book_data['first_name']} {$book_data['last_name']}",
				'patient_first_name'	=> $book_data['first_name'],
				'patient_last_name'		=> $book_data['last_name'],
				'patient_mobile'		=> $book_data['phone'],
				'visit_date'			=> $book_data['date'],
				'visit_time'			=> $book_data['start_time'],
				'office'				=> $book_data['office_name']
			];
			if( $sms_book_canceled_settings['specialist']['status'] == true && !empty( $specialist_mobile ) ) {
				SMS::send( $specialist_mobile, 'reserve_notification.specialist.book_canceled', $sms_vars );
			}
			if( $sms_book_canceled_settings['patient']['status'] == true && !empty( $book_data['phone'] ) ) {
				SMS::send( $book_data['phone'], 'reserve_notification.patient.book_canceled', $sms_vars );
			}
		}
	}

	public static function success_statuses() {
		return apply_filters( 'drplus/booking/success_statuses', ['processing'] );
	}
}
