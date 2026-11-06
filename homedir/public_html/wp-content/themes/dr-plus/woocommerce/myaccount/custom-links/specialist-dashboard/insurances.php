<?php

if( !defined( 'ABSPATH' ) ) exit;
?>
<div class="drplus-specialist-form-body drplus-specialist-form-insurances">
	<?php
	get_template_part( "templates/specialists/onboard/template-specialists-onboard-insurances", null, [
		'specialist'	=> $specialist
	] );
	?>
</div>