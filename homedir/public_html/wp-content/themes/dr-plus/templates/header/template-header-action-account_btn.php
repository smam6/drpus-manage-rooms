<?php

use DrPlus\Utils;
use DrPlus\Utils\Notifications;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\User;

$my_account_link = Utils::is_wc_active() ? home_url( 'my-account' ) : home_url();
$account_btn_text = "";
$is_user_logged_in = is_user_logged_in();

if( $is_user_logged_in ) {
	$user = wp_get_current_user();
	$account_btn_link = $my_account_link;
	$account_btn_text = $user->display_name;
} else {
	$account_btn_link = home_url( "?login=true" );
}

$options = Options::get_options( [
	'show-account-btn-menu'	=> true,
	'show-notif-count'		=> true,
] );
if( $is_user_logged_in ) {
	$user_options = Options::get_options( [
		'account-btn-user-text-type'		=> 'username',
		'account-btn-user-text'				=> __( 'Account', 'drplus' ),
		'account-btn-user-attachment-type'	=> 'avatar',
		'account-btn-user-icon'				=> 'drplus-icon-user',
		'account-btn-link'					=> $account_btn_link,
		'account-btn-user-link-newtab'		=> false,
	] );
} else {
	$user_options = Options::get_options( [
		'account-btn-text-type'			=> 'none',
		'account-btn-text'				=> __( 'Account', 'drplus' ),
		'account-btn-attachment-type'	=> 'icon',
		'account-btn-icon'				=> 'drplus-icon-user',
		'guest-login-url'				=> $account_btn_link,
		'account-btn-link-newtab'		=> false,
	] );
}
$options = array_merge( $options, $user_options );

$args = Utils::check_default( $args, [
	'call_mode'						=> 'template', // template | elementor
	'mobile_mode'					=> false,
	'show_arrow'					=> false,
	'account-btn-text-type'			=> !$is_user_logged_in ? $options['account-btn-text-type'] : $options['account-btn-user-text-type'], // username | custom_text | none
	'account-btn-text'				=> !$is_user_logged_in ? $options['account-btn-text'] : $options['account-btn-user-text'],
	'account-btn-attachment-type'	=> !$is_user_logged_in ? $options['account-btn-attachment-type'] : $options['account-btn-user-attachment-type'], // avatar | icon | none
	'account-btn-attachment-align'	=> 'end', // start | end
	'account-btn-icon'				=> !$is_user_logged_in ? $options['account-btn-icon'] : $options['account-btn-user-icon'],
	'account-btn-link'				=> !$is_user_logged_in ? $options['guest-login-url'] : $options['account-btn-link'],
	'account-btn-link-newtab'		=> !$is_user_logged_in ? $options['account-btn-link-newtab'] : $options['account-btn-user-link-newtab'],
	'show-account-btn-menu'			=> Utils::to_bool( $options['show-account-btn-menu'] ),
	'account-btn-menu-align'		=> 'p-start',
	'show-notif-count'				=> Utils::to_bool( $options['show-notif-count'] ),
	'show_user_name_in_menu'		=> false,
	'show_user_email_in_menu'		=> false,
	'show_signout_in_menu'			=> false,
], ['account-btn-icon'] );

if( $args['account-btn-text-type'] == 'custom_text' )  {
	$account_btn_text = Utils::convert_chars( $args['account-btn-text'] );
}

if( empty( $args['account-btn-attachment-align'] ) ) $args['account-btn-attachment-align'] = 'end';
if( empty( $args['account-btn-icon'] ) ) $args['account-btn-icon'] = Sanitizers::icon( 'drplus-icon-user', 'header-action-icon header-account-icon' );

if( $args['show-account-btn-menu'] ) {
	$account_btn_items = User::get_account_menu_items();
}
$notif_count = 0;
if( $args['show-notif-count'] ) {
	$notif_count = Notifications::count_user_unread();
}

$account_btn_args = [
	'class'	=> ['header-action-btn', 'header-account'],
];
if( !$is_user_logged_in ) $account_btn_args['class'][] = "guest-user";
if( Utils::to_bool( $args['account-btn-link-newtab'] ) ) {
	$account_btn_args['target'] = '_blank';
	$account_btn_args['rel'] = 'noopener noreferrer';
}

$wrap_classes = [
	'header-action',
	'header-account-wrap'
];
if( !$is_user_logged_in ) $wrap_classes[] = 'guest-user-wrap';
if( $args['call_mode'] == 'template' || $args['mobile_mode'] == true ) {
	if( $args['mobile_mode'] ) {
		$wrap_classes[] = 'hide-desktop';
		$wrap_classes[] = 'hide-tablet';
		$wrap_classes[] = 'has-btn-arrow';
	} else {
		$wrap_classes[] = 'hide-mobile';
	}
}
if( $args['show_arrow'] ) $wrap_classes[] = 'has-btn-arrow';
if( $args['show-notif-count'] && $notif_count ) $wrap_classes[] = 'has-notif-badge';

if( !in_array( 'has-btn-arrow', $wrap_classes ) ) {
	$account_btn_args['href'] = esc_url( $args['account-btn-link'] );
} 

if( $args['account-btn-text-type'] == 'none' ) {
	$account_btn_text = "";
}

