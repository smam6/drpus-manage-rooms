<?php
namespace DrPlus\Backend\Taxonomies;

use DrPlus\Utils;

if( !defined( 'ABSPATH' ) ) exit;

class IdentityTypes {
	public static function add() {
		$labels = [
			'name'			=> __( 'Identity types', 'drplus' ),
			'singular_name'	=> __( 'Identity type', 'drplus' ),
			'search_items'	=> __( 'Search types', 'drplus' ),
			'all_items'		=> __( 'All types', 'drplus' ),
			'edit_item'		=> __( 'Edit type', 'drplus' ),
			'update_item'	=> __( 'Update type', 'drplus' ),
			'add_new_item'	=> __( 'Add New type', 'drplus' ),
			'new_item_name'	=> __( 'New type Name', 'drplus' ),
			'menu_name'		=> __( 'Identity types', 'drplus' ),
			'not_found'		=> __( 'No type found.', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> false,
			'publicly_queryable'	=> false,
			'show_in_rest'			=> false,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_quick_edit'	=> false,
			'hierarchical'			=> false,
			'rewrite'				=> false,
			'query_var'				=> false,
		];

		register_taxonomy( 'identity_type', [], $args );
	}

	public static function add_fields() {
		?>
		<div class="form-field term-optional-wrap">
			<label>
				<input type="checkbox" name="drplus_is_optional" id="drplus_is_optional" value="true" checked>
				<span><?php esc_html_e( 'If this document is optional check this.', 'drplus' ) ?></span>
			</label>
		</div>
		<?php
	}

	public static function edit_fields( $term ) {
		$optional = Utils::to_bool( get_term_meta( $term->term_id, 'optional', true ) );
		?>
		<tr>
			<th>
				<label for="drplus_is_optional"><?php esc_html_e( 'Is optional?', 'drplus' ) ?></label>
			</th>

			<td>
				<label>
					<input type="checkbox" name="drplus_is_optional" id="drplus_is_optional" value="true" <?php checked( true, $optional ) ?>>
					<span><?php esc_html_e( 'If this document is optional check this.', 'drplus' ) ?></span>
				</label>
			</td>
		</tr>
		<?php
	}

	public static function save( $term_id ) {
		$optional = false;
		if( !empty( $_POST['drplus_is_optional'] ) ) {
			$optional = true;
		}
		update_term_meta( $term_id, 'optional', $optional );
	}

	public static function custom_columns( $columns ) {
		$columns = Utils::unset( $columns, ['slug', 'posts'] );
		$columns['optional'] = esc_html__( "Is optional?", 'drplus' );
		return $columns;
	}

	public static function col_data( $content, $col_name, $term_id ) {
		if( $col_name == 'optional' ) {
			$optional = Utils::to_bool( get_term_meta( $term_id, 'optional', true ) );
			$content = '<p>' . ( $optional ? esc_html__( "Optional", 'drplus' ) : esc_html__( "Required", 'drplus' ) ) . '</p>';
		}
		return $content;
	}
}
IdentityTypes::add();

if( is_admin() ) {
	add_action( "identity_type_add_form_fields", [IdentityTypes::class, 'add_fields'] );
	add_action( "identity_type_edit_form_fields", [IdentityTypes::class, 'edit_fields'] );
	add_action( "create_identity_type", [IdentityTypes::class, 'save'] );
	add_action( "edited_identity_type", [IdentityTypes::class, 'save'] );
	add_filter( "manage_edit-identity_type_columns", [IdentityTypes::class, 'custom_columns'] );
	add_filter( "manage_identity_type_custom_column", [IdentityTypes::class, 'col_data'], 10, 3 );
}