<?php
namespace DrPlus\Backend\Metaboxes\Hospital;

use DrPlus\AdminScripts;
use DrPlus\PublicScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Hospital;

class Settings {
	PRIVATE STATIC $PREFIX = "drplus_hospital_";
	PRIVATE STATIC $POST_TYPES = ['hospital'];

	private static $settings = [];

	private static function get_settings() {
		if( !self::$settings ) {
			self::$settings = Hospital::get_options( get_the_ID(), true );
		}
		return self::$settings;
	}

	public static function enqueue( $hook ) {
		if( !in_array( $hook, ['post-new.php', 'post.php'] ) || !in_array( get_post_type(), self::$POST_TYPES ) ) return;

		PublicScripts::localizations( ['cities'] );
		PublicScripts::select2();
		PublicScripts::swapy();
		AdminScripts::tabs();
		AdminScripts::metabox( ['icon_picker'] );
		AdminScripts::repeater();
		AdminScripts::modal();
		AdminScripts::icon_picker();

		wp_enqueue_media();
		wp_enqueue_script('wp-util');

		wp_enqueue_style( 'drplus-hospital', DRPLUS_URI . "assets/css/backend/metaboxes/hospital.min.css", [], DRPLUS_VERSION );
		if( DRPLUS_DEV ) {
			wp_enqueue_script( 'drplus-hospital', DRPLUS_URI . "assets/js/backend/metaboxes/hospital.js", [], DRPLUS_VERSION, true );
		} else {
			wp_enqueue_script( 'drplus-hospital', DRPLUS_URI . "assets/js/backend/metaboxes/hospital.min.js", [], DRPLUS_VERSION, true );
		}
	}

	public static function add() {
		add_meta_box(
			self::$PREFIX,				// id
			__( 'Settings', 'drplus' ),	// title
			[__CLASS__, 'view'],		// callback
			self::$POST_TYPES			// screens
		);
	}

