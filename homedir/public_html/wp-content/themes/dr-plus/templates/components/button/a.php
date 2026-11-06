<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;

if( !defined( 'ABSPATH' ) ) exit;

$prefix = '';
if( isset( $args["prefix"] ) ) {
	$prefix = $args["prefix"];
}

$attributes = Elementor::get_link_attributes( $args["{$prefix}link"] );
$attributes['class'] = $args["{$prefix}classes"];
if( !empty( $args["{$prefix}id"] ) ) {
	$attributes['id'] = $args["{$prefix}id"];
}
if( !empty( $args["{$prefix}atts"] ) ) {
	foreach( $args["{$prefix}atts"] as $key => $val ) {
		$attributes[$key] = $val;
	}
}

if( !empty( $args["{$prefix}title"] ) ) {
	$attributes['title'] = $args["{$prefix}title"];
}

?>
<a <?php echo Utils::get_html_attributes( $attributes ) ?>>
	<?php get_template_part( "templates/components/button/content", null, $args ) ?>
</a>