<?php
/*************** NOT COMPLETED ***************/
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'name_field'			=> esc_html_x( 'Name', 'Booking widget', 'drplus' ),
	'phone_field'			=> esc_html__( 'Phone Number', 'drplus' ),
	'Insurance_field'		=> esc_html_x( 'Insurance Type', 'Booking widget', 'drplus' ),
	'specialists_field'		=> esc_html__( 'The desired doctor', 'drplus' ),
	'specialities_field'	=> esc_html__( 'Doctor\'s specialty', 'drplus' ),
	'date_field'			=> esc_html__( 'Visit date', 'drplus' ),
	'time_text'				=> esc_html__( 'Select visit time', 'drplus' ),
	'reserved_slot_text'	=> esc_html__( 'Reserved', 'drplus' ),
	'selected_slot_text'	=> esc_html__( 'Selected', 'drplus' ),
] );

foreach( $args as $key => $value ) {
	$args[$key] = Utils::convert_chars( $value );
}

$booking_page_link = Booking::get_booking_page_url();

$default_time_slots = [
	[
		'time'	=> '10:00',
		'label'	=> __( 'Morning', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '10:30',
		'label'	=> __( 'Morning', 'drplus' ),
		'status'	=> 'reserved',
	],
	[
		'time'	=> '11:00',
		'label'	=> __( 'Morning', 'drplus' ),
		'status'	=> 'reserved',
	],
	[
		'time'	=> '11:30',
		'label'	=> __( 'Morning', 'drplus' ),
		'status'	=> 'reserved',
	],
	[
		'time'	=> '12:00',
		'label'	=> __( 'Morning', 'drplus' ),
		'status'	=> 'reserved',
	],
	[
		'time'	=> '12:30',
		'label'	=> __( 'Noon', 'drplus' ),
		'status'	=> 'reserved',
	],
	[
		'time'	=> '13:00',
		'label'	=> __( 'Noon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '13:30',
		'label'	=> __( 'Noon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '14:00',
		'label'	=> __( 'Noon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '14:30',
		'label'	=> __( 'Noon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '15:00',
		'label'	=> __( 'Afternoon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '15:30',
		'label'	=> __( 'Afternoon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '16:00',
		'label'	=> __( 'Afternoon', 'drplus' ),
		'status'	=> 'available',
	],
	[
		'time'	=> '16:30',
		'label'	=> __( 'Afternoon', 'drplus' ),
		'status'	=> 'available',
	],
];

$button_args = Elementor::get_button_args( $args );
$button_args['atts'] = [
	'type'	=> 'submit',
	'name'	=> 'booking_widget_submit',
];
$button_args['prefix'] = 'button_';
$button_args['button_classes'] = ['drplus-booking-widget-button'];
$button_args['button_align'] = 'end';

$slots_slider_attr = Elementor::get_display_attributes( [
	'desktop_slider'		=> true,
	'desktop_slides_type'	=> 'auto',
	'desktop_slides_space'	=> 16,
	'tablet_slider'			=> true,
	'tablet_slides_type'	=> 'auto',
	'tablet_slides_space'	=> 16,
	'mobile_slider'			=> false,
	'mobile_display'		=> 'flex',
	'mobile_cols'			=> 3,
	'mobile_gap'			=> 16
] );
$time_slots_attributes = [
	'class'	=> array_merge( ['drplus-slider-wrap', 'drplus-booking-widget-time-wrap'], $slots_slider_attr['wrap_classes'] ),
	'data-settings'	=> $slots_slider_attr['args'],
	'style'			=> $slots_slider_attr['style'],
];
$time_slots_wrapper_attributes = [
	'class'	=> array_merge( [
		'wrapper',
		'drplus-booking-widget-time-slots',
	], $slots_slider_attr['classes'] ),
];
?>
<div class="drplus-booking-widget">
	<form action="<?php echo $booking_page_link ?>" method="post">
		<?php wp_nonce_field( "booking_widget_nonce_value", 'booking_widget_nonce' ) ?>
		<div class="drplus-booking-widget-fields">
			<div class="drplus-booking-widget-field-group">
				<input type="text" class="drplus-booking-widget-field drplus-booking-widget-name-field input-transparent" name="drplus_booking_name" placeholder="<?php echo esc_attr( $args['name_field'] ) ?>">
			</div>
			<div class="drplus-booking-widget-field-group">
				<input type="tel" class="drplus-booking-widget-field drplus-booking-widget-phone-field input-transparent" name="drplus_booking_phone" placeholder="<?php echo esc_attr( $args['phone_field'] ) ?>">
			</div>
			<div class="drplus-booking-widget-field-group drplus-booking-widget-insurane-field-group">
				<label class="drplus-booking-widget-field-wrap drplus-booking-widget-field-select-wrap">
					<input type="text" class="drplus-booking-widget-insurance-field drplus-booking-widget-select drplus-booking-widget-field input-transparent" name="drplus_booking_insurance" placeholder="<?php echo esc_attr( $args['Insurance_field'] ) ?>">
					<i class="drplus-icon-bottom" aria-hidden="true"></i>
				</label>
			</div>
			<div class="drplus-booking-widget-field-group drplus-booking-widget-speciality-field-group">
				<label class="drplus-booking-widget-field-wrap drplus-booking-widget-field-select-wrap">
					<input type="text" class="drplus-booking-widget-speciality-field drplus-booking-widget-select drplus-booking-widget-field input-transparent" name="drplus_booking_speciality" placeholder="<?php echo esc_attr( $args['specialities_field'] ) ?>">
					<i class="drplus-icon-bottom" aria-hidden="true"></i>
				</label>
			</div>
			<div class="drplus-booking-widget-field-group drplus-booking-widget-specialist-field-group">
				<label class="drplus-booking-widget-field-wrap drplus-booking-widget-field-select-wrap">
					<input type="text" class="drplus-booking-widget-specialist-field drplus-booking-widget-select drplus-booking-widget-field input-transparent" name="drplus_booking_specialist" placeholder="<?php echo esc_attr( $args['specialists_field'] ) ?>">
					<i class="drplus-icon-bottom" aria-hidden="true"></i>
				</label>
			</div>
			<div class="drplus-booking-widget-field-group drplus-booking-widget-date-field-group">
				<label class="drplus-booking-widget-field-wrap">
					<input type="text" class="drplus-booking-widget-date-field drplus-booking-widget-field input-transparent" name="drplus_booking_date" placeholder="<?php echo esc_attr( $args['date_field'] ) ?>">
					<i class="drplus-icon-bottom" aria-hidden="true"></i>
				</label>
			</div>
		</div>
		<div <?php echo Utils::get_html_attributes( $time_slots_attributes ) ?>>
			<?php get_template_part( "templates/components/template-components-section_title", null, [
				'tag'		=> 'p',
				'title'		=> esc_html( $args['time_text'] ),
				'nav_btns'	=> true,
				'classes'	=> ['drplus-booking-widget-time-title'],
			] ); ?>
			<div <?php echo Utils::get_html_attributes( $time_slots_wrapper_attributes ) ?>>
				<?php foreach( $default_time_slots as $slot ) { ?>
					<label class="drplus-booking-widget-time-slot slider-slide drplus-popover-wrap <?php echo $slot['status'] ?>">
						<?php if( $slot['status'] != 'reserved' ) { ?>
							<input type="radio" name="drplus_booking_time" class="drplus-booking-widget-time-radio" value="<?php echo $slot['time'] ?>">
						<?php } ?>
						<span class="drplus-booking-widget-time-slot-time"><?php echo $slot['time'] ?></span>
						<span class="drplus-booking-widget-time-slot-label"><?php echo $slot['label'] ?></span>
						<div class="drplus-booking-widget-time-slot-note drplus-popover drplus-popover-center">	
							<?php if( $slot['status'] == 'reserved' ) { ?>
								<span class="slot-status"><?php esc_html_e( 'Reserved', 'drplus' ) ?></span>
							<?php } ?>
							<span class="selected-slot"><?php esc_html_e( 'Selected', 'drplus' ) ?></span>
						</div>
						<i class="drplus-icon-tick drplus-booking-widget-time-slot-selected-icon" aria-hidden="true"></i>
					</label>
				<?php } ?>
			</div>
		</div>
		<?php get_template_part( "templates/components/template-components-button", null, $button_args ); ?>
	</form>
</div>