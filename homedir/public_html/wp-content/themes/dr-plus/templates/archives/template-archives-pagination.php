<?php if( !defined( 'ABSPATH' ) ) exit; ?>
<div class="pagination">
	<?php
	$is_rtl = is_rtl();

	$prev_text = $is_rtl ? "<i class='drplus-icon-chevron-right-dot'></i>" : "<i class='drplus-icon-chevron-left-dot'></i>";
	$prev_text .= esc_html__( 'Previous page', 'drplus' );
	$next_text = esc_html__( 'Next page', 'drplus' );
	$next_text .= $is_rtl ? "<i class='drplus-icon-chevron-left-dot'></i>" : "<i class='drplus-icon-chevron-right-dot'></i>";

	the_posts_pagination(
		[
			'prev_text'	=> $prev_text,
			'next_text'	=> $next_text,
			'mid_size'	=> 3,
			'class'		=> '',
			/* translators: Hidden accessibility text. */
			'before_page_number'	=> '<span class="meta-nav screen-reader-text">' . __( 'Page', 'drplus' ) . ' </span>',
		]
	);
	?>
</div>