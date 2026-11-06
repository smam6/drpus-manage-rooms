<?php
namespace DrPlus\Widgets;

use DrPlus\CategoryWalker;
use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

class HospitalCategories extends \WP_Widget {
	private $defaults = [
		'title'			=> '',
		'show_count'	=> true,
		'hide_empty'	=> false,
		'only_childs'	=> false,
	];

	public function __construct() {
		$this->defaults['title'] = esc_html__( "Categories", 'drplus' );
		parent::__construct(
			'drplus_hospital_categories', // Base ID
			esc_html__( 'Doctor Plus - Hospital categories', 'drplus' ), // Name
			[
				'description'	=> __( 'List of categories of the hospitals', 'drplus' )
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
			<input class="checkbox " id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ) ?>" type="checkbox" value="1" <?php checked( true, $values['show_count'] ) ?>>
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php esc_html_e( 'Show counts', 'drplus' ); ?></label>
		</p>

		<p>
			<input class="checkbox " id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ) ?>" type="checkbox" value="1" <?php checked( true, $values['hide_empty'] ) ?>>
			<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>"><?php esc_html_e( 'Hide empty categories', 'drplus' ); ?></label>
		</p>

		<p>
			<input class="checkbox " id="<?php echo $this->get_field_id( 'only_childs' ); ?>" name="<?php echo $this->get_field_name( 'only_childs' ) ?>" type="checkbox" value="1" <?php checked( true, $values['only_childs'] ) ?>>
			<label for="<?php echo $this->get_field_id( 'only_childs' ); ?>"><?php esc_html_e( 'Only show children of the current category', 'drplus' ); ?></label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['show_count'] = !empty( $new_instance['show_count'] );
		$instance['hide_empty'] = !empty( $new_instance['hide_empty'] );
		$instance['only_childs'] = !empty( $new_instance['only_childs'] );

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
 
		include_once( DRPLUS_DIR . "inc/Classes/CategoryWalker.php" );
		$list_args = [
			'walker'						=> new CategoryWalker,
			'taxonomy'						=> 'hospital_category',
			'hide_empty'					=> $instance['hide_empty'],
			'title_li'						=> '',
			'show_count'					=> $instance['show_count'],
			'current_category_ancestors'	=> [],
		];
		if( $instance['only_childs'] ) {
			$current_cat = 0;
			$cat_ancestors = [];

			if ( is_tax( 'hospital_category' ) ) {
				global $wp_query;
				$current_cat = $wp_query->queried_object;
				$cat_ancestors = get_ancestors( $current_cat->term_id, 'hospital_category' );
			} elseif ( is_singular( 'hospital' ) ) {
				global $post;
				$terms = wp_get_post_terms(
					$post->ID,
					'hospital_category',
					[
						'orderby' => 'parent',
						'order'   => 'DESC',
					]
				);

				if( $terms ) {
					$current_cat = $terms[0];
					$cat_ancestors = get_ancestors( $current_cat->term_id, 'hospital_category' );
				}
			}

			$list_args['current_category'] = $current_cat;
			$list_args['current_category_ancestors'] = $cat_ancestors;

			// Show Siblings and Children Only.
			if ( $instance['only_childs'] && $current_cat ) {
				$list_args['include'] = get_terms(
					'hospital_category',
					array(
						'fields'       => 'ids',
						'parent'       => $current_cat->term_id,
						'hierarchical' => true,
						'hide_empty'   => $instance['hide_empty'],
					)
				);
			}
		}

		echo '<ul class="hospital-categories">';
			wp_list_categories( $list_args );
		echo '</ul>';
		
		echo $args['after_widget'];
	}
}