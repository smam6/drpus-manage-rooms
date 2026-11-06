<?php

use DrPlus\AdminScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\WC;

class WCAttributeFields {
	public static function add_fields() {
		?>
		<div class="form-field">
			<label for="drplus_attribute_display_type"><?php esc_html_e( 'Attribute display type', 'drplus' ); ?></label>
			<select name="drplus_attribute_display_type" id="drplus_attribute_display_type">
				<?php foreach( WC::attr_display_types() as $key => $label ) { ?>
					<option value="<?php echo esc_attr( $key ) ?>"><?php echo esc_html( $label ) ?></option>
				<?php } ?>
			</select>
			<p class="description"><?php esc_html_e( 'This option specifies how to display this feature in filters', 'drplus' ) ?></p>
		</div>

		<div class="form-field">
			<label for="drplus_attribute_icon"><?php esc_html_e( 'Icon', 'drplus' ); ?></label>
			<?php
			AdminUI::icon_picker( [
				'id'		=> "drplus_attribute_icon",
				'name'		=> "drplus_attribute_icon",
				'icon'		=> 'drplus-icon-brush',
				'modal_id'	=> 'drplus-icon-picker-modal',
			] );
			?>
		</div>
		<?php
	}

	public static function edit_fields() {
		$id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;

		$attr_settings = WC::get_attribute_settings( $id );
		?>
		<tr>
			<th scope="row" valign="top">
				<label for="drplus_attribute_display_type"><?php esc_html_e( 'Attribute display type', 'drplus' ); ?></label>
			</th>

			<td>
				<select name="drplus_attribute_display_type" id="drplus_attribute_display_type">
					<?php foreach( WC::attr_display_types() as $key => $label ) { ?>
						<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $attr_settings['display_type'] ) ?>><?php echo esc_html( $label ) ?></option>
					<?php } ?>
				</select>
				<p class="description"><?php esc_html_e( 'This option specifies how to display this feature in filters', 'drplus' ) ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row" valign="top">
				<label for="drplus_attribute_icon"><?php esc_html_e( 'Icon', 'drplus' ); ?></label>
			</th>

			<td>
				<?php
				AdminUI::icon_picker( [
					'id'		=> "drplus_attribute_icon",
					'name'		=> "drplus_attribute_icon",
					'icon'		=> $attr_settings['icon'],
					'modal_id'	=> 'drplus-icon-picker-modal',
				] );
				?>
			</td>
		</tr>
		<?php
	}

	public static function save( $id ) {
		WC::update_attribute_settings( $id, [
			'display_type'	=> !empty( $_POST['drplus_attribute_display_type'] ) ? Utils::convert_chars( $_POST['drplus_attribute_display_type'] ) : 'select',
			'icon'			=> !empty( $_POST['drplus_attribute_icon'] ) ? Utils::convert_chars( $_POST['drplus_attribute_icon'] ) : '',
		] );
	}

	public static function tax_add_fields( $taxonomy ) {
		$attr_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		$options = WC::get_attribute_settings( $attr_id );
		if( !in_array( $options['display_type'], array_keys( WC::attr_display_types() ) ) ) return;
		if( $options['display_type'] == 'color' ) {
			?>
			<div class="form-field">
				<label for="drplus_color"><?php esc_html_e( 'Color', 'drplus' ) ?></label>
				<input type="color" name="drplus_color" id="drplus_color">
			</div>
			<?php
		} else if( $options['display_type'] == 'image' ) {
			?>
			<div class="form-field">
				<?php
				AdminUI::attachment( [
					'name'	=> 'drplus_image',
					'type'	=> 'image'
				] );
				?>
			</div>
			<?php
		}
	}

	public static function tax_edit_fields( $term, $taxonomy ) {
		$attr_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		$options = WC::get_attribute_settings( $attr_id );
		if( !in_array( $options['display_type'], array_keys( WC::attr_display_types() ) ) ) return;

		if( $options['display_type'] == 'color' ) {
			$color = WC::get_term_color( $term->term_id );
			?>
			<tr class="form-field">
				<th>
					<label for="drplus_color"><?php esc_html_e( 'Color', 'drplus' ) ?></label>
				</th>
				<td>
					<input type="color" name="drplus_color" id="drplus_color" value="<?php echo esc_attr( $color ) ?>">
				</td>
			</tr>
			<?php
		} else if( $options['display_type'] == 'image' ) {
			$image = WC::get_term_img( $term->term_id );
			?>
			<tr class="form-field">
				<th>
					<label for="drplus_image"><?php esc_html_e( 'Image', 'drplus' ) ?></label>
				</th>
				<td>
					<?php
					AdminUI::attachment( [
						'name'	=> 'drplus_image',
						'type'	=> 'image',
						'file'	=> $image,
					] );
					?>
				</td>
			</tr>
			<?php
		}
	}

	public static function tax_save( $term_id ) {
		if( !empty( $_POST["drplus_color"] ) ) {
			WC::update_term_color( $term_id, Utils::convert_chars( $_POST["drplus_color"], true, 'sanitize_hex_color' ) );
		}
		if( !empty( $_POST["drplus_image"] ) ) {
			WC::update_term_img( $term_id, Utils::convert_chars( $_POST["drplus_image"], true, 'absint' ) );
		}
	}

	public static function attribute_enqueue( $hook ) {
		if( $hook != 'product_page_product_attributes' ) return;
		
		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	public static function attribute_icon_picker_modal() {
		global $pagenow;
		if( $pagenow != 'edit.php' || empty( $_GET['post_type'] ) || empty( $_GET['page'] ) || $_GET['post_type'] != 'product' || $_GET['page'] != 'product_attributes' ) return;
		
		AdminUI::modal( [
			'id'				=> "drplus-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function taxonomy_enqueue( $hook ) {
		if( $hook != 'edit-tags.php' && $hook != 'term.php' ) return;

		wp_enqueue_media();
		AdminScripts::attachment();
	}
}
add_action( 'woocommerce_after_add_attribute_fields', [WCAttributeFields::class, 'add_fields'] );
add_action( 'woocommerce_after_edit_attribute_fields', [WCAttributeFields::class, 'edit_fields'] );
add_action( 'woocommerce_attribute_added', [WCAttributeFields::class, 'save'] );
add_action( 'woocommerce_attribute_updated', [WCAttributeFields::class, 'save'] );
add_action( 'admin_enqueue_scripts', [WCAttributeFields::class, 'attribute_enqueue'] );
add_action( 'admin_footer', [WCAttributeFields::class, 'attribute_icon_picker_modal'] );

global $pagenow;
if( !empty( $pagenow ) && in_array( $pagenow, ['edit-tags.php', 'term.php', 'admin-ajax.php'] ) ) {
	$attribute_taxonomies = wc_get_attribute_taxonomies();
	add_action( 'admin_enqueue_scripts', [WCAttributeFields::class, 'taxonomy_enqueue'] );
	foreach( $attribute_taxonomies as $tax ) {
		add_action( "pa_{$tax->attribute_name}_add_form_fields", [WCAttributeFields::class, 'tax_add_fields'] );
		add_action( "pa_{$tax->attribute_name}_edit_form_fields", [WCAttributeFields::class, 'tax_edit_fields'], 10, 2 );
		add_action( "created_pa_{$tax->attribute_name}", [WCAttributeFields::class, 'tax_save'] );
		add_action( "edited_pa_{$tax->attribute_name}", [WCAttributeFields::class, 'tax_save'] );
	}
}