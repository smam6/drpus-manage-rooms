<?php
namespace DrPlus\Backend\Taxonomies\Product;

use DrPlus\AdminScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;

if( !defined( 'ABSPATH' ) ) exit;

class Badge {
	public static function add() {
		if( !class_exists( 'WooCommerce' ) ) return;

		$labels = [
			'name'			=> __( 'Product badges', 'drplus' ),
			'singular_name'	=> __( 'Product badge', 'drplus' ),
			'search_items'	=> __( 'Search Product badges', 'drplus' ),
			'all_items'		=> __( 'All Product badges', 'drplus' ),
			'edit_item'		=> __( 'Edit Product badge', 'drplus' ),
			'update_item'	=> __( 'Update Product badge', 'drplus' ),
			'add_new_item'	=> __( 'Add New Product badge', 'drplus' ),
			'new_item_name'	=> __( 'New Product badge Name', 'drplus' ),
			'menu_name'		=> __( 'Product badges', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'public'				=> false,
			'publicly_queryable'	=> false,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_rest'			=> true,
			'show_in_quick_edit'	=> true,
			'hierarchical'			=> false,
			'rewrite'				=> false
		];

		register_taxonomy( 'product-badge', 'product', $args );
	}

	public static function add_image_field() {
		?>
		<div class="form-field">
			<label for="badge_image"><?php esc_html_e( 'Image', 'drplus' ); ?></label>
			<?php
			AdminUI::attachment( [
				'id'	=> "badge_image",
				'name'	=> "badge_image",
				'type'	=> 'image',
				'file'	=> 0,
			] );
			?>
			<p class="description"><?php esc_html_e( 'Size: 24*24px', 'drplus' ) ?></p>
		</div>
		<?php
	}

	public static function edit_image_field( $term ) {
		$image = get_term_meta( $term->term_id, 'badge_image', true );
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="badge_image"><?php esc_html_e( 'Image', 'drplus' ); ?></label>
			</th>
			<td>
				<?php
				AdminUI::attachment( [
					'id'	=> "badge_image",
					'name'	=> "badge_image",
					'type'	=> 'image',
					'file'	=> $image,
				] );
				?>
				<p class="description"><?php esc_html_e( 'Size: 24*24px', 'drplus' ) ?></p>
			</td>
		</tr>
		<?php
	}

	public static function save_image_field( $term_id ) {
		if ( isset( $_POST['badge_image'] ) ) {
			update_term_meta( $term_id, 'badge_image', Utils::convert_chars( $_POST['badge_image'], true, 'absint' ) );
		}
	}

	public static function enqueue() {
		$screen = get_current_screen();
		if( empty( $screen->id ) || $screen->id != 'edit-product-badge' ) return;

		wp_enqueue_media();
		AdminScripts::attachment();
	}

	public static function columns( $columns ) {
		$columns['image'] = esc_html__( "Image", 'drplus' );
		Utils::reposition_array_element( $columns, 'image', 1 );

		$columns = Utils::unset( $columns, ['slug'] );
		return $columns;
	}

	public static function column_content( $string, $column_name, $term_id ) {
		if( $column_name == 'image' ) {
			$image_id = absint( get_term_meta( $term_id, 'badge_image', true ) );
			if( $image_id ) {
				$string = wp_get_attachment_image( $image_id, [24, 24] );
			}
		}
		return $string;
	}
}
Badge::add();
add_action( 'product-badge_add_form_fields', [Badge::class, 'add_image_field'] );
add_action( 'product-badge_edit_form_fields', [Badge::class, 'edit_image_field'] );
add_action( 'edited_product-badge', [Badge::class, 'save_image_field'] );
add_action( 'create_product-badge', [Badge::class, 'save_image_field'] );
add_action( 'admin_enqueue_scripts', [Badge::class, 'enqueue'] );
add_filter( 'manage_edit-product-badge_columns', [Badge::class, 'columns'] );
add_filter( 'manage_product-badge_custom_column', [Badge::class, 'column_content'], 10, 3 );