<?php
namespace DrPlus\Widgets;

use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

class Socials extends \WP_Widget {
	private $defaults = [
		'title'	=> '',
		'style'	=> 'style_1',
		'items'	=> [],
	];

	public function __construct() {
		parent::__construct(
			'drplus_socials', // Base ID
			esc_html__( 'Doctor Plus - Socials', 'drplus' ), // Name
			[
				'description'	=> __( 'Show social pages', 'drplus' )
			]
		);
	}

	public function form( $instance ) {
		$values = Utils::check_default( $instance, $this->defaults );
		$options = Options::get_options( [
			'socials'	=> [
				'social_name'	=> [
					esc_html__( "Instagram", 'drplus' ),
					esc_html__( "LinkedIn", 'drplus' ),
					esc_html__( "Telegram", 'drplus' ),
					esc_html__( "Facebook", 'drplus' ),
				],
				'social_icon'	=> [
					'drplus-icon-instagram',
					'drplus-icon-linkedin',
					'drplus-icon-telegram',
					'drplus-icon-facebook',
				],
				'social_link'	=> [
					'#',
					'#',
					'#',
					'#',
				]
			]
		] );

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'drplus' ); ?>:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $values['title'] ); ?>" >
		</p>

		<p>
			<label><?php esc_html_e( 'Widget style', 'drplus' ) ?></label>
			<select name="<?php echo $this->get_field_name( 'style' ) ?>" class="widefat">
				<option value="style_1" <?php selected( $values['style'], "style_1" ) ?>><?php esc_html_e( 'Style 1', 'drplus' ) ?></option>
				<option value="style_2" <?php selected( $values['style'], "style_2" ) ?>><?php esc_html_e( 'Style 2', 'drplus' ) ?></option>
				<option value="style_3" <?php selected( $values['style'], "style_3" ) ?>><?php esc_html_e( 'Style 3', 'drplus' ) ?></option>
			</select>
		</p>

		<?php if( !empty( $options['socials'] ) && !empty( $options['socials']['social_name'] ) ) { ?>
			<div><?php esc_html_e( 'Select your socials to show in this widget', 'drplus' ) ?></div>

			<?php foreach( $options['socials']['social_name'] as $index => $social_name ) {
				?>
				<p>
					<label>
						<input type="checkbox" name="<?php echo $this->get_field_name( 'items' ) ?>[]" value="<?php echo esc_attr( $social_name ) ?>" <?php checked( in_array( $social_name, $values['items'] ) ) ?>>
						<i class="social-icon <?php echo $options['socials']['social_icon'][$index] ?>"></i>
						<span class="social-label"><?php echo esc_html( $social_name ) ?></span>
					</label>
				</p>
				<?php
			}
			?>
			<p><strong><?php printf( __( 'To add social accounts, Go to <a href="%s">settings</a>', 'drplus' ), admin_url( "admin.php?page=drplus&tab=34" ) ) ?></strong></p>
		<?php } else { ?>
			<p><strong><?php printf( __( 'Add some social accounts from <a href="%s">settings</a>', 'drplus' ), admin_url( "admin.php?page=drplus&tab=34" ) ) ?></strong></p>
			<?php
		}
		?>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->defaults;

		$instance['title'] = !empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : $instance['title'];
		$instance['style'] = !empty( $new_instance['style'] ) ? sanitize_text_field( $new_instance['style'] ) : $instance['style'];
		$instance['items'] = !empty( $new_instance['items'] ) ? $new_instance['items'] : $instance['items'];

		return $instance;
	}

	public function widget( $args, $instance ) {
		$socials_options = Options::get_options( [
			'socials'	=> [
				'social_name'	=> [
					esc_html__( "Instagram", 'drplus' ),
					esc_html__( "LinkedIn", 'drplus' ),
					esc_html__( "Telegram", 'drplus' ),
					esc_html__( "Facebook", 'drplus' ),
				],
				'social_icon'	=> [
					'drplus-icon-instagram',
					'drplus-icon-linkedin',
					'drplus-icon-telegram',
					'drplus-icon-facebook',
				],
				'social_link'	=> [
					'#',
					'#',
					'#',
					'#',
				]
			]
		] )['socials'];
		if( empty( $socials_options ) || empty( $socials_options['social_name'] ) ) return;

		$socials = [];
		$items = $instance['items'];
		if( !empty( $socials['social_name'] ) ) {
			foreach( $items as $social_name ) {
				$index = array_search( $social_name, $socials['social_name'] );
				if( $index === false ) continue;
				if( empty( $socials['social_link'] ) || empty( $socials['social_link'][$index] ) ) continue;
				if( empty( $socials['social_icon'] ) || empty( $socials['social_icon'][$index] ) ) continue;
				$socials[] = [
					'name'	=> $social_name,
					'link'	=> $socials['social_link'][$index],
					'icon'	=> $socials['social_icon'][$index],
				];
			}
		}

		if( empty( $socials ) ) return;

		$instance = Utils::check_default( $instance, $this->defaults );
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		?>
		<div class="social_widget-inner <?php echo esc_attr( $instance['style'] ) ?>">
			<?php
			if( !empty( $title ) ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			?>
			<div class="social-items">
				<?php
				foreach( $socials as $social ) {
					?>
					<a href="<?php echo esc_url( $social['link'] ) ?>" target="_blank" rel="noopener noreferrer" class="social-item">
						<i class="<?php echo esc_attr( $social['icon'] ) ?> social-icon"></i>
						<span class="social-name"><?php echo esc_html( $social['name'] ) ?></span>
					</a>
					<?php
				}
				?>
			</div>
		</div>
		<?php	
		echo $args['after_widget'];
	}
}