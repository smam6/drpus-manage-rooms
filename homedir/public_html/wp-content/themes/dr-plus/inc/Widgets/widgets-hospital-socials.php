<?php
namespace DrPlus\Widgets;

use DrPlus\Backend\WidgetsPage;
use DrPlus\Components\SimpleIcon;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\Widgets;

if( !defined( 'ABSPATH' ) ) exit;

class HospitalSocials extends \WP_Widget {
	private $defaults = [
		'title'	=> '',
		'icon'	=> 'drplus-icon-messages-2-bold',
	];

	public function __construct() {
		/* translators: {name}: Hospital name. */
		$this->defaults['title'] = sprintf( esc_html__( "{name} socials", 'drplus' ) );
		parent::__construct(
			'drplus_hospital_socials', // Base ID
			esc_html__( 'Doctor Plus - Hospital socials', 'drplus' ), // Name
			[
				'description'	=> __( 'Show hospital socials page. Use this widget only in "Single hospital sidebar"', 'drplus' )
			]
		);
	}

	public function form( $instance ) {
		$values = Utils::check_default( $instance, $this->defaults );

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'drplus' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $values['title'] ); ?>" >
		</p>

		<p>
			<?php esc_html_e( 'You can use these variables in the title:', 'drplus' ); ?>
			<?php Utils::variables_html( WidgetsPage::hospital_variables(), true );
			?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'icon' ); ?>"><?php esc_html_e( 'Icon', 'drplus' ); ?>:</label>
			<?php
			AdminUI::icon_picker( [
				'name'		=> $this->get_field_name( 'icon' ),
				'id'		=> $this->get_field_id( 'icon' ),
				'icon'		=> $values['icon'],
				'modal_id'	=> "drplus-icon-picker-modal"
			] );
			?>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['icon'] = !empty( $new_instance['icon'] ) ? Utils::convert_chars( $new_instance['icon'] ) : $instance['icon'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		$hospital_settings = Hospital::get_options( get_the_ID() );
		if( empty( $hospital_settings['socials'] ) ) return;
		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$title = Widgets::apply_hospital_variables( $title );
		$title = apply_filters( 'widget_title', $title );

		$args['before_title'] .= SimpleIcon::view( [
			'icon'	=> $instance['icon']
		], false );
 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<div class="hospital-contact-socials" role="list">
			<?php foreach( $hospital_settings['socials'] as $social ) { ?>
				<a
					href="<?php echo esc_url( $social['link'], ['https'] ) ?>"
					class="hospital-social-item"
					target="_blank"
					rel="noopener noreferrer nofollow"
					aria-label="<?php echo esc_attr( sprintf( __( 'Visit our %s page', 'drplus' ), $social['title'] ) ); ?>"
					itemprop="sameAs"
				>
					<i class="hospital-contact-social-icon <?php echo esc_attr( $social['icon'] ) ?>" aria-hidden="true"></i>
					<div class="hospital-contact-title" role="listitem"><?php echo esc_html( $social['title'] ) ?></div>
				</a>
			<?php } ?>
		</div>
		<?php
		
		echo $args['after_widget'];
	}
}