<?php
namespace DrPlus\Widgets;

use DrPlus\Utils;
use DrPlus\Utils\Search;

if( !defined( 'ABSPATH' ) ) exit;

class SearchSpecialists extends \WP_Widget {
	private $defaults = [
		'title'	=> '',
	];

	public function __construct() {
		parent::__construct(
			'drplus_search_specialists', // Base ID
			esc_html__( 'Doctor Plus - Search specialists', 'drplus' ), // Name
			[
				'description'	=> __( 'Search form for specialists', 'drplus' )
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
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		$city = Search::get_city_from_GET();
		if( !$city && is_tax( 'location' ) ) {
			$term = get_queried_object();
			$city = $term && !is_wp_error( $term ) ? $term->slug : '';
		}
		
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		get_template_part( "searchform", null, [
			'section'	=> 'specialist',
			'city'		=> $city
		] );
		echo $args['after_widget'];
	}
}
