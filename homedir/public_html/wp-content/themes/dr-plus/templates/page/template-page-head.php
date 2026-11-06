<?php

use DrPlus\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;
$options = !empty( $args['options'] ) ? $args['options'] : Page::default_options();
?>
<?php
if( $options['show_breadcrumb'] ) {
	drplus_breadcrumb();
}
if( !$options['use_content_style'] && $options['show_title'] ) {
	get_template_part( "templates/page/template-page-title", null, $args );
}