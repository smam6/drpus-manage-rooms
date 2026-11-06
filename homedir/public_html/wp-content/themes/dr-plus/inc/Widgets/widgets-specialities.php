<?php
namespace DrPlus\Widgets;

use DrPlus\Utils;
use DrPlus\Utils\Search;
use DrPlus\Utils\Speciality;

if( !defined( 'ABSPATH' ) ) exit;

class Specialities extends \WP_Widget {
	private $defaults = [
		'title'			=> '',
		'show_count'	=> true,
		'hide_empty'	=> false,
		'multiple'		=> true,
	];

	public function __construct() {
		parent::__construct(
			'drplus_specialities', // Base ID
			esc_html__( 'Doctor Plus - Specialities', 'drplus' ), // Name
			[
				'description'	=> __( 'Show list of specialities. Filter the specialists by specialities', 'drplus' )
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
			<input class="checkbox" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ) ?>" type="checkbox" value="1" <?php checked( true, $values['show_count'] ) ?>>
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php esc_html_e( 'Show specialists count for each speciality', 'drplus' ); ?></label>
		</p>

		<p>
			<input class="checkbox" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ) ?>" type="checkbox" value="1" <?php checked( true, $values['hide_empty'] ) ?>>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php esc_html_e( 'Hide empty specialities', 'drplus' ); ?></label>
		</p>

		<p>
			<input class="checkbox" id="<?php echo $this->get_field_id( 'multiple' ); ?>" name="<?php echo $this->get_field_name( 'multiple' ) ?>" type="checkbox" value="1" <?php checked( true, $values['multiple'] ) ?>>
			<label for="<?php echo $this->get_field_id( 'multiple' ); ?>"><?php esc_html_e( 'Let user select multiple specialities', 'drplus' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['show_count'] = !empty( $new_instance['show_count'] );
		$instance['hide_empty'] = !empty( $new_instance['hide_empty'] );
		$instance['multiple'] = !empty( $new_instance['multiple'] );

		return $instance;
	}

	public function widget( $args, $instance ) {
		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		$location_term_id = Search::get_city_from_GET( "term_id" );
		$city_slug = Search::get_city_from_GET();
		if( !$city_slug && is_tax( 'location' ) ) {
			$term = get_queried_object();
			if( $term && !is_wp_error( $term ) ) {
				$city_slug = $term->slug;
				$location_term_id = $location_term_id ?: $term->term_id;
			}
		}

		$specialities = [];
		foreach( Speciality::all() as $speciality ) {
			$specialities[] = [
				'id'	=> $speciality->ID,
				'name'	=> $speciality->post_title,
				'count'	=> $instance['show_count'] || $instance['hide_empty'] ? Speciality::count_specialists( $speciality->ID, $location_term_id ) : 0,
			];
		}

		$active_specialities = Speciality::get_specialities_from_GET();
		
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<form method="get" action="" class="drplus-specialities-filter">
			<?php if( $city_slug ) { ?>
				<input type="hidden" name="city" value="<?php echo esc_attr( $city_slug ); ?>">
			<?php } ?>
			<?php
			foreach( $specialities as $speciality ) {
				if( $instance['hide_empty'] && !$speciality['count'] ) {
					continue;
				}
				$active = in_array( $speciality['id'], $active_specialities );
				?>
				<label class="drplus-specialities-filter-item<?php echo $active ? ' drplus-specialities-filter-item-active' : '' ?>">
					<input type="<?php echo $instance['multiple'] ? 'checkbox' : 'radio' ?>" name="<?php echo $instance['multiple'] ? 'specialities[]' : 'specialities' ?>" class="drplus-specialities-filter-item-checkbox" value="<?php echo esc_attr( $speciality['id'] ) ?>"<?php checked( true, $active ) ?>>
					<div class="drplus-specialities-filter-item-text"><?php echo esc_html( $speciality['name'] ) ?></div>
					<?php if( $instance['show_count'] ) { ?>
						<div class="drplus-specialities-filter-item-count"><?php echo esc_html( number_format_i18n( $speciality['count'], 0 ) ) ?></div>
					<?php } ?>
				</label>
			<?php } ?>
			<?php Utils::query_string_form_fields( null, ['specialities', 'city'] ) ?>
		</form>
		<?php
		echo $args['after_widget'];
	}
}
