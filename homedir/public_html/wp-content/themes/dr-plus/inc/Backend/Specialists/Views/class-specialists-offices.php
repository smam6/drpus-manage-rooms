<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils;
use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Location;
use DrPlus\Utils\SpecialistHospitalsRel;

class SpecialistOffices extends SpecialistView {
	private static $provinces = [];
	private static $location_has_second_level = false;
	private static $all_locations = [];

	public static function view() {
		// Check if location taxonomy has at least 1 term with parent != 0
		self::$all_locations = Location::locations();
		foreach( self::$all_locations as $term ) {
			if( $term->parent != 0 ) {
				self::$location_has_second_level = true;
				break;
			}
		}

		// Get location taxonomies with parent 0
		foreach( self::$all_locations as $term_data ) {
			if( $term_data->parent !== 0 ) continue;
			self::$provinces[$term_data->term_id] = $term_data->name;
		}

		$user_hospitals = SpecialistHospitalsRel::get_user_hospitals( parent::$user->ID )->pluck( 'hospital_id' );
		$user_data = [];
		$main_office_options = [];
		if( !empty( $user_hospitals ) ) {
			$_user_hospital_posts = get_posts( [
				'post_type'		=> 'hospital',
				'numberposts'	=> -1,
				'include'		=> $user_hospitals
			] );
			foreach( $_user_hospital_posts as $hospital_post ) {
				$hospital_option = Hospital::get_options( $hospital_post->ID );
				$user_data['hospitals'][$hospital_post->ID] = "{$hospital_post->post_title} - {$hospital_option['subtitle']} ({$hospital_option['city']})";
				$main_office_options[$hospital_post->ID] = "{$hospital_post->post_title} - {$hospital_option['subtitle']} ({$hospital_option['city']})";
			}
		}
		
		$offices = !empty( parent::$specialist->offices ) ? parent::$specialist->offices : [];
		?>
		<div class="<?php echo parent::$PREFIX ?>hospitals-wrap">
			<?php AdminUI::dropzone( [], true ) ?>
			<span class="<?php echo parent::$PREFIX ?>part-title"><?php esc_html_e( 'Hospitals', 'drplus' ) ?></span>
			<?php
			AdminUI::select_with_label( [
				'value'				=> array_keys( $user_data['hospitals'] ?? [] ),
				'id'				=> parent::$PREFIX . "select_hospitals",
				'name'				=> parent::$PREFIX . "offices[hospitals][]",
				'select_classes'	=> ['drplus-select2', parent::$PREFIX . 'select_hospitals'],
				'data-width'		=> '100%',
				'options'			=> $user_data['hospitals'] ?? [],
				'multiple'			=> true
			] );
			?>
		</div>
		<div class="<?php echo parent::$PREFIX ?>offices-wrap">
			<span class="<?php echo parent::$PREFIX ?>part-title"><?php esc_html_e( 'Offices', 'drplus' ) ?></span>
			<div id="<?php echo parent::$PREFIX ?>offices" class="<?php echo parent::$PREFIX ?>repeater_container">
				<?php
				$index = 1;
				foreach( $offices as $office ) {
					if( $office['type'] != 'custom' ) continue;
					self::office_repeater_template( $index, $office );
					$index++;
				}
				?>
			</div>
			<button type="button" id="<?php echo parent::$PREFIX ?>office-add" class="<?php echo parent::$PREFIX ?>repeater-add"><?php esc_html_e( 'Add', 'drplus' ) ?></button>
			<script type="text/html" id="tmpl-<?php echo parent::$PREFIX ?>office_template">
				<?php echo self::office_repeater_template( '{{{data.index}}}' ); ?>
			</script>
		</div>
		<div class="<?php echo parent::$PREFIX ?>main_office-wrap">
			<span class="<?php echo parent::$PREFIX ?>part-title"><?php esc_html_e( 'Main office', 'drplus' ) ?></span>
			<?php
			$main_office = "0";
			foreach( $offices as $office ) {
				if( !empty( $office['main'] ) && $office['main'] == true ) {
					$main_office = $office['id'];
					break;
				}
			}
			foreach( $offices as $office ) {
				if( $office['type'] != 'custom' ) continue;
				$main_office_options[$office['id']] = $office['name'];
			}
			AdminUI::select_with_label( [
				'label'				=> esc_html__( 'Select office', 'drplus' ),
				'value'				=> $main_office,
				'id'				=> parent::$PREFIX . "main_office",
				'name'				=> parent::$PREFIX . "main_office",
				'select_classes'	=> ['drplus-select2'],
				'options'			=> $main_office_options,
			] );
			?>
			<p class="description"><?php esc_html_e( 'Save and reload the page to update options for new added offices', 'drplus' ) ?></p>
		</div>
		<?php
		do_action( "drplus/specialist/profile/offices", parent::$specialist );
	}

