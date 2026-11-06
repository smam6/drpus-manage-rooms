<?php

use DrPlus\Utils;
use DrPlus\Utils\UtilsSpecialists;

if( !defined( 'ABSPATH' ) ) exit;

$specialist = $args['specialist'];

$args = Utils::check_default( $args, [
	'name-tag'		=> 'h2',
	'short_bio-tag'	=> 'div',
	'avatar_size'	=> 56
] );

$page_link = UtilsSpecialists::get_page_link( $specialist );
?>

<a href="<?php echo $page_link ?>" class="specialist-link" title="<?php echo esc_attr( $specialist->display_name ) ?>">
	<div class="specialist-avatar-wrap">
		<?php echo get_avatar( $specialist->user_id, $args['avatar_size'] ) ?>
	</div>
	<div class="specialist-item-texts">
		<<?php echo tag_escape( $args['name-tag'] ) ?> class="specialist-name line-clamp line-clamp-1"><?php echo esc_html( $specialist->display_name ) ?></<?php echo tag_escape( $args['name-tag'] ) ?>>
		<<?php echo tag_escape( $args['short_bio-tag'] ) ?> class="specialist-short_bio line-clamp line-clamp-1"><?php echo esc_html( $specialist->subtitle ) ?></<?php echo tag_escape( $args['short_bio-tag'] ) ?>>
	</div>
</a>