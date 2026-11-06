<?php

if( !defined( 'ABSPATH' ) ) exit;
?>
<div class="drplus-specialist-form-body drplus-specialist-form-offices">
	<?php
	get_template_part( "templates/specialists/onboard/template-specialists-onboard-offices", null, [
		'specialist'	=> $specialist
	] );
	?>
</div>