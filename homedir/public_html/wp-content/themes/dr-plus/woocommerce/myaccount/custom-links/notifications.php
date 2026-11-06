<?php

use DrPlus\Utils\Notifications;
use DrPlus\Utils\Options;
use DrPlus\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$notifications = Notifications::get_current_user_notifications( empty( $_GET['only-unread'] ) );
?>
<h2 class="drplus-myaccount-page-title"><?php esc_html_e( 'Notifications', 'drplus' ) ?></h2>
<div id="notifications-filters" class="drplus_filter_additional_options">
	<?php UI::filter_radio( __( "Show only unread notifications", 'drplus' ), 'only-unread', true, [
		'radio-align'	=> 'start'
	] ) ?>
</div>
<?php if( $notifications ) { ?>
	<div id="notifications-content">
		<div id="notifications">
			<?php
			foreach( $notifications as $notification ) {
				$notification_data = Notifications::get_options( $notification->ID );
				?>
				<div class="notification <?php echo $notification->read ? 'notification-read' : 'notification-unread' ?>" data-id="<?php echo esc_attr( $notification->ID ) ?>">
					<div class="notification-head">
						<div class="notification-title-wrap">
							<?php get_template_part( "templates/components/template-components-simple_icon", null, [
								'icon'		=> 'drplus-icon-notification',
								'classes'	=> ['notification-icon-read'],
							] ); ?>
							<?php get_template_part( "templates/components/template-components-simple_icon", null, [
								'icon'		=> 'drplus-icon-notification-fill',
								'classes'	=> ['notification-icon-unread'],
							] ); ?>
							<h4 class="notification-title"><?php echo esc_html( $notification->post_title ) ?></h4>
						</div>
						
						<div class="notification-detail">
							<?php if( !$notification->read ) { ?>
								<div class="notification-status-unread"><?php echo esc_html__( "Unread", 'drplus' ) ?></div>
							<?php } ?>
							<div class="notification-time"><?php echo esc_html( $notification->post_date ) ?></div>
						</div>

					</div>

					<div class="notification-text"><?php echo wpautop( $notification_data['message'] ) ?></div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } else {
	$options = Options::get_options( [
		'wc_empty_notifications_text'	=> esc_html__( 'The notification list is empty.', 'drplus' ),
		'wc_empty_unread_notifications_text' => esc_html__( 'You have no unread notifications.', 'drplus' ),
	] );
		
	?>
	<div class="empty-page">
		<i class="empty-page-icon empty-cart-icon drplus-icon-notification"></i>
		<div class='empty-page-text'>
			<?php echo esc_html( empty( $_GET['only-unread'] ) ? $options['wc_empty_notifications_text'] : $options['wc_empty_unread_notifications_text'] ) ?>
		</div>
		<?php
		get_template_part( 'templates/components/template-components-button', null, [
			'text'	=> empty( $_GET['only-unread'] ) ? apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to Shop', 'drplus' ) ) : __( 'Show all notifications', 'drplus' ),
			'link'	=> empty( $_GET['only-unread'] ) ? apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) : remove_query_arg( 'only-unread' ),
			'align'	=> 'center',
			'small'	=> true,
		] );
		?>
	</div>
<?php } ?>