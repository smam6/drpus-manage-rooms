<?php

use DrPlus\Utils;
use DrPlus\Utils\Hospital;
use DrPlus\Utils\Location;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\UI;

extract( $args );

$specialist_hospitals = [];
$offices = $specialist->offices;
$offices = Utils::obj_to_array( $offices, true );
foreach( $offices as $index => $office ) {
	if( empty( $office['type'] ) || $office['type'] != 'hospital' ) continue;
	$specialist_hospitals[] = $office['id'];
	unset( $offices[$index] );
}

$get_posts_args = [
	'post_type'				=> 'hospital',
    'numberposts'			=> 10,
    'post_status'			=> 'publish',
    'orderby'				=> 'date',
    'order'					=> 'DESC',
	'ignore_sticky_posts'	=> true,
	'post__in'				=> $specialist_hospitals,
];
// remove limit to get all selected posts
if( $specialist_hospitals ) {
	unset( $get_posts_args['numberposts'] );
}
$last_hospitals = get_posts( $get_posts_args );
// get remaining posts
if( $specialist_hospitals && count( $specialist_hospitals ) < 10 ) {
	$get_posts_args['numberposts'] = 10 - count( $specialist_hospitals );
	unset( $get_posts_args['post__in'] );
	$get_posts_args['post__not_in'] = $specialist_hospitals;
	$last_hospitals = array_merge_recursive( $last_hospitals, get_posts( $get_posts_args ) );
}

$offices = array_values( $offices );
$offices[] = [
	'type'		=> 'custom',
	'id'		=> '',
	'name'		=> '',
	'phone'		=> '',
	'province'	=> '',
	'city'		=> '',
	'address'	=> '',
	'map_url'	=> '',
	'image'		=> '',
];
?>

