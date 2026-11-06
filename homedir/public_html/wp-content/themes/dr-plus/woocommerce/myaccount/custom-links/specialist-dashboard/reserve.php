<?php

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Booking;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

// int ID for hospital
// uuid for office
$offices = $specialist->offices;
$all_offices_ids = wp_list_pluck( $offices, 'id' );
$offline_offices = array_filter( $offices, fn( $office ) => $office['type'] != 'consultation' );
?>
<div class="drplus-specialist-form-body drplus-specialist-form-reserve">
	<?php if( empty( $_GET['office'] ) || !in_array( Utils::convert_chars( $_GET['office'] ), $all_offices_ids ) ) { ?>
		<label class="checkbox-wrap">
			<input type="checkbox" name="specialist_offline_visit" class="specialist_offline_visit" value="true" <?php checked( true, $specialist->offline_visit ) ?>>
			<?php esc_html_e( 'I am interested in offering online appointment booking.', 'drplus' ) ?>
		</label>
		
		<?php if( $specialist->offline_visit ) { ?>
			<div class="drplus-specialist-form-reserve-offices">
				<?php
				$hospitals_ids = wp_list_pluck( array_filter( $offices, fn($office) => $office['type'] == 'hospital' ), 'id' );
				$hospitals = [];
				if( !empty( $hospitals_ids ) ) {
					$hospitals = get_posts( [
						'post_type' => 'hospital',
						'include'	=> $hospitals_ids,
					] );
				}
				if( !empty( $offline_offices ) || !empty( $hospitals ) ) {
					foreach( $hospitals as $hospital ) {
						?>
						<a href="<?php echo add_query_arg( ['office' => $hospital->ID] ) ?>" class="drplus-specialist-form-reserve-office">
							<?php
							if( has_post_thumbnail( $hospital ) ) {
								echo get_the_post_thumbnail( $hospital, [54, 54] );
							} else {
								?>
								<img src="<?php echo DRPLUS_URI . "assets/images/hospital-placeholder.webp" ?>" alt="<?php echo esc_attr( $hospital->post_title ) ?>">
							<?php } ?>
							<span class="drplus-specialist-form-reserve-office-name"><?php echo esc_html( $hospital->post_title ) ?></span>
						</a>
						<?php
					}
					foreach( $offices as $office ) {
						if( $office['type'] == 'hospital' || $office['type'] == 'consultation' ) continue;
						?>
						<a href="<?php echo add_query_arg( ['office' => $office['id'] ] ) ?>" class="drplus-specialist-form-reserve-office">
							<?php
							if( $office['image'] ) {
								echo wp_get_attachment_image( $office['image'], [54, 54] );
							} else {
								?>
								<img src="<?php echo DRPLUS_URI . "assets/images/hospital-placeholder.webp" ?>" alt="<?php echo esc_attr( $office['name'] ) ?>">
							<?php } ?>
							<span class="drplus-specialist-form-reserve-office-name"><?php echo esc_html( $office['name'] ) ?></span>
						</a>
						<?php
					}
				} else {
					$show_save_button = false;
					?>
					<div class="empty-page">
						<i class="empty-page-icon empty-orders-icon drplus-icon-hospital"></i>
						<p class="empty-page-text empty-orders-text"><?php esc_html_e( "You don't have defined any offices yet.", 'drplus' ) ?></p>
						<?php
						Button::view( [
							'type'			=> 'action',
							'text'			=> __( "Add new offices", 'drplus' ),
							'small'			=> true,
							'icon'			=> 'drplus-icon-add-square',
							'icon_align'	=> 'end',
							'align'			=> 'center',
							'link'			=> wc_get_account_endpoint_url( 'specialist-dashboard/offices' ),
						] );
						?>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

		<label class="checkbox-wrap">
			<input type="checkbox" name="specialist_online_visit" class="specialist_online_visit" value="true" <?php checked( true, $specialist->online_visit ) ?>>
			<?php esc_html_e( 'I am interested in offering online consultation appointments.', 'drplus' ) ?>
		</label>

		<?php if( $specialist->online_visit ) { ?>
			<div class="drplus-specialist-form-reserve-consultation-offices">
				<?php foreach( Booking::consultation_offices( 1 ) as $key => $data ) { ?>
					<div class="drplus-specialist-form-reserve-offices">
						<a href="<?php echo add_query_arg( ['office' => $key] ) ?>" class="drplus-specialist-form-reserve-office">
							<i class="drplus-icon-<?php echo $data['icon'] ?>"></i>
							<span class="drplus-specialist-form-reserve-office-name"><?php echo $data['label'] ?></span>
						</a>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	<?php
	} else {
		$office_id = Utils::convert_chars( $_GET['office'] );
		$index = array_search( $office_id, $all_offices_ids );
		$is_instant_chat = $office_id == 'instant_chat_consultation';

		if( is_numeric( $office_id ) ) {
			$_office = get_post( $office_id );
			$office = [
				'name'					=> $_office->post_title,
				'max_booking_days'		=> $offices[$index]['max_booking_days'] ?? "",
				'min_time_before_book'	=> $offices[$index]['min_time_before_book'] ?? "",
				'custom_off_days'		=> $offices[$index]['custom_off_days'] ?? [],
				'visit_time'			=> $offices[$index]['visit_time'],
				'visit_price'			=> $offices[$index]['visit_price'],
				'enable_booking'		=> $offices[$index]['enable_booking'], 
			];
		} else {
			if( !empty( $offices[$index] ) ) {
				$office = $offices[$index];
			}
		}

		$office_times = Booking::get_times_by_office( $office_id, $specialist );
		$default_times = $office_times['default_times'];
		$days = $office_times['days'];
		?>
		<h3 class="drplus-specialist-form-times-head"><?php echo esc_html( $office['name'] ) ?></h3>

		<section class="onboard-subsection drplus-specialist-form-times-general">
			<div class="onboard-subsection-title"><?php esc_html_e( 'General settings', 'drplus' ) ?></div>
			<div class="onboard-subsection-body">
				<?php
				UI::switch( [
					'name'			=> 'specialist_enable_booking',
					'id'			=> 'specialist_enable_booking',
					'value'			=> 1,
					'active'		=> Utils::to_bool( $office['enable_booking'] ?? 1 ),
					'label'			=> esc_html__( 'Enable for booking', 'drplus' ),
					'input_classes'	=> ['regular-text'],
					'disabled'		=> false,
					'wrap_id'		=> 'drplus-specialist-form-times-enable_booking-wrap',
				] );
				if( !$is_instant_chat ) {
					UI::input_with_label( [
						'label'				=> esc_html__( 'Visit time duration (minutes)', 'drplus' ),
						'type'				=> 'text',
						'value'				=> $office['visit_time'],
						'id'				=> "specialist_visit_time",
						'name'				=> "specialist_visit_time",
						'input_classes'		=> ['input-ltr', 'drplus-numeric-input'],
						'inputmode'			=> 'numeric',
						'required'			=> true
					] );
				}
				$woocommerce_currency = get_woocommerce_currency();
				if( in_array( $woocommerce_currency, ['IRR', 'IRT', 'IRHR', 'IRHT'] ) ) {
					$visit_price_label = esc_html__( 'Visit price (Toman)', 'drplus' );
				} else {
					$visit_price_label = sprintf( esc_html__( 'Visit price (%s)', 'drplus' ), get_woocommerce_currency_symbol() );
				}
				UI::input_with_label( [
					'label'			=> $visit_price_label,
					'type'			=> 'text',
					'value'			=> Formatters::price( $office['visit_price'] ),
					'id'			=> "specialist_visit_price",
					'name'			=> "specialist_visit_price",
					'input_classes'	=> ['input-ltr', 'drplus-price-input', 'drplus-numeric-input'],
					'inputmode'		=> 'numeric',
					'required'		=> true
				] );
				if( !$is_instant_chat ) {
					UI::input_with_label( [
						'label'				=> esc_html__( 'Maximum bookable days', 'drplus' ),
						'type'				=> 'text',
						'value'				=> $office['max_booking_days'] ?? "",
						'id'				=> "specialist_max_booking_days",
						'name'				=> "specialist_max_booking_days",
						'input_classes'		=> ['input-ltr', 'drplus-numeric-input'],
						'inputmode'			=> 'numeric',
						'required'			=> false,
						'description'		=> esc_html__( 'Specify the maximum number of days in which the user can book an appointment. leave empty for no limit', 'drplus' ),
					] );
					UI::input_with_label( [
						'label'				=> esc_html__( 'Minimum Time Before Appointment Booking', 'drplus' ),
						'type'				=> 'text',
						'value'				=> $office['min_time_before_book'] ?? "",
						'id'				=> "specialist_min_time_before_book",
						'name'				=> "specialist_min_time_before_book",
						'input_classes'		=> ['input-ltr', 'drplus-numeric-input'],
						'inputmode'			=> 'numeric',
						'required'			=> false,
						'description'		=> esc_html__( 'This option defines how many hours in advance a user can book an appointment relative to the current time. leave empty for no limit', 'drplus' ),
					] );
				}
				?>
				<div class="input-group">
					<label class="input-label" id="specialist_custom_off_days_label">
						<?php esc_html_e( 'Off days', 'drplus' ); ?>
					</label>
					<div class="input-wrap input-wrap-white" id="specialist_custom_off_days">
						<!-- Each off days as input hidden -->
						<?php foreach( $office['custom_off_days'] as $custom_off_day ) {
							get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/off_day_item_template", null, [
								'text'	=> date_i18n( 'j F Y', $custom_off_day ),
								'value'	=> $custom_off_day,
							] );
						} ?>
						<input type="text" id="specialist_add_off_days" value="<?php echo esc_html__( 'Add', 'drplus' ) ?>" readonly>
					</div>

					<p class="drplus-field-description"><?php esc_html_e( 'Select closed dates for disable booking', 'drplus' ) ?></p>
				</div>
			</div>
		</section>

		<section class="onboard-subsection drplus-specialist-form-times-defaults">
			<div class="onboard-subsection-title"><?php esc_html_e( 'Default times', 'drplus' ) ?></div>
			<div class="onboard-subsection-body">
				<div class="drplus-specialist-form-times">
					<?php
					if( !empty( $default_times ) ) {
						foreach( $default_times as $index => $time ) {
							get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/default_time_row", null, [
								'index'	=> $index,
								'time'	=> $time->toArray(),
							] );
						}	
					} else {
						get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/default_time_row", null, [
							'index'	=> 0,
						] );
					}
					?>
				</div>
				<?php
				Button::view( [
					'type'			=> 'action',
					'text'			=> __( 'Add Time', 'drplus' ),
					'icon'			=> 'drplus-icon-add-square',
					'icon_align'	=> 'end',
					'align'			=> 'end',
					'small'			=> true,
					'classes'		=> ['drplus-specialist-form-times-new'],
					'atts'			=> [
						'type'		=> 'button',
						'data-type'	=> 'default'
					],
				] );
				?>
			</div>
		</section>

		<section class="onboard-subsection drplus-specialist-form-times-days">
			<div class="onboard-subsection-title"><?php esc_html_e( 'Available Days', 'drplus' ) ?></div>
			<div class="onboard-subsection-body">
				<?php
				foreach( $days as $index => $day ) {
					get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/day", null, [
						'index'	=> $index,
						'day'	=> $day,
					] );
				}
				?>
			</div>
		</section>

		<script type="text/html" id="tmpl-specialist_default_time">
			<?php
			get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/default_time_row", null, [
				'index'	=> "{{{data.index}}}",
			] );
			?>
		</script>

		<script type="text/html" id="tmpl-specialist_custom_time">
			<?php
			get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/custom_time_row", null, [
				'index'		=> "{{{data.index}}}",
				'day_index'	=> "{{{data.day_index}}}",
			] );
			?>
		</script>

		<script type="text/html" id="tmpl-specialist_off_day_item_template">
			<?php
			get_template_part( "woocommerce/myaccount/custom-links/specialist-dashboard/reserve/off_day_item_template", null, [
				'text'		=> "{{{data.text}}}",
				'value'	=> "{{{data.value}}}",
			] );
			?>
		</script>
	<?php } ?>
</div>