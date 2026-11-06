<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

$prefix = '';
if( isset( $args["prefix"] ) ) {
	$prefix = $args["prefix"];
}
$attributes['class'] = $args["{$prefix}classes"];
if( !empty( $args["{$prefix}id"] ) ) {
	$attributes['id'] = $args["{$prefix}id"];
}
if( !empty( $attributes["{$prefix}disabled"] ) ) {
	$attributes['disabled'] = "disabled";
}
if( !empty( $args["{$prefix}atts"] ) ) {
	foreach( $args["{$prefix}atts"] as $key => $val ) {
		$attributes[$key] = $val;
	}
}
?>
<button <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php get_template_part( "templates/components/button/content", null, $args ) ?>
</button>