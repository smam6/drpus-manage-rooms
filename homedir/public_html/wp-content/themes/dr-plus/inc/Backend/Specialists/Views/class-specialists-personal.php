<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Formatters;
use DrPlus\Utils\Options;
use DrPlus\Utils\User;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistPersonal extends SpecialistView {
	public static function view() {
		$options = Options::get_options( [
			'onboard-info-field-name-enabled'				=> true,
			'onboard-info-field-subtitle-enabled'			=> true,
			'onboard-info-field-email-enabled'				=> true,
			'onboard-info-field-birthday-enabled'			=> true,
			'onboard-info-field-nid-enabled'				=> true,
			'onboard-info-field-specialist-code-enabled'	=> true,
			'onboard-info-field-phone-enabled'				=> true,
			'onboard-info-field-gender-enabled'				=> true,
			'onboard-info-field-bio-enabled'				=> true,
			'onboard-info-field-name-required'				=> true,
			'onboard-info-field-subtitle-required'			=> true,
			'onboard-info-field-email-required'				=> true,
			'onboard-info-field-birthday-required'			=> true,
			'onboard-info-field-nid-required'				=> true,
			'onboard-info-field-specialist-code-required'	=> true,
			'onboard-info-field-phone-required'				=> true,
			'onboard-info-field-gender-required'			=> true,
			'onboard-info-field-bio-required'				=> false,
			'use-outside-iran'								=> false
		] );

		$use_outside_iran = Utils::to_bool( $options['use-outside-iran'] );

		// Get data
		if( !parent::$new ) {
			$meta_keys = ['mobile', 'birthday', 'nid', 'specialist_code', 'gender'];
			$user_data = [
				'first_name'	=> parent::$user->first_name,
				'last_name'		=> parent::$user->last_name,
				'email'			=> parent::$user->user_email,
				'avatar'		=> User::get_avatar_id( parent::$user->ID ),
			];

			foreach ( $meta_keys as $key ) {
				if( $key == 'specialist_code' ) {
					$_key = 'specialist-code';
				} else if( $key == 'mobile' ) {
					$_key = 'phone';
				} else {
					$_key = $key;
				}
				if( $options["onboard-info-field-{$_key}-enabled"] ) {
					$user_data[$key] = get_user_meta( parent::$user->ID, $key, true );
				}
			}
			if( isset( $user_data['mobile'] ) && empty( $user_data['mobile'] ) ) {
				$user_data['mobile'] = User::get_phone( parent::$user->ID );
			}
			if( !empty( $user_data['birthday'] ) ) {
				$user_data['birthday'] = strtotime( $user_data['birthday'] );
			}

			$user_data['avatar_url'] = get_avatar_url( parent::$user->ID, [
				'size'	=> 132
			] );
			$user_data['name'] = parent::$specialist['name'];
			$user_data['slug'] = parent::$specialist['slug'];
			$user_data['subtitle'] = parent::$specialist['subtitle'];
			$user_data['about'] = parent::$specialist['about'];
			$user_data['is_verified'] = parent::$specialist['is_verified'];
			$user_data['status'] = parent::$specialist['status'];
			$user_data['reject'] = parent::$specialist['reject'];
		}
		?>
		<div class="<?php echo self::$PREFIX ?>loading-wrap" style="display:none">
			<?php AdminUI::loading() ?>
			<div class="<?php echo self::$PREFIX ?>loading-text"><?php esc_html_e( 'Retrieving information...', 'drplus' ) ?></div>
		</div>
		<!-- Select User if new -->
		<?php if( parent::$new ) { ?>
			<div class="<?php echo parent::$PREFIX ?>select_user <?php echo parent::$PREFIX ?>row">
				<?php wp_nonce_field( 'drplus_get_users_nonce', 'drplus_get_users_nonce_value' ) ?>
				<?php wp_nonce_field( 'drplus_get_user_data_nonce', 'drplus_get_user_data_nonce_value' ) ?>
				<?php
				AdminUI::select_with_label( [
					'label'		=> esc_html__( 'Select Specialist', 'drplus' ),
					'value'		=> "",
					'id'		=> parent::$PREFIX . "select_user",
					'name'		=> parent::$PREFIX . "user_id",
					'required'	=> true,
				] );
				?>
			</div>
			<p id="<?php echo self::$PREFIX ?>new_specialist-note" class="description"><?php printf( __( 'To add a new specialist, you must first add the desired user from the <a href="%s">Users</a>, and then select the user from the list above.', 'drplus' ), admin_url( 'users.php' ) ) ?></p>
		<?php } else { ?>
			<input type="hidden" id="<?php echo parent::$PREFIX ?>user_id" name="<?php echo parent::$PREFIX ?>user_id" value="<?php echo parent::$user->ID ?>">
		<?php } ?>

		<!-- Side 1 -->
		<div class="<?php echo parent::$PREFIX ?>side">
			<div
				class="<?php echo parent::$PREFIX ?>avatar <?php echo parent::$PREFIX ?>row"
				id="<?php echo self::$PREFIX ?>avatar-wrap"
				data-default-avatar="<?php echo DRPLUS_URI . 'assets/images/user.svg' ?>"<?php echo empty( parent::$user ) ? " style='display:none'" : '' ?>
				<?php echo empty( $user_data ) || $user_data['status'] == 'rejected' ? " style='display:none'" : '' ?>
			>
				<input type="hidden" id="<?php echo parent::$PREFIX ?>avatar_file" name="<?php echo parent::$PREFIX ?>avatar" value="<?php echo $user_data['avatar'] ?? "" ?>">
				<img src="<?php echo !empty( $user_data['avatar'] ) ? esc_url( $user_data['avatar_url'] ) :  DRPLUS_URI . 'assets/images/user.svg' ?>" alt="<?php esc_attr_e( 'Avatar', 'drplus' ) ?>" class="<?php echo parent::$PREFIX ?>avatar_img">
				<div class="<?php echo parent::$PREFIX ?>avatar_action_btns">
					<button type="button" id="<?php echo parent::$PREFIX ?>remove_avatar"<?php echo empty( parent::$user ) || empty( $user_data['avatar'] ) ? " style='display:none'" : '' ?>><?php echo esc_html__( 'Remove', 'drplus' ) ?></button>
					<button type="button" id="<?php echo parent::$PREFIX ?>change_avatar"><?php echo esc_html__( 'Change', 'drplus' ) ?></button>
				</div>
			</div>
			<div class="<?php echo parent::$PREFIX ?>switches_data"<?php echo empty( parent::$user ) ? " style='display:none'" : '' ?>>
				<?php
				AdminUI::select_with_label( [
					'label'				=> esc_html__( 'Status', 'drplus' ),
					'value'				=> $user_data['status'] ?? "incomplete",
					'id'				=> parent::$PREFIX . "status",
					'name'				=> parent::$PREFIX . "status",
					'select_classes'	=> ['drplus-select2'],
					'required'			=> true,
					'data-width'		=> '100%',
					'options'			=> UtilsSpecialists::statuses(),
				] );
				AdminUI::switch( [
					'name'		=> parent::$PREFIX . "is_verified",
					'id'		=> parent::$PREFIX . "is_verified",
					'active'	=> $user_data['is_verified'] ?? false,
					'label'		=> __( 'Verified Specialist', 'drplus' ),
					'value'		=> 1,
					'wrap_id'	=> parent::$PREFIX . "is_verified-wrap",
				] );
				?>
			</div>
		</div>

		<!-- Side 2 -->
		<div class="<?php echo parent::$PREFIX ?>personal_data <?php echo parent::$PREFIX ?>row"<?php echo empty( parent::$user ) || empty( $user_data ) || $user_data['status'] == 'rejected' ? " style='display:none'" : '' ?>>
			<?php
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'First name', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $user_data['first_name'] ?? "",
				'id'			=> parent::$PREFIX . "first_name",
				'name'			=> parent::$PREFIX . "first_name",
				'input_classes'	=> ['regular-text'],
				'required'		=> true
			] );
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Last name', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $user_data['last_name'] ?? "",
				'id'			=> parent::$PREFIX . "last_name",
				'name'			=> parent::$PREFIX . "last_name",
				'input_classes'	=> ['regular-text'],
				'required'		=> true
			] );
			if( $options['onboard-info-field-name-enabled'] ) {
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Display name', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $user_data['name'] ?? "",
					'id'			=> parent::$PREFIX . "name",
					'name'			=> parent::$PREFIX . "name",
					'input_classes'	=> ['regular-text'],
					'required'		=> $options['onboard-info-field-name-required']
				] );
			}
			if( $options['onboard-info-field-subtitle-enabled'] ) {
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Subtitle', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $user_data['subtitle'] ?? "",
					'id'			=> parent::$PREFIX . "subtitle",
					'name'			=> parent::$PREFIX . "subtitle",
					'input_classes'	=> ['regular-text'],
					'required'		=> $options['onboard-info-field-subtitle-required']
				] );
			}
			AdminUI::input_with_label( [
				'label'			=> esc_html__( 'Slug', 'drplus' ),
				'type'			=> 'text',
				'value'			=> $user_data['slug'] ?? "",
				'id'			=> parent::$PREFIX . "slug",
				'name'			=> parent::$PREFIX . "slug",
				'input_classes'	=> ['regular-text', 'ltr', 'drplus-slug-input'],
				'required'		=> true,
				'minlength'		=> 1,
				'maxlength'		=> 255,
			] );
			if( $options['onboard-info-field-email-enabled'] ) {
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Email', 'drplus' ),
					'type'			=> 'email',
					'value'			=> $user_data['email'] ?? "",
					'id'			=> parent::$PREFIX . "email",
					'name'			=> parent::$PREFIX . "email",
					'input_classes'	=> ['regular-text', 'ltr'],
					'required'		=> $options['onboard-info-field-email-required'],
				] );
			}
			if( $options['onboard-info-field-birthday-enabled'] ) {
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Birthday', 'drplus' ),
					'type'			=> 'text',
					'data-time'		=> !empty( $user_data['birthday'] ) ? $user_data['birthday'] : strtotime( '-18 years' ),
					'id'			=> parent::$PREFIX . "birthday",
					'input_classes'	=> ['regular-text', 'drplus-datepicker-input'],
					'required'		=> $options['onboard-info-field-birthday-required'],
					'readonly'		=> 'readonly',
					'data-options'	=> [
						'maxDate'	=> strtotime( '-18 years' )*1000,
					],
					'alt_field'		=> [
						'id'	=> parent::$PREFIX . "birthday_alt", // Don't remove _alt
						'name'	=> parent::$PREFIX . "birthday",
						'value'	=> $user_data['birthday'] ?? ""
					],
				] );
			}
			if( $options['onboard-info-field-nid-enabled'] ) {
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'National ID', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $user_data['nid'] ?? "",
					'id'			=> parent::$PREFIX . "nid",
					'name'			=> parent::$PREFIX . "nid",
					'input_classes'	=> ['regular-text', 'ltr', 'drplus-numeric-input'],
					'required'		=> $options['onboard-info-field-nid-required'],
					'minlength'		=> 10,
					'maxlength'		=> 10,
					'inputmode'		=> 'numeric',
				] );
			}
			if( $options['onboard-info-field-specialist-code-enabled'] ) {
				AdminUI::input_with_label( [
					'label'			=> esc_html__( 'Medical ID', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $user_data['specialist_code'] ?? "",
					'id'			=> parent::$PREFIX . "specialist_code",
					'name'			=> parent::$PREFIX . "specialist_code",
					'input_classes'	=> ['regular-text'],
					'required'		=> $options['onboard-info-field-specialist-code-required'],
				] );
			}
			if( $options['onboard-info-field-phone-enabled'] ) {
				if ( empty( $user_data['mobile'] ) ) {
					$mobile = '';
				} else {
					$mobile = $use_outside_iran
						? $user_data['mobile']
						: Formatters::phone( $user_data['mobile'] );
				}
				$phone_input_args = [
					'label'			=> esc_html__( 'Phone number', 'drplus' ),
					'type'			=> 'text',
					'value'			=> $mobile,
					'id'			=> parent::$PREFIX . "mobile",
					'name'			=> parent::$PREFIX . "mobile",
					'input_classes'	=> ['regular-text', 'ltr'],
					'required'		=> $options['onboard-info-field-phone-required'],
					'inputmode'		=> 'tel',
				];
				if( !$use_outside_iran ) {
					$phone_input_args['minlength'] = 13;
					$phone_input_args['maxlength'] = 13;
					$phone_input_args['placeholder'] = '09...';
					$phone_input_args['input_classes'][] = 'drplus-phone-input';
				} else {
					$phone_input_args['input_classes'][] = 'drplus-numeric-input';
				}
				AdminUI::input_with_label( $phone_input_args );
				unset( $phone_input_args );
			}
			if( $options['onboard-info-field-gender-enabled'] ) {
				AdminUI::select_with_label( [
					'label'				=> esc_html__( 'Gender', 'drplus' ),
					'value'				=> $user_data['gender'] ?? "male",
					'id'				=> parent::$PREFIX . "gender",
					'name'				=> parent::$PREFIX . "gender",
					'select_classes'	=> ['drplus-select2'],
					'required'			=> $options['onboard-info-field-gender-required'],
					'data-width'		=> '100%',
					'options'			=> [
						'male'		=> __( 'Male', 'drplus' ),
						'female'	=> __( 'Female', 'drplus' ),
					]
				] );
			}
			if( $options['onboard-info-field-bio-enabled'] ) {
				wp_editor( $user_data['about'] ?? '', parent::$PREFIX . "about" );
			}
			?>
		</div>

		<div class="<?php echo parent::$PREFIX ?>rejected <?php echo parent::$PREFIX ?>row"<?php echo empty( parent::$user ) || empty( $user_data ) || $user_data['status'] != 'rejected' ? " style='display:none'" : '' ?>>
			<?php
			AdminUI::input_with_label( [
				'label'		=> esc_html__( 'Reason for rejection', 'drplus' ),
				'value'		=> $user_data['reject'] ?? "",
				'id'		=> parent::$PREFIX . "reject",
				'name'		=> parent::$PREFIX . "reject",
				'textarea'	=> true,
				'rows'		=> 8,
			] );
			?>
		</div>
		<?php
	}
}