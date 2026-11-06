<div class="<?php echo self::$PREFIX ?>tab-content" id="<?php echo self::$PREFIX ?>settings-content" style="display:none;">
	<div class="<?php echo self::$PREFIX ?>section-content" id="<?php echo self::$PREFIX ?>settings-auth-content">
		<?php include( DRPLUS_DIR . "inc/sms/Pages/SettingsAuth.php" ); ?>
	</div>
	<div class="<?php echo self::$PREFIX ?>section-content" id="<?php echo self::$PREFIX ?>settings-reserve-content" style="display:none;">
		<?php include( DRPLUS_DIR . "inc/sms/Pages/SettingsReserve.php" ); ?>
	</div>
	<div class="<?php echo self::$PREFIX ?>section-content" id="<?php echo self::$PREFIX ?>settings-specialist-content" style="display:none;">
		<?php include( DRPLUS_DIR . "inc/sms/Pages/SettingsSpecialist.php" ); ?>
	</div>
</div>