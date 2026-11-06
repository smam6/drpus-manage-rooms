<?php
use DrPlus\Utils;

?>
<div class="footer-content" id="footer-socials-wrap">
	<?php if( Utils::to_bool( $args['footer_show_org_logos'] ) ) { ?>
		<div id="footer-org-items-wrap">	
			<?php if( !empty( $args['footer_orgs_logo_items']['org_logos'] ) ) { ?>
				<div id="footer-orgs-logo-section">
					<?php foreach( $args['footer_orgs_logo_items']['org_logos'] as $index => $item ) { ?>
						<div class="footer-org-item"><?php echo $item ?></div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<?php if( Utils::to_bool( $args['footer_show_social_info'] ) ) { ?>
		<div id="footer-social">
			<?php if( !empty( $args['footer_social_title'] ) ) { ?>
				<div class="footer-section-title">
					<span class="footer-section-title-text"><?php echo $args['footer_social_title'] ?></span>
				</div>
			<?php } ?>
			<?php if( !empty( $args['footer_social_info']['footer_social_items'] ) ) { ?>
				<div id="footer-social-section">
					<?php foreach( $args['footer_social_info']['footer_social_items'] as $index => $item ) { ?>
						<?php if( empty( $item ) || empty( $args['footer_social_info']['footer_social_icons'][$index] ) ) continue; ?>
						<a href="<?php echo $item ?>" target="_blank" class="footer-social-item" rel="noopener noreferrer nofollow">
							<i class="<?php echo $args['footer_social_info']['footer_social_icons'][$index] ?>"></i>
						</a>
					<?php } ?>
				</div>
			<?php } ?>
			<?php if( $args['footer_social_show_bottom_logo'] ) { ?>
				<?php get_template_part( "templates/footer/footer-logo", null ) ?>
			<?php } ?>
		</div>
	<?php } ?>
</div>