<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistCode extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_code';
	}

	public function get_title() {
		return esc_html__( 'Specialist code (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-number-field';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'code', 'متخصص', 'شناسه', 'شماره'];
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
		
		$this->add_control( // title_text
			'title_text',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Title text', 'drplus' ),
				'label_block'	=> true,
				'default'		=> esc_html__( 'Medical system number', 'drplus' ),
			]
		);

		$this->add_control( // text_tag
			'text_tag',
			[
				'label'		=> esc_html__( 'Text tag', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'span',
				'options'	=> Utils::custom_tags(),
			]
		);

		$this->add_control( // hide_if_empty
			'hide_if_empty',
			[
				'label'			=> esc_html__( 'Hide if empty', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'no',
			]
		);

		$this->add_control( // empty_text
			'empty_text',
			[
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'label'			=> esc_html__( 'Empty text', 'drplus' ),
				'label_block'	=> true,
				'default'		=> __( 'Unknown', 'drplus' ),
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'		=> [
					'hide_if_empty!'	=> 'yes'
				],
			]
		);

		$this->add_control( // show_verified_icon
			'show_verified_icon',
			[
				'label'			=> esc_html__( 'Show verified icon', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::SWITCHER,
				'description'	=> esc_html__( 'Show verified icon if specialist id verified', 'drplus' ),
				'label_on'		=> esc_html__( 'Show', 'drplus' ),
				'label_off'		=> esc_html__( 'Hide', 'drplus' ),
				'return_value'	=> 'yes',
				'default'		=> 'yes',
				'separator'		=> 'before'
			]
		);

		$this->add_control( // verified_icon
			'verified_icon',
			[
				'type'			=> \Elementor\Controls_Manager::ICONS,
				'label'			=> esc_html__( 'Icon', 'drplus' ),
				'skin'			=> 'inline',
				'label_block'	=> false,
				'default'	=> [
					'value'		=> 'drplus-icon-verify-fill',
					'library'	=> 'drplus-icon',
				],
				'condition'		=> [
					'show_verified_icon'	=> 'yes'
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'code_wrap_',
			'selector'		=> '.specialist_code-wrap',
			'section'	=> [
				'label'	=> esc_html__( 'Container', 'drplus'),
				'name'	=> 'code_wrap',
			],
			'mode'		=> 'wrap',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'verified_icon_',
			'selector'		=> '.specialist_verified-icon',
			'section'	=> [
				'label'	=> esc_html__( 'Verified icon', 'drplus'),
				'name'	=> 'verified_icon',
				'condition'		=> [
					'show_verified_icon'	=> 'yes'
				],
			],
			'mode'		=> 'icon',
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'specialist_code_',
			'selector'		=> '.specialist_code',
			'section'	=> [
				'label'	=> esc_html__( 'Code text', 'drplus'),
				'name'	=> 'specialist_code',
			],
			'mode'		=> 'specialist_code',
		] );

		ElementorControls::dark_mode_toggle_controls( $this );
		$dark_condition = ElementorControls::dark_condition();
		$dark_excludes = ElementorControls::dark_excludes();

		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_code_wrap_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_code-wrap',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Container', 'drplus') ),
				'name'	=> 'dark_code_wrap',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'wrap',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_verified_icon_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_verified-icon',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Verified icon', 'drplus') ),
				'name'	=> 'dark_verified_icon',
				'condition'		=> [
					'show_verified_icon'	=> 'yes',
					'enable_dark_mode' 	=> 'yes',
				],
			],
			'mode'				=> 'icon',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'dark_specialist_code_',
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_code',
			'section'	=> [
				'label'	=> ElementorControls::dark_control_label( esc_html__( 'Code text', 'drplus') ),
				'name'	=> 'dark_specialist_code',
				'condition'	=> $dark_condition,
			],
			'mode'		=> 'specialist_code',
			'excludes'			=> $dark_excludes,
			'hover_excludes'	=> $dark_excludes,
		] );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$specialist = UtilsSpecialists::get_specialist_from_post();
		if( empty( $specialist ) ) return;

		$specialist_code = UtilsSpecialists::get_specialist_code( $specialist->user_id );

		if( Utils::to_bool( $settings['hide_if_empty'] ) && empty( $specialist_code ) ) return '';
		
		$tag = tag_escape( $settings['text_tag'] );
		$title_text = sanitize_text_field( $settings['title_text'] );
		$empty_text = sanitize_text_field( $settings['empty_text'] );

		$show_verified_icon = $specialist->is_verified && Utils::to_bool( $settings['show_verified_icon'] );
		if( Utils::to_bool( $settings['show_verified_icon'] ) ) {
			$is_elementor_editor = \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode();
			if( $is_elementor_editor ) $show_verified_icon = true;
		}
		
		?>
		
		<div class="specialist_code-wrap">
			<?php if( $show_verified_icon ) { ?>
				<?php echo Sanitizers::icon( $settings['verified_icon'], 'specialist_verified-icon' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Verified specialist', 'drplus' ) ?></span>
			<?php } ?>
			<<?php echo tag_escape( $tag ) ?> class="specialist_code"><?php printf( '%s: %s', $title_text, !empty( $specialist_code ) ? $specialist_code : $empty_text ) ?></<?php echo tag_escape( $tag ) ?>>
		</div>
		<?php
	}
}