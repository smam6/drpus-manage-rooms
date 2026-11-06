<?php
if( !defined( 'ABSPATH' ) ) exit;
?>
<aside id="sidebar" class="sidebar sidebar-general col-md-3 col-sm-12" aria-label="<?php esc_attr_e( 'Sidebar', 'drplus' ); ?>">
	<section id="widget-area" class="widget-area" role="complementary" aria-label="<?php esc_attr_e( 'Widgets', 'drplus' ); ?>">
		<?php dynamic_sidebar( 'general' ); ?>
	</section>
</aside>