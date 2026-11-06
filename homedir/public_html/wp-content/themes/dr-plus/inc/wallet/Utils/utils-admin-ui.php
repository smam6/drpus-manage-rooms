<?php
namespace Sheyda\Wallet\Utils;

use SheydaWalletUtils as WalletUtils;

class AdminUI extends WalletUtils {
	public static function switch( $args ) {
		$args = parent::check_default( $args, [
			'name'			=> '',
			'id'			=> '',
			'value'			=> '',
			'active'		=> true,
			'label'			=> '',
			'input_classes'	=> [],
			'disabled'		=> false,
			'wrap_id'		=> '',
		] );

		$input_attrs = [
			'type'	=> 'checkbox',
			'name'	=> $args['name'],
			'class'	=> array_merge( ['sheyda_wallet-switch'], $args['input_classes'] ),
			'value'	=> $args['value'],
		];
		if( !empty( $args['id'] ) ) {
			$input_attrs['id'] = $args['id'];
		}
		if( $args['active'] ) {
			$input_attrs['checked'] = 'checked';
		}
		if( $args['disabled'] ) {
			$input_attrs['disabled'] = 'disabled';
		}

		$wrap_attrs = [
			'class'	=> ['sheyda_wallet-switch-wrap'],
		];
		if( $args['wrap_id'] ) {
			$wrap_attrs['id'] = $args['wrap_id'];
		}
		?>
		<label <?php echo parent::get_html_attributes( $wrap_attrs ) ?>>
			<input <?php echo parent::get_html_attributes( $input_attrs ) ?>>
			<div class="sheyda_wallet-switch-slider"></div>
			<div class="sheyda_wallet-switch-label"><?php echo esc_html( $args['label'] ) ?></div>
		</label>
		<?php
	}

	public static function switch_select( array $args ) {
		$args = parent::check_default( $args, [
			'name'		=> '',
			'id'		=> '',
			'active'	=> true,
			'label1'	=> '',
			'label2'	=> '',
			'classes'	=> [],
		] );

		$wrap_attrs = [
			'class'	=> array_merge( ['sheyda_wallet-switch-select-wrap'], $args['classes'] ),
		];
		if( $args['id'] ) {
			$wrap_attrs['id'] = $args['id'];
		}

		$input_attrs = [
			'type'	=> 'radio',
			'name'	=> $args['name'],
			'class'	=> ['sheyda_wallet-switch-select-input'],
		];
		$input1_attrs = array_merge( $input_attrs, [
			'value'	=> 'on',
		] );
		$input2_attrs = array_merge( $input_attrs, [
			'value'	=> 'off',
		] );
		if( $args['active'] ) {
			$input1_attrs['checked'] = 'checked';
		} else {
			$input2_attrs['checked'] = 'checked';
		}
		?>
		<div <?php echo parent::get_html_attributes( $wrap_attrs ) ?>>
			<label class="sheyda_wallet-switch-select-label">
				<input <?php echo parent::get_html_attributes( $input1_attrs ) ?>>
				<span class="sheyda_wallet-switch-select-text"><?php echo esc_html( $args['label1'] ) ?></span>
			</label>
			<label class="sheyda_wallet-switch-select-label">
				<input <?php echo parent::get_html_attributes( $input2_attrs ) ?>>
				<span class="sheyda_wallet-switch-select-text"><?php echo esc_html( $args['label2'] ) ?></span>
			</label>
		</div>
		<?php
	}

