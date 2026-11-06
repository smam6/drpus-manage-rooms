<?php
$plan = $args['plan_data'];
?>
<div class="drplus-plan-checkout-data">
	<div class="drplus-plan-checkout-head">
		<i class="drplus-plan-checkout-icon <?php echo $plan['icon'] ?>"></i>
		<h2 class="drplus-plan-checkout-title"><?php echo $plan['title'] ?></h2>
		<span class="drplus-plan-checkout-subtitle"><?php echo $plan['subtitle'] ?></span>
	</div>
	
	<?php if( $plan['duration_label'] ) { ?>
		<span class="drplus-plan-checkout-duration_label"><?php echo $plan['duration_label'] ?></span>
	<?php } ?>

	<?php if( !empty( $plan['features'] ) ) { ?>
		<ul class="drplus-plan-checkout-features">
			<?php foreach( $plan['features'] as $feature ) { ?>
				<li class="drplus-plan-checkout-feature">
					<i class="drplus-icon-tick drplus-plan-checkout-feature-icon"></i>
					<span class="drplus-plan-checkout-feature-title"><?php echo esc_html( $feature ) ?></span>
				</li>
			<?php } ?>		
		</ul>
	<?php } ?>
</div>
