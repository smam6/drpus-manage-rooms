<?php /* Template Name: Fullwidth */ ?>
<?php

use DrPlus\Utils;
use DrPlus\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;

get_header();

$options = Page::get_options();

$main_classes = [];

$primary_classes = ['content-area'];
if( !$options['use_content_style'] ) {
	$primary_classes[] = 'content-area-empty';
}
?>
<div id="page-body" <?php echo Utils::prepare_html_classes( $main_classes, true ) ?>>
	<main id="page-main">
		<?php get_template_part( "templates/page/template-page-head", null, ['options' => $options] ); ?>

		<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
			<div class="entry-container">
				<?php get_template_part( "templates/page/template-page-content", null, ['options' => $options] ); ?>
			</div>
		</div>
	</main>
</div>
<?php
get_footer();