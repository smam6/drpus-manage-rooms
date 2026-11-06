<?php

use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Search;

if( !defined( 'ABSPATH' ) || !class_exists( "DrPlus\Utils" ) ) exit;

$post_type = Archive::get_archive_post_type();

$args = Utils::check_default( $args, [
	'section'	=> '',
	'city'		=> '',
] );

$city = '';
if( !empty( $args['city'] ) ) {
	$city = Utils::convert_chars( $args['city'], 'sanitize_title_with_dashes' );
} else if( $args['section'] === 'specialist' ) {
	$city = Search::get_city_from_GET();
	if( !$city && is_tax( 'location' ) ) {
		$term = get_queried_object();
		$city = $term && !is_wp_error( $term ) ? $term->slug : '';
	}
}
?>
<form role="search" method="get" class="searchform" action="<?php echo home_url() ?>">
	<label class="screen-reader-text"><?php esc_html_e( 'Search for:', 'drplus' ) ?></label>
	<div class="input-group input-group-row">
		<input
			type="search"
			name="s"
			class="search-field"
			placeholder="<?php echo esc_attr_x( 'Search', 'placeholder', 'drplus' ) ?>"
			value="<?php echo get_search_query() ?>"
			title="<?php echo esc_attr_x( 'Search for:', 'label', 'drplus' ) ?>"
		/>
		<?php if( !empty( $args['section'] ) ) { ?>
			<input type="hidden" name="section" value="<?php echo esc_attr( $args['section'] ) ?>">
		<?php } ?>
		<?php if( $city ) { ?>
			<input type="hidden" name="city" value="<?php echo esc_attr( $city ) ?>">
		<?php } ?>
		<button type="submit" class="search-field-icon" title="<?php echo esc_attr_e( "Search", 'drplus' ) ?>"><i class="drplus-icon-search"></i></button>
	</div>
	<?php if( !empty( $post_type ) && empty( $args['section'] ) ) { ?>
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ) ?>">
	<?php } ?>
</form>
