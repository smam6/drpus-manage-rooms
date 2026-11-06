<?php
namespace DrPlus\Widgets;

use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

class RecentPosts extends \WP_Widget {
	private $defaults = [
		'title'		=> '',
		'post_type'	=> 'post',
		'count'		=> 5,
		'title_tag'	=> 'h2',
		'show_date'	=> true,
	];

	public function __construct() {
		parent::__construct(
			'drplus_recent_posts', // Base ID
			esc_html__( 'Doctor Plus - Recent Posts', 'drplus' ), // Name
			[
				'description'	=> __( 'Show recent posts', 'drplus' )
			]
		);
	}

	public function form( $instance ) {
		$values = Utils::check_default( $instance, $this->defaults );
		$post_types = get_post_types( [
			'public'	=> true
		], 'objects' );

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'drplus' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $values['title'] ); ?>" >
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php esc_html_e( 'Post type', 'drplus' ); ?>:</label>
			<select name="<?php echo $this->get_field_name( 'post_type' ) ?>" id="<?php echo $this->get_field_id( 'post_type' ); ?>">
				<option value="all"><?php echo esc_html_e( "All", 'drplus' ) ?></option>
				<?php foreach( $post_types as $post_type ) { ?>
					<option value="<?php echo esc_attr( $post_type->name ) ?>" <?php selected( $post_type->name, $values['post_type'] ) ?>><?php echo esc_html( $post_type->label ) ?></option>
				<?php } ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php esc_html_e( 'Count', 'drplus' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" min="1" value="<?php echo esc_attr( $values['count'] ); ?>" >
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'title_tag' ); ?>"><?php esc_html_e( 'Title tag', 'drplus' ); ?>:</label>
			<select name="<?php echo $this->get_field_name( 'title_tag' ) ?>" id="<?php echo $this->get_field_id( 'title_tag' ); ?>">
				<?php foreach( Utils::custom_tags() as $tag => $label ) { ?>
					<option value="<?php echo esc_attr( $tag ) ?>" <?php selected( $tag, $values['title_tag'] ) ?>><?php echo esc_html( $label ) ?></option>
				<?php } ?>
			</select>
		</p>

		<p>
			<label>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'show_date' ); ?>" id="<?php echo $this->get_field_id( 'show_date' ); ?>" <?php checked( true, $values['show_date'] ) ?>>
				<?php esc_html_e( 'Show date', 'drplus' ) ?>
			</label>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['post_type'] = !empty( $new_instance['post_type'] ) ? Utils::convert_chars( $new_instance['post_type'] ) : $instance['post_type'];
		$instance['count'] = !empty( $new_instance['count'] ) ? Utils::convert_chars( $new_instance['count'], true, 'absint' ) : $instance['count'];
		$instance['title_tag'] = !empty( $new_instance['title_tag'] ) ? Sanitizers::tag( $new_instance['title_tag'] ) : $instance['title_tag'];
		$instance['show_date'] = !empty( $new_instance['show_date'] );

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
 
		$query = new \WP_Query( [
			'post_type'			=> $instance['post_type'],
			'posts_per_page'	=> $instance['count'],
			'no_found_rows'		=> true,
		] );
		if( $query->have_posts() ) {
			?>
			<div class="recent-posts">
				<?php
				while( $query->have_posts() ) {
					$query->the_post();
					?>
					<div <?php post_class( 'recent-post' ); ?>>
						<div class="recent-post-texts">
							<<?php echo $instance['title_tag'] ?> class="post-title"><a href="<?php the_permalink() ?>" class="post-link line-clamp line-clamp-2"><?php echo drplus_get_post_title() ?></a></<?php echo $instance['title_tag'] ?>>
							<?php if( $instance['show_date'] ) { ?>
								<time datetime="<?php echo esc_attr( get_the_date( "Y-m-d H:i:s" ) ) ?>" class="post-time"><?php echo esc_html( get_the_date() ) ?></time>
							<?php } ?>
						</div>
						<?php drplus_post_thumbnail() ?>
					</div>
					<?php
				}
				wp_reset_postdata();
				?>
			</div>
			<?php
		}
		
		echo $args['after_widget'];
	}
}