<?php

if( !defined( 'ABSPATH' ) ) exit;
?>
<div class="drplus-specialist-form-body drplus-specialist-form-certificates">
	<?php
	get_template_part( "templates/specialists/onboard/template-specialists-onboard-certificates", null, [
		'specialist'	=> $specialist
	] );
	?>
</div>