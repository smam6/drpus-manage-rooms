<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Model\Specialists;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\UtilsSpecialists;


class SpecialistView extends Settings {
	protected static $PREFIX = 'specialist_';
	protected static $new = true;
	protected static $specialist = null;
	protected static $user = null;
	protected static $active_submit_button = true;
	protected static $max_upload_size_bytes = 0;
	
	public static function view() {
		$sid = 0;
		if( !empty( $_GET['sid'] ) ) {
			$sid = Utils::convert_chars( $_GET['sid'], true, 'absint' );

			// Get specialist
			self::$specialist = (new Specialists)->find( $sid );
			self::$new = empty( self::$specialist );
			$user_id = self::$specialist->user_id;
			self::$user = get_user_by( 'ID', $user_id );
		}

		self::$max_upload_size_bytes = Utils::get_max_upload_size();

		$tabs = [
			'personal'		=> [
				'label'		=> __( "Personal Information", 'drplus' ),
				'function'	=> [__CLASS__, 'personal'],
			],
			'identity'		=> [
				'label'		=> __( "Identity documents", 'drplus' ),
				'function'	=> [__CLASS__, 'identity'],
			],
			'services'		=> [
				'label'		=> __( "Specialized Services", 'drplus' ),
				'function'	=> [__CLASS__, 'services'],
			],
			'offices'		=> [
				'label'		=> __( "Offices", 'drplus' ),
				'function'	=> [__CLASS__, 'offices'],
			],
			'reserve'		=> [
				'label'		=> __( "Reservation Settings", 'drplus' ),
				'function'	=> [__CLASS__, 'reserve'],
			],
			'certificates'	=> [
				'label'		=> __( "Certificates and Courses", 'drplus' ),
				'function'	=> [__CLASS__, 'certificates'],
			],
			'financial'		=> [
				'label'		=> __( "Financial Information", 'drplus' ),
				'function'	=> [__CLASS__, 'financial'],
			],
			'seo'			=> [
				'label'		=> __( 'SEO', 'drplus' ),
				'function'	=> [__CLASS__, 'seo'],
			],
		];
		if( !self::$new ) {
			$tabs['user-reviews'] = [
				'label'		=> __( 'User Reviews', 'drplus' ),
				'function'	=> ''
			];
		}
		$tabs = apply_filters( 'drplus/backend/specialist/settings/tabs', $tabs, self::$new, self::$specialist, self::$user );

		$active_tab = Utils::convert_chars( $_GET['section'] ?? array_key_first( $tabs ) );
		$active_tab = Utils::ensure_values_in_array( $active_tab, array_keys( $tabs ), array_key_first( $tabs ) );

		$url = remove_query_arg( ['office'] );

		// Redirect to first tab if new
		if( self::$new === true && $active_tab != array_key_first( $tabs ) ) {
			wp_redirect( add_query_arg( ['section' => array_key_first( $tabs )], $url ) );
			die;
		}
		?>
		<div class="<?php echo self::$PREFIX ?>specialist">
			<h2 class="<?php echo self::$PREFIX ?>title">
				<?php self::$new ? esc_html_e( 'Add new specialist', 'drplus' ) : printf( esc_html__( 'Edit Specialist %s', 'drplus' ), self::$user->display_name ) ?>
				<?php
				if( !self::$new ) {
					$post = get_posts( [
						'post_type'		=> 'specialist',
						'meta_key'		=> '_drplus_specialist_id',
						'meta_value'	=> self::$specialist['id'],
					] );
					if( !empty( $post[0] ) ) {
						?>
						<a href="<?php echo get_permalink( $post[0] ) ?>" class="page-title-action" target="_blank"><?php esc_html_e( 'View specialist', 'drplus' ) ?></a>
						<?php
					}
					?>
				<?php } ?>
			</h2>
			<div class="drplus_metabox-tabs-container">
				<div class="drplus_metabox-tabs">
					<?php foreach( $tabs as $tab => $tab_data ) { ?>
						<?php
						$tab_classes = ["drplus_metabox-tab"];
						if( $tab === $active_tab ) $tab_classes[] = 'drplus_metabox-tab-active';

						// Add disabled class to other tab button if new
						if( $tab != array_key_first( $tabs ) &&
							( self::$new === true ) ||
							( !empty( self::$specialist ) && self::$specialist['status'] == 'rejected' )
						) {
							$tab_classes[] = 'disabled';
						}

						if( $tab == 'user-reviews' ) {
							$tab_link = admin_url( "edit-comments.php?only_specialists=true&specialist={$sid}" );
						} else {
							$tab_link = add_query_arg( ['section' => $tab], $url );
						}
						?>
						<a href="<?php echo $tab_link ?>" class="<?php echo Utils::prepare_html_classes( $tab_classes ) ?>"><?php echo esc_html( $tab_data['label'] ) ?></a>
					<?php } ?>
				</div>

				<div class="drplus_metabox-tabs-contents">
					<form action="" method="POST" id="specialist-form">
						<input type="hidden" name="<?php echo self::$PREFIX ?>section" id="<?php echo self::$PREFIX ?>section" value="<?php echo $active_tab ?>">
						<?php if( $active_tab != 'personal' ) { ?>
							<input type="hidden" id="<?php echo self::$PREFIX ?>user_id" name="<?php echo self::$PREFIX ?>user_id" value="<?php echo self::$user->ID ?? 0 ?>">
						<?php } ?>
						<?php wp_nonce_field( self::$PREFIX . "nonce", self::$PREFIX . "nonce_value" ) ?>
						<h3 class="<?php echo self::$PREFIX ?>section_title">
							<?php echo $tabs[$active_tab]['label'] ?>
						</h3>
						<div class="<?php echo self::$PREFIX . $active_tab ?>-section">
							<?php
							do_action( "drplus/backend/specialist/settings/{$active_tab}/start", self::$specialist, self::$user, self::$new );
							if( !empty( $tabs[$active_tab] ) && !empty( $tabs[$active_tab]['function'] ) ) {
								call_user_func( $tabs[$active_tab]['function'] );
							}
							do_action( "drplus/backend/specialist/settings/{$active_tab}/end", self::$specialist, self::$user, self::$new );
							?>
						</div>

						<input
							type="submit"
							name="<?php echo self::$PREFIX ?>submit"
							id="<?php echo self::$PREFIX ?>submit"
							value="<?php esc_html_e( 'Apply changes', 'drplus' ) ?>"
							<?php echo self::$active_submit_button ? '' : 'class="disabled"' ?>
						>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	public static function save() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Saves/specialists-view-save.php" );
	}

