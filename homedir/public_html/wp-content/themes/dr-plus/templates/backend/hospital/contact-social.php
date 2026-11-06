<?php

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;

$prefix = 'drplus_hospital_';

$args = Utils::check_default( $args, [
	'index'	=> 0,
	'title'	=> '',
	'icon'	=> 'drplus-icon-instagram',
	'link'	=> '',
], ['index'] );
?>
<div class="<?php echo $prefix ?>contact-slot <?php echo $prefix ?>social-slot" data-swapy-slot="social-slot-<?php echo esc_attr( $args['index'] ) ?>">
	<div class="<?php echo $prefix ?>contact-item <?php echo $prefix ?>social" data-swapy-item="social-<?php echo esc_attr( $args['index'] ) ?>">
		<i class="dashicons dashicons-menu-alt3 <?php echo $prefix ?>contact-move <?php echo $prefix ?>social-move <?php echo $prefix ?>move" data-swapy-handle></i>
		<i class="dashicons dashicons-trash <?php echo $prefix ?>contact-remove <?php echo $prefix ?>social-remove <?php echo $prefix ?>remove"></i>

		<label class="<?php echo $prefix ?>contact-field-wrap">
			<span class="<?php echo $prefix ?>contact-label"><?php esc_html_e( 'Title', 'drplus' ) ?></span>
			<input type="text" name="<?php echo $prefix ?>social[<?php echo $args['index'] ?>][title]" class="large-text" value="<?php echo esc_attr( $args['title'] ) ?>">
		</label>

		<label class="<?php echo $prefix ?>contact-field-wrap">
			<span class="<?php echo $prefix ?>contact-label"><?php esc_html_e( 'Link', 'drplus' ) ?></span>
			<input type="text" name="<?php echo $prefix ?>social[<?php echo $args['index'] ?>][link]" class="large-text ltr" value="<?php echo esc_attr( $args['link'] ) ?>">
		</label>

		<label class="<?php echo $prefix ?>contact-field-wrap">
			<span class="<?php echo $prefix ?>contact-label"><?php esc_html_e( 'Icon', 'drplus' ) ?></span>
			<?php
			AdminUI::icon_picker( [
				'id'		=> "{$prefix}social-icon-{$args['index']}",
				'name'		=> "{$prefix}social[{$args['index']}][icon]",
				'icon'		=> $args['icon'],
				'modal_id'	=> 'drplus-icon-picker-modal',
			] );
			?>
		</label>
	</div>
</div>