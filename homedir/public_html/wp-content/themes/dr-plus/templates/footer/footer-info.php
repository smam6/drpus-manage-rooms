<?php
use DrPlus\Utils;
use DrPlus\Utils\Options;

$use_outside_iran = Utils::to_bool( Options::get_options( ['use-outside-iran' => false] )['use-outside-iran'] );

$contact_infos = [];
$is_rtl = is_rtl();
if( !empty( $args['footer_contact_info'] ) && !empty( $args['footer_contact_info']['footer_contact_items'] ) ) {
	foreach( $args['footer_contact_info']['footer_contact_items'] as $index => $item ) {
		if( is_email( $item ) ) {
			$contact_infos[$index]['text'] = '<a href="mailto:' . $item . '" class="footer-contact footer-email">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		} else if( is_numeric( $item ) ) {
			$contact_infos[$index]['text'] = '<a href="tel:' . Utils::convert_chars( $item ) . '" class="footer-contact footer-phone">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		} else {
			$contact_infos[$index]['text'] = '<a href="' . $item . '" class="footer-phone">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		}

		if( !isset( $args['footer_contact_info']['footer_contact_types'][$index] ) ) continue;

		$type = $args['footer_contact_info']['footer_contact_types'][$index];
		$link = $args['footer_contact_info']['footer_contact_links'][$index];
		if( $type == 'phone' ) {
			$contact_infos[$index]['text'] = '<a href="tel:' . Utils::convert_chars( $item ) . '" class="footer-contact footer-phone" rel="noopener noreferrer nofollow">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		} else if( $type == 'email' ) {
			$contact_infos[$index]['text'] = '<a href="mailto:' . $item . '" class="footer-contact footer-email" rel="noopener noreferrer nofollow">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		} else if( $type == 'address' ) {
			$contact_infos[$index]['text'] = '<address><a href="' . $link . '" class="footer-contact footer-address" rel="noopener noreferrer nofollow">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a></address>';
		} else {
			$contact_infos[$index]['text'] = '<a href="' . $link . '" class="footer-contact footer-info-item" rel="noopener noreferrer nofollow">' . Utils::convert_chars( $item, true, '', $is_rtl ) . '</a>';
		}
		$contact_infos[$index]['icon'] = !empty( $args['footer_contact_info'] ) && !empty( $args['footer_contact_info']['footer_contact_icons'] ) && !empty( $args['footer_contact_info']['footer_contact_icons'][$index] ) ? $args['footer_contact_info']['footer_contact_icons'][$index] : '';
	}
}

?>
<div class="footer-content" id="footer_info">
	<?php
	if( Utils::to_bool( $args['footer_show_menu'] ) ) { ?>
		<nav class="footer-menu-wrap">
			<div class="footer-section-title">
				<span class="footer-section-title-text"><?php echo $args['footer_menu_title'] ?></span>
			</div>
			<?php
			wp_nav_menu( [
				'theme_location'	=> "footer-menu",
				'container_class'	=> "footer-menu",
			] );
			?>
		</nav>
	<?php } ?>
	<?php if( Utils::to_bool( $args['footer_show_contact_info'] ) ) { ?>
		<div id="footer-contact_info">
			<div class="footer-section-title">
				<span class="footer-section-title-text"><?php echo $args['footer_contact_info_title'] ?></span>
			</div>
			<div id="footer-contact_info-inner">
				<?php if( !empty( $args['footer_contact_info_text'] ) ) { ?>
					<div class="footer-contact_info-text">
						<?php echo wpautop( $args['footer_contact_info_text'] ) ?>
					</div>
				<?php } ?>
				<?php if( !empty( $contact_infos ) ) { ?>
					<div class="footer-contacts-wrap">
						<?php foreach( $contact_infos as $contact_info ) { ?>
							<div class="footer-contact-wrap">
								<?php if( !empty( $contact_info['icon'] ) ) { ?>
									<i class="<?php echo $contact_info['icon'] ?>"></i>								
								<?php } ?>
								<?php echo $contact_info['text'] ?>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
</div>