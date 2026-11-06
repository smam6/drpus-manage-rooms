<?php
use DrPlus\Utils;
use DrPlus\Utils\UI;

extract($args);
$avg_score = Utils::get_post_avg( get_the_ID(), 1, $comments_count );
?>
<div class="<?php echo $prefix ?>sidebar-comments-wrap">
	<?php UI::stars( absint( $avg_score ), 5 ) ?>
	<?php if( !empty( $args['show_score_text'] ) ) { ?>
		<div class="sidebar-comments-count"><?php echo $comments_count == 0 ? esc_html__( "No comment", 'drplus' ) : sprintf( esc_html__( "%d comments", 'drplus' ), $comments_count ) ?></div>
	<?php } ?>
</div>