	private static function office_repeater_template( $index, $office = [] ) {
		$prefix = parent::$PREFIX . "office_";
		$office = Utils::check_default( $office, [
			'id'		=> '',
			'name'		=> '',
			'phone'		=> '',
			'province'	=> '',
			'city'		=> '',
			'address'	=> '',
			'map_url'	=> '',
			'image'		=> 0,
		] );

		// Get second level locations with parent of selected first level or first element of first level locations
		$cities = [];
		if( !empty( self::$provinces ) && self::$location_has_second_level ) {
			$parent_location = !empty( $office['province'] ) ? $office['province'] : array_key_first( self::$provinces );
			foreach( self::$all_locations as $term ) {
				if( (int)$term->parent !== (int)$parent_location || $term->parent === 0 ) continue;
				$cities[$term->term_id] = $term->name;
			}
		} else if( !self::$location_has_second_level ) {
			$cities = self::$provinces; // In this case, provinces are actually cities
		}
		if( $office['id'] === 0 ) $office['id'] = "";
		?>
		<div class="<?php echo esc_attr( $prefix ) ?>wrap <?php echo parent::$PREFIX ?>repeater_slot" data-swapy-slot="<?php echo esc_attr( $prefix ) ?>slot-<?php echo esc_attr( $index ) ?>">
			<div class="<?php echo esc_attr( $prefix ) ?>item <?php echo parent::$PREFIX ?>repeater_item" data-swapy-item="<?php echo esc_attr( $prefix ) . esc_attr( $index ) ?>">
				<div class="<?php echo esc_attr( $prefix ) ?>head <?php echo parent::$PREFIX ?>repeater-head">
					<span class="<?php echo esc_attr( $prefix ) ?>index <?php echo parent::$PREFIX ?>repeater-index"><?php echo esc_html( $index ) ?></span>
					<i class="dashicons dashicons-menu-alt3 <?php echo parent::$PREFIX ?>repeater-move" data-swapy-handle></i>
					<i class="dashicons dashicons-trash <?php echo parent::$PREFIX ?>repeater-remove"></i>
				</div>
				<div class="<?php echo esc_attr( $prefix ) ?>body <?php echo parent::$PREFIX ?>repeater-body">
					<input type="hidden" name="<?php echo parent::$PREFIX ?>offices[<?php echo $index ?>][id]" id="<?php echo $prefix . "id_" . $index ?>" value="<?php echo $office['id'] ?>">
					<?php
					AdminUI::input_with_label( [
						'label'			=>	esc_html__( 'Name', 'drplus' ),
						'type'			=> 'text',
						'value'			=> $office['name'],
						'id'			=> $prefix . "name_{$index}",
						'name'			=> parent::$PREFIX . "offices[{$index}][name]",
						'input_classes'	=> ['regular-text', $prefix . "name"],
					] );
					AdminUI::input_with_label( [ // Do not validate, it can be تلفن ثابت!
						'label'			=>	esc_html__( 'Phone', 'drplus' ),
						'type'			=> 'text',
						'value'			=> $office['phone'],
						'id'			=> $prefix . "phone_{$index}",
						'name'			=> parent::$PREFIX . "offices[{$index}][phone]",
						'textarea'		=> true,
						'rows'			=> 3,
						'description'	=> esc_html__( 'Write each number in one line', 'drplus' ),
						'input_classes'	=> ['regular-text', $prefix . "phone", 'ltr'],
					] );
					if( self::$location_has_second_level ) {
						AdminUI::select_with_label( [
							'label'					=> __( "Province", 'drplus' ),
							'value'					=> $office['province'],
							'options'				=> self::$provinces,
							'id'					=> $prefix . "province_{$index}",
							'name'					=> parent::$PREFIX . "offices[{$index}][province]",
							'select_classes'		=> ['drplus-province-selector'],
							'data-city-selector'	=> '#' . $prefix . "city_{$index}",
							'data-width'			=> '100%',
						] );
					}
					AdminUI::select_with_label( [
						'label'				=> __( 'City', 'drplus' ),
						'value'				=> $office['city'],
						'options'			=> $cities,
						'id'				=> $prefix . "city_{$index}",
						'name'				=> parent::$PREFIX . "offices[{$index}][city]",
						'select_classes'	=> ['drplus-select2'],
						'data-width'		=> '100%',
					] );
					AdminUI::input_with_label( [
						'label'			=>	esc_html__( 'Address', 'drplus' ),
						'type'			=> 'text',
						'value'			=> $office['address'],
						'id'			=> $prefix . "address_{$index}",
						'name'			=> parent::$PREFIX . "offices[{$index}][address]",
						'input_classes'	=> ['regular-text', $prefix . "address"],
					] );
					AdminUI::input_with_label( [
						'label'			=>	esc_html__( 'Map iframe code', 'drplus' ),
						'type'			=> 'text',
						'value'			=> $office['map_url'],
						'id'			=> $prefix . "map_url_{$index}",
						'name'			=> parent::$PREFIX . "offices[{$index}][map_url]",
						'input_classes'	=> ['regular-text', $prefix . "map_url", 'ltr'],
					] );
					AdminUI::dropzone( [
						'title'				=> esc_html__( 'Office Image', 'drplus' ),
						'max_upload_size'	=> parent::$max_upload_size_bytes,
						'input_name'		=> parent::$PREFIX . "offices[{$index}][image]",
						'input_id'			=> parent::$PREFIX . "offices-image-{$index}",
						'value'				=> $office['image'],
						'required'			=> '',
					] );
					?>
				</div>
			</div>
		</div>
		<?php
	}
}