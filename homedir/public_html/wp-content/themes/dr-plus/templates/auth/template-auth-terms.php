<?php if( $args['auth_terms'] && $args['auth_terms_text'] ) { ?>
	<?php if( $args['auth_terms_url'] ) { ?>
		<a href="<?php echo esc_url( $args['auth_terms_url'], ['http', 'https'] ) ?>" class="auth-terms">
	<?php } else { ?>
		<div class="auth-terms">
	<?php } ?>
		<?php echo wp_kses_post( $args['auth_terms_text'] ) ?>
	<?php if( $args['auth_terms_url'] ) { ?>
		</a>
	<?php } else { ?>
		</div>
	<?php } ?>
<?php } ?>