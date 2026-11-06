<?php

use DrPlus\Components\Button;
use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlusUtilsChat as Chat;
use DrPlus\Utils\Options;
use DrPlus\Utils\UtilsSpecialists;

$default = [
	'view_type'		=> 'all',
	'chat_id'		=> 0,
];
$args = Utils::check_default( $args, $default );
extract( $args );

$user_id = get_current_user_id();
$chats = Chat::get_sessions( $user_id, $view_type );
$specialist_chat_page = wc_get_account_endpoint_url( 'specialist-dashboard' ) . 'specialist-chats/';
if( $view_type == 'specialist' ) {
	$chats_page_url = $specialist_chat_page;
} else {
	$chats_page_url = wc_get_account_endpoint_url( 'chats' );
}

$ppp = 12;
$current_page = !empty( $_GET['chats-page'] ) ? Utils::convert_chars( $_GET['chats-page'], true, 'absint' ) : 1;
$current_page = $current_page < 1 ? 1 : $current_page;
$offset = ( $current_page - 1 ) * $ppp;
$max_num_page = ceil( count( $chats ) / $ppp );

$chats = array_slice( $chats, $offset, $ppp );

$other_users_info = [];
foreach( $chats as $index => $chat ) {
	$other_user_id = $chat['user_1_id'] == $user_id ? $chat['user_2_id'] : $chat['user_1_id'];
	if( empty( $other_users_info[$other_user_id] ) ) {
		$user = get_user_by( 'id', $other_user_id );
		$other_users_info[$other_user_id] = [
			'display_name'	=> !empty( $user ) ? $user->display_name : __( 'Unknown', 'drplus' ),
			'last_name'		=> !empty( $user ) ? $user->last_name : __( 'Unknown', 'drplus' ),
			'avatar'		=> get_avatar( $other_user_id, 96, 'gravatar_default', __( 'User Avatar', 'drplus' ), ['class' => 'chat-item-user-avatar'] )
		];
		if( $view_type == 'customer' ) {
			// Other user is specialist
			$other_users_info[$other_user_id]['subtitle'] = Specialists::query()->select( 'subtitle' )->where( 'user_id', $other_user_id )->first()->subtitle ?? '';
		}
	}

	$chats[$index]['other_user_info'] = $other_users_info[$other_user_id];
}

?>
<h2 class="drplus-myaccount-page-title" role="heading" aria-level="2"><?php esc_html_e( 'Your Chats', 'drplus' ) ?></h2>
<?php if( $view_type == 'customer' && UtilsSpecialists::is_user_specialist( $user_id, true ) ) { ?>
	<p class="chats-specialist-redirect-info"><?php printf( __( 'To view chats with patients, go to the <a href="%s">chats page</a> on the Specialist Dashboard page.', 'drplus' ), $specialist_chat_page ) ?></p>
<?php } ?>
<?php if( empty( $chats ) ): ?>
	<?php
	$options = Options::get_options( [
		'wc_empty_chats_text'	=> esc_html__( 'You haven\'t started any conversation.', 'drplus' ),
	] );	
	?>
	<div class="empty-page" role="status" aria-live="polite">
		<i class="empty-page-icon empty-cart-icon drplus-icon-messages-2" aria-hidden="true"></i>
		<div class='empty-page-text'>
			<?php echo esc_html( $options['wc_empty_chats_text']  ) ?>
		</div>
	</div>
<?php else: ?>
	<ul class="chats-content" role="list" aria-label="<?php esc_attr_e('Chat sessions', 'drplus') ?>">
		<?php foreach( $chats as $chat ) : ?>
			<?php
			$last_message = "";
			if( !empty( $chat['last_message'] ) ) {
				$last_message = $chat['last_message']['message'];
				if( $chat['last_message']['type'] == 'audio' ) {
					$last_message = esc_html__( 'Sent a voice', 'drplus' );
				}
			}
			?>
			<li class="chat-item<?php echo !$chat['is_seen'] ? ' chat-item-not-seen' : '' ?>" role="listitem" aria-label="<?php echo esc_attr( $chat['other_user_info']['display_name'] ) ?>">
				<a href="<?php echo stripslashes( $chats_page_url ) . intval( $chat['id'] ) ?>" class="chat-item-wrap" role="link" tabindex="0" aria-label="<?php printf( esc_attr__('Open chat with %s', 'drplus'), $chat['other_user_info']['display_name'] ) ?>">
					<div class="chat-item-head">
						<?php echo $chat['other_user_info']['avatar'] ?>
						<div class="chat-item-head-user-info">
							<span class="chat-item-user-name line-clamp line-clamp-1" role="text"><?php echo esc_html( $chat['other_user_info']['display_name'] ) ?></span>
							<?php if( !empty( $chat['other_user_info']['subtitle'] ) ) { ?>
								<span class="chat-item-user-subtitle line-clamp line-clamp-1" role="text"><?php echo esc_html( $chat['other_user_info']['subtitle'] ) ?></span>						
							<?php } ?>
						</div>
						<div class="chat-item-head-time-wrap">
							<span class="chat-item-head-date" role="note"><?php echo date_i18n( 'd F Y', strtotime( esc_html( $chat['last_message_time'] ?? $chat['created_at'] ) ) ) ?></span>
							<span class="chat-item-head-time" role="note"><?php echo date_i18n( 'H:i', strtotime( esc_html( $chat['last_message_time'] ?? $chat['created_at'] ) ) ) ?></span>
						</div>
					</div>
					<div class="chat-item-body">
						<?php if( !empty( $chat['last_message'] ) ) { ?>
							<div class="chat-item-last-message-wrap">
								<?php if( !$chat['is_seen'] ) { ?>
									<span class="chat-item-not-seen-circle" role="status" aria-label="<?php esc_attr_e('New message', 'drplus') ?>"></span>
								<?php } ?>
								<span class="chat-item-last-message-sender" role="text"><?php echo sprintf( $chat['last_message']['type'] == 'audio' ? '%s' : '%s:', $chat['last_message']['sender_id'] == $user_id ? esc_html__( 'You', 'drplus' ) : $chat['other_user_info']['last_name'] ) ?></span>
								<div class="chat-item-last-message line-clamp line-clamp-1" role="text"><?php echo esc_html( $last_message ) ?></div>
							</div>
						<?php } ?>
					</div>
					<div class="chat-item-btns">
						<?php echo Button::view( [
							'text'			=> esc_html__( 'Continue chat', 'drplus' ),
							'icon'			=> 'drplus-icon-chevron-left-dot',
							'icon_align'	=> 'end',
							'align'			=> 'end',
							'small'			=> true,
							'type'			=> 'bordered',
							'atts'			=> [
								'type'	=> 'button'
							]
						] ) ?>
					</div>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
	if( $max_num_page > 1 ) {
		get_template_part( 'templates/archives/template-archives-pagination', 'custom', [
			'max_num_pages'		=> $max_num_page,
			'paged'				=> $current_page,
			'query_arg_name'	=> 'chats-page',
		] );
	}
	?>
<?php endif; ?>