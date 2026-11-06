<?php
namespace DrPlus\Widgets;

use DrPlus\Utils;
use DrPlus\Utils\Search;

if( !defined( 'ABSPATH' ) ) exit;

class SearchSections extends \WP_Widget {
	private $defaults = [
		'title'	=> '',
		'count'	=> true,
	];

	public function __construct() {
		parent::__construct(
			'drplus_search_sections', // Base ID
			esc_html__( 'Doctor Plus - Search result sections', 'drplus' ), // Name
			[
				'description'	=> __( 'Use this only in "search" sidebar. It will show result sections of the search.', 'drplus' )
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
			<label>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'count' ); ?>" id="<?php echo $this->get_field_id( 'count' ); ?>" <?php checked( true, $values['count'] ) ?>>
				<?php esc_html_e( 'Show results count', 'drplus' ) ?>
			</label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['count'] = !empty( $new_instance['count'] );

		return $instance;
	}

	public function widget( $args, $instance ) {
		$categorized_results = Search::get_categorized_results();
		if( empty( $categorized_results ) ) return;

		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
 
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if( $instance['count'] ) {
			$all_counts = array_sum( wp_list_pluck( $categorized_results, 'count' ) );
		}

		$current_post_type = Search::get_post_type();

		$url = home_url( "?s=" . get_search_query() );
		?>
		<ul class="search_categorized_results-sections">
			<li class="search_categorized_results-section<?php echo empty( $current_post_type ) ? ' active' : '' ?>">
				<a href="<?php echo remove_query_arg( "section", $url ) ?>" class="search_categorized_results-section-link" rel="tag">
					<span class="search_categorized_results-section-label"><?php esc_html_e( "All", 'drplus' ) ?></span>
					<span class="search_categorized_results-section-count"><?php echo esc_html( $all_counts ) ?></span>
				</a>
			</li>
			<?php foreach( $categorized_results as $section => $results ) { ?>
				<li class="search_categorized_results-section<?php echo $section == $current_post_type ? ' active' : '' ?>">
					<a href="<?php echo add_query_arg( "section", $section, $url ) ?>" class="search_categorized_results-section-link" rel="tag">
						<span class="search_categorized_results-section-label"><?php echo esc_html( $results['label'] ) ?></span>
						<?php if( $instance['count'] ) { ?>
							<span class="search_categorized_results-section-count"><?php echo esc_html( $results['count'] ) ?></span>
						<?php } ?>
					</a>
				</li>
			<?php } ?>
		</ul>
		<?php
		
		echo $args['after_widget'];
	}
}