	public static function view( $post ) {
		wp_nonce_field( self::$PREFIX . "save_hospital", self::$PREFIX . "nonce" );

		self::get_settings();

		$tabs = [
			'general'	=> __( "General", 'drplus' ),
			'location'	=> __( "Location", 'drplus' ),
			'gallery'	=> __( "Gallery", 'drplus' ),
			'services'	=> __( "Services", 'drplus' ),
			'contacts'	=> __( "Contacts", 'drplus' ),
		];
		$default_tab = array_key_first( $tabs );
		?>

		<div class="drplus_metabox-tabs-container">
			<div class="drplus_metabox-tabs">
				<?php foreach( $tabs as $tab => $label ) { ?>
					<div class="drplus_metabox-tab<?php echo $tab === $default_tab ? " drplus_metabox-tab-active" : '' ?>" data-tab="<?php echo $tab ?>"><?php echo esc_html( $label ) ?></div>
				<?php } ?>
			</div>

			<div class="drplus_metabox-tabs-contents">
				<?php foreach( array_keys( $tabs ) as $tab ) { ?>
					<div class="drplus_metabox-tab-content drplus_metabox-tab-<?php echo $tab ?>-content" data-tab="<?php echo esc_attr( $tab ) ?>"<?php Utils::hide( $default_tab, $tab ) ?>>
						<?php self::$tab() ?>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
		AdminUI::modal( [
			'id'				=> "drplus-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function general() {
		?>
		<table class="form-table">
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>icon"><?php esc_html_e( 'Icon', 'drplus' ) ?></label>
				</th>

				<td>
					<?php
					AdminUI::icon_picker( [
						'id'		=> self::$PREFIX . "icon",
						'name'		=> self::$PREFIX . "icon",
						'icon'		=> self::$settings['icon'],
						'modal_id'	=> 'drplus-icon-picker-modal',
					] );
					?>
					<p class="description"><?php esc_html_e( 'This icon is displayed at the head of the hospital page.', 'drplus' ) ?></p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>subtitle"><?php esc_html_e( 'Subtitle', 'drplus' ) ?></label>
				</th>

				<td>
					<input type="text" name="<?php echo self::$PREFIX ?>subtitle" id="<?php echo self::$PREFIX ?>subtitle" class="large-text" value="<?php echo esc_attr( self::$settings['subtitle'] ) ?>">
				</td>
			</tr>
		</table>
		<?php
	}

	public static function location() {
		?>
		<table class="form-table">
			<tr>
				<th>
					<label for="<?php echo self::$PREFIX ?>address"><?php esc_html_e( 'Address', 'drplus' ) ?></label>
				</th>

				<td>
					<input type="text" name="<?php echo self::$PREFIX ?>address" id="<?php echo self::$PREFIX ?>address" class="large-text" value="<?php echo esc_attr( self::$settings['address'] ) ?>">
					<p class="description"><?php printf( __( 'Please Select province and city from <a href="%s">"Location section"</a> at sidebar', 'drplus' ), '#locationdiv' ) ?></p>
				</td>
			</tr>

			<tr>
				<th>
					<label><?php esc_html_e( 'Map URL', 'drplus' ) ?></label>
				</th>

				<td>
					<p class="description"><?php esc_html_e( 'Enter the map iframe code here', 'drplus' ) ?></p>
					<input type="text" name="<?php echo self::$PREFIX ?>map_address" id="<?php echo self::$PREFIX ?>map_address" class="ltr large-text" value="<?php echo esc_attr( self::$settings['map_address'] ) ?>">
				</td>
			</tr>
		</table>
		<?php
	}

	public static function gallery() {
		?>
		<input type="hidden" name="<?php echo self::$PREFIX ?>gallery" id="<?php echo self::$PREFIX ?>gallery" value="<?php echo esc_attr( implode( ",", self::$settings['gallery'] ) ) ?>">
		<div id="<?php echo self::$PREFIX ?>gallery-wrap">
			<?php
			foreach( self::$settings['gallery'] as $img_id ) {
				echo wp_get_attachment_image( $img_id, [128, 128] );
			}

			AdminUI::repeater_btn( [
				'id'	=> self::$PREFIX . "gallery-add",
				'icon'	=> 'dashicons dashicons-plus-alt',
				'text'	=> __( 'Add new image', 'drplus' ),
			] );
			?>
		</div>
		<?php
	}

	public static function services() {
		?>
		<script type="text/html" id="tmpl-drplus-hospital-service">
			<?php
			get_template_part( "templates/backend/hospital/service", null, [
				'index' 		=> "{{{data.index}}}",
				'title'			=> "{{{data.title}}}",
				'description'	=> "{{{data.description}}}",
			] );
			?>
		</script>

		<div id="<?php echo self::$PREFIX ?>services">
			<div id="<?php echo self::$PREFIX ?>services-notice"><?php esc_html_e( 'Please select your main services from the services in the sidebar. If you want to add some specific services for this hospital, please add them below.', 'drplus' ) ?></div>
			<?php
			foreach( self::$settings['services'] as $index => $service ) {
				get_template_part( "templates/backend/hospital/service", null, [
					'index' 		=> $index,
					'title'			=> $service['title'],
					'description'	=> $service['description'],
				] );
			}

			AdminUI::repeater_btn( [
				'id'	=> self::$PREFIX . "service-add",
				'icon'	=> 'dashicons dashicons-plus-alt',
				'text'	=> __( 'Add new service', 'drplus' ),
			] );
			?>
		</div>
		<?php
	}

	public static function contacts() {
		?>
		<div class="<?php echo self::$PREFIX ?>contacts">
			<div class="<?php echo self::$PREFIX ?>contacts-section">
				<script type="text/html" id="tmpl-drplus-hospital-phone">
					<?php
					get_template_part( "templates/backend/hospital/contact", 'phone', [
						'index'	=> "{{{data.index}}}",
						'title'	=> "{{{data.title}}}",
						'phone'	=> "{{{data.phone}}}",
					] );
					?>
				</script>

				<div class="<?php echo self::$PREFIX ?>contacts-title"><?php esc_html_e( 'Phones', 'drplus' ) ?></div>
				
				<div class="<?php echo self::$PREFIX ?>contacts-list" id="<?php echo self::$PREFIX ?>phones">
					<?php
					foreach( self::$settings['phones'] as $index => $phone ) {
						get_template_part( "templates/backend/hospital/contact", 'phone', [
							'index'	=> $index,
							'title'	=> $phone['title'],
							'phone'	=> $phone['phone'],
						] );
					}

					AdminUI::repeater_btn( [
						'id'	=> self::$PREFIX . "phone-add",
						'icon'	=> 'dashicons dashicons-plus-alt',
						'text'	=> __( 'Add new phone', 'drplus' ),
					] );
					?>
				</div>
			</div>

			<div class="<?php echo self::$PREFIX ?>contacts-section">
				<script type="text/html" id="tmpl-drplus-hospital-email">
					<?php
					get_template_part( "templates/backend/hospital/contact", 'email', [
						'index'	=> "{{{data.index}}}",
						'title'	=> "{{{data.title}}}",
						'email'	=> "{{{data.email}}}",
					] );
					?>
				</script>

				<div class="<?php echo self::$PREFIX ?>contacts-title"><?php esc_html_e( 'Emails', 'drplus' ) ?></div>
				
				<div class="<?php echo self::$PREFIX ?>contacts-list" id="<?php echo self::$PREFIX ?>emails">
					<?php
					foreach( self::$settings['emails'] as $index => $email ) {
						get_template_part( "templates/backend/hospital/contact", 'email', [
							'index'	=> $index,
							'title'	=> $email['title'],
							'email'	=> $email['email'],
						] );
					}

					AdminUI::repeater_btn( [
						'id'	=> self::$PREFIX . "email-add",
						'icon'	=> 'dashicons dashicons-plus-alt',
						'text'	=> __( 'Add new email', 'drplus' ),
					] );
					?>
				</div>
			</div>

			<div class="<?php echo self::$PREFIX ?>contacts-section">
				<script type="text/html" id="tmpl-drplus-hospital-social">
					<?php
					get_template_part( "templates/backend/hospital/contact", 'social', [
						'index'	=> "{{{data.index}}}",
						'title'	=> "{{{data.title}}}",
					] );
					?>
				</script>

				<div class="<?php echo self::$PREFIX ?>contacts-title"><?php esc_html_e( 'Social accounts', 'drplus' ) ?></div>
				
				<div class="<?php echo self::$PREFIX ?>contacts-list" id="<?php echo self::$PREFIX ?>socials">
					<?php
					foreach( self::$settings['socials'] as $index => $social ) {
						get_template_part( "templates/backend/hospital/contact", 'social', [
							'index'	=> $index,
							'title'	=> $social['title'],
							'icon'	=> $social['icon'],
							'link'	=> $social['link'],
						] );
					}

					AdminUI::repeater_btn( [
						'id'	=> self::$PREFIX . "social-add",
						'icon'	=> 'dashicons dashicons-plus-alt',
						'text'	=> __( 'Add new account', 'drplus' ),
					] );
					?>
				</div>
			</div>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;

		// Check nonce value
		if( empty( $_POST[self::$PREFIX . "nonce"] ) ) return;
				
		// Check nonce
		$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
		if( !wp_verify_nonce( $nonce, self::$PREFIX . "save_hospital" ) ) return;
		
		$settings = [
			'icon'			=> $_POST[self::$PREFIX . "icon"],
			'subtitle'		=> $_POST[self::$PREFIX . "subtitle"],
			'address'		=> $_POST[self::$PREFIX . "address"],
			'map_address'	=> $_POST[self::$PREFIX . "map_address"],
			'gallery'		=> $_POST[self::$PREFIX . "gallery"] ?? [],
			'services'		=> $_POST[self::$PREFIX . "service"] ?? [],
			'phones'		=> $_POST[self::$PREFIX . "phone"] ?? [],
			'emails'		=> $_POST[self::$PREFIX . "email"] ?? [],
			'socials'		=> $_POST[self::$PREFIX . "social"] ?? [],
		];
		Hospital::save_options( $settings, $post_id );
	}
}
add_action( 'admin_enqueue_scripts', [Settings::class, 'enqueue'] );
add_action( 'add_meta_boxes', [Settings::class, 'add'] );
add_action( 'save_post', [Settings::class, 'save'], 10, 2 );