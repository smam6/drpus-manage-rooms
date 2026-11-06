<?php
if( !defined( 'ABSPATH' ) ) exit;
$options = $args['options'];
?>
<div id="page-content" class="site-content single" role="main">
	<?php
	if( have_posts() ) {
		while( have_posts() ) {
			the_post();
			?>
			<article id="page-<?php the_ID(); ?>" <?php post_class( 'entry-content' ); ?>>
				<?php
				if( $options['use_content_style'] && $options['show_title'] ) {
					get_template_part( "templates/page/template-page-title", null, $args );
				}
				the_content();
				?>
			</article><!-- #page-<?php the_ID(); ?> -->
			<?php

			// If comments are open or we have at least one comment, load up the comment template.
			if( comments_open() || get_comments_number() ) {
				comments_template();
			}
		}
	}
	?>
</div>