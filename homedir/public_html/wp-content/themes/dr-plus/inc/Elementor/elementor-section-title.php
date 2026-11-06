<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;

class SectionTitle extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_section_title';
	}

	public function get_title() {
		return esc_html__( 'Section title (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return ['drplus', 'basic'];
	}

	public function get_keywords() {
		return ['section', 'icon', 'button', 'link', 'title', 'بخش', 'دکمه', 'آیکون', 'لینک', 'عنوان'];
	}

	protected function register_controls() {
		ElementorControls::section_title_settings( $this, ['prefix' => ''] );

		ElementorControls::section_title_styles( $this );
		ElementorControls::dark_mode_toggle_controls( $this );
		ElementorControls::section_title_styles( $this, false, true, true );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/components/template-components-section_title", null, $settings );
	}
}