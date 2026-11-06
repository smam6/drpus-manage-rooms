<?php

use DrPlus\Utils;

$comment_id = $args['comment_id'];

$comment = get_comment( $comment_id );
							
$child_comments = get_comments( [
	'order'  		=> 'ASC',
	'parent'		=> $comment_id,
	'status'		=> 'approve',
	'hierarchical' => 'threaded',
] );

if( empty( $comment ) ) return;

?>
<div class="drplus-booking-receipt-review">
	<div class="drplus-booking-receipt-review-head">
		
	</div>
	<div class="drplus-booking-receipt-review-body"></div>
</div>
<?php if( !empty( $child_comments ) ) { ?>						
	<div class="drplus-booking-receipt-review-childs">
		<?php foreach( $child_comments as $child ) { ?>
			
		<?php } ?>
	</div>
<?php } ?>