$account_btn_attachment = "";
if( $args['account-btn-attachment-type'] == 'avatar' && $is_user_logged_in ) {
	$account_btn_attachment = '<span class="header-account-avatar-wrap">' . get_avatar( $user->ID, 48, '', $user->display_name, ['class' => 'account-avatar'] ) . '</span>';
} else if( $args['account-btn-attachment-type'] == 'icon' ) {
	if( $account_btn_text ) {
		$account_btn_attachment = '<span class="header-account-avatar-wrap">';
	}
	$account_btn_attachment .= Sanitizers::icon( $args['account-btn-icon'], 'header-account-btn-icon' );
	if( $account_btn_text ) {
		$account_btn_attachment .= '</span>';
	}
}
?>
<div class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
	<?php if( Utils::to_bool( $args['show-notif-count'] ) ) {
		if( $notif_count > 0 ) { ?>
			<div class="account-notif-count-wrap account-notif-count-wrap-float">
				<span class="account-notif-count"><?php echo esc_html( $notif_count ) ?></span>
			</div>
		<?php }
	} ?>

	<a <?php echo Utils::get_html_attributes( $account_btn_args ) ?>>
		<?php if( $is_user_logged_in ) { ?>
			<?php if( $args['account-btn-attachment-align'] == 'start' && !empty( $account_btn_text ) ) { ?>
				<span class="header-account-display_name line-clamp line-clamp-1"><?php echo esc_html( $account_btn_text ) ?></span>
			<?php } ?>
			<?php echo $account_btn_attachment ?>
			<?php if( $args['account-btn-attachment-align'] == 'end' && !empty( $account_btn_text ) ) { ?>
				<span class="header-account-display_name line-clamp line-clamp-1"><?php echo esc_html( $account_btn_text ) ?></span>
			<?php } ?>
		<?php } else { ?>
			<?php if( $args['account-btn-attachment-align'] == 'start' && !empty( $account_btn_text ) ) { ?>
				<span class="header-account-display_name line-clamp line-clamp-1"><?php echo esc_html( $account_btn_text ) ?></span>
			<?php } ?>
			<?php echo $account_btn_attachment; ?>
			<?php if( $args['account-btn-attachment-align'] == 'end' && !empty( $account_btn_text ) ) { ?>
				<span class="header-account-display_name line-clamp line-clamp-1"><?php echo esc_html( $account_btn_text ) ?></span>
			<?php } ?>
		<?php } ?>
		<?php if( ( $args['call_mode'] == 'template' && $args['mobile_mode'] ) || $args['show_arrow'] ) { ?>
			<i class="header-account-active-icon drplus-icon-bottom"></i>
		<?php } ?>
	</a>

	<?php if( Utils::to_bool( $args['show-account-btn-menu'] ) && !empty( $account_btn_items ) ) { ?>
		<ul class="account-items header-account-items header-popover <?php echo $args['account-btn-menu-align'] ?>">
			<?php if( $is_user_logged_in && ($args['show_user_name_in_menu'] || $args['show_user_email_in_menu'] ) ) { ?>
				<?php $user = wp_get_current_user() ?>
				<li class="account-item account-user-info">
					<span class="account-item-label">
						<?php if( $args['show_user_name_in_menu'] ) { ?>
							<span class="account-user_name"><?php echo $user->display_name ?></span>
						<?php } ?>
						<?php if( $args['show_user_email_in_menu'] ) { ?>
							<span class="account-user_email"><?php echo $user->user_email ?></span>
						<?php } ?>
					</span>
				</li>
			<?php } ?>
			<?php foreach( $account_btn_items as $index => $item ) {
				$item_class = 'account-item';
				if( is_string( $index ) ) $item_class .= " account-{$index}";
				?>
				<li class="<?php echo $item_class ?>">
					<a href="<?php echo $item['link'] ?>" class="account-item-link">
						<?php if( !empty( $item['icon'] ) ) { ?>
							<i class="account-item-icon <?php echo $item['icon'] ?>" aria-hidden="true"></i>
						<?php } ?>
						<span class="account-item-label">
							<?php echo $item['label'] ?>
							<?php
							if( Utils::to_bool( $args['show-notif-count'] ) && str_contains( $item['link'], 'notifications' ) ) {
								// Check url
								$parsed_url = wp_parse_url( $item['link'] );
								$path_parts = explode( '/', trim( $parsed_url['path'], '/' ) );
								if( end( $path_parts ) == 'notifications' ) {
									if( $notif_count > 0 ) { ?>
										<div class="account-notif-count-wrap">
											<span class="account-notif-count"><?php echo esc_html( $notif_count ) ?></span>
										</div>
									<?php }
								}
							}
							?>
						</span>
						<i class="account-item-hover-icon drplus-icon-square-arrow-<?php echo is_rtl() ? 'right' : 'left' ?>"></i>
					</a>
				</li>
			<?php } ?>
			<?php if( $is_user_logged_in && $args['show_signout_in_menu'] ) { ?>
				<li class="account-item account-user-signout">
					<a href="<?php echo wp_logout_url( $my_account_link ) ?>" class="account-item-link">
						<i class="account-item-icon drplus-icon-logout" aria-hidden="true"></i>
						<span class="account-item-label"><?php esc_html_e( 'Sign Out', 'drplus' ) ?></span>
						<i class="account-item-hover-icon drplus-icon-square-arrow-<?php echo is_rtl() ? 'right' : 'left' ?>"></i>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
</div>