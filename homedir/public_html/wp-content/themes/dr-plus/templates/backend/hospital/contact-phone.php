<?php

use DrPlus\Utils;

$prefix = 'drplus_hospital_';

$args = Utils::check_default( $args, [
	'index'	=> 0,
	'title'	=> '',
	'phone'	=> '',
], ['index'] );
?>
<div class="<?php echo $prefix ?>contact-slot <?php echo $prefix ?>phone-slot" data-swapy-slot="phone-slot-<?php echo esc_attr( $args['index'] ) ?>">
	<div class="<?php echo $prefix ?>contact-item <?php echo $prefix ?>phone" data-swapy-item="phone-<?php echo esc_attr( $args['index'] ) ?>">
		<i class="dashicons dashicons-menu-alt3 <?php echo $prefix ?>contact-move <?php echo $prefix ?>phone-move <?php echo $prefix ?>move" data-swapy-handle></i>
		<i class="dashicons dashicons-trash <?php echo $prefix ?>contact-remove <?php echo $prefix ?>phone-remove <?php echo $prefix ?>remove"></i>

		<label class="<?php echo $prefix ?>contact-field-wrap">
			<span class="<?php echo $prefix ?>contact-label"><?php esc_html_e( 'Title', 'drplus' ) ?></span>
			<input type="text" name="<?php echo $prefix ?>phone[<?php echo $args['index'] ?>][title]" class="large-text" value="<?php echo esc_attr( $args['title'] ) ?>">
		</label>

		<label class="<?php echo $prefix ?>contact-field-wrap">
			<span class="<?php echo $prefix ?>contact-label"><?php esc_html_e( 'Phone', 'drplus' ) ?></span>
			<input type="text" name="<?php echo $prefix ?>phone[<?php echo $args['index'] ?>][phone]" class="large-text ltr" value="<?php echo esc_attr( $args['phone'] ) ?>" inputmode="numeric">
		</label>
	</div>
</div>