<?php if( $last_hospitals ) { ?>
	<div class="onboard-subsection onboard-hospitals">
		<div class="onboard-subsection-title"><?php esc_html_e( 'Hospitals', 'drplus' ) ?></div>
		<div class="onboard-subsection-body">
			<div class="onboard-search-wrap onboard-hospitals-search-wrap">
				<div class="input-wrap input-wrap-white">
					<input
						type="search"
						class="onboard-search"
						placeholder="<?php esc_attr_e( "Search in hospitals", 'drplus' ) ?>"
						data-nonce="<?php echo wp_create_nonce( 'drplus-search-onboard' ) ?>"
						data-type="hospital"
						autofocus
					>
					<i class="drplus-icon-search-2-fill"></i>
				</div>
				<div class="onboard-search-error"></div>
			</div>
			<?php
			foreach( $last_hospitals as $hospital ) {
				$hospital_data = Hospital::get_options( $hospital->ID, false, ['city'] );
				$label_classes = ['checkbox-wrap', 'checkbox-box', 'onboard-hospital'];
				$active = in_array( $hospital->ID, $specialist_hospitals );
				if( $active ) {
					$label_classes[] = 'checked';
				}
				?>
				<label class="<?php echo Utils::prepare_html_classes( $label_classes ) ?>" title="<?php echo esc_attr( $hospital->post_title ) ?>" data-id="<?php echo esc_attr( $hospital->ID ) ?>">
					<?php
					if( has_post_thumbnail( $hospital ) ) {
						echo get_the_post_thumbnail( $hospital->ID, [56, 56] );
					} else {
						echo Sanitizers::icon( 'drplus-icon-hospital', 'checkbox-icon' );
					}
					?>
					<div class="checkbox-label onboard-hospital-name line-clamp line-clamp-1">
						<?php echo esc_html( $hospital->post_title ) ?>
						<div class="checkbox-label-sub onboard-hospital-city"><?php echo esc_html( $hospital_data['city'] ) ?></div>
					</div>
					<input type="checkbox" name="specialist_offices[hospitals][]" class="checkbox" value="<?php echo esc_attr( $hospital->ID ) ?>" <?php checked( true, $active ) ?>>
				</label>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php
do_action( 'drplus/specialist/onboard/offices/after_hospitals', $specialist );
?>

<div class="onboard-subsection onboard-subsection-gray onboard-offices">
	<div class="onboard-subsection-title"><?php esc_html_e( 'Offices', 'drplus' ) ?></div>
	<div class="onboard-subsection-body">
		<?php
		UI::dropzone( [], true );
		$repeater_rows = [];
		$max_upload_size_bytes = Utils::get_max_upload_size();

		// Check if location taxonomy has at least 1 term with parent != 0
		$all_locations = Location::locations();
		$location_has_second_level = false;
		foreach( $all_locations as $term ) {
			if( $term->parent !== 0 ) {
				$location_has_second_level = true;
				break;
			}
		}

		// Get location taxonomies with parent 0
		$provinces = [];
		foreach( $all_locations as $term_data ) {
			if( $term_data->parent !== 0 ) continue;
			$provinces[$term_data->term_id] = $term_data->name;
		}

		foreach( $offices as $index => $office ) {
			if( empty( $office['type'] ) || $office['type'] != 'custom' ) continue;

			// Get second level locations with parent of selected first level or first element of first level locations
			$cities = [];
			if( !empty( $provinces ) && $location_has_second_level ) {
				$parent_location = !empty( $office['province'] ) ? $office['province'] : array_key_first( $provinces );
				foreach( $all_locations as $term ) {
					if( $term->parent !== $parent_location ) continue;
					$cities[$term->term_id] = $term->name;
				}
			} else if( !$location_has_second_level ) {
				$cities = $provinces; // In this case, provinces are actually cities
			}

			$province_and_city_fields = [ // province & city
				'type'		=> 'double',
				'field'		=> [
					'type'					=> 'select',
					'name'					=> "specialist_offices[%index%][province]",
					'classes'				=> ['input-secondary', 'drplus-province-selector', 'office-province'],
					'value'					=> $office['province'] ?? "",
					'options'				=> $provinces,
					'data-city-selector'	=> '#specialist_office_%index%_city',
					'data-width'			=> '100%',
				],
				'field2'	=> [
					'type'			=> 'select',
					'name'			=> "specialist_offices[%index%][city]",
					'id'			=> 'specialist_office_%index%_city',
					'classes'		=> ['input-secondary', 'drplus-select2', 'office-city'],
					'value'			=> $office['city'] ?? "",
					'options'		=> $cities,
					'data-width'	=> '100%',
				],
			];

			if( !$location_has_second_level ) {
				$province_and_city_fields['type'] = 'full';
				$province_and_city_fields['field'] = $province_and_city_fields['field2'];
				unset( $province_and_city_fields['field2'] );
			}

			$repeater_rows[$index] = [
				'id'	=> $office['id'],
				[ // name & phone
					'type'		=> 'double',
					'field'		=> [ // name
						'type'			=> 'text',
						'placeholder'	=> __( "Name", 'drplus' ),
						'name'			=> "specialist_offices[%index%][name]",
						'classes'		=> ['input-secondary', 'office-name'],
						'value'			=> $office['name'] ?? "",
						'autocomplete'	=> 'organization',
						'required'		=> 'required',
					],
					'field2'	=> [ // phone
						'type'			=> 'textarea',
						'placeholder'	=> __( "Phone", 'drplus' ),
						'name'			=> "specialist_offices[%index%][phone]",
						'classes'		=> ['input-secondary', 'input-ltr', 'office-phone'],
						'value'			=> $office['phone'] ?? "",
						'required'		=> 'required',
						'rows'			=> 3,
					],
				],
				$province_and_city_fields,
				[ // address & map_url
					'type'		=> 'double',
					'field'		=> [
						'type'			=> 'text',
						'placeholder'	=> __( "Address", 'drplus' ),
						'name'			=> "specialist_offices[%index%][address]",
						'classes'		=> ['input-secondary', 'office-address'],
						'value'			=> $office['address'] ?? "",
						'autocomplete'	=> 'address-line1',
						'required'		=> 'required',
					],
					'field2'	=> [
						'type'			=> 'url',
						'placeholder'	=> __( "Map URL", 'drplus' ),
						'name'			=> "specialist_offices[%index%][map_url]",
						'classes'		=> ['input-secondary', 'input-ltr', 'office-map_url'],
						'value'			=> $office['map_url'] ?? "",
					],
				],
				[ // image
					'type'	=> 'dropzone',
					'field'	=> [
						'type'				=> 'dropzone',
						'title'				=> __( "Office image", 'drplus' ),
						'max_upload_size'	=> $max_upload_size_bytes,
						'input_name'		=> "specialist_offices[%index%][image]",
						'value'				=> $office['image'] ?? "",
						'required'			=> '',
					],
				]
			];
		}
		UI::repeater( [
			'template_id'	=> 'specialist-office',
			'type'			=> 'manual',
			'style'			=> 'grid',
			'slot_attrs'	=> [
				'data-swapy-slot'	=> 'specialist-hospital-%index%-slot',
			],
			'item_attrs'	=> [
				'data-swapy-item'	=> 'specialist-hospital-%index%-item',
				'id_field'			=> [
					'name'	=> 'specialist_offices[%index%][id]',
				],
			],
			'rows'	=> $repeater_rows,
		] );
		?>
	</div>
</div>