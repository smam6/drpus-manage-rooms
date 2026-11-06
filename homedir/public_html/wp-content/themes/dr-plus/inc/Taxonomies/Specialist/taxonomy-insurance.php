<?php
namespace DrPlus\Backend\Taxonomies;

use DrPlus\AdminScripts;
use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Options;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

class Insurance {
	public static function enqueue( $hook ) {
		if( $hook != 'edit-tags.php' && $hook != 'term.php' ) return;
		if( empty( $_GET['taxonomy'] ) || $_GET['taxonomy'] != 'insurance' ) return;

		AdminScripts::modal();
		AdminScripts::icon_picker();
	}

	public static function add() {
		$labels = [
			'name'				=> __( 'Insurances', 'drplus' ),
			'singular_name'		=> __( 'Insurance', 'drplus' ),
			'search_items'		=> __( 'Search Insurances', 'drplus' ),
			'all_items'			=> __( 'All Insurances', 'drplus' ),
			'edit_item'			=> __( 'Edit Insurance', 'drplus' ),
			'update_item'		=> __( 'Update Insurance', 'drplus' ),
			'add_new_item'		=> __( 'Add New Insurance', 'drplus' ),
			'new_item_name'		=> __( 'New Insurance Name', 'drplus' ),
			'menu_name'			=> __( 'Insurances', 'drplus' ),
			'not_found'			=> __( 'No insurance found.', 'drplus' ),
		];
		$args = [
			'labels'				=> $labels,
			'show_ui'				=> true,
			'show_in_menu'			=> true,
			'show_in_nav_menus'		=> true,
			'show_in_rest'			=> true,
			'show_in_quick_edit'	=> true,
			'hierarchical'			=> false,
			'rewrite'				=> false
		];

		register_taxonomy( 'insurance', [], $args );
	}

	public static function add_fields() {
		?>
		<div class="form-field term-icon-wrap">
			<label for="tag-icon"><?php esc_html_e( 'Icon', 'drplus' ) ?></label>
			<?php AdminUI::icon_picker( [
				'name'		=> "drplus_icon",
				'id'		=> "drplus-icon",
				'icon'		=> 'drplus-icon-toseei',
				'modal_id'	=> "drplus-icon-picker-modal"
			] ); ?>
		</div>
		<?php

		AdminUI::modal( [
			'id'				=> "drplus-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function edit_fields( $term ) {
		$icon = get_term_meta( $term->term_id, 'icon', true );
		?>
		<tr>
			<th>
				<label for="drplus-icon"><?php esc_html_e( 'Icon', 'drplus' ) ?></label>
			</th>

			<td>
				<?php AdminUI::icon_picker( [
					'name'		=> "drplus_icon",
					'id'		=> "drplus-icon",
					'icon'		=> !empty( $icon ) ? $icon : 'drplus-icon-toseei',
					'modal_id'	=> "drplus-icon-picker-modal"
				] ); ?>
			</td>
		</tr>
		<?php
		AdminUI::modal( [
			'id'				=> "drplus-icon-picker-modal",
			'title'				=> esc_html__( "Select your icon", 'drplus' ),
			'classes'			=> ['icon-picker-modal'],
			'submit_btn_text'	=> esc_html__( "Select icon", 'drplus' ),
		] );
	}

	public static function save( $term_id ) {
		$icon = '';
		if( $_POST['drplus_icon'] ) {
			$icon = Utils::convert_chars( $_POST['drplus_icon'] );
		}
		update_term_meta( $term_id, 'icon', $icon );
	}

	public static function custom_columns( $columns ) {
		$columns = Utils::unset( $columns, ['slug', 'posts'] );
		$columns['icon'] = __( "Icon", 'drplus' );
		return $columns;
	}

	public static function col_data( $content, $col_name, $term_id ) {
		if( $col_name == 'icon' ) {
			$icon = get_term_meta( $term_id, 'icon', true );
			$content = '<p>' . Sanitizers::icon( $icon ) . '</p><pre>' . esc_html( $icon ) . '</pre>';
		}
		return $content;
	}
}
$options = Options::get_options( [
	'insurance'	=> true,
] );
if( !$options['insurance'] ) return;

Insurance::add();

if( is_admin() ) {
	add_action( 'admin_enqueue_scripts', [Insurance::class, 'enqueue'] );
	add_action( "insurance_add_form_fields", [Insurance::class, 'add_fields'] );
	add_action( "insurance_edit_form_fields", [Insurance::class, 'edit_fields'] );
	add_action( "create_insurance", [Insurance::class, 'save'] );
	add_action( "edited_insurance", [Insurance::class, 'save'] );
	add_filter( "manage_edit-insurance_columns", [Insurance::class, 'custom_columns'] );
	add_filter( "manage_insurance_custom_column", [Insurance::class, 'col_data'], 10, 3 );
}