	public static function personal() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-personal.php" );
		SpecialistPersonal::view();
	}

	private static function identity() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-identity.php" );
		SpecialistIdentity::view();
	}

	private static function services() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-services.php" );
		SpecialistServices::view();
	}

	private static function offices() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-offices.php" );
		SpecialistOffices::view();
	}

	private static function reserve() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-reserve.php" );
		SpecialistReserve::view();
	}

	private static function certificates() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-certificates.php" );
		SpecialistCertificates::view();
	}

	private static function financial() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-financial.php" );
		SpecialistFinancial::view();
	}

	private static function seo() {
		include( DRPLUS_DIR . "inc/Backend/Specialists/Views/class-specialists-seo.php" );
		SpecialistSeo::view();
	}

	protected static function repeater_template( $args ) {
		$args = Utils::check_default(  $args, [
			'prefix'			=> "",
			'id'				=> '',
			'input_value'		=> '',
			'input_label'		=> '',
			'input_name'		=> '',
			'textarea_value'	=> '',
			'textarea_label'	=> '',
			'textarea_name'		=> '',
			'data_name'			=> '',
		] );

		?>
		<div class="<?php echo esc_attr( $args['prefix'] ) ?>wrap <?php echo self::$PREFIX ?>repeater_slot" data-swapy-slot="<?php echo esc_attr( $args['prefix'] ) ?>slot-<?php echo esc_attr( $args['id'] ) ?>">
			<div class="<?php echo esc_attr( $args['prefix'] ) ?>item <?php echo self::$PREFIX ?>repeater_item" data-swapy-item="<?php echo esc_attr( $args['prefix'] ) . esc_attr( $args['id'] ) ?>">
				<div class="<?php echo esc_attr( $args['prefix'] ) ?>head <?php echo self::$PREFIX ?>repeater-head">
					<span class="<?php echo esc_attr( $args['prefix'] ) ?>index <?php echo self::$PREFIX ?>repeater-index"><?php echo esc_html( $args['id'] ) ?></span>
					<i class="dashicons dashicons-menu-alt3 <?php echo self::$PREFIX ?>repeater-move" data-swapy-handle></i>
					<i class="dashicons dashicons-trash <?php echo self::$PREFIX ?>repeater-remove"></i>
				</div>
				<div class="<?php echo esc_attr( $args['prefix'] ) ?>body <?php echo self::$PREFIX ?>repeater-body">
					<?php
					AdminUI::input_with_label( [
						'label'			=> $args['input_label'],
						'type'			=> 'text',
						'value'			=> $args['input_value'],
						'id'			=> $args['prefix'] . "title_{$args['id']}",
						'name'			=> self::$PREFIX . "meta[{$args['data_name']}][{$args['id']}][{$args['input_name']}]",
						'input_classes'	=> ['regular-text', $args['prefix'] . "title"],
					] );
					AdminUI::input_with_label( [
						'label'			=> $args['textarea_label'],
						'value'			=> $args['textarea_value'],
						'id'			=> $args['prefix'] . "desc_{$args['id']}",
						'name'			=> self::$PREFIX . "meta[{$args['data_name']}][{$args['id']}][{$args['textarea_name']}]",
						'textarea'		=> true,
						'input_classes'	=> [$args['prefix'] . "desc"],
						'rows'			=> 2
					] );
					?>
				</div>
			</div>
		</div>
		<?php
	}
}