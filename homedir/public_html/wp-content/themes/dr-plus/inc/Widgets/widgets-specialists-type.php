<?php
namespace DrPlus\Widgets;

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

class SpecialistsType extends \WP_Widget {
	private $defaults = [
		'title'	=> '',
	];

	public function __construct() {
		parent::__construct(
			'drplus_specialists_type', // Base ID
			esc_html__( 'Doctor Plus - Specialists type filter', 'drplus' ), // Name
			[
				'description'	=> __( 'Filter the specialists by type', 'drplus' )
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

		$items = [
			'all'		=> esc_html__( "All", 'drplus' ),
			'in-person'	=> esc_html__( "in-person visit", 'drplus' ),
			'online'	=> esc_html__( "Online visit", 'drplus' ),
		];

		$active = 'all';
		if( !empty( $_GET['specialist-type'] ) && isset( $items[$_GET['specialist-type']] ) ) {
			$active = Utils::convert_chars( $_GET['specialist-type'] );
		}
		
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
		<div class="specialists-type-filter">
			<?php foreach( $items as $item_key => $item_label ) { ?>
				<a href="<?php echo esc_url( add_query_arg( 'specialist-type', $item_key ) ) ?>" class="checkbox-wrap checkbox-secondary specialists-type-filter-item">
					<input type="checkbox" class="specialists-type-filter-item-checkbox" value="true" <?php checked( $item_key, $active ) ?>>
					<span class="specialists-type-filter-item-text"><?php echo esc_html( $item_label ) ?></span>
				</a>
			<?php } ?>
		</div>
		<?php
		echo $args['after_widget'];
	}
}