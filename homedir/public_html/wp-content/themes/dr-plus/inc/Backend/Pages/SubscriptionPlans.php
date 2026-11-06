<?php
namespace DrPlus\Backend\Pages;

use DrPlus\AdminScripts;
use DrPlus\Backend\Pages\SubscriptionPlans\Plans;
use DrPlus\Backend\Pages\SubscriptionPlans\PlansGeneral;
use DrPlus\PublicScripts;
use DrPlus\Utils;

class SubscriptionPlansSettings {
	PRIVATE STATIC $PREFIX = 'drplus_plans_';

	public static function enqueue() {
		if( empty( $_GET['page'] ) || $_GET['page'] != 'drplus-subscription-plans' ) return;

		wp_enqueue_style( 'drplus-icons', DRPLUS_URI . "assets/css/iconly.min.css", [], DRPLUS_VERSION );
		AdminScripts::switch();

		$active_item = self::get_active_section();
		if( $active_item == 'plans' ) {
			AdminScripts::form_group();
			AdminScripts::modal();
			AdminScripts::icon_picker();
			wp_enqueue_script('wp-util');
			if( DRPLUS_DEV ) {
				wp_enqueue_script( 'drplus-subscription-plans', DRPLUS_URI . "/assets/js/backend/pages/subscription_plans.js", ['jquery'], DRPLUS_VERSION, true );
			} else {
				wp_enqueue_script( 'drplus-subscription-plans', DRPLUS_URI . "/assets/js/backend/pages/subscription_plans.min.js", ['jquery'], DRPLUS_VERSION, true );
			}
		} else if( $active_item == 'general' ) {
			PublicScripts::select2();
			AdminScripts::form_group();
		}
		
		wp_enqueue_style( 'drplus-subscription-plans', DRPLUS_URI . "assets/css/backend/pages/subscription_plans.min.css", [], DRPLUS_VERSION );
	}

	public static function menu() {
		add_submenu_page(
			'drplus', 										// $parent_slug:string,
			__( 'Subscription Plans settings', 'drplus' ),	// $page_title:string,
			__( 'Subscription Plans settings', 'drplus' ),	// $menu_title:string,
			'manage_options',								// $capability:string,
			'drplus-subscription-plans',					// $menu_slug:string,
			[__CLASS__, 'view'],							// $callback:callable,
			50												// $position:integer|float|null
		);
	}

	public static function get_active_section() {
		$sidebar_items = self::sidebar_items();
		return Utils::ensure_values_in_array( Utils::convert_chars( $_GET['section'] ?? "" ), array_keys( $sidebar_items ), array_key_first( $sidebar_items ) );
	}

	public static function sidebar_items() {
		return [
			'general'	=> [
				'label'		=> __( 'General', 'drplus' ),
				'icon'		=> 'element-3',
				'class'		=> PlansGeneral::class,
			],
			'plans'	=> [
				'label'		=> __( 'Plans', 'drplus' ),
				'icon'		=> 'archive-book',
				'class'		=> Plans::class,
			],
			// 'statistics'	=> [
			// 	'label'		=> __( 'Statistics', 'drplus' ),
			// 	'icon'		=> 'counter',
			// 	'class'		=> PlansStatistics::class,
			// ],
			// 'specialists'	=> [
			// 	'label'		=> __( 'Specialists', 'drplus' ),
			// 	'icon'		=> 'doctor-profile',
			// 	'class'		=> PlansSpecialists::class,
			// ],
		];
	}

	public static function view() {
		$sidebar_items = self::sidebar_items();
		$active_item = self::get_active_section();

		?>
		<div class="wrap">
			<?php echo settings_errors( 'drplus-subscription-plans-settings' ); ?>
			<h1 class="page-title"><?php echo esc_html( get_admin_page_title() ) ?></h1>
			<p class="description"><?php esc_html_e( "In this section, you can configure Specialists subscription plans settings.", 'drplus' ) ?></p>
			<hr>

			<div id="<?php echo self::$PREFIX ?>container">
				<?php wp_nonce_field( self::$PREFIX . "save", self::$PREFIX . "nonce" ) ?>
				<div id="<?php echo self::$PREFIX ?>sidebar">
					<?php
					$page_link = add_query_arg( ['page' => 'drplus-subscription-plans' ], admin_url( 'admin.php' ) );
					foreach( $sidebar_items as $id => $item ) {
						$item_classes = [self::$PREFIX . 'sidebar-item'];
						if( $id == $active_item ) {
							$item_classes[] = 'active';
						}
						?>
						<div class="<?php echo Utils::prepare_html_classes( $item_classes ) ?>" data-tab="<?php echo $id ?>">
							<a href="<?php echo add_query_arg( ['section' => $id], $page_link ) ?>" class="<?php echo self::$PREFIX ?>sidebar-item-inner">
								<i class="<?php echo self::$PREFIX ?>sidebar-item-icon drplus-icon-<?php echo $item['icon'] ?>"></i>
								<span class="<?php echo self::$PREFIX ?>sidebar-item-label"><?php echo esc_html( $item['label'] ) ?></span>
							</a>
						</div>
					<?php } ?>
				</div>

				<div id="<?php echo self::$PREFIX ?>body">
					<?php
					include_once( DRPLUS_DIR . "inc/Backend/Pages/SubscriptionPlans/subscription-plans-{$active_item}.php" );
					$sidebar_items[$active_item]['class']::view();
					?>
				</div>
			</div>
		</div>
		<?php
	}

	public static function create_nonce() {
		wp_nonce_field( self::$PREFIX . 'save', self::$PREFIX . 'nonce' );
	}

	public static function save() {
		if( empty( $_POST ) || empty( $_POST[self::$PREFIX . "nonce"] ) ) return;

		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . 'nonce'] );
		if( empty( $nonce ) || !wp_verify_nonce( $nonce, self::$PREFIX . "save" ) ) return;

		$sidebar_items = self::sidebar_items();
		$active_item = self::get_active_section();
		include_once( DRPLUS_DIR . "inc/Backend/Pages/SubscriptionPlans/subscription-plans-{$active_item}.php" );
		$sidebar_items[$active_item]['class']::save();
	}
}
add_action( 'admin_enqueue_scripts', [SubscriptionPlansSettings::class, 'enqueue'] );
add_action( 'admin_menu', [SubscriptionPlansSettings::class, 'menu'], 11 );
add_action( 'admin_init', [SubscriptionPlansSettings::class, 'save'] );