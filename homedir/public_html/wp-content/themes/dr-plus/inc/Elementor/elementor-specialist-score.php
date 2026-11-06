<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;

class SpecialistScore extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_score';
	}

	public function get_title() {
		return esc_html__( 'Specialist Score (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-star-o';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'score', 'review', 'متخصص', 'امتیاز', 'دیدگاه'];
	}

	public function show_in_panel() {
		if ( !Elementor::is_allowed_context( ['single-specialist', 'loop-item'] ) ) {
			return false;
		}

		return parent::show_in_panel();
	}

	private function stars_controls_section( $is_dark = false ) {
		$section_args = [
			'label'	=> esc_html__( 'Stars', 'drplus' ),
			'tab'	=> \Elementor\Controls_Manager::TAB_STYLE,
		];
		$prefix = "";
		$base_selector = "{{WRAPPER}} ";
		if( $is_dark ) {
			$section_args['condition'] = ElementorControls::dark_condition();
			$prefix = "dark_";
			$base_selector = 'html[data-theme="dark"] ' . $base_selector;
		}
		$this->start_controls_section( // content_section
			$prefix . "style_score_stars",
			$section_args
		);

		$this->start_controls_tabs( $prefix . "tabs_score_stars_style" );

		ElementorControls::color( $this, $prefix . 'style_score_stars_fill', $base_selector . '.drplus_star_fill', [
			'label'	=> ElementorControls::maybe_dark_label( esc_html__( 'Filled Star Color', 'drplus' ), $is_dark ),
		] );
		ElementorControls::color( $this, $prefix . 'style_score_stars_empty', $base_selector . '.drplus_star_empty', [
			'label'	=> ElementorControls::maybe_dark_label( esc_html__( 'Empty Star Color', 'drplus' ), $is_dark ),
		] );

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // show_score_text
			'show_score_text',
			[
				'label'			=> esc_html__( 'Show Score text', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		$this->stars_controls_section();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'score_text_',
			'selector'		=> '.sidebar-comments-count',
			'section'	=> [
				'label'	=> esc_html__( 'Score text', 'drplus'),
				'name'	=> 'score_text',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		$this->stars_controls_section( true );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_score_text_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .sidebar-comments-count',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Score text', 'drplus') ),
				'name'	=> 'dark_score_text',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$comments = get_comments( [
			'fields'		=> 'ids',
			'post_id'		=> get_the_ID(),
			'meta_key'		=> '_drplus_patient_review',
			'meta_value'	=> true,
			'status'		=> 'approve',
		] );
		get_template_part( 'templates/specialists/single/template-specialists-single-score', null, [
			'prefix'			=> "specialist_",
			'comments_count'	=> count( $comments ),
			'show_score_text'	=> Utils::to_bool( $settings['show_score_text'] )
		] );
	}
}