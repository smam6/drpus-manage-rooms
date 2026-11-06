<?php

use DrPlus\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$prefix = '';
if( isset( $args["prefix"] ) ) {
	$prefix = $args["prefix"];
}
if( $args["{$prefix}icon_align"] == 'start' && $args["{$prefix}icon"] ) {
	echo $args["{$prefix}icon"];
}
?>
<?php if( $args["{$prefix}text"] !== '' ) { ?>
	<span class="button-text"><?php echo $args["{$prefix}text"] ?></span>
<?php } ?>
<?php
if( $args["{$prefix}icon_align"] == 'end' && $args["{$prefix}icon"] ) {
	echo $args["{$prefix}icon"];
}
if( !empty( $args["{$prefix}loading"] ) ) {
	UI::button_loading();
}

if( !empty( $args["{$prefix}popup"] ) ) {
	echo '<div class="drplus-popover drplus-popover-center">' . esc_html( $args["{$prefix}popup"] ) . '</div>';
}