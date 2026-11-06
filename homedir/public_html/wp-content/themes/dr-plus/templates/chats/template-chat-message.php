<?php

use DrPlus\Utils;

$defaults = [
	'message_id'				=> '',
	'sender_is_current_user'	=> false,
	'message'					=> '',
	'type'						=> 'text',
	'file_url'					=> '',
	'created_at'				=> '',
	'chat_id'					=> '',
	'progressbar'				=> false,
	'sender_name'				=> "",
];
$args = Utils::check_default( $args, $defaults );

$wrap_classes = [
	'chat-message'
];
if( $args['sender_is_current_user'] ) {
	$wrap_classes[] = 'current-user-message';
} else {
	$wrap_classes[] = 'other-user-message';
}

?>
<div class="<?php echo implode( ' ', $wrap_classes )?>"<?php echo !empty( $args['chat_id'] ) ? " id='{$args['chat_id']}'" : "" ?> role="listitem" aria-label="<?php echo $args['sender_is_current_user'] ? esc_attr__('Your message', 'drplus') : esc_attr__('Other user message', 'drplus') ?>" data-message-id="<?php echo esc_attr( $args['message_id'] ) ?>">
	<?php if( !empty( $args['sender_name'] ) ) { ?>
		<span class="chat-message-sender-name"><?php echo esc_html( $args['sender_name'] ) ?></span>
	<?php } ?>
	<div class="chat-message-content">
		<?php if( $args['type'] == 'text' ) { ?>
			<div class="chat-message-text" role="document"><?php echo $args['message'] ?></div>
			<div class="chat-message-text-error">
				<i class="drplus-icon-error"></i>
				<span class="chat-message-text-error-text"></span>
			</div>
		<?php } else if( $args['type'] == 'file' ) { ?>
			<div class="chat-message-file" data-url="<?php echo $args['file_url'] != "{{data.file_url}}" ? esc_url( $args['file_url'] ) : "{{data.file_url}}" ?>" role="group" aria-label="<?php echo esc_attr__('File message', 'drplus') ?>">
				<i class="drplus-icon-paperclip chat-message-file-icon" aria-hidden="true"></i>
				<?php if( $args['progressbar'] ) { ?>
					<div class="chat-upload-progress-container" role="progressbar" aria-label="<?php echo esc_attr__('Uploading file', 'drplus') ?>">
						<i class="drplus-icon-close chat-message-upload-cancel" title="<?php echo esc_html__( 'Cancel', 'drplus' ) ?>" role="button" tabindex="0" aria-label="<?php echo esc_html__( 'Cancel', 'drplus' ) ?>"></i>
						<div class="chat-upload-progress-wrapper"></div>
					</div>
					<i class="drplus-icon-error chat-message-upload-failed-icon" aria-hidden="true"></i>
					<span class="chat-message-upload-failed" role="alert"></span>
				<?php } ?>
				<span class="chat-message-file-name line-clamp line-clamp-1" role="text"><?php echo basename( $args['message'] ) ?></span>
			</div>
		<?php } else if( $args['type'] == 'voice' ) { ?>
			<audio class="chat-message-audio" controls role="audio" aria-label="<?php echo esc_attr__('Voice message', 'drplus') ?>">
				<source src="<?php echo $args['file_url'] != "{{data.file_url}}" ? esc_url( $args['file_url'] ) : "{{data.file_url}}" ?>" type="audio/webm">
			</audio>
		<?php } ?>
		<div class="chat-message-date" role="note"><?php echo $args['created_at'] != '{{data.created_at}}' ?  date_i18n( 'H:i', strtotime( $args['created_at'] ) ) : $args['created_at'] ?></div>
	</div>
</div>