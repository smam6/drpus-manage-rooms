<?php

if( !defined( 'ABSPATH' ) ) exit;
?>
<div class="drplus-specialist-form-body drplus-specialist-form-personal">
	<?php
	get_template_part( "templates/specialists/onboard/template-specialists-onboard-personal", null, [
		'specialist'	=> $specialist,
		'disable_slug'	=> true,
		'my-account'	=> true,
	] );
	?>
</div>