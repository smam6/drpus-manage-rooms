<?php

use DrPlus\Utils;
use DrPlus\Utils\Hospital;

if( !defined( 'ABSPATH' ) ) exit;

if( empty( $args['specialist'] ) ) return;

$specialist = $args['specialist'];
foreach( $specialist->offices as $office ) {
	if( !is_array( $office ) ) {
		$office = (array) $office;
	}
	if( empty( $office['main'] ) || !Utils::to_bool( $office['main'] ) ) continue;

	if( empty( $office['type'] ) ) continue;
	if( $office['type'] == 'hospital' ) {
		$address = Hospital::get_options( $office['id'], false, ['address'] )['address'];
	} else {
		$address = $office['address'];
	}
}
?>
<?php if( !empty( $address ) ) { ?>
	<div class="specialist-meta specialist-meta-inline specialist-meta-address">
		<i class="drplus-icon-location"></i>
		<div class="specialist-meta-value line-clamp line-clamp-1"><?php echo esc_html( $address ) ?></div>
	</div>
<?php } ?>