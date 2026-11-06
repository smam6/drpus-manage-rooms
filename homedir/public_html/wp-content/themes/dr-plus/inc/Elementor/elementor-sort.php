<?php
namespace DrPlus\Elementor;

class Sort extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_sort';
	}

	public function get_title() {
		return esc_html__( 'Sort (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_categories() {
		return ['drplus_archive'];
	}

	public function get_keywords() {
		return ['sort', 'archive', 'post', 'blog', 'مرتب سازی', 'ترتیب', 'پست', 'نوشته', 'بلاگ', 'آرشیو'];
	}

	private function settings_controls() {
		$this->start_controls_section( // content_section
			'settings_section',
			[
				'label'	=> esc_html__( 'Settings', 'drplus' ),
				'tab'	=> \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( // label
			'label',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Label', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Sort:', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		get_template_part( "templates/archives/template-archives-sort", null, [
			'label'	=> $settings['label']
		] );
	}
}