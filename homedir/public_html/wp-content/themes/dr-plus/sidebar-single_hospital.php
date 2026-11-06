<?php
if( !defined( 'ABSPATH' ) || !class_exists( "DrPlus\Utils" ) ) exit;

?>
<aside id="sidebar" class="sidebar sidebar-single sidebar-single-hospital col-lg-3 col-md-12" aria-label="<?php esc_attr_e( 'Sidebar', 'drplus' ) ?>">
	<section id="widget-area" class="widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Widgets', 'drplus' ) ?>">
		<?php dynamic_sidebar( 'single_hospital' ); ?>
	</section>
</aside>