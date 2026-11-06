<?php
use MJ\Whitebox\Utils;

extract($args);

if( Utils::to_bool( $options['single_specialist_show_reviews'] ) && comments_open() ) { ?>									
	<section class="<?php echo $prefix ?>reviews" role="region" aria-label="<?php echo esc_html_e( 'User reviews', 'drplus' ) ?>">
		<?php comments_template(); ?>
	</section>
<?php } ?>