<?php

use DrPlus\Model\SpecialistInsurancesRel;
use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;
use DrPlus\Utils\UtilsSpecialists;

extract( $args );

$insurances_ids = SpecialistInsurancesRel::query()->select( 'insurance_id' )->where( 'user_id', $specialist->user_id )->get()->pluck( 'insurance_id' );

$all_insurances = UtilsSpecialists::get_insurances_terms();
$insurances = [];
foreach( $all_insurances as $insurance ) {
	$insurances[] = [
		'ID'	=> $insurance['id'],
		'name'	=> $insurance['name'],
		'icon'	=> $insurance['icon'],
	];
}
?>

<div class="onboard-subsection onboard-insurances">
	<div class="onboard-subsection-title"><?php esc_html_e( 'Insurances', 'drplus' ) ?></div>
	<div class="onboard-subsection-body">
		<?php
		foreach( $insurances as $insurance ) {
			$label_classes = ['checkbox-wrap', 'checkbox-box', 'onboard-insurance'];
			$active = in_array( $insurance['ID'], $insurances_ids );
			if( $active ) {
				$label_classes[] = 'checked';
			}
			?>
			<label class="<?php echo Utils::prepare_html_classes( $label_classes ) ?>">
				<?php echo Sanitizers::icon( $insurance['icon'], 'checkbox-icon onboard-insurance-icon' ) ?>
				<div class="checkbox-label onboard-insurance-name line-clamp line-clamp-2"><?php echo esc_html( $insurance['name'] ) ?></div>
				<input type="checkbox" name="specialist_insurances[]" class="checkbox" value="<?php echo esc_attr( $insurance['ID'] ) ?>" <?php checked( true, $active ) ?>>
			</label>
		<?php } ?>
	</div>
</div>