<?php

use DrPlus\Components\Button;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$settings = Options::get_options( [
	'404_image'		=> [
		'id'	=> 0,
		'url'	=> DRPLUS_URI . "assets/images/404.svg",
	],
	'404_title'		=> esc_html__( "The desired page was not found.", 'drplus' ),
	'404_subtitle'	=> esc_html__( "This page may not exist or has been deleted.", 'drplus' ),
] );

get_header();
?>
<div id="page-body" class="page-width">
	<main id="page-main">
		<?php drplus_breadcrumb(); ?>

		<div id="primary" class="content-area content-area-empty row">
			<div class="entry-container col-12" id="not-found">
				<div id="not-found-image-wrap"><?php echo !empty( $settings['404_image']['id'] ) ? wp_get_attachment_image( $settings['404_image']['id'], 'full' ) : '<img src="' . $settings['404_image']['url'] . '" alt="">' ?></div>
				<div id="not-found-title"><?php echo esc_html( $settings['404_title'] ) ?></div>
				<div id="not-found-subtitle"><?php echo esc_html( $settings['404_subtitle'] ) ?></div>
				<div id="not-found-btns">
					<?php
					Button::view( [
						'small'	=> true,
						'text'	=> __( 'Back to home page', 'drplus' ),
						'link'	=> home_url(),
						'align'	=> 'center',
					] );

					if( !empty( $_SERVER['HTTP_REFERER'] ) ) {
						Button::view( [
							'small'	=> true,
							'type'	=> 'bordered',
							'text'	=> __( 'Back to previous page', 'drplus' ),
							'link'	=> $_SERVER['HTTP_REFERER'],
							'align'	=> 'center',
						] );
					}
					?>
				</div>
			</div>
		</div>
	</main>
</div>
<?php
get_footer();