<?php

use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Date;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;

$options = Options::get_options( [
	'booking-info-field-phone-enabled'		=> true,
	'booking-info-field-email-enabled'		=> true,
	'booking-info-field-nid-enabled'		=> true,
	'booking-info-field-gender-enabled'		=> true,
	'booking-info-field-birthday-enabled'	=> true,
	'booking-info-field-reason-enabled'		=> true,
	
	'use-outside-iran'						=> false,
] );

$book_data = $args['book_data'];

$specialist_id = $book_data['specialist_id'];
$specialist = (new Specialists())->find( $specialist_id );

foreach( $specialist->offices as $office ) {
	if( $office['id'] == $book_data['office_id'] ) {
		$selected_office = $office;
		break;
	}
}

$date = $book_data['raw_date'];
$time = $book_data['raw_time'];
$week_name = date_i18n( 'l', $date );
$date = date_i18n( Utils::is_iran_timezone() ? 'd F Y' : get_option( 'date_format' ), $date );
if( $book_data['office_id'] != 'instant_chat_consultation' ) {
	$hour = date( "H", $time/1000 );
	$time = date( "H:i", $time/1000 );
	
	$time .= " " . Date::get_time_period( $hour );
} else {
	$time = esc_html__( 'Instant Chat', 'drplus' );
}

$customer_info = [
	'full_name' => [
		'label'	=> esc_html__( 'Full Name', 'drplus' ),
		'value'	=> "{$book_data['first_name']} {$book_data['last_name']}",
	],
	'phone' => [
		'label'	=> esc_html__( 'Phone', 'drplus' ),
		'value'	=> Utils::to_bool( $options['use-outside-iran'] ) ? $book_data['phone'] : Formatters::phone( $book_data['phone'] ),
		'ltr'	=> true,
	],
	'nid'	=> [
		'label'	=> esc_html__( 'National ID', 'drplus' ),
		'value'	=> $book_data['nid'],
		'ltr'	=> true,
	],
	'email'	=> [
		'label'	=> esc_html__( 'Email', 'drplus' ),
		'value'	=> $book_data['email'],
		'ltr'	=> true,
	],
	'gender'	=> [
		'label'	=> esc_html__( 'Gender', 'drplus' ),
		'value'	=> $book_data['gender'] == 'male' ? esc_html__( 'Male', 'drplus' ) : esc_html__( 'Female', 'drplus' ),
	],
	'birthday'	=> [
		'label'	=> esc_html__( 'Year of birth', 'drplus' ),
		'value'	=> date_i18n( 'd F Y', $book_data['birthday'] ),
	],
	'reason'	=> [
		'label'		=> esc_html__( 'Reason for visit', 'drplus' ),
		'value'		=> !empty( $book_data['reason'] ) ? $book_data['reason'] : "&nbsp;",
	]
];

$custom_fields = ['birthday', 'email', 'phone', 'reason', 'gender', 'nid'];
foreach( $custom_fields as $field ) {
	if( !$options["booking-info-field-{$field}-enabled"] ) {
		unset( $customer_info[$field] );
	}
}

?>
<div class="booking-section booking-checkout-customer-info">
	<?php
	foreach( $customer_info as $index => $customer_info_item ) {
		$value = $index != 'reason' ? esc_html( $customer_info_item['value'] ) : wpautop( $customer_info_item['value'] );
		?>
		<div class="booking-checkout-customer-info-item">
			<span class="booking-checkout-customer-info-label"><?php echo esc_html( $customer_info_item['label'] ) ?></span>
			<span class="booking-checkout-customer-info-value<?php echo !empty( $customer_info_item['ltr'] ) ? ' ltr' : '' ?>"><?php echo !empty( $value ) ? $value : '&nbsp;'  ?></span>
		</div>
	<?php } ?>
</div>

<div class="booking-section booking-checkout-specialist-info">
	<?php Booking::specialist_info_html( $specialist ) ?>
	<?php Booking::specialist_office_html( $selected_office ) ?>
	<div class="booking-checkout-book-info">
		<div class="booking-info-date-wrap">
			<span class="booking-info-week"><?php echo esc_html( $week_name ) ?></span>
			<span class="booking-info-date"><?php echo esc_html( $date ) ?></span>
		</div>
		<span class="booking-info-time"><?php echo esc_html( $time ) ?></span>
	</div>
</div>

<?php if( DRPLUS_DEV ) { ?>
	<pre class="ltr">
		<?php
		if( !empty( $_SESSION['booking'] ) ) {
			print_r( $_SESSION['booking'] );
		} else {
			echo "Session is empty";
		}
		?>
	</pre>
<?php } ?>