<?php

use DrPlus\Model\SpecialistSpecialitiesRel;
use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\Speciality;
use DrPlus\Utils\UI;

extract( $args );

$specialities_ids = SpecialistSpecialitiesRel::query()->select( 'speciality_id' )->where( 'user_id', $specialist->user_id )->get()->pluck( 'speciality_id' );
$get_posts_args = [
	'numberposts'			=> 9,
	'post__in'				=> $specialities_ids,
	'ignore_sticky_posts'	=> true,
	'post_status'    		=> 'publish',
    'orderby'        		=> 'date',
    'order'          		=> 'DESC',
];
// remove limit to get all selected posts
if( $specialities_ids ) {
	unset( $get_posts_args['numberposts'] );
}
$all_specialities = Speciality::all( $get_posts_args );
// get remaining posts
if( $specialities_ids && count( $specialities_ids ) < 9 )  {
	$get_posts_args['numberposts'] = 9 - count( $specialities_ids );
	unset( $get_posts_args['post__in'] );
	$get_posts_args['post__not_in'] = $specialities_ids;
	$all_specialities = array_merge_recursive( $all_specialities, Speciality::all( $get_posts_args ) );
}

$specialities = [];
foreach( $all_specialities as $speciality ) {
	$speciality_options = Speciality::get_options( $speciality->ID );
	$specialities[] = [
		'ID'	=> $speciality->ID,
		'name'	=> $speciality->post_title,
		'icon'	=> $speciality_options['icon'],
	];
}

$meta = $specialist->meta;
$services = !empty( $meta['services'] ) ? $meta['services'] : [];
$services[] = [
	'title'	=> '',
	'desc'	=> '',
];
?>

<div class="onboard-subsection onboard-specialities">
	<div class="onboard-subsection-title"><?php esc_html_e( 'Specialities', 'drplus' ) ?></div>
	<div class="onboard-subsection-body">
		<div class="onboard-search-wrap onboard-specialities-search-wrap">
			<div class="input-wrap input-wrap-white">
				<input
					type="search"
					class="onboard-search"
					placeholder="<?php esc_attr_e( "Search in specialities", 'drplus' ) ?>"
					data-nonce="<?php echo wp_create_nonce( 'drplus-search-onboard' ) ?>"
					data-type="speciality"
					autofocus
				>
				<i class="drplus-icon-search-2-fill"></i>
			</div>
			<div class="onboard-search-error"></div>
		</div>

		<?php
		foreach( $specialities as $speciality ) {
			$label_classes = ['checkbox-wrap', 'checkbox-box', 'onboard-speciality'];
			$active = in_array( $speciality['ID'], $specialities_ids );
			if( $active ) {
				$label_classes[] = 'checked';
			}
			?>
			<label class="<?php echo Utils::prepare_html_classes( $label_classes ) ?>" title="<?php echo esc_attr( $speciality['name'] ) ?>" data-id="<?php echo esc_attr( $speciality['ID'] ) ?>">
				<?php echo Sanitizers::icon( $speciality['icon'], 'checkbox-icon onboard-speciality-icon' ) ?>
				<div class="checkbox-label onboard-speciality-name line-clamp line-clamp-2"><?php echo esc_html( $speciality['name'] ) ?></div>
				<input type="checkbox" name="specialist_specialities[]" class="checkbox" value="<?php echo esc_attr( $speciality['ID'] ) ?>" <?php checked( true, $active ) ?>>
			</label>
		<?php } ?>
	</div>
</div>

<div class="onboard-subsection">
	<div class="onboard-subsection-title"><?php esc_html_e( 'Services', 'drplus' ) ?></div>
	<div class="onboard-subsection-body">
		<?php
		$repeater_rows = [];
		foreach( $services as $index => $service ) {
			$repeater_rows[$index] = [
				[
					'type'	=> 'primary',
					'field'	=> [
						'type'			=> 'text',
						'placeholder'	=> __( "Service title", 'drplus' ),
						'name'			=> "specialist_meta[services][%index%][title]",
						'classes'		=> ['input-transparent', 'service-title'],
						'value'			=> $service['title'] ?? "",
					],
				],
				[
					'type'	=> 'secondary',
					'field'	=> [
						'type'			=> 'text',
						'placeholder'	=> __( "Service description", 'drplus' ),
						'name'			=> "specialist_meta[services][%index%][desc]",
						'classes'		=> ['input-transparent', 'service-desc'],
						'value'			=> $service['desc'] ?? "",
					],
				],
			];
		}
		UI::repeater( [
			'template_id'	=> 'specialist-service',
			'slot_attrs'	=> [
				'data-swapy-slot'	=> 'specialist-service-%index%-slot',
			],
			'item_attrs'	=> [
				'data-swapy-item'	=> 'specialist-service-%index%-item',
			],
			'rows'	=> $repeater_rows,
		] );
		?>
	</div>
</div>