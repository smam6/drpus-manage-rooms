<?php

use DrPlus\Utils\Sanitizers;

extract($args);
$stats = apply_filters( 'drplus/specialist/single/stats', $stats, $specialist );

if( !empty( $stats ) ) { ?>
	<section class="<?php echo $prefix ?>stats" role="region" aria-label="<?php printf( esc_html__( 'Stats of %s', 'drplus' ), $specialist->display_name ) ?>">
		<<?php echo tag_escape( $options['single_specialist_sections_tag'] ) ?> class="screen-reader-text">
			<?php printf( esc_html__( 'Stats of %s', 'drplus' ), $specialist->display_name ) ?>
		</<?php echo tag_escape( $options['single_specialist_sections_tag'] ) ?>>
		<?php foreach( $stats as $stat ) { ?>
			<div class="<?php echo $prefix ?>stat">
				<div class="<?php echo $prefix ?>stat-data">
					<span class="<?php echo $prefix ?>stat-title"><?php echo esc_html( $stat['title'] ) ?></span>
					<span class="<?php echo $prefix ?>stat-value"><?php echo esc_html( $stat['value'] ) ?></span>
				</div>
				<?php echo Sanitizers::icon( $stat['icon'], "{$prefix}stat-icon" ) ?>
			</div>
		<?php } ?>
	</section>
<?php } ?>