<?php

use Sheyda\Wallet\Backend\Settings;

$prefix = $args['prefix'];
?>
<form method="post" action="" class="<?php echo $prefix ?>section-wrap">
	<?php Settings::create_nonce(); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					
				</th>
				<td>
					
				</td>
			</tr>
		</tbody>
	</table>

	<button type="submit" id="<?php echo $prefix ?>submit"><?php esc_html_e( 'Save changes', 'sheyda_wallet' ) ?></button>
</form>