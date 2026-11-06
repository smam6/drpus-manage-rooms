<?php
namespace DrPlus\Components;

use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;

class Alert {
	public static function view( array $args ) {
		$args = Utils::check_default( $args, [
			'type'		=> 'info', // success, warning, error
			'text'		=> '',
			'icon'		=> '',
			'classes'	=> [],
		], ['icon'] );

		$wrap_classes = ['drplus-alert', "drplus-alert-{$args['type']}"];
		if( !empty( $args['classes'] ) ) {
			$wrap_classes = array_merge( $wrap_classes, $args['classes'] );
		}
		$icon = Sanitizers::icon( $args['icon'], 'drplus-alert-icon' );
		if( $icon ) {
			$wrap_classes[] = 'drplus-alert-with-icon';
		}
		?>
		<div class="<?php echo Utils::prepare_html_classes( $wrap_classes ) ?>">
			<?php echo $icon ?>
			<div class="drplus-alert-text"><?php echo wp_kses_post( $args['text'] ) ?></div>
		</div>
		<?php
	}
}