<?php

use DrPlus\Components\SectionTitle;
use DrPlus\Utils\Elementor;
use MJ\Whitebox\Utils;

extract($args);

$options = Utils::check_default( $options, [
	'single_specialist_sections_tag'	=> 'h3',

	'section_title'									=> esc_html__( 'Certificates and Courses', 'drplus' ),
	'section_icon'									=> 'drplus-icon-personalcard-bold',
	'single_specialist_show_certificate_image'		=> true,
	'single_specialist_certificates_verified_text'	=> sprintf( esc_html__( "All of {name}'s credentials have been verified by %s", 'drplus' ), get_bloginfo( 'name' ) ),
	'single_specialist_show_certificates_verified'	=> true,
], ['section_icon'] );

$display_attrs = Elementor::get_display_attributes( $args['display_settings'] );
$display_attrs['classes'][] = $prefix . 'certificates-list';

?>										
<section class="<?php echo $prefix ?>section <?php echo $prefix ?>certificates" role="complementary" aria-label="<?php echo esc_html__( 'Certificates and Courses', 'drplus' ) ?>">
	<?php
	if( !empty( $options['section_title'] ) ) {
		SectionTitle::view( [
			'icon'		=> $options['section_icon'],
			'tag'		=> $options['single_specialist_sections_tag'],
			'title'		=> sprintf( $options['section_title'], $specialist->display_name ),
			'classes'	=> [$prefix . "section-title"]
		] );
	}
	?>
	<ul <?php echo Utils::get_html_attributes( $display_attrs ) ?>>
		<?php
		foreach( $specialist->meta['certificates'] as $certificate ) {
			if( empty( $certificate['title'] ) ) continue;

			$item_html_attrs = [
				'classes'	=> "{$prefix}certificate-item",
			];

			if( !empty( $options['single_specialist_show_certificate_image'] ) && $options['single_specialist_show_certificate_image'] && !empty( $certificate['attachment_id'] ) ) {
				$item_html_attrs['data-src'] = wp_get_attachment_image_url( $certificate['attachment_id'], 'full' );
			}
			?>
			<li <?php echo Utils::get_html_attributes( $item_html_attrs ) ?>><?php echo esc_html( $certificate['title'] ) ?></li>
		<?php } ?>
	</ul>
	<?php
	if( $options['single_specialist_show_certificates_verified'] && !empty( $options['single_specialist_certificates_verified_text'] ) ) {
		$certificates_verified_text = $options['single_specialist_certificates_verified_text'];
		$certificates_verified_text = str_replace( '{name}', $specialist->display_name, $certificates_verified_text );
		?>
		<div class="<?php echo $prefix ?>certificates-verified">
			<i class="drplus-icon-verify-fill"></i>
			<span class="<?php echo $prefix ?>certificates-verified-text">
				<?php echo esc_html( $certificates_verified_text ) ?>
			</span>
		</div>
	<?php } ?>
</section>