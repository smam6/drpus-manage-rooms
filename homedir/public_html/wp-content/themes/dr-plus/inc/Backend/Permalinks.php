<?php
namespace DrPlus\Backend;

class Permalinks {
	public static function register() {
		add_settings_section(
			'drplus_permalinks_section',				// $id:string,
			__( 'Doctor plus permalinks', 'drplus' ),	// $title:string,
			null,										// $callback:callable,
			'permalink',								// $page:string,
		);
		$fields = self::fields();

		foreach( $fields as $field_id => $field ) {
			register_setting(
				'permalink',	// $option_group:string,
				$field_id,		// $option_name:string,
				[				// $args:array
					'type'				=> 'string',
					'label'				=> $field['label'],
					'sanitize_callback'	=> 'sanitize_title',
					'show_in_rest'		=> false,
				]
			);

			$field_args = $field;
			$field_args['id'] = $field_id;
			add_settings_field(
				$field_id,						// $id:string,
				$field['label'],				// $title:string,
				[__CLASS__, 'field'],			// $callback:callable,
				'permalink',					// $page:string,
				'drplus_permalinks_section',	// $section:string,
				$field_args						// $args:array
			);
		}
	}

	/**
	 * Return fields configuration.
	 *
	 * @return array
	 */
	protected static function fields() {
		return [
			'drplus_specialists' => [
				'label' => _x( 'Specialists archive', 'permalink label', 'drplus' ),
				'default' => 'specialists',
			],
			'drplus_specialist' => [
				'label' => _x( 'Single Specialist', 'permalink label', 'drplus' ),
				'default' => 'specialist',
			],
			'drplus_specialities' => [
				'label' => _x( 'Specialities archive', 'permalink label', 'drplus' ),
				'default' => 'specialities',
			],
			'drplus_speciality' => [
				'label' => _x( 'Single speciality', 'permalink label', 'drplus' ),
				'default' => 'speciality',
			],
			'drplus_hospitals' => [
				'label' => _x( 'Hospitals archive', 'permalink label', 'drplus' ),
				'default' => 'hospitals',
			],
			'drplus_hospital' => [
				'label' => _x( 'Single Hospital', 'permalink label', 'drplus' ),
				'default' => 'hospital',
			],
			'drplus_hospital-category' => [
				'label' => _x( 'Hospital category', 'permalink label', 'drplus' ),
				'default' => 'hospital-category',
			],
		];
	}

	/**
	 * Save posted values when Permalink settings are submitted.
	 */
	public static function save() {
		if ( ! is_admin() ) {
			return;
		}

		// Only proceed when Permalink form is submitted and nonce valid.
		if ( empty( $_POST ) ) {
			return;
		}

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'update-permalink' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$fields = self::fields();
		foreach ( $fields as $field_id => $field ) {
			if ( isset( $_POST[ $field_id ] ) ) {
				$value = sanitize_title( wp_unslash( $_POST[ $field_id ] ) );
				update_option( $field_id, $value );
			}
		}
	}

	public static function field( $args = [] ) {
		$value = get_option( $args['id'], $args['default'] );
		?>
		<input type="text" name="<?php echo esc_attr( $args['id'] ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>" class="regular-text code ltr" value="<?php echo esc_attr( $value ) ?>">
		<code><?php echo home_url( '/' ) ?></code>
		<?php
	}

	/**
	 * Initialize on the Permalink settings page only.
	 * Registers settings/fields and processes saves when options-permalink.php is loaded.
	 */
	public static function init_page() {
		// Register settings and fields so they're available for rendering on this page.
		self::register();

		// Process any posted values from this page.
		self::save();
	}
}

// Hook into the specific Permalink settings page load to limit execution to that page.
add_action( 'load-options-permalink.php', [Permalinks::class, 'init_page'] );