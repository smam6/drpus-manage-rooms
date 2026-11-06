<?php

if( !defined( 'ABSPATH' ) ) exit;

?>
<div class="drplus-specialist-form-body drplus-specialist-form-services">
	<?php
	get_template_part( "templates/specialists/onboard/template-specialists-onboard-services", null, [
		'specialist'	=> $specialist
	] );
	?>
</div>