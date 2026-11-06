<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils\AdminUI;

class SpecialistCertificates extends SpecialistView {
	public static function view() {
		$certificates = parent::$specialist->meta['certificates'] ?? [];
		?>
		<div id="<?php echo parent::$PREFIX ?>certificates-wrap">
			<?php AdminUI::dropzone( [], true ) ?>
			<div id="<?php echo parent::$PREFIX ?>certificates" class="<?php echo parent::$PREFIX ?>repeater_container">
				<?php foreach( $certificates as $index => $certificate ) { ?>
					<div class="<?php echo parent::$PREFIX ?>repeater_slot <?php echo parent::$PREFIX ?>certificate-wrap" data-swapy-slot="certificate-slot-<?php echo esc_attr( $index ) ?>">
						<div class="<?php echo parent::$PREFIX ?>repeater_item <?php echo parent::$PREFIX ?>certificate" data-swapy-item="<?php echo "certificate-" . esc_attr( $index ) ?>">
							<div class="<?php echo parent::$PREFIX ?>certificate-head <?php echo parent::$PREFIX ?>repeater-head">
								<span class="<?php echo parent::$PREFIX ?>certificate-index <?php echo parent::$PREFIX ?>repeater-index"><?php echo $index+1 ?></span>
								<i class="dashicons dashicons-menu-alt3 <?php echo parent::$PREFIX ?>repeater-move" data-swapy-handle></i>
								<i class="dashicons dashicons-trash <?php echo parent::$PREFIX ?>repeater-remove"></i>
							</div>

							<div class="<?php echo parent::$PREFIX ?>repeater-body <?php echo parent::$PREFIX ?>certificate-body">
								<?php
								AdminUI::input_with_label( [
									'label'			=> 	__( 'Certificate or course title', 'drplus' ),
									'type'			=> 'text',
									'value'			=> $certificate['title'],
									'id'			=> parent::$PREFIX . "certificate-title_{$index}",
									'name'			=> parent::$PREFIX . "meta[certificates][{$index}][title]",
									'input_classes'	=> ['regular-text', parent::$PREFIX . "certificate-title"],
									'required'		=> true,
								] );
								AdminUI::dropzone( [
									'max_upload_size'	=> parent::$max_upload_size_bytes,
									'input_name'		=> parent::$PREFIX . "meta[certificates][{$index}][attachment_id]",
									'input_id'			=> parent::$PREFIX . "certificates-{$index}",
									'value'				=> !empty( $certificate['attachment_id'] ) ? $certificate['attachment_id'] : '',
									'required'			=> '',
								] );
								?>
							</div>
						</div>
					</div>
				<?php } ?>
				<button type="button" id="<?php echo parent::$PREFIX ?>certificate-add" class="<?php echo parent::$PREFIX ?>repeater-add"><?php esc_html_e( 'Add', 'drplus' ) ?></button>
			</div>

			<script type="text/html" id="tmpl-<?php echo parent::$PREFIX ?>certificate">
				<div class="<?php echo parent::$PREFIX ?>repeater_slot <?php echo parent::$PREFIX ?>certificate-wrap" data-swapy-slot="certificate-slot-{{{data.index}}}">
					<div class="<?php echo parent::$PREFIX ?>repeater_item <?php echo parent::$PREFIX ?>certificate-item" data-swapy-item="certificate-{{{data.index}}}">
						<div class="<?php echo parent::$PREFIX ?>certificate-head <?php echo parent::$PREFIX ?>repeater-head">
							<span class="<?php echo parent::$PREFIX ?>certificate-index <?php echo parent::$PREFIX ?>repeater-index">{{{data.index1}}}</span>
							<i class="dashicons dashicons-menu-alt3 <?php echo parent::$PREFIX ?>repeater-move" data-swapy-handle></i>
							<i class="dashicons dashicons-trash <?php echo parent::$PREFIX ?>repeater-remove"></i>
						</div>

						<div class="<?php echo parent::$PREFIX ?>repeater-body <?php echo parent::$PREFIX ?>certificate-body">
							<?php
							AdminUI::input_with_label( [
								'label'			=> 	__( 'Certificate or course title', 'drplus' ),
								'type'			=> 'text',
								'value'			=> '',
								'id'			=> parent::$PREFIX . 'certificate-title_{{{data.index}}}',
								'name'			=> parent::$PREFIX . 'meta[certificates][{{{data.index}}}][title]',
								'input_classes'	=> ['regular-text', parent::$PREFIX . "certificate-title"],
								'required'		=> true,
							] );
							AdminUI::dropzone( [
								'max_upload_size'	=> parent::$max_upload_size_bytes,
								'input_name'		=> parent::$PREFIX . 'meta[certificates][{{{data.index}}}][attachment_id]',
								'input_id'			=> parent::$PREFIX . 'certificates-{{{data.index}}}',
								'value'				=> '',
								'required'			=> '',
							] );
							?>
						</div>
					</div>
				</div>
			</script>
		</div>
		<?php
	}
}