	public static function alert( array $args ) {
		$args = parent::check_default( $args, [
			'text'		=> '',
			'type'		=> 'notice',
			'icon'		=> '',
			'classes'	=> [],
		] );
		
		$classes = [
			'sheyda_wallet-alert',
			"sheyda_wallet-alert-{$args['type']}",
		];
		$classes = array_merge( $classes, $args['classes'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>">
			<?php if( !empty( $args['icon'] ) ) { ?>
				<i class="sheyda_wallet-alert-icon <?php echo esc_attr( $args['icon'] ) ?>"></i>
			<?php } ?>
			<span class="sheyda_wallet-alert-text"><?php echo $args['text'] ?></span>
		</div>
		<?php
	}

	/**
	 * Retrieve input with label html
	 *
	 * @param array $args [
	 * 		@param string label
	 * 		@param string value
	 * 		@param string type
	 * 		@param string id
	 * 		@param string name
	 * 		@param array input_classes
	 * 		@param array label_classes
	 * 		@param bool|array alt_field bool for disabled. [
	 * 			@param string id
	 * 			@param string name
	 * 			@param string value
	 * 		]
	 * ]
	 * @return string html of input and label
	 */
	public static function input_with_label( array $args ) {
		$args = parent::check_default( $args, [
			'label'			=> '',
			'value'			=> '',
			'type'			=> '',
			'id'			=> '',
			'name'			=> '',
			'input_classes'	=> [],
			'label_classes'	=> [],
			'description'	=> '',
			'textarea'		=> false,
			'alt_field'		=> false,
		], ['alt_field'] );

		$alt_field = $args['alt_field'];
		if( !empty( $alt_field ) ) {
			$alt_field = parent::check_default( $alt_field, [
				'id'	=> '',
				'name'	=> '',
				'value'	=> '',
				// Other args will directly add as html attribute
			] );
		}

		$label_attrs = [];
		if( !empty( $args['id'] ) ) {
			$label_attrs['for'] = $args['id'];
			$label_attrs['id'] = $args['id'] . "_label";
		}
		$label_attrs['class'] = array_merge( ['sheyda_wallet_form_group-label'], $args['label_classes'] );

		$is_textarea = $args['textarea'];
		if( $is_textarea ) {
			$value = $args['value'];
			unset( $args['value'] );
			unset( $args['type'] );
		}
		
		$args['class'] = array_merge( ['sheyda_wallet_form_group-input'], $args['input_classes'] );
		$label_text = $args['label'];
		$description = WalletUtils::convert_chars( $args['description'] );
		$args = parent::remove_empty_indexes( $args );
		$args = parent::unset( $args, ['input_classes', 'label_classes', 'label', 'alt_field', 'textarea', 'description'] );
		$args['placeholder'] = $args['placeholder'] ?? "&nbsp;";

		?>
		<div class="sheyda_wallet_form_fieldset">
			<div class="sheyda_wallet_form_group">
				<?php if( $is_textarea ) { ?>
					<textarea <?php echo parent::get_html_attributes( $args ) ?>><?php echo $value ?></textarea>
				<?php } else { ?>
					<input <?php echo parent::get_html_attributes( $args ) ?>>
				<?php } ?>
				<?php if( !empty( $alt_field ) ) { ?>
					<input type="hidden" <?php echo parent::get_html_attributes( $alt_field ) ?>>
				<?php } ?>
				<label <?php echo parent::get_html_attributes( $label_attrs ) ?>>
					<?php echo $label_text ?>
					<?php if( !empty( $args['required'] ) ) { ?>
						<span class="sheyda_wallet_form_group-req">*</span>
					<?php } ?>
				</label>
			</div>

			<?php if( !empty( $description ) ) { ?>
				<p class="description"><?php echo $description ?></p>
			<?php } ?>
			
			<div class="sheyda_wallet_form_field_error">
				<i class="sheyda_wallet-icon-error"></i>
				<div class="sheyda_wallet_form_field_error-text"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieve input with label html
	 *
	 * @param array $args [
	 * 		@param string label
	 * 		@param string value
	 * 		@param string type
	 * 		@param string id
	 * 		@param string name
	 * 		@param array input_classes
	 * 		@param array label_classes
	 * @return string html of input and label
	 * ]
	 */
	public static function select_with_label( array $args ) {
		$args = parent::check_default( $args, [
			'label'				=> '',
			'value'				=> [],
			'options'			=> [],
			'id'				=> '',
			'name'				=> '',
			'select_classes'	=> [],
			'label_classes'		=> [],
		], ['value'] );

		if( !is_array( $args['value'] ) ) $args['value'] = [$args['value']];

		$label_attrs = [];
		if( !empty( $args['id'] ) ) {
			$label_attrs['for'] = $args['id'];
			$label_attrs['id'] = $args['id'] . "_label";
		}
		$label_attrs['class'] = array_merge( ['sheyda_wallet_form_group-label', 'sheyda_wallet_form_group-select-label'], $args['label_classes'] );
		
		$args['class'] = array_merge( ['sheyda_wallet_form_group-select'], $args['select_classes'] );
		$label_text = $args['label'];
		$options = $args['options'];
		$values = $args['value'];
		$args = parent::remove_empty_indexes( $args );
		$args = parent::unset( $args, ['select_classes', 'label_classes', 'label', 'options', 'value'] );
		?>
		<div class="sheyda_wallet_form_fieldset">
			<div class="sheyda_wallet_form_group">
				<select <?php echo parent::get_html_attributes( $args ) ?>>
					<?php foreach( $options as $op_value => $op_label ) { ?>
						<option value="<?php echo $op_value ?>" <?php selected( in_array( $op_value, $values ), true ) ?>><?php echo $op_label ?></option>
					<?php } ?>
				</select>
				<?php if( !empty( $label_text ) ) { ?>
					<label <?php echo parent::get_html_attributes( $label_attrs ) ?>>
						<?php echo $label_text ?>
						<?php if( !empty( $args['required'] ) ) { ?>
							<span class="sheyda_wallet_form_group-req">*</span>
						<?php } ?>
					</label>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Show loading
	 *
	 * @param boolean $svg return svg file content
	 * @param boolean $echo
	 * @return string
	 */
	public static function loading( bool $svg = false, bool $echo = true ) : string {
		$file = "assets/images/loading.svg";
		if( $svg ) {
			$html = file_get_contents( SHEYDA_WALLET_DIR . $file );
		} else {
			$html = '<img src="' . SHEYDA_WALLET_URI . $file . '" alt="">';
		}
		if( $echo ) echo $html;

		return $html;
	}
}