<?php

use DrPlus\Components\Button;
use DrPlus\Model\Specialists;
use DrPlus\Utils\WC;

$show_save_button = true;
$show_back_button = true;
$disabled_save_button = false;
$back_link = wc_get_account_endpoint_url( 'specialist-dashboard' );

$user_id = get_current_user_id();
$specialist = Specialists::query()->where( 'user_id', $user_id )->first();

$sections = WC::specialist_profile_sections();
$current_section = WC::get_current_specialist_profile_section();

$no_form_pages = [
	'dashboard',
	'specialist-appointments',
	'specialist-chats',
	'subscription'
];

$show_form = apply_filters( 'drplus/wc/specialist/profile/show_form', !in_array( $current_section, $no_form_pages ), $current_section, $specialist );

if( $show_form ) {
	$section_title = apply_filters( 'drplus/wc/specialist/profile/section_title', !empty( $sections[$current_section]['label'] ) ? $sections[$current_section]['label'] : $sections['dashboard']['label'], $current_section, $specialist );
	?>
	<?php if( $section_title ) { ?>
		<h2 class="drplus-myaccount-page-title"><?php echo esc_html( $section_title ) ?></h2>
	<?php } ?>
	<form action="" method="post" class="drplus-specialist-form">
		<?php wp_nonce_field( 'drplus_specialist-save', 'nonce' ) ?>
		<input type="hidden" name="section" value="<?php echo esc_attr( $current_section ) ?>">
		<input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id ) ?>">
	<?php
}

include( apply_filters( 'drplus/wc/specialist/profile/section_file', DRPLUS_DIR . "woocommerce/myaccount/custom-links/specialist-dashboard/{$current_section}.php", $current_section, $specialist ) );

$buttons = [];
if( $current_section != 'dashboard' ) {
	if( $show_save_button ) {
		$buttons['save'] = [
			'text'			=> __( "Save", 'drplus' ),
			'icon'			=> 'drplus-icon-tick-circle',
			'icon_align'	=> 'start',
			'fullwidth'		=> true,
			'small'			=> true,
			'classes'		=> ['onboard-next-btn'],
			'id'			=> 'onboard-submit',
			'disabled'		=> $disabled_save_button,
		];
	}
	if( $show_back_button ) {
		$buttons['back'] = [
			'type'			=> 'bordered',
			'text'			=> __( "Back to dashboard", 'drplus' ),
			'link'			=> $back_link,
			'small'			=> true,
			'fullwidth'		=> true,
			'icon'			=> is_rtl() ? 'drplus-icon-left' : 'drplus-icon-right',
			'icon_align'	=> 'end',
			'align'			=> 'end',
		];
	}
}
$buttons = apply_filters( 'drplus/wc/specialist/profile/buttons', $buttons, $current_section, $specialist );
if( !empty( $buttons ) ) {
	?>
	<div class="drplus-specialist-form-actions">
		<?php
		foreach( $buttons as $button ) {
			Button::view( $button );
		}
		?>
	</div>
	<?php if( $show_form ) { ?>
		</form>
	<?php } ?>
	<?php
}