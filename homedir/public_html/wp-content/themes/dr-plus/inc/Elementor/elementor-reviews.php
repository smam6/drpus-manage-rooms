<?php
namespace DrPlus\Elementor;

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Elementor;

class Reviews extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_reviews';
	}

	public function get_title() {
		return esc_html__( 'Reviews (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-comments';
	}

	public function get_categories() {
		return ['drplus_archive'];
	}

	public function get_keywords() {
		return ['review', 'comment', 'نظرات', 'دیدگاه'];
	}

	public function show_in_panel() {
		if ( !Elementor::is_allowed_context( ['single-specialist', 'single'] ) ) {
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

		$this->add_control(
			'reviews_section_tag',
			[
				'label'		=> esc_html__( 'Reviews Section tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'h3',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control(
			'reviews_section_title',
			[
				'label'			=> esc_html__( 'Reviews Title', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Comments', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control(
			'reviews_section_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Reviews Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> 'drplus-icon-chat-fill',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->add_control(
			'form_section_tag',
			[
				'label'		=> esc_html__( 'Form Section tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'h3',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control(
			'form_section_title',
			[
				'label'			=> esc_html__( 'Form Title', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'default'		=> esc_html__( 'Add reply', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
			]
		);

		$this->add_control(
			'form_section_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Form Icon', 'drplus' ),
				'skin'			=> 'inline',
				'default'		=> [
					'value'		=> 'drplus-icon-diamond',
					'library'	=> 'drplus-icon'
				],
				'label_block'	=> false,
			],
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'review_section_title_',
			'base_selector'		=> '.comments-title',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> esc_html__( 'Review title', 'drplus'),
				'name'	=> 'review_section_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'review_section_title_icon_',
			'base_selector'		=> '.comments-title',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Review title icon', 'drplus'),
				'name'	=> 'review_section_title_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_list_',
			'selector'		=> '.comment-list',
			'section'	=> [
				'label'	=> esc_html__( 'Comments list', 'drplus'),
				'name'	=> 'comment_list',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_author_',
			'selector'		=> '.comment-author-name cite',
			'section'	=> [
				'label'	=> esc_html__( 'Comment author', 'drplus'),
				'name'	=> 'comment_author',
			],
			'only'		=> ['color', 'typography'],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_author_avatar_',
			'selector'		=> '.comment-author-avatar img',
			'section'	=> [
				'label'	=> esc_html__( 'Comment author avatar', 'drplus'),
				'name'	=> 'comment_author_avatar',
			],
			'mode'		=> 'img',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_text_',
			'selector'		=> '.comment-text-wrap',
			'section'	=> [
				'label'	=> esc_html__( 'Comment text', 'drplus'),
				'name'	=> 'comment_text',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_reply_btn_',
			'selector'		=> '.comment-reply-link',
			'section'	=> [
				'label'	=> esc_html__( 'Comment reply button', 'drplus'),
				'name'	=> 'comment_reply_btn',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'form_section_title_',
			'base_selector'		=> '.comment-respond',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> esc_html__( 'Form title', 'drplus'),
				'name'	=> 'form_section_title',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'form_section_title_icon_',
			'base_selector'		=> '.comment-respond',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Form title icon', 'drplus'),
				'name'	=> 'form_section_title_icon',
			],
			'mode'		=> 'icon',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_form_',
			'selector'		=> '.comment-form',
			'section'	=> [
				'label'	=> esc_html__( 'Comment form', 'drplus'),
				'name'	=> 'comment_form',
			],
			'mode'		=> 'wrap',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_form_fields_',
			'selector'		=> '{{WRAPPER}} .comment-form textarea, {{WRAPPER}} .comment-form input',
			'section'	=> [
				'label'	=> esc_html__( 'Comment form fields', 'drplus'),
				'name'	=> 'comment_form_fields',
			],
			'mode'		=> 'input',
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'comment_form_button_',
			'selector'		=> '.form-submit .button',
			'section'	=> [
				'label'	=> esc_html__( 'Comment form button', 'drplus'),
				'name'	=> 'comment_form_button',
			],
			'mode'		=> 'text',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_review_section_title_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comments-title',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Review title', 'drplus') ),
				'name'	=> 'dark_review_section_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_review_section_title_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comments-title',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Review title icon', 'drplus') ),
				'name'	=> 'dark_review_section_title_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_list_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .comment-list',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comments list', 'drplus') ),
				'name'	=> 'dark_comment_list',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_author_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-author-name cite',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment author', 'drplus') ),
				'name'	=> 'dark_comment_author',
				'condition'	=> $dark_condition,
			],
			'only'		=> ['color', 'typography'],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_author_avatar_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-author-avatar img',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment author avatar', 'drplus') ),
				'name'	=> 'dark_comment_author_avatar',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'img',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_text_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-text-wrap',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment text', 'drplus') ),
				'name'	=> 'dark_comment_text',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_reply_btn_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-reply-link',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment reply button', 'drplus') ),
				'name'	=> 'dark_comment_reply_btn',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_form_section_title_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-respond',
			'selector'			=> '.section-title-title',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Form title', 'drplus') ),
				'name'	=> 'dark_form_section_title',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_form_section_title_icon_',
			'base_selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-respond',
			'selector'			=> '.section-title-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Form title icon', 'drplus') ),
				'name'	=> 'dark_form_section_title_icon',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_form_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .comment-form',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment form', 'drplus') ),
				'name'	=> 'dark_comment_form',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_form_fields_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .comment-form textarea, html[data-theme="dark"] {{WRAPPER}} .comment-form input',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment form fields', 'drplus') ),
				'name'	=> 'dark_comment_form_fields',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'input',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );

		ElementorControls::general_style_controls( $this, [
			'prefix'			=> 'dark_comment_form_button_',
			'selector'			=> 'html[data-theme="dark"] {{WRAPPER}} .form-submit .button',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Comment form button', 'drplus') ),
				'name'	=> 'dark_comment_form_button',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'text',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		set_query_var( 'drplus_comments_args', [
			'reviews_section_tag'	=> $settings['reviews_section_tag'],
			'reviews_section_title'	=> $settings['reviews_section_title'],
			'reviews_section_icon'	=> $settings['reviews_section_icon'],
			'form_section_tag'		=> $settings['form_section_tag'],
			'form_section_title'	=> $settings['form_section_title'],
			'form_section_icon'		=> $settings['form_section_icon'],
		] );
		comments_template();
	}
}