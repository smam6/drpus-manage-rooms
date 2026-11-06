<?php

use DrPlus\Utils;

$args = Utils::check_default( $args, [
	'title'			=> '',
	'description'	=> '',
] );
if( !$args['title'] ) return;
?>
<div class="hospital-service" role="article" itemscope itemtype="https://schema.org/Service">
	<h4 class="hospital-service-title" itemprop="name" role="heading" aria-level="4"><?php echo esc_html( $args['title'] ) ?></h4>
	<div class="hospital-service-description" itemprop="description" role="text"><?php echo esc_html( $args['description'] ) ?></div>
</div>