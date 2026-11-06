<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'text'		=> '',
	'classes'	=> [],
] );

$class = array_merge( ['drplus-loading'], $args['classes'] );
?>
<div class="<?php echo Utils::prepare_html_classes( $class ) ?>">
	<?php echo file_get_contents( DRPLUS_DIR . "assets/images/loading.svg" ) ?>
	<?php if( $args['text'] !== '' ) { ?>
		<span class="drplus-loading-text"><?php echo wp_kses_post( $args['text'] ) ?></span>
	<?php } ?>
</div>