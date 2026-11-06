<?php
namespace DrPlus\Widgets;

use DrPlus\Components\CustomSelect;
use DrPlus\Utils;
use DrPlus\Utils\Location;
use DrPlus\Utils\Search;

if( !defined( 'ABSPATH' ) ) exit;

class HospitalCityFilter extends \WP_Widget {
	private $defaults = [
		'title'			=> '',
		'label'			=> '',
		'placeholder'	=> '',
	];

	public function __construct() {
		$this->defaults['label'] = esc_html__( "Select city", 'drplus' );
		$this->defaults['placeholder'] = esc_html__( 'All cities', 'drplus' );
		parent::__construct(
			'drplus_hospital_city_filter', // Base ID
			esc_html__( 'Doctor Plus - Hospital city filter', 'drplus' ), // Name
			[
				'description'	=> __( 'Filter hospitals by city', 'drplus' )
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
			<label for="<?php echo $this->get_field_id( 'label' ); ?>"><?php esc_html_e( 'Field label', 'drplus' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>" type="text" value="<?php echo esc_attr( $values['label'] ); ?>" >
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'placeholder' ); ?>"><?php esc_html_e( 'Placeholder', 'drplus' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'placeholder' ); ?>" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" type="text" value="<?php echo esc_attr( $values['placeholder'] ); ?>" >
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['label'] = isset( $new_instance['label'] ) ? sanitize_text_field( $new_instance['label'] ) : $instance['label'];
		$instance['placeholder'] = isset( $new_instance['placeholder'] ) ? sanitize_text_field( $new_instance['placeholder'] ) : $instance['placeholder'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
 
		$city_terms = Location::get_location_terms_by_post_type( 'hospital', [], true );
		if( empty( $city_terms ) ) {
			$cities = [];
		} else {
			$cities = wp_list_pluck( $city_terms, 'name', 'slug' );
		}
		$cities = array_merge( ['' => __( 'All cities', 'drplus' )], $cities );

		$city = Search::get_city_from_GET();
		?>
		<form action="" method="get" class="hospital-city-filter-form">
			<?php if( $instance['label'] ) { ?>
				<label class="hospital-city-filter-label"><?php echo esc_html( $instance['label'] ) ?></label>
			<?php } ?>
			<?php
			CustomSelect::view( [
				'select'		=> [
					'classes'	=> ['drplus-search-city', 'hospital-city-filter'],
					'name'		=> 'city',
				],
				'value'			=> !empty( $city ) && !is_wp_error( $city ) && is_object( $city ) ? $city->slug : '',
				'placeholder'	=> $instance['placeholder'],
				'options'		=> $cities,
			] );

			Utils::query_string_form_fields( null, ['city', 'page'] );
			?>
		</form>
		<?php
		
		echo $args['after_widget'];
	}
}