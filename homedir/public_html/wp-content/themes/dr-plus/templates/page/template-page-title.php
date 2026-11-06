<?php

use DrPlus\Components\SimpleIcon;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;
$options = $args['options'];

$settings = Options::get_options( [
	'page-title-tag'	=> 'h1',
	'archive-title-tag'	=> 'h1',
] );

$title_tag = !empty( $args['is_archive'] ) ? $settings['archive-title-tag'] : $settings['page-title-tag'];
?>
<header id="page-header">
	<div id="page-title-wrap" class="section-title">
		<<?php echo tag_escape( $title_tag ) ?> class="section-title-inner">
			<?php
			if( !empty( $options['page_icon'] ) ) {
				SimpleIcon::view( [
					'icon'		=> $options['page_icon'],
					'classes'	=> ['page-title-icon'],
				] );
			}
			?>
			<span id="page-title" class="section-title-title"><?php drplus_archive_title() ?></span>
		</<?php echo tag_escape( $title_tag ) ?>>
	</div>
	<?php
	if( !empty( $args['show_sort_archive'] ) ) {
		get_template_part( "templates/archives/template-archives-sort", null, $args);
	}
	?>
</header>