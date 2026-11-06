<?php

use DrPlus\Utils;
use DrPlus\Utils\Options;
use DrPlus\Utils\Product;

if( !defined( 'ABSPATH' ) ) exit;

$options = Options::get_options( [
	'wc-single-show-product-services'	=> true,
] );
if( !Utils::to_bool( $options['wc-single-show-product-services'] ) ) return;

global $product;
if( empty( $product ) ) return;
$services = Product::get_services( $product->get_id() );
?>
<?php if( !empty( $services ) ) { ?>
	<div class="product-services-wrap">
		<?php foreach( $services as $service ) { ?>
			<div class="product-service-item">
				<?php if( !empty( $service->icon ) ) { ?>
					<i class="product-service-icon <?php echo $service->icon ?>"></i>								
				<?php } ?>
				<span class="product-service-name"><?php echo esc_html( $service->name ) ?></span>
			</div>
		<?php } ?>
	</div>
<?php } ?>