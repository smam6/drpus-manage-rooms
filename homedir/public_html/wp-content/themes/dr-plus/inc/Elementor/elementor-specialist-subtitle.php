<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistSubtitle extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_subtitle';
	}

	public function get_title() {
		return esc_html__( 'Specialist subtitle (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-t-letter';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'subtitle', 'متخصص', 'زیرعنوان'];
	}

	public function show_in_panel() {
		if ( !Elementor::is_allowed_context( ['single-specialist', 'loop-item'] ) ) {
			return false;
		}

		return parent::show_in_panel();
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // title_tag
			'title_tag',
			[
				'label'		=> esc_html__( 'Title tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'span',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'text_',
			'selector'		=> '.specialist_subtitle',
			'section'	=> [
				'label'	=> esc_html__( 'Text', 'drplus'),
				'name'	=> 'text',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_text_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_subtitle',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Text', 'drplus') ),
				'name'	=> 'dark_text',
				'condition'	=> $dark_condition,
			],
			'mode'				=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$specialist = UtilsSpecialists::get_specialist_from_post();
		if( empty( $specialist ) || empty( $specialist->subtitle ) ) return;

		$tag = tag_escape( $settings['title_tag'] );
		?>
		<<?php echo $tag ?> class="specialist_subtitle"><?php echo esc_html( $specialist->subtitle ) ?></<?php echo $tag ?>>
		<?php
	}
}