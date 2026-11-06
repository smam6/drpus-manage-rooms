<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistFaq extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_faq';
	}

	public function get_title() {
		return esc_html__( 'Specialist faq (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-toggle';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'faq', 'متخصص', 'پرسش', 'پاسخ', 'سوالات متداول'];
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

		$this->add_control( // section_tag
			'section_tag',
			[
				'label'		=> esc_html__( 'Section tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'h3',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control( // section_title
			'section_title',
			[
				'label'			=> esc_html__( 'Title', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Frequently Asked Questions (FAQs)', 'drplus' ),
				'description'	=> esc_html__( 'Use %s to for specialist name', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control(
			'section_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Title Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> 'drplus-icon-faq',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->add_control( // style
			'style',
			[
				'label'		=> esc_html__( 'Style', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'style-1',
				'options'	=> [
					'style-1'		=> esc_html__( 'Style 1', 'drplus' ),
					'style-2'		=> esc_html__( 'Style 2', 'drplus' ),
				],
			]
		);

		$this->add_control(
			'desktop_two_cols',
			[
				'label'			=> esc_html__( 'Two columns in desktop', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->add_control(
			'show_bg_icon',
			[
				'label'			=> esc_html__( 'Show background icon', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'condition'		=> [
					'style'	=> 'style-2'
				]
			]
		);

		$this->add_control(
			'bg_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Background Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> 'drplus-icon-dr-plus-1',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
				'condition'		=> [
					'show_bg_icon'	=> 'yes',
					'style'	=> 'style-2'
				]
			],
		);

		$this->add_control(
			'faq_schema',
			[
				'label'			=> esc_html__( 'Add FAQ schema', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Yes', 'drplus' ),
				'label_off'		=> esc_html__( 'No', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'container_',
			'selector'		=> '.specialist_faqs',
			'section'	=> [
				'label'	=> esc_html__( 'Container', 'drplus'),
				'name'	=> 'container',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_row_',
			'base_selector'		=> '.section-title-wrap',
			'section'	=> [
				'label'	=> esc_html__( 'Section title row style', 'drplus'),
				'name'	=> 'section_title_row',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_',
			'base_selector'		=> '.section-title-wrap',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> esc_html__( 'Section title style', 'drplus'),
				'name'	=> 'section_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'section_title_icon_',
			'base_selector'		=> '.section-title-wrap',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Section title icon style', 'drplus'),
				'name'	=> 'section_title_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_',
			'base_selector'		=> '.accordion-item',
			'section'	=> [
				'label'	=> esc_html__( 'faq item', 'drplus'),
				'name'	=> 'faq_item',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_head_',
			'base_selector'		=> '.accordion-item-head',
			'section'	=> [
				'label'	=> esc_html__( 'item head', 'drplus'),
				'name'	=> 'faq_item_head',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_title_',
			'base_selector'		=> '.accordion-item-title',
			'section'	=> [
				'label'	=> esc_html__( 'Question', 'drplus'),
				'name'	=> 'faq_item_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_icon_',
			'base_selector'		=> '.accordion-item-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Arrow icon', 'drplus'),
				'name'	=> 'faq_item_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_content_',
			'base_selector'		=> '.accordion-item-content',
			'section'	=> [
				'label'	=> esc_html__( 'Answer container', 'drplus'),
				'name'	=> 'faq_item_content',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_answer_',
			'base_selector'		=> '.accordion-item-content-text',
			'section'	=> [
				'label'	=> esc_html__( 'Answer', 'drplus'),
				'name'	=> 'faq_item_answer',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'faq_item_answer_icon_',
			'base_selector'		=> '.accordion-item-bg-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Background icon', 'drplus'),
				'name'	=> 'faq_item_answer_icon',
				'condition'	=> [
					'style'	=> 'style-2'
				]
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_container_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_faqs',
			'section'	=> [
				'label'		=>ElementorControls::dark_control_label(  esc_html__( 'Container', 'drplus') ),
				'name'		=> 'dark_container',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_section_title_row_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .section-title-wrap',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Section title row style', 'drplus') ),
				'name'	=> 'dark_section_title_row',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_section_title_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .section-title-wrap',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Section title style', 'drplus') ),
				'name'	=> 'dark_section_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_section_title_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .section-title-wrap',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Section title icon style', 'drplus') ),
				'name'	=> 'dark_section_title_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_',
			'base_selector'		=> '.accordion-item',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'faq item', 'drplus') ),
				'name'	=> 'dark_faq_item',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_head_',
			'base_selector'		=> '.accordion-item-head',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'item head', 'drplus') ),
				'name'	=> 'dark_faq_item_head',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_title_',
			'base_selector'		=> '.accordion-item-title',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Question', 'drplus') ),
				'name'	=> 'dark_faq_item_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_icon_',
			'base_selector'		=> '.accordion-item-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Question icon', 'drplus') ),
				'name'	=> 'dark_faq_item_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_content_',
			'base_selector'		=> '.accordion-item-content',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Answer container', 'drplus') ),
				'name'	=> 'dark_faq_item_content',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_answer_',
			'base_selector'		=> '.accordion-item-content-text',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Answer', 'drplus') ),
				'name'	=> 'dark_faq_item_answer',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_faq_item_answer_icon_',
			'base_selector'		=> '.accordion-item-bg-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Background icon', 'drplus') ),
				'name'	=> 'dark_faq_item_answer_icon',
				'condition'	=> [
					'style'	=> 'style-2'
				] + $dark_condition
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$specialist = UtilsSpecialists::get_specialist_from_post();
		if( empty( $specialist ) || empty( $specialist->meta['faqs'] ) ) return '';
		foreach( $specialist->meta['faqs'] as $faq ) {
			if( empty( $faq['question'] ) || empty( $faq['answer'] ) ) continue;
			$faq_item = [
				'title'	=> $faq['question'],
				'text'	=> $faq['answer'],
			];
			if( $settings['style'] == 'style-2' && Utils::to_bool( $settings['show_bg_icon'] ) ) {
				$faq_item['show_bg_icon'] = true;
				$faq_item['bg_icon'] = $settings['bg_icon'];
			}
			$faqs[] = $faq_item;
		}

		if( empty( $faqs ) ) return;

		get_template_part( 'templates/specialists/single/template-specialists-single-faqs', null, [
			'prefix'				=> 'specialist_',
			'specialist'			=> $specialist,
			'faqs'					=> $faqs,
			'options'				=> [
				'single_specialist_sections_tag'	=> $settings['section_tag'],
				'section_title'						=> $settings['section_title'],
				'section_icon'						=> $settings['section_icon'],
				'desktop_two_cols'					=> $settings['desktop_two_cols'],
				'style'								=> $settings['style'],
			],
		] );
		if( Utils::to_bool( $settings['faq_schema'] ) && !empty( $faqs ) ) {
			$json = [
				'@context' => 'https://schema.org',
				'@type' => 'FAQPage',
				'mainEntity' => [],
			];

			foreach( $faqs as $faq ) {
				$json['mainEntity'][] = [
					'@type' => 'Question',
					'name' => wp_strip_all_tags( $faq['title'] ),
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text' => Elementor::parse_text_editor( $faq['text'] ),
					],
				];
			}
			?>
			<script type="application/ld+json"><?php echo wp_json_encode( $json ); ?></script>
			<?php
		}
	}
}