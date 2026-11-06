<?php

use DrPlus\Components\Button;
use DrPlus\Model\Booking;
use DrPlus\Model\ChatSession;
use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlusUtilsChat as Chat;
use DrPlus\Utils\Date;
use DrPlus\Utils\Options;

extract( $args ); // $view_type, $chat_id

$user_id = get_current_user_id();
$chat = ChatSession::find( $chat_id );

if( $view_type == 'admin' ) {
	$user_id = $chat->user_2_id;
}
if( !Chat::is_participant( $chat, $user_id ) ) {
	?>
	<div class="chat-disallowed-access">
		<i class="chat-disallowed-access-icon empty-cart-icon drplus-icon-error"></i>
		<div class='chat-disallowed-access-text'>
			<?php echo esc_html__( 'You don\'t have access to this chat', 'drplus' ) ?>
		</div>
	</div>
	<?php
	return;
}

$back_link = "";
if( $view_type == 'specialist' ) {
	$back_link = wc_get_account_endpoint_url( 'specialist-dashboard' );
	$back_link .= 'specialist-chats/';
} else if( $view_type == 'customer' ) {
	$back_link = wc_get_account_endpoint_url( 'chats' );
}

if( !empty( $_GET['close_nonce'] ) ) {
	// check nonce
	$nonce = Utils::convert_chars( $_GET['close_nonce'] );
	if( !wp_verify_nonce( $nonce, "drplus_close_session_{$chat_id}" ) ) return;
	$chat->is_closed = 1;
	$chat->closed_at = Date::maybe_j2g( date_i18n( 'Y-m-d H:i:s' ) );
	$chat->save();

	if( !empty( $chat->context_id ) ) {
		$book = Booking::query()->select( 'order_id' )->where( 'book_id', $chat->context_id )->first();
		if( $book ) {
			$order = wc_get_order( $book->order_id );
			if( $order ) {
				$order->update_status( 'completed' );
			}
		}
	}
}

// get other user info
$other_user = [];
$other_user_id = $chat['user_1_id'] == $user_id ? $chat['user_2_id'] : $chat['user_1_id'];
$other_user_data = get_user_by( 'id', $other_user_id );
$other_user = [
	'display_name'	=> !empty( $other_user_data ) ? $other_user_data->display_name : __( 'Unknown', 'drplus' ),
	'avatar_url'	=> get_avatar_url( $other_user_id ),
];
if( $view_type == 'customer' || $view_type == 'admin' ) {
	// Other user is specialist
	$specialist = Specialists::query()->select( ['subtitle', 'post_id'] )->where( 'user_id', $other_user_id )->first();
	$other_user['subtitle'] = $specialist->subtitle ?? "";
}

// Get appointment receipt link
$receipt_url = "";
if( !empty( $chat->context_id ) ) {
	$order_id = Booking::query()->select( 'order_id' )->where( 'book_id', $chat->context_id )->first()->order_id;
	if( !empty( $order_id ) ) {
		if( $view_type == 'customer' ) {
			$receipt_url = add_query_arg( 'order_id', $order_id, wc_get_account_endpoint_url( 'appointments' ) );
		} else {
			$receipt_url = add_query_arg( 'order_id', $order_id, wc_get_account_endpoint_url( 'specialist-dashboard/specialist-appointments' ) );
		}
	}
}

$messages = Chat::get_messages( $chat_id );

$last_message_date = "";
$flag_new_messages = false;

$chat_status = 'open';
if( $chat->is_closed ) {
	$chat_status = 'closed';
} else if( $chat->open_at > Date::maybe_j2g( date_i18n( 'Y-m-d H:i:s' ) ) ) {
	$chat_status = 'not_open';
}

$chat_options = Options::get_options( [
	'chat-enable-voice-record'				=> true,
	'chat-enable-send-file'					=> true,
	'chat-page-fullscreen'					=> true,
	'chat-page-get-messages-with-ajax'		=> true,
	'chat-page-get-message-ajax-interval'	=> '5',
	'chat-page-background-type'				=> 'predefined',
	'chat-page-predefined-background'		=> 'chat-bg-1',
	'chat-page-custom-background'			=> []
] );

