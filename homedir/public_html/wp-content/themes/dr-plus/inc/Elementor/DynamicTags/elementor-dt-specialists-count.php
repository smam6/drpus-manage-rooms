<?php
namespace DrPlus\Elementor\DynamicTags;

use DrPlus\Utils\Speciality;

class SpecialistsCount extends \Elementor\Core\DynamicTags\Tag {
	public function get_name() : string {
		return 'drplus-specialists-count';
	}

	public function get_title() : string {
		return esc_html__( 'Specialists count', 'drplus' );
	}

	public function get_group() : array {
		return ['drplus'];
	}

	public function get_categories() : array {
		return [ \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY, \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	protected function register_controls(): void {
		$this->add_control( // speciality_id
			'speciality_id',
			[
				'label'			=> esc_html__( 'Select speciality', 'drplus' ),
				'description'	=> esc_html__( 'Select a speciality you want to show count. If you do not select any speciality, the count of all specialists will be displayed.', 'drplus' ),
				'label_block'	=> true,
				'multiple'		=> false,
				'type' 			=> \ElementorPro\Modules\QueryControl\Module::QUERY_CONTROL_ID,
				'autocomplete'	=> [
					'object'	=> \ElementorPro\Modules\QueryControl\Module::QUERY_OBJECT_POST,
					'query'		=> [
						'post_type'	=> 'speciality',
					],
				],
			]
		);
	}

	public function render() : void {
		$speciality_id = absint( $this->get_settings( 'speciality_id' ) );

		echo number_format_i18n( Speciality::count_specialists( $speciality_id ), 0 );
	}
}