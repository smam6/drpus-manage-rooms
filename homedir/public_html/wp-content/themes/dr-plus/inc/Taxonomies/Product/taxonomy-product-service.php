<?php
namespace DrPlus\Backend\Taxonomies\Product;

use DrPlus\AdminScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;

if( !defined( 'ABSPATH' ) ) exit;

class Service {
	public static function add() {
		if( !class_exists( 'WooCommerce' ) ) return;

		$labels = [
			'name'				=> __( 'Product Services', 'drplus' ),
			'singular_name'		=> __( 'Product Service', 'drplus' ),
			'search_items'		=> __( 'Search Product Services', 'drplus' ),
			'all_items'			=> __( 'All Product Services', 'drplus' ),
			'edit_item'			=> __( 'Edit Product Service', 'drplus' ),
			'update_item'		=> __( 'Update Product Service', 'drplus' ),
			'add_new_item'		=> __( 'Add New Product Service', 'drplus' ),
			'new_item_name'		=> __( 'New Product Service Name', 'drplus' ),
			'menu_name'			=> __( 'Product Services', 'drplus' ),
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

		register_taxonomy( 'product-service', 'product', $args );
	}

	public static function add_icon_field() {
		?>
		<div class="form-field">
			<label for="service_icon_field"><?php esc_html_e( 'Icon', 'drplus' ); ?></label>
			<?php
			AdminUI::icon_picker( [
				'id'		=> "service_icon_field",
				'name'		=> "service_icon_field",
				'icon'		=> '',
				'modal_id'	=> 'icon-picker-modal',
			] );
			?>
			<p class="description"><?php esc_html_e( 'Select an icon for service (optional)', 'drplus' ); ?></p>
		</div>
		<?php
		AdminUI::modal( [
			'id'				=> 'icon-picker-modal',
			'title'				=> __( 'Select your icon', 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function edit_icon_field( $term ) {
		$icon = get_term_meta( $term->term_id, 'service_icon', true );
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="service_icon_field"><?php esc_html_e( 'Icon', 'drplus' ); ?></label>
			</th>
			<td>
				<?php
				AdminUI::icon_picker( [
					'id'		=> "service_icon_field",
					'name'		=> "service_icon_field",
					'icon'		=> esc_html( $icon ),
					'modal_id'	=> 'icon-picker-modal',
				] );
				?>
				<p class="description"><?php esc_html_e( 'Select an icon for service (optional)', 'drplus' ); ?></p>
			</td>
		</tr>
		<?php
		AdminUI::modal( [
			'id'				=> 'icon-picker-modal',
			'title'				=> __( 'Select your icon', 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function save_icon_field( $term_id ) {
		if ( isset( $_POST['service_icon_field'] ) ) {
			update_term_meta( $term_id, 'service_icon', Utils::convert_chars( $_POST['service_icon_field'] ) );
		}
	}

	public static function enqueue() {
		$screen = get_current_screen();
		if( empty( $screen->id ) || $screen->id != 'edit-product-service' ) return;

		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	public static function columns( $columns ) {
		$columns['icon'] = esc_html__( "Icon", 'drplus' );
		Utils::reposition_array_element( $columns, 'icon', 1 );
		return $columns;
	}

	public static function column_content( $string, $column_name, $term_id ) {
		if( $column_name == 'icon' ) {
			$string = '<i class="' . get_term_meta( $term_id, 'service_icon', true ) . '"></i>';
		}
		return $string;
	}
}
Service::add();
add_action( 'product-service_add_form_fields', [Service::class, 'add_icon_field'] );
add_action( 'product-service_edit_form_fields', [Service::class, 'edit_icon_field'] );
add_action( 'edited_product-service', [Service::class, 'save_icon_field'] );
add_action( 'create_product-service', [Service::class, 'save_icon_field'] );
add_action( 'admin_enqueue_scripts', [Service::class, 'enqueue'] );
add_filter( 'manage_edit-product-service_columns', [Service::class, 'columns'] );
add_filter( 'manage_product-service_custom_column', [Service::class, 'column_content'], 10, 3 );