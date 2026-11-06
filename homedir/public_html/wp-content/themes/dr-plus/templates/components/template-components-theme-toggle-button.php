<?php

use DrPlus\Utils\UI;
use MJ\Whitebox\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$prefix = 'drplus_theme_toggle_';

$args = Utils::check_default( $args, [
	'button_style'			=> 'style-1',
] );
$config = UI::get_color_mode_settings();

?>
<button type="button" class="button-transparent <?php echo $prefix ?>button <?php echo $args['button_style'] ?>">
	<input type="checkbox" class="<?php echo $prefix ?>checkbox" style="display:none !important" <?php checked( 'dark', $config['initial'] ) ?>>
	<label class="<?php echo $prefix ?>label">
		<?php if( $args['button_style'] == 'style-1' ) { ?>
			<div class="<?php echo $prefix ?>icon-wrapper">
				<i class="drplus-icon-sun <?php echo $prefix ?>icon light_icon"></i>
				<i class="drplus-icon-moon <?php echo $prefix ?>icon dark_icon"></i>
			</div>
		<?php } else if( $args['button_style'] == 'style-2' ) { ?>
			<span class="<?php echo $prefix ?>slider"></span>
		<?php } ?>
	</label>
</button>