<?php
namespace DrPlus\Elementor;

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\ElementorControls;
use DrPlus\Utils\Booking;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistStatistics extends \Elementor\Widget_Base {
	public function get_name() {
		return 'drplus_specialist_statistics';
	}

	public function get_title() {
		return esc_html__( 'Specialist statistics (Doctor Plus)', 'drplus' );
	}

	public function get_icon() {
		return 'eicon-facebook-like-box';
	}

	public function get_categories() {
		return ['drplus', 'drplus_single_specialist'];
	}

	public function get_keywords() {
		return ['specialist', 'statistics', 'آمار', 'متخصص'];
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

		$this->add_control( // statistic
			'statistic',
			[
				'label'		=> esc_html__( 'Statistic', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::SELECT,
				'default'	=> 'patients_review',
				'options'	=> [
					'patients_review'		=> esc_html__( 'Patient satisfaction', 'drplus' ),
					'online_consultation'	=> esc_html__( 'Online consultation', 'drplus' ),
					'visits_count'			=> esc_html__( 'Visits count', 'drplus' ),	
					'articles'				=> esc_html__( 'Number of articles', 'drplus' )
				],
			]
		);

		$this->add_control( // no_review_text
			'no_review_text',
			[
				'label'		=> esc_html__( 'No review text', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( 'Text when specialist has no review yet.', 'drplus' ),
				'default'	=> esc_html__( 'No reviews', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'statistic'	=> 'patients_review'
				]	
			]
		);

		$this->add_control( // no_consultation_text
			'no_consultation_text',
			[
				'label'		=> esc_html__( 'No consultation text', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( "Text when specialist has no online consultation yet.", 'drplus' ),
				'default'	=> esc_html_x( 'No consultations', 'consultation duration', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'statistic'	=> 'online_consultation'
				]	
			]
		);

		$this->add_control( // visit_count_text
			'visit_count_text',
			[
				'label'			=> esc_html__( 'visit count text', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( "use %s for replace with visit count", 'drplus' ),
				'default'		=> esc_html__( '%s people', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'statistic'	=> 'visits_count'
				]	
			]
		);

		$this->add_control( // no_appointment_text
			'no_appointment_text',
			[
				'label'		=> esc_html__( 'No appointment text', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( "Text when specialist has no appointment yet.", 'drplus' ),
				'default'	=> esc_html_x( 'No Appointments', 'consultation duration', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'statistic'	=> 'visits_count'
				]	
			]
		);

		$this->add_control( // articles_text
			'articles_text',
			[
				'label'			=> esc_html__( 'Articles text', 'drplus' ),
				'type'			=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( "use %s for replace with number of articles", 'drplus' ),
				'default'		=> '+%s',
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'statistic'	=> 'articles'
				]	
			]
		);

		$this->add_control( // no_articles_text
			'no_articles_text',
			[
				'label'		=> esc_html__( 'No articles text', 'drplus' ),
				'type'		=> \Elementor\Controls_Manager::TEXT,
				'description'	=> esc_html__( "Text when specialist has no articles", 'drplus' ),
				'default'	=> esc_html_x( 'No Articles', 'consultation duration', 'drplus' ),
				'ai'			=> [
					'type'		=> 'text',
					'language'	=> 'html',
				],
				'dynamic'		=> [
					'active'	=> true,
				],
				'condition'	=> [
					'statistic'	=> 'articles'
				]	
			]
		);

		$this->end_controls_section();
	}

	protected function register_controls() {
		$this->settings_controls();
		ElementorControls::general_style_controls( $this, [
			'prefix'		=> 'text_',
			'selector'		=> '.specialist_stat_value',
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
			'selector'		=> 'html[data-theme="dark"] {{WRAPPER}} .specialist_stat_value',
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
		if( empty( $specialist ) ) return;

		$value = "";
		switch ( $settings['statistic'] ) {
			case 'patients_review':
				$comments = get_comments( [
					'fields'		=> 'ids',
					'post_id'		=> get_the_ID(),
					'meta_key'		=> '_drplus_patient_review',
					'meta_value'	=> true,
					'status'		=> 'approve',
				] );
				$comments_count = count( $comments );
				$full_avg_score = Utils::get_post_avg( get_the_ID(), false, $comments_count );
				$value = !empty( $full_avg_score ) ? round( $full_avg_score/5*100 ) . "%" : $settings['no_review_text'];
				break;
			case 'online_consultation':
				$value = Booking::get_specialist_consultations_duration( $specialist->id, ['completed'], [], $settings['no_consultation_text'] );
				break;
			case 'visits_count':
				$appointments_number = Booking::get_specialist_appointments_count( $specialist->id );
				$value = !empty( $appointments_number ) ? sprintf( $settings['visit_count_text'], number_format_i18n( $appointments_number, 0 ) ) : $settings['no_appointment_text'];
				break;
			case 'articles':
				$value = !empty( $specialist->meta['articles'] ) ? sprintf( $settings['articles_text'], number_format_i18n( $specialist->meta['articles'], 0 ) ) : $settings['no_articles_text'];
				break;
			default:
				break;
		}

		if( empty( $value ) ) return '';
		?>
		<span class="specialist_stat_value"><?php echo $value ?></span>
		<?php
	}
}