<?php

if( !defined( 'ABSPATH' ) ) exit;

?>
<div class="drplus-specialist-form-body drplus-specialist-form-identity">
	<?php
	get_template_part( "templates/specialists/onboard/template-specialists-onboard-identity", null, [
		'specialist'	=> $specialist
	] );
	?>
</div>