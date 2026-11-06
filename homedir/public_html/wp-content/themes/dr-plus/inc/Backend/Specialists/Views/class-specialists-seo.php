<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils\AdminUI;

class SpecialistSeo extends SpecialistView {
	public static function view() {
		$specialist = parent::$specialist;
		$user_data = $specialist->meta;

		AdminUI::input_with_label( [
			'label'			=> esc_html__( 'About Same As', 'drplus' ),
			'type'			=> 'text',
			'value'			=> $user_data['seo_about_same_as'] ?? "",
			'id'			=> parent::$PREFIX . "seo_same_as",
			'name'			=> parent::$PREFIX . "meta[seo_about_same_as]",
			'input_classes'	=> ['regular-text', 'ltr'],
			'textarea'		=> true,
		] );
		?>
		<p class="description"><?php esc_html_e( 'Write each url one line', 'drplus' ) ?></p>
		<?php
	}
}