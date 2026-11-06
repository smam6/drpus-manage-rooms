<?php
namespace DrPlus\SMS;

use DrPlus\SMS\Utils as SMSUtils;
use DrPlus\Utils;

class Includes {
	public static function main() {
		include_once( DRPLUS_DIR . "inc/sms/SMSUtils.php" );
		include_once( DRPLUS_DIR . "inc/sms/Gateways/Gateway.php" );

		// Include gateway files automatically
		$gateways = SMSUtils::gateways();
		foreach( $gateways as $gateway_name => $gateway ) {
			$filename = Utils::convert_to_pascal_case( $gateway_name );
			include_once( DRPLUS_DIR . "inc/sms/Gateways/{$filename}.php" );
		}
		
		include_once( DRPLUS_DIR . "inc/sms/Gateways/SMS.php" );

		if( is_admin() ) {
			include_once( DRPLUS_DIR . "inc/sms/Templates/Backend/Variable.php" );
			include_once( DRPLUS_DIR . "inc/sms/Page.php" );
		}
	}
}
Includes::main();