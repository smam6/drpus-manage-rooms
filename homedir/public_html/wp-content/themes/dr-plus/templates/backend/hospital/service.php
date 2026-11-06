<?php

use DrPlus\Utils;

$prefix = 'drplus_hospital_';

$args = Utils::check_default( $args, [
	'index'			=> 0,
	'title'			=> '',
	'description'	=> '',
], ['index'] );
?>
<div class="<?php echo $prefix ?>service-slot" data-swapy-slot="service-slot-<?php echo esc_attr( $args['index'] ) ?>">
	<div class="<?php echo $prefix ?>service" data-swapy-item="service-<?php echo esc_attr( $args['index'] ) ?>">
		<i class="dashicons dashicons-menu-alt3 <?php echo $prefix ?>service-move <?php echo $prefix ?>move" data-swapy-handle></i>
		<i class="dashicons dashicons-trash <?php echo $prefix ?>service-remove <?php echo $prefix ?>remove"></i>
	
		<label class="<?php echo $prefix ?>service-field-wrap <?php echo $prefix ?>service-title-wrap">
			<span class="<?php echo $prefix ?>service-label <?php echo $prefix ?>service-title-label"><?php esc_html_e( 'Title', 'drplus' ) ?></span>
			<input type="text" name="<?php echo "{$prefix}service[{$args['index']}][title]" ?>" class="large-text <?php echo $prefix ?>service-title" value="<?php echo esc_attr( $args['title'] ) ?>">
		</label>
	
		<label class="<?php echo $prefix ?>service-field-wrap <?php echo $prefix ?>service-description-wrap">
			<span class="<?php echo $prefix ?>service-label <?php echo $prefix ?>service-description-label"><?php esc_html_e( 'Description', 'drplus' ) ?></span>
			<input type="text" name="<?php echo "{$prefix}service[{$args['index']}][description]" ?>" class="large-text <?php echo $prefix ?>service-description" value="<?php echo esc_attr( $args['description'] ) ?>">
		</label>
	</div>
</div>