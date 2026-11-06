<?php
namespace DrPlus\Utils;

use DrPlus\Components\Button;
use DrPlus\Utils;
use DrPlus\Utils\Options;

class UI extends Utils {
	/**
	 * Create stars
	 *
	 * @param integer $active
	 * @param integer $count
	 * @param boolean $radio
	 * @param string $radio_name
	 * @param boolean $echo
	 * @return string
	 */
	public static function stars( int $active = 0, int $count = 5, bool $radio = false, string $radio_name = 'drplus_star', bool $echo = true ) : string {
		$stars_label = [
			1 => esc_html__( "Bad", 'drplus' ),
			2 => esc_html__( "Average", 'drplus' ),
			3 => esc_html__( "Good", 'drplus' ),
			4 => esc_html__( "Very good", 'drplus' ),
			5 => esc_html__( "Excellent", 'drplus' ),
		];
		$html = '<div class="drplus_stars' . ($radio ? " drplus_stars-has-radio" : '') . '">';
		for( $index = 1; $index <= $count; $index++ ) {
			if( $radio ) {
				$html .= '<input type="radio" name="' . $radio_name . '" class="drplus_star-input" value="' . $index . '">';
			}
			$title = $radio ? ' title="' . $stars_label[$index] . '"' : '';
			$html .= '<i class="drplus_star drplus-icon-star' . ( ( $active !== 0 && $index <= $active ) || $radio ? "-fill drplus_star_fill" : " drplus_star_empty") . '"' . $title  . '></i>';
		}
		$html .= "</div>";

		if( $echo ) echo $html;

		return $html;
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

	public static function button_loading( bool $echo = true ) {
		$html = '';
		if( $html === '' ) {
			ob_start();
			get_template_part( "templates/components/button/loading" );
			$html = ob_get_clean();
		}
		if( $echo ) echo $html;

		return $html;
	}

	public static function get_menu_icon( $item_id, $return = 'html', $menu_style = 'style-1' ) {
		$icon = get_post_meta( $item_id, '_drplus_icon', true );
		$filename = Utils::convert_chars( $icon, true, 'strtolower' );
		if( file_exists( DRPLUS_DIR . "assets/icons/{$filename}.svg" ) ) {
			if( strpos( $icon, "drplus-icon-" ) !== 0 ) {
				$icon = "drplus-icon-{$icon}";
			}
		}
		if( $return != 'html' ) return $icon;
		$wrap_classes = ['drplus-simple-icon-wrap'];
		if( $menu_style == 'style-1' ) $wrap_classes[] = 'icon-has-bg';
		$wrap_classes = parent::prepare_html_classes( $wrap_classes );

		// Get HTML
		$html = '<span class="' . $wrap_classes . '">';
		$icon_packs = Utils::get_icon_packs();
		$find_icon = false;
		foreach( $icon_packs as $pack ) {
			foreach( $pack['icons'] as $icon_name_or_url ) {
				if( $pack['mode'] == 'font-icon' ) {
					if( $icon != "{$pack['prefix']}{$icon_name_or_url}" ) continue;
				} else {
					if( !substr( $icon_name_or_url, -strlen( "{$icon}.svg" ) ) != $icon ) continue;
					$html .= '<img src="' . $icon . '" alt="" class="menu-item-icon">';
					$find_icon = true;
					break;
				}
			}
		}
		if( !$find_icon && $icon ) {
			$html .= '<i class="menu-item-icon ' . $icon . '" aria-hidden="true"></i>';
		}
		$html .= '</span>';
		return $html;
	}

	public static function get_menu_subtitle( $item_id ) {
		$subtitle = get_post_meta( $item_id, '_drplus_subtitle', true );
		if( empty( $subtitle ) ) return "";
		return '<span class="menu-item-subtitle">' . esc_html( $subtitle ) . '</span>';
	}

	public static function product_wishlist( $product_id, array $args = [] ) {
		$user_logged_in = is_user_logged_in();
		$icon = 'heart';
		if( $user_logged_in ) {
			$wishlist_products = User::get_wishlist_products();
			if( in_array( $product_id, $wishlist_products ) ) {
				$icon = 'heart-bold';
			}
		}

		$args = parent::check_default( $args, [
			'additional_classes'	=> [],
			'label'					=> '',
			'added_text'			=> esc_html__( "Added to wishlist.", 'drplus' ),
			'removed_text'			=> esc_html__( "Removed from wishlist.", 'drplus' ),
			'login_text'			=> esc_html__( "Login to your account", 'drplus' ),
		] );
		$classes = array_merge( ['wishlist-button'], $args['additional_classes'] );

		if( !$user_logged_in ) {
			$classes[] = 'drplus-popover-wrap';
		}
		?>
		<div class="<?php echo parent::prepare_html_classes( $classes ) ?>" data-product-id="<?php echo esc_attr( absint( $product_id ) ) ?>" data-nonce="<?php echo wp_create_nonce( "wishlist-toggle-{$product_id}" ) ?>">
			<i class="drplus-icon-<?php echo $icon ?>" aria-hidden="true"></i>
			<?php
			if( $args['label'] ) {
				echo '<span class="wishlist-label">' . $args['label'] . '</span>';
			}
			?>
			<?php if( $user_logged_in ) { ?>
				<div class="wishlist-popover wishlist-popover-added">
					<?php echo $args['added_text'] ?>
				</div>

				<div class="wishlist-popover wishlist-popover-removed">
					<?php echo $args['removed_text'] ?>
				</div>

				<img src="<?php echo DRPLUS_URI ?>assets/images/wishlist-loading.svg" alt="" class="wishlist-loading">
			<?php } else { ?>
				<div class="drplus-popover drplus-popover-center"><?php echo esc_html( $args['login_text'] ) ?></div>
			<?php } ?>
		</div>
		<?php
	}

	public static function filter_radio( string $text, string $query_param, string $query_param_value, array $args = [] ) {
		$is_active = isset( $_GET[$query_param] );
		$url = $is_active ? remove_query_arg( $query_param ) : add_query_arg( $query_param, $query_param_value );

		$args = parent::check_default( $args, [
			'radio-align'	=> 'end', // end | start
		] );

		$classes = ['drplus_filter', 'drplus_filter_additional_option'];
		if( $is_active ) {
			$classes[] = 'active';
		}
		if( $args['radio-align'] == 'end' ) {
			$classes[] = 'radio-end';
		} else {
			$classes[] = 'radio-start';
		}
		?>
		<a href="<?php echo esc_url( $url ) ?>" class="<?php echo parent::prepare_html_classes( $classes ) ?>"><?php echo $text ?></a>
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
			<div class="drplus-dropzone-wrap" data-index="<?php echo $dropzone_index ?>">
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

	public static function input_with_label( array $args ) {
		$args = parent::check_default( $args, [
			'label'	=> '',
			'value'	=> '',
			'type'	=> 'text',
			'id'	=> '',
			'name'	=> '',

			'group_classes'	=> [],
			'wrap_classes'	=> [],
			'label_classes'	=> [],
			'input_classes'	=> [],

			'wrap_white'	=> true,

			'textarea'		=> false,
			'description'	=> "",
			'alt_field'		=> false,
		], ['alt_field'] );

		$group_classes = array_merge( ['input-group'], $args['group_classes'] );
		$wrap_classes = array_merge( ['input-wrap'], $args['wrap_classes'] );
		if( $args['wrap_white'] ) {
			$wrap_classes[] = 'input-wrap-white';
		}

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
		$label_attrs['class'] = array_merge( ['input-label'], $args['label_classes'] );

		$is_textarea = $args['textarea'];
		if( $is_textarea ) {
			$value = $args['value'];
			unset( $args['value'] );
			unset( $args['type'] );
		}

		$args['class'] = array_merge( ['input-field'], $args['input_classes'] );
		$label_text = $args['label'];
		$description = Utils::convert_chars( $args['description'] );
		$args = parent::remove_empty_indexes( $args );
		$args = parent::unset( $args, ['group_classes', 'wrap_classes', 'input_classes', 'label_classes', 'label', 'alt_field', 'textarea', 'wrap_white'] );
		$args['placeholder'] = $args['placeholder'] ?? "";
		?>
		<div class="<?php echo parent::prepare_html_classes( $group_classes ) ?>">
			<label <?php echo parent::get_html_attributes( $label_attrs ) ?>><?php echo esc_html( $label_text ) ?><?php echo !empty( $args['required'] ) ? ' <span class="input-required">*</span>' : '' ?></label>
			<div class="<?php echo parent::prepare_html_classes( $wrap_classes ) ?>">
				<?php if( $is_textarea ) { ?>
					<textarea <?php echo parent::get_html_attributes( $args ) ?>><?php echo $value ?></textarea>
				<?php } else { ?>
					<input <?php echo parent::get_html_attributes( $args ) ?>>
				<?php } ?>
				<?php if( !empty( $alt_field ) ) { ?>
					<input type="hidden" <?php echo parent::get_html_attributes( $alt_field ) ?>>
				<?php } ?>
			</div>

			<?php if( !empty( $description ) ) { ?>
				<p class="drplus-field-description"><?php echo $description ?></p>
			<?php } ?>

			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
		</div>
		<?php
	}

	public static function select_with_label( array $args ) {
		$args = parent::check_default( $args, [
			'label'		=> '',
			'value'		=> [],
			'options'	=> [],
			'id'		=> '',
			'name'		=> '',

			'group_classes'		=> [],
			'wrap_classes'		=> [],
			'select_classes'	=> [],
			'label_classes'		=> [],

			'wrap_white'	=> true,
		], ['value'] );

		if( !is_array( $args['value'] ) ) $args['value'] = [$args['value']];

		$group_classes = array_merge( ['input-group'], $args['group_classes'] );
		$wrap_classes = array_merge( ['input-wrap'], $args['wrap_classes'] );
		if( $args['wrap_white'] ) {
			$wrap_classes[] = 'input-wrap-white';
		}

		$label_attrs = [];
		if( !empty( $args['id'] ) ) {
			$label_attrs['for'] = $args['id'];
			$label_attrs['id'] = $args['id'] . "_label";
		}
		$label_attrs['class'] = array_merge( ['input-label', 'select-label'], $args['label_classes'] );

		$args['class'] = array_merge( ['drplus_form_group-select'], $args['select_classes'] );
		$label_text = $args['label'];
		$options = $args['options'];
		$values = $args['value'];
		$args = parent::remove_empty_indexes( $args );
		$args = parent::unset( $args, ['group_classes', 'wrap_classes', 'select_classes', 'label_classes', 'label', 'options', 'wrap_white', 'value'] );
		?>
		<div class="<?php echo parent::prepare_html_classes( $group_classes ) ?>">
			<label <?php echo parent::get_html_attributes( $label_attrs ) ?>><?php echo esc_html( $label_text ) ?><?php echo !empty( $args['required'] ) ? ' <span class="input-required">*</span>' : '' ?></label>
			<div class="<?php echo parent::prepare_html_classes( $wrap_classes ) ?>">
				<select <?php echo parent::get_html_attributes( $args ) ?>>
					<?php foreach( $options as $op_value => $op_label ) { ?>
						<option value="<?php echo $op_value ?>" <?php selected( in_array( $op_value, $values ), true ) ?>><?php echo $op_label ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="input-error">
				<i class="drplus-icon-error"></i>
				<span class="input-error-text"></span>
			</div>
		</div>
		<?php
	}

	private static function repeater_item_args_replace( $args, $row_index ) {
		foreach( $args as $index => $value ) {
			if( is_array( $value ) ) {
				$args[$index] = self::repeater_item_args_replace( $value, $row_index );
			} else if( is_string( $value ) ) {
				$args[$index] = str_replace( '%index%', $row_index, $value );
			}
		}
		return $args;
	}

	private static function repeater_input_html( $field_attrs, $field_value ) {
		if( $field_attrs['type'] == 'textarea' ) {
			unset( $field_attrs['type'] );
			?>
			<textarea <?php echo parent::get_html_attributes( $field_attrs ) ?>><?php echo esc_textarea( $field_value ) ?></textarea>
		<?php
		} else if( $field_attrs['type'] == 'select' ) {
			$field_value = !is_array( $field_value ) ? [$field_value] : $field_value;
			$options = !empty( $field_attrs['options'] ) ? $field_attrs['options'] : [];

			$field_attrs = parent::unset( $field_attrs, ['type', 'options'] );
			?>
			<select <?php echo parent::get_html_attributes( $field_attrs ) ?>>
				<?php foreach( $options as $option_value => $option_label ) { ?>
					<option value="<?php echo esc_attr( $option_value ) ?>" <?php selected( true, in_array( $option_value, $field_value ) ) ?>><?php echo esc_html( $option_label ) ?></option>
				<?php } ?>
			</select>
		<?php
		} else if( $field_attrs['type'] == 'dropzone' ) {
			unset( $field_attrs['type'] );
			$field_attrs['value'] = $field_value;
			self::dropzone( $field_attrs );
		} else {
			$field_attrs['value'] = $field_value;
			?>
			<input <?php echo parent::get_html_attributes( $field_attrs ) ?>>
			<?php
		}
	}

	private static function repeater_rows_html( $args ) {
		foreach( $args['rows'] as $index => $rows ) {
			$slot_attrs = [
				'class'	=> 'repeater-slot',
			];
			$slot_attrs = array_merge( $slot_attrs, $args['slot_attrs'] );
			$slot_attrs = self::repeater_item_args_replace( $slot_attrs, $index );

			$item_attrs = [
				'class'	=> 'repeater-item',
			];
			$item_attrs = array_merge( $item_attrs, $args['item_attrs'] );
			$item_attrs = self::repeater_item_args_replace( $item_attrs, $index );
			?>
			<div <?php echo parent::get_html_attributes( $slot_attrs ) ?>>
				<div <?php echo parent::get_html_attributes( $item_attrs, ['id_field'] ) ?>>
					<div class="repeater-icons">
						<i class="drplus-icon-menu repeater-move" data-swapy-handle></i>
						<i class="drplus-icon-cross repeater-remove"></i>
					</div>
					<?php
					if( !empty( $args['item_attrs']['id_field'] ) ) {
						$id_field = $args['item_attrs']['id_field'];
						$id_field['type'] = 'hidden';
						$id_field = parent::check_default( $id_field, [
							'type'	=> 'text',
							'value'	=> '',
						], ['value'] );
						$id_value = $rows['id'];
						if( isset( $id_field['class'] ) && is_string( $id_field['class'] ) ) {
							$id_field['class'] = explode( " ", $id_field['class'] );
						}
						$id_field['class'][] = 'repeater-field';
						$id_field['class'][] = 'repeater-id-field';
						$id_field = self::repeater_item_args_replace( $id_field, $index );
						self::repeater_input_html( $id_field, $id_value );
						unset( $rows['id'] );
					}
					foreach( $rows as $row ) {
						$row = parent::check_default( $row, [
							'type'		=> 'primary',
							'field'		=> [],
						] );
						$row_type = $row['type'];
						if( $row_type == 'double' ) {
							$field2_attrs = $row['field2'];
						}

						$row_attrs = [
							'class'	=> ['repeater-row', "repeater-row-{$row_type}"],
						];
						$field_attrs = $row['field'];
						if( $row_type == 'double' ) {
							$field2_attrs = $row['field2'];
						}

						$row = parent::unset( $row, ['type', 'field', 'field2'] );
						$row_attrs = array_merge( $row_attrs, $row );
						$row_attrs = self::repeater_item_args_replace( $row_attrs, $index );

						$field_attrs = parent::check_default( $field_attrs, [
							'type'	=> 'text',
							'value'	=> '',
						], ['value'] );
						$field_value = $field_attrs['value'];
						unset( $field_attrs['value'] );
						if( isset( $field_attrs['class'] ) && is_string( $field_attrs['class'] ) ) {
							$field_attrs['class'] = explode( " ", $field_attrs['class'] );
						}
						$field_attrs['class'][] = 'repeater-field';
						$field_attrs = self::repeater_item_args_replace( $field_attrs, $index );

						if( $row_type == 'double' ) {
							$field2_attrs = parent::check_default( $field2_attrs, [
								'type'	=> 'text',
								'value'	=> '',
							], ['value'] );
							$field2_value = $field2_attrs['value'];
							unset( $field2_attrs['value'] );
							if( isset( $field2_attrs['class'] ) && is_string( $field2_attrs['class'] ) ) {
								$field2_attrs['class'] = explode( " ", $field2_attrs['class'] );
							}
							$field2_attrs['class'][] = 'repeater-field';
							$field2_attrs['class'][] = 'repeater-field2';
							$field2_attrs = self::repeater_item_args_replace( $field2_attrs, $index );
						}
						?>
						<div <?php echo parent::get_html_attributes( $row_attrs ) ?>>
							<?php
							self::repeater_input_html( $field_attrs, $field_value );
							if( $row_type == 'double' ) {
								self::repeater_input_html( $field2_attrs, $field2_value );
							}
							?>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
		}
	}

	public static function repeater( array $args ) {
		// use %index% in values to replace with index. This will works for item attributes(also slot)
		$args = parent::check_default( $args, [
			'template_id'	=> '',
			'type'			=> 'auto', // auto | manual
			'style'			=> 'flex', // flex | grid
			'flow'			=> 'column', // column | row
			'wrap_attrs'	=> [/* HTML attributes */],
			'slot_attrs'	=> [
				'data-swapy-slot'	=> '',
				// Other HTML attributes
			],
			'item_attrs'	=> [
				'data-swapy-item'	=> '',
				'id_field'			=> [],
				// Other HTML attributes
			],
			'rows'			=> [], // The key will used for index and value is for inputs
		] );

		/**
		 * Rows: [
		 * 	type: primary | secondary | double | dropzone | full
		 * 	field: [
		 * 		type: Input types | textarea | select | dropzone
		 * 		[other HTML attributes]
		 * 		[for dropzone the other args will directly pass to the function]
		 * 	]
		 * 	[other HTML attributes]
		 * ]
		 */
		
		$wrap_attrs = [
			'class'				=> ['repeater', "repeater-{$args['type']}", "repeater-{$args['style']}", "repeater-flow-{$args['flow']}"],
			'data-template_id'	=> $args['template_id'],
		];
		$wrap_attrs = array_merge( $wrap_attrs, $args['wrap_attrs'] );
		?>
		<div <?php echo parent::get_html_attributes( $wrap_attrs ) ?>>
			<?php
			self::repeater_rows_html( $args );
			if( $args['type'] == 'manual' ) {
				Button::view( [
					'type'			=> 'action',
					'text'			=> __( "Add new office", 'drplus' ),
					'icon'			=> 'drplus-icon-add-square',
					'icon_align'	=> 'end',
					'small'			=> true,
					'classes'		=> ['repeater-new-slot-btn'],
					'align'			=> 'end',
					'atts'			=> [
						'type'	=> 'button'
					],
				] );
			}
			?>
		</div>
		<?php
		$template_args = $args;
		$template_args['rows'] = ['{{{data.index}}}' => array_values( $args['rows'] )[0]];
		foreach( $template_args['rows']['{{{data.index}}}'] as $row_index => $row_details ) {
			if( empty( $row_details['field'] ) ) continue;
			$template_args['rows']['{{{data.index}}}'][$row_index]['field'] = parent::unset( $row_details['field'], ['value'] );
			if( !empty( $template_args['rows']['{{{data.index}}}'][$row_index]['field2'] ) ) {
				$template_args['rows']['{{{data.index}}}'][$row_index]['field2'] = parent::unset( $row_details['field2'], ['value'] );
			}
		}
		?>
		<script type="text/html" id="tmpl-<?php echo esc_attr( $args['template_id'] ) ?>">
			<?php self::repeater_rows_html( $template_args ) ?>
		</script>
		<?php
	}

	/**
	 * Create custom dropdown
	 *
	 * @param array $args [
	 * 		id		=> string Custom id
	 * 		classes	=> array Additional classes
	 * 		options	=> array List of options by key for value of the option and value for label
	 * 		current	=> string Selected option
	 * 		empty	=> string Label of the empty option
	 * ]
	 * @param bool $echo Echo the dropdown [Default: true]
	 * @return string
	 */
	public static function dropdown( array $args, bool $echo = true ) : string {
		$args = parent::check_default( $args, [
			'id'		=> '',
			'classes'	=> [],
			'options'	=> [],
			'current'	=> '',
			'empty'		=> '',
			'attrs'		=> [],
		] );
		$wrap_attrs = [
			'class'	=> array_merge( ['dropdown'], $args['classes'] ),
		];
		if( !empty( $args['id'] ) ) {
			$wrap_attrs['id'] = $args['id'];
		}
		if( !empty( $args['attrs'] ) ) {
			$wrap_attrs = array_merge( $wrap_attrs, $args['attrs'] );
		}

		$current = !empty( $args['current'] ) && !empty( $args['options'][$args['current']] ) ? $args['options'][$args['current']] : $args['empty'];

		$html = "<div " . parent::get_html_attributes( $wrap_attrs ) . ">";
			$html .= '<div class="dropdown-current-wrap">';
				$html .= '<div class="dropdown-current">' . esc_html( $current ) . '</div>';
				$html .= '<i class="drplus-icon-bottom dropdown-current-icon"></i>';
			$html .= '</div>';

			$html .= '<ul class="dropdown-items">';
				foreach( $args['options'] as $value => $label ) {
					$html .= '<li class="dropdown-item" data-value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</li>';
				}
			$html .= '</ul>';
		$html .= '</div>';
		
		if( $echo ) echo $html;

		return $html;
	}

	public static function map_popup() {
		get_template_part( "templates/components/template-components-map_popup" );
	}

	/**
	 * Get theme color mode settings.
	 *
	 * @return array
	 */
	public static function get_color_mode_settings() : array {
		static $cache = null;
		if( $cache !== null ) return $cache;

		$options = Options::get_options( [
			'color_mode'					=> 'light',
			'color_mode_auto_behavior'		=> 'system',
			'color_mode_user_switch'		=> true,
		] );
		
		
		$allow_switch = Utils::to_bool( $options['color_mode_user_switch'] );
		$initial = 'light';
		if( $options['color_mode'] === 'dark' ) {
			$initial = 'dark';
		} else if( $options['color_mode'] === 'both' && $options['color_mode_auto_behavior'] === 'prefer_dark' ) {
			$initial = 'dark';
		}

		$cache = [
			'mode'			=> $options['color_mode'],
			'auto'			=> $options['color_mode_auto_behavior'],
			'allow_switch'	=> $allow_switch,
			'initial'		=> $initial,
			'storage_key'	=> 'drplus-color-mode',
		];

		return $cache;
	}
}