wp_localize_script( 'drplus-chat', 'drplusChat', [
	'chatID'					=> $chat_id,
	'userID'					=> $user_id,
	'restNonce'					=> wp_create_nonce( 'wp_rest' ),
	'lastMessageID' 			=> !empty( $messages ) ? end( $messages )['id'] : 0,
	'chatStatus' 				=> $chat_status,
	'ajaxCheckMessage'			=> Utils::to_bool( $chat_options['chat-page-get-messages-with-ajax'] ),
	'ajaxCheckMessageInterval'	=> intval( $chat_options['chat-page-get-message-ajax-interval'] ),
	'allowedFileTypes'			=> array_values( Chat::allowed_file_types( 1 ) ),
	'sendFile'					=> Utils::to_bool( $chat_options['chat-enable-send-file'] ),
	'recordVoice'				=> Utils::to_bool( $chat_options['chat-enable-voice-record'] ),
	'siteUrl'					=> get_site_url( null, '', $_SERVER['HTTP_HOST'] === 'localhost' ? 'http' : 'https' ),
	'senderType'				=> $view_type == 'customer' ? 'patient' : 'specialist',
	'i18n'						=> [
		'invalidFileType'		=> esc_html__( 'File type not allowed.', 'drplus' ),
		'sendMessageFailed'		=> esc_html__( 'Send message failed!', 'drplus' ),
		'uploadFailed'			=> esc_html__( 'Upload failed!', 'drplus' ),
		'uploadCancelled'		=> esc_html__( 'Upload cancelled!', 'drplus' ),
		'uploading'				=> esc_html__( 'Uploading...', 'drplus' ),
		'uploadingVoice'		=> esc_html__( 'Uploading voice...', 'drplus' ),
		'voiceNotSupported'		=> esc_html__( 'Your browser does not support audio recording.', 'drplus' ),
		'micPermissionDenied'	=> esc_html__( 'Microphone permission denied.', 'drplus' ),
	]
] );
?>
<div class="chat-content<?php echo Utils::to_bool( $chat_options['chat-page-fullscreen'] ) ? ' fullscreen' : '' ?>" role="main">
	<div class="chat-header" role="banner">
		<a href="<?php echo $back_link ?>" class="chat-header-back" title="<?php echo esc_html__( 'Back to chats', 'drplus' ) ?>" role="button" aria-label="<?php echo esc_html__( 'Back to chats', 'drplus' ) ?>">
			<i class="drplus-icon-arrow-<?php echo is_rtl() ? 'right' : 'left' ?>" aria-hidden="true"></i>
		</a>
		<div class="chat-header-other-user-wrap" role="presentation">
			<img src="<?php echo $other_user['avatar_url'] ?>" alt="" class="chat-header-other-user-avatar" role="img" aria-label="<?php echo esc_attr( $other_user['display_name'] ) ?>">
			<div class="chat-header-other-user-info" role="presentation">
				<span class="chat-header-other-user-name" role="text">
					<?php echo esc_html( $other_user['display_name'] ) ?>
				</span>
				<?php if( !empty( $other_user['subtitle'] ) ) { ?>
					<span class="chat-header-other-user-subtitle line-clamp line-clamp-1" role="text">
						<?php echo esc_html( $other_user['subtitle'] ) ?>
					</span>
				<?php } ?>
			</div>
		</div>
		<?php if( $view_type != 'admin' ) { ?>
			<div class="chat-header-action-wrap" role="navigation" aria-label="Chat actions">
				<i class="chat-header-action-icon drplus-icon-menu" title="<?php echo esc_html_e( 'More info', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php echo esc_html__( 'More info', 'drplus' ) ?>"></i>
				<div class="chat-header-action-list" role="menu">
					<?php if( $view_type == 'customer' ) { ?>
						<a href="<?php echo !empty( $specialist->post_id ) ? esc_url( get_permalink( $specialist->post_id ) ) : '#' ?>" target="_blank" class="chat-header-action-item" role="menuitem">
							<i class="drplus-icon-profile chat-header-action-item-icon"></i>
							<?php echo esc_html__( 'View profile', 'drplus' ) ?>
						</a>
					<?php } ?>
					<?php if( !empty( $receipt_url ) ) { ?>
						<a href="<?php echo esc_url( $receipt_url ) ?>" target="_blank" class="chat-header-action-item" role="menuitem">
							<i class="drplus-icon-documentmoney chat-header-action-item-icon"></i>
							<?php esc_html_e( 'View appointment detail', 'drplus' ) ?>
						</a>
					<?php } ?>
					<?php if( $view_type == 'specialist' && !$chat->is_closed ) { ?>
						<?php
						$close_session_link = add_query_arg( 'close_nonce', wp_create_nonce( "drplus_close_session_{$chat_id}" ) )	
						?>
						<a href="<?php echo $close_session_link ?>" class="chat-header-action-item chat-close-session" role="menuitem">
							<i class="drplus-icon-close-circle-bold chat-header-action-item-icon"></i>
							<?php echo esc_html__( 'Close chat', 'drplus' ) ?>
						</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="chat-messages" role="log" aria-live="polite" aria-relevant="additions text">
		<?php if( $chat_status == 'not_open' ) { ?>
			<span class="chat-not-opened"><?php printf( esc_html__( 'Chat will opened at %s %s', 'drplus' ), date_i18n( 'd F Y', strtotime( $chat->open_at ) ), date_i18n( 'H:i', strtotime( $chat->open_at ) ) ) ?></span>
		<?php } ?>
		<div class="chat-new-messages-notif" role="status" aria-live="polite">
			<span class="chat-new-message-count"></span>
			<span><?php esc_html_e( 'New message(s)', 'drplus' ) ?></span>
			<i class="drplus-icon-arrow-down"></i>
		</div>
		<?php foreach( $messages as $message ) { ?>
			<?php
			if( $view_type == 'admin' )	{
				if( $message['sender_id'] == $user_id ) {
					$sender_name = $_customer_name;
				} else {
					$sender_name = $_specialist_name;
				}
			}
			?>
			<?php if( $message['sender_id'] != $user_id && !$message['is_seen'] && !$flag_new_messages && $view_type != 'admin' ) { ?>
				<div class="chat-new-messages-separator"><?php esc_html_e( 'New messages', 'drplus' ) ?></div>
				<?php $flag_new_messages = true; ?>
			<?php } ?>
			<?php if( date_i18n( 'j F Y', strtotime( $message['created_at'] ) ) != $last_message_date ) { ?>
				<?php $last_message_date = date_i18n( 'j F Y', strtotime( $message['created_at'] ) ) ?>
				<div class="chat-date-separator">
					<?php echo $last_message_date ?>
				</div>
			<?php } ?>
			<?php get_template_part( 'templates/chats/template-chat-message', null, [
				'message_id'				=> $message['id'],
				'sender_is_current_user'	=> $user_id == $message['sender_id'],
				'message'					=> $message['type'] == 'text' ? wpautop( $message['message'] ) : $message['message'],
				'type'						=> $message['type'],
				'file_url'					=> $message['type'] == 'voice' ? add_query_arg( 'chat_file', esc_url( $message['file_url'] ), home_url() ) : $message['file_url'],
				'created_at'				=> $message['created_at'],
				'sender_name'				=> $sender_name ?? "" // only for admin view
			] );
			?>
		<?php } ?>
	</div>
	<?php if( $chat_status == 'open' && $view_type != 'admin' ) { ?>
		<div class="chat-send-container" role="form" aria-label="<?php esc_html_e( 'Send message', 'drplus' ) ?>">
			<div class="chat-send-action" role="group" aria-label="<?php esc_html_e( 'Send actions', 'drplus' ) ?>">
				<?php if( Utils::to_bool( $chat_options['chat-enable-voice-record'] ) ) { ?>
					<i class="drplus-icon-microphone chat-record-voice-btn font-size-transition" id="chat-record-voice-btn" title="<?php esc_html_e( 'Send voice', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php esc_html_e( 'Send voice', 'drplus' ) ?>"></i>
					<i class="drplus-icon-send chat-send-voice font-size-transition" id="chat-send-voice-btn" title="<?php esc_html_e( 'Send voice', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php esc_html_e( 'Send voice', 'drplus' ) ?>"></i>
				<?php } ?>
				<i class="drplus-icon-send chat-send-btn font-size-transition" id="chat-send-btn" title="<?php esc_html_e( 'Send message', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php esc_html_e( 'Send message', 'drplus' ) ?>"></i>
			</div>
			<div class="chat-send-input-wrap" role="presentation">
				<?php if( Utils::to_bool( $chat_options['chat-enable-voice-record'] ) ) { ?>
					<div class="chat-send-recording-voice-wrap" role="status" aria-live="polite">
						<span class="chat-send-recording-voice-time" role="timer"></span>
						<i class="drplus-icon-voice-square chat-send-recording-voice-icon" aria-hidden="true"></i>
						<span class="chat-send-recording-voice-text" role="text"><?php esc_html_e( 'Recording voice', 'drplus' ) ?></span>
					</div>
				<?php } ?>
				<textarea id="chat-send-input" class="chat-send-input" placeholder="<?php esc_html_e( 'Message', 'drplus' ) ?>" rows="1" aria-label="<?php esc_html_e( 'Message', 'drplus' ) ?>"<?php echo wp_is_mobile() ? '' : ' autofocus' ?>></textarea>
			</div>
			<div class="chat-send-attachment-wrap" role="group" aria-label="<?php esc_html_e( 'Attachment actions', 'drplus' ) ?>">
				<?php if( Utils::to_bool( $chat_options['chat-enable-send-file'] ) || Utils::to_bool( $chat_options['chat-enable-voice-record'] ) ) { ?>
					<?php if( Utils::to_bool( $chat_options['chat-enable-send-file'] ) ) { ?>
						<i class="drplus-icon-paperclip chat-send-attachment-btn font-size-transition" id="chat-send-attachment-btn" title="<?php esc_html_e( 'Send file', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php esc_html_e( 'Send file', 'drplus' ) ?>"></i>
						<input type="file" class="chat-send-attachment" id="chat-send-attachment" accept="<?php echo implode( ',', array_values( Chat::allowed_file_types( 1 ) ) ) ?>" aria-label="<?php esc_html_e( 'Send file', 'drplus' ) ?>">
					<?php } ?>
					<?php if( Utils::to_bool( $chat_options['chat-enable-voice-record'] ) ) { ?>
						<i class="drplus-icon-trash-2 chat-cancel-record-voice-btn font-size-transition" id="chat-cancel-record-voice-btn" title="<?php esc_html_e( 'Cancel voice recording', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php esc_html_e( 'Cancel voice recording', 'drplus' ) ?>"></i>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	<?php } else if( $chat_status == 'closed' ) { ?>
		<div class="chat-closed-wrap" role="status" aria-live="polite">
			<span class="chat-closed-info">
				<?php printf( esc_html__( 'This chat was closed at %s', 'drplus' ), date_i18n( 'j F Y - H:i', strtotime( $chat['closed_at'] ?: $chat['updated_at'] ) ) ) ?>
			</span>
			<?php if( $view_type == 'customer' ) {
				Button::view( [
					'text'	=> esc_html__( 'Submit Review', 'drplus' ),
					'link'	=> $receipt_url . '#drplus-booking-receipt-review',
					'small'	=> true,
					'icon'	=> 'drplus-icon-like',
					'icon_align' => 'end'
				] );
			} ?>
		</div>
	<?php } ?>
</div>
<script type="text/html" id="tmpl-chat-current-user-message-text">
	<?php get_template_part( 'templates/chats/template-chat-message', null, [
		'message_id'				=> "{{data.message_id}}",
		'sender_is_current_user'	=> true,
		'message'					=> "{{{data.message}}}",
		'type'						=> 'text',
		'file_url'					=> '',
		'created_at'				=> "{{data.created_at}}"
	] );
	?>
</script>
<script type="text/html" id="tmpl-chat-other-user-message-text">
	<?php get_template_part( 'templates/chats/template-chat-message', null, [
		'message_id'				=> "{{data.message_id}}",
		'sender_is_current_user'	=> false,
		'message'					=> "{{{data.message}}}",
		'type'						=> 'text',
		'file_url'					=> "",
		'created_at'				=> "{{data.created_at}}"
	] );
	?>
</script>
<?php if( Utils::to_bool( $chat_options['chat-enable-send-file'] ) ) { ?>
	<script type="text/html" id="tmpl-chat-current-user-message-file">
		<?php get_template_part( 'templates/chats/template-chat-message', null, [
			'message_id'				=> "{{data.message_id}}",
			'sender_is_current_user'	=> true,
			'message'					=> "{{data.message}}",
			'type'						=> 'file',
			'file_url'					=> "{{data.file_url}}",
			'created_at'				=> "{{data.created_at}}",
			'chat_id'					=> "{{data.chat_id}}",
			'progressbar'				=> true,
		] );
		?>
	</script>
	<script type="text/html" id="tmpl-chat-other-user-message-file">
		<?php get_template_part( 'templates/chats/template-chat-message', null, [
			'message_id'				=> "{{data.message_id}}",
			'sender_is_current_user'	=> false,
			'message'					=> "{{data.message}}",
			'type'						=> 'file',
			'file_url'					=> "{{data.file_url}}",
			'created_at'				=> "{{data.created_at}}"
		] );
		?>
	</script>
<?php } ?>
<?php if( Utils::to_bool( $chat_options['chat-enable-voice-record'] ) ) { ?>
	<script type="text/html" id="tmpl-chat-current-user-message-voice">
		<?php get_template_part( 'templates/chats/template-chat-message', null, [
			'message_id'				=> "{{data.message_id}}",
			'sender_is_current_user'	=> true,
			'message'					=> "",
			'type'						=> 'voice',
			'file_url'					=> "{{data.file_url}}",
			'created_at'				=> "{{data.created_at}}",
			'chat_id'					=> "{{data.chat_id}}",
			'progressbar'				=> true,
		] );
		?>
	</script>
	<script type="text/html" id="tmpl-chat-other-user-message-voice">
		<?php get_template_part( 'templates/chats/template-chat-message', null, [
			'message_id'				=> "{{data.message_id}}",
			'sender_is_current_user'	=> false,
			'message'					=> "",
			'type'						=> 'voice',
			'file_url'					=> "{{data.file_url}}",
			'created_at'				=> "{{data.created_at}}"
		] );
	?>
	</script>
<?php } ?>
<script type="text/html" id="tmpl-chat-upload-file">
	<div id="chat-upload-progress" style="width:100%;margin-top:8px;position: absolute;width: 70px;background: var(--gray-200);">
		<div style="height: 6px; background: rgb(30, 144, 255); width: 71%; border-radius: 3px; transition: width 0.2s;" class="bar">
		</div>
	</div>
</script>