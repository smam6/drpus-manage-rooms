<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class AdminUI extends Utils {
	public static function modal( array $args ) {
		$args = parent::check_default( $args, [
			'id'				=> '',
			'title'				=> '',
			'submit_btn_text'	=> esc_html__( 'Apply', 'drplus' ),
			'body'				=> '',
			'classes'			=> [],
		] );

		$classes = array_merge( ['drplus-modal'], $args['classes'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>">
			<div class="drplus-modal-head">
				<span class="drplus-modal-title"><?php echo esc_html( $args['title'] ) ?></span>
				<div class="drplus-modal-close"><i class="dashicons dashicons-no-alt"></i></div>
			</div>

			<div class="drplus-modal-content"><?php echo $args['body'] ?></div>

			<div class="drplus-modal-footer">
				<button class="button button-primary drplus-modal-close drplus-modal-submit-btn"><?php echo esc_html( $args['submit_btn_text'] ) ?></button>
			</div>
		</div>
		<div class="drplus-modal-overlay"></div>
		<?php
	}

	public static function icon_picker( array $args ) {
		$args = parent::check_default( $args, [
			'id'		=> '',
			'name'		=> '',
			'icon'		=> '',
			'modal_id'	=> '',
		] );
		if( !$args['id'] ) {
			$args['id'] = $args['name'];
		}
		?>
		<div class="icon-picker-form" data-modal="<?php echo esc_attr( $args['modal_id'] ) ?>">
			<i class="<?php echo esc_attr( $args['icon'] ) ?> icon-picker-select icon-picker-select-icon"></i>
			<input type="text" name="<?php echo esc_attr( $args['name'] ) ?>" id="<?php echo esc_attr( $args['id'] ) ?>" class="ltr icon-picker-field" value="<?php echo esc_attr( $args['icon'] ) ?>">
			<div class="button icon-picker-select"><?php esc_html_e( 'Select', 'drplus' ) ?></div>
		</div>
		<?php
	}

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
			'class'	=> array_merge( ['drplus-switch'], $args['input_classes'] ),
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
			'class'	=> ['drplus-switch-wrap'],
		];
		if( $args['wrap_id'] ) {
			$wrap_attrs['id'] = $args['wrap_id'];
		}
		?>
		<label <?php echo parent::get_html_attributes( $wrap_attrs ) ?>>
			<input <?php echo parent::get_html_attributes( $input_attrs ) ?>>
			<div class="drplus-switch-slider"></div>
			<div class="drplus-switch-label"><?php echo esc_html( $args['label'] ) ?></div>
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
			'class'	=> array_merge( ['drplus-switch-select-wrap'], $args['classes'] ),
		];
		if( $args['id'] ) {
			$wrap_attrs['id'] = $args['id'];
		}

		$input_attrs = [
			'type'	=> 'radio',
			'name'	=> $args['name'],
			'class'	=> ['drplus-switch-select-input'],
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
			<label class="drplus-switch-select-label">
				<input <?php echo parent::get_html_attributes( $input1_attrs ) ?>>
				<span class="drplus-switch-select-text"><?php echo esc_html( $args['label1'] ) ?></span>
			</label>
			<label class="drplus-switch-select-label">
				<input <?php echo parent::get_html_attributes( $input2_attrs ) ?>>
				<span class="drplus-switch-select-text"><?php echo esc_html( $args['label2'] ) ?></span>
			</label>
		</div>
		<?php
	}

	public static function attachment( $args ) {
		$args = parent::check_default( $args, [
			'name'	=> '',
			'file'	=> 0,
			'icon'	=> 'dashicons dashicons-media-default',
			'type'	=> '',
		] );

		$file = get_attached_file( $args['file'] );
		$is_image = !empty( $file ) ? file_is_valid_image( $file ) : false;
		?>
		<div class="drplus-attachment-wrap" data-type="<?php echo esc_html( $args['type'] ) ?>">
			<input type="hidden" name="<?php echo esc_attr( $args['name'] ) ?>" class="drplus-attachment-input" value="<?php echo esc_attr( $args['file'] ) ?>">
			<div class="drplus-attachment-icon">
				<?php
				if( $is_image ) {
					echo wp_get_attachment_image( $args['file'], [80, 80] );
				} else {
					?>
					<i class="<?php echo $args['icon'] ?>"></i>
				<?php } ?>
			</div>
			<div class="drplus-attachment-details">
				<strong class="drplus-attachment-name"><?php echo esc_html( !empty( $file ) ? wp_basename( $file ) : __( 'Select file', 'drplus' ) ) ?></strong>
				<div class="drplus-attachment-size"<?php parent::hide( true, !empty( $file ) ) ?>><?php echo esc_html( !empty( $file ) ? size_format( filesize( $file ) ) : '' ) ?></div>
			</div>
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
			'drplus-alert',
			"drplus-alert-{$args['type']}",
		];
		$classes = array_merge( $classes, $args['classes'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>">
			<?php if( !empty( $args['icon'] ) ) { ?>
				<i class="drplus-alert-icon <?php echo esc_attr( $args['icon'] ) ?>"></i>
			<?php } ?>
			<span class="drplus-alert-text"><?php echo $args['text'] ?></span>
		</div>
		<?php
	}

	public static function repeater_btn( $args ) {
		$args = parent::check_default( $args, [
			'id'	=> '',
			'icon'	=> 'drplus_metabox_repeater-add-icon',
			'text'	=> '',
		] );
		?>
		<div class="drplus_metabox_repeater-add" id="<?php echo esc_attr( $args['id'] ) ?>">
			<i class="drplus_metabox_repeater-add-icon <?php echo esc_attr( $args['icon'] ) ?>"></i>
			<?php if( $args['text'] ) { ?>
				<span class="drplus_metabox_repeater-add-text"><?php echo esc_html( $args['text'] ) ?></span>
			<?php } ?>
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
		$label_attrs['class'] = array_merge( ['drplus_form_group-label'], $args['label_classes'] );

		$is_textarea = $args['textarea'];
		if( $is_textarea ) {
			$value = $args['value'];
			unset( $args['value'] );
			unset( $args['type'] );
		}
		
		$args['class'] = array_merge( ['drplus_form_group-input'], $args['input_classes'] );
		$label_text = $args['label'];
		$description = Utils::convert_chars( $args['description'] );
		$args = parent::remove_empty_indexes( $args );
		$args = parent::unset( $args, ['input_classes', 'label_classes', 'label', 'alt_field', 'textarea', 'description'] );
		$args['placeholder'] = $args['placeholder'] ?? "&nbsp;";

		?>
		<div class="drplus_form_fieldset">
			<div class="drplus_form_group">
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
						<span class="drplus_form_group-req">*</span>
					<?php } ?>
				</label>
			</div>

			<?php if( !empty( $description ) ) { ?>
				<p class="description"><?php echo $description ?></p>
			<?php } ?>
			
			<div class="drplus_form_field_error">
				<i class="drplus-icon-error"></i>
				<div class="drplus_form_field_error-text"></div>
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
		$label_attrs['class'] = array_merge( ['drplus_form_group-label', 'drplus_form_group-select-label'], $args['label_classes'] );
		
		$args['class'] = array_merge( ['drplus_form_group-select'], $args['select_classes'] );
		$label_text = $args['label'];
		$options = $args['options'];
		$values = $args['value'];
		$args = parent::remove_empty_indexes( $args );
		$args = parent::unset( $args, ['select_classes', 'label_classes', 'label', 'options', 'value'] );
		?>
		<div class="drplus_form_fieldset">
			<div class="drplus_form_group">
				<select <?php echo parent::get_html_attributes( $args ) ?>>
					<?php foreach( $options as $op_value => $op_label ) { ?>
						<option value="<?php echo $op_value ?>" <?php selected( in_array( $op_value, $values ), true ) ?>><?php echo $op_label ?></option>
					<?php } ?>
				</select>
				<?php if( !empty( $label_text ) ) { ?>
					<label <?php echo parent::get_html_attributes( $label_attrs ) ?>>
						<?php echo $label_text ?>
						<?php if( !empty( $args['required'] ) ) { ?>
							<span class="drplus_form_group-req">*</span>
						<?php } ?>
					</label>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	private static function dropzone_script() {
		?>
		<script type="text/html" id="tmpl-drplus-dropzone-current-value">
			<div class="drplus-dropzone-current-value">
				<a href="{{{data.img}}}" class="drplus-dropzone-current-value-img" target="_blank" download><img src="{{{data.img}}}" alt=""></a>
				<div class="drplus-dropzone-current-value-details">
					<div class="drplus-dropzone-current-value-size">{{{data.size}}}</div>
					<a href="{{{data.img}}}" class="drplus-dropzone-current-value-filename line-clamp line-clamp-1" target="_blank" download>{{{data.filename}}}</a>
				</div>
				<i class="drplus-dropzone-current-value-remove drplus-icon-close-circle"></i>
			</div>
		</script>
		<?php
	}

	public static function dropzone( array $args, bool $only_script = false ) {
		static $current_value_template_rendered = false;
		if( !$current_value_template_rendered ) {
			$current_value_template_rendered = true;
			self::dropzone_script();
		}
		if( !$only_script ) {
			$args = parent::check_default( $args, [
				'title'				=> '',
				'description'		=> '',
				'upload_icon'		=> 'drplus-icon-document-text-bold',
				'upload_text'		=> esc_html__( 'Select file', 'drplus' ),
				'max_upload_size'	=> 0,
				'input_name'		=> '',
				'input_id'			=> '',
				'value'				=> 0,
				'required'			=> 'required',
			], ['upload_icon'] );
			if( !$args['max_upload_size'] ) {
				$args['max_upload_size'] = parent::get_max_upload_size();
			}

			$input_attrs = [
				'type'		=> 'hidden',
				'name'		=> $args['input_name'],
				'id'		=> $args['input_id'],
				'required'	=> $args['required'],
				'value'		=> $args['value'],
			];
			$input_attrs = array_filter( $input_attrs );

			static $dropzone_index = 1;
			?>
			<div class="drplus-dropzone-wrap drplus-dropzone-wp" data-index="<?php echo $dropzone_index ?>">
				<input <?php echo parent::get_html_attributes( $input_attrs ) ?>>
				<div class="drplus-dropzone-head">
					<div class="drplus-dropzone-title"><?php echo esc_html( $args['title'] ) ?><?php echo $args['required'] ? ' <span class="required">*</span>' : '' ?></div>
					<?php if( !empty( $args['description'] ) ) { ?>
						<div class="drplus-dropzone-description"><?php echo wpautop( $args['description'] ) ?></div>
					<?php } ?>
				</div>
				<div class="drplus-dropzone" data-index="<?php echo $dropzone_index ?>" data-max="<?php echo esc_attr( $args['max_upload_size'] ); ?>">
					<?php echo Sanitizers::icon( $args['upload_icon'], 'drplus-dropzone-upload-icon' ) ?>
					<div class="drplus-dropzone-upload-text"><?php echo esc_html( $args['upload_text'] ) ?></div>
					<div class="drplus-dropzone-uploading"><img src="<?php echo DRPLUS_URI . "assets/images/loading-1.svg" ?>" alt=""></div>
				</div>
				<div class="drplus-dropzone-info-row">
					<div class="drplus-dropzone-supports"><?php esc_html_e( 'File format: png, jpg', 'drplus' ) ?></div>
					<div class="drplus-dropzone-max-size"><?php printf( __( "File size: Maximum %s", 'drplus' ), parent::convert_bytes_to_mb( $args['max_upload_size'] ) ) ?></div>
				</div>

				<?php if( $args['value'] ) { ?>
					<div class="drplus-dropzone-current-value">
						<a href="<?php echo wp_get_attachment_url( $args['value'] ) ?>" class="drplus-dropzone-current-value-img" target="_blank" download>
							<?php echo wp_get_attachment_image( $args['value'] ) ?>
						</a>
						<div class="drplus-dropzone-current-value-details">
							<div class="drplus-dropzone-current-value-size"><?php echo size_format( filesize( get_attached_file( $args['value'] ) ) ) ?></div>
							<a href="<?php echo wp_get_attachment_url( $args['value'] ) ?>" class="drplus-dropzone-current-value-filename line-clamp line-clamp-1" target="_blank" download><?php echo wp_basename( get_attached_file( $args['value'] ) ) ?></a>
						</div>
						<i class="drplus-dropzone-current-value-remove drplus-icon-close-circle"></i>
					</div>
				<?php } ?>
			</div>
			<?php
			$dropzone_index++;
		}
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
			$html = file_get_contents( DRPLUS_DIR . $file );
		} else {
			$html = '<img src="' . DRPLUS_URI . $file . '" alt="">';
		}
		if( $echo ) echo $html;

		return $html;
	}
}