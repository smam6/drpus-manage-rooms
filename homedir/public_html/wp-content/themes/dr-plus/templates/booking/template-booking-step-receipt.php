<?php

use DrPlus\Components\Button;
use DrPlus\Model\Booking as ModelBooking;
use DrPlus\Model\ChatSession;
use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Date;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;
use DrPlus\Utils\Skyroom;
use DrPlus\Utils\User;

$options = Options::get_options( [
	'booking-receipt-section-title-success'			=> esc_html__( 'Thank you. your requested appointment has been booked.', 'drplus' ),
	'booking-receipt-section-title-pending'			=> esc_html__( 'Your appointment has been reserved, but payment is still pending.', 'drplus' ),
	'booking-receipt-section-title-failed'			=> esc_html__( 'We\'re sorry; your appointment booking was unsuccessful. Please attempt your book again.', 'drplus' ),
	'booking-receipt-section-title-cancelled'		=> esc_html__( 'Your appointment has been cancelled as requested.', 'drplus' ),
	'booking-receipt-section-title-refunded'		=> esc_html__( 'Your payment has been refunded successfully.', 'drplus' ),
	'booking-receipt-section-offline-description'	=> esc_html__( 'Please arrive at the medical center 30 minutes before your scheduled appointment time.
In case of cancellation, half of the consultation fee will be deducted.', 'drplus' ),
	'booking-receipt-section-online-description'	=> esc_html__( 'At the scheduled consultation time, the specialist will contact you using the phone number you provided during the booking.
Please make sure to be available at that time and ensure your phone is turned on and has proper signal coverage.', 'drplus' ),
	'booking-receipt-section-chat-consultation-description'	=> esc_html__( 'At the scheduled time for your consultation, your online chat will open on the DoctorPlus website.
You can access your chat list from the Chats section of your account.', 'drplus' ),
	'booking-receipt-section-video-consultation-description'	=> esc_html__( 'At the scheduled time for your consultation, your video meeting will open on the DoctorPlus website. Meeting link will show here', 'drplus' ),

	'booking-info-field-phone-enabled'		=> true,
	'booking-info-field-email-enabled'		=> true,
	'booking-info-field-nid-enabled'		=> true,
	'booking-info-field-gender-enabled'		=> true,
	'booking-info-field-birthday-enabled'	=> true,
	'booking-info-field-reason-enabled'		=> true,

	'video-enter-btn-text'					=> esc_html__( 'Enter the video call', 'drplus' ),
	'video-enter-btn-icon'					=> 'drplus-icon-video',
	'video-specialist-not-started-text'		=> esc_html__( 'The session will start on {start_time} with {patient_name}', 'drplus' ),
	'video-visitor-not-started-text'		=> esc_html__( 'The session will start on {start_time} with {specialist_name}', 'drplus' ),
	'video-specialist-ended-text'			=> esc_html__( 'This session ended on {end_time}', 'drplus' ),
	'video-visitor-ended-text'				=> esc_html__( 'This session ended on {end_time}', 'drplus' ),

	'booking-cancel-by-customer'			=> false,
	'booking-cancel-by-specialist'			=> false,
	'booking-max-cancellation-hours'		=> 24,

	'use-outside-iran'						=> false,
] );

$args = Utils::check_default( $args, [
	'show_back_btn'	=> false,
	'book_data'		=> [],
	'order'			=> [],
	'view_type'		=> 'user' // user, specialist, thankyou, admin
], ['book_data', 'order'] );
extract( $args );

if( !empty( $_GET['action'] ) && Utils::convert_chars( $_GET['action'] ) == 'cancel_app' && !empty( $_GET['c_nonce'] ) ) {
	// Check nonce
	$c_nonce = sanitize_text_field( wp_unslash( $_GET['c_nonce'] ) );
	if( wp_verify_nonce( $c_nonce, "drplus_cancel_booking_{$book_data['book_id']}" ) ) {
		// Cancel booking
		$order_status = $order->get_status();
		if( !in_array( $order_status, ['cancelled', 'refunded', 'failed'] ) ) {
			$cancel_result = Booking::cancel_booking( $book_data['book_id'], $order, $view_type == 'admin' ? 'admin' : "" );
			if( is_wp_error( $cancel_result ) ) {
				echo '<div class="drplus-notice drplus-notice-error">' . esc_html( $cancel_result->get_error_message() ) . '</div>';
			} else {
				echo '<div class="drplus-notice drplus-notice-success">' . esc_html__( 'The appointment has been cancelled successfully.', 'drplus' ) . '</div>';
				// Refresh book data and order status
				$order = wc_get_order( $order->get_id() );
				$book_data = $order->get_meta( '_booking_data' );
			}
		}
	}
}

// backward compatibility
if( $book_data['office_id'] == 'consultation' ) $book_data['office_id'] = 'phone_consultation';

$receipt_title = $options['booking-receipt-section-title-success'];
$order_status = $order->get_status();
if( in_array( $order_status, Booking::success_statuses() ) ) {
	$receipt_title = $options['booking-receipt-section-title-success'];
} else if( $order_status == 'on-hold' || $order_status == 'pending') {
	$receipt_title = $options['booking-receipt-section-title-pending'];
} else if( $order_status == 'failed' ) {
	$receipt_title = $options['booking-receipt-section-title-failed'];
} else if( $order_status == 'cancelled' || $order_status == 'trash' ) {
	$receipt_title = $options['booking-receipt-section-title-cancelled'];
} else if( $order_status == 'refunded' ) {
	$receipt_title = $options['booking-receipt-section-title-refunded'];
}

$specialist_id = $book_data['specialist_id'];
$specialist = (new Specialists)->find( $specialist_id );
$selected_office = null;
if( !empty( $specialist->id ) ) {
	foreach( $specialist->offices as $office ) {
		if( $office['id'] == $book_data['office_id'] ) {
			$selected_office = $office;
			break;
		}
	}
} else {
	// get specialist data from book data
	$saved_specialist_data = new stdClass();
	$saved_specialist_data->removed_specialist = true;
	$saved_specialist_data->ID = $specialist_id;
	$saved_specialist_data->user_id = 0;
	$saved_specialist_data->display_name = $book_data['specialist_name'];
	$saved_specialist_data->subtitle = $book_data['specialist_subtitle'];
	$saved_office = new stdClass();
	$saved_office->type = is_numeric( $book_data['office_id'] ) ? 'hospital' : 'custom';
	$saved_office->id = $book_data['office_id'];
	$saved_office->name = $book_data['office_name'];
	$saved_office->address = $book_data['office_address'];
	$saved_office->image = "";
	$saved_office->phone = "";
	$saved_specialist_data->offices = [$saved_office];
}

$is_consultation = false;
$is_chat_consultation = false;
$consultation_office = Booking::consultation_offices();
if( in_array( $book_data['office_id'], array_keys( $consultation_office ) ) ) $is_consultation = true;

switch ( $book_data['office_id'] ) {
	case 'phone_consultation':
		$receipt_note = $options['booking-receipt-section-online-description'];
		break;
	case 'chat_consultation':
		$receipt_note = $options['booking-receipt-section-chat-consultation-description'];
		break;
	case 'instant_chat_consultation':
		$receipt_note = $options['booking-receipt-section-instant-chat-consultation-description'];
		break;
	case 'video_consultation':
	$receipt_note = $options['booking-receipt-section-video-consultation-description'];
	break;
	default:
		$receipt_note = $options['booking-receipt-section-offline-description'];
}

if( $book_data['office_id'] == 'chat_consultation' || $book_data['office_id'] == 'instant_chat_consultation' ) {
	$chat = ChatSession::query()->where( 'context_id', $book_data['book_id'] )->first();
	if( !empty( $chat ) ) {
		$is_chat_consultation = true;
		if( $view_type == 'user' || $view_type == 'thankyou' ) {
			$chat_page_url = wc_get_account_endpoint_url( 'chats' );
			$chat_page_url = stripslashes( $chat_page_url ) . intval( $chat['id'] );
		} else if ( $view_type == 'specialist' ) {
			$chat_page_url = wc_get_account_endpoint_url( 'specialist-dashboard' ) . 'specialist-chats/';
			$chat_page_url = stripslashes( $chat_page_url ) . intval( $chat['id'] );
		} else { // admin
			$chat_page_url = add_query_arg( ['show_chat' => intval( $chat['id'] )] );
		}
	}
} else if( $book_data['office_id'] == 'video_consultation' ) {
	$booking_times = ModelBooking::query()->select( ['date', 'start_time', 'end_time'] )->where( 'book_id', $book_data['book_id'] )->first();
	$start_time = strtotime( "{$booking_times->date} {$booking_times->start_time}" );
	$end_time = strtotime( "{$booking_times->date} {$booking_times->end_time}" );
	$current_time = current_time( 'U' );
	$video_link = '';
	$video_status = ''; // not_started | ongoing | ended
	if( $current_time >= $start_time && $current_time < $end_time ) {
		$video_status = 'ongoing';
		if( $view_type == 'user' || $view_type == 'thankyou' || $view_type == 'specialist' ) {
			if( $args['order']->get_status() == 'processing' ) {
				$video_link = Skyroom::get_room_link( $args['order'] );
			}
		}
	} else if( $current_time < $start_time ) {
		$video_status = 'not_started';
	} else if( $current_time > $end_time ) {
		$video_status = 'ended';
	}

	$video_text_shortcodes = [
		'{specialist_name}'	=> $specialist->display_name,
		'{patient_name}'	=> "{$book_data['first_name']} {$book_data['last_name']}",
		'{start_time}'		=> date_i18n( 'Y/m/d H:i', $start_time ),
		'{end_time}'		=> date_i18n( 'Y/m/d H:i', $end_time ),
	];
	$options['video-specialist-not-started-text'] = str_replace( array_keys( $video_text_shortcodes ), array_values( $video_text_shortcodes ), $options['video-specialist-not-started-text'] );
	$options['video-visitor-not-started-text'] = str_replace( array_keys( $video_text_shortcodes ), array_values( $video_text_shortcodes ), $options['video-visitor-not-started-text'] );
	$options['video-specialist-ended-text'] = str_replace( array_keys( $video_text_shortcodes ), array_values( $video_text_shortcodes ), $options['video-specialist-ended-text'] );
	$options['video-visitor-ended-text'] = str_replace( array_keys( $video_text_shortcodes ), array_values( $video_text_shortcodes ), $options['video-visitor-ended-text'] );
}

if ( empty( $book_data['phone'] ) ) {
	$book_data['phone'] = "";
} else {
	$book_data['phone'] = Utils::to_bool( $options['use-outside-iran'] )
		? $book_data['phone']
		: Formatters::phone( $book_data['phone'] );
}

$customer_info = [
	'full_name' => [
		'label'	=> esc_html__( 'Full Name', 'drplus' ),
		'value'	=> "{$book_data['first_name']} {$book_data['last_name']}",
	],
	'birthday'	=> [
		'label'		=> esc_html__( 'Birthday', 'drplus' ),
		'value'		=> !empty( $book_data['birthday'] ) ? date_i18n( 'd F Y', $book_data['birthday'] ) : "",
	],
	'nid'		=> [
		'label'	=> esc_html__( 'National ID', 'drplus' ),
		'value'	=> $book_data['nid'],
		'ltr'	=> true,
	],
	'email'		=> [
		'label'	=> esc_html__( 'Email', 'drplus' ),
		'value'	=> $book_data['email'],
		'ltr'	=> true,
	],
	'phone' => [
		'label'	=> esc_html__( 'Phone', 'drplus' ),
		'value'	=> $book_data['phone'],
		'ltr'	=> true,
	],
	'create_order_date'	=> [
		'label'	=> esc_html__( 'Submit request date', 'drplus' ),
		'value'	=> date_i18n( 'd F Y H:i', $order->get_date_created()->getTimestamp() + wp_timezone()->getOffset( new \DateTime() ) ),
	],
	'book_date'	=> [
		'label'	=> $is_consultation ? esc_html_x( 'Appointment date', 'online consultation', 'drplus' ) : esc_html__( 'Appointment date', 'drplus' ),
		'value'	=> '',
	],
	'reason'	=> [
		'label'		=> esc_html__( 'Reason for visit', 'drplus' ),
		'value'		=> !empty( $book_data['reason'] ) ? $book_data['reason'] : "&nbsp;",
	],
];

if( $order_status == 'cancelled' ) {
	if( !empty( $book_data['canceled_by'] ) ) {
		$customer_info['canceled_by'] = [
			'label'	=> esc_html__( 'Cancelled by', 'drplus' ),
			'value'	=> $book_data['canceled_by'] == 'specialist' ? esc_html__( 'Specialist', 'drplus' ) : ( $book_data['canceled_by'] == 'customer' && $view_type == 'user' ? esc_html__( 'You', 'drplus' ) : esc_html__( 'Admin', 'drplus' ) ),
		];
	}
	$customer_info['canceled_at'] = [
		'label'	=> esc_html__( 'Cancelled at', 'drplus' ),
		'value'	=> !empty( $book_data['canceled_at'] ) ? $book_data['canceled_at'] : '',
	];
	$customer_info['book_date']['label'] .= ' ' . esc_html__( '(Cancelled)', 'drplus' );
}

$custom_fields = ['birthday', 'email', 'phone', 'reason', 'nid'];
foreach( $custom_fields as $field ) {
	if( !$options["booking-info-field-{$field}-enabled"] && empty( $customer_info[$field]['value'] ) ) {
		unset( $customer_info[$field] );
	}
}

$date = date_i18n( Utils::is_iran_timezone() ? 'd F Y' : get_option( 'date_format' ), $book_data['raw_date'] );
$hour = date( "H", $book_data['raw_time']/1000 );
$time = date( "H:i", $book_data['raw_time']/1000 );
$time .= " " . Date::get_time_period( $hour );
$customer_info['book_date']['value'] = "{$date} {$time}";

$transaction_info = [
	'visit_price'	=> [
		'label'	=> esc_html__( 'Visit price', 'drplus' ),
		'value'	=> sprintf( esc_html__( '%s Toman', 'drplus' ), number_format( $book_data['visit_price'], 0 ) ),
	],
	'commission_fee'	=> [
		'label'	=> esc_html__( 'Commission fee', 'drplus' ),
		'value'	=> sprintf( esc_html__( '%s Toman', 'drplus' ), number_format( $book_data['commission_value'], 0 ) ),
	],
	'discount'	=> [
		'label'	=> esc_html__( 'Discount', 'drplus' ),
		'value'	=> sprintf( esc_html__( '%s Toman', 'drplus' ), number_format( $order->get_total_discount(), 0 ) ),
	],
	'transaction_id'	=> [
		'label'	=> esc_html__( 'Transaction ID', 'drplus' ),
		'value'	=> empty( $order->get_transaction_id() ) ? esc_html__( 'N/A', 'drplus' ) : $order->get_transaction_id(),
	],
	'payment_method'	=> [
		'label'	=> esc_html__( 'Payment method', 'drplus' ),
		'value'	=> $order->get_payment_method_title(),
	],
	'payment_date'	=> [
		'label'	=> esc_html__( 'Payment date', 'drplus' ),
		'value'	=> date_i18n( 'd F Y H:i', $order->get_date_created()->getTimestamp() + wp_timezone()->getOffset( new \DateTime() ) ),
	],
	'payment_status'	=> [
		'label'	=> esc_html__( 'Payment status', 'drplus' ),
		'value'	=> $order_status == 'processing' ? esc_html__( 'Paid', 'woocommerce' ) : wc_get_order_status_name( $order_status ),
	],
];

if( $view_type == 'specialist' || $view_type == 'admin' ) {
	$customer_info['office'] = [
		'label'	=> esc_html__( 'Office', 'drplus' ),
		'value'	=> $book_data['office_name']
	];
	$transaction_info['specialist_income'] = [
		'label'	=>  $view_type == 'specialist' ? esc_html__( 'Your income', 'drplus' ) : esc_html__( 'Specialist income', 'drplus' ),
		'value'	=> sprintf( esc_html__( '%s Toman', 'drplus' ), number_format( $book_data['specialist_income'], 0 ) )
	];
	Utils::reposition_array_element( $transaction_info, 'specialist_income', 3 );
}

if( $book_data['commission_calculate_type'] != 'add_to_customer_order' ) {
	$transaction_info['commission_fee']['value'] = sprintf( esc_html__( '%s Toman', 'drplus' ), 0 );
}

$show_cancel_btn = true;
if( in_array( $order_status, ['processing', 'pending'] ) && $view_type != 'thankyou' ) {
	if( $view_type == 'specialist' ) {
		if( !Utils::to_bool( $options['booking-cancel-by-specialist'] ) ) {
			$show_cancel_btn = false;
		}
	} else if( $view_type == 'user' ) {
		if( Utils::to_bool( $options['booking-cancel-by-customer'] ) ) {
			// Check booking-max-cancellation-hours
			$max_cancellation_hours = intval( $options['booking-max-cancellation-hours'] );
			if( $max_cancellation_hours > 0 ) {
				$booking_datetime = strtotime( date( 'Y-m-d', $book_data['raw_date'] ) . " {$book_data['start_time']}" );
				$cancel_before_datetime = $booking_datetime - ( $max_cancellation_hours * HOUR_IN_SECONDS );
				$current_datetime = current_time( 'timestamp' );
				if( $current_datetime > $cancel_before_datetime ) {
					$show_cancel_btn = false;
				}
			}
		} else {
			$show_cancel_btn = false;
		}
	}
} else {
	$show_cancel_btn = false;
}

?>
<div class="drplus-booking-receipt-wrap<?php echo $view_type == 'thankyou' ? ' drplus-booking-receipt-wrap-thankyou' : '' ?>">
	<?php if ( $order->has_status( 'failed' ) ) : ?>
		<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php echo $receipt_title ?></p>
	<?php else : ?>
		<?php if( $view_type == 'thankyou' ) { ?>
			<h3 class="drplus-booking-receipt-title">
				<?php echo $receipt_title ?>
			</h3>
		<?php } ?>
		<div class="drplus-booking-receipt booking-section">
			<div class="drplus-booking-receipt-head">
				<span class="drplus-booking-receipt-id"><?php $is_consultation ? printf( esc_html__( '%s appointment #%s', 'drplus' ), $consultation_office[$book_data['office_id']]['label'] , $book_data['book_id'] ) : printf( esc_html__( 'Appointment #%s', 'drplus' ), $book_data['book_id'] ) ?></span>
				<span class="drplus-booking-receipt-status status-<?php echo $order_status ?>"><?php echo esc_html( Booking::get_order_statuses()[$order_status] ) ?></span>
				<span class="drplus-booking-receipt-app-type <?php echo $is_consultation ? "app-consultation" : "app-office" ?>"><?php echo $is_consultation ? $consultation_office[$book_data['office_id']]['label'] : esc_html__( 'in-person visit', 'drplus' ) ?></span>

				<?php if( Utils::to_bool( $show_back_btn ) ) {
					Button::view( [
						'icon'	=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
						'link'	=> remove_query_arg( 'order_id' ),
						'type'	=> 'gray',
						'small'	=> true,
						'align'	=> 'end'
					] );
				} ?>
			</div>
			<?php if( $view_type == 'specialist'|| $view_type == 'admin' ) { ?>
				<span class="drplus-specialist-apps-customer-info-title drplus-booking-receipt-part-title"><?php esc_html_e( 'Patient information', 'drplus' ) ?>:</span>
			<?php } ?>
			<div class="drplus-booking-receipt-customer-info-wrap<?php echo $view_type != 'specialist' ? ' info-has-desc' : ' booking-section' ?>">
				<div class="drplus-booking-receipt-customer-info">
					<?php
					foreach( $customer_info as $index => $customer_info_item ) {
						$value = $index != 'reason' ? esc_html( $customer_info_item['value'] ) : wpautop( $customer_info_item['value'] );
						?>
						<div class="drplus-booking-receipt-customer-info-item">
							<span class="drplus-booking-receipt-customer-info-label drplus-booking-receipt-part-title"><?php echo esc_html( $customer_info_item['label'] ) ?></span>
							<span class="drplus-booking-receipt-customer-info-value<?php echo !empty( $customer_info_item['ltr'] ) ? ' ltr' : '' ?>"><?php echo $value ?></span>
						</div>
					<?php } ?>
				</div>
				<?php if( $view_type != 'specialist' || $is_consultation || in_array( $order_status, ['processing', 'pending'] ) ) { ?>
					<div class="drplus-booking-receipt-booking-description">
						<?php if( $view_type != 'specialist' ) { ?>
							<span class="drplus-booking-receipt-part-title">
								<?php esc_html_e( 'Description', 'drplus' ) ?>
							</span>
							<div class="drplus-booking-receipt-booking-description-text">
								<?php echo wpautop( esc_html( $receipt_note ) ) ?>
							</div>
						<?php } ?>
						<?php
						if( ( $book_data['office_id'] == 'chat_consultation' || $book_data['office_id'] == 'instant_chat_consultation' ) && !empty( $chat_page_url ) ) {
							Button::view( [
								'text'			=> esc_html__( 'Continue chat', 'drplus' ),
								'link'			=> $chat_page_url,
								'icon'			=> 'drplus-icon-chevron-left-dot',
								'icon_align'	=> 'end',
								'small'			=> true,
								'fullwidth'		=> true,
								'classes'		=> ['drplus-booking-receipt-chat-button'],
							] );
						} else if( $book_data['office_id'] == 'video_consultation' ) {
							if( $video_status == 'ongoing' ) {
								if( $view_type == 'admin' ) {
									?>
									<p class="drplus-booking-receipt-booking-video drplus-booking-receipt-customer-info-value">
										<?php esc_html_e( 'The session is in progress.', 'drplus' ); ?>
									</p>
									<?php
								} else if( !empty( $video_link ) ) {
									if( $view_type == 'admin' ) {
										?>
										<p class="drplus-booking-receipt-booking-video drplus-booking-receipt-customer-info-value">
											<?php esc_html_e( 'The session is in progress.', 'drplus' ); ?>
										</p>
										<?php
									} else {
										if( is_wp_error( $video_link ) ) {
											?>
											<div class="drplus-booking-receipt-create-room-notice" data-args='<?php echo json_encode( [
												'orderID'	=> $args['order']->get_id(),
												'userID'	=> get_current_user_id(),
												'nonce'		=> wp_create_nonce( sprintf( 'drplus_booking_create_room_%s_%s', $args['order']->get_id(), get_current_user_id() ) )
											] ) ?>'>
												<span class="drplus-booking-receipt-create-room-notice-working"><?php esc_html_e( 'Creating room link. Please wait', 'drplus' ) ?></span>
												<span class="drplus-booking-receipt-create-room-notice-error hidden"><?php esc_html_e( 'Error in create room link.', 'drplus' ) ?></span>
												<div class="drplus-booking-receipt-create-room-notice-loading"><?php echo file_get_contents( DRPLUS_DIR . "assets/images/loading.svg" ) ?></div>
											</div>
											<?php
										}
										Button::view( [
											'text'			=> $options['video-enter-btn-text'],
											'link'			=> is_wp_error( $video_link ) ? "#" : $video_link,
											'icon'			=> $options['video-enter-btn-icon'],
											'icon_align'	=> 'end',
											'small'			=> true,
											'fullwidth'		=> true,
											'new_tab'		=> true,
											'classes'		=> ['drplus-booking-receipt-video-btn', is_wp_error( $video_link ) ? "hidden" : ""],
										] );
									}
								} else {
									?>
									<p class="drplus-booking-receipt-booking-video drplus-booking-receipt-customer-info-value">
										<?php esc_html_e( 'Error in create room link.', 'drplus' ); ?>
									</p>
									<?php
								}
							} else {
								?>
								<p class="drplus-booking-receipt-booking-video drplus-booking-receipt-customer-info-value">
									<?php
									if( $video_status == 'not_started' ) {
										if( $view_type == 'specialist' ) {
											echo $options['video-specialist-not-started-text'];
										} else {
											echo $options['video-visitor-not-started-text'];
										}
									} else if( $video_status == 'ended' ) {
										if( $view_type == 'specialist' ) {
											echo $options['video-specialist-ended-text'];
										} else {
											echo $options['video-visitor-ended-text'];
										}
									}
									?>
								</p>
								<?php
							}
						}
						?>
						<?php
						if( $order->has_status( 'pending' ) ) { // Show payment link button
							Button::view( [
								'text'		=> esc_html__( 'Pay for Appointment', 'drplus' ),
								'icon'		=> is_rtl() ? 'drplus-icon-arrow-up-right-square' : 'drplus-icon-arrow-up-left-square',
								'type'		=> 'primary',
								'small'		=> true,
								'classes'	=> ['drplus-booking-receipt-pay-pending-appointment'],
								'align'		=> 'center',
								'link'		=> $order->get_checkout_payment_url(),
							] );
						}
						if( $show_cancel_btn ) {
							// Show cancel booking btn
							$cancelling_nonce = wp_create_nonce( "drplus_cancel_booking_{$book_data['book_id']}" );
							Button::view( [
								'text'		=> esc_html__( 'Cancel Appointment', 'drplus' ),
								'icon'		=> 'drplus-icon-trash-2',
								'type'		=> 'bordered',
								'classes'	=> ['drplus-booking-receipt-cancel-booking-open-popup'],
								'small'		=> true,
								'align'		=> 'center'
							] );
							?>
							<div class="drplus-booking-receipt-cancel-booking-popup">
								<span class="drplus-booking-receipt-cancel-booking-title"><?php esc_html_e( 'Do you want to cancel this appointment?', 'drplus' ) ?></span>

								<div class="drplus-booking-receipt-cancel-booking-btns">
									<?php
									Button::view( [
										'text'		=> esc_html__( 'Cancel Appointment', 'drplus' ),
										'link'		=> add_query_arg( ['action' => 'cancel_app', 'c_nonce' => $cancelling_nonce] ),										
										'type'		=> 'bordered',
										'icon'		=> 'drplus-icon-trash-2',
										'classes'	=> ['drplus-booking-receipt-cancel-booking-btn'],
										'small'		=> true,
										'align'		=> 'center'
									] );
									Button::view( [
										'text'		=> esc_html__( 'Back', 'drplus' ),
										'classes'	=> ['drplus-booking-receipt-back-cancel-booking'],
										'small'		=> true,
										'align'		=> 'center'
									] );
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				<?php } ?>
			</div>
			<?php if( $view_type != 'specialist' ) { ?>
				<div class="drplus-booking-receipt-specialist-info-wrap">
					<span class="drplus-booking-receipt-specialist-info-title drplus-booking-receipt-part-title">
						<?php esc_html_e( 'The selected specialist\'s information:', 'drplus' ) ?>
					</span>
					<div class="drplus-booking-receipt-specialist-info booking-section">
						<?php Booking::specialist_info_html( !empty( $specialist->id ) ? $specialist : $saved_specialist_data ); ?>
						<?php Booking::specialist_office_html( !empty( $specialist->id ) ? $selected_office : (array) $saved_office ); ?>
					</div>
				</div>
			<?php } ?>
			<div class="drplus-booking-receipt-transaction-info-wrap">
				<span class="drplus-booking-receipt-transaction-info-title drplus-booking-receipt-part-title">
					<?php esc_html_e( 'The transaction information:', 'drplus' ) ?>
				</span>
				<div class="drplus-booking-receipt-transaction-info booking-section">
					<div class="drplus-booking-receipt-transaction-info-inner booking-section">
						<?php foreach( $transaction_info as $transaction_info_item ) { ?>
							<div class="drplus-booking-receipt-transaction-info-item">
								<span class="drplus-booking-receipt-transaction-info-label drplus-booking-receipt-part-title"><?php echo esc_html( $transaction_info_item['label'] ) ?></span>
								<span class="drplus-booking-receipt-transaction-info-value<?php echo !empty( $transaction_info_item['ltr'] ) ? ' ltr' : '' ?>"><?php echo $transaction_info_item['value'] ?></span>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php if( $view_type == 'user' ) { ?>
				<?php if( $order_status == 'completed' ) { ?>
					<div class="drplus-booking-receipt-review-wrap" id="drplus-booking-receipt-review">
						<?php
						$user_app_reviews = User::get_user_appointments_reviews();
						if( array_key_exists( $order->get_id(), $user_app_reviews ) ) { // user added a review
							// Show review
							$comment_id = $user_app_reviews[$order->get_id()];
							$comment = get_comment( $comment_id );
							$comment_args = [
								'avatar_size'	=> 75,
								'style'			=> 'div',
								'max_depth'		=> 1,
							];
							if( !empty( $comment ) ) {
							?>
								<div class="drplus-booking-receipt-review-title drplus-booking-receipt-part-title">
									<?php esc_html_e( 'Your review', 'drplus' ) ?>
								</div>
								<div class="drplus-booking-receipt-review-container booking-section">
									<?php drplus_comment_walker( $comment, $comment_args, 1 ) ?>
								</div>
							<?php
							}
						} else if( !empty( $specialist->post_id ) ) {
							ob_start();
							get_template_part( "templates/components/template-components-button", null, [
								'type'			=> 'primary',
								'text'			=> 'button-text',
								'icon'			=> 'drplus-icon-arrow-up-left-square',
								'icon_align'	=> 'end',
								'align'			=> 'end',
								'small'			=> true,
								'classes'		=> ['button-class'],
								'id'			=> 'button-id',
								'atts'			=> [
									'type'	=> 'submit',
									'name'	=> 'button-name'
								],
							] );
							$submit_button = ob_get_clean();
							$submit_button = str_replace( 'button-name', '%1$s', $submit_button );
							$submit_button = str_replace( 'button-id', '%2$s', $submit_button );
							$submit_button = str_replace( 'button-class', '%3$s', $submit_button );
							$submit_button = str_replace( 'button-text', '%4$s', $submit_button );
							$submit_button = '<input type="hidden" name="drplus_comment_order_id" value="' . $order->get_id() . '">' . $submit_button;						
							
							$comment_args = [
								'logged_in_as'			=> '',
								'comment_notes_before'	=> '',
								'title_reply'			=> esc_html__( 'Add a review', 'drplus' ),
								'title_reply_before'  	=> '<span class="drplus-booking-receipt-part-title">',
								'title_reply_after'   	=> '</span>',
								'submit_button'			=> $submit_button
							];
							comment_form( $comment_args, $specialist->post_id );
						}
						?>
					</div>
				<?php } else { ?>
					<span class="drplus-booking-receipt-review-notice">
						<?php esc_html_e( 'You will be able to submit a review after the appointment is completed.', 'drplus' ) ?>
					</span>
				<?php } ?>
			<?php } ?>
		</div>
	<?php endif; ?>
</div>