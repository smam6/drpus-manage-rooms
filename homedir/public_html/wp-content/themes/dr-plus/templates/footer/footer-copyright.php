<?php

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;
?>
<?php if( !empty( $args['footer_copyright'] ) ) { ?>
	<div id="footer-copyright">
		<?php echo wpautop( $args['footer_copyright'] ) ?>
	</div>
<?php } ?>

<?php if( !empty( $args['footer_socials_items']['footer_social_icon'] ) ) { ?>
	<div id="footer-social-items">
		<?php
		if( Utils::to_bool( $args['show_footer_socials_items'] ) ) {
			foreach( $args['footer_socials_items']['footer_social_icon'] as $index => $social_icon ) {
				if( empty( $args['footer_socials_items']['footer_social_link'][$index] ) ) continue;
				?>
				<a href="<?php echo esc_url( $args['footer_socials_items']['footer_social_link'][$index] ) ?>" target="_blank" rel="nofollow noopener" class="footer-social-item">
					<i class="footer-social-icon <?php echo esc_attr( $social_icon ) ?>"></i>
				</a>
				<?php
			}
		}
		?>
	</div>
<?php } ?>