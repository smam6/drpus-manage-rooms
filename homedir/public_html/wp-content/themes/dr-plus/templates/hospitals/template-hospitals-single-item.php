<?php

use DrPlus\Utils;
use DrPlus\Utils\Hospital;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'title_tag'		=> 'h2',
	'item_classes'	=> [],
] );

$classes = array_merge( ['slider-slide'], $args['item_classes'] );

$hospital = Hospital::get_options( get_the_ID() );
?>
<article <?php post_class( Utils::prepare_html_classes( $classes ) ); ?>>
	<a href="<?php echo get_permalink() ?>" title="<?php echo get_the_title() ?>">
		<?php drplus_post_thumbnail( null, [76, 76], false ) ?>

		<div class="hospital-item-texts">
			<<?php echo tag_escape( $args['title_tag'] ) ?> class="hospital-name line-clamp line-clamp-1"><?php echo drplus_get_post_title() ?></<?php echo tag_escape( $args['title_tag'] ) ?>>
			<?php if( $hospital['address'] ) { ?>
				<div class="hospital-address-wrap">
					<i class="drplus-icon-location hospital-address-icon"></i>
					<address class="hospital-address line-clamp line-clamp-1"><?php echo esc_html( $hospital['address'] ) ?></address>
				</div>
			<?php } ?>
		</div>
	</a>
</article>