<?php

use DrPlus\Utils;
use DrPlus\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;

get_header();

$options = Page::get_options( get_the_ID() );

$main_classes = [];
if( !$options['fullwidth'] ) {
	$main_classes[] = 'page-width';
}

$primary_classes = ['content-area', 'row'];
if( !$options['use_content_style'] ) {
	$primary_classes[] = 'content-area-empty';
}
$show_sidebar = $options['show_sidebar'] && is_active_sidebar( $options['sidebar'] );
if( $show_sidebar ) {
	$primary_classes[] = 'content-area-with-sidebar';
}
?>
<div id="page-body" <?php echo Utils::prepare_html_classes( $main_classes, true ) ?>>
	<main id="page-main">
		<?php get_template_part( "templates/page/template-page-head", null, ['options' => $options] ); ?>

		<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
			<?php
			if( $show_sidebar ) {
				get_sidebar( $options['sidebar'] );
			}
			?>
			<div class="entry-container<?php echo $show_sidebar ? ' col-md-9 col-sm-12' : ' col-12' ?>">
				<?php get_template_part( "templates/page/template-page-content", null, [
					'options'		=> $options,
					'has_sidebar'	=> $show_sidebar
				] ); ?>
			</div>
		</div>
	</main>
</div>
<?php
get_footer();