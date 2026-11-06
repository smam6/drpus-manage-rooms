<?php
namespace DrPlus\Metaboxes\Backend\Page;

use DrPlus\AdminScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Page;

if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( "DrPlus\Metaboxes\Page\Settings" ) ) {
	class Settings {
		PRIVATE STATIC $PREFIX = "drplus_";
		PRIVATE STATIC $POST_TYPES = ['page'];

		public static function enqueue( $hook ) {
			if( !in_array( $hook, ['post-new.php', 'post.php'] ) || get_post_type() != 'page' ) return;
			
			AdminScripts::metabox( ['icon_picker'] );
			AdminScripts::tabs();
			AdminScripts::switch();
			AdminScripts::alert();
			AdminScripts::modal();
			AdminScripts::icon_picker();
		}

		public static function add() {
			add_meta_box(
				self::$PREFIX,					// id
				__( 'Settings', 'drplus' ),	// title
				[__CLASS__, 'view'],			// callback
				self::$POST_TYPES				// screens
			);
		}

		public static function view( $post ) {
			wp_nonce_field( self::$PREFIX . "save_page", self::$PREFIX . "nonce" );

			$tabs = [
				'header'	=> __( "Header", 'drplus' ),
				'body'		=> __( "Body", 'drplus' ),
				'footer'	=> __( "Footer", 'drplus' ),
			];
			$default_tab = 'header';
			?>
			<div class="drplus_metabox-tabs-container">
				<?php
				$is_archive_page = absint( get_option( 'page_for_posts' ) ) === get_the_ID();
				if( $is_archive_page ) {
					AdminUI::alert( [
						'text'	=> sprintf( __( 'This page is set to archive page. Change the settings from <a href="%s">options</a>', 'drplus' ), admin_url( "admin.php?page=drplus&tab=1" ) ),
						'icon'	=> 'drplus-icon-edit'
					] );
				} else {
					$options = Page::get_options( $post->ID );
					?>
					<div class="drplus_metabox-tabs">
						<?php foreach( $tabs as $tab => $label ) { ?>
							<div class="drplus_metabox-tab<?php echo $tab === $default_tab ? " drplus_metabox-tab-active" : '' ?>" data-tab="<?php echo $tab ?>"><?php echo esc_html( $label ) ?></div>
						<?php } ?>
					</div>

					<div class="drplus_metabox-tabs-contents">
						<?php foreach( array_keys( $tabs ) as $tab ) { ?>
							<div class="drplus_metabox-tab-content" data-tab="<?php echo esc_attr( $tab ) ?>"<?php Utils::hide( $default_tab, $tab ) ?>>
								<?php self::$tab( $options ) ?>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
			</div>
			<?php
		}

		public static function save( $post_id, $post ) {
			if( !in_array( $post->post_type, self::$POST_TYPES ) || empty( $_POST ) ) return;
	
			// Check nonce value
			if( empty( $_POST[self::$PREFIX . "nonce"] ) ) return;
					
			// Check nonce
			$nonce = Utils::convert_chars( $_POST[self::$PREFIX . "nonce"] );
			if( !wp_verify_nonce( $nonce, self::$PREFIX . "save_page" ) ) return;
	
			$options = [
				'disable_header'		=> !empty( $_POST[self::$PREFIX . "disable_header"] ) && Utils::to_bool( $_POST[self::$PREFIX . "disable_header"] ) ? true : false,
				'disable_header_user'	=> Utils::convert_chars( $_POST[self::$PREFIX . "disable_header_user"] ),
				'show_breadcrumb'		=> !empty( $_POST[self::$PREFIX . "show_breadcrumb"] ) && Utils::to_bool( $_POST[self::$PREFIX . "show_breadcrumb"] ) ? true : false,
				'show_title'			=> !empty( $_POST[self::$PREFIX . "show_title"] ) && Utils::to_bool( $_POST[self::$PREFIX . "show_title"] ) ? true : false,
				'show_sidebar'			=> !empty( $_POST[self::$PREFIX . "show_sidebar"] ) && Utils::to_bool( $_POST[self::$PREFIX . "show_sidebar"] ) ? true : false,
				'fullwidth'				=> !empty( $_POST[self::$PREFIX . "fullwidth"] ) && Utils::to_bool( $_POST[self::$PREFIX . "fullwidth"] ) ? true : false,
				'use_content_style'		=> !empty( $_POST[self::$PREFIX . "use_content_style"] ) && Utils::to_bool( $_POST[self::$PREFIX . "use_content_style"] ) ? true : false,
				'disable_footer'		=> !empty( $_POST[self::$PREFIX . "disable_footer"] ) && Utils::to_bool( $_POST[self::$PREFIX . "disable_footer"] ) ? true : false,
				'disable_footer_user'	=> Utils::convert_chars( $_POST[self::$PREFIX . "disable_footer_user"] ),
				'page_icon'				=> Utils::convert_chars( $_POST[self::$PREFIX . "page_icon"] ),
				'sidebar'				=> Utils::convert_chars( $_POST[self::$PREFIX . 'sidebar'] ),
			];
			Page::save_options( $options, $post_id );
		}

		public static function header( $options ) {
			AdminUI::switch( [
				'name'		=> self::$PREFIX . "disable_header",
				'id'		=> self::$PREFIX . "disable_header",
				'value'		=> 'true',
				'active'	=> $options['disable_header'] === true,
				'label'		=> __( 'Disable header in this page', 'drplus' )
			] );
			?>
			<table class="form-table" id="disable_header_user-table" <?php echo !$options['disable_header'] ? 'style="display:none"' : '' ?>>
				<tr>
					<th><label for="<?php echo self::$PREFIX ?>disable_header_user"><?php esc_html_e( 'Disable header for specific users', 'drplus' ) ?></label></th>
					<td>
						<select name="<?php echo self::$PREFIX ?>disable_header_user" id="<?php echo self::$PREFIX ?>disable_header_user" class="regular-text">
							<option value="all" <?php selected( $options['disable_header_user'], 'all' ) ?>><?php esc_html_e( 'All users', 'drplus' ) ?></option>
							<option value="guests" <?php selected( $options['disable_header_user'], 'guests' ) ?>><?php esc_html_e( 'Guests only', 'drplus' ) ?></option>
							<option value="users" <?php selected( $options['disable_header_user'], 'users' ) ?>><?php esc_html_e( 'Logged in users only', 'drplus' ) ?></option>
						</select>
					</td>
				</tr>
			</table>
			<?php
		}

		public static function body( $options ) {
			AdminUI::switch( [
				'name'		=> self::$PREFIX . "fullwidth",
				'id'		=> self::$PREFIX . "fullwidth",
				'value'		=> 'true',
				'active'	=> $options['fullwidth'] === true,
				'label'		=> __( 'Check the checkbox to set the page to fullwidth', 'drplus' )
			] );

			AdminUI::switch( [
				'name'		=> self::$PREFIX . "use_content_style",
				'id'		=> self::$PREFIX . "use_content_style",
				'value'		=> 'true',
				'active'	=> $options['use_content_style'] === true,
				'label'		=> __( 'Check the checkbox to use default styles for content(background, border and others). If you want just show the content, uncheck this', 'drplus' )
			] );
			
			AdminUI::switch( [
				'name'		=> self::$PREFIX . "show_breadcrumb",
				'id'		=> self::$PREFIX . "show_breadcrumb",
				'value'		=> 'true',
				'active'	=> $options['show_breadcrumb'] === true,
				'label'		=> __( 'Check the checkbox to show breadcrumb', 'drplus' )
			] );

			AdminUI::switch( [
				'name'		=> self::$PREFIX . "show_title",
				'id'		=> self::$PREFIX . "show_title",
				'id'		=> self::$PREFIX . "show_title",
				'value'		=> 'true',
				'active'	=> $options['show_title'] === true,
				'label'		=> __( 'Check the checkbox to show title of the page', 'drplus' )
			] );

			AdminUI::switch( [
				'name'		=> self::$PREFIX . "show_sidebar",
				'id'		=> self::$PREFIX . "show_sidebar",
				'id'		=> self::$PREFIX . "show_sidebar",
				'value'		=> 'true',
				'active'	=> $options['show_sidebar'] === true,
				'label'		=> __( 'Check the checkbox to show sidebar', 'drplus' )
			] );

			?>
			<table class="form-table">
				<tr id="page-icon-row"<?php Utils::hide( true, $options['show_title'] ) ?>>
					<th>
						<label for="<?php echo self::$PREFIX ?>page_icon"><?php esc_html_e( 'Page icon', 'drplus' ) ?></label>
					</th>

					<td>
						<?php AdminUI::icon_picker( [
							'name'		=> self::$PREFIX . "page_icon",
							'icon'		=> $options['page_icon'],
							'modal_id'	=> self::$PREFIX . "icon-picker-modal"
						] ) ?>
					</td>
				</tr>

				<tr id="select-sidebar-row"<?php Utils::hide( true, $options['show_sidebar'] ) ?>>
					<th>
						<label for="<?php echo self::$PREFIX ?>sidebar"><?php esc_html_e( 'Select sidebar', 'drplus' ) ?></label>
					</th>

					<td>
						<select name="<?php echo self::$PREFIX ?>sidebar" id="<?php echo self::$PREFIX ?>sidebar" class="regular-text">
							<?php foreach( Utils::sidebars_list() as $sidebar ) { ?>
								<option value="<?php echo esc_attr( $sidebar['id'] ) ?>" <?php selected( $sidebar['id'], $options['sidebar'] ) ?>><?php echo esc_html( $sidebar['name'] ) ?></option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</table>
			<?php
			AdminUI::modal( [
				'id'				=> self::$PREFIX . "icon-picker-modal",
				'title'				=> esc_html__( "Select your icon", 'drplus' ),
				'classes'			=> ['icon-picker-modal'],
				'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
			] );
		}

		public static function footer( $options ) {
			AdminUI::switch( [
				'name'		=> self::$PREFIX . "disable_footer",
				'id'		=> self::$PREFIX . "disable_footer",
				'value'		=> 'true',
				'active'	=> $options['disable_footer'] === true,
				'label'		=> __( 'Disable footer in this page', 'drplus' )
			] );
			?>
			<table class="form-table" id="disable_footer_user-table" <?php echo !$options['disable_footer'] ? 'style="display:none"' : '' ?>>
				<tr>
					<th><label for="<?php echo self::$PREFIX ?>disable_footer_user"><?php esc_html_e( 'Disable footer for specific users', 'drplus' ) ?></label></th>
					<td>
						<select name="<?php echo self::$PREFIX ?>disable_footer_user" id="<?php echo self::$PREFIX ?>disable_footer_user" class="regular-text">
							<option value="all" <?php selected( $options['disable_footer_user'], 'all' ) ?>><?php esc_html_e( 'All users', 'drplus' ) ?></option>
							<option value="guests" <?php selected( $options['disable_footer_user'], 'guests' ) ?>><?php esc_html_e( 'Guests only', 'drplus' ) ?></option>
							<option value="users" <?php selected( $options['disable_footer_user'], 'users' ) ?>><?php esc_html_e( 'Logged in users only', 'drplus' ) ?></option>
						</select>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	add_action( 'admin_enqueue_scripts', [Settings::class, 'enqueue'] );
	add_action( 'add_meta_boxes', [Settings::class, 'add'] );
	add_action( 'save_post', [Settings::class, 'save'], 10, 2 );
}